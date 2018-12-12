<?php
if (!class_exists('DocdirectUserProviderServicesListingsRoutes')) {

    class DocdirectUserProviderServicesListingsRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/provider_services_list',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'provider_services_list'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Make Reviews Request
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function provider_services_list($request)
        {
            $json = array();           
            if(!empty($request['data_id']))
            {

                $user_identity = $request['data_id'];
                $user_data = get_user_meta($user_identity, 'booking_services', true);
                $user_data = !empty( $user_data ) ? $user_data : array();                
                if ( !empty( $user_data ) ) {
                    $new_list = array();                                
                    foreach ( $user_data as $key => $value ) {
                        $value['key']   = $key;
                        $new_list[]     = $value;   
                    }                       
                    return new WP_REST_Response($new_list, 200);                   
                } else {
                    $json['type'] = 'success';
                    $json['message'] = esc_html__('User has booking services yet', 'docdirect');
                    return new WP_REST_Response($json, 200);
                }                                                                             
            } 
            $json['type']       = 'error';
            $json['message']    = esc_html__('User ID needed', 'docdirect');
            return new WP_REST_Response($json, 200);           
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUserProviderServicesListingsRoutes;
        $controller->register_routes();
    });
