<div class="wrap uniq-wrap" id="uniq-wrap">
    <?php echo erp_rec_opening_admin_progressbar( 'questionnaire_selection' ); ?>
    <?php $postid = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;?>
    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Questionnaire selection', 'wp-erp-rec' ); ?></h3>
        <div class="inside" style="overflow-y: hidden;">
            <p class="info-message">
                <?php _e('You can create question sets for your candidates. During filling the application form, candidates will have to answer your selected question sets.', 'wp-erp-rec');?>
            </p>
            <form action="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment' ); ?>" method="post" id="unique-form-customize">
                <label id="label-show-questionnaire"><input type="checkbox" id="show-questionnaire"><?php _e('This job requires question set(s)', 'wp-erp-rec');?></label>
                <div id="step-questionnaire" class="openingform_input_wrapper">
                    <div id="meta-inner-question-left-side">
                        <?php
                        // get questionnaire post types and show in a drop down list
                        $posts = get_posts(array(
                                'post_type'      => 'erp_hr_questionnaire',
                                'post_status'    => 'publish',
                                'posts_per_page' => -1
                            )
                        );
                        if ( isset($posts) ) : ?>
                            <div>
                                <?php if ( is_array($posts) && count($posts) > 0 ) : ?>
                                    <label><?php _e('Please Select Question set:', 'wp-erp-rec');?></label>
                                    <select id="qset">
                                        <?php foreach ( $posts as $p ) : ?>
                                            <?php if ( count(get_post_meta($p->ID, '_erp_hr_questionnaire', true)) > 0 ) : ?>
                                                <option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="add page-title-action page-title-action-q"><?php _e('Add Question Set', 'wp-erp-rec'); ?></span>
                                <?php else : ?>
                                    <label id="no-question-caption"><?php _e('No question set found. Please create a question set first', 'wp-erp-rec'); ?></label>
                                    <a id="create-question-btn" class="button button-hero button-primary alignright" href="<?php echo admin_url('edit.php?post_type=erp_hr_questionnaire');?>"><?php _e('Create question set', 'wp-erp-rec');?></a>
                                <?php endif;?>
                            </div>
                        <?php endif; ?>
                        <span id="here"></span>
                    </div>
                    <?php if ( is_array($posts) && count($posts) > 0 ) : ?>
                        <div id="meta-inner-question-right-side">
                            <a class="button button-hero button-primary alignright" href="<?php echo admin_url('edit.php?post_type=erp_hr_questionnaire');?>"><?php _e('Create question set', 'wp-erp-rec');?></a>
                        </div>
                    <?php endif;?>
                </div>
                <input type="hidden" name="postid" value="<?php echo $postid; ?>">
                <?php wp_nonce_field( 'questionnaire' ); ?>
                <div id="question-next-prev-buttons">
                    <a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=candidate_basic_information&postid='.$postid ); ?>" class="button button-hero"><?php _e('&larr; Back', 'wp-erp-rec');?></a>
                    <input type="submit" name="questionnaire" class="button-primary button button-hero alignright" value="<?php _e( 'Finish', 'wp-erp-rec');?>">
                </div>
            </form>
        </div>
    </div>
</div>