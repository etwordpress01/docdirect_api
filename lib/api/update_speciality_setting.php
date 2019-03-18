<?php
/**
 * APP API to save specialities
 *
 * This file will include all global settings which will be used in all over the plugin,
 * It have gatter and setter methods
 *
 * @link              https://themeforest.net/user/amentotech/portfolio
 * @since             1.0.0
 * @package           Docdirect App
 *
 */
if (!class_exists('DocdirectUpdateUserSpecialitySettingRoutes')) {

    class DocdirectUpdateUserSpecialitySettingRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'profile_setting';

            register_rest_route($namespace, '/' . $base . '/speciality_setting',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'update_speciality_setting'),
                        'args' => array(),
                    ),
                )
            );
        }

        /**
         * Make speciality settings
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function update_speciality_setting($request)
        {
            $json = array();
            if(!empty($request['user_id'])) {
                $user_identity = $request['user_id'];                
                $specialities = get_user_meta($user_identity, 'user_profile_specialities', true);
                $specialities = !empty( $specialities ) ? $specialities : array();
                
				//Specialities
				$db_directory_type	 = get_user_meta( $user_identity, 'directory_type', true);
				if( !empty( $db_directory_type ) ) {
					$specialities_list	 = docdirect_prepare_taxonomies('directory_type','specialities',0,'array');
				}

				$submitted_specialities	= !empty( $request['specialities'] ) ? $request['specialities'] : array();

				//limit specialities
				if (function_exists('fw_get_db_settings_option')) {
					$speciality_limit 		= fw_get_db_settings_option('speciality_limit');
				}
				
				$speciality_limit		= !empty( $speciality_limit ) ? $speciality_limit : '50';
				$submitted_specialities	= array_slice($submitted_specialities, 0, $speciality_limit);

				if( !empty( $specialities_list ) ){
					$counter	= 0;
					foreach( $specialities_list as $key => $speciality ){
						if( isset( $submitted_specialities ) 
							&& is_array( $submitted_specialities ) 
							&& in_array( $speciality->slug, $submitted_specialities ) 
						 ){
							$specialities[$speciality->slug]	= $speciality->name;
						}

						$counter++;
					}
				}

				update_user_meta( $user_identity, 'user_profile_specialities', $specialities ); 

                $json['type'] 	 = 'success';
                $json['message'] = esc_html__('Specialities has been updated.', 'docdirect_api');
                return new WP_REST_Response($json, 200);
            } else{
				$json['type']       = 'error';
				$json['message']    = esc_html__('User ID is required', 'docdirect_api');
				return new WP_REST_Response($json, 203);           
			}
        }
    }
}

add_action('rest_api_init',
function ()
{
    $controller = new DocdirectUpdateUserSpecialitySettingRoutes;
    $controller->register_routes();
});
