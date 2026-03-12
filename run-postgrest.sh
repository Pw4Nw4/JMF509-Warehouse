#!/bin/bash
# Start JMF 509 Warehouse site
# Add DB later and configure .env

cd "$(dirname "$0")"

if ! command -v docker &>/dev/null; then
  echo "Docker not found. Install Docker first:"
  echo "  - https://docs.docker.com/get-docker/"
  exit 1
fi

echo "Starting site..."
docker compose up -d --build

echo ""
echo "Site: http://localhost:8080"
echo "Add DB later: copy .env.example to .env and set DB credentials"
echo ""
echo "Stop: docker compose down"
