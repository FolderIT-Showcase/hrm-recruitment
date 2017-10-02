<div class="wrap erp-candidate-detail">
    <h1>
        <?php _e('Candidates', 'wp-erp-rec');?>
        <a id="add_candidate" class="page-title-action" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=add_candidate');?>"><?php _e('Add Candidate','wp-erp-rec');?></a>
    </h1>
    <?php $jobid = (isset($_GET['jobid']) ? $_GET['jobid'] : 0);?>
    <?php $total_applicants = erp_rec_applicant_counter($jobid);?>
    <?php
        $all_candidate_link = ($jobid == 0) ? admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list') : admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&jobid='.$jobid);
    ?>
    <form method="post">
        <div id="dashboard-widgets-wrap">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;">
                            <div id="left-fixed-menu">
                                <ul>
                                    <li>
                                        <?php
                                            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                                            $actual_link = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                                            $selected_class = ($actual_link == $all_candidate_link) ? 'left-menu-current-item' : '' ;
                                        ?>
                                        <span id="menu-overview" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link;?>"><?php _e('Overview', 'wp-erp-rec');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_added_by_me']) && $_GET['filter_added_by_me'] == 'added_by_me') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-added-by-me" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_added_by_me=added_by_me';?>"><?php _e('Added by me', 'wp-erp-rec');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'shortlisted') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-shortlisted" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=shortlisted';?>"><?php _e('Short-Listed', 'wp-erp-rec');?></a></span>
                                    </li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'hired') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-hired" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=hired';?>"><?php _e('Hired', 'wp-erp-rec');?></a></span></li>
                                    <li>
                                        <?php $selected_class = (isset($_GET['filter_status']) && $_GET['filter_status'] == 'rejected') ? 'left-menu-current-item' : '' ;?>
                                        <span id="menu-rejected" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&filter_status=rejected';?>"><?php _e('Rejected', 'wp-erp-rec');?></a></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="single-information-container">
                                <div id="candidate-overview-zone">

                                    <div class="filter-box-wrapper">
                                        <a class="filter-box" href="<?php echo $all_candidate_link;?>">
                                            <span class="top-zone-number"><?php echo $total_applicants;?></span>
                                            <span class="footer-zone-text"><?php _e(' Candidates', 'wp-erp-rec'); ?></span>
                                        </a>
                                    </div>

                                    <?php if ( $jobid == 0 ) : ?> <!-- get stage for this job if job id is zero then show all -->
                                        <?php $stages = erp_rec_get_all_stages();
                                        foreach ( $stages as $stage ) : ?>
                                            <div class="filter-box-wrapper">
                                                <a class="filter-box" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&filter_stage='.$stage['id']);?>">
                                                    <span class="top-zone-number">
                                                        <?php echo erp_rec_get_candidate_number_in_this_stages($jobid, $stage['id']);?>
                                                    </span>
                                                    <span class="footer-zone-text"><?php echo $stage['title'];?></span>
                                                </a>
                                            </div>
                                        <?php endforeach;?>
                                    <?php else : ?>
                                    <?php $stages = erp_rec_get_this_job_stages($jobid);
                                        foreach ( $stages as $stage ) : ?>
                                            <div class="filter-box-wrapper">
                                                <a class="filter-box" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&jobid='.$jobid.'&filter_stage='.$stage['stageid']);?>">
                                                    <span class="top-zone-number">
                                                        <?php echo erp_rec_get_candidate_number_in_this_stages($jobid, $stage['stageid']);?>
                                                    </span>
                                                    <span class="footer-zone-text"><?php echo $stage['title'];?></span>
                                                </a>
                                            </div>
                                    <?php endforeach;?>
                                    <?php endif;?>
                                </div>
                                <input type="hidden" name="page" value="jobseeker_list">
                                <?php
                                    $customer_table = new \WeDevs\ERP\ERP_Recruitment\Jobseeker_List_Table();
                                    $customer_table->prepare_items();
                                    $customer_table->search_box(__('Search', 'wp-erp-rec'), 'erp-recruitment-search');
                                    $customer_table->views();
                                    $customer_table->display();
                                ?>
                            </div>
                        </div><!-- inside -->
                    </div><!-- postbox -->
                </div><!-- col-6 -->
            </div><!-- row -->
        </div><!-- erp-grid-container -->
    </form>
</div>
