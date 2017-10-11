<div class="row">
  <?php $application_id = $_GET['application_id'];?>
  <div class="col-lg-12 form-group">
    <div class="input-group">
      <label class="input-group-btn">
            <span class="btn btn-primary">
                <?php _e('Upload CV', 'wp-erp-rec'); ?>&hellip; <input type="file" style="display:none;" name="erp_rec_file" id="erp_rec_file" required />
            </span>
        </label>
      <input type="text" name="erp_rec_file_label" id="erp_rec_file_label" class="form-control" readonly>
    </div>
  </div>
</div>

<input type="hidden" id="attachment_application_id" name="attachment_application_id" value="">
<input type="hidden" id="attachment_applicant_id" name="attachment_applicant_id" value="">

<script>
  jQuery(function() {
    jQuery(document).on('change', ':file', function() {
      var input = jQuery(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
    });

    jQuery(document).ready(function() {
      jQuery(':file').on('fileselect', function(event, numFiles, label) {
        jQuery(this).parents('.input-group').find(':text').val(label);
      });
    });

  });

</script>
