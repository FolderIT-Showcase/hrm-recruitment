<?php
global $wpdb;
$term_table_name = $wpdb->prefix . "erp_application_terms";

$rows = $wpdb->get_results("SELECT id,name,slug FROM {$term_table_name} ORDER BY name");
?>

<div class="wrap" id="comms-dashboard" v-cloak>
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="row">
        <div class="col-md-12">
          <h2 class="panel-title pull-left"><?php _e('Comms', 'wp-erp-rec'); ?></h2>
          <div class="input-group pull-right">
            <div class="btn-toolbar">
              <span class="spinner pull-left" v-if="loading === true" style="visibility:visible;"></span>
              <div class="btn-group btn-group-sm pull-right" style="display:none;">
                <button class="btn btn-default" type="button" v-on:click="getAllComms" :disabled="true"><?php _e('Get All Comms', 'wp-erp-rec'); ?></button>
                <button class="btn btn-default" type="button" v-on:click="remapApplications" :disabled="true"><?php _e('Remap Missing Applications', 'wp-erp-rec'); ?></button>
                <button class="btn btn-default" type="button" v-on:click="remapForce" :disabled="true"><?php _e('Force Remap', 'wp-erp-rec'); ?></button>
              </div>
              <div class="input-group input-group-sm pull-right" style="display:none;">
                <input type="number" class="form-control pull-right" style="width:auto;" name="application_id" min="0" :disabled="true" placeholder="<?php _e("Application ID", "wp-erp-rec"); ?>" id="application_id">
                <span class="input-group-btn"><button class="btn btn-default" type="button" v-on:click="retrieveEmails" :disabled="true"><?php _e('Retrieve Emails', 'wp-erp-rec'); ?></button></span>
              </div>
              <div class="btn-group btn-group-sm pull-right">
                <button class="btn btn-default btn-labeled" type="button" v-on:click="retrieveAllEmails" :disabled="loading === true"><span class="btn-label" style="padding:3px 11px;font-size:14px;"><i class="fa fa-refresh"></i></span><?php _e('Retrieve All Comms', 'wp-erp-rec'); ?></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="panel-body" style="padding-top:0px;">
      <div class="row">
        <div class="col-md-12 text-center">
          <h5 class="pull-left" style="position:absolute;left:20px;bottom:0px;"><?php _e("Last Update:","wp-erp-rec");?> {{last_update | formatDate}}</h5>
          <ul id="pager-comms" class="pagination pagination-lg"></ul>
          <h5 class="pull-right" style="position:absolute;right:20px;bottom:0px;">{{comms.length}} <?php _e("Comms","wp-erp-rec");?></h5>
        </div>
        <!--        <div class="col-md-12 application-comms-list scrollable scrollable-lg">-->
        <div class="col-md-12 application-comms-list">
          <div class="panel-group ng-scope" id="accordion-comms" style="margin-bottom:0px;">
            <div class="panel panel-default panel-comms" v-for="(index, comm) in comms">
              <template v-if="comm.application_id">
                <a class="btn btn-sm btn-default pull-right" type="button" target="_blank" href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=applicant_detail&application_id='); ?>{{ comm.application_id }}" style="margin:10px;z-index:99;position:relative;"><?php _e('Go to Application', 'wp-erp-rec'); ?></a>
              </template>
              <div class="panel-heading" v-bind:class="{ collapsed: (index!==0) }" data-toggle="collapse" data-target="#comm-collapse-{{comm.id}}" data-parent="#accordion-comms">
                <div class="row" style="position:relative;padding-left:22px;">
                  <span class="pull-left comm-direction" v-bind:class="[ comm.comm_uid ? 'in' : 'out' ]"><i v-bind:class="[ comm.comm_uid ? 'fa-angle-double-left' : 'fa-angle-double-right' ]" class="fa fa-lg"></i></span>
                  <template v-if="comm.comm_from_raw">
                    <div class="col-xl-4 col-lg-12" style="width:auto;">
                      <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('From: ','wp-erp-rec'); ?></b> <span>{{ comm.comm_from_raw }}</span></h5>
                    </div>
                  </template>
                  <template v-else="comm.comm_from">
                    <div class="col-xl-4 col-lg-12" style="width:auto;">
                      <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('From: ','wp-erp-rec'); ?></b>{{ comm.comm_author }} <span>({{ comm.comm_from }})</span></h5>
                    </div>
                  </template>

                  <template v-if="comm.comm_to_raw">
                    <div class="col-xl-4 col-lg-12" style="width:auto;" v-if="comm.comm_to_raw">
                      <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('To: ','wp-erp-rec'); ?></b> <span>{{ comm.comm_to_raw }} </span></h5>
                    </div>
                  </template>
                  <template v-else>
                    <div class="col-xl-4 col-lg-12" style="width:auto;" v-else-if="comm.comm_to">
                      <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('To: ','wp-erp-rec'); ?></b>{{ comm.comm_to_name }} <span>({{ comm.comm_to }})</span></h5>
                    </div>
                  </template>

                  <template v-if="comm.comm_cc_raw">
                    <div class="col-xl-4 col-lg-12" style="width:auto;">
                      <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('CC: ','wp-erp-rec'); ?></b> <span>{{ comm.comm_cc_raw }} </span></h5>
                    </div>
                  </template>
                  <template v-else>
                    <template v-if="comm.comm_cc">
                      <div class="col-xl-4 col-lg-12" style="width:auto;">
                        <h5 class="panel-heading-overflow" style="margin-top:0px;"><b class="fn"><?php _e('CC: ','wp-erp-rec'); ?></b>{{ comm.comm_cc_name }} <span>({{ comm.comm_cc }})</span></h5>
                      </div>
                    </template>
                  </template>
                </div>                            
                <div class="row">
                  <div class="comm-subject col-lg-12">
                    <h6 class="panel-heading-overflow" style="margin-top:4px;margin-bottom:0px;"><b class="fn">{{ comm.comm_subject }}</b></h6>
                    <span class="pull-right comm-date" style="margin-top:4px;"><b class="fn">{{ comm.comm_date | formatDate }}</b></span>
                  </div>
                </div>
              </div>
              <div id="comm-collapse-{{comm.id}}" class="panel-collapse collapse" v-bind:class="{ in: (index===0) }">
                <div class="panel-body" style="max-height:600px;overflow-y:auto;">
                  <div style="white-space:pre-line;line-height:1.2;" v-html="comm.comm_message"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="panel-footer clearfix">
      <div class="input-group input-group-sm">
        <span class="input-group-btn">
          <button class="btn btn-default pull-right" type="button" style="display:none;" :disabled="true"><?php _e('Send Email', 'wp-erp-rec'); ?></button>
        </span>
      </div>
    </div>
  </div>
</div>