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
			
			register_rest_route($namespace, '/' . $base . '/recent_question',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_recent_question'),
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
        function get_recent_question($request) {
            $item = array();
            $items = array();
            if(!empty($request['number_of_posts'])){
                $number_of_posts = $request['number_of_posts'];
            }else{
                $number_of_posts = 4;
            }

            $query_args = array(
                'posts_per_page' => $number_of_posts,
                'post_type' => 'sp_questions',
                'order' => 'DESC',
                'post_status' => 'publish',
                'orderby' => 'ID',
                'suppress_filters' => false,
                'ignore_sticky_posts' => 1
            );
            $query = new WP_Query($query_args);
            //return $query;
            if( $query->have_posts() ){
                while ($query->have_posts()) : $query->the_post();
                    global $post;
                    $post_id = $post->ID;
                    $category = get_post_meta($post_id, 'question_cat', true);
                    $category_icon = '';
                    if (!empty( $category )) {
                        $category_icon = fw_get_db_post_option($category, 'dir_icon', true);
                    }
                    $total_votes = get_post_meta($post_id, 'total_votes', true);
                    $question_total_ans 	= fw_ext_get_total_question_answers($post_id);

                    $item['id'] = $post_id;
                    $item['category_icon'] = $category_icon;
                    $item['title'] = get_the_title();
                    $item['url'] = get_permalink();
                    $item['votes'] = intval($total_votes);
                    $item['answers'] = intval($question_total_ans);
                    $items[] = $item;
                 endwhile;
            }
            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppQuestionVoteRoutes;
    $controller->register_routes();
});
