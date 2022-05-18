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
				//echo "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$date_start_position = strpos($grid_item, "%date(");
				//echo "<br /><strong>date_meta_start_position: </strong>" . $date_start_position;
				
				$date_open_paranthesis = $date_start_position + 5;
				//echo "<br /><strong>date_meta_open_paranthesis: </strong>" . $date_open_paranthesis;
				
				$date_close_paranthesis = $date_start_position + strpos(substr($grid_item, $date_start_position, $date_start_position+50), ")");
				//echo "<br /><strong>date_meta_close_paranthesis: </strong>" . $date_close_paranthesis;
				
				$date_code = substr($grid_item, $date_start_position+1, $date_close_paranthesis-$date_start_position);
				//echo "<br /><strong>date_meta: </strong>" . $date_code;
				
				$date_code_args = substr($grid_item, $date_open_paranthesis+2, $date_close_paranthesis-$date_open_paranthesis-3);
				//echo "<br /><strong>date_meta_args: </strong>" . $date_code_args;
				
				$date_formatted = get_the_date($date_code_args, $post->ID);
				//echo "<br /><strong>date_formatted: </strong>" . $date_formatted;
				
				$grid_item = substr_replace($grid_item, $date_formatted, $date_start_position, $date_close_paranthesis-$date_start_position+2);
				//echo "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";
			}
			
			// Replace metafiled meta
			if(strpos($grid_item, '%meta') !== false){
				//echo "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$meta_start_position = strpos($grid_item, "%meta(");
				//echo "<br /><strong>meta_meta_start_position: </strong>" . $meta_start_position;
				
				$meta_open_paranthesis = $meta_start_position + 5;
				//echo "<br /><strong>meta_meta_open_paranthesis: </strong>" . $meta_open_paranthesis;
				
				$meta_close_paranthesis = $meta_start_position + strpos(substr($grid_item, $meta_start_position, $meta_start_position+50), ")");
				//echo "<br /><strong>meta_meta_close_paranthesis: </strong>" . $meta_close_paranthesis;
				
				$meta_code = substr($grid_item, $meta_start_position+1, $meta_close_paranthesis-$meta_start_position);
				//echo "<br /><strong>meta_meta: </strong>" . $meta_code;
				
				$meta_code_args = substr($grid_item, $meta_open_paranthesis+1, $meta_close_paranthesis-$meta_open_paranthesis-1);
				//echo "<br /><strong>meta_meta_args: </strong>" . $meta_code_args;
				
				$meta_formatted = get_post_meta( $post->ID, $meta_code_args, true );
				//echo "<br /><strong>meta_formatted: </strong>" . $meta_formatted;
				
				$grid_item = substr_replace($grid_item, $meta_formatted, $meta_start_position, $meta_close_paranthesis-$meta_start_position+2);
				//echo "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";
			}
			
			// Replace taxonomy meta
			if(strpos($grid_item, '%tax') !== false){
				//echo "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$tax_start_position = strpos($grid_item, "%tax(");
				//echo "<br /><strong>tax_meta_start_position: </strong>" . $tax_start_position;
				
				$tax_open_paranthesis = $tax_start_position + 5;
				//echo "<br /><strong>tax_meta_open_paranthesis: </strong>" . $tax_open_paranthesis;
				
				$tax_close_paranthesis = $tax_start_position + strpos(substr($grid_item, $tax_start_position, $tax_start_position+50), ")");
				//echo "<br /><strong>tax_meta_close_paranthesis: </strong>" . $tax_close_paranthesis;
				
				$tax_code = substr($grid_item, $tax_start_position+1, $tax_close_paranthesis-$tax_start_position);
				//echo "<br /><strong>tax_meta: </strong>" . $tax_code;
				
				$tax_code_args = substr($grid_item, $tax_open_paranthesis+0, $tax_close_paranthesis-$tax_open_paranthesis-0);
				//echo "<br /><strong>tax_meta_args: </strong>" . $tax_code_args;
				
				$term_list = get_the_terms( $post->ID, $tax_code_args );
				$terms_array = array();				
				foreach ( $term_list as $term ) {
					$terms_array[] = '<a href="'.  esc_attr( get_term_link( $term->slug, $tax_code_args ) ) .'">'. esc_attr( $term->name ) .'</a>';
				}
				$terms_string = join( ', ', $terms_array );

				$tax_formatted = $terms_string;
				//echo "<br /><strong>tax_formatted: </strong>" . $tax_formatted;
				
				$grid_item = substr_replace($grid_item, $tax_formatted, $tax_start_position, $tax_close_paranthesis-$tax_start_position+2);
				//echo "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";
			}
			
		    $code1 = array(
		    	'%title%', 
		    	'%url%', 
		    	'%date%',
		    );
		    
		    $code2 = array(
		    	get_the_title($post->ID), 
		    	get_permalink($post->ID), 
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