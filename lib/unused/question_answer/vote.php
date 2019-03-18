<?php
if (!class_exists('DocdirectAppQuestionVoteRoutes')) {

    class DocdirectAppQuestionVoteRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/vote',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'set_vote'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * Set Vote
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function set_vote($request) {
            if(!empty($request['current_user'])){
                $current_user = $request['current_user'];
                $json = array();
                $key	= !empty( $request['key'] ) ? esc_attr( $request['key'] ) : '';
                $id		= !empty( $request['id'] ) ? intval( $request['id'] ) : 0;

                if(empty( $id ) || empty( $current_user )){ return;}

                $db_key	= 'total_votes';
                $count  = get_post_meta($id, $db_key, true);

                $vote_users = array();
                $vote_users = get_post_meta($id, 'vote_users', true);
                $vote_users = !empty($vote_users) && is_array($vote_users) ? $vote_users : array();

                if( in_array( $current_user, $vote_users) ){
                    do_action('fw_remove_user_from_votes',$id);
                    $count--;
                    update_post_meta($id, $db_key, $count);
                    $json['message'] = esc_html__('Your vote has removed', 'docdirect_api');
                } else{
                    do_action('fw_add_user_to_votes',$id);
                    $count++;
                    update_post_meta($id, $db_key, $count);
                    $json['message'] = esc_html__('Your vote has update', 'docdirect_api');
                }

                $json['vote'] = $count;
                $json['type'] = 'success';

                echo json_encode($json);
                die;
            }
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppQuestionVoteRoutes;
    $controller->register_routes();
});
