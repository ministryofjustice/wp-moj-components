jQuery(function ($) {
    function mojQString(key, value)
    {
        var params = new URLSearchParams(window.location.search);

        if (!value && params.has(key)) {
            return params.get(key);
        }

        if (!key) {
            return false;
        }

        params.set(key, value);
        if (!window.history) {
            /* shhh */
        } else {
            window.history.replaceState({}, '', `${location.pathname}?${params}`);
        }
    }

    function setTab(tab)
    {
        var tabId;

        if (!tab) {
            tab = $('.nav-tab-wrapper a').eq(0);
        } else {
            tab = $(".nav-tab-wrapper a[href='" + tab +"']");
        }

        if (!tab.attr('href')) {
            tab = $('.nav-tab-wrapper a').eq(0);
        }

        tabId = tab.attr('href').split('#')[1];

        tab.parent().find('a').removeClass('nav-tab-active');
        tab.addClass('nav-tab-active');

        $('.moj-component-settings-section').hide();
        $('div#' + tabId).fadeIn();

        //add to query string
        mojQString('moj-tab', tabId);

        return false;
    }

    // only run JS on our settings page
    if ($('.settings_page_mojComponentSettings').length > 0) {
        $('.nav-tab-wrapper').on('click', 'a', function (e) {
            e.preventDefault();

            console.log($(this).attr('href'));
            setTab($(this).attr('href'));
            return false;
        });

        // set the tab
        var mojTabSelected = mojQString('moj-tab');

        if (mojTabSelected) {
            setTab('#' + mojTabSelected);
        } else {
            setTab();
        }
    }
});
