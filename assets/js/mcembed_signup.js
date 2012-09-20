jQuery(document).bind( 'ready', function() {

    jQuery('.mcembed_signupform_back').live( 'click', function (e) {
        var container = jQuery(this).parents('.mcembed_container');
        
        MCEMbed_ShowForm(container, jQuery(container).data('html').slider, e);
    });
    
    jQuery('.mcembed_signupform_submit').live( 'click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        
        var form = jQuery(this).parents('form');
        var response = '';
        var success = false;
        
        jQuery.ajax({
                url: MCEmbed.ajaxurl,                
                async: false,
                type: 'POST',
                cache: false,
                data: { action: 'mcembed-get-signup-form-submit', nonce : MCEmbed.nonce, fields: jQuery(form).serialize() },
                success: function( r ) {
                    if (r.status == 'success') {
                        success = r.response.subscribed;
                        response =  r.response.message;
                    } else {
                    alert('** Aajx call failed. User would be redirected to MailChimp hosted signup form ** ');
//                        jQuery(location).attr('href',link);
                    }
                },
                error: function( r ) {
                    response =  r.response.message;
                },
            }
        );
        if ( success == true ) {
            var container = jQuery(this).parents('.mcembed_container');
            jQuery(container).html('<p>' + response + '</p><p><a class="mcembed_signupform_back" href="#">Go back to the slider</a></p>');
        } else {
            jQuery(form).next().html(response);
        }
    });
    
    jQuery('.mcembed_signuplink').live( 'click', function (e) {
        
        e.stopPropagation();
        e.preventDefault();

        var uid = '';
        var listid = '';
        var success = false;
        var code = '';
        
        var container = jQuery(this).parents('.mcembed_container');
        var link = jQuery(this).attr('href');
        
        container.find('span:hidden').each( function (index) {
            if ( jQuery(this).hasClass('mcembed_uid') ) {
                uid = jQuery(this).html();
            } else if ( jQuery(this).hasClass('mcembed_listid') ) {
                listid = jQuery(this).html();
            }
        });
        
        if ( jQuery(container).data('html') == undefined || jQuery(container).data('html').form === '' ) {        
            var slider = jQuery(container).html();
            
            jQuery.ajax({
                    url: MCEmbed.ajaxurl,                
                    async: false,
                    type: 'POST',
                    cache: false,
                    data: { action: 'mcembed-get-signup-form', uid: uid, listid: listid, nonce : MCEmbed.nonce, },                
                    success: function( r ) {
                        if (r.status == 'success') {
                        
                            MCEMbed_ShowForm(container, r.response, e);
                            
                        } else {
                    alert('** Aajx call failed. User would be redirected to MailChimp hosted signup form ** ');
//                            jQuery(location).attr('href',link);
                        }
                    },
                    error: function( r ) {
                    alert('** Aajx call failed. User would be redirected to MailChimp hosted signup form ** ');
//                        jQuery(location).attr('href',link);
                    },
                }
            );
            var form = jQuery(container).html();
            
            jQuery(container).data('html', { slider: slider, form: form} );
        }
        MCEMbed_ShowForm(container, jQuery(container).data('html').form, e);
        
    });
});

function MCEMbed_ShowForm(container, code, e) {
    e.stopPropagation();
    e.preventDefault();

    jQuery(container).html(code);
}

