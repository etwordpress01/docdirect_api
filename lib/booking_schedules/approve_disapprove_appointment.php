<?php
if (!class_exists('DocdirectApproveAppointmentSettingRoutes')) {

    class DocdirectApproveAppointmentSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/change_appointment_status',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'change_appointment_status'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Make Reviews Request
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function change_appointment_status($request)
        {
            $json = array();           
            if(!empty($request['user_id']))
            {
                $user_identity = $request['user_id'];                
                $type          = esc_attr( $request['type']);
                $post_id       = esc_attr( $request['id'] );
                
                if( empty( $type ) || empty( $post_id ) ){
                    $json['type']   = 'error';
                    $json['message']    = esc_html__('Please provide required information','docdirect');
                    return new WP_REST_Response($json, 200);   
                }

                if( $type === 'approve' ){
                    $value  = 'approved';
                    
                    update_post_meta($post_id,'bk_status',$value);
                    
                    //Send Email
                    $email_helper     = new DocDirectProcessEmail();
                    $emailData  = array();
                    $emailData['post_id']   = $post_id;
                    $email_helper->process_appointment_approved_email($emailData);
                    
                    //Send status                  
                    $json['type']          = 'success';
                    $json['message']        = esc_html__('Appointment status has been updated.','docdirect');
                    return new WP_REST_Response($json, 200);   
                
                } else if( $type === 'cancel' ){
                    $value  = 'cancelled';

                    //Send Email
                    $email_helper     = new DocDirectProcessEmail();
                    $emailData  = array();
                    $emailData['post_id']   = $post_id;
                    $email_helper->process_appointment_cancelled_email($emailData);
                    
                    update_post_meta($post_id,'bk_status',$value);
                    
                    //Return status                   
                    $json['type']          = 'success';
                    $json['message']        = esc_html__('Appointment has been cancelled.','docdirect');
                    return new WP_REST_Response($json, 200);   
                }                                
            } else {
                $json['type']       = 'error';
                $json['message']    = esc_html__('User ID needed', 'docdirect');
                return new WP_REST_Response($json, 200);           
            }
        }
    }
}

add_action('rest_api_init',
    function ()
    {
        $controller = new DocdirectApproveAppointmentSettingRoutes;
        $controller->register_routes();
    });
