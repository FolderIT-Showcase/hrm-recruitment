<?php
namespace WeDevs\ERP\ERP_Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class Job_Application extends Email {

  use Hooker;

  public $attachments;

  function __construct() {
    $this->id          = 'job-application';
    $this->title       = __( 'Job Application Received', 'wp-erp-rec' );
    $this->description = __( 'Job application received from frontend.', 'wp-erp-rec' );

    $this->subject     = __( 'Your Job Application Was Received', 'wp-erp-rec');
    $this->heading     = __( 'Job application received', 'wp-erp-rec');

    $this->attachments = array();

    $this->find = [
      'full-name'          => '{full_name}',
      'job-title'          => '{job_title}',
      'applicant-mobile'   => '{applicant_mobile}',
      'applicant-email'   => '{applicant_email}',
      'apply-date'         => '{apply_date}'
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

  function get_attachments() {
    return apply_filters( 'erp_email_attachments', $this->attachments, $this->id, $this->object );
  }

  public function attach($filename = '') {
    array_push($this->attachments, $filename);
  }

  public function trigger( $data = [] ) {
    global $current_user;

    if ( empty( $data ) ) {
      return;
    }
    
    $this->recipient = $data['applicant_email'];

    $this->heading = $this->get_option( 'heading', $this->heading );
    $this->subject = $this->get_option( 'subject', $this->subject );

    $this->replace = [
      'full-name'        => $data['full_name'],
      'job-title'        => $data['job_title'],
      'applicant-mobile' => $data['applicant_mobile'],
      'applicant-email'  => $data['applicant_email'],
      'apply-date'       => $data['apply_date']
    ];

    $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
  }
}
