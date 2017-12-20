<?php
global $wpdb;
$term_table_name = $wpdb->prefix . "erp_application_terms";
$term_id = $_GET["term_id"];
//update
if (isset($_POST['update'])) {
  $term_name = $_POST["term_name"];
  $term_slug = erp_rec_sanitize_string($term_name);
  $wpdb->update(
    $term_table_name, //table
    array('name' => $term_name, 'slug' => $term_slug), //data
    array('ID' => $term_id), //where
    array('%s', '%s'), //data format
    array('%d') //where format
  );
} else {//selecting value to update	
  $terms = $wpdb->get_results($wpdb->prepare("SELECT id,name,slug FROM $term_table_name where id=%s", $term_id));
  foreach ($terms as $t) {
    $term_name = $t->name;
    $term_slug = $t->slug;
  }
}
?>

<div class="wrap">
  <h2><?php _e('Terms', 'wp-erp-rec');?></h2>

  <?php if ($_POST['update']) : ?>
  <div><p><?php _e('Term updated', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <table class='wp-list-table widefat fixed' style="margin-bottom:10px;">
      <tr><th><?php _e('Term Name', 'wp-erp-rec'); ?></th><td><input type="text" name="term_name" value="<?php echo $term_name; ?>" required/></td></tr>
      <tr><th><?php _e('Term Slug', 'wp-erp-rec'); ?></th><td><input type="text" name="term_slug" value="<?php echo $term_slug; ?>" disabled/></td></tr>
    </table>
    <input type='submit' name="update" value='<?php _e('Save', 'wp-erp-rec'); ?>' class='button'> &nbsp;&nbsp;
  </form>
</div>