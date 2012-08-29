jQuery(document).bind( 'ready', function() {
	jQuery('.mcembed_side').live( 'mouseenter', function() {
        if ( !jQuery(this).hasClass('dimmed') ) {
            jQuery(this).css('cursor','pointer');
        }
    }).mouseout( function() { 
        jQuery(this).css('cursor','auto');
    });
    
    jQuery('.mcembed_larrow').live( 'click', function () {
        var curItem = null;
        var prevItem = null;
        var larrow = jQuery(this);
        var rarrow = jQuery(larrow).siblings().filter('.mcembed_rarrow');
        
        jQuery(larrow).siblings().find('.mcembed_campaigns li').each( function (index) {
        	curItem = jQuery(this);
            if ( jQuery(curItem).css('display') !== 'none' ) {
            	prevItem = jQuery(curItem).prev();

                if ( jQuery(prevItem).length ) {
                    jQuery(rarrow).removeClass('dimmed');

                    if ( jQuery(prevItem).prev().length == 0 ) {
                        jQuery(larrow).addClass('dimmed').css('cursor','auto');
                    }

                    jQuery(curItem).fadeOut( 'fast', function() {
                        jQuery(curItem).hide();
                        jQuery(prevItem).fadeIn('fast', function () {});
                    });
                }
                return false;
            }
        });
    });
    

    jQuery('.mcembed_rarrow').live( 'click', function () {
        var curItem = null;
        var nextItem = null;
        var rarrow = jQuery(this);
        var larrow = jQuery(rarrow).siblings().filter('.mcembed_larrow');
        
        jQuery(rarrow).siblings().find('.mcembed_campaigns li').each( function (index) {
        	curItem = jQuery(this);
            if ( jQuery(curItem).css('display') !== 'none' ) {
            	nextItem = jQuery(curItem).next();

                if ( jQuery(nextItem).length ) {
                    jQuery(larrow).removeClass('dimmed');

                    if ( jQuery(nextItem).next().length == 0 ) {
                        jQuery(rarrow).addClass('dimmed').css('cursor','auto');
                    }

                    jQuery(curItem).fadeOut( 'fast', function() {
                        jQuery(curItem).hide();
                        jQuery(nextItem).fadeIn('fast', function () {});
                    });
                }
                return false;
            }
        });
    });
    
    jQuery('.mcembed_campaigns').each( function (index) { jQuery(this).find('li').first().show().siblings().hide(); });
    
    jQuery('.mcembed_larrow').addClass('dimmed').css('cursor','auto');
    if ( jQuery('.mcembed_campaigns li').length == 1 ) {
        jQuery('.mcembed_rarrow').addClass('dimmed').css('cursor','auto');
    }
});

