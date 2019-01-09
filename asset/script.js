jQuery(document).ready(function ($) {

    //Accordion
    jQuery(document).on("click", ".order-accordion .title", function (e) {
        e.preventDefault();
        $(this).parent().find(".content").toggle('slow');
    });


});