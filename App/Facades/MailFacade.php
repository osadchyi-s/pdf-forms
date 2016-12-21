<?php
namespace PdfFormsLoader\Facades;


class MailFacade
{
    protected $to;
    protected $subject;
    protected $message;
    protected $headers = [];
    protected $attachments = null;

    public function __construct($args = [])
    {
        return $this->setParams($args);
    }

    public function send() {
        try {
            return wp_mail( $this->getTo(), $this->getSubject(), $this->getMessage(), $this->getHeaders(), $this->getAttachments());
        } catch(\Exception $e) {
            return false;
        }
    }

    public function setParams($args) {
        $this->setTo( (array) array_get($args, 'to', null));
        $this->setSubject( (string) array_get($args, 'subject', null));
        $this->setMessage( (String) array_get($args, 'message', null));
        $this->setHeaders( (array) array_get($args, 'headers', []));
        $this->setAttachments( (array) array_get($args, 'attachments', null));

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return null
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param null $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }
}