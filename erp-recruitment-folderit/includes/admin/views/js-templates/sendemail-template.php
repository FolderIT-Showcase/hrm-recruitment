<div class="row">
  <div class="col-lg-6 form-group">
    <label for="email_to"><?php _e('To', 'wp-erp-rec'); ?></label>
    <input type="text" readonly value="" id="email_to" name="email_to" class="form-control">
  </div>
  <div class="col-lg-6 form-group">
    <label for="email_to_name"><?php _e('Addressee', 'wp-erp-rec'); ?></label>
    <input type="text" readonly value="" id="email_to_name" name="email_to_name" class="form-control">
  </div>

  <div class="col-lg-12 form-group">
    <?php

    $erp_email_settings = get_option( 'erp_settings_erp-email_smtp', [] );

    if ( ! isset( $erp_email_settings['default_subject'] ) ) {
      $default_subject = '';
    } else {
      $default_subject = $erp_email_settings['default_subject'];
    }

    erp_html_form_input(array(
      'label'       => __('Subject', 'wp-erp-rec'),
      'name'        => 'email_subject',
      'value'       => $default_subject,
      'type'        => 'text',
      'id'          => 'email_subject',
      'class'       => 'form-control',
      'required'    => true
    )); ?>
  </div>

  <div class="col-lg-12 form-group">
    <?php erp_html_form_input(array(
  'label'       => __('Message', 'wp-erp-rec'),
  'name'        => 'email_message',
  'value'       => '',
  'type'        => 'textarea',
  'id'          => 'email_message',
  'class'       => 'form-control',
  'custom_attr' => array(
    'rows'  => 10,
    'media' => true,
    'teeny' => false
  ),
  'required'    => true
)); ?>
  </div>
</div>

<input type="hidden" value="<?php echo $_GET['application_id']; ?>" id="email_aplication_id" name="email_aplication_id">
<?php wp_nonce_field('wp_erp_rec_send_email_nonce', '_sendemail_nonce'); ?>
