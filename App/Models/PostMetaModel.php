<?php
namespace PdfFormsLoader\Models;

class PostMetaModel
{
    protected $postId;

    public function __construct($postId)
    {
        $this->postId = (int) $postId;
    }


    public function getSendMailList($fields) {
        $emails = [];

        $sendToAdmin = (string) get_post_meta($this->postId, 'pdfform_send_mail_send_to_admin', true);
        if ($sendToAdmin == 'true') {
            $adminList = get_users( 'role=administrator' );
            foreach ( $adminList as $admin ) {
                $emails[] = $admin->user_email;
            }
        }

        $sendToFromField = get_post_meta($this->postId, 'pdfform_send_mail_send_to_field_email', true);
        $userEmail = (string) get_post_meta($this->postId, 'pdfform_send_mail_email_field', true);
        if ($sendToFromField == 'true' && !empty($userEmail)) {
            $emails[] = $fields[$userEmail];
        }

        $customEmails = (string) get_post_meta($this->postId, 'pdfform_send_mail_custom_emails', true);
        $emails = array_merge($emails, explode(',', $customEmails));
        $emails = array_unique($emails);
        $emails = array_filter($emails);

        return $emails;
    }
}