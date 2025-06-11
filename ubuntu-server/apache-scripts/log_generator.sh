#!/bin/bash

# 📍 Directorios de logs montados por Filebeat
APACHE_LOG="/var/log/apache2/access.log"
ERROR_LOG="/var/log/apache2/error.log"

# 📆 Fechas simuladas (formato Apache y PostgreSQL)
TODAY=$(date '+%d/%b/%Y')
YESTERDAY=$(date -d 'yesterday' '+%d/%b/%Y')
TWO_DAYS_AGO=$(date -d '2 days ago' '+%d/%b/%Y')


# 🧪 Generar logs falsos con timestamps retroactivos

generate_logs() {
  echo "🟢 Generando logs simulados..."

  # Apache access logs
  for day in "$TODAY" "$YESTERDAY" "$TWO_DAYS_AGO"; do
    echo "192.168.1.1 - - [09/Jun/2025:10:00:00 +0000] \"GET / HTTP/1.1\" 200 1234" >> "$APACHE_LOG"
    echo "192.168.1.1 - - [08/Jun/2025:10:00:00 +0000] \"GET / HTTP/1.1\" 200 1234" >> "$APACHE_LOG"
    echo "192.168.1.1 - - [07/Jun/2025:10:00:00 +0000] \"GET / HTTP/1.1\" 200 1234" >> "$APACHE_LOG"
    echo "192.168.1.1 - - [$day:12:00:00 +0000] \"POST /login HTTP/1.1\" 500 999" >> "$APACHE_LOG"
  done

  # Apache error logs
  for day in "$TODAY" "$YESTERDAY" "$TWO_DAYS_AGO"; do
    echo "[$day 12:00:00.000000 2025] [php:error] [pid 123] [client 127.0.0.1:9999] Test PHP error 500" >> "$ERROR_LOG"
  done

  echo "✅ Logs generados con éxito"
}

generate_logs