<?php
if (!class_exists('DocdirectAppUpdateServiceCategoryRoutes')) {

    class DocdirectAppUpdateServiceCategoryRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/update_service_category',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_category'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * update service category
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function update_category($request){
            if(!empty($request['user_id'])){

                $user_identity	= $request['user_id'];

                $cat_title	 = esc_attr($request['title']);

                if( empty( $cat_title ) ){
                    $json['type']	= 'error';
                    $json['message']	= esc_html__('Please add title.','docdirect');
                    echo json_encode($json);
                    die;
                }

                $services_cats	= array();
                $key	 = !empty( $request['key'] ) ? esc_attr( $request['key'] ) : 'new';
                $type	 = !empty( $request['type'] ) ? esc_attr( $request['type'] ) : 'add';

                if( $key === 'new' ) {
                    $services_cats = get_user_meta($user_identity , 'services_cats' , true);
                    $services_cats	= empty( $services_cats ) ? array() : $services_cats;
                    $title	  = $cat_title;
                    $key	  = sanitize_title($title);

                    if ( !empty( $services_cats )
                        && array_key_exists($key, $services_cats)

                    ) {
                        $key	  = sanitize_title($title).docdirect_unique_increment(3);
                    }

                    $new_cat[$key]	=  $title;

                    $services_cats	= array_merge($services_cats,$new_cat);
                    $message	= esc_html__('Category added successfully.','docdirect');
                } else{
                    $services_cats = get_user_meta($user_identity , 'services_cats' , true);
                    $services_cats	= empty( $services_cats ) ? array() : $services_cats;
                    $title	= esc_attr ( $request['title'] );
                    $services_cats[$key]	= $title;
                    $message	= esc_html__('Category updated successfully.','docdirect');
                }

                update_user_meta( $user_identity, 'services_cats', $services_cats );

                $json['message_type']	 = 'success';
                $json['message']  = $message;
                echo json_encode($json);
                die;

            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppUpdateServiceCategoryRoutes;
        $controller->register_routes();
    });
