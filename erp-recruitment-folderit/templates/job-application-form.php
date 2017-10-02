<?php
global $wpdb;

$default_fields             = erp_rec_get_default_fields();
$all_personal_fields        = erp_rec_get_personal_fields();
$db_choosen_personal_fields = get_post_meta(get_the_ID(), '_personal_fields', true);
$postid                     = get_the_ID();
$meta_key                   = '_personal_fields';

$db_choosen_fields_array    = [];
$qset                       = [];
$qset_array                 = [];

$personal_field_data = $wpdb->get_var(
    $wpdb->prepare("SELECT meta_value
        FROM {$wpdb->prefix}postmeta
        WHERE meta_key = %s AND post_id = %d", $meta_key, $postid ) );
$personal_field_data = maybe_unserialize( $personal_field_data );

// convert object to array
if ( is_array($db_choosen_personal_fields) ) {
    foreach ( $db_choosen_personal_fields as $dbf ) {
        $db_choosen_fields_array[] = (array)$dbf;
    }
}

$qset_array = get_post_meta(get_the_ID(), '_erp_hr_questionnaire', true);
if ( is_array($qset_array) ) {
    foreach ($qset_array as $q) {
        $qset[] = $q['questionset_id'];
    }
}

$quesionset=[];
foreach ($qset as $q) {
    $quesionset[] = get_post_meta($q, '_erp_hr_questionnaire', true);
}

?>

<form class="erp-rec-jobseeker-form" id="jobseeker_form" method="post" enctype="multipart/form-data">
    <?php $j = 0; $k = 0;?>

    <fieldset class="recruitment-personal-info">
        <h3 class="job-fieldset-title"><?php _e( 'Please enter your personal information', 'wp-erp-rec'); ?></h3>

        <?php // var_dump( $default_fields ); ?>

        <?php foreach ($default_fields as $key => $value) : ?>
            <div class="erp-rec-form-field rec-clearfix">

                <label class="title">
                    <?php echo esc_html( $value['label'] ); ?>

                    <?php if ( isset($value['required']) && $value['required'] == true ) : ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>

                <span class="erp-rec-input-field">

                    <?php if ( in_array( $value['type'], [ 'text', 'textarea', 'email' ] ) ) : ?>

                        <?php erp_html_form_input(array(
                            'name'     => $value['name'],
                            'required' => (( isset($value['required']) && $value['required'] == true) ? "required" : ""),
                            'value'    => '',
                            'class'    => ($value['required'] == true) ? "reqc" : "",
                            'type'     => $value['type'],
                        ) ); ?>

                    <?php elseif ( $value['type'] == 'name' ) : ?>

                        <span class="rec-clearfix">

                            <span class="name-col first-name">
                                <input type="text" class="inputclass reqc" name="first_name" value="" placeholder="<?php echo esc_attr( __( 'First Name', 'wp-erp-rec' ) ); ?>" maxlength="50" required />
                            </span>

                            <span class="name-col last-name">
                                <input type="text" class="inputclass reqc" name="last_name" value="" placeholder="<?php echo esc_attr( __( 'Last Name', 'wp-erp-rec' ) ); ?>" maxlength="50" required />
                            </span>
                        </span>

                    <?php elseif ( $value['type'] == 'file' ) : ?>

                        <input type="file" class="inputclass <?php echo ($value['required'] == true) ? "reqc" : ""; ?>" name="<?php echo $value['name']; ?>" required />

                    <?php elseif ( $value['type'] == 'date' ) : ?>

                        <input type="text" class="erp-rec-date-field inputclass <?php echo ($value['required'] == true) ? "reqc" : ""; ?>" name="<?php echo $value['name']; ?>"/>


                    <?php elseif ( $value['type'] == 'select' ) : ?>
                        <?php erp_html_form_input(array(
                            'name'     => $value['name'],
                            'required' => ((isset($value['required']) && $value['required'] == true) ? "required" : ""),
                            'value'    => '',
                            'class'    => "erp-hrm-select2" . ($value['required'] == true) ? " reqc" : "",
                            'type'     => 'select',
                            'options'  => array('' => __('- Select -', 'wp-erp-rec')) + (isset($value['options']) ? $value['options'] : [])
                        ) ); ?>
                    <?php endif; ?>

                    <?php if ( isset( $value['help'] ) && !empty( $value['help'] ) ) { ?>
                        <p class="help-field"><?php echo $value['help']; ?></p>
                    <?php } ?>
                </span>
            </div>
        <?php endforeach; ?>

        <?php if ( is_array($personal_field_data) ) :  ?>
            <?php foreach ($personal_field_data as $key => $value) :

                $field = json_decode( $value );

                if ( ! $field->showfr ) {
                    continue;
                }

                $personal_data_field      = $field->field;
                $personal_data_showfr     = $field->showfr;
                $personal_data_req        = $field->req;
                $personal_data_field_type = $field->type;
                $help_text                = '';

                if ( isset( $all_personal_fields[ $field->field ]['help'] ) ) {
                    $help_text = $all_personal_fields[ $field->field ]['help'];
                }
                ?>

                <div class="erp-rec-form-field rec-clearfix">

                    <label class="title">
                        <!-- <?php echo ucfirst( str_replace( "_", " ", $personal_data_field ) ); ?> -->
						<?php echo $all_personal_fields[ $field->field ]['label']; ?>
                        <?php if ( $personal_data_req == true ) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>

                    <span class="erp-rec-input-field">

                        <?php if ( $personal_data_field_type == 'text' && $personal_data_showfr == true ) : ?>

                            <input type="text" class="inputclass <?php echo ($personal_data_req == true) ? "reqc" : ""; ?>" name="<?php echo $personal_data_field; ?>" value="" maxlength="50"/>

                        <?php elseif ( $personal_data_field_type == 'email' && $personal_data_showfr == true ) : ?>

                            <input type="email" class="inputclass <?php echo ($personal_data_req == true) ? "reqc" : ""; ?>" name="<?php echo $personal_data_field; ?>" value=""/>

                        <?php elseif ( $personal_data_field_type == 'file' && $personal_data_showfr == true ) : ?>

                            <input type="file" class="inputclass <?php echo ($personal_data_req == true) ? "reqc" : ""; ?>" name="<?php echo $personal_data_field; ?>"/>

                        <?php elseif ( $personal_data_field_type == 'date' && $personal_data_showfr == true ) : ?>

                            <input type="text" class="erp-rec-date-field inputclass <?php echo ($personal_data_req == true) ? "reqc" : ""; ?>" name="<?php echo $personal_data_field; ?>"/>

                        <?php elseif ( $personal_data_field_type == 'textarea' && $personal_data_showfr == true ) : ?>

                            <textarea type="textarea" rows="5" class="inputclass <?php echo ($personal_data_req == true) ? "reqc" : ""; ?>" name="<?php echo $personal_data_field; ?>"></textarea>

                        <?php elseif ( $personal_data_field_type == 'select' && $personal_data_showfr == true ) : ?>
                            <?php erp_html_form_input(array(
                                'name'     => $personal_data_field,
                                'required' => ( ( $personal_data_req == true ) ? "required" : "" ),
                                'value'    => '',
                                'class'    => "erp-hrm-select2" . ($personal_data_req == true) ? "reqc" : "",
                                'type'     => 'select',
                                'options'  => array('' => __('- Select -', 'wp-erp-rec')) + (isset($all_personal_fields[$personal_data_field]['options']) ? $all_personal_fields[$personal_data_field]['options'] : [])
                            )); ?>

                        <?php elseif ( $personal_data_field_type == 'checkbox' && $personal_data_showfr == true ) : ?>
                            <?php $checkbox_name = $personal_data_field;?>
                            <?php erp_html_form_input(array(
                                'name'     => $checkbox_name.'[]',
                                'required' => "",
                                'value'    => '',
                                'class'    => '',
                                'type'     => 'multicheckbox',
                                'options'  => isset($all_personal_fields[$personal_data_field]['options']) ? $all_personal_fields[$personal_data_field]['options'] : []
                                )); ?>
                        <?php endif; ?>

                        <?php if ( !empty( $help_text ) ) { ?>
                            <p class="help-field"><?php echo $help_text; ?></p>
                        <?php } ?>
                    </span>

                </div>

            <?php endforeach; ?>

        <?php endif;?>
	</fieldset>
	
	<?php if ( is_array($quesionset) ) : ?>

        <div class="recruitment-questions-wrapper">
            <?php foreach ($quesionset as $topvalue) : ?>
                <fieldset class="question_answer_fieldset">
                    <!-- <h3 class="job-fieldset-title"><?php _e( 'Please answer the questions below first', 'wp-erp-rec'); ?></h3> -->

                    <?php if ( is_array($topvalue) ) : ?>
                        <?php foreach ( $topvalue as $key => $value ) : ?>

                            <div class="erp-rec-form-field rec-clearfix">

                                <label class="title">
                                    <?php echo esc_html( $value['label'] ); ?>

                                    <?php if ( isset($value['req']) && $value['req'] == true ) : ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>

                                <input type="hidden" name="question[]" value="<?php echo esc_html($value['label']);?>">

                                <span class="erp-rec-input-field">

                                    <?php if ( isset($value['type']) && $value['type'] == 'text' ) : ?>

                                        <input type="text" class="inputclass" name="answer[]" value="" />

                                    <?php elseif ( isset($value['type']) && $value['type'] == 'textarea' ) : ?>

                                        <textarea type="textarea" class="inputclass" name="answer[]" rows="5"></textarea>

                                    <?php elseif ( isset($value['type']) && $value['type'] == 'radio' ) : ?>

                                        <?php foreach ( $value['options'] as $opt ) : ?>
                                            <label>
                                                <input type="radio" class="answer_radio" name="answer[chk<?php echo $k; ?>]" value="<?php echo $opt['value']; ?>"> <?php echo $opt['value']; ?>
                                            </label>
                                        <?php endforeach; ?>

                                    <?php elseif ( isset($value['type']) && $value['type'] == 'checkbox' ) : ?>

                                        <?php foreach ( $value['options'] as $opt ) : ?>
                                            <label>
                                                <input type="checkbox" class="answer_checkbox" name="answer[chk<?php echo $j; ?>][]" value="<?php echo $opt['value']; ?>"> <?php echo $opt['value']; ?>
                                            </label>
                                        <?php endforeach; ?>

                                    <?php elseif ( isset($value['type']) && $value['type'] == 'select' ) : ?>

                                        <select name="answer[]">
                                            <?php foreach ( $value['options'] as $opt ) : ?>
                                                <option value="<?php echo $opt['value']; ?>"><?php echo $opt['value']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>

                                    <?php if ( isset( $value['helptext'] ) && !empty( $value['helptext'] ) ) : ?>
                                        <p class="help-field"><?php echo $value['helptext'];?></p>
                                    <?php endif;?>

                                </span>

                            </div>

                            <?php $j++; $k++; ?>

                        <?php endforeach; ?>
                    <?php endif;?>

                </fieldset>

            <?php endforeach; ?>
        </div><!-- .recruitment-questions-wrapper -->
    <?php endif;?>
	
	<fieldset>
        <div class="erp-rec-form-field rec-clearfix">
            <?php
            $first_rand_number  = rand(1, 20);
            $second_rand_number = rand(1, 20);
            $sum_result         = $first_rand_number + $second_rand_number;
            // echo $first_rand_number . '+' . $second_rand_number;
            ?>
            <label class="title">
                <?php printf( __( 'What\'s %d + %d = ?', 'wp-erp-rec' ), $first_rand_number, $second_rand_number ); ?>
                <span class="required">*</span>
            </label>

            <span class="erp-rec-input-field">
                <input id="captcha_correct_result" type="hidden" name="captcha_correct_result" value="<?php echo $sum_result; ?>">
                <input id="captcha_result" type="text" name="captcha_result" class="inputclass" value="" maxlength="3" required />
            </span>
        </div>

        <?php wp_nonce_field('wp-erp-rec-job-seeker-nonce'); ?>

        <input type="hidden" name="job_id" value="<?php echo get_the_ID(); ?>">
        <input type="hidden" name="action" value="wp-erp-rec-job-seeker-creation"/>
        <input type="submit" class="sub" name="submit_app" id="submit_app" value="<?php _e( 'Submit Application', 'wp-erp-rec' ); ?>"/>

        <div id="loader_wrapper"><div id="loader_gif"></div></div>
    </fieldset>
</form>
