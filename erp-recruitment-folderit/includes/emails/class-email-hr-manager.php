<?php
namespace WeDevs\ERP\ERP_Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class HR_Manager extends Email {

  use Hooker;

  public $attachments;

  function __construct() {
    $this->id          = 'hr-manager';
    $this->title       = __( 'HR Manager Email Template', 'wp-erp-rec' );
    $this->description = __( 'Email internal template for HR managers.', 'wp-erp-rec' );

    $this->subject     = '';
    $this->heading     = '';

    $this->attachments = array();

    $this->find = [
      'email-subject' => '{subject}',
      'email-message' => '{message}'
    ];

    $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

    parent::__construct();
  }

  function get_args() {
    return [
      'email_heading' => $this->heading,
      'email_body'    => wpautop( $this->get_option( 'body' ) ),
    ];
  }

  public function attach($filename = '') {
    array_push($this->attachments, $filename);
  }

  public function trigger( $email_to, $email_subject, $email_message, $email_headers = '' ) {
    global $current_user;

    if ( empty( $email_message ) ) {
      return;
    }

    $this->heading = $this->get_option( 'heading', $this->heading );

    $this->headers = $email_headers;
    $this->subject = $email_subject;
    $this->recipient = $email_to;

    $this->replace = [
      'email-subject' => $email_subject,
      'email-message' => wpautop($email_message)
    ];

    add_filter('erp_email_headers', function($headers) use($email_headers) {
      if ( !empty( $email_headers ) ) {
        foreach ( $email_headers as $value ) {
          if (strpos($headers, $value) === false) {
            $headers .= $value . "\r\n";
          }
        }
      }

      return $headers;
    });

    return $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
  }
}
