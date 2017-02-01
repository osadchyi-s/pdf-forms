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

    public function sendDocument($fillableTemplateId, $fields, $emails, $subject = null, $message = null) {
        try {
            $pdffiller = new PDFFillerModel();
            $document = $pdffiller->saveFillableTemplates($fillableTemplateId, $fields);

            $mediaData = $pdffiller->insertDocumentToMedia($document['document_id']);

            if (empty($emails)) {
                return true;
            }

            $subject = !empty($subject) ? $subject : self::DEFAULT_SUBJECT;
            $message = !empty($message) ? $message : self::DEFAULT_MESSAGE;

            $this->setParams([
                'to' => $emails,
                'subject' => $subject,
                'message' => $message,
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