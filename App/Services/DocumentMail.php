<?php
namespace PdfFormsLoader\Services;

use PdfFormsLoader\Facades\MailFacade;

use PdfFormsLoader\Models\PDFFillerModel;
use PdfFormsLoader\Models\PostMetaModel;

class DocumentMail extends MailFacade
{
    const DEFAULT_SUBJECT = 'PDFForm';
    const DEFAULT_MESSAGE = 'You can download filled form.';

    public function sendDocument($fillableTemplateId, $fields, $emails, $subject = null, $message = null) {
        try {
            $pdffiller = new PDFFillerModel();
            $document = $pdffiller->saveFillableTemplates($fillableTemplateId, $fields);

            $filesService = new Files();
            $filesService->setFileFromPDFFiller($document['document_id'])->removeAfterLoadSite();

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
                'attachments' => [$filesService->getFullPath()],
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