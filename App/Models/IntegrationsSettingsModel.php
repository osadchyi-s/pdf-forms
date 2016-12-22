<?php
namespace PdfFormsLoader\Models;

class IntegrationsSettingsModel
{
    public static function getSettings() {
        $settings = get_option('pdfforms-main-integrations');
        return $settings;
    }

    public static function getCF7Setting() {
        $settings = self::getSettings();
        return $settings['contact-7-form'];
    }
}