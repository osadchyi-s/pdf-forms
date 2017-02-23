<?php
namespace PdfFormsLoader\Core\Ui;

use PdfFormsLoader\Core\Ui\Input;

class FieldsMapper
{
    const DEFAULT_UI = 'Input';
    const DEFAULT_INPUT_TYPE = 'text';

    public function getUIByAttr($attr) {
        try {
            $uiClass = 'PdfFormsLoader\\Core\\Ui\\' . ucfirst($attr['ui']);
            unset($attr['ui']);
            $ui = new $uiClass($attr);
        } catch(\Exception $e) {
            $ui = new Input($attr);
        }

        return $ui;
    }

    public function prepareShortCodeAttr($fillableField) {

        $fillableField = (array) $fillableField;

        $list = [];
        if (!empty($fillableField['list']) && is_array($fillableField['list'])) {
            foreach($fillableField['list'] as $item) {
                $list[$item] = $item;
            }
        }

        $format = Null;
        if (isset($fillableField['format']) and $fillableField['type'] == 'date') {
            $fillableField['format'] = mb_strtolower($fillableField['format']);
            $format = str_replace('yyyy', 'yy', $fillableField['format'] );
            $fillableField['type'] = 'text';
        }
        
        $field = [
            'ui' => $this->uiMap($fillableField['type']),
            'type' => $fillableField['type'],
            'name' => $fillableField['name'],
            'date_format' => $format,
            'list' => implode(',', $list),
            'required' => $fillableField['required'],
            'placeholder' => array_get($fillableField, 'initial', null),
            'value' => array_get($fillableField, 'initial', null),
        ];

        /*if (array_has($fillableField, 'radioGroup')) {
            $field['name'] = $fillableField['radioGroup'] . '[' . $field['name'] . ']';
        }*/ // #TODO Вирішити проблему з radio

        if (array_has($fillableField, 'list')) {
            $list = [];
            foreach($fillableField['list'] as $item) {
                $list[] = $item;
            }
            $field['list'] = $list;
        }

        if (array_has($fillableField, 'allowCustomText')) {
            $field['type'] = 'text';
            $field['ui'] = 'input';
            if (array_has($fillableField, 'list')) {
                $field['datalist'] =  $field['list'];
                //unset($field['list']);
            }
        }

        $field['type'] = $this->typeInput($field['type']);

        $field = array_filter($field);

        return (object) $field;
    }

    protected function uiMap($type) {
        $map = [
            'text' => 'input',
            'number' => 'input',
            'date' => 'input',
            'dropdown' => 'select',
            'checkmark' => 'switcher',
        ];

        return isset($map[$type]) ? $map[$type] : self::DEFAULT_UI;
    }

    protected function typeInput($type) {
        $map = [
            'text' => 'text',
            'checkmark' => 'checkbox',
            'number' => 'number',
            'date' => 'date',
        ];

        return isset($map[$type]) ? $map[$type] : self::DEFAULT_INPUT_TYPE;
    }
}