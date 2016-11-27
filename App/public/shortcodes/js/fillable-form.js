(function($){

$('.pdfform-form').on('click', function(event) {
    event.preventDefault();
    $_messages = $(this).children('.pdfform-messages');
    $.post(window.PdfFormGlobalVariables.PdfformFillableForm.adminAjaxUrl, $(this).serialize(), function() {
        $_messages.html(window.PdfFormGlobalVariables.PdfformFillableForm.messageSuccess).show('slow').delay(8000).fadeOut();
    }).fail( function() {
        $_messages.html(window.PdfformFillableForm.PdfFormGlobalVariables.messageFail).show('slow').delay(8000).fadeOut();
    });
});

})(jQuery);