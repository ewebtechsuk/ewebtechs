=== YITH WooCommerce Stripe ===

Contributors: yithemes
Tags: stripe, simple stripe checkout, stripe checkout, credit cards, online payment, payment, payments, recurring billing, subscribe, subscriptions, bitcoin, gateway, yithemes, woocommerce, shop, ecommerce, e-commerce
Requires at least: 6.6
Tested up to: 6.8
Stable tag: 3.34.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 3.34.0 - Released on 10 June 2025 =

* New: support for WooCommerce 9.9
* New: support for WooCommerce Email Preview feature
* Update: YITH plugin framework

= 3.33.0 - Released on 8 April 2025 =

* New: support for WordPress 6.8
* Update: YITH plugin framework

= 3.32.0 - Released on 18 March 2025 =

* New: support for WooCommerce 9.8
* Update: YITH plugin framework

= 3.31.0 - Released on 12 February 2025 =

* New: support for WooCommerce 9.7
* Update: YITH plugin framework

= 3.30.0 - Released on 28 January 2025 =

* New: support for WooCommerce 9.6
* Update: YITH plugin framework
* Fix: conflict with tabs in the plugin panel
* Dev: added new filter yith_wcstripe_admin_menu_args

= 3.29.0 - Released on 5 December 2024 =

* New: support for WooCommerce 9.5
* Update: YITH plugin framework

= 3.28.0 - Released on 6 November 2024 =

* New: support for WordPress 6.7
* New: support for WooCommerce 9.4
* Update: YITH plugin framework

= 3.27.0 - Released on 4 September 2024 =

* New: support for WooCommerce 9.3
* Update: YITH plugin framework
* Update: Stripe PHP library to version 15.8.0
* Update: Stripe API to version 2024-06-20

= 3.26.0 - Released on 6 August 2024 =

* New: support for WooCommerce 9.2
* Update: YITH plugin framework

= 3.25.0 - Released on 15 July 2024 =

* New: support for WordPress 6.6
* New: support for WooCommerce 9.1
* Update: YITH plugin framework
* Fix: save correctly the payment token for the Pre-Order integration

= 3.24.0 - Released on 10 June 2024 =

* New: support for WooCommerce 9.0
* Update: YITH plugin framework

= 3.23.0 - Released on 16 May 2024 =

* New: support for WooCommerce 8.9
* Update: YITH plugin framework

= 3.22.0 - Released on 18 April 2024 =

* New: support for WordPress 6.5
* New: support for WooCommerce 8.8
* Update: YITH plugin framework
* Fix: charge correctly the deposit order when HPOS is enabled

= 3.21.0 - Released on 21 March 2024 =

* New: support for WordPress 6.5
* New: support for WooCommerce 8.7
* Update: YITH plugin framework
* Fix: added missing $email parameter to email template actions
* Fix: wrong name of subscription meta table

= 3.20.0 - Released on 22 February 2024 =

* New: support for WooCommerce 8.6
* Update: YITH plugin framework
* Tweak: move focus automatically to next field when customer completes card data in Standard mode

= 3.19.1 - Released on 30 January 2024 =

* Update: YITH plugin framework

= 3.19.0 - Released on 11 January 2024 =

* New: support for WooCommerce 8.5
* Update: YITH plugin framework

= 3.18.0 - Released on 14 December 2023 =

* New: support for WooCommerce 8.4
* Update: YITH plugin framework
* Fix: added return_url to confirm method when processing subscription
* Dev: added yith_wcstripe_show_name_on_card_field filter

= 3.17.3 - Released on 30 November 2023 =

* Fix: load bundle version of the elements script

= 3.17.2 - Released on 21 November 2023 =

* Fix: avoided errors related to integration with WooCommerce blocks when the plugin is activated

= 3.17.1 - Released on 20 November 2023 =

* New: support for WooCommerce checkout block

= 3.17.0 - Released on 20 November 2023 =

* New: support for WordPress 6.4
* New: support for WooCommerce 8.3
* Update: YITH plugin framework
* Update: language files
* Tweak: check for token gateway id before processing renew
* Tweak: minor changes for sbs refactoring support

= 3.16.0 - Released on 5 October 2023 =

* New: support for WooCommerce 8.2
* Update: YITH plugin framework
* Fix: added some checks over the payment intent to avoid possible errors

= 3.15.0 - Released on 11 September 2023 =

* New: support for WooCommerce 8.1
* Update: YITH plugin framework

= 3.14.0 - Released on 2 August 2023 =

* New: support for WordPress 6.3
* New: support for WooCommerce 8.0
* Update: YITH plugin framework

= 3.13.0 - Released on 12 July 2023 =

* New: support for WooCommerce 7.9
* Update: YITH plugin framework

= 3.12.0 - Released on 06 June 2023 =

* New: support for WooCommerce 7.8
* Update: YITH plugin framework

= 3.11.0 - Released on 08 May 2023 =

* New: support for WooCommerce 7.7
* Update: YITH plugin framework

= 3.10.0 - Released on 11 April 2023 =

* New: support for WooCommerce 7.6
* Update: YITH plugin framework
* Tweak: prevent API from trying to cancel an already cancelled subscription

= 3.9.0 - Released on 06 March 2023 =

* New: Support for WordPress 6.2
* New: support for WooCommerce 7.5
* Update: YITH plugin framework
* Tweak: improved instance checking for webhooks

= 3.8.0 - Released on 04 January 2023 =

* New: support for WooCommerce 7.4
* Update: YITH plugin framework

= 3.7.0 - Released on 04 January 2023 =

* New: support for WooCommerce 7.3
* Update: YITH plugin framework
* Update: API to version 2022-11-15
* Update: Stripe PHP library to version 10.3
* Tweak: improved author info inside composer.json file
* Tweak: improved refund messages
* Fix: undefined variable refund in refund handling

= 3.6.0 - Released on 13 December 2022 =

* New: support for WooCommerce 7.2
* Update: YITH plugin framework
* Tweak: Allow to add query parameters in the return url
* Dev: added check to make sure wc_add_notice function is defined

= 3.5.1 - Released on 14 November 2022 =

* Fix: patched security vulnerability

= 3.5.0 - Released on 26 October 2022 =

* New: support for WordPress 6.1
* New: support for WooCommerce 7.1
* Update: YITH plugin framework

= 3.4.0 - Released on 04 October 2022 =

* New: support for WooCommerce 7.0
* Update: YITH plugin framework
* Update: Stripe PHP library to version 9.6
* Update: Stripe API to version 2022-08-01

= 3.3.0 - Released on 13 September 2022 =

* New: support for WooCommerce 6.9
* Update: YITH plugin framework
* Tweak: added name during Customer creation
* Tweak: improved description for the customer
* Tweak: send phone number together with card details at checkout
* Dev: added yith_wcstripe_customer_description filter

= 3.2.0 - Released on 02 August 2022 =

* New: support for WooCommerce 6.8
* Update: YITH plugin framework

= 3.1.1 - Released on 11 July 2022 =

* Tweak: added option to enable/disable custom Payment Method table style

= 3.1.0 - Released on 06 July 2022 =

* New: support for WooCommerce 6.7
* New: added new style for payment method action buttons
* Update: YITH plugin framework

= 3.0.0 - Released on 29 June 2022 =

* Update: YITH plugin framework
* Tweak: improved panel style and UX
* Dev: added yith_wcstripe_checkout_description filter, to allow modify the checkout description

= 2.12.0 - Released on 16 June 2022 =

* New: support for WooCommerce 6.6
* Update: YITH plugin framework
* Fix: added country-select dependency to plugin script, when showing billing fields Updated submodules

= 2.11.0 - Released on 17 May 2022 =

* New: support for WordPress 6.0
* New: support for WooCommerce 6.5
* New: added option to show Shipping fields on Stripe Checkout page
* New: Pre-order integration - exclude Pay Later orders when processing an upon release pre-order
* Update: YITH plugin framework
* Dev: added some checks to prevent some errors
* Dev: added yith_wcstripe_do_instances_match filter, to allow third party code greenlight domains for security check

= 2.10.0 - Released on 12 April 2022 =

* New: support for WooCommerce 6.4
* Update: YITH plugin framework
* Fix: added check over function exists when processing renew, to avoid fatal errors on backend

= 2.9.0 - Released on 03 March 2022 =

* New: support for WooCommerce 6.3
* Tweak: added metadata to refund API request
* Update: YITH plugin framework
* Fix: charge_refunded webhook handler checking wrong instance

= 2.8.0 - Released on 08 February 2022 =

* New: support for WooCommerce 6.2
* Update: YITH plugin framework

= 2.7.0 - Released on 12 January 2022 =

* New: support for WordPress 5.9
* New: support for WooCommerce 6.1
* Update: YITH plugin framework

= 2.6.0 - Released on 2 December 2021 =

* New: support for WooCommerce 6.0
* Update: YITH plugin framework
* Fix: placeholder in refund reason

= 2.5.0 - Released on 4 November 2021 =

* New: support for WooCommerce 5.9
* Tweak: improved queue handling for Dispatch expiring card reminders cron
* Update: YITH plugin framework

= 2.4.0 - Released on 11 October 2021 =

* New: support for WooCommerce 5.8
* New: German translation
* Update: YITH plugin framework

= 2.3.1 - Released on 27 September 2021 =

* Update: YITH Plugin Framework
* Fix: debug info feature removed for all logged in users

= 2.3.0 - Released on 23 September 2021 =

* New: support for WooCommerce 5.7
* Update: YITH Plugin Framework
* Update: Stripe PHP library to 7.97.0

= 2.2.1 - Released on 24 August 2021 =

* Update: YITH Plugin Framework
* Fix: reverted back small chunks of code working on PHP8 only
* Fix: manage requires_capture intent status during renew payment
* Dev: added filter yith_wcstripe_renew_capture_method to allow third party set manual capture on renews

= 2.2.0 - Released on 18 August 2021 =

* New: support for WooCommerce 5.6
* Update: YITH Plugin Framework
* Tweak: major code refactoring

= 2.1.6 - Released on 23 July 2021 =

* New: support for WooCommerce 5.5
* New: support for WordPress 5.8
* Update: YITH Plugin Framework
* Dev: added new filter yith_wcstripe_line_item_tax_name

= 2.1.5 - Released on 18 June 2021 =

* New: support for WooCommerce 5.4
* Update: YITH plugin framework
* Update: Stripe PHP library to 7.85.0
* Fix: broken link in capture settings
* Fix: added new check before updating CC form fields, in order to avoid interfering with other plugins

= 2.1.4 - Released on 17 May 2021 =

* New: support for WooCommerce 5.3
* Update: YITH plugin framework
* Update: Stripe PHP library to 7.78.0
* Tweak: avoid possible problems with 3DSecure callback, passing both order and order_id params within return url

= 2.1.3 - Released on 21 April 2021 =

* New: support for WooCommerce 5.2
* Update: YITH plugin framework
* Update: Stripe PHP library to 7.75.0
* Fix: trialing subscription failing payment due to setup intent checks
* Fix: change payment token also on cancelled or suspended subscriptions, when setting a new default card
* Fix: save intent_id inside order after correctly processing intent in Stripe Classic mode
* Dev: new filters yith_wcstripe_elements_color and yith_wcstripe_elements_font_size
* Dev: added new filters and parameters to make the Stripe form customizable

= 2.1.2 - Released on 14 March 2021 =

* New: support for WordPress 5.7
* New: support for WooCommerce 5.1
* Update: YITH plugin framework
* Update: Stripe PHP library to 7.75.0
* Dev: new filters yith_wcstripe_elements_color and yith_wcstripe_elements_font_size
* Dev: added new filters to allow customization of the Stripe Elements form
* Dev: added new yith_wcstripe_elements_show_icon filter

= 2.1.1 - Released on 18 February 2021 =

* New: support for WooCommerce 5.0
* Update: YITH plugin framework
* Update: Stripe PHP library to 7.74.0
* Fix: order captured status after Checkout Session payment
* Fix: remove deprecated ready event from scripts
* Fix: it was not possible to pay an order using YITH One Click Checkout feature
* Dev: added new yith_wcstripe_line_item_description filter

= 2.1.0 - Released on 12 January 2021 =

* New: support for WooCommerce 4.9
* Update: plugin framework

= 2.0.18 - Released on 18 December 2020 =

* Fix: fatal error occurring for older versions of PHP

= 2.0.17 - Released on 18 December 2020 =

* Update: plugin framework
* Update: Stripe PHP library to 7.67.0
* Tweak: security improvements for checkout process
* Fix: check intent type before proceeding with payment

= 2.0.16 - Released on 09 December 2020 =

* New: support for WooCommerce 4.8
* Update: plugin framework
* Update: Stripe PHP library to 7.66.1

= 2.0.15 - Released on 11 November 2020 =

* New: support for WordPress 5.6
* New: support for WooCommerce 4.7
* New: possibility to update plugin via WP-CLI
* Update: plugin framework
* Update: Stripe PHP library to 7.62.0
* Tweak: added minified versions of the scripts
* Dev: removed deprecated .ready method from scripts
* Dev: added filter yith_wcstripe_does_webhook_instance_match

= 2.0.14 - Released on 16 October 2020 =

* Update: plugin framework
* Tweak: added check over plan_id to avoid useless call to subscription endpoint
* Tweak: added postal code to the Stripe payment intent
* Tweak: workaround to make Checkout mode work when Gift Cards are applied to the order
* Fix: notice when dealing with customer cards: always double check sources property existence
* Dev: added yith_wcstripe_maybe_show_subscription_action filter, as short circuit to skip manual renew button before any API call

= 2.0.13 - Released on 30 September 2020 =

* New: Support for WooCommerce 4.6
* Update: plugin framework
* Fix: wrong last day of the month in renew time calculation
* Fix: Stripe Classic subscriptions causing Fatal Error with new APIs
* Dev: added new filter yith_stripe_cancel_url

= 2.0.12 - Released on 18 September 2020 =

* New: Support for WooCommerce 4.5
* New: change subscriptions default payment method when customer changes default payment method
* Update: plugin framework
* Update: Stripe PHP library to version 7.52
* Update: API version to 2020-08-27
* Tweak: removed usage of deprecated ready event from js assets
* Tweak: reviewed method that process renew payments
* Dev: added action yith_wcstripe_deleted_card
* Dev: new action yith_wcstripe_before_send_expiring_card_notification
* Dev: new action yith_wcstripe_after_send_expiring_card_notification

= 2.0.11 - Released on 14 August 2020 =

* New: Support for WordPress 5.5
* New: Support for WooCommerce 4.4
* Update: plugin framework
* Update: Stripe PHP library to version 7.47.0
* Tweak: improved integration with YITH WooCommerce Subscription 2.0
* Tweak: added check over YITH_DOING_RENEWS constant on is_available method of the gateway
* Tweak: added check over instance before processing invoice webhooks
* Tweak: added switch locale for customer emails
* Fix: remove HTML tags of product description in Stripe checkout mode
* Dev: added new filter ywsbs_suspend_for_failed_recurring_payment
* Dev: added new filter yith_wcstripe_schedule_crons
* Dev: added new parameter to filter yith_wcstripe_checkout_session_detailed_line_items

= 2.0.10 - Released on 10 June 2020 =

* New: Support for WooCommerce 4.2
* Tweak calculate more accurately renew data for monthly plans
* Update: plugin framework
* Update: Stripe PHP library to version 7.37.0
* Dev: added yith_wcstripe_capture_charge_params filter

= 2.0.9 - Released on 08 May 2020 =

* New: Support for WooCommerce 4.1
* New: added line details for Stripe Checkout
* Update: plugin framework
* Update: Stripe PHP library to version 7.31.0
* Tweak: added error message when manual renew attempt fails
* Tweak: moved "retry invoice with other cards" procedure after suspension handling
* Tweak: removed deprecated param from subscription API
* Tweak: retrieve gateway using proper methods
* Fix: issue with charge retrieval during dispute webhook handling
* Fix: payment intent not being refreshed when original order is cancelled and a new one with same amount is being attempted
* Fix: issue with free item when creating Checkout session
* Fix: issue with short description of the item when creating checkout session
* Dev: added filter yith_wcstripe_checkout_session_detailed_line_items to disable line details on Stripe Checkout
* Dev: added yith_wcstripe_session_param filter

= 2.0.8 - Released on 14 March 2020 =

* New: support for WordPress 4.0
* New: support for WooCommerce 4.0
* New: support for API 2020-03-02
* Update: Stripe PHP library to version 7.27.2
* Update: plugin framework
* Update: Spanish language
* Fix: prevent notice when paying renews
* Fix: process_refund method now correctly process refunds on shipping items (thanks to Alex)

= 2.0.7 - Released on 24 December 2019 =

* New: support for WooCommerce 3.9
* Update: plugin framework
* Update: Stripe library to version 7.14.2
* Update: Stripe API to version 2019-12-03
* Tweak: add some log in renew subscription process
* Fix: notice when visiting stripe_webhook api endpoint directly
* Fix: Removed usage of Stripe\Exception\ApiErrorException::factory; using new Exception instead
* Fix: issue with renew payment for guest users

= 2.0.6 - Released on 07 November 2019 =

* New: support for WordPress 5.3
* New: support for WooCommerce 3.8
* New: support for API 2019-11-05
* Update: Plugin framework
* Update: StripePHP library
* Tweak: Added check over instance for webhooks that handle disputes
* Tweak: print success message when update_customer method does not find instance match
* Tweak: avoid using deprecated function to retrieve cart url
* Fix: added wrapper function yith_wcstripe_get_cart_hash to provide $cart->get_cart_hash() function in WC < 3.6
* Dev: reviewed get_customer method to correctly handle exceptions

= 2.0.5 - Released on 14 October 2019 =

* New: support to 2019-10-08 API
* Update: internal plugin framework
* Update: Stripe PHP library to version 7.3.1
* Update: Dutch language
* Fix: cards are now stored when processing a trialing subscription
* Fix: possible notice during pay_renew process

= 2.0.4 – Released on 19 September 2019 =

* Update: Italian language
* Update: Dutch language
* Tweak: prevent error when $order doesn't exists
* Fix: issue with sync_tokens on customer_updated webhook
* Fix: new check over API error to prevent possible Uncaught exception fatal error
* Fix: enabled flag on renew_needs_action email
* Fix: check used to know if plugin should send renew_needs_action email
* Fix: retrieving order again before sending renew_needs_action email, to double check for correct status
* Dev: added action yith_wcstripe_before_refresh_intent

= 2.0.3 - Released on 13 September 2019 =

* New: support to 2019-09-09 API
* Update: Stripe PHP to version 7.0.2
* Update: internal plugin framework
* Tweak: reviewed standard/elements payment process, to confirm intent on server
* Tweak: prevent fatal error checking if return an object
* Fix: error abstract class
* Fix: ApiErrorException on process standard payment

= 2.0.2 - Released on 06 September 2019 =

* Tweak: removed paymentIntent creation on is_available method, to avoid excessive API calls & Webhooks requests (thanks to Jeremy & Scott)
* Fix: error on session creation when user is guest (thanks to Paul & Maurice)

= 2.0.1 - Released on 05 September 2019 =

* Fix: placeholder for CVC field on Standard payment mode

= 2.0.0 - Released on 05 September 2019 =

* New: support to latest API version (2019-08-14)
* New: support to SCA-ready payment methods only
* New: support to PaymentIntent API
* New: support to new version of Stripe Checkout
* New: email renew needs action (requires YITH WooCommerce Subscription 1.6.1 or greater)
* New: action to confirm old cards
* Update: internal plugin framework
* Update: Stripe php library to version 7.0.0
* Update: Italian language
* Fix: undefined variable err on gateway error reporting method
* Delete: dropped support to legacy payment method (as suggested by Stripe)
* Delete: dropped support to legacy cards endpoint

= 1.9.2 - Released on 12 August 2019 =

* New: WooCommerce 3.7.0 RC2 support
* Tweak: added check over gateway instance before calling subscription methods
* Tweak: added default card template, when token is not a Stripe one
* Tweak: use .length() instead of .size(), to prevent problem with new jQuery version
* Tweak: add a function_exists to prevent problems with a previous version
* Tweak: add maxlength to Expiration date
* Update: internal plugin framework
* Update: Italian language
* Fix: calculate cart totals before sending it to stripe, to make stripe checkout work with gift card
* Dev: added method to API class, to update cards

= 1.9.1 - Released on 29 May 2019 =

* New: Support to WordPress 5.2
* Update: Plugin Framework
* Tweak: get_home_url() to get_site_url()
* Tweak: remove protocol from url before performing security check
* Dev: Added new filter yith_wcstripe_charge_params

= 1.9.0 - Released on 11 April 2019 =

* New: WooCommerce 3.6 support
* New: retry renews when a fail occurs
* New: added check over site url, to set Test Mode when plugin is enabled on a staging installation
* Tweak: disabled Make default button for expired cards
* Tweak: improved card form on mobile devices
* Tweak: avoid duplicated cards in card expiration reminder queue
* Tweak: updated card expiration reminder email, to provide correct information when card is already expired
* Tweak: improved error reporting system
* Tweak: system now register card fingerprint during webhook handling
* Update: internal plugin framework
* Update: dutch translation
* Fix: issue when trying to pay with a previously registered card (default card was applied)
* Fix: stripe do not execute code when customer tries to delete/set as default tokens created with other gateways
* Fix: avoid to register token twice, when the same card_id is already saved for the same customer
* Fix: avoid saving cards when related option is disabled

= 1.8.1 - Released on 11 February 2019 =

* New: WooCommerce 3.5.4 support
* New: button to automatically set webhook on Stripe
* New: added reminder email for card expiration
* Update: internal plugin framework
* Update: Stripe php library to version 6.29.3
* Update: Dutch language
* Fix: processing renew instead of new order when reactivating subscriptions

= 1.8.0 - Released on 19 December 2018 =

* New: WordPress 5.0 support
* New: WooCommerce 3.5.2 support
* New: support to latest API version (2018-11-08)
* New: admin can now choose whether they want to automatically store card reference, or ask customer which cards to save (for appropriate payment modes only)
* Tweak: prevent stripe to execute subscriptions methods, when payment method for the subscription is not stripe
* Tweak: added check over gateway existence before filtering YWSBS from list
* Tweak: improved Webhook error messages
* Tweak: check over configurable properties in update_subscription method
* Update: internal plugin framework
* Update: dutch translation
* Fix: error in js to trigger elements handling
* Fix: solved issue occurring when billing state select is not visible
* Fix: implicit casting when registering a new failed invoice
* Fix: call to undefined endpoint, causing a 404 error in API; Limited labels accordingly to new limit set for the API
* Dev: added filter yith_wcstripe_error_message_order_note to let third party code filter error messages stored in order notes

= 1.7.2 - Released on 24 October 2018 =

* New: WooCommerce 3.5 support
* Tweak: updated plugin framework

= 1.7.1 - Released on 15 October 2018 =

* Fix: wrong links on admin page
* Fix: restored plugin panel under YITH menu
* Update: Italian language

= 1.7.0 - Released on 09 October 2018 =

* New: added WordPress 4.9.8 compatibility
* New: added WooCommerce 3.5-RC compatibility
* New: updated Stripe API version to 2018-09-24
* New: updated plugin framework
* New: added pt_PT translation (thanks to Ricardo A.)
* Tweak: plugin now registers card even for subscription only orders;
* Tweak: set default card when processing subscriptions
* Tweak: removed create_function for php 7.2 compatibility
* Tweak: removed usage of deprecated WC function from gateway class
* Tweak: improved error handling for invoices: skip useless checks, and add notes to renew order, instead of parent one
* Fix: order total when paying from order-pay endpoint
* Fix: notices when registering a card
* Fix: check over subscription expiration date
* Fix: get_plan now does not create new plans any longer when just used to check plan existance (this mainly happens when checking if order has active subscription)
* Fix: SSL error notification on admin pages
* Fix: warning when failed attempts is not an array
* Dev: added yith_wcstripe_before_create_token trigger
* Dev: added yith_wcstripe_add_payment_method_result filter
* Dev: added yith_wcstripe_gateway_us_icons filter
* Dev: added yith_wcstripe_gateway_default_icons filter
* Dev: added yith_wcstripe_gateway_icon filter
* Dev: added yith_wcstripe_use_plugin_error_codes filter to show original API error messages
* Dev: added yith_wcstripe_error_message filter to let third party code filter error messages

= 1.6.0 - Released on 28 May 2018 =

* New: WooCommerce 3.4.0 support
* New: WordPress 4.9.6 support
* New: updated plugin fw
* New: GDPR compliance
* New: Stripe library 6.7.1 (Requires API update on Stripe Dashboard)
* New: trial period is now added only when Subscription Product has trial set
* New: added billing and shipping information on Stripe Checkout payment mode
* New: added Hosted mode, similar to free version
* Tweak: added description to renew charges after successful charge webhook
* Tweak: improved customer handling when registering cards
* Tweak: moved notify_failed_renewal to avoid issues when adding a new card from My Account endpoint
* Fix: plan creation, for newer version of API
* Fix: now subscription is cancelled after end date
* Fix: improved js to avoid implict conversions
* Dev: added yith_wcstripe_plan_trial_period filter
* Dev: added filter yith_wcstripe_gateway_enabled to programmatically enable/disable Stripe Gateway
* Dev: added filter yith_wcstripe_gateway_id to let developers filter gateway ID (Use this filter at your own risk; after filtering gateway ID you will need to configure gateway again)

= 1.5.0 - Released on 08 February 2018 =

* New: WooCommerce 3.3.1 support
* New: WordPress 4.9.4 support
* New: updated Stripe library to 6.0 revision
* New: updated plugin-fw library

= 1.4.0 - Released on 09 January 2018 =

* New: WooCommerce 3.2.6 support
* New: updated plugin-fw to version 3.0
* New: updated Stripe library to 5.8 revision
* Tweak: added check over save_cards flag before token creation; this way cards won't be actually saved if admin disable related option
* Fix: check on captured flag on payment_complete
* Fix: stripe script not being loaded in Add Payment Method page
* Fix: token error when remember card functionality is disabled
* Fix: trial start/end time when pausing/resuming subscriptions
* Dev: added yith_wcstripe_subscription_amount to let third party plugin to change plan amount
* Dev: added yith_wcstripe_card_number_dots filter to let dev change "dots" in cc number
* Dev: added filters to change default CC form labels
* Dev: added yith_wcstripe_environment filter
* Dev: added yith_wcstripe_metadata filter to let third party developers change metadata sent to Stripe servers

= 1.3.0 - Released on 04 April 2017 =

* New: WordPress 4.7.3 compatibility
* New: WooCommerce 3.0.0-RC2 compatibility
* New: added italian - ITALY translation
* Fix: plan amount with recurring shipping payment, for YITH WooCommerce Subscription plugin
* Fix: added ajax to refresh amount when hosted checkout needs to be refreshed
* Fix: "Renewal failed" message repeated on my-account page
* Fix: subscription renew link inside MyAccount message
* Fix: guest checkout when purchasing subscription
* Tweak: added check over gateway existence
* Tweak: updated Stripe library to 3.23.0
* Tweak: improved failed renew message, when YITH WooCommerce Subscription active
* Tweak: changed text domain to yith-woocommerce-stripe
* Dev: added yith_wcstripe_capture_payment filter
* Dev: added yith_stripe_locale filter to change locale used in hosted checkout

= 1.2.10 - Released on 16 June 2016 =

* Added: ufficial support to WC 2.6
* Fixed: minor bug fixes

= 1.2.9.1 - Released on 13 June 2016 =

* Added: notification for failed and success renewal, with yith subscription plugin
* Fixed: bugs for final release of WC 2.6

= 1.2.9 - Released on 31 May 2016 =

* Added: support to WC 2.6 Beta 3
* Tweak: improved exception catcher
* Fixed: bug on Stripe Checkout mode when pay order create manually by admin

= 1.2.8 - Released on 27 April 2016 =

* Fixed: amount doesn't shown on stripe checkout
* Fixed: fatal error on card validation on checkout
* Fixed: duplicate cancel notification when triggered "cancel" action from my account
* Fixed: payment due date duplicate on renew

= 1.2.7 - Released on 29 March 2016 =

* Tweak: hash on plan name, on avoid subscription configuration no product (like changing price, interval, trial period, etc..)
* Fixed: improved webhooks on payment succedeed
* Fixed: credit card form isn't shown if selected "New card" on checkout page
* Fixed: fatal error with Stripe\Error\API
* Fixed: wrong cart total on hosted checkout
* Fixed: internal server error if the import is lower then .50 cent
* Fixed: a refund from website is marked double, dued an error from webhook
* Fixed: can't create blacklist table and feature not working
* Fixed: total without tax in plan amount

= 1.2.6 - Released on 16 February 2016 =

* Added: ability to add new credit card by my account
* Fixed: localization for "Stripe checkout"

= 1.2.5 - Released on 16 February 2016 =

* Added: "Stripe checkout" mode directly on checkout page, without button on second page.
* Added: 'order_email' parameter in metadata of Stripe charge
* Added: order note when there is an error during the payment (card declined or card validation by stripe)
* Fixed: stripe library loading causing fatal error in some servers
* Fixed: ccv2 help box not opening on checkout
* Fixed: validation of extra billing fields below credit card form 
* Fixed: bitcoin option didn't work
* Fixed: better response for webhooks, because they remains in pending in some cases

= 1.2.4 - Released on 19 January 2016 =

* Added: compatibility with WooCommerce 2.5
* Added: compatibility with YITH WooCommerce Subscriptions and YITH WooCommerce Membership, so now ability to open and manage new subscriptions with Stripe (available only for "Standard" mode of checkout)
* Added: language support for "Stripe checkout" mode
* Added: ability to show extra address fields below credit card info, if you are using any plugin that change fields on checkout, to reduce fraudolent payment risk
* Updated: Stripe API library with latest version

= 1.2.3 - Released on 14 December 2015 =

* Fixed: no errors for wrong cards during checkout

= 1.2.2 - Released on 10 December 2015 =

* Added: compatibility to multi currency plugin
* Added: compatibility with one-click checkout
* Fixed: bug on refunds for orders not captured yet
* Fixed: localization of CVV suggestion text
* Fixed: bitcoin receivers errors on logs

= 1.2.1 - Released on 19 August 2015 =

* Fixed: Minor bug

= 1.2.0 - Released on 12 August 2015 =

* Added: Support to WooCommerce 2.4
* Updated: Plugin core framework
* Updated: Language pot file

= 1.1.4 - Released on 24 July 2015 =

* Fixed: blacklist table not created on database
* Fixed: blacklist table on admin without pagination

= 1.1.3 - Released on 21 July 2015 =

* Added: ability to ban automatically the users with errors during the payment and ability to manage them in a blacklist page

= 1.1.2 - Released on 09 June 2015 =

* Fixed: localization of cvv help popup content

= 1.1.1 - Released on 24 April 2015 =

* Fixed: creation on-hold orders and flushing checkout session after card error on checkout

= 1.1.0 - Released on 22 April 2015 =

* Added: support to WordPress 4.2
* Added: CVV Card Security Code suggestion
* Fixed: bug on checkout

= 1.0.4 - Released on 21 April 2015 =

* Added: languages pot catalog

= 1.0.3 - Released on 15 April 2015 =

* Added: Name on Card field on Credit Card form of checkout
* Fixed: bug with customer profile creating during purchase

= 1.0.2 - Released on 04 March 2015 =

* Updated: Plugin core framework

= 1.0.1 - Released on 03 March 2015 =

* Fixed: minor bugs

= 1.0.0 =

* Initial release
