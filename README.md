
git clone.....

apt install npm composer vite
a2enmod rewrite (con più siti su stesso server)


composer require laravel/breeze --dev
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require barryvdh/laravel-dompdf
composer dump-autoload


sudo chown -R prog:www-data /var/www/riskmanagement

sudo find /var/www/riskmanagement -type d -exec chmod 750 {} \;
sudo find /var/www/riskmanagement -type f -exec chmod 640 {} \;


sudo find /var/www/riskmanagement/storage -type d -exec chmod 770 {} \; # rwxrwx---
sudo find /var/www/riskmanagement/storage -type f -exec chmod 660 {} \; # rw-rw----
sudo find /var/www/riskmanagement/bootstrap/cache -type d -exec chmod 770 {} \; # rwxrwx---
sudo find /var/www/riskmanagement/bootstrap/cache -type f -exec chmod 660 {} \; # rw-rw----

sudo chmod g+s /var/www/riskmanagement/storage
sudo chmod g+s /var/www/riskmanagement/bootstrap/cache
sudo find /var/www/riskmanagement/storage -type d -exec chmod g+s {} \;
sudo find /var/www/riskmanagement/bootstrap/cache -type d -exec chmod g+s {} \;

npm install

npm run build


7 0 * * * /home/prog/deploy.sh >> /var/www/riskmanagement/storage/logs/cron_deploy.log 2>&1


#!/bin/bash

# Vai alla directory del progetto
cd /var/www/riskmanagement || exit

# Esegui il pull di Git
git pull

# Ora, sistema i permessi. Questi comandi richiedono sudo.
# Cambia il proprietario di tutti i file a prog:www-data
chown -R prog:www-data .

# Imposta i permessi corretti per le cartelle di Laravel che richiedono scrittura
# g+w significa "aggiungi il permesso di scrittura per il gruppo"
chmod -R g+w storage bootstrap/cache

# Opzionale ma consigliato: pulisci la cache di Laravel
# Esegui questi comandi come utente www-data per evitare problemi di permessi sulla cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

echo "Deploy completato e permessi sistemati."