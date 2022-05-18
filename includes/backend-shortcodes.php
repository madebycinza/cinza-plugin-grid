<?php
	
add_action( 'init', 'cgrid_shortcodes_init' );
function cgrid_shortcodes_init() {
	add_shortcode( 'cinza_grid', 'cgrid_shortcode' );
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// grid shortcode
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_shortcode( $atts = [], $content = null, $tag = 'cinza_grid' ) {

	// Enqueue scripts
    wp_enqueue_script('isotope');
    wp_enqueue_style('animate');
    wp_enqueue_style('cgrid-frontend');
	
    // Normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
 
    // Override default attributes with user attributes
    $cgrid_atts = shortcode_atts(
        array(
            'id' => 'Empty',
        ), $atts, $tag
    );
	$grid_id = intval( $cgrid_atts['id'] );
    $cgrid_options = get_post_meta($grid_id, '_cgrid_options', true);
    $cgrid_skin = get_post_meta($grid_id, '_cgrid_skin', true);

	// Shortcode validation
    if ( $grid_id == 'Empty' || !is_int($grid_id) || empty($cgrid_options) || (get_post_status_object( get_post_status($grid_id) )->label != 'Published' )) {
        return "<p class='cgrid-error'>Please enter a valid grid ID.</p>";
    }

    // Retrieves an array of the latest posts, or posts matching the given criteria
    // https://developer.wordpress.org/reference/functions/get_posts/
	$args = array(
		'post_type' => esc_attr($cslider_options['cgrid_posttype']),
		'post_status' => 'publish',
		'numberposts' => -1,
	);
	$posts = get_posts( $args );
	
/*
	// Sorting
	$sorting ='<h2>Sort</h2>
    <div id="sorts" class="button-group">
		<button class="button is-checked" data-sort-by="original-order">original order</button>
		<button class="button" data-sort-by="title">title</button>
		<button class="button" data-sort-by="color">color</button>
    </div>';
    
	// Filters
	$filters = '<h2>Filter</h2>
    <div id="filters" class="button-group">
		<button class="button is-checked" data-filter="*">show all</button>
		<button class="button" data-filter="yellow">yellow</button>
		<button class="button" data-filter="blue">blue</button>
		<button class="button" data-filter="purple">purple</button>
    </div>';
*/






		






    // Grid items
    $grid = '<div class="grid">';    
	if( !empty( $posts ) ){
		foreach ( $posts as $post ){
			
			$grid_item = $cgrid_skin['cgrid_skin_content'];
			
			// Replace date meta with custom format
			if(strpos($grid_item, '%date(') !== false){
				//echo "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><br />";
				
				$date_meta_start_position = strpos($grid_item, "%date(");
				//echo "<br /><strong>date_meta_start_position: </strong>" . $date_meta_start_position;
				
				$date_meta_open_paranthesis = $date_meta_start_position + 5;
				//echo "<br /><strong>date_meta_open_paranthesis: </strong>" . $date_meta_open_paranthesis;
				
				$date_meta_close_paranthesis = $date_meta_start_position + strpos(substr($grid_item, $date_meta_start_position, $date_meta_start_position+50), ")");
				//echo "<br /><strong>date_meta_close_paranthesis: </strong>" . $date_meta_close_paranthesis;
				
				$date_meta = substr($grid_item, $date_meta_start_position+1, $date_meta_close_paranthesis-$date_meta_start_position);
				//echo "<br /><strong>date_meta: </strong>" . $date_meta;
				
				$date_meta_args = substr($grid_item, $date_meta_open_paranthesis+2, $date_meta_close_paranthesis-$date_meta_open_paranthesis-3);
				//echo "<br /><strong>date_meta_args: </strong>" . $date_meta_args;
				
				$date_formatted = get_the_date($date_meta_args, $post->ID);
				//echo "<br /><strong>date_formatted: </strong>" . $date_formatted;
				
				$grid_item = substr_replace($grid_item, $date_formatted, $date_meta_start_position, $date_meta_close_paranthesis-$date_meta_start_position+2);
				//echo "<strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";
			}
			
		    $code1 = array(
		    	'%title%', 
		    	'%url%', 
		    	'%date%',
		    );
		    
		    $code2 = array(
		    	$post->post_title, 
		    	get_permalink( $post->ID ), 
		    	get_the_date('F j, Y', $post->ID),
		    );
		    
			$grid .= '<div class="element-item">'. str_replace($code1, $code2, $grid_item) .'</div>';
		}
	}
    $grid .= '</div>';
    
    $grid_reference = '<div class="grid">
      <div class="element-item transition metal " data-category="transition">
        <h3 class="title">One</h3>
        <p class="color">Yellow</p>
      </div>
      <div class="element-item metalloid " data-category="metalloid">
        <h3 class="title">Two</h3>
        <p class="color">Blue</p>
      </div>
      <div class="element-item post-transition metal " data-category="post-transition">
        <h3 class="title">Three</h3>
        <p class="color">Purple</p>
      </div>
    </div>';
    
    // Style
    $style = '';
    
    //return $sorting . $filters . $grid . $style;
    return $grid . $style;
}

function cgrid_replace_date( $p ) {
	//echo get_the_date( 'l F j, Y', $p );
	
	return $date_formatted;
}