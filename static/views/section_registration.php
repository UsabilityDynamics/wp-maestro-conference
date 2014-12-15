<?php
/**
 * Settings page tab template
 */
?>
<style>
    .uisf-field {
	float: left;
	width: 100%;
    }
</style>
<div class="accordion-section-content">
    <div class="inside">
        <div class="uisf-field uisf-checkbox-wrapper"><div class="uisf-label">
                <label for="registration_is_enabled">Registration is enabled</label>
            </div>
            <div class="uisf-input">
                <input type='hidden' name='registration|is_enabled' value='0' />
                <input type="checkbox" value="1" id="registration_is_enabled" name="registration|is_enabled" <?php echo $this->get('registration.is_enabled') == 1 ? "checked" : ""; ?> class="sui-text">
            </div>
        </div>          
        <div class="uisf-field uisf-checkbox-wrapper"><div class="uisf-label">
                <label for="registration_is_enabled_non_register">Registration is disabled for non-registered users</label>
            </div>
            <div class="uisf-input">
                <input type='hidden' name='registration|is_enabled_non_register' value='0' />
                <input type="checkbox" value="1" id="registration_is_enabled_non_register" name="registration|is_enabled_non_register" <?php echo $this->get('registration.is_enabled_non_register') == 1 ? "checked" : ""; ?> class="sui-text">
            </div>
        </div>          
    </div><!-- .inside -->
</div>