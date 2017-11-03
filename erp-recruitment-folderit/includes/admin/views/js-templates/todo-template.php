<div class="row">
  <div class="col-lg-12 form-group">
    <label><?php _e( 'Title (required)', 'wp-erp-rec' ); ?></label>
    <input name="todo_title" id="todo_title" class="form-control" value="" tabindex="1" type="text">
  </div>
  <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
  <div class="col-lg-12 form-group">
    <label><?php _e( 'Assign user (required)', 'wp-erp-rec' ); ?></label>
    <?php erp_html_form_input( array(
                'label'       => __( '', 'wp-erp-rec' ),
                'name'        => 'assign_user_id[]',
                'value'       => '',
                'type'        => 'select',
                'id'          => 'assign_user_id',
                'custom_attr' => [ 'multiple' => 'multiple', 'data-placeholder' => 'Select an employee' ],
                'class'       => 'multiple-selection form-control',
                'options'     => $employee_list,
                'required'    => true
            ) ); ?>
  </div>
  <div class="col-lg-6 form-group">
    <label><?php _e( 'Dead Line', 'wp-erp-rec' ); ?></label>
    <?php erp_html_form_input( array(
                'label'    => __( '', 'wp-erp-rec' ),
                'name'     => 'deadlinedate',
                'value'    => '',
                'type'     => 'text',
                'class'    => 'erp-date-field-todo-deadline form-control',
                'required' => false
            ) ); ?>
  </div>
  <div class="col-lg-6 form-group">
    <?php erp_html_form_input( array(
                'label'    => __( '', 'wp-erp-rec' ),
                'name'     => 'deadlinetime',
                'value'    => '',
                'type'     => 'text',
                'class'    => 'erp-time-field form-control',
                'required' => false
            ) ); ?>
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
