<?php
/**
 * Setup wizard class
 *
 * Walkthrough to the basic setup for creating opening
 *
 * @package WP-ERP\Admin
 */

namespace WeDevs\ERP\Recruitment;

/**
 * The class
 */
class Setup_Wizard {

    /** @var string Currenct Step */
    private $step   = '';

    /** @var array Steps for the setup wizard */
    private $steps  = array();

    /**
     * Hook in tabs.
     */
    public function __construct() {

        // if we are here, we assume we don't need to run the wizard again
        // and the user doesn't need to be redirected here
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        add_action( 'admin_init', array( $this, 'setup_wizard' ) );

    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'erp-recruitment-setup', '' );
    }

    /**
     * Show the setup wizard
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'erp-recruitment-setup' !== $_GET['page'] ) {
            return;
        }

        $this->steps = array(
            'introduction' => array(
                'name'    =>  __( 'Introduction', 'wp-erp-rec' ),
                'view'    => array( $this, 'setup_step_introduction' ),
                'handler' => ''
            ),
            'job_description' => array(
                'name'    =>  __( 'Job Description', 'wp-erp-rec' ),
                'view'    => array( $this, 'setup_step_job_description' ),
                'handler' => array( $this, 'setup_step_job_description_save' )
            ),
            'stage' => array(
                'name'    =>  __( 'Stage', 'erp' ),
                'view'    => array( $this, 'setup_step_stage' ),
                'handler' => array( $this, 'setup_step_stage_save' )
            ),
            'designation' => array(
                'name'    =>  __( 'Designations', 'erp' ),
                'view'    => array( $this, 'setup_step_designation' ),
                'handler' => array( $this, 'setup_step_designation_save' ),
            ),
            'workdays' => array(
                'name'    =>  __( 'Work Days', 'erp' ),
                'view'    => array( $this, 'setup_step_workdays' ),
                'handler' => array( $this, 'setup_step_workdays_save' ),
            ),
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'erp' ),
                'view'    => array( $this, 'setup_step_ready' ),
                'handler' => ''
            )
        );

        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
        $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '';

        wp_enqueue_style( 'jquery-ui', WPERP_ASSETS . '/vendor/jquery-ui/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'erp-setup', WPERP_ASSETS . '/css/setup.css', array( 'dashicons', 'install' ) );

        wp_register_script( 'erp-select2', WPERP_ASSETS . '/vendor/select2/select2.full.min.js', false, false, true );
        wp_register_script( 'erp-setup', WPERP_ASSETS . "/js/erp$suffix.js", array( 'jquery', 'jquery-ui-datepicker', 'erp-select2' ), date( 'Ymd' ), true );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );
        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
    }

    /**
     * Setup Wizard Header
     */
    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WP ERP - Recruitment &rsaquo; Setup Wizard', 'wp-erp-rec' ); ?></title>
            <?php wp_print_scripts( 'erp-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
        </head>
        <body class="erp-setup wp-core-ui">
            <h1 class="erp-logo"><a href="https://wperp.com/downloads/recruitment/">WP ERP - Recruitment</a></h1>
        <?php
    }

    /**
     * Setup Wizard Footer
     */
    public function setup_wizard_footer() {
        ?>
            <?php if ( 'next_steps' === $this->step ) : ?>
                <a class="erp-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'erp' ); ?></a>
            <?php endif; ?>

            </body>
        </html>
        <?php
    }

    /**
     * Output the steps
     */
    public function setup_wizard_steps() {
        $ouput_steps = $this->steps;
        array_shift( $ouput_steps );
        ?>
        <ol class="erp-setup-steps">
            <?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                ?>"><?php echo esc_html( $step['name'] ); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function setup_wizard_content() {
        echo '<div class="erp-setup-content">';
        call_user_func( $this->steps[ $this->step ]['view'] );
        echo '</div>';
    }

    public function next_step_buttons() {
        ?>
        <p class="erp-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'wp-erp-rec' ); ?>" name="save_step" />
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'wp-erp-rec' ); ?></a>
            <?php wp_nonce_field( 'erp-setup' ); ?>
        </p>
        <?php
    }

    /**
     * Introduction step
     */
    public function setup_step_introduction() {
        ?>
        <h1><?php _e( 'Welcome to WP ERP - Recruitment!', 'wp-erp-rec' ); ?></h1>
        <p><?php _e( 'Thank you for choosing WP-ERP Recruitment. An easier way to manage or track your applicant! This quick setup wizard will help you configure the basic settings of opening. <strong>It’s partially optional and shouldn’t take longer than two minutes.</strong>', 'erp' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'wp-erp-rec' ); ?></p>
        <p class="erp-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'wp-erp-rec' ); ?></a>
            <a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) ); ?>" class="button button-large"><?php _e( 'Not right now', 'wp-erp-rec' ); ?></a>
        </p>
        <?php
    }

    public function setup_step_job_description() {
        ?>
        <h1><?php _e( 'Job Description', 'wp-erp-rec' ); ?></h1>

        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="gen_financial_month"><?php _e( 'Opening Title', 'wp-erp-rec' ); ?></label></th>
                    <td>
                        <?php erp_html_form_input([
                            'name'    => 'opening_title',
                            'id'      => 'opening_title',
                            'type'    => 'text',
                            'value'   => '',
                            'help'    => __( '', 'wp-erp-rec' )
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gen_com_start"><?php _e( 'Opening Description', 'wp-erp-rec' ); ?></label></th>
                    <td>
                        <textarea id="opening_description" name="opening_description" rows="7" cols="50" placeholder=""></textarea>
                    </td>
                </tr>
            </table>
            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    public function setup_step_job_description_save() {
        check_admin_referer( 'erp-setup' );

        $opening_title = sanitize_text_field( $_POST['opening_title'] );
        $opening_description = sanitize_text_field( $_POST['opening_description'] );

        $post_data = [
            'post_title'   => $opening_title,
            'post_content' => $opening_description,
            'post_type'    => 'erp_hr_recruitment',
            'post_status' => 'publish'
        ];

        $postid = wp_insert_post($post_data, true);

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_stage() {
        ?>
        <h1><?php _e( 'Hiring Workflow', 'wp-erp-rec' ); ?></h1>
        <form method="post" class="form-table">

            <div class="two-col">
                <div class="col">
                    <p><?php _e( 'Stages reflect the step in your hiring process for your company. e.g. Screening, Phone Interview, Face to face Interview, etc. ', 'wp-erp-rec' ); ?></p>
                </div>

                <div class="col">
                    <div id="openingform_stage_handler" class="openingform_input_wrapper">
                        <button style="margin-bottom: 10px;" class="button alignright" v-on:click.prevent="createStage">
                            <i class="fa fa-plus"></i>&nbsp;<?php _e('Add Stage','wp-erp-rec');?>
                        </button>
                        <div id="openingform_sortit">
                            <div id="item_{{$index}}" class="stage-list" v-for="st in stageData">
                                <i id="{{$index}}" class="fa fa-trash" style="float: right; cursor: pointer" v-on:click="deleteStage($index)"></i>
                                {{st.title}}
                                <input type="hidden" name="stage_name[]" value="{{st.title}}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php

        $this->script_input_duplicator();
    }

    public function script_input_duplicator() {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('.add-new').on('click', 'a', function(e) {
                    e.preventDefault();

                    var self = $(this),
                        parent = self.closest('li');

                    parent.prev().clone().insertBefore( parent ).find('input').val('');
                });
            });
        </script>
        <?php
    }

    public function setup_step_stage_save() {
        check_admin_referer( 'erp-setup' );

        $departments = array_map( 'sanitize_text_field', $_POST['departments'] );

        if ( $departments ) {
            foreach ($departments as $department) {
                if ( ! empty( $department ) ) {
                    erp_hr_create_department([
                        'title' => $department
                    ]);
                }
            }
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_designation() {
        ?>
        <h1><?php _e( 'Designation Setup', 'erp' ); ?></h1>
        <form method="post" class="form-table">

            <div class="two-col">
                <div class="col-first">
                    <p><?php _e( 'Create some designations for your company. e.g. Manager, Senior Developer, Marketing Manager, Support Executive, etc. ', 'erp' ); ?></p>
                    <p><?php _e( 'Leave empty for not to create any designations.', 'erp' ); ?></p>
                </div>

                <div class="col-last">
                    <ul class="unstyled">
                        <li>
                            <input type="text" name="designations[]" class="regular-text" placeholder="<?php esc_attr_e( 'Designation name', 'erp' ); ?>">
                        </li>
                        <li>
                            <input type="text" name="designations[]" class="regular-text" placeholder="<?php esc_attr_e( 'Designation name', 'erp' ); ?>">
                        </li>
                        <li class="add-new"><a href="#" class="button"><?php _e( 'Add New', 'erp' ); ?></a></li>
                    </ul>
                </div>
            </div>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php

        $this->script_input_duplicator();
    }

    public function setup_step_designation_save() {
        check_admin_referer( 'erp-setup' );

        $designations = array_map( 'sanitize_text_field', $_POST['designations'] );

        if ( $designations ) {
            foreach ($designations as $designation) {
                if ( ! empty( $designation ) ) {
                    erp_hr_create_designation([
                        'title' => $designation
                    ]);
                }
            }
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_workdays() {
        $working_days = erp_company_get_working_days();
        $options = array(
            '8' => __( 'Full Day', 'erp' ),
            '4' => __( 'Half Day', 'erp' ),
            '0' => __( 'Non-working Day', 'erp' )
        );
        $days = array(
            'mon' => __( 'Monday', 'erp' ),
            'tue' => __( 'Tuesday', 'erp' ),
            'wed' => __( 'Wednesday', 'erp' ),
            'thu' => __( 'Thursday', 'erp' ),
            'fri' => __( 'Friday', 'erp' ),
            'sat' => __( 'Saturday', 'erp' ),
            'sun' => __( 'Sunday', 'erp' )
        );
        ?>
        <h1><?php _e( 'Workdays Setup', 'erp' ); ?></h1>
        <form method="post">

            <table class="form-table">

                <?php
                foreach( $days as $key => $day ) {
                    ?>
                    <tr>
                        <th scope="row"><label for="gen_financial_month"><?php echo $day; ?></label></th>
                        <td>
                            <?php erp_html_form_input( array(
                                'name'     => 'day[' . $key . ']',
                                'value'    => $working_days[ $key ],
                                'type'     => 'select',
                                'options'  => $options
                            ) ); ?>
                        </td>
                    </tr>
                <?php } ?>

            </table>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    public function setup_step_workdays_save() {
        check_admin_referer( 'erp-setup' );

        $option_key = 'erp_settings_erp-hr_workdays';
        $days       = array_map( 'absint', $_POST['day'] );

        if ( count( $days ) == 7 ) {
            update_option( $option_key, $days );
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_ready() {
        ?>

        <div class="final-step">
            <h1><?php _e( 'Your Site is Ready!', 'erp' ); ?></h1>

            <div class="erp-setup-next-steps">
                <div class="erp-setup-next-steps-first">
                    <h2><?php _e( 'Next Steps &rarr;', 'erp' ); ?></h2>

                    <a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr-employee' ) ); ?>"><?php _e( 'Add your employees!', 'erp' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
}

return new Setup_Wizard();
