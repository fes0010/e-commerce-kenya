#!/bin/bash
# MySQL Proxy Script - Forward MySQL from Docker container to localhost
# This script creates an SSH tunnel to make the MySQL container accessible on localhost:3306

# Kill any existing tunnel
pkill -f "ssh -N -L 3307:10.0.1.29:3306"

# Create new tunnel on port 3307 to avoid conflicts
ssh -N -L 3307:10.0.1.29:3306 localhost &

echo "MySQL proxy started on localhost:3307"
echo "Update your .env file to use DB_PORT=3307"
