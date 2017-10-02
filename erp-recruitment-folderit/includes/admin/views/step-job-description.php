<div class="wrap uniq-wrap" id="uniq-wrap">
    <?php
    echo erp_rec_opening_admin_progressbar( 'job_description' );

    $postid   = isset( $_REQUEST['postid'] ) ? intval( $_REQUEST['postid'] ) : 0;
    $content  = ( $postid == 0 ) ? '' : get_post( $postid )->post_content;
    $settings = array(
        'media_buttons' => false,
        'teeny'         => true,
        'quicktags'     => false,
        'editor_height' => 200
    );
    ?>

    <div class="postbox metabox-holder" style="padding-top: 0; max-width: 1060px; margin: 0 auto;">
        <h3 class="openingform_header_title hndle"><?php _e( 'Job information', 'wp-erp-rec' ); ?></h3>

        <div class="inside" style="overflow-y: hidden;">
            <form action="<?php echo admin_url( 'edit.php?post_type=erp_hr_recruitment&page=add-opening&action=edit&step=hiring_workflow' ); ?>" method="post" id="unique-form-component">
                <div class="item">
                    <h4 style="margin-bottom: 7px;"><?php _e('Position Title', 'wp-erp-rec');?></h4>
                    <input class="widefat" type="text" id="opening_title" name="opening_title" value="<?php echo ( $postid == 0 ) ? '' : get_the_title($postid);?>">
                </div>
                <div class="item">
                    <h4 style="margin-bottom: 7px;"><?php _e('Opening Description', 'wp-erp-rec');?></h4>
                    <?php wp_editor( $content, 'opening_description', $settings ); ?>
                </div>
                <input type="hidden" name="postid" value="<?php echo $postid;?>">
                <?php wp_nonce_field( 'create_opening' ); ?>
                <?php submit_button( __( 'Next &rarr;', 'wp-erp-rec' ), 'primary button-hero alignright', 'create_opening' ); ?>
            </form>
        </div>
    </div>
</div>