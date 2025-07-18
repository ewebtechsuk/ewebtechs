jQuery( function( $ ){
    var enable_input            = $( '.woocommerce_yith-stripe_enabled' ).children( 'input:checkbox' ), 
        stripe_mode             = $( '.woocommerce_yith-stripe_mode' ),
        save_cards              = $( '.woocommerce_yith-stripe_save_cards' ),
        save_cards_mode         = $( '.woocommerce_yith-stripe_save_cards_mode' ),
        billing_checkout_field  = $( '.woocommerce_yith-stripe_add_billing_hosted_fields' ),
        shipping_checkout_field = $( '.woocommerce_yith-stripe_add_shipping_hosted_fields' ),
        custom_style            = $( '.woocommerce_yith-stripe_custom_payment_method_style' ),
        billing_field           = $( '.woocommerce_yith-stripe_add_billing_fields' ),
        name_on_card            = $( '.woocommerce_yith-stripe_show_name_on_card' ),
        zip_field               = $( '.woocommerce_yith-stripe_elements_show_zip' ),
        config_webhook          = $( '#config_webhook' ),
        blacklist_input         = $( '.woocommerce_yith-stripe-enable-blacklist' ).children( 'input:checkbox' ),
        blacklist_tab           = $( '.yith-plugin-fw-tabs' ).children('li').eq(1),
        save_button             = $( '#yith-plugin-fw-float-save-button' );

    // Multi dependencies. 
    stripe_mode.on( 'change', function(){
        var t = $(this),
            v = t.val();

        switch ( v ) {
            case 'standard':
                save_cards.closest( 'tr' ).show();
                custom_style.closest( 'tr' ).show();
                billing_checkout_field.closest( 'tr' ).hide();
                shipping_checkout_field.closest( 'tr' ).hide();
                billing_field.closest( 'tr' ).show();
                name_on_card.closest( 'tr' ).show();
                zip_field.closest( 'tr' ).hide();
                break;
            case 'hosted':
                save_cards.closest( 'tr' ).hide();
                custom_style.closest( 'tr' ).hide();
                billing_checkout_field.closest( 'tr' ).show();
                shipping_checkout_field.closest( 'tr' ).show();
                billing_field.closest( 'tr' ).hide();
                name_on_card.closest( 'tr' ).show();
                zip_field.closest( 'tr' ).hide();
                break;
            case 'elements':
                save_cards.closest( 'tr' ).show();
                custom_style.closest( 'tr' ).show();
                billing_checkout_field.closest( 'tr' ).hide();
                shipping_checkout_field.closest( 'tr' ).hide();
                billing_field.closest( 'tr' ).hide();
                name_on_card.closest( 'tr' ).show();
                zip_field.closest( 'tr' ).show();
                break;
        }
    } ).change();

    // General toggle dependencies.
    enable_input.on( 'change', function(){
      
        var t          = $(this),
            v          = t.val(),
            stripe_val = stripe_mode.val();
            
        if ( 'hosted' === stripe_val ) {
            billing_checkout_field.closest( 'tr' ).show();
            shipping_checkout_field.closest( 'tr' ).show();
        } else {
            billing_checkout_field.closest( 'tr' ).hide();
            shipping_checkout_field.closest( 'tr' ).hide();
        }

        if ( 'yes' === v  ) {
            stripe_mode.closest( 'tr' ).show();
            save_cards.closest( 'tr' ).show();
            custom_style.closest( 'tr' ).show();
            save_cards_mode.closest( 'tr' ).show();
            billing_field.closest( 'tr' ).show();
            name_on_card.closest( 'tr' ).show();
            zip_field.closest( 'tr' ).show();
        } else {
            stripe_mode.closest( 'tr' ).hide();
            save_cards.closest( 'tr' ).hide();
            custom_style.closest( 'tr' ).hide();
            save_cards_mode.closest( 'tr' ).hide();
            billing_field.closest( 'tr' ).hide();
            name_on_card.closest( 'tr' ).hide();
            zip_field.closest( 'tr' ).hide();
        }
    } ).change();

    // Save cards dependencies.
    save_cards.on( 'change', function(){
      
        var t = $(this).children( 'input:checkbox' ),
            v = t.val();

        if ( 'yes' === v  ) {
            save_cards_mode.closest( 'tr' ).show();
            custom_style.closest( 'tr' ).show();
        } else {
            save_cards_mode.closest( 'tr' ).hide();
            custom_style.closest( 'tr' ).hide();
        }
    } ).change();

    // Webhook behavior.
    config_webhook.on( 'click', function( ev ){
        var t = $(this),
            p = t.closest('div');

        ev.preventDefault();

        $.ajax( {
            beforeSend: function(){
                p.block({message: null, overlayCSS: {background: "#fff", opacity: .6}});
            },
            complete: function(){
                p.unblock();
            },
            data: {
                action: yith_stripe.actions.set_webhook,
                security: yith_stripe.security.set_webhook
            },
            success: function( data ){
                if( data && typeof data.status != 'undefined' ){
                    var noticeClass = data.status ? 'success' : 'error',
                        noticeContent = $( '<p/>', { text: data.message } ),
                        notice = $( '<div/>', { id: 'webhook_notice', class: 'notice notice-' + noticeClass } ),
                        removeContent = function(){
                            $(this).remove();
                        };

                    $( '#webhook_notice' ).fadeOut( removeContent );

                    p.before( notice.append( noticeContent ) );

                    setTimeout( function(){
                        notice.fadeOut( removeContent );
                    }, 3000 );
                }
            },
            url: ajaxurl
        } );
    } );
} );