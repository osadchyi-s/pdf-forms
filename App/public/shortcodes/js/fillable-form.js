(function($){

    $('.pdfform-form').on('submit', function(event) {
        event.preventDefault();
        $_messages = $(this).children('.pdfform-messages');
        $_messages.append( "Loading..." );
        $.post(window.PdfFormGlobalVariables.PdfformFillableForm.adminAjaxUrl, $(this).serialize(), function(response, status) {
            if (!window.PdfFormGlobalVariables.PdfformFillableForm.messageSuccess) {
                window.PdfFormGlobalVariables.PdfformFillableForm.messageSuccess = 'Success';
            }
            $_messages.html(window.PdfFormGlobalVariables.PdfformFillableForm.messageSuccess).show('slow').delay(8000).fadeOut();
        }).fail( function(response, status) {
            if (!window.PdfFormGlobalVariables.PdfformFillableForm.messageFail) {
                window.PdfFormGlobalVariables.PdfformFillableForm.messageFail = 'Fail';
            }
            $_messages.html(window.PdfFormGlobalVariables.PdfformFillableForm.messageFail).show('slow').delay(8000).fadeOut();
        });
    });

})(jQuery);