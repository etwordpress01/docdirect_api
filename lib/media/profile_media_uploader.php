<?php
if (!class_exists('DocdirectAppImageUploaderRoutes')) {

    class DocdirectAppImageUploaderRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'media';

            register_rest_route($namespace, '/' . $base . '/upload_media',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'save_image'),
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
        public function save_image($request)
        {
            if (!empty($request['user_id']) && !empty($request['profile_image']))
            {

            }
            else
            {
                $json['error']	= 'Error';
                $json['message']	= esc_html__('please insert user and image id.','docdirect');
                echo json_encode($json);
                die;
            }

        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppImageUploaderRoutes;
        $controller->register_routes();
    });
