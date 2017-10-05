<?php
namespace WeDevs\ERP\ERP_Recruitment;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for HRM
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_wp-erp-rec-get-applicationRating', 'get_application_rating' );
        $this->action( 'wp_ajax_wp-erp-rec-get-applicationAvgRating', 'get_applicants_avg_rating' );
        $this->action( 'wp_ajax_wp-erp-rec-get-examDetail', 'get_exam_details' );
        $this->action( 'wp_ajax_nopriv_wp-erp-rec-job-seeker-creation', 'job_seeker_create' );
        $this->action( 'wp_ajax_wp-erp-rec-job-seeker-creation', 'job_seeker_create' );
        $this->action( 'wp_ajax_wp-erp-rec-job-seeker', 'admin_create_candidate' );
        $this->action( 'wp_ajax_wp-erp-rec-manager-rating', 'manager_rating' );
        $this->action( 'wp_ajax_wp-erp-rec-manager-status', 'status_posting' );
        $this->action( 'wp_ajax_wp-erp-rec-get-comments', 'get_comments' );
        $this->action( 'wp_ajax_wp-erp-rec-manager-comment', 'manager_comment' );
        $this->action( 'wp_ajax_recruitment_form_builder', 'recruitment_form_builder_handler' );
        $this->action( 'wp_ajax_wp-erp-rec-serial-personal-fields', 'sort_personal_fields' );
        $this->action( 'wp_ajax_wp-erp-rec-sendEmail', 'send_email' );

        // to-do
        $this->action( 'wp_ajax_erp-rec-get-todo', 'get_todo' );
        $this->action( 'wp_ajax_erp-rec-create-todo', 'create_todo' );
        $this->action( 'wp_ajax_erp-rec-update-todo', 'update_todo_status' );
        $this->action( 'wp_ajax_erp-rec-delete-todo', 'delete_todo' );

        // interview
        $this->action( 'wp_ajax_erp-rec-create-interview', 'create_interview' );
        $this->action( 'wp_ajax_erp-rec-get-interview', 'get_interview' );
        $this->action( 'wp_ajax_erp-rec-del-interview', 'delete_interview' );
        $this->action( 'wp_ajax_erp-rec-update-interview', 'update_interview' );
        $this->action( 'wp_ajax_erp-rec-update-feedback', 'update_feedback' );
      
        // cv
        $this->action( 'wp_ajax_erp-rec-upload-cv', 'upload_cv' );

        // stage
        $this->action( 'wp_ajax_erp-rec-get-stage', 'rec_get_stage' );
        $this->action( 'wp_ajax_erp-rec-get-application-stage', 'rec_get_stage' );
        $this->action( 'wp_ajax_erp-rec-create-stage', 'create_stage' );
        $this->action( 'wp_ajax_erp-rec-add-application-stage', 'add_application_stage' );
        $this->action( 'wp_ajax_erp-rec-del-stage', 'delete_stage' );
        $this->action( 'wp_ajax_erp-rec-delete-application-stage', 'delete_application_stage' );
        $this->action( 'wp_ajax_erp-rec-change_stage', 'change_stage' );

        // status
        $this->action( 'wp_ajax_erp-rec-change_status', 'change_status' );

        // to-do calendar
        $this->action( 'wp_ajax_erp-get-calendar-overview', 'todo_calendar_overview' );
        $this->action( 'wp_ajax_erp-get-calendar-overdue', 'todo_calendar_overdue' );
        $this->action( 'wp_ajax_erp-get-calendar-today', 'todo_calendar_today' );
        $this->action( 'wp_ajax_erp-get-calendar-later', 'todo_calendar_later' );
        $this->action( 'wp_ajax_erp-get-calendar-no-date', 'todo_calendar_no_date' );
        $this->action( 'wp_ajax_erp-get-calendar-this-month', 'todo_calendar_this_month' );
        $this->action( 'wp_ajax_erp-rec-get-single-todo-details', 'get_cal_selected_todo' );

        // report
        $this->action( 'wp_ajax_erp-rec-get-opening-report', 'get_opening_report' );
        $this->action( 'wp_ajax_erp-rec-get-candidate-report', 'get_candidate_report' );
    }

    /**
     * Get opening report
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_opening_report() {
        global $wpdb;

        $job_id = $_REQUEST['jobid'];

        $query = "SELECT post.post_title as opening,
                    post.post_date as create_date,
                    COUNT(job_id) as total_candidate,
                    app.job_id as jobid,
                    (SELECT COUNT(job_id) FROM {$wpdb->prefix}erp_application as app
                      WHERE app.status=0
                      AND app.stage<>(SELECT stageid FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid=app.job_id ORDER BY id LIMIT 1)
                      AND app.job_id=jobid) as in_process,

                    (SELECT COUNT(job_id) FROM {$wpdb->prefix}erp_application as app
                      LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                      ON app.applicant_id=peoplemeta.erp_people_id
                      WHERE peoplemeta.meta_key='status' AND peoplemeta.meta_value='archive' AND app.job_id=jobid
                      OR peoplemeta.meta_key='status' AND peoplemeta.meta_value='hired' AND app.job_id=jobid
                      OR peoplemeta.meta_key='status' AND peoplemeta.meta_value='rejected' AND app.job_id=jobid) as archive,

                    (SELECT COUNT(job_id) FROM {$wpdb->prefix}erp_application as app
                      WHERE app.stage=(SELECT stageid FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid=app.job_id ORDER BY id LIMIT 1)
                      AND app.status=0
                      AND app.job_id=jobid) as unscreen,

                    (SELECT COUNT(job_id) FROM {$wpdb->prefix}erp_application as app
                      LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
                      ON app.applicant_id=peoplemeta.erp_people_id
                      WHERE app.stage='other'
                      AND peoplemeta.meta_key='status' AND peoplemeta.meta_value<>'archive'
                      AND peoplemeta.meta_value<>'hired' AND peoplemeta.meta_value<>'rejected'
                      AND app.job_id=jobid) as other

                    FROM {$wpdb->prefix}erp_application as app
                    LEFT JOIN {$wpdb->prefix}posts as post
                    ON app.job_id=post.ID
                    WHERE app.status=0";
        if ( $job_id == 0 ) {
            $query .= " GROUP BY opening";
        } else {
            $query .= " AND app.job_id={$job_id} GROUP BY opening";
        }

        $qdata = $wpdb->get_results( $query, ARRAY_A );

        $report_data           = [ ];
        $grand_total_candidate = 0;
        foreach ( $qdata as $ud ) {
            $grand_total_candidate += intval( $ud['total_candidate'] );
            $report_data[] = array(
                'opening'         => $ud['opening'],
                'create_date'     => date( 'd M Y', strtotime( $ud['create_date'] ) ),
                'total_candidate' => $ud['total_candidate'],
                'in_process'      => $ud['in_process'],
                'archive'         => $ud['archive'],
                'unscreen'        => $ud['unscreen'],
                'other'           => $ud['other']
            );
        }

        $this->send_success( $report_data );
    }

    /**
     * Get candidate report
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_candidate_report() {
        global $wpdb;

        $job_id = $_REQUEST['jobid'];

        $query = "SELECT people.first_name as fname,
                    people.email as email,
                    people.phone as phone,
                    app.apply_date as apply_date,
                    base_stage.title as current_stage
                    FROM {$wpdb->prefix}erp_application as app
                    LEFT JOIN {$wpdb->prefix}erp_peoples as people
                    ON app.applicant_id=people.id
                    LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                    ON base_stage.id=app.stage";
        if ( $job_id != 0 ) {
            $query .= " WHERE app.job_id={$job_id}";
        }

        $qdata = $wpdb->get_results( $query, ARRAY_A );

        $report_data = [ ];
        foreach ( $qdata as $ud ) {
            $report_data[] = array(
                'first_name'    => $ud['fname'],
                'email'         => $ud['email'],
                'phone'         => $ud['phone'],
                'apply_date'    => date( "d M Y", strtotime( $ud['apply_date'] ) ),
                'current_stage' => $ud['current_stage']
            );
        }

        $this->send_success( $report_data );
    }

    /**
    * Sorting personal fields
    *
    * @since 1.0.0
    *
    * @return void
    */
    public function sort_personal_fields() {
        if ( isset( $_POST['list'] ) ) {
            $list    = $_POST['list'];
            $post_id = $_POST['post_id'];
            $output  = [ ];
            update_post_meta( $post_id, '_personal_fields', $list );
            $this->send_success( $output );
        } else {
            $this->send_error( __( 'list not found!', 'wp-erp-rec' ) );
        }
    }

    /**
     * Handle recruitement form builder
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function recruitment_form_builder_handler() {
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'recruitment_form_builder_nonce' ) ) {
            wp_die( __( 'You are not allowed', 'wp-erp-rec' ) );
        }

        $qcollection = isset( $_POST['qcollection'] ) ? $_POST['qcollection'] : '';
        $postid      = isset( $_POST['postid'] ) ? $_POST['postid'] : 0;

        update_post_meta( $postid, '_erp_hr_questionnaire', $qcollection );

        wp_send_json_success( $qcollection );
    }

    /**
    * Manage manager comments
    *
    * @since 1.0.0
    *
    * @return bool
    */
    public function manager_comment() {
        global $wpdb;
        $this->verify_nonce( 'wp_erp_rec_applicant_comment_nonce' );

        $application_id = isset( $_POST['application_id'] ) ? $_POST['application_id'] : 0;
        $comment        = isset( $_POST['manager_comment'] ) ? $_POST['manager_comment'] : '';
        $admin_user_id  = isset( $_POST['admin_user_id'] ) ? $_POST['admin_user_id'] : 0;

        if ( !isset( $comment ) || $comment == '' ) {
            $this->send_error( __( 'Comment cannot be empty', 'wp-erp-rec' ) );
        } else {

            $data = array(
                'application_id' => $application_id,
                'comment'        => strip_tags( $comment ),
                'user_id'        => $admin_user_id
            );

            $format = array(
                '%d',
                '%s',
                '%d'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_application_comment', $data, $format );

            $data['display_name'] = get_the_author_meta( 'display_name', $admin_user_id );
            $data['comment_date'] = date( "Y-m-d H:i:s" );
            $data['user_pic']     = get_avatar( $admin_user_id, 64 );

            $this->send_success( $data );
        }
    }

    /**
     * Get comments list
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_comments() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT *, user.ID as uid
                FROM {$wpdb->prefix}erp_application_comment as comment
                LEFT JOIN {$wpdb->base_prefix}users as user
                ON comment.user_id = user.ID
                WHERE comment.application_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $_GET['application_id'] ), ARRAY_A );

            $user_pic_data = [ ];
            foreach ( $udata as $ud ) {
                $user_pic_data[] = array(
                    'uid'             => $ud['uid'],
                    'applicationd_id' => $ud['application_id'],
                    'comment'         => $ud['comment'],
                    'comment_date'    => $ud['comment_date'],
                    'user_id'         => $ud['user_id'],
                    'display_name'    => $ud['display_name'],
                    'user_email'      => $ud['user_email'],
                    'user_pic'        => get_avatar( $ud['uid'], 64 )
                );
            }

            $this->send_success( $user_pic_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Hireing post status
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function status_posting() {
        global $wpdb;
        $this->verify_nonce( 'wp_erp_rec_applicant_status_nonce' );

        $application_id = isset( $_POST['application_id'] ) ? $_POST['application_id'] : 0;
        $applicant_id   = isset( $_POST['applicant_id'] ) ? $_POST['applicant_id'] : 0;
        $status         = isset( $_POST['status'] ) ? $_POST['status'] : '';
        $admin_user_id  = isset( $_POST['admin_user_id'] ) ? $_POST['admin_user_id'] : 0;

        if ( erp_rec_has_status( $applicant_id ) ) { // if true then update status peoplesmeta table
            $wpdb->update( "{$wpdb->prefix}erp_peoplemeta",
                array( 'meta_value' => $status ),
                array( 'erp_people_id' => $applicant_id, 'meta_key' => 'status' ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            $this->send_success( __( 'Status updated successfully', 'wp-erp-rec' ) );
        } else { // insert status to peoplesmeta table
            $data = array(
                'erp_people_id' => $applicant_id,
                'meta_key'      => 'status',
                'meta_value'    => $status
            );

            $format = array(
                '%d',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );

            $this->send_success( __( 'Status posted successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Manage post rating
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function manager_rating() {
        global $wpdb;
        $this->verify_nonce( 'wp_erp_rec_applicant_rating_nonce' );

        $application_id = isset( $_POST['application_id'] ) ? $_POST['application_id'] : 0;
        $rating         = isset( $_POST['rating'] ) ? $_POST['rating'] : 0;
        $admin_user_id  = isset( $_POST['admin_user_id'] ) ? $_POST['admin_user_id'] : 0;

        if ( erp_rec_has_rating( $application_id, $admin_user_id ) ) {
            $wpdb->update( "{$wpdb->prefix}erp_application_rating",
                array( 'rating' => $rating ),
                array( 'application_id' => $application_id, 'user_id' => $admin_user_id ),
                array( '%d' ),
                array( '%d', '%d' )
            );
            $this->send_success( __( 'Rating updated successfully', 'wp-erp-rec' ) );
        } else {
            $data = array(
                'application_id' => $application_id,
                'rating'         => $rating,
                'user_id'        => $admin_user_id
            );

            $format = array(
                '%d',
                '%d',
                '%d'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_application_rating', $data, $format );

            $this->send_success( __( 'Rating posted successfully', 'wp-erp-rec' ) );
        }

    }

    /**
     * Get exam details
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_exam_details() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT exam_detail
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.id='" . $_GET['application_id'] . "'";
            $this->send_success( json_decode( $wpdb->get_var( $query ) ), true );
        } else {
            $this->send_success( [ ] );
        }
    }

    /*
    * get specific application rating
    *
    *
    * return array
    */
    public function get_application_rating() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT users.ID as uid, app_rating.rating as urating, users.display_name as display_name
                FROM {$wpdb->prefix}erp_application_rating as app_rating
                LEFT JOIN {$wpdb->base_prefix}users as users
                ON app_rating.user_id = users.ID
                WHERE app_rating.application_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $_GET['application_id'] ), ARRAY_A );

            $user_pic_data = [ ];
            foreach ( $udata as $ud ) {
                $user_pic_data[] = array(
                    'uid'          => $ud['uid'],
                    'display_name' => $ud['display_name'],
                    'user_pic'     => get_avatar( $ud['uid'], 32 ),
                    'rating'       => $ud['urating']
                );
            }
            $this->send_success( $user_pic_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /*
    * get applicants average rating
    * para custom post id
    * return float
    */
    public function get_applicants_avg_rating() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT FORMAT( AVG( rating ), 1 ) FROM {$wpdb->prefix}erp_application_rating WHERE application_id = " . $_GET['application_id'];
            $this->send_success( ( $wpdb->get_var( $query ) == null ) ? 0 : $wpdb->get_var( $query ) );
        } else {
            $this->send_success( 0 );
        }
    }

    /**
     * Job seeker create
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function job_seeker_create() {
        global $wpdb;
        $this->verify_nonce( 'wp-erp-rec-job-seeker-nonce' );

        // default fields
        $first_name             = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
        $last_name              = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
        $email                  = isset( $_POST['email'] ) ? $_POST['email'] : '';
        $captcha_result         = isset( $_POST['captcha_result'] ) ? $_POST['captcha_result'] : '';
        $captcha_correct_result = isset( $_POST['captcha_correct_result'] ) ? $_POST['captcha_correct_result'] : 0;
        $job_id                 = isset( $_POST['job_id'] ) ? $_POST['job_id'] : 0;

        $db_persoanl_fields  = get_post_meta( $job_id, '_personal_fields' );
        $meta_key            = '_personal_fields';
        $personal_field_data = $wpdb->get_var(
            $wpdb->prepare( "SELECT meta_value
                                    FROM {$wpdb->prefix}postmeta
                                    WHERE meta_key = %s AND post_id = %d", $meta_key, $job_id ) );
        $personal_field_data = maybe_unserialize( $personal_field_data );

        // convert object to array
        $db_persoanl_fields_array = [ ];
        if ( isset( $db_persoanl_fields ) ) {
            foreach ( $db_persoanl_fields as $dbf ) {
                $db_persoanl_fields_array[] = (array) $dbf;
            }
        }

        if ( isset( $_FILES['erp_rec_file']['name'] ) ) {
            $file_name             = $_FILES['erp_rec_file']['name'];
            $file_size             = ceil( $_FILES['erp_rec_file']['size'] / 1024 ); //size in killobites
            $file_type             = $_FILES['erp_rec_file']['type'];
            $file_tmp_name         = $_FILES['erp_rec_file']['tmp_name'];
            $file_extension_holder = explode( '.', $file_name );
            $file_extension        = end( $file_extension_holder );
        }
        $attach_info['attach_id'] = '';

        //personal data validation
        foreach ( $db_persoanl_fields_array as $db_data ) {
            if ( $db_data['req'] == true ) {
                if ( $_POST[$db_data['field']] == "" ) {
                    $this->send_error( __( 'please enter ' . str_replace( '_', ' ', $db_data['field'] ), 'wp-erp-rec' ) );
                }
            }
        }

        $question_answer = [ ];
        if ( isset( $_POST['question'] ) ) {
            $qset            = $_POST['question'];
            $aset            = isset( $_POST['answer'] ) ? $_POST['answer'] : [ ];
            $question_answer = array_combine( $qset, $aset );
        }

        if ( !isset( $first_name ) || $first_name == '' ) {
            $this->send_error( [ 'type' => 'first-name-error', 'message' => __( 'First name is empty', 'wp-erp-rec' ) ] );
        } elseif ( preg_match('#[\d]#', str_replace( ' ', '', $first_name ) ) === true ) {
            $this->send_error( [ 'type' => 'first-name-error', 'message' => __( 'First name must only contain letters and space!', 'wp-erp-rec' ) ] );
        } elseif ( preg_match('#[\d]#', str_replace( ' ', '', $last_name ) ) === true ) {
            $this->send_error( [ 'type' => 'last-name-error', 'message' => __( 'Last name must only contain letters and space!', 'wp-erp-rec' ) ] );
        } elseif ( !isset( $last_name ) || $last_name == '' ) {
            $this->send_error( [ 'type' => 'last-name-error', 'message' => __( 'Last name is empty', 'wp-erp-rec' ) ] );
        } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $this->send_error( [ 'type' => 'invalid-email', 'message' => __( 'Invalid Email', 'wp-erp-rec' ) ] );
        } elseif ( erp_rec_is_duplicate_email( $email, $job_id ) ) {
            $this->send_error( [ 'type' => 'duplicate-email', 'message' => __( 'E-mail address already exist', 'wp-erp-rec' ) ] );
        } elseif ( !isset( $file_name ) || $file_name == "" ) {
            $this->send_error( [ 'type' => 'file-error', 'message' => __( 'Please upload your cv ( .doc, .docx or .pdf file only )', 'wp-erp-rec' ) ] );
        } elseif ( $captcha_result != $captcha_correct_result ) {
            $this->send_error( [ 'type' => 'captcha-error', 'message' => __( 'Incorrect Captcha', 'wp-erp-rec' ) ] );
        } else {
            if ( isset( $file_name ) && $file_name != "" ) { // user upload cv so check file validation now
                if ( $file_size > 2048 ) {
                    $this->send_error( [ 'type' => 'file-error', 'message' => __( 'File size is greater than 2MB', 'wp-erp-rec' ) ] );
                //} elseif ( $file_type != "application/msword" && $file_type != "application/pdf" && $file_extension != "pdf" && $file_type != "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ) {
				} elseif ( $file_extension != "doc" && $file_extension != "pdf" && $file_extension != "docx" ) {
				//} elseif ( $file_type != "application/msword" && $file_type != "application/pdf" && $file_type != "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ) {
                    $this->send_error( [ 'type' => $file_type, 'message' => __( 'Please upload doc, pdf or docx only', 'wp-erp-rec' ) ] );
                } else {
                    // wp way file upload
                    $upload      = array(
                        'name'     => $_FILES['erp_rec_file']['name'],
                        'type'     => $_FILES['erp_rec_file']['type'],
                        'tmp_name' => $_FILES['erp_rec_file']['tmp_name'],
                        'error'    => $_FILES['erp_rec_file']['error'],
                        'size'     => $_FILES['erp_rec_file']['size']
                    );
                    $attach_info = erp_rec_handle_upload( $upload );
                }
            }

            $data = array(
                'first_name' => strip_tags( $first_name ),
                'last_name'  => strip_tags( $last_name ),
                'email'      => $email
            );

            $format = array(
                '%s',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoples', $data, $format );
            $jobseeker_id = $wpdb->insert_id;

            //insert applicant attach cv id
            $data = array(
                'erp_people_id' => $jobseeker_id,
                'meta_key'      => 'attach_id',
                'meta_value'    => $attach_info['attach_id']
            );

            $format = array(
                '%d',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );

            //insert applicant IP Address
            $data = array(
                'erp_people_id' => $jobseeker_id,
                'meta_key'      => 'ip',
                'meta_value'    => $_SERVER['REMOTE_ADDR']
            );

            $format = array(
                '%d',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );

            // get the first or default stage for this applicant
            $stage_id = $wpdb->get_var( "SELECT stageid FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid='" . $job_id . "' ORDER BY id LIMIT 1" );

            // personal fields that is coming dynamically
            foreach ( $personal_field_data as $db_data ) {
                if ( json_decode( $db_data )->showfr == true ) {
                    if ( json_decode( $db_data )->type == 'checkbox' ) {
                        if ( isset( $_POST[json_decode( $db_data )->field] ) ) {
                            $data   = array(
                                'erp_people_id' => $jobseeker_id,
                                'meta_key'      => json_decode( $db_data )->field,
                                'meta_value'    => implode( ",", $_POST[json_decode( $db_data )->field] )
                            );
                            $format = array(
                                '%d',
                                '%s',
                                '%s'
                            );
                            $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );
                        }
                    } else {
                        $data   = array(
                            'erp_people_id' => $jobseeker_id,
                            'meta_key'      => json_decode( $db_data )->field,
                            'meta_value'    => isset( $_POST[json_decode( $db_data )->field] ) ? $_POST[json_decode( $db_data )->field] : ''
                        );
                        $format = array(
                            '%d',
                            '%s',
                            '%s'
                        );
                        $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );
                    }
					
					if (json_decode( $db_data )->field == 'mobile') {
						$mobile = isset( $_POST[json_decode( $db_data )->field] ) ? $_POST[json_decode( $db_data )->field] : '';
					}
                }
            }

            //insert job id and applicant id to application table
            $data = array(
                'job_id'       => $job_id,
                'applicant_id' => $jobseeker_id,
                'stage'        => ( $stage_id == NULL ) ? 1 : $stage_id,
                'exam_detail'  => json_encode( $question_answer )
            );

            $format = array(
                '%d',
                '%d',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_application', $data, $format );

            do_action( 'erp_rec_applied_job', $data );

            /*send an email to admin that a new applicant has been applied*/
            // get email address of hiring manager
            $hiring_manager_ids = get_post_meta( $job_id, '_hiring_lead', true );
            $job_title = get_the_title($job_id);
            $subject = sprintf(__('A new applicant has been applied - %1$s (%2$s)', 'wp-erp-rec'),
				$first_name . ' ' . $last_name,
				$job_title);
			$message = '';
			$message .= __('Job title : ', 'wp-erp-rec') . $job_title;
			$message .= '<br /><br /><strong>' . __('Applicant details', 'wp-erp-rec') . '</strong>';
			$message .= '<br />' . __('Name : ', 'wp-erp-rec') . $first_name . ' ' . $last_name;
			$message .= '<br />' . __('Email : ', 'wp-erp-rec') . $email;
			$message .= '<br />' . __('Mobile : ', 'wp-erp-rec') . $mobile;
			$message .= '<br /><a href="' . wp_get_attachment_url($attach_info['attach_id']) . '">CV</a>';
            $headers[] = "Content-type: text/html";
            foreach ( $hiring_manager_ids as $hid ) {
                $employee_object = new \WeDevs\ERP\HRM\Employee( intval( $hid ) );
                wp_mail( $employee_object->user->user_email, $subject, $message, $headers );
            }
			
			$subject = 'Application received (%1$s)';
			$subject = sprintf(__('Application received (%1$s)', 'wp-erp-rec'),
				$job_title);
				
			setlocale(LC_ALL, "es_ES");
			
			$default_local_date = ucwords(utf8_encode(strftime("%A, %e de %B de %Y")));

			$date_connectors_capital = array('De', 'Del');
			$date_connectors_lower = array('de', 'del');

			$local_date = str_replace($date_connectors_capital, $date_connectors_lower, $default_local_date);
			
			$message = sprintf(__(
				'Job title: %1$s - %5$s
<br/><br/>Thank you for submitting your application.
<br/><br/><strong>Applicant details</strong>
<br/>Name: %2$s
<br/>Email: %3$s
<br/>Mobile: %4$s
<br/><br/>Best regards', 'wp-erp-rec'),
				$job_title,
				$first_name . ' ' . $last_name,
				$email,
				$mobile,
				$local_date);
			
            $headers[] = "Content-type: text/html";
			wp_mail( $email, $subject, $message, $headers );

            $this->send_success( [ 'message' => __( 'Your application has been received successfully. Thank you for applying.', 'wp-erp-rec' ) ] );
        }
    }

    /**
     * Candidate create from admin side
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_create_candidate() {
        global $wpdb;

        $this->verify_nonce( 'wp-erp-rec-job-seeker-nonce' );

        // default fields
        $first_name             = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
        $last_name              = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
        $email                  = isset( $_POST['email'] ) ? $_POST['email'] : '';
        $captcha_result         = isset( $_POST['captcha_result'] ) ? $_POST['captcha_result'] : '';
        $captcha_correct_result = isset( $_POST['captcha_correct_result'] ) ? $_POST['captcha_correct_result'] : 0;
        $job_id                 = isset( $_POST['job_id'] ) ? $_POST['job_id'] : 0;
        $attach_id              = isset( $_POST['attach_id'] ) ? $_POST['attach_id'] : 0;

        $db_persoanl_fields  = get_post_meta( $job_id, '_personal_fields' );
        $meta_key            = '_personal_fields';
        $personal_field_data = $wpdb->get_var(
            $wpdb->prepare( "SELECT meta_value
                                    FROM {$wpdb->prefix}postmeta
                                    WHERE meta_key = %s AND post_id = %d", $meta_key, $job_id ) );
        $personal_field_data = maybe_unserialize( $personal_field_data );
        // convert object to array
        $db_persoanl_fields_array = [ ];
        if ( isset( $db_persoanl_fields ) ) {
            foreach ( $db_persoanl_fields as $dbf ) {
                $db_persoanl_fields_array[] = (array) $dbf;
            }
        }

        //personal data validation
        foreach ( $db_persoanl_fields_array as $db_data ) {
            if ( $db_data['req'] == true ) {
                if ( $_POST[$db_data['field']] == "" ) {
                    $this->send_error( __( 'please enter ' . str_replace( '_', ' ', $db_data['field'] ), 'wp-erp-rec' ) );
                }
            }
        }

        if ( !isset( $first_name ) || $first_name == '' ) {
            $this->send_error( [ 'type' => 'first-name-error', 'message' => __( 'First name is empty', 'wp-erp-rec' ) ] );
        } elseif ( preg_match('#[\d]#', str_replace( ' ', '', $first_name ) ) === true ) {
            $this->send_error( [ 'type' => 'first-name-error', 'message' => __( 'First name must only contain letters and space!', 'wp-erp-rec' ) ] );
        } elseif ( preg_match('#[\d]#', str_replace( ' ', '', $last_name ) ) === true ) {
            $this->send_error( [ 'type' => 'last-name-error', 'message' => __( 'Last name must only contain letters and space!', 'wp-erp-rec' ) ] );
        } elseif ( !isset( $last_name ) || $last_name == '' ) {
            $this->send_error( [ 'type' => 'last-name-error', 'message' => __( 'Last name is empty', 'wp-erp-rec' ) ] );
        } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $this->send_error( [ 'type' => 'invalid-email', 'message' => __( 'Invalid Email', 'wp-erp-rec' ) ] );
        } elseif ( erp_rec_is_duplicate_email( $email, $job_id ) ) {
            $this->send_error( [ 'type' => 'duplicate-email', 'message' => __( 'E-mail address already exist', 'wp-erp-rec' ) ] );
        } elseif ( !isset( $attach_id ) || $attach_id == "" ) {
            $this->send_error( [ 'type' => 'file-error', 'message' => __( 'Please upload your cv ( .doc or .pdf file only )', 'wp-erp-rec' ) ] );
        } else {

            $data = array(
                'first_name' => strip_tags( $first_name ),
                'last_name'  => strip_tags( $last_name ),
                'email'      => $email
            );

            $format = array(
                '%s',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoples', $data, $format );
            $jobseeker_id = $wpdb->insert_id;

            //insert applicant attach cv id
            $data = array(
                'erp_people_id' => $jobseeker_id,
                'meta_key'      => 'attach_id',
                'meta_value'    => $attach_id
            );

            $format = array(
                '%d',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );

            // get the first or default stage for this applicant
            $stage_id = $wpdb->get_var( "SELECT stageid FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid='" . $job_id . "' ORDER BY id LIMIT 1" );

            // personal fields that is coming dynamically
            foreach ( $personal_field_data as $db_data ) {
                if ( json_decode( $db_data )->showfr == true ) {
                    $data   = array(
                        'erp_people_id' => $jobseeker_id,
                        'meta_key'      => json_decode( $db_data )->field,
                        'meta_value'    => isset( $_POST[json_decode( $db_data )->field] ) ? $_POST[json_decode( $db_data )->field] : ''
                    );
                    $format = array(
                        '%d',
                        '%s',
                        '%s'
                    );
                    $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );
                }
            }

            //insert job id and applicant id to application table
            $data = array(
                'job_id'       => $job_id,
                'applicant_id' => $jobseeker_id,
                'stage'        => ( $stage_id == NULL ) ? 1 : $stage_id,
                'exam_detail'  => json_encode( [ ] ),
                'added_by'     => get_current_user_id()
            );

            $format = array(
                '%d',
                '%d',
                '%s',
                '%s',
                '%d'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_application', $data, $format );

            $this->send_success( [ 'message' => __( 'Thank you for applying', 'wp-erp-rec' ) ] );
        }
    }

    /**
     * Sending email
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_email() {
        $this->verify_nonce( 'wp_erp_rec_sendEmail_nonce' );
        $to      = isset( $_POST['to'] ) ? $_POST['to'] : '';
        $subject = isset( $_POST['subject'] ) ? $_POST['subject'] : '';
        $message = isset( $_POST['emessage'] ) ? $_POST['emessage'] : '';
		
		//echo $to . '<br />' . $subject . '<br />' . $message . '<br />';

        if ( !isset( $to ) || $to == '' ) {
            $this->send_error( __( 'You did not select any applicant to send email', 'wp-erp-rec' ) );
        } elseif ( !isset( $subject ) || $subject == '' ) {
            $this->send_error( __( 'Subject cannot be empty', 'wp-erp-rec' ) );
        } elseif ( !isset( $message ) || $message == '' ) {
            $this->send_error( __( 'Message cannot be empty', 'wp-erp-rec' ) );
        } else {
            $headers[] = "Content-type: text/html";
            if (wp_mail( $to, $subject, $message, $headers )) {
				$this->send_success( __( 'Email sent successfully', 'wp-erp-rec' ) );
			} else {
				$this->send_error( __( 'An error occured when sending email', 'wp-erp-rec' ) );
			}
        }
    }

    /**
     * Create todos
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function create_todo() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $params = [];

        parse_str( $_POST['fdata'], $params );

        $todotitle      = $params['todo_title'];
        $application_id = $params['todo_application_id'];
        $assign_user_id = $params['assign_user_id'];
        $deadlinedate   = $params['deadlinedate'];
        $deadlinetime   = $params['deadlinetime'];

        $current_date = date_create( date( 'Y-m-d' ) );
        $given_date   = date_create( $deadlinedate );
        $diff         = date_diff( $current_date, $given_date );

        if ( !isset( $todotitle ) ) {
            $this->send_error( __( 'Title cannot be empty!', 'wp-erp-rec' ) );
        } elseif ( count( $assign_user_id ) == 0 ) {
            $this->send_error( __( 'Please assign user for this todo!', 'wp-erp-rec' ) );
        } elseif ( isset( $deadlinedate ) && $diff->format( "%r%a" ) < 0 ) {
            $this->send_error( __( 'invalid deadline - pick today or any next day', 'wp-erp-rec' ) );
        } else {
            $todo_array_ids = [ ];
            foreach ( $assign_user_id as $value ) {
                array_push( $todo_array_ids, $value );
            }
            $todo_ids = implode( ',', $todo_array_ids );

            //insert todos
            $data = array(
                'title'          => $todotitle,
                'application_id' => $application_id,
                'deadline_date'  => ( $deadlinedate == '' ? '' : date( 'Y-m-d H:i:s', strtotime( "$deadlinedate $deadlinetime" ) ) ),
                'created_by'     => get_current_user_id(),
                'created_at'     => date( 'Y-m-d H:i:s', time() )
            );

            $format = array(
                '%s',
                '%d',
                '%s',
                '%s',
                '%s'
            );
            $wpdb->insert( $wpdb->prefix . 'erp_application_todo', $data, $format );
            $todo_id = $wpdb->insert_id;

            //insert assigned users in relation table
            $assigned_users_ids = '';
            foreach ( $assign_user_id as $value ) {
                $assigned_users_ids .= ',' . $value;
                $todo_data = array(
                    'todo_id'          => $todo_id,
                    'assigned_user_id' => $value
                );

                $format = array(
                    '%d',
                    '%d'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_todo_relation', $todo_data, $format );
            }

            // send email
            $data['assigned_user_id'] = $assigned_users_ids;
            $email                    = new Emails\New_Todo();
            $email->trigger( $data );
            $this->send_success( __( 'To-do created successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get todos
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_todo() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT app_todo.id, app_todo.title, app_todo.deadline_date, app_todo.status
                        FROM {$wpdb->prefix}erp_application_todo as app_todo
                        WHERE app_todo.application_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $_GET['application_id'] ), ARRAY_A );

            $user_data = [ ];
            foreach ( $udata as $ud ) {

                $query_assigned_user = "SELECT user.display_name
                                        FROM {$wpdb->prefix}erp_application_todo_relation as app_todo_relation
                                        LEFT JOIN {$wpdb->base_prefix}users as user
                                        ON app_todo_relation.assigned_user_id=user.ID
                                        WHERE app_todo_relation.todo_id='%d'";
                $urelationdata       = $wpdb->get_results( $wpdb->prepare( $query_assigned_user, $ud['id'] ), ARRAY_A );

                $todo_handler_name = '';
                foreach ( $urelationdata as $todo_handler_dname ) {
                    $todo_handler_name .= ',' . $todo_handler_dname['display_name'];
                }

                $user_data[] = array(
                    'id'            => $ud['id'],
                    'title'         => $ud['title'],
                    'deadline_date' => date( 'Y-m-d g:i A', strtotime( $ud['deadline_date'] ) ),
                    'display_name'  => $todo_handler_name,
                    'status'        => $ud['status'],
                    'is_overdue'    => ( date( 'Y-m-d', strtotime( $ud['deadline_date'] ) ) < date( 'Y-m-d', time() ) ? 1 : 0 )
                );
            }
            $this->send_success( $user_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Update todo status
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function update_todo_status() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $todo_id     = isset( $_POST['todo_id'] ) ? $_POST['todo_id'] : 0;
        $todo_status = isset( $_POST['todo_status'] ) ? $_POST['todo_status'] : '';
        if ( isset( $todo_id ) ) {

            //update todo to done
            $data = array(
                'status' => $todo_status
            );

            $where = array(
                'id' => $todo_id
            );

            $format = array(
                '%d'
            );

            $where_format = array(
                '%d'
            );

            $wpdb->update( $wpdb->prefix . 'erp_application_todo', $data, $where, $format, $where_format );
            $this->send_success( 'To-Do updated successfully' );
        } else {
            $this->send_error( 'To-Do updated operation failed' );
        }
    }

    /**
     * Delete todos
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function delete_todo() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $todo_id = isset( $_POST['todo_id'] ) ? $_POST['todo_id'] : 0;
        if ( isset( $todo_id ) ) {
            //delete todo
            $where        = array(
                'id' => $todo_id
            );
            $where_format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_application_todo', $where, $where_format );

            //delete todo from relation table
            $where        = array(
                'todo_id' => $todo_id
            );
            $where_format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_application_todo_relation', $where, $where_format );

            $this->send_success( __( 'To-Do deleted successfully', 'wp-erp-rec' ) );
        } else {
            $this->send_error( __( 'To-Do deleted operation failed', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get calendar for selected todos
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_cal_selected_todo() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT app_todo.id, app_todo.title, app_todo.deadline_date, app_todo.status
                        FROM {$wpdb->prefix}erp_application_todo as app_todo
                        WHERE app_todo.application_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $_GET['application_id'] ), ARRAY_A );

            $user_data = [ ];
            foreach ( $udata as $ud ) {

                $query_assigned_user = "SELECT user.display_name
                                        FROM {$wpdb->prefix}erp_application_todo_relation as app_todo_relation
                                        LEFT JOIN {$wpdb->base_prefix}users as user
                                        ON app_todo_relation.assigned_user_id=user.ID
                                        WHERE app_todo_relation.todo_id='%d'";
                $urelationdata       = $wpdb->get_results( $wpdb->prepare( $query_assigned_user, $ud['id'] ), ARRAY_A );

                $todo_handler_name = '';
                foreach ( $urelationdata as $todo_handler_dname ) {
                    $todo_handler_name .= ',' . $todo_handler_dname['display_name'];
                }

                $user_data[] = array(
                    'id'            => $ud['id'],
                    'title'         => $ud['title'],
                    'deadline_date' => date( 'Y-m-d g:i A', strtotime( $ud['deadline_date'] ) ),
                    'display_name'  => $todo_handler_name,
                    'status'        => $ud['status'],
                    'is_overdue'    => ( date( 'Y-m-d', strtotime( $ud['deadline_date'] ) ) < date( 'Y-m-d', time() ) ? 1 : 0 )
                );
            }
            $this->send_success( $user_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Create an interviews
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function create_interview() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $params = [ ];
        parse_str( $_POST['fdata'], $params );

        $type_of_interview               = $params['type_of_interview'];
        $type_of_interview_text          = $params['type_of_interview_text'];
        $internal_type_of_interview      = $params['internal_type_of_interview'];
        $internal_type_of_interview_text = $params['internal_type_of_interview_text'];
        $application_id                  = $params['interview_application_id'];
        $interview_detail                = $params['interview_detail'];
        $interview_tech                  = $params['interview_tech'];
        $interviewers                    = $params['interviewers'];
        $duration                        = $params['duration'];
        $interview_date                  = $params['interview_date'];
        $interview_time                  = $params['interview_time'];

        $current_date = date_create( date( 'Y-m-d' ) );
        $given_date   = date_create( $interview_date );
        $diff         = date_diff( $current_date, $given_date );

        if ( count( $type_of_interview ) == 0 ) {
            $this->send_error( __( 'Please input interviewer for this interview!', 'wp-erp-rec' ) );
        } elseif ( isset( $interview_date ) && $diff->format( "%r%a" ) < 0 ) {
            $this->send_error( __( 'Interview date cannot less than today!', 'wp-erp-rec' ) );
        } else {
            //insert interview
            $data = array(
                'interview_type_id'          => $type_of_interview,
                'interview_internal_type_id' => $internal_type_of_interview,
                'application_id'             => $application_id,
                'interview_detail'           => $interview_detail,
                'interview_tech'             => $interview_tech,
                'start_date_time'            => date( 'Y-m-d H:i:s', strtotime( "$interview_date $interview_time" ) ),
                'duration_minutes'           => $duration,
                'created_by'                 => get_current_user_id(),
                'created_at'                 => date( 'Y-m-d H:i:s', time() )
            );

            $format = array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_application_interview', $data, $format );
            $interview_id = $wpdb->insert_id;
            //insert interviewer in relation table
            $interviewer_ids = '';
            foreach ( $interviewers as $value ) {
                $interviewer_ids .= ',' . $value;
                $interview_data = array(
                    'interview_id'   => $interview_id,
                    'interviewer_id' => $value
                );

                $format = array(
                    '%d',
                    '%d'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_interviewer_relation', $interview_data, $format );
            }
			
			// Obtener datos del candidato
			$query = "SELECT app.id, people.first_name, people.last_name, people.email, meta.meta_value as mobile
                        FROM {$wpdb->prefix}erp_application as app
                        LEFT JOIN {$wpdb->prefix}erp_peoples as people
                        ON app.applicant_id=people.id
                        LEFT JOIN {$wpdb->prefix}erp_peoplemeta as meta
                        ON people.id=meta.erp_people_id AND meta_key='mobile'
                        WHERE app.id=%d";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
			
            // send email
			foreach ( $udata as $ud ) {
				$data['first_name'] = $ud['first_name'];
				$data['last_name'] = $ud['last_name'];
				$data['mobile'] = $ud['mobile'];
				$data['email'] = $ud['email'];
			}
			
			$data['link'] = admin_url('edit.php?post_type=erp_hr_recruitment&page=applicant_detail&application_id=' . $application_id);
			
            $data['interviewer_id']         = $interviewer_ids;
            $data['type_of_interview_text'] = $type_of_interview_text;
            $data['internal_type_of_interview_text'] = $internal_type_of_interview_text;
            $email                          = new Emails\New_Interview();
            $email->trigger( $data );
            $this->send_success( __( 'Interview created successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get interviews
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_interview() {
        if ( isset( $_GET['application_id'] ) ) {
            global $wpdb;

            $query = "SELECT app_inv.id, app_inv.interview_detail, app_inv.interview_tech, types.type_detail, app_inv.start_date_time, app_inv.duration_minutes, stage.title, app_inv.feedback_comment, app_inv.feedback_english_level, app_inv.feedback_english_conversation
                        FROM {$wpdb->prefix}erp_application_interview as app_inv
                        LEFT JOIN {$wpdb->prefix}erp_application_stage as stage
                        ON app_inv.interview_type_id=stage.id
						LEFT JOIN {$wpdb->prefix}erp_application_interview_types as types
						ON app_inv.interview_internal_type_id=types.id
                        WHERE app_inv.application_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $_GET['application_id'] ), ARRAY_A );

            $user_data = [ ];
            foreach ( $udata as $ud ) {

                $query_interviewer_user = "SELECT user.display_name
                                        FROM {$wpdb->prefix}erp_application_interviewer_relation as app_inv_relation
                                        LEFT JOIN {$wpdb->base_prefix}users as user
                                        ON app_inv_relation.interviewer_id=user.ID
                                        WHERE app_inv_relation.interview_id='%d'";
                $urelationdata          = $wpdb->get_results( $wpdb->prepare( $query_interviewer_user, $ud['id'] ), ARRAY_A );

                $interviewers_name = '';
				$isFirst = true;
                foreach ( $urelationdata as $interviewer_dname ) {
					if (!$isFirst) {
						$interviewers_name .= ', ';
					}
	
					$isFirst = false;
                    $interviewers_name .= $interviewer_dname['display_name'];
                }

                // make end time
                $minutes_to_add = $ud['duration_minutes'];
                $time           = new \DateTime( $ud['start_date_time'] );
                $time->add( new \DateInterval( 'PT' . $minutes_to_add . 'M' ) );
                $stamp = $time->format( 'g:i A' );

                $user_data[] = array(
                    'id'                            => $ud['id'],
                    'title'                         => $ud['title'],
                    'type_detail'                   => $ud['type_detail'],
                    'interview_detail'              => $ud['interview_detail'],
                    'interview_tech'                => $ud['interview_tech'],
                    'feedback_comment'              => $ud['feedback_comment'],
                    'feedback_english_level'        => $ud['feedback_english_level'],
                    'feedback_english_conversation' => $ud['feedback_english_conversation'],
                    'interview_time'                => date( 'Y-m-d g:i A', strtotime( $ud['start_date_time'] ) ) . ' - ' . $stamp,
                    'interview_date'                => date( 'Y-m-d', strtotime( $ud['start_date_time'] ) ),
                    'interview_timee'               => date( 'g:i A', strtotime( $ud['start_date_time'] ) ),
                    'duration'                      => $minutes_to_add,
                    'display_name'                  => $interviewers_name
                );

            }

            $this->send_success( $user_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Delete interviews
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function delete_interview() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );
        $interview_id = isset( $_POST['interview_id'] ) ? $_POST['interview_id'] : 0;

        if ( !isset( $interview_id ) ) {
            $this->send_error( __( 'Wrong move to delete!', 'wp-erp-rec' ) );
        } else {
            //delete interview from interview table
            $where = array(
                'id' => $interview_id
            );

            $format = array(
                '%d'
            );

            $wpdb->delete( $wpdb->prefix . 'erp_application_interview', $where, $format );

            //delete interview from interview relation table
            $where = array(
                'interview_id' => $interview_id
            );

            $format = array(
                '%d'
            );

            $wpdb->delete( $wpdb->prefix . 'erp_application_interviewer_relation', $where, $format );

            $this->send_success( __( 'Interview deleted successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Update an interviews
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function update_interview() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $interviewid = isset( $_POST['interview_id'] ) ? $_POST['interview_id'] : 0;
        $params      = [];

        parse_str( $_POST['fdata'], $params );

        $type_of_interview               = $params['type_of_interview'];
        $type_of_interview_text          = $params['type_of_interview_text'];
        $internal_type_of_interview      = $params['internal_type_of_interview'];
        $internal_type_of_interview_text = $params['internal_type_of_interview_text'];
        $application_id                  = $params['interview_application_id'];
        $interview_detail                = $params['interview_detail'];
        $interview_tech                  = $params['interview_tech'];
        $interviewers                    = $params['interviewers'];
        $duration                        = $params['duration'];
        $interview_date                  = $params['interview_date'];
        $interview_time                  = $params['interview_time'];

        $current_date = date_create( date( 'Y-m-d' ) );
        $given_date   = date_create( $interview_date );
        $diff         = date_diff( $current_date, $given_date );

        if ( !isset( $interviewid ) ) {
            $this->send_error( __( 'Interview ID not available!', 'wp-erp-rec' ) );
        } elseif ( count( $type_of_interview ) == 0 ) {
            $this->send_error( __( 'Please input interviewer for this interview!', 'wp-erp-rec' ) );
        } elseif ( isset( $interview_date ) && $diff->format( "%r%a" ) < 0 ) {
            $this->send_error( __( 'Interview date cannot less than today!', 'wp-erp-rec' ) );
        } else {
            //update interview
            $data = array(
                'interview_type_id'          => $type_of_interview,
                'interview_internal_type_id' => $internal_type_of_interview,
                'application_id'             => $application_id,
                'interview_detail'           => $interview_detail,
                'interview_tech'             => $interview_tech,
                'start_date_time'            => date( 'Y-m-d H:i:s', strtotime( "$interview_date $interview_time" ) ),
                'duration_minutes'           => $duration,
                'created_by'                 => get_current_user_id(),
                'created_at'                 => date( 'Y-m-d H:i:s', time() )
            );

            $where = array(
                'id' => $interviewid
            );

            $data_format = array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            );

            $where_format = array(
                '%d'
            );

            $wpdb->update( $wpdb->prefix . 'erp_application_interview', $data, $where, $data_format, $where_format );
            //first delete all interviewers from relation table where interview id
            $where        = array(
                'interview_id' => $interviewid
            );
            $where_format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_application_interviewer_relation', $where, $where_format );
            //now insert interviewer in relation table
            $interviewer_ids = '';
            foreach ( $interviewers as $value ) {
                $interviewer_ids .= ',' . $value;
                $interview_data = array(
                    'interview_id'   => $interviewid,
                    'interviewer_id' => $value
                );

                $format = array(
                    '%d',
                    '%d'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_interviewer_relation', $interview_data, $format );
            }
            $this->send_success( __( 'Interview updated successfully', 'wp-erp-rec' ) );
        }
    }
  
    /**
     * Upload a CV
     *
     * @since  1.0.2
     *
     * @return void
     */
    public function upload_cv() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $params = [ ];
        parse_str( $_POST['fdata'], $params );

        $jobseeker_id = $params['attachment_applicant_id'];
      
        if ( isset( $_FILES['erp_rec_file']['name'] ) ) {
          $file_name             = $_FILES['erp_rec_file']['name'];
          $file_size             = $_FILES['erp_rec_file']['size']; //size in killobites
          $file_type             = $_FILES['erp_rec_file']['type'];
          $file_tmp_name         = $_FILES['erp_rec_file']['tmp_name'];
          $file_error            = $_FILES['erp_rec_file']['error'];
          
          $attach_info['attach_id'] = '';

          // wp way file upload
          $upload      = array(
            'name'     => $file_name,
            'type'     => $file_type,
            'tmp_name' => $file_tmp_name,
            'error'    => $file_error,
            'size'     => $file_size
          );
          $attach_info = erp_rec_handle_upload( $upload );
          
          //insert applicant attach cv id
          $data = array(
            'erp_people_id' => $jobseeker_id,
            'meta_key'      => 'attach_id',
            'meta_value'    => $attach_info['attach_id']
          );
          
          $format = array(
            '%d',
            '%s',
            '%s'
          );
          
          $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );
          
          $this->send_success( [ 'message' => __( 'CV Uploaded', 'wp-erp-rec' ) ] );
        } else {
          $this->send_error( __( 'No file to upload sent', 'wp-erp-rec' ) );
        }
    }
    
    /**
     * Update a feedback
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function update_feedback() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $interviewid = isset( $_POST['interview_id'] ) ? $_POST['interview_id'] : 0;
        $params      = [];

        parse_str( $_POST['fdata'], $params );

        $application_id                  = $params['interview_application_id'];
        $feedback_comment                = $params['feedback_comment'];
        $feedback_english_level          = $params['feedback_english_level_text'];
        $feedback_english_conversation   = $params['feedback_english_conversation_text'];

        if ( !isset( $interviewid ) ) {
            $this->send_error( __( 'Interview ID not available!', 'wp-erp-rec' ) );
        } else {
            //update interview
            $data = array(
                'application_id'                => $application_id,
                'feedback_comment'              => $feedback_comment,
                'feedback_english_level'        => $feedback_english_level,
                'feedback_english_conversation' => $feedback_english_conversation
            );

            $where = array(
                'id' => $interviewid
            );

            $data_format = array(
                '%d',
                '%s',
                '%s',
                '%s'
            );

            $where_format = array(
                '%d'
            );

            $wpdb->update( $wpdb->prefix . 'erp_application_interview', $data, $where, $data_format, $where_format );
            
            $this->send_success( __( 'Feedback updated successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get recruitment stages
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function rec_get_stage() {
        global $wpdb;
        $jobid = $_GET['jobid'];
        if ( isset( $jobid ) ) {
            $query       = "SELECT COUNT(id) FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid={$jobid}";
            $row_counter = $wpdb->get_var( $query );
            if ( $row_counter > 0 ) {
                $query = "SELECT stage.id as sid,stage.title,
                          ( SELECT stageid
                            FROM {$wpdb->prefix}erp_application_job_stage_relation
                            WHERE jobid={$jobid} AND stageid=sid ) as stage_selected
                          FROM {$wpdb->prefix}erp_application_stage as stage ORDER BY stage.stage_order";
                $qdata = [ ];
                $udata = $wpdb->get_results( $query, ARRAY_A );
                foreach ( $udata as $ud ) {
                    $qdata[] = [
                        'sid'            => $ud['sid'],
                        'title'          => $ud['title'],
                        'stage_selected' => $ud['stage_selected'] === NULL ? false : $ud['sid']
                    ];
                }
                $this->send_success( $qdata );
            } else {
                $query = "SELECT stage.id, stage.title
                FROM {$wpdb->prefix}erp_application_stage as stage
                LEFT JOIN {$wpdb->base_prefix}users as user
                ON stage.created_by = user.ID";

                $udata = $wpdb->get_results( $query, ARRAY_A );
                $this->send_success( $udata );
            }
        } else {
            $this->send_error( __( 'something went wrong!', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get applications statges
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_application_stage() {
        global $wpdb;
        $job_id = $_GET['job_id'];
        $query  = "SELECT stage.id, stage.title
                FROM {$wpdb->prefix}erp_application_stage as stage
                WHERE stage.post_id=%d";
        $udata  = $wpdb->get_results( $wpdb->prepare( $query, $job_id ), ARRAY_A );
        $this->send_success( $udata );
    }

    /**
     * Delete application status
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function delete_application_stage() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $stage_title = isset( $_POST['stage_title'] ) ? $_POST['stage_title'] : '';
        $jobid       = isset( $_POST['job_id'] ) ? $_POST['job_id'] : 0;

        if ( isset( $jobid ) ) {
            // check this stage has candidate or not
            if ( erp_rec_has_candidate( $jobid, $stage_title ) ) {
                $this->send_error( __( 'This Stage have candidate(s), so you cannot delete it now! You can delete this stage only after moving them(candidate) to other stage(s)', 'wp-erp-rec' ) );
            } elseif ( erp_rec_count_stage( $jobid ) < 2 ) {
                $this->send_error( __( 'At-least one stage required', 'wp-erp-rec' ) );
            } else {
                $where = array(
                    'title'   => $stage_title,
                    'post_id' => $jobid
                );

                $format = array(
                    '%s',
                    '%d'
                );

                $wpdb->delete( $wpdb->prefix . 'erp_application_stage', $where, $format );
                $this->send_success( __( 'Stage deleted successfully', 'wp-erp-rec' ) );
            }
        }
    }

    /**
     * Get temporary states
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function get_temporary_stage() {
        $this->send_success( erp_rec_get_hiring_stages() );
    }

    /**
     * Create stage
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function create_stage() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );
        $stage_title = isset( $_POST['stage_title'] ) ? $_POST['stage_title'] : '';
        $jobs_ide    = isset( $_POST['job_id'] ) ? $_POST['job_id'] : 0;
        if ( erp_rec_check_duplicate_stage( $stage_title ) ) {
            $this->send_error( __( 'Stage title already exist!', 'wp-erp-rec' ) );
        } else {
            if ( isset( $stage_title ) ) {
                $data = array(
                    'title'      => $stage_title,
                    'created_by' => get_current_user_id(),
                    'created_at' => date( 'Y-m-d H:i:s', time() )
                );

                $format = array(
                    '%s',
                    '%d',
                    '%s'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_stage', $data, $format );
                $this->send_success( __( 'Stage created successfully', 'wp-erp-rec' ) );
            } else {
                $this->send_error( __( 'Stage title error!', 'wp-erp-rec' ) );
            }
        }
    }

    /**
     * Add application stage
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_application_stage() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $stage_title = isset( $_POST['stage_title'] ) ? $_POST['stage_title'] : '';
        $job_id      = isset( $_POST['job_id'] ) ? $_POST['job_id'] : 0;

        if ( erp_rec_check_duplicate_stage( $stage_title ) ) {
            $this->send_error( __( 'Stage title already exist!', 'wp-erp-rec' ) );
        } else {
            if ( isset( $stage_title ) ) {
                $data = array(
                    'title'      => $stage_title,
                    'created_by' => get_current_user_id(),
                    'created_at' => date( 'Y-m-d H:i:s', time() )
                );

                $format = array(
                    '%s',
                    '%d',
                    '%s'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_stage', $data, $format );
                $latest_stage_id = $wpdb->insert_id;

                $data = array(
                    'jobid'   => $job_id,
                    'stageid' => $latest_stage_id
                );

                $format = array(
                    '%d',
                    '%d'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_application_job_stage_relation', $data, $format );
                $this->send_success( __( 'Stage created successfully', 'wp-erp-rec' ) );
            } else {
                $this->send_error( __( 'Stage title error!', 'wp-erp-rec' ) );
            }
        }
    }

    /**
     * Delete stage
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function delete_stage() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $stage_id = isset( $_POST['stage_id'] ) ? $_POST['stage_id'] : 0;

        if ( isset( $stage_id ) ) {
            $where = array(
                'id' => $stage_id
            );

            $format = array(
                '%d'
            );

            $wpdb->delete( $wpdb->prefix . 'erp_application_stage', $where, $format );
            $this->send_success( __( 'Stage deleted successfully', 'wp-erp-rec' ) );
        }
    }

    /**
     * Change stages
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function change_stage() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $application_id = isset( $_POST['application_id'] ) ? $_POST['application_id'] : 0;
        $stage_id       = isset( $_POST['stage_id'] ) ? $_POST['stage_id'] : 0;

        if ( isset( $stage_id ) ) {
            $data         = [
                'stage' => $stage_id
            ];
            $where        = [
                'id' => $application_id
            ];
            $data_format  = [ '%d' ];
            $where_format = [ '%d' ];
            $wpdb->update( $wpdb->prefix . 'erp_application', $data, $where, $data_format, $where_format );
            $this->send_success( __( 'Stage changed successfully', 'wp-erp-rec' ) );
        } else {
            $this->send_error( __( 'Something went wrong!', 'wp-erp-rec' ) );
        }
    }

    /**
     * Change recrutment statuses
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function change_status() {
        global $wpdb;
        $this->verify_nonce( 'recruitment_form_builder_nonce' );

        $application_id = isset( $_POST['application_id'] ) ? $_POST['application_id'] : 0;
        $status_name    = isset( $_POST['status_name'] ) ? $_POST['status_name'] : '';

        if ( isset( $status_name ) ) {
            $query    = "SELECT applicant_id FROM {$wpdb->prefix}erp_application WHERE id=" . $application_id;
            $peopleid = $wpdb->get_var( $query );
            erp_people_update_meta( $peopleid, 'status', $status_name );
            if ( $status_name == 'shortlisted' ) {
                do_action( 'erp_rec_shortlisted_applicants', $application_id );
            }
            $this->send_success( __( 'status changed successfully', 'wp-erp-rec' ) );
        } else {
            $this->send_error( __( 'Something went wrong!', 'wp-erp-rec' ) );
        }
    }

    /**
     * Get todo calender overviews
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_overview() {
        global $wpdb;

        $query_overdue = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                          FROM {$wpdb->prefix}erp_application_todo as todo
                          WHERE todo.deadline_date < CURRENT_DATE AND status=0";

        $query_today = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                        FROM {$wpdb->prefix}erp_application_todo as todo
                        WHERE date(todo.deadline_date) = CURRENT_DATE AND status=0";

        $query_later = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                        FROM {$wpdb->prefix}erp_application_todo as todo
                        WHERE date(todo.deadline_date) > CURRENT_DATE AND status=0";


        $events_array = $wpdb->get_results( $query_overdue, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#AE3B3B',
            );
        }

        $events_array = $wpdb->get_results( $query_today, ARRAY_A );

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#3A87AD',
            );
        }

        $events_array = $wpdb->get_results( $query_later, ARRAY_A );

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#39994F',
            );
        }

        ?>
  <script>
    ;
    jQuery(document).ready(function($) {

      $('#todo-calendar-overview').fullCalendar({
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,agendaWeek,agendaDay'
        },
        editable: false,
        eventLimit: true,
        events: <?php echo json_encode( $events ); ?>,
        eventRender: function(event, element, calEvent) {
          if (event.holiday) {
            element.find('.fc-content').find('.fc-title').css({
              'top': '0px',
              'left': '3px',
              'fontSize': '13px',
              'padding': '2px'
            });
          }
        },
        eventClick: function(event, jsEvent, view) {
          $.erpPopup({
            title: wpErpRec.todo_description_popup.title,
            button: wpErpRec.todo_description_popup.close,
            id: 'new-todo-popup',
            content: wp.template('erp-rec-todo-description-template')().trim(),
            extraClass: 'medium',
            onReady: function(modal) {
              $('#todo-description').text(event.title);
              $('#todo-deadline').text(event.deadline);
              $('#todo-assigned-user-list').text(event.assigned_user_list);
            },
            onSubmit: function(modal) {
              modal.closeModal();
            }
          });
        }
      });
    });

  </script>
  <?php
        exit;
    }

    /**
     * Get todo calendar overdues
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_overdue() {
        global $wpdb;

        $query = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                FROM {$wpdb->prefix}erp_application_todo as todo
                WHERE todo.deadline_date < CURRENT_DATE AND status=0";

        $events_array = $wpdb->get_results( $query, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#AE3B3B'
            );
        }

        ?>
    <script>
      ;
      jQuery(document).ready(function($) {

        $('#todo-calendar-overdue').fullCalendar({
          header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
          },
          editable: false,
          eventLimit: true,
          events: <?php echo json_encode( $events ); ?>,
          eventRender: function(event, element, calEvent) {
            if (event.holiday) {
              element.find('.fc-content').find('.fc-title').css({
                'top': '0px',
                'left': '3px',
                'fontSize': '13px',
                'padding': '2px'
              });
            }
          },
          eventClick: function(event, jsEvent, view) {
            $.erpPopup({
              title: wpErpRec.todo_description_popup.title,
              button: wpErpRec.todo_description_popup.close,
              id: 'new-todo-popup',
              content: wp.template('erp-rec-todo-description-template')().trim(),
              extraClass: 'medium',
              onReady: function(modal) {
                $('#todo-description').text(event.title);
                $('#todo-deadline').text(event.deadline);
                $('#todo-assigned-user-list').text(event.assigned_user_list);
              },
              onSubmit: function(modal) {
                modal.closeModal();
              }
            });
          }
        });
      });

    </script>
    <?php
        exit;
    }

    /**
     * Get todays todos in calendar
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_today() {
        global $wpdb;

        $query = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                FROM {$wpdb->prefix}erp_application_todo as todo
                WHERE date(todo.deadline_date) = CURRENT_DATE AND status=0";

        $events_array = $wpdb->get_results( $query, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => ''
            );
        }

        ?>
      <script>
        ;
        jQuery(document).ready(function($) {

          $('#todo-calendar-today').fullCalendar({
            header: {
              left: 'prev,next today',
              center: 'title',
              right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: true,
            events: <?php echo json_encode( $events ); ?>,
            eventRender: function(event, element, calEvent) {
              if (event.holiday) {
                element.find('.fc-content').find('.fc-title').css({
                  'top': '0px',
                  'left': '3px',
                  'fontSize': '13px',
                  'padding': '2px'
                });
              }
            },
            eventClick: function(event, jsEvent, view) {
              $.erpPopup({
                title: wpErpRec.todo_description_popup.title,
                button: wpErpRec.todo_description_popup.close,
                id: 'new-todo-popup',
                content: wp.template('erp-rec-todo-description-template')().trim(),
                extraClass: 'medium',
                onReady: function(modal) {
                  $('#todo-description').text(event.title);
                  $('#todo-deadline').text(event.deadline);
                  $('#todo-assigned-user-list').text(event.assigned_user_list);
                },
                onSubmit: function(modal) {
                  modal.closeModal();
                }
              });
            }
          });
        });

      </script>
      <?php
        exit;
    }

    /**
     * Get laters todos in calendar
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_later() {
        global $wpdb;

        $query = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                FROM {$wpdb->prefix}erp_application_todo as todo
                WHERE date(todo.deadline_date) > CURRENT_DATE AND status=0";

        $events_array = $wpdb->get_results( $query, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#39994F'
            );
        }

        ?>
        <script>
          ;
          jQuery(document).ready(function($) {

            $('#todo-calendar-later').fullCalendar({
              header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
              },
              editable: false,
              eventLimit: true,
              events: <?php echo json_encode( $events ); ?>,
              eventRender: function(event, element, calEvent) {
                if (event.holiday) {
                  element.find('.fc-content').find('.fc-title').css({
                    'top': '0px',
                    'left': '3px',
                    'fontSize': '13px',
                    'padding': '2px'
                  });
                }
              },
              eventClick: function(event, jsEvent, view) {
                $.erpPopup({
                  title: wpErpRec.todo_description_popup.title,
                  button: wpErpRec.todo_description_popup.close,
                  id: 'new-todo-popup',
                  content: wp.template('erp-rec-todo-description-template')().trim(),
                  extraClass: 'medium',
                  onReady: function(modal) {
                    $('#todo-description').text(event.title);
                    $('#todo-deadline').text(event.deadline);
                    $('#todo-assigned-user-list').text(event.assigned_user_list);
                  },
                  onSubmit: function(modal) {
                    modal.closeModal();
                  }
                });
              }
            });
          });

        </script>
        <?php
        exit;
    }

    /**
     * Get without date todos in calendar
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_no_date() {
        global $wpdb;

        $query = "SELECT id, title, deadline_date
                FROM {$wpdb->prefix}erp_application_todo as todo
                WHERE ( todo.deadline_date = '0000-00-00 00:00:00' ) AND todo.status=0";

        $events_array = $wpdb->get_results( $query, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'    => $ev['id'],
                'title' => $ev['title'],
                'start' => $ev['deadline_date'],
                'end'   => '',
                'url'   => '#',
                'color' => '#D8D8D8'
            );
        }
        $this->send_success( $events );
    }

    /**
     * Get current month todos in calendar
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function todo_calendar_this_month() {
        global $wpdb;

        $query = "SELECT id, title, deadline_date,
                            ( SELECT GROUP_CONCAT(user.display_name)
                              FROM {$wpdb->prefix}erp_application_todo_relation as todo_relation
                              LEFT JOIN {$wpdb->base_prefix}users as user
                              ON todo_relation.assigned_user_id=user.ID
                              WHERE todo_relation.todo_id=todo.id ) as assigned_user_list
                FROM {$wpdb->prefix}erp_application_todo as todo
                WHERE YEAR(CURDATE()) = YEAR(todo.deadline_date)
                AND MONTH(CURDATE()) = MONTH(todo.deadline_date) AND status=0";

        $events_array = $wpdb->get_results( $query, ARRAY_A );
        $events       = [ ];

        foreach ( $events_array as $ev ) {
            $events[] = array(
                'id'                 => $ev['id'],
                'title'              => $ev['title'],
                'start'              => $ev['deadline_date'],
                'deadline'           => date( 'd-m-Y h:i:s A', strtotime( $ev['deadline_date'] ) ),
                'end'                => '',
                'assigned_user_list' => $ev['assigned_user_list'],
                'url'                => '#',
                'color'              => '#39994F'
            );
        }
        ?>
          <script>
            ;
            jQuery(document).ready(function($) {

              $('#todo-calendar-this-month').fullCalendar({
                header: {
                  left: 'prev,next today',
                  center: 'title',
                  right: 'month,agendaWeek,agendaDay'
                },
                editable: false,
                eventLimit: true,
                events: <?php echo json_encode( $events ); ?>,
                eventRender: function(event, element, calEvent) {
                  if (event.holiday) {
                    element.find('.fc-content').find('.fc-title').css({
                      'top': '0px',
                      'left': '3px',
                      'fontSize': '13px',
                      'padding': '2px'
                    });
                  }
                },
                eventClick: function(event, jsEvent, view) {
                  $.erpPopup({
                    title: wpErpRec.todo_description_popup.title,
                    button: wpErpRec.todo_description_popup.close,
                    id: 'new-todo-popup',
                    content: wp.template('erp-rec-todo-description-template')().trim(),
                    extraClass: 'medium',
                    onReady: function(modal) {
                      $('#todo-description').text(event.title);
                      $('#todo-deadline').text(event.deadline);
                      $('#todo-assigned-user-list').text(event.assigned_user_list);
                    },
                    onSubmit: function(modal) {
                      modal.closeModal();
                    }
                  });
                }
              });
            });

          </script>
          <?php
        exit;
    }

}
