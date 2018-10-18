<?php
if (!class_exists('DocdirectAppSubmitClaimRoutes')) {

    class DocdirectAppSubmitClaimRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'claim';
			
			register_rest_route($namespace, '/' . $base . '/submit_claim',
                array(
					  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'send_submit_claim'),
                        'args' => array(),
                    ),
                )
            );
        }
		
		/**
         * Submit Claim
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function send_submit_claim($request) {
            $json	= array();

            $user_to	= !empty( $request['user_to'] ) ? intval( $request['user_to'] ) : '';
            $user_from  = $request['current_user'];

            $subject	= sanitize_text_field( $request['subject'] );
            $report	    = sanitize_text_field( $request['report'] );

            if( empty( $subject )
                ||
                empty( $report )
                ||
                empty( $user_to )
                ||
                empty( $user_from )
            ) {
                $json['type']	   = 'error';
                $json['message']	= esc_html__('Please fill all the fields.','docdirect');
                echo json_encode($json);
                die;
            }


            $claim_post = array(
                'post_title'  => $subject,
                'post_status' => 'publish',
                'post_content'=> $report,
                'post_author' => $user_from,
                'post_type'   => 'doc_claims',
                'post_date'   => current_time('Y-m-d H:i:s')
            );

            $post_id = wp_insert_post( $claim_post );

            $claim_meta = array(
                'subject' 	  => $subject,
                'user_from'   => $user_from,
                'user_to'     => $user_to,
                'report'  	  => $report,
            );

            //Update post meta
            foreach( $claim_meta as $key => $value ){
                update_post_meta($post_id,$key,$value);
            }

            $new_values = $claim_meta;

            if (isset($post_id) && !empty($post_id)) {
                fw_set_db_post_option($post_id, null, $new_values);
            }

            if( class_exists( 'DocDirectProcessEmail' ) ) {
                $email_helper	= new DocDirectProcessEmail();
                $emailData	   = array();

                $emailData['claimed_user_name']	= docdirect_get_username($user_to);
                $emailData['claimed_by_name']	= docdirect_get_username($user_from);
                $emailData['claimed_user_link']	= get_author_posts_url($user_to);
                $emailData['claimed_by_link']	= get_author_posts_url($user_from);
                $emailData['message']			= $report;

                $email_helper->process_claim_admin_email( $emailData );
            }


            $json['type']	   = 'success';
            $json['message']	= esc_html__('Your report received successfully.','docdirect');
            echo json_encode($json);
            die;
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSubmitClaimRoutes;
    $controller->register_routes();
});
