<?php
// Pieces carousel block

// Create id attribute allowing for custom "anchor" value.
$id = 'pieces-carousel-' . $block['id'];
if( !empty($block['anchor']) ) {
  $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'pieces-carousel-block';
if( !empty($block['className']) ) {
  $className .= ' ' . $block['className'];
}

// Check if user is logged in
$is_user_logged = is_user_logged_in();

// WP_Query arguments
if( $is_user_logged ) {
  // Logged in users can see all pieces
  $args = array(
    'post_type'              => array( 'content' ),
    'post_status'            => array( 'publish' ),
    'posts_per_page'         => 5,
  );
} else {
  // Not logged in users can see only pieces in not-private galleries
  // So, first, create an array of id of private galleries, then exclude those terms in the query
  $private_terms = get_terms( array(
    'taxonomy'      => 'gallery',
    'hide_empty'    => false,
    'meta_key'      => 'wpcf-private',
    'meta_value'    => '1',
    'fields'        => 'ids'
  ) );
  $args = array(
    'post_type'              => array( 'content' ),
    'post_status'            => array( 'publish' ),
    'posts_per_page'         => 5,
    'tax_query'              => array(
      array(
        'taxonomy'    => 'gallery',
        'field'       => 'term_id',
        'terms'       => $private_terms,
        'operator'    => 'NOT IN',
      ),
    ),
  );
}

// The Query
$query = new WP_Query( $args );

// The Loop
if ( $query->have_posts() ) {
  ?>
  <div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <div class="swiper-container">
      <h2 class="has-text-align-center">Latest pieces</h2>
      <div class="swiper-wrapper">
        <?php
      	  while ( $query->have_posts() ) {
    		    $query->the_post(); ?>
            <?php
            $terms = get_the_terms( get_the_id(), 'gallery' );
            $term_name = $terms[0]->name;
             ?>
            <article class="post-406 content-card swiper-slide">
              <a href="<?php the_permalink() ?>" class="content-image-wrapper"><?php the_post_thumbnail( 'medium', ['class' => 'attachment-medium size-medium wp-post-image']); ?></a>
              <p class="content-typology">Gallery: <?php echo $term_name ?></p>
              <h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
              <p class="content-author">by <?php the_author_link(); ?></p>
              <p class="content-date"><?php the_date() ?></p>
            </article>
        <?php	} ?>
      </div>
      <!-- Add Pagination and Arrows -->
    </div>
    <div class="swiper-nav">
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </div>
  <?php if( !is_admin() ): ?>
  <script>
    var swiper = new Swiper('#<?php echo $id ?> .swiper-container', {
      slidesPerView: 1,
      spaceBetween: 30,
      navigation: {
        nextEl: '#<?php echo $id ?> .swiper-button-next',
        prevEl: '#<?php echo $id ?> .swiper-button-prev',
      },
      watchSlidesVisibility: true,
      breakpoints: {
        960: {
          slidesPerView: 3,
        }
      }
    });
  </script>
  <?php endif; ?>

<?php
}
// Restore original Post Data
wp_reset_postdata();
?>
