#!/bin/bash
# Quick way to run JMF 509 Warehouse locally
# Requires: PHP and MySQL

cd "$(dirname "$0")"

if ! command -v php &>/dev/null; then
  echo "PHP not found. Install it first:"
  case "$(uname -s)" in
    Darwin)
      echo "  - macOS: brew install php   (requires Homebrew)"
      ;;
    Linux)
      echo "  - Ubuntu/Debian: sudo apt install php php-mysql php-mbstring"
      echo "  - Fedora: sudo dnf install php php-mysqlnd php-mbstring"
      echo "  - Arch: sudo pacman -S php php-mysql"
      ;;
    *)
      echo "  - Install PHP 7.4+ with MySQL PDO and mbstring extensions"
      ;;
  esac
  echo "  - Or use XAMPP: https://www.apachefriends.org/"
  exit 1
fi

if [ ! -f .env ]; then
  echo "Creating .env from .env.example..."
  cp .env.example .env
  echo "Edit .env and set your DB_HOST, DB_NAME, DB_USER, DB_PASS"
  echo "Then run: mysql -u user -p your_db < schema.sql"
fi

echo "Starting server at http://localhost:8080"
echo "Open that URL in your browser."
php -S localhost:8080
