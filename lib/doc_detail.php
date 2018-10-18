<?php
if (!class_exists('DocdirectAppDocDetailRoutes')) {

    class DocdirectAppDocDetailRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'doc';

            register_rest_route($namespace, '/' . $base . '/doc_detail',
                array(
					array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_items'),
                        'args' => array(
                        ),
                    ),
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_doc_detail'),
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
         * Get Doctor Detail
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_doc_detail($request)
        {
            if (!empty($request['user_id'])) {
                $user_id    = $request['user_id'];
                $user_meta  = get_user_meta($user_id);
                $user_data  = get_userdata($user_id);
                //return $user_meta;
                $items = array();
                $item = array();
                $doc_type_id    = get_user_meta( $user_id, 'directory_type', true);
                $featured       = get_user_meta( $user_id, 'user_featured', true);
                $item['name']   = docdirect_get_username($user_id);
                $item['id']     = $user_id;
                //Get user image
                $user_gallery	=  apply_filters(
                                        'docdirect_get_user_avatar_filter',
                                        docdirect_get_user_avatar(array('width'=>270,'height'=>270),$user_id),
                                        array('width'=>270,'height'=>270) //size width,height
                                    );
                //Get user banner
                $profile_banner	=  apply_filters(
                    'docdirect_get_user_avatar_filter',
                    docdirect_get_user_banner(array('width'=>270,'height'=>270), $user_id) ,
                    array('width'=>270,'height'=>270) //size width,height=
                );
                //Get privacy settings
                $privacy = docdirect_get_privacy_settings($user_id);
                $item['image'] = $user_gallery;
                $item['banner'] = $profile_banner;
                $item['directory_type'] = $user_meta['directory_type'];
                $item['directory_type_name'] = get_the_title( $doc_type_id );
                $item['directory_type_url'] = esc_url( get_permalink($doc_type_id));
                $item['video_url'] = $user_meta['video_url'];
                $item['address'] = $user_meta['user_address'];
                if($privacy['phone'] === 'on'){
                    $item['phone'] = $user_meta['phone_number'];
                }
                 $item['fax'] = $user_meta['fax'];
                if($privacy['email'] === 'on') {
                    $item['email'] = $user_data->user_email;
                }
                if($privacy['phone'] === 'on'){
                    $item['phone'] = $user_meta['phone_number'];
                }
                if($privacy['contact_form'] === 'on'){
                    $item['contact_form'] = $user_meta['contact_form'];
                }
                $item['website'] = $user_data->user_url;
                $languages_array	= docdirect_prepare_languages();
                //Get Language Array
                foreach($user_meta['languages'] as $key => $language){
                   $selected_lan = unserialize($language);
                }

                $languages_array = array_intersect_key($languages_array, $selected_lan);
                $item['language'] = $languages_array;
                $item['verify'] = $user_meta['verify_user'];
                $item['user_featured'] = $featured;
                $item['latitude'] = $user_meta['latitude'];
                $item['longitude'] = $user_meta['longitude'];
                $item['location'] = $user_meta['location'];
                $item['views'] = $user_meta['doc_user_views_count'];
                $item['facebook'] = $user_meta['facebook'];
                $item['twitter'] = $user_meta['twitter'];
                $item['linkedin'] = $user_meta['linkedin'];
                $item['pinterest'] = $user_meta['pinterest'];
                $item['google_plus'] = $user_meta['google_plus'];
                $item['tumblr'] = $user_meta['tumblr'];
                $item['instagram'] = $user_meta['instagram'];
                $item['skype'] = $user_meta['skype'];
                $item['zip'] = $user_meta['zip'];
                $item['tagline'] = $user_meta['tagline'];
                //Get education data
                foreach($user_meta['education'] as $key => $edu){
                    $item['education'] = unserialize($edu);
                }
                //Get award data
                foreach($user_meta['awards'] as $key => $award){
                    $item['awards'] = unserialize($award);
                }
                //Get experience data
                foreach($user_meta['experience'] as $key => $exp){
                    $item['experience'] = unserialize($exp);
                }

                $item['description'] = $user_meta['description'];
                //Get specialities data
                foreach($user_meta['user_profile_specialities'] as $key => $specialities){
                    $item['user_profile_specialities'] = unserialize($specialities);
                }
                //Get prices list
                foreach($user_meta['prices_list'] as $key => $price){
                    $item['prices_list'] = unserialize($price);
                }
                //Get Teams id
                foreach($user_meta['teams_data'] as $key => $teams){
                    $item['team'] =  unserialize($teams);
                }
				
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
				'booking_services' => '',
				'teams_data' => '');

				foreach( $meta_list as $key => $value ){
					$data  = get_user_meta($user->ID, $key, true);
					$item['all'][$key] = maybe_unserialize($data);
				}
				
                $items[] = $item;
            }
            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppDocDetailRoutes;
        $controller->register_routes();
    });
