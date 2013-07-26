<?php
/**
 * Display User Activity Logs
 *
 *
 */
 
 $activity_log = WP_CRM_F::get_detailed_activity_log();

?>

<div class="wrap">
<h2><?php _e('Activity Logs','wp_crm'); ?></h2>

<table class="wp_crm_activity_log widefat">
  <thead>
    <tr>
      <th class="user"><?php _e( 'User', 'wp_crm' ); ?></th>
      <th class="activity"><?php _e( 'Activity', 'wp_crm' ); ?></th>
      <th class="location"><?php _e( 'Location', 'wp_crm' ); ?></th>
      <th class="detail"><?php _e( 'ISP', 'wp_crm' ); ?></th>
      <th class="time"><?php _e( 'Time', 'wp_crm' ); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( (array) $activity_log as $count => $row ) { ?>
      <tr data-object_id="<?php echo $row->object_id; ?>" data-row_id="<?php echo $row->id; ?>">
        <td class="user"><a href="<?php echo $row->edit_url ?>"><?php echo $row->display_name ?></a></td>
        <td class="activity"><?php echo $row->text ?></td>
        <td class="location">
        <?php if( $row->location ) { ?>
        <a href="<?php echo add_query_arg( array( 'q' => $row->location->latitude . ',' . $row->location->longitude, 'z' => 7 ),  'https://maps.google.com/' ); ?>" target="_blank">
          <?php echo implode( ', ', array_filter( array(  $row->location->city, $row->location->region_code, $row->location->country_name ) ) ); ?>
        </a>
        <?php } else { ?>-<?php } ?>        
        </td>
        <td class="detail"><?php echo $row->host_name ?></td>
        <td class="time"><?php echo ( time() - $row->time_stamp < 432000 ) ? $activity_log[ $count ]->time_ago : $row->date; ?></td>
      </tr>
    <?php } ?>
  </tbody>
  </tbody>
  <tfoot>
    <tr>
      <th class="user"><?php _e( 'User', 'wp_crm' ); ?></th>
      <th class="activity"><?php _e( 'Activity', 'wp_crm' ); ?></th>
      <th class="location"><?php _e( 'Location', 'wp_crm' ); ?></th>
      <th class="detail"><?php _e( 'ISP', 'wp_crm' ); ?></th>
      <th class="time"><?php _e( 'Time', 'wp_crm' ); ?></th>
    </tr>  
  </tfoot>
</table>

</div>