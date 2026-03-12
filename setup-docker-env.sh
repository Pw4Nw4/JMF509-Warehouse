#!/bin/bash
# Generate secure credentials for Docker stack
# Creates .env from .env.docker.example with random passwords

cd "$(dirname "$0")"

if [ -f .env ] && ! grep -q "CHANGE_ME" .env 2>/dev/null; then
  echo ".env already exists with custom credentials. Skipping."
  exit 0
fi

PG_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)
PGADMIN_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)

cat > .env << EOF
# Docker stack - auto-generated secure credentials
POSTGRES_USER=jmf509_user
POSTGRES_PASSWORD=$PG_PASS
POSTGRES_DB=jmf509_warehouse

PGADMIN_EMAIL=admin@jmf509.com
PGADMIN_PASSWORD=$PGADMIN_PASS
EOF

echo "Created .env with secure credentials."
echo ""
echo "PostgreSQL: user=jmf509_user  password=$PG_PASS"
echo "pgAdmin:    email=admin@jmf509.com  password=$PGADMIN_PASS"
echo ""
echo "Save these credentials. Run: docker compose up -d"
