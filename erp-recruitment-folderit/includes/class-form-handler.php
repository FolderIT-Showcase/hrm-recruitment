<?php
namespace WeDevs\ERP\ERP_Recruitment;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 *
 * @package WP ERP
 * @subpackage HRM
 */
class Form_Handler {

  use Hooker;

  /**
     * Loaded all actions and filters
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function __construct() {
    $this->action( 'load-recruitment_page_jobseeker_list', 'jobseeker_bulk_action' );
    $this->action( 'load-admin_page_make_employee', 'make_employee' );
    $this->action( 'load-recruitment_page_opening_reports', 'export_opening_report_csv' );
    $this->action( 'load-recruitment_page_candidate_reports', 'export_candidate_report_csv' );
    $this->action( 'load-recruitment_page_csv_reports', 'send_email_with_csv_report' );
    $this->action( 'admin_init', 'create_opening' );
    $this->action( 'admin_init', 'add_hiring_workflow' );
    $this->action( 'admin_init', 'add_job_information' );
    $this->action( 'admin_init', 'add_candidate_basic_information' );
    $this->action( 'admin_init', 'add_questionnaire' );
  }

  /**
     * Send email with attachment as CSV report
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function send_email_with_csv_report() {
    if ( isset( $_REQUEST['func'] ) && $_REQUEST['func'] == 'send-email-with-csv-report' ) {
      global $wpdb;
      $report_type = $_REQUEST['report_type'];
      $from_date   = $_REQUEST['from_date'];
      $to_date     = $_REQUEST['to_date'];
      $report_path = WPERP_REC_PATH . '/assets/csv_reports/';

      if ( $report_type == "opening_report" ) {
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
                        WHERE app.status=0
                        AND post.post_status='publish'";
        if ( $from_date != '' && $to_date != '' ) {
          $from_date = date( "Y-m-d H:i:s", strtotime( $from_date ) );
          $to_date   = date( "Y-m-d H:i:s", strtotime( $to_date ) );
          $query .= " AND post.post_date BETWEEN '$from_date' AND '$to_date' GROUP BY opening";
        } else {
          $query .= " GROUP BY opening";
        }

        $qdata = $wpdb->get_results( $query, ARRAY_A );

        if ( chmod( $report_path, 0777 ) ) {
          $fp            = fopen( WPERP_REC_PATH . '/assets/csv_reports/' . 'opening-report.csv', 'w' );
          $report_header = [ 'Opening', 'Created Date', 'Candidate Added', 'In Process', 'Archive', 'Unscreen', 'Other' ];
          fputcsv( $fp, $report_header );

          $report_data           = [ ];
          $csv_row               = [ ];
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

            array_push( $csv_row, $ud['opening'] );
            array_push( $csv_row, date( 'd M Y', strtotime( $ud['create_date'] ) ) );
            array_push( $csv_row, $ud['total_candidate'] );
            array_push( $csv_row, $ud['in_process'] );
            array_push( $csv_row, $ud['archive'] );
            array_push( $csv_row, $ud['unscreen'] );
            array_push( $csv_row, $ud['other'] );
            fputcsv( $fp, $csv_row );
            $csv_row = [ ];
          }

          fclose( $fp );
          $email = new Emails\Opening_Report();
          $email->trigger();

          $redirect_to = admin_url( 'edit.php?post_type=erp_hr_recruitment&page=csv_reports&csv_create=1' );
        } else {
          $redirect_to = admin_url( 'edit.php?post_type=erp_hr_recruitment&page=csv_reports&csv_create=0' );
        }
        wp_redirect( $redirect_to );
        exit();
      } else {
        $query = "SELECT people.first_name as fname,
                    people.email as email,
                    people.phone as phone,
                    app.apply_date as apply_date,
                    base_stage.title as current_stage
                    FROM {$wpdb->prefix}erp_application as app
                    LEFT JOIN {$wpdb->prefix}erp_peoples as people
                    ON app.applicant_id=people.id
                    LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                    ON app.stage=base_stage.id";
        if ( $from_date != '' && $to_date != '' ) {
          $from_date = date( "Y-m-d H:i:s", strtotime( $from_date ) );
          $to_date   = date( "Y-m-d H:i:s", strtotime( $to_date ) );
          $query .= " AND app.apply_date BETWEEN '$from_date' AND '$to_date'";
        }

        $qdata = $wpdb->get_results( $query, ARRAY_A );
        //ensure the asset path
        if ( chmod( $report_path, 0777 ) ) {
          $fp            = fopen( $report_path . 'candidate-report.csv', 'w' );
          $report_header = [ 'First name', 'Email', 'Phone', 'Apply date', 'Current stage' ];
          fputcsv( $fp, $report_header );

          $report_data = [ ];
          $csv_row     = [ ];
          foreach ( $qdata as $ud ) {
            $report_data[] = array(
              'first_name'    => $ud['fname'],
              'email'         => $ud['email'],
              'phone'         => $ud['phone'],
              'apply_date'    => $ud['apply_date'],
              'current_stage' => $ud['current_stage']
            );
            array_push( $csv_row, $ud['fname'] );
            array_push( $csv_row, $ud['email'] );
            array_push( $csv_row, $ud['phone'] );
            array_push( $csv_row, $ud['apply_date'] );
            array_push( $csv_row, $ud['current_stage'] );
            fputcsv( $fp, $csv_row );
            $csv_row = [ ];
          }
          fclose( $fp );
          // attchment email code
          $email = new Emails\Candidate_Report();
          $email->trigger();

          $redirect_to = admin_url( 'edit.php?post_type=erp_hr_recruitment&page=csv_reports&csv_create=1' );
        } else {
          $redirect_to = admin_url( 'edit.php?post_type=erp_hr_recruitment&page=csv_reports&csv_create=0' );
        }
        wp_redirect( $redirect_to );
        exit();
      }
    }
  }

  /**
     * Export CSV Reports
     *
     * @since  1.0.0
     *
     * @return void
     */
  public function export_opening_report_csv() {
    if ( isset( $_REQUEST['func'] ) && $_REQUEST['func'] == 'opening-report-csv' ) {
      global $wpdb;
      $job_id = isset( $_REQUEST['jobid'] ) ? $_REQUEST['jobid'] : 0;

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

      // create a file pointer connected to the output stream
      //BUILD CSV CONTENT
      $csv                   = 'Opening, Created Date, Candidate Added, In Process, Archive, Unscreen, Other' . "\n";
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
        $csv .= $ud['opening'] . "," . date( 'd M Y', strtotime( $ud['create_date'] ) ) . "," . $ud['total_candidate'] . "," . $ud['in_process'] . "," . $ud['archive'] . "," . $ud['unscreen'] . "," . $ud['other'] . "\n";

      }

      //NAME THE FILE
      $table = "opening-report";

      //OUPUT HEADERS
      header( "Pragma: public" );
      header( "Expires: 0" );
      header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
      header( "Cache-Control: private", false );
      header( "Content-Type: application/octet-stream" );
      header( "Content-Disposition: attachment; filename=\"$table.csv\";" );
      header( "Content-Transfer-Encoding: binary" );
      echo( $csv );
      exit;
    }
  }

  /**
     * Export candidate report
     *
     * @since  1.0.0
     *
     * @return void
     */
  public function export_candidate_report_csv() {
    if ( isset( $_REQUEST['func'] ) && $_REQUEST['func'] == 'candidate-report-csv' ) {
      global $wpdb;
      $job_id = isset( $_REQUEST['jobid'] ) ? $_REQUEST['jobid'] : 0;

      $query = "SELECT people.first_name as fname,
                    people.email as email,
                    people.phone as phone,
                    app.apply_date as apply_date,
                    base_stage.title as current_stage
                    FROM {$wpdb->prefix}erp_application as app
                    LEFT JOIN {$wpdb->prefix}erp_peoples as people
                    ON app.applicant_id=people.id
                    LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                    ON app.stage=base_stage.id";
      if ( $job_id != 0 ) {
        $query .= " WHERE app.job_id={$job_id}";
      }

      $qdata = $wpdb->get_results( $query, ARRAY_A );

      // create a file pointer connected to the output stream
      //BUILD CSV CONTENT
      $csv         = 'First Name, Email, Phone, Apply Date, Current Status' . "\n";
      $report_data = [ ];
      foreach ( $qdata as $ud ) {
        $report_data[] = array(
          'first_name'    => $ud['fname'],
          'email'         => $ud['email'],
          'phone'         => $ud['phone'],
          'apply_date'    => $ud['apply_date'],
          'current_stage' => $ud['current_stage']
        );
        $csv .= $ud['fname'] . "," . $ud['email'] . "," . $ud['phone'] . "," . $ud['apply_date'] . "," . $ud['current_stage'] . "\n";

      }

      //NAME THE FILE
      $table = "candidate-report";

      //OUPUT HEADERS
      header( "Pragma: public" );
      header( "Expires: 0" );
      header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
      header( "Cache-Control: private", false );
      header( "Content-Type: application/octet-stream" );
      header( "Content-Disposition: attachment; filename=\"$table.csv\";" );
      header( "Content-Transfer-Encoding: binary" );
      echo( $csv );
      exit;
    }
  }

  /**
     * Check is current page actions
     *
     * @since 1.0.0
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
  public function verify_current_page_screen( $page_id, $bulk_action ) {

    //if ( !isset($_REQUEST['_wpnonce']) || !isset($_GET['page']) ) {
    if ( !isset( $_REQUEST['_wpnonce'] ) || !isset( $_REQUEST['page'] ) ) {
      return false;
    }

    //if ( $_GET['page'] != $page_id ) {
    if ( $_REQUEST['page'] != $page_id ) {
      return false;
    }

    if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
      return false;
    }

    return true;
  }

  /**
     * Handle jobseeker bulk action
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function jobseeker_bulk_action() {
    if ( !$this->verify_current_page_screen( 'jobseeker_list', 'bulk-jobseekers' ) ) {
      return;
    }

    $jobseeker_table = new \WeDevs\ERP\ERP_Recruitment\Jobseeker_List_Table();
    $action          = $jobseeker_table->current_action();

    if ( $action ) {
      switch ($action) {
        case 'filter_status' :
          $redirect_to = remove_query_arg( 'paged', $_SERVER['REQUEST_URI'] );
          $redirect_to = add_query_arg( 'filter_status', $_REQUEST['filter_status'], $redirect_to );
          wp_redirect( $redirect_to );
          exit();
        case 'filter_project' :
          $redirect_to = remove_query_arg( 'paged', $_SERVER['REQUEST_URI'] );
          $redirect_to = add_query_arg( 'filter_project', $_REQUEST['filter_project'], $redirect_to );
          wp_redirect( $redirect_to );
          exit();
        case 'bulk-email' :
          $page_url = remove_query_arg( [ 'page' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
          $page_url = add_query_arg( [ 'page' => 'jobseeker_list_email' ], $page_url );
          $query       = array( 'email_ids' => $_REQUEST['bulk-email'] );
          $redirect_to = add_query_arg( $query, $page_url );
          wp_redirect( $redirect_to );
          exit();
      }
    }
  }

  /**
     * Jod opening create
     *
     * @since  1.0.0
     *
     * @return void
     */
  public function create_opening() {
    if ( !isset( $_POST['create_opening'] ) ) {
      return;
    }

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'create_opening' ) ) {
      wp_die( __( 'Cheating?', 'wp-erp-rec' ) );
    }

    $opening_title       = isset( $_POST['opening_title'] ) ? $_POST['opening_title'] : '';
    $opening_description = isset( $_POST['opening_description'] ) ? $_POST['opening_description'] : 'no content';

    if ( $_REQUEST['postid'] == 0 ) {
      $post_data = [
        'post_title'   => $opening_title,
        'post_content' => $opening_description,
        'post_type'    => 'erp_hr_recruitment',
        'post_status'  => 'publish'
      ];

      $postid = wp_insert_post( $post_data, true );
    } else {
      $postid = $_REQUEST['postid'];
      // Update post
      $update_post_data = [
        'ID'           => $postid,
        'post_title'   => $opening_title,
        'post_content' => $opening_description
      ];

      // Update the post into the database
      wp_update_post( $update_post_data );
    }

    $location = add_query_arg( array( 'action' => 'edit', 'step' => 'hiring_workflow', 'postid' => $postid ), admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' ) );
    wp_redirect( $location );
  }

  /**
     * Add hiring workflow
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function add_hiring_workflow() {

    if ( !isset( $_POST['hidden_hiring_workflow'] ) ) {
      return;
    }

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'hiring_workflow' ) ) {
      wp_die( __( 'Cheating?', 'wp-erp-rec' ) );
    }

    $jobid           = isset( $_POST['postid'] ) ? $_POST['postid'] : 0;
    $selected_stages = isset( $_POST['stage_name'] ) ? $_POST['stage_name'] : [ ];

    global $wpdb;
    // first delete all stage in this job id
    $where  = array(
      'jobid' => $jobid
    );
    $format = array(
      '%d'
    );
    $wpdb->delete( $wpdb->prefix . 'erp_application_job_stage_relation', $where, $format );
    // now insert stage id to this job id
    foreach ( $selected_stages as $stdata ) {
      $sql = "INSERT INTO {$wpdb->prefix}erp_application_job_stage_relation(jobid,stageid) VALUES('%d','%d')";
      $wpdb->query( $wpdb->prepare( $sql, $jobid, $stdata ) );
    }

    $location = add_query_arg( array( 'action' => 'edit', 'step' => 'job_information', 'postid' => $jobid ), admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' ) );
    wp_redirect( $location );
  }

  /**
     * Add job information
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function add_job_information() {
    if ( !isset( $_POST['hidden_job_information'] ) ) {
      return;
    }

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'job_information' ) ) {
      wp_die( __( 'Cheating?', 'wp-erp-rec' ) );
    }

    $postid             = isset( $_POST['postid'] ) ? $_POST['postid'] : 0;
    $hiring_lead        = isset( $_POST['hiring_lead'] ) ? $_POST['hiring_lead'] : 0;
    $department         = isset( $_POST['department'] ) ? $_POST['department'] : 0;
    $employment_type    = isset( $_POST['employment_type'] ) ? $_POST['employment_type'] : '';
    $remote_job         = ( isset( $_POST['remote_job'] ) ) ? 1 : 0;
    $minimum_experience = isset( $_POST['minimum_experience'] ) ? $_POST['minimum_experience'] : '';
    $expire_date        = isset( $_POST['expire_date'] ) ? $_POST['expire_date'] : '';
    $location           = isset( $_POST['location'] ) ? $_POST['location'] : '';
    $vacancy            = isset( $_POST['vacancy'] ) ? $_POST['vacancy'] : '';
    $hide_job_list      = ( isset( $_POST['hide_job_list'] ) ) ? 1 : 0;
    $permanent_job      = ( isset( $_POST['permanent_job'] ) ) ? 1 : 0;

    update_post_meta( $postid, '_hiring_lead', $hiring_lead );
    update_post_meta( $postid, '_department', $department );
    update_post_meta( $postid, '_employment_type', $employment_type );
    update_post_meta( $postid, '_remote_job', $remote_job );
    update_post_meta( $postid, '_minimum_experience', $minimum_experience );
    update_post_meta( $postid, '_expire_date', $expire_date );
    update_post_meta( $postid, '_location', $location );
    update_post_meta( $postid, '_vacancy', $vacancy );
    update_post_meta( $postid, '_hide_job_list', $hide_job_list );
    update_post_meta( $postid, '_permanent_job', $permanent_job );

    $location = add_query_arg( array( 'action' => 'edit', 'step' => 'candidate_basic_information', 'postid' => $postid ), admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' ) );
    wp_redirect( $location );
  }

  /**
     * Add candidate basic information
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function add_candidate_basic_information() {
    if ( !isset( $_POST['candidate_basic_information'] ) ) {
      return;
    }

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'candidate_basic_information' ) ) {
      wp_die( __( 'Cheating?', 'wp-erp-rec' ) );
    }

    $postid = isset( $_POST['postid'] ) ? $_POST['postid'] : 0;

    // update post meta for personal fields
    $efields            = isset( $_POST['efields'] ) ? $_POST['efields'] : [ ];
    $req                = isset( $_POST['req'] ) ? $_POST['req'] : [ ];
    $fields             = erp_rec_get_personal_fields();
    $db_personal_fields = get_post_meta( $postid, '_personal_fields', true );
    $personal_fields    = [ ];

    if ( is_array( $db_personal_fields ) && count( $db_personal_fields ) > 0 ) {
      foreach ( $db_personal_fields as $key => $value ) {
        $pfield = json_decode( $value )->field;
        if ( is_array( $efields ) ) {
          if ( in_array( $pfield, $efields, true ) ) {
            if ( is_array( $req ) ) {
              if ( in_array( $pfield, $req, true ) ) {
                $personal_fields[] = json_encode( [ "field" => json_decode( $value )->field, "type" => json_decode( $value )->type, "req" => true, "showfr" => true ] );
              } else {
                $personal_fields[] = json_encode( [ "field" => json_decode( $value )->field, "type" => json_decode( $value )->type, "req" => false, "showfr" => true ] );
              }
            } else {
              $personal_fields[] = json_encode( [ "field" => json_decode( $value )->field, "type" => json_decode( $value )->type, "req" => false, "showfr" => true ] );
            }
          } else {
            $personal_fields[] = json_encode( [ "field" => json_decode( $value )->field, "type" => json_decode( $value )->type, "req" => false, "showfr" => false ] );
          }
        }
      }
    } else {
      foreach ( $fields as $key => $value ) {
        if ( is_array( $efields ) ) {
          if ( in_array( $key, $efields, true ) ) {
            if ( is_array( $req ) ) {
              if ( in_array( $key, $req, true ) ) {
                $personal_fields[] = json_encode( [ "field" => $key, "type" => $value['type'], "req" => true, "showfr" => true ] );
              } else {
                $personal_fields[] = json_encode( [ "field" => $key, "type" => $value['type'], "req" => false, "showfr" => true ] );
              }
            } else {
              $personal_fields[] = json_encode( [ "field" => $key, "type" => $value['type'], "req" => false, "showfr" => true ] );
            }
          } else {
            $personal_fields[] = json_encode( [ "field" => $key, "type" => $value['type'], "req" => false, "showfr" => false ] );
          }
        }
      }
    }

    update_post_meta( $postid, '_personal_fields', $personal_fields );

    $location = add_query_arg( array( 'action' => 'edit', 'step' => 'questionnaire', 'postid' => $postid ), admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' ) );
    wp_redirect( $location );
  }

  /**
     * Add questionnaire
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function add_questionnaire() {
    if ( !isset( $_POST['questionnaire'] ) ) {
      return;
    }

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'questionnaire' ) ) {
      wp_die( __( 'Cheating?', 'wp-erp-rec' ) );
    }

    $postid    = isset( $_POST['postid'] ) ? $_POST['postid'] : 0;
    $questions = isset( $_POST['questions'] ) ? $_POST['questions'] : [ ];

    //update_post_meta( $postid, '_questionnaire', $questions );
    update_post_meta( $postid, '_erp_hr_questionnaire', $questions );

    $location = add_query_arg( array( 'action' => 'edit', 'step' => 'finish', 'postid' => $postid ), admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' ) );
    wp_redirect( $location );

  }

  /**
     * Convert candidate to employee
     *
     * @since  1.0.0
     *
     * @return void
     */
  public function make_employee() {
    global $wpdb;

    if ( isset( $_POST['submit'] ) ) {
      $applicant_id               = isset( $_POST['applicant_id'] ) ? $_POST['applicant_id'] : 0;
      $email                      = isset( $_POST['email'] ) ? $_POST['email'] : '';
      $department_id              = isset( $_POST['department'] ) ? $_POST['department'] : 0;
      $employment_type            = isset( $_POST['employment_type'] ) ? $_POST['employment_type'] : 0;
      $first_name                 = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
      $last_name                  = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
      $hobbies                    = isset( $_POST['hobbies'] ) ? $_POST['hobbies'] : '';
      $location                   = isset( $_POST['location'] ) ? $_POST['location'] : 0;
      $mobile                     = isset( $_POST['mobile'] ) ? $_POST['mobile'] : '';
      $other_email                = isset( $_POST['other_email'] ) ? $_POST['other_email'] : '';
      $pay_rate                   = isset( $_POST['pay_rate'] ) ? $_POST['pay_rate'] : 0;
      $personal_address           = isset( $_POST['personal_address'] ) ? $_POST['personal_address'] : '';
      $personal_description       = isset( $_POST['personal_description'] ) ? $_POST['personal_description'] : '';
      $personal_driving_license   = isset( $_POST['personal_driving_license'] ) ? $_POST['personal_driving_license'] : '';
      $personal_gender            = isset( $_POST['personal_gender'] ) ? $_POST['personal_gender'] : '';
      $personal_marital_status    = isset( $_POST['personal_marital_status'] ) ? $_POST['personal_marital_status'] : '';
      $personal_nationality       = isset( $_POST['personal_nationality'] ) ? $_POST['personal_nationality'] : '';
      $personal_phone             = isset( $_POST['personal_phone'] ) ? $_POST['personal_phone'] : '';
      $personal_user_url          = isset( $_POST['personal_user_url'] ) ? $_POST['personal_user_url'] : '';
      $work_date_of_birth         = isset( $_POST['work_date_of_birth'] ) ? $_POST['work_date_of_birth'] : '';
      $work_designation           = isset( $_POST['work_designation'] ) ? $_POST['work_designation'] : 0;
      $work_hiring_date           = isset( $_POST['work_hiring_date'] ) ? $_POST['work_hiring_date'] : '';
      $work_hiring_source         = isset( $_POST['work_hiring_source'] ) ? $_POST['work_hiring_source'] : '';
      $work_pay_type              = isset( $_POST['work_pay_type'] ) ? $_POST['work_pay_type'] : '';
      $work_phone                 = isset( $_POST['work_phone'] ) ? $_POST['work_phone'] : '';
      $work_reporting_to          = isset( $_POST['work_reporting_to'] ) ? $_POST['work_reporting_to'] : 0;
      $work_status                = isset( $_POST['work_status'] ) ? $_POST['work_status'] : '';
      $welcome_email_notification = ( isset( $_POST['welcome_email_notification'] ) ) ? 1 : 0;

      if ( !isset( $first_name ) ) {
        //$this->send_error('First name is empty!');
      } elseif ( !isset( $last_name ) ) {
        //$this->send_error('Last name is empty!');
      } elseif ( !isset( $email ) ) {
        //$this->send_error('User email name is empty!');
      } else {
        if ( erp_rec_has_status( $applicant_id ) ) { // if true then update status peoplesmeta table
          $wpdb->update( "{$wpdb->prefix}erp_peoplemeta",
                        array( 'meta_value' => 'hired' ),
                        array( 'erp_people_id' => $applicant_id, 'meta_key' => 'status' ),
                        array( '%s' ),
                        array( '%d', '%s' )
                       );
        } else { // insert status to peoplesmeta table
          $data = array(
            'erp_people_id' => $applicant_id,
            'meta_key'      => 'status',
            'meta_value'    => 'hired'
          );

          $format = array(
            '%d',
            '%s',
            '%s'
          );

          $wpdb->insert( $wpdb->prefix . 'erp_peoplemeta', $data, $format );
        }

        $args = array(
          'user_email' => $email,
          'work'       => array(
            'designation'   => $work_designation,
            'department'    => $department_id,
            'location'      => $location,
            'hiring_source' => $work_hiring_source,
            'hiring_date'   => $work_hiring_date,
            'date_of_birth' => $work_date_of_birth,
            'reporting_to'  => $work_reporting_to,
            'pay_rate'      => $pay_rate,
            'pay_type'      => $work_pay_type,
            'type'          => $employment_type,
            'status'        => $work_status,
          ),
          'personal'   => array(
            'photo_id'        => 0,
            'employee_id'     => 0,
            'user_id'         => 0,
            'first_name'      => $first_name,
            'middle_name'     => $last_name,
            'last_name'       => $last_name,
            'other_email'     => $other_email,
            'phone'           => $personal_phone,
            'work_phone'      => $work_phone,
            'mobile'          => $mobile,
            'address'         => $personal_address,
            'gender'          => $personal_gender,
            'marital_status'  => $personal_marital_status,
            'nationality'     => $personal_nationality,
            'driving_license' => $personal_driving_license,
            'hobbies'         => $hobbies,
            'user_url'        => $personal_user_url,
            'description'     => $personal_description
          )
        );

        $employee_id = erp_hr_employee_create( $args ); // create new employee and get id

        // update people table with this employee id
        erp_rec_update_people_data( $employee_id, $email, $applicant_id );
        $redirect_url = admin_url( 'admin.php' ) . '?page=erp-hr-employee&action=view&id=' . $employee_id . '&message=1';

        do_action( 'erp_rec_hired_employee', $args );

        /*send a welcome email to this employee */
        if ( $welcome_email_notification ) {
          $emailer    = wperp()->emailer->get_email( 'New_Employee_Welcome' );
          $send_login = isset( $posted['login_info'] ) ? true : false;

          if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
            $emailer->trigger( $employee_id, $send_login );
          }
        }

        wp_redirect( $redirect_url );
        exit;
      }
    }
  }
}

new Form_Handler();