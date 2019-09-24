jQuery(document).on('click', '.moj-intro-notice .notice-dismiss', function () {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'popup_dismissed',

        }
    });
});
