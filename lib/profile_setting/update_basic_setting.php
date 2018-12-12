<?php
if (!class_exists('DocdirectUpdateBasicSettingRoutes')) {

    class DocdirectUpdateBasicSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/basic_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_basic_setting'),
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
        public function update_basic_setting($request){

            if(!empty($request['user_id'])){

                $user_identity = $request['user_id'];
                
				$basic_data	= array(
					'nickname' 			=> 'nickname',
					'first_name' 		=> 'first_name',
					'last_name' 		=> 'last_name',
					'phone_number' 		=> 'phone_number',
					'user_url'			=> 'user_url',
					'tagline' 			=> 'tagline',
					'zip' 				=> 'zip',
					'description' 		=> 'description',
				);
				
				//Update Basics
                if (!empty($basic_data)) {
                    foreach ($basic_data as $key => $value) {
						if( $key == 'user_url' ){
							wp_update_user( array( 'ID' => $user_identity, 'user_url' => esc_url($request[$key]) ) );
						} else{
							update_user_meta($user_identity, $key, $request[$key]);
						}
                    }
                }                
                
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
                return new WP_REST_Response($json, 200); 
            }

            $json['type']       = 'error';
            $json['message']    = esc_html__('user_id Needed.', 'docdirect');
            return new WP_REST_Response($json, 200); 
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUpdateBasicSettingRoutes;
        $controller->register_routes();
    });
