<?php
/**
 * APP API to get all specialities
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectAppSpecialitiesRoutes')) {

    class DocdirectAppSpecialitiesRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'specialities';
			
			register_rest_route($namespace, '/' . $base . '/specialities_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_specialities'),
                        'args' => array(),
                    ),
                )
            );
        }		

        /**
         * Get specialities
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_specialities($request) {
            $items	= array();
            $item	= array();
            $specialities_list	= docdirect_prepare_taxonomies('directory_type','specialities',0,'array');
			if(!empty( $specialities_list )){
				foreach( $specialities_list as $key => $speciality ){
					$speciality_meta = array();
					$term_id = $speciality->term_id;
					$speciality_icon = array();
					if (!empty($speciality_meta['icon']['icon-class'])) {
						$speciality_icon = $speciality_meta['icon']['icon-class'];
					}

					$item['id'] = $term_id;
					$item['slug'] = $speciality->slug;
					$item['name'] = $speciality->name;
					$item['category'] = $speciality->taxonomy;
					if(!empty($speciality_icon)){
						$item['icon'] = esc_attr($speciality_icon);
					}

					if (!empty($speciality_meta['icon']['url'])){
						$item['meta'] = esc_url($speciality_meta['icon']['url']);
					}

					$items[] = $item;
				}

				return new WP_REST_Response($items, 200);
			} else{
				$json['type']	= 'error';
				$json['message']	= esc_html__('Some error occur, please try again later.','docdirect_api');
				return new WP_REST_Response($json, 203);
			} 
        }
    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppSpecialitiesRoutes;
    $controller->register_routes();
});
