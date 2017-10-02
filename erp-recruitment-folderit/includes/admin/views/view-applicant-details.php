<?php
/* view applicant Details */

// Exit if accessed directly
if ( !defined('ABSPATH') ) exit;

if ( !isset($_GET['application_id']) || !is_numeric($_GET['application_id']) ) {
    wp_die(__('Application ID not supplied. Please try again', 'wp-erp-rec'), __('Error', 'wp-erp-rec'));
}
// Setup the variables
$application_id = $_GET['application_id'];
global $post;
?>
<?php
$applicant_information = erp_rec_get_applicant_information($application_id);
$hire_status           = 0;
$jobid                 = 0;
$application_status    = '';
$applicant_id          = 0;

if ( isset($applicant_information[0]) ) {
    $hire_status        = $applicant_information[0]['status'];
    $attachment_url     = wp_get_attachment_url($applicant_information[0]['other']);
    $applicant_id       = $applicant_information[0]['applicant_id'];
    $attach_id          = erp_people_get_meta($applicant_id, 'attach_id', true);
    $jobid              = $applicant_information[0]['job_id'];
    $application_status = erp_people_get_meta($applicant_id, 'status');
}
?>
<div id="candidate-detail" class="wrap erp erp-applicant-detail wp-erp-wrap">
    <h1><?php _e('Applicant Details', 'wp-erp-rec'); ?></h1>

    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <div class="inside" style="overflow-y: hidden; margin-bottom: 0; padding-bottom: 0;">
                        <div id="gravater_image">
                            <?php
                            $email_address = isset($applicant_information[0]['email']) ? $applicant_information[0]['email'] : '';
                            echo get_avatar($email_address);
                            ?>
                        </div>
                        <div id="name_and_position">
                            <span id="candidate_name">
                                <?php echo isset($applicant_information[0]['first_name']) ? ucfirst($applicant_information[0]['first_name']) : ''; ?>
                                <?php echo isset($applicant_information[0]['last_name']) ? ucfirst($applicant_information[0]['last_name']) : ''; ?>
                            </span>
                            <span id="job_title"><?php echo "( " . strtoupper( get_the_title( $applicant_information[0]['job_id'] ) ) . " )"; ?></span>
                        </div>
                        <div id="stage_and_rating">
                            <span id="stage_name">
                                <i class="fa fa-flag"></i>&nbsp;
                                <?php _e('Stage : ', 'wp-erp-rec'); ?>
                                <span id="change_stage_name"><?php echo erp_rec_get_app_stage($application_id);?></span>
                            </span>
                            <span id="rating">
                                <i class="fa fa-star"></i>&nbsp;
                                <?php _e('Rating : ', 'wp-erp-rec'); ?>{{ avgRating }}/5
                            </span>
                            <span id="status">
                                <i class="fa fa-info-circle"></i>&nbsp;
                                <span id="change_status_name"><?php _e('Status : ', 'wp-erp-rec'); ?>
                                    <?php echo ( erp_people_get_meta($applicant_id, 'status', true) != "" ) ? ucfirst( str_replace( "_", " ", erp_people_get_meta($applicant_id, 'status', true ) ) ) : __('No status set', 'wp-erp-rec') ; ?>
                                </span>
                            </span>
                        </div>
                        <div id="button-actions" class="<?php echo ( $hire_status == 1 || $application_status == 'rejected' ) ? 'button-actions-hired' : '';?>">
                            <div class="button-controls alignright">
                                <?php if ( $hire_status == 0 && $application_status != 'rejected' ) : ?>
                                <button class="button btn-interview"><i class="fa fa-lg fa-calendar"></i>&nbsp;<?php _e('New Interview', 'wp-erp-rec'); ?></button>
                                <button class="button btn-todo"><i class="fa fa-lg fa-list-alt"></i>&nbsp;<?php _e('New To-do', 'wp-erp-rec'); ?></button>
                                <?php endif;?>
                                <?php
                                if ( $hire_status == 0 && $application_status != 'rejected' ) {
                                    $make_employee_url = admin_url('admin.php?page=make_employee&application_id=' . $application_id);
                                    echo sprintf( '<a id="make_him_employee" class="btn button alignright" href="%s"><i class="fa fa-lg fa-user-plus"></i>%s</a>', $make_employee_url, __( 'Hire', 'wp-erp-rec' ) );
                                }
                                ?>
                                <?php if ( isset($attach_id) && $attach_id != '' ) : ?>
                                <a class="alignright btn button" href="<?php echo wp_get_attachment_url($attach_id); ?>"><i class="fa fa-lg fa-file"></i>&nbsp;<?php _e('View CV', 'wp-erp-rec'); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ( $hire_status == 0 ) : ?>
                        <div id="dropdown-actions">
                            <div id="stage_action">
                                <?php $stages = erp_rec_get_application_stages($application_id); ?>
                                <select id="change_stage" name="change_stage" v-model="stage_id">
                                    <option value="none" selected><?php _e('&mdash; Change Stage &mdash;', 'wp-erp-rec');?></option>
                                    <?php foreach ( $stages as $value ) : ?>
                                    <option value="<?php echo $value['stageid']; ?>"><?php echo $value['title']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button button-primary" v-on:click="changeStage"><?php _e('Move', 'wp-erp-rec'); ?></button>
                                <span class="spinner"></span>
                            </div>
                            <div id="decision_action">
                                <?php $status = erp_rec_get_hiring_status(); ?>
                                <select id="change_status" name="change_status" v-model="status_name">
                                    <option value="none" selected><?php _e('&mdash; Change Status &mdash;', 'wp-erp-rec');?></option>
                                    <?php foreach ( $status as $key => $value ) : ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button button-primary" v-on:click="changeStaus"><?php _e('Done', 'wp-erp-rec'); ?></button>
                            </div>
                        </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="cpostbox" style="overflow-y: hidden">
                    <div class="cinside" style="overflow-y: hidden">
                        <table style="width: 100%">
                            <tr>
                                <td id="td-lside" style="width: 150px; vertical-align: top; background-color: #f1f1f1;">
                                    <div id="left-fixed-menu">
                                        <ul>
                                            <li><a href="#section-personal-info" class="list-item-scroller"><?php _e('Personal Information', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-resume" class="list-item-scroller"><?php _e('Resume', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-exam-detail" class="list-item-scroller"><?php _e('Exam Detail', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-comment" class="list-item-scroller"><?php _e('Comments', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-rating" class="list-item-scroller"><?php _e('Rating', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-interview" class="list-item-scroller"><?php _e('Interview', 'wp-erp-rec'); ?></a></li>
                                            <li><a href="#section-todo" class="list-item-scroller"><?php _e('Todo', 'wp-erp-rec'); ?></a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td style="width: 80%; vertical-align: top;">
                                    <div class="single-information-container meta-box-sortables ui-sortable">
                                        <section id="section-personal-info" class="postbox section-personal-info">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header">
                                                <h2 class="hndle"><span><?php _e('Candidate Profile', 'wp-erp-rec'); ?></span></h2>
                                            </div>
                                            <div class="section-content toggle-metabox-show">
                                                <div class="hndle-title">
                                                    <label><?php _e('Personal Information', 'wp-erp-rec'); ?></label>
                                                </div>
                                                <div class="content-body">
                                                    <ul class="erp-list list-table-like separated">
                                                        <li>
                                                            <label><?php _e('Name', 'wp-erp-rec'); ?></label>
                                                            <span class="sep"> : </span>
                                                            <span class="value">
                                                                <?php echo isset($applicant_information[0]['first_name']) ? esc_html( $applicant_information[0]['first_name'] ) : ''; ?>
                                                                <?php echo isset($applicant_information[0]['last_name']) ? esc_html( $applicant_information[0]['last_name'] ) : ''; ?>
                                                            </span>
                                                        </li>
                                                        <li>
                                                            <label><?php _e('Email', 'wp-erp-rec'); ?></label>
                                                            <span class="sep"> : </span>
                                                            <span class="value"><?php echo isset($applicant_information[0]['email']) ? esc_html( $applicant_information[0]['email'] ) : ''; ?></span>
                                                        </li>
                                                        <?php $db_personal_fields = get_post_meta( $jobid, '_personal_fields', true );?>
                                                        <?php foreach ( $db_personal_fields as $personal_data ) : ?>
                                                        <?php $field_name = json_decode($personal_data)->field;?>
                                                        <li>
                                                            <label><?php echo ucfirst(str_replace("_"," ",$field_name));?></label>
                                                            <span class="sep"> : </span>
                                                            <span class="value">
                                                                <?php
                                                                $value = erp_people_get_meta($applicant_id, $field_name, true);
                                                                $value = stripslashes( $value );
                                                                echo '<span class="value-pre">' . esc_html( $value ) . '</pre>';
                                                                ?>
                                                            </span>
                                                        </li>
                                                        <?php endforeach;?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="section-resume" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('Resume', 'wp-erp-rec'); ?></span></h2></div>
                                            <div class="section-content toggle-metabox-show">
                                                <?php if ( isset($attach_id) ) : ?>
                                                <?php if ( get_post_mime_type($attach_id) == 'application/msword' ) : ?>
                                                <?php $file_url = wp_get_attachment_url($attach_id); ?>
                                                <iframe class="doc" src="https://docs.google.com/gview?url=<?php echo esc_url( $file_url ); ?>&embedded=true" width="100%" height="900">
                                                    <p><?php _e('Your browser does not support iframes.','wp-erp-rec');?></p>
                                                </iframe>
                                                <?php elseif ( get_post_mime_type($attach_id) == 'application/pdf' ) : ?>
                                                <?php $file_url = wp_get_attachment_url($attach_id); ?>
                                                <iframe class="doc" src="<?php echo $file_url; ?>" width="100%" height="900">
                                                    <p><?php _e('Your browser does not support iframes.','wp-erp-rec');?></p>
                                                </iframe>
                                                <?php endif; ?>
                                                <?php else: ?>
                                                <p><?php _e('No Resume Found!','wp-erp-rec');?></p>
                                                <?php endif; ?>
                                            </div>
                                        </section>

                                        <section id="section-exam-detail" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('Exam Detail', 'wp-erp-rec'); ?></span></h2></div>
                                            <div id="exam_detail" class="section-content toggle-metabox-show not-loaded">
                                                <ul class="applicant-exam-detail">
                                                    <li class="examlist" v-for="edata in exam_data">
                                                        <div class="questions_here">
                                                            <label><?php _e('Q', 'wp-erp-rec'); ?>.&nbsp;</label><strong>{{ edata.question }}</strong></div>
                                                        <div class="answers_here"><label><?php _e('A', 'wp-erp-rec'); ?>.&nbsp;</label>{{ edata.answer }}</div>
                                                    </li>
                                                </ul>
                                                <span class="spinner"></span>
                                            </div>
                                        </section>

                                        <section id="section-comment" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('Comments', 'wp-erp-rec'); ?></span></h2></div>
                                            <div class="section-content toggle-metabox-show full-width">
                                                <div id="comment_form_wrapper" class="not-loaded">
                                                    <form id="applicant-comment-form" method="post">
                                                        <div class="row">
                                                            <div id="input_comment_wrapper">
                                                                <ul>
                                                                    <li>
                                                                        <textarea class="widefat" id="manager_comment" name="manager_comment" v-model="manager_comment"></textarea>
                                                                    </li>
                                                                </ul>
                                                                <span class="spinner"></span>
                                                                <?php wp_nonce_field('wp_erp_rec_applicant_comment_nonce'); ?>
                                                                <input type="hidden" name="admin_user_id" value="<?php echo get_current_user_id(); ?>" id="comment_admin_user_id">
                                                                <input type="hidden" name="application_id" value="<?php echo $application_id; ?>" id="application_id">
                                                                <input type="hidden" name="action" value="wp-erp-rec-manager-comment"/>
                                                                <input class="page-title-action alignright button button-primary" type="button" v-on:click="postManagerComment" name="submit" value="Submit"/>

                                                                <div v-bind:class="[ isError ? error_notice_class : success_notice_class ]" v-show="isVisible">{{ response_message }}</div>
                                                            </div>

                                                            <ul class="application-comment-list">
                                                                <li class="comment thread-even depth-1" v-for="cmnt in comments">
                                                                    <article class="comment-body">
                                                                        <div class="comment-meta">
                                                                            <div class="comment-author vcard">
                                                                                <?php //echo get_avatar( "{{ cmnt.ID }}", 64 );?>
                                                                                {{{ cmnt.user_pic }}}
                                                                                <b class="fn">{{ cmnt.display_name }}</b>
                                                                                <span class="says">&nbsp;<?php _e('says:', 'wp-erp-rec'); ?></span>

                                                                                <div class="ctime">{{ cmnt.comment_date }}</div>
                                                                                <div class="comment-content">{{ cmnt.comment }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </article>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="section-rating" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('Rating', 'wp-erp-rec'); ?></span></h2></div>
                                            <div class="section-content toggle-metabox-show full-width">
                                                <div id="rating_status_form_wrapper" class="not-loaded">
                                                    <form id="rating_status_form" method="post" v-on:submit.prevent="ratingSubmit">
                                                        <div id="input_rating_wrapper">
                                                            <label id="or_label"><?php _e('Overall Rating', 'wp-erp-rec'); ?></label>
                                                            <label id="average_rating">{{ avgRating }}/5</label>

                                                            <div class="stars stars-example-fontawesome">
                                                                <select id="example-fontawesome" name="rating" v-on:click="ratingSubmit">
                                                                    <option value=""></option>
                                                                    <option value="1"><?php _e('Bad', 'wp-erp-rec'); ?></option>
                                                                    <option value="2"><?php _e('Average', 'wp-erp-rec'); ?></option>
                                                                    <option value="3"><?php _e('Good', 'wp-erp-rec'); ?></option>
                                                                    <option value="4"><?php _e('Super', 'wp-erp-rec'); ?></option>
                                                                    <option value="5"><?php _e('Excellent', 'wp-erp-rec'); ?></option>
                                                                </select>
                                                            </div>
                                                            <div class="br-current-rating"></div>
                                                        </div>

                                                        <?php wp_nonce_field('wp_erp_rec_applicant_rating_nonce'); ?>
                                                        <input type="hidden" name="admin_user_id" value="<?php echo get_current_user_id(); ?>">
                                                        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                                                        <input type="hidden" name="action" value="wp-erp-rec-manager-rating"/>
                                                        <input class="page-title-action alignright button button-primary" v-show="false" type="submit" name="submit" value="Save"/>
                                                    </form>
                                                    <div v-bind:class="[ isError ? error_notice_class : success_notice_class ]" v-show="isVisible">{{ response_message }}</div>

                                                    <ul class="application-rating-list not-loaded" v-show="showSwitch">
                                                        <li class="comment thread-even depth-1" v-for="rt in ratingData">
                                                            <div class="comment-author vcard">
                                                                <div class="fn">
                                                                    {{{ rt.user_pic }}}
                                                                    <label>{{ rt.display_name }}</label>
                                                                    <label style="color: #000"><?php _e(' rated ', 'wp-erp-rec'); ?></label>
                                                                </div>
                                                                <div class="stars stars-example-fontawesome">
                                                                    <select class="examplefontawesome" v-barrating="rt.rating">
                                                                        <option value=""></option>
                                                                        <option value="1"><?php _e('1', 'wp-erp-rec'); ?></option>
                                                                        <option value="2"><?php _e('2', 'wp-erp-rec'); ?></option>
                                                                        <option value="3"><?php _e('3', 'wp-erp-rec'); ?></option>
                                                                        <option value="4"><?php _e('4', 'wp-erp-rec'); ?></option>
                                                                        <option value="5"><?php _e('5', 'wp-erp-rec'); ?></option>
                                                                    </select>
                                                                </div>
                                                                <label class="rating_number">&nbsp;({{rt.rating}}/5)</label>

                                                                <div class="br-current-rating"></div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <span class="spinner"></span>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="section-interview" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('Interview', 'wp-erp-rec'); ?></span></h2></div>
                                            <div class="section-content toggle-metabox-show">
                                                <h3 class="no-interview-todo-caption" v-if="hasInterview"><?php _e('No interview set', 'wp-erp-rec');?></h3>
                                                <ul class="calendar-list not-loaded">
                                                    <li class="calendar-list-item" v-for="rt in interviewData">
                                                        <div class="interview_type">
                                                            <label id="interview-type-title-{{rt.id}}">{{ rt.type_detail }}</label>
                                                            <span class="delete-button" v-on:click="deleteInterview(rt.id)">
                                                                <input id="interviewid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                                                <i class="fa fa-lg fa-trash"></i>&nbsp;<?php _e('Delete','wp-erp-rec');?>
                                                            </span>
                                                            <span class="edit-button" v-on:click="editInterview(rt.id)">
                                                                <input id="intervieweditid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                                                <i class="fa fa-lg fa-pencil"></i>&nbsp;<?php _e('Edit','wp-erp-rec');?>
                                                            </span>
                                                            <span class="feedback-button" v-on:click="feedbackInterview(rt.id)">
                                                                <input id="interviewfeedbackid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                                                <i class="fa fa-lg fa-comment-o"></i>&nbsp;<?php _e('Feedback','wp-erp-rec');?>
                                                            </span>
                                                        </div>

                                                        <div class="row">
                                                            <div class="interviewlside">
                                                                <div class="interview_stage">
                                                                    <i class="fa fa-lg fa-th-list"></i>&nbsp;<?php _e('Stage : ', 'wp-erp-rec'); ?>
                                                                    <span id="interview-stage-text-{{rt.id}}">{{ rt.title }}</span>
                                                                </div>
                                                                <div class="interviewers">
                                                                    <i class="fa fa-lg fa-user"></i>&nbsp;<?php _e('Interviewers : ', 'wp-erp-rec'); ?>
                                                                    <span id="interviewers-display-name-{{rt.id}}">{{ rt.display_name }}</span>
                                                                </div>
                                                                <div class="interview_time"><i class="fa fa-lg fa-clock-o"></i>&nbsp;<?php _e('Date and Time : ', 'wp-erp-rec'); ?>{{ rt.interview_time }}</div>
                                                                <div class="interview_detail">
                                                                    <i class="fa fa-lg fa-th-list"></i>&nbsp;<?php _e('Detail : ', 'wp-erp-rec'); ?>
                                                                    <span id="interview-detail-text-{{rt.id}}">{{ rt.interview_detail }}</span>
                                                                </div>
                                                                <div class="interview_tech">
                                                                    <i class="fa fa-lg fa-th-list"></i>&nbsp;<?php _e('Techs : ', 'wp-erp-rec'); ?>
                                                                    <span id="interview-tech-text-{{rt.id}}">{{ rt.interview_tech }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="interviewrside">
                                                                <div class="feedback_comment">
                                                                    <i class="fa fa-lg fa-comments-o"></i>&nbsp;<?php _e('Feedback Comment : ', 'wp-erp-rec'); ?>
                                                                    <pre id="feedback-comment-text-{{rt.id}}">{{ rt.feedback_comment }}</pre>
                                                                </div>
                                                                <div class="feedback_english_level">
                                                                    <i class="fa fa-lg fa-comments-o"></i>&nbsp;<?php _e('Feedback English Level : ', 'wp-erp-rec'); ?>
                                                                    <span id="feedback-english-level-text-{{rt.id}}">{{ rt.feedback_english_level }}</span>
                                                                </div>
                                                                <div class="feedback_english_conversation">
                                                                    <i class="fa fa-lg fa-comments-o"></i>&nbsp;<?php _e('Feedback English Conversation : ', 'wp-erp-rec'); ?>
                                                                    <span id="feedback-english-conversation-text-{{rt.id}}">{{ rt.feedback_english_conversation }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" id="interview-date-{{rt.id}}" value="{{rt.interview_date}}">
                                                        <input type="hidden" id="interview-time-{{rt.id}}" value="{{rt.interview_timee}}">
                                                        <input type="hidden" id="interview-duration-min-{{rt.id}}" value="{{rt.duration}}">
                                                    </li>
                                                </ul>
                                                <?php if ( $hire_status == 0 && $application_status != 'rejected' ) : ?>
                                                <button id="new-interview" class="button button-primary alignright"><?php _e('New Interview', 'wp-erp-rec');?></button>
                                                <?php endif;?>
                                                <span class="spinner"></span>
                                            </div>
                                        </section>

                                        <section id="section-todo" class="postbox">
                                            <span class="hndle-toogle-button"></span>
                                            <div class="section-header"><h2 class="hndle"><span><?php _e('To-Do List', 'wp-erp-rec'); ?></span></h2></div>
                                            <div class="section-content toggle-metabox-show full-width">
                                                <h3 class="no-interview-todo-caption" v-if="hasTodo"><?php _e('No To-do set', 'wp-erp-rec');?></h3>
                                                <ul id="calendar_list" class="calendar-list not-loaded">
                                                    <li class="calendar-list-item" v-for="rt in todoData">
                                                        <div class="interview_type">
                                                            <span class="todo-handler" v-on:click="handleTodo(rt.id, 0)" v-if=" rt.status == '1' "><i class="fa fa-lg fa-check-square-o"></i></span>
                                                            <span class="todo-handler" v-on:click="handleTodo(rt.id, 1)" v-if=" rt.status == '0' "><i class="fa fa-lg fa-square-o"></i></span>
                                                            <label class="todo-title">{{ rt.title }}</label>
                                                        </div>
                                                        <div class="interviewers">
                                                            <i class="fa fa-lg fa-user"></i>&nbsp;<?php _e('Todo handlers : ', 'wp-erp-rec'); ?>{{ rt.display_name }}
                                                        </div>
                                                        <div class="interview_time">
                                                            <span title="click to undo" class="todo-status-done-button" v-if=" rt.status == '1' ">
                                                                <i class="fa fa-lg fa-check"></i><?php _e('Done', 'wp-erp-rec'); ?>
                                                            </span>
                                                            <span title="click to undo" class="todo-status-overdue-button" v-if=" rt.is_overdue == '1' ">
                                                                <?php _e('Overdue', 'wp-erp-rec'); ?>
                                                            </span>
                                                            <i class="fa fa-lg fa-clock-o"></i>&nbsp;<span>{{ rt.deadline_date }}</span>
                                                            <span class="todo-delete" v-on:click="deleteTodo(rt.id)"><i class="fa fa-lg fa-trash-o"></i></span>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <?php if ( $hire_status == 0 && $application_status != 'rejected' ) : ?>
                                                <button id="new-todo" style="margin-right:1%" class="button button-primary alignright"><?php _e('Add To-Do', 'wp-erp-rec');?></button>
                                                <?php endif;?>
                                                <span class="spinner"></span>
                                            </div>
                                        </section>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /.wrap -->