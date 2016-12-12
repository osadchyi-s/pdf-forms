<?php
namespace PdfFormsLoader\Widgets;

use PdfFormsLoader\Core\Ui\Input;
use PdfFormsLoader\Core\Ui\Select;
use PdfFormsLoader\Core\Views;

class PdfFormWidget extends \WP_Widget
{
    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'pdfform_widget', // Base ID
            esc_html__( 'PDFForm widget', 'pdfforms' ), // Name
            array( 'description' => esc_html__( 'Show fillable form template', 'pdfforms' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        $postIdActive = ! empty( $instance['postId'] ) ? $instance['postId'] : '';

        echo Views::render(
            'widgets/pdfform/frontend.php',
            array(
                'id'  => $postIdActive,
                'title'  => $instance['title'],
                'beforeWidget' => $args['before_widget'],
                'afterWidget' => $args['after_widget'],
                'beforeTitle' => $args['before_title'],
                'afterTitle' => $args['after_title'],
            )
        );
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $titleText = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'pdfforms' );

        $title = new Input([
            'label'   => esc_html__( 'Title', 'pdfforms' ),
            'name'    => $this->get_field_name( 'title' ),
            'value'   => $titleText,
        ]);

        $postIdActive = ! empty( $instance['postId'] ) ? $instance['postId'] : 0;

        $posts = get_posts(['post_type'=>'pdfforms']);

        $list = [];
        if(!empty($posts)) {
            foreach($posts as $post) {
                $list[$post->ID] = $post->post_title;
            }
        }

        $forms = new Select([
            'label'     => esc_html__( 'Choose form', 'pdfforms' ),
            'name'      => $this->get_field_name( 'postId' ),
            'options'   => $list,
            'default'   => $postIdActive,
        ]);

        echo Views::render(
            'widgets/pdfform/form.php',
            array(
                'title'  => $title->output(),
                'forms' => $forms->output(),
            )
        );
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : $old_instance['title'];
        $instance['postId'] = empty( $new_instance['postId'] ) ? $old_instance['postId'] : $new_instance['postId'];

        return $instance;
    }

}
