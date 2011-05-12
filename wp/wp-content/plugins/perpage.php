<?php
/*
Plugin Name: Custom Posts Per Page
Version: 1.0
Plugin URI: http://wordpress.org/support/6/11211
Description: Change the number of posts per page displayed for different page types.
Author: Based on code by rboren & brehaut
Author URI:
*/


function custom_posts_per_page($query_string) {
global $posts_per;

	$query = new WP_Query();
	$query->parse_query($query_string);
	
	if ($query->is_category('news') ) {
		$num = '10';
	} elseif ($query->is_category('products')) {
		$num = '10';
	}
	
	if (isset($num)) {
	
	
		if (preg_match("/posts_per_page=/", $query_string)) {
			
			$query_string = preg_replace("/posts_per_page=[0-9]*/", "posts_per_page=$num", $query_string);
		} else {
			if ($query_string != '') {
				$query_string .= '&';
			}
		$query_string .= "posts_per_page=$num";
		}
		
	}
	
return $query_string;
}
add_filter('query_string', 'custom_posts_per_page');
?>