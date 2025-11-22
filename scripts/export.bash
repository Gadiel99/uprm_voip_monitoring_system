#!/usr/bin/env bash

# ============================================
# SipXcom Auto Export Script (Optimized)
# Exports only required columns for ETL
# This script goes in the SipXcom server.
# ============================================

# Configuration
EXPORT_BASE="$HOME/sipxcom-exports"
EXPORT_DIR="${EXPORT_BASE}/latest"
ARCHIVE_DIR="${EXPORT_BASE}/archives"
LOG_FILE="$HOME/sipxcom-export.log"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
ARCHIVE_NAME="sipxcom-export-${TIMESTAMP}.tar.gz"

# Database credentials
PG_HOST="localhost"
PG_PORT="5432"
PG_DATABASE="sipxconfig"
PG_USER="postgres"
PG_PASSWORD="admin"

MONGO_HOST="localhost"
MONGO_PORT="27017"
MONGO_DATABASE="node"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "${LOG_FILE}"
}

log "🚀 Starting optimized export (required columns only)..."

# Create directories
mkdir -p "${EXPORT_DIR}"
mkdir -p "${ARCHIVE_DIR}"

# Clean export directory
rm -f "${EXPORT_DIR}"/*

# ===== EXPORT POSTGRESQL USERS TABLE =====
log "📊 Exporting PostgreSQL users table (first_name, last_name, user_name only)..."
PGPASSWORD="${PG_PASSWORD}" psql -h "${PG_HOST}" \
     -p "${PG_PORT}" \
     -U "${PG_USER}" \
     -d "${PG_DATABASE}" \
     -c "COPY (
         SELECT 
             user_name,
             first_name,
             last_name
         FROM users
         WHERE user_name IS NOT NULL
           AND user_name != ''
         ORDER BY user_name
     ) TO STDOUT WITH (FORMAT CSV, HEADER)" \
     > "${EXPORT_DIR}/users.csv"

if [ $? -eq 0 ]; then
    USER_COUNT=$(tail -n +2 "${EXPORT_DIR}/users.csv" | wc -l)
    log "✅ users.csv exported (${USER_COUNT} users)"
else
    log "❌ ERROR: Failed to export users.csv"
    exit 1
fi

# ===== EXPORT POSTGRESQL PHONE TABLE =====
log "📞 Exporting PostgreSQL phone table (serial_number/MAC only)..."
PGPASSWORD="${PG_PASSWORD}" psql -h "${PG_HOST}" \
     -p "${PG_PORT}" \
     -U "${PG_USER}" \
     -d "${PG_DATABASE}" \
     -c "COPY (
         SELECT 
             phone_id,
             serial_number
         FROM phone
         WHERE serial_number IS NOT NULL
           AND serial_number != ''
         ORDER BY phone_id
     ) TO STDOUT WITH (FORMAT CSV, HEADER)" \
     > "${EXPORT_DIR}/phone.csv"

if [ $? -eq 0 ]; then
    PHONE_COUNT=$(tail -n +2 "${EXPORT_DIR}/phone.csv" | wc -l)
    log "✅ phone.csv exported (${PHONE_COUNT} phones)"
else
    log "❌ ERROR: Failed to export phone.csv"
    exit 1
fi

# ===== EXPORT MONGODB DATA (REQUIRED FIELDS ONLY) =====
log "🍃 Exporting MongoDB registrar collection (required fields only)..."
mongoexport --host "${MONGO_HOST}" \
            --port "${MONGO_PORT}" \
            --db "${MONGO_DATABASE}" \
            --collection registrar \
            --fields identity,binding,instrument,expired \
            --out "${EXPORT_DIR}/registrar.json" \
            --jsonArray

if [ $? -eq 0 ]; then
    REG_COUNT=$(jq '. | length' "${EXPORT_DIR}/registrar.json" 2>/dev/null || echo "0")
    log "✅ registrar.json exported (${REG_COUNT} registrations)"
else
    log "❌ ERROR: Failed to export registrar.json"
    exit 1
fi

# ===== CREATE METADATA FILE =====
log "📝 Creating metadata..."
cat > "${EXPORT_DIR}/metadata.json" << EOF
{
  "export_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "export_timestamp": $(date +%s),
  "server": "$(hostname)",
  "export_version": "2.1",
  "optimization": "required_columns_only",
  "postgresql": {
    "host": "${PG_HOST}",
    "port": "${PG_PORT}",
    "database": "${PG_DATABASE}",
    "tables": {
      "users": {
        "columns": ["user_name", "first_name", "last_name"],
        "filter": "WHERE user_name IS NOT NULL AND user_name != ''",
        "record_count": ${USER_COUNT:-0}
      },
      "phone": {
        "columns": ["phone_id", "serial_number"],
        "filter": "WHERE serial_number IS NOT NULL AND serial_number != ''",
        "record_count": ${PHONE_COUNT:-0}
      }
    }
  },
  "mongodb": {
    "host": "${MONGO_HOST}",
    "port": "${MONGO_PORT}",
    "database": "${MONGO_DATABASE}",
    "collection": "registrar",
    "fields": ["identity","binding","instrument","expired"],
    "record_count": ${REG_COUNT:-0}
  },
  "files": {
    "users_csv": {
      "filename": "users.csv",
      "format": "CSV",
      "size_bytes": $(stat -c%s "${EXPORT_DIR}/users.csv" 2>/dev/null || stat -f%z "${EXPORT_DIR}/users.csv" 2>/dev/null || echo 0)
    },
    "phone_csv": {
      "filename": "phone.csv",
      "format": "CSV",
      "size_bytes": $(stat -c%s "${EXPORT_DIR}/phone.csv" 2>/dev/null || stat -f%z "${EXPORT_DIR}/phone.csv" 2>/dev/null || echo 0)
    },
    "registrar": {
      "filename": "registrar.json",
      "format": "JSON Array",
      "size_bytes": $(stat -c%s "${EXPORT_DIR}/registrar.json" 2>/dev/null || stat -f%z "${EXPORT_DIR}/registrar.json" 2>/dev/null || echo 0)
    }
  }
}
EOF

log "✅ metadata.json created"

# ===== CREATE ARCHIVE =====
log "📦 Creating archive..."
cd "${EXPORT_BASE}"
tar -czf "${ARCHIVE_DIR}/${ARCHIVE_NAME}" -C latest .

if [ $? -eq 0 ]; then
    ARCHIVE_SIZE=$(du -h "${ARCHIVE_DIR}/${ARCHIVE_NAME}" | cut -f1)
    log "✅ Archive created: ${ARCHIVE_NAME} (${ARCHIVE_SIZE})"
    
    # Create symlink to latest
    ln -sf "${ARCHIVE_DIR}/${ARCHIVE_NAME}" "${ARCHIVE_DIR}/latest.tar.gz"
    log "✅ Symlink updated: latest.tar.gz"
else
    log "❌ ERROR: Failed to create archive"
    exit 1
fi

# ===== CLEANUP OLD ARCHIVES (Keep last 10) =====
log "🧹 Cleaning old archives..."
cd "${ARCHIVE_DIR}"
ls -t sipxcom-export-*.tar.gz | tail -n +11 | xargs rm -f 2>/dev/null
REMAINING=$(ls -1 sipxcom-export-*.tar.gz 2>/dev/null | wc -l)
log "📦 Archives retained: ${REMAINING}"

log "✅ Export and packaging completed!"
log "   Archive: ${ARCHIVE_DIR}/${ARCHIVE_NAME}"
log "   Ready for transfer to application server"


