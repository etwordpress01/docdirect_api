<?php
if (!class_exists('DocdirectAppCategoryRoutes')) {

    class DocdirectAppCategoryRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'post_type';
			
			register_rest_route($namespace, '/' . $base . '/get_categories',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_all_categories'),
                        'args' => array(),
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
        public function get_all_categories($request) {
            $args = array('posts_per_page' => '-1',
                'post_type' => 'directory_type',
                'post_status' => 'publish',
                'suppress_filters' => false
            );
			
			$options = '';
            $cust_query = get_posts($args);
			$items	= array();
            if (!empty($cust_query)) {
                $counter = 0;
                foreach ($cust_query as $key => $dir) {
                    $meta = get_post_meta($dir->ID);
					$item = array();

                    $item['id'] 	= $dir->ID;
                    $item['title']  = get_the_title($dir->ID);

                    $item 		 	+= unserialize($meta['fw_options'][0]);
					$specialities 	= $item['specialities'];
					$category_image = fw_get_db_post_option($dir->ID, 'category_image', true);

					if( !empty( $category_image['attachment_id'] ) ){
						$banner	= docdirect_get_image_source($category_image['attachment_id'],100,100);
					} else{
						$banner	= get_template_directory_uri().'/images/user100x100.jpg';;
					}
					
					$item['banner'] = $banner;
					
					if (!empty($specialities)) {
						$subarray = array();
                        foreach ($specialities as $key => $term) {
							$speciality = get_term_by('id',$key,'specialities','OBJECT');
							$subarray[] = $speciality;
						}
						
                        $item['specialities'] = $subarray;
                    } 
					
					$items[] = $item;
                }
			} 

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppCategoryRoutes;
    $controller->register_routes();
});
