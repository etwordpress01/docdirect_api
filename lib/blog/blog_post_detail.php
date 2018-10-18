<?php
if (!class_exists('DocdirectAppBlogPostDetailRoutes')) {

    class DocdirectAppBlogPostDetailRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'blog';
			
			register_rest_route($namespace, '/' . $base . '/post_detail',
                array(
					 array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'get_post'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * Get Post Content by ID
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_post($request) {
            if(!empty($request['post_id'])){
                $post_id = $request['post_id'];
                $post_data = get_post( $post_id );
                $item = array();
                $items = array();
                $thumbnail	= get_the_post_thumbnail_url($post_id,'full');
                $post_content = $post_data->post_content;
                $post_tags = get_the_tags($post_id);
                $post_comments = get_comments( array( 'post_id' => $post_id ) );

                $item['post_id'] = $post_id;
                $item['post_title'] = $post_data->post_title;
                $item['post_url'] = $post_data->guid;
                $item['post_image'] = $thumbnail;
                $item['post_content'] = $post_content;
                $item['publish_date'] = date_i18n('Y-m-d', strtotime(get_the_date('Y-m-d',$post_id)));
                foreach($post_tags as $key => $tag){
                    $item[$key.'-tag'] = $tag->name;
                }
                $item['comment_count'] = get_comments_number($post_id);
                $item['comments'] =  $post_comments;
                $items[] = $item;
            }





            return new WP_REST_Response($items, 200);
        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppBlogPostDetailRoutes;
    $controller->register_routes();
});
