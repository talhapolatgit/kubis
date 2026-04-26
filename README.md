1. Sunucuya Bağlan
   
ssh root@SUNUCU_IP

3. Sistemi Güncelle
   
apt update && apt upgrade -y

5. Coolify'ı Kur
   
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash

URL: http://SUNUCU_IP:8000

4. Veritabanı Kur
   
Mysql

6. ENV
   
SERVICE_URL_APP=

SERVICE_FQDN_APP=

APP_KEY=

APP_ENV=production

APP_DEBUG=true

APP_URL=

DB_CONNECTION=mysql

DB_HOST=

DB_PORT=3306

DB_DATABASE=

DB_USERNAME=

DB_PASSWORD=

8. Migrations
   
php artisan migrate:fresh --force

php artisan migrate:fresh --seed --force
