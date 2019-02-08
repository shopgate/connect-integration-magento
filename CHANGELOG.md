# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Changed
- Simplified tax export for shipping costs

## [3.3.2] - 2019-01-28
### Fixed
- Returning net or gross shipping amount, depending on Magento settings.

## [3.3.1] - 2019-01-10
### Added
- Customizable redirect logic to the quote controller

### Changed
- oAuth2 library to version 1.11.1

### Fixed
- App-only discounts for new customers who register in the checkout process

## [3.3.0] - 2018-12-05
### Added
- Endpoint for wishlist retrieval      (GET shopgate/v2/wishlists)
- Endpoint for wishlist creation       (POST shopgate/v2/wishlists)
- Endpoint for wishlist deletion       (DELETE shopgate/v2/wishlists/:wishlistId)
- Endpoint for wishlist item retrieval (GET /v2/wishlists/:wishlistId/items)
- Endpoint for wishlist item creation  (POST /v2/wishlists/:wishlistId/items)
- Endpoint for wishlist item deletion  (DELETE /v2/wishlists/:wishlistId/items)
- A redirect to web checkout when a customer browses with an app, and hits a web cart page
- All web cart success/error/warning messages are forwarded to the web checkout page

### Fixed
- Check for a cart to have been ordered already
- PayPal cancellation to now redirect to checkout page with a message printed on top

## [3.2.0] - 2018-10-04
### Added
- Endpoint for customer address retrieval   (GET shopgate/v2/customers/:id/addresses)
- Endpoint for customer address creation    (POST shopgate/v2/customers/:id/addresses)
- Endpoint for customer address deletion    (DELETE shopgate/v2/customers/:id/addresses/:id)
- Endpoint for customer address update      (POST shopgate/v2/customers/:id/addresses/:id)
- Endpoint for customer data update         (POST shopgate/v2/customers/:id)
- Endpoint for customer email update        (POST shopgate/v2/customers/:id/email)
- Endpoint for customer password update     (POST shopgate/v2/customers/:id/password)
- Shell script to update our REST attributes / roles
- Support of app-only coupons (except when EE Customer Segments is installed)
- Support for guest checkout

### Changed
- Initial installed admin role, it no longer uses all resources
- Endpoint for basic customer data now retrieves the user group

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

[Unreleased]: https://github.com/shopgate/cloud-integration-magento/compare/3.3.2...HEAD
[3.3.2]: https://github.com/shopgate/cloud-integration-magento/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/shopgate/cloud-integration-magento/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/shopgate/cloud-integration-magento/compare/3.2.0...3.3.0
[3.2.0]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.3...3.2.0
[3.1.3]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/shopgate/cloud-integration-magento/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/shopgate/cloud-integration-magento/compare/3.0.0...3.1.0
