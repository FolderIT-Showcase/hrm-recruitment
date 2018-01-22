<?php
global $wpdb;
$term_table_name = $wpdb->prefix . "erp_application_terms";

$rows = $wpdb->get_results("SELECT id,name,slug FROM {$term_table_name} ORDER BY name");
?>

<div class="wrap">
  <h1><?php _e('Terms', 'wp-erp-rec');?></h1>
  <?php if ($_POST['delete']) : ?>
  <div><p><?php echo $term_name . ' - ' . __('Term deleted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <?php if ($_POST['insert']) : ?>
  <div><p><?php echo $term_name . ' - ' . __('Term inserted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <div class="postbox">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12" style="margin:10px 0px;">
          <table id="terms_table" class='wp-list-table widefat fixed striped posts table table-responsive'>
            <thead>
              <tr>
                <th><?php _e('Term Name','wp-erp-rec'); ?></th>
                <th><?php _e('Term Slug','wp-erp-rec'); ?></th>
                <th><?php _e('Actions','wp-erp-rec'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr id="term_new">
                <form class="form-terms-new" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                  <?php wp_nonce_field('wp_erp_rec_term_create_nonce', '_term_create_nonce'); ?>
                  <td><input type="text" class="form-control" name="term_name" value="" required/></td>
                  <td><input type="text" class="form-control" name="term_slug" value="" disabled/></td>
                </form>
                <td><input type="button" value="<?php _e('Insert', 'wp-erp-rec'); ?>" class="button button-primary button-new"/></td>
              </tr>
              <?php foreach ($rows as $row) : ?>
              <tr id="term_id_<?php echo $row->id; ?>">
                <form method="post" class="form-terms">
                  <input type="hidden" name="term_id" value="<?php echo $row->id; ?>">
                  <input type="hidden" name="term_old_slug" value="<?php echo $row->slug; ?>"/>
                  <?php wp_nonce_field('wp_erp_rec_term_nonce', '_term_nonce'); ?>
                  <td><input type="text" class="form-control form-control-nobg" name="term_name" defaultValue="<?php echo $row->name; ?>" value="<?php echo $row->name; ?>" disabled required/></td>
                  <td><input type="text" class="form-control form-control-nobg" name="term_slug" value="<?php echo $row->slug; ?>" disabled/></td>
                </form>
                <td>
                  <input type="button" value='<?php _e('Edit', 'wp-erp-rec'); ?>' class='button button-enable'>
                  <input type="button" value='<?php _e('Save', 'wp-erp-rec'); ?>' class='button-primary button-update' style="display:none;">
                  <input type="button" value='<?php _e('Cancel', 'wp-erp-rec'); ?>' class='button button-cancel' style="display:none;">
                  <input type="button" value='<?php _e('Delete', 'wp-erp-rec'); ?>' class='button button-danger button-remove'>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <script>
            $(document).on("click", ".button-new", function () {
              var that = $(this);
              var formData = that.parent().parent().find("input").serialize();
              var nonce = that.parent().parent().find('input[name="_term_create_nonce"]').val();

              that.prop("disabled", true);
              that.parent().parent().find("[name='term_name']").prop("disabled", true);

              wp.ajax.send('erp-rec-create-term', {
                data: {
                  fdata: formData,
                  op: 'create',
                  _wpnonce: nonce
                },
                success: function (res) {
                  res = $.parseJSON(res);
                  alertify.success(res.message);

                  $newRow = '<tr id="term_id_'+res.data.term_id+'"><form method="post" class="form-terms">';
                  $newRow += '<input type="hidden" name="term_id" value="'+res.data.term_id+'">';
                  $newRow += '<input type="hidden" name="term_old_slug" value="'+res.data.term_slug+'">';
                  $newRow += '<?php wp_nonce_field('wp_erp_rec_term_nonce', '_term_nonce'); ?>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="term_name" defaultValue="'+res.data.term_name+'" value="'+res.data.term_name+'" disabled required/></td>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="term_slug" value="'+res.data.term_slug+'" disabled/></td></form>';
                  $newRow += '<td>';
                  $newRow += '<input type="button" value="<?php _e('Edit', 'wp-erp-rec'); ?>" class="button button-enable" style="margin-right:4px;">';
                  $newRow += '<input type="button" value="<?php _e('Save', 'wp-erp-rec'); ?>" class="button-primary button-update" style="margin-right:4px;display:none;">';
                  $newRow += '<input type="button" value="<?php _e('Cancel', 'wp-erp-rec'); ?>" class="button button-cancel" style="margin-right:4px;display:none;">';
                  $newRow += '<input type="button" value="<?php _e('Delete', 'wp-erp-rec'); ?>" class="button button-danger button-remove" style="margin-right:4px;">';
                  $newRow += '</td></tr>';

                  $('#terms_table tr').eq(1).after($newRow);

                  that.prop("disabled", false);
                  that.parent().parent().find("[name='term_name']").prop("disabled", false).val('');
                },
                error: function (error) {
                  alert(error);

                  that.prop("disabled", false);
                  that.parent().parent().find("[name='term_name']").prop("disabled", false);
                }
              });
            });

            $(document).on("click", ".button-cancel", function () {
              var that = $(this);
              that.hide();
              that.siblings("input.button-primary.button-update").hide();
              that.siblings("input.button.button-remove").show();
              that.siblings("input.button.button-enable").show();

              that.parent().parent().find("[name='term_name']").prop("disabled", true);
              that.parent().parent().find("[type='text']").addClass("form-control-nobg");

              // Restaurar valores originales de campos
              that.parent().parent().find("[name='term_name']").val($(this).parent().parent().find("[name='term_name']").attr("defaultvalue"));
            });

            $(document).on("click", ".button-remove", function () {
              if(!confirm("<?php _e('Please confirm Term deletion', 'wp-erp-rec');?>")) {
                return;
              }

              var that = $(this);
              var formData = that.parent().parent().find("input").serialize();
              var nonce = that.parent().parent().find('input[name="_term_nonce"]').val();
              var termId = that.parent().parent().find('input[name="term_id"]').val();

              that.prop("disabled", true);
              that.siblings().prop("disabled", true);  

              wp.ajax.send('erp-rec-delete-term', {
                data: {
                  fdata: formData,
                  op: 'delete',
                  _wpnonce: nonce
                },
                success: function (res) {
                  alertify.success(res);

                  // Quitar fila
                  $("#term_id_"+termId).remove();
                  that.prop("disabled", false);
                  that.siblings().prop("disabled", false);
                },
                error: function (error) {
                  alert(error);
                  that.prop("disabled", false);
                  that.siblings().prop("disabled", false);
                }
              });
            });

            $(document).on("click", ".button-enable", function () {
              var that = $(this);
              that.hide();
              that.siblings("input.button.button-remove").hide();
              that.siblings("input.button.button-cancel").show();
              that.siblings("input.button-primary.button-update").show();

              that.parent().parent().find("[name='term_name']").prop("disabled", false);
              that.parent().parent().find("[type='text']").removeClass("form-control-nobg");
            });

            $(document).on("click", ".button-update", function () {
              var that = $(this);
              var formData = that.parent().parent().find("input").serialize();
              var nonce = that.parent().parent().find('input[name="_term_nonce"]').val();

              that.parent().parent().find("[name='term_name']").prop("disabled", true);
              that.prop("disabled", true);
              that.siblings().prop("disabled", true);             

              wp.ajax.send('erp-rec-update-term', {
                data: {
                  fdata: formData,
                  op: 'update',
                  _wpnonce: nonce
                },
                success: function (res) {
                  res = $.parseJSON(res);
                  alertify.success(res.message);

                  that.hide();
                  that.siblings("input.button.button-cancel").hide();
                  that.siblings("input.button.button-remove").show();
                  that.siblings("input.button.button-enable").show();

                  that.parent().parent().find("[name='term_name']").prop("disabled", true).attr("defaultvalue", res.data.term_name);
                  that.parent().parent().find("[name='term_slug']").val(res.data.term_slug);
                  that.parent().parent().find("[name='term_old_slug']").val(res.data.term_slug);
                  that.parent().parent().find("[type='text']").addClass("form-control-nobg");
                  that.prop("disabled", false);
                  that.siblings().prop("disabled", false);
                },
                error: function (error) {
                  alert(error);

                  that.parent().parent().find("[name='term_name']").prop("disabled", false);
                  that.parent().parent().find("[type='text']").removeClass("form-control-nobg");
                  that.prop("disabled", false);
                  that.siblings().prop("disabled", false);
                }
              });
            });
          </script>
        </div>
      </div>
    </div>
  </div>
</div>