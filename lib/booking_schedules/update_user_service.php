<?php
if (!class_exists('DocdirectUpdateUserServiceDataSettingRoutes')) {

    class DocdirectUpdateUserServiceDataSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/add_service',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_service_setting'),
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
        public function update_service_setting($request)
        {
            $json = array();           
            if(!empty($request['user_id']))
            {
                $user_identity = $request['user_id'];
                $service_title  = esc_attr ( $request['service_title'] );
                $service_price  = esc_attr ( $request['service_price'] );
                $service_category = esc_attr ( $request['service_category'] );
                
                if( empty( $service_title )
                    || empty( $service_price )
                    || empty( $service_category )
                 ){
                    $json['type']   = 'error';
                    $json['message']    = esc_html__('Please fill all the fields.','docdirect');
                    return new WP_REST_Response($json, 200);
                }
                
                $services   = array();                
                $type    = !empty( $request['key'] ) ? esc_attr( $request['key'] ) : 'new';
                $key      = sanitize_title($service_title).docdirect_unique_increment(3);
                if( $type === 'new' ) {
                    $services = get_user_meta($user_identity , 'booking_services' , true);
                    $services   = empty( $services ) ? array() : $services;
                    $key      = sanitize_title($service_title);

                    if ( !empty( $services )
                        && array_key_exists($key, $services)
                        
                    ) {
                        $key      = sanitize_title($service_title).docdirect_unique_increment(3);
                    }
                    
                    $new_service[$key]['title'] = $service_title;
                    $new_service[$key]['price'] = $service_price;
                    $new_service[$key]['category']  = $service_category;
                    
                    $services   = array_merge($services,$new_service);
                    $json['message']     = esc_html__('Service added successfully.','docdirect');
                } else{          
                    $services = get_user_meta($user_identity , 'booking_services' , true);
                    $services   = empty( $services ) ? array() : $services;
                    $service_title     = esc_attr ( $request['service_title'] );
                    $service_price     = esc_attr ( $request['service_price'] );
                    $service_category  = esc_attr ( $request['service_category'] );

                    $services[$key]['title']    = $service_title;
                    $services[$key]['price']    = $service_price;
                    $services[$key]['category'] = $service_category;                    
                    $json['message']    = esc_html__('Service updated successfully.','docdirect');
                }
                
                update_user_meta( $user_identity, 'booking_services', $services );
                return new WP_REST_Response($json, 200);
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
        $controller = new DocdirectUpdateUserServiceDataSettingRoutes;
        $controller->register_routes();
    });
