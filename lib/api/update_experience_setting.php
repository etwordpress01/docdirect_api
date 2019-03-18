<?php
/**
 * APP API to save expeience
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUpdateExperienceSettingRoutes')) {

    class DocdirectUpdateExperienceSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/experience_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_experience_setting'),
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
        public function update_experience_setting($request)
        {

            if(!empty($request['user_id'])){

                $user_identity = $request['user_id'];                
                $user_data = get_user_meta($user_identity, 'experience', true);
                $user_data = !empty( $user_data ) ? $user_data : array();

                //Form validation
                if( empty( $request['title'] ) 
                    || empty( $request['company'] )
                    || empty( $request['start_date'] )
                    || empty( $request['end_date'] )
                    || empty( $request['description'] ) ) {

                    $json['type'] = 'error';
                    $json['message'] = esc_html__('All fields are required', 'docdirect_api');
                    return new WP_REST_Response($json, 203);  
                }

                //Experience
                $experiences = array(
                    'title'         => $request['title'],
                    'company'       => $request['company'],
                    'start_date'    => $request['start_date'],
                    'end_date'      => $request['end_date'],
                    'start_date_formated' => date_i18n('M,Y', strtotime(esc_attr($value['start_date']))),
                    'end_date_formated'   => date_i18n('M,Y', strtotime(esc_attr($value['end_date']))),
                    'description'   => $request['description']
                );

                $user_data[] = $experiences;    
                update_user_meta($user_identity, 'experience', $user_data);
                
                $json['type']       = 'success';
                $json['message']    = esc_html__('Settings saved.', 'docdirect_api');
                return new WP_REST_Response($json, 200);       
            } else{
				$json['type'] = 'error';
				$json['message'] = esc_html__('User ID is required', 'docdirect_api');
				return new WP_REST_Response($json, 203);    
			}
              
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUpdateExperienceSettingRoutes;
    $controller->register_routes();
});
