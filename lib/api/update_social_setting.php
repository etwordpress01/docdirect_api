<?php
/**
 * APP API to save social settings
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUpdateSocialSettingRoutes')) {

    class DocdirectUpdateSocialSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/social_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'update_social_setting'),
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
        public function update_social_setting($request)
        {

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Form Validation
                if( empty( $request['name'] ) || empty( $request['url'] ) ){
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Name and URL needed', 'docdirect_api');
                    return new WP_REST_Response($json, 200);
                }

                //Social data
                $key   = $request['name'];
                $value = $request['url'];
               
                update_user_meta( $user_identity, $key, esc_url( $value ) );               
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect_api');
                return new WP_REST_Response($json, 200); 
            }

            $json['type']       = 'error';
            $json['message']    = esc_html__('user_id Needed.', 'docdirect_api');
            return new WP_REST_Response($json, 200); 
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUpdateSocialSettingRoutes;
        $controller->register_routes();
    });
