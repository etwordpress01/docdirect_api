<?php
if (!class_exists('DocdirectAppUserScheduleRoutes')) {

    class DocdirectAppUserScheduleRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'schedule';
			
			register_rest_route($namespace, '/' . $base . '/user_schedule',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_schedule'),
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
        public function get_schedule($request) {
            if(!empty($request['user_id'])){
				$json 	= array();
                $items 	= array();
                $item 	= array();
                $user_identity= $request['user_id'];
                $db_schedules	= array();
                $db_schedules = get_user_meta( $user_identity, 'schedules', true);
                $time_format = get_user_meta( $user_identity, 'time_format', true);
                $schedules	= docdirect_get_week_array();
                $time_format	= get_option('time_format');
                $time_format	= !empty( $time_format ) ? $time_format : 'g:i A';
                if( isset( $schedules ) && !empty( $schedules ) ) {
                    foreach ($schedules as $key => $value) {
                        $start_time = isset($db_schedules[$key . '_start']) ? $db_schedules[$key . '_start'] : '';
                        $end_time = isset($db_schedules[$key . '_end']) ? $db_schedules[$key . '_end'] : '';
                        $item['value'] = $value;
                        $item['start time'] = $start_time;
                        $item['end time'] = $end_time;
                        $items[] = $item;
                    }
                }

				return new WP_REST_Response($items, 200);
            } else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect');
				return new WP_REST_Response($json, 203);
			}
        }
    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppUserScheduleRoutes;
    $controller->register_routes();
});
