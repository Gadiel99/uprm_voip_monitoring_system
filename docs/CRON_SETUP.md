# Auto-Import Cron Setup

## ✅ Cron Job Installed

The cron job has been successfully configured to run every 5 minutes as the `www-data` user.

### Cron Schedule
```
*/5 * * * * /var/www/auto-import-voip-cron.sh >> /var/www/uprm_voip_monitoring_system/storage/logs/cron.log 2>&1
```

## 🔐 Complete SSH Key Setup (IMPORTANT)

The SSH key authentication needs to be finalized. Follow these steps:

### Option 1: Add Key Manually to Remote Server

1. Copy the public key:
```bash
cat /var/www/.ssh/id_rsa_voip_auto.pub
```

2. SSH to the remote server (as estudiante):
```bash
ssh estudiante@136.145.71.54
```

3. Add the key to authorized_keys:
```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "PASTE_THE_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
exit
```

4. Test from the Laravel server:
```bash
sudo -u www-data ssh -i /var/www/.ssh/id_rsa_voip_auto estudiante@136.145.71.54 "echo 'Success'; hostname"
```

### Option 2: Use sshpass (If Available)

If the remote server allows it temporarily:
```bash
sudo apt-get install sshpass
sshpass -p 'PASSWORD' ssh-copy-id -i /var/www/.ssh/id_rsa_voip_auto.pub estudiante@136.145.71.54
```

## 📊 Monitoring the Cron Job

### View Cron Logs
```bash
tail -f /var/www/uprm_voip_monitoring_system/storage/logs/cron.log
```

### View Auto-Import Logs
```bash
tail -f /var/www/uprm_voip_monitoring_system/storage/logs/auto-import.log
```

### Check Cron Status
```bash
# View current cron jobs
sudo -u www-data crontab -l

# Check if cron is running
systemctl status cron
```

### Manual Test Run
```bash
sudo -u www-data bash /var/www/auto-import-voip-cron.sh
```

## 🔧 Troubleshooting

### Cron Not Running
```bash
# Restart cron service
sudo systemctl restart cron

# Check cron logs
sudo grep CRON /var/log/syslog | tail -20
```

### SSH Authentication Fails
```bash
# Test SSH connection
sudo -u www-data ssh -vvv -i /var/www/.ssh/id_rsa_voip_auto estudiante@136.145.71.54

# Verify key permissions
ls -la /var/www/.ssh/
```

### Permission Errors
```bash
# Fix storage permissions
sudo chown -R www-data:www-data /var/www/uprm_voip_monitoring_system/storage/

# Fix SSH directory permissions
sudo chown -R www-data:www-data /var/www/.ssh/
sudo chmod 700 /var/www/.ssh/
sudo chmod 600 /var/www/.ssh/*
sudo chmod 644 /var/www/.ssh/*.pub
```

## 📋 Cron Job Details

- **Script**: `/var/www/auto-import-voip-cron.sh`
- **Schedule**: Every 5 minutes
- **User**: www-data
- **Logs**: 
  - Cron output: `/var/www/uprm_voip_monitoring_system/storage/logs/cron.log`
  - Auto-import: `/var/www/uprm_voip_monitoring_system/storage/logs/auto-import.log`
- **Lock File**: `/tmp/voip-import.lock` (prevents concurrent runs)

## 🎯 What the Cron Does

1. **Checks** for new archives on remote server (136.145.71.54)
2. **Downloads** the latest archive via SCP
3. **Verifies** archive integrity
4. **Extracts** the archive
5. **Runs ETL** to process data into MariaDB
6. **Sends notifications** if configured
7. **Logs** all activities

## 📝 Modify Cron Schedule

To change the frequency:

```bash
# Edit crontab
sudo -u www-data crontab -e

# Example schedules:
# Every 10 minutes: */10 * * * *
# Every hour: 0 * * * *
# Every 30 minutes: */30 * * * *
# Daily at 2 AM: 0 2 * * *
```

## 🗑️ Remove Cron Job

```bash
sudo -u www-data crontab -l | grep -v "auto-import-voip-cron.sh" | sudo -u www-data crontab -
```

## ✅ Verification Checklist

- [ ] SSH key authentication works passwordless
- [ ] Cron job is installed (`sudo -u www-data crontab -l`)
- [ ] Log files are writable by www-data
- [ ] Script is executable
- [ ] First manual run completed successfully
- [ ] Monitoring logs for automated runs
