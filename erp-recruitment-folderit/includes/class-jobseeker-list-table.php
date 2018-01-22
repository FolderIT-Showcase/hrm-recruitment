<?php
namespace WeDevs\ERP\ERP_Recruitment;

/**
 * List table class
 */
class Jobseeker_List_Table extends \WP_List_Table {

  function __construct() {
    global $status, $page;

    parent::__construct(array(
      'singular' => 'jobseeker',
      'plural'   => 'jobseekers',
      'ajax'     => true
    ));
  }

  public function display() {
    $singular = $this->_args['singular'];

    $this->display_tablenav( 'top' );

    $this->screen->render_screen_reader_content( 'heading_list' );
?>
<div class="table-scroll">
  <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    <thead>
      <tr>
        <?php $this->print_column_headers(); ?>
      </tr>
    </thead>

    <tbody id="the-list"<?php
    if ( $singular ) {
      echo " data-wp-lists='list:$singular'";
    } ?>>
      <?php $this->display_rows_or_placeholder(); ?>
    </tbody>

    <tfoot>
      <tr>
        <?php $this->print_column_headers( false ); ?>
      </tr>
    </tfoot>

  </table>
</div>
<?php
    $this->display_tablenav( 'bottom' );
  }

  /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0.0
     *
     * @param  string $which
     *
     * @return void
     */
  function extra_tablenav( $which ) {

    if ( $which != 'top' ) {
      return;
    }

    $selected_status = (isset($_REQUEST['statusslug'])) ? $_REQUEST['statusslug'] : null;
    $selected_project = (isset($_REQUEST['projectid'])) ? $_REQUEST['projectid'] : null;
    $jobid = (isset($_REQUEST['jobid'])) ? $_REQUEST['jobid'] : null;

?>
<div class="alignleft actions">
  <label class="screen-reader-text" for="statusslug_select"><?php _e('Filter by Status', 'wp-erp-rec') ?></label>
  <select name="statusslug" id="statusslug_select">
    <option value=""><?php _e('- Select All -', 'wp-erp-rec'); ?></option>
    <?php echo erp_hr_get_status_dropdown($selected_status); ?>
  </select>
</div>
<div class="alignleft actions">
  <label class="screen-reader-text" for="projectid_select"><?php _e('Filter by Project', 'wp-erp-rec') ?></label>
  <select name="projectid" id="projectid_select">
    <option value=""><?php _e('- Select All -', 'wp-erp-rec'); ?></option>
    <?php echo erp_hr_get_projects_dropdown($selected_project); ?>
  </select>

</div>
<div class="alignleft actions">
  <label class="screen-reader-text" for="jobid_select"><?php _e('Filter by Position', 'wp-erp-rec') ?></label>
  <select name="jobid" id="jobid_select">
    <option value=""><?php _e('- Select All -', 'wp-erp-rec'); ?></option>
    <?php echo erp_hr_get_positions_dropdown($jobid); ?>
  </select>
</div>
<?php submit_button(__('Filter'), 'button', 'filter_button', false); ?>
<?php
  }


  /**
     * Message to show if no department found
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function no_items() {
    _e('No jobseeker found.', 'wp-erp-rec');
  }

  /**
     * Get the column names
     *
     * @since 1.0.0
     *
     * @return array
     */
  public function get_columns() {
    $columns = array(
      'cb'                => '<input type="checkbox" />',
      'full_name'         => __('Name', 'wp-erp-rec'),
      'apply_date'        => __('Date', 'wp-erp-rec'),
      //      'avg_rating'        => __('Rating', 'wp-erp-rec'),
      'summary_rating'    => __('Summary Rating Column', 'wp-erp-rec'),
      'remote'            => __('Remote', 'wp-erp-rec'),
      'job_title'         => __('Applied Job', 'wp-erp-rec'),
      'stage'             => __('Stage', 'wp-erp-rec'),
      'interview_rrhh'    => __('RRHH Interview', 'wp-erp-rec'),
      'interview_tech'    => __('Technical Interview', 'wp-erp-rec'),
      'interview_english' => __('English Interview', 'wp-erp-rec'),
      'project'           => __('Project', 'wp-erp-rec'),
      'status'            => __('Status', 'wp-erp-rec'),
      'skills'            => __('Skills', 'wp-erp-rec'),
      'action'            => __('Action', 'wp-erp-rec')
    );

    return apply_filters('erp_hr_jobseeker_table_cols', $columns);
  }

  /**
     * Show default column
     *
     * @since  1.0.0
     *
     * @param  array    $item
     * @param  string    $column_name
     *
     * @return string
     */
  public function column_default( $item, $column_name ) {
    global $wpdb;

    $jobseeker_preview_url = admin_url('edit.php?post_type=erp_hr_recruitment&page=applicant_detail&application_id=' . $item['applicationid']);
    $jobseeker_email_url = admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list_email');

    $query = "SELECT app_iv.id, app_iv.feedback_comment, app_iv.feedback_english_level, app_iv.feedback_english_conversation, types.type_detail, types.type_identifier
      FROM {$wpdb->prefix}erp_application_interview as app_iv
      LEFT JOIN {$wpdb->prefix}erp_application_interview_types as types
      ON app_iv.interview_internal_type_id = types.id
      WHERE app_iv.application_id=%d";
    $idata = $wpdb->get_results( $wpdb->prepare( $query, $item['applicationid'] ), ARRAY_A );

    $status = erp_rec_get_hiring_status();
    $terms = erp_rec_get_terms();

    $projects = erp_rec_get_available_projects(true);

    $interview_rrhh = "";
    $interview_tech = "";
    $interview_english = "";

    $interview_rrhh_style = "";
    $interview_tech_style = "";
    $interview_english_style = "";

    $interview_style = "border:1px #1e8cbe solid;";

    foreach ( $idata as $intv ) {
      if ($intv['type_identifier'] == "rrhh") {
        $interview_rrhh_style = $interview_style;

        if (trim($intv['feedback_comment']) !== '') {
          $interview_rrhh = "checked";
        }
      }

      if ($intv['type_identifier'] == "tech") {
        $interview_tech_style = $interview_style;

        if (trim($intv['feedback_comment']) !== '') {
          $interview_tech = "checked";
        }
      }

      if ($intv['type_identifier'] == "english") {
        $interview_english_style = $interview_style;

        if (trim($intv['feedback_comment']) !== '' && trim($intv['feedback_english_level']) !== '' && trim($intv['feedback_english_conversation']) !== '') {
          $interview_english = "checked";
        }
      }
    }

    switch ($column_name) {
      case 'apply_date':
        return date('Y-m-d', strtotime($item['apply_date']));
      case 'full_name':
        return sprintf(__('<a href="%s">' . $item['first_name'] . ' ' . $item['last_name'] . '</a>', 'wp-erp-rec'), $jobseeker_preview_url);
      case 'avg_rating':
        return number_format($item['avg_rating'], 2, '.', ',');
      case 'summary_rating':
        return number_format($item['summary_rating'], 1, '.', ',');
      case 'remote':
        return '<input type="checkbox" ' . ($item['remote']=="1"?"checked":"") .' disabled></input>';
      case 'skills':
        if(!empty($item['skills'])) {
          $terms_names = [];
          $terms_cloud = '';
//          $terms_cloud .= '<span class="select2 select2-container select2-container--default select2-container--disabled">';
//          $terms_cloud .= '<span class="selection">';
//          $terms_cloud .= '<span class="select2-selection select2-selection--multiple" style="background-color:transparent;border-width:0px;">';
          $terms_cloud .= '<ul style="padding:0px;margin:0px;">';
          $skills = json_decode(str_replace('&quot;', '"', $item['skills']), true)['terms'];
          foreach($skills as $skill) {
            $terms_cloud .= '<li style="margin:2px;padding:0px 5px;border:1px solid #337ab7;border-radius:4px;color:#337ab7;background-color:#fff;display:inline-block;">'.$terms[$skill].'</li>';
            //            array_push($terms_names, $terms[$skill]);
          }
          //          return implode(", ", $terms_names);
          $terms_cloud .= '</ul>';
//          $terms_cloud .= '</span>';
//          $terms_cloud .= '</span>';
//          $terms_cloud .= '</span>';
          return $terms_cloud;
        }
        break;
      case 'job_title':
        return $item['post_title'];
      case 'stage':
        return $item['title'];
      case 'interview_rrhh':
        return '<input type="checkbox" ' . $interview_rrhh . ' style="' . $interview_rrhh_style. '" disabled></input>';
      case 'interview_tech':
        return '<input type="checkbox" ' . $interview_tech . ' style="' . $interview_tech_style. '" disabled></input>';
      case 'interview_english':
        return '<input type="checkbox" ' . $interview_english . ' style="' . $interview_english_style. '" disabled></input>';
      case 'action':
        $jobseeker_email_url .= '&email_ids[0]=' . $item['email'];
        return sprintf(__('<a class="fa" href="%s"><span class="dashicons dashicons-visibility"></span></a> | <a class="fa" href="%s"><span class="dashicons dashicons-email-alt"></span></a>'), $jobseeker_preview_url, $jobseeker_email_url);
      case 'project':
        return $projects[$item['project_id']];
      case 'status':
        return $status[$item['status']];
      default:
    }
    return $item[$column_name];
  }

  /**
     * Get sortable columns
     *
     * @since 1.0.0
     *
     * @return array
     */
  public function get_sortable_columns() {
    $sortable_columns = array(
      'apply_date'     => array( 'apply_date', true ),
      'full_name'      => array( 'full_name', true ),
      //      'avg_rating'     => array( 'avg_rating', true ),
      'summary_rating' => array( 'summary_rating', true ),
      'remote'         => array( 'remote', true ),
      'project'        => array( 'project_title', true ),
      'stage'          => array( 'title', true ),
      'job_title'      => array( 'post_title', true ),
    );

    return $sortable_columns;
  }

  /**
     * Render the bulk edit checkbox
     *
     * @since 1.0.0
     *
     * @param array $item
     *
     * @return string
     */
  public function column_cb( $item ) {
    return sprintf(
      '<input type="checkbox" name="bulk-email[]" value="%s" />', $item['email']
    );
  }

  /**
     * Returns an associative array containing the bulk action.
     *
     * @since 1.0.0
     *
     * @return array
     */
  public function get_bulk_actions() {
    $actions = [
      'bulk-email' => __('Send Email', 'wp-erp-rec')
    ];

    return $actions;
  }

  /**
     * Render current trigger bulk action
     *
     * @since 1.0.0
     *
     * @return string
     */
  public function current_action() {

    if ( isset( $_REQUEST['filter_button'] ) ) {
      return 'filter_button';
    }

    if ( isset( $_REQUEST['recruitment_search'] ) ) {
      return 'recruitment_search';
    }

    return parent::current_action();
  }

  /**
     * Search form for list table
     *
     * @since 1.0.0
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
  public function search_box( $text, $input_id ) {

    if ( empty($_REQUEST['s']) && !$this->has_items() ) {
      return;
    }

    $input_id = $input_id . '-search-input';

    if ( !empty($_REQUEST['orderby']) ) {
      echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
    }

    if ( !empty($_REQUEST['order']) ) {
      echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
    }

    if ( !empty($_REQUEST['status']) ) {
      echo '<input type="hidden" name="status" value="' . esc_attr($_REQUEST['status']) . '" />';
    }

?>
<p class="search-box">
  <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
  <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
  <?php submit_button($text, 'button', 'recruitment_search', false, array('id' => 'search-submit')); ?>
</p>
<?php
  }

  /**
     * Prepare the class items
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function prepare_items() {
    global $per_page;
    $columns = $this->get_columns();
    $hidden = [];
    $sortable = $this->get_sortable_columns();
    $primary = 'full_name';
    $this->_column_headers = array($columns, $hidden, $sortable, $primary);

    $per_page = 20;
    $current_page = $this->get_pagenum();
    $offset = ($current_page - 1) * $per_page;
    $this->page_status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '2';
    $jobid = isset($_REQUEST['jobid']) ? $_REQUEST['jobid'] : 0;

    // only necessary because we have sample data
    $args = array(
      'offset' => $offset,
      'number' => $per_page,
      'jobid'  => $jobid
    );

    if ( isset($_REQUEST['statusslug']) && $_REQUEST['statusslug'] ) {
      $args['status'] = $_REQUEST['statusslug'];
    }

    if ( isset($_REQUEST['projectid']) && $_REQUEST['projectid'] ) {
      $args['project_id'] = $_REQUEST['projectid'];
    }

    if ( isset($_REQUEST['jobid']) && $_REQUEST['jobid'] ) {
      $args['jobid'] = $_REQUEST['jobid'];
    }

    if ( isset($_REQUEST['filter_stage']) && $_REQUEST['filter_stage'] ) {
      $args['stage'] = $_REQUEST['filter_stage'];
    }

    if ( isset($_REQUEST['filter_added_by_me']) && $_REQUEST['filter_added_by_me'] ) {
      $args['added_by_me'] = $_REQUEST['filter_added_by_me'];
    }

    if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
      $args['orderby'] = $_REQUEST['orderby'];
      $args['order'] = $_REQUEST['order'] ;
    }

    if ( isset($_REQUEST['s']) ) {
      $args['search_key'] = $_REQUEST['s'];
    }

    $skills = [];
    foreach($_GET as $key => $value) {
      if (strpos($key, 'skills_') === 0) {
        array_push($skills, substr($key, strlen('skills_')));
      }
    }

    if ( !empty($skills) ) {
      $args['skills'] = $skills;
    }

    $this->items = erp_rec_get_applicants_information($args);
    $total_rows = erp_rec_total_applicant_counter($args);

    $this->set_pagination_args(array(
      'total_items' => $total_rows,
      'per_page'    => $per_page
    ));
  }
}
