<?php

if (!class_exists('DocdirectAppLanguagesRoute')) {

    class DocdirectAppLanguagesRoute extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 		= '1';
            $namespace 		= 'api/v' . $version;
            $base 			= 'configs';

            register_rest_route($namespace, '/' . $base . '/get_languages',
                    array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(&$this, 'get_languages'),
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
        public function get_languages( $request ) {
            $languages_array = array();           
            $languages_array	= docdirect_prepare_languages();//Get Language Array
            if( !empty( $languages_array ) ){
                $items	= array();
                $item	= array();
                foreach( $languages_array as $key => $value ) {
                    $item['key'] = $key;
                    $item['language'] = $value;
                    $items[] = $item;
                }                
                return new WP_REST_Response($items, 200);
            }

        }

    }

}
add_action('rest_api_init', function () {
    $controller = new DocdirectAppLanguagesRoute;
    $controller->register_routes();
});
