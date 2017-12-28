<div class="wrap erp-candidate-detail">
  <h1>
    <?php _e('Candidates', 'wp-erp-rec');?>
    <a id="add_candidate" class="page-title-action" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=add_candidate');?>">
      <?php _e('Add Candidate','wp-erp-rec');?>
    </a>
  </h1>
  <?php $jobid = (isset($_GET['jobid']) ? $_GET['jobid'] : 0);?>
  <?php $filter_stage = (isset($_GET['filter_stage']) ? $_GET['filter_stage'] : 0);?>
  <?php $total_applicants = erp_rec_applicant_counter($jobid);?>
  <?php
  $all_candidate_link = ($jobid == 0) ? admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list') : admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list&jobid='.$jobid);
  $job_title = ($jobid == 0) ? '' : erp_rec_get_position($jobid)->post_title;
  $filter_url = ($filter_stage == 0) ? '' : '&filter_stage='.$filter_stage;
  ?>
  <div id="dashboard-widgets-wrap">
    <div class="row">
      <div class="col-6">
        <div class="postbox">
          <div class="inside" style="margin-bottom:0;margin-top:0;overflow-y:hidden;padding-bottom:0;padding-left:0;">
            <div class="container-fluid">
              <div class="row">
                <div class="col-lg-12 hidden">
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
                        <?php $selected_class = (isset($_GET['statusslug']) && $_GET['statusslug'] == 'shortlisted') ? 'left-menu-current-item' : '' ;?>
                        <span id="menu-shortlisted" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&statusslug=shortlisted';?>"><?php _e('Short-Listed', 'wp-erp-rec');?></a></span>
                      </li>
                      <li>
                        <?php $selected_class = (isset($_GET['statusslug']) && $_GET['statusslug'] == 'hired') ? 'left-menu-current-item' : '' ;?>
                        <span id="menu-hired" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&statusslug=hired';?>"><?php _e('Hired', 'wp-erp-rec');?></a></span></li>
                      <li>
                        <?php $selected_class = (isset($_GET['statusslug']) && $_GET['statusslug'] == 'rejected') ? 'left-menu-current-item' : '' ;?>
                        <span id="menu-rejected" class="<?php echo $selected_class;?>"><a href="<?php echo $all_candidate_link.'&statusslug=rejected';?>"><?php _e('Rejected', 'wp-erp-rec');?></a></span>
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="col-lg-12">
                  <div class="single-information-container">
                    <?php if ( !empty($jobid) ) : ?>
                    <h4><?php echo __('Filtering by position: ', 'wp-erp-rec') . ' ' . $job_title; ?> <small><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list'.$filter_url); ?>">[<?php _e('Remove filter', 'wp-erp-rec'); ?>]</a></small></h4>
                    <?php endif; ?>
                    <div id="candidate-overview-zone">
                      <a type="button" class="btn btn-default btn-arrow-right" href="<?php echo $all_candidate_link; ?>">
                        <span class="icon-arrow-right"><b><?php echo $total_applicants;?></b><br/><small><?php _e(' Candidates', 'wp-erp-rec'); ?></small></span>
                      </a>
                      <?php
                      if(empty($jobid)) {
                        $stages = erp_rec_get_all_stages();
                      } else {
                        $stages = erp_rec_get_this_job_stages($jobid);
                      }
                      foreach ( $stages as $stage ) :
                      $stage_individual = $stage['stage_individual'];
                      if($jobid == 0) {
                        $joburl = '';
                        $stageid = $stage['id'];
                      } else {
                        $stageid = $stage['stageid'];
                        $joburl = '&jobid='.$jobid;
                      }
                      ?>
                      <a type="button" class="btn btn-default btn-arrow-right <?php if ($filter_stage == $stageid) echo " btn-primary active "; ?> <?php if ($stage_individual != 0) echo " btn-individual "; ?>" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=jobseeker_list'.$joburl.'&filter_stage='.$stageid);?>">
                        <span class="icon-arrow-right"><b><?php echo erp_rec_get_candidate_number_in_this_stages($jobid, $stageid);?></b><br/><small><?php echo $stage['title'];?></small></span>
                      </a>
                      <?php endforeach;?>
                    </div>
                    <div id="candidate-tags-zone">
                      <form method="get">
                        <?php
                        foreach($_GET as $name => $value) {
                          if (strpos($name, 'skills_') !== 0 && $name !== 'paged') {
                            $name = htmlspecialchars($name);
                            $value = htmlspecialchars($value);
                            echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
                          }
                        }
                        ?>
                        <?php $terms = erp_rec_get_terms(); ?>
                        <div data-toggle="buttons">
                          <?php foreach($terms as $term_slug => $term_name) : ?>
                          <label class="btn btn-toggle <?php echo (isset($_GET["skills_".$term_slug]) === true)?"active":"" ?>" style="margin-bottom:6px;">
                            <input type="checkbox" autocomplete="off" name="skills_<?php echo $term_slug; ?>" <?php echo (isset($_GET["skills_".$term_slug]) === true)?"checked":"" ?>><?php echo $term_name; ?>
                          </label>
                          <?php endforeach;?>
                        </div>

                        <input type="submit" class="button" value="<?php _e('Filter Terms', 'wp-erp-rec'); ?>">
                        <button id="candidate-tags-clear" class="button"><?php _e('Clear Terms', 'wp-erp-rec'); ?></button>
                        <script>
                          $('#candidate-tags-clear').click(function(e) {
                            $('#candidate-tags-zone input:checked').removeAttr('checked').trigger('click');
                            e.preventDefault();
                          });
                        </script>
                      </form>
                    </div>
                    <form method="post">
                      <input type="hidden" name="page" value="jobseeker_list">
                      <?php
                      $customer_table = new \WeDevs\ERP\ERP_Recruitment\Jobseeker_List_Table();
                      $customer_table->prepare_items();
                      $customer_table->search_box(__('Search', 'wp-erp-rec'), 'erp-recruitment-search');
                      $customer_table->views();
                      $customer_table->display();
                      ?>
                    </form>
                  </div>
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
