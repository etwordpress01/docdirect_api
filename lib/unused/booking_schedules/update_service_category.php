<?php
if (!class_exists('DocdirectUpdateServiceCategorySettingRoutes')) {

    class DocdirectUpdateServiceCategorySettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/add_category',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_category_setting'),
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
        public function update_category_setting($request)
        {
            $json = array();           
            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];
                $user_data = get_user_meta($user_identity, 'services_cats', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
                
                //Form validation    
                if( empty( $request['title'] ) ){
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Kindly add category title', 'docdirect_api');
                    return new WP_REST_Response($json, 200);
                }
                
                $title    = $request['title'];
                $key      = sanitize_title($title);

                if ( !empty( $user_data )
                    && array_key_exists($key, $user_data)
                    
                ) {
                    $key      = sanitize_title($title).docdirect_unique_increment(3);
                }
                
                $new_cat[$key]  =  $title;
                
                $user_data  = array_merge($user_data,$new_cat);
                update_user_meta( $user_identity, 'services_cats', $user_data );
                              
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect_api');
                return new WP_REST_Response($json, 200);
            } 
            $json['type']       = 'error';
            $json['message']    = esc_html__('User ID needed', 'docdirect_api');
            return new WP_REST_Response($json, 200);           
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUpdateServiceCategorySettingRoutes;
        $controller->register_routes();
    });
