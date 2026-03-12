#!/bin/bash
# Generate secure credentials for Docker stack (PostgreSQL only)

cd "$(dirname "$0")"

if [ -f .env ] && ! grep -q "CHANGE_ME" .env 2>/dev/null; then
  echo ".env already exists with custom credentials. Skipping."
  exit 0
fi

PG_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)

cat > .env << EOF
# Docker stack - PostgreSQL credentials
POSTGRES_USER=jmf509_user
POSTGRES_PASSWORD=$PG_PASS
POSTGRES_DB=jmf509_warehouse
EOF

echo "Created .env with secure credentials."
echo ""
echo "PostgreSQL: user=jmf509_user  password=$PG_PASS  database=jmf509_warehouse"
echo ""
echo "Save these credentials. Run: docker compose up -d"
