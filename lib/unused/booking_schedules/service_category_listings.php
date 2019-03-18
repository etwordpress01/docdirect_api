<?php
if (!class_exists('DocdirectServiceCategoryListingsRoutes')) {

    class DocdirectServiceCategoryListingsRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/category_list',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'category_list'),
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
        public function category_list($request)
        {
            $json = array();           
            if(!empty($request['user_id'])) {

                $user_identity = $request['user_id'];
                $user_data = get_user_meta($user_identity, 'services_cats', true);
                $user_data = !empty( $user_data ) ? $user_data : array();
                                
                if ( !empty( $user_data ) ) {
                    $temp_list = array();
                    $new_list = array();
                    foreach ($user_data as $key => $value) {
                        $temp_list['title'] = $key;
                        $temp_list['data']  = $value;
                        $new_list[] = $temp_list;
                    }
                    return new WP_REST_Response($new_list, 200);
                } else {
                    $json['type'] 	 = 'error';
                    $json['message'] = esc_html__('User has no category to list', 'docdirect_api');
                    return new WP_REST_Response($json, 203);
                }                                                                             
            } else {
                $json['type']       = 'error';
                $json['message']    = esc_html__('User ID is required', 'docdirect_api');
                return new WP_REST_Response($json, 203);           
            }
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectServiceCategoryListingsRoutes;
        $controller->register_routes();
    });
