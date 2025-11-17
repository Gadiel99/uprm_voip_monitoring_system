# SSL/HTTPS Configuration

## Overview
The UPRM VoIP Monitoring System is now configured to run over HTTPS with automatic HTTP to HTTPS redirection.

**URL:** https://voipmonitor.uprm.edu

## Configuration Details

### SSL Certificate
- **Type:** Self-signed certificate (365 days validity)
- **Location:** 
  - Certificate: `/etc/apache2/ssl/voip-selfsigned.crt`
  - Private Key: `/etc/apache2/ssl/voip-selfsigned.key`
- **Domain:** voipmonitor.uprm.edu

### Apache Configuration
- **Config File:** `/etc/apache2/sites-available/voip.conf`
- **HTTP Port:** 80 (redirects to HTTPS)
- **HTTPS Port:** 443

### Security Headers Enabled
- Strict-Transport-Security (HSTS)
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection

### SSL Protocol Settings
- **Enabled:** TLS 1.2, TLS 1.3
- **Disabled:** SSLv3, TLS 1.0, TLS 1.1
- **Cipher Suite:** HIGH:!aNULL:!MD5:!3DES

## Laravel Configuration

### Environment Settings (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://voipmonitor.uprm.edu
```

### Service Provider
`app/Providers/AppServiceProvider.php` is configured to force HTTPS in production environment.

## Important Notes

### Self-Signed Certificate
The current SSL certificate is **self-signed**, which means:
- ✅ Traffic is encrypted
- ❌ Browsers will show a security warning
- ❌ Users must manually accept the certificate

### Upgrading to Trusted Certificate

For production use, you should obtain a trusted SSL certificate. Options:

#### Option 1: Let's Encrypt (Free)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d voipmonitor.uprm.edu
```

#### Option 2: UPRM IT Department
Contact UPRM IT to obtain an official university SSL certificate for the uprm.edu domain.

## Maintenance Commands

### Restart Apache
```bash
sudo systemctl restart apache2
```

### Check Apache Status
```bash
sudo systemctl status apache2
```

### Test Configuration
```bash
sudo apache2ctl configtest
```

### View SSL Certificate Info
```bash
openssl x509 -in /etc/apache2/ssl/voip-selfsigned.crt -text -noout
```

### Check SSL Port
```bash
sudo netstat -tlnp | grep :443
```

### Renew Self-Signed Certificate
```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/apache2/ssl/voip-selfsigned.key \
  -out /etc/apache2/ssl/voip-selfsigned.crt \
  -subj "/C=US/ST=PuertoRico/L=Mayaguez/O=UPRM/CN=voipmonitor.uprm.edu"

sudo systemctl restart apache2
```

## Troubleshooting

### Port 443 Not Listening
```bash
# Check if SSL module is enabled
sudo a2enmod ssl
sudo systemctl restart apache2
```

### Certificate Errors
```bash
# Verify certificate files exist and have correct permissions
ls -lh /etc/apache2/ssl/
```

### Mixed Content Warnings
If you see mixed content warnings in browser console:
1. Check all asset URLs use HTTPS
2. Clear Laravel cache: `php artisan cache:clear`
3. Check APP_URL in .env is set to HTTPS

## Security Best Practices

1. **Keep SSL Certificate Updated**
   - Self-signed: Renew annually
   - Let's Encrypt: Auto-renews every 90 days

2. **Monitor SSL Labs Rating**
   - Test: https://www.ssllabs.com/ssltest/
   - Target: A+ rating

3. **Regular Updates**
   ```bash
   sudo apt update && sudo apt upgrade
   sudo systemctl restart apache2
   ```

4. **Review Logs**
   - SSL Errors: `/var/log/apache2/voip-ssl-error.log`
   - SSL Access: `/var/log/apache2/voip-ssl-access.log`

## References

- Apache SSL/TLS Documentation: https://httpd.apache.org/docs/2.4/ssl/
- Let's Encrypt: https://letsencrypt.org/
- Mozilla SSL Configuration Generator: https://ssl-config.mozilla.org/

---

**Date Configured:** November 17, 2025  
**Configured By:** UPRM VoIP Monitoring System Team
