<?php
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
         * Get Featured Listing
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function set_wishlist($request)
        {
            if (isset($request['current_user']) && isset($request['requested_user']))
            {

            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectWishlistRoutes;
        $controller->register_routes();
    });
