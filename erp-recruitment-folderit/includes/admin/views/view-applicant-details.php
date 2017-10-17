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
$status = erp_rec_get_hiring_status();
$hire_status           = 0;
$jobid                 = 0;
$application_status    = '';
$applicant_id          = 0;

if ( isset($applicant_information[0]) ) {
    $hire_status          = $applicant_information[0]['status'];
    $attachment_url       = wp_get_attachment_url($applicant_information[0]['other']);
    $applicant_id         = $applicant_information[0]['applicant_id'];
    $attachments          = erp_people_get_meta($applicant_id, 'attach_id', false);
    $attach_id = '';
    if(count($attachments)>0) {
      rsort($attachments);
      $attach_id = $attachments[0];
    }
    $jobid                = $applicant_information[0]['job_id'];
    $application_status   = erp_people_get_meta($applicant_id, 'status');
    $application_stage_id = $applicant_information[0]['stage'];
    $application_stage_title = $applicant_information[0]['title'];
    $default_internal_type_id = erp_rec_get_app_interview_type_default();
}
?>
    <nav class="navbar navbar-default navbar-static-top" style="margin-left:-20px;padding-left:10px;margin-bottom:-10px;z-index:99;">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only"><?php _e('Toggle Navigation', 'wp-erp-rec'); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand pull-left" href="#">
            <?php _e('Sections', 'wp-erp-rec'); ?>
          </a>
        </div>
        <div id="navbar" class="navbar-right navbar-collapse collapse" aria-expanded="false" style="height: 1px;">
          <ul class="nav navbar-nav">
            <li>
              <a href="#section-personal-info" class="list-item-scroller">
                <?php _e('Personal Information', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-resume" class="list-item-scroller">
                <?php _e('Resume', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-exam-detail" class="list-item-scroller">
                <?php _e('Exam Detail', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-comment" class="list-item-scroller">
                <?php _e('Comments', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-rating" class="list-item-scroller">
                <?php _e('Rating', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-interview" class="list-item-scroller">
                <?php _e('Interview', 'wp-erp-rec'); ?>
              </a>
            </li>
            <li>
              <a href="#section-todo" class="list-item-scroller">
                <?php _e('Todo', 'wp-erp-rec'); ?>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div id="candidate-detail" class="wrap erp erp-applicant-detail wp-erp-wrap" style="margin-top:20px;">
      <div id="dashboard-widgets-wrap" class="">
        <div class="row">
          <div class="col-lg-12">
            <input type="hidden" id="application_stage_id" name="application_stage_id" value="<?php echo $application_stage_id; ?>" />
            <input type="hidden" id="application_stage_title" name="application_stage_title" value="<?php echo $application_stage_title; ?>" />
            <input type="hidden" id="default_internal_type_id" name="default_internal_type_id" value="<?php echo $default_internal_type_id; ?>" />

            <div class="row">
              <div class="col-lg-12">
                <div class="panel panel-default">

                  <div class="panel-heading">
                    <h4>
                      <?php _e('Applicant Details', 'wp-erp-rec'); ?>
                    </h4>
                  </div>

                  <div class="panel-body">
                    <div class="col-md-2 col-xs-12 col-sm-6 col-lg-2">
                      <!--                    <img alt="User Pic" src="https://x1.xingassets.com/assets/frontend_minified/img/users/nobody_m.original.jpg" id="profile-image1" class="img-circle img-responsive">-->
                      <?php
                        $email_address = isset($applicant_information[0]['email']) ? $applicant_information[0]['email'] : '';
                        echo get_avatar($email_address, 500, '', false, ['class' => 'img-circle img-responsive']);
                        ?>
                    </div>
                    <div class="col-md-10 col-xs-12 col-sm-6 col-lg-10">
                      <div class="container">
                        <div class="row">
                          <div class="col-lg-12">
                            <h2>
                              <?php echo isset($applicant_information[0]['first_name']) ? ucfirst($applicant_information[0]['first_name']) : ''; ?>
                              <?php echo isset($applicant_information[0]['last_name']) ? ucfirst($applicant_information[0]['last_name']) : ''; ?>
                            </h2>
                            <p>
                              <?php _e('Opening', 'wp-erp-rec'); ?>: <b><?php echo strtoupper( get_the_title( $applicant_information[0]['job_id'] ) ); ?></b></p>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="container">
                        <div class="row">
                          <ul class="details">
                            <li>
                              <p><span class="glyphicon glyphicon-flag one" style="width:30px;"></span>
                                <?php _e('Stage : ', 'wp-erp-rec'); ?>
                                <?php echo erp_rec_get_app_stage($application_id);?>
                              </p>
                            </li>
                            <li>
                              <p><span class="glyphicon glyphicon-star one" style="width:30px;"></span>
                                <?php _e('Rating : ', 'wp-erp-rec'); ?>{{ avgRating }}/5</p>
                            </li>
                            <li>
                              <p><span class="glyphicon glyphicon-info-sign one" style="width:30px;"></span>
                                <?php _e('Status : ', 'wp-erp-rec'); ?>
                                <?php echo ( erp_people_get_meta($applicant_id, 'status', true) != "" ) ? ucfirst( str_replace( "_", " ", $status[erp_people_get_meta($applicant_id, 'status', true )] ) ) : __('No status set', 'wp-erp-rec') ; ?>
                              </p>
                            </li>
                          </ul>
                        </div>
                      </div>
                      <hr>
                      <div class="container">
                        <div class="row">
                          <div class="col-lg-12">
                            <div class="btn-group <?php echo ( $hire_status == 1 || $application_status == 'rejected' ) ? 'button-actions-hired' : '';?>">
                              <?php if ( $hire_status == 0 && $application_status != 'rejected' ) : ?>
                              <button class="btn btn-default btn-interview"><i class="fa fa-lg fa-calendar"></i>&nbsp;<?php _e('New Interview', 'wp-erp-rec'); ?></button>
                              <button class="btn btn-default btn-todo"><i class="fa fa-lg fa-list-alt"></i>&nbsp;<?php _e('New To-do', 'wp-erp-rec'); ?></button>
                              <button class="btn btn-default btn-attach-cv"><i class="fa fa-lg fa-paperclip"></i>&nbsp;<?php _e('Attach CV', 'wp-erp-rec'); ?></button>
                              <?php endif;?>
                              <?php
                            if ( $hire_status == 0 && $application_status != 'rejected' ) {
                                $make_employee_url = admin_url('admin.php?page=make_employee&application_id=' . $application_id);
                                echo sprintf( '<button id="make_him_employee" class="btn btn-default" href="%s"><i class="fa fa-lg fa-user-plus"></i>%s</button>', $make_employee_url, __( 'Hire', 'wp-erp-rec' ) );
                            }
                            ?>
                                <?php if ( isset($attach_id) && $attach_id != '' ) : ?>
                                <button class="btn btn-default" href="<?php echo wp_get_attachment_url($attach_id); ?>"><i class="fa fa-lg fa-file"></i>&nbsp;<?php _e('View CV', 'wp-erp-rec'); ?></button>
                                <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="container">
                        <div class="row">
                          <div class="col-lg-12">
                            <?php if ( $hire_status == 0 ) : ?>
                            <form class="form-inline">
                              <span class="spinner"></span>
                              <div class="input-group">
                                <?php $stages = erp_rec_get_application_stages($application_id); ?>
                                <select class="form-control" id="change_stage" name="change_stage" v-model="stage_id">
                              <option value="none" selected><?php _e('&mdash; Change Stage &mdash;', 'wp-erp-rec');?></option>
                              <?php foreach ( $stages as $value ) : ?>
                              <option value="<?php echo $value['stageid']; ?>"><?php echo $value['title']; ?></option>
                              <?php endforeach; ?>
                            </select>
                                <span class="input-group-btn">
                            <button class="btn btn-default" style="padding-top:3px;padding-bottom:3px;" v-on:click="changeStage"><?php _e('Move', 'wp-erp-rec'); ?></button>
                            </span>
                              </div>
                              <div class="input-group">
                                <?php $status = erp_rec_get_hiring_status(); ?>
                                <select class="form-control" id="change_status" name="change_status" v-model="status_name">
                              <option value="none" selected><?php _e('&mdash; Change Status &mdash;', 'wp-erp-rec');?></option>
                              <?php foreach ( $status as $key => $value ) : ?>
                              <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                              <?php endforeach; ?>
                            </select>
                                <span class="input-group-btn">
                            <button class="btn btn-default" style="padding-top:3px;padding-bottom:3px;" v-on:click="changeStaus"><?php _e('Done', 'wp-erp-rec'); ?></button>
                            </span>
                              </div>
                            </form>
                            <?php endif;?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-personal-info" class="postbox section-personal-info">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Candidate Profile', 'wp-erp-rec'); ?></span></h2>
              </div>
              <div class="section-content toggle-metabox-show full-width">
                <div class="col-lg-12">
                  <dl class="dl-custom dl-horizontal">
                    <h5>
                      <?php _e('Personal Information', 'wp-erp-rec'); ?>
                    </h5>
                    <dt><?php _e('Name', 'wp-erp-rec'); ?></dt>
                    <dd>
                      <?php echo isset($applicant_information[0]['first_name']) ? esc_html( $applicant_information[0]['first_name'] ) : ''; ?>
                      <?php echo isset($applicant_information[0]['last_name']) ? esc_html( $applicant_information[0]['last_name'] ) : ''; ?>
                    </dd>

                    <dt><?php _e('Email', 'wp-erp-rec'); ?></dt>
                    <dd>
                      <?php echo isset($applicant_information[0]['email']) ? esc_html( $applicant_information[0]['email'] ) : ''; ?>
                    </dd>

                    <?php $db_personal_fields = get_post_meta( $jobid, '_personal_fields', true );?>
                    <?php foreach ( $db_personal_fields as $personal_data ) : ?>
                    <?php $field_name = json_decode($personal_data)->field;?>
                    <dt><?php echo ucfirst(str_replace("_"," ",$field_name));?></dt>
                    <dd>
                      <?php
                        $value = erp_people_get_meta($applicant_id, $field_name, true);
                        $value = stripslashes( $value );
                        echo esc_html( $value );
                      ?>
                    </dd>
                    <?php endforeach;?>
                  </dl>
                </div>
              </div>
            </section>
          </div>
          <div class="col-lg-6 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-comment" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Comments', 'wp-erp-rec'); ?></span></h2>
              </div>
              <div class="section-content toggle-metabox-show full-width">
                <div id="comment_form_wrapper" class="not-loaded">
                  <form id="applicant-comment-form" method="post">
                    <div class="col-lg-12">
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="row">
                            <div id="input_comment_wrapper">
                              <ul>
                                <li>
                                  <textarea class="widefat" rows="5" id="manager_comment" name="manager_comment" v-model="manager_comment"></textarea>
                                </li>
                              </ul>
                              <?php wp_nonce_field('wp_erp_rec_applicant_comment_nonce'); ?>
                              <input type="hidden" name="admin_user_id" value="<?php echo get_current_user_id(); ?>" id="comment_admin_user_id">
                              <input type="hidden" name="application_id" value="<?php echo $application_id; ?>" id="application_id">
                              <input type="hidden" name="applicant_id" value="<?php echo $applicant_id; ?>" id="applicant_id">
                              <input type="hidden" name="action" value="wp-erp-rec-manager-comment" />
                              <input class="page-title-action alignright button button-primary" type="button" v-on:click="postManagerComment" name="submit" value="Submit" style="margin-bottom:10px;" />
                              <span class="spinner"></span>

                              <div v-bind:class="[ isError ? error_notice_class : success_notice_class ]" v-show="isVisible">{{ response_message }}</div>
                            </div>
                          </div>

                          <ul class="application-comment-list">
                            <li class="comment thread-even depth-1">
                              <article v-for="cmnt in comments">
                                <div class="row">
                                  <div class="col-xs-2 col-md-2">
                                    <!--                                  {{{ cmnt.user_pic }}}-->
                                    <?php echo get_avatar("{{ cmnt.ID }}", 100, '', false, ['class' => 'img-rounded img-responsive']); ?>
                                  </div>
                                  <div class="col-xs-10 col-md-10" style="padding-left:0px;">
                                    <h6 style="margin-top:4px;"><b class="fn">{{ cmnt.display_name }}</b> {{ cmnt.comment_date }}</h6>
                                    <p>{{ cmnt.comment }}</p>
                                  </div>
                                </div>
                                <hr>
                              </article>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </section>
          </div>
          <div class="col-lg-6 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-rating" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Ratings', 'wp-erp-rec'); ?></span></h2>
              </div>
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
                    <input type="hidden" name="action" value="wp-erp-rec-manager-rating" />
                    <input class="page-title-action alignright button button-primary" v-show="false" type="submit" name="submit" value="Save" />
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
          </div>
          <div class="col-lg-6 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-exam-detail" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Exam Detail', 'wp-erp-rec'); ?></span></h2>
              </div>
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
          </div>
          <div class="col-lg-6 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-todo" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('To-Do List', 'wp-erp-rec'); ?></span></h2>
              </div>
              <div class="section-content toggle-metabox-show full-width">
                <h3 class="no-interview-todo-caption" v-if="hasTodo">
                  <?php _e('No To-do set', 'wp-erp-rec');?>
                </h3>
                <ul id="calendar_list" class="calendar-list not-loaded">
                  <li class="calendar-list-item" v-for="rt in todoData">
                    <div class="interview_type">
                      <span class="todo-handler" v-on:click="handleTodo(rt.id, 0)" v-if=" rt.status == '1' "><i class="fa fa-lg fa-check-square-o"></i></span>
                      <span class="todo-handler" v-on:click="handleTodo(rt.id, 1)" v-if=" rt.status == '0' "><i class="fa fa-lg fa-square-o"></i></span>
                      <label class="todo-title">{{ rt.title }}</label>
                    </div>
                    <div class="interviewers">
                      <i class="fa fa-lg fa-user"></i>&nbsp;
                      <?php _e('Todo handlers : ', 'wp-erp-rec'); ?>{{ rt.display_name }}
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
          <div class="col-lg-12 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-interview" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Interviews', 'wp-erp-rec'); ?></span></h2>
              </div>
              <div class="section-content toggle-metabox-show full-width">
                <h3 class="no-interview-todo-caption" v-if="hasInterview">
                  <?php _e('No interview set', 'wp-erp-rec');?>
                </h3>
                <div class="col-lg-12 calendar-list not-loaded">
                  <div class="panel panel-default calendar-list-item" v-for="rt in interviewData">
                    <div class="panel-heading clearfix">
                      <h4 class="panel-title pull-left" id="interview-type-title-{{rt.id}}">{{ rt.type_detail }}</h4>
                      <div class="btn-group pull-right">
                        <button class="btn btn-primary btn-sm" v-on:click="feedbackInterview(rt.id)">
                                    <input id="interviewfeedbackid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                    <i class="fa fa-lg fa-comment-o"></i>&nbsp;<?php _e('Feedback','wp-erp-rec');?>
                                </button>
                        <button class="btn btn-primary btn-sm" v-on:click="editInterview(rt.id)">
                                    <input id="intervieweditid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                    <i class="fa fa-lg fa-pencil"></i>&nbsp;<?php _e('Edit','wp-erp-rec');?>
                                </button>
                        <button class="btn btn-danger btn-sm" v-on:click="deleteInterview(rt.id)">
                                    <input id="interviewid-{{rt.id}}" type="hidden" value="{{rt.id}}">
                                    <i class="fa fa-lg fa-trash"></i>&nbsp;<?php _e('Delete','wp-erp-rec');?>
                                </button>
                      </div>
                    </div>

                    <div class="panel-body">
                      <div class="row">
                        <div class="col-lg-6">
                          <ul class="list-group">
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-th-list"></i>&nbsp;
                              <?php _e('Stage : ', 'wp-erp-rec'); ?>
                              <span id="interview-stage-text-{{rt.id}}">{{ rt.title }}</span>
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-user"></i>&nbsp;
                              <?php _e('Interviewers : ', 'wp-erp-rec'); ?>
                              <span id="interviewers-display-name-{{rt.id}}">{{ rt.display_name }}</span>
                            </li>
                            <li class="list-group-item"><i class="fa fa-lg fa-clock-o"></i>&nbsp;
                              <?php _e('Date and Time : ', 'wp-erp-rec'); ?>{{ rt.interview_time }}
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-th-list"></i>&nbsp;
                              <?php _e('Detail : ', 'wp-erp-rec'); ?>
                              <span id="interview-detail-text-{{rt.id}}">{{ rt.interview_detail }}</span>
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-th-list"></i>&nbsp;
                              <?php _e('Techs : ', 'wp-erp-rec'); ?>
                              <span id="interview-tech-text-{{rt.id}}">{{ rt.interview_tech }}</span>
                            </li>
                          </ul>
                        </div>

                        <div class="col-lg-6">
                          <ul class="list-group">
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-comments-o"></i>&nbsp;
                              <?php _e('Feedback English Level : ', 'wp-erp-rec'); ?>
                              <span id="feedback-english-level-text-{{rt.id}}">{{ rt.feedback_english_level }}</span>
                            </li>
                            <li class="list-group-item">
                              <i class="fa fa-lg fa-comments-o"></i>&nbsp;
                              <?php _e('Feedback English Conversation : ', 'wp-erp-rec'); ?>
                              <span id="feedback-english-conversation-text-{{rt.id}}">{{ rt.feedback_english_conversation }}</span>
                            </li>
                          </ul>
                        </div>

                        <div class="col-lg-12">
                          <ul class="list-group">
                            <li class="list-group-item">
                              <p>
                                <i class="fa fa-lg fa-comments-o"></i>&nbsp;
                                <?php _e('Feedback Comment : ', 'wp-erp-rec'); ?>
                              </p>
                              <textarea readonly class="form-control" rows="5" id="feedback-comment-text-{{rt.id}}">{{ rt.feedback_comment }}</textarea>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <input type="hidden" id="interview-stage-id-{{rt.id}}" value="{{rt.interview_type_id}}">
                    <input type="hidden" id="interview-interviewers-ids-{{rt.id}}" value="{{rt.interviewers_ids}}">
                    <input type="hidden" id="interview-datetime-{{rt.id}}" value="{{rt.interview_datetime}}">
                    <input type="hidden" id="interview-date-{{rt.id}}" value="{{rt.interview_date}}">
                    <input type="hidden" id="interview-time-{{rt.id}}" value="{{rt.interview_timee}}">
                    <input type="hidden" id="interview-duration-min-{{rt.id}}" value="{{rt.duration}}">
                    <input type="hidden" id="interview-type-id-{{rt.id}}" value="{{rt.type_id}}">
                  </div>
                </div>

                <?php if ( $hire_status == 0 && $application_status != 'rejected' ) : ?>
                <button class="button button-primary alignright btn-interview"><?php _e('New Interview', 'wp-erp-rec');?></button>
                <?php endif;?>
                <span class="spinner"></span>
              </div>
            </section>
          </div>
          <div class="col-lg-12 single-information-container meta-box-sortables ui-sortable" style="margin-left:0px;">
            <section id="section-resume" class="postbox">
              <span class="hndle-toogle-button"></span>
              <div class="section-header">
                <h2 class="hndle"><span><?php _e('Resumes', 'wp-erp-rec'); ?></span></h2>
              </div>
              <div class="section-content toggle-metabox-show">
                <?php if ( isset($attach_id) ) : ?>

                <?php if ( count($attachments) > 0 ) : ?>
                <?php foreach ( $attachments as $cv_id ) : ?>
                <p><i class="fa fa-lg fa-file"></i>&nbsp;
                  <?php echo get_the_date('', $cv_id) . ' ' . get_the_time('', $cv_id) . ' - '; ?>
                  <a class="" href="<?php echo wp_get_attachment_url($cv_id); ?>">
                    <?php
                          echo basename(get_attached_file($cv_id));
                          ?>
                  </a>
                </p>
                <?php endforeach; ?>
                <?php endif; ?>

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
                <p>
                  <?php _e('No Resume Found!','wp-erp-rec');?>
                </p>
                <?php endif; ?>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
