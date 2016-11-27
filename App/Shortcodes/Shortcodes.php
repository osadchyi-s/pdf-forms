<?php
namespace PdfFormsLoader\Shortcodes;

class Shortcodes
{
    public static function initShortcodeItem($name) {
        $class = 'PdfFormsLoader\\Shortcodes\\' . $name.'Shortcode';
        $shortcode = new $class;
        add_shortcode($shortcode->slug, [&$shortcode, $shortcode->initMethod]);
    }

    public static function initShortcodes($names) {
        foreach($names as $name) {
            self::initShortcodeItem($name);
        }
    }
}