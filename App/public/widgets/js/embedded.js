/*(function( $ ) {
    jQuery('.pdfform-embedded').click(function() {
        console.log('6767');
        $_this = $(this);
        PDFfiller.init({
            client_id: $_this.data('clientId'),
            url: ' https://www.pdffiller.com/en/project/' + $_this.data('documentId') + '.htm?mode=link_to_fill',
            width: $_this.data('width'),
            height: $_this.data('height')
        });
    });
});*/

jQuery('.pdfform-embedded').click(function() {
    _this = jQuery(this);
    console.log({
        client_id: _this.data('clientid'),
        url: _this.data('url'),
        width: _this.data('width'),
        height: _this.data('height')
    });
    PDFfiller.init({
        client_id: _this.data('clientid'),
        url: _this.data('url'),
        width: _this.data('width'),
        height: _this.data('height')
    });
});

console.log(444);