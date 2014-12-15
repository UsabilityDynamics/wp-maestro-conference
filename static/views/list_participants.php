<?php
/**
 * Settings page tab template
 */
?>
<table style="width:100%;">
	<tr style="font-weight:bold;">
		<td><?php _e('Name:', ud_get_wp_maestro_conference('domain')); ?>:</td>
		<td><?php _e('PIN:', ud_get_wp_maestro_conference('domain')); ?>:</td>
	</tr>
	<?php foreach ($participants as $value): ?>
  	<tr>
  		<td>
			  <?php if ($value['wp_user_id']): ?>
				<a target="_blank" href="<?php get_edit_user_link($value['wp_user_id']) ?>"><?php $value['name'] ?></a>
			  <?php else: ?>
				<?php $value['name']; ?>
			</td>
		  <?php endif; ?>
  		<td><?php $value['PIN'] ?></td>';
  	</tr>
	<?php endforeach; ?>
</table>
