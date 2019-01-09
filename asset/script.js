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


});