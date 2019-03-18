<?php
/**
 * APP API to update security settings
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectSecuritySettingRoutes')) {

    class DocdirectSecuritySettingRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'security';

            register_rest_route($namespace, '/' . $base . '/setting',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'set_security_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Set Security Settings
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function set_security_setting($request)
        {
            if (!empty($request['user_id'])){
                $user_identity	= $request['user_id'];
                $json	=  array();

                $user = get_userdata($user_identity); //trace($user);

                if(!empty($request['old_password'])){
                    $old_passowrd	= sanitize_text_field( $request['old_password'] );
                }
				
                if(!empty($request['new_password'])){
                    $new_passowrd	= sanitize_text_field( $request['new_password'] );
                }
				
                if(!empty($request['confirm_password'])){
                    $confirm_password	= sanitize_text_field( $request['confirm_password'] );
                }

                $is_password = wp_check_password( $old_passowrd, $user->user_pass, $user->data->ID );

                if( $is_password ){

                    if ( empty( $new_passowrd ) || empty( $confirm_password ) ) {
                        $json['type']		=  'error';
                        $json['message']		= esc_html__('Please add your new password.','docdirect_api');
                        return new WP_REST_Response($json, 203);
                    }

                    if ( $new_passowrd  === $confirm_password ) {
                        wp_update_user( array( 'ID' => $user_identity, 'user_pass' => esc_attr( $new_passowrd ) ) );
                        $json['type']		=  'success';
                        $json['message']		= esc_html__('Password Updated.','docdirect_api');
						return new WP_REST_Response($json, 200);
                    } else {
                        $json['type']		=  'error';
                        $json['message']		= esc_html__('The passwords you entered do not match. Your password was not updated', 'docdirect_api');
						return new WP_REST_Response($json, 203);
                    }
					
                } else{
                    $json['type']		=  'error';
                    $json['message']		= esc_html__('Old Password doesn\'t match the existing password', 'docdirect_api');
					return new WP_REST_Response($json, 200);
                }
            } else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
				return new WP_REST_Response($json, 203);
			}
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectSecuritySettingRoutes;
        $controller->register_routes();
    });
