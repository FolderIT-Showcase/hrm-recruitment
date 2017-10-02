<div class="wrap erp-candidate-detail">
    <h1><?php _e( 'Reports', 'wp-erp-rec' ); ?></h1><?php $jobid = ( isset( $_GET['jobid'] ) ? $_GET['jobid'] : 0 ); ?>
    <?php $total_applicants = erp_rec_applicant_counter( $jobid ); ?>
    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;min-height: 500px;">
                        <div id="left-fixed-menu">
                            <ul>
                                <li>
                                    <span id="opening-report"><a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=opening_reports' ); ?>"><?php _e( 'Opening Report', 'wp-erp-rec' ); ?></a></span>
                                </li>
                                <li>
                                    <span id="candidate-report"><a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=candidate_reports' ); ?>"><?php _e( 'Candidate Report', 'wp-erp-rec' ); ?></a></span>
                                </li>
                                <li>
                                    <span id="csv-report" class="left-menu-current-item"><a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=csv_reports' ); ?>"><?php _e( 'CSV Report', 'wp-erp-rec' ); ?></a></span>
                                </li>
                            </ul>
                        </div>

                        <div class="single-information-container">
                            <div id="candidate-overview-zone">
                                <h1 style="border-bottom:1px solid #e1e1e1;padding-bottom:15px;margin-bottom:15px;">
                                    <i class="fa fa-file-excel-o">&nbsp;</i><?php _e( 'CSV Report', 'wp-erp-rec' ); ?>
                                </h1>
                            </div>

                            <div class="candidate-job-list">
                                <form method="post" action="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=csv_reports');?>">
                                    <select id="job-title" name="report_type">
                                        <option value="opening_report"><?php _e('Opening report','wp-erp-rec'); ?></option>
                                        <option value="candidate_report"><?php _e('Candidate report','wp-erp-rec'); ?></option>
                                    </select>
                                    <?php erp_html_form_input( array(
                                        'label'       => __( '', 'wp-erp-rec' ),
                                        'name'        => 'from_date',
                                        'id'          => 'from_date',
                                        'placeholder' => 'From',
                                        'value'       => '',
                                        'class'       => 'erp-date-field'
                                    ) ); ?>
                                    <?php erp_html_form_input( array(
                                        'label'       => __( '', 'wp-erp-rec' ),
                                        'name'        => 'to_date',
                                        'id'          => 'to_date',
                                        'placeholder' => 'To',
                                        'value'       => '',
                                        'class'       => 'erp-date-field'
                                    ) ); ?>
                                    <input type="hidden" name="func" value="send-email-with-csv-report">
                                    <input type="submit" class="button button-default" value="<?php _e( 'Generate', 'wp-erp-rec' ); ?>">
                                </form>
                                <div id="email-notification-status">
                                    <?php if( isset($_REQUEST['csv_create']) && $_REQUEST['csv_create'] == '1' ) : ?>
                                        <p class="info-message" style="margin-top: 15px;">
                                            <?php
                                                $author_obj = get_user_by('ID', get_current_user_id());
                                                _e('It will be sent to <strong>'.$author_obj->user_email.'</strong> shortly.<br>','wp-erp-rec');
                                                _e('Please Check your email in some time.','wp-erp-rec');
                                            ?>
                                        </p>
                                    <?php elseif ( isset($_REQUEST['csv_create']) && $_REQUEST['csv_create'] == '0' ) : ?>
                                        <p class="info-message" style="margin-top: 15px;">
                                            <?php _e('Please Check your folder permission, CSV report not created.','wp-erp-rec');?>
                                        </p>
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- inside -->
                </div>
                <!-- postbox -->
            </div>
            <!-- col-6 -->
        </div>
        <!-- row -->
    </div>
    <!-- erp-grid-container -->
</div>