#!/bin/sh

cd ${WEB_PATH}

# todo-sg: create a travis matrix of folder_name->repo_name
# check if cache exists
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
-d no_download=${DOWNLOAD}

# todo-sg: atm does not pass the right info, just use newman
curl -X POST \
  http://127.0.0.1/${MAGE_FOLDER1}/shopgate/v2/auth/token \
  -H 'cache-control: no-cache' \
  -H 'content-type: application/json' \
  -H "php_auth_user: ${CFG_API_CUSTOMER_NUMBER}-${CFG_API_SHOP_NUMBER}" \
  -H "php_auth_pw: ${CFG_API_KEY}" \
  -d "{
  'grant_type': 'password',
  'username': '${USER1_EMAIL}',
  'password': '${USER_PASS}'
}"

# return back
cd ${TRAVIS_BUILD_DIR}
