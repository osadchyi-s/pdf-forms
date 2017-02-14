
jQuery( ".input-fox[date_format]" ).each(
    function (i, elem) {
        jQuery(elem).datepicker({
            dateFormat: jQuery(elem).attr('date_format')
        });
    }
);

