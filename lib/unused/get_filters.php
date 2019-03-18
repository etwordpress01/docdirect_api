<?php
if (!class_exists('DocdirectFiltersRoutes')) {

    class DocdirectFiltersRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'listing';

            register_rest_route($namespace, '/' . $base . '/get_filters',
                array(
                  array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array(&$this, 'get_filters'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Featured Listing
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_filters($request){
			$items	= array();
			$insurance_list = array();
            $insurance_list	= docdirect_prepare_taxonomies('directory_type','insurance',0,'array');
			$insurance	= array();
			$insurance_items	= array();
            if(!empty($insurance_list)){
                foreach( $insurance_list as $i_key => $val ){
                    $insurance['id']	= $val->term_id;
                    $insurance['name']	= $val->name;
                    $insurance['slug']	= $val->slug;
                    $insurance_items['insurance'][] = $insurance;
                }
				
				$items[] = $insurance_items;
            }
			
			//languages
			$languages_array = array();
            $languages_array	= docdirect_prepare_languages();//Get Language Array
            if(!empty($languages_array)){
				
				$lang			= array();
				$lang_items		= array();
				
				foreach( $languages_array as $l_key => $value ) {
					$lang['languages'][$l_key] = $value;
					
				}
				
				$items[] = $lang;
			}
			
			//languages
			$locations = get_terms( 'locations', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) ); 
            if( !empty( $locations ) ) {
				$location			= array();
				$location_items		= array();
				foreach ( $locations as $key => $val ) {
					$location['locations'][$key] 		= $val;
				}
				$items[] 	= $location;
			}
				
            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
function () {
	$controller = new DocdirectFiltersRoutes;
	$controller->register_routes();
});
