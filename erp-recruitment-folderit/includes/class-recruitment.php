<?php
namespace WeDevs\ERP\Recruitment;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 *  Recruitment class HR
 *
 *  Recruitment for employees
 *
 * @since 0.1
 *
 * @author weDevs <info@wedevs.com>
 */
class Recruitment {

  use Hooker;

  private $post_type = 'erp_hr_recruitment';
  private $post_type_plural = 'erp_hr_recruitments';
  private $assign_type = [ ];

  /**
     *  Load autometically all actions
     */
  function __construct() {

    $this->assign_type = array( '' => __( '-- Select --', 'wp-erp-rec' ), 'all_employee' => __( 'All Employees', 'wp-erp-rec' ), 'selected_employee' => __( 'Selected Employee', 'wp-erp-rec' ) );

    $this->action( 'init', 'post_types' );
    $this->action( 'do_meta_boxes', 'do_metaboxes' );
    $this->action( 'save_post', 'save_recruitment_meta', 10, 2 );
    $this->action( 'manage_erp_hr_recruitment_posts_custom_column', 'recruitment_table_content', 10, 2 );
    $this->action( 'delete_post', 'delete_candidate_info' );
    $this->action( 'wp', 'force_404_redirect' );

    $this->filter( 'manage_erp_hr_recruitment_posts_columns', 'recruitment_table_head' );
    $this->filter( 'the_content', 'single_job_content' );
    $this->action( 'admin_url', 'change_url_for_add_new_post', 10, 3 );

    $this->filter( 'post_row_actions', 'remove_quick_edit' );

    add_shortcode( 'erp-job-list', array( $this, 'job_list' ) );
    add_shortcode( 'erp-job-post', array( $this, 'job_post' ) );

  }

  /*
     * override 404 page if single page is not valid
     * para
     * return void
     */
  public function force_404_redirect() {
    global $wp_query;

    if ( is_singular( $this->post_type ) ) {
      $exp_date = date( 'Y-m-d', strtotime( get_post_meta( get_the_ID(), '_expire_date', true ) ) );
      $permanent_job = get_post_meta( get_the_ID(), '_permanent_job', true);
      if ( date( 'Y-m-d' ) > $exp_date && $permanent_job != true ) {
        $wp_query->set_404();
      }
    }
  }

  /**
     * Filter admin url for "Add Recruitment" button
     *
     * @return string
     */
  public function change_url_for_add_new_post( $url, $path, $blog_id ) {
    if ( 'post-new.php?post_type=erp_hr_recruitment' === $path ) {
      $url  = admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening' );
      $path = 'edit.php?post_type=erp_hr_recruitment&page=add-opening';
    }

    return $url;
  }

  /**
     * single job content
     *
     * @return
     */
  public function single_job_content( $content ) {
    global $post;

    if ( $post->post_type == 'erp_hr_recruitment' ) {
      ob_start();
      include WPERP_REC_PATH . '/templates/single-template.php';
      $content = ob_get_clean();
    }

    return $content;
  }

  /**
     * shortcode job list callback function
     *
     * @return array
     */
  public function job_list( $atts ) {

    ob_start();
    include WPERP_REC_PATH . '/templates/shortcode-job-list.php';
    $content = ob_get_clean();

    return $content;
  }

  /**
     * shortcode job post callback function
     *
     * @return array
     */
  public function job_post( $atts ) {

    ob_start();
    include WPERP_REC_PATH . '/templates/shortcode-job-post.php';
    $content = ob_get_clean();

    return $content;
  }

  /**
     * Register Recruitment post type
     *
     * @since 0.1
     *
     * @return void
     */
  function post_types() {
    $capability = 'erp_hr_manager';

    register_post_type( $this->post_type, array(
      'label'           => __( 'Recruitment', 'wp-erp-rec' ),
      'description'     => '',
      'public'          => true,
      'show_ui'         => true,
      'show_in_menu'    => false,
      'capability_type' => 'post',
      'hierarchical'    => false,
      'rewrite'         => array('slug' => 'job'),
      'query_var'       => false,
      'supports'        => array(
        'title',
        'editor'
      ),
      'menu_icon'       => 'dashicons-businessman',
      'capabilities'    => array(
        'edit_post'          => $capability,
        'read_post'          => $capability,
        'delete_posts'       => $capability,
        'edit_posts'         => $capability,
        'edit_others_posts'  => $capability,
        'publish_posts'      => $capability,
        'read_private_posts' => $capability,
        'create_posts'       => $capability,
        'delete_post'        => $capability
      ),
      'labels'          => array(
        'name'               => __( 'Recruitment', 'wp-erp-rec' ),
        'singular_name'      => __( 'Recruitment', 'wp-erp-rec' ),
        'menu_name'          => __( 'Recruitment', 'wp-erp-rec' ),
        'add_new'            => __( 'Add Recruitment', 'wp-erp-rec' ),
        'add_new_item'       => __( 'Add New Recruitment', 'wp-erp-rec' ),
        'edit'               => __( 'Edit', 'wp-erp-rec' ),
        'edit_item'          => __( 'Edit Recruitment', 'wp-erp-rec' ),
        'new_item'           => __( 'New Recruitment', 'wp-erp-rec' ),
        'view'               => __( 'View Recruitment', 'wp-erp-rec' ),
        'view_item'          => __( 'View Recruitment', 'wp-erp-rec' ),
        'search_items'       => __( 'Search Recruitment', 'wp-erp-rec' ),
        'not_found'          => __( 'No Recruitment Found', 'wp-erp-rec' ),
        'not_found_in_trash' => __( 'No Recruitment found in trash', 'wp-erp-rec' ),
        'parent'             => __( 'Parent Recruitment', 'wp-erp-rec' )
      )
    )
                      );
  }

  /**
     * initialize meta boxes for recruitment post type
     *
     * @return void
     */
  public function do_metaboxes() {
    add_meta_box( 'erp-hr-recruitment-meta-box', __( 'Recruitment Settings', 'wp-erp-rec' ),
                 array( $this, 'meta_boxes_cb' ), $this->post_type, 'advanced', 'high' );

    add_meta_box( 'erp-hr-applicant-personal-fields', __( 'Applicant Personal Fields', 'wp-erp-rec' ),
                 array( $this, 'personal_fields' ), $this->post_type, 'advanced', 'low' );

    add_meta_box( 'erp-hr-applicantion-stage', __( 'Hiring workflow', 'wp-erp-rec' ),
                 array( $this, 'edit_stage' ), $this->post_type, 'advanced', 'low' );

    add_meta_box( 'erp-hr-applicant-questionnaire', __( 'Set Question For This Job', 'wp-erp-rec' ),
                 array( $this, 'applicant_questionnaire' ), $this->post_type, 'advanced', 'low' );

  }

  /**
     * recruitment metabox callback function
     *
     * @return
     */
  public function meta_boxes_cb( $post_id ) {
    global $post;

    $employees              = erp_hr_get_employees( array( 'no_object' => true ) );
    $departments            = erp_hr_get_departments( array( 'no_object' => true ) );
    $employment_types       = erp_hr_get_employee_types();
    $minimum_experience     = erp_rec_get_recruitment_minimum_experience();
    $get_hiring_lead        = get_post_meta( $post->ID, '_hiring_lead', true );
    $get_department         = get_post_meta( $post->ID, '_department', true );
    $get_employment_type    = get_post_meta( $post->ID, '_employment_type', true );
    $get_remote_job         = get_post_meta( $post->ID, '_remote_job', true );
    $get_minimum_experience = get_post_meta( $post->ID, '_minimum_experience', true );
    $get_location           = get_post_meta( $post->ID, '_location', true );
    $get_state              = get_post_meta( $post->ID, '_state', true );
    $get_state_text         = get_post_meta( $post->ID, '_state_text', true );
    $get_expire_date        = get_post_meta( $post->ID, '_expire_date', true );
    $get_vacancy            = get_post_meta( $post->ID, '_vacancy', true );
    $get_hide_job_list      = get_post_meta( $post->ID, '_hide_job_list', true );
    $get_permanent_job      = get_post_meta( $post->ID, '_permanent_job', true );
?>
<table class="form-table erp-hr-recruitment-meta-wrap-table" xmlns:v-on="http://www.w3.org/1999/xhtml">
  <tr>
    <td width="50%">
      <label><?php _e( 'Select Hiring Lead', 'wp-erp-rec' ); ?></label>
      <?php
    $hiring_lead_ids = get_post_meta( $post->ID, '_hiring_lead', true ) ? get_post_meta( $post->ID, '_hiring_lead', true ) : [];
    $hiring_lead_name = [];
    if ( is_array( $hiring_lead_ids ) && count( $hiring_lead_ids ) > 0 ) {
      foreach ( $hiring_lead_ids as $hiring_lead_id ) {
        $employee_object = new \WeDevs\ERP\HRM\Employee( intval( $hiring_lead_id ) );
        array_push( $hiring_lead_name, $employee_object->get_full_name() );
      }
    }
      ?>
      <select name="hiring_lead[]" id="hiring_lead" class="erp-select2" multiple="multiple">
        <option></option>
        <?php foreach ( $employees as $user ) : ?>
        <?php if ( in_array( $user->user_id, $hiring_lead_ids ) ) : ?>
        <option value="<?php echo $user->user_id; ?>" selected="selected">
          <?php echo $user->display_name; ?>
        </option>
        <?php else : ?>
        <option value="<?php echo $user->user_id; ?>">
          <?php echo $user->display_name; ?>
        </option>
        <?php endif; ?>
        <?php endforeach; ?>
      </select>
    </td>
    <td width="50%">
      <label><?php _e( 'Department', 'wp-erp-rec' ); ?></label> <select name="department">
      <option></option><?php foreach ( $departments as $department ) { ?>
      <option value='<?php echo $department->id; ?>'<?php if ( $get_department == $department->id ): ?> selected="selected"<?php endif; ?>>
        <?php echo $department->title; ?>
      </option><?php } ?>
      </select>
    </td>
  </tr>

  <tr>
    <td width="50%">
      <label><?php _e( 'Employment Type', 'wp-erp-rec' ); ?></label> <select name="employment_type">
      <option></option><?php foreach ( $employment_types as $key => $value ) { ?>
      <option value='<?php echo $key; ?>'<?php if ( $get_employment_type == $key ): ?> selected="selected"<?php endif; ?>>
        <?php echo $value; ?>
      </option><?php } ?>
      </select>
    </td>
    <td width="50%">
      <label>
        <input type="checkbox" name="remote_job" <?php echo ( $get_remote_job == 1 ) ? 'checked' : ''; ?> />
        <?php _e( 'Remote working is an option for this opening', 'wp-erp-rec' ); ?>
      </label>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <label>
        <input type="checkbox" name="hide_job_list" <?php echo ( $get_hide_job_list == 1 ) ? 'checked' : ''; ?> />
        <?php _e( 'Hide job from public list', 'wp-erp-rec' ); ?>
      </label>
    </td>
    <td width="50%">
      <label>
        <input type="checkbox" name="permanent_job" <?php echo ( $get_permanent_job == 1 ) ? 'checked' : ''; ?> />
        <?php _e( 'Permanent job (doesn\'t expire and has no candidate limits)', 'wp-erp-rec' ); ?>
      </label>
    </td>
  </tr>

  <tr>
    <td width="50%">
      <label><?php _e( 'Minimum Experience', 'wp-erp-rec' ); ?></label> <select name="minimum_experience">
      <option></option><?php
    foreach ( $minimum_experience as $key => $value ) { ?>
      <option value='<?php echo $key; ?>'<?php if ( $get_minimum_experience == $key ): ?> selected="selected"<?php endif; ?>>
        <?php echo $value; ?>
      </option><?php } ?>
      </select>
    </td>
    <td width="50%">
      <label><?php _e( 'Location', 'wp-erp-rec' ); ?></label>
      <input type="text" id="glocation" name="location" value="<?php echo $get_location; ?>" />
      <input type="hidden" id="latlocation" name="latlocation" value="" />
      <input type="hidden" id="lnglocation" name="lnglocation" value="" />
    </td>
  </tr>

  <tr>
    <td width="50%">
      <?php
    $date = date( 'Y-m-d' );
    $date = date( 'Y-m-d', strtotime( '+30 days', strtotime( $date ) ) );
      ?>
      <?php erp_html_form_input(
        array( 'label' => __( 'Submission Deadline', 'wp-erp-rec' ),
              'name'  => 'expire_date', 'value' => ( $get_expire_date == "" ) ? $date : $get_expire_date,
              'type'  => 'text', 'class' => 'erp-date-field' )
      );
      ?>
    </td>
    <td width="50%">
      <label><?php _e( 'Vacancy', 'wp-erp-rec' ); ?></label>
      <input type="text" id="vacancy" name="vacancy" value="<?php _e( $get_vacancy, 'wp-erp-rec' ); ?>" maxlength="2" />
    </td>
  </tr>
  <tr></tr>
  <tr></tr>
  <tr>
    <td width="50%"></td>
    <td width="50%"></td>
  </tr>
  <tr></tr>

</table>
<?php wp_nonce_field( 'hr_recruitment_meta_action', 'hr_recruitment_meta_action_nonce' );
  }

  /**
     * recruitment personal fields
     *
     * @return void
     */
  public function personal_fields( $post_id ) {
    global $post, $fArray;

    $fields             = erp_rec_get_personal_fields();
    $db_personal_fields = get_post_meta( $post->ID, '_personal_fields', true );

    // var_dump( $fields, $db_personal_fields );

    // check has new extra field exist or not
    $extra_fields = get_option( 'erp-employee-fields' );

    $new_fields = [ ];
    $count      = 0;
    if ( is_array( $extra_fields ) ) {
      foreach ( $extra_fields as $single ) {

        $new_fields[$count] = [
          'label'       => $single['label'],
          'name'        => $single['name'],
          'section'     => $single['section'],
          'icon'        => $single['icon'],
          'required'    => $single['required'],
          'type'        => $single['type'],
          'placeholder' => $single['placeholder'],
          'helptext'    => $single['helptext'],
        ];

        if ( is_array( $single['options'] ) && !empty( $single['options'] ) ) {
          foreach ( $single['options'] as $opt ) {
            $new_fields[$count]['options'][$opt['value']] = $opt['text'];
          }
        }

        $count++;
      }
    }

    if ( is_array( $db_personal_fields ) ) { // if user full filled candidate basic info during step filling
      if ( is_array( $new_fields ) ) { // check if new fields has or not
        $db_personal_fields_name = [ ];
        foreach ( $db_personal_fields as $dbf ) { // make an array to match new fields exist in personal fields or not
          array_push( $db_personal_fields_name, json_decode( $dbf )->field );
        }
        foreach ( $new_fields as $single_field ) {
          if ( !in_array( $single_field['name'], $db_personal_fields_name ) ) {
            $push_new_field = json_encode( [ "field" => $single_field['name'], "type" => $single_field['type'], "req" => filter_var( $single_field['required'], FILTER_VALIDATE_BOOLEAN ), "showfr" => true ] );
            array_push( $db_personal_fields, $push_new_field );
          }
        }
      }
      update_post_meta( $post->ID, '_personal_fields', $db_personal_fields );
    } else { // if user did not full fill candidate basic information step at the first time then fields will come from default array = $personal_fields
      $default_personal_fields = [ ];
      foreach ( $fields as $default_field ) { // making the json and push to a new array as personal fields
        $push_new_field = json_encode( [ "field" => $default_field['name'], "type" => $default_field['type'], "req" => $default_field['required'], "showfr" => true ] );
        array_push( $default_personal_fields, $push_new_field );
      }
      update_post_meta( $post->ID, '_personal_fields', $default_personal_fields );
      $db_personal_fields = $default_personal_fields;
    }

    // HOTFIX para restaurar el metadata _personal_fields
    /*
    $default_showfr = false;
    $default_hotfix_fields = array("english_level", "observations", "cover_letter", "mobile");
    $default_personal_fields = [ ];
    foreach ( $fields as $default_field ) {
      if ( in_array($default_field['name'], $default_hotfix_fields) ) {
        $default_showfr = true;
      } else { 
        $default_showfr = false;
      }
      $push_new_field = json_encode( [ "field" => $default_field['name'], "type" => $default_field['type'], "req" => $default_field['required'], "showfr" => $default_showfr ] );
      array_push( $default_personal_fields, $push_new_field );
    }
    update_post_meta( $post->ID, '_personal_fields', $default_personal_fields );
    $db_personal_fields = $default_personal_fields;
    */
?>

<div class="applicant_personal_mandatory_fields">
  <label>
    <?php _e( 'First Name', 'wp-erp-rec' ); ?>
  </label>

  <div class="alignright">
    <label>
      <?php _e( 'This field is required', 'wp-erp-rec' ); ?>
    </label>
  </div>
</div>
<div class="applicant_personal_mandatory_fields">
  <label>
    <?php _e( 'Last Name', 'wp-erp-rec' ); ?>
  </label>

  <div class="alignright">
    <label>
      <?php _e( 'This field is required', 'wp-erp-rec' ); ?>
    </label>
  </div>
</div>
<div class="applicant_personal_mandatory_fields">
  <label>
    <?php _e( 'Email', 'wp-erp-rec' ); ?>
  </label>

  <div class="alignright">
    <label>
      <?php _e( 'This field is required', 'wp-erp-rec' ); ?>
    </label>
  </div>
</div>
<div class="applicant_personal_mandatory_fields">
  <label>
    <?php _e( 'Upload CV', 'wp-erp-rec' ); ?>
  </label>

  <div class="alignright">
    <label>
      <?php _e( 'This field is required', 'wp-erp-rec' ); ?>
    </label>
  </div>
</div>
<hr>

<div id="label-wrapper">
  <label class="applicant_check_all"><input id="checkAll" type="checkbox"><?php _e( 'Check All', 'wp-erp-rec' ); ?></label>
  <label class="applicant_check_all" style="float: right"><input id="checkAllReq" type="checkbox"><?php _e( 'Check All', 'wp-erp-rec' ); ?></label>
</div>

<div id="sortit">
  <?php if ( count( $db_personal_fields ) > 0 && is_array( $db_personal_fields ) ) : ?>
  <?php foreach ( $db_personal_fields as $key => $value ) :
    $fArray = [
      "field"  => json_decode( $value )->field,
      "type"   => json_decode( $value )->type,
      "req"    => json_decode( $value )->req,
      "showfr" => json_decode( $value )->showfr
    ];
  ?>
  <div id="<?php echo htmlspecialchars( json_encode( $fArray ), ENT_QUOTES, 'UTF-8' ); ?>" class="applicant_personal_fields">
    <label>
      <?php if ( json_decode( $value )->showfr == true ) : ?>
      <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field; ?>" checked="checked">
      <?php else : ?>
      <input class="applicant_chkbox" type="checkbox" name="efields[]" value="<?php echo json_decode( $value )->field; ?>">
      <?php endif; ?>

      <?php echo ucwords( str_replace( '_', ' ', json_decode( $value )->field ) ); ?>
    </label>

    <div class="alignright">
      <label>
        <?php if ( json_decode( $value )->req == true ) : ?>
        <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field; ?>" checked="checked">
        <?php else : ?>
        <input class='applicant_chkbox_req' type="checkbox" name="req[]" value="<?php echo json_decode( $value )->field; ?>">
        <?php endif; ?>

        <?php _e( 'This field is required', 'wp-erp-rec' ); ?>
      </label>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php
  }

  /*
     * update stages
     * para
     * return
     */
  public function edit_stage() {
?>
<div id="update_openingform_stage_handler" class="openingform_input_wrapper">
  <button style="margin-bottom: 10px;" class="button alignright" v-on:click.prevent="createStage">
    <i class="fa fa-plus"></i>&nbsp;<?php _e( 'Add Stage', 'wp-erp-rec' ); ?>
  </button>
  <div id="stage-validation-message"></div>
  <div id="openingform_sortit_edit_mode">
    <?php $get_stage = erp_rec_get_stage( get_the_ID() ); ?>
    <?php foreach ( $get_stage as $st ) : ?>
    <div class="stage-list">
      <?php if ( array_key_exists( 'stage_selected', $st ) && is_null( $st['stage_selected'] ) ) : ?>
      <label>
        <input type="checkbox" name="stage_name[]" value="<?php echo $st['sid']; ?>"><?php echo $st['title']; ?>
        <input type="hidden" class="candidate_number" value="<?php echo $st['candidate_number']; ?>">
      </label>
      <?php else: ?><label>
      <input type="checkbox" name="stage_name[]" value="<?php echo $st['sid']; ?>" checked="checked"><?php echo $st['title']; ?>
      <input type="hidden" class="candidate_number" value="<?php echo isset( $st['candidate_number'] ) ? $st['candidate_number'] : 0; ?>">
      </label>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <span class="spinner"></span>
</div>
<?php
                               }

  /**
     * Save Recruitment post meta
     *
     * @since  0.1
     *
     * @param  integer $post_id
     * @param  object  $post
     *
     * @return mixed
     */
  function save_recruitment_meta( $post_id, $post ) {
    global $post;

    if ( !isset( $_POST['hr_recruitment_meta_action_nonce'] ) ) {
      return $post_id;
    }

    if ( !wp_verify_nonce( $_POST['hr_recruitment_meta_action_nonce'], 'hr_recruitment_meta_action' ) ) {
      return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }

    $post_type = get_post_type_object( $post->post_type );

    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
      return $post_id;
    }

    if ( !current_user_can( 'manage_options' ) ) {
      return $post_id;
    }

    if ( $post->post_type != 'erp_hr_recruitment' ) {
      return;
    }

    $recruitment_hiring_lead        = ( isset( $_POST['hiring_lead'] ) ) ? $_POST['hiring_lead'] : '';
    $recruitment_department         = ( isset( $_POST['department'] ) ) ? $_POST['department'] : '';
    $recruitment_employment_type    = ( isset( $_POST['employment_type'] ) ) ? $_POST['employment_type'] : '';
    $remote_job                     = ( isset( $_POST['remote_job'] ) ) ? 1 : 0;
    $recruitment_minimum_experience = ( isset( $_POST['minimum_experience'] ) ) ? $_POST['minimum_experience'] : '';
    $location                       = ( isset( $_POST['location'] ) ) ? $_POST['location'] : '';
    $latlocation                    = ( isset( $_POST['latlocation'] ) ) ? $_POST['latlocation'] : '';
    $lnglocation                    = ( isset( $_POST['lnglocation'] ) ) ? $_POST['lnglocation'] : '';
    $state                          = ( isset( $_POST['state'] ) ) ? $_POST['state'] : '';
    $erp_state_text                 = ( isset( $_POST['erp_state_text'] ) ) ? $_POST['erp_state_text'] : '';
    $expire_date                    = ( isset( $_POST['expire_date'] ) ) ? $_POST['expire_date'] : '';
    $stage_name                     = ( isset( $_POST['stage_name'] ) ) ? $_POST['stage_name'] : [ ];
    $vacancy                        = ( isset( $_POST['vacancy'] ) ) ? $_POST['vacancy'] : 0;
    $hide_job_list                  = ( isset( $_POST['hide_job_list'] ) ) ? 1 : 0;
    $permanent_job                  = ( isset( $_POST['permanent_job'] ) ) ? 1 : 0;

    $job_data['job_title']          = get_the_title( $post_id );
    $job_data['hiring_lead']        = $recruitment_hiring_lead;
    $job_data['department']         = $recruitment_department;
    $job_data['employment_type']    = $recruitment_employment_type;
    $job_data['remote_job']         = $remote_job;
    $job_data['minimum_experience'] = $recruitment_minimum_experience;
    $job_data['vacancy']            = $vacancy;
    $job_data['hide_job_list']      = $hide_job_list;
    $job_data['permanent_job']      = $permanent_job;

    if ( isset($recruitment_hiring_lead) && $recruitment_hiring_lead == '' ) {
      //$location = add_query_arg( array( 'post_error_message' => 1, 'action' => 'edit' ), admin_url( 'post.php' ) );
      wp_redirect( admin_url( 'post.php?post='.$post_id.'&post_error_message=1&action=edit' ) );
      //wp_redirect( $location );
      exit();
    }

    update_post_meta( $post_id, '_hiring_lead', $recruitment_hiring_lead );
    update_post_meta( $post_id, '_department', $recruitment_department );
    update_post_meta( $post_id, '_employment_type', $recruitment_employment_type );
    update_post_meta( $post_id, '_remote_job', $remote_job );
    update_post_meta( $post_id, '_minimum_experience', $recruitment_minimum_experience );
    update_post_meta( $post_id, '_location', $location );
    update_post_meta( $post_id, '_latlocation', $latlocation );
    update_post_meta( $post_id, '_lnglocation', $lnglocation );
    update_post_meta( $post_id, '_state', $state );
    update_post_meta( $post_id, '_state_text', $erp_state_text );
    update_post_meta( $post_id, '_expire_date', $expire_date );
    update_post_meta( $post_id, '_vacancy', $vacancy );
    update_post_meta( $post_id, '_hide_job_list', $hide_job_list );
    update_post_meta( $post_id, '_permanent_job', $permanent_job );

    // update post meta for personal fields
    $efields            = isset( $_POST['efields'] ) ? $_POST['efields'] : [ ];
    $req                = isset( $_POST['req'] ) ? $_POST['req'] : [ ];
    $db_personal_fields = get_post_meta( $post->ID, '_personal_fields', true );
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
    }

    update_post_meta( $post_id, '_personal_fields', $personal_fields );
    // update stages
    erp_rec_update_stage( $stage_name, $post_id );
    // update questionnaire
    $questions = ( isset( $_POST['questions'] ) ? $_POST['questions'] : '' );
    update_post_meta( $post_id, '_erp_hr_questionnaire', $questions );

    do_action( 'erp_rec_opened_recruitment', $job_data );
  }

  /**
     * recruitment applicant questionnaire
     *
     * @return void
     */
  public function applicant_questionnaire( $post_id ) {
    global $post;
    $localize_scripts = [ 'qset' => get_post_meta( $post->ID, '_erp_hr_questionnaire', true ) ];
    wp_localize_script( 'erp-recruitment-app-script', 'wpErpHrQuestionnaire', $localize_scripts );
?>
<div id="meta_inner">
  <?php
    // get questionnaire post types and show in a drop down list
    $posts = get_posts( array( 'post_type' => 'erp_hr_questionnaire', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    $get_questionnaire = get_post_meta($post->ID, '_erp_hr_questionnaire', true);
  ?>
  <div>
    <label><?php _e( 'Please Select Question set:', 'wp-erp-rec' ); ?></label>
    <select id="qset">
      <?php foreach ( $posts as $p ) : ?><?php if ( count( get_post_meta( $p->ID, '_erp_hr_questionnaire', true ) ) > 0 ) : ?>
      <option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option><?php endif; ?><?php endforeach; ?>
    </select>
    <span class="add page-title-action page-title-action-q"><?php _e( 'Add Question Set', 'wp-erp-rec' ); ?></span>
  </div>
  <span id="here"></span>
</div>
<?php
  }

  /**
     * custom columns
     *
     * @return array
     */
  public function recruitment_table_head( $defaults ) {
    $defaults['title']       = __( 'Job Title', 'wp-erp-rec' );
    $defaults['view_list']   = __( 'Job Title', 'wp-erp-rec' );
    $defaults['applicants']  = __( 'Applicants', 'wp-erp-rec' );
    $defaults['hiring_lead'] = __( 'Hiring Lead', 'wp-erp-rec' );
    $defaults['status']      = __( 'Status', 'wp-erp-rec' );
    $defaults['created']     = __( 'Created On', 'wp-erp-rec' );
    $defaults['expire_date'] = __( 'Expire Date', 'wp-erp-rec' );

    unset($defaults['title']);
    unset($defaults['date']);

    return $defaults;
  }

  /**
     * Remove quick edit from recruitment post type
     *
     * @param  array  $actions
     *
     * @return array
     */
  function remove_quick_edit( $actions ) {
    global $post;

    if ( $post->post_type == 'erp_hr_recruitment' ) {
      unset( $actions['inline hide-if-no-js'] );
    }

    return $actions;
  }

  /**
     * get and set content of recruitment list table
     *
     * @return void
     */
  public function recruitment_table_content( $column_name, $post_id ) {

    if ( $column_name == 'view_list' ) { ?>
<a class="jtitle" href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&jobid=' . get_the_ID() ); ?>">
  <?php the_title(); ?>
</a>
<?php
                                       }

    if ( $column_name == 'status' ) {
      if ( 'publish' == get_post_status() ) {
        _e( 'Open', 'wp-erp-rec' );
      } elseif ( 'pending' == get_post_status() ) {
        _e( 'On Hold', 'wp-erp-rec' );
      } elseif ( 'draft' == get_post_status() ) {
        _e( 'Draft', 'wp-erp-rec' );
      }
    }

    if ( $column_name == 'applicants' ) { ?>
<a class="jtitle" href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&jobid=' . get_the_ID() ); ?>">
  <?php echo erp_rec_applicant_counter( get_the_ID() ); ?>
</a>
<?php }

    if ( $column_name == 'hiring_lead' ) {
      $hiring_lead_ids = get_post_meta( $post_id, '_hiring_lead', true ) ? get_post_meta( $post_id, '_hiring_lead', true ) : [];
      $hiring_lead_name = [];
      if ( is_array( $hiring_lead_ids ) && count( $hiring_lead_ids ) > 0 ) {
        foreach ( $hiring_lead_ids as $hiring_lead_id ) {
          $employees = new \WeDevs\ERP\HRM\Employee( intval( $hiring_lead_id ) );
          array_push( $hiring_lead_name, $employees->get_full_name() );
        }
        $hiring_list = implode( ', ', $hiring_lead_name );
        echo $hiring_list;
      } else {
        _e( 'No Lead found', 'wp-erp-rec' );
      }
    }

    if ( $column_name == 'created' ) {
      echo get_the_date();
    }

    if ( $column_name == 'expire_date' ) {
      $e_date = get_post_meta( $post_id, '_expire_date', true ) ? get_post_meta( $post_id, '_expire_date', true ) : "N/A";
      if ( $e_date != "N/A" ) {
        echo date( "M, d Y", strtotime( $e_date ) );
      }
    }

  }

  /*
     * function delete candidate info because employer is deleting job
     * para jobid
     * return void
     */
  public function delete_candidate_info( $jobid ) {
    if ( get_post_type( $jobid ) == 'erp_hr_recruitment' ) {
      global $wpdb;
      $query = "SELECT id as appid, applicant_id FROM {$wpdb->prefix}erp_application as app WHERE app.job_id='" . $jobid . "'";
      $udata = $wpdb->get_results( $query, ARRAY_A );
      foreach ( $udata as $peoplemetadata ) {
        $wpdb->delete( $wpdb->prefix . 'erp_peoples', array( 'id' => $peoplemetadata['applicant_id'] ), array( '%d' ) );
        $wpdb->delete( $wpdb->prefix . 'erp_peoplemeta', array( 'erp_people_id' => $peoplemetadata['applicant_id'] ), array( '%d' ) );
        $wpdb->delete( $wpdb->prefix . 'erp_application_rating', array( 'application_id' => $peoplemetadata['appid'] ), array( '%d' ) );
      }
      $wpdb->delete( $wpdb->prefix . 'erp_application', array( 'job_id' => $jobid ), array( '%d' ) );
      $wpdb->delete( $wpdb->prefix . 'erp_application_job_stage_relation', array( 'jobid' => $jobid ), array( '%d' ) );
    }
  }

}

new Recruitment();
