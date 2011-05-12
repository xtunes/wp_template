<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;

$ngg_options  = get_option ('ngg_options');
$options      = get_option ('monoslideshow');
$siteurl      = get_option ('siteurl');

// get the gallery id
$galleryID = (int) $_GET['gid'];

// check if someone send the preset value
$preset = ( isset($_GET['preset']) ) ? $_GET['preset'] : '';

// get the pictures
if ($galleryID == 0) {
	$pictures = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 ORDER BY tt.{$ngg_options['galSort']} {$ngg_options['galSortDir']} ");
} else {
	$pictures = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = '$galleryID' AND tt.exclude != 1 ORDER BY tt.{$ngg_options['galSort']} {$ngg_options['galSortDir']} ");
}
// read some default options
$delay = (int) $options['delay'];
$randomize = ($options['randomize']) ? 'true' : 'false';
$scaleMode = $options['scaleMode'];
$kenBurnsMode = $options['kenBurnsMode'];
$onAlbumEnd = $options['onAlbumEnd'];
$controls = ($options['controls']) ? 'normal' : 'none';
$backgroundColor = ($options['bgTransparent'] == true) ? 'transparent' : '#' . $options['backgroundColor'];
$displayMode  = $options['displayMode'];
$config = false;

//look for a preset file
if ( !empty($preset) )
    $config =  nggMonoslideshow::read_preset( $preset );

// Create XML output
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?'.'>';
echo "<album>\n";
if ($config === false) {
echo "\t<configuration onAlbumEnd='$onAlbumEnd' fullScreenScaleMode='$scaleMode' scaleMode='$scaleMode' backgroundColor='$backgroundColor' delay='$delay' randomize='$randomize'>\n";
echo "\t\t<caption displayMode='$displayMode' />\n";
echo "\t\t<controller type='$controls' />\n";
echo "\t\t<transition kenBurnsMode='$kenBurnsMode' />\n";
echo "\t</configuration>\n";
} else
echo "\t$config\n";
echo "\t<contents>\n";

if (is_array ($pictures)){
	foreach ($pictures as $picture) {
	   
        $description = htmlspecialchars ( strip_tags( stripslashes( nggGallery::i18n($picture->description) ) ) , ENT_QUOTES );
        
        if (!empty($picture->alttext))	
            $title = stripslashes( htmlspecialchars (nggGallery::i18n($picture->alttext) , ENT_QUOTES) );
        else
            $title = $picture->filename;
            
        $path = $siteurl . '/' . $picture->path . '/' . $picture->filename;
        $thumbnail = $siteurl . '/' . $picture->path . '/thumbs/thumbs_' . $picture->filename;   
        
        echo "\t\t<image id='$picture->pid' source='$path' title='$title' description='$description' thumbnail='$thumbnail' />\n";             
	}
}
 
echo "	</contents>\n";
echo "</album>\n";

?>