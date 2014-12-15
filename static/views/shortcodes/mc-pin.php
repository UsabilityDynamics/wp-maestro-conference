<?php
/**
 * Shortcode: [mc_pin]
 *
 *  Available vars:
 *  $user_id - specified User ID
 *  $template - current template's name
 *  $conference_id - specified Conference ID
 */
?>
<?php if (!empty($pin)) : ?>
  <span class="mc-pin"><?php echo $pin; ?></span>
<?php endif; ?>
