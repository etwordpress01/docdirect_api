<?php
if (!class_exists('DocdirectAppDeleteDeactivateAccountRoutes')) {

    class DocdirectAppDeleteDeactivateAccountRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'security';
			
			register_rest_route($namespace, '/' . $base . '/delete_deactivate_account',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'delete_account'),
                        'args' => array(),
                    ),
                )
            );
        }
		

        /**
         * Delete and De-Activate Account Setting
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function delete_account($request) {
            if(!empty($request['user_id']) && !empty($request['message']) ){
                $user_identity	=$request['user_id'];

                $json	=  array();
                $action				= sanitize_text_field( $request['process'] );
                $old_password		= sanitize_text_field( $request['old_password'] );
                $confirm_password	= sanitize_text_field( $request['confirm_password'] );
                $message			= sanitize_text_field( $request['message'] );
                $user               = get_userdata($user_identity); //trace($user)

                //Account Activation
                if( isset( $action ) && $action === 'activateme' ){
                    update_user_meta( $user->data->ID, 'profile_status', 'active' );
                    $json['type']		=  'success';
                    $json['message']		= esc_html__('Account activated..','docdirect_api');
                    echo json_encode($json);
                    die;
                }

                //Account de-activation

                if ( empty( $message ) ) {
                    $json['type']		=  'error';
                    $json['message']		= esc_html__('Please add some description','docdirect_api');
                    echo json_encode($json);
                    exit;
                }

                if ( empty($old_password ) || empty( $confirm_password ) ) {
                    $json['type']		=  'error';
                    $json['message']		= esc_html__('Please add your password and confirm password.','docdirect_api');
                    echo json_encode($json);
                    exit;
                }

                $is_password = wp_check_password( sanitize_text_field( $old_password ), $user->user_pass, $user->data->ID );

                if( $is_password ){
                    if ( $old_password == $confirm_password ) {
                        if( isset( $action ) && $action === 'deleteme' ){
                            wp_delete_user( $user->data->ID, 1 );

                            if( class_exists( 'DocDirectProcessEmail' ) ) {
                                $email_helper	= new DocDirectProcessEmail();

                                $emailData	= array();
                                $emailData['user_identity']	=  $user->data->ID;
                                $emailData['reason']		=  esc_attr( $message );
                                $emailData['email']	   		=  $user->data->user_email;;
                                $email_helper->delete_user_account($emailData);

                            } else{
                                docdirect_wp_user_delete_notification($user->data->ID,$message); //email to admin
                            }


                            $json['type']		=  'success';
                            $json['message']		= esc_html__('Account deleted.','docdirect_api');

                        } elseif( isset( $action ) && $action === 'deactivateme' ){
                            update_user_meta( $user->data->ID, 'profile_status', 'de-active' );
                            update_user_meta( $user->data->ID, 'deactivate_reason', $message );

                            $json['type']			=  'success';
                            $json['message']		= esc_html__('Account de-activated.','docdirect_api');
                        }

                    } else {
                        $json['type']		=  'error';
                        $json['message']		= esc_html__('The passwords you entered do not match.', 'docdirect_api');
                    }
                } else{
                    $json['type']		=  'error';
                    $json['message']		= esc_html__('Password not match.', 'docdirect_api');
                }

                echo json_encode($json);
                exit;

            }
             $json['type']		=  'error';
             $json['message']		= esc_html__('Fields must not be empty.', 'docdirect_api');
            echo json_encode($json);
            exit;
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppDeleteDeactivateAccountRoutes;
    $controller->register_routes();
});
