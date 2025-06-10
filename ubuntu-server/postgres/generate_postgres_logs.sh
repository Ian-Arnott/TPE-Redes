#!/bin/bash

echo "🔧 Ejecutando pruebas de logs en PostgreSQL..."

# Configuración de conexión
HOST="localhost"
PORT="5432"
USER="admin"
DB="postgres"

export PGPASSWORD="admin"

# Consulta rápida
echo "Consulta rápida"
psql -h "$HOST" -U "$USER" -d "$DB" -c "SELECT NOW();" || echo "❌ Error ejecutando consulta rápida"

# Consulta lenta (> 3s, generará log si log_min_duration_statement=3000 está configurado)
echo "Consulta lenta (duración > 3s)"
psql -h "$HOST" -U "$USER" -d "$DB" -c "SELECT pg_sleep(4);" || echo "❌ Error ejecutando consulta lenta"

# Consulta inválida
echo "Consulta inválida (tabla inexistente)"
psql -h "$HOST" -U "$USER" -d "$DB" -c "SELECT * FROM tabla_inexistente;" || echo "❌ Consulta inválida lanzada correctamente"

echo "Finalizado. Verifica los logs en /var/log/postgresql/postgresql.log"
