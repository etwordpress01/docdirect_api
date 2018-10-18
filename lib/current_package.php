<?php
if (!class_exists('DocdirectAppCurrentPackageRoutes')) {

    class DocdirectAppCurrentPackageRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'packages';
			
			register_rest_route($namespace, '/' . $base . '/current_package',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_current_package'),
                        'args' => array(),
                    ),
                )
            );
        }
		

        /**
         * Get Current Package
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_current_package($request) {
            if(!empty($request['user_id'])){
                $items = array();
                $item = array();
                $current_date	= date('Y-m-d H:i:s');

                $user_identity	= $request['user_id'];
                $url_identity	= $user_identity;

                if( isset( $request['user_id'] ) && !empty( $request['user_id'] ) ){
                    $url_identity	= intval( $request['user_id'] );
                }
                $current_package	= get_user_meta($url_identity, 'user_current_package', true);
                $package_expiry		= get_user_meta($url_identity, 'user_current_package_expiry', true);
                $user_featured		= get_user_meta($url_identity, 'user_featured', true);
                $package_expiry	= !empty( $package_expiry ) ? date( 'Y-m-d', $package_expiry ) : '';

                if( !empty( $package_expiry ) && strtotime( $package_expiry )  > strtotime( $current_date ) ) {
                    $package_title	= !empty( $current_package ) ? get_the_title($current_package) : esc_html__('NILL','docdirect');
                }else{
                    $package_title	=  esc_html__('NILL','docdirect');
                }
                $item['Package Title'] = $package_title;
                $items[] = $item;

            }
            return new WP_REST_Response($items, 200);

        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppCurrentPackageRoutes;
    $controller->register_routes();
});
