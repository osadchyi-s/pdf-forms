(function($){
    $('.pdfform-form').on('submit', function(event) {
        event.preventDefault();
        $_messages = $(this).children('.pdfform-messages');
        $_messages.empty().append( "Loading..." ).show().css({
            "position": "absolute",
            "margin-top":"-25px"
        });
        $('.pdfform-form input[type="submit"]').attr('disabled', 'disabled');
        $.post(window.PdfFormGlobalVariables.PdfformFillableForm.adminAjaxUrl, $(this).serialize(), function(response, status) {
            $('.pdfform-form input[type="submit"]').removeAttr('disabled');
            $('.pdfform-form')[0].reset();
            $_messages.html(window.PdfFormGlobalVariables.PdfformFillableForm.messageSuccess).show('slow').delay(8000).fadeOut();
        }).fail( function(response, status) {
            $('.pdfform-form input[type="submit"]').removeAttr('disabled');
            $_messages.html(window.PdfFormGlobalVariables.PdfformFillableForm.messageFail).show('slow').delay(8000).fadeOut();
        });
    });

})(jQuery);