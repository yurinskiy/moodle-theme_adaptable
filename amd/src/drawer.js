/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Adaptable Drawer AMD');

    return {
        init: function() {
            $(document).ready(function($) {
                var body = $('body');
                var side = $('#drawer').attr('data-side');
                $('#drawer').click(function() {
                    var drawer = $('#nav-drawer');

                    if (drawer.hasClass('closed')) {
                        // Drawer closed -> open.
                        drawer.removeClass('closed');
                        body.addClass('drawer-open-' + side);
                        drawer.attr('aria-hidden', 'false');
                        $(this).attr('aria-expanded', 'true');
                    } else {
                        // Drawer open -> closed.
                        drawer.addClass('closed');
                        body.removeClass('drawer-open-' + side);
                        drawer.attr('aria-hidden', 'true');
                        $(this).attr('aria-expanded', 'false');
                    }
                });
                body.addClass('drawer-ease');

                // Header two message drawer height.
                if (body.hasClass("header-style2")) {
                    var height = $('#adaptable-page-header-wrapper').height();
                    $('.header-style2 [data-region=right-hand-drawer].drawer')
                        .css({'height':'calc(100% - ' + height + 'px)', 'top': height + 'px'});
                    log.debug('Header 2 height: ' + height);
                }

                log.debug('Adaptable Drawer AMD init');
            });
        }
    };
});
/* jshint ignore:end */
