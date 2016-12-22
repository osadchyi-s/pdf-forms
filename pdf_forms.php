<?php
/*
Plugin Name: Pdf Forms
Plugin URI:
Description:
Author:
Version:
Author URI:
*/

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ;

use PdfFormsLoader\Facades\PostTypesFacade;
use PdfFormsLoader\Facades\PageBuilderFacade;
use PdfFormsLoader\Facades\TinymceButtonsFacade;
use PdfFormsLoader\Facades\MetaBoxesFacade;
use PdfFormsLoader\Core\Assets;
use PdfFormsLoader\Core\JsVariables;
use PdfFormsLoader\Models\MainSettingsModel;
use PdfFormsLoader\Models\IntegrationsSettingsModel;
use PdfFormsLoader\Models\PDFFillerModel;
use PdfFormsLoader\Shortcodes\Shortcodes;
use PdfFormsLoader\Shortcodes\FillableFormShortcode;
use PdfFormsLoader\Core\Ui\FieldsMapper;
use PdfFormsLoader\Integrations\IntegrationsAPI;
use PdfFormsLoader\Integrations\IntegrationFabric;

class PdfFormsLoader {

    public static $PDFFillerModel;

    public function __construct()
    {
        self::$PDFFillerModel = new PDFFillerModel();

        $this->addPostTypes();
        $this->addAdminMenu();
        $this->addButtons();
        $this->addMetaboxes();
        $this->addShortcodes();
        $this->addWidgets();
        $this->runIntegrations();

        add_action('admin_init', [$this, 'assignAsyncEvents']);
    }

    public function assignAsyncEvents() {
        $fillableFormShortcode = new FillableFormShortcode();
        add_action('wp_ajax_pdfformsave', [&$fillableFormShortcode, 'fillableSave']);
        add_action('wp_ajax_nopriv_pdfformsave', [&$fillableFormShortcode, 'fillableSave']);
    }

    protected function addShortcodes() {
        $shortcodes = new Shortcodes();
        $shortcodes->initShortcodes(['FormsFields', 'FillableForm']);
    }

    private function addWidgets() {
        add_action( 'widgets_init', function() {
            register_widget( 'PdfFormsLoader\Widgets\PdfFormWidget' );
            register_widget( 'PdfFormsLoader\Widgets\EmbeddedJsClientWidget' );
        });
    }

    private function addMetaboxes() {
        $documents = self::$PDFFillerModel->getFillableTemplates();

        MetaBoxesFacade::make([
            'slug' => 'fillable_template_list',
            'title' => 'Fillable template list',
            'postType' => 'pdfforms',
            'context' => 'side',
            'priority' => 1,
            'fields' => [
                [
                    'name' => 'fillable_template_list',
                    'type' => 'select',
                    'list' => $documents,
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_send_mail',
            'title' => 'Send document to email',
            'postType' => 'pdfforms',
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'label'=> 'Send to admins emails',
                    'name' => 'send_to_admin',
                    'type' => 'switcher',
                ],
                [
                    'label'=> 'Send to email from field',
                    'name' => 'send_to_field_email',
                    'type' => 'switcher',
                ],
                [
                    'label'=> 'Email field',
                    'name' => 'email_field',
                    'type' => 'input',
                ],
                [
                    'label'=> 'Send to custom emails',
                    'name' => 'custom_emails',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_submit_location',
            'title' => 'Submit button location',
            'postType' => 'pdfforms',
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_submit_location',
                    'type' => 'select',
                    'list' => [
                        'bottom' => 'Bottom',
                        'top' => 'Top',
                    ],
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_submit_message',
            'title' => 'Submit message',
            'postType' => 'pdfforms',
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_submit_message',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_message_success',
            'title' => 'Success message',
            'postType' => 'pdfforms',
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_message_success',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_message_fail',
            'title' => 'Fail message',
            'postType' => 'pdfforms',
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_message_fail',
                    'type' => 'input',
                ],
            ],
        ]);
    }

    private function getFillableTemplateFields($postId = null) {
        $template = [];
        if (!$postId && !empty($_GET['post'])) {
            $postId = $_GET['post'];
        }

        if (empty($postId)) {
            return $template;
        }

        $templateId = (int) get_post_meta($postId, 'fillable_template_list_fillable_template_list', true);

        if (!empty($templateId)) {
            $dictionary = self::$PDFFillerModel->getFillableFields($templateId);
            if (!empty($dictionary)) {
                foreach($dictionary as $key => $field) {
                    /*if ($field->type == 'checkbox') {
                        $field->type = 'switcher';
                    }

                    if ($field->type == 'dropdown') {
                        $field->type = 'select';
                        $field->options = $field->list;
                    }*/
                    $template[] = $field;
                }
            }
        }

        //dd($template);

        return $template;
    }

    private function addButtons() {
        $template = $this->getFillableTemplateFields();

        $fieldsMapper = new FieldsMapper();

        $fields = [];
        foreach($template as $field) {
            $fields[] = (object) [
                'fieldAttr' => $fieldsMapper->prepareShortCodeAttr($field),
                'text' => $field->name,
                'type' => 'button',
            ];
        }

        JsVariables::addVariable('pdfforms_button', [
            'image' => Assets::getImageUrlStatic('form.png', 'tinymce'),
            'fields' => $fields
        ]);

        TinymceButtonsFacade::buttonsFactory([
            'button_name' => 'pdfforms_button',
            'post_types' => ['pdfforms', 'post', 'page'],
            'assets' => [
                'scripts' => [
                    [
                        'name' => 'pdfforms_button',
                        'file' => 'button.js',
                        'parent' => ['jquery'],
                        'footer' => true,
                        'version' => '1.0',
                    ]
                ],
            ],
        ])->makeButton();


        $posts = get_posts(['post_type'=>'pdfforms']);
        $templates = [];
        foreach($posts as $post) {
            $templates[] = (object) [
                'type' => 'button',
                'text' => $post->post_title,
                'id' => $post->ID,
                'class' => 'pdfform-editor-button',
            ];
        }

        JsVariables::addVariable('pdfforms_list_button', [
            'documents' => $templates
        ]);

        TinymceButtonsFacade::buttonsFactory([
            'button_name' => 'pdfforms_list_button',
            'post_types' => ['post', 'page'],
            'assets' => [
                'scripts' => [
                    [
                        'name' => 'pdfforms_list_button',
                        'file' => 'button.js',
                        'parent' => ['jquery'],
                        'footer' => true,
                        'version' => '1.0',
                    ]
                ],
            ],
        ])->makeButton();
    }

    private function addPostTypes() {
        PostTypesFacade::createPostType('pdfforms', 'PDFForms', 'PDFForm');
    }

    private function addAdminMenu()
    {
        $settings['pdfforms-main-settings'][] = array(
            'type'			=> 'input',
            'slug'			=> 'pdffiller-api-key',
            'title'			=> __( 'PDFFiller Api Key', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdffiller-api-key',
                'value'			=> '',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type'			=> 'input',
            'slug'			=> 'message-success',
            'title'			=> __( 'Messages success', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdfforms-message-success',
                'value'			=> 'Fillable form have been completed',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type'			=> 'input',
            'slug'			=> 'message-fail',
            'title'			=> __( 'Messages fail', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdfforms-message-fail',
                'value'			=> 'Fillable form can`t completed',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type'			=> 'input',
            'slug'			=> 'submit-message',
            'title'			=> __( 'Submit message', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdfforms-submit-message',
                'value'			=> 'Send',
            ),
        );

        $settings['pdfforms-main-integrations'][] = array(
            'type'			=> 'switcher',
            'slug'			=> 'contact-7-form',
            'title'			=> __( 'Contact 7 form', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'contact-7-form',
                'value'         => 'false',
            ),
        );

        PageBuilderFacade::makePageMenu( 'pdfforms-settings', 'Settings', 'edit.php?post_type=pdfforms' )
            ->set(
                array(
                    'capability'	=> 'manage_options',
                    'position'		=> 22,
                    'icon'			=> 'dashicons-admin-site',
                    'sections'		=> array(
                        'pdfforms-main-settings' => array(
                            'slug'			=> 'pdfforms-main-settings',
                            'name'			=> __( 'Main', 'pdfforms' ),
                            'description'	=> '',
                        ),
                        'pdfforms-main-messages' => array(
                            'slug'			=> 'pdfforms-main-messages',
                            'name'			=> __( 'Messages', 'pdfforms' ),
                            'description'	=> '',
                        ),
                        'pdfforms-main-integrations' => array(
                            'slug'			=> 'pdfforms-main-integrations',
                            'name'			=> __( 'Integrations', 'pdfforms' ),
                            'description'	=> '',
                        ),
                    ),
                    'settings'		=> $settings,
                )
            );
    }

    protected function runIntegrations() {
        add_filter('pdfform_integrations', [$this, 'getIntegrationsList'], 40, 4);
    }

    public function getIntegrationsList($integrations, $obj) {
        if(IntegrationsSettingsModel::getCF7Setting() == 'true') {
            $integrations['custom-form-7'] = IntegrationFabric::getIntegration('Contact7Form');
        }
        return $integrations;
    }

    public function activate() {

        // This call needs to be made to activate this app within WP MVC

        $this->activate_app(__FILE__);

        // Perform any databases modifications related to plugin activation here, if necessary

        require_once ABSPATH.'wp-admin/includes/upgrade.php';

        add_option('pdf_forms_db_version', $this->db_version);

    }

    public function deactivate() {

        // This call needs to be made to deactivate this app within WP MVC

        $this->deactivate_app(__FILE__);

        // Perform any databases modifications related to plugin deactivation here, if necessary

    }
}

$integrationsAPI = new IntegrationsAPI();
//$integrationsAPI->initIntegrations();

add_action( 'admin_init', [$integrationsAPI, 'initIntegrations'] );

new PdfFormsLoader();

?>