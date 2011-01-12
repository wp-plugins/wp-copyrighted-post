<?php
/*
Plugin Name: Copyrighted Post
Plugin URI: http://www.simplelib.com/?p=166
Description: Adds copyright notice in the end of each post of your blog. Visit <a href="http://www.simplelib.com/">SimpleLib blog</a> for more details.
Version: 1.1.10
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
			
		function __construct() {
			define('WCP_DOMAIN', 'wp-copyrighted-post');
      define('WCP_OPTIONS', 'CopyrightedPostAdminOptions');
      
      $plugin_dir = basename( dirname( __FILE__ ) );
			if ( function_exists( 'load_plugin_textdomain' ) ) 
				load_plugin_textdomain( WCP_DOMAIN, false, $plugin_dir );
				
			add_action('admin_init', array(&$this, 'initSettings'));
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
			$cpAdminOptions['crString'] = __("All rights reserved.", WCP_DOMAIN);
			$cpOptions = get_option(WCP_OPTIONS);
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
    
    function initSettings() {
      add_settings_section("wcp_section", __("Copyright Settings", WCP_DOMAIN), array(&$this, "drawSection"), "reading");
      add_settings_field('owner', __("Copyright owner", WCP_DOMAIN), array(&$this, 'drawRadioOption'), 'reading', 'wcp_section', array('optionName' => 'owner', 'description' => __('Selecting "Blog Name"("Author Name") will show name of blog (author of post) as copyright owner. If your blog is multi author blog, use "Author name" to highlight copyright owner.', WCP_DOMAIN), 'options' => array( 'blog' => __('Blog Name', WCP_DOMAIN), 'author' => __('Author Name', WCP_DOMAIN))));
      add_settings_field('crString', __("Define Copyright Notice String", WCP_DOMAIN), array(&$this, 'drawTextOption'), 'reading', 'wcp_section', array('optionName' => 'crString', 'description' => __('This is a phrase that originated in copyright law as part of copyright notices. English: "All rights reserved."', WCP_DOMAIN), 'width' => 95));
      add_settings_field('crStringEx', __("Define Extended Copyright Notice String", WCP_DOMAIN), array(&$this, 'drawTextOption'), 'reading', 'wcp_section', array('optionName' => 'crStringEx', 'description' => __('This is extended copyright notice string. You can place additional information here.', WCP_DOMAIN), 'width' => 95));
      add_settings_field('singlePost', __("Display in Single Post Only", WCP_DOMAIN), array(&$this, 'drawRadioOption'), 'reading', 'wcp_section', array('optionName' => 'singlePost', 'description' => __('Select "Yes", if you want display copyright notice only at the end of post in single post viewing mode.', WCP_DOMAIN), 'options' => array( 'true' => __('Yes', WCP_DOMAIN), 'false' => __('No', WCP_DOMAIN))));
      
      register_setting('reading', WCP_OPTIONS, array(&$this, 'sanitizeSettings'));
    }
    
    function drawSection() {
      echo __('Parameters of copyright notice in the end of each post of your blog (Copyrighted Post plugin).', WCP_DOMAIN);
    }
    
    function drawRadioOption( $args ) {
      $optionName = $args['optionName'];
      $options = $args['options'];
      $settings = $this->getAdminOptions();
      
      foreach ($options as $key => $option) {
      ?>
        <label for="<?php echo $optionName.'_'.$key; ?>">
          <input type="radio" 
            id="<?php echo $optionName.'_'.$key; ?>" 
            name="<?php echo WCP_OPTIONS.'['.$optionName.']'; ?>" 
            value="<?php echo $key; ?>" <?php checked($key, $settings[$optionName]); ?> /> 
          <?php echo $option;?>
        </label>&nbsp;&nbsp;&nbsp;&nbsp;        
      <?php
      }
      echo "<p><em>{$args['description']}</em></p>";
    }
    
    function drawTextOption( $args ) {
      $optionName = $args['optionName'];
      $settings = $this->getAdminOptions();
      $width = $args['width'];
      ?>
        <input id="<?php echo $optionName; ?>"
          name="<?php echo WCP_OPTIONS.'['.$optionName.']'; ?>"
          type="text"
          value="<?php echo $settings[$optionName]; ?>" 
          style="height: 22px; font-size: 11px; <?php if(!empty($width)) echo 'width: '.$width.'%;' ?>" />
      <?php
      echo "<p><em>{$args['description']}</em></p>";
    }
    
    function sanitizeSettings($input) {
      $output = $input;
      $output['crString'] = htmlspecialchars(stripslashes($input['crString']));
      $output['crStringEx'] = htmlspecialchars(stripslashes($input['crStringEx']));
      return $output;
    }
	} // end class
} // end if

if (class_exists("CopyrightedPost")) {
	$minimus_copyrighted_post = new CopyrightedPost;
}
?>