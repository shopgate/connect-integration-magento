(function(customerNumber, apiKey, magentoUrl) {

  SGEvent.Broadcast.declare('webcheckoutSuccess')
  SGEvent.Broadcast.register('webcheckoutSuccess')
  SGEvent.registerCallback('webcheckoutSuccess', function () {
    clearCart(function () {
      SGAction.broadcastEvent({event: 'reloadCart'})
    })
  })

  $(document).bind('shopgateFramework.dataUpdate.cart', function() {
    overwriteListenerForCheckoutButton()
  })

  hideCouponField()
  overwriteListenerForCheckoutButton()

  /**
   * It overwrite the button listener for the checkout button
   */
  function overwriteListenerForCheckoutButton() {
    $('.cart-checkout-button')
        .unbind('click')
        .off('click')
        .click(function (e) {
          e.preventDefault()
          e.stopPropagation()
          createMagentoCheckout()
        })

    SGEvent.registerCallback('viewWillAppear, pageInsetsChanged', function () {
      $('.cart-checkout-button')
          .unbind('click')
          .off('click')
          .click(function (e) {
            e.preventDefault()
            e.stopPropagation()
            createMagentoCheckout()
          })
    })
  }

  /**
   * Hide coupon field
   * For shopify its only supported in the checkout process
   */
  function hideCouponField() {
    var style = document.createElement('style')
    var cssText = '.coupon-code, .voucher-wrap { display: none; }'
    style.type = 'text/css'
    if (style.styleSheet) {
      style.styleSheet.cssText = cssText
    } else {
      style.appendChild(document.createTextNode(cssText))
    }
    document.head.appendChild(style)
  }

  /**
   * Sends over all items to magento and redirect to the magento checkout afterwards
   */
  function createMagentoCheckout () {
    var productsToAdd = []

    sgData.cart.products.forEach(function (sgProduct) {
      productsToAdd.push({item_id: sgProduct.uid, sku: sgProduct.productNumber, qty: sgProduct.quantity})
    })

    var cartRequest = sendRequest(magentoUrl + "sg-cloudapi/rest/carts", {})
    cartRequest.onreadystatechange = function() {
      if (this.readyState == 4) {
        if (this.status == 200) {
          console.log('success')
          var cartId = this.response.success[0].cartId
          console.log(cartId)

          var addProductsRequest = sendRequest(magentoUrl + "sg-cloudapi/rest/carts/" + cartId + "/items", productsToAdd)
          addProductsRequest.onreadystatechange = function() {
            if (this.readyState == 4) {
              if (this.status == 200) {
                console.log('products added')
                var redirectUrl = magentoUrl + 'shopgate-checkout/quote/auth/token/' + cartId

                if (sgData.user.loggedIn && sgData.user.email) {
                  redirectUrl = redirectUrl + '/login/' + sgData.user.email
                }

                SGAction.openPage({
                  src: redirectUrl,
                  emulateBrowser: true,
                  navigationBarParams: {
                    rightButtonCallback: "SGAction.showTab({'targetTab': 'main'});"
                  }
                })
              } else {
                console.log('could not add products to cart')
              }
            }
          }
        } else {
          console.log('could not fetch cart')
        }
      }
    }
  }

  /**
   * @param url
   * @param params
   * @returns {XMLHttpRequest}
   */
  function sendRequest(url, params) {
    var timestamp = Math.floor(Date.now() / 1000)
    var authUser = customerNumber + '-' + timestamp
    var authToken = calculateAuthenticationToken(timestamp)
    var request = new XMLHttpRequest();

    request.responseType = 'json';
    request.open("POST",  url, true)
    request.setRequestHeader("Content-Type", "application/json")
    request.setRequestHeader('X-Shopgate-Auth-User', authUser)
    request.setRequestHeader('X-Shopgate-Auth-Token', authToken)
    request.setRequestHeader('Version', 1)

    request.send(JSON.stringify(params))
    return request
  }

  /**
   * @param callback
   */
  function clearCart (callback) {
    var deleteUrls = $('.delete-product, .item-delete').map(function (index, element) {return $(element).attr('href')})
    var finishedCalls = 0
    for (var i = 0; i < deleteUrls.length; i++) {
      $.shopgate.ajax({
        type: 'GET',
        url: deleteUrls[i],
        success: function () {
          finishedCalls++
          // check if all calls are finished now
          if (finishedCalls === deleteUrls.length) {
            callback()
          }
        }
      })
    }
  }

  /**
   * Build token for authentication header
   * @param timestamp
   * @returns {*}
   */
  function calculateAuthenticationToken(timestamp) {
    return sha1('SPA-' + customerNumber + '-' + timestamp + '-' + apiKey)
  }

})(window.magentoSettings.customerNumber, window.magentoSettings.apiKey, window.magentoSettings.domain)