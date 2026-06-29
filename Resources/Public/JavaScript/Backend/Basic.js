define(['jquery'], function($) {
    var Basic = {};

    Basic.init = function() {
        $("*[data-toggle='modal']").click(function() {
            const index = Number($(this).attr("data-index"));
            const carousel = $(this).attr("data-target");
            $(carousel).carousel(index);
        })
    };

    Basic.init();
    return Basic;
});