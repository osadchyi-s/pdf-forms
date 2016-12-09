<?php
namespace PdfFormsLoader\Shortcodes;

class FormsFieldsShortcode
{
    public $slug = 'pdfformfield';
    public $initMethod = 'addShortcode';

    public function addShortcode($atts) {
        $atts = shortcode_atts( array(
            'name' => 'Text_1',
            'type' => 'text',
            'list' => ''
        ), $atts );

        $type = ucfirst($this->uiMap($atts['type']));

        if ($type == 'Select' && !empty($atts['list'])) {
            foreach(explode(',', $atts['list']) as $optionValue) {
                $atts['options'][$optionValue] = $optionValue;
            }
        } elseif($type == 'Select') {
            $type = 'Input';
        }

        $uiType = 'PdfFormsLoader\\Core\Ui\\' . $type;

        /*if (isset($atts['options'])) {
            $atts['options'] = explode(',', $atts['options']);
        }*/

        if ($type == 'Input') {
            $atts['type'] = $this->typeInput($atts['type']);
            if ($atts['type'] == 'checkbox' && empty($atts['value'])) {
                $atts['value'] = 1;
            }
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