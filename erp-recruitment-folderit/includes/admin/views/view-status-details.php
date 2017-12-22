<?php
global $wpdb;
$status_table_name = $wpdb->prefix . "erp_application_status";
$peoplemeta_table_name = $wpdb->prefix . "erp_peoplemeta";
$status_id = $_GET["status_id"];
//update
if (isset($_POST['update'])) {
  $status_name = $_POST["status_name"];
  $status_description = $_POST["status_description"];
  $status_order = $_POST["status_order"];
  $status_old_code = $_POST["status_old_code"];
  var_dump($status_old_code);
  $status_code = erp_rec_sanitize_string($status_name);
  $wpdb->update(
    $status_table_name, //table
    array('code' => $status_code, 'title' => $status_name, 'description' => $status_description, 'status_order' => $status_order), //data
    array('ID' => $status_id), //where
    array('%s', '%s', '%s', '%d'), //data format
    array('%d') //where format
  );
  
  // Actualizar referencias en peoplemeta
  $wpdb->query($wpdb->prepare("UPDATE $peoplemeta_table_name SET meta_value = %s WHERE meta_key = 'status' AND meta_value = %s", $status_code, $status_old_code));
} else { //selecting value to update
  $statuses = $wpdb->get_results($wpdb->prepare("SELECT id,code,title,description,status_order FROM $status_table_name where id=%s", $status_id));
  foreach ($statuses as $s) {
    $status_name = $s->title;
    $status_description = $s->description;
    $status_order = $s->status_order;
    $status_code = $s->code;
  }
}
?>
<div class="wrap">
  <h2><?php _e('Statuses', 'wp-erp-rec');?></h2>

  <?php if ($_POST['update']) : ?>
  <div><p><?php _e('Status updated', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <table class='wp-list-table widefat fixed' style="margin-bottom:10px;">
      <tr><th><?php _e('Status Name', 'wp-erp-rec'); ?></th><td><input type="text" name="status_name" value="<?php echo $status_name; ?>" required/></td></tr>
      <tr><th><?php _e('Status Description', 'wp-erp-rec'); ?></th><td><input type="text" name="status_description" value="<?php echo $status_description; ?>" required/></td></tr>
      <tr><th><?php _e('Status Order', 'wp-erp-rec'); ?></th><td><input type="number" name="status_order" value="<?php echo $status_order; ?>" required/></td></tr>
      <tr><th><?php _e('Status Code', 'wp-erp-rec'); ?></th><td><input type="text" name="status_code" value="<?php echo $status_code; ?>" disabled/></td></tr>
    </table>
    <input type="hidden" name="status_old_code" value="<?php echo $status_code; ?>">
    <input type='submit' name="update" value='<?php _e('Save', 'wp-erp-rec'); ?>' class='button'> &nbsp;&nbsp;
  </form>
</div>