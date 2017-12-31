<?php
/*
Plugin Name: WP ERP - Recruitment - Folder IT
Plugin URI: https://wperp.com/downloads/recruitment/
Description: Recruitment solution for WP-ERP. Create job posting and hire employee for your company.
Version: 1.0.9
Author: weDevs, Folder IT
Author URI: https://wedevs.com
Text Domain: wp-erp-rec
Domain Path: languages
*/

// don't call the file directly
if ( !defined( 'ABSPATH' ) )
  exit;

/**
 * Base_Plugin class
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
class WeDevs_ERP_Recruitment {

  /**
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */

  /* plugin version
    *
    * @var string
    */
  public $version = '1.0.9';

  /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function __construct() {
    $this->define_constants();

    require_once WPERP_REC_INCLUDES . '/class-install.php';

    add_action( 'erp_hrm_loaded', array( $this, 'erp_hrm_loaded_hook' ) );
  }

  /**
     * Loaded after hrm module
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function erp_hrm_loaded_hook() {
    $this->includes();

    $this->instantiate();

    $this->actions();

    $this->filters();
  }

  /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
  public static function init() {
    static $instance = false;

    if ( !$instance ) {
      $instance = new WeDevs_ERP_Recruitment();
    }

    return $instance;
  }

  /**
     * check php version is supported
     *
     * @return bool
     */
  public function is_supported_php() {
    if ( version_compare( PHP_VERSION, '5.4.0', '<=' ) ) {
      return false;
    }

    return true;
  }

  /**
     * define the plugin constant
     *
     * @return void
     */
  public function define_constants() {
    define( 'WPERP_REC', $this->version );
    define( 'WPERP_REC_FILE', __FILE__ );
    define( 'WPERP_REC_PATH', dirname( WPERP_REC_FILE ) );
    define( 'WPERP_REC_INCLUDES', WPERP_REC_PATH . '/includes' );
    define( 'WPERP_REC_MODULES', WPERP_REC_PATH . '/modules' );
    define( 'WPERP_REC_URL', plugins_url( '', WPERP_REC_FILE ) );
    define( 'WPERP_REC_ASSETS', WPERP_REC_URL . '/assets' );
    define( 'WPERP_REC_VIEWS', WPERP_REC_INCLUDES . '/admin/views' );
    define( 'WPERP_REC_JS_TMPL', WPERP_REC_VIEWS . '/js-templates' );
  }

  /**
     * function objective
     *
     * @return
     */
  public function includes() {
    require_once WPERP_REC_INCLUDES . '/class-install.php';
    require_once WPERP_REC_INCLUDES . '/class-recruitment.php';
    require_once WPERP_REC_INCLUDES . '/class-hr-questionnaire.php';
    require_once WPERP_REC_INCLUDES . '/functions-recruitment.php';
    require_once WPERP_REC_INCLUDES . '/class-rec-ajax.php';
    require_once WPERP_REC_INCLUDES . '/class-form-handler.php';
    require_once WPERP_REC_INCLUDES . '/rec-actions-filters.php';
    require_once WPERP_REC_INCLUDES . '/class-icalendar.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-new-interview.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-new-todo.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-opening-report.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-candidate-report.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-job-application.php';
    require_once WPERP_REC_INCLUDES . '/emails/class-email-hr-manager.php';

    // Setup/welcome
    if ( !empty( $_GET['page'] ) ) {

      if ( 'erp-recruitment-setup' == $_GET['page'] ) {
        require_once WPERP_REC_INCLUDES . '/class-setup-wizard.php';
      }
    }
    if ( !class_exists( 'WP_List_Table' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }
    require_once WPERP_REC_INCLUDES . '/class-jobseeker-list-table.php';

    if ( !$this->is_supported_php() ) {
      return;
    }
  }

  /**
     * function objective
     *
     * @return
     */
  public function instantiate() {
    new \WeDevs\ERP\ERP_Recruitment\Ajax_Handler();

    if ( is_admin() && class_exists( '\WeDevs\ERP\License' ) ) {
      new \WeDevs\ERP\License( __FILE__, 'Recruitment', $this->version, 'weDevs' );
    }
  }

  /**
     * function objective
     *
     * @return
     */
  public function actions() {
    add_action( 'init', array( $this, 'localization_setup' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'front_end_scripts' ) );
    add_action( 'admin_menu', array( $this, 'set_recruitment_menu' ) );
    add_action( 'admin_footer', array( $this, 'admin_rec_js_templates' ) );
    add_action( 'admin_footer', 'erp_rec_include_popup_markup' );
    add_action( 'admin_menu', [ $this, 'hide_add_opening_menu_item' ] );
  }

  /**
     * function objective
     *
     * @return
     */
  public function filters() {
    $this->job_description_filter();

    //Templates de emails
    add_filter('erp_email_classes', function($emails) {
      $emails['New_Interview'] = new WeDevs\ERP\ERP_Recruitment\Emails\New_Interview();
      $emails['New_Todo'] = new WeDevs\ERP\ERP_Recruitment\Emails\New_Todo();
      $emails['Candidate_Report'] = new WeDevs\ERP\ERP_Recruitment\Emails\Candidate_Report();
      $emails['Opening_Report'] = new WeDevs\ERP\ERP_Recruitment\Emails\Opening_Report();
      $emails['Job_Application'] = new WeDevs\ERP\ERP_Recruitment\Emails\Job_Application();
      $emails['HR_Manager'] = new WeDevs\ERP\ERP_Recruitment\Emails\HR_Manager();
      return apply_filters('erp_hrm_email_classes', $emails);
    });
  }

  /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
  public function localization_setup() {
    load_plugin_textdomain( 'wp-erp-rec', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  }

  /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
  public function enqueue_scripts() {
    /**
         * All styles goes here
         */
    //    wp_enqueue_style( 'erp-recruitment-style', WPERP_REC_ASSETS . '/css/stylesheet.css');
    wp_enqueue_style( 'erp-recruitment-barrating-star-style', WPERP_REC_ASSETS . '/css/fontawesome-stars.css' );
    wp_enqueue_style( 'erp-recruitment-extra-fields-style', WPERP_REC_ASSETS . '/css/extra-fields-style.css' );
    wp_enqueue_style( 'alertify-core-style', WPERP_REC_ASSETS . '/css/alertify.core.css' );
    wp_enqueue_style( 'alertify-default-style', WPERP_REC_ASSETS . '/css/alertify.default.css' );
    wp_enqueue_style( 'erp-timepicker' );
    wp_enqueue_style( 'erp-fullcalendar' );
    wp_enqueue_style( 'erp-sweetalert' );

    /**
         * All scripts goes here
         */
    //wp_enqueue_script('erp-recruitment-vuejs-script', WPERP_REC_ASSETS . '/js/vue.min.js', [], false, true);
    wp_enqueue_script( 'erp-vuejs' );
    wp_enqueue_script( 'erp-recruitment-barrating-script', WPERP_REC_ASSETS . '/js/jquery.barrating.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'erp-recruitment-app-script', WPERP_REC_ASSETS . '/js/app.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'erp-recruitment-script', WPERP_REC_ASSETS . '/js/recruitment_entry.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'erp-recruitment-dynamic-field-script', WPERP_REC_ASSETS . '/js/script.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    wp_enqueue_script( 'erp-google-map-script-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBkI1ZYg131g_O4YfbCc7eCmIen8omKFC4', [ ], false, true );
    wp_enqueue_script( 'erp-timepicker' );
    wp_enqueue_script( 'erp-fullcalendar' );
    wp_enqueue_script( 'bootstrap-js', WPERP_REC_ASSETS . '/js/bootstrap.min.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'bootstrap-datetimepicker-js', WPERP_REC_ASSETS . '/js/bootstrap-datetimepicker.min.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'multi-step-form-script', WPERP_REC_ASSETS . '/js/openingFormToWizard.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'alertify-lib', WPERP_REC_ASSETS . '/js/alertify.min.js', array( 'jquery' ), false, true );
    wp_enqueue_script( 'erp-popup-bootstrap', WPERP_REC_ASSETS . '/js/jquery-popup-bootstrap.js', array( 'jquery' ), false, true );

    $localize_scripts = [
      'nonce'                          => wp_create_nonce( 'recruitment_form_builder_nonce' ),
      'qcollection'                    => [ ],
      'admin_url'                      => admin_url( 'admin.php' ),
      'todo_popup'                     => [
        'title'       => __( 'Create a new To-do', 'wp-erp-rec' ),
        'del_confirm' => __( 'Are you sure you want to delete this to-do?', 'wp-erp-rec' ),
        'submit'      => __( 'Create', 'wp-erp-rec' )
      ],
      'todo_description_popup'         => [
        'title' => __( 'To-do Detail', 'wp-erp-rec' ),
        'close' => __( 'Close', 'wp-erp-rec' )
      ],
      'interview_popup'                => [
        'title'        => __( 'Create a new Interview', 'wp-erp-rec' ),
        'update_title' => __( 'Update Interview', 'wp-erp-rec' ),
        'del_confirm'  => __( 'Are you sure you want to delete this interview?', 'wp-erp-rec' ),
        'submit'       => __( 'Create', 'wp-erp-rec' ),
        'update'       => __( 'Update', 'wp-erp-rec' )
      ],
      'sendemail_popup'                => [
        'title'        => __( 'Send Email', 'wp-erp-rec' ),
        'submit'       => __( 'Send', 'wp-erp-rec' )
      ],
      'cv_upload_popup'                => [
        'title'        => __( 'Attach CV', 'wp-erp-rec' ),
        'submit'       => __( 'Upload', 'wp-erp-rec' )
      ],
      'stage_del_confirm'              => __( 'Are you sure you want to delete this stage?', 'wp-erp-rec' ),
      'add_candidate_popup'            => [
        'title'  => __( 'Add Candidate', 'wp-erp-rec' ),
        'submit' => __( 'Create', 'wp-erp-rec' )
      ],
      'candidate_submission'           => [
        'success_message'    => __( 'Candidate added successfully', 'wp-erp-rec' ),
        'candidate_list_url' => admin_url( 'edit.php?post_type=erp_hr_recruitment&page=jobseeker_list' )
      ],
      'stage_message'                  => [
        'duplicate_error_message'        => __( 'Given stage name already exist!', 'wp-erp-rec' ),
        'candidate_number_error_message' => __( 'You cannot uncheck it because this stage has candidate!', 'wp-erp-rec' ),
        'prompt_message'                 => __( 'Please enter stage title', 'wp-erp-rec' ),
        'title_message'                  => __( 'Please select at least one stage (A stage has been auto-selected)', 'wp-erp-rec' )
      ],
      'information_validation_message' => [
        'hiring_validation_message'      => __( 'Hiring lead cannot be empty. Please select a hiring lead.', 'wp-erp-rec' ),
        'department_validation_message'  => __( 'Department cannot be empty. Please select a department name.', 'wp-erp-rec' ),
        'employment_validation_message'  => __( 'Employment type cannot be empty. Please select an employment type.', 'wp-erp-rec' ),
        'minimum_exp_validation_message' => __( 'Minimum experience cannot be empty. Please select minimum experience.', 'wp-erp-rec' ),
        'expire_date_validation_message' => __( 'Expire date cannot be empty. Please select expire date.', 'wp-erp-rec' ),
        'location_validation_message'    => __( 'Location cannot be empty. Please enter location.', 'wp-erp-rec' ),
        'vacancy_validation_message'     => __( 'Vacancy cannot be empty. Please enter vacancy number.', 'wp-erp-rec' )
      ]
    ];

    wp_localize_script( 'erp-recruitment-app-script', 'wpErpRec', $localize_scripts );
  }


  /**
     * Enqueue front-end scripts
     *
     * @return void
     */
  public function front_end_scripts() {      
    wp_register_style( 'erp-recruitment-front-end-style', WPERP_REC_ASSETS . '/css/frontend.css' );
    // wp_register_style( 'multi-step-form-style', WPERP_REC_ASSETS . '/css/multi-form-style.css' );
    wp_enqueue_style( 'erp-sweetalert' );
    wp_enqueue_script( 'erp-sweetalert' );
    wp_enqueue_style( 'alertify-core-style', WPERP_REC_ASSETS . '/css/alertify.core.css' );
    wp_enqueue_style( 'alertify-default-style', WPERP_REC_ASSETS . '/css/alertify.default.css' );
    wp_enqueue_script( 'alertify-lib', WPERP_REC_ASSETS . '/js/alertify.min.js', array( 'jquery' ), false, true );
    wp_register_script( 'erp-recruitment-frontend-script', WPERP_REC_ASSETS . '/js/recruitment_frontend.js', array( 'jquery' ), false, true );
    wp_register_script( 'multi-step-form-script', WPERP_REC_ASSETS . '/js/formToWizard.js', array( 'jquery' ), false, true );
    wp_enqueue_style( 'erp-recruitment-front-end-style' );
    wp_enqueue_style( 'multi-step-form-style' );
    wp_enqueue_script( 'erp-recruitment-frontend-script' );
    wp_enqueue_script( 'multi-step-form-script' );
    wp_enqueue_style( 'load-fa', WPERP_REC_ASSETS . '/css/font-awesome.min.css' );

    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css' );

    $localize_script = apply_filters( 'erp_rec_localize_script', array(
      'nonce'    => wp_create_nonce( 'wp-erp-rec-nonce' ),
      'popup'    => array(
        'jobseeker_title'  => __( 'New JobSeeker', 'wp-erp-rec' ),
        'jobseeker_submit' => __( 'Submit', 'wp-erp-rec' )
      ),
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'confirm'  => __( 'Are you sure?', 'wp-erp-rec' )
    ) );

    wp_localize_script( 'erp-recruitment-frontend-script', 'wpErpHr', $localize_script );

    $country = \WeDevs\ERP\Countries::instance();
    wp_localize_script( 'erp-recruitment-frontend-script', 'wpErpCountries', $country->load_country_states() );

  }

  /**
     * set recruitment menu
     *
     * @return
     */
  public function set_recruitment_menu() {
    /* recruitment menu */

    $capability = 'erp_hr_manager';

    add_menu_page( __( 'Recruitment', 'wp-erp-rec' ), __( 'Recruitment', 'wp-erp-rec' ), $capability,
                  'edit.php?post_type=erp_hr_recruitment', '', 'dashicons-businessman' );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Job Opening', 'wp-erp-rec' ), __( 'Job Opening', 'wp-erp-rec' ),
                     $capability, 'edit.php?post_type=erp_hr_recruitment' );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Add Opening', 'wp-erp-rec' ), __( 'Add Opening', 'wp-erp-rec' ),
                     $capability, 'add-opening', [ $this, 'job_description_step' ] );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Candidates', 'wp-erp-rec' ), __( 'Candidates', 'wp-erp-rec' ),
                     $capability, 'jobseeker_list', array( $this, 'candidate_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Calendar', 'wp-erp-rec' ), __( 'Calendar', 'wp-erp-rec' ),
                     $capability, 'todo-calendar', array( $this, 'todo_calendar_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Question Sets', 'wp-erp-rec' ), __( 'Question Sets', 'wp-erp-rec' ),
                     $capability, 'edit.php?post_type=erp_hr_questionnaire' );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Jobseeker List', 'wp-erp-rec' ), __( 'Job Seeker List', 'wp-erp-rec' ),
                     $capability, 'jobseeker_list', array( $this, 'jobseeker_list' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Applicant Details', 'wp-erp-rec' ), __( 'Applicant Details', 'wp-erp-rec' ),
                     $capability, 'applicant_detail', array( $this, 'applicant_detail' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Jobseeker List to email', 'wp-erp-rec' ), __( 'Job Seeker List to email', 'wp-erp-rec' ),
                     $capability, 'jobseeker_list_email', array( $this, 'jobseeker_list_email_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Reports', 'wp-erp-rec' ), __( 'Reports', 'wp-erp-rec' ),
                     $capability, 'opening_reports', array( $this, 'opening_reports_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Candidate Reports', 'wp-erp-rec' ), __( 'Candidate Reports', 'wp-erp-rec' ),
                     $capability, 'candidate_reports', array( $this, 'candidate_reports_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'CSV Reports', 'wp-erp-rec' ), __( 'CSV Reports', 'wp-erp-rec' ),
                     $capability, 'csv_reports', array( $this, 'csv_reports_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Add candidate', 'wp-erp-rec' ), __( 'Add candidate', 'wp-erp-rec' ),
                     $capability, 'add_candidate', array( $this, 'add_candidate' ) );

    add_submenu_page( 'options.php', __( 'Make Employee', 'wp-erp-rec' ), __( 'Make Employee', 'wp-erp-rec' ),
                     $capability, 'make_employee', array( $this, 'make_employee' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Terms', 'wp-erp-rec' ), __( 'Terms', 'wp-erp-rec' ),
                     $capability, 'terms_list', array( $this, 'terms_list_page' ) );

    add_submenu_page( 'edit.php?post_type=erp_hr_recruitment', __( 'Statuses', 'wp-erp-rec' ), __( 'Statuses', 'wp-erp-rec' ),
                     $capability, 'status_list', array( $this, 'status_list_page' ) );
  }

  /*
     * opening page
     * para
     * return void
     */
  public function job_description_step() {
    $action = isset( $_GET['action'] ) ? $_GET['action'] : 'new';
    $step   = isset( $_GET['step'] ) ? $_GET['step'] : 'job_description';

    if ( $action == 'new' ) {
      require_once WPERP_REC_VIEWS . '/step-job-description.php';
    } elseif ( $action == 'edit' ) {
      if ( $step == 'hiring_workflow' ) {
        require_once WPERP_REC_VIEWS . '/step-hiring-workflow.php';
      } elseif ( $step == "job_information" ) {
        require_once WPERP_REC_VIEWS . '/step-job-information.php';
      } elseif ( $step == "candidate_basic_information" ) {
        require_once WPERP_REC_VIEWS . '/step-candidate-basic-information.php';
      } elseif ( $step == "questionnaire" ) {
        require_once WPERP_REC_VIEWS . '/step-questionnaire.php';
      }
    }
  }

  /**
     * hide recruitment menu item
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function hide_add_opening_menu_item() {
    global $submenu;
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][1] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][1] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][5] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][6] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][7] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][9] );
    unset( $submenu['edit.php?post_type=erp_hr_recruitment'][10] );
    //unset( $submenu['edit.php?post_type=erp_hr_questionnaire'] );
  }

  /**
     * Apply standard WordPress filters on the text
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function job_description_filter() {
    add_filter( 'erp_rec_job_description', 'wptexturize'        );
    add_filter( 'erp_rec_job_description', 'convert_smilies'    );
    add_filter( 'erp_rec_job_description', 'convert_chars'      );
    add_filter( 'erp_rec_job_description', 'wpautop'            );
    add_filter( 'erp_rec_job_description', 'shortcode_unautop'  );
    add_filter( 'erp_rec_job_description', 'prepend_attachment' );

    if ( ! empty( $GLOBALS['wp_embed'] ) ) {
      add_filter( 'erp_rec_job_description', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
      add_filter( 'erp_rec_job_description', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
    }
  }

  /*
     * Candidate list page
     * @since 1.0.0
     * @return void
     */
  public function candidate_page() {
    require_once WPERP_REC_VIEWS . '/jobseeker-list.php';
  }

  /*
     * Todos calendar page
     * @since 1.0.0
     * @return void
     */
  public function todo_calendar_page() {
    require_once WPERP_REC_VIEWS . '/todo-calendar.php';
  }

  /*
     * Opening reports page
     * @since 1.0.0
     * @return void
     */
  public function opening_reports_page() {
    require_once WPERP_REC_VIEWS . '/reports/opening-reports.php';
  }

  /*
     * Interviewer reports page
     * @since 1.0.0
     * @return void
     */
  public function candidate_reports_page() {
    require_once WPERP_REC_VIEWS . '/reports/candidate-reports.php';
  }

  /*
     * Opening reports page
     * @since 1.0.0
     * @return void
     */
  public function csv_reports_page() {
    require_once WPERP_REC_VIEWS . '/reports/csv-reports.php';
  }

  /*
     * Applicant list to email page
     * @since 1.0.0
     * @return void
     */
  public function jobseeker_list_email_page() {
    require_once WPERP_REC_VIEWS . '/jobseeker-list-email.php';
  }

  /*
     * Include make employee from applicant detail page
     * @since 1.0.0
     * @return void
     */
  public function jobseeker_list() {
    require_once WPERP_REC_VIEWS . '/jobseeker-list.php';
  }

  /*
     * Include make employee from applicant detail page
     * @since 1.0.0
     * @return void
     */
  public function make_employee() {
    require_once WPERP_REC_VIEWS . '/view-make-employee.php';
  }

  /*
     * Include applicant detail page
     * @since 1.0.0
     * @return void
     */
  public function applicant_detail() {
    require_once WPERP_REC_VIEWS . '/view-applicant-details.php';
  }

  /*
     * Include add candidate page
     * @since 1.0.0
     * @return void
     */
  public function add_candidate() {
    require_once WPERP_REC_VIEWS . '/add-candidate.php';
  }
  
  /*
     * Lista de tags (terms)
     * @since 1.0.8
     * @return void
     */
  public function terms_list_page() {
    require_once WPERP_REC_VIEWS . '/terms-list.php';
  }
  
  /*
     * Include term detail page
     * @since 1.0.8
     * @return void
     */
  public function term_detail() {
    require_once WPERP_REC_VIEWS . '/view-term-details.php';
  }
  
  /*
     * Lista de estados
     * @since 1.0.8
     * @return void
     */
  public function status_list_page() {
    require_once WPERP_REC_VIEWS . '/status-list.php';
  }
  
  /*
     * Include status detail page
     * @since 1.0.9
     * @return void
     */
  public function status_detail() {
    require_once WPERP_REC_VIEWS . '/view-status-details.php';
  }

  /**
     * Print JS templates in footer
     * @since 1.0.0
     * @return void
     */
  public function admin_rec_js_templates() {
    global $current_screen;

    switch ( $current_screen->base ) {
      case 'recruitment_page_jobseeker_list':
        wp_enqueue_style( 'bootstrap', WPERP_REC_ASSETS . '/css/bootstrap.css' );
        wp_enqueue_style( 'bootstrap-datetimepicker', WPERP_REC_ASSETS . '/css/bootstrap-datetimepicker.min.css', 'bootstrap' );
        wp_enqueue_style( 'erp-recruitment-style', WPERP_REC_ASSETS . '/css/stylesheet.css');
        break;

      case 'recruitment_page_applicant_detail':
        erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
        erp_get_js_template( WPERP_REC_JS_TMPL . '/interview-template.php', 'erp-rec-interview-template' );
        erp_get_js_template( WPERP_REC_JS_TMPL . '/interview-feedback-template.php', 'erp-rec-interview-feedback-template' );
        erp_get_js_template( WPERP_REC_JS_TMPL . '/cv-upload-template.php', 'erp-rec-cv-upload-template' );
        erp_get_js_template( WPERP_REC_JS_TMPL . '/sendemail-template.php', 'erp-rec-sendemail-template' );

        wp_enqueue_style( 'bootstrap', WPERP_REC_ASSETS . '/css/bootstrap.css' );
        wp_enqueue_style( 'bootstrap-datetimepicker', WPERP_REC_ASSETS . '/css/bootstrap-datetimepicker.min.css', 'bootstrap' );
        wp_enqueue_style( 'erp-recruitment-style', WPERP_REC_ASSETS . '/css/stylesheet.css');

        break;
      case 'recruitment_page_term_detail':
      case 'recruitment_page_terms_list':
      case 'recruitment_page_status_detail':
      case 'recruitment_page_status_list':
        wp_enqueue_style( 'bootstrap', WPERP_REC_ASSETS . '/css/bootstrap.css' );
        wp_enqueue_style( 'bootstrap-datetimepicker', WPERP_REC_ASSETS . '/css/bootstrap-datetimepicker.min.css', 'bootstrap' );
        wp_enqueue_style( 'erp-recruitment-style', WPERP_REC_ASSETS . '/css/stylesheet.css');
        
        break;
      case 'job-openings_page_todo-calendar':
        erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
        break;
      case 'recruitment_page_todo-calendar':
        erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
        erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template-detail.php', 'erp-rec-todo-description-template' );
        break;
      case 'recruitment_page_candidate-filter-list':
        break;
      default:
        # code...
        break;
    }

  }

} // Base_Plugin

$WeDevs_ERP_Recruitment = WeDevs_ERP_Recruitment::init();
