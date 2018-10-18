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
        public function update_basic_setting($request)
        {

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];
                //Update Basics
                if (!empty($request['basics'])) {
                    foreach ($request['basics'] as $key => $value) {
                        update_user_meta($user_identity, $key, esc_attr($value));
                    }
                }

                update_user_meta($user_identity, 'show_admin_bar_front', false);

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
        $controller = new DocdirectUpdateBasicSettingRoutes;
        $controller->register_routes();
    });