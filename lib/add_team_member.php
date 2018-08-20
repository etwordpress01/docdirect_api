<?php
if (!class_exists('DocdirectAppAddTeamRoutes')) {

    class DocdirectAppAddTeamRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'manage_team';

            register_rest_route($namespace, '/' . $base . '/add_team',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'add_team_members'),
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
        function add_team_members($request) {
            if (!empty($request['user_id']) && !empty($request['team_id']))
            {
                $user_id = $request['user_id'];

                $id = sanitize_text_field($request['team_id']);

                $teams	= array();
                $teams    = get_user_meta($user_id,'teams_data', true);
                $teams    = !empty($teams) && is_array( $teams ) ? $teams : array();

                if( !empty( $id ) ) {
                    $team_id	 = intval( $id );
                    $teams[]	 = $team_id;
                    $teams = array_unique($teams);
                    update_user_meta($user_id,'teams_data',$teams);

                    $json	= array();
                    $json['type']	= 'success';
                    $json['message']	= esc_html__('Successfully! added to your team members','docdirect');
                    echo json_encode($json);
                    die();
                }

                $json	= array();
                $json['type']	= 'error';
                $json['message']	= esc_html__('Oops! something is going wrong.','docdirect');
                echo json_encode($json);
                die();

        }   }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppAddTeamRoutes;
        $controller->register_routes();
    });
