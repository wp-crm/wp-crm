<?php
/**
 * CRM User List Table class.
 *
 * @package WP-Invoice
 * @since 3.0
 * @access private
 */
class CRM_User_List_Table extends WP_CMR_List_Table {

  /**
   *
   * @var type 
   */
  var $site_id;
  
  /**
   *
   * @var type 
   */
  var $aoColumns;
  
  /**
   *
   * @var type 
   */
  var $_args;
  
  /**
   *
   * @var type 
   */
  var $is_site_users;
  
  /**
   *
   * @var type 
   */
  var $aoColumnDefs;
  
  /**
   *
   * @var type 
   */
  var $column_ids;
  
  /**
   *
   * @var type 
   */
  var $user_ids;
  
  /**
   *
   * @var type 
   */
  var $page_user_ids;

  /**
   * Setup options mostly.
   *
   * @todo Get list of displayed columns from options
   *
   */
  function __construct($args = '') {
    global $wp_crm;

    $args = wp_parse_args( $args, array(
      'plural' => '',
      'iColumns' => 3,
      'per_page' => 20,
      'iDisplayStart' => 0,
      'ajax_action' => 'wp_crm_list_table',
      'current_screen' => 'toplevel_page_wp_crm', //* toplevel_page_wp_crm */
      'singular' => '',
      'ajax' => false
    ) );

    $this->_args = $args;

    $pr_columns = array(
      'ID',
      'user_login',
      'user_pass',
      'user_nicename',
      'user_email',
      'user_url',
      'user_registered',
      'user_activation_key',
      'user_status',
      'display_name',
    );

    //* Returns columns, hidden, sortable */
    list( $columns, $hidden ) = $this->get_column_info();
    
    //** Build aoColumns for ajax return */
    $column_count = 0;
    foreach($columns as $column_slug => $column_title) {

      if(in_array( $column_slug, $hidden )) {
        $column_visible = 'false';
      } else {
        $column_visible = 'true';
      }

      //** Determine if column can be been sortable */
      $column_sortable = 'false';
      $attr = str_replace( 'wp_crm_', '', $column_slug );
      if( in_array( $attr, $pr_columns ) ) {
        $column_sortable = 'true';
      } else {
        if( key_exists( $attr, $wp_crm[ 'data_structure' ][ 'attributes' ] ) ) {
          if( empty( $wp_crm['data_structure']['attributes'][$attr][ 'option_keys' ] ) ) {
            $column_sortable = 'true';
          }
        }
      }

	  if($attr == 'cb'){
        $extra_classs = ' checkbox-column';
	  }else{
		$extra_classs = ' noncb';
	  }

      $this->aoColumns[] = "{ 'sClass': '{$column_slug} column-{$column_slug}$extra_classs', 'bVisible': {$column_visible}, 'bSortable': {$column_sortable}}";
      $this->aoColumnDefs[] = "{ 'sName': '{$column_slug}', 'aTargets': [{$column_count}]}";
      $this->column_ids[$column_count] = $column_slug;
      $column_count++;
   }

    $this->_args['iColumns'] = count($this->aoColumns);

  }

  /**
   *
   * @param type $args
   */
  function data_tables_script($args = '') {

    $args = wp_parse_args($args, array());
	//print_r($args);

    ?>

    <script type="text/javascript">

      var wp_list_table;
      var wp_list_counts = {};
      var wp_table_column_ids = {}
    <?php foreach ($this->column_ids as $col_id => $col_slug) { ?>
        wp_table_column_ids['<?php echo $col_slug; ?>'] = '<?php echo $col_id; ?>';
    <?php } ?>

      jQuery(document).ready(function() {

        wp_list_table = jQuery("#wp-list-table").dataTable({
          "sPaginationType": "full_numbers",
          "sDom": '<"crm-msg-bulk-action">iprt<"crm-msg-bulk-action">lp',
          "iDisplayLength": 25,
          "bAutoWidth": false,
          "asStripClasses": ['wp_crm_row odd_row', 'wp_crm_row even_row'],
          "oLanguage": {
            "sLengthMenu": 'Display <select><option value="25">25 </option><option value="50">50 </option><option value="100">100</option><option value="-1">All </option></select> records',
            "sProcessing": '<div class="ajax_loader_overview"></div>'
          },
          "iColumns": <?php echo count($this->aoColumnDefs); ?>,
          "bProcessing": true,
          "bServerSide": true,
          "aoColumnDefs": [<?php echo implode(',', $this->aoColumnDefs); ?>],
          "sAjaxSource": ajaxurl + '?&action=<?php echo $this->_args['ajax_action']; ?>',
          "fnServerData": function(sSource, aoData, fnCallback) {

            aoData.push({
              name: 'wp_crm_filter_vars',
              value: jQuery('#wp-crm-filter').serialize()
            });

            jQuery.ajax({
              "dataType": 'json',
              "type": "POST",
              "url": sSource,
              "data": aoData,
              "success": function(data, textStatus, jqXHR) {
                wp_list_counts.user_ids = data.user_ids;
                wp_list_counts.total_returned = data.iTotalRecords;
                fnCallback(data, textStatus, jqXHR);
              }
            });

          },
          "aoColumns": [<?php echo implode(",", $this->aoColumns); ?>],
          "fnDrawCallback": function(data) {
            wp_list_table_do_columns();
          }
        });
        var bulkWrapper = jQuery('.crm-msg-bulk-action');
        var bulkAction = jQuery('<select></select>');
        bulkAction.append('<option value="">Bulk Action</option>');
        bulkAction.append('<option value="trash_user">Delete</option>');
        bulkWrapper.append(bulkAction);

        var btnAction = jQuery('<button type="submit" id="doaction" class="button action" >Apply</button>');
        btnAction.on('click', function(event){
          var ids = [];
          var action = jQuery(this).siblings('select').val();
		  if(action=='trash_user'){
			  if(!confirm("<?php _e( "Confirm Delition" , ud_get_wp_crm()->domain );?>")){
				  return false;
			  }
		  }
		  
          var selected = jQuery('[name="users[]"]:checked');
          event.preventDefault();

          if(action != '' && selected.length > 0){
            selected.each(function(i, item){
              ids.push(jQuery(this).val());
            });
            jQuery.post({
              url: ajaxurl,
              dataType: "json",
              data: {
                action : 'wp_crm_quick_action',
                object_id: ids,
                wp_crm_quick_action: action
              },
              success: function(result) {
                switch( result.action ) {
                  case 'hide_element':
                    selected.each(function(i, item){
                      jQuery(this).parent().parent().remove();
                    });
                  break;
                }
              }
            });
          }
          return false;
        });
        bulkWrapper.append(btnAction);

        jQuery("#wp-crm-filter").submit(function(event) {
          event.preventDefault();
          wp_list_table.fnDraw();
          return false;
        });

        jQuery('.metabox-prefs .hide-column-tog').click(function() {
          wp_list_table_do_columns();
        });


      });

      //** Cycle through rows and update the odd / even classes */
      function wp_list_table_rebrand_rows() {
        jQuery("#wp-list-table .wp_crm_row").removeClass("even_row odd_row");
        jQuery("#wp-list-table .wp_crm_row:odd").addClass("odd_row");
        jQuery("#wp-list-table .wp_crm_row:even").addClass("even_row");

      }

      //** Check which columns are hidden, and hide data table columns */
      function wp_list_table_do_columns() {

        var visible_columns = jQuery('.hide-column-tog').filter(':checked').map(function() {
          return jQuery(this).val();
        });
        var hidden_columns = jQuery('.hide-column-tog').filter(':not(:checked)').map(function() {
          return jQuery(this).val();
        });

        jQuery.each(visible_columns, function(key, row_class) {
          jQuery('#wp-list-table .column-' + row_class).show();
        });

        jQuery.each(hidden_columns, function(key, row_class) {
          jQuery('#wp-list-table .column-' + row_class).hide();
        });

      }
    </script>
  <?php
  }

  /**
   * Get search results based on query.
   *
   * @todo Needs to be updated to handle the AJAX requests.
   *
   */
  function prepare_items( $wp_crm_search = false, $args = array() ) {
    global $role, $usersearch;

    $args = wp_parse_args( $args, array(
      'order_by' => 'user_registered',
      'sort_order' => 'DESC',
    ));

    if(!isset($this->all_items)) {
      $this->all_items = WP_CRM_F::user_search( $wp_crm_search, $args );
    }

    //** Get User IDs */
    foreach($this->all_items as $object) {
      $this->user_ids[] = $object->ID;
    }

     //** Do pagination  */


    if($this->_args['per_page'] != -1) {
      $this->item_pages = array_chunk($this->all_items, $this->_args['per_page']);

      $total_chunks = count($this->item_pages);

      //** figure out what page chunk we are on based on iDisplayStart
      $this_chunk = ($this->_args['iDisplayStart'] / $this->_args['per_page']);

      //** Get page items */
      $this->items = !empty($this->item_pages[$this_chunk])?$this->item_pages[$this_chunk]:array();

      if(is_array($this->items)) {
        foreach($this->items as $object) {
          $this->page_user_ids[] = $object->ID;
        }
      }

    } else {
      $this->items = $this->all_items;
    }


  }


  /**
   * Display the search box.
   *
   *
   */
  function search_box( $text, $input_id, $placeholder = "" ) {

    ?>
  <p class="search-box">
    <input type="text" id="<?php echo $input_id ?>" name="wp_crm_search[search_string]" value="<?php echo !empty($_REQUEST['wp_crm_search']['search_string'])?$_REQUEST['wp_crm_search']['search_string']:''; ?>" placeholder="<?php echo $placeholder;?>" />
  </p>
  <?php
  }


  function ajax_user_can() {
    if ( $this->is_site_users )
      return current_user_can( 'manage_sites' );
    else
      return current_user_can( 'list_users' );
  }




  /**
   * Display the bulk actions dropdown.
   *
   * @since 3.1.0
   * @access public
   */
  function bulk_actions( $which = '' ) {
    $screen = get_current_screen();

    if ( is_null( $this->_actions ) ) {
      // This filter can currently only be used to remove actions.
      $this->_actions = apply_filters( 'bulk_actions-' . $screen->id, $this->_actions );

      $two = '';
    } else {
      $two = '2';
    }

    if ( empty( $this->_actions ) )
      return;

    echo "<select name='action$two'>\n";
    echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', ud_get_wp_crm()->domain ) . "</option>\n";
    foreach ( $this->_actions as $name => $title )
      echo "\t<option value='$name'>$title</option>\n";
    echo "</select>\n";

    submit_button( __( 'Apply', ud_get_wp_crm()->domain ), 'button-secondary action', false, false, array( 'id' => "doaction$two" ) );
    echo "\n";
  }

  /**
   * 
   */
  function display_rows_or_placeholder() {

    if ( $this->has_items() ) {
      $this->display_rows();
    } else {
      list( $columns, $hidden ) = $this->get_column_info();
      echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
      $this->no_items();
      echo '</td></tr>';
    }
  }


  /**
   * Generate HTML for a single row on the users.php admin panel.
   *
   */
  function single_row($user_id) {

    if (is_object($user_id)) {
      $user_id = $user_id->ID;
    }

    $user_object = wp_crm_get_user($user_id);
    $user_object = apply_filters('wp_crm_user_list_table_object', $user_object);

    $r = "<tr id='user-$user_id'>";

    list( $columns, $hidden ) = $this->get_column_info();

    foreach ($columns as $column_name => $column_display_name) {
      $class = "class=\"$column_name column-$column_name\"";

      $style = '';

      if (in_array($column_name, $hidden)) {
        $style = ' style="display:none;"';
      }

      $attributes = "$class$style";

      $r .= "<td {$attributes}>";
      $single_cell = $this->single_cell($column_name, $user_object, $user_id);

      //** Need to insert some sort of space in there to avoid DataTable error that occures when "null" is returned */
      $ajax_cells[] = ' ' . $single_cell;
      $r .= $single_cell;
      $r .= "</td>";
    }
    
    $r .= '</tr>';

    if ($this->_args['ajax']) {
      return $ajax_cells;
    }

    return $r;
  }

  /**
   * 
   * @global type $wp_crm
   * @param type $full_column_name
   * @param type $user_object
   * @param type $user_id
   * @return type
   */
  function single_cell($full_column_name, $user_object, $user_id) {
      global $wp_crm;
      
      $r = '';

      $column_name = str_replace('wp_crm_', '', $full_column_name);

      $this_attribute = !empty($wp_crm['data_structure']['attributes'][$column_name])?$wp_crm['data_structure']['attributes'][$column_name]:false;

      switch ( $column_name ) {

        case 'cb':
          $r .= "<input type='checkbox' name='users[]' id='user_{$user_id}' class='single-user-chk' value='{$user_id}' />";
        break;

        case 'user_card':

          $r .= WP_CRM_F::render_user_card(array(
            'user_id' => $user_id,
            'user_object' => $user_object,
            'full_column_name' => $full_column_name,
            'show_user_actions' => true
          ));

        break;

        case 'role':
          $r .= $role_name;
          break;
        case 'posts':

          if ( $numposts > 0 ) {
            $r .= "<a href='edit.php?author=$user_id' title='" . esc_attr__( 'View posts by this author', ud_get_wp_crm()->domain ) . "' class='edit'>";
            $r .= $numposts;
            $r .= '</a>';
          } else {
            $r .= 0;
          }

        break;

        default:

        if( !empty( $user_object[ $column_name] ) && is_array( $user_object[ $column_name] ) ) {
          foreach($user_object[$column_name] as $option_slug =>  $values) {

            if(($this_attribute['input_type'] == 'text' || $this_attribute['input_type'] == 'date' || $this_attribute['input_type'] == 'textarea') && !empty($this_attribute['has_options'])) {
              //** We have a text input with options (dropdown) */

              $r .= wp_crm_get_value($column_name, $user_id);

            } elseif( !empty($wp_crm['data_structure']['attributes'][$column_name]['has_options']) ) {

              //** Get label and only show when enabled */
              $visible_options = WP_CRM_F::list_options($user_object, $column_name);

            } else {
              //** Regular value, no need to get option title */
              foreach($values as $single_value) {
                $visible_options[] = nl2br($single_value);
              }
            }
          }
        }

        if( !empty( $visible_options ) && is_array( $visible_options ) ) {
          foreach($visible_options as $key => $single_value) {
            $visible_options[$key] = nl2br($single_value);
          }
          $r .= '<ul><li>' . implode('</li><li>', $visible_options) . '</li></ul>';
        }

        $r = apply_filters('wp_crm_overview_cell', $r, array('column_name' => $column_name, 'user_object' => $user_object, 'user_id' => $user_id));


        break;
      }

      return $r;

  }


    /**
     * Displays the primary filtering for the table, should be able to work alongside the search query.
     *
     * @since 0.01
     *
    */
    function views() {
        global $wp_crm;

        $screen = get_current_screen();

        $views = $this->get_views();
        $views = apply_filters( 'views_' . $screen->id, $views );

        $search = !empty($_REQUEST['wp_crm_search'])?$_REQUEST['wp_crm_search']:array();

        if (empty($views)) {
          return;
        }

        echo "<ul class='wp_crm_overview_filters'>\n";
        echo "<li class='wpp_crm_filter_section_title'>" . __('Role Lists', ud_get_wp_crm()->domain) . " <a class='wpp_crm_filter_show'>". __('Show', ud_get_wp_crm()->domain) ."</a></li>";

        if(is_array($views)) {
          foreach ( $views as $class => $view ) {
            $views[$class] = "\t<li class='$class wp_crm_checkbox_filter'>$view";
          }
        }

        echo implode( " </li>\n", $views) . "</li>\n";
        echo "</ul>";

        //** Get all fiterable keys - for now just checkboxes */
        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
          foreach($wp_crm['data_structure']['attributes'] as $meta_key => $meta_data) {
            if($meta_data['input_type'] == 'checkbox' || $meta_data['input_type'] == 'dropdown') {
                $filterable_keys[$meta_key] = $meta_data;
            }
          }
        }

        $filterable_keys = apply_filters('wp_crm_filterable_keys',$filterable_keys, 'overview_page');

        if(is_array($filterable_keys)) {
          foreach($filterable_keys as $main_key => $meta_data) {

              $filterable_keys_display[] = "<ul class='wp_crm_overview_filters'>";
              $filterable_keys_display[] = "<li class='wpp_crm_filter_section_title'>{$meta_data['title']}<a class='wpp_crm_filter_show'>".__('Show', ud_get_wp_crm()->domain)."</a></li>";

              if($meta_data['has_options']) {

                foreach($meta_data['option_labels'] as $option_slug => $option_label){

                  $option_full_key = $wp_crm['data_structure']['attributes'][$main_key]['option_keys'][$option_slug];

                  $filterable_keys_display[] = "
                      <li class='wp_crm_checkbox_filter'>
                      <input ". ( !empty($search[$main_key]) && is_array($search[$main_key]) && in_array($option_slug, $search[$main_key]) ? "checked" : "") ." type='checkbox' name=wp_crm_search[{$main_key}][] value='$option_slug' class='{$option_slug}_class' id='wp_crm_filterable_key_{$option_full_key}' />
                      <label for='wp_crm_filterable_key_{$option_full_key}'>{$option_label}</label>
                      </li>";
                }

              } else {

                  $filterable_keys_display[] = "
                      <li class='wp_crm_checkbox_filter'>
                      <input ". (is_array($search[$main_key])  ? "checked" : "") ." type='checkbox' name=wp_crm_search[{$main_key}] value='$main_key' class='{$main_key}_class' id='wp_crm_filterable_key_{$main_key}' />
                      <label for='wp_crm_filterable_key_{$main_key}'>{$meta_data[title]}</label>
                      </li>";

              }

              if ( isset($options) && count( $options ) > 0 ) {
                  $filterable_keys_display[] = '</div>';
              }

              $filterable_keys_display[] = "</ul>";
          }
        }

        if(is_array($filterable_keys_display)) {
            echo implode("\n", $filterable_keys_display );
        }
    }


  function get_views() {
    global $wp_roles, $role;

    if ( $this->is_site_users ) {
      $url = 'site-users.php?id=' . $this->site_id;
      switch_to_blog( $this->site_id );
      $users_of_blog = count_users();
      restore_current_blog();
    } else {
      $url = 'admin.php?page=wp_crm';
      $users_of_blog = count_users();
    }
    $total_users = $users_of_blog['total_users'];
    $avail_roles =& $users_of_blog['avail_roles'];
    unset($users_of_blog);

    $current_role = false;
    $role = !empty($_REQUEST['role'])?$_REQUEST['role']:'';
    $class = empty($role) ? ' checked="checked"' : '';
    $role_links = array();

    $role_links['all'] = "<input type='radio' id='wp-crm-all' $class /> <label for='wp-crm-all'>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users' ), number_format_i18n( $total_users ) ).'</label>';
    foreach ( $wp_roles->get_names() as $this_role => $name ) {
      if ( !isset($avail_roles[$this_role]) )
        continue;

      $class = '';
      if (!empty($role) && in_array($this_role, $role)) {
        $current_role = $this_role;
        $class = 'checked="checked"';
      }

      $name = translate_user_role( $name );
      /* translators: User role name with count */
      $name = sprintf( __('%1$s <span class="count">(%2$s)</span>', ud_get_wp_crm()->domain), $name, $avail_roles[$this_role] );
      $role_links[$this_role] = "<input type='radio' $class name='wp_crm_search[wp_role][]' value='$this_role' id='wp-crm-$this_role' class='wp_crm_role_list'/> <label for='wp-crm-$this_role'>$name</label>";
    }

    return $role_links;
  }

  function get_bulk_actions() {
    $actions = array();

    if ( WP_CRM_F::current_user_can_manage_crm() ){
      if ( is_multisite() ) {
          $actions['remove'] = __( 'Remove', ud_get_wp_crm()->domain );
      } else {
          $actions['delete'] = __( 'Delete', ud_get_wp_crm()->domain );
      }
    }

    return $actions;
  }

  function extra_tablenav( $which ) {
  }

  function current_action() {
    if ( isset($_REQUEST['changeit']) && !empty($_REQUEST['new_role']) )
      return 'promote';

    return parent::current_action();
  }

  /**
   * Keep it simple here.  Mostly to be either replaced by child classes, or hookd into
   *
   */
/*  function single_cell($full_column_name, $object, $object_id) {

    $object = (array) $object;

    $r = '';

    $column_name = str_replace('wp_crm_', '', $full_column_name);

    $cell_data = array(
        'table_scope' => $this->_args['table_scope'],
        'column_name' => $column_name,
        'object_id' => $object_id,
        'object' => $object
    );

    switch ($column_name) {

      case 'cb':
        $r .= "<input type='checkbox' name='users[]' id='user_{$object_id}'  value='{$object_id}' />";
        break;

      default:
        $r .= apply_filters('wp_list_table_cell', $cell_data);
        break;
    }

    return $r;
  }

*/
}
