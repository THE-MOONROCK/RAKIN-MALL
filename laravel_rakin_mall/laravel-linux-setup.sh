#!/bin/bash
# Laravel Linux Setup
# http://ask.osify.com

echo "             https://github.com/osify/laravel-linux-setup          "
echo ""
echo "                            ask.osify.com                          "
echo ""
echo "..:::Laravel Linux Setup File/Folder Permission for Production:::.."
echo "..................................................................."

echo "Make sure, you have created the missing folders in storage, storage/framework..."

# mkdir bootstrap/cache storage storage/framework && cd storage/framework && mkdir sessions views cache

echo "Webserver as owner, please wait... "
echo "In my case, my webserver user/group is www-data, normally they are www-data"

echo ""
echo ""

chown -R www-data:www-data .

find * -type f -exec chmod 644 {} \;
find * -type d -exec chmod 755 {} \;

#echo "Your user as ower"
#chown -R root:www-data .

#find * -type f -exec chmod 664 {} \;
#find * -type d -exec chmod 775 {} \;

echo "Then give the webserver the rights to read and write to storage and cache"
echo ""
echo ""

chown -R www-data:www-data vendor storage storage/framework storage/framework/cache storage/framework/sessions storage/framework/views bootstrap bootstrap/cache public/uploads

chgrp -R www-data vendor storage storage/framework storage/framework/cache storage/framework/sessions storage/framework/views bootstrap bootstrap/cache public/uploads
chmod -R ug+rwx vendor storage storage/framework storage/framework/cache storage/framework/sessions storage/framework/views bootstrap bootstrap/cache public/uploads

# give the newly created files/directories the group of the parent directory
# e.g. the laravel group
find ./bootstrap/cache -type d -exec chmod g+s {} \;
find ./storage -type d -exec chmod g+s {} \;

# let newly created files/directories inherit the default owner
# permissions up to maximum permission of rwx e.g. new files get 664,
# folders get 775
setfacl -R -d -m g::rwx storage/logs
setfacl -R -d -m g::rwx bootstrap/cache

echo "Add ftp user to the www group"
echo ""
echo ""

# www-data to group root
usermod -a -G www-data root


echo "Do some laravel stuff with php artisan"

php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo ""
echo ""
echo ""
echo ""
echo "done!"
