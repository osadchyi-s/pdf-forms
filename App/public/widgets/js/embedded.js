jQuery(document).ready( function() {

    jQuery('.pdfform-embedded').click(function() {
        _this = jQuery(this);
        PDFfiller.init({
            client_id: _this.data('clientid'),
            url: _this.data('url'),
            width: _this.data('width'),
            height: _this.data('height')
        });
    });

});

