#!/bin/bash

# Run Laravel migrations from Docker container with access to dokploy network

echo "Running migrations from Docker container..."
echo "This container has access to the dokploy-network"
echo ""

docker run --rm \
  --network dokploy-network \
  -v $(pwd):/app \
  -w /app \
  -e DB_HOST=10.0.1.29 \
  -e DB_PORT=3306 \
  -e DB_DATABASE=bagisto \
  -e DB_USERNAME=root \
  -e DB_PASSWORD=Enterpassword001. \
  composer:latest \
  bash -c "composer install --no-dev --optimize-autoloader && php artisan migrate --force"

echo ""
echo "Migration complete!"
