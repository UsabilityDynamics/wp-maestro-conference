<?php
/**
 * Shortcode: [mc_conferences]
 * Item Template
 */
$conference = ud_get_wp_maestro_conference()->get_conference_data($post->ID, OBJECT);
$user_conference_data = ud_get_wp_maestro_conference()->get_user_conference_data($post->ID);
?>
<link href="http://financialtherapynetwork.loc/wp-content/themes/twentyfourteen/css/bootstrap/bootstrap.min.css" rel="stylesheet">
<script src="http://financialtherapynetwork.loc/wp-content/themes/twentyfourteen/js/bootstrap.min.js"></script>
<div class="col-md-8">
    <h2><?php the_title(); ?></h2>
    <p>
        <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
        <?php echo date('F d, Y', strtotime($conference->scheduledStartDate)); ?>
        <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
        <?php echo date('h:i A', strtotime($conference->scheduledStartDate)); ?>
    </p>
    <p>
        <?php echo (!empty($user_conference_data->PIN)) ? 'PIN: ' . $user_conference_data->PIN : ''; ?>
        <?php echo (!empty($user_conference_data->phone)) ? 'Phone: ' . $user_conference_data->phone : ''; ?>
    </p>
    <p><?php the_content(); ?></p>
</div>
<div class="col-md-4">
    <?php
      if (!$conference->is_active && $conference->status == 'active') {
        do_shortcode("[mc_button conference_id='" . $post->ID . "' action='" . ((!$user_conference_data->is_registered) ? 'add' : 'remove') . "' label='" . ((!$user_conference_data->is_registered) ? 'Pre-Register' : 'Cancel') . "' send_mail='true' desc='Pre-Registration in Conference' extra='id=12&test=asdf' callback='my_function_to_call']");
      }
    ?>
</div>