<?php
/* view make employee */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

if ( !isset( $_GET['application_id'] ) || !is_numeric( $_GET['application_id'] ) ) {
    wp_die( __( 'Application ID not supplied. Please try again', 'wp-erp-rec' ), __( 'Error', 'wp-erp-rec' ) );
}

// Setup the variables
$application_id = $_GET['application_id'];

?>
<div class="wrap erp wp-erp-wrap">

    <div id="wp-erp-rec-edit-applicant-details">
        <h1><?php _e( 'Fill below fields to make him an employee', 'wp-erp-rec' ); ?></h1>
            <?php
            $application_information = erp_rec_get_applicant_information( $application_id );
            if ( count( $application_information ) > 0 ): ?><?php
                $applicant_id = $application_information[0]['applicant_id'];
                $email = $application_information[0]['email'];
                $first_name = $application_information[0]['first_name'];
                //$middle_name = $application_information[0]['middle_name'];
                $last_name = $application_information[0]['last_name'];
                $get_department = get_post_meta( $application_information[0]['job_id'], '_department', true );
                $get_location = get_post_meta( $application_information[0]['job_id'], '_location', true );
                $get_employment_type = get_post_meta( $application_information[0]['job_id'], '_employment_type', true );
            ?>
            <form id="make_him_employee_form_id" method="post">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th>
                            <label><?php _e( 'Department', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php $departments = erp_hr_get_departments( array( 'no_object' => true ) );
                            // make object to array
                            $departmentss = [ ];
                            foreach ( $departments as $value ) {
                                $departmentss[$value->id] = $value->title;
                            }
                            erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'department',
                                'value'    => $get_department,
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'options'  => $departmentss,
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Location', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'       => '',
                                'name'        => 'location',
                                'value'       => '',
                                'custom_attr' => array( 'data-id' => 'erp-company-new-location' ),
                                'class'       => 'erp-hrm-select2-add-more erp-hr-location-drop-down',
                                'type'        => 'select',
                                'options'     => erp_company_get_location_dropdown_raw()
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Employment Type', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $employment_types = erp_hr_get_employee_types();
                            erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'employment_type',
                                'value'    => $get_employment_type,
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'options'  => $employment_types,
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Source of Hire', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'work_hiring_source',
                                'value'    => '',
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'options'  => array( '-1' => __( '- Select -', 'wp-erp-rec' ) ) + erp_hr_get_employee_sources(),
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Pay Rate', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <input type="number" class="regular-text" name="pay_rate" value="" min="1" max="10000000"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Work Phone', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="work_phone" value="" maxlength="15"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Designation', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'       => __( '', 'wp-erp-rec' ),
                                'name'        => 'work_designation',
                                'value'       => '',
                                'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
                                'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
                                'type'        => 'select',
                                'options'     => erp_hr_get_designation_dropdown_raw(),
                                'required'    => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Reporting To', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'work_reporting_to',
                                'value'    => '',
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'id'       => 'work_reporting_to',
                                'options'  => erp_hr_get_employees_dropdown_raw(),
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Employee Status', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'work_status',
                                'value'    => '',
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'options'  => erp_hr_get_employee_statuses(),
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Date of Hire', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'work_hiring_date',
                                'value'    => '',
                                'type'     => 'text',
                                'class'    => 'erp-date-field regular-text',
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Pay Type', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'   => __( '', 'wp-erp-rec' ),
                                'name'    => 'work_pay_type',
                                'value'   => '',
                                'class'   => 'erp-hrm-select2',
                                'type'    => 'select',
                                'options' => array( '-1' => __( '- Select -', 'wp-erp-rec' ) ) + erp_hr_get_pay_type()
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Mobile', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="mobile" value="<?php echo erp_rec_get_applicant_single_information( $applicant_id, 'mobile' ); ?>" maxlength="25"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Other Email', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <input type="email" class="regular-text" name="other_email" value="<?php echo erp_rec_get_applicant_single_information( $applicant_id, 'other_email' ); ?>" maxlength="255"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Nationality', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'   => __( '', 'wp-erp-rec' ),
                                'name'    => 'personal_nationality',
                                'value'   => erp_rec_get_applicant_single_information( $applicant_id, 'nationality' ),
                                'class'   => 'erp-hrm-select2',
                                'type'    => 'select',
                                'options' => \WeDevs\ERP\Countries::instance()->get_countries()
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Marital Status', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'   => __( '', 'wp-erp-rec' ),
                                'name'    => 'personal_marital_status',
                                'value'   => erp_rec_get_applicant_single_information( $applicant_id, 'marital_status' ),
                                'class'   => 'erp-hrm-select2',
                                'type'    => 'select',
                                'options' => erp_hr_get_marital_statuses()
                            ) );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Hobbies', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="hobbies" value="<?php echo erp_rec_get_applicant_single_information( $applicant_id, 'hobbies' ); ?>" maxlength="255"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Address', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label' => __( '', 'wp-erp-rec' ),
                                'name'  => 'personal_address',
                                'value' => erp_rec_get_applicant_single_information( $applicant_id, 'address' ),
                                'type'  => 'textarea'
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Phone', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label' => __( '', 'wp-erp-rec' ),
                                'name'  => 'personal_phone',
                                'class' => 'regular-text',
                                'value' => erp_rec_get_applicant_single_information( $applicant_id, 'phone' )
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Date of Birth', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'work_date_of_birth',
                                'value'    => erp_rec_get_applicant_single_information( $applicant_id, 'date_of_birth' ),
                                'class'    => 'erp-date-field regular-text',
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Gender', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'    => __( '', 'wp-erp-rec' ),
                                'name'     => 'personal_gender',
                                'value'    => erp_rec_get_applicant_single_information( $applicant_id, 'gender' ),
                                'class'    => 'erp-hrm-select2',
                                'type'     => 'select',
                                'options'  => erp_hr_get_genders(),
                                'required' => true
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Driving License', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label' => __( '', 'wp-erp-rec' ),
                                'name'  => 'personal_driving_license',
                                'class' => 'regular-text',
                                'value' => erp_rec_get_applicant_single_information( $applicant_id, 'driving_license' )
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Website', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label' => __( '', 'wp-erp-rec' ),
                                'name'  => 'personal_user_url',
                                'class' => 'regular-text',
                                'value' => erp_rec_get_applicant_single_information( $applicant_id, 'website' ),
                                'type'  => 'text'
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php _e( 'Biography', 'wp-erp-rec' ); ?></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label' => __( '', 'wp-erp-rec' ),
                                'name'  => 'personal_description',
                                'value' => erp_rec_get_applicant_single_information( $applicant_id, 'biography' ),
                                'type'  => 'textarea'
                            ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label></label>
                        </th>
                        <td>
                            <?php erp_html_form_input( array(
                                'label'       => '',
                                'name'        => 'welcome_email_notification',
                                'help'        => __( 'Send a welcome email', 'wp-erp-rec' ),
                                'type'        => 'checkbox',
                            ) ); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <?php wp_nonce_field( 'wp_erp_rec_make_him_employee_nonce' ); ?>
                <input type="hidden" name="applicant_id" value="<?php echo $applicant_id; ?>"/>
                <input type="hidden" name="email" value="<?php echo $email; ?>"/>
                <input type="hidden" name="first_name" value="<?php echo $first_name; ?>"/>
                <input type="hidden" name="last_name" value="<?php echo $last_name; ?>"/>
                <p class="submit">
                    <input class="page-title-action button button-primary" type="submit" value="<?php _e( 'Hire employee', 'wp-erp-rec' ); ?>" name="submit">
                </p>
            </form><?php endif; ?><!--        </div><!-- /.left box -->
    </div>
    <!-- /#edd-customer-details -->
</div><!-- /.wrap -->
