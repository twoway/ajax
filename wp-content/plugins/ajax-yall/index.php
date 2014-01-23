<?php

/*
 * Plugin Name: AJAX Y'all!
 * Description: A sample AJAX plugin for WordCamp Atlanta 2013
 * Author: Micah Wood
 * Author URI: http://micahwood.me
 * Plugin URI: http://micahwood.me/doing-ajax-in-wordpress/
 * Author Email: micah@mpress.co
 * Version: 0.2
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! class_exists( 'AJAX_Yall' ) ) {

	class AJAX_Yall {

        /**
         * WordPress requires an action name for each AJAX request
         *
         * @var string $action
         */
        private $action = 'ajaxin-it';

		function __construct() {

			// Add our AJAX-ified button to the end of every post
			add_filter( 'the_content', array( $this, 'the_content' ) );

			// Add our javascript file that will initiate our AJAX requests
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

            // Let's make sure we are actually doing AJAX first
            if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                // Add our callbacks for AJAX requests
                add_action( 'wp_ajax_' . $this->action, array( $this, 'do_ajax' ) ); // For logged in users
                add_action( 'wp_ajax_nopriv_' . $this->action, array( $this, 'do_ajax' ) ); // For logged out users

            }
		}

        /**
         * Append our AJAX-ified button to the end of every post.
         *
         * @param $content
         * @return string
         */
        function the_content( $content ) {

			global $post;

			if( is_single() && 'post' == $post->post_type ) { // We have strict criteria for where this will show up

				$content .= '<button class="ajax-yall" data-postid="'. $post->ID .'">'. __('Click me!', 'ajax-yall') .'</button>';

			}

			return $content;
		}

        /**
         * Enqueue our script that will initiate our AJAX requests and pass important variables
         * from PHP to our JavaScript.
         */
        function wp_enqueue_scripts() {

			if( is_single() && 'post' == get_post_type() ){ // Make sure you only call your scripts where you need them!

                // Load our script
				wp_enqueue_script( 'ajax-yall', plugins_url('ajax.js', __FILE__), array('jquery') );

                // Pass a collection of variables to our JavaScript
				wp_localize_script( 'ajax-yall', 'ajaxYall', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'action' => $this->action,
                    'nonce' => wp_create_nonce( $this->action ),
				) );

			}
		}

        /**
         * Back-end processing of our AJAX requests.
         */
        function do_ajax() {

            // By default, let's start with an error message
			$response = array(
				'status' => 'error',
				'message' => 'Invalid nonce',
			);

            // Next, check to see if the nonce is valid
            if( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], $this->action ) ){

                /*
                 * You would normally do something here, like fetch or store data.
                 * Note: We are passing $_GET['postID'], but not using it.
                 */

                // Update our message / status since our request was successfully processed
                $response['status'] = 'success';
                $response['message'] = "It's AJAX Y'all!";

            }

            // Return our response to the script in JSON format
			header( 'Content: application/json' );
			echo json_encode( $response );
			die;
		}

	}

	new AJAX_Yall();

}