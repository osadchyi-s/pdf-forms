<?php
namespace PdfFormsLoader\Facades;

class PostTypesFacade {

    /**
     *Post type slug
     *
     * @var null
     */
    private $slug = null;

    /**
     * Post type arguments
     *
     * @var null
     */
    private $args = null;

    /**
     * The registered custom post type.
     *
     * @var Object|\WP_Error
     */
    private $post_type;

    /**
     * Font awesome icon name.
     *
     * @var null
     */
    private $icon = null;

	/**
	 * Default post type arguments
	 *
	 * @var null
	 */
	private $defaults = null;

	/**
	 * Created popst types list
	 *
	 * @var array
	 */
	public static $created_post_types = array();

	/**
	 * Cherry_Post_Type class constructor
	 */
	public function __construct( $slug = '', $args = array() ) {
		$this->slug = $slug;
		$this->defaults = $args;
	}

	public static function createPostType( $slug, $plural, $singular, $args = array() ) {
		$postTypesFacade = new self;
		$postTypesFacade->create( $slug, $plural, $singular, $args );
	}

	/**
	 * Create new Post Type.
	 *
	 * @param [type] $slug The post type slug name.
	 * @param [type] $plural The post type plural name for display.
	 * @param [type] $singular The post type singular name for display.
	 * @param array  $args The custom post type arguments.
	 * @throws Exception Invalid custom post type parameter.
	 * @return Cherry_Post_Type
	 */
	public function create( $slug, $plural, $singular, $args = array() ) {
        $this->slug = $slug;

		// Set main properties.
		$this->defaults      = array_merge(
            $this->defaults,
			$this->getDefaultArguments( $plural, $singular )
		);
		$this->args = array_merge( $this->defaults, $args );

        // Register post type
        add_action( 'init', array( &$this, 'register' ) );

		return $this;
	}

	/**
	 * Get the custom post type default arguments.
	 *
	 * @param [type] $plural The post type plural display name.
	 * @param [type] $singular The post type singular display name.
	 * @return array
	 */
	private function getDefaultArguments($plural, $singular ) {
		$labels = array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New '. $singular,
			'edit_item'          => 'Edit '. $singular,
			'new_item'           => 'New ' . $singular,
			'all_items'          => 'All ' . $plural,
			'view_item'          => 'View ' . $singular,
			'search_items'       => 'Search ' . $singular,
			'not_found'          => 'No '. $singular .' found',
			'not_found_in_trash' => 'No '. $singular .' found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => $plural,
		);

		$defaults = array(
			'label' 		=> $plural,
			'labels' 		=> $labels,
			'description'	=> '',
			'public'		=> true,
			'menu_position'	=> 10,
			'has_archive'	=> true,
		);

		return $defaults;
	}

    /**
     * Triggered by the 'init' action event.
     * Register a WordPress custom post type.
     *
     * @return void
     */
    public function register() {
        $this->post_type = register_post_type(
            $this->slug,
            $this->args
        );
    }

    /**
     * Add font awesome icon to menu
     *
     * @param  [type] $icon font awesome icon code.
     * @return boolen true if succes | false if not.
     */
    public function fontAwesomeIcon($icon = '' ) {
        if ( '' === $icon ) {
            return false;
        }

        $this->icon = $icon;

        add_action( 'admin_enqueue_scripts', array( &$this, 'loadFontAwesome') );

        return true;
    }

    /**
     * Load font awesome fonts to admin menu.
     *
     * @return void
     */
    public function loadFontAwesome() {
        wp_enqueue_style(
            'font-awesome',
            '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'
        );

        ?>
        <style type='text/css' media='screen'>
            #adminmenu .menu-icon-<?php echo $this->slug; ?> div.wp-menu-image:before {
                font-family: Fontawesome !important;
                content: '\<?php echo $this->icon; ?>';
            }
        </style>
        <?php
    }
}
