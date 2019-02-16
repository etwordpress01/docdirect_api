<?php
if (!class_exists('DocdirectAppSubmitAnswerRoutes')) {

    class DocdirectAppSubmitAnswerRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/submit_answer',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'save_answer'),
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
        function save_answer($request) {
           if(!empty($request['current_user'])){
               $current_user = $request['current_user'];
               $json = array();

               remove_all_filters("content_save_pre");
			   
               if (empty($request['answer_description'])) {
                  $json['type']     = 'error';
                  $json['message']  = esc_html__('Answer description area should not be empty.', 'docdirect');
                  return new WP_REST_Response($json, 203);
               }

              $answer_detail = force_balance_tags($request['answer_description']);
              $question_id   = !empty( $request['question_id'] ) ? intval( $request['question_id'] ) : '';

              if ( empty( $question_id ) ) {
                  $json['type']     = 'error';
                  $json['message']  = esc_html__('Question ID must not be empty.', 'docdirect');
                  return new WP_REST_Response($json, 203);
              }

              $questions_answers_post = array(
                 'post_title' 	=> '',
                 'post_status' 	=> 'publish',
                 'post_content' 	=> $answer_detail,
                 'post_author' 	=> $current_user,
                 'post_type' 	=> 'sp_answers',
                 'post_parent'	=> $question_id,
                 'post_date' 	=> current_time('Y-m-d H:i:s')
              );

              $post_id = wp_insert_post($questions_answers_post);

              update_post_meta($post_id, 'answer_question_id', $question_id);
              update_post_meta($post_id, 'answer_user_id', $current_user);

              if (class_exists('DocDirectProcessEmail')) {
                   $email_helper = new DocDirectProcessEmail();
                   $emailData	= array();
                   $question_author = get_post_meta($question_id, 'question_by', true);
                   $emailData['answer_author']		= $current_user;
                   $emailData['question_author']	= $question_author;
                   $emailData['question_title']		= get_the_title($question_id);
                   $emailData['link']				= get_the_permalink($question_id);

                   //if method exist
                   if (method_exists($email_helper, 'process_answer_email')){
                       $email_helper->process_answer_email($emailData);
                   }
              }

              $json['type']     = 'success';
              $json['message'] = esc_html__('Answer submitted successfully.', 'docdirect');
              return new WP_REST_Response($json, 200);
            }else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect');
				return new WP_REST_Response($json, 203);
			}
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSubmitAnswerRoutes;
    $controller->register_routes();
});
