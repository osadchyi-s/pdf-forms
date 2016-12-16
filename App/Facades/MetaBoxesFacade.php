<?php
namespace PdfFormsLoader\Facades;

class MetaBoxesFacade
{
    protected $slug;
    protected $title;
    protected $postType;
    protected $context;
    protected $priority;
    protected $args;

    public function __construct($params)
    {
        $this->slug = $params['slug'];
        $this->title = $params['title'];
        $this->postType = $params['postType'];
        $this->context = $params['context'];
        $this->priority = $params['priority'];
        $this->args = $params;

        return $this;
    }

    public static function make($params) {
        $box = new MetaBoxesFacade($params);
        add_action('add_meta_boxes', [&$box, 'addMetaBox']);
        add_action( 'save_post', [&$box, 'metaSave'] );
    }

    public function addMetaBox() {
        add_meta_box(
            $this->slug,
            $this->title,
            [&$this, 'render'],
            $this->postType,
            $this->context
            //, $this->priority
        );
    }

    function metaSave($postId) {
        if (empty($_POST['post_type']) || empty($_POST[$this->slug])) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( $this->postType == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $postId ) )
                return;
        } else {
            if ( !current_user_can( 'edit_post', $postId ) )
                return;
        }

        $value = $_POST[$this->slug];
        update_post_meta( $postId, $this->slug, $value );
    }

    protected function getCurrentValue() {
        if (isset($_GET['post'])) {
            return get_post_meta($_GET['post'], $this->slug, true);
        }
        return '';
    }

    public function render() {
        $uiType = 'PdfFormsLoader\\Core\Ui\\' . ucfirst($this->args['field']['type']);
        $this->args['field']['name'] = $this->slug;
        $this->args['field']['value'] = $this->getCurrentValue();
        $ui = new $uiType($this->args['field']);
        echo $ui->output();
    }
}