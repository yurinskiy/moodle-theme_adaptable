/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    "use strict"; // ...jshint ;_; !!!

    log.debug('Adaptable AMD Zoom in');

    return {
        init: function() {
            $(document).ready(function($) {
                log.debug('Adaptable AMD Zoom in init');

                var sidePost = $('#block-region-side-post');
                var zoomInIcon = $('#zoominicon i.fa');
                var body = $('body');
                var zoomLeft = false;
                if ($('#zoominicon').hasClass('left')) {
                    zoomLeft = true;
                }

                if (typeof sidePost != 'undefined') {
                    $('#zoominicon').click(function() {
                        if (body.hasClass('zoomin') ) { // Blocks not shown.
                            body.removeClass('zoomin');
                            if (zoomLeft) {
                                zoomInIcon.removeClass('fa-angle-right');
                                zoomInIcon.addClass('fa-angle-left');
                            } else {
                                zoomInIcon.removeClass('fa-angle-left');
                                zoomInIcon.addClass('fa-angle-right');
                            }
                            M.util.set_user_preference('theme_adaptable_zoom', 'nozoom');
                        } else {
                            body.addClass('zoomin');
                            if (zoomLeft) {
                                zoomInIcon.removeClass('fa-angle-left');
                                zoomInIcon.addClass('fa-angle-right');
                            } else {
                                zoomInIcon.removeClass('fa-angle-right');
                                zoomInIcon.addClass('fa-angle-left');
                            }
                            M.util.set_user_preference('theme_adaptable_zoom', 'zoomin');
                        }
                    });
                }
            });
        }
    };
});
/* jshint ignore:end */
