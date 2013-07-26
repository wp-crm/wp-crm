<?php
/**
 * UsabilityDynamics General UI Classes - Customized for WP-CRM
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @todo Add function to check that latest version of UD classes is loaded if others exists
 * @version 1.3
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package UsabilityDynamics
*/


if(!class_exists('CRM_UD_UI')):

/**
 * UsabilityDynamics General UI Class
 *
 * Used for displaying common elements such as input fields. Uses the defined value of 'WP_CRM' to prefixes
 *
 * Example:
 * <code>
 * echo CRM_UD_UI::checkbox("name=send_newsletter&group=settings&value=true&label=If checked, you will receive our newsletters.", $send_newsletter);
 * </code>
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @version 1.3
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package UsabilityDynamics
*/


class CRM_UD_UI {


 /**
  * Formats phone number for display
  *
   *
  * @since 1.0
   * @param string $phone_number
   * @return string $phone_number
  */
 function checked_in_array($item, $array) {

    if(is_array($array) && in_array($item, $array))
      echo 'checked="checked"';


 }

 /**
  * Formats phone number for display
  *
   *
  * @since 1.0
   * @param string $phone_number
   * @return string $phone_number
  */
 function format_phone_number($phone_number) {

    $phone_number = ereg_replace("[^0-9]",'',$phone_number);
  if(strlen($phone_number) != 10) return(False);
  $sArea = substr($phone_number,0,3);
  $sPrefix = substr($phone_number,3,3);
  $sNumber = substr($phone_number,6,4);
  $phone_number = "(".$sArea.") ".$sPrefix."-".$sNumber;

  return $phone_number;
 }


 /**
  * Return a link to a post or page from passed variable.
  *
  * Intended to allow for searching for most likely post/page based on passed string.
  * This is supposed to prevent broken links when a post_id changes for whatever reason.
  *
  * If its a number, then assumes its the id
  * If it resembles a slug, then get the first slug match
  * If not a slug, search by
  *
  * @since 1.1
  * @uses get_permalink() to get the link once an id is identified
  * @link http://usabilitydynamics/codex/CRM_UD_UI/post_link
  *
  * @todo  This function needs work, searching should be more comprehensive, page/post popularity should be considered in logic
  *
  * @return string Front-end link to post/page/custom post
  */
 function post_link($title = false) {
  global $wpdb;

  if(!$title)
   return get_bloginfo('url');

  if(is_numeric($title))
   return get_permalink($title);

        if($id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$title'  AND post_status='publish'"))
   return get_permalink($id);

  if($id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE LOWER(post_title) = '".strtolower($title)."'   AND post_status='publish'"))
   return get_permalink($id);



 }



 /**
  * Shorthand function for drawing checkbox input fields.
  *
  * Draws a checkbox using the plugin prefix.
  * Also creates a hidden input field for the opposite value of the checkbox.
  *
  * The list of checkbox arguments are 'status', 'orderby', 'comment_date_gmt','order', 'number', 'offset', and 'post_id'.
  *
  * List of default arguments are as follows:
  * 'id' - False
  * 'class' - False
  * 'value' - 'true'
  * 'label' - False
  * 'group' - False
  * 'maxlength' - False
  *
  * @since 1.0
  * @uses wp_parse_args() Creates an array from string $args.
  * @link http://usabilitydynamics/codex/CRM_UD_UI/checkbox
  *
  * @param string $args List of arguments to overwrite the defaults.
  * @param bool $checked Option, default is false. Whether checkbox is checked or not.
  * @return string Checkbox input field and hidden field with the opposive value
  */
 function checkbox($args = '', $checked = false) {
  $defaults = array('name' => '', 'id' => false,'class' => false, 'group' => false,'special' => '','value' => 'true', 'label' => false, 'maxlength' => false);
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  // Get rid of all brackets
  if(strpos("$name",'[') || strpos("$name",']')) {
   $replace_variables = array('][',']','[');
   $class_from_name = $name;
   $class_from_name = 'WP_CRM' . str_replace($replace_variables, '_', $class_from_name);
  } else {
   $class_from_name = 'WP_CRM' . $name;
  }


  // Setup Group
  if($group) {
   if(strpos($group,'|')) {
    $group_array = explode("|", $group);
    $count = 0;
    foreach($group_array as $group_member) {
     $count++;
     if($count == 1) {
      $group_string .= "$group_member";
     } else {
      $group_string .= "[$group_member]";
     }
    }
   } else {
    $group_string = "$group";
   }
  }


  // Use $checked to determine if we should check the box
  $checked = strtolower($checked);
  if($checked == 'yes')  $checked = 'true';
  if($checked == 'true')  $checked = 'true';
  if($checked == 'no')  $checked = false;
  if($checked == 'false') $checked = false;


  $id     =  ($id ? $id : $class_from_name);

  $insert_id    =  ($id ? " id='$id' " : " id='$class_from_name' ");
  $insert_name  =  ($group_string ? " name='".$group_string."[$name]' " : " name='$name' ");
  $insert_checked  =  ($checked ? " checked='checked' " : " ");
  $insert_value  =  " value=\"$value\" ";
  $insert_class   =  " class='$class_from_name $class ".'WP_CRM'."checkbox " . ($group ? 'WP_CRM' . $group . '_checkbox' : ''). "' ";
  $insert_maxlength =  ($maxlength ? " maxlength='$maxlength' " : " ");

  // Determine oppositve value
  switch ($value) {
   case 'yes':
   $opposite_value = 'no';
   break;

   case 'true':
   $opposite_value = 'false';
   break;

   case 'open':
   $opposite_value = 'closed';
   break;

  }

  // Print label if one is set
  if($label) $return .= "<label for='$id'>";

  // Print hidden checkbox
  $return .= "<input type='hidden' value='$opposite_value' $insert_name />";

  // Print checkbox
  $return .= "<input type='checkbox' $insert_name $insert_id $insert_class $insert_checked $insert_maxlength  $insert_value $special />";
  if($label) $return .= " $label</label>";

  return $return;
 }


 /**
  * Shorthand function for drawing a textarea
  *
  *
  * @since 1.0
  * @uses wp_parse_args() Creates an array from string $args.
  * @link http://usabilitydynamics/codex/CRM_UD_UI/input
  *
  * @param string $args List of arguments to overwrite the defaults.
   * @return string Input field and hidden field with the opposive value
  */

 function textarea($args = '') {
  $defaults = array('name' => '', 'id' => false,  'checked' => false,  'class' => false, 'style' => false, 'group' => '','special' => '','value' => '', 'label' => false, 'maxlength' => false);
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );


  // Get rid of all brackets
  if(strpos("$name",'[') || strpos("$name",']')) {
   $replace_variables = array('][',']','[');
   $class_from_name = $name;
   $class_from_name = 'WP_CRM' . str_replace($replace_variables, '_', $class_from_name);
  } else {
   $class_from_name = 'WP_CRM' . $name;
  }


  // Setup Group
  if($group) {
   if(strpos($group,'|')) {
    $group_array = explode("|", $group);
    $count = 0;
    foreach($group_array as $group_member) {
     $count++;
     if($count == 1) {
      $group_string .= "$group_member";
     } else {
      $group_string .= "[$group_member]";
     }
    }
   } else {
    $group_string = "$group";
   }
  }

  $id     =  ($id ? $id : $class_from_name);

  $insert_id    =  ($id ? " id='$id' " : " id='$class_from_name' ");
  $insert_name  =  ($group_string ? " name='".$group_string."[$name]' " : " name=' ".'WP_CRM'."$name' ");
  $insert_checked  =  ($checked ? " checked='true' " : " ");
  $insert_style  =  ($style ? " style='$style' " : " ");
  $insert_value  =  ($value ? $value : "");
  $insert_class   =  " class='$class_from_name input_textarea $class' ";
  $insert_maxlength =  ($maxlength ? " maxlength='$maxlength' " : " ");

  // Print label if one is set

  // Print checkbox
  $return .= "<textarea $insert_name $insert_id $insert_class $insert_checked $insert_maxlength $special $insert_style>$insert_value</textarea>";


  return $return;
 }


 /**
  * Shorthand function for drawing regular or hidden input fields.
  *
  * Returns an input field using the plugin-specific prefix.
  *
  * The list of input field arguments are 'name', 'group', 'special','value', 'type', 'hidden', 'style', 'readonly', and 'label'.
  *
  * List of default arguments are as follows:
  * 'hidden' - False
  * 'style' - False
  * 'readonly' - False
  * 'label' - False
  *
  * @since 1.3
  * @uses wp_parse_args() Creates an array from string $args.
  * @link http://usabilitydynamics/codex/CRM_UD_UI/input
  *
  * @param string $args List of arguments to overwrite the defaults.
  * @param string $value Value may be passed in arg array or seperately
   * @return string Input field and hidden field with the opposive value
  */
 function input($args = '', $value = false) {
  $defaults = array('name' => '', 'group' => '','special' => '','value' => $value, 'type' => 'text', 'class' => false, 'hidden' => false, 'style' => false, 'readonly' => false, 'label' => false);
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  // Add prefix
  //$name = 'WP_CRM' . "$name";

  if($class)
   $class = 'WP_CRM' . "$class";


  // if [ character is present, we do not use the name in class and id field
  if(!strpos("$name",'[')) {
   $id = $name;
   $class_from_name = $name;
  }

  if($label) $return .= "<label for='$name'>";
  $return .= "<input ".($type ?  "type=\"$type\" " : '')." ".($style ?  "style=\"$style\" " : '')." id=\"$id\" class=\"".($type ?  "" : "input_field")." $class_from_name $class ".($hidden ?  " hidden " : '').""  .($group ? "group_$group" : ''). " \"   name=\"" .($group ? $group."[".$name."]" : $name). "\"  value=\"".stripslashes($value)."\"  title=\"$title\" $special ".($type == 'forget' ?  " autocomplete='off'" : '')." ".($readonly ?  " readonly=\"readonly\" " : "")." />";
  if($label) $return .= " $label </label>";

  return $return;
 }


 /**
  * Inserts JavaScript functions for UI
   *
  * @todo May be best to be in an external file
  *
   * @param $args Optional, pass 'return' => true to return content. Echo on default
  * @since 1.1
  */

 function print_admin_scripts($args = '') {
  $defaults = array('return' => false);
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );


  ob_start();

  ?>
  <script type="text/javascript">
   jQuery(document).ready(function() {


   });
   /**
    * Displays a JSON response
    *
    * If a #message box does not exist on page, one is created after .wrap h2
    *
    * @param Array
    *
    */
   function ud_json_response(data) {

    // Create message element if does not exist
    if(jQuery('#message').length == 0) {
     var message_element = '<div style="display: none;" class="updated fade ud_inserted_element" id="message"></div>'
     jQuery(message_element).insertAfter('.wrap h2');
    }

    // Re-get the message element
    var message_element = jQuery(".wrap #message");

    // If data.success = 'false' change class to .error
    if(data.success == 'false') {
     jQuery(message_element).removeClass('updated');
     jQuery(message_element).addClass('error');
    }

    // If data.response = 'true' change class to .updated
    if(data.success == 'true') {
     jQuery(message_element).removeClass('error');
     jQuery(message_element).addClass('updated');
    }

    // Show and print message
    jQuery(message_element).show();
    jQuery(message_element).html("<p>" +data.message+"</p>");

   }

   /**
    * Displays a processing action
    *
    * If a #message box does not exist on page, one is created after .wrap h2
    *
    * @param Array
    *
    */
   function ud_json_processing(data) {

    // Create message element if does not exist
    if(jQuery('#message').length == 0) {
     var message_element = '<div style="display: none;" class="updated fade ud_inserted_element" id="message"></div>'
     jQuery(message_element).insertAfter('.wrap h2');
    }

    // Re-get the message element
    var message_element = jQuery(".wrap #message");
     jQuery(message_element).addClass('updated');


    // Show and print message
    jQuery(message_element).show();
    jQuery(message_element).html("<p><?php _('Processing...','wpp') ?></p>");

   }



  </script>


  <?php

  $content = ob_get_contents();
  ob_end_clean();

  if($return)
   return $content;

  echo $content;
 }

 /**
  * Loads UD admin scripts into admin header
   *
  * @uses add_action() Calls 'add_action'
  *
  */
 function use_ud_scripts() {
  add_action('admin_head', array('CRM_UD_UI', 'print_admin_scripts'));
 }

 /**
  * Displays the UD UI log page
   *
  * @todo Add button or link to delete log
  * @todo Add nonce to clear_log functions
  * @since 1.0
  * @uses CRM_UD_F::delete_log()
  * @uses CRM_UD_F::get_log()
  * @uses CRM_UD_F::nice_time()
  * @uses add_action() Calls 'admin_menu' hook with an anonymous (lambda-style) function which uses add_menu_page to create a UI Log page
  * @uses add_action() Calls 'admin_menu' hook with an anonymous (lambda-style) function which uses add_menu_page to create a UI Log page
  */

 function show_log_page() {

  if($_REQUEST['ud_action'] == 'clear_log')
   CRM_UD_F::delete_log();

  ?>
  <style type="text/css">

   .ud_event_row b { background:none repeat scroll 0 0 #F6F7DC;
padding:2px 6px;}
  </style>

  <div class="wrap">
  <h2><?php _e('UD Log Page for','wp_crm') ?> get_option('<?php echo 'WP_CRM' . 'log'; ?>');
  <a href="<?php echo admin_url("admin.php?page=ud_log&ud_action=clear_log"); ?>" class="button"><?php _e('Clear Log','wp_crm') ?></a>
  </h2>


  <table class="widefat">
   <thead>
   <tr>
    <th style="width: 150px"><?php _e('Timestamp','wp_crm') ?></th>
    <th><?php _e('Event','wp_crm') ?></th>
    <th><?php _e('User','wp_crm') ?></th>
   </tr>
   </thead>

   <tbody>
   <?php foreach(CRM_UD_F::get_log() as $event): ?>
   <tr class="ud_event_row">
    <td><?php echo CRM_UD_F::nice_time($event[0]); ?></td>
    <td><?php echo $event[1]; ?></td>
    <td><?php $user_data = get_userdata($event[2]); echo $user_data->display_name; ?></td>
   </tr>
   <?php endforeach; ?>
   </tbody>
  </table>
  </div>
  <?php

  }


}

endif; /* f(!class_exists('CRM_UD_UI')): */



if(!class_exists('CRM_UD_F')):

/**
 * General Shared Functions used in UsabilityDynamics and TwinCitiesTech.com plugins and themes.
 *
 * Used for performing various useful functions applicable to different plugins.
 *
 * @version 1.5
 * @link http://usabilitydynamics/codex/CRM_UD_F
 * @package UsabilityDynamics
 */

class CRM_UD_F {

  function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
  }

 function get_column_names($table) {
  global $wpdb;

  $table_info = $wpdb->get_results("SHOW COLUMNS FROM $table");


  if(empty($table_info))
   return;


  foreach($table_info as $row) {

   $columns[] = $row->Field;
  }


  return $columns;



 }

 /**
  * Get a URL of a page.
  *
  *
  * @version 1.5
 **/
  function is_url($string) {


    if(is_array($string)) {
      $string = $string[0];
    }

    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $string);
  }


 /**
  * Get a URL of a page.
  *
  *
  * @version 1.5
 **/
 function objectToArray($object) {

    if(!is_object( $object ) && !is_array( $object )) {
    return $object;
   }

    if(is_object($object) ) {
   $object = get_object_vars( $object );
    }

    return array_map(array('CRM_UD_F' , 'objectToArray'), $object );
  }


 /**
  * Get a URL of a page.
  *
  *
  * @version 1.5
 **/
 function base_url($page = '') {

  $permalink = get_option('permalink_structure');


  if ( '' != $permalink) {
   $permalink = home_url( $page);
   $permalink = user_trailingslashit($permalink);

  } else {
  $permalink = home_url('?p=' . $page);

  }

  return $permalink;

 }

 /**
  * Merges any number of arrays / parameters recursively,
  *
  * Replacing entries with string keys with values from latter arrays.
  * If the entry or the next value to be assigned is an array, then it
  * automagically treats both arguments as an array.
  * Numeric entries are appended, not replaced, but only if they are
  * unique
  *
  * @source http://us3.php.net/array_merge_recursive
  * @version 1.4
 **/

  function array_merge_recursive_distinct () {
   $arrays = func_get_args();
   $base = array_shift($arrays);
   if(!is_array($base)) $base = empty($base) ? array() : array($base);
   foreach($arrays as $append) {
  if(!is_array($append)) $append = array($append);
  foreach($append as $key => $value) {
    if(!array_key_exists($key, $base) and !is_numeric($key)) {
   $base[$key] = $append[$key];
   continue;
    }
    if(is_array($value) or @is_array($base[$key])) {
   $base[$key] = CRM_UD_F::array_merge_recursive_distinct($base[$key], $append[$key]);
    } else if(is_numeric($key)) {
   if(!in_array($value, $base)) $base[] = $value;
    } else {
   $base[$key] = $value;
    }
  }
   }
   return $base;
 }


 /**
  * Adds a post object to cache.
  *
  * Creates cache tables if not created, and adds an object
  *
  *
  * @version 1.4
 **/
 function add_cache($id = false, $type = false, $data = false) {
  global $wpdb;

  if(!is_numeric($id))
   return false;

  if(empty($data))
   return false;

  if(empty($type))
   return false;

  $tablename = $wpdb->prefix . "ud_cache";



  // Check if table exists
  if(CRM_UD_F::check_cache_table()) {

   // Remove old cahce
   $wpdb->query("DELETE FROM $tablename WHERE id = '$id'");


   if(!$wpdb->insert($tablename, array('id' => $id, 'type' => $type, 'data' => serialize($data))))
    return false;


   return true;
  }

  return false;

 }

  /**
  * Gets cache if it exists
  *
  * Creates cache tables if not created, and adds an object
  *
  *
  * @version 1.4
 **/
 function get_cache($id = false) {
  global $wpdb;

  if(!is_numeric($id))
   return false;


  $tablename = $wpdb->prefix . "ud_cache";

  // Check if table exists
  if(!CRM_UD_F::check_cache_table())
   return;

  $data = $wpdb->get_var("SELECT data FROM $tablename WHERE id = '$id'");

  $data = unserialize($data);

  if(is_array($data))
   return $data;


  return false;

 }




 /**
  * Checks if a database table exists, if not, create it
  *
  *
  * @version 1.4
 **/
 function check_cache_table($table_name = 'ud_cache') {
  global $wpdb;

  // Get tablename
  $table_name = $wpdb->prefix . $table_name;

  // If table already exists do nothing
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)
   return true;

  CRM_UD_F::log(__("Cache table does not exist. Attempting to create:",'wp_crm'));

  // Table does not exist, make it
  $sql = "CREATE TABLE " . $table_name . " (
     id bigint(20) NOT NULL,
     type varchar(200) NOT NULL DEFAULT '',
     data text NOT NULL,
     modified TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
   );";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

  CRM_UD_F::log(sprintf(__('SQL Ran: %s , verifying existence.','wp_crm'), $sql ));


  // Verify it exists
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
   CRM_UD_F::log(__("Table created and exists.",'wp_crm'));
   return true;
  }

  CRM_UD_F::log(__("Table not created.",'wp_crm'));

  // Something went terribly wrong
  return false;

 }


  /**
  * Returns a URL to a post object based on passed variable.
  *
  * If its a number, then assumes its the id, If it resembles a slug, then get the first slug match.
  *
  * @since 1.0
  * @param string $title A page title, although ID integer can be passed as well
  * @return string The page's URL if found, otherwise the general blog URL
  */
 function post_link($title = false) {
  global $wpdb;

  if(!$title)
   return get_bloginfo('url');

  if(is_numeric($title))
   return get_permalink($title);

        if($id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$title'  AND post_status='publish'"))
   return get_permalink($id);

  if($id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE LOWER(post_title) = '".strtolower($title)."'   AND post_status='publish'"))
   return get_permalink($id);

 }




 /**
  * Add an entry to the plugin-specifig log.
  *
  * Creates log if one does not exist.
  *
  * Example:
  * <code>
  * CRM_UD_F::log("Settings updated");
  * </code>
  *
  * $param string Event description

   * @uses get_option()
  * @uses update_option()
  * @uses check_prefix()
   * @return bool True if event added succesfully.
  *
  */
 function log($event) {
  CRM_UD_F::check_prefix();
  $current_user = wp_get_current_user();

  $log_name = 'WP_CRM' . 'log';
  $current_user_id = $current_user->ID;

  $this_log = get_option($log_name);

  // If no session log created yet, create one
  if(!is_array($this_log)) {
   $this_log = array();
   array_push($this_log, array(time(), __("Log Started.",'wp_crm')));
  }

  // Insert event into session
  array_push($this_log, array(time(),$event, $current_user_id));

  update_option($log_name, $this_log);

  return true;
 }

 /**
  * Used to get the current plugin's log created via UD class
  *
  * If no log exists, it creates one, and then returns it in chronological order.
  *
  * Example to view log:
  * <code>
  * print_r(CRM_UD_F::get_log());
  * </code>
  *
  * $param string Event description
   * @uses get_option()
  * @uses update_option()
  * @uses check_prefix()
   * @return array Using the get_option function returns the contents of the log.
  *
  */
 function get_log() {
  CRM_UD_F::check_prefix();

  $log_name = 'WP_CRM' . 'log';
  $this_log = get_option($log_name);

  // If no session log created yet, create one
  if(!is_array($this_log)) {
   $this_log = array();
   array_push($this_log, array(time(),__("Log Started.",'wp_crm')));
  }
  update_option($log_name, $this_log);

  return array_reverse(get_option($log_name));
 }

 /**
  * Delete UD log for this plugin.
  *
  *
  * @uses update_option()
  * @uses check_prefix()
   *
  */
 function delete_log() {
  CRM_UD_F::check_prefix();

  $log_name = 'WP_CRM' . 'log';

  delete_option($log_name);

 }

 /**
  * Check if a prefix is defined, if not defines one automatically.
  *
  * @todo Needs functionality to generate the $auto_prefix based on plugin name, or something.
  * @param string $prefix Optional, set the prefix.
  * @return bool True is prefix exists or was set, false otherwise
  *
  */
 function check_prefix($prefix = false) {

  // Get pfix
  if(defined('WP_CRM'))
   return true;

  if($prefix) {
   define('WP_CRM', $auto_prefix);
   return true;
  }

  // Generate auto_prefix based on plugin's folder
  $auto_prefix = "ui_";

  // Set prefix using auto-generated one
  define('WP_CRM', $auto_prefix);

  // if prefix wasn't auto-generated:
  // return false


 }


 /**
  * Displays the numbers of days elapsed between a provided date and today.
  *
  * Dates can be passed in any formation recognizable by strtotime()
  *
  * @param string $date1 Date to use for calculation.
  * @param bool $return_number Optional, default is false. If true, forces function to return an integer of days difference.
  *
   * @uses get_option()
  * @uses update_option()
  * @uses strtotime()
  * @uses check_prefix()
  * @uses apply_filters() Calls 'CRM_UD_F_days_since' filter on provided date.
   * @return string|int Returns either a string value of days passed, integer if $return_number passed as true, or blank value if no date is passed.
  *
  */
 function days_since($date1, $return_number = false) {

  if(empty($date1)) return "";

  // In case a passed date cannot be ready by strtotime()
  $date1 = apply_filters('CRM_UD_F_days_since', $date1);

  $date2 = date("Y-m-d");
  $date1 = date("Y-m-d", strtotime($date1));


  // determine if future or past
  if(strtotime($date2) < strtotime($date1)) $future = true;

  $difference = abs(strtotime($date2) - strtotime($date1));
  $days = round(((($difference/60)/60)/24), 0);

  if($return_number)
   return $days;

  if($days == 0) __('Today', 'wp_crm');
  elseif($days == 1) { return($future ? __("Tomorrow ",'wp_crm') : __("Yesterday ",'wp_crm')); }
  elseif($days > 1 && $days <= 6) { return ($future ? sprintf(__(" in %d days ",'wp_crm'), $days) : sprintf(__("%d days ago",'wp_crm'), $days)); }
  elseif($days > 6) { return date(get_option('date_format'), strtotime($date1)); }
 }

 /**
  * Returns date and/or time using the WordPress date or time format.
  *
  * @param string $time Date or time to use for calculation.
    * @param string $args List of arguments to overwrite the defaults.
  *
  * @todo Add functionality to adjust for time zone
   * @uses wp_parse_args()
  * @uses get_option()
   * @return string|bool Returns formatted date or time, or false if no time passed.
  *
  */
 function nice_time($time, $args = false) {


   $defaults = array('format' => 'date_and_time');
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  if(!$time)
   return false;

  if($format == 'date')
   return date(get_option('date_format'), $time);

  if($format == 'time')
   return date(get_option('time_format'), $time);

  if($format == 'date_and_time')
   return date(get_option('date_format'), $time) . " " . date(get_option('time_format'), $time);



  return false;

 }

 /**
  * Creates Admin Menu page for UD Log
   *
  * @todo Need to make sure this will work if multiple plugins utilize the UD classes
  * @see function show_log_page
  * @since 1.0
  * @uses add_action() Calls 'admin_menu' hook with an anonymous (lambda-style) function which uses add_menu_page to create a UI Log page
  */
 function add_log_page() {
  add_action('admin_menu', create_function('', "add_menu_page(__('Log','wp_crm'), __('Log','wp_crm'), 10, 'ud_log', array('CRM_UD_UI','show_log_page'));"));
 }

 /**
  * Turns a passed string into a URL slug
   *
   * Argument 'check_existance' will make the function check if the slug is used by a WordPress post
    *
  * @param string $content
  * @param string $args Optional list of arguments to overwrite the defaults.
  * @since 1.0
  * @uses add_action() Calls 'admin_menu' hook with an anonymous (lambda-style) function which uses add_menu_page to create a UI Log page
  * @return string
  */
 function create_slug($content, $args = false) {

   $defaults = array('check_existance' => false);
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  $content = trim($content);
  $content = preg_replace('~[^\\pL0-9_]+~u', '_', $content); // substitutes anything but letters, numbers and '_' with separator
  $content = trim($content, "-");
  $content = iconv("utf-8", "us-ascii//TRANSLIT", $content); // TRANSLIT does the whole job
  $content = strtolower($content);
  $slug = preg_replace('~[^-a-z0-9_]+~', '', $content); // keep only letters, numbers, '_' and separator

  return $slug;

 }

 /**
  * Convert a slug to a more readable string
   *
  * @since 1.3
  * @return string
  */
 function de_slug($string) {

   $string = ucwords(str_replace("_", " ", $string));

  return $string;

 }
 /**
  * Returns a JSON response, and dies.
   *
   * Designed for AJAX functions.
    *
  * @todo Create option to disable database logging
  * @param bool $success
  * @param string $message
  * @since 1.1

  * @return string
  */
 function json($success, $message) {

   if($success)
   $r_success = 'true';

  if(!$success)
   $r_success = 'false';

  $return = array(
   'success' => $r_success,
   'message' => $message);

  // Log in database
  CRM_UD_F::log(__("Automatically logged failed JSON response: ",'wp_crm').$message);

  echo json_encode($return);
  die();

 }


 /**
  * Returns location information from Google Maps API call
   *
     *
  * @since 1.2
  * @return object
  */

  function geo_locate_address($address = false, $localization = "en") {


  if(!$address)
   return false;

  if(is_array($address))
   return false;

  $address = urlencode($address);

  $url = str_replace(" ", "+" ,"http://maps.google.com/maps/api/geocode/json?address={$address}&sensor=true&language=$localization");


  $obj = (json_decode(wp_remote_fopen($url)));

  if($obj->status != "OK")
   return false;

  //print_r($obj->results);
  $results = $obj->results;
  $results_object = $results[0];
   $geometry = $results_object->geometry;



  $return->formatted_address = $results_object->formatted_address;
  $return->latitude = $geometry->location->lat;
  $return->longitude = $geometry->location->lng;

  // Cycle through address component objects picking out the needed elements, if they exist
  foreach($results_object->address_components as $ac) {

   // types is returned as an array, look through all of them
   foreach($ac->types as $type) {
    switch($type){

     case 'street_number':
      $return->street_number = $ac->long_name;
     break;

     case 'route':
      $return->route = $ac->long_name;
     break;


     case 'locality':
       $return->city = $ac->long_name;
     break;


     case 'administrative_area_level_3':
      if(empty($return->city))
      $return->city = $ac->long_name;
     break;

     case 'administrative_area_level_2':
      $return->county = $ac->long_name;
     break;

     case 'administrative_area_level_1':
      $return->state = $ac->long_name;
      $return->state_code = $ac->short_name;
     break;

     case 'country':
      $return->country = $ac->long_name;
      $return->country_code = $ac->short_name;
     break;

     case 'postal_code':
      $return->postal_code = $ac->long_name;
     break;

    }
   }


  }

  //print_r($return);

  return $return;


  }


 /**
  * Gmail-like time ago
  *
  *
  * @since 1.4
  */
  function time_ago($time) {

    if(!$time)
    return;

    $minutes_ago = round((time() - $time) / 60);

    if($minutes_ago > 60) {
    return sprintf(__('%s hours ago','wp_crm'),round($minutes_ago / 60));
    } else {
    return sprintf(__('%s minutes ago','wp_crm'),$minutes_ago);
    }

  }



 /**
  * Convert a string to a url-like slug
  *
  *
  * @since 1.4
  */
  function slug_to_label($slug = false) {

    if(!$slug)
    return;

    $slug = str_replace("_", " ", $slug);
    $slug = ucwords($slug);
    return $slug;

  }


 /**
  * Removes all metaboxes from given page
  *
  * Should be called by function in add_meta_boxes_$post_type
  * Cycles through all metaboxes
  *
  * @since 1.1
  */
  function remove_object_ui_elements($post_type, $remove_elements) {
    global $wp_meta_boxes, $_wp_post_type_features;

    /** Remove Metaboxes */
    foreach($wp_meta_boxes[$post_type] as $context_slug => $priority_array) {
      foreach($priority_array as $priority_slug => $meta_box_array) {
        foreach($meta_box_array as $meta_box_slug => $meta_bog_data) {
          if(in_array($meta_box_slug, $remove_elements))
            unset($wp_meta_boxes[$post_type][$context_slug][$priority_slug][$meta_box_slug]);
        }
      }
    }

    if(is_array($_wp_post_type_features[$post_type])) {
      // Remove features
      foreach($_wp_post_type_features[$post_type] as $feature => $enabled) {

        if(in_array($feature, $remove_elements))
        unset($_wp_post_type_features[$post_type][$feature]);

      }
    }
  }


/**
 * The letter l (lowercase L) and the number 1
 * have been removed, as they can be mistaken
 * for each other.
 */
 function createRandomPassword() {
  $chars = "abcdefghijkmnopqrstuvwxyz023456789";
  srand((double)microtime()*1000000);
  $i = 0;
  $pass = '' ;

  while ($i <= 7) {
   $num = rand() % 33;
   $tmp = substr($chars, $num, 1);
   $pass = $pass . $tmp;
   $i++;
  }

  return $pass;

 }

  function check_email_address($email) {
    // First, we check that there's one @ symbol,
    // and that the lengths are right.
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
   // Email invalid because wrong number of characters
   // in one section or wrong number of @ symbols.
   return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
   if
  (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
  ?'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
  $local_array[$i])) {
     return false;
   }
    }
    // Check if domain is IP. If not,
    // it should be valid domain name
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
   $domain_array = explode(".", $email_array[1]);
   if (sizeof($domain_array) < 2) {
    return false; // Not enough parts to domain
   }
   for ($i = 0; $i < sizeof($domain_array); $i++) {
     if
  (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
  ?([A-Za-z0-9]+))$",
  $domain_array[$i])) {
    return false;
     }
   }
    }
    return true;
 }

  /**
   *
   * @param type $text
   * @param type $maxurl_len
   * @param type $target
   * @return type
   */
  function parse_urls($text, $maxurl_len = 35, $target = '_self') {
    if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,\!]+)/si', $text, $urls)) {
      $offset1 = ceil(0.65 * $maxurl_len) - 2;
      $offset2 = ceil(0.30 * $maxurl_len) - 1;
      foreach (array_unique($urls[1]) AS $url) {
        if ($maxurl_len AND strlen($url) > $maxurl_len) {
          $urltext = substr($url, 0, $offset1) . '...' . substr($url, -$offset2);
        } else {
          $urltext = $url;
        }
        $text = str_replace($url, '<a href="'. $url .'" target="'. $target .'" title="'. $url .'">'. $urltext .'</a>', $text);
      }
    }
    return $text;
  }

  /**
   * Check if the current WP version is older then given parameter $version.
   * @param string $version
   * @author peshkov@UD
   */
  static function is_older_wp_version ($version = '') {
    if(empty($version) || (float)$version == 0) return false;
    $current_version = get_bloginfo('version');
    /** Clear version numbers */
    $current_version = preg_replace("/^([0-9\.]+)-(.)+$/", "$1", $current_version);
    $version = preg_replace("/^([0-9\.]+)-(.)+$/", "$1", $version);
    return ((float)$current_version < (float)$version) ? true : false;
  }

}

endif; /* f(!class_exists('CRM_UD_F')): */