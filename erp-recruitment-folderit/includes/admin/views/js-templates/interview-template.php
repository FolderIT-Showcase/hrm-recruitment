<div class="wrap erp">
    <div class="interview-template-container">
        <?php $application_id = $_GET['application_id'];?>
        <div class="row">
            <div class="popuplside">
                <p><?php _e('Select type of interview (required)', 'wp-erp-rec'); ?></p>
                <?php erp_html_form_input(array(
                    'label'       => __('', 'wp-erp-rec'),
                    'name'        => 'type_of_interview',
                    'value'       => '',
                    'type'        => 'select',
                    'id'          => 'type_of_interview',
                    'options'     => erp_rec_get_application_stage_intvw_popup($application_id),
                    'required'    => true
                )); ?>
            </div>
            <div class="popuprside">
                <p><?php _e('Interview Detail (e.g vanue, phone etc)', 'wp-erp-rec'); ?></p>
                <input type="text" name="interview_detail" id="interview_detail" value="">
            </div>
        </div>
		
		<div class="row">
            <div class="popuplside">
				<p><?php _e('Select internal type of interview (required)', 'wp-erp-rec'); ?></p>
				<?php erp_html_form_input(array(
					'label'       => __('', 'wp-erp-rec'),
					'name'        => 'internal_type_of_interview',
					'value'       => '',
					'type'        => 'select',
					'id'          => 'internal_type_of_interview',
					'options'     => erp_rec_get_application_type_intvw_popup($application_id),
					'required'    => true
				)); ?>
            </div>
            <div class="popuprside">
				<p><?php _e('Interview Techs', 'wp-erp-rec'); ?></p>
                <input type="text" name="interview_tech" id="interview_tech" value="">
            </div>
        </div>

        <div class="row">
            <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
            <p><?php _e('Interviewers (required)', 'wp-erp-rec'); ?></p>
            <?php erp_html_form_input(array(
                'label'       => __('', 'wp-erp-rec'),
                'name'        => 'interviewers[]',
                'value'       => '',
                'type'        => 'select',
                'id'          => 'interviewers',
                'custom_attr' => ['multiple' => 'multiple'],
                'class'       => 'select_multiple_selection',
                'options'     => $employee_list,
                'required'    => true
            )); ?>
        </div>

        <div class="row">
            <p><?php _e('Duration', 'wp-erp-rec'); ?></p>
            <?php erp_html_form_input(array(
                'label'       => __('', 'wp-erp-rec'),
                'name'        => 'duration',
                'value'       => '',
                'type'        => 'select',
                'id'          => 'duration',
                'options'     => erp_rec_get_interview_time_duration(),
                'required'    => false
            )); ?>
        </div>

        <div class="row">
            <label><?php _e('Interview Date', 'wp-erp-rec'); ?></label>
            <?php erp_html_form_input(array(
                'label'    => __('', 'wp-erp-rec'),
                'name'     => 'interview_date',
                'value'    => '',
                'type'     => 'text',
                'class'    => 'erp-date-field-todo-deadline',
                'required' => false
            )); ?>
            <?php erp_html_form_input(array(
                'label'    => __('', 'wp-erp-rec'),
                'name'     => 'interview_time',
                'value'    => '',
                'type'     => 'text',
                'class'    => 'erp-time-field',
                'required' => false
            )); ?>
        </div>
        <input type="hidden" id="interview_application_id" name="interview_application_id" value="">
        <input type="hidden" id="type_of_interview_text" name="type_of_interview_text" value="">
        <input type="hidden" id="internal_type_of_interview_text" name="internal_type_of_interview_text" value="">
    </div>
</div>