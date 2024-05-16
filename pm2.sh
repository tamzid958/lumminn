#!/bin/bash

# Check if the queue worker process exists
if ! pm2 list | grep -q "queue-worker"; then
    # Start the queue worker process if it doesn't exist
    pm2 start artisan --interpreter php --name queue-worker -- queue:work --sleep=3 --tries=3 --daemon
fi
