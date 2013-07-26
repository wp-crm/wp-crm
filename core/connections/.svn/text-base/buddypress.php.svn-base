<?php

/**
  * Name: BuddyPress plugin connector
  * Description: Adds Extra functionality to WP-CRM when the BuddyPress plugin is active.
  * Author: Usability Dynamics, Inc.
  * Version: 1.0
  *
  */

add_action( 'init', array('WPC_BuddyPress', 'init' ), 5);

class WPC_BuddyPress {


  /**
   * Loader.
   *
   * @action init (5)
   * @author potanin@UD
   */
  function init() {


    if( class_exists( 'BP_XProfile_Group' ) ) {

      //** All BuddyPress actions are initialized after bb_include action is ran. */
      add_action( 'bp_include', array('WPC_BuddyPress', 'bp_init' ));

      //** Add BP fields to CRM edit user page */
      add_action( 'wp_crm_metaboxes', array('WPC_BuddyPress', 'bp_profile_fields' ));

      //** Add CRM tab to BP profile frontend */
      add_action( 'bp_before_profile_field_content', array('WPC_BuddyPress', 'bp_profile_add_fields' ));

      //** Add BP Groups dropdown to CRM field advanced options */
      add_action( 'wp_crm_attributes_after_advanced_list', array('WPC_BuddyPress','bp_wp_crm_attribute_advanced' ));

      //** Add assigned fields to BP edit page */
      add_action( 'bp_after_profile_field_content', array('WPC_BuddyPress','bp_after_profile_field_content' ));

      //** Hook data saving */
      add_action( 'xprofile_updated_profile', array('WPC_BuddyPress','bp_profile_updated'), 100, 3);

      //* Adds 'View CRM Profile' link to frontend profile */
      add_action('bp_member_header_actions', array('WPC_BuddyPress','add_crm_button' ));

      add_action('wp_crm_before_save_user_data', array('WPC_BuddyPress','bp_save_profile_data' ));
    }


  }


  /**
   * Only load code that needs BuddyPress to run once BP is loaded and initialized.
   *
   * @author potanin@UD
   */
  function bp_init() {

    WP_CRM_F::console_log(sprintf(__('Executing: %1s.', 'wp_crm'), 'WPC_BuddyPress::bb_init()'));
    add_filter('wp_crm_settings_lower', array('WPC_BuddyPress','wp_crm_settings_lower'));
    add_filter('wp_crm_user_action', array('WPC_BuddyPress','wp_crm_user_action'));

  }


  /**
   * Declare new buddypress_profile action.
   *
   * @author potanin@UD
   */
  function wp_crm_settings_lower($wp_crm) {
    $wp_crm['overview_user_actions']['buddypress_profile']['label'] = __('BuddyPress Profile Link','wp_crm');
    return $wp_crm;
  }


  /**
   * Modify the default buddypress_profile action's HTML to include a link to the profile.
   *
   * @author potanin@UD
   */
  function wp_crm_user_action($action) {
    if($action['action'] != 'buddypress_profile') {
      return $action;
    }

    $action['html'] = sprintf(__('<a href="%1s">BuddyPress Profile</a>', 'wp_crm'), bp_core_get_userlink( $action['user_id'], false, true));

    return $action;
  }

  /**
   * Add BP metabox to edit user page
   *
   * @global array $wp_crm
   * @author korotkov@UD
   */
  function bp_profile_fields() {
    global $wp_crm;
    add_meta_box( 'bp_profile_fields', apply_filters('wp_crm_add_new_bp_metabox_title', __('BuddyPress Profile Fields', 'wp_crm')), array(__CLASS__,'bp_profile_fields_metabox'), $wp_crm['system']['pages']['add_new'], 'normal', 'default');
  }

  /**
   * Add WPC fields to BP fields
   *
   * @global array $wp_crm
   * @global object $profile_template
   * @author korotkov@UD
   */
  function bp_profile_add_fields() {
    global $wp_crm, $profile_template;

    //** Loop through CRM attrs */
    foreach ($wp_crm['data_structure']['attributes'] as $slug => $attribute) {
      //** Continue cycle if BP group has no any WPC fields */
      if ( empty( $attribute['bp_group'] ) ||
           $attribute['bp_group'] == '0' ||
           $attribute['bp_group'] != $profile_template->groups[$profile_template->current_group]->id) continue;

      //** Create new field custom object */
      $new_field_object = new stdClass();
      $new_field_object->source          = 'wp_crm';
      $new_field_object->slug            = $slug;
      $new_field_object->attribute       = $attribute;
      $profile_template->group->fields[] = $new_field_object;
    }
  }

  /**
   * Render CRM fields on BP edit profile page
   *
   * @global object $profile_template
   * @global object $current_user
   * @author korotkov@UD
   */
  function bp_after_profile_field_content() {
    //** If we are going to edit profile */
    if ( bp_is_current_action( 'edit' ) ) {
      global $profile_template, $current_user;

      //** Loop through BP fields in current group */
      foreach ($profile_template->group->fields as $field_key => $field_value) {

        //** If field is not CRM field - skip */
        if ( empty($field_value->source) || $field_value->source != 'wp_crm' ) continue;

        //** Get current data from CRM */
        $user_data = wp_crm_get_user($current_user->ID);

        //** Attribute title */
        ob_start();
        ?>
        <label class="control-label"><?php echo $field_value->attribute['title']; ?></label>
        <?php
        $label = ob_get_contents(); ob_clean();

        //** Attribute description */
        ob_start();
        ?>
        <p class="help-block"><?php echo $field_value->attribute['description']; ?></p>
        <?php
        $description = ob_get_contents(); ob_clean();

        //** Draw title, input and description */
        ?>
        <div class="control-group">
          <?php
          echo apply_filters('bp_wp_crm_profile_field_label', $label);
          ?>
          <div class="controls">
          <?php
          echo WP_CRM_F::user_input_field($field_value->slug, $user_data[$field_value->slug], $field_value->attribute, $user_data);
          echo apply_filters('bp_wp_crm_profile_field_description', $description);
          ?>
          </div>
        </div>
        <?php
        //** Remove current custom field from BP fields list so they won't be processed by BP */
        unset($profile_template->group->fields[$field_key]);
      }
    }
  }

  /**
   * Handles BP profile saving action
   *
   * @global object $current_user
   * @param int $user_id
   * @param array $posted_field_ids
   * @param array $errors
   * @author korotkov@UD
   */
  function bp_profile_updated( $user_id, $posted_field_ids, $errors ) {
    global $current_user, $bp;

    if(is_admin()) return;

    //** Get new user_data from POST */
    $user_data = $_REQUEST['wp_crm']['user_data'];

    //** user_id is required */
    $user_data['user_id'][0]['value'] = $user_id;

    //** user_email is required */
    if ( !array_key_exists('user_email', $user_data) ) {
      $user_data['user_email'][0]['value'] = $current_user->user_email;
    }

    //** Change display name if xprofile full name exists */
    $fullname_field_name = bp_xprofile_fullname_field_name();
    $fullname_field_id = xprofile_get_field_id_from_name($fullname_field_name);
    if(in_array($fullname_field_id, $posted_field_ids)) {
      $display_name = xprofile_get_field_data( $fullname_field_name, $user_id );
      $user_data['display_name'] = $display_name;
    }

    //** Save user data */
    $user_id = wp_crm_save_user_data($user_data);

    //** Determine if user changed nicename (display name in URL). peshkov@UD */
    if($user_id && isset($user_data['user_nicename'])) {
      $user_domain = bp_displayed_user_domain();
      $userdata = get_userdata($user_id);
      $user_nicename = $userdata->data->user_nicename;
      $needle = bp_get_members_root_slug().'/'.$user_nicename.'/';
      $pos = strpos($user_domain, $needle);
      if($pos === false) {
        //** Looks like user_nicename was changed so redirect to the new profile's URL. */
        $user_domain = str_replace($bp->displayed_user->userdata->user_nicename, $user_nicename, $user_domain);
        if ( $errors ) {
          bp_core_add_message( __( 'There was a problem updating some of your profile information, please try again.', 'wp_crm' ), 'error' );
        } else {
          bp_core_add_message( __( 'Changes saved.', 'wp_crm' ) );
        }
        //* Redirect back to the edit screen to display the updates and message */
        bp_core_redirect( trailingslashit( $user_domain . $bp->profile->slug . '/edit/group/' . bp_action_variable( 1 ) ) );
      }
    }
  }

  /**
   * Advanced option - BP Group dropdown
   *
   * @global array $wp_crm
   * @param array $args
   * @author korotkov@UD
   */
  function bp_wp_crm_attribute_advanced( $args ) {
    global $wp_crm;

    $defaults = array();
    extract(wp_parse_args($args, $defaults));

    $bp_groups = BP_XProfile_Group::get();
    if ( !empty( $bp_groups ) && is_array($bp_groups) ):
    ?>
    <?php ob_start(); ?>
      <li class="wp_crm_advanced_configuration">
        <label title="<?php _e('BuddyPress Profile Group', 'wp_crm'); ?>"><?php _e('BP Group:', 'wp_crm'); ?></label>
        <select class="wp_crm_bp_group" name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][bp_group]">
          <option value="0"><?php _e(' - ', 'wp_crm'); ?></option>
          <?php foreach ($bp_groups as $key => $group_object): ?>
          <option value="<?php echo $group_object->id; ?>"<?php echo $wp_crm['data_structure']['attributes'][$slug]['bp_group'] == $group_object->id ? ' selected="selected"' : ''; ?>><?php echo $group_object->name; ?></option>
          <?php endforeach; ?>
        </select>
      </li>
    <?php $html = ob_get_contents(); ob_clean();
    echo apply_filters('bp_wp_crm_attribute_advanced', $html);
    endif;
  }

  /**
   * Render metabox content
   *
   * @param array $user_object
   * @author korotkov@UD
   */
  function bp_profile_fields_metabox($user_object) {
    global $bp;
    /** Set user id */
    $user_id = WP_CRM_F::get_first_value($user_object['ID']);
    $bp->displayed_user->id = $user_id;

    /** Get BP Profile groups */
    $bp_user_profile_groups = BP_XProfile_Group::get(
      array(
        'user_id'           => $user_id,
        'fetch_fields'      => 1,
        'fetch_field_data'  => 1,
        'hide_empty_groups' => 1
      )
    );

    $bp_user_profile_groups = apply_filters('wp_crm_bp_groups_before_render', $bp_user_profile_groups);
    if ( !empty( $bp_user_profile_groups ) && is_array( $bp_user_profile_groups ) ) :
    ?>
    <script type="text/javascript">
      if( typeof bp_clear_profile_field == 'undefined' ) {
        function bp_clear_profile_field(container) {
          if( !document.getElementById(container) ) return;
          var container = document.getElementById(container);
          if ( radioButtons = container.getElementsByTagName('INPUT') ) {
            for(var i=0; i<radioButtons.length; i++) {
              radioButtons[i].checked = '';
            }
          }
          if ( options = container.getElementsByTagName('OPTION') ) {
            for(var i=0; i<options.length; i++) {
              options[i].selected = false;
            }
          }
          return;
        }
      }
    </script>
    <table class="form-table bp-profile">
      <?php foreach($bp_user_profile_groups as $group_key => $group_object): ?>
        <tr class="wp_crm_bp_group_name">
          <th colspan="2">
            <?php ob_start();?>
            <label>
              <?php echo $group_object->name; ?>
            </label>
            <div class="wp_crm_description"><?php echo $group_object->description; ?></div>
            <?php $label = ob_get_contents(); ob_end_clean(); ?>
            <?php echo apply_filters('wp_crm_bp_profile_group_name', $label, $group_object, $user_object); ?>
          </th>
        </tr>
        <?php if ( !empty($group_object->fields) && is_array($group_object->fields) ): ?>
          <?php foreach($group_object->fields as $field_key => $field_object): ?>
            <tr class="wp_crm_user_entry_row">
              <th>
                <?php ob_start();?>
                <label><?php echo $field_object->name; ?></label>
                <div class="wp_crm_description"><?php echo $field_object->description; ?></div>
                <?php $label = ob_get_contents(); ob_end_clean(); ?>
                <?php echo apply_filters('wp_crm_bp_profile_field_name', $label, $field_object, $user_object); ?>
              </th>
              <td class="wp_crm_user_data_row">
                <div class="input_div">
                  <?php if ( $field_object->name == bp_xprofile_fullname_field_name() ) : ?>
                  <div class="bp readonly">
                    <input readonly="readonly" type="text" value="<?php echo $field_object->data->value; ?>" />
                  </div>
                  <?php else : ?>
                  <?php echo self::render_bp_field($field_object); ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </table>
    <?php
    endif;
  }

  /**
   * Renders Buddypress profile field
   *
   * @param object $bp_field.
   * @param boolean $returm.
   * @author peshkov@UD
   */
  protected function render_bp_field($bp_field, $return = false) {
    global $field;
    $field = $bp_field;
    $type = bp_get_the_profile_field_type();

    ob_start();
    switch ($type) {
      case 'textbox':
        ?>
        <div class="bp textbox">
        <input type="text" name="bp[<?php bp_the_profile_field_input_name(); ?>]" id="<?php bp_the_profile_field_input_name(); ?>" value="<?php bp_the_profile_field_edit_value(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>/>
        </div>
        <?php
        break;
      case 'textarea':
        ?>
        <div class="bp textarea">
        <textarea rows="5" cols="40" name="bp[<?php bp_the_profile_field_input_name(); ?>]" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>><?php bp_the_profile_field_edit_value(); ?></textarea>
        </div>
        <?php
        break;
      case 'selectbox':
        ?>
        <div class="bp selectbox">
        <select name="bp[<?php bp_the_profile_field_input_name(); ?>]" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
          <?php bp_the_profile_field_options() ?>
        </select>
        </div>
        <?php
        break;
      case 'multiselectbox':
        $input_type = bp_get_the_profile_field_input_name();
        preg_match('/field_[0-9]+/', $input_type, $matches);
        if($matches) $input_type = $matches[0];
        ?>
        <div class="bp multiselectbox">
        <select name="bp[<?php echo $input_type; ?>][]" id="<?php echo $input_type ?>" multiple="multiple" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
          <?php bp_the_profile_field_options(); ?>
        </select>
        <?php if ( !bp_get_the_profile_field_is_required() ) : ?>
          <a class="bp-clear-value" href="javascript:bp_clear_profile_field( '<?php echo $input_type; ?>' );"><?php _e( 'Clear', 'wp_crm' ); ?></a>
        <?php endif; ?>
        </div>
        <?php
        break;
      case 'radio':
        ?>
        <div class="bp radio">
        <?php
        $options = bp_get_the_profile_field_options();
        $options = preg_replace('/(name=[\"\'])(field\_[0-9]+)/', '$1bp[$2]', $options);
        echo $options;
        ?>
        <?php if ( !bp_get_the_profile_field_is_required() ) : ?>
          <a class="bp-clear-value" href="javascript:bp_clear_profile_field( '<?php bp_the_profile_field_input_name(); ?>' );"><?php _e( 'Clear', 'wp_crm' ); ?></a>
        <?php endif; ?>
        </div>
        <?php
        break;
      case 'checkbox':
        ?>
        <div class="bp checkbox">
        <?php
        $options = bp_get_the_profile_field_options();
        $options = preg_replace('/(name=[\"\'])(field\_[0-9]+)/', '$1bp[$2]', $options);
        echo $options;
        ?>
        </div>
        <?php
        break;
      case 'datebox':
        ?>
        <div class="bp datebox">
          <select name="bp[<?php bp_the_profile_field_input_name(); ?>_day]" id="<?php bp_the_profile_field_input_name(); ?>_day" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
            <?php bp_the_profile_field_options( 'type=day' ); ?>
          </select>
          <select name="bp[<?php bp_the_profile_field_input_name() ?>_month]" id="<?php bp_the_profile_field_input_name(); ?>_month" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
            <?php bp_the_profile_field_options( 'type=month' ); ?>
          </select>
          <select name="bp[<?php bp_the_profile_field_input_name() ?>_year]" id="<?php bp_the_profile_field_input_name(); ?>_year" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
            <?php bp_the_profile_field_options( 'type=year' ); ?>
          </select>
        </div>
        <?php
        break;
    }
    $return = ob_get_contents();
    ob_end_clean();

    if($return) return $return;
    else echo $return;
  }

  /**
   * Adds 'View CRM Profile' link to frontend profile
   * if the current user has permissions
   *
   * @author peshkov@UD
   */
  function add_crm_button() {
    global $bp;

    if(current_user_can('manage_options')) {
    ?>
    <div id="view_crm_profile" class="btn">
      <a target="_blank" href="<?php echo admin_url("admin.php?page=wp_crm_add_new&user_id={$bp->displayed_user->id}"); ?>"><?php _e('View CRM Profile', 'wp_crm'); ?></a>
    </div>
    <?php
    }
  }

  /**
   * Saves Buddypress profile data.
   *
   * @uses WP_CRM_Core::wp_crm_save_user_data()
   * @param array $data. Request (POST,GET)
   * @author peshkov@UD
   */
  function bp_save_profile_data($data) {
    global $bp;

    if(empty($data['bp']) || empty($data['user_id'])) return;

    //* Set necessary variables */
    $user_id = $data['user_id'];
    $user_data = $data['wp_crm']['user_data'];
    $data = $data['bp'];
    $errors = false;
    $posted_field_ids = array();
    $is_required = array();

    //* Set xprofile full name from display_name */
    $display_name = WP_CRM_F::get_first_value($user_data['display_name']);
    if(!empty($display_name)) {
      $fullname_field_name = bp_xprofile_fullname_field_name();
      $fullname_field_id = xprofile_get_field_id_from_name($fullname_field_name);
      $data["field_{$fullname_field_id}"] = $display_name;
    }

    //* Get all posted field ids */
    foreach($data as $name => $value) {
      $field_id = str_replace(array('field_','_day','_month','_year'), '', $name);
      array_push($posted_field_ids, $field_id);
    }
    $posted_field_ids = array_unique($posted_field_ids);

    //* Validate the field */
    foreach ( $posted_field_ids as $field_id ) {
      if ( !isset( $data['field_' . $field_id] ) ) {
        if ( !empty( $data['field_' . $field_id . '_day'] ) && !empty( $data['field_' . $field_id . '_month'] ) && !empty( $data['field_' . $field_id . '_year'] ) ) {
          /* Concatenate the values */
          $date_value = $data['field_' . $field_id . '_day'] . ' ' . $data['field_' . $field_id . '_month'] . ' ' . $data['field_' . $field_id . '_year'];
          /* Turn the concatenated value into a timestamp */
          $data['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
        }
      }

      $is_required[$field_id] = xprofile_check_is_required_field( $field_id );
      if ( $is_required[$field_id] && empty( $data['field_' . $field_id] ) )
      $errors = true;
    }

    //** There are errors */
    if ( $errors ) {
      WP_CRM_F::add_message(__('Please make sure you fill in all required Buddypress fields in this profile field group before saving.', 'wp_crm'), 'bad');
    //** No errors */
    } else {
      //** Now we've checked for required fields, lets save the values. */
      foreach ( $posted_field_ids as $field_id ) {
        //** Certain types of fields (checkboxes, multiselects) may come through empty. */
        //** Save them as an empty array so that they don't get overwritten by the default on the next edit. */
        if ( empty( $data['field_' . $field_id] ) ) $value = array();
        else $value = $data['field_' . $field_id];

        if ( !xprofile_set_field_data( $field_id, $user_id, $value, $is_required[$field_id] ) ) $errors = true;
        else do_action( 'xprofile_profile_field_data_updated', $field_id, $value );
      }

      //** Set the feedback message if we have error */
      if ( $errors ) {
        WP_CRM_F::add_message(__( 'There was a problem updating some of Buddypress profile information, please try again.', 'wp_crm' ), 'bad');
      }
    }
  }

}