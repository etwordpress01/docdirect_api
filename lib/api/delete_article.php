<?php
if (!class_exists('DocdirectDeleteArticleRoutes')) {

    class DocdirectDeleteArticleRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'articles';

            register_rest_route($namespace, '/' . $base . '/delete_article',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'delete_article'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Create New Articles
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function delete_article($request){
            if ( !empty($request['user_id']) ) {
                $current_user_id = $request['user_id'];
                $post_id = intval($request['article_id']);
                $post_author = get_post_field('post_author', $post_id);

                if ( !empty($post_id) && intval($current_user_id) === intval($post_author) ) {
                    wp_delete_post($post_id);
                    $json['type'] = 'success';
                    $json['message'] = esc_html__('Article deleted successfully.', 'docdirect');
                    return new WP_REST_Response($json, 200);
                } else {
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Post ID needed.', 'docdirect');
                    return new WP_REST_Response($json, 203);
                }
            } else {
                $json['type'] = 'error';
                $json['message'] = esc_html__('user_id is needed', 'docdirect');
                return new WP_REST_Response($json, 203);
            }
        }
    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectDeleteArticleRoutes;
        $controller->register_routes();
    });