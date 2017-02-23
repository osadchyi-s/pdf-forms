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
			'list'   => array(),
			'value'   => '',
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

            $label = '';
            if ( ! empty( $this->settings['label'] ) ) {
                $label = $this->settings['label'];
                unset( $this->settings['label'] );
            }

            if (!empty($this->settings['datalist']) && empty($this->settings['list'])) {
                $this->settings['list'] = $this->settings['datalist'];
            }

            if (is_string($this->settings['list'])) {
                $list = explode(',', $this->settings['list']);
                $newList = [];
                foreach($list as $key => $item) {
                    $newList[$item] = $item;
                }
                $list = $newList;
                unset($newList);
            }

            if (is_array($this->settings['list'])) {
                $list = $this->settings['list'];
            }

            unset( $this->settings['list'] );

			$attributes = '';
			if ( empty( $this->settings['value'] ) ) {
				$default = '';
			} else {
				$default = $this->settings['value'];
				unset( $this->settings['value'] );
			}

			foreach ( $this->settings as $key => $value ) {
				$attributes .= ' ' . $key . '="' . $value . '"';
			}

            $html = Views::render(
                'ui/select.php',
                array(
                    'attributes'    => $attributes,
                    'label'         => $label,
                    'list'          => $list,
                    'default'       => $default,
                )
            );

            return $html;
		}
	}
