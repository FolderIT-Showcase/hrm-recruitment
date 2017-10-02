<div class="wrap uniq-wrap" id="uniq-wrap">
    <?php echo erp_rec_opening_admin_progressbar( 'job_information' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div id="job-information-step" class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Job information', 'wp-erp-rec' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
<!--            <form action="--><?php //echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=candidate_basic_information' ); ?><!--" method="post" id="unique-form-condition">-->
            <form action="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=candidate_basic_information' ); ?>" method="post" id="job-information-step-form">
                <div class="openingform_input_wrapper">
                    <div class="erp-grid-container">
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e( 'Select Hiring Lead', 'wp-erp-rec' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php $employees = erp_hr_get_employees(array('no_object' => true));?>
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_hiring_lead = get_post_meta($postid, '_hiring_lead', true);?>
<!--                                    <select name="hiring_lead" id="hiring_lead" class="widefat erp-select2">-->
<!--                                        <option value="">--><?php //_e('-- Select hiring lead --','wp-erp-rec');?><!--</option>-->
<!--                                        --><?php //foreach ( $employees as $user ): ?>
<!--                                            <option value="--><?php //echo $user->user_id; ?><!--"-->
<!--                                                --><?php //if ( $get_hiring_lead == $user->user_id ): ?><!-- selected="selected"--><?php //endif; ?><!-->
<!--                                                --><?php //echo $user->display_name; ?>
<!--                                            </option>-->
<!--                                        --><?php //endforeach; ?>
<!--                                    </select>-->
                                    <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
                                    <?php erp_html_form_input(array(
                                        'label'       => __('', 'wp-erp-rec'),
                                        'name'        => 'hiring_lead[]',
                                        'value'       => '',
                                        'type'        => 'select',
                                        'id'          => 'interviewers',
                                        'custom_attr' => ['multiple' => 'multiple'],
                                        'class'       => 'select_multiple_selection erp-select2 widefat',
                                        'options'     => $employee_list,
                                        'required'    => true
                                    )); ?>
                                <?php else : ?>
                                    <select name="hiring_lead" id="hiring_lead" class="widefat erp-select2">
                                        <option value=""><?php _e('-- Select hiring lead --','wp-erp-rec');?></option>
                                        <?php foreach ( $employees as $user ): ?>
                                            <option value="<?php echo $user->user_id;?>">
                                                <?php echo $user->display_name;?>
                                            </option>
                                        <?php endforeach;?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e('Department', 'wp-erp-rec'); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php $departments = erp_hr_get_departments(array('no_object' => true));?>
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_department = get_post_meta($postid, '_department', true);?>
                                    <select name="department" class="widefat">
                                        <?php foreach ($departments as $department) { ?>
                                            <option value='<?php echo $department->id; ?>'
                                                <?php if ( $get_department == $department->id ): ?> selected="selected"<?php endif; ?>>
                                                <?php echo $department->title; ?>
                                            </option>
                                        <?php }?>
                                    </select>
                                <?php else : ?>
                                    <select name="department" class="widefat">
                                        <?php foreach ( $departments as $department ) { ?>
                                            <option value='<?php echo $department->id; ?>'>
                                                <?php echo $department->title; ?>
                                            </option>
                                        <?php }?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e('Employment Type', 'wp-erp-rec'); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php $employment_types = erp_hr_get_employee_types();?>
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_employment_type = get_post_meta($postid, '_employment_type', true);?>
                                    <select name="employment_type" class="widefat">
                                        <?php foreach ($employment_types as $key => $value) { ?>
                                            <option value='<?php echo $key; ?>'
                                                <?php if ( $get_employment_type == $key ): ?> selected="selected"<?php endif; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php }?>
                                    </select>
                                <?php else : ?>
                                    <select name="employment_type" class="widefat">
                                        <?php $employment_types = erp_hr_get_employee_types();?>
                                        <?php foreach ( $employment_types as $key => $value ) { ?>
                                            <option value='<?php echo $key; ?>'>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php }?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2"></div>
                            <div class="col-4">
                                <?php $get_remote_job = get_post_meta($postid, '_remote_job', true);?>
                                <label>
                                    <input type="checkbox" name="remote_job" <?php echo ( $get_remote_job == 1 ) ? 'checked' : ''; ?> />
                                    <?php _e('Remote working is an option for this opening', 'wp-erp-rec'); ?>
                                </label>
                            </div>
                        </div>
						<div class="row">
                            <div class="col-2"></div>
                            <div class="col-4">
                                <?php $get_hide_job_list = get_post_meta($postid, '_hide_job_list', true);?>
                                <label>
                                    <input type="checkbox" name="hide_job_list" <?php echo ( $get_hide_job_list == 1 ) ? 'checked' : ''; ?> />
                                    <?php _e('Hide job from public list', 'wp-erp-rec'); ?>
                                </label>
                            </div>
                        </div>
						<div class="row">
                            <div class="col-2"></div>
                            <div class="col-4">
                                <?php $get_permanent_job = get_post_meta($postid, '_permanent_job', true);?>
                                <label>
                                    <input type="checkbox" name="permanent_job" <?php echo ( $get_permanent_job == 1 ) ? 'checked' : ''; ?> />
                                    <?php _e('Permanent job (doesn\'t expire and has no candidate limits)', 'wp-erp-rec'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e('Minimum Experience', 'wp-erp-rec'); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php $minimum_experience = erp_rec_get_recruitment_minimum_experience();?>
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_minimum_experience = get_post_meta($postid, '_minimum_experience', true);?>
                                    <select name="minimum_experience" class="widefat">
                                        <?php foreach ($minimum_experience as $key => $value) { ?>
                                            <option value='<?php echo $key; ?>'
                                                <?php if ( $get_minimum_experience == $key ): ?> selected="selected"<?php endif; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                <?php else : ?>
                                    <select name="minimum_experience" class="widefat">
                                        <?php foreach ( $minimum_experience as $key => $value ) { ?>
                                            <option value='<?php echo $key; ?>'>
                                                <?php echo $value;?>
                                            </option>
                                        <?php }?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e( 'Submission Deadline', 'wp-erp-rec' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php
                                    $date = date('Y-m-d');
                                    $date = date('Y-m-d', strtotime('+30 days', strtotime($date)));
                                ?>
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_expire_date = get_post_meta($postid, '_expire_date', true);?>
                                    <input type="text" id="expire_date" name="expire_date" value="<?php echo ( $get_expire_date == "" ) ? $date : $get_expire_date;?>">
                                <?php else : ?>
                                    <input type="text" id="expire_date" name="expire_date" value="<?php echo $date;?>">
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e( 'Location', 'wp-erp-rec' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_location = get_post_meta($postid, '_location', true);?>
                                    <input type="text" id="glocation" name="location" value="<?php echo ( $get_location == "" ) ? '' : $get_location;?>">
                                <?php else : ?>
                                    <input type="text" id="glocation" name="location" value="">
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label><?php _e('Number of Vacancy', 'wp-erp-rec'); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-4">
                                <?php if ( $postid != 0 ) : ?>
                                    <?php $get_vacancy = get_post_meta($postid, '_vacancy', true);?>
                                    <input type="text" id="vacancy" name="vacancy" placeholder="" value="<?php echo ( $get_vacancy == "" ) ? '' : $get_vacancy;?>">
                                <?php else : ?>
                                    <input type="text" id="vacancy" name="vacancy" placeholder="" value="" maxlength="2"/>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="postid" value="<?php echo $postid; ?>">
                <input type="hidden" name="hidden_job_information" value="job_information">
                <?php wp_nonce_field( 'job_information' ); ?>
                <br style="clear: both">
                <a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=hiring_workflow&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'wp-erp-rec');?></a>
                <input type="submit" id="job_information" name="job_information" class="button-primary button button-hero alignright" value="<?php _e( 'Next &rarr;', 'wp-erp-rec');?>">
            </form>
        </div>
    </div>
</div>