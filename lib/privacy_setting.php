<?php
if (!class_exists('DocdirectAppPrivacySettingRoutes')) {

    class DocdirectAppPrivacySettingRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'privacy';

            register_rest_route($namespace, '/' . $base . '/setting',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'update_privacy_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Team Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_privacy_setting($request)
        {
            if (!empty($request['user_id'])) {
                $user_identity	= $request['user_id'];
                $json	= array();
                if(!empty($request['privacy'])){
                    update_user_meta( $user_identity, 'privacy', docdirect_sanitize_array( $request['privacy'] ) );
                }

                //update privacy for search
                if( !empty( $request['privacy'] ) ) {
                    foreach( $request['privacy'] as $key => $value ) {
                        update_user_meta( $user_identity, $key, esc_attr( $value ) );
                    }
                }

                $json['type']	= 'success';
                $json['message']	= esc_html__('Privacy Settings Updated.','docdirect');
                echo json_encode($json);
                die;
            }

        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppPrivacySettingRoutes;
        $controller->register_routes();
    });
