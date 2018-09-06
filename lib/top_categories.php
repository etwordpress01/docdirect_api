<?php
if (!class_exists('DocdirectAppTopCategoryRoutes')) {

    class DocdirectAppTopCategoryRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'top_categories';
			
			register_rest_route($namespace, '/' . $base . '/get_top_categories',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_parent_categories'),
                        'args' => array(),
                    ),
                )
            );
        }
		

        /**
         * Get Parent categories
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_parent_categories($request) {
            $args = array('posts_per_page' => '-1',
                'post_type' => 'directory_type',
                'post_status' => 'publish',
                'suppress_filters' => false
            );
			
			$cust_query = get_posts($args);
			$items	= array();
            if (!empty($cust_query)) {
                foreach ($cust_query as $key => $dir) {
					$item = array();
					$item['id'] 	= $dir->ID;
                    $item['title']  = get_the_title($dir->ID);
                    $item['url'] = esc_url( get_permalink($dir->ID));
					$item['icon'] = fw_get_db_post_option($dir->ID, 'dir_icon');

                    $items[] = $item;
                }
			} 

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppTopCategoryRoutes;
    $controller->register_routes();
});
