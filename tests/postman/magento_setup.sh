#!/bin/sh

cd ${WEB_PATH}

# todo-sg: enable travis caching and configure it to now download all the time
if [ ! -d "${MAGE_FOLDER}" ];
then
	DOWNLOAD="-v"
else
	DOWNLOAD="--noDownload"
fi

n98 script:repo:run n98-setup \
-d folder=${MAGE_FOLDER} \
-d package=${MAGE_PACKAGE} \
-d api_key=${CFG_API_KEY} \
-d shop_number=${CFG_API_SHOP_NUMBER} \
-d customer_number=${CFG_API_CUSTOMER_NUMBER} \
-d user1_email=${USER1_EMAIL} \
-d user2_email=${USER2_EMAIL} \
-d user3_email=${USER3_EMAIL} \
-d user_pass=${USER_PASS} \
-d no_download=${DOWNLOAD} > /dev/null 2>&1

# Adds support to versions < 1.9. Consequences are unknown.
mysql ${MAGE_FOLDER} -e "DELETE FROM eav_attribute WHERE backend_model='catalog/product_attribute_backend_startdate_specialprice';"

sudo chmod 777 -R ${MAGE_FOLDER}/var

# return back
cd ${TRAVIS_BUILD_DIR}
