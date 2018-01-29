<?php

/**
 * get the hiring status
 * @return array
 */
function erp_rec_get_hiring_status() {  
  $hr_status = array();

  global $wpdb;

  $query = "SELECT status.id, status.code, status.title, status.description
      FROM {$wpdb->prefix}erp_application_status as status
      WHERE internal = 0
      ORDER BY status_order, title";

  $status = $wpdb->get_results( $query, ARRAY_A );

  foreach( $status as $row) {

    $hr_status[$row['code']] = $row['title'];

  }

  return apply_filters( 'erp_hiring_status', $hr_status );
}

/**
 * Get recruitment status drop down
 *
 * @param  int  status id
 * @param  string  selected status
 *
 * @return string the drop down
 */
function erp_hr_get_status_dropdown( $selected = '' ) {
  $status   = erp_rec_get_hiring_status();
  $dropdown = '';

  if ( $status ) {
    foreach ( $status as $key => $title ) {
      $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
    }
  }

  return $dropdown;
}

/**
 * Get recruitment projects drop down
 *
 * @param  int  status id
 * @param  string  selected status
 *
 * @return string the drop down
 */
function erp_hr_get_projects_dropdown( $selected = '' ) {
  $projects   = erp_rec_get_available_projects( true );
  $dropdown = '';

  if ( $projects ) {
    foreach ( $projects as $key => $title ) {
      $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
    }
  }

  return $dropdown;
}

/**
 * Get recruitment positions drop down
 *
 * @param  int  status id
 * @param  string  selected status
 *
 * @return string the drop down
 */
function erp_hr_get_positions_dropdown( $selected = '' ) {
  $positions = erp_rec_get_available_positions( true );
  $dropdown = '';

  if ( $positions ) {
    foreach ( $positions as $key => $title ) {
      $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
    }
  }

  return $dropdown;
}

/**
 * get the minimum experience of recruitment
 *
 * @return array
 */
function erp_rec_get_recruitment_minimum_experience() {
  $min_exp = array(
    'Fresher'        => __( 'Fresher', 'wp-erp-rec' ),
    '1 - 3 Year'     => __( '1 - 3 Year', 'wp-erp-rec' ),
    '3 - 5 Years'    => __( '3 - 5 Years', 'wp-erp-rec' ),
    '5 - 7 Years'    => __( '5 - 7 Years', 'wp-erp-rec' ),
    '7 - 10 Years'   => __( '7 - 10 Years', 'wp-erp-rec' ),
    'Above 10 Years' => __( 'Above 10 Years', 'wp-erp-rec' )
  );

  return apply_filters( 'erp_recruitment_minimum_experience', $min_exp );
}

/**
 * get default fields
 *
 * @return array
 */
function erp_rec_get_default_fields() {

  $default_fields = array(
    'name' => array(
      'label'       => __( 'Name', 'wp-erp-rec' ),
      'type'        => 'name',
      'required'    => true
    ),
    'email'      => array(
      'label'       => __( 'Email', 'wp-erp-rec' ),
      'name'        => 'email',
      'type'        => 'email',
      'placeholder' => __( 'enter email address', 'wp-erp-rec' ),
      'required'    => true
    ),
    'upload_cv'  => array(
      'label'    => __( 'Upload CV', 'wp-erp-rec' ),
      'name'     => 'erp_rec_file', //wpuf_file
      'type'     => 'file',
      'help'     => __( 'only doc, pdf or docx file allowed and file size will be less than 2MB', 'wp-erp-rec' ),
      'required' => true
    )
  );

  return $default_fields;
}

/**
 * get personal fields
 *
 * @return array
 */
function erp_rec_get_personal_fields() {

  $country = \WeDevs\ERP\Countries::instance();
  $terms = erp_rec_get_terms();

  $personal_fields = array(
    'english_level'   => array(
      'label'    => __( 'English Level', 'wp-erp-rec' ),
      'name'     => 'english_level',
      'type'     => 'select',
      'options'  => array(
        'none'  => __( 'None', 'wp-erp-rec' ),
        'basic' => __( 'Basic', 'wp-erp-rec' ),
        'intermediate' => __( 'Intermediate', 'wp-erp-rec' ),
        'advanced' => __( 'Advanced', 'wp-erp-rec' ),
        'native' => __( 'Native', 'wp-erp-rec' )
      ),
      'required' => false
    ),
    'observations'       => array(
      'label'       => __( 'Observations', 'wp-erp-rec' ),
      'name'        => 'observations',
      'type'        => 'textarea',
      'placeholder' => '',
      'required'    => false,
      'help'        => __( 'Comments, observations and/or notes about your application', 'wp-erp-rec' )
    ),
    'cover_letter'    => array(
      'label'       => __( 'Cover Letter', 'wp-erp-rec' ),
      'name'        => 'cover_letter',
      'type'        => 'textarea',
      'placeholder' => '',
      'required'    => false,
      'help'        => __( 'Why do you think you are a good fit for this job?', 'wp-erp-rec')
    ),
    'mobile'          => array(
      'label'       => __( 'Mobile', 'wp-erp-rec' ),
      'name'        => 'mobile',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false
    ),
    'other_email'     => array(
      'label'       => __( 'Other Email', 'wp-erp-rec' ),
      'name'        => 'other_email',
      'type'        => 'email',
      'placeholder' => '',
      'required'    => false
    ),
    'nationality'     => array(
      'label'    => __( 'Nationality', 'wp-erp-rec' ),
      'name'     => 'nationality',
      'type'     => 'select',
      'options'  => $country->countries,
      'required' => false
    ),
    'marital_status'  => array(
      'label'    => __( 'Marital Status', 'wp-erp-rec' ),
      'name'     => 'marital_status',
      'type'     => 'select',
      'options'  => array(
        'single'  => __( 'Single', 'wp-erp-rec' ),
        'married' => __( 'Married', 'wp-erp-rec' ),
        'widowed' => __( 'Widowed', 'wp-erp-rec' )
      ),
      'required' => false
    ),
    'hobbies'         => array(
      'label'       => __( 'Hobbies', 'wp-erp-rec' ),
      'name'        => 'hobbies',
      'type'        => 'textarea',
      'placeholder' => '',
      'required'    => false
    ),
    'address'         => array(
      'label'       => __( 'Address', 'wp-erp-rec' ),
      'name'        => 'address',
      'type'        => 'textarea',
      'placeholder' => '',
      'required'    => false
    ),
    'phone'           => array(
      'label'       => __( 'Phone', 'wp-erp-rec' ),
      'name'        => 'phone',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false
    ),
    'date_of_birth'   => array(
      'label'       => __( 'Date of Birth', 'wp-erp-rec' ),
      'name'        => 'date_of_birth',
      'type'        => 'date',
      'placeholder' => '',
      'required'    => false
    ),
    'gender'          => array(
      'label'    => __( 'Gender', 'wp-erp-rec' ),
      'name'     => 'gender',
      'type'     => 'select',
      'options'  => array(
        'male'   => __( 'Male', 'wp-erp-rec' ),
        'female' => __( 'Female', 'wp-erp-rec' )
      ),
      'required' => false
    ),
    'driving_license' => array(
      'label'       => __( 'Driving License', 'wp-erp-rec' ),
      'name'        => 'driving_license',
      'type'        => 'text',
      'placeholder' => __( 'enter driving license', 'wp-erp-rec' ),
      'required'    => false
    ),
    'website'         => array(
      'label'       => __( 'Website', 'wp-erp-rec' ),
      'name'        => 'website',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false
    ),
    'biography'       => array(
      'label'       => __( 'Biography', 'wp-erp-rec' ),
      'name'        => 'biography',
      'type'        => 'textarea',
      'placeholder' => '',
      'required'    => false,
      'help'        => __( 'Let us know a little bit about yourself', 'wp-erp-rec' )
    ),
    'remote'       => array(
      'label'       => __( 'Remote', 'wp-erp-rec' ),
      'name'        => 'remote',
      'type'        => 'checkbox',
      'placeholder' => '',
      'required'    => false
    ),
    'remote_location'       => array(
      'label'       => __( 'Remote Location', 'wp-erp-rec' ),
      'name'        => 'remote_location',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false
    ),
    'recommended_by'       => array(
      'label'       => __( 'Recommended By', 'wp-erp-rec' ),
      'name'        => 'recommended_by',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false
    ),
    'interest'         => array(
      'label'       => __( 'Interest', 'wp-erp-rec' ),
      'name'        => 'interest',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false,
      'internal'    => true
    ),
    'current_job'         => array(
      'label'       => __( 'Current Job', 'wp-erp-rec' ),
      'name'        => 'current_job',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false,
      'internal'    => true
    ),
    'current_role'         => array(
      'label'       => __( 'Current Role', 'wp-erp-rec' ),
      'name'        => 'current_role',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false,
      'internal'    => true
    ),
    'skills'         => array(
      'label'       => __( 'Skills', 'wp-erp-rec' ),
      'name'        => 'skills',
      'type'        => 'select2',
      'options'     => $terms,
      'placeholder' => '',
      'required'    => false,
      'internal'    => true,
      'terms'       => true
    ),
    'linkedin'         => array(
      'label'       => __( 'LinkedIn', 'wp-erp-rec' ),
      'name'        => 'linkedin',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false,
      'internal'    => true,
      'href'        => array(
        'prefix'      => 'https://www.linkedin.com/in/',
        'suffix'      => '',
        'target'      => '_blank'
      )
    ),
    'skype'         => array(
      'label'       => __( 'Skype', 'wp-erp-rec' ),
      'name'        => 'skype',
      'type'        => 'text',
      'placeholder' => '',
      'required'    => false,
      'internal'    => true,
      'href'        => array(
        'prefix'      => 'skype:',
        'suffix'      => '?add',
        'validate'    => false
      )
    )
  );

  return apply_filters( 'erp_personal_fields', $personal_fields );
}

/*
 * get available positions
 * return array
 */
function erp_rec_get_available_positions( $all = false ) {
  global $wpdb;
  $positions = array();

  $query = "SELECT post.ID as ID, post.post_title as post_title,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_hide_job_list') as hide_job_list,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_expire_date') expire_date,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_permanent_job') permanent_job

            FROM {$wpdb->prefix}posts as post

            WHERE post.post_type = 'erp_hr_recruitment' ";

  if ($all !== true) {
    $query .= " HAVING (hide_job_list = 0 OR hide_job_list is null)
            AND (expire_date >= '" . date("Y-m-d") . "' OR permanent_job = 1) ";
  }

  $query .= " ORDER BY post.menu_order, post.post_title ";

  $rows = $wpdb->get_results( $query, ARRAY_A );

  foreach ( $rows as $row ) {
    $positions[$row['ID']] = $row['post_title'];
  }

  return $positions;
}

/*
 * get positions
 * return array
 */
function erp_rec_get_position( $jobid ) {
  global $wpdb;

  $query = "SELECT post.ID as ID, post.post_title as post_title,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_hide_job_list') as hide_job_list,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_expire_date') expire_date,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_permanent_job') permanent_job

            FROM {$wpdb->prefix}posts as post

            WHERE post.post_type = 'erp_hr_recruitment' AND post.ID = {$jobid}";

  $query .= " ORDER BY post.menu_order, post.post_title ";

  $position = $wpdb->get_row( $query );

  return $position;
}

/*
 * get available projects
 * return array
 */
function erp_rec_get_available_projects( $all = false ) {
  global $wpdb;
  $projects = array();

  $query = "SELECT post.ID as ID, post.post_title as post_title,
            (SELECT meta.meta_value FROM {$wpdb->prefix}postmeta as meta WHERE meta.post_id = post.ID AND meta.meta_key = '_project_active') as project_active

            FROM {$wpdb->prefix}posts as post

            WHERE post.post_type = 'cpm_project' ";

  if ($all !== true) {
    $query .= " HAVING (project_active = 'yes' OR project_active is null) ";
  }

  $query .= " ORDER BY post.menu_order, post.post_title ";

  $rows = $wpdb->get_results( $query, ARRAY_A );

  foreach ( $rows as $row ) {
    $projects[$row['ID']] = $row['post_title'];
  }

  return $projects;
}

/*
 * get terms (tags)
 * return array
 */
function erp_rec_get_terms( ) {
  $terms = array();

  global $wpdb;

  $query = "SELECT term.id, term.name, term.slug
            FROM {$wpdb->prefix}erp_application_terms as term
            ORDER BY term.name";

  if ($all != true) {
    $query .= "";
  }

  $rows = $wpdb->get_results( $query, ARRAY_A );

  foreach ( $rows as $row ) {
    $terms[$row['slug']] = $row['name'];
  }

  return $terms;
}

/*
 * get count applicants number
 * para custom post id
 * return int
 */
function erp_rec_applicant_counter( $job_id ) {
  global $wpdb;
  if ( $job_id == 0 ) {
    $query = "SELECT COUNT(job_id)
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.status=0";
  } else {
    $query = "SELECT COUNT(job_id)
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.status=0 AND app.job_id='" . $job_id . "'";
  }

  return $wpdb->get_var( $query );
}

/*
 * get total applicants number for pagination
 * para args array
 * return int
 */
function erp_rec_total_applicant_counter( $args ) {
  global $wpdb;
  $job_id             = $args['jobid'];
  $offset             = $args['offset'];
  $limit              = isset( $args['limit'] ) ? $args['limit'] : 0;
  $filter_stage       = isset( $args['stage'] ) ? $args['stage'] : 0;
  $filter_added_by_me = isset( $args['added_by_me'] ) ? $args['added_by_me'] : 0;

  $query = "SELECT count(DISTINCT app.applicant_id)
          FROM {$wpdb->prefix}erp_application as app";

  if ( isset( $args['status'] ) ) {
    $query .= " LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta ON app.applicant_id = peoplemeta.erp_people_id";
  }
  if ( isset( $args['skills'] ) && !empty($args['skills']) ) {
    $query .= " LEFT JOIN {$wpdb->prefix}erp_application_terms_relation as tr
    ON app.id = tr.application_id
    LEFT JOIN {$wpdb->prefix}erp_application_terms as terms
    ON tr.term_id = terms.id";
  }

  if ( isset( $args['status'] ) && $args['status'] == 'hired' ) {
    $query .= " WHERE app.status=1";
  } else {
    $query .= " WHERE app.status=0";
  }

  if ( isset( $args['skills'] ) && !empty($args['skills']) ) {
    $slugs = "'" . implode("', '", $args['skills']) . "'";
    $query .= " AND tr.meta_key = 'skills' AND terms.slug IN (" . $slugs .") ";
  }
  if ( $args['jobid'] != 0 ) {
    $query .= " AND app.job_id='" . $job_id . "'";
  }
  if ( isset( $filter_stage ) && $filter_stage != '' ) { //has stage
    $query .= " AND app.stage='" . $filter_stage . "'";
  }
  if ( isset( $args['status'] ) ) { //has status
    $query .= " AND peoplemeta.meta_key='status' AND peoplemeta.meta_value='" . $args['status'] . "'";
  }
  if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' ) { //has status
    $query .= " AND peoplemeta.meta_key='status' AND peoplemeta.meta_value='" . $args['status'] . "'";
  }
  if ( isset( $args['project_id'] ) && $args['project_id'] != '' && $args['project_id'] != '-1' ) { //has projcct
    $query .= " AND application.project_id=" . $args['project_id'];
  }
  if ( isset( $filter_added_by_me ) && $filter_added_by_me != '' ) { //has status
    $query .= " AND app.added_by='" . get_current_user_id() . "'";
  }

  return $wpdb->get_var( $query );
}

/*
 * get applicants information
 * para custom post id
 * return array
 */
function erp_rec_get_applicants_information( $args ) {
  global $wpdb;

  $plugin_projects = false;
  if( is_plugin_active( 'administrador-de-proyectos/administrador-de-proyectos.php' ) ) {
    $plugin_projects = true;
  }

  $defaults = array(
    'number' => 5,
    'offset' => 0
  );

  $args = wp_parse_args( $args, $defaults );

  $query = "SELECT *, posts.post_title as post_title, base_stage.title as title, application.id as applicationid, people.id as peopleid,
    ( select AVG(rating)";

  if( $plugin_projects ) {
    $query .= ", project.project_title as project_title";
  }

  $query .= " FROM {$wpdb->prefix}erp_application_rating
        WHERE application_id = applicationid ) as avg_rating,
    application.summary_rating as summary_rating,
    application.summary_comment as summary_comment,
    CONCAT( first_name, ' ', last_name ) as full_name,
    ( select meta_value
        FROM {$wpdb->prefix}erp_peoplemeta
        WHERE erp_people_id = peopleid AND meta_key = 'status' ) as status,
    ( select meta_value
        FROM {$wpdb->prefix}erp_peoplemeta
        WHERE erp_people_id = peopleid AND meta_key = 'remote' ) as remote,
    ( select meta_value
        FROM {$wpdb->prefix}erp_peoplemeta
        WHERE erp_people_id = peopleid AND meta_key = 'skills' ) as skills
    FROM {$wpdb->prefix}erp_application as application
    LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
    ON application.stage=base_stage.id
    LEFT JOIN {$wpdb->prefix}posts as posts
    ON posts.ID=application.job_id
    LEFT JOIN {$wpdb->prefix}erp_peoplemeta as peoplemeta
    ON application.applicant_id = peoplemeta.erp_people_id
    LEFT JOIN {$wpdb->prefix}erp_application_job_stage_relation as stage
    ON application.job_id=stage.jobid
    LEFT JOIN {$wpdb->prefix}erp_peoples as people
    ON people.id=application.applicant_id
    LEFT JOIN {$wpdb->prefix}erp_application_terms_relation as tr
    ON application.id = tr.application_id
    LEFT JOIN {$wpdb->prefix}erp_application_terms as terms
    ON tr.term_id = terms.id";

  if( $plugin_projects ) {
    $query .= " LEFT JOIN {$wpdb->prefix}erp_projects as project
    ON application.project_id = project.project_id";
  }

  if ( isset( $args['status'] ) && $args['status'] == 'hired' ) {
    $query .= " WHERE application.status='1'";
  } else {
    $query .= " WHERE application.status='0'";
  }

  if ( $args['jobid'] != 0 ) {
    $query .= " AND application.job_id='" . $args['jobid'] . "'";
  }
  if ( isset( $args['stage'] ) && $args['stage'] != '' ) { //has stage
    $query .= " AND application.stage='" . $args['stage'] . "'";
  }
  if ( isset( $args['status'] ) && $args['status'] != '' && $args['status'] != '-1' ) { //has status
    $query .= " AND peoplemeta.meta_key='status' AND peoplemeta.meta_value='" . $args['status'] . "'";
  }
  if ( isset( $args['project_id'] ) && $args['project_id'] != '' && $args['project_id'] != '-1' ) { //has projcct
    $query .= " AND application.project_id=" . $args['project_id'];
  }
  if ( isset( $args['added_by_me'] ) && $args['added_by_me'] != '' ) { //added by me query
    $query .= " AND application.added_by='" . get_current_user_id() . "'";
  }
  if ( isset( $args['search_key'] ) && $args['search_key'] != '' ) { //search is not empty
    $query .= " AND people.first_name LIKE '%" . $args['search_key'] . "%' OR people.last_name LIKE '%" . $args['search_key'] . "%'";
  }
  if ( isset( $args['skills'] ) && !empty($args['skills']) ) {
    $slugs = "'" . implode("', '", $args['skills']) . "'";
    $query .= " AND tr.meta_key = 'skills' AND terms.slug IN (" . $slugs .") ";
  }

  if ( isset( $args['orderby'] ) ) {
    if ($args['orderby'] == 'project_title' && !$plugin_projects) {
      $args['orderby'] = 'project_id';
    }
    $query .= " GROUP BY applicationid ORDER BY " . $args['orderby'] . " " . $args['order'] . " LIMIT {$args['offset']}, {$args['number']}";
  } else {
    $query .= " GROUP BY applicationid ORDER BY application.apply_date DESC LIMIT {$args['offset']}, {$args['number']}";
  }
  
  return $wpdb->get_results( $query, ARRAY_A );
}

/*
 * get applicant information
 * para custom post id, applicant id
 * return array
 */
function erp_rec_get_applicant_information( $application_id ) {
  global $wpdb;

  $query = "SELECT *
                FROM {$wpdb->prefix}erp_peoples as people
                LEFT JOIN {$wpdb->prefix}erp_application as app
                ON people.id = app.applicant_id
                LEFT JOIN {$wpdb->prefix}erp_application_stage as stage
                ON app.stage = stage.id
                WHERE app.id=%d";
  return $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
}

/*
* get applicant information
* para custom post id, applicant id
* return array
*/
function erp_rec_get_applicant_single_information( $applicant_id, $meta_key ) {
  global $wpdb;

  $query = "SELECT meta_value
                FROM {$wpdb->prefix}erp_peoplemeta as peoplemeta
                WHERE peoplemeta.meta_key='%s'
                AND peoplemeta.erp_people_id=%s";
  return $wpdb->get_var( $wpdb->prepare( $query, $meta_key, $applicant_id ) );
}

/*
* get comment of specific applicant of specific job
* para application id, applicant id
* return array
*/
function erp_rec_get_application_comments( $application_id ) {
  global $wpdb;

  $query = "SELECT *
                FROM {$wpdb->prefix}erp_application_comment as comment
                LEFT JOIN {$wpdb->base_prefix}users as user
                ON comment.admin_user_id = user.ID
                WHERE comment.application_id='" . $application_id . "'";
  return $wpdb->get_results( $query, ARRAY_A );
}

/*
 * function get application stage
 * para int
 * return array
 */
function erp_rec_get_application_stage_intvw_popup( $application_id ) {
  global $wpdb;
  $query    = "SELECT stage.stageid, base_stage.title
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON stage.jobid=application.job_id
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON stage.stageid=base_stage.id
                WHERE application.id='%d'
                ORDER BY base_stage.stage_order";
  $stages   = $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
  $dropdown = array( 0 => __( '- Select Stage -', 'wp-erp-rec' ) );
  if ( count( $stages ) > 0 ) {
    foreach ( $stages as $value ) {
      $dropdown[$value['stageid']] = $value['title'];
    }
  }
  return $dropdown;
}

/*
 * function get internal types
 * para int
 * return array
 */
function erp_rec_get_application_type_intvw_popup( $application_id ) {
  global $wpdb;
  $query    = "SELECT types.id, types.type_detail
                FROM {$wpdb->prefix}erp_application_interview_types as types
                ORDER BY types.type_order";
  $types   = $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
  //$dropdown = array( 0 => __( '- Select Internal Type -', 'wp-erp-rec' ) );
  $dropdown = array();
  if ( count( $types ) > 0 ) {
    foreach ( $types as $value ) {
      $dropdown[$value['id']] = $value['type_detail'];
    }
  }
  return $dropdown;
}

/*
 * function get app stage
 * para int
 * return array
 */
function erp_rec_get_app_stage( $application_id ) {
  global $wpdb;
  $query = "SELECT base_stage.title
                FROM {$wpdb->prefix}erp_application as app
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON app.stage=base_stage.id
                WHERE app.id='%d'
                ORDER BY base_stage.stage_order";
  return $wpdb->get_var( $wpdb->prepare( $query, $application_id ) );
}

/*
 * function get app stage id
 * para int
 * return array
 */
function erp_rec_get_app_interview_type_default( ) {
  global $wpdb;
  $query   = "SELECT types.id
                FROM {$wpdb->prefix}erp_application_interview_types as types
                ORDER BY types.type_order";

  return $wpdb->get_var( $query );
}

/**
 * get the minimum experience of recruitment
 *
 * @return array
 */
function erp_rec_get_interview_time_duration() {
  $interview_time_duration = array(
    '15'  => __( '15 minutes', 'wp-erp-rec' ),
    '30'  => __( '30 minutes', 'wp-erp-rec' ),
    '45'  => __( '45 minutes', 'wp-erp-rec' ),
    '60'  => __( '1 hour', 'wp-erp-rec' ),
    '90'  => __( '1 hour 30 minutes', 'wp-erp-rec' ),
    '120' => __( '2 hours', 'wp-erp-rec' )
  );

  return apply_filters( 'interview_time_duration', $interview_time_duration );
}

/**
 * get english levels
 *
 * @return array
 */
function erp_rec_get_feedback_english_levels() {
  $feedback_english_levels = array(
    '' => __( '- Select English Level -', 'wp-erp-rec' ),
    'elementary'  => __( 'Elementary', 'wp-erp-rec' ),
    'beginners'  => __( 'Beginners', 'wp-erp-rec' ),
    'pre-intermediate'  => __( 'Pre-Intermediate', 'wp-erp-rec' ),
    'intermediate'  => __( 'Intermediate', 'wp-erp-rec' ),
    'upper'  => __( 'Upper', 'wp-erp-rec' ),
    'advanced'  => __( 'Advanced', 'wp-erp-rec' ),
    'speaking'  => __( 'Speaking', 'wp-erp-rec' )
  );

  return apply_filters( 'feedback_english_levels', $feedback_english_levels );
}

/**
 * get english conversation
 *
 * @return array
 */
function erp_rec_get_feedback_english_conversation() {
  $feedback_english_conversation = array(
    '' => __( '- Select English Conversation -', 'wp-erp-rec' ),
    'yes'  => __( 'Yes', 'wp-erp-rec' ),
    'no'  => __( 'No', 'wp-erp-rec' )
  );

  return apply_filters( 'feedback_english_conversation', $feedback_english_conversation );
}

/*
* check email is duplicate or not
* para email as string, job id as int
* return bool
*/
function erp_rec_is_duplicate_email( $email, $job_id ) {
  global $wpdb;

  $query = "SELECT email
                FROM {$wpdb->prefix}erp_peoples as people
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON people.id = application.applicant_id
                WHERE people.email='%s' AND application.job_id=%d";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $email, $job_id ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/*
* check email already in other application
* return bool
*/
function erp_rec_is_existing_email( $email, $job_id ) {
  global $wpdb;

  $query = "SELECT email
                FROM {$wpdb->prefix}erp_peoples as people
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON people.id = application.applicant_id
                WHERE people.email='%s' AND application.job_id IS NOT NULL AND application.job_id <> %d";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $email, $job_id ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/*
* check rating done or not
* para int application id , int admin user id
* return bool
*/
function erp_rec_has_rating( $application_id, $admin_user_id ) {
  global $wpdb;
  $query = "SELECT id
                FROM {$wpdb->prefix}erp_application_rating
                WHERE application_id='%d' AND user_id='%d'";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $application_id, $admin_user_id ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/*
* check this stage has candidate or not
* para int job id
* return bool
*/
function erp_rec_has_candidate( $job_id, $stage_title ) {
  global $wpdb;
  $query = "SELECT app.id
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.job_id='%d'
                AND app.stage='%s'";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $job_id, $stage_title ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/*
* check this job has stage or not
* para int job id
* return bool
*/
function erp_rec_count_stage( $job_id ) {
  global $wpdb;
  $query = "SELECT stage.id
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                WHERE stage.jobid='%d'";

  return count( $wpdb->get_results( $wpdb->prepare( $query, $job_id ), ARRAY_A ) );
}

/* check rating done or not
* para int application id , int admin user id
* return bool
*/
function erp_rec_has_status( $applicant_id ) {
  global $wpdb;
  $meta_key = 'status';
  $query    = "SELECT meta_id
        FROM {$wpdb->prefix}erp_peoplemeta
        WHERE meta_key='%s' AND erp_people_id='%d'";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $meta_key, $applicant_id ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/* check duplicate stage name or not
* para int stage title
* return bool
*/
function erp_rec_check_duplicate_stage( $stage_title ) {
  global $wpdb;
  $query = "SELECT id FROM {$wpdb->prefix}erp_application_stage WHERE title='%s' ORDER BY stage_order";

  if ( count( $wpdb->get_results( $wpdb->prepare( $query, $stage_title ), ARRAY_A ) ) > 0 ) {
    return true;
  } else {
    return false;
  }
}

/*
* update people table that now that people is an employee
* para employee id
* return void
*/
function erp_rec_update_people_data( $employee_id, $email, $applicant_id ) {
  global $wpdb;
  // update application table
  $data         = array(
    'status' => 1
  );
  $where        = array(
    'applicant_id' => $applicant_id
  );
  $data_format  = array(
    '%d'
  );
  $where_format = array(
    '%d'
  );
  $wpdb->update( $wpdb->prefix . 'erp_application', $data, $where, $data_format, $where_format );

  return true;
}

/*
* file uploader
* para file array
* return array
*/
function erp_rec_handle_upload( $upload_data ) {

  $uploaded_file = wp_handle_upload( $upload_data, array( 'test_form' => false ) );
  // If the wp_handle_upload call returned a local path for the image
  if ( isset( $uploaded_file['file'] ) ) {
    $file_loc    = $uploaded_file['file'];
    $file_name   = basename( $upload_data['name'] );
    $file_type   = wp_check_filetype( $file_name );
    $attachment  = array(
      'post_mime_type' => $file_type['type'],
      'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
      'post_content'   => '',
      'post_status'    => 'inherit'
    );
    $attach_id   = wp_insert_attachment( $attachment, $file_loc );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    return array( 'success' => true, 'attach_id' => $attach_id );
  }
  return array( 'success' => false, 'error' => $uploaded_file['error'] );
}

/**
 * get the hiring stages
 * @return array
 */
function erp_rec_get_hiring_stages() {
  $hr_stages = array(
    'Screening'              => __( 'Screening', 'wp-erp-rec' ),
    'Phone Interview'        => __( 'Interview', 'wp-erp-rec' ),
    'Face to Face Interview' => __( 'Face to Face Interview', 'wp-erp-rec' ),
    'Made an Offer'          => __( 'Made an Offer', 'wp-erp-rec' )
  );

  return apply_filters( 'erp_hiring_stages', $hr_stages );
}

/*
 * get stage id and title of specific applicant of specific job
 * para application id, applicant id
 * return array
 */
function erp_rec_get_application_stages( $application_id ) {
  global $wpdb;

  $query = "SELECT stage.stageid, base_stage.title
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application as application
                ON stage.jobid = application.job_id
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON base_stage.id = stage.stageid
                WHERE application.id=%d
                ORDER BY base_stage.stage_order";
  return $wpdb->get_results( $wpdb->prepare( $query, $application_id ), ARRAY_A );
}

/*
 * get stage id and title of specific job
 * para job id
 * return array
 */
function erp_rec_get_this_job_stages( $job_id ) {
  global $wpdb;

  $query = "SELECT stage.stageid, base_stage.title, base_stage.stage_individual
                FROM {$wpdb->prefix}erp_application_job_stage_relation as stage
                LEFT JOIN {$wpdb->prefix}erp_application_stage as base_stage
                ON stage.stageid=base_stage.id
                WHERE stage.jobid=%d
                ORDER BY base_stage.stage_order";
  return $wpdb->get_results( $wpdb->prepare( $query, $job_id ), ARRAY_A );
}

/*
 * get stage id and title of all jobs
 * para
 * return array
 */
function erp_rec_get_all_stages() {
  global $wpdb;
  $query = "SELECT stage.id, stage.title, stage.stage_individual
                FROM {$wpdb->prefix}erp_application_stage as stage
                ORDER BY stage.stage_order";
  return $wpdb->get_results( $query, ARRAY_A );
}

/*
 * get candidate number in this stage
 * para job id, stage title
 * return array
 */
function erp_rec_get_candidate_number_in_this_stages( $job_id, $stage_id ) {
  global $wpdb;

  if ( empty($job_id) ) {
    $query = "SELECT COUNT(app.id)
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.status=0
                AND app.stage='%d'";
    return $wpdb->get_var( $wpdb->prepare( $query, $stage_id ) );
  } else {
    $query = "SELECT COUNT(app.id)
                FROM {$wpdb->prefix}erp_application as app
                WHERE app.job_id='%d'
                AND app.stage='%d'
                AND app.status=0";
    return $wpdb->get_var( $wpdb->prepare( $query, $job_id, $stage_id ) );
  }

}

/*
 * get all jobs
 * para
 * return array
 */
function erp_rec_get_all_jobs() {
  $type     = 'erp_hr_recruitment';
  $args     = array(
    'post_type'      => $type,
    'post_status'    => 'publish',
    'posts_per_page' => -1
  );
  $jobs     = [ ];
  $my_query = null;
  $my_query = new WP_Query( $args );
  if ( $my_query->have_posts() ) {
    while ( $my_query->have_posts() ) {
      $my_query->the_post();
      $jobs[] = [ 'jobid' => get_the_ID(), 'jobtitle' => get_the_title() ];
    }
  }
  wp_reset_query();  // Restore global post data stomped by the_post()
  return $jobs;
}

/*
 * get stage
 * para
 * return array
 */
function erp_rec_get_stage( $jobid ) {
  global $wpdb;
  if ( isset( $jobid ) ) {
    $query       = "SELECT COUNT(id) FROM {$wpdb->prefix}erp_application_job_stage_relation WHERE jobid=%d";
    $row_counter = $wpdb->get_var( $wpdb->prepare( $query, $jobid ) );
    if ( $row_counter > 0 ) {
      $query = "SELECT stage.id as sid,stage.title,
                          ( SELECT stageid
                            FROM {$wpdb->prefix}erp_application_job_stage_relation
                            WHERE jobid={$jobid} AND stageid=sid ) as stage_selected,
                          ( SELECT COUNT(id)
                            FROM {$wpdb->prefix}erp_application
                            WHERE job_id={$jobid} AND stage=sid ) as candidate_number
                          FROM {$wpdb->prefix}erp_application_stage as stage ORDER BY stage.stage_order";
      return $wpdb->get_results( $query, ARRAY_A );
    } else {
      $query = "SELECT stage.id as sid, stage.title
                FROM {$wpdb->prefix}erp_application_stage as stage
                LEFT JOIN {$wpdb->base_prefix}users as user
                ON stage.created_by = user.ID
                ORDER BY stage.stage_order";

      return $wpdb->get_results( $query, ARRAY_A );
    }
  } else {
    return false;
  }
}

/*
 * get stages in creating recruitment
 * para
 * return array
 */
function erp_rec_get_stages( $jobid ) {
  global $wpdb;
  if ( isset( $jobid ) ) {
    $query      = "SELECT stage.id as sid, stage.title as title FROM {$wpdb->prefix}erp_application_stage as stage ORDER BY stage.stage_order";
    $stages     = $wpdb->get_results( $query, ARRAY_A );

    $query  = "SELECT st_rel.stageid as sid, stage.title as title
                  FROM {$wpdb->prefix}erp_application_stage as stage
                  LEFT JOIN {$wpdb->prefix}erp_application_job_stage_relation as st_rel
                  ON stage.id=st_rel.stageid
                  WHERE st_rel.jobid=%d
                  ORDER BY stage.stage_order";
    $st_rel = $wpdb->get_results( $wpdb->prepare( $query, $jobid ), ARRAY_A );

    $final_selected_stage = [ ];
    foreach ( $stages as $stage_value ) {
      $got_stage_id = erp_rec_searchForId( $stage_value['sid'], $st_rel );
      if ( $got_stage_id ) {
        $final_selected_stage[] = [
          'sid'      => $stage_value['sid'],
          'title'    => $stage_value['title'],
          'selected' => true
        ];
      } else {
        $final_selected_stage[] = [
          'sid'      => $stage_value['sid'],
          'title'    => $stage_value['title'],
          'selected' => false
        ];
      }
    }

    return $final_selected_stage;

  } else {
    return false;
  }
}

/*
 * search id in an array
 *
 * @return mixed
 */
function erp_rec_searchForId( $sid, $array ) {
  foreach ( $array as $key => $val ) {
    if ( $val['sid'] === $sid ) {
      return $sid;
    }
  }
  return false;
}

/*
 * update stage on update recruitment post
 * para post id int
 * return mix
 */

function erp_rec_update_stage( $selected_stages, $jobid ) {

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
}


/*
 * show admin notice
 * para
 * return void
 */
function erp_rec_show_notice() {
  if ( isset( $_REQUEST['page'] ) == 'erp-hr-employee' && isset( $_REQUEST['action'] ) == 'view' && isset( $_REQUEST['id'] ) ) {
    if ( $_REQUEST['page'] == 'erp-hr-employee' && $_REQUEST['action'] == 'view' && is_numeric( $_REQUEST['id'] ) && $_REQUEST['id'] > 0 && isset( $_REQUEST['message'] ) ) {
      if ( $_REQUEST['message'] == 1 ) { ?>
<div class="notice notice-success is-dismissible">
  <p>
    <?php _e( 'Congrats! New employee has been created successfully', 'wp-erp-rec' ); ?>
  </p>
</div>
<?php }
    }
  }
}

/**
 * show admin notice post error during edit in job opening
 *
 * @return void
 */
function erp_rec_show_post_error_notice() {
  if ( isset( $_REQUEST['action'] ) == 'edit' && isset( $_REQUEST['post_error_message'] ) == 1 ) { ?>
<div class="notice notice-error is-dismissible">
  <p>
    <?php _e( 'Hiring lead cannot be empty!', 'wp-erp-rec' ); ?>
  </p>
</div>
<?php }
}

/*
 * show admin progressbar
 * para
 * return void
 */
//function erp_rec_opening_admin_progressbar( $selected = array('job_description') ) {
function erp_rec_opening_admin_progressbar( $selected ) {
  $steps = array(
    'job_description'             => __( 'Job description', 'wp-erp-rec' ),
    'hiring_workflow'             => __( 'Hiring workflow', 'wp-erp-rec' ),
    'job_information'             => __( 'Job information', 'wp-erp-rec' ),
    'candidate_basic_information' => __( 'Basic information', 'wp-erp-rec' ),
    'questionnaire_selection'     => __( 'Question set', 'wp-erp-rec' ),
  );

  $step_counter = 1;
  $html         = '';
  $html .= '<ul class="recruitment-step-progress">';
  foreach ( $steps as $key => $value ) {
    $html .= sprintf( '<li class="%s"><span class="step-number">%d</span><span class="step-content">%s</span></li>', ( $key == $selected ) ? 'active' : 'not-active', $step_counter, $value );
    $step_counter++;
  }

  $html .= '</ul>';

  return $html;
}

/**
 * Include required HTML form erp-popup-bs
 *
 * @since 1.1.12
 *
 * @return void
 */
function erp_rec_include_popup_markup() {
  include_once WPERP_REC_VIEWS . '/erp-modal-bs.php';
}

function erp_rec_sanitize_string($dirty_text) {
  $not_allowed= array (" ", ".", "á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
  $allowed= array ("_", "_", "a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
  $clean_text = strtolower(str_replace($not_allowed, $allowed, $dirty_text));
  return $clean_text;
}