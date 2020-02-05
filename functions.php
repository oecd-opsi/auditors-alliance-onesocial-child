<?php
/**
 * @package OneSocial Child Theme
 * The parent theme functions are located at /onesocial/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

/**
 * Sets up theme defaults
 *
 * @since OneSocial Child Theme 1.0.0
 */
function onesocial_child_theme_setup()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   * Read more at: http://www.buddyboss.com/tutorials/language-translations/
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'onesocial', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'onesocial' instances in all child theme files to 'onesocial_child_theme'.
  // load_theme_textdomain( 'onesocial_child_theme', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'onesocial_child_theme_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since OneSocial Child Theme  1.0.0
 */
function onesocial_child_theme_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  /*
   * Styles
   */
  wp_enqueue_style( 'onesocial-child-custom', get_stylesheet_directory_uri().'/css/custom.css' );
}
add_action( 'wp_enqueue_scripts', 'onesocial_child_theme_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here

/*
 * Get the most recently replied-to topics, and their most recent reply
 * from https://www.daggerhart.com/bbpress-recent-replies-shortcode/
 */
function custom_bbpress_recent_replies_by_topic($atts){
  $short_array = shortcode_atts(array('show' => 5, 'forum' => false, 'include_empty_topics' => false), $atts);
  extract($short_array);

  // default values
  $post_types = array('reply');
  $meta_key = '_bbp_last_reply_id';

  // allow for topics with no replies
  if ( $include_empty_topics ) {
    $meta_key = '_bbp_last_active_id';
    $post_types[] = 'topic';
  }

  // get the 5 topics with the most recent replies
  $args = array(
    'posts_per_page' => $show,
    'post_type' => array('topic'),
    'post_status' => array('publish'),
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'meta_key' => $meta_key,
  );
  // allow for specific forum limit
  if ($forum){
    $args['post_parent'] = $forum;
  }

  $query = new WP_Query($args);
  $reply_ids = array();

  // get the reply post->IDs for these most-recently-replied-to-topics
  while($query->have_posts()){
    $query->the_post();
    if ($reply_post_id = get_post_meta(get_the_ID(), $meta_key, true)){
      $reply_ids[] = $reply_post_id;
    }
  }
  wp_reset_query();

  // get the actual replies themselves
  $args = array(
    'posts_per_page' => $show,
    'post_type' => $post_types,
    // 'post__in' => $reply_ids,
    'orderby' => 'date',
    'order' => 'DESC'
  );

  $query = new WP_Query($args);
  ob_start();
    // loop through results and output our rows
    while($query->have_posts()){
      $query->the_post();

      // custom function for a single reply row
      custom_bbpress_recent_reply_row_template( $query->current_post + 1 );
    }
    wp_reset_query();

  $output = '<div class="latest-replies"><h3 class="latest-replies-title">Latest post</h3><ul class="latest-replies-list">' . ob_get_clean() . '</ul></div>';
  return $output;
}
add_shortcode('bbpress_recent_replies_by_topic', 'custom_bbpress_recent_replies_by_topic');
/*
 * Executed during our custom loop
 */
function custom_bbpress_recent_reply_row_template( $row_number ){

  // get the reply title
  $title = get_the_title();
  // $title = substr( $title, 0, 55); // trim title to specific number of characters (55 characters)
  // $title = wp_trim_words( $title, 5, '...'); // trim title to specific number of words (5 words)...

  // get belonging forum
  $parent = array_reverse( get_post_ancestors( get_the_ID()) );
  $first_parent = get_page( $parent[0] );
  $parent_forum_ID = apply_filters('the_ID', $first_parent->ID);
  $parent_forum_title = bbp_get_forum_title( $parent_forum_ID );

  // determine if odd or even row
  $row_class = ($row_number % 2) ? 'odd' : 'even';
  ?>
    <li class="bbpress-recent-reply-row <?php print $row_class; ?>">
      <div class="recent-replies-avatar"><?php echo get_avatar( get_the_author_meta( 'ID' ) ); ?></div>
      <div class="recent-replies-body">
        <div class="recent-replies-title"><a href="<?php the_permalink(); ?>"><?php the_author(); ?> <?php echo __('posted an update in ', 'onesocial') ?> <?php echo $parent_forum_title; ?></a></div>
      </div>
      <div class="recent-replies-time-diff"><?php print human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ago'; ?></div>
    </li>
  <?php

  // Refs
  // http://codex.wordpress.org/Template_Tags#Post_tags
  // http://codex.wordpress.org/Function_Reference/get_avatar
  // http://codex.wordpress.org/Function_Reference/human_time_diff
  // (template tags for bbpress)
  // https://bbpress.trac.wordpress.org/browser/trunk/src/includes/users/template.php
  // https://bbpress.trac.wordpress.org/browser/trunk/src/includes/replies/template.php
}

// Shortcode to display Gallery featured image
function gallery_feat_img() {
  $term_id = get_queried_object_id();
  $feat_img = get_term_meta( $term_id, 'wpcf-featured-image', true );
  return $feat_img;
}
add_shortcode( 'gallery-featured-img', 'gallery_feat_img' );

// Shortcode to display Term description
function term_desc() {
  $term_id = get_queried_object_id();
  $term_desc = term_description( $term_id );
  return $term_desc;
}
add_shortcode( 'term-description', 'term_desc' );

// Shortcode to display Term name
function term_name() {
  $term_obj = get_queried_object();
  $term_name = $term_obj->name;
  return $term_name;
}
add_shortcode( 'term-name', 'term_name' );

// Display Gallery main topic discussion
function gallery_main_topic() {
  $term_id = get_queried_object_id();
  // Query to get the Forum with current term associated
  $args = array(
  	'post_type'              => array( 'forum' ),
    'tax_query' => array(
        array (
            'taxonomy' => 'gallery',
            'field' => 'term_id',
            'terms' => $term_id,
        )
    ),
  );
  $query = new WP_Query( $args );
  $related_forum = $query->posts[0];
  $forum_id = $related_forum->ID;
  return do_shortcode( '[bbp-single-topic id=133]');
}
add_shortcode( 'gallery-forum', 'gallery_main_topic' );

/**
 * Remove archive title prefixes.
 *
 * @param  string  $title  The archive title from get_the_archive_title();
 * @return string          The cleaned title.
 */
function grd_custom_archive_title( $title ) {
	// Remove any HTML, words, digits, and spaces before the title.
	return preg_replace( '#^[\w\d\s]+:\s*#', '', strip_tags( $title ) );
}
add_filter( 'get_the_archive_title', 'grd_custom_archive_title' );
