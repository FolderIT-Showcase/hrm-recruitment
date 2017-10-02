<div class="wrap uniq-wrap" id="uniq-wrap" xmlns:v-on="http://www.w3.org/1999/xhtml">
    <?php echo erp_rec_opening_admin_progressbar( 'hiring_workflow' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Hiring stage', 'wp-erp-rec' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
            <form id="hiring-workflow-form" action="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=job_information' ); ?>" method="post" id="unique-form-customize">
                <div id="openingform_stage_handler" class="openingform_input_wrapper">
                    <p class="info-message">
                        <?php _e('Stages below reflect the steps in your hiring process. Coordinator of a stage typically schedules interviews, collects evaluation from interviewers and communicates with the candidate.', 'wp-erp-rec');?>
                    </p>
                    <button style="margin-bottom: 10px;" class="button alignright" v-on:click.prevent="createStage">
                        <i class="fa fa-plus"></i>&nbsp;<?php _e('Add Stage','wp-erp-rec');?>
                    </button>
                    <span class="spinner"></span>
                    <div id="stage-validation-message"></div>
                    <div id="openingform_sortit">
                        <?php $get_stage = erp_rec_get_stages($postid);?>
                        <?php foreach ( $get_stage as $st ) : ?>
                            <div class="stage-list">
                                <?php //if ( array_key_exists( 'stage_selected', $st ) && is_null( $st['stage_selected'] ) ) { ?>
                                <?php if ( $st['selected'] == false ) { ?>
                                    <label><input type="checkbox" name="stage_name[]" value="<?php echo $st['sid'];?>"><?php echo $st['title'];?></label>
                                <?php } else { ?>
                                    <label><input type="checkbox" name="stage_name[]" value="<?php echo $st['sid'];?>" checked="checked"><?php echo $st['title'];?></label>
                                <?php }?>
                            </div>
                        <?php endforeach;?>
                    </div>
                </div>
                <input type="hidden" id="postid" name="postid" value="<?php echo $postid;?>">
                <input type="hidden" id="postid" name="hidden_hiring_workflow" value="hiring_workflow">
                <?php wp_nonce_field( 'hiring_workflow' ); ?>
                <br style="clear: both">
                <a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'wp-erp-rec');?></a>
                <input type="submit" id="hiring_workflow" name="hiring_workflow" class="button-primary button button-hero alignright" value="<?php _e( 'Next &rarr;', 'wp-erp-rec');?>">
            </form>
        </div>
    </div>
</div>