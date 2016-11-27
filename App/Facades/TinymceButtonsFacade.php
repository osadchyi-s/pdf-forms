<?php
namespace PdfFormsLoader\Facades;

use PdfFormsLoader\Core\Assets;

class TinymceButtonsFacade {

    protected $buttonName = 'Big Brother Watch You';
    protected $postTypes = [];
    protected $typeNow = 'post';
    protected $assets = [
        'scripts' => [
            [
                'name' => 'button',
                'file' => 'button.js',
                'parent' => ['jquery'],
                'footer' => true,
                'version' => '1.0',
            ]
        ],
    ];
    protected $params = [];

    public function __construct($params)
    {
        global $typenow;
        $this->postTypes = $typenow;

        if (!empty($params['button_name'])) {
            $this->buttonName = $params['button_name'];
        }

        if (!empty($params['post_types'])) {
            $this->postTypes = $params['post_types'];
        }

        if (!empty($params['assets'])) {
            $this->assets = $params['assets'];
        }

        $this->params = $params;
    }

    public static function buttonsFactory($params) {
        return new self($params);
    }

    public function makeButton() {
        add_action('admin_head', [$this, 'addButton']);
    }

	public function addButton() {
		if( ! in_array( $this->typeNow, $this->postTypes ) ) {
            return;
        }

        add_filter("mce_external_plugins", [$this, 'addTinymcePlugin']);
        add_filter('mce_buttons_3', [$this, 'registerButton']);
	}

	public function addTinymcePlugin($plugin_array) {
        if (!empty($this->assets['scripts'])) {
            foreach($this->assets['scripts'] as $script) {
                $plugin_array[$this->buttonName] =  Assets::getJsUrlStatic($script['file'], 'tinymce' );
            }
        }

		return $plugin_array;
	}

	public function registerButton($buttons) {
		array_push($buttons, $this->buttonName);
		return $buttons;
	}
}
