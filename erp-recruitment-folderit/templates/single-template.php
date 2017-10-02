<?php
global $post;

$job_id            = get_the_ID();
$employment_types  = erp_hr_get_employee_types();
$employment_type   = get_post_meta( $job_id, '_employment_type', true);
$expire_date       = get_post_meta( $job_id, '_expire_date', true);
$expire_timestamp  = !empty( $expire_date ) ? strtotime( $expire_date ) : false;
$location          = get_post_meta( $job_id, '_location', true );
$number_of_vacancy = get_post_meta( $job_id, '_vacancy', true);
$vacancy           = ( $number_of_vacancy != '' ) ? $number_of_vacancy : 'N/A';
$min_experience    = get_post_meta( $job_id, '_minimum_experience', true);
$permanent_job     = get_post_meta( $job_id, '_permanent_job', true);
?>

<div class="erp-recruitment-single" itemscope itemtype="http://schema.org/JobPosting">
    <meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />

    <?php do_action( 'erp_rec_single_job_listing_meta_before' ); ?>

    <ul class="erp-recruitment-meta">
        <li class="employment-type <?php echo sanitize_title( $employment_type ); ?>" itemprop="employmentType">
            <?php echo isset( $employment_types[ $employment_type ] ) ? $employment_types[ $employment_type ] : ''; ?>

            <?php if ( get_post_meta( $job_id, '_remote_job', true) == 1 ) : ?>
                <small class="employment-remote"><?php _e( '(allows remote)','wp-erp-rec' ); ?></small>
            <?php endif;?>
        </li>

        <?php if ( !empty( $location ) ) { ?>
        <li class="erp-recruitment-location" itemprop="jobLocation">
            <span class="rec-icon-location"></span>
            <?php echo $location; ?>
        </li>
        <?php } ?>

		<!--
        <li class="erp-recruitment-vacancy">
            <span class="rec-icon-users"></span>
            <?php printf( __('No. of Vacancies: %s', 'wp-erp-rec'), $number_of_vacancy ); ?>
        </li>
		-->

		<!--
        <?php if ( $min_experience != '' ) :?>
            <li>
                <span class="rec-icon-briefcase"></span> <?php printf( __( 'Experience: %s', 'wp-erp-rec'), $min_experience ); ?>
            </li>
        <?php endif;?>
		-->

		<!--
        <li class="date-posted" itemprop="datePosted">
            <span class="rec-icon-calendar"></span>
            <date><?php printf( __( 'Posted %s ago', 'wp-erp-rec' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date>
        </li>
		-->
    </ul>

    <?php do_action( 'erp_rec_single_job_listing_meta_after' ); ?>

    <div class="erp-recruitment-description" itemprop="description">
        <?php echo apply_filters( 'erp_rec_job_description', get_the_content() ); ?>

        <?php
        if ( $expire_timestamp ) {
            //printf( '<p class="job-application-deadline"> ' . __( '<strong><em>Application Deadline</em></strong>: %s', 'wp-erp-rec' ) . '</p>', date_i18n( get_option( 'date_format' ), $expire_timestamp ) );
        }
        ?>
    </div>

    <?php if ( ($expire_timestamp && $expire_timestamp > time()) || $permanent_job == true ) { ?>
        <div class="erp-recruitment-application">
            <input type="button" class="application_button button" id="btn_apply_job" name="btn_apply_job" value="<?php echo esc_attr( 'Apply for this position', 'wp-erp-rec'); ?>"/>

            <div class="erp-recruitment-from-wrapper" id="job_seeker_table_wrapper">
                <?php include __DIR__ . '/job-application-form.php'; ?>
            </div>

            <div id="jobseeker_insertion_message"></div>
        </div>
    <?php } ?>
</div>

