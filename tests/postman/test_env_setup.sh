#!/usr/bin/env bash

set -e

# Install Apache & Enable php-fpm
sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "always_populate_raw_post_data=-1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
sudo chown -R travis:travis /var/lib/apache2/fastcgi
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure apache virtual hosts
sudo cp -f ./tests/postman/travis-ci-apache /etc/apache2/sites-available/default
sudo chown -R travis:travis ${WEB_PATH}
sudo chmod 750 ${WEB_PATH}
sudo chown -R travis:travis /var/lock/apache2/
sudo service apache2 restart

# n98 related
wget --quiet https://files.magerun.net/n98-magerun.phar
chmod +x ./n98-magerun.phar
sudo mv ./n98-magerun.phar /usr/local/bin/n98
sudo mkdir -p /usr/local/share/n98-magerun/scripts
sudo cp -a ./tests/postman/n98-scripts/* /usr/local/share/n98-magerun/scripts/
sudo chown -R travis:travis /usr/local/share/n98-magerun
sudo chmod +x /usr/local/share/n98-magerun/scripts/*.magerun
sudo cp -a ./tests/postman/.n98-magerun.yaml ~/
sudo chmod +x ~/.n98-magerun.yaml

if [[ ${MAGE_LOCALE} == "en_US" ]];
then
	sudo sed -e "s?EUR?USD?g" --in-place ~/.n98-magerun.yaml
	sudo sed -e "s?de_DE?${MAGE_LOCALE}?g" --in-place ~/.n98-magerun.yaml
	echo 'subbed default config values'
fi

if [[ ${MAGE_TYPE} == "EE" ]]; then
	pip install --user awscli
fi

# newman related
npm install newman --global
