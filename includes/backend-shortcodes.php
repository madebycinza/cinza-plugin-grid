<?php
	
add_action( 'init', 'cgrid_shortcodes_init' );
function cgrid_shortcodes_init() {
	add_shortcode( 'cinzagrid', 'cgrid_shortcode' ); // Main
	add_shortcode( 'cinza_grid', 'cgrid_shortcode' ); // Fallback
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Grid shortcode
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_shortcode( $atts = [], $content = null, $tag = 'cinzagrid' ) {

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
		'post_type' => esc_attr($cgrid_options['cgrid_posttype']),
		'post_status' => 'publish',
		'numberposts' => -1,
	);
	$posts = get_posts( $args );
	
/*
	// BEGIN: "My First Grid" test
	// Sorting
	$sorting ='<h2>Sort</h2>
    <div id="cinza-grid-sorts" class="cinza-grid-button-group">
		<button class="button is-checked" data-sort-by="original-order">Original order</button>
		<button class="button" data-sort-by="title">Title</button>
		<button class="button" data-sort-by="color">Color</button>
    </div>';
    
    // Filter 
	$filters = '<h2>Filter by Meta Field</h2>
    <div id="cinza-grid-filters" class="cinza-grid-button-group">
		<button class="button is-checked" data-filter="*">Show all (not case sensitive)</button>
		<button class="button" data-filter="red">Red</button>
		<button class="button" data-filter="brown">Brown</button>
		<button class="button" data-filter="purple">Purple</button>
		<button class="button" data-filter="green">Green</button>
		<button class="button" data-filter="blue">Blue</button>
    </div>';
	// END: "My First Grid" test
*/
    
/*
    // BEGIN: "My Second Grid" test
	// Filters
	$filters = "<div class='cinza-grid-filters'>
        <div class='ui-group'>
            <h3>Color</h3>
            <select class='filter-select' value-group='color'>
                <option value=''>any (case sensitive)</option>
                <option value='.Red'>Red</option>
                <option value='.Brown'>Brown</option>
                <option value='.Purple'>Purple</option>
                <option value='.Green'>Green</option>
                <option value='.Blue'>Blue</option>
            </select>
        </div>
		<div class='ui-group'>
			<h3>Category</h3>
			<select class='filter-select' value-group='category'>
				<option value=''>any (case sensitive)</option>
				<option value='.Small'>Small</option>
				<option value='.Medium'>Medium</option>
				<option value='.Large'>Large</option>
			</select>
		</div>
	</div>";
	// END: "My Second Grid" test
*/

    // Grid items
    $grid = '<div class="cinza-grid cinza-grid-'.$grid_id.'">';    
	if( !empty( $posts ) ){
		$debug = "";

		foreach ( $posts as $post ){			
			$grid_item = $cgrid_skin['cgrid_skin_content'];
						
			// Replace date meta with custom format
			while(strpos($grid_item, '%date(') !== false){
				//echo "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$date_start_position = strpos($grid_item, "%date(");
				//$debug .= "<br /><strong>date_meta_start_position: </strong>" . $date_start_position;
				
				$date_open_paranthesis = $date_start_position + 5;
				//$debug .= "<br /><strong>date_meta_open_paranthesis: </strong>" . $date_open_paranthesis;
				
				$date_close_paranthesis = $date_start_position + strpos(substr($grid_item, $date_start_position, $date_start_position+50), ")");
				//$debug .= "<br /><strong>date_meta_close_paranthesis: </strong>" . $date_close_paranthesis;
				
				$date_code = substr($grid_item, $date_start_position+1, $date_close_paranthesis-$date_start_position);
				//$debug .= "<br /><strong>date_meta: </strong>" . $date_code;
				
				$date_code_args = substr($grid_item, $date_open_paranthesis+2, $date_close_paranthesis-$date_open_paranthesis-3);
				//$debug .= "<br /><strong>date_meta_args: </strong>" . $date_code_args;
				
				$date_formatted = get_the_date($date_code_args, $post->ID);
				//$debug .= "<br /><strong>date_formatted: </strong>" . $date_formatted;
				
				$grid_item = substr_replace($grid_item, $date_formatted, $date_start_position, $date_close_paranthesis-$date_start_position+2);
				//$debug .= "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";
			}
			
			// Replace metafiled meta
			while(strpos($grid_item, '%meta') !== false){
				//$debug .= "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$meta_start_position = strpos($grid_item, "%meta(");
				//$debug .= "<br /><strong>meta_meta_start_position: </strong>" . $meta_start_position;
				
				$meta_open_paranthesis = $meta_start_position + 5;
				//$debug .= "<br /><strong>meta_meta_open_paranthesis: </strong>" . $meta_open_paranthesis;
				
				$meta_close_paranthesis = $meta_start_position + strpos(substr($grid_item, $meta_start_position, $meta_start_position+50), ")");
				//$debug .= "<br /><strong>meta_meta_close_paranthesis: </strong>" . $meta_close_paranthesis;
				
				$meta_code = substr($grid_item, $meta_start_position+1, $meta_close_paranthesis-$meta_start_position);
				//$debug .= "<br /><strong>meta_meta: </strong>" . $meta_code;
				
				$meta_code_args = substr($grid_item, $meta_open_paranthesis+2, $meta_close_paranthesis-$meta_open_paranthesis-3);
				//$debug .= "<br /><strong>meta_meta_args: </strong>" . $meta_code_args;
				
				$meta_formatted = get_post_meta( $post->ID, $meta_code_args, true );
				//$debug .= "<br /><strong>meta_formatted: </strong>" . $meta_formatted;
				
				$grid_item = substr_replace($grid_item, $meta_formatted, $meta_start_position, $meta_close_paranthesis-$meta_start_position+2);
				//$debug .= "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";					
			}
			
			// Replace taxonomy meta (without link and without separator)
			while(strpos($grid_item, '%tax(') !== false){
				//$debug .= "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$tax_start_position = strpos($grid_item, "%tax(");
				//$debug .= "<br /><strong>tax_meta_start_position: </strong>" . $tax_start_position;
				
				$tax_open_paranthesis = $tax_start_position + 5;
				//$debug .= "<br /><strong>tax_meta_open_paranthesis: </strong>" . $tax_open_paranthesis;
				
				$tax_close_paranthesis = $tax_start_position + strpos(substr($grid_item, $tax_start_position, $tax_start_position+50), ")");
				//$debug .= "<br /><strong>tax_meta_close_paranthesis: </strong>" . $tax_close_paranthesis;
				
				$tax_code = substr($grid_item, $tax_start_position+1, $tax_close_paranthesis-$tax_start_position);
				//$debug .= "<br /><strong>tax_meta: </strong>" . $tax_code;
				
				$tax_code_args = substr($grid_item, $tax_open_paranthesis+1, $tax_close_paranthesis-$tax_open_paranthesis-2);
				//$debug .= "<br /><strong>tax_meta_args: </strong>" . $tax_code_args;
				
				$term_list = get_the_terms( $post->ID, $tax_code_args );
				if( $term_list && ! is_wp_error( $term_list ) ) {
					$terms_array = array();				
					foreach ( $term_list as $term ) {
						$terms_array[] = esc_attr( $term->name );
					}
					$terms_string = join( ' ', $terms_array );
	
					$tax_formatted = $terms_string;
					//$debug .= "<br /><strong>tax_formatted: </strong>" . $tax_formatted;
					
					$grid_item = substr_replace($grid_item, $tax_formatted, $tax_start_position, $tax_close_paranthesis-$tax_start_position+2);
					//$debug .= "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";					
				} else {
					$grid_item = substr_replace($grid_item, "Invalid taxonomy.", $tax_start_position, $tax_close_paranthesis-$tax_start_position+2);
				}
			}
			
			// Replace taxonomy meta (without link and with separator)
			while(strpos($grid_item, '%taxsep(') !== false){
				//$debug .= "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$taxsep_start_position = strpos($grid_item, "%taxsep(");
				//$debug .= "<br /><strong>tax_meta_start_position: </strong>" . $taxsep_start_position;
				
				$taxsep_open_paranthesis = $taxsep_start_position + 5;
				//$debug .= "<br /><strong>tax_meta_open_paranthesis: </strong>" . $taxsep_open_paranthesis;
				
				$taxsep_close_paranthesis = $taxsep_start_position + strpos(substr($grid_item, $taxsep_start_position, $taxsep_start_position+50), ")");
				//$debug .= "<br /><strong>tax_meta_close_paranthesis: </strong>" . $taxsep_close_paranthesis;
				
				$taxsep_code = substr($grid_item, $taxsep_start_position+1, $taxsep_close_paranthesis-$taxsep_start_position);
				//$debug .= "<br /><strong>tax_meta: </strong>" . $taxsep_code;
				
				$taxsep_code_args = substr($grid_item, $taxsep_open_paranthesis+1, $taxsep_close_paranthesis-$taxsep_open_paranthesis-2);
				//$debug .= "<br /><strong>tax_meta_args: </strong>" . $taxsep_code_args;
				
				$term_list = get_the_terms( $post->ID, $taxsep_code_args );
				if( $term_list && ! is_wp_error( $term_list ) ) {
					$terms_array = array();				
					foreach ( $term_list as $term ) {
						$terms_array[] = esc_attr( $term->name );
					}
					$terms_string = join( ', ', $terms_array );
	
					$taxsep_formatted = $terms_string;
					//$debug .= "<br /><strong>tax_formatted: </strong>" . $taxsep_formatted;
					
					$grid_item = substr_replace($grid_item, $taxsep_formatted, $taxsep_start_position, $taxsep_close_paranthesis-$taxsep_start_position+2);
					//$debug .= "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";					
				} else {
					$grid_item = substr_replace($grid_item, "Invalid taxonomy.", $taxsep_start_position, $taxsep_close_paranthesis-$taxsep_start_position+2);
				}
			}
			
			// Replace taxonomy meta (with link and with separator)
			while(strpos($grid_item, '%taxurl(') !== false){
				//$debug .= "<strong>grid_item (before): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br />";
				
				$taxurl_start_position = strpos($grid_item, "%taxurl(");
				//$debug .= "<br /><strong>taxurl_meta_start_position: </strong>" . $taxurl_start_position;
				
				$taxurl_open_paranthesis = $taxurl_start_position + 5;
				//$debug .= "<br /><strong>taxurl_meta_open_paranthesis: </strong>" . $taxurl_open_paranthesis;
				
				$taxurl_close_paranthesis = $taxurl_start_position + strpos(substr($grid_item, $taxurl_start_position, $taxurl_start_position+50), ")");
				//$debug .= "<br /><strong>taxurl_meta_close_paranthesis: </strong>" . $taxurl_close_paranthesis;
				
				$taxurl_code = substr($grid_item, $taxurl_start_position+1, $taxurl_close_paranthesis-$taxurl_start_position);
				//$debug .= "<br /><strong>taxurl_meta: </strong>" . $taxurl_code;
				
				$taxurl_code_args = substr($grid_item, $taxurl_open_paranthesis+4, $taxurl_close_paranthesis-$taxurl_open_paranthesis-5);
				//$debug .= "<br /><strong>taxurl_meta_args: </strong>" . $taxurl_code_args;
				
				$term_list = get_the_terms( $post->ID, $taxurl_code_args );
				if( $term_list && ! is_wp_error( $term_list ) ) {
					$terms_array = array();				
					foreach ( $term_list as $term ) {
						$terms_array[] = '<a href="'.  esc_attr( get_term_link( $term->slug, $taxurl_code_args ) ) .'">'. esc_attr( $term->name ) .'</a>';
					}
					$terms_string = join( ', ', $terms_array );
	
					$taxurl_formatted = $terms_string;
					//$debug .= "<br /><strong>taxurl_formatted: </strong>" . $taxurl_formatted;
					
					$grid_item = substr_replace($grid_item, $taxurl_formatted, $taxurl_start_position, $taxurl_close_paranthesis-$taxurl_start_position+2);
					//$debug .= "<br /><strong>grid_item (after): </strong><br />" . nl2br(htmlentities($grid_item)) . "<br /><hr />";					
				} else {
					$grid_item = substr_replace($grid_item, "Invalid taxonomy.", $taxurl_start_position, $taxurl_close_paranthesis-$taxurl_start_position+2);
				}
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
		    
			$grid .= '<div class="cinza-grid-item">'. str_replace($code1, $code2, $grid_item) .'</div>';
		}
	}
    $grid .= '</div>';
    
    // Style
    $style = '';
    
    //return $debug . $sorting . $filters . $grid . $style;
    return $debug . $grid . $style;
}

function cgrid_replace_date( $p ) {
	//echo get_the_date( 'l F j, Y', $p );
	
	return $date_formatted;
}