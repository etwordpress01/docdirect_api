<?php
if (!class_exists('DocdirectUpdateQualificationSettingRoutes')) {

    class DocdirectUpdateQualificationSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/qualification_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_qualification_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * update qualification setting
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_qualification_setting($request)
        {

            if(!empty($request['user_id'])) {

                $user_identity = $request['user_id'];
                $user_data = get_user_meta($user_identity, 'education', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
        
                //Form validation    
                if( empty( $request['title'] ) || 
                    empty( $request['institute'] ) || 
                    empty( $request['start_date'] ) || 
                    empty( $request['end_date'] ) || 
                    empty( $request['description'] ) ){

                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Kindly fill all fields', 'docdirect');
                    return new WP_REST_Response($json, 200);
                }

                //Education 
                $education = array(
                    'title'                 => $request['title'],
                    'institute'             => $request['institute'],
                    'start_date'            => $request['start_date'],
                    'end_date'              => $request['end_date'],
                    'start_date_formated'   => date_i18n('M,Y', strtotime(esc_attr($request['start_date']))),
                    'end_date_formated'     => date_i18n('M,Y', strtotime(esc_attr($request['end_date']))),                    
                    'description'           => $request['description'],
                );

                $user_data[] = $education;
                update_user_meta($user_identity, 'education', $user_data);   

                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
                return new WP_REST_Response($json, 200);                
            } else{
				 $json['type'] = 'error';
				$json['message'] = esc_html__('User ID is required', 'docdirect');
				return new WP_REST_Response($json, 203);
			}
           
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUpdateQualificationSettingRoutes;
    $controller->register_routes();
});
