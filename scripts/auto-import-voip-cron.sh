
#!/usr/bin/env bash
# ============================================
# Automated VoIP Data Import Script
# Uses SCP to fetch archive from CentOS
# ============================================

APP_PATH="/var/www/voip_mon"
LOG_FILE="${APP_PATH}/storage/logs/auto-import.log"
LOCK_FILE="/tmp/voip-import.lock"

# SCP Configuration
CENTOS_HOST="136.145.71.54"  # Replace with your CentOS IP
CENTOS_USER="estudiante"       # Replace with your CentOS user
CENTOS_ARCHIVE_DIR="/home/estudiante/sipxcom-exports/archives"
SSH_KEY="/var/www/.ssh/id_rsa_voip_auto"

# Local paths
IMPORT_DIR="${APP_PATH}/storage/app/imports/archives"

# Logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "${LOG_FILE}"
}

# Read .env variable
get_env() {
    local key="$1"
    grep -E "^${key}=" "${APP_PATH}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"' | tr -d "'"
}

# Prevent concurrent runs
if [ -f "${LOCK_FILE}" ]; then
    log "⚠️  Previous import still running, skipping..."
    exit 0
fi

touch "${LOCK_FILE}"
trap "rm -f ${LOCK_FILE}" EXIT

log "🤖 Starting auto-import via SCP..."

# Verify SSH key exists
if [ ! -f "${SSH_KEY}" ]; then
    log "❌ SSH key not found: ${SSH_KEY}"
    exit 1
fi

# Get list of available archives from CentOS
log "🔍 Checking for new archives on ${CENTOS_HOST}..."

REMOTE_ARCHIVES=$(ssh -i "${SSH_KEY}" \
                      -o StrictHostKeyChecking=no \
                      -o ConnectTimeout=10 \
                      "${CENTOS_USER}@${CENTOS_HOST}" \
                      "ls -t ${CENTOS_ARCHIVE_DIR}/sipxcom-export-*.tar.gz 2>/dev/null" \
                      | head -1)

if [ -z "${REMOTE_ARCHIVES}" ]; then
    log "⚠️  No archives found on remote server"
    log "📧 Running notification check with existing data..."
    cd "${APP_PATH}"
    php artisan notifications:check
    if [ $? -ne 0 ]; then
        log "⚠️  Notification check encountered an error"
    else
        log "✅ Notification check completed"
    fi
    log "🎉 Notification check finished (no new archives)"
    exit 0
fi

ARCHIVE_NAME=$(basename "${REMOTE_ARCHIVES}")
DEST_ARCHIVE="${IMPORT_DIR}/${ARCHIVE_NAME}"

# Check if already processed
if [ -f "${DEST_ARCHIVE}" ]; then
    log "ℹ️  Archive already processed: ${ARCHIVE_NAME}"
    log "📧 Running notification check with existing data..."
    cd "${APP_PATH}"
    php artisan notifications:check
    if [ $? -ne 0 ]; then
        log "⚠️  Notification check encountered an error"
    else
        log "✅ Notification check completed"
    fi
    log "🎉 Notification check finished (no new data to import)"
    exit 0
fi

# Download archive via SCP
log "📥 Downloading ${ARCHIVE_NAME}..."
mkdir -p "${IMPORT_DIR}"

scp -i "${SSH_KEY}" \
    -o StrictHostKeyChecking=no \
    -o ConnectTimeout=10 \
    "${CENTOS_USER}@${CENTOS_HOST}:${REMOTE_ARCHIVES}" \
    "${DEST_ARCHIVE}"

if [ $? -ne 0 ]; then
    log "❌ Failed to download archive via SCP"
    exit 1
fi

ARCHIVE_SIZE=$(du -h "${DEST_ARCHIVE}" | cut -f1)
log "✅ Archive downloaded (${ARCHIVE_SIZE})"

# Verify archive integrity
log "🔍 Verifying archive..."
tar -tzf "${DEST_ARCHIVE}" > /dev/null 2>&1

if [ $? -ne 0 ]; then
    log "❌ Archive verification failed - corrupt file"
    rm -f "${DEST_ARCHIVE}"
    exit 1
fi

log "✅ Archive verified"

# Set proper permissions
# chown www-data:www-data "${DEST_ARCHIVE}"
# chmod 644 "${DEST_ARCHIVE}"

# Extract using Laravel command
log "📂 Extracting archive..."
cd "${APP_PATH}"

php artisan data:import "${DEST_ARCHIVE}" --no-interaction

if [ $? -ne 0 ]; then
    log "❌ Extraction failed"
    exit 1
fi

log "✅ Extraction completed"

# Run ETL
log "⚙️  Running ETL..."
LATEST_EXTRACT=$(ls -td "${APP_PATH}"/storage/app/imports/extracted/import_* 2>/dev/null | head -1)

if [ -z "${LATEST_EXTRACT}" ]; then
    log "❌ No extracted directory found"
    exit 1
fi

php artisan etl:run --import="${LATEST_EXTRACT}"

if [ $? -ne 0 ]; then
    log "❌ ETL failed"
    exit 1
fi

log "✅ ETL completed"

# Notification sending is handled inside the ETL command (RunETL) to avoid duplicate emails.
log "📧 Skipping external notifications:check (handled by etl:run)"

log "🎉 Auto-import finished successfully!"

exit 0
