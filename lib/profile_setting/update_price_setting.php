<?php
if (!class_exists('DocdirectUpdatePriceSettingRoutes')) {

    class DocdirectUpdatePriceSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/price_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_price_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * update price setting
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_price_setting($request)
        {

            if(!empty($request['user_id']))
            {

                $user_identity = $request['user_id'];

                //Prices
                $prices = array();
                if (!empty($request['prices'])) {
                    $counter = 0;
                    foreach ($request['prices'] as $key => $value) {
                        if (!empty($value['title'])) {
                            $prices[$counter]['title'] = esc_attr($value['title']);
                            $prices[$counter]['price'] = esc_attr($value['price']);
                            $prices[$counter]['description'] = esc_attr($value['description']);
                            $counter++;
                        }
                    }
                    $json['prices_list'] = $prices;
                }

                update_user_meta($user_identity, 'prices_list', $prices);

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
        $controller = new DocdirectUpdatePriceSettingRoutes;
        $controller->register_routes();
    });
