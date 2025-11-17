cd /home/kirimi/sipeta

# Change ownership dulu
sudo chown -R kirimi:kirimi /home/kirimi/sipeta

# Stash perubahan lokal
git stash

# Pull
git pull

# Jika masih error, force reset
git fetch origin
git reset --hard origin/main  # atau origin/master jika branch master

# Kembalikan ownership
sudo chown -R www-data:www-data /home/kirimi/sipeta
sudo chown -R kirimi:kirimi /home/kirimi/sipeta/.git
sudo chmod -R 755 /home/kirimi/sipeta
sudo chmod -R 775 /home/kirimi/sipeta/storage
sudo chmod -R 775 /home/kirimi/sipeta/bootstrap/cache

# Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear

# Restart
sudo systemctl restart php8.3-fpm nginx
