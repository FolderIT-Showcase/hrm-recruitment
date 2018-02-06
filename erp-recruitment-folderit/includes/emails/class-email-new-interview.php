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
      'interview-link'        => '{interview_link}',
      'interview-cv'          => '{interview_cv}',
      'interview-cc'          => '{interview_cc}',
      'interviewers-names'    => '{interviewers_names}'
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

    $toraw = '';
    $toname = '';

    $ids = explode(',', $data['interviewer_id']);
    foreach ( $ids as $inv_id ) {
      if(empty($inv_id)) {
        continue;
      }
      $author_obj = get_user_by('ID', $inv_id);
      if($toraw !== '') {
        $toraw .= ', ';
        $toname .= '<br/>';
      }
      $toraw .= '"'.$author_obj->display_name.'" <'.$author_obj->user_email.'>';
      $toname .= $author_obj->display_name.' ('.$author_obj->user_email.')';
    }

    $ccname = '';
    if(isset($data['interview_cc']) && !empty(['interview_cc'])) {
      $ccs = explode(",", $data['interview_cc']);
      if($ccs) {
        foreach($ccs as $cc) {
          if($ccname !== '') {
            $ccname .= "<br/>";
          }
          $cc_clean = $cc;
          $cc_clean = str_replace("<","(",$cc_clean);
          $cc_clean = str_replace(">",")",$cc_clean);
          $ccname .= $cc_clean;
        }
      }
    }

    add_filter('erp_email_headers', function($headers) use($data) {
      if(isset($data['interview_cc']) && !empty(['interview_cc'])) {
        $headers .= 'Cc: '.$data['interview_cc']. "\r\n";
      }

      return $headers;
    });

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
      'interview-link'        => $data['interview_link'],
      'interview-cv'          => $data['interview_cv'],
      'interview-cc'          => $ccname,
      'interviewers-names'    => $toname
    ];

    //Enviar un solo email
    $this->send( $toraw, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
  }
}
