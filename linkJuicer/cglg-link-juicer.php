<?php
/*
Plugin Name: CG Link Juicer
Plugin URI: http://contentgenius.com/wordpress-plugins/cg-link-juicer
Description: Premium SEO blog network membership / management system.
Version: 1.0
Author: Sheldon Smith
Author URI: http://www.iwebcreations.co.uk
License: GPLv2
*/


// local path to plugin folder
//echo plugin_dir_path(__FILE__);

// plugins url path
// echo plugins_url('gfx', __FILE__);

//full wp includes url path eg: http://example.com/wp-includes
//string Optional param. $path will be appended. 
//Since:
//2.6.0 
//echo includes_url($path);

// full wp-content directory eg: http://example.com/wp-content
// optional string param: $path will be appended.
//content_url($path);

// full wp-admin directory eg: http://example.com/wp-admin
// optional string param: $path will be appended.
//admin_url($path);

// Site URL - Retrieve the site url for the current site 
// eg http://example.com
// optional param : $path - appends to the site url
// optional param : $scheme to give the site url context. Currently 'http','https', 'login', 'login_post', or 'admin'.

//site_url($path, $scheme)
//echo site_url('test', 'https');
//$cglg_plugin_path =
define('PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define('CGLG_CSS', plugins_url( 'css/tiny_mce.css' ) );
$cglg_plugin_path = plugin_dir_path( __FILE__ );
$cglg_plugin_url = plugins_url('cglg-link-juicer.php', __FILE__ );

// Include all Admin additonal PHP plugin files.
require_once( PLUGIN_PATH . 'admin/cglg-admin.php');

// Include members additional php plugin files.
require_once( PLUGIN_PATH . 'members/add-projects.php' );
require_once( PLUGIN_PATH . 'members/add-url.php' );
require_once( PLUGIN_PATH . 'members/write-post.php' );
require_once( PLUGIN_PATH . 'members/projects.php' );
require_once( PLUGIN_PATH . 'members/submissions.php' );

//require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//require_once( plugins_url( 'admin/cglg-admin.php', __FILE__ ) ); 


// Register all Plugin Hooks activation, deactivation & uninstall
register_activation_hook( __FILE__, 'cglg_linkjuicer_install' );
register_deactivation_hook( __FILE__, 'cglg_linkjuicer_deactivate' );
//register_uninstall_hook( __FILE__, 'cglg_linkjuicer_uninstall' );

// register shortcodes
add_shortcode( 'cglg-members-page', 'cglg_members_page');

function cglg_linkjuicer_install()
{
	
	// Verify wordpress version compatibility
	if( version_compare( get_bloginfo( 'version' ), '3.2.1', '<') )
	{
		//deactivate_plugins( basename( __FILE__ ) );// Deactivate our plugin
		br_trigger_error('This Plugin Requires WordPress 3.2.1 or Higher Please Update Your WordPress Installation!', E_USER_ERROR);
		//wp_die("This Plugin Requires WordPress 3.2.1 or Higher Please Update Your WordPress Installation!", 'WP Version Incompatible');
	}
	else 
	{	
		global $wpdb;
		
		// extended users table
		$cglg_eu_table = $wpdb->prefix . "cglg_extended_users";
		$sql = "CREATE TABLE `$cglg_eu_table` (
		  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
		  `user_approvedtime` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  `membership_lvl` int(2) DEFAULT '0' NOT NULL,
		  `monthly_credits` int(32) DEFAULT '0' NOT NULL,
		  `writer_credits` int(32) DEFAULT '0' NOT NULL,
		  UNIQUE KEY ID (`ID`)
	    );";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		//additonal option so that we can have a version of db for updates in future
		add_option( 'cglg_database_version', '1.0' );
		
		// posts table
		$cglg_posts = $wpdb->prefix . "cglg_posts";
		$sql = "CREATE TABLE `$cglg_posts` (
		  `id` int(64) NOT NULL AUTO_INCREMENT,
		  `pt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `pt_title` varchar(64) NOT NULL,
		  `pt_content` longtext NOT NULL,
		  `pt_keys` varchar(64) NOT NULL,
		  `pt_delay` int(6) NOT NULL,
		  `self_written` int(1) NOT NULL,
		  `project_id` int(64) NOT NULL,
		  `domain_id` int(64) NOT NULL,
		  `category_id` int(64) NOT NULL,
		  `pt_owner_id` bigint(20) NOT NULL,
		  `pt_status` int(1) NOT NULL,
		  PRIMARY KEY (`id`)
	    );";
	
	   	dbDelta($sql);
		
	   // domains table
		$cglg_domains = $wpdb->prefix . "cglg_domains";
	   $sql = "CREATE TABLE `$cglg_domains` (
	   	 `d_id` int(64) NOT NULL AUTO_INCREMENT,
		 `d_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		 `project_id` int(64) NOT NULL,
		 `category_id` int(64) NOT NULL,
		 `domain` varchar(128) NOT NULL,
		 `d_status` int(1) NOT NULL,
		 `d_owner_id` bigint(20) NOT NULL,
		 PRIMARY KEY (`d_id`)
	    );";
	
	  	dbDelta($sql);

	  	// projects table
	   	$cglg_projects = $wpdb->prefix . "cglg_projects";
	   $sql = "CREATE TABLE `$cglg_projects` (
		 `p_id` int(64) NOT NULL AUTO_INCREMENT,
		 `p_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		 `p_name` varchar(64) NOT NULL,
		 `p_status` int(2) NOT NULL,
		 `p_desc` longtext NOT NULL,
		 `p_owner_id` bigint(20) NOT NULL,
		 PRIMARY KEY (`p_id`)
	    );";
	
	   	dbDelta($sql);
	   	
	   	// logs table
	   	$cglg_logs = $wpdb->prefix . "cglg_logs";
	   $sql = "CREATE TABLE `$cglg_logs` (
		 `l_id` int(64) NOT NULL AUTO_INCREMENT,
		 `l_time` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		 `user_id` bigint(20) NOT NULL,
		 `activity` VARCHAR(64) NOT NULL,
		 `comments` longtext DEFAULT '' NOT NULL,
		 PRIMARY KEY (`l_id`)
	    );";

   		dbDelta($sql);
		
   		// Categories table
	  	$cglg_cat = $wpdb->prefix . "cglg_categories";
	   $sql = "CREATE TABLE `$cglg_cat` (
		 `cat_id` int(64) NOT NULL AUTO_INCREMENT,
		 `cat_name` VARCHAR(64) NOT NULL,
		 PRIMARY KEY (`cat_id`)
	    );";

   		dbDelta($sql);  
	}
}

/* ACTION HOOKS
 * $tag - The name of the action hook your function executes on.
 * $function - The name of your function that WordPress calls.
 * $priority - An integer that represents the order in which the action is fired.
 * When no value is given it defaults to 10. The lower the number, the earlier the
 * function will be called.
 * $accepted_args - The number of parameters the action hook will pass to your funtion. 
 * By default, it passes only one paramater
 */
// add_action($tag, $function, $priority, $accepted_args);

/* FILTER
 * 
 * 
 * 
 * 
 */

// Add top level menu
add_action('admin_menu', 'cglg_create_menu' );
function cglg_create_menu()
{
	// create custom top level menu for link juicer
	// add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $position)
	add_menu_page( 'Link Juicer Settings', 'Link Juicer', 'manage_options', __FILE__, 'cglg_linkjuicer_settings_page' );
	
	// create custom submenus for link juicer
	// add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function)
	add_submenu_page( __FILE__, 'Post Workflow', 'Post WorkFlow', 'manage_options', 'cglg_post_workflow_page', 'cglg_post_workflow_page' );
	
	add_submenu_page( __FILE__, 'URL Workflow', 'URL WorkFlow', 'manage_options', 'cglg_url_workflow_page', 'cglg_url_workflow_page' );
	
	add_submenu_page( __FILE__, 'Blog Management', 'Blog Management', 'manage_options', 'cglg_blog_management_page', 'cglg_blog_management_page' );
	
	add_submenu_page( __FILE__, 'Member Management', 'Members', 'manage_options', 'cglg_member_management_page', 'cglg_member_management_page' );
	
	add_submenu_page( __FILE__, 'Post Management', 'Post Management', 'manage_options', 'cglg_post_management_page', 'cglg_post_management_page' );
	
	add_submenu_page( __FILE__, 'URL Management', 'URL Management', 'manage_options', 'cglg_url_management_page', 'cglg_url_management_page' );
	
	add_submenu_page( __FILE__, 'Logs', 'Logs', 'manage_options', 'cglg_logs_page', 'cglg_logs_page' );
	
	add_submenu_page( __FILE__, 'Stats', 'Stats', 'manage_options', 'cglg_stats_page', 'cglg_stats_page' );
	
}


// Deactivate plugin 
function cglg_linkjuicer_deactivate()
{
	
}

 /**
  * Activation Error Function
  */
function br_trigger_error($message, $errno) 
{
    if(isset($_GET['action']) && $_GET['action'] == 'error_scrape') 
    {
        echo '<strong>' . $message . '</strong>';
        exit;
    }
    else 
    {
        trigger_error($message, $errno);
    }
}

/**
 * Adds jQuery Validation script on page.
 */
function cglg_vc_scripts() {
	if( is_user_logged_in() && is_page() ) {
		wp_enqueue_script(
			'jquery-validate',
			plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js',
			array('jquery'),
			'1.8.1',
			false
		);
		
		wp_enqueue_script(
			'tiny-mce',
			plugin_dir_url( __FILE__ ) . 'js/tiny_mce/tiny_mce.js',
			array(),
			'3.4.7'
		); 

		wp_enqueue_style(
			'jquery-validate',
			plugin_dir_url( __FILE__ ) . 'css/style.css',
			array(),
			'1.0'
		);
	}
}
add_action('template_redirect', 'cglg_vc_scripts');

/**
 * Initiate the script 
 * Calls the validation options on the members form.
 */
function cglg_vc_init() { if ( is_page() && is_user_logged_in() ) {  ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.auto-focus:first').focus();
            
            //  Initialize auto-hint fields
            $('INPUT.auto-hint, TEXTAREA.auto-hint').focus(function(){
                if($(this).val() == $(this).attr('title')){ 
                    $(this).val('');
                    $(this).removeClass('auto-hint');
                }
            });
            
            $('INPUT.auto-hint, TEXTAREA.auto-hint').blur(function(){
                if($(this).val() == '' && $(this).attr('title') != ''){ 
                    $(this).val($(this).attr('title'));
                    $(this).addClass('auto-hint'); 
                }
            });
            
            $('INPUT.auto-hint, TEXTAREA.auto-hint').each(function(){
                if($(this).attr('title') == ''){ return; }
                if($(this).val() == ''){ $(this).val($(this).attr('title')); }
                else { $(this).removeClass('auto-hint'); } 
            });

            // Clear fields that have title set as content on submission.
            $('form').submit(function(){
            	$('.auto-hint').each(function(){
            	if($(this).attr('title') == $(this).val()){$(this).val('');}
            	});
            });
         	// without page reload autofill select box depending on first select box value.
			$("#cglg_wp_projName").change(function() {
			$("#cglg_projDomain").load("<?php echo plugin_dir_url( __FILE__ ) . 'members/write-post.php?projname=' ?>" + $("#cglg_wp_projName").val());
			});
			
			$('#addProjects').validate({
				rules: {
				p_name: {
					required: true,
					minlength: 4
				},
				p_status: {
					required: true
				},
				p_desc: {
					minlength: 20
				}
			},
			messages: {
				projName: {
							required: "This field is required.",
							minlength: "Please enter at least 4 characters."
			},
				projStatus: "This field is required.",
				projDesc: "Please enter at least 20 characters for your projects description."
				
			}
			});
			$('#cglg_addURL_form').validate({
				rules: {
				cglg_au_projName: {
					required: true
				},
				cglg_au_category: {
					required: true
				},
				cglg_addURL: {
					required: true,
					url: true
				}
			},
			messages: {
				cglg_au_projName:  "This field is required.",
				cglg_au_category:  "This field is required.",
				cglg_addURL: {
						required: "This field is required.",
						url: "Invalid URL! TLD or sub domain only"
				}
			}
			});
			$('#cglg_write_post').validate({
				rules: {
					cglg_wp_projName: {
						required: true
					},
					cglg_projDomain: {
						required: true
					},
					cglg_wp_category: {
						required: true
					},
					cglg_postTitle: {
						required: true,
						minlength: 4,
						maxlength: 65
					},
					cglg_postBody: {
						required: true,
						minlength: 150
					},
					cglg_postDelay: {
						digits: true
					}
				},
				messages: {
					cglg_wp_projName: "This field is required!",
					cglg_projDomain: "This field is required!",
					cglg_wp_category: "This field is required!",
					cglg_postTitle: {
						required: "This field is required",
						minlength: "Minimum of 4 characters are needed for the post title",
						maxlength: "Most major search engines only index the first 60-65 characters"
					},
					cglg_postBody: {
						required: "This field is required",
						minlength: "Your article must contain 150 characters or more"
					},
					cglg_postDelay: {
						digits: "Digits only"
					}
				}
			});	
			
		});
	</script> 
<?php  }
}
add_action('wp_footer', 'cglg_vc_init', 999);

// the shortcode callback function that will replace [cglg-members-page page = ""]
function cglg_members_page ( $attr )
	{
		//[members-page page = ""]
		extract( shortcode_atts( array (
		'page' => 'page not found' //default value
		), $attr ) );

		switch ($page)
		{
			case 'members area':
				$function = cglg_members_dashboard( $page );
				break;
			
			case 'dashboard':
				$function = cglg_members_dashboard( $page );
				break;
				
			case 'add projects':
				$function = cglg_build_projects_form( $page );
				break;
				
			case 'add url':
				$function = cglg_build_add_url_form( $page );
				break;
				
			case 'write post':
				$function = cglg_build_write_post_form( $page );
				break;
				
			case 'projects':
				$function = cglg_build_projects_tbl( $page );
				break;
				
			case 'submissions':
				$function = cglg_build_submissions_tbl( $page );
				break;
					
			default:
				$function = cglg_build_projects_form( $page );
			break;
	}
	
	return $function;
}

//process add members project form
function cglg_apj_form_process ()
{
	
}
//add_action('init', 'cglg_apj_form_process');

// Maximum character length for users input.
function character_length( $string, $max )
{
	//$search  = array("\n","\r\n","\r");
	//$textarea = str_replace($search, "", $string);
	sanitize_text_field( $string );
	if(strlen($string) >= $max)
	{
		return true;
	}
	else
	{
		return false;
	}

}
// Maximum character length for users input.
function min_character_length( $string, $min )
{
	//$search  = array("\n","\r\n","\r");
	//$textarea = str_replace($search, "", $string);
	sanitize_text_field( $string );
	if(strlen($string) < $min)
	{
		return true;
	}
	else
	{
		return false;
	}

}
/** 
 * Counts Number of Words in content
 * @param $post_content - a string of text to count
 *
 * @uses strip_tags(): strips all html tags from content
 * @link http://uk3.php.net/manual/en/function.strip-tags.php
 * 
 * @uses count(): Counts all elements in an array, or something in an object.
 * @link http://uk3.php.net/manual/en/function.count.php
 * 
 * @uses explode(): first paramater is called a delimeter.&nbsp 
 * It Returns an array of strings, each of which is a substring of string
 * formed by splitting it on boundaries formed by the string delimiter
 * @link http://uk3.php.net/manual/en/function.explode.php	 
 * 
 * @return Returns the number of words in string that was passed to function.
 */
function cglg_word_count( $wysiwyg_content = '' )
{	
	$raw_content = strip_tags( $wysiwyg_content );
	return sizeof( explode(" ", $raw_content) );
}

function cglg_link2words_check( $wysiwyg_content = ''  )
{
	// matches all anchor links in wysiwyg content
	preg_match_all( "#<a\b[^>]*>.*?</a>#i", $wysiwyg_content, $matches, PREG_SET_ORDER );
	// counts num of matches found
	$num_of_links = count( $matches );
	// get the word count from wysiwyg
	$word_count = cglg_word_count( $wysiwyg_content);
	// find percentage of links in wysiwyg
	$link2words_perc = round(( $num_of_links / $word_count ) * 100, 3);
	// 1 Link out of 150 words = 0.667% rounds to third decimal point
	// if link2words perc is greater then 0.667% return true.
	// the reason i rounded to 3rd decimal point is because 2 is not accurate enough
	if( $link2words_perc > round(( 1 / 150 ) * 100, 3) )
	{
		return true;
	}
	else 
	{
		return false;
	}
}
function cglg_anchor_link_check( $wysiwyg_content = '', $domain_names )
{
	
	/*<a\b.*?href=['|\"]([^\"']+)['|\"].*?>.*?</a>#i*/

	preg_match_all( "#<a\b[^>]*>.*?</a>#i", $wysiwyg_content, $anchors, PREG_SET_ORDER );
	$num_of_matches = count($anchors);
	// preg_match_all returns 2 dimensional numberd array 
	// hence the reason for 2 foreach loops. 
	// because preg_match 2nd parameter expects a string
	/*
	foreach($domain_names as $domain_name)
	{
	 $domain = remove_http($domain_name->domain);
	 $strip_url = str_replace('www.', '', $domain);
	 preg_match('#http://.*?\.' . $strip_url . '|http://' . $strip_url . '#i', 'http://irock.com/kkkiik.html', $url_match);
	 return $url_match;
	}
	*/
	foreach ($anchors as $anchor)
	{
		foreach($anchor as $url)
		{
			foreach($domain_names as $domain_name)
			{
				$domain = cglg_remove_http($domain_name->domain);
				$strip_url = str_replace('www.', '', $domain);
				preg_match('#http://.*?\.' . $strip_url . '|http://' . $strip_url . '#i', $url, $url_match);
				if($url_match)
				{
					$exists++;
				}
			}
		}
	}
	if($exists != $num_of_matches)
	{
		return true;
	}
	else 
	{
		return false;
	}
}
function cglg_img_check($wysiwyg_content = '')
{
	preg_match_all("#<img[^']*?src=\"([^']*?)\"[^']*?>#i", $wysiwyg_content, $img_matches, PREG_SET_ORDER );
	return count($img_matches);
}
function cglg_kw_check($keywords = '')
{
	return sizeof( explode(",", $keywords) );
}
function cglg_remove_http($url = '')
{
return(str_replace(array('http://','https://'), '', $url));
}
/** Dynamic Custom Prepared MySQL Select statement
 *  @uses Used if you need to create a dynamic custom query 
 *  where you cannot hardcode every component.
 *  
 *  @param string $table_name - expects string without prefix
 *  @param int $current_user_id - current logged in users id 
 *  @param constant $output_type - Optional output format  ARRAY_A, ARRAY_N or OBJECT
 *  
 *  @return mixed defined by $output_type it either returns results 
 *  as an object, associate array or numerically indexed.
 */
function cglg_prep_projname_select( $current_user_id, $output_type = OBJECT )
{
	GLOBAL $wpdb;
	
	$cglg_projects_db_table = $wpdb->prefix . 'cglg_projects';
	$sql = "SELECT `p_id`, `p_name` 
			FROM $cglg_projects_db_table
			WHERE `p_owner_id` = %d ";
	$safe_sql = $wpdb->prepare( $sql, $current_user_id );
	$results = $wpdb->get_results( $safe_sql, $output_type );
	
	return $results;
}

function cglg_cat_db_select ( $output_type = OBJECT )
{
	GLOBAL $wpdb; 
	$cglg_categories_db_table = $wpdb->prefix . 'cglg_categories';
	$sql = "SELECT `cat_id`, `cat_name` 
			FROM $cglg_categories_db_table";
	$results = $wpdb->get_results( $sql, $output_type );
	
	return $results;
}
function cglg_proj_domain_select ( $current_user_id, $project_id, $output_type = OBJECT )
{
	GLOBAL $wpdb; 
	
	$cglg_domains_db_table = $wpdb->prefix . 'cglg_domains';
	$sql = "SELECT `d_id`, `domain`
			FROM $cglg_domains_db_table
			WHERE `d_owner_id` = %d
			AND `project_id` = %d
			AND `d_status` = 0";
	$safe_sql = $wpdb->prepare( $sql, $current_user_id, $project_id );
	
	$results = $wpdb->get_results( $safe_sql, $output_type );
	
	return $results;
}
//Insert Write Post Values into DB
function cglg_insert_posts( $write_post_values = array())
{
	GLOBAL $wpdb;
	$cglg_posts_tbl_name = $wpdb->prefix . 'cglg_posts';
	$results = $wpdb->insert($cglg_posts_tbl_name, $write_post_values, array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d'));
	return $results;
}
/** replace the php empty() function 
 *  @uses in situations where you want 0 and "0" not to be considered empty.
 *  @example considers the following values as empty:
 *  an unset variable -> empty
 *  null -> empty
 *  0 -> NOT empty
 *  "0" -> NOT empty
 *  false -> empty
 *  true -> NOT empty
 *  'string value' -> NOT empty
 *  "    " (white space) -> empty
 *  array() (empty array) -> empty
 *  @param bool $allow_false - setting this to true will make the function consider a boolean value of false as NOT empty.&nbsp;
 *  This parameter is false by default.
 *  @param bool $allow_ws - setting this to true will make the function consider a string with nothing but white space as NOT empty.&nbsp;
 *  This parameter is false by default.
 */
function is_empty($var, $allow_false = false, $allow_ws = false) {
    if (!isset($var) || is_null($var) || ($allow_ws == false && trim($var) == "" && !is_bool($var)) || ($allow_false === false && is_bool($var) && $var === false) || (is_array($var) && empty($var))) {   
        return true;
    } else {
        return false;
    }
}