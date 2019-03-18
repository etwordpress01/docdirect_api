<?php
if (!class_exists('DocdirectAppDeleteUserServiceRoutes')) {

    class DocdirectAppDeleteUserServiceRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/delete_service',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'delete_service'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * delete service category
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function delete_service($request){
            if(!empty($request['user_id'])){
                $json = array();
                $user_identity	= $request['user_id'];
                $posted_key	 = sanitize_title($request['key']);
                if( empty( $posted_key ) ){
                    $json['type']	= 'error';
                    $json['message']	= esc_html__('Provide service key to remove','docdirect_api');
                    return new WP_REST_Response($json, 200);
                }                    
                
                $booking_services   = array();
                $booking_services = get_user_meta($user_identity , 'booking_services' , true);
        
                unset( $booking_services[$posted_key] );        
                update_user_meta( $user_identity, 'booking_services', $booking_services );             
                $json['message_type']	 = 'success';
                $json['message']  = esc_html__('Service deleted successfully.','docdirect_api');
                return new WP_REST_Response($json, 200);
            } else {
                $json['message_type']    = 'error';
                $json['message']  = esc_html__('User ID needed','docdirect_api');
                return new WP_REST_Response($json, 200);
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppDeleteUserServiceRoutes;
        $controller->register_routes();
    });
