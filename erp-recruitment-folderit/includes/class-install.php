<?php
/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP Recruitment
 */

/**
 * Installer Class
 *
 * @package ERP
 */
class WeDevs_ERP_Recruitment_Installer {

  /**
     * Binding all events
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function __construct() {
    register_activation_hook( WPERP_REC_FILE, array( $this, 'activate_rec_now' ) );
    register_deactivation_hook( WPERP_REC_FILE, array( $this, 'deactivate' ) );
  }

  /**
     * Placeholder for activation function
     * Nothing being called here yet.
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function activate_rec_now() {

    $this->create_rec_tables();

    // create jobs page and put shortcode
    $jobs_page = array(
      'post_title' => 'Job List Page',
      'post_content' => '[erp-job-list]',
      'post_status' => 'publish',
      'post_type' => 'page'
    );

    $post_id = wp_insert_post($jobs_page);
  }

  /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     *
     * @since 1.0.0
     *
     * @return void
     */
  public function deactivate() {

  }

  /**
     * Create necessary table for ERP & HRM
     *
     * @since 1.0.0
     *
     * @return  void
     */
  public function create_rec_tables() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
      if ( !empty( $wpdb->charset ) ) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
      }

      if ( !empty( $wpdb->collate ) ) {
        $collate .= " COLLATE $wpdb->collate";
      }
    }

    $current_user_id = get_current_user_id();

    $table_schema = [
      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `job_id` int(11) unsigned DEFAULT NULL,
                 `applicant_id` int(11) unsigned DEFAULT NULL,
                 `stage` int(11) DEFAULT 1,
                 `apply_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 `exam_detail` text,
                 `added_by` int(11) unsigned DEFAULT 0,
                 `status` tinyint unsigned DEFAULT 0,
                 `project_id` int(11) unsigned DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `job_id` (`job_id`),
                 KEY `applicant_id` (`applicant_id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_comment` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `comment` text,
                 `user_id` int(11) unsigned DEFAULT 0,
                 `comment_date` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_rating` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `rating` int(11) unsigned DEFAULT 0,
                 `user_id` int(11) unsigned DEFAULT 0,
                 `rating_date` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_interview_types` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `type_detail` varchar(255) DEFAULT NULL,
                 `type_identifier` varchar(255) DEFAULT NULL,
                 `type_order` int(11) DEFAULT NULL,
                 PRIMARY KEY (`id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_interview` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `interview_type_id` int(11) unsigned DEFAULT NULL,
                 `interview_detail` varchar(255) DEFAULT NULL,
                 `feedback_comment` text,
                 `feedback_english_level` varchar(255) DEFAULT NULL,
                 `feedback_english_conversation` varchar(255) DEFAULT NULL,
                 `interview_tech` varchar(255) DEFAULT NULL,
                 `interview_internal_type_id` int(11) unsigned DEFAULT NULL,
                 `start_date_time` datetime DEFAULT NULL,
                 `duration_minutes` varchar(15) DEFAULT NULL,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_interviewer_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `interview_id` int(11) unsigned DEFAULT NULL,
                 `interviewer_id` int(11) unsigned DEFAULT NULL,
                 PRIMARY KEY (`id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_todo` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `application_id` int(11) unsigned DEFAULT NULL,
                 `title` varchar(255) DEFAULT NULL,
                 `deadline_date` datetime DEFAULT NULL,
                 `status` boolean NOT NULL DEFAULT 0,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 KEY `application_id` (`application_id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_todo_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `todo_id` int(11) unsigned DEFAULT NULL,
                 `assigned_user_id` int(11) unsigned DEFAULT NULL,
                 PRIMARY KEY (`id`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_job_stage_relation` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `jobid` int(11) DEFAULT NULL,
                 `stageid` int(11) DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 KEY `jobid` (`jobid`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_stage` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `title` varchar(50) DEFAULT NULL,
                 `stage_order` int(11) DEFAULT NULL,				 
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`),
                 UNIQUE (`title`)
             ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_application_status` (
                 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                 `code` varchar(50) DEFAULT NULL,
                 `title` varchar(50) DEFAULT NULL,
                 `description` text DEFAULT NULL,
                 `status_order` int(11) DEFAULT 0,
                 `internal` tinyint unsigned DEFAULT 0,
                 `created_by` int(11) DEFAULT NULL,
                 `created_at` datetime DEFAULT NULL,
                 `updated_at` timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                 PRIMARY KEY (`id`)
             ) $collate;",

      "INSERT INTO `{$wpdb->prefix}erp_application_stage` (`id`, `title`, `created_by`, `created_at`)
             VALUES (NULL, 'Screening', $current_user_id, NOW()),
                    (NULL, 'Phone Interview', $current_user_id, NOW()),
                    (NULL, 'Face to Face Interview', $current_user_id, NOW()),
                    (NULL, 'Make an Offer', $current_user_id, NOW())
                    ON DUPLICATE KEY UPDATE id=id"
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ( $table_schema as $table ) {
      dbDelta( $table );
    }
  }
}

new WeDevs_ERP_Recruitment_Installer();
