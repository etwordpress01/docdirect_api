<?php
if (!class_exists('DocdirectAppGetTeamsRoutes')) {

    class DocdirectAppGetTeamsRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/get_teams',
                array(
                  array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_listing'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Featured Listing
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_listing($request){
			
			$id	= $request['id'];
			if( empty( $id ) ){
				$items['type']	= 'error';
				$items['message']	= esc_html__('Please provide user id.','docdirect_api');
				return new WP_REST_Response($items, 200);
			}
			
            $teams    = get_user_meta($id,'teams_data', true);
			$teams    = !empty($teams) && is_array( $teams ) ? $teams : array();
			
			if( empty( $teams ) ){
				$items['type']	= 'error';
				$items['message']	= esc_html__('No user is added in the teams.','docdirect_api');
				return new WP_REST_Response($items, 200);
			}
			
			$total_users = (int)count($teams); //Total Users


			$query_args	= array(
									'role'  	=> 'professional',
									'include' 	=> $teams
								 );

			$user_query  = new WP_User_Query($query_args);
            if ( ! empty( $user_query->results ) ) {
                $items	= array();
                foreach ( $user_query->results as $user ) {
                    $item = array();
					
					//$featured_all = get_user_meta( $user->ID);
                    $avatar = apply_filters(
                        'docdirect_get_user_avatar_filter',
                        docdirect_get_user_avatar(array('width'=>270,'height'=>270), $user->ID),
                        array('width'=>270,'height'=>270) //size width,height
                    );
					
                    $review_data	= docdirect_get_everage_rating ( $user->ID );
                    $doc_type_id 	= get_user_meta( $user->ID, 'directory_type', true);
                    $postdata 		= get_post($doc_type_id);
                    $slug 	 		= $postdata->post_name;
                    $item['id'] 	= $user->ID;
                    $item['author_url'] = get_author_posts_url($user->ID);
                    $item['verified']  = get_user_meta($user->ID, 'verify_user', true);
                    $item['img_url'] = $avatar;
                    $item['directory_type'] = $doc_type_id;
                    $item['directory_type_name'] = get_the_title( $doc_type_id );
                    $item['directoty_type_slug'] = $slug;
                    $item['directory_type_url'] = esc_url( get_permalink($doc_type_id));
                    $item['name'] =  docdirect_get_username( $user->ID );
                    if( isset( $reviews_switch ) && $reviews_switch === 'enable' ){
                        $item['rating']  =  docdirect_get_rating_stars_v2($review_data,'echo');
                    }
					
                    $item['address'] = $user->user_address;
                    $item['phone'] = $user->phone_number;
                    $item['fax'] = $user->fax;
                    $item['email'] = $user->user_email;
                    $item['website'] = $user->user_url;
                    $item['category_color'] = fw_get_db_post_option($doc_type_id, 'category_color');
					
					$reviews_switch     = fw_get_db_post_option($directory_type, 'reviews', true);
					$review_data		= docdirect_get_everage_rating ( $user->ID );
					$item['review_data'] 	= $review_data;
					$item['rating'] 	= number_format((float)$review_data['average_rating'], 1, '.', '');
                    $item['likes']    	= get_user_meta($user->ID,'doc_user_likes_count', true);
					
					$meta_list = array( 'user_type' => '',
						'full_name' => '',
						'directory_type' => '',
						'video_url' => '',
						'user_gallery' => '',
						'userprofile_media' => '',
						'facebook' => '',
						'twitter' => '',
						'linkedin' => '',
						'pinterest' => '',
						'google_plus' => '',
						'tumblr' => '',
						'instagram' => '',
						'skype' => '',
						'user_address' => '',
						'contact_form' => '',
						'profile_status' => '',
						'tagline' => '',
						'phone_number' => '',
						'fax' => '',
						'languages' => '',
						'address' => '',
						'latitude' => '',
						'longitude' => '',
						'location' => '',
						'zip' => '',
						'verify_user' => '',
						'privacy' => '',
						'awards' => '',
						'education' => '',
						'experience' => '',
						'user_profile_specialities' => '',
						'description' => '',
						'first_name' => '',
						'last_name' => '',
						'nickname' => '',
						'schedules' => '',
						'time_format' => '',
						'time_format' => '',
						'professional_statements' => '',
						'appointments' => '',
						'phone' => '',
						'email' => '',
						'opening_hours' => '',
						'prices_list' => '',
						'user_current_package_expiry' => '',
						'user_featured' => '',
						'user_current_package' => '',
						'userprofile_banner' => '',
						'paypal_enable' => '',
						'paypal_email_id' => '',
						'stripe_enable' => '',
						'stripe_secret' => '',
						'stripe_publishable' => '',
						'stripe_site' => '',
						'stripe_decimal' => '',
						'approved_title' => '',
						'confirmation_title' => '',
						'cancelled_title' => '',
						'thank_you' => '',
						'schedule_message' => '',
						'booking_approved' => '',
						'booking_confirmed' => '',
						'booking_cancelled' => '',
						'currency_symbol' => '',
						'currency' => '',
						'services_cats' => '',
						'wishlist' => '',
						'booking_services' => '',
						'teams_data' => ''
					);
					
					foreach( $meta_list as $key => $value ){
						$data  = get_user_meta($user->ID, $key, true);

						if( $key === 'user_gallery' ){
							$user_gallery = maybe_unserialize($data);
							$db_user_gallery = array();

							foreach( $user_gallery as $gkey => $value ){
								$thumbnail = docdirect_get_image_source($gkey, 150, 150);
								$full = docdirect_get_image_source($gkey, 0, 0);
								$db_user_gallery[$gkey]['thumb'] = $thumbnail;
								$db_user_gallery[$gkey]['full'] = $full;
								$db_user_gallery[$gkey]['id']  = $gkey;
							}
							$item['all'][$key]	= array_values( $db_user_gallery );
						}elseif( $key === 'languages' ){
							$languages	= docdirect_prepare_languages();
							$db_languages = maybe_unserialize($data);
							$db_user_languages = array();
							foreach( $db_languages as $lkey => $value ){
								$db_user_languages[$lkey]  = $languages[$lkey];
							}
							
							$item['all'][$key]	= array_values( $db_user_languages );
							
						}else{
							$item['all'][$key] = maybe_unserialize($data);
						}
					} 
					
					
                    $items[] = $item;
                }

            }

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppGetTeamsRoutes;
        $controller->register_routes();
    });



if (!class_exists('ListingoApp_Providers_Route')) {

    class ListingoApp_Providers_Route extends WP_REST_Controller {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'providers';

            register_rest_route($namespace, '/' . $base, array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_providers'),
                    'args' => array(
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_provider')
                ),
                    )
            );

            register_rest_route($namespace, '/' . $base . '/appointment/slots', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'get_appointment_slots'),
                ),
                    )
            );

            register_rest_route($namespace, '/' . $base . '/appointment', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_appointment'),
                ),
                    )
            );

            register_rest_route($namespace, '/' . $base . '/confirm-appointment', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'confirm_appointment'),
                ),
                    )
            );
            register_rest_route($namespace, '/' . $base . '/save-rating', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'save_rating'),
                ),
                    )
            );


            register_rest_route($namespace, '/' . $base . '/reviews', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'get_provider_reviews'),
                ),
                    )
            );  
            register_rest_route($namespace, '/' . $base . '/manage-appointments', array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'manage_appointments'),
                    'args' => array(
                    ),
                ),
                    )
            );
            register_rest_route($namespace, '/' . $base . '/appointments-status', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'update_appointment_status'),
                ),
                    )
            );  


             register_rest_route($namespace, '/' . $base . '/privacy-settings',
                    array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_privacy_settings'),
                    // 'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args' => array(
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'set_privacy_settings'),
                    //'permission_callback' => array( $this, 'create_item_permissions_check' ),
                    'args' => array(),
                ),
            ));
             register_rest_route($namespace, '/' . $base . '/business-hours',
                    array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_business_hours'),
                    // 'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args' => array(
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'set_business_hours'),
                    //'permission_callback' => array( $this, 'create_item_permissions_check' ),
                    'args' => array(),
                ),
            ));

        }



        public function get_privacy_settings( $request ){
            $params = $request->get_params();
            $items = new stdClass();
            $publiser_id = $_GET['publisher_id'];
            if( !empty( $publiser_id )){
                $items = get_user_meta( $publiser_id , 'privacy_settings', true);    
            }
            if( empty($items)){
                $items = new stdClass();
            }
            return new WP_REST_Response( $items, 200);
            
        }
        public function set_privacy_settings( $request ){
            $params = $request->get_params();
            
            $publiser_id = $params['publisher_id'];
            $privacy_settings = $params['privacy_settings'];
            update_user_meta( $publiser_id ,'privacy_settings', $privacy_settings );

            $items['type'] =  "success";
            $items['message'] =  'Saved';
            return new WP_REST_Response($items, 200);

        }


        public function get_business_hours( $request ){
            $params = $request->get_params();
            $items = new stdClass();
            $publiser_id = $_GET['publisher_id'];
            if( !empty( $publiser_id )){
                $hours = get_user_meta( $publiser_id , 'business_hours', true);    
                foreach ($hours as $key => $h) {
                    
                    $items->$key = empty($h) ? new stdClass() : $h;
                }
            }
            if( empty($items)){
                $items = new stdClass();
            }
            return new WP_REST_Response( $items, 200);
            
        }
        public function set_business_hours( $request ){
            $params = $request->get_params();
            
            $publiser_id = $params['publisher_id'];
            $business_hours = $params['business_hours'];
            update_user_meta( $publiser_id ,'business_hours', $business_hours );

            $items['type'] =  "success";
            $items['message'] =  'Saved';
            return new WP_REST_Response($items, 200);

        }



        public function update_appointment_status($request){
            $params = $request->get_params();
            $publiser_id = $params['publisher_id'];
            $post_id = $params['post_id'];
            $status = $params['status'];
            //pending
            // publish
            $my_post = array(); 
            $my_post['ID'] = $post_id;
            $my_post['post_status'] = $status;

            // Update the post into the database
            wp_update_post( $my_post );
             $items['type'] =  "success";
            $items['message'] =  'Saved';
            return new WP_REST_Response($items, 200);
        }
        public function manage_appointments($request) {

            $sort_by = !empty($_GET['sortby']) ? $_GET['sortby'] : 'ID';
            $showposts = !empty($_GET['showposts']) ? $_GET['showposts'] : -1;
            $items = array();
            //Order
            $order = 'DESC';
            if (!empty($_GET['orderby'])) {
                $order = esc_attr($_GET['orderby']);
            }

            if (!empty($_GET['appointment_date'])) {
                $apt_date = strtotime(esc_attr($_GET['appointment_date']));
            }

            $status = array('pending', 'publish');
            if (!empty($_GET['appointment_status'])) {
                $status = array();
                $status[] = $_GET['appointment_status'];
            }


            $query_args = array(
                'posts_per_page' => "-1",
                'post_type' => 'sp_appointments',
                'order' => $order,
                'orderby' => $sort_by,
                'post_status' => $status,
                'ignore_sticky_posts' => 1);

            $total_query = new WP_Query($query_args);
            $total_posts = $total_query->post_count;

            $query_args = array(
                'posts_per_page' => $showposts,
                'post_type' => 'sp_appointments',
                'paged' => $paged,
                'order' => $order,
                'orderby' => $sort_by,
                'post_status' => $status,
                'ignore_sticky_posts' => 1);

            $meta_query_args[] = array(
                'key' => 'apt_user_to',
                'value' => $_GET['provider_id'],
                'compare' => '=',
                'type' => 'NUMERIC'
            );

            if (!empty($apt_date)) {
                $meta_query_args[] = array(
                    'key' => 'apt_date',
                    'value' => esc_attr($apt_date),
                    'compare' => '=',
                );
            }
            if (!empty($meta_query_args)) {
                $query_relation = array('relation' => 'AND',);
                $meta_query_args = array_merge($query_relation, $meta_query_args);
                $query_args['meta_query'] = $meta_query_args;
            }

            $items = array();

            $appt_data = new WP_Query($query_args);
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            if ($appt_data->have_posts()) {
                $counter = 1;
                while ($appt_data->have_posts()) : $appt_data->the_post();
                    global $post;
                    $item['apt_types'] = $apt_types = get_post_meta($post->ID,
                            'apt_types', true);

                    $item['post_id'] = $apt_types = get_the_ID();
                    $item['key'] = $apt_types = get_the_title();
                    $item['apt_services'] = $apt_services = get_post_meta($post->ID,
                            'apt_services', true);
                    $item['apt_reasons'] = $apt_reasons = get_post_meta($post->ID,
                            'apt_reasons', true);
                    $item['apt_description'] = $apt_description = get_post_meta($post->ID,
                            'apt_description', true);
                    $apt_user_from = get_post_meta($post->ID, 'apt_user_from',
                            true);
                    $apt_user_to = get_post_meta($post->ID, 'apt_user_to', true);



                    $item['apt_currency_symbol'] = $apt_currency_symbol = get_post_meta($post->ID,
                            'apt_currency_symbol', true);
                    $item['apt_user_to'] = $apt_user_to = get_post_meta($post->ID,
                            'apt_user_to', true);
                    $item['provider'] = $username = listingo_get_username($apt_user_to);
                    $item['apt_date'] = $apt_date = get_post_meta($post->ID,
                            'apt_date', true);
                    $apt_time = get_post_meta($post->ID,
                            'apt_time', true);
$time = explode('-', $apt_time);
 $item['apt_time'] = date_i18n($time_format, strtotime('2016-01-01 ' . $time[0])) . ' - ' .date_i18n($time_format, strtotime('2016-01-01 ' . $time[1]));
                    $item['time'] = $time = explode('-', $apt_time);

                    $item['booking_services'] = $booking_services = get_user_meta($apt_user_to,
                            'profile_services', true);
                    $item['booking_types'] = $booking_types = get_user_meta($apt_user_to,
                            'appointment_types', true);
                    $item['booking_reasons'] = $booking_reasons = get_user_meta($apt_user_to,
                            'appointment_reasons', true);

                    $item['apt_user_from'] = $apt_user_from = get_post_meta($post->ID,
                            'apt_user_from', true);
                    $item['username'] = $username = listingo_get_username($apt_user_from);
                    $item['status'] = get_post_status();

                    $item['avatar'] = $avatar = apply_filters(
                            'listingo_get_media_filter',
                            listingo_get_user_avatar(array('width' => 100, 'height' => 100),
                                    $apt_user_from),
                            array('width' => 100, 'height' => 100)
                    );
                    $items[] = $item;
                    $counter++;
                endwhile;
                wp_reset_postdata();
            }


            return new WP_REST_Response($items, 200);
        }
  public function confirm_appointment($request) {

            $params = $request->get_params();

            $json = array();


            global $current_user, $wp_roles, $userdata, $post;

            $user_identity = $params['user_id'];
            $json = array();


            $apt_meta_array = array();

            $appointment_form = get_user_meta($user_identity,
                    'appointment_form', true);


            $appointment_data = get_user_meta($user_identity,
                    'appointment_data', true);


            $apt_data = explode("|", $appointment_data);

            $author_id = !empty($apt_data[0]) ? intval($apt_data[0]) : '';
            $apt_time = !empty($apt_data[1]) ? esc_attr($apt_data[1]) : '';
            $apt_date = !empty($apt_data[2]) ? esc_attr($apt_data[2]) : '';

            $apt_prefix = substr($blogname, 0, 2);
            if (function_exists('fw_get_db_settings_option')) {
                $apt_prefix = fw_get_db_settings_option('appointment_no_prefix');
            }
            $appointment_no = esc_attr($apt_prefix) . '-' . sp_unique_increment(5);

            //Add Booking
            $appointment = array(
                'post_title' => $appointment_no,
                'post_status' => 'pending',
                'post_author' => $user_identity,
                'post_type' => 'sp_appointments',
                'post_date' => current_time('Y-m-d h')
            );

            $post_id = wp_insert_post($appointment);
            $blogname = wp_specialchars_decode(get_option('blogname'),
                    ENT_QUOTES);

            if (!empty($appointment_form)) {
                foreach ($appointment_form as $key => $apt_meta) {
                    $apt_meta_array[$key] = $apt_meta;
                }
            }

            $apt_meta_array['apt_number'] = esc_attr($appointment_no);
            $apt_meta_array['apt_user_from'] = intval($user_identity);
            $apt_meta_array['apt_user_to'] = intval($author_id);
            $apt_meta_array['apt_time'] = esc_attr($apt_time);

            $apt_meta_array['apt_date'] = strtotime($apt_date);
            $apt_meta_array['apt_description'] = esc_attr($params['apt_description']);

            $apt_meta_array['apt_currency_symbol'] = esc_attr($params['apt_currency_symbol']);
            $apt_meta_array['apt_services'] = esc_attr($params['apt_services']);
            $apt_meta_array['apt_types'] = esc_attr($params['apt_types']);
            $apt_meta_array['apt_reasons'] = esc_attr($params['apt_reasons']);





            //Update post meta
            foreach ($apt_meta_array as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }

            //Get Appointment Data
            $appointment_json_data = listingo_get_appointment_data($post_id);

            //Send Confirmation Mail


            $json['type'] = 'success';
            $json['appt_data'] = $appointment_json_data;
            $json['message'] = esc_html__('Step 4', 'listingo');

            // add_action('wp_ajax_listingo_get_appointment_step_four', 'listingo_get_appointment_step_four');
            // add_action('wp_ajax_nopriv_listingo_get_appointment_step_four', 'listingo_get_appointment_step_four');
            return new WP_REST_Response($json, 200);
        }

        /**
         * Get provider list for application
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_providers($request) {

            global $paged, $wp_query, $total_users, $query_args, $limit;

            $dir_search_pagination = fw_get_db_settings_option('dir_search_pagination');
            if (!empty($_GET['showposts'])) {
                $per_page = $_GET['showposts'];
            } else {
                $per_page = !empty($dir_search_pagination) ? $dir_search_pagination : get_option('posts_per_page');
            }
			
			$per_page = 25;
            $limit = (int) $per_page;

            $pg_page = get_query_var('page') ? get_query_var('page') : 1; //rewrite the global var
            $pg_paged = get_query_var('paged') ? get_query_var('paged') : 1; //rewrite the global var
            //paged works on single pages, page - works on homepage
            $paged = max($pg_page, $pg_paged);

            $offset = ($paged - 1) * $limit;

            $json = array();
            $directories = array();
            $meta_query_args = array();


            if( isset( $_GET['featured'])){



				$today = time();

				$query_args = array(
					 'role__in' => array('professional', 'business'),
					 );
				

				$meta_query_args = array();
				//Verify user
				$meta_query_args[] = array(
					'key'   => 'verify_user',
					'value'  => 'on',
					'compare'   => '='
				);
				//active users filter
				$meta_query_args[] = array(
					'key'   => 'activation_status',
					'value'  => 'active',
					'compare'  => '='
				);

				if (!empty($meta_query_args)) {
					$query_relation = array('relation' => 'AND',);
					$meta_query_args = array_merge($query_relation, $meta_query_args);
					$query_args['meta_query'] = $meta_query_args;
				}

				//By Categories
				if( !empty( $meta_category ) ) {
				 $query_relations = array( 'relation' => 'OR',);
				 $meta_query_args = array_merge( $query_relations, $meta_category );
				 $query_args['meta_query'][] = $meta_query_args;
				}

				//Featured
				$expiry_args = array(
					 'key'     => 'subscription_featured_expiry',
					 'value'   => $today,
					 'type'    => 'numeric',
					 'compare' => '>'
					);
				$query_args['meta_query'][] = $expiry_args;

				$query_args['meta_key']    = 'subscription_featured_expiry';
				$query_args['orderby']     = 'meta_value';



	}else{




            if (!empty($_GET['category'])) {
                $category = listingo_get_page_by_slug($_GET['category'], 'sp_categories', 'id');
            } else {
                if (is_singular('sp_categories')) {
                    $category = $wp_query->get_queried_object_id();
                } else {
                    $category = '';
                }
            }


if (!empty($_GET['category_id'])) {
                $category = $_GET['category_id'];
            } else {
                if (is_singular('sp_categories')) {
                    $category = $wp_query->get_queried_object_id();
                } else {
                    $category = '';
                }
            }

//search filters

            $location = !empty($_GET['geo']) ? esc_attr($_GET['geo']) : '';
            $keyword = !empty($_GET['keyword']) ? esc_attr($_GET['keyword']) : '';
            $appointments = !empty($_GET['appointment']) ? esc_attr($_GET['appointment']) : '';
            $ratings = !empty($_GET['ratings']) ? esc_attr($_GET['ratings']) : '';
            $sort_by = !empty($_GET['sortby']) ? esc_attr($_GET['sortby']) : 'recent';
            $photos = !empty($_GET['photo']) ? $_GET['photo'] : '';
            $zip = !empty($_GET['zip']) ? esc_attr($_GET['zip']) : '';

//Category seearch
            if (is_tax('sub_category')) {
                $sub_cat = $wp_query->get_queried_object();
                if (!empty($sub_cat->slug)) {
                    $sub_category = array($sub_cat->slug);
                }
            } else {
                $sub_category = !empty($_GET['sub_categories']) ? $_GET['sub_categories'] : '';
            }

//Country search
            if (!empty($_GET['country'])) {
                $country = !empty($_GET['country']) ? esc_attr($_GET['country']) : '';
            } else {
                if (is_tax('countries')) {
                    $sub_cat = $wp_query->get_queried_object();
                    if (!empty($sub_cat->slug)) {
                        $country = $sub_cat->slug;
                    }
                } else {
                    $country = '';
                }
            }

//city search
            if (!empty($_GET['city'])) {
                $city = !empty($_GET['city']) ? esc_attr($_GET['city']) : '';
            } else {
                if (is_tax('cities')) {
                    $sub_cat = $wp_query->get_queried_object();
                    if (!empty($sub_cat->slug)) {
                        $city = $sub_cat->slug;
                    }
                } else {
                    $city = '';
                }
            }

//insurance search
            if (!empty($_GET['insurance'])) {
                $insurance = !empty($_GET['insurance']) ? $_GET['insurance'] : '';
            } else {
                if (is_tax('insurance')) {
                    $sub_cat = $wp_query->get_queried_object();
                    if (!empty($sub_cat->slug)) {
                        $insurance = array($sub_cat->slug);
                    }
                } else {
                    $insurance = '';
                }
            }

//languages search
            if (!empty($_GET['languages'])) {
                $languages = !empty($_GET['languages']) ? $_GET['languages'] : '';
            } else {
                if (is_tax('languages')) {
                    $sub_cat = $wp_query->get_queried_object();
                    if (!empty($sub_cat->slug)) {
                        $languages = array($sub_cat->slug);
                    }
                } else {
                    $languages = '';
                }
            }

//Order
            $order = 'DESC';
            if (!empty($_GET['orderby'])) {
                $order = esc_attr($_GET['orderby']);
            }

            $sorting_order = 'ID';
            if ($sort_by === 'recent') {
                $sorting_order = 'ID';
            } else if ($sort_by === 'title') {
                $sorting_order = 'display_name';
            }

            $query_args = array(
                'role__in' => array('professional', 'business'),
                'order' => $order,
                'orderby' => $sorting_order,
            );

//Search by featured
            if ($sort_by === 'featured') {
                $query_args['orderby'] = 'meta_value_num';
                $query_args['order'] = 'DESC';

                $query_relation = array('relation' => 'OR',);
                $featured_args = array();
                $featured_args[] = array(
                    'key' => 'subscription_featured_expiry',
                    'compare' => 'EXISTS'
                );

                $meta_query_args[] = array_merge($query_relation, $featured_args);
            }

//Search By likes
            if ($sort_by === 'likes') {
                $query_args['order'] = $order;
                $query_args['orderby'] = 'meta_value_num';

                $query_relation = array('relation' => 'OR',);
                $likes_args = array();
                $likes_args[] = array(
                    'key' => 'likes_count',
                    'compare' => 'EXISTS'
                );

                $meta_query_args[] = array_merge($query_relation, $likes_args);
            }


//Search By Keywords
            if (!empty($_GET['keyword'])) {
                $s = sanitize_text_field($_GET['keyword']);
                //$query_args['search'] = $s;
                $search_args = array(
                    'search' => '*' . esc_attr($s) . '*',
                    'search_columns' => array(
                        'ID',
                        'display_name',
                        'user_login',
                        'user_nicename',
                        'user_email',
                        'user_url',
                    )
                );

                $meta_keyword = array('relation' => 'OR',);
                $meta_keyword[] = array(
                    'key' => 'first_name',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'last_name',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'nickname',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'username',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'full_name',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'company_name',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'description',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                $meta_keyword[] = array(
                    'key' => 'professional_statements',
                    'value' => $s,
                    'compare' => 'LIKE',
                );

                if (!empty($meta_keyword)) {
                    $meta_query_args[] = array_merge($meta_keyword, $meta_query_args);
                }
            }

//Category Type Search
            if (!empty($category)) {
                $meta_query_args[] = array(
                    'key' => 'category',
                    'value' => $category,
                    'compare' => '=',
                );
            }
//Sub category Type Search
            if (!empty($sub_category) && !empty($sub_category[0]) && is_array($sub_category)) {
                $query_relation = array('relation' => 'OR',);
                $sub_category_args = array();
                foreach ($sub_category as $key => $value) {
                    $sub_category_args[] = array(
                        'key' => 'sub_category',
                        'value' => $value,
                        'compare' => '='
                    );
                }

                $meta_query_args[] = array_merge($query_relation, $sub_category_args);
            }


//Cities
            if (!empty($country)) {
                $meta_query_args[] = array(
                    'key' => 'country',
                    'value' => $country,
                    'compare' => '=',
                );
            }

//Cities
            if (!empty($city)) {
                $meta_query_args[] = array(
                    'key' => 'city',
                    'value' => $city,
                    'compare' => '=',
                );
            }

   

//Photos search
            if (!empty($photos) && $photos === 'true') {
                $meta_query_args[] = array(
                    'key' => 'profile_photo',
                    'value' => 'on',
                    'compare' => '='
                );

                $meta_query_args[] = array(
                    'key' => 'profile_avatar',
                    'value' => '',
                    'compare' => '!='
                );
            }

//online appointments Search
            if (!empty($appointments) && $appointments === 'true') {
                $meta_query_args[] = array(
                    'key' => 'profile_appointment',
                    'value' => 'on',
                    'compare' => '='
                );
            }

//Zip Search
            if (!empty($zip)) {
                $meta_query_args[] = array(
                    'key' => 'zip',
                    'value' => $zip,
                    'compare' => '='
                );
            }

//Language Search;
            if (!empty($languages) && !empty($languages[0]) && is_array($languages)) {
                $query_relation = array('relation' => 'OR',);
                $language_args = array();
                foreach ($languages as $key => $value) {
                    $language_args[] = array(
                        'key' => 'profile_languages',
                        'value' => serialize(strval($value)),
                        'compare' => 'LIKE'
                    );
                }

                $meta_query_args[] = array_merge($query_relation, $language_args);
            }
//Insurance
            if (!empty($insurance) && !empty($insurance[0]) && is_array($insurance)) {
                $query_relation = array('relation' => 'OR',);
                $insurance_args = array();
                foreach ($insurance as $key => $value) {
                    $insurance_args[] = array(
                        'key' => 'profile_insurance',
                        'value' => serialize(strval($value)),
                        'compare' => 'LIKE'
                    );
                }

                $meta_query_args[] = array_merge($query_relation, $insurance_args);
            }

//Speciality Search;
            if (!empty($speciality) && !empty($speciality[0]) && is_array($speciality)) {
                $query_relation = array('relation' => 'OR',);
                $speciality_args = array();
                foreach ($speciality as $key => $value) {
                    $speciality_args[] = array(
                        'key' => $value,
                        'value' => $value,
                        'compare' => '='
                    );
                }

                $meta_query_args[] = array_merge($query_relation, $speciality_args);
            }

//Verify user
            $meta_query_args[] = array(
                'key' => 'verify_user',
                'value' => 'on',
                'compare' => '='
            );
//active users filter
            $meta_query_args[] = array(
                'key' => 'activation_status',
                'value' => 'active',
                'compare' => '='
            );

            if (!empty($meta_query_args)) {
                $query_relation = array('relation' => 'AND',);
                $meta_query_args = array_merge($query_relation, $meta_query_args);
                $query_args['meta_query'] = $meta_query_args;
            }

//Radius Search
            if ((isset($_GET['geo']) && !empty($_GET['geo']))) {

                $Latitude = '';
                $Longitude = '';
                $prepAddr = '';
                $minLat = '';
                $maxLat = '';
                $minLong = '';
                $maxLong = '';

                if (isset($_GET['geo_distance']) && !empty($_GET['geo_distance'])) {
                    $radius = $_GET['geo_distance'];
                } else {
                    $radius = 50;
                }

                //Distance in miles or kilometers
                if (function_exists('fw_get_db_settings_option')) {
                    $dir_distance_type = fw_get_db_settings_option('dir_distance_type');
                } else {
                    $dir_distance_type = 'mi';
                }

                if ($dir_distance_type === 'km') {
                    $radius = $radius * 0.621371;
                }

                $address = sanitize_text_field($_GET['geo']);
                $prepAddr = str_replace(' ', '+', $address);

                $args = array(
                    'timeout' => 15,
                    'headers' => array('Accept-Encoding' => ''),
                    'sslverify' => false
                );

                $url = 'http://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false';
                $response = wp_remote_get($url, $args);
                $geocode = wp_remote_retrieve_body($response);

                $output = json_decode($geocode);

                if (isset($output->results) && !empty($output->results)) {
                    $Latitude = $output->results[0]->geometry->location->lat;
                    $Longitude = $output->results[0]->geometry->location->lng;

                    if (isset($Latitude) && $Latitude <> '' && isset($Longitude) && $Longitude <> '') {
                        $zcdRadius = new RadiusCheck($Latitude, $Longitude, $radius);
                        $minLat = $zcdRadius->MinLatitude();
                        $maxLat = $zcdRadius->MaxLatitude();
                        $minLong = $zcdRadius->MinLongitude();
                        $maxLong = $zcdRadius->MaxLongitude();
                    }

                    $meta_query_args = array(
                        'relation' => 'AND',
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'address',
                                'value' => str_replace('+', ' ', $prepAddr),
                                'compare' => 'LIKE',
                            ),
                            array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'latitude',
                                    'value' => array($minLat, $maxLat),
                                    'compare' => 'BETWEEN',
                                    'type' => 'DECIMAL(20,10)',
                                ),
                                array(
                                    'key' => 'longitude',
                                    'value' => array($minLong, $maxLong),
                                    'compare' => 'BETWEEN',
                                    'type' => 'DECIMAL(20,10)',
                                )
                            ),
                        ),
                    );

                    if (isset($query_args['meta_query']) && !empty($query_args['meta_query'])) {
                        $meta_query = array_merge($meta_query_args, $query_args['meta_query']);
                    } else {
                        $meta_query = $meta_query_args;
                    }

                    $query_args['meta_query'] = $meta_query;
                }
            }



}

            //Count total users for pagination
            $total_query = new WP_User_Query($query_args);
            $total_users = $total_query->total_users;
            $query_args['number'] = $limit;
            $query_args['offset'] = $offset;

            $default_view = 'list';
            if (function_exists('fw_get_db_post_option')) {
                $default_view = fw_get_db_settings_option('dir_search_view');
            }

            $user_query = new WP_User_Query($query_args);
            $sp_userslist['status'] = 'none';
            $sp_usersdata = array();
            $sp_userslist = array();
            $favorites= array();
            if( isset($_GET['user_id'])){
                $favorites  =  get_user_meta($_GET['user_id'], 'wishlist', true);    
            }
            


            if (!empty($user_query->results)) {

                $sp_userslist['status'] = 'found';

                if (!empty($sp_category)) {
                    $title = get_the_title($sp_category);
                    $postdata = get_post($sp_category);
                    $slug = $postdata->post_name;
                } else {
                    $title = '';
                    $slug = '';
                }

                foreach ($user_query->results as $user) {

                    if (isset($media_type) && $media_type === 'banner') {
                        $thumb = apply_filters(
                                'listingo_get_media_filter', listingo_get_user_banner(array('width' => 370, 'height' => 270), $user->ID), array('width' => 370, 'height' => 270)
                        );
                    } else {
                        $thumb = apply_filters(
                                'listingo_get_media_filter', listingo_get_user_avatar(array('width' => 370, 'height' => 270), $user->ID), array('width' => 370, 'height' => 270)
                        );
                    }

                    $item['category_id'] = get_user_meta($user->ID, 'category', true);
                    $item['category'] = get_the_title($user->category);
                    $item['ID'] = $user->ID;
                    $item['latitude'] = $user->latitude;
                    $item['longitude'] = $user->longitude;
                    $item['phone'] = $user->phone;
                    $username = listingo_get_username($user->ID);
                    $item['username'] = $username;
                    $item['avatar'] = $thumb;


                    $item['wp_capabilities'] = ""; //$user->wp_capabilities;
                    $item['wp_user_level'] = $user->wp_user_level;
                    $item['usertype'] = $user->usertype;

                    $item['email'] = $user->user_email;
                    $item['website'] = $user->user_url;
                    
                    if( is_array($favorites)  &&  in_array($user->ID ,$favorites )){
                        $item['isfav'] = true;    
                    }else{
                        $item['isfav'] = false;
                    }

                    

                    $item['full_name'] = $user->full_name;
                    $item['nickname'] = $user->nickname;
                    $item['r_id'] = $user->r_id;
                    //$item['category'] = $user->category;
                    $item['sub_category'] = empty($user->sub_category) ? array() : (array) $user->sub_category;
                    $item['company_name'] = $user->company_name;
                    $item['profile_avatar'] = empty($user->profile_avatar) ? new stdClass() : $user->profile_avatar;
                    if (!empty($user->profile_gallery_photos)) {
                        $a = $user->profile_gallery_photos;
                        $a['image_data'] = array_values($a['image_data']);
                    } else {
                       $a=array();
                        $a['image_data'] = array();
                    }

                    $db_category_type = get_user_meta($user->ID, 'category', true);


                    /* Get the rating headings */
                    $rating_titles = $this->listingo_get_reviews_evaluation($db_category_type, 'leave_rating');
                    $item['rating_titles'] = $rating_titles;

                    $item['profile_gallery_photos'] = empty($user->profile_gallery_photos) ? new stdClass() : ( $a);
                    $item['facebook'] = $user->facebook;
                    $item['twitter '] = $user->twitter;
                    $item['linkedin'] = $user->linkedin;
                    $item['pinterest'] = $user->pinterest;
                    $item['google_plus'] = $user->google_plus;
                    $item['tumblr'] = $user->tumblr;
                    $item['instagram'] = $user->instagram;
                    $item['skype'] = $user->skype;
                    $item['activation_status'] = $user->activation_status;
                    $item['address'] = $user->address;
                    $item['latitude'] = $user->latitude;
                    $item['longitude'] = $user->longitude;
                    $item['tag_line'] = $user->tag_line;
                    $item['phone'] = $user->phone;
                    $item['fax'] = $user->fax;
if( is_array( $user->profile_languages)) {
                    $item['profile_languages'] = empty($user->profile_languages) ? array() :($user->profile_languages);
}else{
                   $item['profile_languages'] = is_string($user->profile_languages) && !strlen($user->profile_languages) ? array() :array($user->profile_languages);
  
}

                   $item['zip'] = $user->zip;
                    $item['verify_user'] = $user->verify_user;
                    $item['privacy'] = empty($user->privacy) ? new stdClass() : $user->privacy;
                    $item['country'] = $user->country;
                    $item['city'] = $user->city;
                    $item['description'] = $user->description;
                    $item['first_name'] = $user->first_name;
                    $item['last_name'] = $user->last_name;
                    $item['professional_statements'] = $user->professional_statements;
                    $item['subscription_featured_expiry'] = $user->subscription_featured_expiry;
                    if(isset($user->subscription_featured_expiry)){
                        $item['subscription_featured_expiry_preview'] = date('Y-m-d H:i:s', $user->subscription_featured_expiry);    
                    }
					
                    $item['subscription_expiry'] = $user->subscription_expiry;
                    $item['subscription_id'] = $user->subscription_id;
                    $item['sp_subscription'] = empty($user->sp_subscription) ? new stdClass() : $user->sp_subscription;
                    $item['awards'] = empty($user->awards) ? array() : $user->awards;
                    $_business_hours = array();
                    if (!empty($user->business_hours)){
                        foreach ($user->business_hours as $key => $bh) {
                            if(!empty($bh)){
                                $_business_hours[$key] = $bh ;
                            }else{
                                $_business_hours[$key] = new stdClass() ;
                            }
                        }
                    }


                    $item['business_hours'] = empty($_business_hours) ? new stdClass() : $_business_hours;
                    $item['business_hours_format'] = $user->business_hours_format;
                    //$item['default_slots'] = empty($user->default_slots) ? new stdClass() : $user->default_slots;

                    $default_slots = array();
                    
                    if( !empty( $user->default_slots )){
                            $_default_slots = $user->default_slots;                        
                            foreach ($_default_slots as $key => $slot) {
                                $default_slots[$key] = empty($slot) ? new stdClass() : $slot;
                                # code...
                            }
                    }

                    $item['default_slots'] = empty($default_slots) ? new stdClass() : $default_slots;



                    $item['experience'] = empty($user->experience) ? array() : $user->experience ;
                    $item['profile_brochure'] = $user->profile_brochure;
                    $item['profile_insurance'] = empty($user->profile_insurance) ? array() : $user->profile_insurance;
                    $amenities = $user->profile_amenities;


                    $_amenities = array();
                    if (!empty($amenities))
                        foreach ($amenities as $key => $amenitie) {
                        $a = get_term_by('slug', $amenitie, 'amenities');
                        if(!empty($a))
                            $_amenities[] = $a ;
                        }


                    $item['profile_amenities'] = empty($user->profile_amenities) ? array() : $_amenities;
                    $item['audio_video_urls'] =empty($user->audio_video_urls) ? array() : $user->audio_video_urls;
                    $item['qualification'] = empty($user->qualification) ? array() : $user->qualification;
                    $item['privacy_settings'] = empty($user->privacy_settings) ? new stdClass() : $user->privacy_settings;
                    $item['profile_photo'] = $user->profile_photo;
                    $item['profile_banner'] = $user->profile_banner;
                    $item['profile_appointment'] = $user->profile_appointment;
                    $item['profile_contact'] = $user->profile_contact;
                    $item['profile_hours'] = $user->profile_hours;
                    $item['profile_service'] = $user->profile_service;
                    $item['profile_team'] = $user->profile_team;
                    $item['profile_gallery'] = $user->profile_gallery;
                    $item['profile_videos'] = $user->profile_videos;
                    $item['appt_booking_approved'] = $user->appt_booking_approved;
                    $item['appt_booking_confirmed'] = $user->appt_booking_confirmed;
                    $item['appt_booking_cancelled'] = $user->appt_booking_cancelled;
                    $item['appt_cancelled_title'] = $user->appt_cancelled_title;
                    $item['appt_approved_title'] = $user->appt_approved_title;
                    $item['appt_confirmation_title'] = $user->appt_confirmation_title;
                    $item['appointment_currency'] = $user->appointment_currency;
                    $item['appointment_inst_desc'] = $user->appointment_inst_desc;
                    $item['appointment_inst_title'] = $user->appointment_inst_title;


                    $item['profile_services'] = empty($user->profile_services) ? array() : array_values($user->profile_services);
                    $item['appointment_reasons'] = empty($user->appointment_reasons) ? array() : array_values($user->appointment_reasons);
                    $item['appointment_types'] = empty($user->appointment_types) ? array() : array_values($user->appointment_types);
                    $item['teams_data'] = empty($user->teams_data) ? array() : (array) array_values($user->teams_data);

                    $review_data = get_user_meta($user->ID, 'review_data', true);
                    $item['review_data'] = empty($review_data) ? new stdClass() : $review_data;

                    $items[] = $item;
                }
            }

            // print_r($items);die;
            return new WP_REST_Response($items, 200);
        }

        /**
         * Get appoinment slots
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Request
         */
        public function get_appointment_slots($request) {
            $params = $request->get_params();
            $slot_date = $params['slot_date'];
            $author_id = $params['author_id'];


            $time_slots = array();
            $day_name = strtolower(date('l', strtotime($slot_date)));
            $time_slots = get_user_meta($author_id, 'default_slots', true);
            $time_format = get_option('time_format');

            ob_start();
            $i = 0;
            $response = array();
            if (!empty($time_slots[$day_name])) {
                foreach ($time_slots[$day_name] as $key => $value) {
                    $time = explode('-', $key);
                    $data = listingo_check_booked_slot($key, $slot_date, $author_id);
                    if (isset($data['available']) && $data['available'] === 'yes') {
                        $response[$i]['time_slot'] = date_i18n($time_format, strtotime('2016-01-01 ' . $time[0])) . '-' . date_i18n($time_format, strtotime('2016-01-01 ' . $time[1]));
                        $response[$i]['remaining'] = esc_attr($data['remaining']);
                        $response[$i]['key'] = $key;
                        $i++;
                    }
                }
            }



            return new WP_REST_Response($response, 200);
        }

       public function listingo_get_reviews_evaluation($category_type, $reviews_type) {

        $reviews_evaluation = array('leave_rating' => array(),'total_wait_time' =>array() );

        $reviews = '';
        if (function_exists('fw_get_db_settings_option')) {
            $reviews = fw_get_db_post_option($category_type, 'enable_reviews', true);
        }   
        // echo $reviews_type;
        // print_r($reviews);
        // print_r($reviews['enable'][$reviews_type]);
        // die;
        if( !empty( $reviews['enable'][$reviews_type] )){
                foreach ($reviews['enable'][$reviews_type] as $key => $value) {
                    $reviews_evaluation['leave_rating'][$key]['title'] = $value;
                    $reviews_evaluation['leave_rating'][$key]['slug'] = sanitize_title($value);
                }
                 foreach ($reviews['enable']['total_wait_time'] as $key => $value) {
                    $reviews_evaluation['total_wait_time'][] = $value;
                }
        }
        

        // $reviews_check = !empty($reviews['gadget']) ? $reviews['gadget'] : '';

        // if (!empty($reviews_check) && $reviews_check === 'enable' && $reviews_type === 'total_wait_time') {
        //     $reviews_evaluation = !empty($reviews['enable'][$reviews_type]) ? $reviews['enable'][$reviews_type] : array();
        // } else if (!empty($reviews_check) && $reviews_check === 'enable' && $reviews_type === 'leave_rating') {
        //     $reviews_evaluation = !empty($reviews['enable'][$reviews_type]) ? $reviews['enable'][$reviews_type] : array();
        // }

        // $reviews_evaluation = array_filter($reviews_evaluation);
        // $reviews_evaluation = array_combine(array_map('sanitize_title', $reviews_evaluation), $reviews_evaluation);
        return $reviews_evaluation;
    }

        /**
         * Create new appoinment from application 
         * @param type $request
         * @return \WP_REST_Response
         */
        public function create_appointment($request) {
            $params = $request->get_params();


            $json = array();
            $time_slot_link = '';

            if (empty($params['tg-timeslot'])) {
                $json['type'] = 'error';
                $json['message'] = esc_html__('Kindly choose the time slot first.', 'listingo');
            }

            if (!empty($params['tg-timeslot']) && !empty($params['author_id'])) {
                $protocol = is_ssl() ? 'https' : 'http';

                $time_slot = $params['tg-timeslot'];
                $slot_date = $params['slot_date'];
                $author_id = $params['author_id'];

                $merge_appointment_data = sprintf("%u%s%s%s%s", $author_id, '|', $time_slot, '|', $slot_date);
                $key_hash = md5(uniqid(openssl_random_pseudo_bytes(32)));

                $appointment_page = '';
                if (function_exists('fw_get_db_settings_option')) {
                    $appointment_page = fw_get_db_settings_option('appointment_form_page', true);
                }

                if (empty($appointment_page[0])) {
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Please set the page first in directory settings.', 'listingo');
                    echo json_encode($json);
                    die;
                } else {
                    $appt_page_slug = listingo_get_slug($appointment_page[0]);
                }

                $time_slot_link = esc_url(add_query_arg(array(
                    'key' => $key_hash
                                ), home_url('/' . esc_attr($appt_page_slug) . '/', $protocol)));

                //Update appointmnet key in user logged in table.
                update_user_meta($params['user_id'], 'appointment_data', $merge_appointment_data);
                update_user_meta($params['user_id'], 'appointment_key', $key_hash);

                $json['type'] = 'success';
                $json['appointment_link'] = $time_slot_link;
            }


            return new WP_REST_Response($json, 200);
        }

        public function get_provider_reviews($request) {

            $params = $request->get_params();
            //$params['provider_id'] 
            global $paged;

            $pg_page = get_query_var('page') ? get_query_var('page') : 1; //rewrite the global var
            $pg_paged = get_query_var('paged') ? get_query_var('paged') : 1; //rewrite the global var
//paged works on single pages, page - works on homepage
            $paged = max($pg_page, $pg_paged);
            $show_posts = 4;

            /**
             * @Prepare Reviews Data
             * @Get Total Reviews
             * @Get Reviews Loop Data
             */
            $meta_query_args = array('relation' => 'AND');
            $meta_query_args[] = array(
                'key' => 'user_to',
                'value' => $params['provider_id'],
                'compare' => '=',
                'type' => 'NUMERIC'
            );

            $query_args = array('posts_per_page' => -1,
                'post_type' => 'sp_reviews',
                'post_status' => 'publish',
                'order' => 'ASC'
            );

            $query_args['meta_query'] = $meta_query_args;

            $query = new WP_Query($query_args);

            $total_reviews = $query->post_count;

            $total_reviews_text = esc_html__('Reviews', 'listingo');
            $review_heading = sprintf("%u %s", $total_reviews, $total_reviews_text);

            $query_args1 = array(
                'posts_per_page' => $show_posts,
                'post_type' => 'sp_reviews',
                'paged' => $paged,
                'order' => 'ASC',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1);

            $query_args1['meta_query'] = $meta_query_args;
            $review_query = new WP_Query($query_args1);

            $items = array();
            if ($review_query->have_posts()) {

                while ($review_query->have_posts()) {
                    $review_query->the_post();
                    global $post;
                    $item['_ID']  = $post->ID;
                    $item['ID']  = $post->post_author;
                    $item['post_author']  = $post->post_author;



                    $db_category_type = get_user_meta(get_post_meta($post->ID, 'user_to', true), 'category', true);


                    /* Get the rating headings */
                    $rating_titles = $this->listingo_get_reviews_evaluation($db_category_type, 'leave_rating');

                    //$item['rating_titles'] = $rating_titles;

                    $item['reviewer_name']  = listingo_get_username($post->post_author );
                    $item['_reviewer_name']  = ($post->post_author );
                    $item['review_wait_time']  = get_post_meta($post->ID, 'review_wait_time', true);
                    $item['category_type']  = get_post_meta($post->ID, 'category_type', true);
                    $item['total_time']  = listingo_get_reviews_evaluation($category_type, 'total_wait_time');
                    /**
                     * Count user total rating
                     * with individual rating plus.
                     */
                    $count_indivi_rating = 0;
                    if (!empty($rating_titles)) {
                        foreach ($rating_titles['leave_rating'] as $key => $value) {
                            $indivi_rating = get_post_meta($post->ID, $value['slug'], true);
                            $count_indivi_rating += $indivi_rating;
                        }
                    }

                    if (count($rating_titles['leave_rating'])) {
                        $total_ratings = ($count_indivi_rating / count($rating_titles['leave_rating'])) * intval(20);
                    } else {
                        $total_ratings = $count_indivi_rating;
                    }



                    $review_date = get_the_date('Y-m-d h:i:s');
                    $avatar = apply_filters(
                            'listingo_get_media_filter', listingo_get_user_avatar(array('width' => 100, 'height' => 100), $post_author), array('width' => 100, 'height' => 100)
                    );

                    $review_time = '';
                    if (!empty($total_time[$review_wait_time])) {
                        $review_time = esc_attr($total_time[$review_wait_time]);
                    }

                    if (( apply_filters('listingo_get_user_type', $post_author) === 'business' || apply_filters('listingo_get_user_type', $post_author) === 'professional' ) && function_exists('fw_get_db_settings_option')
                    ) {
                        $author_url = get_author_posts_url($post_author);
                    } else {
                        $author_url = 'javascript:;';
                    }
                    $item['avatar'] = $avatar;
                    $item['author_url'] = $author_url;
                    $item['title'] =  get_the_title();
                    //$item['reviewer_name'] =  esc_attr($reviewer_name);
                    $item['review_date'] =  human_time_diff(strtotime($review_date), current_time('timestamp')) . esc_html__(' ago', 'listingo');
                    $item['total_ratings'] =  esc_attr($total_ratings);
                    // if (!empty($rating_titles)) {
                    //     if (!empty($rating_titles)) {
                    //         echo force_balance_tags($review_time);

                    //         foreach ($rating_titles as $key => $rating) {
                    //             $individual_rating = get_post_meta($post->ID, $key, true);
                    //             $indivi_rating_total = ($individual_rating / intval(5)) * intval(100);
                    //             echo esc_attr($indivi_rating_total);
                    //             echo esc_attr($rating);
                    //         }
                    //     }
                    // } 


                    $item['description'] =  get_the_content();
                    $items[] = $item ;

                } 
                wp_reset_postdata();
            } else {
                echo 'No Reviews Found';
            }
           return new WP_REST_Response($items, 200);
        }

        public function save_rating($request) {

            $params = $request->get_params();



            if (empty($params['review_title']) || empty($params['review_description']) || empty($params['provider_id'])) {
                $json['type'] = 'error';
                $json['message'] = esc_html__('Please fill all the fields..', 'listingo');
                //echo json_encode($json);
                return new WP_REST_Response($json, 200);
            }

            $user_to = !empty($params['provider_id']) ? intval($params['provider_id']) : '';


            $cat_review_status = 'pending';

            if (function_exists('fw_get_db_settings_option')) {
                $cat_review_status = fw_get_db_settings_option('dir_review_status', $default_value = null);
            }

            $user_reviews = array(
                'posts_per_page' => "-1",
                'post_type' => 'sp_reviews',
                'post_status' => 'any',
                'author' => $params['user_id'],
                'meta_key' => 'user_to',
                'meta_value' => $params['provider_id'],
                'meta_compare' => "=",
                'orderby' => 'meta_value',
                'order' => 'ASC',
            );

            $reviews_query = new WP_Query($user_reviews);
            $reviews_count = $reviews_query->post_count;

            if (isset($reviews_count) && $reviews_count > 0) {
                $json['type'] = 'error';
                $json['message'] = esc_html__('You have already submit a review.', 'listingo');
                return new WP_REST_Response($json, 200);
                die();
            }

            $db_category_type = get_user_meta($user_to, 'category', true);

            /* Get the rating headings */
            $rating_titles = listingo_get_reviews_evaluation($db_category_type, 'leave_rating');

            //Office Evaluation     
            if (empty($params['review_wait_time'])) {
                $json['type'] = 'error';
                $json['message'] = esc_html__('Total wait time required.', 'listingo');
                return new WP_REST_Response($json, 200);
                die();
            }

            //Provider Evaluation       
            if (!empty($rating_titles)) {
                foreach ($rating_titles as $slug => $label) {
                    if (empty($params['ratingData'][$slug])) {
                        $json['type'] = 'error';
                        $json['message'] = esc_html__('Rating is required.', 'listingo');
                        return new WP_REST_Response($json, 200);
                        die();
                    }
                }
            }


            if (!empty($params['review_title']) || !empty($params['review_description']) || !empty($params['provider_id'])
            ) {



                $review_heading = sanitize_text_field($params['review_title']);
                $review_description = sanitize_text_field($params['review_description']);
                $recommended = sanitize_text_field($params['recommended']);
                $review_wait_time = sanitize_text_field($params['review_wait_time']);
                $user_from = intval($params['user_id']);
                $user_to = intval($params['provider_id']);
                $category_type = intval($db_category_type);

                $review_post = array(
                    'post_title' => $review_heading,
                    'post_status' => $cat_review_status,
                    'post_content' => $review_description,
                    'post_author' => $user_from,
                    'post_type' => 'sp_reviews',
                    'post_date' => current_time('Y-m-d H:i:s')
                );

                $post_id = wp_insert_post($review_post);

                //Rating
                $rating = 0;

                /* Get the rating headings */
                $rating_evaluation = listingo_get_reviews_evaluation($db_category_type, 'leave_rating');
                $rating_evaluation_count = !empty($rating_evaluation) ? count($rating_evaluation) : 0;

                $review_extra_meta = array();
//var_dump($rating_evaluation);
                //Office Evaluation     
                if (!empty($rating_evaluation)) {
                    foreach ($rating_evaluation as $slug => $label) {

  
                        if (isset($params['ratingData'][$slug])) {

                            $review_extra_meta[$slug] = esc_attr($params['ratingData'][$slug]);
                            
                            update_post_meta($post_id, $slug, esc_attr($params['ratingData'][$slug]));
                            $rating += (int) $params['ratingData'][$slug];
                        }
                    }
                }
                if ($rating_evaluation_count) {
                    $user_rating = $rating / $rating_evaluation_count;
                } else {
                    $user_rating = $rating;
                }

                $user_rating = number_format((float) $user_rating, 2, '.', '');


                $review_meta = array(
                    'user_rating' => $user_rating,
                    'user_from' => $user_from,
                    'user_to' => $user_to,
                    'recommended' => $recommended,
                    'category_type' => $category_type,
                    'review_wait_time' => $review_wait_time,
                    'review_date' => current_time('Y-m-d H:i:s'),
                );

                $review_meta = array_merge($review_meta, $review_extra_meta);

                //Update post meta
                foreach ($review_meta as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }

                $review_meta['user_from'] = array($user_from);
                $review_meta['user_to'] = array($user_to);

                $new_values = $review_meta;
                if (isset($post_id) && !empty($post_id)) {
                    fw_set_db_post_option($post_id, null, $new_values);
                }

                /* Update recommendation in user table */
                $total_recommendations = 0;
                $user_review_meta = array();
                if (isset($cat_review_status) && $cat_review_status == 'publish') {
                    if (isset($recommended) && $recommended === 'yes') {
                        $total_recommendations = listingo_get_review_data($user_to, 'sp_total_recommendation', 'value');
                        $total_recommendations++;
                    } else {
                        $total_recommendations = listingo_get_review_data($user_to, 'sp_total_recommendation', 'value');
                    }
                }

                //$user_review_meta['sp_total_recommendation'] = $total_recommendations;
                //update_user_meta($user_to, 'review_data', $user_review_meta);

                /* Update avarage rating in user table */
                $average_rating = listingo_get_everage_rating($user_to);

                foreach ($average_rating as $key => $rating) {
                    $user_review_meta[$key] = $rating;
                }


                update_user_meta($user_to, 'review_data', $user_review_meta);

                $json['type'] = 'success';
                if (isset($cat_review_status) && $cat_review_status == 'publish') {
                    $json['message'] = esc_html__('Your review published successfully.', 'listingo');
                    $json['html'] = 'refresh';
                } else {
                    $json['message'] = esc_html__('Your review submitted successfully, it will be publised after approval.', 'listingo');
                    $json['html'] = '';
                }

                /* Mail Code Here */
                if (class_exists('ListingoProcessEmail')) {
                    $user_from_data = get_userdata($user_from);
                    $user_to_data = get_userdata($user_to);
                    $email_helper = new ListingoProcessEmail();

                    $emailData = array();

                    //User to data
                    $emailData['email_to'] = $user_to_data->user_email;
                    $emailData['link_to'] = get_author_posts_url($user_to_data->ID);
                    $emailData['username_to'] = listingo_get_username($user_to);
                    $emailData['username_from'] = listingo_get_username($user_from);
                    $emailData['link_from'] = get_author_posts_url($user_from_data->ID);

                    //General
                    $emailData['rating'] = $user_rating;
                    $emailData['reason'] = $review_heading;

                    $email_helper->process_rating_email($emailData);
                }

                //echo json_encode($json);
                return new WP_REST_Response($json, 200);

                die;
            } else {
                $json['type'] = 'error';
                $json['message'] = esc_html__('Please fill all the fields.', 'listingo');
                //echo json_encode($json);
                return new WP_REST_Response($json, 200);
                die;
            }
        }

        public function create_provider($request) {

            $params = $request->get_params();
            $temp = json_encode($params);

            $myfile = fopen("createp.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $temp );
            fclose($myfile);

            $_POST['register'] = $params['register'];
            if (isset($_POST['register']['type']) && sanitize_text_field($_POST['register']['type']) === 'seeker') {


                $data_array = array(
                    'username' => esc_html__('Username is required.', 'listingo_core'),
                    'first_name' => esc_html__('First name is required.', 'flistingo_core'),
                    'last_name' => esc_html__('Last name is required.', 'listingo_core'),
                    'gender' => esc_html__('Gender is required.', 'listingo_core'),
                    'phone' => esc_html__('Phone number is required.', 'listingo_core'),
                    'email' => esc_html__('Email address is required.', 'listingo_core'),
                    'password' => esc_html__('Password is required.', 'listingo_core'),
                    'confirm_password' => esc_html__('Please re-type your password.', 'listingo_core'),
                );

                $db_user_role = 'customer';
            } else {

                if (isset($_POST['register']['type']) && sanitize_text_field($_POST['register']['type']) === 'business') {
                    $required_fields = array(
                        'company_name' => esc_html__('Company name is required.', 'flistingo_core'),
                    );
                    $db_user_role = 'business';
                } else {

                    $required_fields = array(
                        'first_name' => esc_html__('First name is required.', 'flistingo_core'),
                        'last_name' => esc_html__('Last name is required.', 'listingo_core'),
                        'gender' => esc_html__('Gender is required.', 'listingo_core'),
                    );
                    $db_user_role = 'professional';
                }


            if (isset($_POST['register']['type']) && sanitize_text_field($_POST['register']['type']) === 'seeker') {
$data_array = array(


                    'phone' => esc_html__('Phone number is required.', 'listingo_core'),
                    'email' => esc_html__('Email address is required.', 'listingo_core'),
                    'password' => esc_html__('Password is required.', 'listingo_core'),
                    'confirm_password' => esc_html__('Please re-type your password.', 'listingo_core'),
                );

}else{
$data_array = array(
                    'category' => esc_html__('Please select category.', 'listingo_core'),
                    'sub_category' => esc_html__('Sub category is required.', 'listingo_core'),
                    'phone' => esc_html__('Phone number is required.', 'listingo_core'),
                    'email' => esc_html__('Email address is required.', 'listingo_core'),
                    'password' => esc_html__('Password is required.', 'listingo_core'),
                    'confirm_password' => esc_html__('Please re-type your password.', 'listingo_core'),
                );
}
                

                $data_array = array_merge($data_array, $required_fields);
            }


            $emailData = array();
            foreach ($data_array as $key => $value) {
                if (empty($_POST['register'][$key])) {
                    $json['type'] = 'error';
                    $json['message'] = $value;
                    echo json_encode($json);
                    die;
                }

                if ($key === 'email') {
                    if (!is_email($_POST['register'][$key])) {
                        $json['type'] = 'error';
                        $json['message'] = esc_html__('Please add a valid email address.', 'listingo_core');
                        echo json_encode($json);
                        die;
                    }
                }

                if ($key === 'confirm_password') {
                    if ($_POST['register']['password'] != $_POST['register']['confirm_password']) {
                        $json['type'] = 'error';
                        $json['message'] = esc_html__('Password does not match.', 'listingo_core');
                        echo json_encode($json);
                        die;
                    }
                }
            }


            if ( !empty($_POST['terms']) && $_POST['terms'] === '0') {
                $json['type'] = 'error';
                $json['message'] = esc_html__('Please select term and conditions', 'listingo_core');
                echo json_encode($json);
                die;
            }

            if (function_exists('fw_get_db_settings_option')) {
                $captcha_settings = fw_get_db_settings_option('captcha_settings', $default_value = null);
            }

            //recaptcha checks
            if (isset($captcha_settings) && $captcha_settings === 'enable'
            ) {
                if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                    $docReResult = listingo_get_recaptcha_response($_POST['g-recaptcha-response']);

                    if ($docReResult == 1) {
                        $workdone = 1;
                    } else if ($docReResult == 2) {
                        echo json_encode(array('type' => 'error', 'loggedin' => false, 'message' => esc_html__('Some error occur, please try again later.', 'listingo_core')));
                        die;
                    } else {
                        echo json_encode(array('type' => 'error', 'loggedin' => false, 'message' => esc_html__('Wrong reCaptcha. Please verify first.', 'listingo_core')));
                        die;
                    }
                } else {
                    echo json_encode(array('type' => 'error', 'loggedin' => false, 'message' => esc_html__('Please enter reCaptcha!', 'listingo_core')));
                    die;
                }
            }

            extract($_POST['register']);
            $json = array();

            $random_password = $password;

            $user_identity = wp_create_user($username, $random_password, $email);
            if (is_wp_error($user_identity)) {
                $json['type'] = "error";
                $json['message'] = esc_html__("User already exists. Please try another one.", 'listingo_core');
                echo json_encode($json);
                die;
            } else {
                global $wpdb;
                wp_update_user(array('ID' => esc_sql($user_identity), 'role' => esc_sql($db_user_role), 'user_status' => 1));

                $wpdb->update(
                        $wpdb->prefix . 'users', array('user_status' => 1), array('ID' => esc_sql($user_identity))
                );

                $full_name = listingo_get_username($user_identity);

                if (isset($_POST['register']['type']) && sanitize_text_field($_POST['register']['type']) === 'seeker') {
                    update_user_meta($user_identity, 'first_name', $first_name);
                    update_user_meta($user_identity, 'last_name', $last_name);
                } else {
                    if (isset($_POST['register']['type']) && sanitize_text_field($_POST['register']['type']) === 'business') {
                        update_user_meta($user_identity, 'company_name', $company_name);
                    } else {
                        update_user_meta($user_identity, 'first_name', $first_name);
                        update_user_meta($user_identity, 'last_name', $last_name);
                    }
                }

                if (function_exists('fw_get_db_settings_option')) {
                    $dir_longitude = fw_get_db_settings_option('dir_longitude');
                    $dir_latitude = fw_get_db_settings_option('dir_latitude');
                    $dir_longitude = !empty($dir_longitude) ? $dir_longitude : '-0.1262362';
                    $dir_latitude = !empty($dir_latitude) ? $dir_latitude : '51.5001524';
                } else {
                    $dir_longitude = '-0.1262362';
                    $dir_latitude = '51.5001524';
                }


                update_user_meta($user_identity, 'show_admin_bar_front', false);
                update_user_meta($user_identity, 'phone_number', sanitize_text_field($phone_number));
                update_user_meta($user_identity, 'verify_user', $verify_user);
                update_user_meta($user_identity, 'full_name', sanitize_text_field($full_name));
                update_user_meta($user_identity, 'phone', sanitize_text_field($phone));
                update_user_meta($user_identity, 'email', sanitize_text_field($email));
                update_user_meta($user_identity, 'category', sanitize_text_field($category));
                update_user_meta($user_identity, 'sub_category', sanitize_text_field($sub_category));
                update_user_meta($user_identity, 'activation_status', 'active');

                update_user_meta($user_identity, 'latitude', $dir_latitude);
                update_user_meta($user_identity, 'longitude', $dir_longitude);

                $key_hash = md5(uniqid(openssl_random_pseudo_bytes(32)));
                update_user_meta($user_identity, 'confirmation_key', $key_hash);

                $protocol = is_ssl() ? 'https' : 'http';

                $verify_link = esc_url(add_query_arg(array(
                    'key' => $key_hash . '&verifyemail=' . $email
                                ), home_url('/', $protocol)));


                //Send email to users and admin
                if (class_exists('ListingoProcessEmail')) {
                    $email_helper = new ListingoProcessEmail();

                    $emailData = array();
                    $emailData['user_identity'] = $user_identity;
                    $emailData['first_name'] = esc_attr($first_name);
                    $emailData['last_name'] = esc_attr($last_name);
                    $emailData['password'] = $random_password;
                    $emailData['username'] = $username;
                    $emailData['email'] = $email;

                    $email_helper->process_registeration_email($emailData);
                    $email_helper->process_registeration_admin_email($emailData);

                    $emailData['verify_link'] = $verify_link;
                    $email_helper->process_email_verification($emailData);
                }


                $json['type'] = "success";
                $json['message'] = esc_html__("An email has sent to your email address, please verify your account before using our services or contact to administrator to verify your account.", "listingo_core");
                return new WP_REST_Response($json, 200);
            }
        }

    }

}

add_action('rest_api_init', function () {
    $controller = new ListingoApp_Providers_Route;
    $controller->register_routes();
});
