<?php
if (!class_exists('DocdirectReviewsRoutes')) {

    class DocdirectReviewsRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'reviews';

            register_rest_route($namespace, '/' . $base . '/user_reviews',
                array(
                  array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_reviews'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Reviews
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_reviews($request){
            if(!empty($request['user_id'])){
                $user_id    = $request['user_id'];
                $items 		= array();
                $item 		= array();
                $item['review_count']  =  intval( apply_filters('docdirect_count_reviews',$user_id) );

                if( apply_filters('docdirect_count_reviews',$user_id) > 0 ){
                    if (empty($paged)) $paged = 1;
                    $show_posts    = get_option('posts_per_page') ? get_option('posts_per_page') : '-1';

                    $meta_query_args = array('relation' => 'AND',);
                    $meta_query_args[] = array(
                        'key' 	   => 'user_to',
                        'value' 	=> $user_id,
                        'compare'   => '=',
                        'type'	  	=> 'NUMERIC'
                    );


                    //Main Query
                    $args 		= array('posts_per_page' => $show_posts,
                        'post_type' => 'docdirectreviews',
                        'paged' => $paged,
                        'order' => 'DESC',
                        'orderby' => 'ID',
                        'post_status' => 'publish',
                        'ignore_sticky_posts' => 1
                    );

                    $args['meta_query'] = $meta_query_args;

                    $query 		= new WP_Query($args);
					$count_post = $query->found_posts;
						
                    if( $query->have_posts() ){
                        while($query->have_posts()) : $query->the_post();
						global $post;
						$user_rating 	= fw_get_db_post_option($post->ID, 'user_rating', true);
						$user_from 		= fw_get_db_post_option($post->ID, 'user_from', true);
						$review_date  	= fw_get_db_post_option($post->ID, 'review_date', true);
						$user_data 	  	= get_user_by( 'id', intval( $user_from ) );
						$content_post 	= get_post($post->ID);
						$content 	  	= $content_post->post_content;
						$content 		= apply_filters('the_content', $content);
						$content 		= str_replace(']]>', ']]&gt;', $content);

						$avatar = apply_filters(
							'docdirect_get_user_avatar_filter',
							docdirect_get_user_avatar(array('width'=>150,'height'=>150), $user_from),
							array('width'=>150,'height'=>150) //size width,height
						);

						$avatar = apply_filters(
							'docdirect_get_user_avatar_filter',
							docdirect_get_user_avatar(array('width'=>150,'height'=>150), $user_from),
							array('width'=>150,'height'=>150) //size width,height
						);

						$user_name	= docdirect_get_username( $user_from );
						$percentage	= $user_rating*20;

						$item['user_url'] 		= get_author_posts_url($user_from);
						$item['user_name'] 		= esc_attr( $user_name );
						$item['review_date'] 	= human_time_diff( strtotime( $review_date )).' '.esc_html__('ago','docdirect_api');
						$item['image'] 			= esc_url( $avatar );
						$item['content'] 		= wp_strip_all_tags( $content );
						$item['rating'] 		= $user_rating;
						$item['percentage'] 	= $percentage;

						$items[] = $item;

						endwhile;
                    }

                }

				return new WP_REST_Response($items, 200);
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
        $controller = new DocdirectReviewsRoutes;
        $controller->register_routes();
    });
