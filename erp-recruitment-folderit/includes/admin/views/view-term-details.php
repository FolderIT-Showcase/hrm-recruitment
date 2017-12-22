<?php
global $wpdb;
$term_table_name = $wpdb->prefix . "erp_application_terms";
$peoplemeta_table_name = $wpdb->prefix . "erp_peoplemeta";
$term_id = $_GET["term_id"];
//update
if (isset($_POST['update'])) {
  $term_name = $_POST["term_name"];
  $term_old_slug = $_POST["term_old_slug"];
  $term_slug = erp_rec_sanitize_string($term_name);
  $wpdb->update(
    $term_table_name, //table
    array('name' => $term_name, 'slug' => $term_slug), //data
    array('ID' => $term_id), //where
    array('%s', '%s'), //data format
    array('%d') //where format
  );  
  
  // Obtener exclusivamente fields que utilizan terms
  $personal_fields = erp_rec_get_personal_fields();
  foreach($personal_fields as $field_name => $field) {
    if(!isset($field['terms']) || $field['terms'] !== true) {
      unset($personal_fields[$field_name]);
    }
  }
  // Obtener todos los peoplemeta de cada field que utiliza terms
  foreach($personal_fields as $field_name => $field) {
    $terms_meta = $wpdb->get_results($wpdb->prepare("SELECT meta_id,meta_value FROM $peoplemeta_table_name WHERE meta_key=%s",$field_name), ARRAY_A);
    // Actualizar el slug de cada peoplemeta
    foreach($terms_meta as $tm) {
      $value = $tm['meta_value'];
      if(empty($value)) {
        continue;
      }
      $meta_values = (!empty($value))?json_decode(str_replace('&quot;', '"', $value), true)['terms']:[];
      if(empty($meta_values)) {
        continue;
      }
      foreach($meta_values as $i => $mv) {
        if($mv === $term_old_slug) {
          $meta_values[$i] = $term_slug;
        }
      }
      // Re-encode JSON. Si el campo queda vacío, poner un string vacio
      $meta_values_json = (empty($meta_values))?"":json_encode(array('terms' => $meta_values));

      $data = array(
        'meta_value'    => $meta_values_json
      );

      $data_format = array(
        '%s'
      );

      $where = array(
        'meta_id' => $tm['meta_id']
      );

      $where_format = array(
        '%d'
      );

      $wpdb->update( $wpdb->prefix . 'erp_peoplemeta', $data, $where, $data_format, $where_format );
    }
  }
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
    <input type="hidden" name="term_old_slug" value="<?php echo $term_slug; ?>">
    <input type='submit' name="update" value='<?php _e('Save', 'wp-erp-rec'); ?>' class='button'> &nbsp;&nbsp;
  </form>
</div>