<?php
add_action( 'after_setup_theme', 'f3f_addfonts',99);
function f3f_addfonts() {
	global $five3_fonts;
	array_push($five3_fonts,'Bowlby One');
}
//register features post type with comprehensssive labels
add_action( 'init', 'f3f_create_feature_post_type', 1 );
function f3f_create_feature_post_type () {
	register_post_type( 'feature',
		array(
			'labels' => array(
				'name' => _x('Features', 'feature type general name'),
				'singular_name' => _x('Feature', 'feature type singular name'), 
				'add_new' => _x('Add New', 'feature'), 
				'add_new_item' => __('Add New Feature'),
				'edit_item' => __('Edit Feature'),
				'new_item' => __('New Feature'),
				'view_item' => __('View Feature'),
				'search_items' => __('Search Features'),
				'not_found' => __('No features found.'),
				'not_found_in_trash' => __('No features found in Trash.'),
				'all_items' => __( 'All Features' )
			),
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'features'),
			'menu_position' => 5
			// 'menu_icon' =>
		)
	);
}
// add f3 metaboxes to features
add_filter('f3_meta_box_post_type', 'feature_post_type', 1, 1);
function feature_post_type($array) {
	array_push( $array, "feature");
	return $array;
}
// add f3 admin scripts to custom post type
add_filter( 'five3_runscript_post_types', 'f3f_runscripts_on_features', 1, 1 );
function f3f_runscripts_on_features( $post_types ) {
	array_push( $post_types, "feature");
	return $post_types;	
}

//custom meta
add_action( 'add_meta_boxes', 'feature_post_type_meta', 1 );
function feature_post_type_meta( $callback ) {
	add_meta_box( 
			'f3f_order',
			__( 'Feature Options', 'five3' ),
			'f3f_order_meta_box',
			'feature',
			'side',
			'low'
		);	
}

// feature_order meta box content
function f3f_order_meta_box( $post ) { ?>
	<?php wp_nonce_field( plugin_basename( __FILE__ ), 'f3f_feature_options' ); ?>
	<p><strong><?php _e('Order') ?></strong></p>
	<p><label class="screen-reader-text" for="feature_order"><?php _e('Order') ?></label><input name="feature_order" type="text" size="4" id="feature_order" value="<?php echo get_post_meta($post->ID, 'feature_order', true )?>" /></p>
<?php }

// save feature_order data on edit page
add_action( 'save_post', 'f3f_order_save_postdata' );
function f3f_order_save_postdata( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	if ( !wp_verify_nonce( $_POST['f3f_feature_options'], plugin_basename( __FILE__ ) ) )
		return;
	if ( !current_user_can( 'edit_post', $post_id ) )
		return;

	update_post_meta($post_id, 'feature_order', $_POST['feature_order']);
}

// Filter custom Excerpt length
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
function custom_excerpt_length( $length ) {
	return 14;
}

/**
 * Custom query on feature post_type ordered by custom meta feature_order
 *
 *
 */
function query_features() {
	
	$args = array(
	    'meta_key'=>'feature_order',
	    'post_type'=>'feature',
	    'orderby'=>'feature_order',
	    'order'=>'ASC'
	);
	$query = new WP_Query( $args );
	query_posts($args);
}

/**
 * Custom query on testimonials category
 *
 *
 */
function query_testimonials() {
	
	// global $query;
	$args = array(
	 'post_type' => 'post',
	'category_name' => 'testimonial',
	 'order' => 'ASC'
	);
	query_posts($args);	
}

/**
 * Output markup for a fixed horizontal side menu. Used in index & child index page templates. 
 *
 *
 * @since 1.2
 */
function f3f_horizontal_nav() { 
	if( get_option( 'f3_horizontal_nav', 'true' ) != 'true' )
		return;

	rewind_posts(); ?>

	<nav id="page">
		<ul>
		<?php while ( have_posts() ) : the_post(); ?>
			<li>
				<h6><a href="#article-<?php the_ID(); ?>"><?php the_title(); ?></a></h6>
				<a href="#article-<?php the_ID(); ?>">Post <?php the_ID(); ?></a>
			</li>
		<?php endwhile; ?>
		</ul>
	</nav>
	<?php
}

function testimonials_slider() { 
?>
	<div id="testimonials_slider">
		<?php slidedeck( 220, array( 'width' => '80%', 'height' => '370px' ) ); ?>
	</div>
<?php
}	

/*
 * Add site description before fixed banner
 *
 */
// add_action( 'get_header', 'get_site_description',99);
function get_site_description() {
	?>
		<div id="title-description" class="five3-font">Meal Replacement<br/>Energy Drink Mix</div>
	<?php
}
/*
 * Remove comments from testimonials posts
 *
 */
add_filter( 'comments_open', 'my_comments_open', 10, 2 );
function my_comments_open( $open, $post_id ) {

	if ( in_category( "testimonial", $post_id ) )
		$open = false;

	return $open;
}
/*
 * Add script to open buynow button in new window
 *
 */
add_action( 'init', 'f3f_buynow_script' );
function f3f_buynow_script() {

	if( is_admin() )
		return;
	/* Scripts */
	wp_enqueue_script( 'buynow', get_stylesheet_directory_uri() . '/js/buynow.js', array( 'jquery', 'jquery-plugins' ) );
}

/*
 * Formatting changes through actions for Woocommerce
 *
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', create_function('', 'echo "<div id=\"primary\"><div id=\"content\" class=\"content\" role=\"main\"
><section id=\"woocommerce\">";'), 10);
add_action('woocommerce_after_main_content', create_function('', 'echo "</section></div></div>";'), 10);

function woocommerce_breadcrumb( $args = array() ) {
	return;
}
function woocommerce_get_sidebar() {
	//woocommerce_get_template( 'shop/sidebar.php' );
}
?>