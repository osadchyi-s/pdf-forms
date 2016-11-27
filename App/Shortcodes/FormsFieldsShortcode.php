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

        $uiType = 'PdfFormsLoader\\Core\Ui\\' . ucfirst($this->uiMap($atts['type']));

        if (isset($atts['options'])) {
            $atts['options'] = explode(',', $atts['options']);
        }

        $ui = new $uiType($atts);
        return $ui->output();
    }

    protected function uiMap($type) {
        $map = [
            'text' => 'input',
            'dropdown' => 'select',
            'checkbox' => 'switcher',
        ];

        return isset($map[$type]) ? $map[$type] : $type;
    }

}