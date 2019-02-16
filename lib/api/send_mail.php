<?php
if (!class_exists('DocdirectAppSendContactMailRoutes')) {

    class DocdirectAppSendContactMailRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'mail';
			
			register_rest_route($namespace, '/' . $base . '/send_contact_mail',
                array(
					  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'send_email'),
                        'args' => array(),
                    ),
                )
            );
        }
		
		/**
         * Send Contact Email
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function send_email($request) {
			$json				= array();
            $recipient 			= sanitize_text_field( $request['email_to'] );

            if( empty( $recipient )){
                $json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect');	
				return new WP_REST_Response($json, 203);
            }

			$bloginfo 		    = get_bloginfo();
			$email_subject 		=  "(" . $bloginfo . ") " . esc_html__('Contact Form Received','docdirect');
			$success_message 	= esc_html__('Message Sent.','docdirect');
			$failure_message 	= esc_html__('Message Fail.','docdirect');

			$recipient 	=  sanitize_text_field( $_POST['email_to'] );

			if( empty( $_POST['username'] )
				|| empty( $_POST['useremail'] ) 
				|| empty( $_POST['userphone']  ) 
				|| empty( $_POST['usersubject']  ) 
				|| empty( $_POST['user_description']  )
			){
				$json['type']	= 'error';
				$json['message']	= esc_html__('Please fill all fields.','docdirect');	
				return new WP_REST_Response($json, 203);
			}

			if( ! is_email($_POST['useremail']) ){
				$json['type']	= 'error';
				$json['message']	= esc_html__('Email address is not valid.','docdirect');	
				return new WP_REST_Response($json, 203);
			}

			$name	    = sanitize_text_field( $_POST['username'] );
			$email	  	= sanitize_text_field( $_POST['useremail'] );
			$subject	= sanitize_text_field( $_POST['usersubject'] );
			$phone	    = sanitize_text_field( $_POST['userphone'] );
			$message	= sanitize_text_field( $_POST['user_description'] );

			if( class_exists( 'DocDirectProcessEmail' ) ) {
				$email_helper	= new DocDirectProcessEmail();
				$emailData	   = array();
				$emailData['name']	  	       = $name;
				$emailData['email']			   = $email;
				$emailData['email_subject']	   = $email_subject;
				$emailData['subject']	  	    = $subject;
				$emailData['phone']	 		    = $phone;					
				$emailData['message']			= $message;
				$emailData['email_to']			= $recipient;

				$email_helper->process_contact_user_email( $emailData );
			}

			// Send response
			$json['type']    = "success";
			$json['message'] = esc_attr($success_message);
			return new WP_REST_Response($json, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSendContactMailRoutes;
    $controller->register_routes();
});
