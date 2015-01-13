<?php
/**
 * Synchronization button template
 */
?>
<a class="button" href="<?php echo add_query_arg( array('debug' => 'true', 'synchronize' => 'true') ); ?>">
    <?php _e('Synchronization', ud_get_wp_maestro_conference('domain')); ?>
</a>
