<div class="wrap erp-candidate-detail" xmlns:v-on="http://www.w3.org/1999/xhtml" xmlns:v-el="http://www.w3.org/1999/xhtml">
    <h1><?php _e('Reports','wp-erp-rec'); ?></h1>
    <?php $jobid = (isset($_GET['jobid']) ? $_GET['jobid'] : 0); ?>
    <?php $total_applicants = erp_rec_applicant_counter($jobid); ?>
    <form method="post">
        <div id="dashboard-widgets-wrap" class="erp-grid-container">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;min-height:500px;">
                            <div id="left-fixed-menu">
                                <ul>
                                    <li><span id="opening-report"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=opening_reports'); ?>"><?php _e('Opening Report', 'wp-erp-rec'); ?></a></span></li>
                                    <li><span id="candidate-report" class="left-menu-current-item"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=candidate_reports'); ?>"><?php _e('Candidate Report', 'wp-erp-rec'); ?></a></span></li>
                                    <li><span id="csv-report"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=csv_reports'); ?>"><?php _e('CSV Report', 'wp-erp-rec'); ?></a></span></li>
                                </ul>
                            </div>

                            <div id="candidate-reports-wrapper" class="single-information-container">
                                <div id="candidate-overview-zone">
                                    <span class="spinner"></span>
                                    <h1 style="border-bottom:1px solid #e1e1e1;padding-bottom:15px;margin-bottom:15px;">
                                        <i class="fa fa-bar-chart-o">&nbsp;</i><?php _e('Candidate Report', 'wp-erp-rec'); ?>
                                    </h1>

                                    <div class="candidate-job-list">
                                        <?php
                                        $query = new \WP_Query(array(
                                                'post_type'      => 'erp_hr_recruitment',
                                                'posts_per_page' => -1,
                                                'order'          => 'ASC',
                                                'orderby'        => 'title'
                                            )
                                        );

                                        if ( $query->have_posts() ): ?>
                                            <select id="job-title" v-model="jobidSelection">
                                                <option value="0"><?php _e('All', 'wp-erp-rec'); ?></option>
                                                <?php while ($query->have_posts()) : $query->the_post(); ?>
                                                    <option value="<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></option>
                                                <?php endwhile; wp_reset_postdata(); ?>
                                            </select>
                                        <?php endif; ?>
                                        <button class="button" v-on:click.prevent="generateCandidateReport"><?php _e('Generate', 'wp-erp-rec'); ?></button>
                                    </div>

                                    <div id="report-csv-link">
                                        <input type="hidden" id="hidden-base-url" value="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=candidate_reports&func=candidate-report-csv'); ?>">
                                        <a id="csv-dl-link" class="necessary-link dl-link" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=candidate-report-csv'; ?>">
                                            <i class="fa fa-download">&nbsp;</i><?php _e('Export to CSV', 'wp-erp-rec'); ?>
                                        </a>
                                    </div>

                                    <table class="wp-list-table widefat fixed striped table-rec-reports">
                                        <thead>
                                        <tr>
                                            <th><?php _e('Candidate Name', 'wp-erp-rec'); ?></th>
                                            <th><?php _e('Email', 'wp-erp-rec'); ?></th>
                                            <th><?php _e('Phone', 'wp-erp-rec'); ?></th>
                                            <th><?php _e('Apply date', 'wp-erp-rec'); ?></th>
                                            <th><?php _e('Current candidate status', 'wp-erp-rec'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="not-loaded">
                                        <tr v-for="rdata in candidateReportData">
                                            <td>{{rdata.first_name}}</td>
                                            <td class="align-center">{{rdata.email}}</td>
                                            <td>{{rdata.phone}}</td>
                                            <td>{{rdata.apply_date}}</td>
                                            <td>{{rdata.current_stage}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!-- inside -->
                    </div><!-- postbox -->
                </div><!-- col-6 -->
            </div><!-- row -->
        </div><!-- erp-grid-container -->
    </form>
</div>
