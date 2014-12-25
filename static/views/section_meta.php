<?php
/**
 * Settings page tab template
 */
?>
<div class="inside">
    <div class="uisf-field uisf-checkbox-wrapper"><div class="uisf-label">
            <label for="meta_fields_show_non_authorized">
                <?php _e('show/hide for non-authorized users', ud_get_wp_maestro_conference('domain')); ?>                
            </label>
        </div>
        <div class="uisf-input">
            <input type='hidden' name='meta_fields|show_non_authorized' value='0' />
            <input type="checkbox" value="1" id="meta_fields_show_non_authorized" name="meta_fields|show_non_authorized" <?php echo $this->get('meta_fields.show_non_authorized') == 1 ? "checked" : ""; ?> class="sui-text">
        </div>
    </div>          
    <div class="uisf-field uisf-checkbox-wrapper">
        <div class="uisf-label">
            <label for="meta_fields_show_non_participant">
                <?php _e('show/hide for non-participant', ud_get_wp_maestro_conference('domain')); ?>
            </label>
        </div>
        <div class="uisf-input">
            <input type='hidden' name='meta_fields|show_non_participant' value='0' />
            <input type="checkbox" value="1" id="meta_fields_show_non_participant" name="meta_fields|show_non_participant" <?php echo $this->get('meta_fields.show_non_participant') == 1 ? "checked" : ""; ?> class="sui-text">
        </div>
    </div>          
</div>