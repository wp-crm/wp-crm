<?php

global $wp_roles;

if(isset($_REQUEST['message'])) {

  switch($_REQUEST['message']) {

    case 'updated':
    WP_CRM_F::add_message('Settings updated.');
    break;
  }
}

if(empty($wp_crm['notifications'])) {
  $wp_crm['notifications']['example']['subject'] = "Subject";
  $wp_crm['notifications']['example']['to'] = "[user_email]";
  $wp_crm['notifications']['example']['message'] = "Hello [display_name], \n\n Thank you for your message.";
  $wp_crm['notifications']['example']['send_from'] = get_bloginfo('admin_email');
}

if(empty($wp_crm['data_structure']['attributes'])) {
  $wp_crm['data_structure']['attributes'] = array('user_email' => array('title' => 'Email', 'primary' => 'true'));
}

  $parseUrl = parse_url(trim(get_bloginfo('url')));
  $this_domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));


?>

<style type="text/css" media="screen">
  .ace_editor { 
    height: 200px;
    margin: 0;
  }
</style>

 <script type="text/javascript">

  /* Build trigger action argument array */
  var notification_action_arguments = new Array();

  jQuery(document).ready(function() {
    jQuery("#wp_crm_settings_tabs").tabs({ cookie: { expires: 30,
      name: 'wpc_settings_page_tabs' } });

    // Check plugin updates
    jQuery(".wp_crm_ajax_check_plugin_updates").click(function() {

      jQuery('.plugin_status').remove();

      jQuery.post(ajaxurl, {
          action: 'wp_crm_check_plugin_updates'
          }, function(data) {

          message = "<div class='plugin_status updated fade'><p>" + data + "</p></div>";
          jQuery(message).insertAfter("h2");
        });
    });
    
  });



  <?php

  $notification_action_arguments = apply_filters('wp_crm_trigger_action_arguments', array());

  if(is_array($notification_action_arguments)) {
    foreach($notification_action_arguments as $action_slug => $action_args) {

      if(!is_array($action_args)) {
        continue;
      }

      ?>
      notification_action_arguments["<?php echo $action_slug; ?>"] = new Array();

      <?php foreach($action_args as $arg => $title) { ?>
      notification_action_arguments["<?php echo $action_slug; ?>"]["<?php echo $arg; ?>"] = "<?php echo $title; ?>";
      <?php } ?>

      <?php

    }
  }

  ?>



 </script>

<div class="wrap">
<h2><?php _e('CRM Settings',ud_get_wp_crm()->domain); ?></h2>

<?php WP_CRM_F::print_messages(); ?>

<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=wp_crm_settings'); ?>"  enctype="multipart/form-data" >
<?php wp_nonce_field('wp_crm_setting_save'); ?>

<div id="wp_crm_settings_tabs" class="wp_crm_settings_tabs clearfix">
  <ul class="tabs">
    <li><a href="#tab_main"><?php _e('Main',ud_get_wp_crm()->domain); ?></a></li>
    <li><a href="#tab_ui"><?php _e('UI',ud_get_wp_crm()->domain); ?></a></li>
    <li><a href="#tab_user_data"><?php _e('Data',ud_get_wp_crm()->domain); ?></a></li>
    <li><a href="#tab_user_roles"><?php _e('Roles',ud_get_wp_crm()->domain); ?></a></li>
    <li><a href="#tab_notifications"><?php _e('Notifications',ud_get_wp_crm()->domain); ?></a></li>
      <?php

        $wp_crm_plugin_settings_nav = apply_filters('wp_crm_settings_nav', array());

        if(is_array($wp_crm_plugin_settings_nav)) {
          foreach($wp_crm_plugin_settings_nav as $feature_slug => $nav) {

            if( !empty($wp_crm['available_features'][$feature_slug]['status']) && $wp_crm['available_features'][$feature_slug]['status'] === 'disabled') {
              continue;
            }

            echo "<li><a href='#tab_{$nav['slug']}'>{$nav['title']}</a></li>\n";
          }
        }

    ?>
    <li><a href="#tab_troubleshooting"><?php _e('Help',ud_get_wp_crm()->domain); ?></a></li>
    <li><a href="#tab_feedback"><?php _e('Support',ud_get_wp_crm()->domain); ?></a></li>

  </ul>

  <div id="tab_feedback">
      <div class="wp_crm_inner_tab">
        <!--[if lte IE 8]>
        <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
        <![endif]-->
        <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
        <script>
          hbspt.forms.create({
            portalId: '3453418',
            formId: '60714dc0-f1bf-42c4-849c-a8736b16a60b'
          });
        </script>
      </div>
  </div>

  <div id="tab_main">

    <table class="form-table">
      <tr>
        <th>
          <?php _e('General Settings',ud_get_wp_crm()->domain); ?>
        </th>
        <td>
          <ul>
            <li>
              <input id="replace_default_user_page" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['replace_default_user_page'])?$wp_crm['configuration']['replace_default_user_page']:false, 'true'); ?> name="wp_crm[configuration][replace_default_user_page]" />
              <label for="replace_default_user_page"><?php _e('Replace default WordPress User page with WP-CRM.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input id="allow_account_creation_with_no_email" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['allow_account_creation_with_no_email'])?$wp_crm['configuration']['allow_account_creation_with_no_email']:false, 'true'); ?> name="wp_crm[configuration][allow_account_creation_with_no_email]" />
              <label for="allow_account_creation_with_no_email"><?php _e('Allow user accounts to be created without an e-mail address.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input id="disable_wp_password_reset_email" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['disable_wp_password_reset_email'])?$wp_crm['configuration']['disable_wp_password_reset_email']:false, 'true'); ?> name="wp_crm[configuration][disable_wp_password_reset_email]" />
              <label for="disable_wp_password_reset_email"><?php _e('Disable default WordPress password reset e-mail notification.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input id="wp_crm_do_not_use_nl2br_in_messages" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['do_not_use_nl2br_in_messages'])?$wp_crm['configuration']['do_not_use_nl2br_in_messages']:false, 'true'); ?> name="wp_crm[configuration][do_not_use_nl2br_in_messages]" />
              <label for="wp_crm_do_not_use_nl2br_in_messages"><?php _e('Do not automatically convert line breaks in outgoing contact messages.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input id="wp_crm_track_detailed_user_activity" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['track_detailed_user_activity'])?$wp_crm['configuration']['track_detailed_user_activity']:false, 'true'); ?> name="wp_crm[configuration][track_detailed_user_activity]" />
              <label for="wp_crm_track_detailed_user_activity"><?php _e('Track detailed user activity.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input id="wp_crm_allow_attributes_grouping" value="true" type="checkbox"  <?php checked(!empty($wp_crm['configuration']['allow_attributes_grouping'])?$wp_crm['configuration']['allow_attributes_grouping']:false, 'true'); ?> name="wp_crm[configuration][allow_attributes_grouping]" />
              <label for="wp_crm_allow_attributes_grouping"><?php _e('Enable Attribute Grouping.', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <label for="wp_crm_user_level"><?php _e("Minimum user level to manage WP-CRM", ud_get_wp_crm()->domain)?></label>
              <select id="wp_crm_user_level" name="wp_crm[configuration][user_level]">
                <?php
                $current_value = isset($wp_crm['configuration']['user_level'])?$wp_crm['configuration']['user_level']:8;
                $user_levels = get_editable_roles();
                unset($user_levels['agent']); // Un setting agent, will be set again if agent plugin is active.

                $user_levels = apply_filters('wp_crm_user_level_roles', $user_levels);

                                /*array(
                                  "0" => __('Subscriber', ud_get_wp_crm()->domain),
                                  "1" => __('Contributor', ud_get_wp_crm()->domain),
                                  "2" => __('Author', ud_get_wp_crm()->domain),
                                  "3" => __('Real Estate Agent', ud_get_wp_crm()->domain),
                                  "5" => __('Editor', ud_get_wp_crm()->domain),
                                  "8" => __('Administrator', ud_get_wp_crm()->domain),
                                );*/

                foreach ( $user_levels as $value => $label ) {
                  $level = WP_CRM_F::get_max_level($label['capabilities']);
                  $label = isset($label['name'])?$label['name']:$label;
                  echo '<option value="'.$value.'"';
                  if ( $value == $current_value )
                    echo " selected";
                  echo ">$label ($level)</option>";
                }
                ?>
              </select>
            </li>

          </ul>
        </td>
      </tr>

      <tr class="wp_crm_smart_row">
        <th><?php _e('Automation',ud_get_wp_crm()->domain); ?></th>
        <td>
          <ul>
            <li>
              <input id="wp_crm_standardize_display_name" wrapper="wp_crm_smart_row" toggle_logic="reverse" class="wp_crm_show_advanced" value="true" type="checkbox" <?php checked(!empty($wp_crm['configuration']['standardize_display_name'])?$wp_crm['configuration']['standardize_display_name']:false, 'true'); ?> name="wp_crm[configuration][standardize_display_name]" />
              <label for="wp_crm_standardize_display_name" ><?php _e('Standardize Display Names.', ud_get_wp_crm()->domain); ?></label>
            </li>
            <li class="wp_crm_advanced_configuration">
              <label for="wp_crm_standardize_display_name_rule"><?php _e('Display name components: ', ud_get_wp_crm()->domain); ?></label>
              <input id="wp_crm_standardize_display_name_rule" class="regular-text wp_crm_force_default" default_value="[user_email]" type="text" value="<?php echo esc_attr( !empty($wp_crm['configuration']['display_name_rule'])?$wp_crm['configuration']['display_name_rule']:''); ?>" name="wp_crm[configuration][display_name_rule]" />
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e('Email Settings',ud_get_wp_crm()->domain); ?></th>
        <td>
          <ul>
            <li>
            <input id="wp_crm_default_email" class="regular-text" type="text" value="<?php echo esc_attr($wp_crm['configuration']['default_sender_email']); ?>" name="wp_crm[configuration][default_sender_email]" />
            <label for="wp_crm_default_email"><?php _e('Default sender e-mail address.', ud_get_wp_crm()->domain); ?></label>
            <div class="description"><?php printf(__('If you are not using SMTP, it is advisable to use a @%s email address to avoid being spammed.', ud_get_wp_crm()->domain), $_SERVER['HTTP_HOST']); ?></div>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e('reCAPTCHA keys',ud_get_wp_crm()->domain); ?></th>
        <td>
          <ul>
            <li>
            <input id="wp_crm_recaptcha_site_key" class="regular-text" type="text" value="<?php echo !empty($wp_crm['configuration']['recaptcha_site_key'])? $wp_crm['configuration']['recaptcha_site_key']: ""; ?>" name="wp_crm[configuration][recaptcha_site_key]" />
            <label for="wp_crm_recaptcha_site_key"><?php _e('Site key', ud_get_wp_crm()->domain); ?></label>
            <div class="description"><?php printf(__('', ud_get_wp_crm()->domain)); ?></div>
            </li>
            <li>
            <input id="wp_crm_recaptcha_secret_key" class="regular-text" type="text" value="<?php echo !empty($wp_crm['configuration']['recaptcha_secret_key'])? $wp_crm['configuration']['recaptcha_secret_key']: ""; ?>" name="wp_crm[configuration][recaptcha_secret_key]" />
            <label for="wp_crm_recaptcha_secret_key"><?php _e('Secret key', ud_get_wp_crm()->domain); ?></label>
            <div class="description"><?php printf(__('', ud_get_wp_crm()->domain)); ?></div>
            </li>
          </ul>
          <p><?php _e('See more details in <a href="#" class="open-help-tab" aria-controls="contextual-help-wrap">help tab</a>.', ud_get_wp_crm()->domain); ?></p>
        </td>
      </tr>

      <?php do_action('wp_crm::settings_page::main_tab_bottom'); ?>

    </table>
  </div>

  <div id="tab_ui">

    <table class="form-table">

      <tr>
        <th><?php _e('General UI Settings',ud_get_wp_crm()->domain); ?></th>
        <td>
          <ul>
            <li>
              <input id="do_not_display_user_avatars" value="true" type="checkbox"  <?php checked( !empty($wp_crm['configuration']['do_not_display_user_avatars'])?$wp_crm['configuration']['do_not_display_user_avatars']:false, 'true' ); ?> name="wp_crm[configuration][do_not_display_user_avatars]" />
              <label for="do_not_display_user_avatars"><?php _e('Do not display user avatars on overview pages.', ud_get_wp_crm()->domain); ?></label>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e('Primary User Identifier',ud_get_wp_crm()->domain); ?></th>
        <td>
          <ul>
            <li>
            <select id="wp_crm_primary_user_attribute" name="wp_crm[configuration][primary_user_attribute]">
              <option value=""> - </option>
                <?php foreach(apply_filters('wp_crm_primary_user_attribute_keys', $wp_crm['data_structure']['attributes']) as $key => $attribute_data) { ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $wp_crm['configuration']['primary_user_attribute']); ?>><?php echo $attribute_data['title']; ?> <?php echo (!empty($attribute_data['quick_description']) ? '(' . $attribute_data['quick_description'] . ')' : ''); ?></option>
            <?php } ?>
            </select>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th>
          <?php _e('Overview Page User Card',ud_get_wp_crm()->domain); ?>
          <div class="description"><?php _e('User data to be displayed in the <b>Information</b> column.', ud_get_wp_crm()->domain); ?></div>
        </th>
        <td>
          <div class="wp-tab-panel">
          <ul>
           <?php foreach(apply_filters('wp_crm_user_card_keys', $wp_crm['data_structure']['attributes']) as $key => $attribute_data): $rand = rand(1000,9999); ?>
            <li>
              <input type="checkbox" value="<?php echo $key; ?>" <?php CRM_UD_UI::checked_in_array($key, !empty($wp_crm['configuration']['overview_table_options']['main_view'])?$wp_crm['configuration']['overview_table_options']['main_view']:''); ?> name="wp_crm[configuration][overview_table_options][main_view][]" id="<?php echo $key.$rand; ?>" />
              <label for="<?php echo $key.$rand; ?>"><?php echo ($attribute_data['title'] ? $attribute_data['title'] : CRM_UD_F::de_slug($key)); ?> <?php echo (!empty($attribute_data['quick_description']) ? '<span class="description">' . $attribute_data['quick_description'] . '</span>' : ''); ?></label>
            </li>
          <?php endforeach; ?>
          </ul>

          </div>
        </td>
      </tr>

      <tr>
        <th>
          <?php _e('Quick User Actions',ud_get_wp_crm()->domain); ?>
          <div class="description"><?php _e('User-specific actions that can be initiated from the user overview page.', ud_get_wp_crm()->domain); ?></div>
        </th>
        <td>
          <ul>
            <?php foreach($wp_crm['overview_user_actions'] as $action => $data) { if(empty($data['label'])) { continue; } ?>
            <li>
              <input type="hidden" name="wp_crm[overview_user_actions][<?php echo $action; ?>][enable]" value="false" />
              <input id="wp_crm_overview_user_actions_<?php echo $action; ?>" type="checkbox" name="wp_crm[overview_user_actions][<?php echo $action; ?>][enable]" value="true" <?php checked( !empty($wp_crm['overview_user_actions'][$action]['enable'])?$wp_crm['overview_user_actions'][$action]['enable']:false, 'true'); ?> />
              <label for="wp_crm_overview_user_actions_<?php echo $action; ?>"><?php printf(__('Enable: %1s',ud_get_wp_crm()->domain), $data['label']); ?></label>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>

    </table>
  </div>

  <div id="tab_notifications">
    <div class="wp_crm_inner_tab">
      <p>
        <?php _e('This tab allows you to create and modify your notifications.  Notifications can be assigned to various events, such as new user registration, general contact messages, etc.', ud_get_wp_crm()->domain); ?>
      </p>
     <table id="wp_crm_notification_messages" class="form-table wp_crm_form_table ud_ui_dynamic_table widefat">
      <thead>
        <tr>
           <th class="wp_crm_message_header_col"><?php _e('Message Header',ud_get_wp_crm()->domain) ?></th>
           <th class="wp_crm_message_col"><?php _e('Message',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_settings_col"><?php _e('Trigger Actions',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_delete_col">&nbsp;</th>
          </tr>
      </thead>
      <tbody>
      <?php  foreach($wp_crm['notifications'] as $notification_slug => $data):  $row_hash = rand(100,999); ?>
        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $notification_slug; ?>"  new_row='false'>
          <td class='wp_crm_message_header_col'>
            <ul class="wp_crm_notification_main_configuration">
              <li>
                <label for=""><?php _e('Subject:', ud_get_wp_crm()->domain); ?></label>
                <input type="text" id="subject_<?php echo $row_hash; ?>" class="slug_setter regular-text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][subject]" value="<?php echo !empty($data['subject'])?$data['subject']:''; ?>" />
              </li>
              <li>
                <label for=""><?php _e('To:', ud_get_wp_crm()->domain); ?></label>
                <input type="text"  id="to_<?php echo $row_hash; ?>"  class="regular-text crm_to_mail" name="wp_crm[notifications][<?php echo $notification_slug; ?>][to]" value="<?php echo !empty($data['to'])?$data['to']:''; ?>" />
             </li>
              <li>
                <label for=""><?php _e('BCC:', ud_get_wp_crm()->domain); ?></label>
                <input type="text"  id="bcc_<?php echo $row_hash; ?>"  class="regular-text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][bcc]" value="<?php echo !empty($data['bcc'])?$data['bcc']:''; ?>"/>
              </li>
              <li>
                <label for=""><?php _e('From:', ud_get_wp_crm()->domain); ?></label>
                <input type="text"  id="send_from_<?php echo $row_hash; ?>"  class="regular-text crm_mail_m" placeholder="Mail" name="wp_crm[notifications][<?php echo $notification_slug; ?>][send_from]" value="<?php echo !empty($data['send_from'])?$data['send_from']:''; ?>"/>
                <input type="text"  id="send_from_<?php echo $row_hash; ?>"  class="regular-text crm_mail_n" placeholder="Name" name="wp_crm[notifications][<?php echo $notification_slug; ?>][send_from_name]" value="<?php echo !empty($data['send_from_name'])?$data['send_from_name']:''; ?>"/>
              </li>
              <li>
                <label for=""><?php _e('Reply To:', ud_get_wp_crm()->domain); ?></label>
                <input type="text"  id="reply_to_<?php echo $row_hash; ?>"  class="regular-text crm_mail_m" placeholder="Mail" name="wp_crm[notifications][<?php echo $notification_slug; ?>][reply_to_mail]" value="<?php echo !empty($data['reply_to_mail'])?$data['reply_to_mail']:''; ?>"/>
                <input type="text"  id="reply_to_<?php echo $row_hash; ?>"  class="regular-text crm_mail_n" placeholder="Name" name="wp_crm[notifications][<?php echo $notification_slug; ?>][reply_to_name]" value="<?php echo !empty($data['reply_to_name'])?$data['reply_to_name']:''; ?>"/>
              </li>

            </ul>
          </td>
          <td>
            <textarea class="html-editor" id="editor_notification_<?php echo $notification_slug; ?>_text" name="wp_crm[notifications][<?php echo $notification_slug; ?>][message]"><?php echo $data['message']; ?></textarea>
            <div id="editor_notification_<?php echo $notification_slug; ?>"></div>

            <?php if( defined( 'WP_CRM_ENABLE_ACE_EDITOR' ) && WP_CRM_ENABLE_ACE_EDITOR ) { ?>
            <script>
              var editor_notification_<?php echo $notification_slug; ?> = ace.edit("editor_notification_<?php echo $notification_slug; ?>");
              var textarea_<?php echo $notification_slug; ?> = jQuery('#editor_notification_<?php echo $notification_slug; ?>_text').hide();
              editor_notification_<?php echo $notification_slug; ?>.getSession().setValue(textarea_<?php echo $notification_slug; ?>.val());
              editor_notification_<?php echo $notification_slug; ?>.getSession().setMode("ace/mode/html");
              editor_notification_<?php echo $notification_slug; ?>.getSession().on('change', function(){
                textarea_<?php echo $notification_slug; ?>.val(editor_notification_<?php echo $notification_slug; ?>.getSession().getValue());
              });
              <?php } ?>
            </script>
          </td>
          <td class="wp_crm_settings_col">

            <?php if(is_array($wp_crm['notification_actions'])) { ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['notification_actions'] as $action_slug => $action_title) { $action_hash = rand(1000,9999);

              if(empty($action_title)) {
                continue;
              }

              ?>
                <li>
                  <input type="checkbox" id="action_<?php echo $action_hash; ?>" <?php CRM_UD_UI::checked_in_array($action_slug, !empty($data['fire_on_action'])?$data['fire_on_action']:array() ); ?> name="wp_crm[notifications][<?php echo $notification_slug; ?>][fire_on_action][]"  value="<?php echo $action_slug; ?>" class="wp_crm_trigger_action" />
                  <label for="action_<?php echo $action_hash; ?>" ><?php echo $action_title; ?></label>
                </li>
              <?php } ?>
            </ul>
            <?php } else { ?>
              <p><?php _e('You do not have any notification actions yet. ', ud_get_wp_crm()->domain); ?></p>
            <?php } ?>
          </td>
          <td valign="middle"><span class="wp_crm_delete_row  button"><?php _e('Delete',ud_get_wp_crm()->domain) ?></span></td>
        </tr>

      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan='4'>
          <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e('Add Row',ud_get_wp_crm()->domain) ?>" />
          </td>
        </tr>
      </tfoot>

      </table>
      <p><?php _e('For the <b>Send From</b> value enter an e-mailed address or a name and e-mail using the folloiwng format: John Smith <john.smith@gmail.com>', ud_get_wp_crm()->domain); ?></p>
      <p><?php _e('To see list of variables you can use in notifications open up the "Help" tab and view the user data structure.  Any variable you see in there can be used in the subject field, to field, BCC field, and the message body. Example: [user_email] would include the recipient\'s e-mail.', ud_get_wp_crm()->domain); ?></p>
      <p><?php _e('To add notification actions use the <b>wp_crm_notification_actions</b> filter, then call the action within <b>wp_crm_send_notification()</b> function, and the messages association with the given action will be fired off.', ud_get_wp_crm()->domain); ?></p>
    <?php do_action('wp_crm_settings_notification_tab'); ?>
    </div>
  </div>



  <div id="tab_user_data">

  <table class="form-table">

  <tr>
    <th><?php _e('General Settings',ud_get_wp_crm()->domain); ?></th>
    <td>


    <table id="wp_crm_attribute_fields" class="ud_ui_dynamic_table widefat">
      <thead>
        <tr>
          <th class='wp_crm_draggable_handle'>&nbsp;</th>
          <th class="wp_crm_attribute_col"><?php _e('Attribute',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_settings_col"><?php _e('Settings',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_type_col"><?php _e('Input Type',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_values_col"><?php _e('Predefined Values',ud_get_wp_crm()->domain) ?></th>
          <th class="wp_crm_delete_col">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($wp_crm['data_structure']['attributes'] as $slug => $data):  $row_hash = rand(100,999); ?>

        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $slug; ?>"  new_row='false'>
        <th class="wp_crm_draggable_handle">&nbsp;</th>

        <td>
          <ul>
            <li>
              <label><?php _e('Title:', ud_get_wp_crm()->domain); ?></label>
              <input class="slug_setter" type="text" name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][title]" value="<?php echo $data['title']; ?>" />
            </li>

            <?php do_action('wp_crm_attributes_before_advanced_list', array('slug'=>$slug)); ?>

            <li class="wp_crm_advanced_configuration">
              <label><?php _e('Note:',ud_get_wp_crm()->domain); ?></label>
               <input type="text" name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][description]" value="<?php echo !empty($data['description'])?$data['description']:''; ?>" />
            </li>

            <li class="wp_crm_advanced_configuration">
              <label><?php _e('Slug:',ud_get_wp_crm()->domain); ?></label>
              <input type="text" class="slug" readonly='readonly' value="<?php echo $slug; ?>" />
            </li>

            <?php do_action('wp_crm_attributes_after_advanced_list', array('slug'=>$slug)); ?>

            <li>
              <span class="wp_crm_show_advanced wp_crm_subtle_link"><?php _e('Toggle Advanced', ud_get_wp_crm()->domain); ?></span>
            </li>

          </ul>
        </td>
        <td>
          <ul>
            <li>
              <input id="<?php echo $row_hash; ?>_primary" value="true" type="checkbox"  <?php checked(!empty($wp_crm['data_structure']['attributes'][$slug]['primary'])?$wp_crm['data_structure']['attributes'][$slug]['primary']:false, 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][primary]" />
              <label for="<?php echo $row_hash; ?>_primary" ><?php _e('Primary', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li>
              <input  id="<?php echo $row_hash; ?>_overview_column"  value="true" type="checkbox"  <?php checked(!empty($wp_crm['data_structure']['attributes'][$slug]['overview_column'])?$wp_crm['data_structure']['attributes'][$slug]['overview_column']:false, 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][overview_column]" />
              <label for="<?php echo $row_hash; ?>_overview_column" ><?php _e('Overview Column', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li class="wp_crm_advanced_configuration">
              <?php if($slug == 'recaptcha'): ?>
                <input value="true" type="hidden" checked="checked" name="wp_crm[data_structure][attributes][recaptcha][required]" />
              <?php endif; ?>
              <input id="<?php echo $row_hash; ?>_required" value="true" type="checkbox"  <?php checked(!empty($wp_crm['data_structure']['attributes'][$slug]['required'])?$wp_crm['data_structure']['attributes'][$slug]['required']:false, 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][required]" <?php if($slug == 'recaptcha') echo 'disabled="disabled"'; ?> />
              <label for="<?php echo $row_hash; ?>_required" ><?php _e('Required', ud_get_wp_crm()->domain); ?></label>
            </li>

            <li class="wp_crm_advanced_configuration">
              <input id="<?php echo $row_hash; ?>_no_edit" value="true" type="checkbox"  <?php checked(!empty($wp_crm['data_structure']['attributes'][$slug]['uneditable'])?$wp_crm['data_structure']['attributes'][$slug]['uneditable']:false, 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][uneditable]" />
              <label for="<?php echo $row_hash; ?>_no_edit" ><?php _e('Uneditable', ud_get_wp_crm()->domain); ?></label>
            </li>

            <?php do_action('wp_crm_data_structure_attributes', array(
                    'slug'     => $slug,
                    'data'     => $data,
                    'row_hash' => $row_hash
                  )); ?>

          </ul>
        </td>
        <td>

            <?php do_action('wp_crm_attributes_before_type', array('slug'=>$slug,'data'=>$data,'row_hash'=>$row_hash)); ?>

            <?php if($slug == 'recaptcha'): ?>
              <input value="recaptcha" type="hidden" name="wp_crm[data_structure][attributes][recaptcha][input_type]" />
            <?php endif; ?>
              <select name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][input_type]" data-slug="<?php echo $slug; ?>" data-input-type="<?php echo $wp_crm['data_structure']['attributes'][$slug]['input_type']; ?>" <?php if($slug == 'recaptcha') echo 'disabled="disabled"'; ?> >
                <?php foreach($wp_crm['configuration']['input_types'] as $this_input_type_slug => $this_input_type_label) { ?>
                <option data-input-type-label="<?php echo $this_input_type_label; ?>" data-input-type-slug="<?php echo $this_input_type_slug; ?>"  value="<?php echo $this_input_type_slug; ?>" <?php selected( isset( $wp_crm['data_structure']['attributes'][$slug]['input_type'] ) ? $wp_crm['data_structure']['attributes'][$slug]['input_type'] : 'text', $this_input_type_slug ); ?> <?php if($this_input_type_slug == 'recaptcha') echo 'disabled="disabled"'; ?>><?php echo $this_input_type_label; ?></option>
                <?php }; ?>
            </select>

            <?php do_action('wp_crm_attributes_after_type', array('slug'=>$slug,'data'=>$data,'row_hash'=>$row_hash)); ?>

          </td>

          <td class='wp_crm_values_col'>
            <?php do_action('wp_crm_attributes_before_options', array('slug'=>$slug,'data'=>$data,'row_hash'=>$row_hash)); ?>
            <textarea  name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][options]"><?php echo !empty($wp_crm['data_structure']['attributes'][$slug]['options'])?htmlentities($wp_crm['data_structure']['attributes'][$slug]['options']):''; ?></textarea>
            <?php do_action('wp_crm_attributes_after_options', array('slug'=>$slug,'data'=>$data,'row_hash'=>$row_hash)); ?>
          </td>

          <td><button class="wp_crm_delete_row  button" <?php if($slug == 'recaptcha') echo 'disabled="disabled"'; ?> ><?php _e('Delete',ud_get_wp_crm()->domain) ?></button></td>
          </tr>

          <?php endforeach; ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan='6'>
            <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e('Add Row',ud_get_wp_crm()->domain) ?>" />
            </td>
          </tr>
        </tfoot>
      </table>
    </tr>
    <?php do_action('wp_crm_after_tab_user_data'); ?>
  </table>
</div>

<div id="tab_user_roles">

  <table class="form-table">

  <tr>
    <th><?php _e('User Roles',ud_get_wp_crm()->domain); ?></th>
    <td>

<table id="" class="ud_ui_dynamic_table widefat">
  <thead>
    <tr>
      <th><?php _e('Role', ud_get_wp_crm()->domain); ?></th>
      <th><?php _e('Hidden Attributes', ud_get_wp_crm()->domain); ?></th>
      <th><?php _e('Capabilities', ud_get_wp_crm()->domain); ?></th>
    </tr>
  </thead>
  <tbody>
  <?php if(is_array($wp_roles->roles)) { foreach($wp_roles->roles as $role_slug => $role) {  $rand_id = rand(1000,9999); ?>
   <tr class="wp_crm_dynamic_table_row" slug="<?php echo $role_slug; ?>"  new_row='false'>

      <td><?php echo $role['name']; ?></td>

        <td >
         <ul class="wp-tab-panel">
          <?php foreach($wp_crm['data_structure']['attributes'] as $key => $attribute_data) { ?>
          <li>
            <input <?php CRM_UD_UI::checked_in_array($key, !empty($wp_crm['hidden_attributes'][$role_slug])?$wp_crm['hidden_attributes'][$role_slug]:array()); ?> id="<?php echo $key; ?>_<?php echo $rand_id; ?>" type="checkbox" name="wp_crm[hidden_attributes][<?php echo $role_slug; ?>][]" value="<?php echo $key; ?>" />
            <label for="<?php echo $key; ?>_<?php echo $rand_id; ?>"><?php echo $attribute_data['title']; ?></label>
          </li>
          <?php } ?>
          </ul>
        </td>
        <td>

        <?php if(is_array($role['capabilities'])) { ?>
          <ul class="wp-tab-panel">
          <?php foreach($role['capabilities'] as $cap_slug => $cap_setting) { ?>
            <li><?php echo $cap_slug; ?></li>
          <?php } ?>
          </ul>
        <?php } else { ?>
        <div class="description"><?php _e('No capabilities found for role.', ud_get_wp_crm()->domain); ?></div>
        <?php } ?>

        </td>
        </tr>
   <?php } }  ?>
  </tbody>
</table>

    </td>
   </tr>

   </table>
 </div>

  <?php
  if(is_array($wp_crm_plugin_settings_nav)) {
    foreach($wp_crm_plugin_settings_nav as $nav) {
      echo "<div id='tab_{$nav['slug']}'>";
      do_action("wp_crm_settings_content_{$nav['slug']}", $wp_crm);
      echo "</div>";
    }
  }

  ?>

  <div id="tab_troubleshooting">
    <div class="wp_crm_inner_tab">

      <div class="wp_crm_settings_block">
        <?php _e('Look up the <b>$wp_crm</b> global settings array. This array stores all the default settings, which are overwritten by database settings, and custom filters.',ud_get_wp_crm()->domain) ?>
        <input type="button" value="<?php _e('Show $wp_crm',ud_get_wp_crm()->domain) ?>" id="wp_crm_show_settings_array"> <span id="wp_crm_show_settings_array_cancel" class="wp_crm_link hidden"><?php _e('Cancel',ud_get_wp_crm()->domain) ?></span>
        <pre id="wp_crm_show_settings_array_result" class="wp_crm_class_pre hidden"><?php echo htmlentities( print_r( $wp_crm, 1 ) ); ?></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Clear WP-CRM Cache. Some shortcodes and widgets use cache, so the good practice is clear it after widget, shortcode changes.',ud_get_wp_crm()->domain) ?>
        <button type="button" id="wp_crm_clear_cache"><?php _e('Clear Cache',ud_get_wp_crm()->domain) ?>&nbsp;<span class='dots'></span></button>
        <div id="clear_cache_status"></div>

      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Show user data structure:',ud_get_wp_crm()->domain) ?>
        <input type="button" value="<?php _e('Show WP_CRM_F::user_object_structure()',ud_get_wp_crm()->domain) ?>" id="" class='wp_crm_toggle_something'> <span id="" class="wp_crm_toggle_something wp_crm_link hidden"><?php _e('Cancel',ud_get_wp_crm()->domain) ?></span>
        <pre class="wp_crm_class_pre hidden"><?php _e('All possible meta keys:',ud_get_wp_crm()->domain); ?>
          <?php print_r(WP_CRM_F::user_object_structure());?>
          <?php _e('Root Only:',ud_get_wp_crm()->domain); ?><?php print_r(WP_CRM_F::user_object_structure('root_only=true'));?>

         </pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Lookup a user object by its ID.',ud_get_wp_crm()->domain) ?>
        <input type="text" value="<?php echo get_current_user_id(); ?>" id="wp_crm_user_id">
        <input type="button" value="<?php _e('Load User',ud_get_wp_crm()->domain) ?>" id="wp_crm_show_user_object">
        <span class="wp_crm_link hidden"><?php _e('Cancel',ud_get_wp_crm()->domain) ?></span>
        <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Get user meta report. Will return an array of common meta keys and a few sample values.  This should be used to help you analyze a website with a lot of existing user meta.',ud_get_wp_crm()->domain) ?>
       <input type="button" value="<?php _e('Get Report',ud_get_wp_crm()->domain) ?>" id="wp_crm_show_meta_report">
        <span class="wp_crm_link hidden"><?php _e('Cancel',ud_get_wp_crm()->domain) ?></span>
        <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e('Generate ',ud_get_wp_crm()->domain) ?> <input type="text" value="5" id="wp_crm_fake_users"> <?php _e('fake users. ',ud_get_wp_crm()->domain) ?>
        <input type="button" value="<?php _e('Generate',ud_get_wp_crm()->domain) ?>" id="wp_crm_generate_fake_users">
        <a href="#" id="wp_crm_delete_fake_users"><?php _e('Delete All Fake Users',ud_get_wp_crm()->domain) ?></a>
       <pre  class="wp_crm_class_pre hidden"></pre>
      </div>

      <div class="wp_crm_settings_block">
        <?php _e("Restore Backup of WP-CRM Configuration", ud_get_wp_crm()->domain); ?>: <input name="wp_crm[settings_from_backup]" type="file" />
        <a href="<?php echo wp_nonce_url( "admin.php?page=wp_crm_settings&message=updated&wp_crm_action=download-wp_crm-backup", 'download-wp_crm-backup'); ?>"><?php _e("Download Backup of Current WP-CRM Configuration.", ud_get_wp_crm()->domain);?></a>
      </div>

      <div class="wp_crm_settings_block">
        <input value="" type="hidden" name="wp_crm[configuration][developer_mode]" />
        <input id="wp_crm_enable_developer_mode" value="true" type="checkbox" <?php checked(!empty($wp_crm['configuration']['developer_mode'])?$wp_crm['configuration']['developer_mode']:false, 'true'); ?> name="wp_crm[configuration][developer_mode]" />
        <label for="wp_crm_enable_developer_mode"><?php _e("Enable developer mode and start displaying additional information in the console log.", ud_get_wp_crm()->domain); ?></label>
      </div>

      <div class="wp_crm_settings_block">
        <input value="" type="hidden" name="wp_crm[configuration][pre_release_updates]" />
        <input id="wp_crm_pre_release_updates" value="true" type="checkbox" <?php checked(!empty($wp_crm['configuration']['pre_release_updates'])?$wp_crm['configuration']['pre_release_updates']:false, 'true'); ?> name="wp_crm[configuration][pre_release_updates]" />
        <label for="wp_crm_pre_release_updates"><?php _e("Enable pre-release updates.", ud_get_wp_crm()->domain); ?></label>
      </div>

      <?php do_action('wp_crm_settings_help_tab'); ?>
    </div>
  </div>

</div>


<br class="cb" />

<p class="wp_crm_save_changes_row">
<input type="submit" value="<?php _e('Save Changes',ud_get_wp_crm()->domain);?>" class="button-primary" name="Submit">
 </p>


</form>
</div>