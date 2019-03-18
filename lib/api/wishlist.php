<?php
/**
 * APP API to save users in favorites
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectWishlistRoutes')) {

    class DocdirectWishlistRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'wishlist';

            register_rest_route($namespace, '/' . $base . '/user_wishlist',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'set_wishlist'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Wish list Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function set_wishlist($request){
            if (!empty($request['wl_id']) && !empty($request['user_id'])){
                $user_id = $request['user_id'];
				
                $wishlist	 = array();
				$json		 = array();
                $wishlist    = get_user_meta($user_id,'wishlist', true);
                $wishlist    = !empty($wishlist) && is_array( $wishlist ) ? $wishlist : array();
                $wl_id		 = sanitize_text_field( $request['wl_id'] );

                if( !empty( $wl_id ) ) {
                    $wishlist[]	= $wl_id;
                    $wishlist = array_unique($wishlist);
                    update_user_meta($user_id,'wishlist',$wishlist);
                    
                    $json['type']	= 'success';
                    $json['message']	= esc_html__('Successfully! added to your favorites','docdirect_api');
					return new WP_REST_Response($json, 200);
                } else{
					$json['type']	= 'error';
                    $json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
					return new WP_REST_Response($json, 203);
				}
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectWishlistRoutes;
        $controller->register_routes();
    });
