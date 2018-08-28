<?php
if (!class_exists('DocdirectSubmitProfileSettingRoutes')) {

    class DocdirectSubmitProfileSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'setting';

            register_rest_route($namespace, '/' . $base . '/profile_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'submit_profile_setting'),
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
        public function submit_profile_setting($request)
        {

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Professional Statements
                if (!empty($request['professional_statements'])) {
                    $professional_statements = docdirect_sanitize_wp_editor($request['professional_statements']);
                    update_user_meta($user_identity, 'professional_statements', $professional_statements);
                }

                //update username
                $full_name = docdirect_get_username($user_identity);
                update_user_meta($user_identity, 'full_name', esc_attr($full_name));
                update_user_meta($user_identity, 'username', esc_attr($full_name));

                //Update General settings

                update_user_meta($user_identity, 'video_url', esc_url($request['video_url']));
                wp_update_user(array('ID' => $user_identity, 'user_url' => esc_url($request['basics']['user_url'])));

                //Specialities
                $db_directory_type = get_user_meta($user_identity, 'directory_type', true);
                if (isset($db_directory_type) && !empty($db_directory_type)) {
                    $specialities_list = docdirect_prepare_taxonomies('directory_type', 'specialities', 0, 'array');
                }

                $specialities = array();
                $submitted_specialities = docdirect_sanitize_array($request['specialities']);

                //limit specialities
                if (function_exists('fw_get_db_settings_option')) {
                    $speciality_limit = fw_get_db_settings_option('speciality_limit');
                }
                $speciality_limit = !empty($speciality_limit) ? $speciality_limit : '50';
                $submitted_specialities = array_slice($submitted_specialities, 0, $speciality_limit);

                if (isset($specialities_list) && !empty($specialities_list)) {
                    $counter = 0;
                    foreach ($specialities_list as $key => $speciality) {
                        if (isset($submitted_specialities)
                            && is_array($submitted_specialities)
                            && in_array($speciality->slug, $submitted_specialities)
                        ) {
                            update_user_meta($user_identity, $speciality->slug, esc_attr($speciality->slug));
                            $specialities[$speciality->slug] = $speciality->name;
                        } else {
                            update_user_meta($user_identity, $speciality->slug, '');
                        }

                        $counter++;
                    }
                }

                update_user_meta($user_identity, 'user_profile_specialities', $specialities);

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

                //Languages
                $languages = array();
                if (isset($request['language']) && !empty($request['language'])) {
                    $counter = 0;
                    foreach ($request['language'] as $key => $value) {
                        $db_value = esc_attr($value);
                        $languages[$db_value] = $db_value;
                        $counter++;
                    }
                }
                update_user_meta($user_identity, 'languages', $languages);


                //Insurance
                $insurance = array();

                if (isset($request['insurance']) && !empty($request['insurance'])) {
                    $counter = 0;
                    foreach ($request['insurance'] as $key => $value) {
                        $db_value = esc_attr($value);
                        $insurance[$db_value] = $db_value;
                        $counter++;
                    }

                    $insurance = array_filter($insurance);
                }

                update_user_meta($user_identity, 'insurance', $insurance);

                //Update sub categories
                if (!empty($request['subcategory'])) {
                    $subcategories = array();
                    $counter = 0;
                    foreach ($request['subcategory'] as $key => $value) {
                        $db_value = esc_attr($value);
                        $subcategories[$db_value] = $db_value;
                        $counter++;
                    }

                    $subcategories = array_filter($subcategories);
                    update_user_meta($user_identity, 'doc_sub_categories', $subcategories);
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
        $controller = new DocdirectSubmitProfileSettingRoutes;
        $controller->register_routes();
    });
