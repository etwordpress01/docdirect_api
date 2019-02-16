<?php
if (!class_exists('DocdirectBookingListRoutes')) {

    class DocdirectBookingListRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'booking';

            register_rest_route($namespace, '/' . $base . '/user_booking',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_booking'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Booking list Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_booking($request)
        {
            if (!empty($request['user_id']))
            {
                global $paged;
                $dir_obj	= new DocDirect_Scripts();
                $user_id 	= $request['user_id'];
                $items 		= array();
                $item 				= array();
				$meta_query_args 	= array();

                if (function_exists('fw_get_db_settings_option')) {
                    $currency_select = fw_get_db_settings_option('currency_select');
                } else{
                    $currency_select = 'USD';
                }
				
				
                $meta_query_args[] = array(
                    'key'     => 'bk_user_to',
                    'value'   => $user_id,
                    'compare'   => '=',
                    'type'	  => 'NUMERIC'
                );

                if( !empty( $request['by_date'] ) ){
                    $meta_query_args[] = array(
                        'key'     => 'bk_timestamp',
                        'value'   => strtotime($request['by_date']),
                        'compare'   => '=',
                        'type'	  => 'NUMERIC'
                    );
                }

                $args 		= array( 'posts_per_page' => -1,
                    'post_type' => 'docappointments',
                    'post_status' => 'publish',
                    'ignore_sticky_posts' => 1,
                );

                if( !empty( $meta_query_args ) ) {
                    $query_relation = array('relation' => 'AND',);
                    $meta_query_args	= array_merge( $query_relation,$meta_query_args );
                    $args['meta_query'] = $meta_query_args;
                }

                $query 		= new WP_Query( $args );
                $count_post = $query->post_count;
                $args 		= array( 'posts_per_page' => -1,
                    'post_type' => 'docappointments',
                    'post_status' => 'publish',
                    'ignore_sticky_posts' => 1,
                    'order'	=> 'DESC',
                    'orderby'	=> 'ID',
                    'paged' => $paged,
                );


                if( !empty( $meta_query_args ) ) {
                    $query_relation = array('relation' => 'AND',);
                    $meta_query_args	= array_merge( $query_relation,$meta_query_args );
                    $args['meta_query'] = $meta_query_args;
                }
				
                $query 		= new WP_Query($args);
                $services_cats = get_user_meta($user_identity , 'services_cats' , true);
                $booking_services = get_user_meta($user_identity , 'booking_services' , true);
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');

                $counter	= 0;
                if( $query->have_posts() ){
                    while($query->have_posts()) : $query->the_post();
                        global $post;
                        $counter++;
                        $bk_code          = get_post_meta($post->ID, 'bk_code',true);
                        $bk_category      = get_post_meta($post->ID, 'bk_category',true);
                        $bk_service       = get_post_meta($post->ID, 'bk_service',true);
                        $bk_booking_date  = get_post_meta($post->ID, 'bk_booking_date',true);
                        $bk_slottime 	  = get_post_meta($post->ID, 'bk_slottime',true);
                        $bk_subject       = get_post_meta($post->ID, 'bk_subject',true);
                        $bk_username      = get_post_meta($post->ID, 'bk_username',true);
                        $bk_userphone 	  = get_post_meta($post->ID, 'bk_userphone',true);
                        $bk_useremail     = get_post_meta($post->ID, 'bk_useremail',true);
                        $bk_booking_note  = get_post_meta($post->ID, 'bk_booking_note',true);
                        $bk_payment       = get_post_meta($post->ID, 'bk_payment',true);
                        $bk_user_to       = get_post_meta($post->ID, 'bk_user_to',true);
                        $bk_timestamp     = get_post_meta($post->ID, 'bk_timestamp',true);
                        $bk_status        = get_post_meta($post->ID, 'bk_status',true);
                        $bk_user_from     = get_post_meta($post->ID, 'bk_user_from',true);
                        $bk_currency 	  = get_post_meta($post->ID, 'bk_currency', true);
                        $bk_paid_amount   = get_post_meta($post->ID, 'bk_paid_amount', true);
                        $bk_transaction_status = get_post_meta($post->ID, 'bk_transaction_status', true);

                        $payment_amount  = $bk_currency.$bk_paid_amount;

                        $time = explode('-',$bk_slottime);

                        $item['status'] = esc_attr( docdirect_prepare_order_status( 'value',$bk_status ) );
                        $item['id'] = intval( $post->ID );
                        $item['tracking_id'] = esc_attr( $bk_code );
                        $item['subject'] = esc_attr( $bk_subject );
                        $item['phone'] = esc_attr( $bk_userphone );
                        $item['user name'] = esc_attr( $bk_username );
                        $item['email'] =esc_attr( $bk_useremail );
                        if( !empty( $services_cats[$bk_category] ) ){
                            $item['category'] = $services_cats[$bk_category];
                        }
                        if( !empty( $booking_services[$bk_service] ) ){
                            $item['service'] = esc_attr( $booking_services[$bk_service]['title'] );
                        }
                        if( !empty( $bk_booking_date ) ){
                            $item['appointment_date'] = date($date_format,strtotime($bk_booking_date));
                        }
				
                        $item['meeting_time'] = date_i18n($time_format,strtotime('2016-01-01 '.$time[0]) );
                        $item['payment_type'] = esc_attr( docdirect_prepare_payment_type( 'value',$bk_payment ));
                        if( !empty( $payment_amount ) ){
                            $item['appointment_fee'] = $payment_amount;
                        }
                        if( !empty( $bk_transaction_status ) ){
                            $item['payment_status'] = docdirect_prepare_order_status( 'value',$bk_transaction_status );
                        }
                        if( !empty( $bk_booking_note ) ){
                            $item['notes'] = $bk_booking_note;
                        }
                        
						$items[] = $item;

                    endwhile;
				}
				
                return new WP_REST_Response($items, 200);
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectBookingListRoutes;
        $controller->register_routes();
    });
