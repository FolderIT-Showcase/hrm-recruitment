<?php
namespace WeDevs\ERP\ERP_Recruitment;

/**
 * List table class
 */
class Jobseeker_List_Table extends \WP_List_Table {

  private $english_levels;
  private $english_conversation;
  private $interview_style;
  private $interview_types_names;
  private $interview_types_identifiers;
  private $projects;
  private $statuses;
  private $terms;

  function __construct() {
    global $status, $page;

    parent::__construct(array(
      'singular' => 'jobseeker',
      'plural'   => 'jobseekers',
      'ajax'     => true
    ));

    $this->english_levels = erp_rec_get_feedback_english_levels();
    $this->english_conversation = erp_rec_get_feedback_english_conversation();
    $this->interview_style = "border:1px #1e8cbe solid;";
    $this->interview_types_names = array(
      "interview_rrhh" => __("HR", "wp-erp-rec"),
      "interview_english" => __("English", "wp-erp-rec"),
      "interview_tech" => __("Technical", "wp-erp-rec"),
    );
    $this->interview_types_identifiers = array(
      "interview_rrhh" => "rrhh",
      "interview_english" => "english",
      "interview_tech" => "tech",
    );
    $this->projects = erp_rec_get_available_projects(true);
    $this->statuses = erp_rec_get_hiring_status();
    $this->terms = erp_rec_get_terms();
  }

  public function display() {
    $singular = $this->_args['singular'];

    $this->display_tablenav( 'top' );

    $this->screen->render_screen_reader_content( 'heading_list' );
?>
<div id="table-scroll-top-navbar" class="table-scroll-top" style="height:20px;width:100%;overflow-x:auto;overflow-y:hidden;">
  <div class="table-scroll-top-div" style="height:20px;"></div>
</div>
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
<script>
  $(function () {
    $('.table-scroll-top').on('scroll', function (e) {
      $('.table-scroll').scrollLeft($('.table-scroll-top').scrollLeft());
    }); 
    $('.table-scroll').on('scroll', function (e) {
      $('.table-scroll-top').scrollLeft($('.table-scroll').scrollLeft());
    });
  });
  $(window).on('load', function (e) {
    $('.table-scroll-top-div').width($('.table-scroll > table').width());
  });
</script>
<script>
  window.onscroll = function() {myFunction()};

  var navbar = document.getElementById("table-scroll-top-navbar");
  var sticky = navbar.offsetTop;

  function myFunction() {
    var parentWidth = navbar.parentElement.scrollWidth;
    if (window.pageYOffset >= sticky + 27) {
      navbar.classList.add("table-scroll-top-sticky");
      navbar.style.width = parentWidth + "px";
    } else {
      navbar.classList.remove("table-scroll-top-sticky");
      navbar.style.width = "100%";
    }
  }
</script>
<script>
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
  });
</script>
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

    if(array_key_exists($column_name, $this->interview_types_names)) {
      $interview_type_name = $this->interview_types_names[$column_name];
      $interview_type_identifier = $this->interview_types_identifiers[$column_name];

      $query = "SELECT app_iv.id, app_iv.feedback_comment,
        app_iv.feedback_english_level, app_iv.feedback_english_conversation, app_iv.start_date_time, 
        types.type_detail, types.type_identifier,
        (SELECT group_concat(user.display_name SEPARATOR ', ')
          FROM {$wpdb->prefix}erp_application_interviewer_relation as app_inv_relation
          LEFT JOIN {$wpdb->base_prefix}users as user
          ON app_inv_relation.interviewer_id=user.ID
          WHERE app_inv_relation.interview_id=app_iv.id) as interviewers_names
        FROM {$wpdb->prefix}erp_application_interview as app_iv
        LEFT JOIN {$wpdb->prefix}erp_application_interview_types as types
        ON app_iv.interview_internal_type_id = types.id
        WHERE app_iv.application_id=%d
        AND types.type_identifier='%s'
        ORDER BY app_iv.id";

      $idata = $wpdb->get_results( $wpdb->prepare( $query, $item['applicationid'], $interview_type_identifier ), ARRAY_A );

      $interview_tooltip = 'title="' . sprintf(__('Missing %s Interview', "wp-erp-rec"), $interview_type_name) . '"';
      $interview_count = 0;
      $interview_checked = "";

      foreach ( $idata as $intv ) {
        $intv_date = date('d/m/Y g:i A', strtotime($intv["start_date_time"]));
        $interview_active_style = $this->interview_style;
        $interview_count++;
        $interview_tooltip = 'title="';
        $interview_tooltip .= __('Date and Time : ', 'wp-erp-rec').$intv_date.'<br/>';
        $interview_tooltip .= __('Interviewers : ', 'wp-erp-rec').$intv["interviewers_names"].'<hr/>';

        if (trim($intv['feedback_comment']) !== '') {
          $interview_checked = "checked";
          if ($intv['type_identifier'] == "english") {
            $interview_tooltip .= __('Feedback English Level : ', 'wp-erp-rec').$this->english_levels[$intv["feedback_english_level"]].'<br/>';
            $interview_tooltip .= __('Feedback English Conversation : ', 'wp-erp-rec').$this->english_conversation[$intv["feedback_english_conversation"]].'<hr/>';
          }
          $interview_tooltip .= nl2br(trim(htmlspecialchars($intv["feedback_comment"])));
        } else {
          $interview_tooltip .= sprintf(__('%s Interview Scheduled', "wp-erp-rec"),$this->interview_types_names[$column_name]);
        }

        if($interview_count > 1) {
          $interview_count_more = $interview_count - 1;
          $interview_tooltip .= '<br/><i><small>'.sprintf($interview_count_more==1?__("%d more interview...", "wp-erp-rec"):__("%d more interviews...", "wp-erp-rec"),$interview_count_more).'</i></small>';
        }

        $interview_tooltip .= '"';
      }

      return '<input type="checkbox" ' . $interview_checked . ' style="zoom:1.3;' . $interview_active_style. '" disabled/><div data-html="true" data-toggle="tooltip"'. $interview_tooltip .' style="position:absolute;z-index:99;width:24px;height:24px;float:left;display:inline;margin-left:-24px;"></div>';
    }

    switch ($column_name) {
      case 'apply_date':
        return date('Y-m-d', strtotime($item['apply_date']));
      case 'full_name':
        return sprintf(__('<a href="%s">' . $item['first_name'] . ' ' . $item['last_name'] . '</a>', 'wp-erp-rec'), $jobseeker_preview_url);
      case 'avg_rating':
        return number_format($item['avg_rating'], 2, '.', ',');
      case 'summary_rating':
        $summary_comment_style = "background-color:transparent;";
        $summary_comment_tooltip = 'data-toggle="tooltip" title="' . __('Missing Summary Comment', "wp-erp-rec") . '"';
        $summary_width = 30;

        if (!empty($item["summary_comment"])) {
          $summary_comment_style = "background-color:#1e8cbe;";
          $summary_comment_tooltip = 'data-toggle="tooltip" title="'.nl2br(trim(htmlspecialchars($item["summary_comment"]))).'"';
        }

        $output = '<div data-html="true" '.$summary_comment_tooltip.' style="text-align:center;">';
        if(empty($item["summary_comment"]) && (empty($item["summary_rating"]) || $item["summary_rating"] == 0)) {
          $output .= "—";
        } else {
          $output .= number_format($item['summary_rating'], 1, '.', ',');
        }

        $output .= '<div data-toggle="tooltip" style="width:'.$summary_width.'px;height:6px;border:1px solid #1e8cbe;border-radius:6px;margin:auto;background-color:white;">';
        $output .= '<div style="width:'.round($item['summary_rating'] * $summary_width / 10).'px;height:100%;'.$summary_comment_style.'"></div>';
        $output .= '</div></div>';

        return $output;
      case 'remote':
        return '<input type="checkbox" style="zoom:1.3;" ' . ($item['remote']=="1"?"checked":"") .' disabled></input>';
      case 'skills':
        if(!empty($item['skills'])) {
          $terms_names = [];
          $terms_cloud = '';
          $terms_cloud .= '<ul class="skills-list">';
          $skills = json_decode(str_replace('&quot;', '"', $item['skills']), true)['terms'];
          foreach($skills as $skill) {
            $terms_cloud .= '<li>'.$this->terms[$skill].'</li>';
          }
          $terms_cloud .= '</ul>';
          return $terms_cloud;
        }
        break;
      case 'job_title':
        return $item['post_title'];
      case 'stage':
        return $item['title'];
      case 'action':
        $jobseeker_email_url .= '&email_ids[0]=' . $item['email'];
        return sprintf(__('<a class="fa" href="%s"><span class="dashicons dashicons-visibility"></span></a> | <a class="fa" href="%s"><span class="dashicons dashicons-email-alt"></span></a>'), $jobseeker_preview_url, $jobseeker_email_url);
      case 'project':
        return $this->projects[$item['project_id']];
      case 'status':
        return $this->statuses[$item['status']];
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
