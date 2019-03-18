<?php
if (!class_exists('DocdirectAppTeamRoutes')) {

    class DocdirectAppTeamRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'team';

            register_rest_route($namespace, '/' . $base . '/team_data',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_team'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Team Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_team($request)
        {
            if (!empty($request['user_id'])) {
                $user_id    = $request['user_id'];
                $user_meta  = get_user_meta($user_id);
                $items = array();
                $item = array();
                $limit = 500;
                if (empty($paged)) $paged = 1;
                $offset = ($paged - 1) * $limit;
                //Get Teams id
                $teams = $user_meta['teams_data'];
                $teams    = !empty($teams) && is_array( $teams ) ? $teams : array();
                //Get Team Array
                foreach($teams as $key => $data){
                    $team_data = unserialize($data);
                }
                $query_args	= array(
                    'role'  => 'professional',
                    'order' => 'DESC',
                    'orderby' => 'ID',
                    'include' => $team_data
                );

                $query_args['number']	= $limit;
                $query_args['offset']	= $offset;

                $user_query  = new WP_User_Query($query_args);
                if ( ! empty( $user_query->results ) ){
                    foreach ( $user_query->results as $user ){
                        $user_link = get_author_posts_url($user->ID);
                        $username = docdirect_get_username($user->ID);
                        $user_email = $user->user_email;
                        $avatar = apply_filters(
                            'docdirect_get_user_avatar_filter',
                            docdirect_get_user_avatar(array('width'=>150,'height'=>150), $user->ID),
                            array('width'=>150,'height'=>150) //size width,height
                        );

                        $item['url'] = $user_link;
                        $item['name'] = $username;
                        $item['email'] = $user_email;
                        $item['image'] = $avatar;
                        $items[] = $item;
                    }
                }
				
				return new WP_REST_Response($items, 200);
            }
            
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppTeamRoutes;
        $controller->register_routes();
    });
