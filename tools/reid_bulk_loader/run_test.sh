#!/bin/bash

# Test script for reid_loader
echo "=== Testing Re-ID Bulk Loader ==="

# Set Go path
export PATH=$PATH:/usr/local/go/bin

# Check if binary exists
if [ ! -f "./reid_loader" ]; then
    echo "Binary not found. Building..."
    go build -o reid_loader .
fi

echo "Binary size: $(ls -lh reid_loader | awk '{print $5}')"
echo ""

# Test 1: Help
echo "=== Test 1: Help ==="
./reid_loader -h
echo ""

# Test 2: Small test run (1000 rows)
echo "=== Test 2: Small Test Run (1000 rows) ==="
echo "Running with 1000 rows, 4 workers, batch 100..."
./reid_loader -rows=1000 -workers=4 -batch=100 -skipFK
echo ""

# Test 3: Medium test run (10000 rows)
echo "=== Test 3: Medium Test Run (10000 rows) ==="
echo "Running with 10000 rows, 8 workers, batch 1000..."
./reid_loader -rows=10000 -workers=8 -batch=1000 -skipFK
echo ""

echo "=== All Tests Completed ==="
