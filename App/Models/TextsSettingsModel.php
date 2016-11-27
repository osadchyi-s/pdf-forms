<?php
namespace PdfFormsLoader\Models;

class TextsSettingsModel
{
    public static $settings;

    public static function getSettings() {
        $settings = get_option('pdfforms-text-settings');
        self::setSettingsCache($settings);
        return $settings;
    }

    public static function getSubmitMesage() {
        return self::getItemSetting('submit-message');
    }

    public static function getSuccessMesage() {
        return self::getItemSetting('message-success');
    }

    public static function getFailMesage() {
        return self::getItemSetting('fail-success');
    }

    public static function getItemSetting($key) {
        $settings = get_option('pdfforms-text-settings');
        $value = !empty($settings[$key]) ? $settings[$key] : '';
        self::setSettingItemCache($key, $value);
        return $value;
    }

    protected static function setSettingsCache($settings) {
        self::$settings = $settings;
    }

    protected static function setSettingItemCache($key, $value) {
        self::$settings[$key] = $value;
    }

    public static function getSettingItemCache($key) {
        return empty(self::$settings[$key]) ? self::getItemSetting($key) : self::$settings[$key];
    }
}