<?php
if (!class_exists('DocdirectAppStaticsRoutes')) {

    class DocdirectAppStaticsRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'sp_question';
			
			register_rest_route($namespace, '/' . $base . '/statics',
                array(
					 array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array($this, 'get_statics'),
                        'args' => array(
                        ),
                    ),
                )
            );
        }
		
		 /**
         * get answer
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function get_statics($request) {
            $item = array();
            $items = array();

            $tqa_args = array('posts_per_page' => '-1',
                'post_type' => 'sp_questions',
                'orderby'   => 'ID',
                'post_status' => 'publish'
            );


            $tqa_query = new WP_Query($tqa_args);
            $question = $tqa_query->post_count;

            $tqa_args = array('posts_per_page' => '-1',
                'post_type' => 'sp_answers',
                'orderby'   => 'ID',
                'post_status' => 'publish'
            );


            $tqa_query = new WP_Query($tqa_args);
            $ans = $tqa_query->post_count;

            $item['Total Questions posted'] = $question;
            $item['Total Queries answered'] = $ans;

            $items[] = $item;
            return new WP_REST_Response($items, 200);

        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppStaticsRoutes;
    $controller->register_routes();
});
