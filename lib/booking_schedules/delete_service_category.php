<?php
if (!class_exists('DocdirectAppDeleteServiceCategoryRoutes')) {

    class DocdirectAppDeleteServiceCategoryRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/delete_service_category',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'delete_category'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * delete service category
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function delete_category($request){
            if(!empty($request['user_id'])){
                $json = array();
                $user_identity	= $request['user_id'];
                $posted_key	 = sanitize_title($request['key']);
                if( empty( $posted_key ) ){
                    $json['type']	= 'error';
                    $json['message']	= esc_html__('Provide category key to remove','docdirect');
                    return new WP_REST_Response($json, 200);
                }

                $services_cats	= array();
                $services_cats = get_user_meta($user_identity , 'services_cats' , true);

                if( !empty( $services_cats ) ){
                    unset( $services_cats[$posted_key] );
                }

                update_user_meta( $user_identity, 'services_cats', $services_cats );

                $json['message_type']	 = 'success';
                $json['message']  = esc_html__('Category deleted successfully.','docdirect');
                return new WP_REST_Response($json, 200);
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppDeleteServiceCategoryRoutes;
        $controller->register_routes();
    });
