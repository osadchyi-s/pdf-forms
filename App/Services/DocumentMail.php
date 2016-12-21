<?php
namespace PdfFormsLoader\Services;

use PdfFormsLoader\Facades\MailFacade;

use PdfFormsLoader\Models\PDFFillerModel;
use PdfFormsLoader\Models\PostMetaModel;

class DocumentMail extends MailFacade
{
    const DEFAULT_SUBJECT = 'PDFForm';
    const DEFAULT_MESSAGE = 'You can download filled form.';

    public function __construct()
    {
        parent::__construct([]);
    }

    public function sendDocument($fillableTemplateId, $fields, $emails) {
        try {
            $pdffiller = new PDFFillerModel();
            $document = $pdffiller->saveFillableTemplates($fillableTemplateId, $fields);

            $mediaData = $pdffiller->insetDocumentToMedia($document['document_id']);

            $this->setParams([
                'to' => $emails,
                'subject' => self::DEFAULT_SUBJECT,
                'message' => self::DEFAULT_MESSAGE,
                'headers' => [],
                'attachments' => [$mediaData['file']],
            ]);
            return $this->send();
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return false;
        }
    }

}