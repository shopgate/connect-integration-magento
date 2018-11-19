# Shopgate Connect Integration Magento

[![GitHub license](http://dmlc.github.io/img/apache2.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/shopgate/cloud-integration-magento.svg?branch=master)](https://travis-ci.org/shopgate/cloud-integration-magento)

The Shopgate Connect integration for Magento enables you to utilize the Shopgate API endpoints to connect to via the App.

## Getting Started
Download and unzip the [latest releases](https://github.com/shopgate/connect-integration-magento/releases/latest) into the root folder of your Shopgate Connect Integration Magento installation.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) file for more information.

## Contributing

See [CONTRIBUTING.md](docs/CONTRIBUTING.md) file for more information.

## About Shopgate

Shopgate is the leading mobile commerce platform. Online retailers use our software-as-a-service (SaaS) to provide their mobile customers with successful native shopping apps. Developers can enhance the Shopgate Connect platform by building extensions that customize the user experience and add new functionality to our powerful ecommerce solutions.

## License

The Shopgate Shopgate Connect Integration Magento integration is available under the Apache License, Version 2.0.

See the [LICENSE.md](LICENSE.md) file for more information.

## Magento Composer Installer

Our repository does support [Magento composer installer](https://github.com/Cotya/magento-composer-installer), however, there is a small caveat with installing sub-directory dependencies. We have placed an oAuth2 inside the `lib` folder that also needs its dependencies updated by composer. You do not need to worry about this if you are downloading the release zip package or using Magento marketplace. The process of handling it will be running these command lines at the root (where the main composer file is):

```
composer require shopgate/connect-integration-magento="~3.1.2"
cd vendor/shopgate/connect-integration-magento/src/lib/Shopgate/connect-integration-magento-oauth2/
composer update
```

If you have 'copy' as deployment strategy, then you will need to redeploy (re-copy) the files by running this command at the root:
```
composer run-script post-install-cmd -vvv -- --redeploy
```
