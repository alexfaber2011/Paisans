<?php

/**
 * The PHP code for setup Theme page custom fields.
 *
 * @package WordPress
 */

function is_edit_page($new_edit = null){
    global $pagenow;
    //make sure we are on the backend
    if (!is_admin()) return false;


    if($new_edit == "edit")
        return in_array( $pagenow, array( 'post.php',  ) );
    elseif($new_edit == "new") //check for new post page
        return in_array( $pagenow, array( 'post-new.php' ) );
    else //check for either new or edit
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}

/*
	Begin creating custom fields
*/

//Get all sidebars
$theme_sidebar = array(
	'' => '',
	'Page Sidebar' => 'Page Sidebar', 
	'Contact Sidebar' => 'Contact Sidebar', 
	'Blog Sidebar' => 'Blog Sidebar',
);

$dynamic_sidebar = get_option('pp_sidebar');

if(!empty($dynamic_sidebar))
{
	foreach($dynamic_sidebar as $sidebar)
	{
		$theme_sidebar[$sidebar] = $sidebar;
	}
}

/*
	Get gallery list
*/
$args = array(
    'numberposts' => -1,
    'post_type' => array('galleries'),
);

$galleries_arr = get_posts($args);
$galleries_select = array();
$galleries_select['(Display Post Featured Image)'] = '';

foreach($galleries_arr as $gallery)
{
	$galleries_select[$gallery->ID] = $gallery->post_title;
}

//Get all menu categories
function retrieve_my_menucats() 
{
    global $terms;
    
    $terms = get_terms('menucats', 'hide_empty=0&hierarchical=0&parent=0');
	$menu_cat_select = array();
	$menu_cat_select[''] = '';
	
	foreach($terms as $term)
	{
	    $menu_cat_select[$term->slug] = $term->name;
	}
	//pp_debug($menu_cat_select);
	return $menu_cat_select;
}
add_action('init', 'retrieve_my_menucats', 9999);

/*
	Get page menu styles
*/
$page_header_select = array();
$page_header_select = array(
	'' => 'Default Style',
	1 => 'Header Style 1',
	2 => 'Header Style 2',
	3 => 'Header Style 3',
	4 => 'Header Style 4',
);

$page_postmetas = 
	array (
		/*
			Begin Page custom fields
		*/
		
		array("section" => "Page Title", "id" => "page_hide_header", "type" => "checkbox", "title" => "Hide Page Title", "description" => "Check this option if you want to hide page title."),
		
		array("section" => "Select Sidebar", "id" => "page_sidebar", "type" => "select", "title" => "Page Sidebar", "description" => "Select this page's sidebar to display", "items" => $theme_sidebar),
		
		array("section" => "Content Type", "id" => "page_gallery_id", "type" => "select", "title" => "Gallery", "description" => "You can select image gallery to display on this page. (If you select Gallery as page template)", "items" => $galleries_select),
		
		/*
			End Page custom fields
		*/
	);

//Check if Layer slider is installed	
$revslider = ABSPATH . '/wp-content/plugins/revslider/revslider.php';

// Check if the file is available to prevent warnings
$pp_revslider_activated = file_exists($revslider);

if($pp_revslider_activated)
{
	//Get WPDB Object
	global $wpdb;
	
	// Get Rev Sliders
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$is_revslider_active = is_plugin_active('revslider/revslider.php');
	$wp_revsliders = array();
	
	if($is_revslider_active)
	{
		$wp_revsliders = array(
			-1		=> "Choose a slide",
		);
		$revslider_objs = new RevSlider();
		$revslider_obj_arr = $revslider_objs->getArrSliders();
		
		foreach($revslider_obj_arr as $revslider_obj)
		{
			$wp_revsliders[$revslider_obj->getAlias()] = $revslider_obj->getTitle();
		}
	}
	
	$page_postmetas[] = array("section" => "Slider", "id" => "page_revslider", "type" => "select", "title" => "Page Slider", "description" => "Select Slider for this page. It will displays at the top of page under page title", "items" => $wp_revsliders);
	
	$page_postmetas[] = array("section" => "Slider", "id" => "page_header_below", "type" => "checkbox", "title" => "Display Header Below Slider", "description" => "Check this option if you want to display page header below page slider (If enable this option menu always displays in solid background color)");
	
	$page_postmetas[] = array("section" => "Slider", "id" => "page_menu_transparent", "type" => "checkbox", "title" => "Make Main Menu Transparent", "description" => "Check this option if you want to display menu in transparent");
}
else
{
	$wp_revsliders[-1] = 'Please Install Revolution Slider Plugin to use this option';
}

?>
<?php

function page_create_meta_box() {

	global $page_postmetas;
	if ( function_exists('add_meta_box') && isset($page_postmetas) && count($page_postmetas) > 0 ) {  
		add_meta_box( 'page_metabox', 'Page Options', 'page_new_meta_box', 'page', 'side', 'high' );  
	}

}  

function page_new_meta_box() {
	global $post, $page_postmetas;
	
	if(!is_edit_page('new'))
	{
		//Add menu cats select
		$menu_cats_select = retrieve_my_menucats();
		$page_postmetas[] = array("section" => "Menu Category", "id" => "page_menu_cat", "type" => "select", "title" => "Food Menu Category", "description" => "You can select food menu category to display on this page. (If you select Menu as page template)", "items" => $menu_cats_select);
	}

	echo '<input type="hidden" name="pp_meta_form" id="pp_meta_form" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	echo '<br/>';
	
	$meta_section = '';

	foreach ( $page_postmetas as $postmeta ) {

		$meta_id = $postmeta['id'];
		$meta_title = $postmeta['title'];
		$meta_description = $postmeta['description'];
		$meta_section = $postmeta['section'];
		
		$meta_type = '';
		if(isset($postmeta['type']))
		{
			$meta_type = $postmeta['type'];
		}
		
		echo "<strong>".$meta_title."</strong><hr class='pp_widget_hr'/>";

		echo "<div class='pp_widget_description'>$meta_description</div>";

		if ($meta_type == 'checkbox') {
			$checked = get_post_meta($post->ID, $meta_id, true) == '1' ? "checked" : "";
			echo "<br style='clear:both'><input type='checkbox' name='$meta_id' id='$meta_id' class='iphone_checkboxes' value='1' $checked /><br style='clear:both'/>";
		}
		else if ($meta_type == 'select') {
			echo "<p><select name='$meta_id' id='$meta_id'>";
			
			if(!empty($postmeta['items']))
			{
				foreach ($postmeta['items'] as $key => $item)
				{
					$page_style = get_post_meta($post->ID, $meta_id);
				
					if(isset($page_style[0]) && $key == $page_style[0])
					{
						$css_string = 'selected';
					}
					else
					{
						$css_string = '';
					}
				
					echo '<option value="'.$key.'" '.$css_string.'>'.$item.'</option>';
				}
			}
			
			echo "</select></p>";
		}
		else if ($meta_type == 'file') { 
		    echo "<p><input type='text' name='$meta_id' id='$meta_id' class='' value='".get_post_meta($post->ID, $meta_id, true)."' style='width:89%' /><input id='".$meta_id."_button' name='".$meta_id."_button' type='button' value='Upload' class='metabox_upload_btn button' readonly='readonly' rel='".$meta_id."' style='margin:7px 0 0 5px' /></p>";
		}
		else if ($meta_type == 'textarea') { 
			echo "<p><textarea name='$meta_id' id='$meta_id' class='' style='width:99%' rows='5'>".get_post_meta($post->ID, $meta_id, true)."</textarea></p>";
		}
		else {
			echo "<p><input type='text' name='$meta_id' id='$meta_id' class='' value='".get_post_meta($post->ID, $meta_id, true)."' style='width:99%' /></p>";
		}
		
		echo '<br/>';
	}
	
	echo '<br/>';

}

function page_save_postdata( $post_id ) {

	global $page_postmetas;
	
	//Add menu cats select
	$menu_cats_select = retrieve_my_menucats();
	$page_postmetas[] = array("section" => "Menu Category", "id" => "page_menu_cat", "type" => "select", "title" => "Food Menu Category", "description" => "You can select food menu category to display on this page. (If you select Menu as page template)", "items" => $menu_cats_select);

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( isset($_POST['pp_meta_form']) && !wp_verify_nonce( $_POST['pp_meta_form'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

	// Check permissions

	if ( isset($_POST['post_type']) && 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	// OK, we're authenticated

	if ( $parent_id = wp_is_post_revision($post_id) )
	{
		$post_id = $parent_id;
	}

	if (isset($_POST['pp_meta_form'])) 
	{
		foreach ( $page_postmetas as $postmeta ) {
		
			if (isset($_POST[$postmeta['id']]) && $_POST[$postmeta['id']]) {
				page_update_custom_meta($post_id, $_POST[$postmeta['id']], $postmeta['id']);
			}
	
			if (isset($_POST[$postmeta['id']]) && $_POST[$postmeta['id']] == "") {
				delete_post_meta($post_id, $postmeta['id']);
			}
			
			if (!isset($_POST[$postmeta['id']])) {
				delete_post_meta($post_id, $postmeta['id']);
			}
		}
		
		// Saving Page Builder Data
		if(isset($_POST['ppb_enable']) && !empty($_POST['ppb_enable']))
		{
			page_update_custom_meta($post_id, $_POST['ppb_enable'], 'ppb_enable');
		}
		else
		{
			delete_post_meta($post_id, 'ppb_enable');
		}

		if(isset($_POST['ppb_form_data_order']) && !empty($_POST['ppb_form_data_order']))
		{
			page_update_custom_meta($post_id, $_POST['ppb_form_data_order'], 'ppb_form_data_order');
			
			$ppb_item_arr = explode(',', $_POST['ppb_form_data_order']);
			if(is_array($ppb_item_arr) && !empty($ppb_item_arr))
			{
				foreach($ppb_item_arr as $key => $ppb_item_arr)
				{
					if(isset($_POST[$ppb_item_arr.'_data']) && !empty($_POST[$ppb_item_arr.'_data']))
					{
						page_update_custom_meta($post_id, $_POST[$ppb_item_arr.'_data'], $ppb_item_arr.'_data');
					}
					
					if(isset($_POST[$ppb_item_arr.'_size']) && !empty($_POST[$ppb_item_arr.'_size']))
					{
						page_update_custom_meta($post_id, $_POST[$ppb_item_arr.'_size'], $ppb_item_arr.'_size');
					}
				}
			}
		}
		//If content builder is empty
		else
		{
			page_update_custom_meta($post_id, '', 'ppb_form_data_order');
		}
	}
}

function page_update_custom_meta($postID, $newvalue, $field_name) {

	if (isset($_POST['pp_meta_form'])) 
	{
		if (!get_post_meta($postID, $field_name)) {
			add_post_meta($postID, $field_name, $newvalue);
		} else {
			update_post_meta($postID, $field_name, $newvalue);
		}
	}

}

//init

add_action('admin_menu', 'page_create_meta_box'); 
add_action('save_post', 'page_save_postdata');  

/*
	End creating custom fields
*/

?>
