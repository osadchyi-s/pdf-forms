<?php
namespace PdfFormsLoader\Shortcodes;

class FormsFieldsShortcode
{
    public $slug = 'pdfformfield';
    public $initMethod = 'addShortcode';

    public function addShortcode($atts) {
        $atts = shortcode_atts( array(
            'name' => 'Text_1',
            'type' => 'text'
        ), $atts );

        $type = ucfirst($this->uiMap($atts['type']));

        $uiType = 'PdfFormsLoader\\Core\Ui\\' . $type;

        if (isset($atts['options'])) {
            $atts['options'] = explode(',', $atts['options']);
        }

        if ($type == 'Input') {
            $atts['type'] = $this->typeInput($atts['type']);
        }

        $ui = new $uiType($atts);
        return $ui->output();
    }

    protected function uiMap($type) {
        $map = [
            'text' => 'input',
            'dropdown' => 'select',
            'checkbox' => 'switcher',
            'checkmark' => 'input',
        ];

        return isset($map[$type]) ? $map[$type] : $type;
    }

    protected function typeInput($type) {
        $map = [
            'text' => 'input',
            'checkmark' => 'checkbox',
        ];

        return isset($map[$type]) ? $map[$type] : $type;
    }

}