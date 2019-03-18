<?php
/**
 * APP API to save articles
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUserArticlesRoutes')) {

    class DocdirectUserArticlesRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'articles';

            register_rest_route($namespace, '/' . $base . '/user_articles',
                array(
                  array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_articles'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Articles Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_articles($request){
			$json = array();
            if ( !empty( $request['user_id'] ) ) {
                $user_id 	= $request['user_id'];
                $item  		= array();
                $items 		= array();
                $order 		= !empty($request['order']) ? $request['order'] : 'DESC';
                $orderby 	= !empty($request['orderby']) ? $request['orderby'] : 'ID';

                //Main Query
                $query_args = array(
                    'posts_per_page'        => -1,
                    'post_type'             => 'sp_articles',
                    'order'                 => $order,
                    'orderby'               => $orderby,
                    'post_status'           => 'publish',
                    'author'                => $user_id,
                    'ignore_sticky_posts'   => 1
                );
				
                $query = new WP_Query($query_args);
				$count_post = $query->found_posts;

                if ($query->have_posts()){
                    while ($query->have_posts()){
                        $query->the_post();
                        global $post;
                        $height = 200;
                        $width  = 370;

                        $author_id = $post->post_author;;

                        $post_thumbnail_id  = get_post_thumbnail_id($post->ID);
                        $thumbnail 			= docdirect_prepare_thumbnail($post->ID, $width, $height);

                        $thumb_meta = array();
                        if (!empty($post_thumbnail_id)) {
                            $thumb_meta = docdirect_get_image_metadata($post_thumbnail_id);
                        }
                        
                        $image_title = !empty($thumb_meta['title']) ? $thumb_meta['title'] : 'no-name';
                        $image_alt = !empty($thumb_meta['alt']) ? $thumb_meta['alt'] : $image_title;

                        $author_name = docdirect_get_username($author_id);
                        $author_avatar = apply_filters(
                            'docdirect_get_user_avatar_filter',
                            docdirect_get_user_avatar(array('width'=>150,'height'=>150), $author_id),
                            array('width'=>150,'height'=>150) //size width,height
                        );
                        $post_view_count    	= get_post_meta($post->ID, 'article_views', true);
                        $item['author_id']      = $author_id;
						$item['id']      		= $post->ID;
                        $item['author_name']    = $author_name;
                        $item['author_image']   = $author_avatar;
                        $item['author_url']     = esc_url(get_author_posts_url($author_id));
                        $item['article_title']  = esc_attr(get_the_title());
                        $item['article_url']    = esc_url(get_the_permalink());
                        $item['article_image']  = $thumbnail;
                        $item['article_date']   = date_i18n(get_option('date_format'), strtotime(get_the_date('Y-m-d', $post->ID)));
                        $item['view_count']     = $post_view_count;
                        $item['content']        = esc_attr(get_the_content());
                        $items[] = $item;
                    }

					return new WP_REST_Response($items, 200);
					
                } else{
					$json['type']	= 'error';
                    $json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
					return new WP_REST_Response($json, 203);
				}

            } else {
                
                $json['type'] = 'error';
                $json['message'] = esc_html__('User ID is required', 'docdirect_api');
                return new WP_REST_Response($json, 203);
            }
        }
    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectUserArticlesRoutes;
        $controller->register_routes();
    });
