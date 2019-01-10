jQuery(document).ready(function ($) {

    /* Loading */
    var wpsa_loading = `<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="36px" height="45px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve"> <rect x="0" y="9.1947" width="4" height="11.6106" fill="#333" opacity="0.2"> <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s" dur="0.6s" repeatCount="indefinite"></animate> </rect> <rect x="8" y="8.3053" width="4" height="13.3894" fill="#333" opacity="0.2"> <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.15s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite"></animate> </rect> <rect x="16" y="5.8053" width="4" height="18.3894" fill="#333" opacity="0.2"> <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.3s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite"></animate> <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite"></animate> </rect> </svg>`;
    var wpsa_btn_spinner = `<img src="` + wps_options_js.loading_img + `" class="wps_spinner_btn">`;

    /* Only Numeric Input */
    jQuery(document).on("keypress keyup blur", ".only-numeric", function (event) {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        if ((event.which != 46) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    /* Trash Actions */
    $(document).on("click", "a[data-trash]", function (e) {
        e.preventDefault();
        var href = $(this).attr('href');

        /* Show Loading Box */
        $.confirm({
            title: wps_options_js.remove_text,
            icon: 'fa fa-warning',
            rtl: parseInt(wps_options_js.is_rtl),
            closeIcon: true,
            content: wps_options_js.sure_remove,
            buttons: {
                confirm: {
                    text: wps_options_js.btn_confirm,
                    btnClass: 'btn-red',
                    action: function () {
                        window.location.href = href;
                    }
                },
                cancel: {
                    text: wps_options_js.btn_cancel,
                }
            }
        });
    });

    /* View Status dialog */
    $(document).on("click", "a[data-view-status]", function (e) {
        e.preventDefault();

        //Get Actions ID
        var ID = $(this).attr('data-view-status');
        var Type = $(this).attr('data-trigger-type');

        //Show Dialog Loading
        $.dialog({
            title: '',
            rtl: parseInt(wps_options_js.is_rtl),
            closeIcon: true,
            content: `<div class="wpsa-status-dialog"><div class="wp-loading">` + wpsa_loading + `</div></div>`,
            buttons: {},
        });

        //Ajax Request
        $.post({
            url: ajaxurl,
            dataType: "json",
            data: {
                'action': 'wps_show_actions_statistics',
                'wp_query_id': ID,
                'wp_query_type': Type,
            },
            success: function (data) {
                $(".wpsa-status-dialog").html(data.text);
            },
            error: function () {
                $(".wpsa-status-dialog").html(wps_options_js.error_ajax_statistics);
            }
        });

    });

    /* Add New Item dialog */
    $(document).on("click", "a.page-title-action", function (e) {
        e.preventDefault();

        //Show Dialog Loading
        $.dialog({
            title: wps_options_js.add_new_action,
            icon: 'fa fa-random',
            rtl: parseInt(wps_options_js.is_rtl),
            closeIcon: true,
            content: `<div class="wpsa-status-dialog"><p>` + wps_options_js.pls_enter_name + `</p><input type="text" name="action_name" autocomplete="off" placeholder="` + wps_options_js.act_name + `"><br><input type="submit" name="submit" id="add_new_actions" class="button button-primary" value="` + wps_options_js.lets_go + `"  /><div class="dialog_alert"></div></div><br>`,
            buttons: {}
        });

    });

    /* Add New Item Ajax */
    function wps_add_new_action() {
        //Get action input
        var action_name = $("input[name=action_name]").val();

        //Show Loading
        $(wpsa_btn_spinner).insertAfter('input#add_new_actions');
        $(".dialog_alert").html("");

        //Create new action
        $.post({
            url: ajaxurl,
            dataType: "json",
            data: {
                'action': 'wps_add_new_actions',
                'name': action_name,
            },
            success: function (data) {
                window.location.href = data.link;
            },
            error: function () {
                $(".dialog_alert").html('<div class="error"><i class="fa fa-exclamation-triangle"></i> ' + wps_options_js.error_ajax_statistics + '</div>');
                $("img.wps_spinner_btn").remove();
            }
        });
    }

    $(document).on("click", ".wpsa-status-dialog input#add_new_actions", function (e) {
        e.preventDefault();
        wps_add_new_action();
    });
    $(document).on("keypress", ".wpsa-status-dialog input[name=action_name]", function (e) {
        if (e.which === 13) {
            wps_add_new_action();
        }
    });

});