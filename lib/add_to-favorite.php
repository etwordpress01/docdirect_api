<?php
if (!class_exists('DocdirectAppGetAddToFavoriteRoutes')) {

    class DocdirectAppGetAddToFavoriteRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/add_to_favorites',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'add_to_favorites'),
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
        public function add_to_favorites($request)
        {
			
			$current_user_id	= $request['current_user_id'];
			$provider_id	= $request['provider_id'];
            
            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppGetAddToFavoriteRoutes;
        $controller->register_routes();
    });
