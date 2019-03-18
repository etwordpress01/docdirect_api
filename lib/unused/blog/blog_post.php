<?php
if (!class_exists('DocdirectAppBlogPostRoutes')) {

    class DocdirectAppBlogPostRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'blog';
			
			register_rest_route($namespace, '/' . $base . '/posts',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_post'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * Get categories
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_post($request) {
            global $paged, $post;
            $item = array();
            $items = array();
            $order = !empty($atts['order']) ? $atts['order'] : 'DESC';
            $orderby = !empty($atts['orderby']) ? $atts['orderby'] : 'ID';


            //Main Query
            $args = array('posts_per_page' => -1,
                'post_type' => 'post',
                'paged' => $paged,
                'order' => $order,
                'orderby' => $orderby,
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1
            );
            $query = new WP_Query($args);
            while ($query->have_posts()) : $query->the_post();
                $width = '370';
                $height = '200';
                $thumbnail	= docdirect_prepare_thumbnail($post->ID ,$width,$height);
                $user_ID = get_the_author_meta('ID');

                if( !empty( $user_ID ) ){
                    $userprofile_media = apply_filters(
                        'docdirect_get_user_avatar_filter',
                        docdirect_get_user_avatar(array('width'=>150,'height'=>150), $user_ID),
                        array('width'=>150,'height'=>150) //size width,height
                    );
                }
                $item['post_id'] = $post->ID;
                $item['post_title'] = get_the_title();
                $item['post_url'] = esc_url(get_the_permalink());
                $item['post_image'] = $thumbnail;
                if(!empty($request['content_length'])){
                    $item['post_content'] = wp_trim_words(get_the_content(),$request['content_length']);
                }else{
                    $item['post_content'] = wp_trim_words(get_the_content(),15);
                }

                $item['author_id'] = $user_ID;
                $item['author_image'] = $userprofile_media;
                $item['author_name'] = the_author();
                $item['publish_date'] = date_i18n('Y-m-d', strtotime(get_the_date('Y-m-d',$post->ID)));
                $items[] = $item;
            endwhile;

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppBlogPostRoutes;
    $controller->register_routes();
});
