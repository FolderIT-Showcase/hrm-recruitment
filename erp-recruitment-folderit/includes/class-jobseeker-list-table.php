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
            'ajax'     => false
        ));
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

        $selected_status = (isset($_REQUEST['filter_status'])) ? $_REQUEST['filter_status'] : -1;
        $jobid = (isset($_REQUEST['jobid'])) ? $_REQUEST['jobid'] : 0;

?>
  <div class="alignleft actions">

    <label class="screen-reader-text" for="new_role"><?php _e('Filter by Status', 'wp-erp-rec') ?></label>
    <select name="filter_status" id="filter_status_select">
        <option value="-1"><?php _e('- Select All -', 'wp-erp-rec'); ?></option>
        <?php echo erp_hr_get_status_dropdown($selected_status); ?>
    </select>
    <input type="hidden" name="jobid" value="<?php echo $jobid;?>">

    <?php
        submit_button(__('Filter'), 'button', 'filter_status_button', false);

        echo '</div>';

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
            'apply_date'        => __('Date', 'wp-erp-rec'),
            'full_name'         => __('Name', 'wp-erp-rec'),
            'avg_rating'        => __('Rating', 'wp-erp-rec'),
            'job_title'         => __('Applied Job', 'wp-erp-rec'),
            'stage'             => __('Stage', 'wp-erp-rec'),
            'interview_rrhh'    => __('RRHH Interview', 'wp-erp-rec'),
            'interview_tech'    => __('Technical Interview', 'wp-erp-rec'),
            'interview_english' => __('English Interview', 'wp-erp-rec'),
            'status'            => __('Status', 'wp-erp-rec'),
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

        $query = "SELECT app_iv.id, app_iv.feedback_comment, app_iv.feedback_english_level, app_iv.feedback_english_conversation, types.type_detail, types.type_identifier
                        FROM {$wpdb->prefix}erp_application_interview as app_iv
                        LEFT JOIN {$wpdb->prefix}erp_application_interview_types as types
                        ON app_iv.interview_internal_type_id = types.id
                        WHERE app_iv.application_id=%d";
        $idata = $wpdb->get_results( $wpdb->prepare( $query, $item['applicationid'] ), ARRAY_A );
      
        $status = erp_rec_get_hiring_status();

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
                $email_action_url = 'mailto:' . $item['email'];
                return sprintf(__('<a class="fa" href="%s"><span class="dashicons dashicons-visibility"></span></a> | <a class="fa" href="%s"><span class="dashicons dashicons-email-alt"></span></a><div>%s</div>'), $jobseeker_preview_url, $email_action_url, erp_people_get_meta($item['id'], 'ip', true ) );
            case 'status':
                return str_replace('_',' ',$status[$item['status']]);
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
            'apply_date' => array( 'apply_date', true ),
            'full_name'  => array( 'full_name', true ),
            'avg_rating' => array( 'avg_rating', true ),
            'stage'      => array( 'title', true ),
            'job_title'  => array( 'post_title', true ),
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

        if ( isset( $_REQUEST['filter_status_button'] ) ) {
            return 'filter_status';
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
        $this->_column_headers = array($columns, $hidden, $sortable);

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

        if ( isset($_REQUEST['filter_status']) && $_REQUEST['filter_status'] ) {
            $args['status'] = $_REQUEST['filter_status'];
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

        $this->items = erp_rec_get_applicants_information($args);
        $total_rows = erp_rec_total_applicant_counter($args);

        $this->set_pagination_args(array(
            'total_items' => $total_rows,
            'per_page'    => $per_page
        ));
    }
}
