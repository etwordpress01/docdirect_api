<?php
/**
 * APP API to get languages
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
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
			$json = array();          
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
            } else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
				return new WP_REST_Response($json, 203);
			}
        }
    }
}
add_action('rest_api_init', function () {
    $controller = new DocdirectAppLanguagesRoute;
    $controller->register_routes();
});
