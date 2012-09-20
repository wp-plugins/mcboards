// JavaScript Document
jQuery(document).ready(function($e) {
	jQuery('.mcb_item').live('mouseenter', function () {
		var o;
		o= jQuery(this).find('.mcb_item_social'); o.fadeIn('fast');
	}).live('mouseleave', function ($e) {
		var o;
		o= jQuery(this).find('.mcb_item_social'); o.fadeOut('slow');
	});
	
	jQuery('td.column-conditionals input[type=\"text\"]').live('focus', function(event) {
	    var inp = this;
	    setTimeout(function() {
	        inp.select();
	    }, 1);
	    event.preventDefault();
		return false;
	});
	
	jQuery('#save[name=save-new]').live( 'click', function (e) {
        jQuery.ajax({
            url: MCBoard.ajaxurl,
            type: 'POST',
            cache: false,
            async: false,
            data: { 
                    action: 'mcboard-board-id-exists', 
                    nonce : MCBoard.nonce, 
                    id : jQuery('#board_id').val()
                },
            success: function( r ) {
                if (r.status == 'success') {
                    if ( r.response == 1 ) {
                	    e.stopPropagation();
                	    e.preventDefault();
                	    
                    	alert(MCBoard.strBoardExists);
                    	return false;
                    } else {
                    	return true;
                    }
                } else {
                	alert(r.response);
                }
        	    e.stopPropagation();
        	    e.preventDefault();
            },
            error: function( r ) {
        	    e.stopPropagation();
        	    e.preventDefault();
            }
        });	    		
	});
	
	jQuery('span.trash a').live( 'click', function (e) {
	    e.stopPropagation();
	    e.preventDefault();
	    
	    var tr = jQuery(this).parents('tr');
	    var board_id = tr.attr('id').substring(7);
	    if ( confirm(board_id + ': ' + MCBoard.strAreYouSure) ) {
	        jQuery.ajax({
                url: MCBoard.ajaxurl,
                type: 'POST',
                cache: false,
                data: { 
                        action: 'mcboard-delete-mcboard', 
                        nonce : MCBoard.nonce, 
                        id : board_id
                    },
                success: function( r ) {
                    if (r.status == 'success') {
                        tr.fadeOut(400, function () { tr.remove(); });
                    } else {
                        alert(r.response);
                    }                    
                },
                error: function( r ) {
                        alert(r.response);
                }
            });	    		
	    	
	    }
	});
	
	jQuery('.mcb_more_campaigns').live( 'click', function (e) {
	    e.stopPropagation();
	    e.preventDefault();
	    
	    var link = jQuery(this);
        var $scid       = link.attr('id').substring(9);

        var $container  = jQuery('#' + $scid );
        var $loading  	= jQuery('#mcb_loading-' + $scid );
        $loading.html('<img src="' + MCBoard.ajax_loading + '" width="16" height="16" />');
        
        jQuery.ajax({
                url: MCBoard.ajaxurl,
                type: 'POST',
                cache: false,
                data: { 
                        action  : 'mcboard-get-more-campaigns', 
                        nonce   : MCBoard.nonce, 
                        board_id: $scid,
                        offset  : $container.find('.mcb_item').length
                    },
                success: function( r ) {
                    $loading.html('&nbsp;');
                    if (r.status == 'success') {
                        
                        var $sc_id       = r.scid;
                        var $c  = jQuery('#' + $sc_id );
                        
                        var TransparentObj = {
                        		'filter'		: 'alpha(opacity=40)',
	    					    '-moz-opacity'	: 0.40,
	    					    'opacity'		: 0.4
                	    };
                        
                        var OpaqueObj = {
                        		'filter'		: 'alpha(opacity=100)',
	    					    '-moz-opacity'	: 1,
	    					    'opacity'		: 1
                	    };
                        
                        var elem = null;
                        for (var i = 0; i < r.elements.length; i++) {
                            
                            elem = jQuery(r.elements[i]);                            
                            
                            $c.append( elem ).masonry('appended', elem, true );
                        }
                        
                        
                        if ( r.elements.length == 0 ) {
                        	jQuery('#mcb_more-' + $scid).wrap('<div></div>').parent().html('<span class="mcb_more_campaigns">' + MCBoard.strNoMoreCampaigns + '</span>');
                        }
                        
                        jQuery('#' + $sc_id + ' .mcb_item').hover( 
                        	function() {
                        		jQuery(this).siblings('.mcb_item').css(TransparentObj);
                        	},
                        	function() {
                        		jQuery(this).siblings('.mcb_item').css(OpaqueObj);
                        	}
                        );
                    } else {
                        alert(r.response);
                    }
                },
                error: function( r ) {
                	$loading.html('&nbsp;');
                    alert(r.response);
                }
            }
        );
	});
});