<?php

namespace PdfFormsLoader\Models;

use PdfFormsLoader\Models\MainSettingsModel;

use PDFfiller\OAuth2\Client\Provider\PDFfiller;
use \PDFfiller\OAuth2\Client\Provider\FillableTemplate;
use \PDFfiller\OAuth2\Client\Provider\FillRequest;
use \PDFfiller\OAuth2\Client\Provider\Document;
use \GuzzleHttp\Client;

class PDFFillerModel
{
    const EXPIRES = 5; //1200;
    public static $PDFFillerProvider;

    public function __construct() {
        self::$PDFFillerProvider = new PDFfiller( [
            'clientId'       => '',
            'clientSecret'   => '',
            'urlAccessToken' => '',
            'urlApiDomain'   => 'https://api.pdffiller.com/v1/',
        ]);
        self::$PDFFillerProvider->setAccessTokenHash(MainSettingsModel::getSettingItemCache('pdffiller-api-key'));
    }

    public function getDocumentContent($documentId) {
        //dd($documentId);
        return Document::download(self::$PDFFillerProvider, $documentId);
    }

    public function insetDocumentToMedia($documentId) {
        $content = $this->getDocumentContent($documentId);
        //dd($content);
        $document = Document::one(self::$PDFFillerProvider, $documentId);
        //dd($document->name);
        $upload = wp_upload_bits( str_replace('.htm', '.pdf', $document->name), null, $content, null );
        //dd($upload);
        $attach_id = 0;
        if ($upload['error'] === false) {
            $wp_upload_dir = wp_upload_dir();

            $attachment = array(
                'guid'           => $upload['file'],
                'post_mime_type' => 'pdf',
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload['file'] ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, basename( $upload['file'] ), null );
        }

        return array_merge($upload, ['attach_id' => $attach_id]);
    }

    public function saveFillableTemplates($fillableTemplateid, $fields) {
        $fillableTemplate = new FillableTemplate(self::$PDFFillerProvider);
        $fillableTemplate->document_id = $fillableTemplateid;
        $fillableTemplate->fillable_fields = $fields;
        return $fillableTemplate->save();
    }

    public function getFillableTemplates() {
        //delete_option('pdfform_fillable_templates');
        //dd(1);
         $fillableTemplates = get_option('pdfform_fillable_templates');

         if (empty($fillableTemplates['expires']) || $fillableTemplates['expires'] < time()) {
            try{
                $response = FillableTemplate::all(self::$PDFFillerProvider);

                $documents = [];
                foreach($response->getList() as $item) {
                    $document = Document::one(self::$PDFFillerProvider, $item->id);
                    $documents[$item->id] = $document->name;
                }
                update_option('pdfform_fillable_templates', ['expires'=>time() + self::EXPIRES, 'items' => $documents ]);
                return $this->getFillableTemplates();
            } catch(\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
         }

        return $fillableTemplates['items'];
    }

    public function getFillableFields($templateId) {
        //delete_option('pdfform_fillable_fields_' . $templateId);
        //dd(1);
        $fillableFields = get_option('pdfform_fillable_fields_' . $templateId, null);
        //dd($fillableFields);
        if (empty($fillableFields['expires']) || $fillableFields['expires'] < time()) {
            try {
                $dictionary = FillableTemplate::dictionary(self::$PDFFillerProvider, $templateId)->toArray();
                update_option('pdfform_fillable_fields_' . $templateId, ['expires'=>time() + self::EXPIRES, 'items' => $dictionary['fillable_fields'] ]);
                return $this->getFillableFields($templateId);
            } catch (\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
        }

        return $fillableFields['items'];
    }

    public function getLinkToFillDocuments() {
        $response = FillRequest::all(self::$PDFFillerProvider, ['perpage' => 100]);

        $l2f = [];
        foreach($response->getList() as $id => $item) {
            $document = Document::one(self::$PDFFillerProvider, $item->document_id);
            $l2f[] = [
                'document_id' => $item->document_id,
                'name' => $document->name,
                'url' => $item->url,
            ];
        }
        //dd($l2f , 'test3333');
        update_option('pdfform_l2f_list', ['expires'=>time() + self::EXPIRES, 'items' => $l2f  ]);
        return $l2f;


        //////
        $l2fList = get_option('pdfform_l2f_list', null);
        //dd($l2fList, 'test-111');

        if (empty($l2fList['expires']) || $l2fList['expires'] < time()) {
            try{
                $response = FillRequest::all(self::$PDFFillerProvider, ['perpage' => 100]);
                $l2f = [];
                foreach($response->getList() as $id => $item) {
                    $document = Document::one(self::$PDFFillerProvider, $item->document_id);
                    $l2f[] = [
                        'document_id' => $item->document_id,
                        'name' => $document->name,
                        'url' => $item->url,
                    ];
                }
                update_option('pdfform_l2f_list', ['expires'=>time() + self::EXPIRES, 'items' => $l2f  ]);
                return $this->getLinkToFillDocuments();
            } catch(\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
        }

        return $l2fList['items'];
    }
}