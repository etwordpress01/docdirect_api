<?php
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
                //Update Socials
                if (isset($request['socials']) && !empty($request['socials'])) {
                    foreach ( $request['socials'] as $key => $value ) {
                        update_user_meta($user_identity, $key, esc_attr($value));
                    }
                }
                do_action('docdirect_do_update_profile_settings', $_POST); //Save custom data
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
                echo json_encode($json);
                die;

            }
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectUpdateSocialSettingRoutes;
        $controller->register_routes();
    });
