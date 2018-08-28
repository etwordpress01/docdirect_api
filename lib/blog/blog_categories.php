<?php
if (!class_exists('DocdirectAppBlogCategoryRoutes')) {

    class DocdirectAppBlogCategoryRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'blog';

            register_rest_route($namespace, '/' . $base . '/categories',
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
         * Get Blog categories
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_all_categories($request) {
            $categories = get_categories();
            $items	= array();
            $item = array();
            if (!empty($categories)) {
                foreach ($categories as $key => $category) {
                    $item['id'] 	= $category->term_id;
                    $item['title']  = $category->name;
                    $item['slug']  = $category->slug;
                    $item['url'] = get_category_link( $category->term_id );
                    $items[] = $item;
                }
            }

            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppBlogCategoryRoutes;
        $controller->register_routes();
    });
