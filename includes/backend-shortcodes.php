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
    if ( $grid_id == 'Empty' || !is_int($grid_id) || empty($cgrid_options) ) {
        return "<p class='cgrid-error'>ERROR: Please enter a valid Cinza Grid ID.</p>";
    } else if ( get_post_status_object( get_post_status($grid_id) )->label == 'Draft' ) {
        return "<p class='cgrid-error'>ERROR: This Cinza Grid is not published yet.</p>";
    }

    // Retrieves an array of the latest posts, or posts matching the given criteria
    // https://developer.wordpress.org/reference/functions/get_posts/
    $aux_orderby_meta = esc_attr($cgrid_options['cgrid_orderby']) === "meta_value";
    $aux_taxonomy = !empty(esc_attr($cgrid_options['cgrid_tax'])) && !empty(esc_attr($cgrid_options['cgrid_tax_terms']));
    $aux_taxonomy_terms = explode (",", esc_attr($cgrid_options['cgrid_tax_terms'])); 
    
    if ($aux_orderby_meta && $aux_taxonomy) {
	    //echo('Scenario 1');
		$args = array(
			'post_type' => esc_attr($cgrid_options['cgrid_posttype']),
			'post_status' => 'publish',
			'numberposts' => esc_attr($cgrid_options['cgrid_num']),
			'meta_key' => esc_attr($cgrid_options['cgrid_orderby_meta']),
			'orderby' => 'meta_value',
			'order' => esc_attr($cgrid_options['cgrid_order']),
		    'tax_query' => array(
		        array(
		            'taxonomy' => esc_attr($cgrid_options['cgrid_tax']),
		            'field'    => 'slug',
		            'terms'    => $aux_taxonomy_terms,
		        ),
		    ),
		);
    } else if (!$aux_orderby_meta && $aux_taxonomy) {
	    //echo('Scenario 2');
		$args = array(
			'post_type' => esc_attr($cgrid_options['cgrid_posttype']),
			'post_status' => 'publish',
			'numberposts' => esc_attr($cgrid_options['cgrid_num']),
			'orderby' => esc_attr($cgrid_options['cgrid_orderby']),
			'order' => esc_attr($cgrid_options['cgrid_order']),
		    'tax_query' => array(
		        array(
		            'taxonomy' => esc_attr($cgrid_options['cgrid_tax']),
		            'field'    => 'slug',
		            'terms'    => $aux_taxonomy_terms,
		        ),
		    ),
		);
    } else if ($aux_orderby_meta && !$aux_taxonomy) {
	    //echo('Scenario 3');
		$args = array(
			'post_type' => esc_attr($cgrid_options['cgrid_posttype']),
			'post_status' => 'publish',
			'numberposts' => esc_attr($cgrid_options['cgrid_num']),
			'meta_key' => esc_attr($cgrid_options['cgrid_orderby_meta']),
			'orderby' => 'meta_value',
			'order' => esc_attr($cgrid_options['cgrid_order']),
		);
    } else {
	    //echo('Scenario 4');
		$args = array(
			'post_type' => esc_attr($cgrid_options['cgrid_posttype']),
			'post_status' => 'publish',
			'numberposts' => esc_attr($cgrid_options['cgrid_num']),
			'orderby' => esc_attr($cgrid_options['cgrid_orderby']),
			'order' => esc_attr($cgrid_options['cgrid_order']),
		);
    }
	$posts = get_posts( $args );
	
	// Sorting
	$sorts = '';
	$sorts_data = '';
	$sorts_temp = empty($cgrid_options['cgrid_sorting']) ? '' : $cgrid_options['cgrid_sorting'];
	
	if(!empty($sorts_temp)) {
		$sorts .= '<div id="cinza-grid-'.$grid_id.'-sorts" class="cinza-grid-button-group">';
			$sort_lines = preg_split("/\r\n|\n|\r/", $sorts_temp);
			
			// First button
			$sorts .= '<button class="button is-checked" data-sort-by="original-order">Original order</button>';
			
			// All other buttons
			foreach ($sort_lines as $sort_line) {
				if(!empty($sort_line)) {
					$sort_atts = explode ("/", $sort_line); 
					$sorts .= '<button class="button" data-sort-by="'. trim($sort_atts[0]) .'">'. trim($sort_atts[1]) .'</button>';
					$sorts_data .= '\'' . trim($sort_atts[0]) . '\': ' . '\'.' . trim($sort_atts[0]) . '\', ';					
				}
			}
		$sorts .= '</div>';
	}
	
    // Filter 
	$filters = '';
	$filters_temp = empty($cgrid_options['cgrid_filters']) ? '' : $cgrid_options['cgrid_filters'];
	
    if(!empty($filters_temp)) {
		$filters = '<div id="cinza-grid-'.$grid_id.'-filters">';
			$filter_lines = preg_split("/\r\n|\n|\r/", $filters_temp);
			
			foreach ($filter_lines as $filter_line) {
				if(!empty($filter_line)) {
					$filter_atts = explode ("/", $filter_line); 
					$filters .= '<div class="cinza-grid-button-group" data-filter-group="'. trim(strtolower($filter_atts[1])) .'">';
						
						// First button
						$filters .= '<button class="button is-checked" data-filter="*">All '. trim($filter_atts[1]) .'</button>';
						
						// All other buttons
						$filter_buttons = explode (",", $filter_atts[2]); 
						foreach ($filter_buttons as $filter_button) {
							$filters .= '<button class="button" data-filter=".'. str_replace(' ', '-', trim(strtolower($filter_button))) .'">'. trim($filter_button) .'</button>';	
						}
					$filters .= '</div>';					
				}
			}
	    $filters .= '</div>';	    
    }
	
	$script = "<script>
	jQuery(document).ready(function($) {
		
	    var grid = $('#cinza-grid-".$grid_id."').isotope({
	        itemSelector: '.cinza-grid-item',
	        layoutMode: 'fitRows',
	        transitionDuration: '0.4s',
	        getSortData: {".$sorts_data."}
	    });
	    
	    if( '".$sorts."' != '' ) {
	    	// bind sort button click
		    $('#cinza-grid-".$grid_id."-sorts').on( 'click', 'button', function() {
		        var sortByValue = $(this).attr('data-sort-by');
		        grid.isotope({ sortBy: sortByValue });
		    });
		    
		    // change is-checked class on buttons
		    $('#cinza-grid-".$grid_id."-sorts').each( function( i, buttonGroup ) {
		        var buttonGroup = $( buttonGroup );
		        buttonGroup.on( 'click', 'button', function() {
		        buttonGroup.find('.is-checked').removeClass('is-checked');
		        $( this ).addClass('is-checked');
		        });
		    });			    
		}
		
	    if( '".$filters."' != '' ) {
			// store filter for each group
			var filters = {};
			
			$('#cinza-grid-".$grid_id."-filters').on( 'click', '.button', function( event ) {
				var button = $( event.currentTarget );
				
				// get group key
				var buttonGroup = button.parents('.cinza-grid-button-group');
				var filterGroup = buttonGroup.attr('data-filter-group');
				
				// set filter for group
				filters[ filterGroup ] = button.attr('data-filter');
				
				// combine filters
				var filterValue = concatValues( filters );
				
				// set filter for Isotope
				grid.isotope({ filter: filterValue });
			});
			
			// change is-checked class on buttons
			$('.cinza-grid-button-group').each( function( i, buttonGroup ) {
				var buttonGroup = $( buttonGroup );
				buttonGroup.on( 'click', 'button', function( event ) {
					buttonGroup.find('.is-checked').removeClass('is-checked');
					var button = $( event.currentTarget );
					button.addClass('is-checked');
				});
			});
			  
			// flatten object by concatting values
			function concatValues( obj ) {
				var value = '';
				for ( var prop in obj ) value += obj[ prop ];
				return value;
			}
		}	    
	});
	</script>";

    // Grid items
    $grid = '<div id="cinza-grid-'.$grid_id.'" class="cinza-grid">';    
	if( !empty( $posts ) ){
		$debug = "";

		foreach ( $posts as $post ) {			
			$grid_item = $cgrid_skin['cgrid_skin_content'];
			$filter_classes = "";
						
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
			while(strpos($grid_item, '%meta(') !== false){
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
			
			$filter_classes .= filter_meta_replace($post, $filters_temp);
			
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
			
			$filter_classes .= filter_tax_replace($post, $filters_temp);
			
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
				
				$taxsep_code_args = substr($grid_item, $taxsep_open_paranthesis+4, $taxsep_close_paranthesis-$taxsep_open_paranthesis-5);
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
		    	'%img%',
		    	'%imgurl%',
		    	'%content%',
		    );
		    
		    $code2 = array(
		    	get_the_title($post->ID), 
				get_permalink($post->ID), 
		    	get_the_date('F j, Y', $post->ID),
		    	get_the_post_thumbnail($post->ID),
		    	get_the_post_thumbnail_url($post->ID),
		    	get_post_field('post_content', $post->ID),
		    );
		    
			$grid .= '<div class="cinza-grid-item cinza-grid-'. $post->ID . $filter_classes.'">'. str_replace($code1, $code2, $grid_item) .'</div>';
		}
	}
    $grid .= '</div>';
    
    // Style
    $style = "<style>
    	";
    
	    $style .= "/* ----- Breakpoint 1 ----- */
	    @media only screen and (max-width: ". esc_attr($cgrid_options['cgrid_breakpoint_2']-1) ."px) {
			.cinza-grid {
				width: calc(100% + ". esc_attr($cgrid_options['cgrid_spacing_2']) ."px); 
				margin: calc(-". esc_attr($cgrid_options['cgrid_spacing_2']) ."px / 2);
			}
			.cinza-grid-item {
				width: calc(100% / ". esc_attr($cgrid_options['cgrid_columns_1']) ." - ". esc_attr($cgrid_options['cgrid_spacing_1']) ."px - 1px); /* -1px to be safe */
				min-height: ". esc_attr($cgrid_options['cgrid_height_1']) ."px;
				margin: calc(". esc_attr($cgrid_options['cgrid_spacing_1']) ."px / 2);
			}";
			
		    if (esc_attr($cgrid_options['cgrid_columns_1']) == 1) {
			    $style .= "
				.cinza-grid {width: 100%; margin: 0px;}
				.cinza-grid-item {width: 100%; margin: 0px 0px ". esc_attr($cgrid_options['cgrid_spacing_1']) ."px 0px;}
				.cinza-grid-item:last-child {margin-bottom: 0px;}";		    
		    }
	    $style .= "
	    }
	    ";

		$style .= "/* ----- Breakpoint 2 ----- */
	    @media only screen and (min-width: ". esc_attr($cgrid_options['cgrid_breakpoint_2']) ."px) and (max-width: ". esc_attr($cgrid_options['cgrid_breakpoint_3']-1) ."px) {
			.cinza-grid {
				width: calc(100% + ". esc_attr($cgrid_options['cgrid_spacing_2']) ."px); 
				margin: calc(-". esc_attr($cgrid_options['cgrid_spacing_2']) ."px / 2);
			}
			.cinza-grid-item {
				width: calc(100% / ". esc_attr($cgrid_options['cgrid_columns_2']) ." - ". esc_attr($cgrid_options['cgrid_spacing_2']) ."px - 1px); /* -1px to be safe */
				min-height: ". esc_attr($cgrid_options['cgrid_height_2']) ."px;
				margin: calc(". esc_attr($cgrid_options['cgrid_spacing_2']) ."px / 2);
			}";
			
		    if (esc_attr($cgrid_options['cgrid_columns_2']) == 1) {
			    $style .= "
				.cinza-grid {width: 100%; margin: 0px;}
				.cinza-grid-item {width: 100%; margin: 0px 0px ". esc_attr($cgrid_options['cgrid_spacing_2']) ."px 0px;}
				.cinza-grid-item:last-child {margin-bottom: 0px;}";		    
		    }
	    $style .= "
	    }
	    ";
	    
    	$style .= "/* ----- Breakpoint 3 ----- */
	    @media only screen and (min-width: ". esc_attr($cgrid_options['cgrid_breakpoint_3']) ."px) and (max-width: ". esc_attr($cgrid_options['cgrid_breakpoint_4']-1) ."px) {
			.cinza-grid {
				width: calc(100% + ". esc_attr($cgrid_options['cgrid_spacing_3']) ."px); 
				margin: calc(-". esc_attr($cgrid_options['cgrid_spacing_3']) ."px / 2);
			}
			.cinza-grid-item {
				width: calc(100% / ". esc_attr($cgrid_options['cgrid_columns_3']) ." - ". esc_attr($cgrid_options['cgrid_spacing_3']) ."px - 1px); /* -1px to be safe */
				min-height: ". esc_attr($cgrid_options['cgrid_height_3']) ."px;
				margin: calc(". esc_attr($cgrid_options['cgrid_spacing_3']) ."px / 2);
			}";
			
		    if (esc_attr($cgrid_options['cgrid_columns_3']) == 1) {
			    $style .= "
				.cinza-grid {width: 100%; margin: 0px;}
				.cinza-grid-item {width: 100%; margin: 0px 0px ". esc_attr($cgrid_options['cgrid_spacing_3']) ."px 0px;}
				.cinza-grid-item:last-child {margin-bottom: 0px;}";		    
		    }
	    $style .= "
	    }
	    ";
	    
	    $style .= "/* ----- Breakpoint 4 ----- */
	    @media only screen and (min-width: ". esc_attr($cgrid_options['cgrid_breakpoint_4']) ."px) and (max-width: ". esc_attr($cgrid_options['cgrid_breakpoint_5']-1) ."px) {
			.cinza-grid {
				width: calc(100% + ". esc_attr($cgrid_options['cgrid_spacing_4']) ."px); 
				margin: calc(-". esc_attr($cgrid_options['cgrid_spacing_4']) ."px / 2);
			}
			.cinza-grid-item {
				width: calc(100% / ". esc_attr($cgrid_options['cgrid_columns_4']) ." - ". esc_attr($cgrid_options['cgrid_spacing_4']) ."px - 1px); /* -1px to be safe */
				min-height: ". esc_attr($cgrid_options['cgrid_height_4']) ."px;
				margin: calc(". esc_attr($cgrid_options['cgrid_spacing_4']) ."px / 2);
			}";
			
		    if (esc_attr($cgrid_options['cgrid_columns_4']) == 1) {
			    $style .= "
				.cinza-grid {width: 100%; margin: 0px;}
				.cinza-grid-item {width: 100%; margin: 0px 0px ". esc_attr($cgrid_options['cgrid_spacing_4']) ."px 0px;}
				.cinza-grid-item:last-child {margin-bottom: 0px;}";		    
		    }
	    $style .= "
	    }
	    ";
	    
	    $style .= "/* ----- Breakpoint 5 ----- */
	    @media only screen and (min-width: ". esc_attr($cgrid_options['cgrid_breakpoint_5']) ."px) {
			.cinza-grid {
				width: calc(100% + ". esc_attr($cgrid_options['cgrid_spacing_5']) ."px); 
				margin: calc(-". esc_attr($cgrid_options['cgrid_spacing_5']) ."px / 2);
			}
			.cinza-grid-item {
				width: calc(100% / ". esc_attr($cgrid_options['cgrid_columns_5']) ." - ". esc_attr($cgrid_options['cgrid_spacing_5']) ."px - 1px); /* -1px to be safe */
				min-height: ". esc_attr($cgrid_options['cgrid_height_5']) ."px;
				margin: calc(". esc_attr($cgrid_options['cgrid_spacing_5']) ."px / 2);
			}";
			
		    if (esc_attr($cgrid_options['cgrid_columns_5']) == 1) {
			    $style .= "
				.cinza-grid {width: 100%; margin: 0px;}
				.cinza-grid-item {width: 100%; margin: 0px 0px ". esc_attr($cgrid_options['cgrid_spacing_5']) ."px 0px;}
				.cinza-grid-item:last-child {margin-bottom: 0px;}";		    
		    }
	    $style .= "
	    }
	    ";
	    
    $style .= "</style>";
    
    return $debug . $sorts . $filters . $grid . $style . $script;
}

function filter_meta_replace($post, $filters_temp) {
	if(strpos($filters_temp, '%meta(') !== false) {
		$meta_start_position = strpos($filters_temp, "%meta(");
		$meta_open_paranthesis = $meta_start_position + 5;
		$meta_close_paranthesis = $meta_start_position + strpos(substr($filters_temp, $meta_start_position, $meta_start_position+50), ")");
		$meta_code = substr($filters_temp, $meta_start_position+1, $meta_close_paranthesis-$meta_start_position);
		$meta_code_args = substr($filters_temp, $meta_open_paranthesis+2, $meta_close_paranthesis-$meta_open_paranthesis-3);
		$meta_formatted = get_post_meta( $post->ID, $meta_code_args, true );
		return " ".str_replace(' ', '-', strtolower($meta_formatted));
	}	
}

function filter_tax_replace($post, $filters_temp) {
	if(strpos($filters_temp, '%tax(') !== false) {
		$tax_start_position = strpos($filters_temp, "%tax(");
		$tax_open_paranthesis = $tax_start_position + 5;
		$tax_close_paranthesis = $tax_start_position + strpos(substr($filters_temp, $tax_start_position, $tax_start_position+50), ")");
		$tax_code = substr($filters_temp, $tax_start_position+1, $tax_close_paranthesis-$tax_start_position);
		$tax_code_args = substr($filters_temp, $tax_open_paranthesis+1, $tax_close_paranthesis-$tax_open_paranthesis-2);
		$term_list = get_the_terms( $post->ID, $tax_code_args );
		if( $term_list && ! is_wp_error( $term_list ) ) {
			$terms_array = array();				
			foreach ( $term_list as $term ) {
				$terms_array[] = str_replace(' ', '-', esc_attr($term->name) );
			}
			$tax_formatted = join( ' ', $terms_array );
			return " ".strtolower($tax_formatted);
		}
	}
}