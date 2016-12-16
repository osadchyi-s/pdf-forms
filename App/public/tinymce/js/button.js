tinymce.PluginManager.add( 'pdfforms_button', function( editor, url ) {

    PdfFormGlobalVariables.pdfforms_button.fields = _.map(PdfFormGlobalVariables.pdfforms_button.fields, function(field) {
        field.onclick = function(e) {
            var shortcodeAttr = '';
            _.each(field.fieldAttr, function(value, key) {
                shortcodeAttr = shortcodeAttr + ' ' + key + '="' + value + '"';
            }, shortcodeAttr);

            var shortcode = '[pdfformfield ' + shortcodeAttr;
            /*var list = field['list'];
            if (list.length > 0) {
                shortcode = shortcode + ' list="' + list + '"';
            }*/
            shortcode = shortcode + ']';
            console.log(shortcode);
            editor.insertContent(shortcode);
        }
        return field;
    });

    console.log(PdfFormGlobalVariables.pdfforms_button.fields);

    // Add Button to Visual Editor Toolbar
    editor.addButton('pdfforms_button', {
        title: "Insert Button Link",
        cmd: "pdfform_insert_field",
        type: "button",
        tooltip: "add form field",
        //icon: "dashicon dashicons-no",
        image: window.PdfFormGlobalVariables.pdfforms_button.image
    });

    editor.addCommand( 'pdfform_insert_field', function() {
        // Calls the pop-up modal
        editor.windowManager.open({
            // Modal settings
            title: 'Insert form field',
            width: jQuery( window ).width() * 0.3,
            // minus head and foot of dialog box
            height: (jQuery( window ).height() - 36 - 50) * 0.7,
            inline: 1,
            id: 'plugin-slug-insert-dialog',
            body: PdfFormGlobalVariables.pdfforms_button.fields,
            buttons: [
                {
                    text: 'Cancel',
                    id: 'plugin-slug-button-cancel',
                    onclick: 'close'
                }
            ],
        });
    });

});


tinymce.PluginManager.add( 'pdfforms_list_button', function( editor, url ) {
    window.PdfFormGlobalVariables.pdfforms_list_button.documents = _.map(PdfFormGlobalVariables.pdfforms_list_button.documents, function(document){
        document.onclick = function(e) {
            editor.insertContent(
                '[pdfform id="' + document.id + ' "]'
            );
        }
        return document;
    });
    //console.log(window.PdfFormGlobalVariables.pdfforms_list_button);

    // Add Button to Visual Editor Toolbar
    editor.addButton('pdfforms_list_button', {
        title: "Insert Button Link",
        cmd: "pdfform_insert_form",
        type: "button",
        tooltip: "add fillable form",
        //icon: "dashicon dashicons-no",
        image: window.PdfFormGlobalVariables.pdfforms_button.image
    });

    editor.addCommand( 'pdfform_insert_form', function() {
        // Calls the pop-up modal
        editor.windowManager.open({
            // Modal settings
            title: 'Insert form',
            width: jQuery( window ).width() * 0.4,
            // minus head and foot of dialog box
            height: (jQuery( window ).height() - 36 - 50) * 0.4,
            inline: 1,
            id: 'plugin-slug-insert-dialog',
            body: PdfFormGlobalVariables.pdfforms_list_button.documents,
            buttons: [
                {
                    text: 'Cancel',
                    id: 'plugin-slug-button-cancel',
                    onclick: 'close'
                }
            ],
        });
    });

});



