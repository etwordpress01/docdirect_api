<?php
/**
 * APP API for categories
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
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
			$show	= !empty( $request['show'] ) ? $request['show'] : -1;
            $args = array('posts_per_page' => $show,
                'post_type' => 'directory_type',
                'post_status' => 'publish',
                'suppress_filters' => false
            );
			
			$options 	= '';
            $cust_query = get_posts($args);
			$items		= array();
			$json		= array();
			
            if (!empty($cust_query)) {
                $counter = 0;
                foreach ($cust_query as $key => $dir) {
                    $meta = get_post_meta($dir->ID);
                   
					$item = array();
                	$category_image = fw_get_db_post_option($dir->ID, 'category_image', true);
					$dir_map_marker = fw_get_db_post_option($dir->ID, 'dir_map_marker', true);
                    $item['id'] 	= $dir->ID;
                    $item['title']  = get_the_title($dir->ID);
                    
                    if( empty( $category_image['attachment_id'] ) ){
                        $item['category_image']['url']				= '';
						$item['category_image']['attachment_id']	= '';
                    }
					
					if( empty( $dir_map_marker['attachment_id'] ) ){
                        $item['dir_map_marker']['url']				= '';
						$item['dir_map_marker']['attachment_id']	= '';
                    }
                    
                    $item 		 	+= unserialize($meta['fw_options'][0]);
                    
					$specialities 	= $item['specialities'];
				
                    
					if( !empty( $category_image['attachment_id'] ) ){
						$banner	= docdirect_get_image_source($category_image['attachment_id'],100,100);
					} else{
						$banner	= get_template_directory_uri().'/images/user100x100.jpg';
						
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

				return new WP_REST_Response($items, 200);
			}  else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('No categories found.','docdirect_api');
				return new WP_REST_Response($json, 203);
			} 
        }
    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppCategoryRoutes;
    $controller->register_routes();
});
