<?php
if (!class_exists('DocdirectAppUpdateUserScheduleRoutes')) {

    class DocdirectAppUpdateUserScheduleRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'schedule';
			
			register_rest_route($namespace, '/' . $base . '/update_schedule',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'update_user_schedule'),
                        'args' => array(),
                    ),
                )
            );
        }
		

        /**
         * Get User Schedule
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_user_schedule($request)
        {
            if (!empty($request['user_id']) && !empty($request['schedules']) && !empty($request['time_format']) )
            {
                $items = array();
                $item = array();
                $json	= array();
                $user_identity	= $request['user_id'];
                $schedules	= docdirect_sanitize_array($request['schedules']);
                update_user_meta( $user_identity, 'schedules', $schedules );

                //Time Formate
                if( !empty( $request['time_format'] ) ){
                    update_user_meta( $user_identity, 'time_format', esc_attr( $request['time_format'] ) );
                }
                $json['type']	= 'success';
                $json['message']	= esc_html__('Schedules Updated.','docdirect');
                echo json_encode($json);
                die;

           }


        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppUpdateUserScheduleRoutes;
    $controller->register_routes();
});
