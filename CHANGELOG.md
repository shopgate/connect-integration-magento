# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Addresses to the response of the customers/me endpoint

## [3.1.3] - 2018-05-30
### Fixed
- Empty cart issue after redirect to checkout 

## [3.1.2] - 2018-05-23
### Added
- Support for Magento composer installer
- Validation for Minimum order amount
### Fixed
- Checkout url when using store code in magento url
- Issues with products endpoint showing tax prices for Magento below CE 1.9 & EE 1.14
- Shipping methods when updating cart items

## [3.1.1] - 2018-03-28
### Added
- Support of new Shopgate Pipeline naming conventions

## [3.1.0] - 2018-03-15
### Added
- shopgate_order_sources table to flag Shopgate orders
- UTM parameters for Google Analytics in web checkout
- Endpoint for retrieving basic customer information
- Added logic for custom layout handles and removed unnecessary elements from app webview
- Endpoint stub (blank) for category retrieval; logic to be added later
### Fixed
- oAuth refresh token handling

## 3.0.0 - 2017-11-23
### Added
- Endpoint for creation oAuth access tokens
- Endpoint for cart creation
- Endpoint for cart content retrieval
- Endpoint for adding/removing/updating products in cart
- Endpoint for adding/removing/updating coupons in cart
- Endpoint for merging guest cart with customer cart
- Endpoint for retrieving a temporary checkout URL
- Endpoint for retrieving of product details

[Unreleased]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.3...HEAD
[3.1.3]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/shopgate/cloud-integration-magento/compare/3.0.0...3.1.0
