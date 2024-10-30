<?php
/*
Plugin Name: Crack Stack
Description: The plugin increases the page loading speed by changing the URL address of external elements on the page (scripts, styles, images, etc.) and by increasing the number of simultaneous browser connections.
Version: 1.0
Author: Egor Stremousov
Author Email: egor.stremousov@gmail.com
*/

/*  Copyright 2010, Egor Stremousov (email : egor.stremousov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('CRACK_STACK_DOMAIN_COUNT', 	'crack-stack-domain-count');
define('CRACK_STACK_DOMAIN_PREFIX', 'crack-stack-domain-prefix');
define('CRACK_STACK_FILE_EXT', 		'crack-stack-file-extensions');

class Crack_Stack
{	
	var $html;
	
	
	function __construct($html)
	{
		if ( !empty($html) )
		{
			$this->parseHTML($html);
		}
	}
	
	
	function parseHTML($html)
	{
		$this->html  = $this->modifyURLs($html);		
		$this->html .= "\n<!-- ". 
			__('Crack Stack Plugin modify this document.', 'crack-stack'). " -->";
	}
	
	
	function modifyURLs($html)
	{
		$domain_prefix 	= (string)get_option( CRACK_STACK_DOMAIN_PREFIX );
		$domain_count 	= (int)get_option( CRACK_STACK_DOMAIN_COUNT );
		
		$extensions 	= get_option( CRACK_STACK_FILE_EXT );
		$extensions 	= explode(',', $extensions);
		foreach($extensions as &$ext) {
			$ext = trim($ext);
		}
		$extensions		= implode('|', $extensions);
		
		$site_url = get_option('siteurl');		
		$site_url = preg_replace('/https?:\/\//i', "", $site_url);
		$site_url = preg_replace('/www\./i', "", $site_url);
		
		$pattern = "/(https?:\/\/)(www\.)?(".$site_url.")(\/[^\"']*\.)(".$extensions.")/ie";
		
		$html = preg_replace($pattern, "'\\1".$domain_prefix."'.crack_stack_get_number('\\1\\3\\4\\5', ".$domain_count.").'.\\3\\4\\5'", $html);
				
		return $html;
	}
	
	
	function __toString()
	{
		return $this->html;
	}	
	
}


function crack_stack_get_number($url, $count)
{
	$url 	= md5($url);		
	$num 	= floor( hexdec(substr($url, 0, 1)) / 16 * $count );		
	return  $num;
}


function crack_stack_finish($html)
{
	return new Crack_Stack($html);
}


function crack_stack_start()
{
	ob_start('crack_stack_finish');
}

add_action('get_header', 'crack_stack_start');



/**
 * Settings Page
 * ============================================================================
 */


add_action('admin_menu', 'crack_stack_create_menu');


function crack_stack_create_menu() 
{
	add_menu_page(
		__('Crack Stack', 'crack-stack'), 
		__('Crack Stack', 'crack-stack'), 
		'administrator', __FILE__, 'crack_stack_settings_page',
		plugins_url('/images/icon.png', __FILE__));

	add_action( 'admin_init', 'crack_stack_register_settings' );
}


function crack_stack_register_settings() 
{
	register_setting( 'crack-stack-settings-group', CRACK_STACK_DOMAIN_COUNT );
	register_setting( 'crack-stack-settings-group', CRACK_STACK_DOMAIN_PREFIX );
	register_setting( 'crack-stack-settings-group', CRACK_STACK_FILE_EXT );	
}


function crack_stack_settings_page() 
{
	$count = get_option( CRACK_STACK_DOMAIN_COUNT );	
	if (empty($count)) {
		update_option(CRACK_STACK_DOMAIN_COUNT, '4');
	}
	
	$prefix = get_option( CRACK_STACK_DOMAIN_PREFIX );
	if (empty($prefix)) {
		update_option(CRACK_STACK_DOMAIN_PREFIX, 'domain');
	}
	
	$ext = get_option( CRACK_STACK_FILE_EXT );
	if (empty($ext)) {
		update_option(CRACK_STACK_FILE_EXT, 'jpg, png, gif, bmp, svg, ico, css, js');
	}
	
?>

	<div class="wrap">
		<h2>Crack Stack Settings</h2>
		
		<p><small>
		Some modern browsers (such as Mozilla Firefox and Internet Explorer) 
		when the page loads restrict the number of simultaneous connections to a 
		single host. 
		
		This leads to the creation of a queue of connections and slows speed of 
		download site content.
		
		And the more you have external elements on the page, the longer the queue 
		and more time opening the page.
		</small></p>
		
		<p><small>
		This plugin allows you to automatically reallocate external resources at 
		various pseudo-domains. 
		
		This removes the limitation on the number of 
		connections and increases the speed of loading pages.		
		</small></p>
		
		<p><small>
		In some cases this can speed up the download images, CSS-styles and 
		JS-scripts in 4 times!
		
		Compare the time downloading images 
		<a href="<?php echo plugins_url('/images/before.gif', __FILE__)?>" target="_blank">before</a>
		and 
		<a href="<?php echo plugins_url('/images/after.gif', __FILE__)?>" target="_blank">after</a>
		using the plugin.
		</small></p>
		
		<p><small>
		<strong>Warning!</strong> Your server should correctly convert the queries to 
		non-existent domains, discarding the prefix added by the plugin. 
		Please, before using the plugin configure your server.
		</small></p>
		
		<form method="post" action="options.php">
		
		    <?php settings_fields( 'crack-stack-settings-group' ); ?>
		    
		    <table class="form-table">
		        <tr valign="top">
			        <th scope="row">Count of pseudo-domains:</th>
			        <td>
			        	<input type="text" name="<?php echo CRACK_STACK_DOMAIN_COUNT; ?>" value="<?php echo get_option(CRACK_STACK_DOMAIN_COUNT); ?>" />
			        	<p><small><strong>Please enter a number from 1 to 16.</strong><br>
						The number of domains should be approximately 
						equal to the number of requests from the page for the 
						external elements divided by 6 and rounded to a larger 
						value.<br> 
						For example, if you have 10 requests, then use two 
						pseudo-domain. If the 15 requests, then use a 3 domain 
						and so on.
						</small></p>
			        </td>
		        </tr>
		         
		        <tr valign="top">
			        <th scope="row">Domain prefix:</th>
			        <td>
			        	<input type="text" name="<?php echo CRACK_STACK_DOMAIN_PREFIX; ?>" value="<?php echo get_option(CRACK_STACK_DOMAIN_PREFIX); ?>" />
			        	<p><small><strong>Please enter a string consisting of 
			        	characters allowed for use in domain names.</strong>
						</small></p>
			        </td>
		        </tr>
		        
		        <tr valign="top">
			        <th scope="row">File extensions:</th>
			        <td>
			        	<input type="text" class="regular-text" name="<?php echo CRACK_STACK_FILE_EXT; ?>" value="<?php echo get_option(CRACK_STACK_FILE_EXT); ?>" />
			        	<p><small><strong>Please specify a comma-separated file 
			        	extensions of external elements.</strong>
						</small></p>
			        </td>
		        </tr>
		    </table>
		    
		    <p class="submit">
		    	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		    </p>
		
		</form>
	</div>
	
<?php 
} 

