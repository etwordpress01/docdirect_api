<?php
if (!class_exists('DocdirectAppLatestProvidersRoutes')) {

    class DocdirectAppLatestProvidersRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'providers';

            register_rest_route($namespace, '/' . $base . '/latest_providers',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_latest_providers'),
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
        public function get_latest_providers($request)
        {
            $show_users	= 10;
            $order		 = 'DESC';

            $query_args	= array(
                'role'  => 'professional',
                'order' => $order,
                'number' => $show_users
            );

            $query_args['orderby']	   = 'ID';
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

                    $doc_type_id = get_user_meta( $user->ID, 'directory_type', true);
                    //$title = get_the_title($directory_type);
                    $postdata = get_post($doc_type_id);
                    $slug 	 = $postdata->post_name;
                    //$item =  $user;
                    $item['id'] = $user->id;
                    $item['author_url'] = get_author_posts_url($user->ID);
                    $item['verified'] =  docdirect_get_verified_tag(true,$user->ID,'','v2');
                    $item['img_url'] = $avatar;
                    $item['directory_type'] = $doc_type_id;
                    $item['directory_type_name'] = get_the_title( $doc_type_id );
                    $item['directoty_type_slug'] = $slug;
                    $item['directory_type_url'] = esc_url( get_permalink($doc_type_id));
                    $item['name'] = $user->first_name.' '.$user->last_name;
                    $item['category_color'] = fw_get_db_post_option($doc_type_id, 'category_color');
                    $items[] = $item;
                }

            }

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppLatestProvidersRoutes;
        $controller->register_routes();
    });
