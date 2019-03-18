<?php
if (!class_exists('DocdirectUpdateAppointmentStatusRoutes')) {

    class DocdirectUpdateAppointmentStatusRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'appointment';

            register_rest_route($namespace, '/' . $base . '/update_appointment',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_appointment_status'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Wish list Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function update_appointment_status($request)
        {
            if(!empty($request['user_id']) && !empty($request['appointment_id']) && !empty($request['type']))
            {
                $user_id = $request['user_id'];

                $type = esc_attr($request['type']);
                $post_id = esc_attr($request['appointment_id']);

                if (empty($type)
                    ||
                    empty($post_id)
                ) {
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Some error occur, please try again later.', 'docdirect_api');
                    echo json_encode($json);
                    die;
                }

                if ($type === 'approve') {
                    $value = 'approved';

                    update_post_meta($post_id, 'bk_status', $value);

                    //Send Email
                    $email_helper = new DocDirectProcessEmail();
                    $emailData = array();
                    $emailData['post_id'] = $post_id;
                    $email_helper->process_appointment_approved_email($emailData);

                    //Send status
                    $json['action_type'] = $value;
                    $json['type'] = 'success';
                    $json['message'] = esc_html__('Appointment status has updated.', 'docdirect_api');
                    echo json_encode($json);
                    die;

                } else if ($type === 'cancel') {
                    $value = 'cancelled';

                    //Send Email
                    $email_helper = new DocDirectProcessEmail();
                    $emailData = array();
                    $emailData['post_id'] = $post_id;
                    $email_helper->process_appointment_cancelled_email($emailData);

                    update_post_meta($post_id, 'bk_status', $value);

                    //Return status
                    $json['action_type'] = $value;
                    $json['type'] = 'success';
                    $json['message'] = esc_html__('Appointment has been cancelled.', 'docdirect_api');
                    echo json_encode($json);
                    die;
                }

            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectUpdateAppointmentStatusRoutes;
        $controller->register_routes();
    });
