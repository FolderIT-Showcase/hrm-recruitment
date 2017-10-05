<form method="post" enctype="multipart/form-data">
  <div class="wrap erp">
    <div class="cv-upload-template-container">
      <?php $application_id = $_GET['application_id'];?>
      <div class="row">
        <div class="popuplside">
          <p>
            <?php _e('Upload CV', 'wp-erp-rec'); ?>
          </p>
          <input type="file" class="inputclass reqc" name="erp_rec_file" id="erp_rec_file" required />
        </div>
        <div class="popuprside">

        </div>
      </div>

      <input type="hidden" id="attachment_application_id" name="attachment_application_id" value="">
      <input type="hidden" id="attachment_applicant_id" name="attachment_applicant_id" value="">
    </div>
  </div>
</form>
