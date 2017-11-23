#!/bin/sh

# Strip "v" from tag -> v1.0.0
VERSION=${TRAVIS_TAG#?}
rm -f cloud-integration-magento-v${VERSION}.tgz
rm -f release/magento1702.zip
rm -rf release/magento
rm -rf vendor

composer install -vvv --no-dev

# Modify version in magento files just in case it was forgotten
xmlstarlet edit -L -S -O -u "/_/version" -v "$VERSION" src/var/connect/shopgate_cloudapi.xml
xmlstarlet edit -L -S -u "/config/modules/Shopgate_Cloudapi/version" -v "$VERSION" src/app/code/community/Shopgate/Cloudapi/etc/config.xml

# Install magento to create package
wget --quiet -O release/magento1702.zip http://files.shopgate.com/magento/magento1702.zip > /dev/null
unzip release/magento1702.zip -d release/magento > /dev/null

# Sync module files & packager script into magento install
rsync -av src/ release/magento > /dev/null
rsync -av CHANGELOG.md release/magento/app/code/community/Shopgate/Cloudapi/CHANGELOG.md
rsync -av release/magento_package.php release/magento/magento_package.php

cd release/magento/
chmod -R 777 var
php magento_package.php
