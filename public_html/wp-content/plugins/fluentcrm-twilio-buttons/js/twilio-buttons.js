jQuery(function($){
    $(document).on('click', '.fcrm_sms_button', function(e){
        e.preventDefault();
        var phone = $(this).data('phone');
        $.post(FCTB.ajax_url, {action:'fctb_send_sms', nonce:FCTB.nonce, phone:phone}, function(resp){
            alert(resp.success ? 'SMS sent' : 'Error: ' + resp.data);
        });
    });
});
