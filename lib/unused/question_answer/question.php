<?php
if (!class_exists('DocdirectAppQuestionRoutes')) {

    class DocdirectAppQuestionRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/question',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_question'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * Get Question Content by ID
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_question($request) {
            if(!empty($request['post_id'])){
                $post_id = $request['post_id'];
                $post_data = get_post( $post_id );
                $item = array();
                $items = array();
                $post_content = $post_data->post_content;
                $category 	 = get_post_meta($post_id, 'question_cat', true);
                $category_icon = fw_get_db_post_option($category, 'dir_icon', true);
                $total_votes = get_post_meta($post_id, 'total_votes', true);
                $question_total_ans 	= fw_ext_get_total_question_answers($post_id);
                $question_views = get_post_meta($post_id, 'question_views', true);
                $pfx_date = get_the_date( 'Y-m-d', $post_id );

                $item['post_id'] = $post_id;
                $item['post_title'] = $post_data->post_title;
                $item['post_content'] = $post_content;
                if (!empty($category_icon)){
                    $item['category_icon'] = esc_attr($category_icon);
                }
                $item['votes'] = intval($total_votes);
                $item['answers'] = intval($question_total_ans);
                $item['views'] = intval($question_views);
                $item['publish_date'] = human_time_diff(strtotime($pfx_date), current_time('timestamp')) . " ago";
                $items[] = $item;
            }

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppQuestionRoutes;
    $controller->register_routes();
});
