<div class="row">
  <?php $application_id = $_GET['application_id'];?>
  <div class="col-lg-6 form-group">
    <label><?php _e('Select internal type of interview (required)', 'wp-erp-rec'); ?></label>
    <?php erp_html_form_input(array(
            'label'       => __('', 'wp-erp-rec'),
            'name'        => 'internal_type_of_interview',
            'value'       => '',
            'type'        => 'radio',
            'class'       => 'form-control',
            'id'          => 'internal_type_of_interview',
            'options'     => erp_rec_get_application_type_intvw_popup($application_id),
            'required'    => true
        )); ?>
  </div>

  <div class="col-lg-6 form-group">
    <label><?php _e('Interview Techs', 'wp-erp-rec'); ?></label>
    <input type="text" class="form-control" name="interview_tech" id="interview_tech" value="">
  </div>

  <div class="col-lg-12 form-group">
    <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
    <label><?php _e('Interviewers (required)', 'wp-erp-rec'); ?></label>
    <?php erp_html_form_input(array(
            'label'       => __('', 'wp-erp-rec'),
            'name'        => 'interviewers[]',
            'value'       => '',
            'type'        => 'select',
            'id'          => 'interviewers',
            'custom_attr' => ['multiple' => 'multiple'],
            'class'       => 'form-control select_multiple_selection',
            'options'     => $employee_list,
            'required'    => true
        )); ?>
  </div>

  <div class="col-lg-6 form-group">
    <label><?php _e('Interview Date', 'wp-erp-rec'); ?></label>
    <div class='input-group date' id="interview_datetime_picker">
      <input type='text' class="form-control" id="interview_datetime" name="interview_datetime" required />
      <span class="input-group-addon">
            <span class="fa fa-calendar"></span>
      </span>
    </div>
  </div>

  <div class="col-lg-6 form-group">
    <label><?php _e('Duration', 'wp-erp-rec'); ?></label>
    <?php erp_html_form_input(array(
          'label'       => __('', 'wp-erp-rec'),
          'name'        => 'duration',
          'value'       => '',
          'type'        => 'select',
          'class'       => 'form-control',
          'id'          => 'duration',
          'options'     => erp_rec_get_interview_time_duration(),
          'required'    => false
      )); ?>
  </div>

  <div class="col-lg-12 form-group">
    <label><?php _e('Interview Detail (e.g vanue, phone etc)', 'wp-erp-rec'); ?></label>
    <?php erp_html_form_input(array(
          'label'    => __('', 'wp-erp-rec'),
          'name'     => 'interview_detail',
          'value'    => '',
          'type'     => 'textarea',
          'class'    => 'form-control',
          'id'       => 'interview_detail',
          'custom_attr' => array(
            'rows'  => 10,
            'media' => true,
            'teeny' => false
          ),
          'required' => false
        )); ?>
  </div>
</div>

<input type="hidden" id="type_of_interview" name="type_of_interview" value="">
<input type="hidden" id="interview_application_id" name="interview_application_id" value="">
<input type="hidden" id="type_of_interview_text" name="type_of_interview_text" value="">
<input type="hidden" id="internal_type_of_interview_text" name="internal_type_of_interview_text" value="">

<script type="text/javascript">
  jQuery(document).ready(function($) {
    jQuery('#interview_datetime_picker').datetimepicker({
      format: 'DD/MM/YYYY h:mm A'
    });
  });

</script>
