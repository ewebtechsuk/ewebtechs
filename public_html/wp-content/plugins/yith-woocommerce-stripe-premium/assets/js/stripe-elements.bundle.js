/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/typeof.js
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js
function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/iterableToArrayLimit.js
function _iterableToArrayLimit(r, l) {
  var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
  if (null != t) {
    var e,
      n,
      i,
      u,
      a = [],
      f = !0,
      o = !1;
    try {
      if (i = (t = t.call(r)).next, 0 === l) {
        if (Object(t) !== t) return;
        f = !1;
      } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
    } catch (r) {
      o = !0, n = r;
    } finally {
      try {
        if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return;
      } finally {
        if (o) throw n;
      }
    }
    return a;
  }
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js
function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;
  for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];
  return arr2;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/nonIterableRest.js
function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/slicedToArray.js




function _slicedToArray(arr, i) {
  return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest();
}
;// CONCATENATED MODULE: ./assets/js/stripe-elements.js


/* global Stripe, yith_stripe_info, woocommerce_params */

(function ($) {
  var $body = $('body'),
    style = {
      base: {
        // Add your base input styles here. For example:
        backgroundColor: yith_stripe_info.background_color,
        fontSize: yith_stripe_info.font_size,
        color: yith_stripe_info.color,
        iconColor: yith_stripe_info.icon_color,
        fontFamily: yith_stripe_info.font_family,
        '::placeholder': {
          color: yith_stripe_info.placeholder_color
        }
      },
      invalid: {
        iconColor: yith_stripe_info.invalid_icon_color,
        color: yith_stripe_info.invalid_color
      },
      complete: {
        color: yith_stripe_info.complete_color
      }
    },
    stripe = Stripe(yith_stripe_info.public_key),
    elements = stripe.elements(),
    card,
    cardExpiry,
    cardCvc,
    // init Stripe Elements fields
    init_elements = function init_elements() {
      // Add an instance of the card Element into the `card-element` <div>.
      if ($(yith_stripe_info.elements_container_id).length) {
        if (typeof card != 'undefined') {
          card.destroy();
        }
        card = elements.create('card', {
          style: style,
          hidePostalCode: !yith_stripe_info.show_zip
        });
        card.mount(yith_stripe_info.elements_container_id);
      } else {
        var number = $('#yith-stripe-card-number'),
          expiry = $('#yith-stripe-card-expiry'),
          cvc = $('#yith-stripe-card-cvc'),
          onComplete = function onComplete(ev) {
            if (!ev.complete) {
              return;
            }
            var next = 'cardNumber' === ev.elementType ? cardExpiry : cardCvc;
            next.focus();
          };
        if (number.length) {
          var placeholder = number.attr('placeholder');
          if (typeof card != 'undefined') {
            card.destroy();
          }
          card = elements.create('cardNumber', {
            style: style,
            placeholder: placeholder,
            showIcon: true
          });
          number.replaceWith('<div id="yith-stripe-card-number" class="yith-stripe-elements-field">');
          card.mount('#yith-stripe-card-number');
          card.on('change', onComplete);
        }
        if (expiry.length) {
          var placeholder = expiry.attr('placeholder');
          if (typeof cardExpiry != 'undefined') {
            cardExpiry.destroy();
          }
          cardExpiry = elements.create('cardExpiry', {
            style: style,
            placeholder: placeholder
          });
          expiry.replaceWith('<div id="yith-stripe-card-expiry" class="yith-stripe-elements-field">');
          cardExpiry.mount('#yith-stripe-card-expiry');
          cardExpiry.on('change', onComplete);
        }
        if (cvc.length) {
          var placeholder = cvc.attr('placeholder');
          if (typeof cardCvc != 'undefined') {
            cardCvc.destroy();
          }
          cardCvc = elements.create('cardCvc', {
            style: style,
            placeholder: placeholder
          });
          cvc.replaceWith('<div id="yith-stripe-card-cvc" class="yith-stripe-elements-field">');
          cardCvc.mount('#yith-stripe-card-cvc');
        }
      }
    },
    // init error handling
    handle_elements_error = function handle_elements_error(response, args) {
      var defaults = {
          form: $('#wc-yith-stripe-cc-form, #yith-stripe-cc-form').closest('.payment_method_yith-stripe'),
          unblock: $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table, #add_payment_method, #order_review')
        },
        args = $.extend(defaults, args);
      args.unblock.removeClass('processing').unblock();
      $('.woocommerce-error', args.form).remove();
      if (response.error) {
        // Remove token, if any
        $('.stripe-intent', args.form).remove();

        // Show the errors on the form
        if (response.error.message) {
          var error = $('<ul>', {
            "class": 'woocommerce-error'
          }).append($('<li>', {
            text: response.error.message
          }));
          args.form.prepend(error);
          $('html, body').animate({
            scrollTop: error.offset().top
          });
        }
      }
    },
    // init form submit
    handle_form_submit = function handle_form_submit(event) {
      if ($('input#payment_method_yith-stripe').is(':checked') && 0 === $('input.stripe-intent').length) {
        var ccForm = $('#wc-yith-stripe-cc-form, #yith-stripe-cc-form'),
          $form = $('form.checkout, form#order_review, form#add_payment_method'),
          toBlockForms = $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table, #add_payment_method'),
          nameInput = $('#yith-stripe-card-name'),
          billing_email = $('#billing_email'),
          billing_country_input = $('#billing_country'),
          billing_city_input = $('#billing_city:visible'),
          billing_address_1_input = $('#billing_address_1:visible'),
          billing_address_2_input = $('#billing_address_2:visible'),
          billing_state_input = $('select#billing_state:visible, input#billing_state:visible'),
          billing_postal_code_input = $('#billing_postcode:visible'),
          billing_postal_code = billing_postal_code_input.val(),
          billing_phone_input = $('#billing_phone:visible'),
          cardData = filter_empty_attributes({
            billing_details: {
              name: nameInput.length ? nameInput.val() : $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
              address: {
                line1: billing_address_1_input.length ? billing_address_1_input.val() : '',
                line2: billing_address_2_input.length ? billing_address_2_input.val() : '',
                city: billing_city_input.length ? billing_city_input.val() : '',
                state: billing_state_input.length ? billing_state_input.val() : '',
                country: billing_country_input.length ? billing_country_input.val() : '',
                postal_code: billing_postal_code_input.length ? billing_postal_code : ''
              },
              email: billing_email.length ? billing_email.val() : '',
              phone: billing_phone_input.length ? billing_phone_input.val() : ''
            }
          }),
          selectedCard = $('input[name="wc-yith-stripe-payment-token"]:checked');

        // update PaymentIntent
        selectedCard = selectedCard.length && 'new' !== selectedCard.val() ? selectedCard.val() : false;
        toBlockForms.block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        });
        if (!selectedCard) {
          stripe.createPaymentMethod('card', card, cardData).then(function (result) {
            if (result.error) {
              handle_elements_error(result);
            } else {
              ccForm.append('<input type="hidden" class="stripe-intent" name="stripe_intent" value=""/>');
              ccForm.append('<input type="hidden" class="stripe-payment-method" name="stripe_payment_method" value="' + result.paymentMethod.id + '"/>');
              toBlockForms.unblock();
              $form.submit();
            }
          });
        } else {
          ccForm.append('<input type="hidden" class="stripe-intent" name="stripe_intent" value=""/>');
          toBlockForms.unblock();
          $form.submit();
        }
        return false;
      }
      return event;
    },
    // init add payment method
    handle_method_add = function handle_method_add(event) {
      if ($('input#payment_method_yith-stripe').is(':checked') && 0 === $('input.stripe-intent').length) {
        var ccForm = $('#wc-yith-stripe-cc-form, #yith-stripe-cc-form'),
          $form = $('form#add_payment_method'),
          toBlockForms = $('#add_payment_method'),
          nameInput = $('#yith-stripe-card-name'),
          billing_email = $('#billing_email'),
          billing_country_input = $('#billing_country'),
          billing_city_input = $('#billing_city:visible'),
          billing_address_1_input = $('#billing_address_1:visible'),
          billing_address_2_input = $('#billing_address_2:visible'),
          billing_state_input = $('select#billing_state:visible, input#billing_state:visible'),
          cardData = filter_empty_attributes({
            payment_method_data: {
              billing_details: {
                name: nameInput.length ? nameInput.val() : $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
                address: {
                  line1: billing_address_1_input.length ? billing_address_1_input.val() : '',
                  line2: billing_address_2_input.length ? billing_address_2_input.val() : '',
                  city: billing_city_input.length ? billing_city_input.val() : '',
                  state: billing_state_input.length ? billing_state_input.val() : '',
                  country: billing_country_input.length ? billing_country_input.val() : ''
                },
                email: billing_email.length ? billing_email.val() : ''
              }
            },
            save_payment_method: true
          }),
          selectedCard = $('input[name="wc-yith-stripe-payment-token"]:checked'),
          intent_id,
          intent_secret;

        // update PaymentIntent
        selectedCard = selectedCard.length && 'new' !== selectedCard.val() ? selectedCard.val() : false;
        toBlockForms.block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        });
        update_intent(selectedCard).then(function (data) {
          if (typeof data.res != 'undefined') {
            if (!data.res && typeof data.error != 'undefined') {
              handle_elements_error(data);
              return false;
            }
          }
          if (typeof data.refresh != 'undefined' && data.refresh) {
            window.location.reload();
            return false;
          }
          intent_id = data.intent_id;
          intent_secret = data.intent_secret;
          if (!selectedCard) {
            stripe.handleCardSetup(intent_secret, card, cardData).then(function (result) {
              if (result.error) {
                handle_elements_error(result);
              } else {
                intent_id = typeof result.paymentIntent != 'undefined' ? result.paymentIntent.id : result.setupIntent.id;
                ccForm.append('<input type="hidden" class="stripe-intent" name="stripe_intent" value="' + intent_id + '"/>');
                toBlockForms.unblock();
                $form.submit();
              }
            });
          } else {
            ccForm.append('<input type="hidden" class="stripe-intent" name="stripe_intent" value="' + intent_id + '"/>');
            toBlockForms.unblock();
            $form.submit();
          }
        });
        return false;
      }
      return event;
    },
    // handle hash change
    on_hash_change = function on_hash_change() {
      var partials = window.location.hash.match(/^#?yith-confirm-pi-([^\/]+)\/(.+)$/);
      if (!partials || 3 > partials.length) {
        return;
      }
      var _partials = _slicedToArray(partials, 3),
        intentClientSecret = _partials[1],
        redirectURL = _partials[2];
      // Cleanup the URL
      window.location.hash = '';
      open_intent_modal(intentClientSecret, redirectURL);
    },
    // manual confirmation for payment intent
    open_intent_modal = function open_intent_modal(secret, redirectURL) {
      var $form = $('form.checkout, form#order_review'),
        handler = secret.indexOf('seti') < 0 ? 'handleCardAction' : 'handleCardSetup';
      stripe[handler](secret).then(function (result) {
        if (result.error) {
          handle_elements_error(result, {
            unblock: $form
          });
        } else {
          window.location = decodeURIComponent(redirectURL);
        }
      })["catch"](function (error) {
        error.log(error);
      });
    },
    // remove token from DOM
    remove_token = function remove_token() {
      $('.stripe-intent').remove();
      $('.stripe-payment-method').remove();
    },
    // handle card selection
    handle_card_selection = function handle_card_selection() {
      var $cards = $('#payment').find('div.cards');
      if ($cards.length) {
        $cards.siblings('fieldset#wc-yith-stripe-cc-form, fieldset#yith-stripe-cc-form').hide();
        $('body').on('updated_checkout', function () {
          $('#payment').find('div.cards').siblings('fieldset#wc-yith-stripe-cc-form, fieldset#yith-stripe-cc-form').hide();
        });
        $('form.checkout, form#order_review').on('change', '#payment input[name="wc-yith-stripe-payment-token"]', function () {
          var input = $(this),
            $cards = $('#payment').find('div.cards');

          // change selected
          $cards.find('div.card').removeClass('selected');
          $cards.find('input[name="wc-yith-stripe-payment-token"]:checked').closest('div.card').addClass('selected');
          if (input.val() === 'new') {
            $cards.siblings('fieldset#wc-yith-stripe-cc-form, fieldset#yith-stripe-cc-form').show();
          } else {
            $cards.siblings('fieldset#wc-yith-stripe-cc-form, fieldset#yith-stripe-cc-form').hide();
          }
        });
      }
    },
    // update paymentIntent
    update_intent = function update_intent(token) {
      var data = [];
      if (yith_stripe_info.is_checkout && !yith_stripe_info.order) {
        data = $('form.checkout').serializeArray();
      }
      return $.ajax({
        data: pushRecursive(data, {
          action: 'yith_stripe_refresh_intent',
          yith_stripe_refresh_intent: yith_stripe_info.refresh_intent,
          selected_token: token,
          is_checkout: yith_stripe_info.is_checkout,
          order: yith_stripe_info.order
        }),
        method: 'POST',
        url: yith_stripe_info.ajaxurl
      });
    },
    // confirm card
    confirm_card = function confirm_card(ev) {
      ev.preventDefault();
      var t = $(this),
        h = t.attr('href'),
        r = /.*\/([0-9]*)\//ig,
        selectedCard = r.exec(h)[1],
        intent_secret,
        intent_id;
      $('table.account-payment-methods-table').block({
        message: null,
        overlayCSS: {
          background: '#fff',
          opacity: 0.6
        }
      });
      update_intent(selectedCard).then(function (data) {
        if (typeof data.res != 'undefined') {
          if (!data.res && typeof data.error != 'undefined') {
            handle_elements_error(data);
            return false;
          }
        }
        if (typeof data.refresh != 'undefined' && data.refresh) {
          window.location.reload();
          return false;
        }
        intent_secret = data.intent_secret;
        stripe.handleCardSetup(intent_secret).then(function (result) {
          if (result.error) {
            handle_elements_error(result, {
              form: $('.woocommerce-notices-wrapper'),
              unblock: $('.account-payment-methods-table')
            });
          } else {
            intent_id = result.setupIntent.id;
            window.location = h + '&stripe_intent=' + intent_id;
          }
        });
      });
    },
    // confirm deletion
    confirm_deletion = function confirm_deletion(ev) {
      ev.preventDefault();
      var $opener = $(this),
        $modal = $('<div/>', {
          "class": 'yith-wcstripe-modal confirm-modal'
        }),
        $title = $('<h3/>', {
          text: yith_stripe_info.labels.confirm_modal.title,
          "class": 'modal-header'
        }),
        $content = $('<p/>', {
          text: yith_stripe_info.labels.confirm_modal.body,
          "class": 'modal-body'
        }),
        $footer = $('<div/>', {
          "class": 'modal-footer'
        }),
        $cancelButton = $('<a/>', {
          text: yith_stripe_info.labels.confirm_modal.cancel_button,
          "class": 'cancel ghost button btn'
        }),
        $confirmButton = $('<a/>', {
          text: yith_stripe_info.labels.confirm_modal.confirm_button,
          "class": 'confirm delete-button button btn'
        });
      $closeButton = $('<a/>', {
        "class": 'close-button',
        html: '&times;'
      });
      $footer.append($cancelButton).append($confirmButton);
      $modal.append($closeButton).append($title).append($content).append($footer);
      $body.addClass('yith-wcstripe-modal-backdrop');
      $cancelButton.add($closeButton).on('click', function () {
        $modal.remove();
        $body.removeClass('yith-wcstripe-modal-backdrop');
      });
      $confirmButton.on('click', function () {
        var url = $opener.attr('href');
        $modal.block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        });
        window.location.href = url;
      });
      $body.append($modal);
    },
    // init cvc popup
    cvv_lightbox = function cvv_lightbox() {
      if (typeof $.fn.prettyPhoto == 'undefined') {
        return;
      }
      $('.woocommerce #payment ul.payment_methods li, form#add_payment_method').find('a.cvv2-help').prettyPhoto({
        hook: 'data-rel',
        social_tools: false,
        theme: 'pp_woocommerce',
        horizontal_padding: 20,
        opacity: 0.8,
        deeplinking: false
      });
    },
    // utility: removes empty attributes from objects
    filter_empty_attributes = function filter_empty_attributes(object) {
      var result = {},
        key,
        value;
      if (_typeof(object) != 'object') {
        return object;
      }
      for (key in object) {
        if (!object.hasOwnProperty(key)) {
          continue;
        }
        value = _typeof(object[key]) == 'object' ? filter_empty_attributes(object[key]) : object[key];
        if (value && !$.isEmptyObject(value)) {
          result[key] = value;
        }
      }
      return result;
    },
    // utility: add data to array that comes from $.serializeArray()
    pushRecursive = function pushRecursive(arr, data) {
      var key;
      for (key in data) {
        if (!data.hasOwnProperty(key)) {
          continue;
        }
        arr.push({
          name: key,
          value: data[key]
        });
      }
      return arr;
    },
    // init checkout
    initCheckout = function initCheckout() {
      $('table.account-payment-methods-table').on('click', '.confirm', confirm_card);
      $('table.account-payment-methods-table').on('click', '.delete', confirm_deletion);
      $('body').hasClass('yith-wcstripe-custom-payment-method-table') && $('table.account-payment-methods-table').find('.payment-method-actions .button').on('mouseenter', function () {
        var t = $(this),
          tip = $('<span/>', {
            text: t.text(),
            "class": 'yith-wcstripe-tooltip'
          });
        t.append(tip);
      }).on('mouseleave', function () {
        var t = $(this);
        t.find('.yith-wcstripe-tooltip').remove();
      });

      // init elements handling, if container was found
      if ($(yith_stripe_info.elements_container_id).length || $('#yith-stripe-card-number').length) {
        init_elements();
        cvv_lightbox();
        handle_card_selection();
        on_hash_change();

        // handles errors messages
        card.addEventListener('change', handle_elements_error);

        // handles hash change
        window.addEventListener('hashchange', on_hash_change);

        // init elements and updates it when checkout is updated
        $body.on('updated_checkout', init_elements);

        // init cc popup when checkout form is updated
        $body.on('updated_checkout', cvv_lightbox);

        // handle checkout error
        $body.on('checkout_error', remove_token);

        // handle form submit: checkout form
        $('form.checkout').on('checkout_place_order_yith-stripe', handle_form_submit);

        // handle form submit: pay form
        $('form#order_review').on('submit', handle_form_submit);

        // handle form submit: add card form
        $('form#add_payment_method').on('submit', handle_method_add);

        // handle change of payment method
        $('form.checkout, form#order_review, form#add_payment_method').on('change', '#wc-yith-stripe-cc-form input, #yith-stripe-cc-form input', remove_token);
      }
    };
  $(document).on('ywsbs-auto-renew-opened', initCheckout);

  // let's start the game
  initCheckout();
})(jQuery);
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=stripe-elements.bundle.js.map