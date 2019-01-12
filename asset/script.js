jQuery(document).ready(function ($) {

    //Accordion
    jQuery(document).on("click", ".order-accordion .title", function (e) {
        e.preventDefault();
        if ($(this).find('.pull-left').html() === "+") {
            $(this).find('.pull-left').html("-");
        } else {
            $(this).find('.pull-left').html("+");
        }
        $(this).parent().find(".content").toggle('slow');
    });

    //Check User Notification
    if (typeof wps_online_js !== "undefined") {
        if (parseInt(wps_online_js.is_login_user) == 1) {
            setInterval(function () {
                jQuery.post({
                    url: wps_online_js.ajax,
                    dataType: "json",
                    cache: false,
                    data: {'action': 'check_new_notification_online_pub', 'time': wps_online_js.time},
                    success: function (data) {
                        if (data.exist == "yes") {
                            jQuery.growl.warning({
                                duration: "8000",
                                location: "br",
                                title: data.title,
                                message: data.text,
                                url: data.url
                            });
                        }
                    },
                    error: function () {
                    }
                });
            }, 9000);
        }
    }


});