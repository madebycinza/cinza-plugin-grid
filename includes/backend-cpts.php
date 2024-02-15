<?php
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Register CPT: cgrid
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'init', 'cgrid_register_post_type' );
function cgrid_register_post_type() {
	$labels = [
		'name'                     => esc_html__( 'Cinza Grids', 'your-textdomain' ),
		'singular_name'            => esc_html__( 'Grid', 'your-textdomain' ),
		'add_new'                  => esc_html__( 'Add New', 'your-textdomain' ),
		'add_new_item'             => esc_html__( 'Add new Grid', 'your-textdomain' ),
		'edit_item'                => esc_html__( 'Edit Grid', 'your-textdomain' ),
		'new_item'                 => esc_html__( 'New Grid', 'your-textdomain' ),
		'view_item'                => esc_html__( 'View Grid', 'your-textdomain' ),
		'view_items'               => esc_html__( 'View Grids', 'your-textdomain' ),
		'search_items'             => esc_html__( 'Search Grids', 'your-textdomain' ),
		'not_found'                => esc_html__( 'No Grids found', 'your-textdomain' ),
		'not_found_in_trash'       => esc_html__( 'No Grids found in Trash', 'your-textdomain' ),
		'parent_item_colon'        => esc_html__( 'Parent Grid:', 'your-textdomain' ),
		'all_items'                => esc_html__( 'All Grids', 'your-textdomain' ),
		'archives'                 => esc_html__( 'Grid Archives', 'your-textdomain' ),
		'attributes'               => esc_html__( 'Grid Attributes', 'your-textdomain' ),
		'insert_into_item'         => esc_html__( 'Insert into Grid', 'your-textdomain' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this Grid', 'your-textdomain' ),
		'featured_image'           => esc_html__( 'Featured image', 'your-textdomain' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'your-textdomain' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'your-textdomain' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'your-textdomain' ),
		'menu_name'                => esc_html__( 'Cinza Grid', 'your-textdomain' ),
		'filter_items_list'        => esc_html__( 'Filter Grids list', 'your-textdomain' ),
		'filter_by_date'           => esc_html__( '', 'your-textdomain' ),
		'items_list_navigation'    => esc_html__( 'Grids list navigation', 'your-textdomain' ),
		'items_list'               => esc_html__( 'Grids list', 'your-textdomain' ),
		'item_published'           => esc_html__( 'Grid published', 'your-textdomain' ),
		'item_published_privately' => esc_html__( 'Grid published privately', 'your-textdomain' ),
		'item_reverted_to_draft'   => esc_html__( 'Grid reverted to draft', 'your-textdomain' ),
		'item_scheduled'           => esc_html__( 'Grid scheduled', 'your-textdomain' ),
		'item_updated'             => esc_html__( 'Grid updated', 'your-textdomain' ),
		'text_domain'              => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	
	$args = [
	    'label'               => esc_html__( 'Grids', 'your-textdomain' ),
	    'labels'              => $labels,
	    'description'         => '',
	    'public'              => true,
	    'hierarchical'        => false,
	    'exclude_from_search' => true,
	    'publicly_queryable'  => false,
	    'show_ui'             => true,
	    'show_in_nav_menus'   => false,
	    'show_in_admin_bar'   => false,
	    'show_in_rest'        => true,
	    'query_var'           => false,
	    'can_export'          => true,
	    'delete_with_user'    => false,
	    'has_archive'         => false,
	    'rest_base'           => '',
	    'show_in_menu'        => true,
	    'menu_icon'           => 'dashicons-admin-generic',
	    'menu_position'       => '',
	    'capability_type'     => 'post',
	    'supports'            => ['title', 'revisions', 'custom-fields'],
	    'taxonomies'          => [],
	    'rewrite'             => ['with_front' => false],
	];

	register_post_type( 'cinza_grid', $args );
}

add_filter( 'set_custom_edit_cinza_grid_columns', 'set_custom_edit_cgrid_columns' );
function set_custom_edit_cgrid_columns($columns) {
    $columns['shortcode'] = __( 'Shortcode', 'your_text_domain' );
    return $columns;
}

add_action( 'manage_cinza_grid_posts_custom_column' , 'custom_cgrid_column', 10, 2 );
function custom_cgrid_column( $column, $post_id ) {
	switch ( $column ) {
		case 'shortcode' :
			cgrid_meta_box_shortcode($post_id);
			break;
	}
}

add_filter ( 'manage_cinza_grid_posts_columns', 'add_cgrid_columns', 99, 99 );
function add_cgrid_columns ( $columns ) {
	unset($columns['title']);
	unset($columns['shortcode']);
	unset($columns['date']);
	unset($columns['rank_math_seo_details']);
	unset($columns['rank_math_title']);
	unset($columns['rank_math_description']);

	return array_merge ( $columns, array ( 
		'title' => __ ('Title'),
		'shortcode' => __ ( 'Shortcode' ),
		'date' => __('Date')
	) );
}

add_filter( 'the_content', 'cgrid_post_content');
function cgrid_post_content ( $content ) {
    if ( is_singular('cinza_grid') ) {
        return do_shortcode('[cinzagrid id="'. get_the_ID() .'"]');
    }
    return $content;
}

// Remove UI for Custom Fields metabox
add_action( 'admin_head' , 'cgrid_remove_post_custom_fields' );
function cgrid_remove_post_custom_fields() {
    remove_meta_box( 'postcustom' , 'cinza_grid' , 'normal' ); 
}

// Remove CPT from SEO sitemap and set robots to noindex nofollow (for Rank Math SEO plugin)
if ( in_array( 'seo-by-rank-math/rank-math.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// https://rankmath.com/kb/make-theme-rank-math-compatible/#exclude-post-type-from-sitemap
	add_filter( 'rank_math/sitemap/exclude_post_type', function ($exclude, $type) {
	    if ($type === 'cinza_grid') {
	        $exclude = true;
	    }
	    return $exclude;
	}, 10, 2);	

	// https://support.rankmath.com/ticket/cpt-noindex/
	add_filter( 'rank_math/frontend/robots', function( $robots ) {
		if(get_post_type() == 'cinza_grid' ) {
			$robots['index'] = 'noindex';
			$robots['follow'] = 'nofollow';
		}
		return $robots;
	});
}

// [Possible future addition] Remove CPT from SEO sitemap (for Yoast SEO plugin)
// https://developer.yoast.com/features/xml-sitemaps/api/#exclude-specific-posts
// https://wordpress.org/support/topic/exclude-multiple-post-types-from-sitemap/

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add Meta Boxes
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'add_meta_boxes', 'cgrid_add_fields_meta_boxes', 99, 99 );
function cgrid_add_fields_meta_boxes() {
	add_meta_box('cgrid-options', 'Options', 'cgrid_meta_box_options', 'cinza_grid', 'normal', 'default');
	add_meta_box('cgrid-skin', 'Skin', 'cgrid_meta_box_skin', 'cinza_grid', 'normal', 'default');
	add_meta_box('cgrid-shortcode', 'Shortcode', 'cgrid_meta_box_shortcode', 'cinza_grid', 'side', 'default');
	add_meta_box('cgrid-documentation', 'Documentation', 'cgrid_meta_box_doc', 'cinza_grid', 'side', 'default');
	add_meta_box('cgrid-credits', 'Developers', 'cgrid_meta_box_credits', 'cinza_grid', 'side', 'default');
	remove_meta_box( 'rank_math_metabox' , 'cinza_grid' , 'normal' ); 
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_options
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_options( $post ) {
	global $post;
    $cgrid_options = get_post_meta( $post->ID, '_cgrid_options', true );
	wp_nonce_field( 'cgrid_meta_box_nonce', 'cgrid_meta_box_nonce' );
	
	// Set default values
	$temp_posttype = isset($cgrid_options['cgrid_posttype']) ? esc_attr($cgrid_options['cgrid_posttype']) : 'post';
	$temp_orderby = isset($cgrid_options['cgrid_orderby']) ? esc_attr($cgrid_options['cgrid_orderby']) : 'date';
	$temp_orderby_meta = isset($cgrid_options['cgrid_orderby_meta']) ? esc_attr($cgrid_options['cgrid_orderby_meta']) : '';
	$temp_order = isset($cgrid_options['cgrid_order']) ? esc_attr($cgrid_options['cgrid_order']) : 'ASC';
	$temp_num = isset($cgrid_options['cgrid_num']) ? esc_attr($cgrid_options['cgrid_num']) : '-1';
	$temp_tax = isset($cgrid_options['cgrid_tax']) ? esc_attr($cgrid_options['cgrid_tax']) : '';
	$temp_tax_terms = isset($cgrid_options['cgrid_tax_terms']) ? esc_attr($cgrid_options['cgrid_tax_terms']) : '';
	$temp_sorting = isset($cgrid_options['cgrid_sorting']) ? esc_attr($cgrid_options['cgrid_sorting']) : '';
	$temp_filters = isset($cgrid_options['cgrid_filters']) ? esc_attr($cgrid_options['cgrid_filters']) : '';
	
	$temp_layout = isset($cgrid_options['cgrid_layout']) ? esc_attr($cgrid_options['cgrid_layout']) : 'fitRows';
	$temp_full_width = isset($cgrid_options['cgrid_full_width']) ? esc_attr($cgrid_options['cgrid_full_width']) : '0';
	$temp_query_string = isset($cgrid_options['cgrid_query_string']) ? esc_attr($cgrid_options['cgrid_query_string']) : '0';
	
	$temp_breakpoint_1 = 1;
	$temp_columns_1 = isset($cgrid_options['cgrid_columns_1']) ? esc_attr($cgrid_options['cgrid_columns_1']) : '1';
	$temp_height_1 = isset($cgrid_options['cgrid_height_1']) ? esc_attr($cgrid_options['cgrid_height_1']) : '0';
	$temp_spacing_1 = isset($cgrid_options['cgrid_spacing_1']) ? esc_attr($cgrid_options['cgrid_spacing_1']) : '20';
	
	$temp_breakpoint_2 = isset($cgrid_options['cgrid_breakpoint_2']) ? esc_attr($cgrid_options['cgrid_breakpoint_2']) : '500';
	$temp_columns_2 = isset($cgrid_options['cgrid_columns_2']) ? esc_attr($cgrid_options['cgrid_columns_2']) : '2';
	$temp_height_2 = isset($cgrid_options['cgrid_height_2']) ? esc_attr($cgrid_options['cgrid_height_2']) : '0';
	$temp_spacing_2 = isset($cgrid_options['cgrid_spacing_2']) ? esc_attr($cgrid_options['cgrid_spacing_2']) : '20';
	
	$temp_breakpoint_3 = isset($cgrid_options['cgrid_breakpoint_3']) ? esc_attr($cgrid_options['cgrid_breakpoint_3']) : '700';
	$temp_columns_3 = isset($cgrid_options['cgrid_columns_3']) ? esc_attr($cgrid_options['cgrid_columns_3']) : '3';
	$temp_height_3 = isset($cgrid_options['cgrid_height_3']) ? esc_attr($cgrid_options['cgrid_height_3']) : '0';
	$temp_spacing_3 = isset($cgrid_options['cgrid_spacing_3']) ? esc_attr($cgrid_options['cgrid_spacing_3']) : '20';
	
	$temp_breakpoint_4 = isset($cgrid_options['cgrid_breakpoint_4']) ? esc_attr($cgrid_options['cgrid_breakpoint_4']) : '900';
	$temp_columns_4 = isset($cgrid_options['cgrid_columns_4']) ? esc_attr($cgrid_options['cgrid_columns_4']) : '4';
	$temp_height_4 = isset($cgrid_options['cgrid_height_4']) ? esc_attr($cgrid_options['cgrid_height_4']) : '0';
	$temp_spacing_4 = isset($cgrid_options['cgrid_spacing_4']) ? esc_attr($cgrid_options['cgrid_spacing_4']) : '20';
	
	$temp_breakpoint_5 = isset($cgrid_options['cgrid_breakpoint_5']) ? esc_attr($cgrid_options['cgrid_breakpoint_5']) : '1200';
	$temp_columns_5 = isset($cgrid_options['cgrid_columns_5']) ? esc_attr($cgrid_options['cgrid_columns_5']) : '5';
	$temp_height_5 = isset($cgrid_options['cgrid_height_5']) ? esc_attr($cgrid_options['cgrid_height_5']) : '0';
	$temp_spacing_5 = isset($cgrid_options['cgrid_spacing_5']) ? esc_attr($cgrid_options['cgrid_spacing_5']) : '20';

	?>
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="2">
					<p>Source</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_posttype">Post Type</label>
				</td>
				<td class="cgrid-options col-2"><?php
					
					// Get a list of all registered post type objects
					// https://developer.wordpress.org/reference/functions/get_post_types/
					$args = array(
					   'public' => true,
					   '_builtin' => false,
					);
					  
					$output = 'names'; // 'names' or 'objects' (default: 'names')
					$operator = 'and'; // 'and' or 'or' (default: 'and')
					
					$post_types = get_post_types( $args, $output, $operator );
					array_unshift($post_types , 'post');
					  
					if ( $post_types ) { // If there are any custom public post types.
					    echo '<select name="cgrid_posttype" id="cgrid_posttype">';
					    foreach ( $post_types  as $post_type ) {
					    	if(!str_starts_with($post_type, 'cinza_')) {?>
							    <option value="<?php echo strtolower($post_type); ?>" <?php if(isset($temp_posttype) && ($temp_posttype == $post_type))  echo 'selected="selected"'; ?>>
							    	<?php echo $post_type; ?>
							    </option><?php
					    	}
					    }
					    echo '</select>'; 
					}?>
					
                </td>
            </tr>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_order">Number of items</label>
				</td>
				<td class="cgrid-options col-2">
					<input type="number" name="cgrid_num" id="cgrid_num" value="<?php echo esc_attr($temp_num); ?>" />
                </td>
            </tr>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_orderby">Order by</label>
				</td>
				<td class="cgrid-options col-2">
					<select name="cgrid_orderby" id="cgrid_orderby" onchange="sortByMetaField(this)">
						<option value="date" <?php if(isset($temp_orderby) && ($temp_orderby == 'date'))  echo 'selected="selected"'; ?>>Date</option>
						<option value="id" <?php if(isset($temp_orderby) && ($temp_orderby == 'id'))  echo 'selected="selected"'; ?>>ID</option>
						<option value="menu_order" <?php if(isset($temp_orderby) && ($temp_orderby == 'menu_order'))  echo 'selected="selected"'; ?>>Menu order</option>
						<option value="modified" <?php if(isset($temp_orderby) && ($temp_orderby == 'modified'))  echo 'selected="selected"'; ?>>Modified</option>
						<option value="title" <?php if(isset($temp_orderby) && ($temp_orderby == 'title'))  echo 'selected="selected"'; ?>>Title</option>
						<option value="meta_value" <?php if(isset($temp_orderby) && ($temp_orderby == 'meta_value'))  echo 'selected="selected"'; ?>>Meta Field</option>
						<option value="rand" <?php if(isset($temp_orderby) && ($temp_orderby == 'rand'))  echo 'selected="selected"'; ?>>Random</option>
					</select>
					<input type="text" name="cgrid_orderby_meta" id="cgrid_orderby_meta" placeholder="field_name" value="<?php echo esc_attr($temp_orderby_meta); ?>" class="meta-disabled" disabled />
                </td>
            </tr>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_order">Order</label>
				</td>
				<td class="cgrid-options col-2">
					<select name="cgrid_order" id="cgrid_order">
						<option value="ASC" <?php if(isset($temp_order) && ($temp_order == 'ASC'))  echo 'selected="selected"'; ?>>ASC</option>
						<option value="DESC" <?php if(isset($temp_order) && ($temp_order == 'DESC'))  echo 'selected="selected"'; ?>>DESC</option>
					</select>
                </td>
            </tr>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_order">Taxonomy</label>
				</td>
				<td class="cgrid-options col-2">
					<input type="text" name="cgrid_tax" id="cgrid_tax" placeholder="taxonomy_name" value="<?php echo esc_attr($temp_tax); ?>" />
                </td>
            </tr>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_order">Taxonomy terms</label>
				</td>
				<td class="cgrid-options col-2">
					<input type="text" name="cgrid_tax_terms" id="cgrid_tax_terms" placeholder="term_slug1, term_slug2, term_slug3" value="<?php echo esc_attr($temp_tax_terms); ?>" />
                </td>
            </tr>
		</tbody>
	</table>
	
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="4">
					<p>Layout</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_layout">Layout mode</label>
				</td>
				<td class="cgrid-options col-2">
					<select name="cgrid_layout" id="cgrid_layout">
						<option value="fitRows" <?php if(isset($temp_layout) && ($temp_layout == 'fitRows'))  echo 'selected="selected"'; ?>>FitRows</option>
						<option value="masonry" <?php if(isset($temp_layout) && ($temp_layout == 'masonry'))  echo 'selected="selected"'; ?>>Masonry</option>
					</select>
                </td>
            </tr>
			<tr>
				<td class="cgrid-options col-1">
					Full width
				</td>
				<td class="cgrid-options col-2">
					<input type="checkbox" name="cgrid_full_width" id="cgrid_full_width" class="widefat cgrid-full_width" value="1" <?php checked('1', $temp_full_width); ?> /> <label for="cgrid_full_width">Enabling this option will force full width with CSS.</label>
				</td>
			</tr>
			<tr class="tr-separator">
				<td colspan="4"></td>
			</tr>
			<tr>
				<td colspan="2">
					<table id="cgrid-optionset" class="cgrid-sizeset">
						<tbody>
							<tr class="size-headings">
								<td class="cgrid-options col-1"></td>
								<td class="cgrid-options col-2">Min-width</td>
								<td class="cgrid-options col-3">Number of columns</td>
								<td class="cgrid-options col-4">Items min-height</td>
								<td class="cgrid-options col-5">Items spacing</td>
				            </tr>
							<tr>
								<td class="cgrid-options col-1">Breakpoint 1</td>
								<td class="cgrid-options col-2"><input type="number" name="cgrid_breakpoint_1" id="cgrid_breakpoint_1" value="1" readonly /> <span>px</span></td>
								<td class="cgrid-options col-3"><input type="number" name="cgrid_columns_1" id="cgrid_columns_1" value="<?php echo esc_attr($temp_columns_1); ?>" /></td>
								<td class="cgrid-options col-4"><input type="number" name="cgrid_height_1" id="cgrid_height_1" value="<?php echo esc_attr($temp_height_1); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-5"><input type="number" name="cgrid_spacing_1" id="cgrid_spacing_1" value="<?php echo esc_attr($temp_spacing_1); ?>" /> <span>px</span></td>
				            </tr>
							<tr>
								<td class="cgrid-options col-1">Breakpoint 2</td>
								<td class="cgrid-options col-2"><input type="number" name="cgrid_breakpoint_2" id="cgrid_breakpoint_2" value="<?php echo esc_attr($temp_breakpoint_2); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-3"><input type="number" name="cgrid_columns_2" id="cgrid_columns_2" value="<?php echo esc_attr($temp_columns_2); ?>" /></td>
								<td class="cgrid-options col-4"><input type="number" name="cgrid_height_2" id="cgrid_height_2" value="<?php echo esc_attr($temp_height_2); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-5"><input type="number" name="cgrid_spacing_2" id="cgrid_spacing_2" value="<?php echo esc_attr($temp_spacing_2); ?>" /> <span>px</span></td>
				            </tr>
							<tr>
								<td class="cgrid-options col-1">Breakpoint 3</td>
								<td class="cgrid-options col-2"><input type="number" name="cgrid_breakpoint_3" id="cgrid_breakpoint_3" value="<?php echo esc_attr($temp_breakpoint_3); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-3"><input type="number" name="cgrid_columns_3" id="cgrid_columns_3" value="<?php echo esc_attr($temp_columns_3); ?>" /></td>
								<td class="cgrid-options col-4"><input type="number" name="cgrid_height_3" id="cgrid_height_3" value="<?php echo esc_attr($temp_height_3); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-5"><input type="number" name="cgrid_spacing_3" id="cgrid_spacing_3" value="<?php echo esc_attr($temp_spacing_3); ?>" /> <span>px</span></td>
				            </tr>
							<tr>
								<td class="cgrid-options col-1">Breakpoint 4</td>
								<td class="cgrid-options col-2"><input type="number" name="cgrid_breakpoint_4" id="cgrid_breakpoint_4" value="<?php echo esc_attr($temp_breakpoint_4); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-3"><input type="number" name="cgrid_columns_4" id="cgrid_columns_4" value="<?php echo esc_attr($temp_columns_4); ?>" /></td>
								<td class="cgrid-options col-4"><input type="number" name="cgrid_height_4" id="cgrid_height_4" value="<?php echo esc_attr($temp_height_4); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-5"><input type="number" name="cgrid_spacing_4" id="cgrid_spacing_4" value="<?php echo esc_attr($temp_spacing_4); ?>" /> <span>px</span></td>
				            </tr>
							<tr>
								<td class="cgrid-options col-1">Breakpoint 5</td>
								<td class="cgrid-options col-2"><input type="number" name="cgrid_breakpoint_5" id="cgrid_breakpoint_5" value="<?php echo esc_attr($temp_breakpoint_5); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-3"><input type="number" name="cgrid_columns_5" id="cgrid_columns_5" value="<?php echo esc_attr($temp_columns_5); ?>" /></td>
								<td class="cgrid-options col-4"><input type="number" name="cgrid_height_5" id="cgrid_height_5" value="<?php echo esc_attr($temp_height_5); ?>" /> <span>px</span></td>
								<td class="cgrid-options col-5"><input type="number" name="cgrid_spacing_5" id="cgrid_spacing_5" value="<?php echo esc_attr($temp_spacing_5); ?>" /> <span>px</span></td>
				            </tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr class="tr-separator">
				<td colspan="4"></td>
			</tr>
			<tr>
				<td class="cgrid-options" colspan="4">
					<p><strong>Notes:</strong></p>
					<ul>
						<li>Breakpoints must be in ascending order.</li>
						<li>Disable <em>Items min-height</em> by setting the value to zero.</li>
					</ul>
				</td>
            </tr>
		</tbody>
	</table>
		
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="2">
					<p>Sorting</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="cgrid-options col-1" colspan="2">
					<p><strong>Enter the CSS class of each element of which the content will be used for sorting.</strong></p>
					<p>Format: <code>class / label</code> (one per line)</p>
					<textarea type="text" class="widefat cgrid-content" name="cgrid_sorting"><?php echo esc_html($temp_sorting); ?></textarea>
				</td>
            </tr>
			<tr class="tr-separator">
				<td colspan="4"></td>
			</tr>
			<tr>
				<td class="cgrid-options" colspan="4">
					<p><strong>Notes:</strong></p>
					<ul>
						<li>To sort by the 'color' meta field when you have the following element skin: <code>&lt;div class=&quot;element-color&quot;&gt;Red&lt;/div&gt;</code></li>
						<li>You should enter the following in the Sorting textarea: <code>element-color / Color</code></li>
					</ul>
				</td>
            </tr>
		</tbody>
	</table>
	
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="2">
					<p>Filters</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="cgrid-options col-1" colspan="2">
					<p><strong>Enter the code and buttons of each element that will be used for filtering.</strong></p>
					<p>Format: <code>meta / "All" button / buttons separated by comma</code> (one per line)</p>
					<textarea type="text" class="widefat cgrid-content" name="cgrid_filters"><?php echo esc_html($temp_filters); ?></textarea>
				</td>
            </tr>
			<tr>
				<td class="cgrid-options col-1">
					Query string
				</td>
				<td class="cgrid-options col-2">
					<input type="checkbox" name="cgrid_query_string" id="cgrid_query_string" class="widefat cgrid-query-string" value="1" <?php checked('1', $temp_query_string); ?> /> <label for="cgrid_query_string">Enable query string update in real time when clicking on filter buttons.</label>
				</td>
			</tr>
			<tr class="tr-separator">
				<td colspan="4"></td>
			</tr>
			<tr>
				<td class="cgrid-options cgrid-notes" colspan="4">
					<p><strong>Notes:</strong></p>
					<ul>
						<li>Filters only work with <code>%meta('field_name')%</code> and <code>%tax('taxonomy_name')%</code>.</li>
						<li>To filter by the 'color' meta field, with the default button called "All Colors" and filters for the colors Blue, Red and Yellow, you should enter the following in the Filter textarea:</li>
						<li><code>%meta('color')% / All Colors / Blue, Red, Yellow</code></li>						
					</ul>
				</td>
            </tr>
		</tbody>
	</table>
	
	<script>
		sortByMetaField();
		function sortByMetaField(sender) {
			let val = document.getElementById("cgrid_orderby").value;
			if (val == "meta_value") {
				jQuery("#cgrid_orderby_meta").removeClass("meta-disabled");	
				jQuery("#cgrid_orderby_meta").prop( "disabled", false );
			} else {
				jQuery("#cgrid_orderby_meta").addClass("meta-disabled");
				jQuery("#cgrid_orderby_meta").prop( "disabled", true );
			}
		}
	</script>
    <?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_skin
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_skin() {
	global $post;
    $cgrid_skin = get_post_meta( $post->ID, '_cgrid_skin', true );
	wp_nonce_field( 'cgrid_meta_box_nonce', 'cgrid_meta_box_nonce' );

	// Set default values
	$temp_skin_content = '';
	
	// Get saved values
	if ( !empty($cgrid_skin) ) {
		$temp_skin_content = esc_attr($cgrid_skin['cgrid_skin_content']);
	}

	?>
	<table id="cgrid-fieldset" width="100%">
		<tbody>
			<tr class="grid-skin">
				<td class="cgrid-content">
					<label>Enter HTML and PHP code template for grid items</label>
					<textarea type="text" class="widefat cgrid-content" name="cgrid_skin_content"><?php echo esc_html($temp_skin_content); ?></textarea>
					
					<p><strong>Supported meta tags:</strong></p>
					<table class="cgrid-skin-tags">
						<tr>
							<td><code>%title%</code></td>
							<td><em>Returns post title</em></td>
						</tr>
						<tr>
							<td><code>%url%</code></td>
							<td><em>Returns post URL</em></td>
						</tr>
						<tr>
							<td><code>%img%</code></td>
							<td><em>Returns post featured image in full size</em></td>
						</tr>
						<tr>
							<td><code>%imgurl%</code></td>
							<td><em>Returns post featured image URL in full size</em></td>
						</tr>
						<tr>
							<td><code>%img('size')%</code></td>
							<td><em>Returns post featured image in the specified size</em></td>
						</tr>
						<tr>
							<td><code>%imgurl('size')%</code></td>
							<td><em>Returns post featured image URL in the specified size</em></td>
						</tr>
						<tr>
							<td><code>%content%</code></td>
							<td><em>Returns post content</em></td>
						</tr>
						<tr>
							<td><code>%excerpt%</code></td>
							<td><em>Returns post excerpt</em></td>
						</tr>
						<tr>
							<td><code>%date%</code></td>
							<td><em>Returns date in the default format</em></td>
						</tr>
						<tr>
							<td><code>%date('l F j, Y')%</code></td>
							<td><em>Returns the date in the specified format</em></td>
						</tr>
						<tr>
							<td><code>%tax('taxonomy_name')%</code></td>
							<td><em>Returns terms of taxonomy (no separator, no link)</em></td>
						</tr>
						<tr>
							<td><code>%taxsep('taxonomy_name')%</code></td>
							<td><em>Returns terms of taxonomy (with separator, no link)</em></td>
						</tr>
						<tr>
							<td><code>%taxurl('taxonomy_name')%</code></td>
							<td><em>Returns terms of taxonomy (with separator, with link)</em></td>
						</tr>
						<tr>
							<td><code>%meta('field_name')%</code></td>
							<td><em>Returns meta data</em></td>
						</tr>
					</table>
					
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_shortcode
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_shortcode( $post ) {
    $grid_SC = '[cinzagrid id=&quot;'. get_the_ID() .'&quot;]';
    $grid_ID = 'cinza-grid-' . get_the_ID();
    
    ?>
    <div class="cgrid_shortcode_copy">
        <input type="text" value="<?php echo $grid_SC; ?>" class="cgrid_shortcode_copy_input" id="<?php echo $grid_ID; ?>" readonly />
        <a class="preview button button-primary" onclick="cgrid_copy_shortcode('<?php echo $grid_ID; ?>')"><span class="icon icon-edit-copy"></span> Copy</a>
    </div>
    <?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_doc
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_doc( $post ) {
	?><a href="https://isotope.metafizzy.co/options.html" target="_blank" class="preview button">Metafizzy Isotope doc</a><?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_doc
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_credits( $post ) {
	$metafizzy_logo = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/metafizzy-icon.png';
	$cinza_logo = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/cinza-icon-pink.png';
	$razorfrog_logo = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/razorfrog-icon-turquoise.png';
	
	?><a href="https://metafizzy.co/" class="button" target="_blank">
		<img src="<?php echo $metafizzy_logo; ?>" />
		<span>Metafizzy</span>
	</a>
	<a href="https://cinza.io/" class="button" target="_blank">
		<img src="<?php echo $cinza_logo; ?>" />
		<span>Cinza</span>
	</a>
	<a href="https://razorfrog.com/" class="button" target="_blank">
		<img src="<?php echo $razorfrog_logo; ?>" />
		<span>Razorfrog</span>
	</a><?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Save Meta Boxes
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('save_post', 'cgrid_save_fields_meta_boxes');
function cgrid_save_fields_meta_boxes($post_id) {
	if ( ! isset( $_POST['cgrid_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cgrid_meta_box_nonce'], 'cgrid_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;

	// Get all _cgrid_options from fields
	$cgrid_posttype 		= isset($_POST['cgrid_posttype']) ? sanitize_text_field($_POST['cgrid_posttype']) : '';
	$cgrid_orderby 			= isset($_POST['cgrid_orderby']) ? sanitize_text_field($_POST['cgrid_orderby']) : '';
	$cgrid_orderby_meta 	= isset($_POST['cgrid_orderby_meta']) ? wp_filter_post_kses($_POST['cgrid_orderby_meta']) : '';
	$cgrid_order 			= isset($_POST['cgrid_order']) ? sanitize_text_field($_POST['cgrid_order']) : '';
	$cgrid_num 				= isset($_POST['cgrid_num']) ? wp_filter_post_kses($_POST['cgrid_num']) : '';
	$cgrid_tax 				= isset($_POST['cgrid_tax']) ? wp_filter_post_kses($_POST['cgrid_tax']) : '';
	$cgrid_tax_terms 		= isset($_POST['cgrid_tax_terms']) ? wp_filter_post_kses($_POST['cgrid_tax_terms']) : '';
	$cgrid_sorting 			= isset($_POST['cgrid_sorting']) ? wp_filter_post_kses($_POST['cgrid_sorting']) : '';
	$cgrid_filters 			= isset($_POST['cgrid_filters']) ? wp_filter_post_kses($_POST['cgrid_filters']) : '';
	
	$cgrid_layout	 		= isset($_POST['cgrid_layout']) ? sanitize_key($_POST['cgrid_layout']) : '';
	$cgrid_full_width 		= isset($_POST['cgrid_full_width']) ? sanitize_key($_POST['cgrid_full_width']) : '';
	$cgrid_query_string 	= isset($_POST['cgrid_query_string']) ? sanitize_key($_POST['cgrid_query_string']) : '';
	
	$cgrid_columns_1 		= isset($_POST['cgrid_columns_1']) ? sanitize_text_field($_POST['cgrid_columns_1']) : '';
	$cgrid_height_1 		= isset($_POST['cgrid_height_1']) ? sanitize_text_field($_POST['cgrid_height_1']) : '';
	$cgrid_spacing_1 		= isset($_POST['cgrid_spacing_1']) ? sanitize_text_field($_POST['cgrid_spacing_1']) : '';
	
	$cgrid_breakpoint_2 	= isset($_POST['cgrid_breakpoint_2']) ? sanitize_text_field($_POST['cgrid_breakpoint_2']) : '';
	$cgrid_columns_2 		= isset($_POST['cgrid_columns_2']) ? sanitize_text_field($_POST['cgrid_columns_2']) : '';
	$cgrid_height_2 		= isset($_POST['cgrid_height_2']) ? sanitize_text_field($_POST['cgrid_height_2']) : '';
	$cgrid_spacing_2 		= isset($_POST['cgrid_spacing_2']) ? sanitize_text_field($_POST['cgrid_spacing_2']) : '';
	
	$cgrid_breakpoint_3 	= isset($_POST['cgrid_breakpoint_3']) ? sanitize_text_field($_POST['cgrid_breakpoint_3']) : '';
	$cgrid_columns_3 		= isset($_POST['cgrid_columns_3']) ? sanitize_text_field($_POST['cgrid_columns_3']) : '';
	$cgrid_height_3 		= isset($_POST['cgrid_height_3']) ? sanitize_text_field($_POST['cgrid_height_3']) : '';
	$cgrid_spacing_3 		= isset($_POST['cgrid_spacing_3']) ? sanitize_text_field($_POST['cgrid_spacing_3']) : '';
	
	$cgrid_breakpoint_4 	= isset($_POST['cgrid_breakpoint_4']) ? sanitize_text_field($_POST['cgrid_breakpoint_4']) : '';
	$cgrid_columns_4 		= isset($_POST['cgrid_columns_4']) ? sanitize_text_field($_POST['cgrid_columns_4']) : '';
	$cgrid_height_4 		= isset($_POST['cgrid_height_4']) ? sanitize_text_field($_POST['cgrid_height_4']) : '';
	$cgrid_spacing_4 		= isset($_POST['cgrid_spacing_4']) ? sanitize_text_field($_POST['cgrid_spacing_4']) : '';
	
	$cgrid_breakpoint_5 	= isset($_POST['cgrid_breakpoint_5']) ? sanitize_text_field($_POST['cgrid_breakpoint_5']) : '';
	$cgrid_columns_5 		= isset($_POST['cgrid_columns_5']) ? sanitize_text_field($_POST['cgrid_columns_5']) : '';
	$cgrid_height_5 		= isset($_POST['cgrid_height_5']) ? sanitize_text_field($_POST['cgrid_height_5']) : '';
	$cgrid_spacing_5 		= isset($_POST['cgrid_spacing_5']) ? sanitize_text_field($_POST['cgrid_spacing_5']) : '';

	$new = array();
	$new['cgrid_posttype'] = empty($cgrid_posttype) ? 'post' : wp_strip_all_tags($cgrid_posttype);
	$new['cgrid_orderby'] = empty($cgrid_orderby) ? 'date' : wp_strip_all_tags($cgrid_orderby);
	$new['cgrid_orderby_meta'] = empty($cgrid_orderby_meta) ? '' : wp_strip_all_tags($cgrid_orderby_meta);
	$new['cgrid_order'] = empty($cgrid_order) ? 'ASC' : wp_strip_all_tags($cgrid_order);
	$new['cgrid_num'] = empty($cgrid_num) ? '-1' : wp_strip_all_tags($cgrid_num);
	$new['cgrid_tax'] = empty($cgrid_tax) ? '' : wp_strip_all_tags($cgrid_tax);
	$new['cgrid_tax_terms'] = empty($cgrid_tax_terms) ? '' : wp_strip_all_tags($cgrid_tax_terms);
	$new['cgrid_sorting'] = empty($cgrid_sorting) ? '' : wp_filter_post_kses($cgrid_sorting);
	$new['cgrid_filters'] = empty($cgrid_filters) ? '' : wp_filter_post_kses($cgrid_filters);
	
	$new['cgrid_layout'] = empty($cgrid_layout) ? 'fitRows' : wp_strip_all_tags($cgrid_layout);
	$new['cgrid_full_width'] = $cgrid_full_width ? '1' : '0';
	$new['cgrid_query_string'] = $cgrid_query_string ? '1' : '0';
	
	$new['cgrid_columns_1'] = empty($cgrid_columns_1) ? '1' : wp_filter_post_kses($cgrid_columns_1);
	$new['cgrid_height_1'] = empty($cgrid_height_1) ? '0' : wp_filter_post_kses($cgrid_height_1);
	$new['cgrid_spacing_1'] = empty($cgrid_spacing_1) ? '0' : wp_filter_post_kses($cgrid_spacing_1);
	
	$new['cgrid_breakpoint_2'] = empty($cgrid_breakpoint_2) ? '1' : wp_filter_post_kses($cgrid_breakpoint_2);
	$new['cgrid_columns_2'] = empty($cgrid_columns_2) ? '1' : wp_filter_post_kses($cgrid_columns_2);
	$new['cgrid_height_2'] = empty($cgrid_height_2) ? '0' : wp_filter_post_kses($cgrid_height_2);
	$new['cgrid_spacing_2'] = empty($cgrid_spacing_2) ? '0' : wp_filter_post_kses($cgrid_spacing_2);
	
	$new['cgrid_breakpoint_3'] = empty($cgrid_breakpoint_3) ? '1' : wp_filter_post_kses($cgrid_breakpoint_3);
	$new['cgrid_columns_3'] = empty($cgrid_columns_3) ? '1' : wp_filter_post_kses($cgrid_columns_3);
	$new['cgrid_height_3'] = empty($cgrid_height_3) ? '0' : wp_filter_post_kses($cgrid_height_3);
	$new['cgrid_spacing_3'] = empty($cgrid_spacing_3) ? '0' : wp_filter_post_kses($cgrid_spacing_3);
	
	$new['cgrid_breakpoint_4'] = empty($cgrid_breakpoint_4) ? '1' : wp_filter_post_kses($cgrid_breakpoint_4);
	$new['cgrid_columns_4'] = empty($cgrid_columns_4) ? '1' : wp_filter_post_kses($cgrid_columns_4);
	$new['cgrid_height_4'] = empty($cgrid_height_4) ? '0' : wp_filter_post_kses($cgrid_height_4);
	$new['cgrid_spacing_4'] = empty($cgrid_spacing_4) ? '0' : wp_filter_post_kses($cgrid_spacing_4);
	
	$new['cgrid_breakpoint_5'] = empty($cgrid_breakpoint_5) ? '1' : wp_filter_post_kses($cgrid_breakpoint_5);
	$new['cgrid_columns_5'] = empty($cgrid_columns_5) ? '1' : wp_filter_post_kses($cgrid_columns_5);
	$new['cgrid_height_5'] = empty($cgrid_height_5) ? '0' : wp_filter_post_kses($cgrid_height_5);
	$new['cgrid_spacing_5'] = empty($cgrid_spacing_5) ? '0' : wp_filter_post_kses($cgrid_spacing_5); 

	update_post_meta($post_id, '_cgrid_options', $new);
	
	// Save _cgrid_skin
	$cgrid_skin_content = isset($_POST['cgrid_skin_content']) ? ($_POST['cgrid_skin_content']) : '';

	$new = array();
	$new['cgrid_skin_content'] = $cgrid_skin_content;

	update_post_meta($post_id, '_cgrid_skin', $new);
}

?>