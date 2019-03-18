<?php
/**
 * APP API to get time slots for bookings
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectGetBookingTimeSlotsRoutes')) {

    class DocdirectGetBookingTimeSlotsRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'booking_schedule';

            register_rest_route($namespace, '/' . $base . '/get_timeslots',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_timeslots'),
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
        public function get_timeslots($request){
            $json = array();           
            if(!empty($request['user_id'])){                                            
                $user_id    = sanitize_text_field( $request['user_id'] );
                $slot_date  = sanitize_text_field( $request['slot_date'] );
                if( empty( $slot_date ) ) {
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Date should not be empty', 'docdirect_api');
                    return new WP_REST_Response($json, 203);
                }
				
                if( !empty( $slot_date ) ){
                    $day        = strtolower(date('D',strtotime( $slot_date )));
                    $current_date_string    = date_i18n('M d, l',strtotime($slot_date));
                    $current_date   = $slot_date;
                    $slot_date      = $slot_date;
                } else{
                    $day        = strtolower(date('D'));
                    $current_date_string    = date_i18n('M d, l');
                    $current_date   = date('Y-m-d');
                    $slot_date     = date('Y-m-d');
                }

                $week_days  = docdirect_get_week_array();
                
                $default_slots  = array();
                $default_slots = get_user_meta($user_id , 'default_slots' , false);
                $time_format   = get_option('time_format');
                
                //Custom Slots
                $custom_slot_list   = docdirect_custom_timeslots_filter($default_slots,$user_id);

                //Get booked Appointments
                $year     = date_i18n('Y',strtotime($slot_date));
                $month    = date_i18n('m',strtotime($slot_date));
                $day_no   = date_i18n('d',strtotime($slot_date));

                $start_timestamp = strtotime($year.'-'.$month.'-'.$day_no.' 00:00:00');
                $end_timestamp = strtotime($year.'-'.$month.'-'.$day_no.' 23:59:59');
                

                $args       = array('posts_per_page' => -1, 
                    'post_type' => 'docappointments', 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1,
                    'meta_query' => array(
                        array(
                            'key'     => 'bk_timestamp',
                            'value'   => array( $start_timestamp, $end_timestamp ),
                            'compare' => 'BETWEEN'
                        ),
                        array(
                            'key'     => 'bk_user_to',
                            'value'   => $user_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'bk_status',
                            'value' => array('approved', 'pending'),
                            'compare' => 'IN'
                        )
                    )
                );
                    
                $query      = new WP_Query($args);
                $count_post = $query->post_count;        
                $appointments_array = array();
                while($query->have_posts()) : $query->the_post();
                    global $post;
                    
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
                    
                    $appointments_array[$bk_slottime]['bk_category']    = $bk_category;
                    $appointments_array[$bk_slottime]['bk_service']     = $bk_service;
                    $appointments_array[$bk_slottime]['bk_booking_date']= $bk_booking_date;
                    $appointments_array[$bk_slottime]['bk_slottime']    = $bk_slottime;
                    $appointments_array[$bk_slottime]['bk_subject']     = $bk_subject;
                    $appointments_array[$bk_slottime]['bk_username']    = $bk_username;
                    $appointments_array[$bk_slottime]['bk_userphone']   = $bk_userphone;
                    $appointments_array[$bk_slottime]['bk_useremail']   = $bk_useremail;
                    $appointments_array[$bk_slottime]['bk_booking_note']= $bk_booking_note;
                    $appointments_array[$bk_slottime]['bk_user_to']     = $bk_user_to;
                    $appointments_array[$bk_slottime]['bk_timestamp']   = $bk_timestamp;
                    $appointments_array[$bk_slottime]['bk_status']      = $bk_status;
                    $appointments_array[$bk_slottime]['bk_user_from']   = $bk_user_from;
                    
                endwhile; wp_reset_postdata();                                 
                
                $formatted_date = date_i18n('Ymd',strtotime($slot_date));
                $day_name      = strtolower(date('D',strtotime($slot_date)));
                
                if (  isset($custom_slot_list[$formatted_date]) 
                    && 
                      !empty($custom_slot_list[$formatted_date])
                ){
                    $todays_defaults = is_array($custom_slot_list[$formatted_date]) ? $custom_slot_list[$formatted_date] : json_decode($custom_slot_list[$formatted_date],true);
                    
                    $todays_defaults_details = is_array($custom_slot_list[$formatted_date.'-details']) ? $custom_slot_list[$formatted_date.'-details'] : json_decode($custom_slot_list[$formatted_date.'-details'],true);
                
                } else if ( isset($custom_slot_list[$formatted_date]) 
                            && 
                            empty($custom_slot_list[$formatted_date])
                ){
                    $todays_defaults = false;
                    $todays_defaults_details = false;
                } else if (  isset($custom_slot_list[$day_name]) 
                             && 
                             !empty($custom_slot_list[$day_name])
                ){
                    $todays_defaults = $custom_slot_list[$day_name];
                    $todays_defaults_details = $custom_slot_list[$day_name.'-details'];
                } else {
                    $todays_defaults = false;
                    $todays_defaults_details = false;
                }
                
                //Data               
                if( !empty( $todays_defaults ) ) {
                    $slots = array();
                    $slot  = array();
                    foreach( $todays_defaults as $key => $value ){
                        $time = explode('-',$key);
                        
                        if( !empty( $appointments_array[$key]['bk_slottime'] )
                            &&
                            $appointments_array[$key]['bk_slottime'] == $key
                        ){                        
                            $slot_status    = false;
                        } else{                       
                            $slot_status    = true;
                        }
                        
                        $start = date_i18n($time_format,strtotime('2016-01-01 '.$time[0]) );
                        $end   = date_i18n($time_format,strtotime('2016-01-01 '.$time[1]) );

                        $final_time 		= $start .' - '. $end;
                        $slot['isSelected'] = $slot_status;
                        $slot['key'] 		= $key;
                        $slot['slot_time']  = $final_time;                        
						
                        $slots[] = $slot;                
                    } 
					
					$json['type']       = 'success';
                	$json['message']    = esc_html__('Slots found', 'docdirect_api');
                    return new WP_REST_Response($slots, 200);  
                }  else {
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('No slots found', 'docdirect_api');
                    return new WP_REST_Response($json, 204);   
                }             
            } else {
                $json['type']       = 'error';
                $json['message']    = esc_html__('Make sure you are logged in', 'docdirect_api');
                return new WP_REST_Response($json, 400);           
            }
        }
    }
}

add_action('rest_api_init',function (){
    $controller = new DocdirectGetBookingTimeSlotsRoutes;
    $controller->register_routes();
});
