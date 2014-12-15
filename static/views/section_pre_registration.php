<?php
/**
 * Settings page tab template
 */
?>
<div class="accordion-section-content">
    <div class="inside">
        <div class="uisf-field uisf-checkbox-wrapper"><div class="uisf-label">
                <label for="registration_pre_registration_cancel">User can cancel pre-registration in conference</label>
            </div>
            <div class="uisf-input">
                <input type='hidden' name='registration|pre_registration_cancel' value='0' />
                <input type="checkbox" <?php echo $this->get('registration.pre_registration_cancel') == 1 ? "checked" : ""; ?> value="1" id="registration_pre_registration_cancel" name="registration|pre_registration_cancel" class="sui-text">
            </div>
        </div>          
        <div class="uisf-field uisf-text-wrapper">
            <div class="uisf-label">
                <label for="registration_pre_registration_cancel_time">User can not cancel registration less then</label>
            </div>
            <div class="uisf-input">
                <input type="text" size="30" placeholder="" value="<?php echo $this->get('registration.pre_registration_cancel_time'); ?>" id="registration_pre_registration_cancel_time" name="registration|pre_registration_cancel_time" class="sui-text">
                <p class="description" id="registration_pre_registration_cancel_time_description">hours before starting conference</p>
            </div>
        </div> 
    </div><!-- .inside -->
</div>