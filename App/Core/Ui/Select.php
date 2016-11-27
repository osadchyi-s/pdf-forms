<?php
/**
 * Description: Fox ui-elements
 * Version: 0.1.0
 * Author: Osadchyi Serhii
 * Author URI: https://github.com/RDSergij
 *
 * @package ui_input_fox
 *
 * @since 0.1.0
 */
namespace PdfFormsLoader\Core\Ui;

use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Assets;

	/**
	 * UI-select.
	 */
	class Select {

		/**
		 * Default settings
		 *
		 * @var type array
		 */
		private $default_settings = array(
			'id'        => 'select-fox',
			'class'     => '',
			'name'      => 'select-fox',
			'options'   => array( 'true' => 'On', 'false' => 'Off' ),
			'default'   => 'true',
		);

		/**
		 * Required settings
		 *
		 * @var type array
		 */
		private $required_settings = array(
			'class'     => 'select-fox',
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
                'select-fox',
                $assets->getCssUrl( 'select.min.css', 'ui' ),
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

			$options = $this->settings['options'];
			unset( $this->settings['options'] );
			$attributes = '';
			if ( empty( $this->settings['default'] ) ) {
				$default = '';
			} else {
				$default = $this->settings['default'];
				unset( $this->settings['default'] );
			}
			foreach ( $this->settings as $key => $value ) {
				$attributes .= ' ' . $key . '="' . $value . '"';
			}

            $html = Views::render(
                'ui/select.php',
                array(
                    'attributes'  => $attributes,
                    'options' => $options,
                    'default' => $default,
                )
            );

            return $html;
		}
	}
