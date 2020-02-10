<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <!-- Search, Blog index, archives, Profile -->
	<?php if ( is_search() || is_archive() || is_home() ) : ?>

		<div class="posts-stream">
			<div class="loader"><?php _e( 'Loading...', 'onesocial' ); ?></div>
		</div>

	<?php endif; ?>

	<?php
	if ( !is_single() ) {
		?>

		<div class="header-area">
			<?php
			$header_class = '';

			if ( has_post_thumbnail() ) {
				$header_class = ' category-thumb';
				?>

				<a class="entry-post-thumbnail" href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail( 'post-thumb' ); ?>
				</a>

			<?php } ?>

			<div class="profile-visible"><?php echo get_the_date( 'M j' ); ?></div>

			<!-- Title -->
			<header class="entry-header<?php echo $header_class; ?>">

				<!-- Search, Blog index, archives -->
				<?php if ( is_search() || is_archive() || is_home() || ( buddyboss_is_bp_active() && bp_is_user() ) ) : ?>

					<h2 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'onesocial' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h2>
					<!-- Single blog post -->
				<?php else : ?>

					<div class="table">
						<div class="table-cell">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</div>
					</div>

				<?php endif; // is_single()    ?>

			</header><!-- .entry-header -->

		</div><!-- /.header-area -->

	<?php } ?>

	<!-- Single job -->
	<?php if ( function_exists( 'WPJM' ) && get_post_type() === 'job_listing' ): ?>
		<header class="entry-header<?php echo $header_class; ?>">
			<h1 class="entry-title"><?php the_title(); ?><?php if(function_exists('sap_edit_post_link')) sap_edit_post_link(); ?></h1>
		</header>
	<?php endif; ?>

	<!-- Search, Blog index, archives, Profile -->
	<?php if ( is_search() || is_archive() || is_home() || ( buddyboss_is_bp_active() && bp_is_user() ) ) : // Only display Excerpts for Search, Blog index, Profile and archives    ?>

		<div class="entry-content entry-summary">

			<?php
			global $post;
			$post_content = $post->post_content;

			//entry-content
			if ( 'excerpt' === onesocial_get_option( 'onesocial_entry_content' ) ):
				the_excerpt();
			else:
				the_content();
			endif;

			?>

			<footer class="entry-meta">
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'onesocial' ), the_title_attribute( 'echo=0' ) ) ); ?>" class="read-more"><?php _e( 'Continue reading', 'onesocial' ); ?></a>
				<span class="sep"><?php _e( '.', 'onesocial' ) ?></span>
				<span><?php echo boss_estimated_reading_time( $post_content ); ?></span>
				<a href="#" class="to-top bb-icon-arrow-top-f"></a>
			</footer><!-- .entry-meta -->

		</div><!-- .entry-content -->

		<!-- all other templates -->
	<?php else : ?>
		<div class="entry-main">
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'onesocial' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'onesocial' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->


		</div>
		<!-- /.entry-main -->

	<?php endif; ?>

</article><!-- #post -->
