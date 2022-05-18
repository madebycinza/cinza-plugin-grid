<?php
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Register CPT: cinza_grid
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
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => true,
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

add_filter( 'manage_cinza_grid_posts_columns', 'set_custom_edit_cinza_grid_columns' );
function set_custom_edit_cinza_grid_columns($columns) {
    $columns['shortcode'] = __( 'Shortcode', 'your_text_domain' );
    return $columns;
}

add_action( 'manage_cinza_grid_posts_custom_column' , 'custom_cinza_grid_column', 10, 2 );
function custom_cinza_grid_column( $column, $post_id ) {
	switch ( $column ) {
		case 'shortcode' :
			echo('[cinza_grid id="'. esc_attr($post_id) .'"]');
			break;
	}
}

add_filter ( 'manage_cinza_grid_posts_columns', 'add_cinza_grid_columns', 99, 99 );
function add_cinza_grid_columns ( $columns ) {
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add Meta Boxes
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'add_meta_boxes', 'cgrid_add_fields_meta_boxes', 99, 99 );
function cgrid_add_fields_meta_boxes() {
	add_meta_box('cgrid-options', 'Options', 'cgrid_meta_box_options', 'cinza_grid', 'normal', 'default');
	add_meta_box('cgrid-skin', 'Skin', 'cgrid_meta_box_skin', 'cinza_grid', 'normal', 'default');
	add_meta_box('cgrid-documentation', 'Documentation', 'cgrid_meta_box_doc', 'cinza_grid', 'side', 'default');
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
	$temp_posttype = 'post';
	
	// Get saved values
	if ( !empty($cgrid_options) ) {
		$temp_posttype = esc_attr($cgrid_options['cgrid_posttype']);
	}

	?>
	<table id="cgrid-optionset" width="100%">
		<thead>
			<tr>
				<td class="cgrid-options" colspan="2">
					<p>Post Type</p>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
            	<td class="cgrid-options col-1">
                    <label for="cgrid_posttype">Post type:</label>
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
							    <option value="<?php echo strtolower($post_type); ?>" <?php if(isset($temp_posttype) && ($temp_posttype == $post_type))  echo 'selected="selected"'; ?>><?php echo $post_type; ?></option><?php
					    	}
					    }
					    echo '</select>'; 
					}?>
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
				<td class="cgrid-options col-1">
					<label for="cgrid_AAAAA">Coming soon!</label>
				</td>
				<td class="cgrid-options col-2">
					<!-- <input type="checkbox" name="cgrid_AAAAA" id="cgrid_AAAAA" class="widefat cgrid-AAAAA" value="1" <?php checked('1', $temp_AAAAA); ?> /> -->
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
				<td class="cgrid-options col-1">
					<label for="cgrid_AAAAA">Coming soon!</label>
				</td>
				<td class="cgrid-options col-2">
					<!-- <input type="checkbox" name="cgrid_AAAAA" id="cgrid_AAAAA" class="widefat cgrid-AAAAA" value="1" <?php checked('1', $temp_AAAAA); ?> /> -->
				</td>
            </tr>
		</tbody>
	</table>
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
					
					<table>
						<tr>
							<td><code>%title%</code></td>
							<td><code>$post->post_title</code></td>
							<td><em></em></td>
						</tr>
						<tr>
							<td><code>%url%</code></td>
							<td><code>get_permalink( $post->ID )</code></td>
							<td><em></em></td>
						</tr>
						<tr>
							<td><code>%date%</code></td>
							<td><code>get_the_date('F j, Y')</code></td>
							<td><em></em></td>
						</tr>
						<tr>
							<td><code>%date('l F j, Y')%</code></td>
							<td><code>get_the_date('l F j, Y', $post->ID)</code></td>
							<td><em>It works with any date format.</em></td>
						</tr>
					</table>
					
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Meta Box: _cgrid_doc
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cgrid_meta_box_doc( $post ) {
	?><a href="https://isotope.metafizzy.co/options.html" target="_blank" class="preview button">Isotope documentation</a><?php
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

	// Save _cgrid_options
	$cgrid_posttype = sanitize_text_field($_POST['cgrid_posttype']);

	$new = array();
	$new['cgrid_posttype'] = empty($cgrid_posttype) ? 'post' : wp_strip_all_tags($cgrid_posttype);

	update_post_meta($post_id, '_cgrid_options', $new);
	
	// Save _cgrid_skin
	$cgrid_skin_content = wp_filter_post_kses($_POST['cgrid_skin_content']);

	$new = array();
	$new['cgrid_skin_content'] = $cgrid_skin_content;

	update_post_meta($post_id, '_cgrid_skin', $new);
}

?>