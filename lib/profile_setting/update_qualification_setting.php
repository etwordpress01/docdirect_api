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

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Education
                $educations = array();
                if (!empty($request['education'])) {
                    $counter = 0;
                    foreach ($request['education'] as $key => $value) {
                        if (!empty($value['title']) && !empty($value['institute'])) {
                            $educations[$counter]['title'] = esc_attr($value['title']);
                            $educations[$counter]['institute'] = esc_attr($value['institute']);
                            $educations[$counter]['start_date'] = esc_attr($value['start_date']);
                            $educations[$counter]['end_date'] = esc_attr($value['end_date']);
                            $educations[$counter]['start_date_formated'] = date_i18n('M,Y', strtotime(esc_attr($value['start_date'])));
                            $educations[$counter]['end_date_formated'] = date_i18n('M,Y', strtotime(esc_attr($value['end_date'])));
                            $educations[$counter]['description'] = esc_attr($value['description']);
                            $counter++;
                        }
                    }
                    $json['education'] = $educations;
                }
                update_user_meta($user_identity, 'education', $educations);

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
        $controller = new DocdirectUpdateQualificationSettingRoutes;
        $controller->register_routes();
    });
