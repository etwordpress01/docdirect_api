<?php
/**
 * APP API to set privacy settings
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectAppPrivacySettingRoutes')) {

    class DocdirectAppPrivacySettingRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'privacy';

            register_rest_route($namespace, '/' . $base . '/setting',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'update_privacy_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Team Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_privacy_setting($request)
        {
            if (!empty($request['user_id'])) {
                $user_identity	= $request['user_id'];
                $json	= array();
				$privacy_array	= array();
                $privacy = array (
                    'appointments' => '',
                    'phone' => '',
                    'email' => '',
                    'contact_form' => '',
                    'opening_hours' => ''
                );
				
				//update privacy for search
                if( !empty( $privacy ) ) {
                    foreach( $privacy as $key => $value ) {
						$data	= !empty( $request[$key] ) ? $request[$key] : 'off';
                        update_user_meta( $user_identity, $key, esc_attr( $data ) );
						$privacy_array[$key] = $data;
                    }
                }
				
				
                if( !empty( $privacy_array ) ){
                    update_user_meta( $user_identity, 'privacy', $privacy_array );
                }


                $json['type']	= 'success';
                $json['message']	= esc_html__('Privacy Settings Updated.','docdirect_api');
                return new WP_REST_Response($json, 200); 

            } else {
                $json['type']   	= 'error';
                $json['message']    = esc_html__('User id is required','docdirect_api'); 
				return new WP_REST_Response($json, 203);
            }
            
        }

    }
}

add_action('rest_api_init',
function () {
    $controller = new DocdirectAppPrivacySettingRoutes;
    $controller->register_routes();
});
