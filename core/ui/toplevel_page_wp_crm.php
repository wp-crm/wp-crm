<?php

if($_REQUEST['message'] == 'user_deleted') {
  WP_CRM_F::add_message(__('User has been deleted and all associated posts have been trashed.', 'wp_crm'));
}

if($_REQUEST['message'] == 'plugin_updated') {
  WP_CRM_F::add_message(__('WP-CRM has been updated, and any premium features your domain qualifies for have been downloaded.', 'wp_crm'));
}

include WP_CRM_Path . '/core/class_user_list_table.php';

$wp_list_table = new CRM_User_List_Table("per_page=25");
$wp_list_table->prepare_items();
$wp_list_table->data_tables_script();

?>
<div class="wp_crm_overview_wrapper wrap">
<div class="wp_crm_ajax_result"></div>
    <?php screen_icon(); ?>
    <h2><?php _e('CRM - All People', 'wp_crm'); ?> <?php if(current_user_can('WP-CRM: View Profiles') && (current_user_can('create_users') || current_user_can('add_users'))) { ?><a href="<?php echo admin_url('admin.php?page=wp_crm_add_new'); ?>" class="button add-new-h2"><?php _e('Add New', 'wp_crm'); ?></a><?php } ?></h2>
    <?php WP_CRM_F::print_messages(); ?>

    <form id="wp-crm-filter" action="#" method="POST">
    <?php if(!CRM_UD_F::is_older_wp_version('3.4')) : ?>
    <div id="poststuff" class="crm-wp-v34">
      <div id="post-body" class="metabox-holder <?php echo 2 == $screen_layout_columns ? 'columns-2' : 'columns-1'; ?>">
        <div id="post-body-content">
          <?php $wp_list_table->display(); ?>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div id="side-sortables" class="meta-box-sortables ui-sortable">
            <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
          </div>
        </div>
      </div>
    </div><!-- /poststuff -->
    <?php else : ?>
    <div id="poststuff" class="<?php echo $current_screen->id; ?>_table metabox-holder <?php echo 2 == $screen_layout_columns ? 'has-right-sidebar' : ''; ?>">
      <div class="wp_crm_sidebar inner-sidebar">
        <div class="meta-box-sortables ui-sortable">
          <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
        </div>
      </div>
      <div id="post-body">
        <div id="post-body-content">
          <?php $wp_list_table->display(); ?>
        </div><!-- /.post-body-content -->
      </div><!-- /.post-body -->
      <br class="clear" />
    </div><!-- /#poststuff -->
    <?php endif; ?>
    </form>

</div><!-- /.wp_crm_overview_wrappe -->
