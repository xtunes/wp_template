<?php
/*
Plugin Name: NextGEN Monoslideshow
Plugin URI: http://nextgen-gallery.com/slideshow/nextgen-monoslideshow/
Description: NextGEN Monoslideshow is a addon plugin for NextGEN Gallery plugin and Monoslideshow flash slideshow
Author: Alex Rabe
Author URI: http://alexrabe.de/
Version: 0.9.6

Copyright 2009-2010 by Alex Rabe

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/ 

class nggMonoslideshow {
	
	var $plugin_name = 'monoslideshow';
    var $plugin_url  = '';
    var $options     = '';

	function nggMonoslideshow() {
	   
		if (!class_exists('nggLoader') ) {
			add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('Sorry, Monoslideshow works only in combination with NextGEN Gallery.','monoss') . '</strong></p></div>\';'));
			return;
		}
		
		$this->plugin_name = plugin_basename( dirname(__FILE__) );
        $this->plugin_url  = WP_PLUGIN_URL . '/' . ltrim( $this->plugin_name, '/' ) . '/';
        $this->options     = get_option ('monoslideshow');
        
        // if no options aviabale, we setup the default values
        if( !is_array($this->options) )
            $this->default_options();
                
		add_action( 'admin_init', array(&$this, 'register_init') );
		add_action( 'admin_menu', array (&$this, 'add_menu') );
		//add_action('admin_print_scripts', array(&$this, 'load_scripts') );
        add_shortcode( 'monoslideshow', array(&$this, 'execute_shortcode' ) );
        
        // Look for XML request, before page is render
        add_action('parse_request',  array(&$this, 'check_request') );
        
        // Adding for admin menu & frontend
        add_action('admin_print_scripts', array(&$this, 'load_scripts') );
        add_action('template_redirect', array(&$this, 'load_scripts') ); 
        
        if (isset($this->options['replaceIR']) && $this->options['replaceIR'] == true ) {
            add_filter('ngg_show_slideshow_content', array(&$this, 'replace_slideshow'), 10, 4);
            add_filter('ngg_show_slideshow_widget_content', array(&$this, 'replace_slideshow'), 10, 4);
        }          
        
	}

    function default_options() {
        
        $this->options = array();
        $this->options['path']              = 'wp-content/uploads/monoslideshow.swf';
        $this->options['defaultWidth']      = 320;
        $this->options['defaultHeight']     = 240;
        $this->options['replaceIR']         = false;
        $this->options['preset']            = 'none';
        $this->options['delay']             = 10;
        $this->options['backgroundColor']   = '000000';
        $this->options['bgTransparent']     = true;
        $this->options['onAlbumEnd']        = 'loop';
        $this->options['randomize']         = false;
        $this->options['controls']          = true;
        $this->options['displayMode']       = 'always';
        $this->options['scaleMode']         = 'scaleToFit';
        $this->options['kenBurnsMode']      = 'random';
        $this->options['disableLogo']       = false;
        
        update_option('monoslideshow', $this->options);
    }
    
    function uninstall() {
     
    	delete_option('monoslideshow');
    }    

    function check_request( $wp ) {
    	
    	if ( !array_key_exists('callback', $wp->query_vars) )
    		return;
        
        if ( $wp->query_vars['callback'] == 'monoslideshow') {
            require_once (dirname (__FILE__) . '/xml.php');
            exit();
        }
        
    }
    
    function replace_slideshow ( $content, $galleryID, $width, $height ) {
        return $this->render_monoslideshow($galleryID, $width, $height); 
    }

    function register_init(){
        register_setting( 'monoslideshow', 'monoslideshow', array(&$this, 'validate') );
    }
    
    function validate($options) {

        // Bool check
        $options['replaceIR'] = isset($options['replaceIR']) ? true : false;
        $options['bgTransparent'] = isset($options['bgTransparent']) ? true : false;
        $options['randomize'] = isset($options['randomize']) ? true : false;
        $options['controls'] = isset($options['controls']) ? true : false;
        $options['disableLogo'] = isset($options['disableLogo']) ? true : false;
                         
        // Must be safe text with no HTML tags
        $options['preset'] =  wp_filter_nohtml_kses($options['preset']);

        return $options;
    }

	// integrate the menu	
	function add_menu()  {
		add_submenu_page( NGGFOLDER , __('Monoslideshow', 'monoss'), __('Monoslideshow', 'monoss'), 'activate_plugins', 'monoslideshow', array (&$this, 'show_page'));
	}
	
	function load_scripts() {
	   
        if (is_admin() && ( !isset($_GET['page']) || $_GET['page'] != 'monoslideshow') )
            return;
            
		wp_enqueue_script('swfobject', NGGALLERY_URLPATH . 'admin/js/swfobject.js', false, '2.2');
	}

	function show_page() {
	   
    $border  = '';
    
    if ( !is_readable( ABSPATH . $this->options['path'] ) || empty($this->options['path']) ) {
        if ( $path = $this->search_file( 'monoslideshow.swf' ) ) {
            $this->options['path'] = $path;
            update_option('monoslideshow', $this->options);
        } else {
            $border = 'style="border-color:red; border-width:2px; border-style:solid; padding:5px;"';
            nggGallery::show_error( '<strong>' . __('Could not found monoslideshow.swf, please verify the path or upload the file.', 'monoss') . '</strong>' );            
        }

    } 
   
    // The update message will not appear outside the options page
    if (isset( $_GET['updated']) )
        nggGallery::show_message( '<strong>' . __('Settings saved.') . '</strong>' );           
        
	?>
    <script type="text/javascript">
        function setcolor(fileid,color) { jQuery(fileid).css("background-color", '#' + color ); };
    </script>
	<div class="wrap">
		<h2><?php _e('Monoslideshow Options', 'monoss'); ?></h2>
        <div id="poststuff" class="has-right-sidebar">
            <div class="inner-sidebar" style="width:335px;">
                <div class="postbox">
                    <h3 class="hndle"><span><?php _e('Preview','monoss') ?></span></h3>
                    <div class="inside">
                    <?php echo $this->render_monoslideshow(0, 320, 240, $this->options['preset']); ?>
                    </div>
                </div>
            </div>
    		<div id="post-body" class="has-sidebar">
                <div id="post-body-content" class="has-sidebar-content">
        		<form method="post" action="options.php" style="float: left;">
        			<?php settings_fields('monoslideshow'); ?>
        			<table class="form-table">
                        <tr valign="top" <?php echo $border; ?>>
                            <th scope="row"><?php _e('Path to Monoslideshow', 'monoss'); ?></th>
        					<td>
                                <input type="text" size="60" name="monoslideshow[path]" value="<?php echo $this->options['path']; ?>" /><br />
                                <span class="description"><?php _e('Upload the flash file to your blog. Default is', 'monoss'); ?> <code>wp-content/uploads/monoslideshow.swf</code></span> 
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Default size (width x height)', 'monoss'); ?></th>
        					<td>
                                <input type="text" size="4" name="monoslideshow[defaultWidth]" value="<?php echo $this->options['defaultWidth']; ?>" />&nbsp;x&nbsp;
                                <input type="text" size="4" name="monoslideshow[defaultHeight]" value="<?php echo $this->options['defaultHeight']; ?>" />
        
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Disable start up logo', 'monoss'); ?></th>
        					<td>
                                <input name="monoslideshow[disableLogo]" type="checkbox" value="1" <?php checked('1', $this->options['disableLogo']); ?> />
                                <span class="description"><?php _e('Remove monoslideshow logo and registration info', 'monoss'); ?></span> 
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Replace default slideshow', 'monoss'); ?></th>
        					<td>
                                <input name="monoslideshow[replaceIR]" type="checkbox" value="1" <?php checked('1', $this->options['replaceIR']); ?> />
                                <span class="description"><?php _e('You can use Monoslideshow for all existing slideshows.', 'monoss'); ?></span> 
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Select default preset', 'monoss'); ?></th>
        					<td>
                                <select size="1" name="monoslideshow[preset]">
            						<option value="none" <?php selected('none', $this->options['preset']); ?> ><?php _e('None', 'monoss'); ?></option>
                                    <option value="iris" <?php selected('iris', $this->options['preset']); ?> ><?php _e('Iris', 'monoss'); ?></option>
                                    <option value="melt" <?php selected('melt', $this->options['preset']); ?> ><?php _e('Melt', 'monoss'); ?></option>
                                    <option value="newsflash" <?php selected('newsflash', $this->options['preset']); ?> ><?php _e('News Flash', 'monoss'); ?></option>
                                    <option value="rgb-separation" <?php selected('rgb-separation', $this->options['preset']); ?> ><?php _e('RGB Separation', 'monoss'); ?></option>
                                    <option value="focus" <?php selected('focus', $this->options['preset']); ?> ><?php _e('Simple Focus Fade', 'monoss'); ?></option>
                                    <option value="shatter" <?php selected('shatter', $this->options['preset']); ?> ><?php _e('Simple Shatter', 'monoss'); ?></option>
                                    <option value="sparkles" <?php selected('sparkles', $this->options['preset']); ?> ><?php _e('Sparkles', 'monoss'); ?></option>
                                    <option value="tv-swap" <?php selected('tv-swap', $this->options['preset']); ?> ><?php _e('TV Swap', 'monoss'); ?></option>
                                    <option value="widescreen" <?php selected('widescreen', $this->options['preset']); ?> ><?php _e('Widescreen', 'monoss'); ?></option>
                                    <option value="waterdrops" <?php selected('waterdrops', $this->options['preset']); ?> ><?php _e('Water Drops', 'monoss'); ?></option>
                                    <option value="wipe-3d" <?php selected('wipe-3d', $this->options['preset']); ?> ><?php _e('Wipe 3D', 'monoss'); ?></option>
                                    <option value="zoom" <?php selected('zoom', $this->options['preset']); ?> ><?php _e('Zoom', 'monoss'); ?></option>
             					</select>
                                <span class="description"><?php _e('If you choose a preset file, all other settings will be ignored', 'monoss'); ?></span>
                            </td>
        				</tr>
        			     <tr valign="top">
                            <th scope="row"><?php _e('Duration time', 'monoss'); ?></th>
        					<td>
                                <input type="text" size="6" maxlength="6" name="monoslideshow[delay]" value="<?php echo $this->options['delay']; ?>" /> <?php _e('sec.', 'monoss'); ?>
                            </td>
        				</tr>
        			     <tr valign="top">
                            <th scope="row"><?php _e('Background color', 'monoss'); ?></th>
        					<td>
                                <input class="picker" type="text" size="6" maxlength="6" name="monoslideshow[backgroundColor]" onchange="setcolor('#previewBack', this.value)" value="<?php echo $this->options['backgroundColor']; ?>" />
        						<input type="text" size="1" readonly="readonly" id="previewBack" style="background-color: #<?php echo $this->options['backgroundColor']; ?>" />
                                <input name="monoslideshow[bgTransparent]" type="checkbox" value="1" <?php checked('1', $this->options['bgTransparent']); ?> /> <?php _e('Transparent', 'monoss'); ?>
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('On album end', 'monoss'); ?></th>
        					<td>
                                <select size="1" name="monoslideshow[onAlbumEnd]">
            						<option value="loop" <?php selected('loop', $this->options['onAlbumEnd']); ?> ><?php _e('Loop', 'monoss'); ?></option>
                                    <option value="loadNextAlbum" <?php selected('loadNextAlbum', $this->options['onAlbumEnd']); ?> ><?php _e('Load next album', 'monoss'); ?></option>
                                    <option value="showNavigation" <?php selected('showNavigation', $this->options['onAlbumEnd']); ?> ><?php _e('Show navigation', 'monoss'); ?></option>
                                    <option value="showParentAlbum" <?php selected('showParentAlbum', $this->options['onAlbumEnd']); ?> ><?php _e('Show parent album', 'monoss'); ?></option>
            					</select>
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Randomize / Shuffle', 'monoss'); ?></th>
        					<td>
                                <input name="monoslideshow[randomize]" type="checkbox" value="1" <?php checked('1', $this->options['randomize']); ?> />
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Show navigation bar', 'monoss'); ?></th>
        					<td>
                                <input name="monoslideshow[controls]" type="checkbox" value="1" <?php checked('1', $this->options['controls']); ?> />
                            </td>
        				</tr>
        				<tr valign="top">
                            <th scope="row"><?php _e('Show caption / image description', 'monoss'); ?></th>
        					<td>
                                <select size="1" name="monoslideshow[displayMode]">
            						<option value="always" <?php selected('always', $this->options['displayMode']); ?> ><?php _e('Always', 'monoss'); ?></option>
                                    <option value="onHover" <?php selected('onHover', $this->options['displayMode']); ?> ><?php _e('Only on hover', 'monoss'); ?></option>
                                    <option value="never" <?php selected('never', $this->options['displayMode']); ?> ><?php _e('Never', 'monoss'); ?></option>
             					</select>
                            </td>
        				</tr>               
        				<tr valign="top">
                            <th scope="row"><?php _e('Item scale mode', 'monoss'); ?></th>
        					<td>
                                <select size="1" name="monoslideshow[scaleMode]">
            						<option value="scaleToFit" <?php selected('scaleToFit', $this->options['scaleMode']); ?> ><?php _e('Scale to fit', 'monoss'); ?></option>
                                    <option value="scaleToFill" <?php selected('scaleToFill', $this->options['scaleMode']); ?> ><?php _e('Scale to fill', 'monoss'); ?></option>
                                    <option value="none" <?php selected('none', $this->options['scaleMode']); ?> ><?php _e('None', 'monoss'); ?></option>
                                    <option value="downscaleToFit" <?php selected('downscaleToFit', $this->options['scaleMode']); ?> ><?php _e('Downscale to fit', 'monoss'); ?></option>
                                    <option value="downscaleToFill" <?php selected('downscaleToFill', $this->options['scaleMode']); ?> ><?php _e('Downscale to fill', 'monoss'); ?></option>
            					</select>
                            </td>
        				</tr>                
        				<tr valign="top">
                            <th scope="row"><?php _e('Ken Burns mode', 'monoss'); ?></th>
        					<td>
                                <select size="1" name="monoslideshow[kenBurnsMode]">
                                    <option value="none" <?php selected('none', $this->options['kenBurnsMode']); ?> ><?php _e('None', 'monoss'); ?></option>
                                    <option value="random" <?php selected('random', $this->options['kenBurnsMode']); ?> ><?php _e('Random', 'monoss'); ?></option>
            						<option value="randomZoom" <?php selected('randomZoom', $this->options['kenBurnsMode']); ?> ><?php _e('Zoom', 'monoss'); ?></option>
            						<option value="randomPan" <?php selected('randomPan', $this->options['kenBurnsMode']); ?> ><?php _e('Pan', 'monoss'); ?></option>
                                    <option value="random3D" <?php selected('random3D', $this->options['kenBurnsMode']); ?> ><?php _e('3D mode', 'monoss'); ?></option>
            					</select>
                            </td>
        				</tr>
        			</table>
        			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
        		</form>
                </div>
            </div>
        </div>
	</div>
	<?php
	}

    function search_file( $filename ) {
    	global $wpdb;
    
    	$upload = wp_upload_dir();

    	// look first at the old place and move it to wp-content/uploads
    	if ( is_readable( trailingslashit(WP_PLUGIN_DIR) . trailingslashit(plugin_basename( dirname(__FILE__) )) . $filename ) )
    		@rename( trailingslashit(WP_PLUGIN_DIR) . trailingslashit (plugin_basename( dirname(__FILE__) )) . $filename, trailingslashit($upload['basedir']) . $filename);

    	// this should be the best place 	
    	if ( is_readable( trailingslashit($upload['basedir']) . $filename ) )
    		return trailingslashit ( get_option( 'upload_path' ) ) . $filename;
    
    	// Find the path to the file via the media library
    	if ( $ID = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '%$filename%'" ) ) {
            if ( $path = get_post_meta( $ID, '_wp_attached_file', true ) )
                return trailingslashit ( get_option( 'upload_path' ) ) . $path;
    	}
        
    	// maybe it's located in wp-content
    	if ( is_readable( trailingslashit(WP_CONTENT_DIR) . $filename ) )
            return str_replace(ABSPATH, '', trailingslashit(WP_CONTENT_DIR) . $filename);	   
    
    	// or in the plugin folder
    	if ( is_readable( trailingslashit(WP_PLUGIN_DIR) . $filename ) )
            return str_replace(ABSPATH, '', trailingslashit(WP_PLUGIN_DIR) . $filename);	   
            
    	// this is deprecated and will be ereased during a automatic upgrade
    	if ( is_readable( trailingslashit(WP_PLUGIN_DIR) . plugin_basename( dirname(__FILE__) ) . $filename ) )
            return str_replace(ABSPATH, '', trailingslashit(WP_PLUGIN_DIR) . trailingslashit(plugin_basename( dirname(__FILE__) )) . $filename);	   
                		
    	return false;
    }

    function read_preset( $name ) {
        
        $file = WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) . '/presets/' . $name . '.xml' ;

  		if ( !is_readable( $file ))
			return false; 
            
        // read in the configuration file
        $data = implode('', file($file));
        
        // lookup for the cinfiguration part 
        preg_match("#<configuration([^>]*)>(.*)<\/configuration>#is", $data, $result);
        if ( !empty($result) )
            return $result[0];
        
        return false;
    }
    
    function execute_shortcode( $atts ) {
   
        extract(shortcode_atts(array(
            'id'        => 0,
            'w'         => '',
            'h'         => '',
            'preset'    => ''
        ), $atts ));
        
        if( !empty($id)  )
            $out = $this->render_monoslideshow($id, $w, $h, $preset);
        else 
            $out = __('[Gallery not found]','nggallery');
            
        return $out;
    }
    
    function render_monoslideshow($galleryID, $width, $height, $preset = '') {
        
        require_once (dirname (__FILE__) . '/swfobject.php');
    
        $options = get_option('monoslideshow');
        
        $galleryID = (int) $galleryID;
        
        $options['swf_url'] = trailingslashit (get_option ('siteurl')) . $this->options['path'];
        
        if (empty($width) ) $width  = (int) $options['defaultWidth'];
        if (empty($height)) $height = (int) $options['defaultHeight'];
        if (empty($preset)) $preset =       $options['preset'];
        
        // wmode transparent will need a lot of CPU power
        $wmode = $options['bgTransparent'] == true ? 'transparent' : 'opaque';
        
        // init the flash output
        $swf = new swfobject( $options['swf_url'] , 'mo' . $galleryID, $width, $height, '7.0.0', 'false');
    
        $swf->message = '<p>'. __('The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed..', 'monoss').'</p>';
        $swf->add_flashvars( 'showLogo', !$options['disableLogo'], 'true');
        $swf->add_flashvars( 'showRegistration', !$options['disableLogo'], 'true');
        $swf->add_params('wmode', $wmode);
        $swf->add_params('allowfullscreen', 'true');
        $swf->add_attributes('bgColor', $options['backgroundColor'], '', 'string', '#');
        $swf->add_attributes('styleclass', 'slideshow');
        $swf->add_attributes('name', 'so' . $galleryID);

        // adding the flash parameter
        $preset = ( empty ($preset) || $preset == 'none' ) ? '' : '&preset=' . $preset; 
        $swf->add_flashvars( 'dataFile', urlencode (get_option ('siteurl') . '/' . 'index.php?callback=monoslideshow&gid=' . $galleryID . $preset ) );

        // create the output
        $out  = '<div class="monoslideshow">' . $swf->output() . '</div>';
        
        // add now the script code
        $out .= "\n".'<script type="text/javascript" defer="defer">';
        $out .= $swf->javascript();
        $out .= "\n".'</script>';
                
        return $out;    
    }   	

}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $nggMonoslideshow; $nggMonoslideshow = new nggMonoslideshow();' ) );
// Delete options if uninstall
register_uninstall_hook( plugin_basename(__FILE__), array('nggMonoslideshow', 'uninstall') );
?>