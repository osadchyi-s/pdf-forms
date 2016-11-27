<?php
namespace PdfFormsLoader\Core;

class Views
{

    private $viewPath;

    public function __construct()
    {
        $this->viewPath = plugin_dir_path( dirname(__FILE__) ) . 'views\\';
        $this->viewPath = str_replace('/', '\\',  $this->viewPath);
        return $this;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Render view
     *
     * @param  string $path View path.
     * @param  array  $data Include data.
     * @return string       Rendered html.
     */
    public static function render( $path, array $data = array() ) {
        // Add parameters to temporary query variable.
        if ( array_key_exists( 'wp_query', $GLOBALS ) ) {
            if ( is_array( $GLOBALS['wp_query']->query_vars ) ) {
                $GLOBALS['wp_query']->query_vars['__data'] = $data;
            }
        }

        $path = (new self)->getViewPath() . $path;

        ob_start();
        load_template( $path, false );
        $result = ltrim( ob_get_clean() );
        /**
         * Remove temporary wp query variable
         * Yeah. I'm paranoic.
         */
        if ( array_key_exists( 'wp_query', $GLOBALS ) ) {
            if ( is_array( $GLOBALS['wp_query']->query_vars ) ) {
                unset( $GLOBALS['wp_query']->query_vars['__data'] );
            }
        }
        // Return the compiled view and terminate the output buffer.
        return $result;
    }
}