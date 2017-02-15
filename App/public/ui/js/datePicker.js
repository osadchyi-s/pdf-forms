(function($){
    $( document ).ready(function () {
        var count = 0;
        $( ".input-fox[date_format]" ).each(
            function (i, elem) {
                $(elem).attr('id', 'date-'+i);
                count++;
            }
        );

        for(var i=0; i<count; i++){
            $('#date-'+i).datepicker({
                dateFormat: $('#date-'+i).attr('date_format')
            });
        }
    })
})(jQuery);