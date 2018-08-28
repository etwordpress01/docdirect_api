<?php
if (!class_exists('DocdirectAppRecentPostRoutes')) {

    class DocdirectAppRecentPostRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'blog';
			
			register_rest_route($namespace, '/' . $base . '/recent_posts',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_recent_post'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * Get Recent Post
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_recent_post($request) {
            $item = array();
            $items = array();
            if(!empty($request['number_of_posts'])){
                $number_of_posts = $request['number_of_posts'];
            }else{
                $number_of_posts = 5;
            }
            $query_args = array('post_type' => 'post', 'posts_per_page' => $number_of_posts, 'orderby' => 'post_date','order' => 'DESC');
            $loop = new WP_Query($query_args);
            while ($loop->have_posts()) : $loop->the_post();
                global $post;
                $post_id = $post->ID;
                $item['id']  =  $post_id;
                $item['url'] = esc_url( get_the_permalink());
                $item['title']  =  get_the_title();
                $item['date']  =   date_i18n('Y-m-d', strtotime(get_the_date('Y-m-d',$post_id)));
                $items[] = $item;
            endwhile;

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppRecentPostRoutes;
    $controller->register_routes();
});
