<?php
global $wpdb;
$term_table_name = $wpdb->prefix . "erp_application_terms";
$term_relation_table_name = $wpdb->prefix . "erp_application_terms_relation";
$peoplemeta_table_name = $wpdb->prefix . "erp_peoplemeta";

if (isset($_POST['delete'])) {
  $term_id = $_POST["term_id"];
  $term_name = $_POST["term_name"];
  $term_slug = $wpdb->get_var($wpdb->prepare("SELECT slug FROM $term_table_name WHERE id = %s", $term_id));
  // Eliminar el term
  $wpdb->query($wpdb->prepare("DELETE FROM $term_table_name WHERE id = %s", $term_id));
  // Eliminar term de todas las relations
  $wpdb->query($wpdb->prepare("DELETE FROM $term_relation_table_name WHERE term_id = %s", $term_id));
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
    // Eliminar el slug de cada peoplemeta
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
        if($mv === $term_slug) {
          unset($meta_values[$i]);
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
} else if (isset($_POST['insert'])) {
  $term_name = $_POST["term_name"];
  $term_slug = erp_rec_sanitize_string($term_name);
  $wpdb->insert(
    $term_table_name, //table
    array('name' => $term_name, 'slug' => $term_slug), //data
    array('%s', '%s') //data format			
  );
}

$rows = $wpdb->get_results("SELECT id,name,slug FROM {$term_table_name} ORDER BY name");
?>

<div class="wrap">
  <h1><?php _e('Add Term', 'wp-erp-rec');?></h1>

  <?php if ($_POST['delete']) : ?>
  <div><p><?php echo $term_name . ' - ' . __('Term deleted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <?php if ($_POST['insert']) : ?>
  <div><p><?php echo $term_name . ' - ' . __('Term inserted', 'wp-erp-rec'); ?></p></div>
  <?php endif; ?>

  <div class="postbox">
    <div class="container-fluid" style="margin:10px 0px;">
      <div class="row">
        <div class="col-lg-12">
          <form class="form-inline" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <div class="form-group">
              <label for="term_name"><?php _e('Term Name', 'wp-erp-rec'); ?></label>
              <input type="text" class="form-control-plaintext" name="term_name" id="term_name" value="" required/>
            </div>
            <input type="submit" name="insert" value="<?php _e('Insert', 'wp-erp-rec'); ?>" class="button"/>
          </form>
        </div>
      </div>
    </div>
  </div>

  <h1><?php _e('Terms', 'wp-erp-rec');?></h1>

  <div class="postbox">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12" style="margin:10px 0px;">
          <table class='wp-list-table widefat fixed striped posts'>
            <tr>
              <th><?php _e('Term Name','wp-erp-rec'); ?></th>
              <th><?php _e('Term Slug','wp-erp-rec'); ?></th>
              <th><?php _e('Actions','wp-erp-rec'); ?></th>
            </tr>
            <?php foreach ($rows as $row) : ?>
            <tr>
              <td><a href="<?php echo admin_url('edit.php?post_type=erp_hr_recruitment&page=term_detail&term_id=' . $row->id); ?>"><?php echo $row->name; ?></a></td>
              <td><?php echo $row->slug; ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="term_id" value="<?php echo $row->id; ?>">
                  <input type="hidden" name="term_name" value="<?php echo $row->name; ?>">
                  <input type='submit' name="delete" value='<?php _e('Delete', 'wp-erp-rec'); ?>' class='button' onclick="return confirm('<?php _e('Please confirm Term deletion', 'wp-erp-rec'); ?>')">
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