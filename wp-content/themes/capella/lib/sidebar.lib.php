<?php

/**
*	Setup Page side bar
**/
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Page Sidebar', 'description' => 'The default sidebar for every pages'));
    
/**
*	Setup Blog side bar
**/
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Blog Sidebar', 'description' => 'The default sidebar for blog page templates'));
    
/**
*	Setup Contact side bar
**/
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Contact Sidebar', 'description' => 'The default sidebar for contact page template'));
    
/**
*	Setup Single Post side bar
**/
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Single Post Sidebar', 'description' => 'The default sidebar for single post page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Single Image Page Sidebar', 'description' => 'The default sidebar for single attachment (image) page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Archives Sidebar', 'description' => 'The default sidebar for archive page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Category Sidebar', 'description' => 'The default sidebar for post category page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Search Sidebar', 'description' => 'The default sidebar for search result page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Tag Sidebar', 'description' => 'The default sidebar for tag post page'));
    
if ( function_exists('register_sidebar') )
    register_sidebar(array('name' => 'Footer Sidebar', 'description' => 'The default sidebar for footer area of every pages'));

if(!THEMEDEMO)
{
	if ( function_exists('register_sidebar') )
    	register_sidebar(array('name' => 'Shop Sidebar', 'description' => 'The default sidebar for shop pages'));
}


//Register dynamic sidebar
$dynamic_sidebar = get_option('pp_sidebar');

if(!empty($dynamic_sidebar))
{
	foreach($dynamic_sidebar as $sidebar)
	{
		if ( function_exists('register_sidebar') )
	    register_sidebar(array('name' => $sidebar));
	}
}

?>