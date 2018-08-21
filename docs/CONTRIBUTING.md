# Contributing

Contributions to the integration are welcome and encouraged!

1. Fork the project and clone locally.
1. Create a branch for every change you plan to have applied.
1. Make *small* commits with *comprehensive* commit messages. Note the minimum supported Magento versions are CE 1.7.0.2 & EE 1.13.0.2.
1. Create a pull request into the "develop" branch for Shopgate to review.
1. Monitor and respond to any feedback that may be given prior to merging.


### Testing
Add your own postman tests if you are creating or fixing API endpoints. You can also run newman (postman) tests locally before creating a PR. Just make sure to install the _newman_ CLI.

Under ```System > Configuration > Shopgate Cloud > Configuration``` make sure to configure the *client_id* which equals to ```[customer-number]-[shop-number]``` and *client_secret*.

You will also need to enable the endpoints by going to the ```System > Web Services > REST Roles > Customer | Guest```, enable the Shopgate checkboxes and save.
```
cd ./tests/postman/newman

newman run ./collection.json -g ./globals.json --insecure --color \
--reporters json,cli --reporter-json-export verbose-report.json \
--global-var "domain=http://domain.com/mage_ee_11434/" \
--global-var "username=[EXISTING_CUSTOMER_EMAIL]" \
--global-var "password=[EXISTING_CUSTOMER_PASSWORD]" \
--global-var "mage_type=[EE OR CE]" \
--global-var "client_id=[CUSTOMER_NUMBER]-[SHOP_NUMBER]" \
--global-var "client_secret=[CLIENT_SECRET]";
```

Customer email and password should be a of a customer in group _General_.
