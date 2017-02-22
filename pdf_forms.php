<?php
/*
Plugin Name: PDF Form Filler
Plugin URI: https://github.com/pdffiller/wp-integration-pdf-forms
Description: Fill and send form
Version: 0.1.4
Author: PDFFiller
Author URI: https://github.com/pdffiller
Text Domain: pdf-form
Domain Path: /languages
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

    protected $defaultSettings = [
        'pdfforms-main-messages' => [
            'message-success' => 'Success',
            'message-fail' => 'Fail',
            'submit-message' => 'Submit',
        ],
        'pdfforms-main-integrations' => [
            'contact-7-form' => 'false',
        ],
        'pdfforms-mail' => [
            'subject' => 'PDFForm attachment',
            'message' => 'You can download pdf file',
        ],
    ];

    public function __construct()
    {
        $addMetaboxes = false;
        $phpSelf = '';
        if ( isset($_SERVER['PHP_SELF']) ) {
            $phpSelf = $_SERVER['PHP_SELF'];
        }

        if ( isset($_GET['post']) && $_GET['post'] > 0 && get_post( strip_tags($_GET['post']) )->post_type === 'pdfforms') {
            $addMetaboxes = true;
        }
        if ( isset($_GET['post_type']) && strip_tags($_GET['post_type']) === "pdfforms" && $phpSelf == '/wp-admin/post-new.php') {
            $addMetaboxes = true;
        }
        if (!empty($_POST['post_type']) && strip_tags($_POST['post_type']) === "pdfforms"){
            $addMetaboxes = true;
        }

        self::$PDFFillerModel = new PDFFillerModel();

        $this->addPostTypes();
        $this->addAdminMenu();
        $this->addButtons();

        if($addMetaboxes) {
            $this->addMetaboxes();
        }

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
                    $template[] = $field;
                }
            }
        }

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
            'image_form' => Assets::getImageUrlStatic('form2.png', 'tinymce'),
            'image_field' => Assets::getImageUrlStatic('field.png', 'tinymce'),
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

    private function checkEmptySettings() {
        foreach($this->defaultSettings as $slugSection => $section) {
            $sectionValue = get_option($slugSection);
            if (empty($sectionValue)) {
                $sectionValue = $section;
            }
            $change = false;
            foreach ($section as $slugSetting => $settingValue) {
                if (empty($sectionValue[$slugSetting])) {
                    $sectionValue[$slugSetting] = $settingValue;
                    $change = true;
                }
            }
            if ($change) {
                update_option($slugSection, $sectionValue);
            }
        }
    }

    private function addAdminMenu()
    {

        $this->checkEmptySettings();

        $settings['pdfforms-main-settings'][] = array(
            'type'			=> 'input',
            'slug'			=> 'pdffiller-client-id',
            'title'			=> __( 'Client Id', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdffiller-api-key',
                'value'			=> '',
            ),
        );
        $settings['pdfforms-main-settings'][] = array(
            'type'			=> 'input',
            'slug'			=> 'pdffiller-client-secret',
            'title'			=> __( 'Client Secret', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdffiller-api-key',
                'type'          => 'password',
                'value'			=> '',
            )
        );
        $settings['pdfforms-main-settings'][] = array(
            'type'			=> 'input',
            'slug'			=> 'pdffiller-account-email',
            'title'			=> __( 'Account email', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdffiller-api-key',
                'value'			=> '',
            ),
        );
        $settings['pdfforms-main-settings'][] = array(
            'type'			=> 'input',
            'slug'			=> 'pdffiller-account-password',
            'title'			=> __( 'Account password', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdffiller-api-key',
                'type'          => 'password',
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

        $settings['pdfforms-mail'][] = array(
            'type'			=> 'input',
            'slug'			=> 'subject',
            'title'			=> __( 'Subject text', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdfforms-mail-subject',
                'value'			=> 'PDFForm attachment',
            ),
        );

        $settings['pdfforms-mail'][] = array(
            'type'			=> 'input',
            'slug'			=> 'message',
            'title'			=> __( 'Message text', 'pdfforms' ),
            'field'			=> array(
                'id'			=> 'pdfforms-mail-message',
                'value'			=> 'You can download pdf file',
            ),
        );

        $Contact7Form = IntegrationFabric::getIntegration('Contact7Form');

        if ($Contact7Form->checker()) {
            $settings['pdfforms-main-integrations'][] = array(
                'type'			=> 'switcher',
                'slug'			=> 'contact-7-form',
                'title'			=> __( 'Contact 7 form', 'pdfforms' ),
                'field'			=> array(
                    'id'			=> 'contact-7-form',
                    'value'         => 'false',
                ),
            );
        }

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
                        'pdfforms-mail' => array(
                            'slug'			=> 'pdfforms-mail',
                            'name'			=> __( 'Mail', 'pdfforms' ),
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
}

$integrationsAPI = new IntegrationsAPI();

add_action( 'after_setup_theme', [$integrationsAPI, 'initIntegrations'] );

new PdfFormsLoader();

?>