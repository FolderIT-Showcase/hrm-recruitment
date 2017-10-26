<?php
namespace WeDevs\ERP\ERP_Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class New_Interview extends Email {

  use Hooker;

  public $attachments;

  function __construct() {
    $this->id          = 'new-interview';
    $this->title       = __( 'New interview Assigned', 'wp-erp-rec' );
    $this->description = __( 'New interview notification.', 'wp-erp-rec' );

    $this->subject     = __( 'Interview notification', 'wp-erp-rec');
    $this->heading     = __( 'Interview Notification', 'wp-erp-rec');

    $this->attachments = array();
    
    $this->find = [
      'full-name'             => '{full_name}',
      'applicant-mobile'      => '{applicant_mobile}',
      'applicant-email'       => '{applicant_email}',
      'interview-date'        => '{interview_date}',
      'interview-duration'    => '{interview_duration}',
      'interview-type'        => '{interview_type}',
      'interview-stage'       => '{interview_stage}',
      'interview-description' => '{interview_description}',
      'interview-tech'        => '{interview_tech}',
      'interview-link'        => '{interview_link}'
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

    $this->heading = $this->get_option( 'heading', $this->heading );
    $this->subject = $this->get_option( 'subject', $this->subject );
    
    $this->replace = [
      'full-name'             => $data['first_name'].' '.$data['last_name'],
      'applicant-mobile'      => $data['applicant_mobile'],
      'applicant-email'       => $data['applicant_email'],
      'interview-date'        => $data['interview_date'],
      'interview-duration'    => $data['interview_duration'],
      'interview-type'        => $data['interview_type'],
      'interview-stage'       => $data['interview_stage'],
      'interview-description' => $data['interview_description'],
      'interview-tech'        => $data['interview_tech'],
      'interview-link'        => $data['interview_link']
    ];

    $ids = explode(',', $data['interviewer_id']);

    foreach ( $ids as $inv_id ) {
      $author_obj = get_user_by('ID', $inv_id);
      $this->send( $author_obj->user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
  }
}
