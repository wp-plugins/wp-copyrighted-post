<?php
/*
Plugin Name: Copyrighted Post
Plugin URI: http://www.simplelib.com/?p=166
Description: Adds copyright notice in the end of each post of your blog. Visit <a href="http://www.simplelib.com/">SimpleLib blog</a> for more details.
Version: 1.0.8
Author: minimus
Author URI: http://blogcoding.ru
*/

/*  Copyright 2009 - 2010, minimus  (email : minimus@simplelib.com)

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

if ( !class_exists( 'CopyrightedPost' ) ) {
	class CopyrightedPost {
		var $adminOptionsName = "CopyrightedPostAdminOptions";
		var $cpInitOptions = array(
			'owner' => 'author',
			'singlePost' => 'true',
			'crString' => 'All rights reserved.',
			'crStringEx' => ''
			);
			
		function CopyrightedPost() {
			//load language
			$plugin_dir = basename( dirname( __FILE__ ) );
			if ( function_exists( 'load_plugin_textdomain' ) ) 
				load_plugin_textdomain( 'wp-copyrighted-post', false, $plugin_dir );
				
			//Actions and Filters
			add_action('admin_menu', array(&$this, 'regAdminPage'));
			add_action('activate_wp-copyrighted-post/wp-copyrighted-post.php',  array(&$this, 'onActivate'));
			add_action('deactivate_wp-copyrighted-post/wp-copyrighted-post.php', array(&$this, 'onDeactivate'));
			add_filter('the_content', array(&$this, 'addCopyright'), 7);
		}
		
		function onActivate() {
			$cpAdminOptions = $this->getAdminOptions();
			update_option($this->adminOptionsName, $cpAdminOptions);
		}
		
		function onDeactivate() {
			delete_option($this->adminOptionsName);
		}
		
		//Returns an array of admin options
		function getAdminOptions() {						
			$cpAdminOptions = $this->cpInitOptions;
			$cpAdminOptions['crString'] = __("All rights reserved.", 'wp-copyrighted-post');
			$cpOptions = get_option($this->adminOptionsName);
			if (!empty($cpOptions)) {
				foreach ($cpOptions as $key => $option) {
					$cpAdminOptions[$key] = $option;
				}
			}
			return $cpAdminOptions;			
		}
		
		function addCopyright( $content ) {
			$cpOptions = $this->getAdminOptions();
			if ( is_single() | ( 'false' === $cpOptions['singlePost'] ) | is_feed() ) {
				$postId = get_the_ID();
				$postData = get_post($postId, ARRAY_A);
				$postDate = explode( '-', $postData['post_date'] );
				$postModifed = explode( '-', $postData['post_modified'] );
				$content .= "\n<p style='text-align:left'>&copy; ".( ( $postDate[0] === $postModifed[0] ) ? $postDate[0] : $postDate[0]." - ".$postModifed[0] ).", <a href='".get_bloginfo('url')."'>".(($cpOptions['owner'] === 'blog') ? get_bloginfo('name') : get_the_author())."</a>. ".htmlspecialchars_decode( $cpOptions['crString'] )." ".htmlspecialchars_decode( $cpOptions['crStringEx'] )."</p>";
			}
			return $content;
		}
		
		function regAdminPage() {
			if (function_exists('add_options_page')) {
				add_options_page(__('Copyrighted Post', 'wp-copyrighted-post'), __('Copyrighted Post', 'wp-copyrighted-post'), 8, basename(__FILE__), array(&$this, 'printAdminPage'));
			}
		}
		
		function printAdminPage() {
			$cpOptions = $this->getAdminOptions();
			$options = array (
				array(	
					"name" => __('Basic Settings', 'wp-copyrighted-post'),
					"disp" => "startSection" ),
				
				array(	
					"name" => __("Copyright owner", "wp-copyrighted-post"),
					"desc" => __("Selecting \"Blog Name\"(\"Author Name\") will show name of blog (author of post) as copyright owner. If your blog is multi author blog, use \"Author name\" to highlight copyright owner.", 'wp-copyrighted-post'),
					"id" => "owner",
					"disp" => "radio",
					"options" => array( 'blog' => __("Blog Name", "wp-copyrighted-post"), 'author' => __("Author Name", "wp-copyrighted-post"))),
					
				array(	
					"name" => __("Define Copyright Notice String", "wp-copyrighted-post"),
					"desc" => __("This is a phrase that originated in copyright law as part of copyright notices. English: \"All rights reserved.\"", 'wp-copyrighted-post'),
					"id" => "crString",
					"disp" => "text",
					"textLength" => '450'),
					
				array(	
					"name" => __("Define Extended Copyright Notice String", "wp-copyrighted-post"),
					"desc" => __("This is extended copyright notice string. You can place additional information here.", 'wp-copyrighted-post'),
					"id" => "crStringEx",
					"disp" => "text",
					"textLength" => '650'),
				
				array(	
					"name" => __("Display in Single Post Only", "wp-copyrighted-post"),
					"desc" => __("Select \"Yes\", if you want display copyright notice only at the end of post in single post viewing mode.", 'wp-copyrighted-post'),
					"id" => "singlePost",
					"disp" => "radio",
					"options" => array( 'true' => __("Yes", "wp-copyrighted-post"), 'false' => __("No", "wp-copyrighted-post"))),
				
				array(
					"disp" => "endSection" )
				);
				
				if (isset($_POST['update_cpSettings'])) {
				foreach ($options as $value) {
					if (isset($_POST[$value['id']])) {
						if ( $value['disp'] === 'text' ) $cpOptions[$value['id']] = htmlspecialchars(stripslashes($_POST[$value['id']]));
						else $cpOptions[$value['id']] = $_POST[$value['id']];
					}
				}
				update_option($this->adminOptionsName, $cpOptions);
				?>
<div class="updated"><p><strong><?php _e("Copyrighted Post Settings Updated.", "wp-copyrighted-post");?></strong></p></div>        
				<?php
			}
			 ?>
<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<div id="icon-options-general" class="icon32"></div>
<h2><?php _e("Copyrighted Post Settings", "wp-copyrighted-post"); ?></h2>
			<?php foreach ($options as $value) {
				switch ( $value['disp'] ) {
					case 'startSection':
						?>
<div id="poststuff" class="ui-sortable">
<div class="postbox opened">
<h3><?php echo $value['name']; ?></h3>
	<div class="inside">
						<?php
						if (!is_null($value['desc'])) echo '<p>'.$value['desc'].'</p>';
						break;
						
					case 'endSection':
						?>
</div>
</div>
</div>
						<?php
						break;
						
					case 'text':
						if ( is_null($value['textLength']) ) $textLengs = '55';
						else $textLengs = $value['textLength'];
						?>
<p><strong><?php echo $value['name']; ?></strong>
<br/><?php echo $value['desc']; ?></p>
<p><input type="text" style="height: 22px; font-size: 11px; width: <?php echo $textLengs;?>px" value="<?php echo $cpOptions[$value['id']] ?>" name="<?php echo $value['id'] ?>" id="<?php echo $value['id'] ?>" /></p>
						<?php
						break;
						
					case 'radio':
						?>
<p><strong><?php echo $value['name']; ?></strong>
<br/><?php echo $value['desc']; ?></p><p>				
						<?php
						foreach ($value['options'] as $key => $option) { ?>
<label for="<?php echo $value['id'].'_'.$key; ?>"><input type="radio" id="<?php echo $value['id'].'_'.$key; ?>" name="<?php echo $value['id']; ?>" value="<?php echo $key; ?>" <?php if ($cpOptions[$value['id']] == $key) { echo 'checked="checked"'; }?> /> <?php echo $option;?></label>&nbsp;&nbsp;&nbsp;&nbsp;
						<?php }
						?>
</p>			
						<?php 
						break;
						
					case 'select':
						?>
<p><strong><?php echo $value['name']; ?></strong>
<br/><?php echo $value['desc']; ?></p>
<p><select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
						<?php foreach ($value['options'] as $option) { ?>
<option value="<?php echo $option; ?>" <?php if ( $cpOptions[$value['id']] == $option) { echo ' selected="selected"'; }?> ><?php echo $option; ?></option>
						<?php } ?>
</select></p>
						<?php
						break;
					
					default:
						
						break;
				}
			}
			?>
<div class="submit">
	<input type="submit" class='button-primary' name="update_cpSettings" value="<?php _e('Update Settings', 'wp-copyrighted-post') ?>" />
</div>
</form>
</div>      
      <?php
			
		} // End of function printAdminPage
	} // end class
} // end if

if (class_exists("CopyrightedPost")) {
	$minimus_copyrighted_post = new CopyrightedPost;
}
?>