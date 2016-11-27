<?php
namespace PdfFormsLoader\Facades;

use PdfFormsLoader\Core\Assets;
use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Ui\Input;
use PdfFormsLoader\Core\Ui\Select;
use PdfFormsLoader\Core\Ui\Switcher;

/**
 * Create options page
 */
class PageBuilderFacade {

	/**
	 * Module arguments
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Page data
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * The page properties.
	 *
	 * @var DataContainer
	 */
	public $views;

	/**
	 * The page sections.
	 *
	 * @var array
	 */
	protected $sections;

	/**
	 * The page settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor for the module
	 */
	function __construct( $args = array() ) {

		$this->args = wp_parse_args(
			$args,
			array(
				'capability'    => 'manage_options',
				'position'      => 20,
				'icon'          => 'dashicons-admin-site',
				'sections'      => array(),
				'settings'      => array(),
				'before'        => '',
				'after'         => '',
				'before_button' => '',
				'after_button'  => '',
			)
		);

		$this->views = '/page-builder/';
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        return $this;
	}

	public static function makePageMenu( $slug, $title, $parent = null ) {
        return (new PageBuilderFacade())->make($slug, $title, $parent);
    }

	/**
	 * Set base data of page
	 *
	 * @param type string $slug        The page slug name.
	 * @param type string $title       The page display title.
	 * @param type string $parent       The parent's page slug if a subpage.
	 *
	 * @return object
	 */
	public function make( $slug, $title, $parent = null ) {

		// Set the page properties.
		$this->data['slug']   = $slug;
        $this->data['title']  = $title;
        $this->data['parent'] = $parent;
        $this->data['args']   = array(
			'capability' => 'manage_options',
			'icon'       => '',
			'position'   => null,
			'tabs'       => true,
			'menu'       => $title,
		);
        $this->data['rules'] = array();

		return $this;
	}

	/**
	 * Set the custom page. Allow user to override
	 * the default page properties and add its own
	 * properties.
	 *
	 * @param array $params      Base parameter.
	 * @return object
	 */
	public function set( array $params = array() ) {
		$this->args = $params;

		$this->addSections( $params['sections'] );
		$this->addSettings( $params['settings'] );

		add_action( 'admin_menu', array( $this, 'build' ) );

		return $this;
	}

	/**
	 * Triggered by the 'admin_menu' action event.
	 * Register/display the custom page in the WordPress admin.
	 *
	 * @return void
	 */
	public function build() {
        if ( ! is_null( $this->data['parent'] ) ) {
			add_submenu_page( $this->data['parent'], $this->data['title'], $this->data['args']['menu'], $this->data['args']['capability'], $this->data['slug'], array( $this, 'render' ) );
		} else {
			add_menu_page( $this->data['title'], $this->data['args']['menu'], $this->data['args']['capability'], $this->data['slug'], array( $this, 'render' ), $this->data['args']['icon'], $this->args['position'] );
		}
	}

	/**
	 * Triggered by the 'add_menu_page' or 'add_submenu_page'.
	 *
	 * @return void
	 */
	public function render() {
		$title         = ! empty( $this->data['title'] ) ? $this->data['title'] : '';
		$page_slug     = ! empty( $this->data['slug'] ) ? $this->data['slug'] : '';
		$page_before   = ! empty( $this->args['before'] ) ? $this->args['before'] : '';
		$page_after    = ! empty( $this->args['after'] ) ? $this->args['after'] : '';
		$button_before = ! empty( $this->args['button_before'] ) ? $this->args['button_before'] : '';
		$button_after  = ! empty( $this->args['button_after'] ) ? $this->args['button_after'] : '';
		$sections      = ( ! empty( $this->sections ) && is_array( $this->sections ) ) ? $this->sections : array();

		$html = Views::render(
			$this->views . 'page.php',
			array(
				'title'         => $title,
				'page_slug'     => $page_slug,
				'page_before'   => $page_before,
				'page_after'    => $page_after,
				'button_before' => $button_before,
				'button_after'  => $button_after,
				'sections'      => $sections,
			)
		);

		echo $html;
	}

	/**
	 * Add custom sections for your settings.
	 *
	 * @param array $sections    List of sections.
	 * @return void
	 */
	public function addSections(array $sections = array() ) {
		$this->sections = $sections;
	}

	/**
	 * Check if the page has sections.
	 *
	 * @return bool
	 */
	public function hasSections() {
		return count( $this->sections ) ? true : false;
	}

	/**
	 * Check if the page has settings.
	 *
	 * @return bool
	 */
	public function has_settings() {
		return (bool) count( $this->settings );
	}

	/**
	 * Add settings to the page. Define settings per section
	 * by setting the 'key' name equal to a registered section and
	 * pass it an array of 'settings' fields.
	 *
	 * @param array $settings The page settings.
	 * @return object
	 */
	public function addSettings(array $settings = array() ) {
		$this->settings = $settings;

		add_action( 'admin_init', array( $this, 'installSettings') );

		return $this;
	}

	/**
	 * Triggered by the 'admin_init' action.
	 * Perform the WordPress settings API.
	 *
	 * @return void
	 */
	public function installSettings() {
		if ( $this->hasSections() ) {
			foreach ( $this->sections as $section ) {
				if ( false === get_option( $section['slug'] ) ) {
					add_option( $section['slug'] );
				}
				add_settings_section( $section['slug'], $section['name'], array( $this, 'displaySections' ), $section['slug'] );
			}
		}

		if ( $this->has_settings() ) {
			foreach ( $this->settings as $section => $settings ) {
				foreach ( $settings as &$setting ) {
					$setting['section'] = $section;
					add_settings_field( $setting['slug'], $setting['title'], array( $this, 'displaySettings' ), $section, $section, $setting );
				}
				register_setting( $section, $section );
			}
		}
	}

	/**
	 * Clear sections
	 */
	public function clearSections() {
		if ( $this->hasSections() ) {
			foreach ( $this->sections as $section ) {
				delete_option( $section['slug'] );
			}
		}
	}

	/**
	 * Handle section display of the Settings API.
	 *
	 * @param array $args     Page parameter.
	 * @return void
	 */
	public function displaySections(array $args ) {
		$description = '';
		if ( ! empty( $this->sections[ $args['id'] ] ) ) {
			if ( ! empty( $this->sections[ $args['id'] ]['description'] ) ) {
				$description = $this->sections[ $args['id'] ]['description'];
			}
		}

        $html = Views::render(
            $this->views . 'section.php',
            array(
                'description' => $description,
            )
        );

        echo $html;
	}

	/**
	 * Handle setting display of the Settings API.
	 *
	 * @param array $setting     Fields setting.
	 * @return void
	 */
	public function displaySettings($setting ) {

		// Check if a registered value exists.
		$value = get_option( $setting['section'] );

		if ( isset( $value[ $setting['slug'] ] ) ) {
			$setting['field']['value'] = $value[ $setting['slug'] ];
		} else {
			$setting['field']['value'] = '';
		}

		// Set the name attribute.
		$setting['field']['name'] = $setting['section'] . '[' . $setting['slug'] . ']';

		if ( isset( $setting['custom_callback'] ) && is_callable( $setting['custom_callback'] ) ) {
			echo call_user_func( $setting['custom_callback'], $setting['field'] );

		} else {
			$ui_class = 'PdfFormsLoader\\Core\\Ui\\' . ucfirst( $setting['type'] );
			$ui_element = new $ui_class( $setting['field'] );

			// Display the field.
			echo $ui_element->output();
		}
	}

	/**
	 * Add styles and scripts
	 *
	 * @return void
	 */
	public function assets() {

	    $assets = new Assets();

		wp_enqueue_script( 'jquery-form' );

		wp_localize_script( 'pdfforms-settings-page', 'PDFFormsMessages', array(
			'success' => 'Successfully',
			'failed' => 'Failed',
		) );

		wp_enqueue_script(
			'pdfforms-settings-page',
            $assets->getJsUrl( 'page-settings.js', 'page-builder' ),
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'pdfforms-settings-page',
            $assets->getCssUrl( 'min/page-settings.min.css', 'page-builder' ),
			array(),
            '1.0.0',
			'all'
		);
	}
}
