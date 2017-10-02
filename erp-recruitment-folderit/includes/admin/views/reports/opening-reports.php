<div class="wrap erp-candidate-detail" xmlns:v-on="http://www.w3.org/1999/xhtml">
    <h1><?php _e('Reports','wp-erp-rec');?></h1>
    <form method="post">
        <div id="dashboard-widgets-wrap" class="erp-grid-container">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;min-height:500px;">
                            <div id="left-fixed-menu">
                                <ul>
                                    <li><span id="opening-report" class="left-menu-current-item"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=opening_reports');?>"><?php _e('Opening Report', 'wp-erp-rec');?></a></span></li>
                                    <li><span id="candidate-report"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=candidate_reports');?>"><?php _e('Candidate Report', 'wp-erp-rec');?></a></span></li>
                                    <li><span id="csv-report"><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=csv_reports');?>"><?php _e('CSV Report', 'wp-erp-rec');?></a></span></li>
                                </ul>
                            </div>

                            <div id="reports-wrapper" class="single-information-container">
                                <div id="candidate-overview-zone">
                                    <h1 style="border-bottom:1px solid #e1e1e1;padding-bottom:15px;margin-bottom:15px;">
                                        <i class="fa fa-bar-chart-o">&nbsp;</i><?php _e('Opening Report', 'wp-erp-rec');?>
                                    </h1>
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
                                            <option value="0"><?php _e('All', 'wp-erp-rec');?></option>
                                            <?php while ($query->have_posts()) : $query->the_post(); ?>
                                                <option value="<?php echo get_the_ID();?>"><?php echo get_the_title();?></option>
                                            <?php endwhile; wp_reset_postdata();?>
                                        </select>
                                    <?php endif;?>
                                    <button class="button" v-on:click.prevent="generateReport"><?php _e('Generate', 'wp-erp-rec');?></button>
                                    <span class="spinner"></span>

                                    <div id="report-csv-link">
                                        <input type="hidden" id="hidden-base-url" value="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=opening_reports&func=opening-report-csv');?>">
                                        <a id="csv-dl-link" class="necessary-link dl-link" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=opening-report-csv';?>">
                                            <i class="fa fa-download">&nbsp;</i><?php _e('Export to CSV', 'wp-erp-rec');?>
                                        </a>
                                    </div>

                                    <table class="wp-list-table widefat fixed striped table-rec-reports">
                                        <thead>
                                            <tr>
                                                <th rowspan="2"><?php _e('Opening', 'wp-erp-rec');?></th>
                                                <th rowspan="2"><?php _e('Created' ,'wp-erp-rec');?></th>
                                                <th style="width:100px;" rowspan="2"><?php _e('# Candidates Added', 'wp-erp-rec');?></th>
                                                <th colspan="4"><?php _e('How are the candidates distributed', 'wp-erp-rec');?></th>
                                            </tr>
                                            <tr>
                                                <th><?php _e('In Process', 'wp-erp-rec');?></th>
                                                <th><?php _e('Archived', 'wp-erp-rec');?></th>
                                                <th><?php _e('Unscreened', 'wp-erp-rec');?></th>
                                                <th><?php _e('Other', 'wp-erp-rec');?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="not-loaded">
                                            <tr v-for="rdata in openingReportData">
                                                <td>{{rdata.opening}}</td>
                                                <td class="align-center">{{rdata.create_date}}</td>
                                                <td class="align-right">{{rdata.total_candidate}}</td>
                                                <td class="align-right">{{rdata.in_process}}</td>
                                                <td class="align-right">{{rdata.archive}}</td>
                                                <td class="align-right">{{rdata.unscreen}}</td>
                                                <td class="align-right">{{rdata.other}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="align-right"><?php _e('Total :');?></td>
                                                <td class="align-right">{{totalCandidate}}</td>
                                                <td class="align-right">{{totalInProcess}}</td>
                                                <td class="align-right">{{totalArchive}}</td>
                                                <td class="align-right">{{totalUnscreen}}</td>
                                                <td class="align-right">{{totalOther}}</td>
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
