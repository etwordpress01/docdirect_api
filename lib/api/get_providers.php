<?php
if (!class_exists('DocdirectAppGetProvidersRoutes')) {

    class DocdirectAppGetProvidersRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/get_listings',
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
			$limit			= !empty( $request['show_users'] ) ? intval( $request['show_users'] ) : 10;
			$page_number	= !empty( $request['page_number'] ) ? intval( $request['page_number'] ) : 1;
			$offset 		= ($page_number - 1) * $limit;
			
			$json	= array();
			$json['type']		= 'error';
			$json['message']	= esc_html__('Some error occur, please try again later','docdirect_api');
			
            if( $request['listing_type'] === 'featured' ){
				$today = time();

				$order		 = 'DESC';
				$is_verify	 = 'on';

				$query_args	= array(
					'role'  => 'professional',
					'order' => $order,
					'number' => $show_users
				);

				//featured users
				$meta_query_args[] = array(
					'key'     => 'user_featured',
					'value'   => $today,
					'type' => 'numeric',
					'compare' => '>'
				);

				if( !empty( $meta_query_args ) ) {
					$query_relation = array('relation' => 'AND',);
					$meta_query_args	= array_merge( $query_relation,$meta_query_args );
					$query_args['meta_query'] = $meta_query_args;
				}

				$query_args['meta_key']	   = 'user_featured';
				$query_args['orderby']	   = 'meta_value';
				
				$query_args['number']	= $limit;
				$query_args['offset']	= $offset;
				
			} elseif( $request['listing_type'] === 'latest' ){
				$order		 = 'DESC';

				$query_args	= array(
					'role'  => 'professional',
					'order' => $order,
					'number' => $show_users
				);

				$query_args['orderby']	   = 'ID';
				$query_args['number']	= $limit;
				$query_args['offset']	= $offset;
			} elseif( $request['listing_type'] === 'search' ){
				$item 				= array();
				$items 				= array();
				$directories		= array();
				$meta_query_args 	= array();
				$city 				= '';
				$directory_type 	= '';
				$insurance 			= '';
				$speciality 		= '';

				//Category Search
				if( !empty( $request['directory_type'] ) ) {
					$directory_type = $request['directory_type'];
				}

				//insurance search
				if (!empty($request['insurance'])) {
					$insurance = !empty($request['insurance']) ? $request['insurance'] : '';
				}

				//Category search

				if (!empty($request['sub_category'])) {
					$sub_category = !empty($request['sub_category']) ? $request['sub_category'] : '';
				}

				//speciality search
				if (!empty($request['speciality'])) {
					$speciality = !empty($request['speciality']) ? $request['speciality'] : '';
				}

				//Other filters
				$geo_location  = !empty( $request['geo_location'] ) ? $request['geo_location'] : '';
				$location	   = !empty( $request['city'] ) ? $request['city'] : '';
				$keyword	   = !empty( $request['keyword'] ) ? $request['keyword'] : '';
				$languages	   = !empty( $request['languages'] ) ? $request['languages'] : '';
				$appointments  = !empty( $request['appointments'] ) ? $request['appointments'] : '';
				$sort_by  	   = !empty( $request['sort_by'] ) ? $request['sort_by'] : 'recent';
				$photos  	   = !empty( $request['photos'] ) ? $request['photos'] : '';
				$zip  	   	   = !empty( $request['zip'] ) ? $request['zip'] : '';

				//Order
				$order	= 'DESC';
				if( isset( $request['order'] ) && !empty( $request['order'] ) ){
					$order	= $request['order'];
				}

				$sorting_order	= 'ID';
				if( $sort_by === 'recent' ){
					$sorting_order	= 'ID';
				} else if( $sort_by === 'title' ){
					$sorting_order	= 'display_name';
				}

				$query_args	= array(
					'role'  => 'professional',
					'order' => $order,
					'orderby' => $sorting_order,
				);

				//Search Featured
				if( $sort_by === 'featured' ){
					$query_args['orderby']	   = 'meta_value_num';
					$query_args['order']	   = 'DESC';

					$query_relation = array('relation' => 'OR',);
					$featured_args	= array();
					$featured_args[] = array(
						'key'     => 'user_featured',
						'compare' => 'EXISTS'
					);

					$meta_query_args[]	= array_merge( $query_relation,$featured_args );

				}

				//Search By likes
				if( $sort_by === 'likes' ){
					$query_args['order']	   = $order;
					$query_args['orderby']	   = 'meta_value_num';

					$query_relation = array('relation' => 'OR',);
					$likes_args	= array();
					$likes_args[] = array(
						'key'     => 'doc_user_likes_count',
						'compare' => 'EXISTS'
					);

					$likes_args[] = array(
						'key'     => 'doc_user_likes_count',
						'compare' => 'NOT EXISTS'
					);

					$meta_query_args[]	= array_merge( $query_relation,$likes_args );

				}

				//Search By Keywords
				if( !empty( $request['by_name'] ) ) {
					$s = sanitize_text_field($request['by_name']);
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

					$meta_by_name = array();
					$meta_by_name[] = array(
						'key' => 'first_name',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'last_name',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'nickname',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'username',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'full_name',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'description',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'professional_statements',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'prices_list',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'user_address',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'awards',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'user_profile_specialities',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'location',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$meta_by_name[] = array(
						'key' => 'tagline',
						'value' => $s,
						'compare' => 'LIKE',
					);

					$query_string = explode(' ', $s);

					if (!empty($query_string)) {
						foreach ($query_string as $key => $value) {
							$meta_by_name[] = array(
								'key' => 'first_name',
								'value' => $value,
								'compare' => 'LIKE',
							);

							$meta_by_name[] = array(
								'key' => 'last_name',
								'value' => $value,
								'compare' => 'LIKE',
							);
							$meta_by_name[] = array(
								'key' => 'full_name',
								'value' => $value,
								'compare' => 'LIKE',
							);

						}
					}

					if (!empty($meta_by_name)) {
						$query_relation = array('relation' => 'OR',);
						$meta_query_args[] = array_merge($query_relation, $meta_by_name);
					}
				}
				//Directory Type Search
				if( isset( $directory_type ) && !empty( $directory_type ) ){
					$meta_query_args[] = array(
						'key' 	   		=> 'directory_type',
						'value' 	 	=> $directory_type,
						'compare'   	=> '=',
					);
				}

				//Cities
				if(  !empty( $city ) ){
					$meta_query_args[] = array(
						'key' 	    => 'location',
						'value' 	=> $city,
						'compare'   => '=',
					);
				}

				//Photos search
				if( !empty( $photos ) &&  $photos === 'true' ){
					$meta_query_args[] = array(
						'key' 	   => 'userprofile_media',
						'value'    => array('',0),
						'compare'  => 'NOT IN'
					);
				}

				//insurance
				if( !empty( $insurance ) ){
					$meta_query_args[] = array(
						'key' 	  => 'insurance',
						'value'   => $insurance,
						'compare' => 'LIKE',
					);
				}

				//online appointments Search
				if( !empty( $appointments ) && $appointments === 'true' ){
					$meta_query_args[] = array(
						'key'     => 'appointments',
						'value'   => 'on',
						'compare' => '='
					);
				}

				//Zip Search
				if( !empty( $zip ) ){
					$meta_query_args[] = array(
						'key'     => 'zip',
						'value'   => $zip,
						'compare' => '='
					);
				}

				//Location Search
				if( !empty( $location ) ){
					$meta_query_args[] = array(
						'key'     => 'location',
						'value'   => $location,
						'compare' => '='
					);
				}

				//Language Search;
				if( !empty( $languages ) ){
					$language_args[] = array(
						'key'     => 'languages',
						'value'   => $languages,
						'compare' => 'like'
					);
					
					/*$query_relation = array('relation' => 'OR',);
					$language_args	= array();
					foreach( $languages as $key => $value ){
						$language_args[] = array(
							'key'     => 'languages',
							'value'   => serialize( strval( $value ) ),
							'compare' => 'LIKE'
						);
					}

					$meta_query_args[]	= array_merge( $query_relation,$language_args );*/
				}

				//Speciality Search;
				if( !empty( $speciality ) ){
					$meta_query_args[] = array(
						'key'     => $speciality,
						'value'   => $speciality,
						'compare' => 'LIKE'
					);
					
					/*$query_relation = array('relation' => 'OR',);
					$speciality_args	= array();
					foreach( $speciality as $key => $value ){
						$speciality_args[] = array(
							'key'     => $value,
							'value'   => $value,
							'compare' => '='
						);
					}

					$meta_query_args[]	= array_merge( $query_relation,$speciality_args );*/
				}

				//Sub Category Search;
				if( !empty( $sub_category ) ){
					$meta_query_args[] = array(
						'key' 		=> 'doc_sub_categories',
						'value'   	=> $sub_category,
						'compare' 	=> 'LIKE',
					);
					
					/*$query_relation = array('relation' => 'OR',);
					$subcategory_args	= array();
					foreach( $sub_category as $key => $value ){
						$subcategory_args[] = array(
							'key' 		=> 'doc_sub_categories',
							'value'   	=> serialize( strval( $value ) ),
							'compare' 	=> 'LIKE',
						);
					}

					$meta_query_args[]	= array_merge( $query_relation,$subcategory_args );*/
				}

				if( !empty( $meta_query_args ) ) {
					$query_relation = array('relation' => 'AND',);
					$meta_query_args	= array_merge( $query_relation,$meta_query_args );
					$query_args['meta_query'] = $meta_query_args;
				}

				//Radius Search
				if( !empty($request['geo_location'] ) ){

					$prepAddr   = '';
					$minLat	 = '';
					$maxLat	 = '';
					$minLong	= '';
					$maxLong	= '';

					$address	 = !empty($request['geo_location']) ? $request['geo_location'] : '';
					$prepAddr	= str_replace(' ','+',$address);

					$Latitude   = !empty( $request['latitude'] ) ? $request['latitude'] : '';
					$Longitude  = !empty( $request['longitude'] ) ? $request['longitude'] : '';

					if( isset( $request['geo_distance'] ) && !empty( $request['geo_distance'] ) ){
						$radius = $request['geo_distance'];
					} else{
						$radius = 20;
					}

					//Distance in miles or kilometers
					if (function_exists('fw_get_db_settings_option')) {
						$dir_distance_type = fw_get_db_settings_option('dir_distance_type');
						$google_key = fw_get_db_settings_option('google_key');
					} else{
						$dir_distance_type = 'mi';
						$google_key = '';
					}

					if( $dir_distance_type === 'km' ) {
						$radius = $radius * 0.621371;
					}

					if( !empty( $Latitude ) && !empty( $Longitude ) ){
						$Latitude	 = $Latitude;
						$Longitude   = $Longitude;

					} else{

						$args = array(
							'timeout'     => 15,
							'headers' => array('Accept-Encoding' => ''),
							'sslverify' => false
						);
						
						$url	 	= 'https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&key='.$google_key;
						$response   = wp_remote_get( $url, $args );
						$geocode	= wp_remote_retrieve_body($response);

						$output	  = json_decode($geocode);

						if( isset( $output->results ) && !empty( $output->results ) ) {
							$Latitude	= $output->results[0]->geometry->location->lat;
							$Longitude   = $output->results[0]->geometry->location->lng;
						}
					}


					if( !empty( $Latitude ) && !empty( $Longitude ) ){

						$zcdRadius = new RadiusCheck($Latitude,$Longitude,$radius);
						$minLat  = $zcdRadius->MinLatitude();
						$maxLat  = $zcdRadius->MaxLatitude();
						$minLong = $zcdRadius->MinLongitude();
						$maxLong = $zcdRadius->MaxLongitude();

						$meta_query_args = array(
							'relation' => 'AND',
							array(
								'relation' => 'AND',
								array(
									'key' 		=> 'latitude',
									'value'  	=> array($minLat, $maxLat),
									'compare' 	=> 'BETWEEN',
									'type' 		=> 'DECIMAL(20,10)',
								),
								array(
									'key' 		=> 'longitude',
									'value'   	=> array($minLong, $maxLong),
									'compare' 	=> 'BETWEEN',
									'type' 		=> 'DECIMAL(20,10)',
								)
							),
						);

						if( isset( $query_args['meta_query'] ) && !empty( $query_args['meta_query'] ) ) {
							$meta_query	= array_merge($meta_query_args,$query_args['meta_query']);
						} else{
							$meta_query	= $meta_query_args;
						}

						$query_args['meta_query']	= $meta_query;
					}
				}
				
				$query_args['number']	= $limit;
				$query_args['offset']	= $offset;
				
			} else if( $request['listing_type'] === 'teams' ){
				$id	= $request['id'];
				if( empty( $id ) ){
					$items['type']		= 'error';
					$items['message']	= esc_html__('Please provide user id.','docdirect_api');
					return new WP_REST_Response($items, 203);
				}

				$teams    = get_user_meta($id,'teams_data', true);
				$teams    = !empty($teams) && is_array( $teams ) ? $teams : array();
				
				if( empty( $teams ) ){
					$json['type']		= 'error';
					$json['message']	= esc_html__('No user is added in the teams.','docdirect_api');
					return new WP_REST_Response($json, 203);
				}
				
				$query_args	= array('role'  	=> 'professional',
									'include' 	=> $teams
								);

			} elseif( $request['listing_type'] === 'favorites' ){
				$items	= array();
				$user_id	= $request['user_id'];

				if( empty( $user_id ) ){
					$items['type']	= 'error';
					$items['message']	= esc_html__('Please provide user id.','docdirect_api');
					return new WP_REST_Response($items, 203);
				}

				$wishlist    	 = get_user_meta($user_id,'wishlist', true);
				$wishlist    	 = !empty($wishlist) && is_array( $wishlist ) ? $wishlist : array();

				if( empty( $wishlist ) ){
					$json['type']	= 'error';
					$json['message']	= esc_html__('No user is added in the favorite listing.','docdirect_api');
					return new WP_REST_Response($json, 203);
				}

				$today 			= time();
				$order		 	= 'DESC';
				$is_verify		= 'on';

				$query_args	= array(
					'role'  	=> 'professional',
					'order' 	=> $order,
					'include' 	=> $wishlist
				);

				$query_args['number']	= $limit;
				$query_args['offset']	= $offset;
				
			} elseif( $request['listing_type'] === 'category_providers' ){
				$id	= $request['id'];
				if( empty( $id ) ){
					$json['type']	= 'error';
					$json['message']	= esc_html__('Please select category.','docdirect_api');
					return new WP_REST_Response($json, 203);
				}
				
				$order		 	= 'DESC';
				$is_verify		= 'on';
				
				$query_args	= array(
					'role'  	=> 'professional',
					'order' 	=> $order,
				);
				
				$meta_query_args = array();
				if( !empty( $id ) ){
					$meta_query_args[] = array(
						'key' 	   		=> 'directory_type',
						'value' 	 	=> $id,
						'compare'   	=> '=',
					);
					
					$meta_query	= array_merge($query_args,$meta_query_args);
					$query_args['meta_query']	= $meta_query;
				}
				
				$query_args['number']	= $limit;
				$query_args['offset']	= $offset;

			} elseif( $request['listing_type'] === 'profile_data' ){
				if( !empty( $request['user_id'] ) ){
					$user_id 	= $request['user_id'];
					$query_args	= array(
						'role'  	=> 'professional',						
						'include' 	=> array($user_id),
					);
					
				} else {
					$json['type'] 		= 'error';
					$json['message'] 	= esc_html__('User ID required', 'docdirect_api');
					return new WP_REST_Response($json, 203);
				}
			} else {
				$json['type']		= 'error';
				$json['message']	= esc_html__('Please provide api type.','docdirect_api');
				return new WP_REST_Response($json, 203);
			}
			
			//Verify user
			$meta_query_args[] = array(
										'key'     => 'verify_user',
										'value'   => 'on',
										'compare' => '='
									);
			$meta_query_args[] = array(
									'key'     => 'profile_status',
									'value'   => 'active',
									'compare' => '='
								);
			
			if(  !empty( $query_args['meta_query'] ) ) {
				$meta_query	= array_merge($meta_query_args,$query_args['meta_query']);
			} else{
				$meta_query	= $meta_query_args;
			}
			
			if( !empty( $meta_query ) ){
				$query_args['meta_query']	= $meta_query;
			}

            $user_query  = new WP_User_Query($query_args);
			
            if ( ! empty( $user_query->results ) ) {
                $items	= array();
                foreach ( $user_query->results as $user ) {
                    $item = array();
                    $avatar = apply_filters(
                        'docdirect_get_user_avatar_filter',
                        docdirect_get_user_avatar(array('width'=>270,'height'=>270), $user->ID),
                        array('width'=>270,'height'=>270) //size width,height
                    );
					
					$banner			= docdirect_get_user_banner(array('width'=>1920,'height'=>450), $user->ID);
					
                    $review_data	= docdirect_get_everage_rating ( $user->ID );
                    $doc_type_id 	= get_user_meta( $user->ID, 'directory_type', true);
                    $postdata 		= get_post($doc_type_id);
                    $slug 	 		= $postdata->post_name;
                    $item['id'] 	= $user->ID;
                    $item['author_url'] = get_author_posts_url($user->ID);
                    $item['verified']  	= get_user_meta($user->ID, 'verify_user', true);
                    $item['img_url'] 	= $avatar;
					$item['banner'] 	= $banner;
                    $item['directory_type'] 		= $doc_type_id;
                    $item['directory_type_name'] 	= get_the_title( $doc_type_id );
                    $item['directoty_type_slug'] 	= $slug;
                    $item['directory_type_url'] 	= esc_url( get_permalink($doc_type_id));
                    $item['name'] = docdirect_get_username($user->ID);

                    $item['address'] 	= $user->user_address;
                    $item['phone'] 		= $user->phone_number;
                    $item['fax'] 		= $user->fax;
                    $item['email'] 		= $user->user_email;
                    $item['website'] 	= $user->user_url;
                    $item['category_color'] = fw_get_db_post_option($doc_type_id, 'category_color');
					
					$reviews_switch     = fw_get_db_post_option($directory_type, 'reviews', true);
					$review_data		= docdirect_get_everage_rating ( $user->ID );
					$item['review_data'] 	= $review_data;
					$item['rating'] 	= !empty( $review_data['average_rating'] ) ? number_format((float)$review_data['average_rating'], 1, '.', '') :0;
					$count_views		= get_user_meta($user->ID,'doc_user_likes_count', true);
                    $item['likes']    	= !empty( $count_views ) ? $count_views : 0;;
					
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
						'teams_data' => '',
						'insurance' => '',
						'qa' => '',
						'articles' => ''
					);
					
					$privacy	= array(
						'education' => array(
							'key'	=> 'education',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'experience' => array(
							'key'	=> 'experience',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'reviews' => array(
							'key'	=> 'reviews',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'bookings' => array(
							'key'	=> 'appointments',
							'check_settings'	=> 'yes',
							'check_packages' => 'yes'
						),
						'insurance' => array(
							'key'	=> 'insurance',
							'check_settings'	=> 'yes',
							'check_packages' => 'yes'
						),
						'teams' => array(
							'key'	=> 'team',
							'check_settings'	=> 'yes',
							'check_packages' => 'yes'
						),
						'awards' => array(
							'key'	=> 'awards',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'price_list' => array(
							'key'	=> 'price_list',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'articles' => array(
							'key'	=> 'articles',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'qa' => array(
							'key'	=> 'qa',
							'check_settings'	=> 'yes',
							'check_packages' => 'no'
						),
						'profile_banner' => array(
							'key'	=> 'profile_banner',
							'check_settings'	=> 'no',
							'check_packages' => 'yes'
						),
						'favorites' => array(
							'key'	=> 'favorite',
							'check_settings'	=> 'no',
							'check_packages' => 'yes'
						),
						'business_hours' => array(
							'key'	=> 'schedules',
							'check_settings'	=> 'no',
							'check_packages' => 'yes'
						)
					);
					
					//privacy
					$db_directory_type	 = get_user_meta( $user->ID, 'directory_type', true);
					$privacy_array	= array();
					if( !empty( $db_directory_type ) ) {
						foreach( $privacy as $p_key => $value ){
							if( $value['check_settings'] === 'yes' ){
								if (function_exists('fw_get_db_settings_option')) {
									$is_enable  = fw_get_db_post_option( $db_directory_type, $p_key, true );
									if( $is_enable === 'enable' ){
										$privacy_array[$p_key] = 'enabled';
									} else{
										$privacy_array[$p_key] = 'disabled';
									}
								}
							}
							
							if( $value['check_packages'] === 'yes' ){
								if( apply_filters('docdirect_is_setting_enabled',$user->ID,$value['key'] ) === true ){
									$privacy_array[$p_key] = 'enabled';
								} else{
									$privacy_array[$p_key] = 'disabled';
								}
							}
						}
						
						$item['all']['items_privacy'] = ($privacy_array);
					}
					
					//data
					foreach( $meta_list as $key => $value ){
						$data  = get_user_meta($user->ID, $key, true);
						$array_type_data = array('user_profile_specialities','awards','education','experience','by_ratings','prices_list','services_cats','wishlist','booking_services','teams_data');
						
						if( $key === 'user_gallery' ){
							if( !empty( $data ) ){
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
							} else{
								$item['all'][$key]	= array();
							}
							
							
						}elseif( $key === 'languages' ){
							$languages	= docdirect_prepare_languages();
							if( !empty( $data ) ){
								$db_languages = maybe_unserialize($data);
								$db_user_languages = array();
								foreach( $db_languages as $lkey => $value ){
									$db_user_languages[$lkey]  = $languages[$lkey];
								}

								$item['all'][$key]	= array_values( $db_user_languages );
							} else{
								$item['all'][$key]	= array();
							}
						}elseif( $key === 'insurance' ){
							if( !empty( $data ) ){
								$user_insurance = maybe_unserialize($data);
								$db_user_insurance = array();

								if( !empty( $user_insurance ) ){
									foreach( $user_insurance as $i_key => $value ){
										$insurance	    = get_term_by( 'slug', $value, 'insurance');
										if( !empty( $insurance ) ) {
											if( !empty( $insurance->name ) ){
												$db_user_insurance[$i_key]  = $insurance->name;
											}
										}
									}
								}

								$item['all'][$key]	= array_values( $db_user_insurance );
							} else{
								$item['all'][$key]	= array();
							}
							
						}elseif( $key === 'articles' ){
							$order 		= 'DESC';
							$sorting 	= 'ID';
							
							$args = array('posts_per_page' => -1,
								'post_type' 	=> 'sp_articles',
								'orderby' 		=> $sorting,
								'order' 		=> $order,
								'post_status' 	=> array( 'publish' ),
								'author' 		=> $user->ID,
								'suppress_filters' => false
							);

							$query = new WP_Query($args);
							
							$username = docdirect_get_username($user->ID);
							$query = new WP_Query($args);
							$articles	= array();
							$articles_array	= array();
							if ($query->have_posts()) {
								 while ($query->have_posts()) : $query->the_post();
									global $post;
									$height 	= 150;
									$width 		= 150;
									$thumbnail 		= docdirect_prepare_thumbnail($post->ID, $width, $height);
									$content_post 	= get_post($post->ID);
									$content 	  	= $content_post->post_content;
									$content 		= apply_filters('the_content', $content);
									$content 		= str_replace(']]>', ']]&gt;', $content);
								
									$articles['id']			= $post->ID;
									$articles['title']		= get_the_title();
									$articles['content']	= $content;
									$articles['image']		= $thumbnail;
									
									$articles_array[]		= $articles;
									
								 endwhile;
								wp_reset_postdata();
							}
	
							$item['all'][$key]	= array_values( $articles_array );
						}elseif( $key === 'qa' ){
							$posts_per_page	= get_option('posts_per_page');
							$q_args = array(
								'post_type' 		=> 'sp_questions',
								'post_status' 		=> 'publish',
								'posts_per_page' 	=> $posts_per_page,
								'order' => 'DESC',
							);

							$meta_query_args	= array();
							$meta_query_args[] = array(
								'key' 		=> 'question_to',
								'value' 	=> $user->ID,
								'compare' 	=> '=',
							);

							$query_relation = array('relation' => 'AND',);
							$meta_query_args = array_merge($query_relation, $meta_query_args);
							$q_args['meta_query'] = $meta_query_args;
							$q_query = new WP_Query($q_args);
							
							$qa				= array();
							$qa_array		= array();
							if ($q_query->have_posts()) {
								 while ($q_query->have_posts()) : $q_query->the_post();
									global $post;
									$height 	= 150;
									$width 		= 150;
									$thumbnail 		= docdirect_prepare_thumbnail($post->ID, $width, $height);
									
									$question_by = get_post_meta($post->ID, 'question_by', true);
									$question_to = get_post_meta($post->ID, 'question_to', true);
									$category 	 = get_post_meta($post->ID, 'question_cat', true);

									$category_icon = '';
									if (function_exists('fw_get_db_post_option') && !empty( $category )) {
										$category_icon = fw_get_db_post_option($category, 'dir_icon', true);
									}
									
									$question_views = get_post_meta($post->ID, 'question_views', true);
									$question_views	= !empty( $question_views ) ? $question_views : 0;
									$pfx_date = get_the_date( 'Y-m-d', $post->ID );
									$pfx_date = human_time_diff(strtotime($pfx_date), current_time('timestamp')) .' '. esc_html__('ago', 'docdirect_api');
									$total_votes 			= get_post_meta($post->ID, 'total_votes', true);
									$question_total_ans 	= fw_ext_get_total_question_answers($post->ID);
								
									$content_post 	= get_post($post->ID);
									$content 	  	= $content_post->post_content;
									$content 		= apply_filters('the_content', $content);
									$content 		= str_replace(']]>', ']]&gt;', $content);
									
									$qa['id']			= $post->ID;
									$qa['title']		= get_the_title();
									$qa['content']		= $content;
									$qa['image']		= $thumbnail;
									$qa['views']		= intval( $question_views );
									$qa['time_diff']	= $pfx_date;
									$qa['votes']		= intval( $total_votes );
									$qa['answers']		= intval( $question_total_ans );
									
									$qa_array[]		= $qa;
									
								 endwhile;
								wp_reset_postdata();
							}
	
							$item['all'][$key]	= array_values( $qa_array );
						}elseif( in_array($key,$array_type_data) ){
							if( !empty( $data ) ){
								$item['all'][$key]	= array_values( $data );
							} else{
								$item['all'][$key]	= array();
							}
						}elseif( $key === 'schedules' ){
							if( !empty( $data ) ){
								$item['all'][$key]	= $data;
							} else{
								$item['all'][$key]	= new stdClass();
								
							}
						}else{
							$item['all'][$key] = maybe_unserialize($data);
						}
					} 
					
					if( $request['listing_type'] === 'profile_data' ){
						 $items = $item; //single onject
					} else{
						 $items[] = $item; //array object
					}
                   
                }
				
				return new WP_REST_Response($items, 200);
            }else{
				$items[] = $json;
				return new WP_REST_Response($items, 203);
			} 
        }

    }
}

add_action('rest_api_init',
function () {
	$controller = new DocdirectAppGetProvidersRoutes;
	$controller->register_routes();
});
