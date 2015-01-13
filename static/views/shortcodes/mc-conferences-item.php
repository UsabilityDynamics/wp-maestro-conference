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
    <p><?php the_excerpt(); ?></p>
</div>
<div class="col-md-4">
  <?php $this->render( "mc-conferences-action", array( 'conference' => $conference, 'user_conference_data' => $user_conference_data ) ); ?>
</div>