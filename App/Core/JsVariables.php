<?php
namespace PdfFormsLoader\Core;

use PdfFormsLoader\Core\Assets;

class JsVariables
{
    protected static $variables = [];
    const PdfFormGlobalVariables = 'PdfFormGlobalVariables';
    protected static $instance;

    public static function addVariable( $name, $value ) {
        self::$variables[$name] = $value;
        add_action('admin_enqueue_scripts', [self::getInstance(), 'localize']);
    }

    protected static function getInstance() {
        if (empty(self::$instance)) {
            return new self;
        }
        return self::$instance;
    }

    public static function addVariableFront( $name, $value ) {
        self::$variables[$name] = $value;
        $JS = new self;
        $JS->localize();
    }

    public function localize() {
        wp_register_script( self::PdfFormGlobalVariables, Assets::getJsUrlStatic('localize.js'), false );
        wp_localize_script( self::PdfFormGlobalVariables, self::PdfFormGlobalVariables, self::$variables );
        wp_enqueue_script( self::PdfFormGlobalVariables );
    }
}