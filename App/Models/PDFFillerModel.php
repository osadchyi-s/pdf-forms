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
    const EXPIRES = 1200;
    public static $PDFFillerProvider;

    public function __construct() {
        self::$PDFFillerProvider = new PDFfiller( [
            'clientId'       => '',
            'clientSecret'   => '',
            'urlAccessToken' => '',
            'urlApiDomain'   => 'http://api.pdffiller.com/v1/',
        ]);
        self::$PDFFillerProvider->setAccessTokenHash(MainSettingsModel::getSettingItemCache('pdffiller-api-key'));
    }

    public function saveFillableTemplates($fillableTemplateid, $fields) {

        $client = new Client( [  'base_uri' => 'https://api.pdffiller.com' ] );
        $response = $client->request( 'POST', 'v1/fillable_template/?default-error-page=1', [
            'headers' => [
                'Content-Type'     => 'application/json; charset=utf-8',
                'Authorization'    => 'Bearer ' . self::$PDFFillerProvider->getAccessToken(),
            ],
            'json' => [
                "document_id"     => $fillableTemplateid,
                "fillable_fields" => $fields,
            ]
        ]);

        return $response->getStatusCode();

        /*
        $fillableTemplate = new FillableTemplate(self::$PDFFillerProvider);
        $fillableTemplate->document_id = $fillableTemplateid;
        $fillableTemplate->fillable_fields = $fields;
        $fillableTemplate->save();*/
    }

    public function getFillableTemplates() {
        $fillableTemplates = get_option('pdfform_fillable_templates', null);

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
            try{
                $response = FillRequest::all(self::$PDFFillerProvider, ['perpage' => 100]);
                dd($response->toArray(), 'test-111');

                update_option('pdfform_l2f_list', ['expires'=>time() + self::EXPIRES, 'items' => $response['items'] ]);
                return $this->getLinkToFillDocuments();
            } catch(\PDFfiller\OAuth2\Client\Provider\Core\Exception $e) {
                return null;
            }
        }

        return $l2fList['items'];
    }
}