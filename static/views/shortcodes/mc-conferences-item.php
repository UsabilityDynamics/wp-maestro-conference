<?php
/**
 * Shortcode: [mc_conferences]
 * Item Template
 */
$conference = ud_get_wp_maestro_conference()->get_conference_data($post->ID, OBJECT);
?>
<li>
	<div class="row">
		<div class="col-md-3"><?php $conference->scheduledStartDate; ?></div>
		<div class="col-md-9">
			<ul>
				<li>
					<label><?php _e('Title:', ud_get_wp_maestro_conference('domain')); ?></label>
					<span><?php the_title(); ?></span>
				</li>
				<li>
					<label><?php _e('Conference:', ud_get_wp_maestro_conference('domain')); ?></label>
					<span><?php the_excerpt(); ?></span>
				</li>
				<li>
					<label><?php _e('Callers:', ud_get_wp_maestro_conference('domain')); ?></label>
					<span><?php $conference->count_participants; ?>/24</span>
				</li>
			</ul>
		<?php 
		  echo do_shortcode( "[wpiw_button amount='25' action='withdraw' label='Pre-Register' "
				  . "send_mail='true' desc='Pre-Registration in Conference' extra='id=12&test=asdf' callback='my_function_to_call'] ");
		?>
		</div>
	</div>
</li>