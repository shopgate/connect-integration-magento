#!/usr/bin/env bash

set -e
cd ${WEB_PATH}

DOWNLOAD=""
PARAM2=""
if [[ ${MAGE_TYPE} == "EE" ]]; then
	~/.local/bin/aws s3api get-object --bucket shopgate-ci --key magento/magento-shoppingsystem-files.tar.gz magento.tar.gz > /dev/null 2>&1
	tar -xzf magento.tar.gz ./
	tar -xzf ./package/ee/${MAGE_PACKAGE}.tar.gz -C ./
	mv ./${MAGE_PACKAGE} ./${MAGE_FOLDER}
	# sample data related
	mkdir ./sample
	tar -xzf ./package/ee/data/magento-sample-data-1.14.2.4.tar.gz -C ./sample
	rsync -a ./sample/magento-sample-data-1.14.2.4/ ./${MAGE_FOLDER}
	mysql -e "CREATE database ${MAGE_FOLDER};"
	mysql ${MAGE_FOLDER} < ./${MAGE_FOLDER}/magento_sample_data_for_1.14.2.4.sql
	mysql ${MAGE_FOLDER} < ${TRAVIS_BUILD_DIR}/tests/postman/sql/addCustomAttribute.sql
	DOWNLOAD="--noDownload"
	PARAM2="--forceUseDb"
	MAGE_PACKAGE="magento-mirror-1.9.3.6" # package needs to exist for n98 to accept install
fi

n98 script:repo:run n98-setup \
-d folder=${MAGE_FOLDER} \
-d package=${MAGE_PACKAGE} \
-d api_key=${CFG_API_KEY} \
-d shop_number=${CFG_API_SHOP_NUMBER} \
-d customer_number=${CFG_API_CUSTOMER_NUMBER} \
-d minimum_order_active=${CFG_MINIMUM_ORDER_ACTIVE} \
-d price_includes_tax=${CFG_PRICE_INC_TAX} \
-d user1_email=${USER1_EMAIL} \
-d user2_email=${USER2_EMAIL} \
-d user_pass=${USER_PASS} \
-d misc_param1=${DOWNLOAD} \
-d misc_param2=${PARAM2}

# Adds support to versions < CE1.9.* && < EE1.14.* when using newer sample data
mysql ${MAGE_FOLDER} -e "DELETE FROM eav_attribute WHERE backend_model='catalog/product_attribute_backend_startdate_specialprice';"
mysql ${MAGE_FOLDER} -e "DELETE FROM eav_attribute WHERE backend_model='enterprise_catalog/product_attribute_backend_urlkey';"

# Adds app-only coupon
mysql ${MAGE_FOLDER} < ${TRAVIS_BUILD_DIR}/tests/postman/sql/addMobileAppCartRule.sql
mysql ${MAGE_FOLDER} < ${TRAVIS_BUILD_DIR}/tests/postman/sql/addSoapUser.sql

# Adds a custom option to an existing product
mysql ${MAGE_FOLDER} < ${TRAVIS_BUILD_DIR}/tests/postman/sql/addCustomOption.sql

# Provides helper shell script
cp -a ${TRAVIS_BUILD_DIR}/tests/postman/scripts/forwarder.php ./${MAGE_FOLDER}/

sudo chmod 777 -R ${MAGE_FOLDER}/var

# return back
cd ${TRAVIS_BUILD_DIR}
