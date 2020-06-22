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

  // Use home featured image as background for the home hero
  if( is_front_page() ){
    global $post;
    $featured_img = get_the_post_thumbnail_url( $post->ID, 'full');
    wp_add_inline_style( 'onesocial-child-custom', '.home-page #page .wp-block-search {background-image:url('.$featured_img.');}' );
  }

}
add_action( 'wp_enqueue_scripts', 'onesocial_child_theme_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here

// Add assets to Gutenberg
function bs_block_styles_enqueue_javascript() {

	// add block style
  wp_enqueue_script( 'bs-block-styles-script',
    get_stylesheet_directory_uri() . '/js/bs-block.js',
    array( 'wp-blocks', 'wp-dom' )
  );

}
add_action( 'enqueue_block_editor_assets', 'bs_block_styles_enqueue_javascript', 0 );

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
        <div class="recent-replies-title"><a href="<?php bbp_reply_url( get_the_ID() ) ?>"><?php the_author(); ?> <?php echo __('posted an update in ', 'onesocial') ?> <?php echo $parent_forum_title; ?></a></div>
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
  wp_reset_query();
  return do_shortcode( '[bbp-single-forum id=' . $forum_id . ']');
}
add_shortcode( 'gallery-forum', 'gallery_main_topic' );

// Display Content related topic discussion
function content_topic() {
  global $post;
  $topic_id = toolset_get_related_post( $post, 'content-topic', 'child' );
  return do_shortcode( '[bbp-single-topic id='.$topic_id.']');
}
add_shortcode( 'content-forum', 'content_topic' );

/**
 * Remove archive title prefixes.
 *
 * @param  string  $title  The archive title from get_the_archive_title();
 * @return string          The cleaned title.
 */
function bs_custom_archive_title( $title ) {
	// Remove any HTML, words, digits, and spaces before the title.
	return preg_replace( '#^[\w\d\s]+:\s*#', '', strip_tags( $title ) );
}
add_filter( 'get_the_archive_title', 'bs_custom_archive_title' );

// Display Back to gallery link - shortcode
function back_to_gallery_func() {
  global $post;
  $terms = get_the_terms( $post->ID, 'gallery');
  $term_url = get_term_link( $terms[0]->term_id );
  $output = '<a href="' . $term_url . '" class="back-to-gallery">&lt; &lt; Back to <span>' . $terms[0]->name . '</span></a>';
  return $output;
}
add_shortcode( 'back-to-gallery', 'back_to_gallery_func');

// Gallery index - Shortcode
function gallery_index_func() {
  // Get Gallery ID
  global $post;
  $terms = get_the_terms( $post->ID, 'gallery');
  $gallery_id = $terms[0]->term_id;

  // WP_Query arguments
  $args = array(
  	'post_type'              => array( 'content' ),
  	'post_status'            => array( 'published' ),
  	'nopaging'               => true,
  	'posts_per_page'         => '-1',
    'tax_query' => array(
        array (
          'taxonomy' => 'gallery',
          'field' => 'term_id',
          'terms' => $gallery_id,
        )
    ),
  );

  // The Query
  $query = new WP_Query( $args );

  $output = '<ul class="gallery-index">';

  // The Loop
  if ( $query->have_posts() ) {
  	while ( $query->have_posts() ) {
  		$query->the_post();

      $url = get_permalink();
      $title = get_the_title();
      $date = get_the_date( 'F j, Y' );

      $output .= '<li><h3><a href="' . $url . '">' . $title . '</a></h3><p class="date">' . $date . '</p></li>';

  	}
  } else {
  	// no posts found
  }

  $output .= '</ul>';

  // Restore original Post Data
  wp_reset_postdata();

  return $output;
}
add_shortcode( 'gallery-index', 'gallery_index_func' );

// Remove single header from single content template
function content_remove_single_header( $value ) {
  $value = is_single() && !( function_exists( 'is_bbpress' ) && is_bbpress() ) && !( function_exists( 'is_product' ) && is_product() ) && !( function_exists( 'WPJM' ) && get_post_type() === 'job_listing' ) && !is_singular( 'content' );
  return $value;
}
add_filter( 'onesocial_single_header', 'content_remove_single_header' );

// Redirect from forum to gallery if there's a relationship
function forum_to_gallery_redirect() {

  if(  bbp_is_single_forum() ) {

    global $post;
    $terms = get_the_terms( $post->ID, 'gallery');
    if( !empty($terms) ) {
      $gallery_url = get_term_link( $terms[0] );
      wp_safe_redirect( $gallery_url );
      exit;
    }

  }

}
add_action( 'template_redirect', 'forum_to_gallery_redirect' );

// Redirect from topic to content if there's a relationship
function topic_to_content_redirect() {

  if(  bbp_is_single_topic() ) {

    global $post;
    $content_id = toolset_get_related_post( $post, 'content-topic', 'parent' );
    if( $content_id > 0 ) {
      $content_url = get_permalink( $content_id );
      wp_safe_redirect( $content_url );
      exit;
    }

  }

}
add_action( 'template_redirect', 'topic_to_content_redirect' );

// Redirect Content archive and main forum page to homepage
function mainforum_to_home_redirect() {

  if(  bbp_is_forum_archive() ||  is_post_type_archive('content') ) {
    wp_safe_redirect( site_url() );
    exit;
  }

}
add_action( 'template_redirect', 'mainforum_to_home_redirect' );

// Avoid bbPress moderation queue filter to work on Gallery archive pages
function remove_moderation_queue_filter( $sql, $query ) {

  if( is_tax( 'gallery' ) ) {
    $sql = str_replace( 'AND ID NOT IN', 'AND wp_posts.ID NOT IN', $sql );
  }

  return $sql;

}
add_filter( 'posts_where', 'remove_moderation_queue_filter', 300, 2 );

// require_once('includes/bp-custom.php');

// Protect frontend: not logged in users get redirected to an information page.
function bs_guest_redirect() {

  if( bbp_is_single_topic() || bbp_is_single_reply() || is_singular( 'content' ) ) {
    if( !is_user_logged_in() ) {
      wp_safe_redirect( site_url( '/not-logged-in/') );
      exit;
    }
  }

}
add_action( 'template_redirect', 'bs_guest_redirect' );

// Extend Gettext override plugin functionalities to use also gettext with context
function bs_edit_label( $translated, $original, $context, $domain ) {

  global $MP_Gettext_Override;

  return $MP_Gettext_Override->mp_gettext_change( $translated, $original, $domain );

}
add_filter( 'gettext_with_context', 'bs_edit_label', 10, 4 );

// Required label
function bp_change_required_label($translated_string, $field_id) {
		return '<span class="red-asterisk" aria-hidden="true">*</span><span class="screen-reader-text">(required)</span>';
}
add_filter('bp_get_the_profile_field_required_label', 'bp_change_required_label', 10, 2);

// Add dynamic select field for Gallery field in Submit a piece form
function bs_dynamic_select_field_galleries_values ( $scanned_tag, $replace ) {

  if ( $scanned_tag['name'] != 'gallery-interests' && $scanned_tag['name'] != 'piece-type' )
    return $scanned_tag;

  if ( $scanned_tag['name'] == 'gallery-interests' ) {
    $taxomy_slug = 'gallery';
  } elseif ( $scanned_tag['name'] == 'piece-type' ) {
    $taxomy_slug = 'typology';
  }

  $rows = get_terms( array (
    'taxonomy'    => $taxomy_slug,
    'hide_empty'  => false,
    'fields'      => 'names',
  ) );

  if ( ! $rows )
      return $scanned_tag;

  foreach ( $rows as $row ) {
      $scanned_tag['raw_values'][] = $row . '|' . $row;
  }

  $pipes = new WPCF7_Pipes($scanned_tag['raw_values']);

  $scanned_tag['values'] = $pipes->collect_befores();
  $scanned_tag['pipes'] = $pipes;

  return $scanned_tag;
}
add_filter( 'wpcf7_form_tag', 'bs_dynamic_select_field_galleries_values', 10, 2);

// Edit members per page number
function bs_bp_members_per_page( $retval ) {
  $retval['per_page'] = 40;
  return $retval;
}
add_filter( 'bp_after_has_members_parse_args', 'bs_bp_members_per_page' );

// Add required legend to form
function required_legend() {
  echo '<p><small><span class="red-asterisk" aria-hidden="true">*</span> required field</small></p>';
}
add_action( 'bp_after_profile_field_content', 'required_legend', 20 );
add_action( 'bp_before_registration_submit_buttons', 'required_legend' );
