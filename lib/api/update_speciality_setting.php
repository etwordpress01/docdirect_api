<?php
if (!class_exists('DocdirectUpdateUserSpecialitySettingRoutes')) {

    class DocdirectUpdateUserSpecialitySettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/speciality_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_speciality_setting'),
                        'args' => array(),
                    ),
                )
            );
        }

        /**
         * Make speciality settings
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_speciality_setting($request)
        {
            $json = array();
            if(!empty($request['user_id'])) {
                $user_identity = $request['user_id'];                
                $user_data = get_user_meta($user_identity, 'user_profile_specialities', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
                
                //Form validation    
                if( empty( $request['slug'] ) || empty( $request['name'] )){
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Speciality slug and name needed', 'docdirect');
                    return new WP_REST_Response($json, 200);
                }

                //Speciality 
                $slug  = $request['slug'];
                $name  = $request['name'];
                $user_data[$slug] = $name;                                    
                update_user_meta($user_identity, 'user_profile_specialities', $user_data);   

                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
                return new WP_REST_Response($json, 200);
            } else{
				$json['type']       = 'error';
				$json['message']    = esc_html__('User ID needed', 'docdirect');
				return new WP_REST_Response($json, 203);           
			}
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUpdateUserSpecialitySettingRoutes;
    $controller->register_routes();
});
