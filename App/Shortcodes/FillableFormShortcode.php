<?php
namespace PdfFormsLoader\Shortcodes;

use PdfFormsLoader\Core\Views;
use PdfFormsLoader\Core\Assets;
use PdfFormsLoader\Core\JsVariables;

use PdfFormsLoader\Models\TextsSettingsModel;
use PdfFormsLoader\Models\PDFFillerModel;

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

        $submitLocation = get_post_meta($post->ID, 'pdfform_submit_location', true);
        empty($submitLocation) ? $submitLocation = 'bottom' : 'nothing';

        $submitMessage = get_post_meta($post->ID, 'pdfform_submit_message', true);
        empty($submitMessage) ? $submitMessage = TextsSettingsModel::getSubmitMesage() : 'nothing';

        $this->assets();

        return  Views::render(
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

       // echo 'test';
       // exit();

        $fields = $_POST;
        $formId = $fields['pdfform-form-id'];
        $fillableTemplateid = get_post_meta($formId, 'fillable_template_list', true);
        unset($fields['action'], $fields['pdfform-form-id']);

        //die($fillableTemplateid);

        $pdffiller = new PDFFillerModel();

        try {
            $pdffiller->saveFillableTemplates($fillableTemplateid, $fields);
        } catch (\Exception $e) {
            echo $e->getMessage();
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