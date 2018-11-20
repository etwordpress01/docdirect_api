<?php
if (!class_exists('DocdirectUpdateUserLanguagesSettingRoutes')) {

    class DocdirectUpdateUserLanguagesSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/language_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_language_setting'),
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
        public function update_language_setting($request)
        {
            $json = array();
            if(!empty($request['user_id']))
            {
                $user_identity = $request['user_id'];                
                $user_data = get_user_meta($user_identity, 'languages', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
                
                //Form validation    
                if( empty( $request['key'] ) ){
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Language key needed', 'docdirect');
                    return new WP_REST_Response($json, 200);
                }

                //Languages 
                $name  = $request['key'];
                $user_data[$name] = $name;                                    
                update_user_meta($user_identity, 'languages', $user_data);   

                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
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
    $controller = new DocdirectUpdateUserLanguagesSettingRoutes;
    $controller->register_routes();
});
