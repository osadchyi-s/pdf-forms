<?php
namespace PdfFormsLoader\Widgets;

use PdfFormsLoader\Core\Ui\Input;
use PdfFormsLoader\Core\Ui\Select;
use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Assets;
use PdfFormsLoader\Core\JsVariables;

use PdfFormsLoader\Models\PDFFillerModel;

class EmbeddedJsClientWidget extends \WP_Widget
{
    const DEMO_DOCUMENT_ID = 'http://pdf.ac/73aLAc';

    protected $fields = [
        'title',
        'clientId',
        'documentId',
        'width',
        'height',
        'customClass',
        'button'
    ];

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'embedded_js_client_widget', // Base ID
            esc_html__( 'PDFFiller Link To Fill widget', 'pdfforms' ), // Name
            array( 'description' => esc_html__( 'Show fillable document in modal window', 'pdfforms' ), ) // Args
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

        wp_enqueue_script(
            'pdfform-embedded-js-client',
           '//integrations-static.pdffiller.com/1.0/PDFfillerClient.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        $assets = new Assets();

        wp_enqueue_script(
            'pdfforms-embedded',
            $assets->getJsUrl( 'embedded.js', 'widgets' ),
            array( 'jquery' ),
            '1.0.0',
            true
        );

        $data = array_merge($instance, [
            'beforeWidget' => $args['before_widget'],
            'afterWidget' => $args['after_widget'],
            'beforeTitle' => $args['before_title'],
            'afterTitle' => $args['after_title'],
        ]);

        echo Views::render(
            'widgets/embedded/frontend.php',
            $data
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
        $titleText = ! empty( $instance['title'] ) ? $instance['title'] : '';

        $title = new Input([
            'label'   => esc_html__( 'Title', 'pdfforms' ),
            'name'    => $this->get_field_name( 'title' ),
            'value'   => $titleText,
        ]);

        $clientIdText = ! empty( $instance['clientId'] ) ? $instance['clientId'] : '';

        $clientId = new Input([
            'label'   => esc_html__( 'Client Id', 'pdfforms' ),
            'name'    => $this->get_field_name( 'clientId' ),
            'value'   => $clientIdText,
        ]);

        $documentIdSelect = ! empty( $instance['documentId'] ) ? $instance['documentId'] : self::DEMO_DOCUMENT_ID; // https://www.pdffiller.com/en/project/86685380.htm?mode=link_to_fill

        $documents = (new PDFFillerModel())->getLinkToFillDocuments();
        //dd($documents, 'test444');

        $l2fList = [self::DEMO_DOCUMENT_ID => 'demo'];
        foreach($documents as $document) {
            $l2fList[$document['url']] = $document['name'];
        }

        $documentId = new Select([
            'label'     => esc_html__( 'Choose form', 'pdfforms' ),
            'name'    => $this->get_field_name( 'documentId' ),
            'options'   => $l2fList,
            'value'   => $documentIdSelect,
        ]);

        $widthText = ! empty( $instance['width'] ) ? $instance['width'] : '960';

        $width = new Input([
            'label'   => esc_html__( 'Modal frame width', 'pdfforms' ),
            'name'    => $this->get_field_name( 'width' ),
            'value'   => $widthText,
        ]);

        $heighText = ! empty( $instance['height'] ) ? $instance['height'] : '400';

        $height = new Input([
            'label'   => esc_html__( 'Modal frame height', 'pdfforms' ),
            'name'    => $this->get_field_name( 'height' ),
            'value'   => $heighText,
        ]);

        $customClassText = ! empty( $instance['customClass'] ) ? $instance['customClass'] : '';

        $customClass = new Input([
            'label'   => esc_html__( 'Custom class', 'pdfforms' ),
            'name'    => $this->get_field_name( 'customClass' ),
            'value'   => $customClassText,
        ]);

        $buttonText = ! empty( $instance['button'] ) ? $instance['button'] : 'Fill document';

        $button = new Input([
            'label'   => esc_html__( 'Button text', 'pdfforms' ),
            'name'    => $this->get_field_name( 'button' ),
            'value'   => $buttonText,
        ]);

        echo Views::render(
            'widgets/embedded/form.php',
            array(
                'title'  => $title->output(),
                'clientId' => $clientId->output(),
                'documentId' => $documentId->output(),
                'width' => $width->output(),
                'height' => $height->output(),
                'customClass' => $customClass->output(),
                'button' => $button->output(),
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
        $instance = [];
        foreach($this->fields as $key) {
            $instance[$key] = ( ! empty( $new_instance[$key] ) ) ? ( $new_instance[$key] ) : $old_instance[$key];
        }
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : $old_instance['title'];

        return $instance;
    }

}
