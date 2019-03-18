<?php
if (!class_exists('DocdirectAppAnswerRoutes')) {

    class DocdirectAppAnswerRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/answer',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_answer'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * get answer
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function get_answer($request) {
           if(!empty($request['post_id'])){
               global $post;
               $item = array();
               $items = array();
               $post_id = $request['post_id'];

               $tqa_args = array('posts_per_page' => '-1',
                   'post_type' => 'sp_answers',
                   'orderby'   => 'ID',
                   'post_status' => 'publish'
               );

               if( !empty( $post_id ) ){
                   $meta_query_args	= array();
                   $meta_query_args[]  = array(
                       'key' 		=> 'answer_question_id',
                       'value' 	=> $post_id,
                       'compare' 	=> '=',
                   );

                   $query_relation = array('relation' => 'AND',);
                   $meta_query_args = array_merge($query_relation, $meta_query_args);
                   $tqa_args['meta_query'] = $meta_query_args;
               }

               $query = new WP_Query($tqa_args);
               while ($query->have_posts()) : $query->the_post();
                   $answer_id = $post->ID;
                   $author_id = $post->post_author;
                   $answer = $post->post_content;
                   if( !empty( $author_id ) ){
                       $user_profile_image = apply_filters(
                           'docdirect_get_user_avatar_filter',
                           docdirect_get_user_avatar(array('width'=>150,'height'=>150), $author_id),
                           array('width'=>150,'height'=>150) //size width,height
                       );
                   }
                   $pfx_date = get_the_date( 'Y-m-d', $answer_id );

                   $item['id']  =  $answer_id;
                   $item['answer']  =  $answer;
                   $item['author_name'] = the_author();
                   $item['author_image'] = $user_profile_image;
                   $item['author_link'] =  get_author_posts_url( $author_id);
                   $item['publish_date'] = human_time_diff(strtotime($pfx_date), current_time('timestamp')) . " ago";
                   $items[] = $item;
                endwhile;

           }
            return new WP_REST_Response($items, 200);

        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppAnswerRoutes;
    $controller->register_routes();
});
