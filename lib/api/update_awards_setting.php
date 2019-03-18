<?php
/**
 * APP API to update awards
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUpdateAwardsSettingRoutes')) {

    class DocdirectUpdateAwardsSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/awards_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_awards_setting'),
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
        public function update_awards_setting($request)
        {
            $json = array();
            if( !empty($request['user_id']) ){

                $user_identity = $request['user_id'];
                $user_data = get_user_meta($user_identity, 'awards', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
                
                //Form validation    
                if( empty( $request['name'] ) || empty( $request['date'] ) || empty( $request['description'] ) ){
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Kindly fill all fields', 'docdirect_api');
                    return new WP_REST_Response($json, 203);
                }

                //Awards 
                $awards = array(
                    'name' => $request['name'],
                    'date' => $request['date'],
                    'date_formated' => date_i18n('d M, Y', strtotime(esc_attr($value['date']))),
                    'description' => $request['description'],
                );

                $user_data[] = $awards;
                update_user_meta($user_identity, 'awards', $user_data);                
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect_api');
                return new WP_REST_Response($json, 200);
            }  else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
				return new WP_REST_Response($json, 203);
			}  
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUpdateAwardsSettingRoutes;
        $controller->register_routes();
    });
