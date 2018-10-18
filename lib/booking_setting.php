<?php
if (!class_exists('DocdirectBookingSettingRoutes')) {

    class DocdirectBookingSettingRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'booking';

            register_rest_route($namespace, '/' . $base . '/settings',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'set_booking_setting'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Set Booking Setting
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function set_booking_setting($request)
        {
            if (!empty($request['user_id']))
            {
                $user_identity	= $request['user_id'];
                if(!empty($request['confirmation_title'])){
                    update_user_meta( $user_identity, 'confirmation_title', sanitize_text_field( $request['confirmation_title'] ) );
                }
                if(!empty($request['approved_title'])){
                    update_user_meta( $user_identity, 'approved_title', sanitize_text_field( $request['approved_title'] ) );
                }
                if(!empty($request['cancelled_title'])){
                    update_user_meta( $user_identity, 'cancelled_title', sanitize_text_field( $request['cancelled_title'] ) );
                }
                if(!empty($request['currency'])){
                    update_user_meta( $user_identity, 'currency', sanitize_text_field( $request['currency'] ) );
                }
                if(!empty($request['currency_symbol'])){
                    update_user_meta( $user_identity, 'currency_symbol', sanitize_text_field( $request['currency_symbol'] ) );
                }
                if(!empty($request['thank_you'])){
                    update_user_meta( $user_identity, 'thank_you', docdirect_sanitize_wp_editor( $request['thank_you'] ) );
                }
                if(!empty($request['schedule_message'])){
                    update_user_meta( $user_identity, 'schedule_message', docdirect_sanitize_wp_editor( $request['schedule_message'] ) );
                }
                if(!empty($request['booking_cancelled'])){
                    update_user_meta( $user_identity, 'booking_cancelled', docdirect_sanitize_wp_editor( $request['booking_cancelled'] ) );
                }
                if(!empty($request['booking_confirmed'])){
                    update_user_meta( $user_identity, 'booking_confirmed', docdirect_sanitize_wp_editor( $request['booking_confirmed'] ) );
                }
                if(!empty($request['booking_approved'])){
                    update_user_meta( $user_identity, 'booking_approved', docdirect_sanitize_wp_editor( $request['booking_approved'] ) );
                }
                if(!empty($request['paypal_enable'])){
                    update_user_meta( $user_identity, 'paypal_enable', sanitize_text_field( $request['paypal_enable'] ) );
                }
                if(!empty($request['paypal_email_id'])){
                    update_user_meta( $user_identity, 'paypal_email_id', sanitize_text_field( $request['paypal_email_id'] ) );
                }
                if(!empty($request['stripe_enable'])){
                    update_user_meta( $user_identity, 'stripe_enable', sanitize_text_field( $request['stripe_enable'] ) );
                }
                if(!empty($request['stripe_secret'])){
                    update_user_meta( $user_identity, 'stripe_secret', sanitize_text_field( $request['stripe_secret'] ) );
                }
                if(!empty($request['stripe_publishable'])){
                    update_user_meta( $user_identity, 'stripe_publishable', sanitize_text_field( $request['stripe_publishable'] ) );
                }
                if(!empty($request['stripe_site'])){
                    update_user_meta( $user_identity, 'stripe_site', sanitize_text_field( $request['stripe_site'] ) );
                }
                if(!empty($request['stripe_decimal'])){
                    update_user_meta( $user_identity, 'stripe_decimal', sanitize_text_field( $request['stripe_decimal'] ) );
                }
                $json['type'] = 'success';
                $json['message'] = esc_html__('Booking settings updated.','docdirect');

                echo json_encode($json);
                exit;
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectBookingSettingRoutes;
        $controller->register_routes();
    });
