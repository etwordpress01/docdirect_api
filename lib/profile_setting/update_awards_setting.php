<?php
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

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Awards
                $awards = array();
                if (!empty($request['awards'])) {

                    $counter = 0;
                    foreach ($request['awards'] as $key => $value) {
                        $awards[$counter]['name'] = esc_attr($value['name']);
                        $awards[$counter]['date'] = esc_attr($value['date']);
                        $awards[$counter]['date_formated'] = date_i18n('d M, Y', strtotime(esc_attr($value['date'])));
                        $awards[$counter]['description'] = esc_attr($value['description']);
                        $counter++;
                    }
                    $json['awards'] = $awards;
                }
                update_user_meta($user_identity, 'awards', $awards);

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
        $controller = new DocdirectUpdateAwardsSettingRoutes;
        $controller->register_routes();
    });
