<div class="row">
  <div class="col-lg-12 form-group">
    <?php erp_html_form_input(array(
  'label'       => __('Subject', 'wp-erp-rec'),
  'name'        => 'email_subject',
  'value'       => '',
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

<input type="hidden" value="" id="email_to" name="email_to">
<input type="hidden" value="<?php echo $_GET['application_id']; ?>" id="email_aplication_id" name="email_aplication_id">
<?php wp_nonce_field('wp_erp_rec_send_email_nonce', '_sendemail_nonce'); ?>
