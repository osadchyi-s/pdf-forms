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
	 * UI-switcher.
	 */
	class Switcher {

		/**
		 * Default settings
		 *
		 * @var type array
		 */
		private $default_settings = array(
			'id'        => 'switcher-fox',
            'label'     => '',
			'class'     => '',
			'name'      => 'switcher-fox',
			'values'    => array( 'true' => 'On', 'false' => 'Off' ),
			'value'    => 'true',
		);

		/**
		 * Required settings
		 *
		 * @var type array
		 */
		private $required_settings = array(
			'class'        => 'switcher-fox',
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
                'switcher-fox',
                $assets->getCssUrl( 'switcher.min.css', 'ui' ),
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

            $label = $this->settings['label'];

			$values = $this->settings['values'];
			$value_first = each( $values );
			$value_second = each( $values );
			if ( empty( $this->settings['value'] ) ) {
				$default_array = each( $values );
				$default = $default_array[ $key ];
			} else {
				$default = $this->settings['value'];
			}
			$name = $this->settings['name'];
			unset( $this->settings['values'], $this->settings['name'], $this->settings['value'] );
			$attributes = '';
			foreach ( $this->settings as $key => $value ) {
				$attributes .= ' ' . $key . '="' . $value . '"';
			}

            $html = Views::render(
                'ui/switcher.php',
                array(
                    'label'       => $label,
                    'values'      => $values,
                    'value_first' => $value_first,
                    'value_second'=> $value_second,
                    'default'     => $default,
                    'name'        => $name,
                    'attributes'  => $attributes,
                )
            );

            return $html;
		}
	}

