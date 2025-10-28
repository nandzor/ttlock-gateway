#!/bin/bash

# Script to run 200 million rows bulk load
echo "=== Re-ID Bulk Loader - 200 Million Rows ==="

# Set Go path
export PATH=$PATH:/usr/local/go/bin

# Check if binary exists
if [ ! -f "./reid_loader" ]; then
    echo "Binary not found. Building..."
    go build -o reid_loader .
fi

echo "Binary size: $(ls -lh reid_loader | awk '{print $5}')"
echo ""

# Show configuration
echo "Configuration:"
echo "- Rows: 200,000,000"
echo "- Workers: 32"
echo "- Batch Size: 20,000"
echo "- Skip FK Check: Yes (for speed)"
echo "- Detected Count: Always 1 (matches branch_event_settings)"
echo "- Device IDs: Predefined (CAM_JKT001_001, etc.)"
echo "- Branch IDs: 1,2,3,4,5,6,7"
echo ""

# Ask for confirmation
read -p "Do you want to proceed with 200M rows? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo "Starting bulk load..."
echo "Press Ctrl+C to stop gracefully"
echo ""

# Run the bulk loader
./reid_loader -rows=200000000 -workers=32 -batch=20000 -skipFK

echo ""
echo "Bulk load completed!"
