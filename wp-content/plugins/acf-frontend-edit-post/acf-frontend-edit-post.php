<?php
/*
Plugin Name: Advanced Custom Fields: Frontend Post Edit
Plugin URI: http://bitbucket.org/jupitercow/acf-frontend-post-edit
Description: Sets up a basic system to manage users ability to edit prescribed posts on the front end.
Version: 0.1
Author: Jake Snyder
Author URI: http://Jupitercow.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

------------------------------------------------------------------------
Copyright 2013 Jupitercow, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if (! class_exists('acf_frontend_edit_posts') ) :

add_action( 'init', array('acf_frontend_edit_posts', 'init') );

class acf_frontend_edit_posts
{
	/**
	 * Class prefix
	 *
	 * @since 	0.1
	 * @var 	string
	 */
	public static $prefix = 'frontend_edit_posts';

	/**
	 * Plugin version
	 *
	 * @since 	0.1
	 * @var 	string
	 */
	public static $version = '0.1';

	/**
	 * Holds post types to use for editing
	 *
	 * @since 	0.1
	 * @var 	array
	 */
	public static $post_types = array();

	/**
	 * Holds users posts
	 *
	 * @since 	0.1
	 * @var 	array
	 */
	public static $user_posts = array(
		'all' => array()
	);

	/**
	 * Holds current post id to be edited
	 *
	 * @since 	0.1
	 * @var 	array
	 */
	public static $user_post_id = false;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function init()
	{
		if (! self::test_requirements() ) return;

		// Turn off email notifications sitewide
		add_filter( 'acf/notify_admin/on', '__return_false' );
		// Turn on email notifications for particular post types
		self::add_notifications_to_post_types();

		// Keep users from editing posts they aren't assigned to
		add_filter( 'acf/pre_save_post', array(__CLASS__, 'only_allow_assigned_edits') );

		// Add post type relationship fields to backend
		self::add_post_types_to_profile_page();
	}

	/**
	 * Add notifications to each post type
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function add_notifications_to_post_types()
	{
		if ( is_array(self::$post_types) ) foreach ( self::$post_types as $key => $post_type )
		{
			add_filter( 'acf/notify_admin/on/type=' . $key, '__return_true' );
		}
	}

	/**
	 * Make sure that any neccessary dependancies exist
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	bool True if everything exists
	 */
	public static function test_requirements()
	{
		// Look for ACF
		if (! class_exists('Acf') ) return false;
		return true;
	}

	/**
	 * A wrapper for ACF's acf_form_head() that adds a lot of the functionality we need.
	 *
	 * This determines what post we should try to present to them for editing and tests user's ability to edit that post.
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function acf_form_head( $post_type='' )
	{
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( defined('DOING_AJAX') && DOING_AJAX ) return;
		if ( $_SERVER['PHP_SELF'] == '/wp-admin/async-upload.php' ) return;

		// Redirect non-logged in users to login
		if (! is_user_logged_in() )
		{
			wp_redirect( wp_login_url( get_permalink() ) );
			die;
		}

		// Offers ability to set post type through a filter.
		$post_type = apply_filters( 'acf/' . self::$prefix . '/post_type', $post_type );

		// Make sure logged in users have a post, if not redirect to profile
		$user_post_ids = self::get_current_user_post_ids();
		if ( empty($user_post_ids['all']) )
		{
			$page = get_page_by_path('profile');
			if ( is_object($page) ) $redirect = get_permalink($page->ID);
			else $redirect = home_url('/');

			wp_redirect( $redirect ); die;
		}

		// If a post_id is submitted in query variables, make sure it is in user's posts, otherwise grab first post from user's posts
		// If query post id isn't in user's assigned posts, redirect the user to profile
		if (! empty($_GET['post_id']) && is_numeric($_GET['post_id']) )
		{
			self::$user_post_id = $_GET['post_id'];

			if (! in_array(self::$user_post_id, $user_post_ids['all']) )
			{
				$page = get_page_by_path('profile');
				if ( is_object($page) ) $redirect = get_permalink($page->ID);
				else $redirect = home_url('/');

				wp_redirect( $redirect ); die;
			}
		}
		else
		{
			// User either first post in an assigned post_type if a post_type has been specified, or grab first of all assigned posts.
			self::$user_post_id = ( $post_type ) ? $user_post_ids[$post_type][0] : $user_post_ids['all'][0];
		}

		// Make sure current post to edit is within the assigned post_type if one has been assigned
		// This will help make sure the wrong form is not used for the wrong post_type
		if ( $post_type && $post_type != get_post_type(self::$user_post_id) )
		{
			$page = get_page_by_path('profile');
			if ( is_object($page) ) $redirect = get_permalink($page->ID);
			else $redirect = home_url('/');

			wp_redirect( $redirect );
			die;
		}

		// Publish post and update notification if "Publish" was clicked.
		if ( isset($_POST['submit-publish']) && ! empty($_POST['fields']) && self::$user_post_id )
		{
			// Set the status field to publish
			add_filter('acf/update_value/name=status', 'custom_update_value_status', 10, 3);
				function custom_update_value_status( $value, $post_id, $field )
				{
					return 'publish';
				}
		
			// This could be used to add a special message
			$_POST['return'] = add_query_arg( 'update', 'published', get_permalink() );
			// Add post_id if one was submitted
			if (! empty($_GET['post_id']) ) $_POST['return'] = add_query_arg( 'post_id', $_GET['post_id'], $_POST['return'] );
			
		}

		// Load up ACF
		acf_form_head();
	}

	/**
	 * A wrapper for ACF's acf_form() that sets some defaults and adds some functionality we need.
	 *
	 * This determines what post we should try to present to them for editing and tests user's ability to edit that post.
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function acf_form( $options=array() )
	{
		if (! self::$user_post_id ) return;

		// defaults
		$defaults = array(
			'field_groups' => array(),
			'form_attributes' => array(
				'id' => 'post',
				'class' => '',
				'action' => '',
				'method' => 'post',
			),
			'return' => add_query_arg( 'updated', 'true', get_permalink() ),
			'html_before_fields' => '',
			'html_after_fields' => '',
			'submit_value' => __("Update", 'acf'),
			'publish_value' => __("Publish", self::$prefix),
			'view_value' => __("View", self::$prefix)
		);

		// merge defaults with options
		$options = array_merge($defaults, $options);

		// merge sub arrays
		foreach( $options as $k => $v )
		{
			if( is_array($v) )
			{
				$options[ $k ] = array_merge($defaults[ $k ], $options[ $k ]);
			}
		}

		$options['form']    = false;
		$options['post_id'] = self::$user_post_id;
		$options['form_attributes']['class'] .= ' acf-form';

		// register post box
		if (! $options['field_groups'] )
		{
			// get field groups for the specified post
			$filter = array(
				'post_id' => self::$user_post_id
			);

			$options['field_groups'] = array();
			$options['field_groups'] = apply_filters( 'acf/location/match_field_groups', $options['field_groups'], $filter );
		}
		elseif (! is_array($options['field_groups']) )
		{
			$options['field_groups'] = array($options['field_groups']);
		}

		// Add title and content fields
		if ( class_exists('acf_edit_title_content') && apply_filters( 'acf/' . self::$prefix . '/title_content', true ) )
		{
			$title_content = array('acf_post-title-content');
			$options['field_groups'] = array_merge($options['field_groups'], $title_content);
		}

		// Add a notification variable to query
		$options['return'] = add_query_arg( 'update', 'true', get_permalink() );
		// If a post_id was submitted via query, make sure it is added.
		if (! empty($_GET['post_id']) ) $options['return'] = add_query_arg( 'post_id', $_GET['post_id'], $options['return'] );

		// Add notifications
		do_action( 'frontend_notifications/show' ); ?>

		<form <?php if($options['form_attributes']){foreach($options['form_attributes'] as $k => $v){echo $k . '="' . $v .'" '; }} ?> enctype="multipart/form-data">

			<?php acf_form( $options ); ?>

			<div class="field">
				<input type="submit" value="<?php echo $options['submit_value']; ?>" />

				<?php if ( apply_filters( 'acf/' . self::$prefix . '/button/publish', true ) && 'publish' != get_post_meta(self::$user_post_id, 'status', true) ) : ?>
					<input type="submit" name="submit-publish" value="<?php echo $options['publish_value']; ?>" />
				<?php endif; ?>

				<?php if ( apply_filters( 'acf/' . self::$prefix . '/button/view', true ) ) : ?>
					<br /><a class="button" href="<?php echo get_permalink(self::$user_post_id); ?>" target="_blank"><?php echo $options['view_value']; ?></a>
				<?php endif; ?>
			</div>

		</form>
		<?php
	}

	/**
	 * A wrapper for register_post_type that also registers the post_type with this class in order to setup extra functionality
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function register_post_type( $post_type, $args )
	{
		register_post_type( $post_type, $args );
		self::$post_types[$post_type] = $args;
	}

	/**
	 * Get any posts assigned to the current user
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	bool|array False if no post_types currently. Returns an array with all post ids in the 'all' key, and each post_types ids in a key for each post_type
	 */
	public static function get_current_user_post_ids()
	{
		if (! self::$post_types ) return false;

		$current_user = wp_get_current_user();

		if (! self::$user_posts['all'] )
		{
			if ( is_array(self::$post_types) )
			{
				foreach ( self::$post_types as $key => $post_type )
				{
					$field_name = apply_filters( 'acf/' . self::$prefix . '/post_type_meta_name', $key );
					$field_name = apply_filters( 'acf/' . self::$prefix . '/post_type_meta_name/type=' . $key, $field_name, $key );

					self::$user_posts[$key] = get_user_meta($current_user->ID, $field_name, true);
					if (! empty(self::$user_posts[$key]) && is_array(self::$user_posts[$key]) )
					{
						foreach ( self::$user_posts[$key] as $post_id )
						{
							self::$user_posts['all'][] = $post_id;
						}
					} 
				}
			}
		}

		return self::$user_posts;
	}

	/**
	 * List user posts as links
	 *
	 * Specify a post_type to pull links from, default is 
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @param	string|array $args $post_type to narrow in on a specific post_type and $edit_page to specify a post slug or post id where the edit form exists.
	 * @return	string A list of anchor links to post depending on inputs
	 */
	public static function edit_link( $args='' )
	{
		$defaults = array(
			'post_type' => false,
			'edit_page' => false,
			'echo' => true
		);
		$options = wp_parse_args( $args, $defaults );
		extract($options);

		$user_post_ids = self::get_current_user_post_ids();

		$permalink = false;

		if ( $post_type )
		{
			$post_ids = $user_post_ids[$post_type];
		}
		else
		{
			$post_ids = $user_post_ids['all'];
		}

		if ( is_numeric($edit_page) )
		{
			$permalink = get_permalink($edit_page);
		}
		elseif ( $edit_page )
		{
			$page      = get_page_by_path( $edit_page );
			if ( is_object($page) ) $permalink = get_permalink($page->ID);
		}
		elseif ( $post_type )
		{
			if (! empty(self::$post_types[$post_type]['edit_page']) )
			{
				if ( is_numeric(self::$post_types[$post_type]['edit_page']) )
				{
					$permalink = get_permalink(self::$post_types[$post_type]['edit_page']);
				}
				elseif ( self::$post_types[$post_type]['edit_page'] )
				{
					$page      = get_page_by_path( self::$post_types[$post_type]['edit_page'] );
					$permalink = get_permalink($page->ID);
				}
			}
		}

		$output = '';
		if ( is_array($post_ids) ) foreach ( $post_ids as $post_id )
		{
			if (! $permalink ) $permalink = add_query_arg('edit', 1, get_permalink($post_id) );

			if ( 1 < count($post_ids) ) $permalink = add_query_arg('post_id', $post_id, $permalink);

			$output .= '<a href="' . $permalink . '">Edit ' . get_the_title($post_id) . '</a><br />';
		}

		if ( $echo )
			echo $output;
		else return $output;
	}

	/**
	 * For security, we need to hook into the pre save filter and shut the user down if they don't have access. 
	 * This will keep users from changing out the post_id in the form through browser dev tools.
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	int The post id being edited if the user hasn't been redirected for security
	 */
	public static function only_allow_assigned_edits( $post_id )
	{
		// Don't run in the admin
		if ( is_admin() ) return $post_id;

		// Get assigned posts for current user
		$user_post_ids = self::get_current_user_post_ids();

		// If not in user's assigned posts, redirect
		if (! in_array($post_id, $user_post_ids['all']) )
		{
			// Add a notification variable
			$return_url = add_query_arg( 'update', 'failed', get_permalink() );
			// If a post_id was submitted via query, make sure it is added.
			if (! empty($_GET['post_id']) ) $return_url = add_query_arg( 'post_id', $_GET['post_id'], $return_url );

			wp_redirect( $return_url ); die;
		}
		else
		{
			return $post_id;
		}
	}

	/**
	 * Adds the post_types as relationship fields to user's profile
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function add_post_types_to_profile_page()
	{
		if ( function_exists("register_field_group") && apply_filters( 'acf/' . self::$prefix . '/profile_post_types', true ) )
		{
			$args = array(
				'id' => 'acf_' . self::$prefix,
				'title' => apply_filters( 'acf/' . self::$prefix . '/field_group/title', 'Assign Posts to User' ),
				'fields' => array (),
				'location' => array (
					array (
						array (
							'param' => 'ef_user',
							'operator' => '==',
							'value' => 'all',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'normal',
					'layout' => 'no_box',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			);

			if ( is_array(self::$post_types) )
			{
				foreach ( self::$post_types as $key => $post_type )
				{
					$args['fields'][] = array (
						'key' => 'field_' . $key,
						'label' => $post_type['labels']['name'],
						'name' => $key,
						'type' => 'relationship',
						'return_format' => 'id',
						'post_type' => array (
							0 => $key,
						),
						'taxonomy' => array (
							0 => 'all',
						),
						'filters' => array (
							0 => 'search',
						),
						'result_elements' => array (
							0 => 'post_type',
							1 => 'post_title',
						),
						'max' => '',
					);
				}
				register_field_group( $args );
			}
		}
	}
}

endif;