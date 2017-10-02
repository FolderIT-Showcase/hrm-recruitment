<?php
global $post;

$query = new \WP_Query( array (
    'post_type'      => $this->post_type,
    'posts_per_page' => -1,
    'order'          => 'DESC',
    'orderby'        => 'post_date',
    'meta_query' => array(
        'relation' => 'OR',
        array (
            'key'     => '_hide_job_list',
            'value'   => '0',
            'compare' => '='
        ),
        array (
            'key'     => '_hide_job_list',
            'compare' => 'NOT EXISTS'
        )
    )
) );

if ( $query->have_posts() ): ?>
<?php $departments = erp_hr_get_departments( array( 'no_object' => true ) ); ?>
<div id="job-ul-list-header">
    <h2 id="job-ul-list-header-label"><?php _e( 'Job List', 'wp-erp-rec' ); ?></h2>
	<h3 id="job-ul-list-subheader-label"><?php _e( 'Select a position that fits your profile', 'wp-erp-rec' ); ?></h3>
</div>
<div id="front-job-list">
    <?php while ($query->have_posts()) : $query->the_post(); ?>
    <?php
    $dep             = get_post_meta( get_the_ID(), '_department', true ) ? get_post_meta( get_the_ID(), '_department', true ) : 0;
    $employment_type = get_post_meta( get_the_ID(), '_employment_type', true ) ? get_post_meta( get_the_ID(), '_employment_type', true ) : '-';
    $min_exp         = get_post_meta( get_the_ID(), '_minimum_experience', true ) ? get_post_meta( get_the_ID(), '_minimum_experience', true ) : '-';
    $expire_date     = get_post_meta( get_the_ID(), '_expire_date', true ) ? get_post_meta( get_the_ID(), '_expire_date', true ) : 'N/A';
    $location        = get_post_meta( get_the_ID(), '_location', true ) ? get_post_meta( get_the_ID(), '_location', true ) : '';
    $permanent_job   = get_post_meta( get_the_ID(), '_permanent_job', true ) ? get_post_meta( get_the_ID(), '_permanent_job', true ) : '';
    $date            = date('Y-m-d');
    $date            = date('Y-m-d', strtotime('+30 days', strtotime($date)));
    $e_date          = date_create( $expire_date == 'N/A' ? $date : $expire_date );
    $current_date    = date_create( date( 'Y-m-d' ) );
    $diff            = date_diff( $current_date, $e_date );

    $exp_date        = date( 'Y-m-d', strtotime( get_post_meta( get_the_ID(), '_expire_date', true ) ) );
    $dname           = '';
    $employment_types  = erp_hr_get_employee_types();

    if ( $dep ) {
        $department_name = new \WeDevs\ERP\HRM\Department( intval( $dep ) );
        $dname = ( $department_name->title != "" ) ? $department_name->title : '';
    }
    ?>
	
    <?php if ( date( 'Y-m-d' ) <= $exp_date || $exp_date == '1970-01-01' || $permanent_job == true ) : ?>
    <div class="erp-rec-job-list <?php echo $dname; ?>">
        <a href="<?php the_permalink(); ?>">
            <div class="jparts">
                <span class="job-title"><?php the_title(); ?></span>
            </div>
            <div class="jparts">
                <span class="location"><i class="fa fa-map-marker"></i> <?php echo $location; ?></span>
                <span class="employment-type"><i class="fa fa-briefcase"></i> <?php echo $employment_types[$employment_type]; ?></span>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <?php endwhile; ?>
</div>

<?php
endif;
wp_reset_postdata();
