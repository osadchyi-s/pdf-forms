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

            $content = $pdffiller->getDocumentContent($document['document_id']); //insertDocumentToMedia($document['document_id']);
            $file = tempnam(sys_get_temp_dir(), 'pdfforms');
            $fp = fopen($file, "w");
            fwrite($fp, $content);
            fclose($fp);

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
                'attachments' => [$file],
            ]);

            $result = $this->send();

            unlink($file);
            return $result;
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return false;
        }
    }

}