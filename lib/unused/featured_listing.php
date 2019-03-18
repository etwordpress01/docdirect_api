<?php
if (!class_exists('DocdirectAppFeaturedListingRoutes')) {

    class DocdirectAppFeaturedListingRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/get_featured_listing',
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
            $today = time();
            $show_users	= 10;
            $order		 = 'DESC';
            $is_verify	= 'on';

            $query_args	= array(
                'role'  => 'professional',
                'order' => $order,
                'number' => $show_users
            );

            //Verify user
            $meta_query_args[] = array(
                'key'     => 'verify_user',
                'value'   => (string)$is_verify,
                'compare' => '='
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
					
                    $review_data	= docdirect_get_everage_rating ( $user->ID );
                    $doc_type_id = get_user_meta( $user->ID, 'directory_type', true);
                    $postdata = get_post($doc_type_id);
                    $slug 	 = $postdata->post_name;
                    $item['id'] = $user->ID;
                    $item['author_url'] = get_author_posts_url($user->ID);
                    $item['verified']  = get_user_meta($user->ID, 'verify_user', true);
                    $item['img_url'] = $avatar;
                    $item['directory_type'] = $doc_type_id;
                    $item['directory_type_name'] = get_the_title( $doc_type_id );
                    $item['directoty_type_slug'] = $slug;
                    $item['directory_type_url'] = esc_url( get_permalink($doc_type_id));
                    $item['name'] = $user->first_name.' '.$user->last_name;
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
							
						}elseif( $key === 'user_profile_specialities' ){
							$item['all'][$key]	= array_values( $data );
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
	$controller = new DocdirectAppFeaturedListingRoutes;
	$controller->register_routes();
});
