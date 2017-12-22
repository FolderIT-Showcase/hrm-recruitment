<?php
global $wpdb;
$status_table_name = $wpdb->prefix . "erp_application_status";
$peoplemeta_table_name = $wpdb->prefix . "erp_peoplemeta";

if (isset($_POST['delete'])) {
  $status_id = $_POST["status_id"];
  $status_name = $_POST["status_name"];
  $status_code = $wpdb->get_var($wpdb->prepare("SELECT code FROM $status_table_name WHERE id = %s", $status_id));
  // Eliminar el status
  $wpdb->query($wpdb->prepare("DELETE FROM $status_table_name WHERE id = %s", $status_id));
  // Limpiar peoplemeta
  $wpdb->query($wpdb->prepare("UPDATE $peoplemeta_table_name SET meta_value = '' WHERE meta_key = 'status' AND meta_value = %s", $status_code));
} else if (isset($_POST['insert'])) {
  $status_name = $_POST["status_name"];
  $status_description = $_POST["status_description"];
  $status_order = $_POST["status_order"];
  $status_code = erp_rec_sanitize_string($status_name);
  $wpdb->insert(
    $status_table_name, //table
    array('code' => $status_code, 'title' => $status_name, 'description' => $status_description, 'status_order' => $status_order), //data
    array('%s', '%s', '%s', '%d') //data format			
  );
}

$rows = $wpdb->get_results("SELECT id,code,title,description,status_order FROM {$status_table_name} WHERE internal = 0 ORDER BY status_order");
?>

<div class="wrap">
  <h1><?php _e('Add Status', 'wp-erp-rec');?></h1>
  <?php if ($_POST['delete']) : ?>
  <div><p><?php echo $status_name . ' - ' . __('Status deleted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>
  <?php if ($_POST['insert']) : ?>
  <div><p><?php echo $status_name . ' - ' . __('Status inserted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>
  <div class="postbox">
    <div class="container-fluid" style="margin:10px 0px;">
      <div class="row">
        <div class="col-lg-12">
          <form class="form-horizontal" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="form-group">
              <label for="status_name" class="col-sm-3 col-lg-2"><?php _e('Status Name', 'wp-erp-rec'); ?></label>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="status_name" id="status_name" value="" required/>
              </div>
            </div>
            <div class="form-group">
              <label for="status_description" class="col-sm-3 col-lg-2"><?php _e('Status Description', 'wp-erp-rec'); ?></label>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="status_description" id="status_description" value="" required/>
              </div>
            </div>
            <div class="form-group">
              <label for="status_order" class="col-sm-3 col-lg-2"><?php _e('Status Order', 'wp-erp-rec'); ?></label>
              <div class="col-sm-2">
                <input type="number" class="form-control" name="status_order" id="status_order" value="0" required/>
              </div>  
            </div>
            <input type="submit" name="insert" value="<?php _e('Insert', 'wp-erp-rec'); ?>" class="button"/>
          </form>
        </div>
      </div>
    </div>
  </div>
  <h1><?php _e('Statuses', 'wp-erp-rec');?></h1>
  <div class="postbox">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12" style="margin:10px 0px;">
          <table class='wp-list-table widefat fixed striped posts table table-responsive'>
            <tr>
              <th><?php _e('Status Name','wp-erp-rec'); ?></th>
              <th><?php _e('Status Description','wp-erp-rec'); ?></th>
              <th><?php _e('Status Order','wp-erp-rec'); ?></th>
              <th><?php _e('Status Code','wp-erp-rec'); ?></th>
              <th><?php _e('Actions','wp-erp-rec'); ?></th>
            </tr>
            <?php foreach ($rows as $row) : ?>
            <tr>
              <td><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=status_detail&status_id=' . $row->id); ?>"><?php echo $row->title; ?></a></td>
              <td><?php echo $row->description; ?></td>
              <td><?php echo $row->status_order; ?></td>
              <td><?php echo $row->code; ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="status_id" value="<?php echo $row->id; ?>">
                  <input type="hidden" name="status_name" value="<?php echo $row->title; ?>">
                  <input type='submit' name="delete" value='<?php _e('Delete', 'wp-erp-rec'); ?>' class='button' onclick="return confirm('<?php _e('Please confirm Status deletion', 'wp-erp-rec'); ?>')">
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>