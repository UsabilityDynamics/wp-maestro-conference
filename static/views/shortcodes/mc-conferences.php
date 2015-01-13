<?php
/**
 * Shortcode: [mc_conferences]
 * Main Template
 */
?>
<?php if (!$is_ajax) : ?>
  <select name="mc_conferences_filter" 
          id="mc_conferences_filter" 
          data-user_id="<?php echo $user_id; ?>" 
          data-offset="<?php echo $offset; ?>" 
          data-per_page="<?php echo $per_page; ?>">
      <?php if(!empty($select_options)): foreach ($select_options as $select_value=>$select_option):?>
        <option value="<?php echo $select_value; ?>"><?php echo $select_option; ?></option>
      <?php endforeach; endif; ?>
  </select>
<?php endif; ?>
<div class="mc_conferences">
  <?php if ($posts && $posts->have_posts()) :
  while ($posts->have_posts()) : $posts->the_post(); global $post; ?>
    <div class="row mc-conferences-shortcode" style="width:800px;">
      <?php $this->render( "mc-conferences-item", array('post' => $post) ); ?>
    </div>       
  <?php 
  endwhile; 
  else: ?>
    <?php _e('Nothing found', ud_get_wp_maestro_conference('domain')); ?>
  <?php endif; ?>
</div>
