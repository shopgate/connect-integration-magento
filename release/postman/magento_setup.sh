#!/bin/sh

cd ${WEB_PATH}

# todo-sg: create a travis matrix of folder_name->repo_name
# todo-sg: enable travis caching and configure it to now download all the time
if [ ! -d "${MAGE_FOLDER1}" ];
then
	DOWNLOAD="-v"
else
	DOWNLOAD="--noDownload"
fi

n98 script:repo:run n98-setup \
-d folder=${MAGE_FOLDER1} \
-d api_key=${CFG_API_KEY} \
-d shop_number=${CFG_API_SHOP_NUMBER} \
-d customer_number=${CFG_API_CUSTOMER_NUMBER} \
-d user1_email=${USER1_EMAIL} \
-d user2_email=${USER2_EMAIL} \
-d user3_email=${USER3_EMAIL} \
-d user_pass=${USER_PASS} \
-d no_download=${DOWNLOAD} > /dev/null 2>&1

sudo chmod 777 -R ${MAGE_FOLDER1}/var

# return back
cd ${TRAVIS_BUILD_DIR}
