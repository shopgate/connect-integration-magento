#!/bin/sh

# Apache related
cd ${WEB_PATH}
mv ${TRAVIS_BUILD_DIR}/release/postman/info.php ${WEB_PATH}

curl localhost/info.php

mysql -e 'CREATE DATABASE magento1;'

# N98 related
chmod +x ${TRAVIS_BUILD_DIR}/release/postman/n98-setup.magerun
n98 script < ${TRAVIS_BUILD_DIR}/release/postman/n98-setup.magerun
