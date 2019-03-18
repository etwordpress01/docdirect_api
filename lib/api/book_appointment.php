<?php
/**
 * APP API to make booking
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUserBookingSettingRoutes')) {

    class DocdirectUserBookingSettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/appointment',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'add_appointment'),
                        'args' => array(),
                    ),
                )
            );
        }

        /**
         * Make Appointment Request
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function add_appointment($request)
        {
            $json = array();           
            if(!empty($request['user_id'])) {                
                global $post;
                $user_identity  = $request['user_id'];
                
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');

                //Validation
                if( empty( $request['bk_category'] ) ||
                    empty( $request['bk_service'] ) ||
                    empty( $request['slot_date'] ) ||
                    empty( $request['slot_time'] ) ||
                    empty( $request['subject'] ) ||
                    empty( $request['username'] ) ||
                    empty( $request['userphone'] ) ||
                    empty( $request['useremail'] ) ||
                    empty( $request['payment'] ) ||
                    empty( $request['data_id'] ) ||
                    empty( $request['booking_note'] )
                ){
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('All fields are required', 'docdirect_api');
                    return new WP_REST_Response($json, 203);
                }

                $bk_category        = esc_attr( $request['bk_category'] );
                $bk_service         = esc_attr( $request['bk_service'] );
                $booking_date       = esc_attr( $request['slot_date'] );
                $timestamp          = strtotime(esc_attr( $request['slot_date'] ));
                $slottime           = esc_attr( $request['slot_time'] );
                $subject            = esc_attr( $request['subject'] );
                $username           = esc_attr( $request['username'] );
                $userphone          = esc_attr( $request['userphone'] );
                $useremail          = esc_attr( $request['useremail'] );
                $booking_note       = esc_attr( $request['booking_note'] );
                $payment            = esc_attr( $request['payment'] );
                $user_to            = esc_attr( $request['data_id'] );
                $status             = 'pending';
                $user_from          = $user_identity;
                $bk_status          = 'pending';
                $payment_status     = 'pending';
                                
                //Add Booking
                $appointment = array(
                    'post_title'  => $subject,
                    'post_status' => 'publish',
                    'post_author' => $user_identity,
                    'post_type'   => 'docappointments',
                    'post_date'   => current_time('Y-m-d h')
                );
                
                //User Detail
                $currency            = get_user_meta( $user_to, 'currency', true);

                //Price
                $services = get_user_meta($user_to , 'booking_services' , true);
                if( !empty( $services[$bk_service]['price'] ) ){
                  $price    = $services[$bk_service]['price'];  
                }
                     
                $post_id  = wp_insert_post($appointment);
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                $appointment_no = substr($blogname,0,2).'-'.docdirect_unique_increment(5);
                
                $appointment_meta = array(
                    'bk_code'            => $appointment_no,
                    'bk_category'        => $bk_category,
                    'bk_service'         => $bk_service,
                    'bk_booking_date'    => $booking_date,
                    'bk_slottime'        => $slottime,
                    'bk_subject'         => $subject,
                    'bk_username'        => $username,
                    'bk_userphone'       => $userphone,
                    'bk_useremail'       => $useremail,
                    'bk_booking_note'    => $booking_note,
                    'bk_payment'         => $payment,
                    'bk_user_to'         => $user_to,
                    'bk_timestamp'       => $timestamp,
                    'bk_status'          => $bk_status,
                    'payment_status'     => $payment_status,
                    'bk_user_from'       => $user_from,
                    'bk_paid_amount'     => $price,
                    'bk_currency'        => $currency,
                    'bk_transaction_status'  => 'pending',
                    'bk_payment_date'       => date('Y-m-d H:i:s'),
                );
                
                $new_values = $appointment_meta;
                if ( isset( $post_id ) && !empty( $post_id ) ) {
                    fw_set_db_post_option($post_id, null, $new_values);
                }
                
                //Update post meta
                foreach( $appointment_meta as $key => $value ){
                    update_post_meta($post_id,$key,$value);
                }
         
                //Confirmation Email
                if( class_exists( 'DocDirectProcessEmail' ) ) {
                    //Send Email
                    $email_helper     = new DocDirectProcessEmail();
                    $emailData  = array();
                    $emailData['post_id']   = $post_id;
                    $email_helper->process_appointment_confirmation_email($emailData);
                    $email_helper->process_appointment_confirmation_admin_email($emailData);
                }
                    
                $json['type']       = 'success';
                $json['message']    = esc_html__('Your boooking submitted succesfully.','docdirect_api');
                return new WP_REST_Response($json, 200);                                     
            } else {
                $json['type']       = 'error';
                $json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
                return new WP_REST_Response($json, 203);           
            }
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUserBookingSettingRoutes;
    $controller->register_routes();
});
