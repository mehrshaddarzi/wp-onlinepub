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

            $.growl.warning({ title: "پیام جدید", message: "شما یک پیام جدید دارید", url: "http://googgle.com" });

        }
    }


});