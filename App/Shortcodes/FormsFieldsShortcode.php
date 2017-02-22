<?php
namespace PdfFormsLoader\Shortcodes;

use PdfFormsLoader\Core\Ui\FieldsMapper;

class FormsFieldsShortcode
{
    public $slug = 'pdfformfield';
    public $initMethod = 'addShortcode';

    protected $fieldsMapper;

    public function __construct()
    {
        $this->fieldsMapper = new FieldsMapper;
    }

    public function addShortcode($atts) {
        /*$atts = shortcode_atts( array(
            'ui' => 'Input',
            'name' => 'Text_1',
            'type' => 'text',
        ), $atts );*/

        $ui = $this->fieldsMapper->getUIByAttr($atts);

        return $ui->output();
    }

}