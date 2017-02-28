<?php

namespace PdfFormsLoader\Models;

use PdfFormsLoader\Models\MainSettingsModel;
use PDFfiller\OAuth2\Client\Provider\Core\GrantType;
use PDFfiller\OAuth2\Client\Provider\PDFfiller;
use \PDFfiller\OAuth2\Client\Provider\FillableTemplate;
use \PDFfiller\OAuth2\Client\Provider\FillRequest;
use \PDFfiller\OAuth2\Client\Provider\Document;
use \GuzzleHttp\Client;

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

class PDFFillerModel
{
    const EXPIRES = 60; //1200;
    const EXPIRES_DOCUMENT = 3600 * 24; //1200;
    public static $PDFFillerProvider;

    protected $lastAttachId = 0;

    public function __construct() {
        $clientId = MainSettingsModel::getSettingItemCache('pdffiller-client-id');
        $clientSecret = MainSettingsModel::getSettingItemCache('pdffiller-client-secret');
        $clientAccountEmail = MainSettingsModel::getSettingItemCache('pdffiller-account-email');
        $clientAccountPassword = MainSettingsModel::getSettingItemCache('pdffiller-account-password');

        if (empty($clientId) || empty($clientSecret) || empty($clientAccountEmail) || empty($clientAccountPassword)) {
            return $this;
        }

        self::$PDFFillerProvider = new PDFfiller( [
            'clientId'       => MainSettingsModel::getSettingItemCache('pdffiller-client-id'),
            'clientSecret'   => MainSettingsModel::getSettingItemCache('pdffiller-client-secret'),
            'urlAccessToken' => 'https://api.pdffiller.com/v1/oauth/access_token',
            'urlApiDomain'   => 'https://api.pdffiller.com/v1/',
        ]);

        try {
            self::$PDFFillerProvider->getAccessToken(GrantType::PASSWORD_GRANT, [
                'username' => MainSettingsModel::getSettingItemCache('pdffiller-account-email'),
                'password' => MainSettingsModel::getSettingItemCache('pdffiller-account-password'),
            ]);
        } catch(\Exception $e) {
            return $this;
        }
    }

    public function getDocumentInfo($documentId) {
        $document = get_option('pdfform_document_' . $documentId);

        if (empty($document['expires']) || $document['expires'] < time()) {
            try{
                $response = Document::one(self::$PDFFillerProvider, $documentId);

                $data = new \stdClass;
                $data->id = $response->id;
                $data->name = $response->name;

                update_option('pdfform_document_' . $documentId, ['expires' => time() + self::EXPIRES_DOCUMENT, 'data' => $data]);
                return $response;
            } catch(\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
        }

        return $document['data'];
    }

    public function getDocumentContent($documentId) {
        return Document::download(self::$PDFFillerProvider, $documentId);
    }

    public function saveFillableTemplates($fillableTemplateid, $fields) {
        $fillableTemplate = new FillableTemplate(self::$PDFFillerProvider);
        $fillableTemplate->document_id = $fillableTemplateid;
        $fillableTemplate->fillable_fields = $fields;

        $newDoc = $fillableTemplate->save();
        $this->renameDocument($newDoc['document_id']);

        return $newDoc;
    }

    public function renameDocument($documentId) {

        $document = $this->getDocumentInfo($documentId);

        $arr = (explode('.', $document->name));
        array_pop($arr);
        $name = implode('.', $arr).'_'.date('Y-m-d-H-i');

        $document->name = $name;

        $res = $document->save(false);

        return $res;
    }

    public function getFillableTemplates() {
        $fillableTemplates = get_option('pdfform_fillable_templates');

        if (empty($fillableTemplates['expires']) || $fillableTemplates['expires'] < time()) {
            try{
                $response = FillableTemplate::all(self::$PDFFillerProvider, ['perpage' => 100]);

                $documents = [];
                foreach($response->getList() as $item) {
                    try {
                        $document = $this->getDocumentInfo($item->id);
                        $documents[$item->id] = $document->name;
                    } catch(\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {

                    }
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
        $fillableFields = get_option('pdfform_fillable_fields_' . $templateId, null);
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

        $l2fList = get_option('pdfform_l2f_list', null);

        if (empty($l2fList['expires']) || $l2fList['expires'] < time()) {
            try {
                $response = FillRequest::all(self::$PDFFillerProvider, ['perpage' => 100]);
                $l2f = [];
                foreach ($response->getList() as $id => $item) {
                    try {
                        //$document = Document::one(self::$PDFFillerProvider, $item->document_id);
                        $l2f[] = [
                            'document_id' => $item->document_id,
                            'name' => $item->document_name,
                            'url' => $item->url,
                        ];
                    } catch (\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {

                    }
                }
                update_option('pdfform_l2f_list', ['expires'=>time() + self::EXPIRES, 'items' => $l2f  ]);
                return $l2f;
            } catch (\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
        }

        return $l2fList['items'];
    }
}