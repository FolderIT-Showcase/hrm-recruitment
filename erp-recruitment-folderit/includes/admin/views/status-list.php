<?php
global $wpdb;
$status_table_name = $wpdb->prefix . "erp_application_status";

$rows = $wpdb->get_results("SELECT id,code,title,description,status_order FROM {$status_table_name} WHERE internal = 0 ORDER BY status_order");
?>

<div class="wrap">
  <h1><?php _e('Statuses', 'wp-erp-rec');?></h1>
  <?php if ($_POST['delete']) : ?>
  <div><p><?php echo $status_name . ' - ' . __('Status deleted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>
  <?php if ($_POST['insert']) : ?>
  <div><p><?php echo $status_name . ' - ' . __('Status inserted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>
  <div class="postbox">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12" style="margin:10px 0px;">
          <table id="statuses_table" class='wp-list-table widefat fixed striped posts table table-responsive'>
            <thead>
              <tr>
                <th><?php _e('Status Name','wp-erp-rec'); ?></th>
                <th><?php _e('Status Description','wp-erp-rec'); ?></th>
                <th><?php _e('Status Order','wp-erp-rec'); ?></th>
                <th><?php _e('Status Code','wp-erp-rec'); ?></th>
                <th><?php _e('Actions','wp-erp-rec'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr id="status_new">
                <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                  <?php wp_nonce_field('wp_erp_rec_status_create_nonce', '_status_create_nonce'); ?>
                  <td><input type="text" class="form-control" name="status_name" id="status_name" value="" required/></td>
                  <td><input type="text" class="form-control" name="status_description" id="status_description" value="" required/></td>
                  <td><input type="number" class="form-control" style="max-width:100px;" name="status_order" id="status_order" value="0" required/></td>
                  <td><input type="text" class="form-control" name="status_code" id="status_code" value="" disabled/></td>
                  <td><input type="submit" name="insert" value="<?php _e('Insert', 'wp-erp-rec'); ?>" class="button button-primary button-new"/></td>
                </form>
              </tr>
              <?php foreach ($rows as $row) : ?>
              <tr id="status_id_<?php echo $row->id; ?>">
                <form method="post" class="form-statuses" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                  <input type="hidden" name="status_id" value="<?php echo $row->id; ?>">
                  <input type="hidden" name="status_old_code" value="<?php echo $row->code; ?>">
                  <?php wp_nonce_field('wp_erp_rec_status_nonce', '_status_nonce'); ?>
                  <td><input type="text" class="form-control form-control-nobg" name="status_name" defaultValue="<?php echo $row->title; ?>" value="<?php echo $row->title; ?>" disabled required/></td>
                  <td><input type="text" class="form-control form-control-nobg" name="status_description" defaultValue="<?php echo $row->description; ?>" value="<?php echo $row->description; ?>" required disabled/></td>
                  <td><input type="number" class="form-control form-control-nobg" name="status_order" style="max-width:100px;" defaultValue="<?php echo $row->status_order; ?>" value="<?php echo $row->status_order; ?>" required disabled/></td>
                  <td><input type="text" class="form-control form-control-nobg" name="status_code" value="<?php echo $row->code; ?>" disabled/></td>
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
              var nonce = that.parent().parent().find('input[name="_status_create_nonce"]').val();

              that.prop("disabled", true);
              that.parent().parent().find("[name='status_name']").prop("disabled", true);
              that.parent().parent().find("[name='status_description']").prop("disabled", true);
              that.parent().parent().find("[name='status_order']").prop("disabled", true);

              wp.ajax.send('erp-rec-create-status', {
                data: {
                  fdata: formData,
                  op: 'create',
                  _wpnonce: nonce
                },
                success: function (res) {
                  res = $.parseJSON(res);
                  alertify.success(res.message);

                  $newRow = '<tr id="status_id_'+res.data.status_id+'"><form method="post" class="form-statuses">';
                  $newRow += '<input type="hidden" name="status_id" value="'+res.data.status_id+'">';
                  $newRow += '<input type="hidden" name="status_old_code" value="'+res.data.status_code+'">';
                  $newRow += '<?php wp_nonce_field('wp_erp_rec_status_nonce', '_status_nonce'); ?>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="status_name" defaultValue="'+res.data.status_name+'" value="'+res.data.status_name+'" disabled required/></td>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="status_description" defaultValue="'+res.data.status_description+'" value="'+res.data.status_description+'" disabled required/></td>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="status_order" defaultValue="'+res.data.status_order+'" value="'+res.data.status_order+'" disabled required/></td>';
                  $newRow += '<td><input type="text" class="form-control form-control-nobg" name="status_code" value="'+res.data.status_code+'" disabled/></td></form>';
                  $newRow += '<td>';
                  $newRow += '<input type="button" value="<?php _e('Edit', 'wp-erp-rec'); ?>" class="button button-enable" style="margin-right:4px;">';
                  $newRow += '<input type="button" value="<?php _e('Save', 'wp-erp-rec'); ?>" class="button-primary button-update" style="margin-right:4px;display:none;">';
                  $newRow += '<input type="button" value="<?php _e('Cancel', 'wp-erp-rec'); ?>" class="button button-cancel" style="margin-right:4px;display:none;">';
                  $newRow += '<input type="button" value="<?php _e('Delete', 'wp-erp-rec'); ?>" class="button button-danger button-remove" style="margin-right:4px;">';
                  $newRow += '</td></tr>';

                  $('#statuses_table tr').eq(1).after($newRow);

                  that.prop("disabled", false);
                  that.parent().parent().find("[name='status_name']").prop("disabled", false).val('');
                  that.parent().parent().find("[name='status_description']").prop("disabled", false).val('');
                  that.parent().parent().find("[name='status_order']").prop("disabled", false).val(0);
                },
                error: function (error) {
                  alert(error);

                  that.prop("disabled", false);
                  that.parent().parent().find("[name='status_name']").prop("disabled", false);
                  that.parent().parent().find("[name='status_description']").prop("disabled", false);
                  that.parent().parent().find("[name='status_order']").prop("disabled", false);
                }
              });
            });

            $(document).on("click", ".button-cancel", function () {
              var that = $(this);
              that.hide();
              that.siblings("input.button-primary.button-update").hide();
              that.siblings("input.button.button-remove").show();
              that.siblings("input.button.button-enable").show();

              that.parent().parent().find("[name='status_name']").prop("disabled", true);
              that.parent().parent().find("[name='status_description']").prop("disabled", true);
              that.parent().parent().find("[name='status_order']").prop("disabled", true);
              that.parent().parent().find("[type='text']").addClass("form-control-nobg");
              that.parent().parent().find("[type='number']").addClass("form-control-nobg");

              // Restaurar valores originales de campos
              that.parent().parent().find("[name='status_name']").val($(this).parent().parent().find("[name='status_name']").attr("defaultvalue"));
              that.parent().parent().find("[name='status_description']").val($(this).parent().parent().find("[name='status_description']").attr("defaultvalue"));
              that.parent().parent().find("[name='status_order']").val($(this).parent().parent().find("[name='status_order']").attr("defaultvalue"));
            });

            $(document).on("click", ".button-remove", function () {
              if(!confirm("<?php _e('Please confirm Status deletion', 'wp-erp-rec');?>")) {
                return;
              }

              var that = $(this);
              var formData = that.parent().parent().find("input").serialize();
              var nonce = that.parent().parent().find('input[name="_status_nonce"]').val();
              var statusId = that.parent().parent().find('input[name="status_id"]').val();

              that.prop("disabled", true);
              that.siblings().prop("disabled", true);  

              wp.ajax.send('erp-rec-delete-status', {
                data: {
                  fdata: formData,
                  op: 'delete',
                  _wpnonce: nonce
                },
                success: function (res) {
                  alertify.success(res);

                  // Quitar fila
                  $("#status_id_"+statusId).remove();
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

              that.parent().parent().find("[name='status_name']").prop("disabled", false);
              that.parent().parent().find("[name='status_description']").prop("disabled", false);
              that.parent().parent().find("[name='status_order']").prop("disabled", false);
              that.parent().parent().find("[type='text']").removeClass("form-control-nobg");
              that.parent().parent().find("[type='number']").removeClass("form-control-nobg");
            });

            $(document).on("click", ".button-update", function () {
              var that = $(this);
              var formData = that.parent().parent().find("input").serialize();
              var nonce = that.parent().parent().find('input[name="_status_nonce"]').val();

              that.parent().parent().find("[name='status_name']").prop("disabled", true);
              that.parent().parent().find("[name='status_description']").prop("disabled", true);
              that.parent().parent().find("[name='status_order']").prop("disabled", true);
              that.prop("disabled", true);
              that.siblings().prop("disabled", true);          

              wp.ajax.send('erp-rec-update-status', {
                data: {
                  fdata: formData,
                  op: 'update',
                  _wpnonce: nonce
                },
                success: function (res) {
                  res = $.parseJSON(res);
                  alertify.success(res["message"]);

                  that.hide();
                  that.siblings("input.button.button-cancel").hide();
                  that.siblings("input.button.button-remove").show();
                  that.siblings("input.button.button-enable").show();

                  that.parent().parent().find("[name='status_name']").prop("disabled", true).attr("defaultvalue", res.data.status_name);
                  that.parent().parent().find("[name='status_description']").prop("disabled", true).attr("defaultvalue", res.data.status_description);
                  that.parent().parent().find("[name='status_order']").prop("disabled", true).attr("defaultvalue", res.data.status_order);
                  that.parent().parent().find("[name='status_code']").val(res.data.status_code);
                  that.parent().parent().find("[name='status_old_code']").val(res.data.status_code);
                  that.parent().parent().find("[type='text']").addClass("form-control-nobg");
                  that.parent().parent().find("[type='number']").addClass("form-control-nobg");
                  that.prop("disabled", false);
                  that.siblings().prop("disabled", false);
                },
                error: function (error) {
                  alert(error);

                  that.parent().parent().find("[name='status_name']").prop("disabled", false);
                  that.parent().parent().find("[name='status_description']").prop("disabled", false);
                  that.parent().parent().find("[name='status_order']").prop("disabled", false);
                  that.parent().parent().find("[type='text']").removeClass("form-control-nobg");
                  that.parent().parent().find("[type='number']").removeClass("form-control-nobg");
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