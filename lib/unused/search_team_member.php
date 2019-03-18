<?php
if (!class_exists('DocdirectAppSearchTeamMemberRoutes')) {

    class DocdirectAppSearchTeamMemberRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'search_team';

            register_rest_route($namespace, '/' . $base . '/team',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_team_members'),
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
        function get_team_members($request)
        {
            if(!empty($request['email']))
            {
                $s = sanitize_text_field($request['email']);

                $json = array();
                if (!is_email($s)) {
                    $json['type'] = 'error';
                    $json['msg'] = esc_html__('Please add valid email ID', 'docdirect_api');
                    echo json_encode($json);
                    die;
                }

                $order = 'DESC';
                $orderby = 'ID';

                $query_args = array(
                    'role' => 'professional',
                    'order' => $order,
                    'orderby' => $orderby,
                );

                $search_args = array(
                    'search' => trim($s),
                    'search_columns' => array(
                        'user_email',
                    )
                );

                $query_args = array_merge($query_args, $search_args);
                $users_query = new WP_User_Query($query_args);
                if (!empty($users_query->results)) {
                    $items = array();
                    $item = array();
                    foreach ($users_query->results as $user) {
                        $username = docdirect_get_username($user->ID);
                        $user_email = $user->user_email;
                        $avatar = apply_filters(
                            'docdirect_get_user_avatar_filter',
                            docdirect_get_user_avatar(array('width' => 150, 'height' => 150), $user->ID),
                            array('width' => 150, 'height' => 150) //size width,height
                        );

                        $item['id'] = $user->ID;
                        $item['username'] = $username;
                        $item['user_email'] = $user_email;
                        $item['photo'] = $avatar;
                        $item['user_link'] = get_author_posts_url($user->ID);

                        $items[] = $item;
                    }

                    return new WP_REST_Response($items, 200);
                }
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppSearchTeamMemberRoutes;
        $controller->register_routes();
    });
