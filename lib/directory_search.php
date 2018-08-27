<?php

if (!class_exists('DocdirectAppDirectorySearchRoute')) {

    class DocdirectAppDirectorySearchRoute extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 		= '1';
            $namespace 		= 'api/v' . $version;
            $base 			= 'directory_search';

            register_rest_route($namespace, '/' . $base . '/get_directory',
                    array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'get_directory_result'),
                    'args' => array(
                    ),
                ),
            ));
        }

        /**
         * Get Languages
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_directory_result($request) {
            global $paged,$wp_query;
            $item =  array();
            $items = array();
            $json	= array();
            $directories	= array();
            $meta_query_args = array();

            $city = '';
            $directory_type = '';
            $insurance = '';
            $speciality = '';

            //Category Search
            if( !empty( $request['directory_type'] ) ) {
                $directory_type = docdirect_get_page_by_slug( $request['directory_type'], 'directory_type','id' );
            }

            //City search
            if (!empty($request['city'])) {
                $city = !empty($request['city']) ? esc_attr($request['city']) : '';
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
            $location	   = !empty( $request['location'] ) ? $request['location'] : '';
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
            if( isset( $request['by_name'] ) && !empty( $request['by_name'] ) ) {
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
                        'value'   => serialize( strval( $insurance ) ),
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
                if( isset( $zip ) && !empty( $zip ) ){
                    $meta_query_args[] = array(
                        'key'     => 'zip',
                        'value'   => $zip,
                        'compare' => '='
                    );
                }

                //Location Search
                if( isset( $location ) && !empty( $location ) ){
                    $meta_query_args[] = array(
                        'key'     => 'location',
                        'value'   => $location,
                        'compare' => '='
                    );
                }

                //Language Search;
                if( !empty( $languages ) && !empty( $languages[0] ) && is_array( $languages ) ){
                    $query_relation = array('relation' => 'OR',);
                    $language_args	= array();
                    foreach( $languages as $key => $value ){
                        $language_args[] = array(
                            'key'     => 'languages',
                            'value'   => serialize( strval( $value ) ),
                            'compare' => 'LIKE'
                        );
                    }

                    $meta_query_args[]	= array_merge( $query_relation,$language_args );
                }

                //Speciality Search;
                if( !empty( $speciality ) && !empty( $speciality[0] ) && is_array( $speciality ) ){
                    $query_relation = array('relation' => 'OR',);
                    $speciality_args	= array();
                    foreach( $speciality as $key => $value ){
                        $speciality_args[] = array(
                            'key'     => $value,
                            'value'   => $value,
                            'compare' => '='
                        );
                    }

                    $meta_query_args[]	= array_merge( $query_relation,$speciality_args );
                }

                //Sub Category Search;
                if( !empty( $sub_category ) && !empty( $sub_category[0] ) && is_array( $sub_category ) ){
                    $query_relation = array('relation' => 'OR',);
                    $subcategory_args	= array();
                    foreach( $sub_category as $key => $value ){
                        $subcategory_args[] = array(
                            'key' 		=> 'doc_sub_categories',
                            'value'   	=> serialize( strval( $value ) ),
                            'compare' 	=> 'LIKE',
                        );
                    }

                    $meta_query_args[]	= array_merge( $query_relation,$subcategory_args );
                }

                //Verify user
                $meta_query_args[] = array(
                    'key'     => 'verify_user',
                    'value'   => 'on',
                    'compare' => '='
                );

                if( !empty( $meta_query_args ) ) {
                    $query_relation = array('relation' => 'AND',);
                    $meta_query_args	= array_merge( $query_relation,$meta_query_args );
                    $query_args['meta_query'] = $meta_query_args;
                }

                //Radius Search
                if( (isset($request['geo_location']) && !empty($request['geo_location'])) ){

                    $prepAddr   = '';
                    $minLat	 = '';
                    $maxLat	 = '';
                    $minLong	= '';
                    $maxLong	= '';

                    $address	 = !empty($request['geo_location']) ? $request['geo_location'] : '';
                    $prepAddr	= str_replace(' ','+',$address);

                    $Latitude   = !empty( $request['lat'] ) ? $request['lat'] : '';
                    $Longitude  = !empty( $request['long'] ) ? $request['long'] : '';

                    if( isset( $request['geo_distance'] ) && !empty( $request['geo_distance'] ) ){
                        $radius = $request['geo_distance'];
                    } else{
                        $radius = 300;
                    }

                    //Distance in miles or kilometers
                    if (function_exists('fw_get_db_settings_option')) {
                        $dir_distance_type = fw_get_db_settings_option('dir_distance_type');
                    } else{
                        $dir_distance_type = 'mi';
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

                        $url	    = 'http://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false';
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
                                    'type' 	=> 'DECIMAL(20,10)',
                                ),
                                array(
                                    'key' 		=> 'longitude',
                                    'value'   	  => array($minLong, $maxLong),
                                    'compare' 	=> 'BETWEEN',
                                    'type' 	=> 'DECIMAL(20,10)',
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

                $query_args	= apply_filters('docdirec_apply_extra_search_filters',$query_args);

                //Count total users for pagination
                $total_query    = new WP_User_Query( $query_args );

                $total_users	= $total_query->total_users;

                if(!empty( $geo_location ) && !empty( $directory_type )){
                    $found_title	= $total_users.'&nbsp;'.esc_html__('matche(s) found for','docdirect').'&nbsp;:&nbsp;<em>'.get_the_title($directory_type).'&nbsp;in&nbsp;'. $geo_location.'</em>';
                } else if( empty( $geo_location )&& !empty( $directory_type )){
                    $found_title	= $total_users.'&nbsp;'.esc_html__('matche(s) found for','docdirect').'&nbsp;:&nbsp;<em>'.get_the_title($directory_type).'</em>';

                } else if( !empty( $geo_location )&& empty( $directory_type )){
                    $found_title	= $total_users.'&nbsp;'.esc_html__('matche(s) found in','docdirect').'<em>&nbsp;'. $geo_location.'</em>';
                } else {
                    $found_title	= $total_users . esc_html__('&nbsp;matches found','docdirect');
                }


            $user_query  = new WP_User_Query($query_args);
            $direction	= docdirect_get_location_lat_long();
            $directories	=  array();
            $directories['status']	= 'none';
            $directories['lat']  = floatval ( $direction['lat'] );
            $directories['long'] = floatval ( $direction['long'] );
            if ( ! empty( $user_query->results ) ) {
                $directories['status'] = 'found';

                if (isset($directory_type) && !empty($directory_type)) {
                    $title = get_the_title($directory_type);
                    $postdata = get_post($directory_type);
                    $slug = $postdata->post_name;
                } else {
                    $title = '';
                    $slug = '';
                }

//                $item['title'] = $title;
//                $item['data'] = $postdata;

                foreach ( $user_query->results as $user ){
                    $latitude	   = get_user_meta( $user->ID, 'latitude', true);
                    $longitude	   = get_user_meta( $user->ID, 'longitude', true);
                    $directory_type = get_user_meta( $user->ID, 'directory_type', true);
                    $review_data	= docdirect_get_everage_rating ( $user->ID );
                    $get_username	= docdirect_get_username( $user->ID );
                    $reviews_switch    = fw_get_db_post_option($directory_type, 'reviews', true);
                    $avatar = apply_filters(
                        'docdirect_get_user_avatar_filter',
                        docdirect_get_user_avatar(array('width'=>270,'height'=>270), $user->ID),
                        array('width'=>270,'height'=>270) //size width,height
                    );
                    $user_link = get_author_posts_url($user->ID);
                    $item['username']		= $get_username;
                    $item['featured']	= get_user_meta( $user->ID, 'user_featured', true);;
                    $item['verify']	= get_user_meta($user->ID, 'verify_user', true);
                    $item['image']		= $avatar;
                    $item['url']		= $user_link;
                    $item['directory_type_name'] = get_the_title( $directory_type );
                    $item['directoty_type_slug'] = $slug;
                    $item['directory_type_url'] = esc_url( get_permalink($directory_type));
                    $item['latitude']	 = $latitude;
                    $item['longitude']	 = $longitude;
                    $item['fax']		 = $user->fax;
                    $item['description']  = $user->description;
                    $item['email']	 	= $user->user_email;
                    $item['phone_number'] = $user->phone_number;
                    $item['address']	  = $user->user_address;
                    $item['rating'] = number_format((float)$review_data['average_rating'], 1, '.', '');
                    $item['likes']    = get_user_meta($user->ID,'user_likes', true);
                    $items[] = $item;
                }

                //$items[] = $item;

            }


            return new WP_REST_Response($items, 200);
        }
    }

}
add_action('rest_api_init', function () {
    $controller = new DocdirectAppDirectorySearchRoute;
    $controller->register_routes();
});
