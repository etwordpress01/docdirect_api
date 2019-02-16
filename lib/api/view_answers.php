<?php
if (!class_exists('DocdirectAppViewQuestionAnswersRoutes')) {

    class DocdirectAppViewQuestionAnswersRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
    			register_rest_route($namespace, '/' . $base . '/view_answers',
                    array(
    					 array(
                            'methods' => WP_REST_Server::CREATABLE,
                            'callback' => array($this, 'view_answers'),
                            'args' => array(
                            ),
                        ),
                    )
                );
            }
		
	    /**
       * Save answer
       *
       * @param WP_REST_Request $request Full data about the request.
       * @return WP_Error|WP_REST_Response
       */
      function view_answers($request) {
        if(!empty($request['question_id'])){    
          $ques_id  = $request['question_id'];           
          $json     = array();            
          do_action('docdirect_is_action_allow'); //is action allow
          $posts_per_page = get_option('posts_per_page');   
          $q_args = array(
              'post_type'   => 'sp_answers',
              'post_status' => 'publish',
              'post_parent' => $ques_id,
              'posts_per_page'  => $posts_per_page,
              'order' => 'DESC',
          );     

          $q_query = new WP_Query($q_args);

          if ( $q_query->have_posts() ) {
              $json['type'] = 'success';
              $json['data'] = array();
              while ( $q_query->have_posts() ) {
                $q_query->the_post();
                global $post;
                $data = array();
                $answer_user_id = get_post_meta($post->ID, 'answer_user_id', true);  
                $user_name      = docdirect_get_username($answer_user_id);                 
                $user_avatar    = apply_filters(
                'docdirect_get_user_avatar_filter',
                   docdirect_get_user_avatar(array('width'=>150,'height'=>150), $answer_user_id),
                   array('width'=>150,'height'=>150) //size width,height
                );   
                $pfx_date           = get_the_date( 'Y-m-d', $post->ID );
                $date               = human_time_diff(strtotime($pfx_date), current_time('timestamp')) .'&nbsp;'. esc_html__('ago', 'docdirect'); 
                $total_votes        = get_post_meta($post->id, 'total_votes', true);
                $total_votes        = !empty( $total_votes ) ? $total_votes : 0;
                $data['user_name']  = $user_name;
                $data['content']    = get_the_content();
                $data['url']        = get_author_posts_url( $answer_user_id );
                $data['avatar']     = $user_avatar;
                $data['votes']      = $total_votes;
                $data['date']       = $date;
                $json['data'][]     = $data;                  
            }
          } else {
            $json['type']     = 'success';
            $json['message']  = esc_html__('No Answers found'. 'docdirect');
			return new WP_REST_Response($json, 200);
          }    
        } else {
          $json['type']     = 'error';
          $json['message']  = esc_html__('Question ID is required', 'docdirect');
		  return new WP_REST_Response($json, 203);
        }
      }
  }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppViewQuestionAnswersRoutes;
    $controller->register_routes();
});
