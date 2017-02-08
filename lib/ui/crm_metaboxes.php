<?php

/**
 * Metaboxes for the main overview page
 *
 * @since 0.01
 *
 */
class toplevel_page_wp_crm_network {

  /**
   * Actions metabox used for primary filtering purposes in network admin.
   *
   * @duplicated from class toplevel_page_wp_crm 
   * @uses CRM_User_List_Table class
   * @since 1.0.2
   *
   */
  static function actions($wp_list_table) {
    ?>
    <div class="misc-pub-section">
      <?php $wp_list_table->search_box('Search', 'wp_crm_text_search', 'Search User'); ?>
      <select id="network-sites" class="combobox" name="wp_crm_search[primary_blog]" data-placeholder="<?php _e("Select Site", ud_get_wp_crm()->domain);?>">
        <option value='0'><?php _e("All", ud_get_wp_crm()->domain);?></option>
      <?php
      $sites = get_sites();
      foreach ($sites as $site) {
        echo "<option value='{$site->blog_id}'>{$site->domain}{$site->path}</option>";
      }
      ?>
      </select>
    </div>

    <div class="major-publishing-actions">

      <div class="publishing-action">
        <?php submit_button(__('Filter Results',ud_get_wp_crm()->domain), 'button', false, false, array('id' => 'search-submit')); ?>
      </div>
      <br class="clear" />
    </div>

    <div class="wp_crm_user_actions">
      <ul class="wp_crm_action_list">
        <li class="button wp_crm_export_to_csv"><?php _e('Export to CSV', ud_get_wp_crm()->domain); ?></li>
      <?php if (WP_CRM_F::get_quantifiable_attributes()) { ?>
        <li class="button wp_crm_visualize_results"><?php _e('Visualize User Data', ud_get_wp_crm()->domain); ?></li>
      <?php } ?>
        <?php do_action('wp_crm_user_actions'); ?>
      </ul>
    </div>

    <div id="performed_actions">
      <h3><?php _e('Perfomed actions', ud_get_wp_crm()->domain); ?></h3>
      <div class="wp_crm_quick_report_wrapper"></div>
    </div>
    <?php
  }

}

/**
 * Metaboxes for the main overview page
 *
 * @since 0.01
 *
 */
class toplevel_page_wp_crm {

  /**
   * Actions metabox used for primary filtering purposes
   *
   *
   * @uses CRM_User_List_Table class
   * @since 0.01
   *
   */
  static function actions($wp_list_table) {
    ?>
    <div class="misc-pub-section">
      <?php $wp_list_table->search_box('Search', 'wp_crm_text_search'); ?>
      <?php $wp_list_table->views(); ?>
    </div>

    <div class="major-publishing-actions">

      <div class="publishing-action">
        <?php submit_button(__('Filter Results',ud_get_wp_crm()->domain), 'button', false, false, array('id' => 'search-submit')); ?>
      </div>
      <br class="clear" />
    </div>

    <div class="wp_crm_user_actions">
      <ul class="wp_crm_action_list">
        <li class="button wp_crm_export_to_csv"><?php _e('Export to CSV', ud_get_wp_crm()->domain); ?></li>
      <?php if (WP_CRM_F::get_quantifiable_attributes()) { ?>
        <li class="button wp_crm_visualize_results"><?php _e('Visualize User Data', ud_get_wp_crm()->domain); ?></li>
      <?php } ?>
        <?php do_action('wp_crm_user_actions'); ?>
      </ul>
    </div>

    <div id="performed_actions">
      <h3><?php _e('Perfomed actions', ud_get_wp_crm()->domain); ?></h3>
      <div class="wp_crm_quick_report_wrapper"></div>
    </div>
    <?php
  }

}

class crm_page_wp_crm_add_new {

  /**
   * Contact history and messages for a user
   *
   *
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */
  static function user_activity_history($object) {

    $user_id = WP_CRM_F::get_first_value( !empty($object['ID'])?$object['ID']:array() );
    $all_messages = array();
    $limited_messages = array();
    $rest_messages = 0;
    $per_page = 10;
    //** If not to check user id it may cause fatal error */
    if ( $user_id ) {
        $all_messages = WP_CRM_F::get_events('import_count=&object_id=' . $user_id);
        $per_page = (get_user_option('crm_page_wp_crm_add_new_per_page')) ? get_user_option('crm_page_wp_crm_add_new_per_page') : 10;
        $params = array(
          'object_id' => $user_id,
          'import_count' => $per_page,
          'hide_empty' => true
        );
        $limited_messages = WP_CRM_F::get_events($params);
        $rest_messages = count($all_messages) - count($limited_messages);
    }
    if (WP_CRM_F::current_user_can_manage_crm()) :
      ?>
      <div class="wp_crm_activity_top">
        <input class="wp_crm_toggle_message_entry" type="button" value="<?php _e('Add Message', ud_get_wp_crm()->domain); ?>" />
        <?php do_action('wp_crm_user_activity_history_top', $object); ?>
        <img class="loading" src="<?php echo ud_get_wp_crm()->path( "static/styles/images/ajax-loader-arrows.gif", 'url' ); ?>" height="16" width="16" style="margin: 0pt auto; display:none" alt="<?php _e("loading",ud_get_wp_crm()->domain);?>"/>
      </div>
      <?php
    endif;
    ?>

    <div class="wp_crm_new_message hidden">
      <textarea id="wp_crm_message_content"></textarea>

      <div class="wp_crm_new_message_options_line">
        <div class="alignleft">
          <div class="wp_crm_show_message_options"><?php _e('Show Options', ud_get_wp_crm()->domain); ?></div>
          <div class="wp_crm_message_options hidden">
            <?php _e('Date:', ud_get_wp_crm()->domain); ?>
            <input class="datepicker" />
          </div>
        </div>
        <div class="alignright">
          <label for="wp_crm_message_type"><?php _e('Message type', ud_get_wp_crm()->domain);?></label>
          <select id="wp_crm_message_type" class='wp_crm_dropdown'>
            <?php foreach ((array)apply_filters('crm_add_message_types',array('general_message'=>array('title'=>'General Message'),'phone_call'=>array('title'=>'Phone Call'),'meeting'=>array('title'=>'Meeting'))) as $type=>$options):?>
            <option value="<?php echo $type;?>" title="Select type of message"><?php echo $options['title'];?></option>
          <?php endforeach; ?>
          </select>
          <input type="button" id="wp_crm_add_message" value="<?php _e('Submit', ud_get_wp_crm()->domain); ?>"/>
        </div>
      </div>
    </div>

    <table id="wp_crm_user_activity_stream" cellpadding="0" cellspacing="0">
      <thead></thead>
      <tbody>
        <?php
        if (!empty($user_id) && is_numeric($user_id)) {
          $stream = json_decode(WP_CRM_F::get_user_activity_stream("user_id={$user_id}", $limited_messages));
          echo $stream->tbody;
        }
        ?>
      </tbody>
    </table>

    <div class="wp_crm_stream_status wp_crm_load_more_stream" limited_messages="<?php echo count($limited_messages); ?>" all_messages="<?php echo count($all_messages); ?>"  per_page="<?php echo (((!empty($stream->per_page)) ? $stream->per_page : $per_page)); ?>" <?php if (empty($rest_messages)) { ?>style="display:none;" <?php } ?>>
        <span class="wp_crm_counts"><?php printf(__('Showing <span class="current_count">%1s</span> messages of <span class="total_count">%2s</span>. Load <span class="more_count">%3s</span> more.', ud_get_wp_crm()->domain), count($limited_messages), count($all_messages), (($rest_messages>=$per_page)) ? $per_page : $rest_messages); ?>&nbsp;<img class="loading" src="<?php echo ud_get_wp_crm()->path( "static/styles/images/ajax-loader-arrows.gif", 'url' ); ?>" height="16" width="16" style="margin: 0pt auto; display:none" alt="<?php _e("loading", ud_get_wp_crm()->domain);?>"/></span>
    </div>
    <?php
  }

  /**
   *
   * @global type $wp_crm
   * @param type $user_object
   */
  static function side_send_invoce($user_object) {
		global  $wpdb,$wpi_settings ;
		if ( WP_CRM_F::current_user_can_manage_crm() ) { ?>
				<input type="button" data-gotourl="<?php echo $wpi_settings['links']['manage_invoice'];?>" class="button" value="<?php echo  __('Send New Invoice', ud_get_wp_crm()->domain) ?>" id="crm_new_invioce"/>
		<?php } else { ?>
				<input type="button" data-gotourl="#" class="button" value="<?php echo  __('Send New Invoice', ud_get_wp_crm()->domain) ?>" disabled="disabled" />
		<?php }
		}
  /**
   * 
   * @global type $wp_crm
   * @param type $user_object
   */
  static function primary_information($user_object) {
    global $wp_crm;
    $user_role = WP_CRM_F::get_first_value( !empty($user_object['role'])?$user_object['role']:array() );
    ?>
    <table class="form-table">
      <?php if (!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) : ?>
        <?php foreach (apply_filters('wp_crm_primary_information_attributes', $wp_crm['data_structure']['attributes']) as $slug => $attribute):

                /* we already have an Actions box to change user pass, so we can just skip it here */
                if ($slug=='user_pass') continue;

                $row_classes = array();
                $row_classes[] = (@$attribute['has_options'] ? 'wp_crm_has_options' : 'wp_crm_no_options');
                $row_classes[] = (@$attribute['required'] == 'true' ? 'wp_crm_required_field' : '');
                $row_classes[] = (@$attribute['primary'] == 'true' ? 'primary' : 'not_primary');
                $row_classes[] = (( !empty($wp_crm['hidden_attributes'][$user_role]) && is_array($wp_crm['hidden_attributes'][$user_role]) && in_array($slug, $wp_crm['hidden_attributes'][$user_role])) ? 'hidden' : '');
                $row_classes[] = 'wp_crm_user_entry_row';
                $row_classes[] = "wp_crm_{$slug}_row";
                ?>
                <tr meta_key="<?php echo esc_attr($slug); ?>" wp_crm_input_type="<?php echo esc_attr($attribute['input_type']); ?>" class="<?php echo implode(' ', $row_classes); ?>">
                  <th>
                    <?php ob_start(); ?>
                    <label for="wp_crm_<?php echo $slug; ?>_field">
                      <?php echo $attribute['title']; ?>
                    </label>
                    <div class="wp_crm_description"><?php echo !empty($attribute['description'])?$attribute['description']:''; ?></div>
                    <?php $label = ob_get_contents();
                    ob_end_clean(); ?>
                    <?php echo apply_filters('wp_crm_user_input_label', $label, $slug, $attribute, $user_object); ?>
                  </th>
                  <td class="wp_crm_user_data_row"  wp_crm_attribute="<?php echo $slug; ?>">
                    <div class="blank_slate hidden" show_attribute="<?php echo $slug; ?>"><?php echo (!empty($attribute['blank_message']) ? $attribute['blank_message'] : "Add {$attribute['title']}"); ?></div>
                    <?php echo WP_CRM_F::user_input_field($slug, !empty($user_object[$slug])?$user_object[$slug]:'', $attribute, $user_object); ?>
                    <?php if (isset($attribute['allow_multiple']) && $attribute['allow_multiple'] == 'true'): ?>
                      <div class="add_another"><?php _('Add Another'); ?></div>
                    <?php endif; ?>
                  </td>
                </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </table>
      <?php
  }

  /**
   * Sidebar metabox for administrative user actions
   *
   *
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */
  static function special_actions($object) {
    global $current_user, $wp_filter, $user_id, $_wp_admin_css_colors;

    $current_user_id = $current_user->ID;
    $user_id = !empty( $object['ID']['default'][0] ) ? $object['ID']['default'][0] : '';
    $profileuser = get_user_to_edit($user_id);

    if ($user_id == $current_user_id) {
      $own_profile = true;
    }
    ?>
    <div id="minor-publishing">
      <ul class="wp_crm_advanced_user_actions_wrapper">
        <li class="wp_crm_advanced_user_actions">
          <div class="wp_crm_toggle_advanced_user_actions wp_crm_link"><?php _e('Toggle Settings',ud_get_wp_crm()->domain); ?></div>
          <div class="wp_crm_advanced_user_actions wp-tab-panel">
            <?php if (WP_CRM_F::current_user_can_manage_crm()) { ?>
            <?php if (current_user_can('WP-CRM: Change Passwords')) { ?>
              <?php _e('Set Password:', ud_get_wp_crm()->domain); ?>
              <ul class="wp_crm_edit_password">
                <li>
                  <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_1" name="wp_crm[user_data][user_pass][<?php echo rand(1000, 9999); ?>][value]" />
                  <span class="description"><?php _e('Type in new password twice to change.',ud_get_wp_crm()->domain); ?></span>
                </li>
                <li>
                  <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_2" />
                  <span class="description"><?php _e('Type your new password again.',ud_get_wp_crm()->domain); ?></span>
                </li>
              </ul>
              <?php } ?>
              <?php _e('Admin Options:', ud_get_wp_crm()->domain); ?>
              <ul>
                <?php if (WP_CRM_F::current_user_can_manage_crm()) { ?>
                  <li class="wp_crm_edit_roles">
                    <label for="wp_crm_role"><?php _e('Capability Role:', ud_get_wp_crm()->domain); ?></label>
                    <select id="wp_crm_role" <?php echo (!empty($own_profile) ? ' disabled="true" ' : ''); ?> name="wp_crm[user_data][role][<?php echo rand(1000, 9999); ?>][value]">
                      <option value=""></option>
                  <?php wp_dropdown_roles(!empty($object['role']['default'][0])?$object['role']['default'][0]:''); ?>
                    </select>
                  </li>
                <?php } ?>
                <li class="wp_crm_capability_bar">
                  <input name="show_admin_bar_front" type="hidden" value="false"  />
                  <input name="show_admin_bar_front" type="checkbox" id="show_admin_bar_front" value="true" <?php checked(_get_admin_bar_pref('front', !empty($profileuser->ID)?$profileuser->ID:'' )); ?> />
                  <label for="show_admin_bar_front"><?php _e('Show Admin Bar when viewing site.',ud_get_wp_crm()->domain); ?> </label>
                </li>
              </ul>
            <?php } ?>
            <?php if ( count($_wp_admin_css_colors) > 1 && has_action('admin_color_scheme_picker') && WP_CRM_F::current_user_can_manage_crm() ) { ?>
            <?php _e('Color Scheme:', ud_get_wp_crm()->domain); do_action('admin_color_scheme_picker'); ?>
            <?php } ?>
            </div>
          </li>
        </ul>
        <?php if ( ( !empty($wp_filter['show_user_profile']) && count( $wp_filter['show_user_profile'] ) ) || ( !empty($wp_filter['profile_personal_options']) && count( $wp_filter['profile_personal_options'] ) ) ) { ?>
          <div class="wp_crm_user_api_actions">
        <?php
            add_filter('wpi_user_information', array('WP_CRM_F', 'wpi_user_information'));
            if ( !empty( $own_profile ) ) {
              do_action('show_user_profile', $profileuser?$profileuser:new WP_User());
            } else {
              do_action('edit_user_profile', $profileuser?$profileuser:new WP_User());
            }
        ?>
          </div>
        <?php } ?>
        <?php if (WP_CRM_F::current_user_can_manage_crm()) {
          do_action('wp_crm_metabox_special_actions');
        } ?>
        </div>

        <div class="major-publishing-actions">
          <div id="publishing-action">
            <input type="hidden" value="Publish" id="original_publish" name="original_publish" />
            <?php if (WP_CRM_F::current_user_can_manage_crm() || (current_user_can('add_users') && $object['new'])) { ?>
            <input type="submit" accesskey="p" tabindex="5" value="<?php echo (!empty($object['new']) ? __('Save', ud_get_wp_crm()->domain) : __('Update', ud_get_wp_crm()->domain)); ?>" class="button-primary" id="publish" name="publish" />
              <?php } else { ?>
                <input type="submit" accesskey="p" tabindex="5" value="<?php echo (!empty($object['new']) ? __('Save', ud_get_wp_crm()->domain) : __('Update', ud_get_wp_crm()->domain)); ?>" class="button-primary" id="publish" name="publish" disabled="true">
              <?php } ?>
          </div>
          <div class="clear"></div>
        </div>

        <div class="wp_crm_user_actions">
          <ul class="wp_crm_action_list">
            <?php do_action('wp_crm_single_user_actions', $object); ?>
            <?php if ((WP_CRM_F::current_user_can_manage_crm() || current_user_can('delete_users')) && (empty($object['new']) && $user_id != $current_user->ID)) { ?>
              <li><a href="<?php echo wp_nonce_url("admin.php?wp_crm_action=delete_user&page=wp_crm&user_id={$user_id}", 'wp-crm-delete-user-' . $user_id); ?>" class="submitdelete deletion button"><?php _e('Delete',ud_get_wp_crm()->domain); ?></a></li>
            <?php } ?>
          </ul>
        </div>
    <?php
  }

}

