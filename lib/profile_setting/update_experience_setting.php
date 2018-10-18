<?php
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

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Experience
                $experiences = array();
                if (!empty($request['experience'])) {
                    $counter = 0;
                    foreach ($request['experience'] as $key => $value) {
                        if (!empty($value['title']) && !empty($value['company'])) {
                            $experiences[$counter]['title'] = esc_attr($value['title']);
                            $experiences[$counter]['company'] = esc_attr($value['company']);
                            $experiences[$counter]['start_date'] = esc_attr($value['start_date']);
                            $experiences[$counter]['end_date'] = esc_attr($value['end_date']);
                            $experiences[$counter]['start_date_formated'] = date_i18n('M,Y', strtotime(esc_attr($value['start_date'])));
                            $experiences[$counter]['end_date_formated'] = date_i18n('M,Y', strtotime(esc_attr($value['end_date'])));
                            $experiences[$counter]['description'] = esc_attr($value['description']);
                            $counter++;
                        }
                    }
                    $json['experience'] = $experiences;
                }
                update_user_meta($user_identity, 'experience', $experiences);

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
        $controller = new DocdirectUpdateExperienceSettingRoutes;
        $controller->register_routes();
    });
