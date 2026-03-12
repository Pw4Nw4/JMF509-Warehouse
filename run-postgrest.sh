#!/bin/bash
# Start PostgreSQL + PostgREST + pgAdmin for JMF 509 Warehouse
# PostgREST API: http://localhost:3000
# pgAdmin: http://localhost:5050

cd "$(dirname "$0")"

if ! command -v docker &>/dev/null; then
  echo "Docker not found. Install Docker first:"
  echo "  - https://docs.docker.com/get-docker/"
  exit 1
fi

if [ ! -f .env ]; then
  echo "No .env found. Running setup to generate secure credentials..."
  chmod +x setup-docker-env.sh 2>/dev/null
  ./setup-docker-env.sh
  echo ""
fi

echo "Starting PostgreSQL + PostgREST + pgAdmin..."
docker compose up -d

echo ""
echo "PostgREST API:  http://localhost:3000"
echo "pgAdmin:       http://localhost:5050"
echo ""
echo "Credentials are in .env (PostgreSQL user, pgAdmin login)"
echo "pgAdmin: Add server with host=db, user from .env, database=jmf509_warehouse"
echo ""
echo "Stop: docker compose down"
