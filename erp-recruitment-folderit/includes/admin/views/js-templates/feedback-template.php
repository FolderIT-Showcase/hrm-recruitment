<div class="row">
  <?php $application_id = $_GET['application_id'];?>
  <div class="col-lg-12 form-group">
    <label>
      <?php _e('Feedback Comments', 'wp-erp-rec'); ?>
    </label>
    <?php erp_html_form_input(array(
      'label'       => __('', 'wp-erp-rec'),
      'name'        => 'feedback_comment',
      'value'       => '',
      'type'        => 'textarea',
      'id'          => 'feedback_comment',
      'class'       => 'form-control',
      'custom_attr' => array(
          'rows'  => 10,
          'media' => true,
          'teeny' => false
      ),
      'required'    => true
    )); ?>
  </div>

  <div class="col-lg-6 form-group">
    <label>
      <?php _e('English Level', 'wp-erp-rec'); ?>
    </label>
    <?php erp_html_form_input(array(
      'label'       => __('', 'wp-erp-rec'),
      'name'        => 'feedback_english_level',
      'value'       => '',
      'class'       => 'form-control',
      'type'        => 'select',
      'id'          => 'feedback_english_level',
      'options'     => erp_rec_get_feedback_english_levels(),
      'required'    => false
    )); ?>
  </div>
  <div class="col-lg-6 form-group">
    <label>
      <?php _e('English Conversation', 'wp-erp-rec'); ?>
    </label>
    <?php erp_html_form_input(array(
      'label'    => __('', 'wp-erp-rec'),
      'name'     => 'feedback_english_conversation',
      'value'    => '',
      'type'     => 'select',
      'class'       => 'form-control',
      'id'       => 'feedback_english_conversation',
      'options'  => erp_rec_get_feedback_english_conversation(),
      'required' => false
    )); ?>
  </div>
</div>

<input type="hidden" id="interview_application_id" name="interview_application_id" value="">
<input type="hidden" id="feedback_english_level_text" name="feedback_english_level_text" value="">
<input type="hidden" id="feedback_english_conversation_text" name="feedback_english_conversation_text" value="">
