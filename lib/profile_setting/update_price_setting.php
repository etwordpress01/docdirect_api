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
                $user_data = get_user_meta($user_identity, 'prices_list', true);
                $user_data = !empty( $user_data ) ? $user_data : array();

                //Form Validation
                if( empty( $request['title'] ) 
                    || empty( $request['price'] )                                   
                    || empty( $request['description'] ) ) {

                    $json['type'] = 'error';
                    $json['message'] = esc_html__('All fields are required', 'docdirect');
                    return new WP_REST_Response($json, 200);  
                }

                //Prices
                $prices = array(
                    'title'         => $request['title'],
                    'price'         => $request['price'],
                    'description'   => $request['description']
                );

                $user_data[] = $prices;               
                update_user_meta($user_identity, 'prices_list', $user_data);
                
                $json['type'] = 'success';
                $json['message'] = esc_html__('Settings saved.', 'docdirect');
                return new WP_REST_Response($json, 200);
            }

            $json['type']       = 'error';
            $json['message']    = esc_html__('user_id Needed.', 'docdirect');
            return new WP_REST_Response($json, 200); 
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUpdatePriceSettingRoutes;
    $controller->register_routes();
});
