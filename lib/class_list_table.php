<?php

if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
} else {
  return;
}

class WP_CMR_List_Table extends WP_List_Table {
  
  var $_args;
  
  var $aoColumns;
  
  var $aoColumnDefs;
  
  var $column_ids;
  
  var $is_site_users;

  var $all_items;

  var $item_pages;

  var $items;

  /**
   * Setup options mostly.
   *
   * @todo Get list of displayed columns from options
   *
   */
  function __construct($args = '') {

    $args = wp_parse_args($args, array(
        'plural' => '',
        'iColumns' => 3,
        'per_page' => 20,
        'iDisplayStart' => 0,
        'ajax_action' => 'wp_crm_list_table',
        'current_screen' => '',
        'table_scope' => '',
        'singular' => '',
        'ajax' => false
            ));

    $this->_args = $args;

    if (empty($this->_args['current_screen'])) {
      $screen = get_current_screen();
      $this->_args['current_screen'] = $screen->id;
    }

    //* Returns columns, hidden, sortable */
    list( $columns, $hidden, $sortable ) = $this->get_column_info();

    //** Build aoColumns for ajax return */
    $column_count = 0;
    foreach ($columns as $column_slug => $column_title) {

      $extra_classs = '';
      if (in_array($column_slug, $hidden)) {
        $column_visible = 'false';
      } else {
        $column_visible = 'true';
      }

      $column_sortable = isset($sortable[$column_slug]) ? 'true' : 'false';

      if($column_slug == 'cb')
        $extra_classs = ' check-column';
      $this->aoColumns[] = "{ 'sClass': '{$column_slug} column-{$column_slug}$extra_classs', 'bVisible': {$column_visible}}";
      $this->aoColumnDefs[] = "{ 'sName': '{$column_slug}', 'aTargets': [{$column_count}], 'bSortable': {$column_sortable}}";
      $this->column_ids[$column_count] = $column_slug;
      $column_count++;
    }

    $this->_args['iColumns'] = count($this->aoColumns);
  }

  /**
   * Whether the table has items to display or not
   *
   */
  function has_items() {
    return !empty($this->all_items);
  }

  /**
   * 
   * @param type $args
   */
  function data_tables_script($args = '') {

    $args = wp_parse_args($args, array());
    
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
        bulkAction.append('<option value="archive_message">Archive</option>');
        bulkAction.append('<option value="trash_message">Delete</option>');
        bulkWrapper.append(bulkAction);

        var btnAction = jQuery('<button type="submit" id="doaction" class="button action" >Apply</button>');
        btnAction.on('click', function(event){
          var ids = [];
          var action = jQuery(this).siblings('select').val();
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
   * Get a list of all, hidden and sortable columns, with filter applied
   *
   * @since 3.1.0
   * @access protected
   *
   * @return array
   */
  function get_column_info() {

    if (isset($this->_column_headers)) {
      return $this->_column_headers;
    }

    $screen = convert_to_screen($this->_args['current_screen']);

    $columns = get_column_headers($screen);

    $hidden = get_hidden_columns($screen);

    $_sortable = apply_filters("manage_{$screen->id}_sortable_columns", $this->get_sortable_columns());

    $sortable = array();
    foreach ($_sortable as $id => $data) {
      if ( empty( $data ) ) {
        continue;
      }

      $data = (array) $data;
      if ( !isset( $data[1] ) ) {
        $data[1] = false;
      }

      $sortable[$id] = $data;
    }

    $primary_column = null;

    if( method_exists( $this, 'get_primary_column_name' ) ) {
      foreach( $columns as $col => $column_name ) {
        $primary_column = $col;
        break;
      }
    }

    $this->_column_headers = array($columns, $hidden, $sortable, $primary_column);

    return $this->_column_headers;
  }

  /**
   * Get search results based on query.
   *
   * @todo user_search() should be removed from here since this is a "general" function
   *
   */
  function prepare_items($wp_crm_search = false, $args = array()) {

    $args = wp_parse_args($args, array(
        'order_by' => 'user_registered',
        'sort_order' => 'DESC',
    ));

    if (!isset($this->all_items)) {
      $this->all_items = WP_CRM_F::user_search($wp_crm_search, $args);
    }

    //** Do pagination  */
    if ($this->_args['per_page'] != -1) {
      $this->item_pages = array_chunk($this->all_items, $this->_args['per_page']);

      //** figure out what page chunk we are on based on iDisplayStart
      $this_chunk = ($this->_args['iDisplayStart'] / $this->_args['per_page']);

      //** Get page items */
      $this->items = !empty($this->item_pages[$this_chunk])?$this->item_pages[$this_chunk]:array();
    } else {
      $this->items = $this->all_items;
    }
  }

  /**
   * Display Rows
   */
  function display_rows() {
    //** Query the post counts for this page */
    if (!$this->is_site_users) {
      $post_counts = count_many_users_posts(array_keys($this->items));
    }

    $style = '';
    foreach ($this->items as $userid => $user_object) {

      $role = '';

      if (!empty($user_object->roles)) {
        $role = reset($user_object->roles);
      }

      if (is_multisite() && empty($role))
        continue;

      $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
      echo "\n\t", $this->single_row($user_object, $style, $role, isset($post_counts) ? $post_counts[$userid] : 0 );
    }
  }

  /**
   * Display the table
   *
   * @since 3.1.0
   * @access public
   */
  function display() {
    ?>
    <div class="wp_crm_above_overview_table"></div>
    <table id="wp-list-table" class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>" cellspacing="0">
      <thead>
        <tr>
    <?php $this->print_column_headers(); ?>
        </tr>
      </thead>

      <tfoot>
        <tr>
    <?php $this->print_column_headers(false); ?>
        </tr>
      </tfoot>

      <tbody id="the-list">
    <?php $this->display_rows_or_placeholder(); ?>
      </tbody>
    </table>
    <?php
  }

  /**
   * 
   * @return string
   */
  function no_items() {

    //** DataTables expects a set number of columns */
    $result[0] = '';
    $result[1] = __('Nothing found.', ud_get_wp_crm()->domain);

    if (count($result) < $this->_args['iColumns']) {

      $add_columns = ($this->_args['iColumns'] - count($result));

      //** Add some blank rows to not break json result array */
      $i = 1;
      while ($i <= $add_columns) {
        $result[] = '';
        $i++;
      }
    }

    return $result;
  }

  /**
   * Generate HTML for a single row on the users.php admin panel.
   *
   */
  function single_row($object) {

    $data = array(
        'table_scope' => $this->_args['table_scope'],
        'object' => $object
    );

    $object = apply_filters('wp_crm_list_table_object', $data);

    $object = (array) $object;
    $object_id = $object['ID'];

    $r = "<tr id='user-$object_id' class='wp_crm_parent_element'>";

    list( $columns, $hidden ) = $this->get_column_info();

    foreach ($columns as $column_name => $column_display_name) {

      $class = "class=\"$column_name column-$column_name\"";

      $style = '';

      if (in_array($column_name, $hidden)) {
        $style = ' style="display:none;"';
      }

      $attributes = "$class$style";

      $r .= "<td {$attributes}>";
      $single_cell = $this->single_cell($column_name, $object, $object_id);

      //** Need to insert some sort of space in there to avoid DataTable error that occures when "null" is returned */
      $ajax_cells[] = ' ' . $single_cell;

      $r .= $single_cell;
      $r .= "</td>";
    }

    $r .= '</tr>';

    if ($this->_args['ajax']) {
      return isset( $ajax_cells ) ? $ajax_cells : array();
    }

    return $r;
  }

  /**
   * Keep it simple here.  Mostly to be either replaced by child classes, or hookd into
   *
   */
  function single_cell($full_column_name, $object, $object_id) {

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

}