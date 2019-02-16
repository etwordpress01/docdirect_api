<?php
if (!class_exists('DocdirectAppSubmitQuestionRoutes')) {

    class DocdirectAppSubmitQuestionRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/submit_question',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'post_question'),
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
        function post_question($request) {
           if(!empty($request['current_user'])){               
              $json = array();

              remove_all_filters("content_save_pre");

              //Get title
              if (empty($request['question_title'])) {
                   $json['type'] = 'error';
                   $json['message'] = esc_html__('Question title should not be empty.', 'docdirect');
                   return new WP_REST_Response($json, 203);                              
              }

              //Get content
              if (empty($request['question_content'])) {
                  $json['type'] = 'error';
                  $json['message'] = esc_html__('Question description area should not be empty.', 'docdirect');                   
                  return new WP_REST_Response($json, 203);
              }
              
              //Get author
              if (empty($request['author_id'])) {
                  $json['type'] = 'error';
                  $json['message'] = esc_html__('Author ID required.', 'docdirect');           
                  return new WP_REST_Response($json, 203);
              }

              $question_title  = $request['question_title'];
              $question_detail = force_balance_tags($request['question_content']);

              //Add question
              $author_id = $request['author_id'];
              $questions_answers_post = array(
                  'post_title'    => $question_title,
                  'post_status'   => 'publish',
                  'post_content'  => $question_detail,
                  'post_author'   => $request['current_user'],
                  'post_type'     => 'sp_questions',
                  'post_date'     => current_time('Y-m-d H:i:s')
              );

              $post_id     = wp_insert_post($questions_answers_post);                    
              $category    = get_user_meta($author_id, 'directory_type', true);              
          
              update_post_meta($post_id, 'question_to', $author_id);
              update_post_meta($post_id, 'question_by', $request['current_user']);
              update_post_meta($post_id, 'question_cat', $category);
          
              if (class_exists('DocDirectProcessEmail')) {
                  if( isset( $type ) && $type === 'closed' && !empty( $author_id ) ){
                  $email_helper = new DocDirectProcessEmail();
                  $emailData  = array();
                  $emailData['user_id']     = $author_id;
                  $emailData['question_title']  = $question_title;
                  
                  //if method exist
                  if (method_exists($email_helper, 'process_question_email')){
                    $email_helper->process_question_email($emailData);
                  }
                } 
              }
              
			  $json['type'] = 'Success';
              $json['message'] = esc_html__('Question added successfully.', 'docdirect');
			  return new WP_REST_Response($json, 200);
            } else{
				$json['type']		= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect');
				return new WP_REST_Response($json, 203);
			}             
        }
    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSubmitQuestionRoutes;
    $controller->register_routes();
});
