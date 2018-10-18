<?php

if (!class_exists('DocdirectAppInsuranceListRoute')) {

    class DocdirectAppInsuranceListRoute extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 		= '1';
            $namespace 		= 'api/v' . $version;
            $base 			= 'configs';

            register_rest_route($namespace, '/' . $base . '/insurance_list',
                    array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(&$this, 'get_insurance_list'),
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
        public function get_insurance_list($request) {
            $insurance_list = array();
            $insurance_list	= docdirect_prepare_taxonomies('directory_type','insurance',0,'array');
            if(!empty($insurance_list)){
                foreach( $insurance_list as $key => $insurance ){
                    $item['id']=$insurance->term_id;
                    $item['name']=$insurance->name;
                    $item['slug']=$insurance->slug;
                    $items[] = $item;
                }
                return new WP_REST_Response($items, 200);
            }


        }

    }

}
add_action('rest_api_init', function () {
    $controller = new DocdirectAppInsuranceListRoute;
    $controller->register_routes();
});