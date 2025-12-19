(function($) {

    // Notice Hide
    $('body').on('click', '.adminify-admin-columns-upgrade-popup .popup-dismiss', function(evt) {
        evt.preventDefault();
        $(this).closest('.adminify-admin-columns-upgrade-popup').fadeOut(200);
    });

    // Notice Show
    $('body').on('click', '.disabled', function(evt) {
        evt.preventDefault();
        $('.adminify-admin-columns-upgrade-popup').fadeIn(200);
    });

    // Install WP Adminify Plugin
    $("body").on("click", ".install-adminify-adminify-admin-columns-now .install-now", function (e) {
        e.preventDefault();
        if (!$(this).hasClass("updating-message")) {
            let plugin = $(this).attr("data-plugin");
            installAdminifyPlugin($(this), plugin);
        }
    });
    
    function installAdminifyPlugin(element, plugin) {
        element.addClass("updating-message");
        element.text("Installing...");
        jQuery.ajax({
            url: ADMINCOLUMNSCORE.admin_ajax,
            type: "POST",
            data: {
                action: "jlt_admin_columns_install_plugin",
                type: "install",
                plugin: plugin,
                nonce: ADMINCOLUMNSCORE.recommended_nonce,
            },
            success: function (response) {
                console.log("response", response);

                setTimeout(() => {
                    element.removeClass("updating-message");
                    element.text("Activated");
                }, 1000);
            },
        });
    }
    

    // Tab
    $('.filter-links').on('click', 'a', function(e) {
        e.preventDefault();
        let cls = $(this).data('type');
        $(this).addClass('current').parent().siblings().find('a').removeClass('current');
        $('#the-list .plugin-card').each(function(i, el) {
            if (cls == 'all') {
                $(this).removeClass('hide');
            } else {
                if ($(this).hasClass(cls)) {
                    $(this).removeClass('hide');
                } else {
                    $(this).addClass('hide');
                }
            }
        });
    });

    // Search
    $('.adminify-admin-columns-search-plugins #search-plugins').on('keyup', function() {
        var value = $(this).val();
        var srch = new RegExp(value, "i");
        $('#the-list .plugin-card').each(function() {
            var $this = $(this);
            if (!($this.find('.name h3 a, .desc p').text().search(srch) >= 0)) {
                $this.addClass('hide');
            }
            if (($this.find('.name h3 a, .desc p').text().search(srch) >= 0)) {
                $this.removeClass('hide');
            }
        });
    });
})(jQuery);


