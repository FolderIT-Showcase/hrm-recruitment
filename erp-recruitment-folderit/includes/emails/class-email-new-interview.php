<?php
namespace WeDevs\ERP\ERP_Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Employee welcome
 */
class New_Interview extends Email {

    use Hooker;

    function __construct() {
        $this->id          = 'new-interview';
        $this->title       = __( 'New interview Assigned', 'wp-erp-rec' );
        $this->description = __( 'New interview notification.', 'wp-erp-rec' );

        $this->subject     = __( 'Interview notification', 'wp-erp-rec');
        $this->heading     = __( 'Interview Notification', 'wp-erp-rec');

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    public function trigger( $data = [] ) {
        global $current_user;

        if ( empty( $data ) ) {
            return;
        }

        $this->heading = $this->get_option( 'heading', $this->heading );
        $this->subject = $this->get_option( 'subject', $this->subject );

        /*making mail body*/
		$html = '';
        $html .= __('Name: ', 'wp-erp-rec').$data['first_name'].' '.$data['last_name'].'<br/>';
        $html .= __('Email: ', 'wp-erp-rec').$data['email'].'<br/>';
        $html .= __('Mobile: ', 'wp-erp-rec').$data['mobile'].'<br/>';
        $html .= '<br/>';
        $html .= __('Interview Type: ', 'wp-erp-rec').$data['type_of_interview_text'].'<br/>';
        $html .= __('Interview Internal Type: ', 'wp-erp-rec').$data['internal_type_of_interview_text'].'<br/>';
        $html .= __('Interview Description (place/phone): ', 'wp-erp-rec').$data['interview_detail'].'<br/>';
        $html .= __('Interview Techs: ', 'wp-erp-rec').$data['interview_tech'].'<br/>';
        $html .= __('Interview DateTime: ', 'wp-erp-rec').$data['start_date_time'].'<br/>';
        $html .= __('Interview Duration: ', 'wp-erp-rec').$data['duration_minutes'].' Minutes'.'<br/>';
		$html .= '<br />';
		$html .= '<a href="'.$data['link'].'">Link</a>';

        $ids = explode(',', $data['interviewer_id']);

        foreach ( $ids as $inv_id ) {
            $author_obj = get_user_by('ID', $inv_id);
            $this->send( $author_obj->user_email, $this->get_subject(), $html, $this->get_headers(), $this->get_attachments() );
        }
    }
}
