/* jshint ignore:start */
define(['jquery', 'theme_bootstrapbase/bootstrap', 'core/log'], function($, bootstrap, log) {

    "use strict"; // ...jshint ;_; !!!

    log.debug('Adaptable Bootstrap AMD opt in functions');

    return {
        init: function(hasaffix) {
            $(document).ready(function($) {
                if (hasaffix) {
                    // Check that #navwrap actually exists.
                    if($("#navwrap").length > 0) {
                        $('#navwrap').affix({
                            'offset': { top: $('#navwrap').offset().top}
                        });
                    }
                }
                $('#openoverlaymenu').click(function() {
                    $('#conditionalmenu').toggleClass('open');
                });
                $('#overlaymenuclose').click(function() {
                    $('#conditionalmenu').toggleClass('open');
                });

                // Bootstrap sub-menu functionality.
                // See: https://bootstrapthemes.co/demo/resource/bootstrap-4-multi-dropdown-hover-navbar/.
                //
                $( '.dropdown-menu a.dropdown-toggle' ).on( 'click', function ( e ) {
                    var $el = $( this );
                    var $parent = $( this ).offsetParent( ".dropdown-menu" );
                    if ( !$( this ).next().hasClass( 'show' ) ) {
                        $( this ).parents( '.dropdown-menu' ).first().find( '.show' ).removeClass( "show" );
                    }
                    var $subMenu = $( this ).next( ".dropdown-menu" );
                    $subMenu.toggleClass( 'show' );
                    
                    $( this ).parent( "li" ).toggleClass( 'show' );

                    $( this ).parents( 'li.nav-item.dropdown.show' ).on( 'hidden.bs.dropdown', function ( e ) {
                        $( '.dropdown-menu .show' ).removeClass( "show" );
                    } );
                    
                     if ( !$parent.parent().hasClass( 'navbar-nav' ) ) {
                        $el.next().css( { "top": $el[0].offsetTop, "left": $parent.outerWidth() - 4 } );
                    }

                    return false;
                } );
            
            
            });

            // Conditional javascript to resolve anchor link clicking issue with sticky navbar.
            // in old bootstrap version. Re: issue #919.
            // Original issue / solution discussion here: https://github.com/twbs/bootstrap/issues/1768.
            if (hasaffix) {
                var shiftWindow = function() { scrollBy(0, -50) };
                if (location.hash) {
                    shiftWindow();
                }
                window.addEventListener("hashchange", shiftWindow);
            }
        }
    };
});
/* jshint ignore:end */
