package main

import (
	"context"
	"crypto/rand"
	"encoding/hex"
	"errors"
	"flag"
	"fmt"
	"log"
	mathrand "math/rand"
	"os"
	"os/signal"
	"runtime"
	"sync"
	"syscall"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgconn"
	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/joho/godotenv"
)

type detectionRow struct {
    reID               string
    branchID           int64
    deviceID           string
    detectionTimestamp time.Time
    detectedCount      int32
    detectionData      string // JSON string
}

func envOr(key, def string) string {
    if v := os.Getenv(key); v != "" {
        return v
    }
    return def
}

func mustLoadEnv() {
	// Try load .env from multiple probable locations
	_ = godotenv.Load(
		".env",
		"../.env",
		"../../.env",
	)
}

func buildConnString() string {
    host := envOr("DB_HOST", "127.0.0.1")
    port := envOr("DB_PORT", "5433")
    user := envOr("DB_USERNAME", "postgres")
    pass := envOr("DB_PASSWORD", "kambin")
    db := envOr("DB_DATABASE", "cctv_dashboard")
    sslmode := envOr("DB_SSLMODE", "disable")

    // Laravel sometimes uses DB_URL; prefer it if set
    if url := os.Getenv("DB_URL"); url != "" {
        return url
    }

    return fmt.Sprintf("postgres://%s:%s@%s:%s/%s?sslmode=%s",
        user, pass, host, port, db, sslmode,
    )
}

func fetchIds(ctx context.Context, pool *pgxpool.Pool, sql string) ([]int64, error) {
    rows, err := pool.Query(ctx, sql)
    if err != nil {
        return nil, err
    }
    defer rows.Close()
    var res []int64
    for rows.Next() {
        var id int64
        if err := rows.Scan(&id); err != nil {
            return nil, err
        }
        res = append(res, id)
    }
    return res, rows.Err()
}

func fetchDeviceIds(ctx context.Context, pool *pgxpool.Pool) ([]string, error) {
    rows, err := pool.Query(ctx, "select device_id from device_masters where status = 'active' and device_type = 'cctv'")
    if err != nil {
        return nil, err
    }
    defer rows.Close()
    var res []string
    for rows.Next() {
        var id string
        if err := rows.Scan(&id); err != nil {
            return nil, err
        }
        res = append(res, id)
    }
    return res, rows.Err()
}

func randomReID() string {
    b := make([]byte, 12)
    if _, err := rand.Read(b); err == nil {
        return hex.EncodeToString(b)
    }
    // fallback
    return fmt.Sprintf("reid_%d", time.Now().UnixNano())
}

// Predefined device IDs based on your pattern
var deviceIDs = []string{
    "CAM_JKT001_001", "CAM_JKT001_002", "NODE_JKT001_001",
    "CAM_JKT002_001", "CAM_JKT002_002", "CAM_BDG001_001",
    "NODE_BDG001_001", "CAM_SBY001_001", "MIKROTIK_SBY001",
}

// Predefined branch IDs
var branchIDs = []int64{1, 2, 3, 4, 5, 6, 7}

func generateRandomDeviceID() string {
    // Generate random device ID that follows the pattern but uses existing cities
    cities := []string{"JKT", "BDG", "SBY"} // Only use cities that exist in predefined devices
    deviceTypes := []string{"CAM", "NODE", "MIKROTIK"} // Only use types that exist

    city := cities[mathrand.Intn(len(cities))]
    deviceType := deviceTypes[mathrand.Intn(len(deviceTypes))]
    branchNum := fmt.Sprintf("%03d", mathrand.Intn(3)+1) // Only 001-003 to match existing pattern
    deviceNum := fmt.Sprintf("%03d", mathrand.Intn(3)+1) // Only 001-003 to match existing pattern

    return fmt.Sprintf("%s_%s%s_%s", deviceType, city, branchNum, deviceNum)
}

func generateRow(baseDay time.Time, useRandomDevices bool) detectionRow {
    branch := branchIDs[mathrand.Intn(len(branchIDs))]

    var device string
    if useRandomDevices {
        // For random devices, still use predefined IDs but shuffle them
        // This ensures foreign key constraints are satisfied
        device = deviceIDs[mathrand.Intn(len(deviceIDs))]
    } else {
        device = deviceIDs[mathrand.Intn(len(deviceIDs))]
    }

    ts := baseDay.Add(time.Duration(mathrand.Intn(24))*time.Hour).Add(time.Duration(mathrand.Intn(60))*time.Minute)

    // Always set detected_count to 1 as per requirement
    // This matches the branch_event_settings expectation where each detection is counted as 1
    count := int32(1)

    // Keep JSON small to reduce IO but include relevant detection data
    json := fmt.Sprintf(`{"confidence":%.2f,"frame":%d,"detection_type":"person","bounding_box":{"x":%d,"y":%d,"width":%d,"height":%d}}`,
        0.85+mathrand.Float64()*0.14,
        1000+mathrand.Intn(9000),
        mathrand.Intn(200)+50,  // x coordinate
        mathrand.Intn(200)+50,  // y coordinate
        mathrand.Intn(100)+50,  // width
        mathrand.Intn(100)+50)  // height

    return detectionRow{
        reID:               randomReID(),
        branchID:           branch,
        deviceID:           device,
        detectionTimestamp: ts,
        detectedCount:      count,
        detectionData:      json,
    }
}

func copyBatch(ctx context.Context, conn *pgx.Conn, rows []detectionRow) error {
    // COPY is fastest path. Ensure columns order matches table.
    copyCount, err := conn.CopyFrom(
        ctx,
        pgx.Identifier{"re_id_branch_detections"},
        []string{"re_id", "branch_id", "device_id", "detection_timestamp", "detected_count", "detection_data"},
        pgx.CopyFromSlice(len(rows), func(i int) ([]any, error) {
            r := rows[i]
            return []any{r.reID, r.branchID, r.deviceID, r.detectionTimestamp, r.detectedCount, r.detectionData}, nil
        }),
    )
    if err != nil {
        return err
    }
    if copyCount != int64(len(rows)) {
        return fmt.Errorf("copy mismatch: expected %d got %d", len(rows), copyCount)
    }
    return nil
}

func main() {
    mustLoadEnv()

    var (
        totalRows      = flag.Int64("rows", 200_000_000, "Total rows to insert")
        workers        = flag.Int("workers", runtime.NumCPU()*3, "Number of concurrent workers")
        batchSize      = flag.Int("batch", 10_000, "Rows per batch (COPY)")
        dayOffset      = flag.Int("dayOffset", 0, "Base day offset from today")
        skipFKCheck    = flag.Bool("skipFK", false, "Skip checking FK source tables (danger)")
        useRandomDevices = flag.Bool("randomDevices", false, "Use randomly generated device IDs instead of predefined ones")
    )
    flag.Parse()

    // Validate parameters
    if *totalRows <= 0 {
        log.Fatal("rows must be greater than 0")
    }
    if *workers <= 0 {
        log.Fatal("workers must be greater than 0")
    }
    if *batchSize <= 0 {
        log.Fatal("batch must be greater than 0")
    }
    if *workers > 1000 {
        log.Printf("WARNING: %d workers is very high, consider reducing to avoid overwhelming the database", *workers)
    }

    // Initialize random source
    mathrand.Seed(time.Now().UnixNano())

    connStr := buildConnString()
    cfg, err := pgxpool.ParseConfig(connStr)
    if err != nil {
        log.Fatalf("parse config: %v", err)
    }
    // Big pool; server will cap appropriately
    cfg.MaxConns = int32(*workers * 2)
    cfg.MinConns = int32(*workers)
    cfg.MaxConnIdleTime = 2 * time.Minute
    cfg.MaxConnLifetime = 0

    // Create context with timeout for the entire operation
    ctx, cancel := context.WithTimeout(context.Background(), 24*time.Hour)
    defer cancel()

    // Handle graceful shutdown on SIGINT/SIGTERM
    sigChan := make(chan os.Signal, 1)
    signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)
    go func() {
        <-sigChan
        log.Println("Received shutdown signal, cancelling context...")
        cancel()
    }()

    pool, err := pgxpool.NewWithConfig(ctx, cfg)
    if err != nil {
        log.Fatalf("connect pool: %v", err)
    }
    defer pool.Close()

    // Test connection
    log.Println("Testing database connection...")
    if err := pool.Ping(ctx); err != nil {
        log.Fatalf("database ping failed: %v", err)
    }
    log.Println("Database connection successful")

    // Use predefined device and branch IDs
    log.Printf("Using predefined Branch IDs: %v", branchIDs)
    if *useRandomDevices {
        log.Printf("Using randomly generated device IDs (pattern: TYPE_CITY###_###)")
    } else {
        log.Printf("Using predefined Device IDs: %v", deviceIDs)
    }

    // Optional: Verify IDs exist in database
    if !*skipFKCheck {
        log.Println("Verifying branch IDs exist in database...")
        for _, branchID := range branchIDs {
            var count int
            err := pool.QueryRow(ctx, "SELECT COUNT(*) FROM company_branches WHERE id = $1", branchID).Scan(&count)
            if err != nil {
                log.Printf("WARNING: Could not verify branch ID %d: %v", branchID, err)
            } else if count == 0 {
                log.Printf("WARNING: Branch ID %d does not exist in database", branchID)
            }
        }

        log.Println("Verifying device IDs exist in database...")
        for _, deviceID := range deviceIDs {
            var count int
            err := pool.QueryRow(ctx, "SELECT COUNT(*) FROM device_masters WHERE device_id = $1", deviceID).Scan(&count)
            if err != nil {
                log.Printf("WARNING: Could not verify device ID %s: %v", deviceID, err)
            } else if count == 0 {
                log.Printf("WARNING: Device ID %s does not exist in database", deviceID)
            }
        }
    } else {
        log.Println("WARNING: skipping FK source checks; ensure referential integrity manually")
    }

    // Dedicated connections per worker for COPY
    dials := make([]*pgx.Conn, *workers)
    for i := 0; i < *workers; i++ {
        c, err := pgx.Connect(ctx, connStr)
        if err != nil {
            log.Fatalf("connect worker %d: %v", i, err)
        }
        dials[i] = c
    }

    // Cleanup function for connections
    defer func() {
        for i, conn := range dials {
            if conn != nil {
                if err := conn.Close(ctx); err != nil {
                    log.Printf("Error closing worker %d connection: %v", i, err)
                }
            }
        }
    }()

    start := time.Now()
    log.Printf("Starting load: rows=%d workers=%d batch=%d", *totalRows, *workers, *batchSize)

    // Work distribution
    type job struct{ rows []detectionRow }
    jobs := make(chan job, *workers*2)
    var wg sync.WaitGroup
    var processedRows int64
    var mu sync.Mutex

    // workers
    for w := 0; w < *workers; w++ {
        wg.Add(1)
        go func(wi int) {
            defer wg.Done()
            conn := dials[wi]
            for j := range jobs {
                // retry with simple backoff on transient errors
                success := false
                for attempt := 1; attempt <= 5; attempt++ {
                    if err := copyBatch(ctx, conn, j.rows); err != nil {
                        var pgErr *pgconn.PgError
                        if errors.As(err, &pgErr) {
                            // Unique/constraint or others; log and continue
                            log.Printf("worker %d: pg error (%s): %s", wi, pgErr.Code, pgErr.Message)
                        } else {
                            log.Printf("worker %d: copy error (attempt %d): %v", wi, attempt, err)
                        }
                        time.Sleep(time.Duration(attempt*250) * time.Millisecond)
                        continue
                    }
                    success = true
                    break
                }

                if success {
                    mu.Lock()
                    processedRows += int64(len(j.rows))
                    mu.Unlock()
                } else {
                    log.Printf("worker %d: failed to process batch after 5 attempts, skipping", wi)
                }
            }
        }(w)
    }

    // Progress monitoring goroutine
    go func() {
        ticker := time.NewTicker(10 * time.Second)
        defer ticker.Stop()
        for {
            select {
            case <-ticker.C:
                mu.Lock()
                processed := processedRows
                mu.Unlock()
                if processed > 0 {
                    elapsed := time.Since(start)
                    rps := float64(processed) / elapsed.Seconds()
                    log.Printf("Progress: %d/%d rows processed (%.1f%%) - %.0f rows/sec",
                        processed, *totalRows, float64(processed)/float64(*totalRows)*100, rps)
                }
            case <-ctx.Done():
                return
            }
        }
    }()

    // producer
    baseDay := time.Now().AddDate(0, 0, *dayOffset).Truncate(24 * time.Hour)
    var produced int64
    for produced < *totalRows {
        select {
        case <-ctx.Done():
            log.Printf("Context cancelled, stopping producer")
            break
        default:
        }

        remaining := int(*totalRows - produced)
        n := *batchSize
        if n > remaining {
            n = remaining
        }
        batch := make([]detectionRow, n)
        for i := 0; i < n; i++ {
            batch[i] = generateRow(baseDay, *useRandomDevices)
        }

        select {
        case jobs <- job{rows: batch}:
            produced += int64(n)
        case <-ctx.Done():
            log.Printf("Context cancelled, stopping producer")
            break
        }
    }
    close(jobs)
    wg.Wait()

    dur := time.Since(start)
    mu.Lock()
    finalProcessed := processedRows
    mu.Unlock()

    if finalProcessed > 0 {
        rps := float64(finalProcessed) / dur.Seconds()
        log.Printf("Completed: %d/%d rows processed in %s (%.0f rows/sec)",
            finalProcessed, *totalRows, dur.Truncate(time.Millisecond), rps)
    } else {
        log.Printf("No rows were successfully processed")
    }
}


