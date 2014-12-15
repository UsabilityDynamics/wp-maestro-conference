<?php
/**
 * Shortcode: [mc_conferences]
 * Main Template
 */
?>
<?php if (!empty($posts)) : ?>
  <div class="row mc-conferences-shortcode">
      <div class="col-md-12">
  		<ul class="list">
			  <?php foreach ($posts as $post) : ?>
				<?php $this->render(array('post' => $post), "{$template}-item"); ?>
			  <?php endforeach; ?>
  		</ul>
      </div>
  </div>
<?php endif; ?>
