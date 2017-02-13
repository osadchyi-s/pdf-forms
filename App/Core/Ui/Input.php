<?php
namespace PdfFormsLoader\Core\Ui;

/**
 * Description: Fox ui-elements
 * Author: Osadchyi Serhii
 * Author URI: https://github.com/RDSergij
 *
 * @package ui_input_fox
 *
 * @since 0.2.1
 */

use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Assets;

	/**
	 * UI-input.
	 */
	class Input {

		const DATE = 'PdffillerDate';

		/**
		 * Default settings
		 *
		 * @var type array
		 */
		private $default_settings = array(
			'id'				=> 'input-fox',
			'class'				=> '',
			'type'				=> 'text',
			'name'				=> 'input-fox',
			'value'				=> '',
			'placeholder'		=> 'enter string',
			'list'			=> null,
		);

		/**
		 * Required settings
		 *
		 * @var type array
		 */
		private $required_settings = array(
			'class'				=> 'input-fox',
		);

		/**
		 * Settings
		 *
		 * @var type array
		 */
		public $settings;

		/**
		 * Init base settings
		 */
		public function __construct( $attr = null ) {
			if ( empty( $attr ) || ! is_array( $attr ) ) {
				$attr = $this->default_settings;
			} else {
				foreach ( $this->default_settings as $key => $value ) {
					if ( empty( $attr[ $key ] ) ) {
						$attr[ $key ] = $this->default_settings[ $key ];
					}
				}
			}

			$this->settings = $attr;
		}

		/**
		 * Add styles
		 */
		private function assets() {
            $assets = new Assets();

            wp_enqueue_style(
                'input-fox',
                $assets->getCssUrl( 'input.min.css', 'ui' ),
                array(),
                '1.0.0',
                'all'
            );
		}

		/**
		 * Render html
		 *
		 * @return string
		 */
		public function output() {
			$this->assets();
			foreach ( $this->required_settings as $key => $value ) {
				$this->settings[ $key ] = empty( $this->settings[ $key ] ) ? $value : $this->settings[ $key ] . ' ' . $value;
			}

            $label = '';
			if ( ! empty( $this->settings['label'] ) ) {
				$label = $this->settings['label'];
				unset( $this->settings['label'] );
			}

            $datalist = [];
			if ( ! empty( $this->settings['list'] ) ) {
                $this->settings['datalist'] = $this->settings['list'];
                $datalist = explode(',', $this->settings['list']);
				unset( $this->settings['list'] );
			}

			$datalist_id = $this->settings['id'] . '-datalist';

			if(isset($this->settings['type']) && $this->settings['type'] == 'number') {
				$this->settings['type'] = 'text';
				$this->settings['pattern'] = '[/#/$/%/(/)/+/=/-/.///%/:/,0-9]+';
			}

			if(isset($this->settings['type']) && $this->settings['type'] == 'date') {
				$this->settings['name'] = self::DATE . $this->settings['name'];
			}

			$attributes = '';
			foreach ( $this->settings as $key => $value ) {
				$attributes .= ' ' . $key . '="' . $value . '"';
			}

            $html = Views::render(
                'ui/input.php',
                array(
                    'label'         => $label,
                    'datalist'      => $datalist,
                    'datalist_id'   => $datalist_id,
                    'attributes'    => $attributes,
                )
            );

            return $html;
		}
	}
