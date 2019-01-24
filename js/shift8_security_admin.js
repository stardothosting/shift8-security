jQuery(document).ready(function() {
    jQuery('#shift8-security-secret-button').click(function(e) {
        e.preventDefault();
        var button = jQuery(this);
        var url = button.attr('href');

        jQuery.ajax({
            url: url,
            data: {
                'action' : 'shift8_security_response',
            },
            success:function(response) {
                //console.log('response : ' + JSON.stringify(response));
                //console.log(the_ajax_script.ajaxurl);
                jQuery('input[name=shift8_security_2fa_secret]').val(response);
                alert('Note : Re-generating the 2FA secret will invalidate all previously configured QR codes. You will have to re-add the QR to your app!');
           },
           error: function(errorThrown) {
                console.debug('Failure : ' + JSON.stringify(errorThrown));
           }
       });
        return false;
    });
});