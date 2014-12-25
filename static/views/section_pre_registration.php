<?php
/**
 * Settings page tab template
 */
?>
<div class="accordion-section-content">
    <div class="inside">
        <div class="uisf-field uisf-checkbox-wrapper"><div class="uisf-label">
                <label for="registration_pre_registration_cancel">
                    <?php _e('User can cancel pre-registration in conference', ud_get_wp_maestro_conference('domain')); ?>                    
                </label>
            </div>
            <div class="uisf-input">
                <input type='hidden' name='registration|pre_registration_cancel' value='0' />
                <input type="checkbox" <?php echo $this->get('registration.pre_registration_cancel') == 1 ? "checked" : ""; ?> value="1" id="registration_pre_registration_cancel" name="registration|pre_registration_cancel" class="sui-text">
            </div>
        </div>          
        <div class="uisf-field uisf-text-wrapper">
            <div class="uisf-label">
                <label for="registration_pre_registration_cancel_time">
                    <?php _e('User can not cancel registration less then', ud_get_wp_maestro_conference('domain')); ?>
                </label>
            </div>
            <div class="uisf-input">
                <input type="text" size="30" placeholder="" value="<?php echo $this->get('registration.pre_registration_cancel_time'); ?>" id="registration_pre_registration_cancel_time" name="registration|pre_registration_cancel_time" class="sui-text">
                <p class="description" id="registration_pre_registration_cancel_time_description">
                    <?php _e('hours before starting conference', ud_get_wp_maestro_conference('domain')); ?>
                </p>
            </div>
        </div> 
    </div><!-- .inside -->
</div>