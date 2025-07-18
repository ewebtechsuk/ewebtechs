/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
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

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ StripeElementBlock)
});

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
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/typeof.js
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js

function _toPrimitive(input, hint) {
  if (_typeof(input) !== "object" || input === null) return input;
  var prim = input[Symbol.toPrimitive];
  if (prim !== undefined) {
    var res = prim.call(input, hint || "default");
    if (_typeof(res) !== "object") return res;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (hint === "string" ? String : Number)(input);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js


function _toPropertyKey(arg) {
  var key = _toPrimitive(arg, "string");
  return _typeof(key) === "symbol" ? key : String(key);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/createClass.js

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
  }
}
function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  Object.defineProperty(Constructor, "prototype", {
    writable: false
  });
  return Constructor;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/defineProperty.js

function _defineProperty(obj, key, value) {
  key = _toPropertyKey(key);
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }
  return obj;
}
;// CONCATENATED MODULE: external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// CONCATENATED MODULE: ./assets/js/stripe-block.js


/* globals wc jQuery Stripe yith_stripe_info */




function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = stripe_block_unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function stripe_block_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return stripe_block_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return stripe_block_arrayLikeToArray(o, minLen); }
function stripe_block_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }


/**
 * Class that init and manages all interactions with Stripe Element
 */
var StripeElementBlock = /*#__PURE__*/function () {
  /**
   * Constructor method
   */
  function StripeElementBlock() {
    _classCallCheck(this, StripeElementBlock);
    /**
     * Components created with Elements library
     */
    _defineProperty(this, "elementComponents", {});
    /**
     * Reference to nodes enhanced with Elements
     */
    _defineProperty(this, "elementNodes", {});
    /**
     * Billing details
     */
    _defineProperty(this, "billing", void 0);
    this.onPaymentProcessing = this.onPaymentProcessing.bind(this);
    this.afterElementsSubmit = this.afterElementsSubmit.bind(this);
    this.openIntentModal = this.openIntentModal.bind(this);
    this.onHashChange = this.onHashChange.bind(this);
    this.init();
  }

  /* === INITIALIZATION METHOD === */

  /**
   * Init current object
   */
  _createClass(StripeElementBlock, [{
    key: "init",
    value: function init() {
      this.registerMethod();
      this.initStripe();
      this.initHashChange();
    }

    /**
     * Init Stripe object
     */
  }, {
    key: "initStripe",
    value: function initStripe() {
      var _yith_stripe_info = yith_stripe_info,
        publicKey = _yith_stripe_info.public_key;
      if (!publicKey) {
        return;
      }
      this.stripe = Stripe(publicKey);
    }

    /**
     * Init hash change
     */
  }, {
    key: "initHashChange",
    value: function initHashChange() {
      window.addEventListener('hashchange', this.onHashChange);
      this.onHashChange();
    }

    /* === BLOCK HANDLING === */

    /**
     * Returns object describing payment method
     */
  }, {
    key: "getMethod",
    value: function getMethod() {
      var _yith_stripe_info2 = yith_stripe_info,
        slug = _yith_stripe_info2.slug,
        title = _yith_stripe_info2.title,
        Label = this.getLabel(),
        Content = this.getContent();
      return {
        name: slug,
        label: /*#__PURE__*/React.createElement(Label, null),
        content: /*#__PURE__*/React.createElement(Content, null),
        edit: /*#__PURE__*/React.createElement(Content, null),
        canMakePayment: function canMakePayment() {
          return true;
        },
        ariaLabel: title
      };
    }

    /**
     * Returns component for the Payment Method label
     */
  }, {
    key: "getLabel",
    value: function getLabel() {
      return function (props) {
        var PaymentMethodLabel = props.components.PaymentMethodLabel,
          _yith_stripe_info3 = yith_stripe_info,
          title = _yith_stripe_info3.title;
        return /*#__PURE__*/React.createElement(PaymentMethodLabel, {
          text: title
        });
      };
    }

    /**
     * Returns component for the Payment Method content
     */
  }, {
    key: "getContent",
    value: function getContent() {
      var _this = this;
      var _yith_stripe_info4 = yith_stripe_info,
        slug = _yith_stripe_info4.slug,
        description = _yith_stripe_info4.description;
      return function (props) {
        var billing = props.billing,
          activePaymentMethod = props.activePaymentMethod,
          eventRegistration = props.eventRegistration,
          onPaymentProcessing = eventRegistration.onPaymentProcessing;
        if (slug !== activePaymentMethod) {
          return;
        }

        // registers current status of the billing details.
        _this.billing = billing;

        // init elements when needed.
        (0,external_wp_element_namespaceObject.useEffect)(function () {
          _this.initElements();
        });

        // register payment processing observer.
        (0,external_wp_element_namespaceObject.useEffect)(function () {
          return onPaymentProcessing(_this.onPaymentProcessing);
        }, [onPaymentProcessing]);
        return /*#__PURE__*/React.createElement(React.Fragment, null, description ? /*#__PURE__*/React.createElement("p", {
          dangerouslySetInnerHTML: {
            __html: description
          }
        }) : null, _this.getForm());
      };
    }

    /**
     * Get components of the payment form
     */
  }, {
    key: "getForm",
    value: function getForm() {
      var _this2 = this;
      var _yith_stripe_info5 = yith_stripe_info,
        mode = _yith_stripe_info5.mode,
        elements = this.getElements();
      if ('hosted' === mode) {
        return;
      }
      return /*#__PURE__*/React.createElement("div", {
        className: "yith-stripe-form-container ".concat(mode)
      }, this.getNameOnCardField(), elements.map(function (element) {
        var ref = (0,external_wp_element_namespaceObject.useRef)(),
          id = 'yith-stripe-' + element.replace(/([A-Z])/g, '-$1').toLowerCase();
        _this2.elementNodes[element] = ref;
        return /*#__PURE__*/React.createElement("div", {
          key: element,
          className: _this2.getElementClasses(element)
        }, /*#__PURE__*/React.createElement("label", null, _this2.getElementLabel(element)), /*#__PURE__*/React.createElement("div", {
          id: id,
          ref: ref,
          className: "yith-stripe-elements-field"
        }));
      }));
    }

    /**
     * Return component for Name on Card field (if enabled)
     */
  }, {
    key: "getNameOnCardField",
    value: function getNameOnCardField() {
      var _yith_stripe_info6 = yith_stripe_info,
        show_name_on_card = _yith_stripe_info6.show_name_on_card;
      if (!show_name_on_card) {
        return;
      }
      var ref = (0,external_wp_element_namespaceObject.useRef)();
      this.elementNodes.nameOnCard = ref;
      return /*#__PURE__*/React.createElement("div", {
        className: "form-row form-row-wide"
      }, /*#__PURE__*/React.createElement("label", null, this.getElementLabel('nameOnCard')), /*#__PURE__*/React.createElement("input", {
        id: "yith-stripe-name-on-card",
        className: "yith-stripe-elements-field",
        type: "text",
        ref: ref,
        placeholder: this.getElementPlaceholder('nameOnCard')
      }));
    }

    /**
     * Register payment methods
     */
  }, {
    key: "registerMethod",
    value: function registerMethod() {
      var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
      registerPaymentMethod(this.getMethod());
    }

    /* === CHECKOUT HANDLING === */

    /**
     * Prints error messages relevant to customer
     *
     * @param {Object} error Error describing current error.
     */
  }, {
    key: "onError",
    value: function onError(error) {
      return {
        type: 'failure',
        message: error.message
      };
    }

    /**
     * During payment step processing, calls Stripe to generate payment method to use in API request.
     */
  }, {
    key: "onPaymentProcessing",
    value: function onPaymentProcessing() {
      return this.submitElements();
    }

    /**
     * This method is triggered after hash change, and it used when intent needsAction from customer
     */
  }, {
    key: "onHashChange",
    value: function onHashChange() {
      var partials = window.location.hash.match(/^#?yith-confirm-pi-([^:]+)\/(.+)$/);
      if (!partials || 3 > partials.length) {
        return;
      }
      var _partials = _slicedToArray(partials, 3),
        intentClientSecret = _partials[1],
        redirectURL = _partials[2];

      // Cleanup the URL
      window.location.hash = '';
      this.openIntentModal(intentClientSecret, redirectURL);
    }

    /**
     * Open modal for card actions
     */
  }, {
    key: "openIntentModal",
    value: function openIntentModal(secret, redirectURL) {
      var _this3 = this;
      var handler = secret.indexOf('seti') < 0 ? 'handleCardAction' : 'handleCardSetup';
      this.stripe[handler](secret).then(function (result) {
        result.error && _this3.onError(result.error);
        result.error || (window.location = decodeURIComponent(redirectURL));
      })["catch"](function (error) {
        return error.log(error);
      });
    }

    /* === ELEMENTS HANDLING === */

    /**
     * Init Elements given that conditions are matched
     */
  }, {
    key: "initElements",
    value: function initElements() {
      if (!this.stripe) {
        return;
      }
      var _yith_stripe_info7 = yith_stripe_info,
        mode = _yith_stripe_info7.mode;
      if ('hosted' === mode) {
        return;
      }
      if (!this.elements) {
        this.elements = this.stripe.elements();
        this.createElements();
      }
      this.mount();
    }

    /***
     * Create elements components
     */
  }, {
    key: "createElements",
    value: function createElements() {
      var _yith_stripe_info8 = yith_stripe_info,
        showZip = _yith_stripe_info8.show_zip,
        style = this.getElementsStyle();
      if (!this.elements) {
        return;
      }
      var elements = this.getElements();
      if (!elements) {
        return;
      }
      var _iterator = _createForOfIteratorHelper(elements),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var element = _step.value;
          var placeholder = this.getElementPlaceholder(element);
          this.elementComponents[element] = this.elements.create(element, {
            style: style,
            placeholder: placeholder,
            showIcon: true,
            hidePostalCode: !showZip
          });
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
    }

    /**
     * Submit Elements form and waits for response
     */
  }, {
    key: "submitElements",
    value: function submitElements() {
      if (!this.stripe || !this.elements) {
        return;
      }
      return this.elements.submit().then(this.afterElementsSubmit)["catch"](this.onError);
    }

    /**
     * Handles successful responses from Elements submit
     */
  }, {
    key: "afterElementsSubmit",
    value: function afterElementsSubmit() {
      var _this4 = this;
      var _yith_stripe_info9 = yith_stripe_info,
        mode = _yith_stripe_info9.mode,
        type = 'standard' === mode ? 'cardNumber' : 'card';
      return this.stripe.createPaymentMethod('card', this.elementComponents[type], {
        billing_details: this.getBillingDetails()
      }).then(function (_ref) {
        var error = _ref.error,
          paymentMethod = _ref.paymentMethod;
        if (error) {
          return _this4.onError(error);
        }
        return {
          type: 'success',
          meta: {
            paymentMethodData: {
              stripe_payment_method: paymentMethod.id
            }
          }
        };
      })["catch"](this.onError);
    }

    /**
     * Mount Elements on target node
     */
  }, {
    key: "mount",
    value: function mount() {
      for (var _i = 0, _Object$entries = Object.entries(this.elementComponents); _i < _Object$entries.length; _i++) {
        var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
          element = _Object$entries$_i[0],
          component = _Object$entries$_i[1];
        var node = this.getElementNode(element);
        if (!node) {
          continue;
        }
        component.mount(node);
      }
    }

    /**
     * Mount Elements on target node
     */
  }, {
    key: "unmount",
    value: function unmount() {
      var _iterator2 = _createForOfIteratorHelper(this.elementComponents),
        _step2;
      try {
        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
          var component = _step2.value;
          component.unmount();
        }
      } catch (err) {
        _iterator2.e(err);
      } finally {
        _iterator2.f();
      }
    }

    /* === GETTERS METHOD === */

    /**
     * Get an object with billing details about customer
     *
     * @return {Object} Billing object.
     */
  }, {
    key: "getBillingDetails",
    value: function getBillingDetails() {
      var address = this.billing.billingAddress,
        nameOnCard = this.getElementNode('nameOnCard');
      var name = "".concat(address.first_name, " ").concat(address.last_name);
      if (nameOnCard) {
        name = nameOnCard.value;
      }
      return {
        name: name,
        email: address === null || address === void 0 ? void 0 : address.email,
        phone: address === null || address === void 0 ? void 0 : address.phone,
        address: {
          country: address === null || address === void 0 ? void 0 : address.country,
          city: address === null || address === void 0 ? void 0 : address.city,
          line1: address === null || address === void 0 ? void 0 : address.address_1,
          line2: address === null || address === void 0 ? void 0 : address.address_2,
          postal_code: address === null || address === void 0 ? void 0 : address.postcode,
          state: address.state
        }
      };
    }

    /**
     * Returns a list of valid elements for current mode
     */
  }, {
    key: "getElements",
    value: function getElements() {
      var _yith_stripe_info10 = yith_stripe_info,
        mode = _yith_stripe_info10.mode;
      var elements;
      switch (mode) {
        case 'elements':
          elements = ['card'];
          break;
        case 'standard':
          elements = ['cardNumber', 'cardExpiry', 'cardCvc'];
          break;
      }
      return elements;
    }

    /**
     * Returns an object containing style properties to apply to each element
     */
  }, {
    key: "getElementsStyle",
    value: function getElementsStyle() {
      var _yith_stripe_info11 = yith_stripe_info,
        baseBackgroundColor = _yith_stripe_info11.background_color,
        baseFontSize = _yith_stripe_info11.font_size,
        baseColor = _yith_stripe_info11.color,
        baseIconColor = _yith_stripe_info11.icon_color,
        baseFontFamily = _yith_stripe_info11.font_family,
        placeholderColor = _yith_stripe_info11.placeholder_color,
        invalidIconColor = _yith_stripe_info11.invalid_icon_color,
        invalidColor = _yith_stripe_info11.invalid_color,
        completeColor = _yith_stripe_info11.complete_color;
      return {
        base: {
          // Add your base input styles here. For example:
          backgroundColor: baseBackgroundColor,
          fontSize: baseFontSize,
          color: baseColor,
          iconColor: baseIconColor,
          fontFamily: baseFontFamily,
          '::placeholder': {
            color: placeholderColor
          }
        },
        invalid: {
          iconColor: invalidIconColor,
          color: invalidColor
        },
        complete: {
          color: completeColor
        }
      };
    }

    /**
     * Searched for the reference to a specific element,
     * and if finds it returns current node
     */
  }, {
    key: "getElementNode",
    value: function getElementNode(element) {
      var _this$elementNodes, _this$elementNodes$el;
      if (!((_this$elementNodes = this.elementNodes) !== null && _this$elementNodes !== void 0 && _this$elementNodes[element])) {
        return null;
      }
      return (_this$elementNodes$el = this.elementNodes[element]) === null || _this$elementNodes$el === void 0 ? void 0 : _this$elementNodes$el.current;
    }

    /**
     * Get label for a specific element
     */
  }, {
    key: "getElementLabel",
    value: function getElementLabel(element) {
      var _yith_stripe_info12;
      var _yith_stripe_info$lab = (_yith_stripe_info12 = yith_stripe_info) === null || _yith_stripe_info12 === void 0 ? void 0 : _yith_stripe_info12.labels,
        fields = _yith_stripe_info$lab.fields,
        elementId = element.replace(/([A-Z])/g, '-$1').toLowerCase();
      if (!fields || !(fields !== null && fields !== void 0 && fields[elementId])) {
        return '';
      }
      return fields === null || fields === void 0 ? void 0 : fields[elementId].label;
    }

    /**
     * Get placeholder for a specific element
     */
  }, {
    key: "getElementPlaceholder",
    value: function getElementPlaceholder(element) {
      var _yith_stripe_info13;
      var _yith_stripe_info$lab2 = (_yith_stripe_info13 = yith_stripe_info) === null || _yith_stripe_info13 === void 0 ? void 0 : _yith_stripe_info13.labels,
        fields = _yith_stripe_info$lab2.fields,
        elementId = element.replace(/([A-Z])/g, '-$1').toLowerCase();
      if (!fields || !(fields !== null && fields !== void 0 && fields[elementId])) {
        return '';
      }
      return fields === null || fields === void 0 ? void 0 : fields[elementId].placeholder;
    }

    /**
     * Get classes to apply to element wrapper
     */
  }, {
    key: "getElementClasses",
    value: function getElementClasses(element) {
      var classes = ['form-row'];
      if (window.matchMedia('(max-width: 768px)').matches) {
        classes.push('form-row-wide');
      } else {
        switch (element) {
          case 'card':
          case 'cardNumber':
            classes.push('form-row-wide');
            break;
          case 'cardExpiry':
            classes.push('form-row-first');
            break;
          case 'cardCvc':
            classes.push('form-row-last');
            break;
        }
      }
      return classes.join(' ');
    }
  }]);
  return StripeElementBlock;
}();

if (typeof wc !== 'undefined') {
  new StripeElementBlock();
}
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=stripe-block.bundle.js.map