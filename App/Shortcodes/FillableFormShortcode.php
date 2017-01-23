<?php
namespace PdfFormsLoader\Shortcodes;

use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Assets;
use PdfFormsLoader\Core\JsVariables;

use PdfFormsLoader\Models\TextsSettingsModel;
use PdfFormsLoader\Models\PDFFillerModel;
use PdfFormsLoader\Models\PostMetaModel;

use PdfFormsLoader\Services\DocumentMail;

class FillableFormShortcode
{
    public $slug = 'pdfform';
    public $initMethod = 'addShortcode';
    protected $postId;

    public function addShortcode($atts) {
        $atts = shortcode_atts( array(
            'id' => '0',
        ), $atts );

        $this->postId = $atts['id'];

        $post = get_post($atts['id']);

        if (empty($post)) {
            return Views::render(
                'shortcodes/errors/choose_page.php'
            );
        }

        $submitLocation = get_post_meta((int) $post->ID, 'pdfform_submit_location', true);
        empty($submitLocation) ? $submitLocation = 'bottom' : 'nothing';

        $submitMessage = get_post_meta((int) $post->ID, 'pdfform_submit_message', true);
        empty($submitMessage) ? $submitMessage = TextsSettingsModel::getSubmitMesage() : 'nothing';

        $this->assets();

        return Views::render(
            'shortcodes/fillableform.php',
            array(
                'id'              => $atts['id'],
                'content'         => apply_filters('the_content', $post->post_content),
                'submitLocation' => $submitLocation,
                'submitMessage' => $submitMessage,
            )
        );
    }

    public function fillableSave() {
        $fields = $_POST;
        $formId = $fields['pdfform-form-id'];
        $fillableTemplateId = get_post_meta((int) $formId, 'fillable_template_list_fillable_template_list', true);

        unset($fields['action'], $fields['pdfform-form-id']);

        $postModel = new PostMetaModel($formId);

        $emails = $postModel->getSendMailList($fields);

        $documentSender = new DocumentMail();
        $result = $documentSender->sendDocument($fillableTemplateId, $fields, $emails);

        if (!$result) {
            header('HTTP/1.0 400 Bad Request');
        }

        wp_die();
    }

    /**
     * Add styles and scripts
     *
     * @return void
     */
    public function assets() {
        $assets = new Assets();

        wp_enqueue_script( 'jquery-form' );

        $messageSuccess = get_post_meta($this->postId, 'pdfform_mesage_success', true);
        $messageFail = get_post_meta($this->postId, 'pdfform_mesage_fail', true);

        empty($messageSuccess) ? $messageSuccess = TextsSettingsModel::getSuccessMesage() : 'nothing';
        empty($messageFail) ? $messageFail = TextsSettingsModel::getFailMesage() : 'nothing';

        JsVariables::addVariableFront('PdfformFillableForm', [
            'adminAjaxUrl' => admin_url( 'admin-ajax.php' ),
            'messageSuccess' => $messageSuccess,
            'messageFail' => $messageFail,
        ]);

        wp_enqueue_script(
            'pdfforms-shortcodes-form',
            $assets->getJsUrl( 'fillable-form.js', 'shortcodes' ),
            array( 'jquery' ),
            '1.0.0',
            true
        );

        /*
        wp_enqueue_style(
            'pdfforms-shortcodes-form',
            $assets->getCssUrl( 'fillable-form.css', 'shortcodes' ),
            array(),
            '1.0.0',
            'all'
        );*/
    }
}