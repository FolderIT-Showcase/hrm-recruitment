<div id="primary" class="content-area">
  <main id="main" class="site-main wrap erp" role="main">

    <div id="message" class="notice notice-success is-dismissible">
      <p id="message_text"></p>
      <button class="notice-dismiss" type="button"><span class="screen-reader-text"></span></button>
    </div>

    <div class="erp-single-container">
      <?php
      $email_string = '';
      $total_selected_jobseekers = 0;
      $selected_email = [];
      if ( isset($_REQUEST['email_ids']) ) {
        $selected_email = $_REQUEST['email_ids'];
        $email_string = implode(',', $selected_email);
        $total_selected_jobseekers = count($selected_email);
      }
      ?>
      <div id="send_email_wrapper" class="erp-area-left">
        <div id="send_email_wrapper_postbox" class="postbox">
          <h3 class="hndle"><?php _e('Send email', 'wp-erp-rec'); ?></h3>

          <div class="email-inside inside">
            <form autocomplete="off" id="send_email_to_jobseeker" method="post" v-on:submit.prevent="sendEmail">
              <div class="row">
                <input type="hidden" id="recipient_list" name="to" value="<?php echo $email_string; ?>">
              </div>
              <div class="row">
                <label><?php _e('Subject', 'wp-erp-rec'); ?></label>
                <input type="text" name="subject" value="" class="widefat" v-model="subject">
              </div>
              <div class="row">
                <label><?php _e('Message', 'wp-erp-rec'); ?></label>
                <?php
                $content = '';
                $editor_id = 'emessage';
                $settings = array('textarea_name' => 'emessage');
                wp_editor($content, $editor_id, $settings);
                ?>
              </div>
              <div class="row">
                <input type="hidden" name="action" value="wp-erp-rec-send-email"/>
                <?php wp_nonce_field('wp_erp_rec_send_email_nonce'); ?>
                <input type="submit" class="page-title-action alignright email_send_button button button-primary" value="Send Email">
              </div>
              <div v-bind:class="[ isError ? error_notice_class : success_notice_class ]" v-show="isVisible">{{ response_message }}</div>
            </form>
          </div>
        </div>
      </div>

      <div id="send_email_recipient_wrapper" class="erp-area-right">
        <div id="email_recipient_list" class="postbox">
          <h3 id="total_recipient" class="hndle"><?php _e('Recipient List', 'wp-erp-rec'); ?></h3>

          <div class="email-list-inside inside">
            <ul>
              <?php foreach ($selected_email as $re) : ?>
              <li>
                <a class="remove_email dashicons dashicons-trash" href="#"></a>
                <div class="gravater_img"><?php echo get_avatar($re, 32); ?></div>
                <label><?php echo $re; ?></label>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!-- .site-main -->
</div><!-- .content-area -->