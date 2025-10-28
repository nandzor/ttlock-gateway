# Re-ID Branch Detection Bulk Loader

High-performance Go tool for bulk inserting data into `re_id_branch_detections` table using PostgreSQL COPY protocol.

## Features

- **High Performance**: Uses PostgreSQL COPY protocol with worker pool
- **Parallel Processing**: Multiple workers for concurrent inserts
- **Configurable**: Adjustable batch size, worker count, and row count
- **Predefined IDs**: Uses your specific device and branch IDs
- **Random Generation**: Option to generate random device IDs following your pattern
- **Environment Support**: Reads Laravel `.env` configuration

## Predefined Data

### Device IDs

```
CAM_JKT001_001, CAM_JKT001_002, NODE_JKT001_001,
CAM_JKT002_001, CAM_JKT002_002, CAM_BDG001_001,
NODE_BDG001_001, CAM_SBY001_001, MIKROTIK_SBY001
```

### Branch IDs

```
1, 2, 3, 4, 5, 6, 7
```

## Usage

### Build

```bash
cd tools/reid_bulk_loader
go build -o reid_loader
```

### Run with predefined device IDs (default)

```bash
./reid_loader -rows=200000000 -workers=32 -batch=20000
```

### Run with random device IDs

```bash
./reid_loader -rows=200000000 -workers=32 -batch=20000 -randomDevices
```

### Skip foreign key verification

```bash
./reid_loader -rows=200000000 -workers=32 -batch=20000 -skipFK
```

## Command Line Options

- `-rows`: Total number of rows to insert (default: 200,000,000)
- `-workers`: Number of concurrent workers (default: CPU cores × 3)
- `-batch`: Rows per batch for COPY operation (default: 10,000)
- `-dayOffset`: Base day offset from today (default: 0)
- `-skipFK`: Skip foreign key verification (dangerous)
- `-randomDevices`: Use randomly generated device IDs instead of predefined ones

## Environment Variables

The tool reads from your Laravel `.env` file or environment variables:

- `DB_HOST` (default: 127.0.0.1)
- `DB_PORT` (default: 5432)
- `DB_DATABASE` (default: postgres)
- `DB_USERNAME` (default: postgres)
- `DB_PASSWORD` (default: empty)
- `DB_SSLMODE` (default: disable)
- `DB_URL` (optional, overrides individual settings)

## Performance Tips

1. **Batch Size**: Larger batches (20k-50k) generally perform better
2. **Workers**: Use 2-4x CPU cores for optimal performance
3. **Database Tuning**: Increase `maintenance_work_mem`, `checkpoint_timeout`
4. **Indexes**: Consider dropping non-essential indexes during bulk load
5. **Storage**: Ensure sufficient IOPS for your storage system

## Example Performance

On a typical server with SSD storage:

- **200M rows**: ~2-4 hours
- **Throughput**: 15,000-50,000 rows/second
- **Memory usage**: ~500MB-2GB depending on batch size

## Random Device ID Pattern

When using `-randomDevices`, generates IDs like:

- `CAM_JKT001_001`
- `NODE_BDG045_123`
- `MIKROTIK_SBY999_456`

Pattern: `{TYPE}_{CITY}{BRANCH}_{DEVICE}`

- TYPE: CAM, NODE, MIKROTIK, SENSOR, GATEWAY
- CITY: JKT, BDG, SBY, MDN, YGY, BAL, PKU
- BRANCH: 001-999
- DEVICE: 001-999
