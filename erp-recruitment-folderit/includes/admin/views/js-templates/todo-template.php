<div class="row">
  <div class="col-lg-12 form-group">
    <?php erp_html_form_input(array(
  'label'       => __('Title', 'wp-erp-rec'),
  'name'        => 'todo_title',
  'value'       => '',
  'type'        => 'text',
  'id'          => 'todo_title',
  'class'       => 'form-control',
  'required'    => true
)); ?>
  </div>
  <div class="col-lg-12 form-group">
    <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
    <?php erp_html_form_input( array(
  'label'       => __('Moderators', 'wp-erp-rec'),
  'name'        => 'assign_user_id[]',
  'value'       => '',
  'type'        => 'select',
  'id'          => 'assign_user_id',
  'custom_attr' => [ 'multiple' => 'multiple', 'data-placeholder' => __('Select an employee', 'wp-erp-rec') ],
  'class'       => 'multiple-selection form-control',
  'options'     => $employee_list,
  'required'    => true
) ); ?>
  </div>
  <div class="col-lg-6 form-group">
    <label><?php _e('Dead Line', 'wp-erp-rec'); ?></label>
    <div class='input-group date' id="deadline_datetime_picker">
      <input type='text' class="form-control" id="deadline_datetime" name="deadline_datetime" />
      <span class="input-group-addon">
        <span class="fa fa-calendar"></span>
      </span>
    </div>
  </div>
  <div class="col-lg-12 form-group">
    <?php erp_html_form_input(array(
      'label'       => __('Description', 'wp-erp-rec'),
      'name'        => 'description',
      'value'       => '',
      'type'        => 'textarea',
      'id'          => 'description',
      'class'       => 'form-control',
      'custom_attr' => array(
          'rows'  => 10,
          'media' => true,
          'teeny' => false
      ),
      'required'    => false
    )); ?>
  </div>
</div>

<input type="hidden" id="todo_application_id" name="todo_application_id" value="">

<script type="text/javascript">
  jQuery(document).ready(function($) {
    jQuery('#deadline_datetime_picker').datetimepicker({
      format: 'DD/MM/YYYY h:mm A'
    });
  });

</script>
