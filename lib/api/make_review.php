<?php
if (!class_exists('DocdirectCreateReviewRoutes')) {

    class DocdirectCreateReviewRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'review';

            register_rest_route($namespace, '/' . $base . '/make_review',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'submit_review'),
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
        public function submit_review($request){
            if (!empty($request['user_to']) && !empty($request['current_user'])){
                $current_user_id = $request['current_user'];
                $user_to	  = sanitize_text_field( $request['user_to'] );
                $is_verified  = get_user_meta($request['current_user'], 'verify_user', true);

                $dir_review_status	= 'pending';
                if (function_exists('fw_get_db_settings_option')) {
                    $dir_review_status = fw_get_db_settings_option('dir_review_status', $default_value = null);
                }

                if( isset( $is_verified ) && $is_verified != 'on' ) {
                    $json['type']			= 'error';
                    $json['message']		= esc_html__('You are not a verified user, You can\'t make a review. Please contact to administrator.','docdirect');
                    return new WP_REST_Response($json, 203);
                }

                $user_reviews = array(
                    'posts_per_page'   => "-1",
                    'post_type'		   => 'docdirectreviews',
                    'post_status'	   => 'any',
                    'author' 		   => $current_user_id,
                    'meta_key'		   => 'user_to',
                    'meta_value'	   => $user_to,
                    'meta_compare'	   => "=",
                    'orderby'		   => 'meta_value',
                    'order'			   => 'ASC',
                );

                $reviews_query = new WP_Query($user_reviews);
                $reviews_count = $reviews_query->post_count;
                if( isset( $reviews_count ) && $reviews_count > 0 ){
                    $json['type']		= 'error';
                    $json['message']	= esc_html__('You have already submit a review.', 'docdirect');
                    return new WP_REST_Response($json, 203);
                }

                $db_directory_type	 = get_user_meta( $user_to, 'directory_type', true);

                if( !empty( $request['user_subject'] )
                    && !empty( $request['user_description'] )
                    && !empty( $request['user_rating'] )
                    && !empty( $request['user_to'] )
                ) {

                    $user_subject	   = sanitize_text_field( $request['user_subject'] );
                    $user_description  = sanitize_text_field( $request['user_description'] );
                    $user_rating	   = sanitize_text_field( $request['user_rating'] );
                    $user_from	       = $current_user_id;
                    $user_to	   	   = sanitize_text_field( $request['user_to'] );
                    $directory_type	   = $db_directory_type;

                    $review_post = array(
                        'post_title'  => $user_subject,
                        'post_status' => $dir_review_status,
                        'post_content'=> $user_description,
                        'post_author' => $user_from,
                        'post_type'   => 'docdirectreviews',
                        'post_date'   => current_time('Y-m-d H:i:s')
                    );

                    $post_id = wp_insert_post( $review_post );

                    $review_meta = array(
                        'user_rating' 	 	 => $user_rating,
                        'user_from' 	     => $user_from,
                        'user_to'   		 => $user_to,
                        'directory_type'  	 => $directory_type,
                        'review_date'   	 => current_time('Y-m-d H:i:s'),
                    );

                    //Update post meta
                    foreach( $review_meta as $key => $value ){
                        update_post_meta($post_id,$key,$value);
                    }

                    $new_values = $review_meta;

                    if (isset($post_id) && !empty($post_id)) {
                        fw_set_db_post_option($post_id, null, $new_values);
                    }

                    $json['type']	   = 'success';
                    if( isset( $dir_review_status ) && $dir_review_status == 'publish' ) {
                        $json['message']	= esc_html__('Your review published successfully.','docdirect');
                        $json['html']	   = 'refresh';
                    } else{
                        $json['message']	= esc_html__('Your review is submitted successfully, it will be published after approval.','docdirect');
                        $json['html']	   = '';
                    }

                    if( class_exists( 'DocDirectProcessEmail' ) ) {
                        $user_from_data		= get_userdata($user_from);
                        $user_to_data	  	= get_userdata($user_to);
                        $email_helper	  	= new DocDirectProcessEmail();

                        $emailData	= array();

                        //User to data
                        $emailData['email_to']	    = $user_to_data->user_email;
                        $emailData['link_to']		= get_author_posts_url($user_to_data->ID);
						$emailData['username_to']   = docdirect_get_username($user_to_data->ID);
                        
                        //User from data
						$emailData['username_from']   = docdirect_get_username($user_to_data->ID);
                        $emailData['link_from']	= get_author_posts_url($user_from_data->ID);

                        //General
                        $emailData['rating']	        = $user_rating;
                        $emailData['reason']	        = $user_subject;

                        $email_helper->process_rating_email($emailData);
                    }
					
					$json['type']		= 'success';
					$json['message']	 = esc_html__('Review added.','docdirect');
					return new WP_REST_Response($json, 200);

                } else{
                    $json['type']		= 'error';
                    $json['message']	 = esc_html__('Please fill all the fields.','docdirect');
                    return new WP_REST_Response($json, 203);
                }
            } else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect');
				return new WP_REST_Response($json, 203);
			}
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectCreateReviewRoutes;
        $controller->register_routes();
    });
