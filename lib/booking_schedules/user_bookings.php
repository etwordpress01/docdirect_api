<?php
if (!class_exists('DocdirectUserBookingsListingsRoutes')) {

    class DocdirectUserBookingsListingsRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/bookings_list',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'bookings_list'),
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
        public function bookings_list($request)
        {
            $json = array();           
            if(!empty($request['user_id']))
            {
                $user_identity = $request['user_id'];
                //testing

                $show_posts = get_option('posts_per_page') ? get_option('posts_per_page') : '-1';  
                //By Date   
                if( !empty( $request['by_date'] ) ){
                    $meta_query_args[] = array(
                        'key'     => 'bk_timestamp',
                        'value'   => strtotime($_GET['by_date']),
                        'compare'   => '=',
                        'type'    => 'NUMERIC'
                    );
                }
                $meta_query_args[] = array(
                    'key'     => 'bk_user_to',
                    'value'   => $user_identity,
                    'compare'   => '=',
                    'type'    => 'NUMERIC'
                );     

                $args       = array( 
                    'posts_per_page' => -1, 
                    'post_type' => 'docappointments', 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1,
                );
                                        
                if( !empty( $meta_query_args ) ) {
                    $query_relation = array('relation' => 'AND',);
                    $meta_query_args    = array_merge( $query_relation,$meta_query_args );
                    $args['meta_query'] = $meta_query_args;
                }

                $query      = new WP_Query( $args );
                if( $query->have_posts() ){
                    $json = array();
                    $items = array();
                    while($query->have_posts()) {
                        $query->the_post();
                        global $post;        
                        $date_format = get_option('date_format');
                        $time_format = get_option('time_format');                        
                        $bk_code          = get_post_meta($post->ID, 'bk_code',true);
                        $bk_category      = get_post_meta($post->ID, 'bk_category',true);
                        $bk_service       = get_post_meta($post->ID, 'bk_service',true);
                        $bk_booking_date  = get_post_meta($post->ID, 'bk_booking_date',true);
                        $bk_slottime      = get_post_meta($post->ID, 'bk_slottime',true);
                        $bk_subject       = get_post_meta($post->ID, 'bk_subject',true);
                        $bk_username      = get_post_meta($post->ID, 'bk_username',true);
                        $bk_userphone     = get_post_meta($post->ID, 'bk_userphone',true);
                        $bk_useremail     = get_post_meta($post->ID, 'bk_useremail',true);
                        $bk_booking_note  = get_post_meta($post->ID, 'bk_booking_note',true);
                        $bk_payment       = get_post_meta($post->ID, 'bk_payment',true);
                        $bk_user_to       = get_post_meta($post->ID, 'bk_user_to',true);
                        $bk_timestamp     = get_post_meta($post->ID, 'bk_timestamp',true);
                        $bk_status        = get_post_meta($post->ID, 'bk_status',true);
                        $bk_user_from     = get_post_meta($post->ID, 'bk_user_from',true);
                        $bk_currency      = get_post_meta($post->ID, 'bk_currency', true);
                        $bk_paid_amount   = get_post_meta($post->ID, 'bk_paid_amount', true);
                        $bk_transaction_status = get_post_meta($post->ID, 'bk_transaction_status', true);                        
                        $payment_amount  = $bk_currency.$bk_paid_amount;
                        
                        $time = explode('-',$bk_slottime); 
                        $meeting_start_time = date_i18n($time_format,strtotime('2016-01-01 '.$time[0]) );
                        $meeting_end_time   = date_i18n($time_format,strtotime('2016-01-01 '.$time[1]) );
                        $booking_date =  date($date_format,strtotime($bk_booking_date));
                        
                        $item = array();
                        $item['id'] = $post->ID;
                        $item['bk_code'] = $bk_code;
                        $item['bk_category'] = $bk_category;
                        $item['bk_service'] = $bk_service;
                        $item['bk_phone'] = $bk_userphone;
                        $item['start_time'] = $meeting_start_time;
                        $item['end_time'] = $meeting_end_time;
                        $item['user_name'] = $bk_username;
                        $item['bk_useremail'] = $bk_useremail;
                        $item['booking_date'] = $booking_date;
                        $item['status'] = $bk_status;
                        $item['payment_type'] = $bk_payment;
                        $item['appointment_fee'] = $payment_amount;
                        $item['payment_status'] = $bk_transaction_status;
                        $item['notes'] = $bk_booking_note;

                        $items[] = $item;                        
                    } wp_reset_postdata();
                    return new WP_REST_Response($items, 200);
                } else {
                    $json['type']       = 'success';
                    $json['message']    = esc_html__('Nothing Found', 'docdirect');
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
        $controller = new DocdirectUserBookingsListingsRoutes;
        $controller->register_routes();
    });
