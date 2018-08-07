<?php

if (!class_exists('DocdirectApp_Location_Route')) {

    class DocdirectApp_Location_Route extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 		= '1';
            $namespace 		= 'api/v' . $version;
            $base 			= 'configs';

            register_rest_route($namespace, '/' . $base . '/get_locations',
                    array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(&$this, 'get_locations'),
                    'args' => array(
                    ),
                ),
            ));
        }

        /**
         * Get countries
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_locations($request) {
            // Get the categories for post and product post types
            $parent_terms = get_terms('locations',
										array('parent' => 0,
											  'orderby' => 'slug',
											  'hide_empty' => false
											 )
										);

            $locations = array();
            if ( !empty($parent_terms)) {
                foreach ($parent_terms as $key => $item) {
                    $locations[]	= $item;
                }
            }


            return new WP_REST_Response($locations, 200);
        }

    }

}
add_action('rest_api_init', function () {
    $controller = new DocdirectApp_Location_Route;
    $controller->register_routes();
});
