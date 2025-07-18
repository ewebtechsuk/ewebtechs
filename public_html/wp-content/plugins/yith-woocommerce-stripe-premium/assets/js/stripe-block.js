'use strict';

/* globals wc jQuery Stripe yith_stripe_info */

import { useEffect, useRef } from '@wordpress/element';

/**
 * Class that init and manages all interactions with Stripe Element
 */
export default class StripeElementBlock {
	/**
	 * Components created with Elements library
	 */
	elementComponents = {};

	/**
	 * Reference to nodes enhanced with Elements
	 */
	elementNodes = {};

	/**
	 * Billing details
	 */
	billing;

	/**
	 * Constructor method
	 */
	constructor() {
		this.onPaymentProcessing = this.onPaymentProcessing.bind( this );
		this.afterElementsSubmit = this.afterElementsSubmit.bind( this );
		this.openIntentModal = this.openIntentModal.bind( this );
		this.onHashChange = this.onHashChange.bind( this );

		this.init();
	}

	/* === INITIALIZATION METHOD === */

	/**
	 * Init current object
	 */
	init() {
		this.registerMethod();
		this.initStripe();
		this.initHashChange();
	}

	/**
	 * Init Stripe object
	 */
	initStripe() {
		const { public_key: publicKey } =
			yith_stripe_info;

		if ( ! publicKey ) {
			return;
		}

		this.stripe = Stripe( publicKey );
	}

	/**
	 * Init hash change
	 */
	initHashChange() {
		window.addEventListener( 'hashchange', this.onHashChange );

		this.onHashChange();
	}

	/* === BLOCK HANDLING === */

	/**
	 * Returns object describing payment method
	 */
	getMethod() {
		const { slug, title } = yith_stripe_info,
			Label = this.getLabel(),
			Content = this.getContent();

		return {
			name: slug,
			label: <Label />,
			content: <Content />,
			edit: <Content />,
			canMakePayment: () => true,
			ariaLabel: title,
		};
	}

	/**
	 * Returns component for the Payment Method label
	 */
	getLabel() {
		return ( props ) => {
			const { PaymentMethodLabel } = props.components,
				{ title } = yith_stripe_info;

			return <PaymentMethodLabel text={ title } />;
		};
	}

	/**
	 * Returns component for the Payment Method content
	 */
	getContent() {
		const { slug, description } = yith_stripe_info;

		return ( props ) => {
			const { billing, activePaymentMethod, eventRegistration } = props,
				{ onPaymentProcessing } = eventRegistration;

			if ( slug !== activePaymentMethod ) {
				return;
			}

			// registers current status of the billing details.
			this.billing = billing;

			// init elements when needed.
			useEffect( () => {
				this.initElements();
			} );

			// register payment processing observer.
			useEffect(
				() => onPaymentProcessing( this.onPaymentProcessing ),
				[ onPaymentProcessing ]
			);

			return (
				<>
					{ description ? <p dangerouslySetInnerHTML={ { __html: description } }></p> : null }
					{ this.getForm() }
				</>
			);
		};
	}

	/**
	 * Get components of the payment form
	 */
	getForm() {
		const { mode } = yith_stripe_info,
			elements = this.getElements();

		if ( 'hosted' === mode ) {
			return;
		}

		return (
			<div className={ `yith-stripe-form-container ${ mode }` }>
				{ this.getNameOnCardField() }
				{ elements.map( ( element ) => {
					const ref = useRef(),
						id = 'yith-stripe-' + element.replace( /([A-Z])/g, '-$1' ).toLowerCase();

					this.elementNodes[ element ] = ref;

					return (
						<div key={ element } className={ this.getElementClasses( element ) }>
							<label>
								{ this.getElementLabel( element ) }
							</label>
							<div
								id={ id }
								ref={ ref }
								className="yith-stripe-elements-field"
							></div>
						</div>
					);
				} ) }
			</div>
		);
	}

	/**
	 * Return component for Name on Card field (if enabled)
	 */
	getNameOnCardField() {
		const { show_name_on_card } = yith_stripe_info;

		if ( ! show_name_on_card ) {
			return;
		}

		const ref = useRef();
		this.elementNodes.nameOnCard = ref;

		return <div className="form-row form-row-wide" >
			<label>
				{ this.getElementLabel( 'nameOnCard' ) }
			</label>
			<input
				id="yith-stripe-name-on-card"
				className="yith-stripe-elements-field"
				type="text"
				ref={ ref }
				placeholder={ this.getElementPlaceholder( 'nameOnCard' ) }
			/>
		</div>;
	}

	/**
	 * Register payment methods
	 */
	registerMethod() {
		const { registerPaymentMethod } = wc.wcBlocksRegistry;

		registerPaymentMethod( this.getMethod() );
	}

	/* === CHECKOUT HANDLING === */

	/**
	 * Prints error messages relevant to customer
	 *
	 * @param {Object} error Error describing current error.
	 */
	onError( error ) {
		return {
			type: 'failure',
			message: error.message,
		};
	}

	/**
	 * During payment step processing, calls Stripe to generate payment method to use in API request.
	 */
	onPaymentProcessing() {
		return this.submitElements();
	}

	/**
	 * This method is triggered after hash change, and it used when intent needsAction from customer
	 */
	onHashChange() {
		const partials = window.location.hash.match( /^#?yith-confirm-pi-([^:]+)\/(.+)$/ );

		if ( ! partials || 3 > partials.length ) {
			return;
		}

		const [ , intentClientSecret, redirectURL ] = partials;

		// Cleanup the URL
		window.location.hash = '';
		this.openIntentModal( intentClientSecret, redirectURL );
	}

	/**
	 * Open modal for card actions
	 */
	openIntentModal( secret, redirectURL ){
		var handler = secret.indexOf( 'seti' ) < 0 ? 'handleCardAction' : 'handleCardSetup';

		this.stripe[ handler ]( secret ).then( ( result ) => {
			result.error && this.onError( result.error );
			result.error || ( window.location = decodeURIComponent( redirectURL ) ) ;
		} ).catch( ( error ) => error.log( error ) );
	}

	/* === ELEMENTS HANDLING === */

	/**
	 * Init Elements given that conditions are matched
	 */
	initElements() {
		if ( ! this.stripe ) {
			return;
		}

		const { mode } = yith_stripe_info;

		if ( 'hosted' === mode ) {
			return;
		}

		if ( ! this.elements ) {
			this.elements = this.stripe.elements();
			this.createElements();
		}

		this.mount();
	}

	/***
	 * Create elements components
	 */
	createElements() {
		const { show_zip: showZip } = yith_stripe_info,
			style = this.getElementsStyle();

		if ( ! this.elements ) {
			return;
		}

		const elements = this.getElements();

		if ( ! elements ) {
			return;
		}

		for ( const element of elements ) {
			const placeholder = this.getElementPlaceholder( element );

			this.elementComponents[ element ] = this.elements.create( element, {
				style,
				placeholder,
				showIcon: true,
				hidePostalCode: ! showZip
			} );
		}
	}

	/**
	 * Submit Elements form and waits for response
	 */
	submitElements() {
		if ( ! this.stripe || ! this.elements ) {
			return;
		}

		return this.elements
			.submit()
			.then( this.afterElementsSubmit )
			.catch( this.onError );
	}

	/**
	 * Handles successful responses from Elements submit
	 */
	afterElementsSubmit() {
		const { mode } = yith_stripe_info,
			type = 'standard' === mode ? 'cardNumber' : 'card';

		return this.stripe
			.createPaymentMethod(
				'card',
				this.elementComponents[ type ],
				{
					billing_details: this.getBillingDetails(),
				}
			)
			.then( ( { error, paymentMethod } ) => {
				if ( error ) {
					return this.onError( error );
				}

				return {
					type: 'success',
					meta: {
						paymentMethodData: {
							stripe_payment_method: paymentMethod.id,
						},
					},
				};
			} )
			.catch( this.onError );
	}

	/**
	 * Mount Elements on target node
	 */
	mount() {
		for ( const [ element, component ] of Object.entries( this.elementComponents ) ) {
			const node = this.getElementNode( element );

			if ( ! node ) {
				continue;
			}

			component.mount( node );
		}
	}

	/**
	 * Mount Elements on target node
	 */
	unmount() {
		for ( const component of this.elementComponents ) {
			component.unmount();
		}
	}

	/* === GETTERS METHOD === */

	/**
	 * Get an object with billing details about customer
	 *
	 * @return {Object} Billing object.
	 */
	getBillingDetails() {
		const address = this.billing.billingAddress,
			nameOnCard = this.getElementNode( 'nameOnCard' );
		let name = `${ address.first_name } ${ address.last_name }`;

		if ( nameOnCard ) {
			name = nameOnCard.value;
		}

		return {
			name,
			email: address?.email,
			phone: address?.phone,
			address: {
				country: address?.country,
				city: address?.city,
				line1: address?.address_1,
				line2: address?.address_2,
				postal_code: address?.postcode,
				state: address.state,
			},
		};
	}

	/**
	 * Returns a list of valid elements for current mode
	 */
	getElements() {
		const { mode } = yith_stripe_info;
		let elements;

		switch ( mode ) {
			case 'elements':
				elements = [ 'card' ];
				break;
			case 'standard':
				elements = [ 'cardNumber', 'cardExpiry', 'cardCvc' ];
				break;
		}

		return elements;
	}

	/**
	 * Returns an object containing style properties to apply to each element
	 */
	getElementsStyle() {
		const {
			background_color: baseBackgroundColor,
			font_size: baseFontSize,
			color: baseColor,
			icon_color: baseIconColor,
			font_family: baseFontFamily,
			placeholder_color: placeholderColor,
			invalid_icon_color: invalidIconColor,
			invalid_color: invalidColor,
			complete_color: completeColor,
		} = yith_stripe_info;

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
				color: invalidColor,
			},
			complete: {
				color: completeColor
			}
		}
	}

	/**
	 * Searched for the reference to a specific element,
	 * and if finds it returns current node
	 */
	getElementNode( element ) {
		if ( ! this.elementNodes?.[ element ] ) {
			return null;
		}

		return this.elementNodes[ element ]?.current;
	}

	/**
	 * Get label for a specific element
	 */
	getElementLabel( element ) {
		const { fields } = yith_stripe_info?.labels,
			elementId = element.replace( /([A-Z])/g, '-$1' ).toLowerCase();

		if ( ! fields || ! fields?.[ elementId ] ) {
			return '';
		}

		return fields?.[ elementId ].label;
	}

	/**
	 * Get placeholder for a specific element
	 */
	getElementPlaceholder( element ) {
		const { fields } = yith_stripe_info?.labels,
			elementId = element.replace( /([A-Z])/g, '-$1' ).toLowerCase();

		if ( ! fields || ! fields?.[ elementId ] ) {
			return '';
		}

		return fields?.[ elementId ].placeholder;
	}

	/**
	 * Get classes to apply to element wrapper
	 */
	getElementClasses( element ) {
		const classes = [ 'form-row' ];

		if ( window.matchMedia( '(max-width: 768px)' ).matches ) {
			classes.push( 'form-row-wide' );
		} else {
			switch ( element ) {
				case 'card':
				case 'cardNumber':
					classes.push( 'form-row-wide' );
					break;
				case 'cardExpiry':
					classes.push( 'form-row-first' );
					break;
				case 'cardCvc':
					classes.push( 'form-row-last' );
					break;
			}

		}
		return classes.join( ' ' );
	}
}

if ( typeof wc !== 'undefined' ) {
	new StripeElementBlock();
}
