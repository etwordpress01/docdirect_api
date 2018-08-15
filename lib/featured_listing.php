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
                        'callback' => array(&$this, 'get_items'),
                        'args' => array(
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_listing'),
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
         * Get categories
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_listing($request) {
			$args = wp_parse_args( $request );
			$args['count_total'] = false;
			$user_search = new WP_User_Query($args);
			$user_data = $user_search->get_results();
			$items	= array();
			foreach($user_data as $key => $val){
				$item = array();
				$user_meta = get_user_meta($val->ID);
				if($user_meta['user_featured']){
					$item = $user_meta;
				}
				
				$items[] = $item;
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
