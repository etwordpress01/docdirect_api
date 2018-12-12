<?php
if (!class_exists('DocdirectAppGetRemoveFromFavoriteRoutes')) {

    class DocdirectAppGetRemoveFromFavoriteRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/remove_favorite',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'remove_favorite'),
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
        public function remove_favorite($request)
        {
			$user_id  = $request['user_id'];
            $json     = array();	
            if( !empty( $user_id ) ) {
                $favourite_id = $request['favourite_id'];
                if( !empty( $favourite_id ) ){  
                    $remove_list = array();                  
                    $wishlist    = array();
                    $wishlist    = get_user_meta($user_id,'wishlist', true);
                    $wishlist    = !empty($wishlist) && is_array( $wishlist ) ? $wishlist : array();
                    $remove_list[] = $favourite_id;
                    $wishlist = array_diff( $wishlist, $remove_list );   
                    update_user_meta( $user_id, 'wishlist', $wishlist);
                    $json['type']       = 'success';
                    $json['message']    = esc_html__('Successfully, removed from your wishlist', 'docdirect_api');
                    return new WP_REST_Response($json, 200);
                } else {
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Favourite ID needed', 'docdirect_api');
                    return new WP_REST_Response($json, 200);
                }
            } else {
                $json['type']       = 'error';
                $json['message']    = esc_html__('User ID needed', 'docdirect_api');
                return new WP_REST_Response($json, 200);
            }	                        
        }
    }
}

add_action('rest_api_init',
function () {
    $controller = new DocdirectAppGetRemoveFromFavoriteRoutes;
    $controller->register_routes();
});
