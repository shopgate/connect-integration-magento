#!/bin/sh

rm cloud-integration-magento.tgz
rm release/magento1702.zip
rm -rf release/magento
rm -rf vendor

composer install -vvv --no-dev

wget -O release/magento1702.zip http://files.shopgate.com/magento/magento1702.zip
unzip release/magento1702.zip -d release/magento

rsync -av src/ release/magento
rsync -av CHANGELOG.md release/magento/app/code/community/Shopgate/Cloudapi/CHANGELOG.md
rsync -av release/magento_package.php release/magento/magento_package.php

cd release/magento/
chmod -R 777 var
php magento_package.php
