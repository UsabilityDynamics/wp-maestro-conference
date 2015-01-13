<?php
/**
 * Settings page tab template
 */
?>
<table style="width:100%;">
	<tr style="font-weight:bold;">
		<td><?php _e('Name', ud_get_wp_maestro_conference('domain')); ?>:</td>
		<td><?php _e('PIN', ud_get_wp_maestro_conference('domain')); ?>:</td>
		<td><?php _e('Phone', ud_get_wp_maestro_conference('domain')); ?>:</td>
	</tr>
	<?php $i=1; foreach ($participants as $k=>$value): ?>
  	<tr>
  		<td>
        <?php echo $i; $i++; ?>
			  <?php if (!empty($value['wp_user_id']) && !empty($value['name'])): $user_data = get_userdata( $value['wp_user_id']); ?>
				<a target="_blank" href="<?php get_edit_user_link($value['wp_user_id']) ?>"><?php echo $value['name']; ?></a> <?php echo '(' . $user_data->data->user_email . ')'; ?>
			  <?php else: ?>
				<?php echo (!empty($value['name'])) ? $value['name'] : ''; ?>
			</td>
		  <?php endif; ?>
  		<td><?php echo (!empty($value['PIN'])) ? $value['PIN'] : '-'; ?></td>
  		<td><?php echo (!empty($value['callInNumber'])) ? $value['callInNumber'] : '-'; ?></td>
  	</tr>
	<?php endforeach; ?>
</table>
