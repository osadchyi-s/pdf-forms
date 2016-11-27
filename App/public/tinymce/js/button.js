tinymce.PluginManager.add( 'pdfforms_button', function( editor, url ) {

    PdfFormGlobalVariables.pdfforms_button.fields = _.map(PdfFormGlobalVariables.pdfforms_button.fields, function(field){
        field.onclick = function(e) {
            editor.insertContent(
                '[pdfformfield type="' + field['field-type'] + '"  name="' + field['name'] + '"]'
            );
        }
        return field;
    });

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
            width: jQuery( window ).width() * 0.2,
            // minus head and foot of dialog box
            height: (jQuery( window ).height() - 36 - 50) * 0.5,
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
            title: 'Insert form field',
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



