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
            $json	= array();
            $bloginfo 		   = get_bloginfo();
            $email_subject 	=  "(" . $bloginfo . ") Contact Form Received";
            $success_message 	= esc_html__('Message Sent.','docdirect');
            $failure_message 	= esc_html__('Message Fail.','docdirect');
            $recipient 	=  sanitize_text_field( $request['email_to'] );

            if( empty( $request['email_to'] )){
                $recipient = get_option( 'admin_email' ,'info@noreply.com' );
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Get the form fields and remove whitespace.

                if( empty( $request['username'] )
                    || empty( $request['useremail'] )
                    || empty( $request['userphone']  )
                    || empty( $request['usersubject']  )
                    || empty( $request['user_description']  )
                ){
                    $json['type']	= 'error';
                    $json['message']	= esc_html__('Please fill all fields.','docdirect');
                    echo json_encode($json);
                    die;
                }

                if( ! is_email($request['useremail']) ){
                    $json['type']	= 'error';
                    $json['message']	= esc_html__('Email address is not valid.','docdirect');
                    echo json_encode($json);
                    die;
                }

                $name	    = sanitize_text_field( $request['username'] );
                $email	  	= sanitize_text_field( $request['useremail'] );
                $subject	= sanitize_text_field( $request['usersubject'] );
                $phone	    = sanitize_text_field( $request['userphone'] );
                $message	= sanitize_text_field( $request['user_description'] );

                // Set the recipient email address.
                // FIXME: Update this to your desired email address.
                // Set the email subject.

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

                // Send the email.
                $json['type']    = "success";
                $json['message'] = esc_attr($success_message);
                echo json_encode( $json );
                die();
            } else {
                echo
                $json['type']    = "error";
                $json['message'] = esc_attr($failure_message);
                echo json_encode( $json );
                die();
            }
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSendContactMailRoutes;
    $controller->register_routes();
});
