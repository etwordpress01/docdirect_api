<?php
/**
 * APP API to manage users
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectApp_User_Route')) {

    class DocdirectApp_User_Route extends WP_REST_Controller{

        /**
         * Register the routes for the user.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'user';
			
			//user login
            register_rest_route($namespace, '/' . $base . '/do_login',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_items'),
                        'args' => array(
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'user_login'),
                        'args' => array(),
                    ),
                )
            );
			
			//user login
            register_rest_route($namespace, '/' . $base . '/do_logout',
                array(                 
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'do_logout'),
                        'args' => array(),
                    ),
                )
            );
			
			//signup
			register_rest_route($namespace, '/' . $base . '/do_signup',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_items'),
                        'args' => array(
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'user_signup'),
                        'args' => array(),
                    ),
                )
            );
			
			//forgot password
			register_rest_route($namespace, '/' . $base . '/forgot_password',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_items'),
                        'args' => array(
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_forgot_password'),
                        'args' => array(),
                    ),
                )
            );
        }

        /**
         * Get a collection of items
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_items($request) {
            $items['data'] = array();        
            return new WP_REST_Response($items, 200);
        }
		
		
		
		/**
         * Set Forgot Password
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_forgot_password($request) {
            global $wpdb;
            $json = array();
            $params = $request->get_params();
			$user_input = !empty($params['email']) ? esc_attr( $params['email'] ) : '';

			if (empty($user_input)) {
				$json['type'] = 'error';
				$json['message'] = esc_html__('Please add email address.', 'docdirect_api');
				return new WP_REST_Response($json, 203);
			} else if (!is_email($user_input)) {
				$json['type'] = "error";
				$json['message'] = esc_html__("Please add a valid email address.", 'docdirect_api');
				return new WP_REST_Response($json, 203);
			}

			$user_data = get_user_by_email($user_input);
			if (empty($user_data) || $user_data->caps[administrator] == 1) {
				//the condition $user_data->caps[administrator] == 1 to prevent password change for admin users.
				//if you prefer to offer password change for admin users also, just delete that condition.
				$json['type'] = "error";
				$json['message'] = esc_html__("Invalid E-mail address!", 'docdirect_api');
				return new WP_REST_Response($json, 203);
			}

			$user_id = $user_data->ID;
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

			if (empty($key)) {
				//generate reset key
				$key = wp_generate_password(20, false);
				$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
			}

			$protocol = is_ssl() ? 'https' : 'http';
			$reset_link = esc_url(add_query_arg(array('action' => 'reset_pwd', 'key' => $key, 'login' => $user_login), home_url('/', $protocol)));

			if (class_exists('DocDirectProcessEmail')) {
				$email_helper = new DocDirectProcessEmail();

				//Get User Name with User ID
				$username = docdirect_get_username($user_id);
				$emailData = array();

				$emailData['username']  = $username;
				$emailData['email'] 	= $user_email;
				$emailData['link'] 		= $reset_link;
				$email_helper->process_lostpassword_email($emailData);
			}

			$json['type'] = "success";
			$json['message'] = esc_html__("A link has been sent, please check your email.", 'docdirect_api');
			return new WP_REST_Response($json, 203);

        }

        /**
         * Login user for application
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Request
         */
        public function user_login($request) {
			$json =  array();
			
            if (isset($request['username']) && isset($request['password'])) {
                $creds = array(
                    'user_login' 			=> $request['username'],
                    'user_password' 		=> $request['password'],
                    'remember' 				=> true
                );
                
                $user = wp_signon($creds, false);
				
				if (is_wp_error($user)) {
                    $json['type']		= 'error';
                    $json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
					return new WP_REST_Response($json, 203);
                } else {
					
					unset($user->allcaps);
					unset($user->filter);
					$user->data->user_type	= get_user_meta($user->data->ID , 'user_type' , true);
					$user->meta = get_user_meta($user->data->ID, '', true);

					$user->avatar = apply_filters(
						'docdirect_get_user_avatar_filter',
						 docdirect_get_user_avatar(array('width'=>270,'height'=>270),$user->data->ID),
						 array('width'=>270,'height'=>270) //size width,height
					);
					
					$user->banner = docdirect_get_user_banner(array('width'=>1920,'height'=>450), $user->data->ID);
					
                    $json['type'] 		= "success";
                    $json['message'] 	= esc_html__('You are logged in successfully', 'docdirect_api');
                    $json['data'] 	 	= $user;
                    return new WP_REST_Response($json, 200);
                }                
            }
        }
		
		 /**
         * Logout user from the application
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Request
         */
        public function do_logout($request) {
			$json  = array();
			
        	if (!empty( $request['user_id'] ) ) {               						
        		$user_id 	= $request['user_id'];
				$sessions 	= WP_Session_Tokens::get_instance($user_id);
				
				// we have got the sessions, destroy them all!
				$sessions->destroy_all();

                $json['type'] = "success";
                $json['message'] = esc_html__('You are logged out successfully', 'docdirect_api');               
                return new WP_REST_Response($json, 200);
            } 
			
            $json['type'] = "error";
            $json['message'] = esc_html__('User ID required', 'docdirect_api');               
            return new WP_REST_Response($json, 200);               
        }
        

		/**
         * Signup user for application
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Request
         */
        public function user_signup($request) {
			
            $captcha_settings 	= '';
			$verify_user		= 'off';
			$verify_switch		= '';
			$json				= array();
			
			if(function_exists('fw_get_db_settings_option')) {
				$verify_switch = fw_get_db_settings_option('verify_user', $default_value = null);
			}
			
			$username 	= !empty( $_POST['username'] ) ? esc_sql( $_POST['username']) : ''; 
			$terms 		= !empty( $_POST['terms'] ) ? esc_sql( $_POST['terms'] ) : ''; 
			$password 	= !empty( $_POST['password'] ) ? esc_sql( $_POST['password']) : '';   
			$confirm_password = !empty( $_POST['confirm_password'] ) ? esc_sql( $_POST['confirm_password']) : '';   
	
			//user type error
			if( empty( $_POST['user_type'] ) ) {
				$json['type']		=  "error";
				$json['message']	=  esc_html__("Please select user type.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
			
			//User Role
			if( isset( $_POST['user_type'] ) && $_POST['user_type'] === 'professional' ) {
				$db_user_role	= 'professional';
			} else{
				$db_user_role	= 'visitor';
			}
			
			if( isset( $_POST['user_type'] ) && $_POST['user_type'] === 'professional' ) {
				if( empty( $_POST['directory_type'] ) ) {
					$json['type']		=  "error";
					$json['message']	=  esc_html__("Please select directory type.", 'docdirect_core');
					return new WP_REST_Response($json, 203);
				}
			}
			
			if(empty($username)) { 
				$json['type']		=  "error";
				$json['message']	=  esc_html__("User name should not be empty.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
			
			$email = esc_sql($_POST['email']); 
			if(empty($email)) { 
				$json['type']		=  "error";
				$json['message']	=  esc_html__("Email should not be empty.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
	
			if( !is_email($email) ) { 
				
				$json['type']		=  "error";
				$json['message']	=  esc_html__("Please enter a valid email.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			}

			if(empty($password)) { 
				$json['type']		=  "error";
				$json['message']	 =  esc_html__("Password is required.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
			
			if( $password != $confirm_password) { 
				$json['type']		=  "error";
				$json['message']	=  esc_html__("Password is not matched.",'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
		
			
			if( $terms  == '0') { 
				$json['type']		=  "error";
				$json['message']	=  esc_html__("Please check terms and conditions",'docdirect_core');
				return new WP_REST_Response($json, 203);
			}
			
			$random_password = $password;

			$user_identity = wp_create_user( $username,$random_password, $email );
			
			if ( is_wp_error($user_identity) ) { 
				$json['type']		=  "error";
				$json['message']	=  esc_html__("User already exists. Please try another one.", 'docdirect_core');
				return new WP_REST_Response($json, 203);
			} else {
				global $wpdb;
				wp_update_user(array('ID'=>esc_sql($user_identity),'role'=>esc_sql($db_user_role),'user_status' => 1));

				$wpdb->update(
				  $wpdb->prefix.'users',
				  array( 'user_status' => 1),
				  array( 'ID' => esc_sql($user_identity) )
				);

				if (function_exists('fw_get_db_settings_option')) {
					$dir_longitude = fw_get_db_settings_option('dir_longitude');
					$dir_latitude  = fw_get_db_settings_option('dir_latitude');
					$dir_longitude	= !empty( $dir_longitude ) ? $dir_longitude : '-0.1262362';
					$dir_latitude	= !empty( $dir_latitude ) ? $dir_latitude : '51.5001524';
				} else{
					$dir_longitude = '-0.1262362';
					$dir_latitude = '51.5001524';
				}

				$privacy	= array(
					'appointments'	=> 'on',
					'phone'			=> 'on',
					'email'			=> 'on',
					'contact_form'	=> 'on',
					'opening_hours'	=> 'on',
				);
				
				update_user_meta( $user_identity, 'show_admin_bar_front', false );
				update_user_meta( $user_identity, 'user_type', esc_sql($_POST['user_type'] ) );
				update_user_meta( $user_identity, 'first_name', esc_sql($_POST['first_name'] ) );
				update_user_meta( $user_identity, 'last_name', esc_sql($_POST['last_name'] ) );
				update_user_meta( $user_identity, 'phone_number', esc_sql($_POST['phone_number'] ) );
				update_user_meta( $user_identity, 'directory_type', intval($_POST['directory_type'] ) );
				update_user_meta( $user_identity, 'latitude', $dir_latitude);
				update_user_meta( $user_identity, 'longitude', $dir_longitude);
				update_user_meta( $user_identity, 'profile_status', 'active' );
				update_user_meta( $user_identity, 'verify_user', $verify_user );
				update_user_meta( $user_identity, 'rich_editing', 'true' );
				update_user_meta( $user_identity, 'privacy', $privacy );

				$full_name = docdirect_get_username($user_identity);
				update_user_meta( $user_identity, 'full_name', $full_name );

				//Update Profile Hits
				$year			= date('y');
				$month		    = date('m');
				$profile_hits	= array();
				$months_array	= docdirect_get_month_array(); //Get Month  Array

				foreach( $months_array as $key => $value ){
					$profile_hits[$year][$key]	= 0;
				}

				update_user_meta( $user_identity, 'profile_hits', $profile_hits );

				if( class_exists( 'DocDirectProcessEmail' ) ) {
					$email_helper	= new DocDirectProcessEmail();

					$emailData	= array();
					$emailData['user_identity']		=  $user_identity;
					$emailData['first_name']	    =  esc_attr( $_POST['first_name']);
					$emailData['last_name']			=  esc_attr( $_POST['last_name'] );
					$emailData['password']			=  $random_password;
					$emailData['username']			=  $username;
					$emailData['email']	   			=  $email;
					$email_helper->process_registeration_email($emailData);
					$email_helper->process_registeration_admin_email($emailData);

					if( !empty( $verify_switch ) && $verify_switch === 'verified' ){
						$key_hash = md5(uniqid(openssl_random_pseudo_bytes(32)));
						update_user_meta( $user_identity, 'confirmation_key', $key_hash);

						$protocol = is_ssl() ? 'https' : 'http';

						$verify_link = esc_url(add_query_arg(array(
							'key' => $key_hash.'&verifyemail='.$email
										), home_url('/', $protocol)));

						$emailData['verify_link'] 	 = $verify_link;
						$email_helper->process_email_verification($emailData);
					}

				} else{
					docdirect_wp_new_user_notification(esc_sql($user_identity), $random_password);
				}


				$user_array = array();
				$user_array['user_login'] 		= $username;
				$user_array['user_password'] 	= $random_password;
				$status = wp_signon( $user_array, false );

				$json['type']			=  "success";
				$json['message']	=  esc_html__("Your have successfully signed up.", "docdirect_core");
				return new WP_REST_Response($json, 200);
			}
			
        }
    }
}
add_action('rest_api_init',
        function () {
    $controller = new DocdirectApp_User_Route;
    $controller->register_routes();
});
