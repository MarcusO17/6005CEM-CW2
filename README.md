# 6005CEM-CW2 Group 11 Application

## Overview
This repository contains the source code for the 6005CEM-CW2 Group 11 Application. The application requires specific setup steps and configuration to ensure secure operation.

## Prerequisites
- [XAMPP](https://www.apachefriends.org/download.html) - PHP development environment
- [Git](https://git-scm.com/downloads) - Version control system
- Web browser ([Chrome](https://www.google.com/chrome/), [Firefox](https://www.mozilla.org/firefox/), or [Edge](https://www.microsoft.com/edge))
- MySQL instance with SSL certificate ([MySQL Community Downloads](https://dev.mysql.com/downloads/))
- [Mailjet](https://www.mailjet.com/) account for API credentials

## Installation Steps

### 1. Base Setup
1. Download and install XAMPP
2. Navigate to XAMPP's `htdocs` directory:
   ```bash
   cd <XAMPP directory/htdocs>
   ```
3. Clone the repository:
   ```bash
   git clone git@github.com:MarcusO17/6005CEM-CW2.git
   ```

### 2. Certificate Setup
1. Create a new folder named `certs` in the project directory
2. Place your MySQL instance's SSL certificate in the `certs` folder

### 3. Environment Configuration
Create a `.htaccess` file in the project root with the following content:
```apache
SetEnv host "<DB hostname>"
SetEnv username "<DB username>"
SetEnv password "<DB password>"
SetEnv dbname "<DB name>"
SetEnv port "<DB port>"
SetEnv certpath "/.certs/<certificate name>"
SetEnv MJPublicKey "<Mailjet Public API Key>"
SetEnv MJSecretKey "<Mailjet Secret API Key>"
SetEnv SenderEmail "<Mailjet Email>"
```

### 4. HTTPS Configuration
1. Place the following SSL files in their respective directories under `xampp/apache/conf/`:

   **ssl.crt folder:**
   - edoc-ca-cert.crt
   - edoc-ca-cert.srl
   - edoc-server-cert.crt

   **ssl.csr folder:**
   - edoc-server-csr.csr

   **ssl.key folder:**
   - edoc-ca-key.key
   - edoc-server-key.key

2. Update Apache configuration in `httpd.conf`:
   - Uncomment the following lines:
     ```apache
     LoadModule ssl_module modules/mod_ssl.so
     LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
     Include conf/extra/httpd-ssl.conf
     ```

3. Update SSL configuration in `extra/httpd-ssl.conf`:
   ```apache
   SSLCertificateFile "conf/ssl.crt/edoc-server-cert.crt"
   SSLCertificateKeyFile "conf/ssl.key/edoc-server-key.key"
   ```

4. Add encryption key to the end of `httpd.conf`:
   ```apache
   # Environment variables
   SetEnv EDOC_ENCRYPTION_KEY "a+tud5SCwfFeGCssf3HDHW9d8Jzb97Bd7UxKqz5GIVM="
   ```

5. Install the self-signed CA certificate:
   - Double-click on edoc-ca-cert.crt
   - Select "Install Certificate"
   - Choose "Local Machine"
   - Select "Trusted Root Certification Authorities" store

### 5. Launch Application
1. Start XAMPP Control Panel
2. Start Apache HTTP Server
3. Access the application at `http://localhost`

## Support
For API keys or additional assistance, contact any of our team members:
- Marcus Ong: p21013106@student.newinti.edu.my
- Kee Yong Yik: p21013110@student.newinti.edu.my
- Lim Ni Feih: p22013772@student.newinti.edu.my
- Matthew Loh: p21013568@student.newinti.edu.my
- Thor Wen Zheng: p23015748@student.newinti.edu.my

## Security Note
For security reasons, API keys and sensitive credentials are not included in this repository. Please contact team members for the required credentials.
