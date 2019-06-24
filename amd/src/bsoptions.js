/* jshint ignore:start */
define(['jquery', 'theme_boost/loader', 'core/log'], function($, bootstrap, log) {
    "use strict"; // ...jshint ;_; !!!

    log.debug('Adaptable Bootstrap AMD opt in functions');

    return {
        init: function(hasaffix) {
            $(document).ready(function($) {

                // Get the navbar, if present.
                var navbar = document.getElementById("main-navbar");

                if (hasaffix && navbar != null) {

                    // New way to handle sticky navbar requirement.
                    // Simply taken from https://www.w3schools.com/howto/howto_js_navbar_sticky.asp.
                    
                    // Initial sticky position.
                    var sticky = navbar.offsetTop;

                    // When the user scrolls the page, execute makeNavbarSticky().
                    window.onscroll = function() {makeNavbarSticky()};

                    // When the page changes size, check the sticky.
                    window.onresize = function() {checkSticky()};

                    // Changed?
                    var isSticky = (window.pageYOffset < sticky); // Initial inverse logic to cause first check to work.

                    // Check if we are already down the page because of an anchor etc.
                    makeNavbarSticky();

                    // Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
                    function makeNavbarSticky() {
                        if (sticky > 0) {
                            if (window.pageYOffset >= sticky) {
                                if (isSticky == false) {
                                    navbar.classList.add("adaptable-navbar-sticky")
                                    isSticky = true;
                                }
                            } else {
                                if (isSticky == true) {
                                    navbar.classList.remove("adaptable-navbar-sticky");
                                    isSticky = false;
                                }
                            }
                        }
                    }

                    // Adjust sticky if 0 when window resizes.
                    function checkSticky() {
                        if (sticky == 0) {
                            sticky = navbar.offsetTop;
                            isSticky = (window.pageYOffset < sticky);
                            // Check if we are already down the page because of an anchor etc.
                            makeNavbarSticky();
                        }
                    }

                }

                var screenmd = 992;

                if (window.innerWidth <= screenmd) {
                    $("#adaptable-page-header-wrapper").addClass("fixed-top");
                    $("body").addClass("page-header-margin")
                } else {
                    $("#adaptable-page-header-wrapper").removeClass("fixed-top");
                    $("body").removeClass("page-header-margin")
                }
                // if you want these classes to toggle when a desktop user shrinks the browser width to an xs width - or from xs to larger
                $(window).resize(function() {
                    if (window.innerWidth <= screenmd) {
                        $("#adaptable-page-header-wrapper").addClass("fixed-top");
                        $("body").addClass("page-header-margin")
                    } else {
                        $("#adaptable-page-header-wrapper").removeClass("fixed-top");
                        $("body").removeClass("page-header-margin")
                    }
                });

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
