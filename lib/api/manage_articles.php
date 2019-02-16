<?php
if (!class_exists('DocdirectManageArticlesRoutes')) {

    class DocdirectManageArticlesRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'articles';

            register_rest_route($namespace, '/' . $base . '/manage_articles',
                array(
                  array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_article_list'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * Get Articles Data
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_article_list($request)
        {
            if(!empty($request['user_id'])){
                $item = array();
                $items = array();

                $user_identity = $request['user_id'];
                $url_identity = $user_identity;
                $dir_profile_page = '';
                if (function_exists('fw_get_db_settings_option')) {
                    $dir_profile_page = fw_get_db_settings_option('dir_profile_page', $default_value = null);
                }

                $get_username = docdirect_get_username($url_identity);
                $profile_page = isset($dir_profile_page[0]) ? $dir_profile_page[0] : '';
                $show_posts = get_option('posts_per_page') ? get_option('posts_per_page') : '2';

                $pg_page = get_query_var('page') ? get_query_var('page') : 1; //rewrite the global var
                $pg_paged = get_query_var('paged') ? get_query_var('paged') : 1; //rewrite the global var
                //paged works on single pages, page - works on homepage
                $paged = max($pg_page, $pg_paged);

                $order = 'DESC';
                if (!empty($request['order'])) {
                    $order = esc_attr($request['order']);
                }

                $sorting = 'ID';
                if (!empty($request['sort'])) {
                    $sorting = esc_attr($request['sort']);
                }

                $args = array('posts_per_page' => '-1',
                    'post_type' => 'sp_articles',
                    'orderby' => 'ID',
                    'post_status' => 'publish',
                    'author' => $url_identity,
                    'suppress_filters' => false
                );
                $query = new WP_Query($args);
                $count_post = $query->post_count;

                $args = array('posts_per_page' => $show_posts,
                    'post_type' => 'sp_articles',
                    'orderby' => $sorting,
                    'order' => $order,
                    'post_status' => array( 'publish','pending' ),
                    'author' => $url_identity,
                    'paged' => $paged,
                    'suppress_filters' => false
                );

                $query = new WP_Query($args);
                if ($query->have_posts()){
                    $today = time();
                    while ($query->have_posts()) : $query->the_post();
                        global $post;
                        $status	= get_post_status($post->ID);
                        $post_thumbnail_id  = get_post_thumbnail_id($post->ID);
                        $thumbnail 			= docdirect_prepare_thumbnail($post->ID);
                        if( isset( $status ) && $status === 'publish' ){
                            $item['status'] = 'publish';
                        }
                        elseif( isset( $status ) && $status === 'pending' ){
                            $item['status'] = 'pending';
                        }
                        $item['id'] = $post->ID;
                        $item['url'] = esc_url(get_the_permalink());
                        $item['title'] = $post->post_title;
                        $item['author'] = esc_attr($get_username);
                        $item['article_image'] = $thumbnail;
                        $items[] = $item;
                    endwhile;
                }
                return new WP_REST_Response($items, 200);
            }
        }


    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectManageArticlesRoutes;
        $controller->register_routes();
    });
