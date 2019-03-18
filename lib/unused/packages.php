<?php
if (!class_exists('DocdirectAppAllPackagesRoutes')) {

    class DocdirectAppAllPackagesRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'packages';
			
			register_rest_route($namespace, '/' . $base . '/get_package',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array(&$this, 'get_all_package'),
                        'args' => array(),
                    ),
                )
            );
        }
		

        /**
         * Get Parent categories
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_all_package($request) {
            $items = array();
            $item = array();
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'order' => 'DESC',
                'orderby' => 'ID',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'suppress_filters'  => false
            );

            $loop = new WP_Query( $args );
            $loop;
            $article_limit = 0;
            if (function_exists('fw_get_db_settings_option')) {
                $article_limit = fw_get_db_settings_option('article_limit');
            }
            $article_limit = !empty( $article_limit ) ? $article_limit  : 0;
            if ( $loop->have_posts() ) {
                while ($loop->have_posts()) : $loop->the_post();
                    global $product;
                    $appointment = docdirect_get_package_check($product->get_id(),'dd_appointments');
                    $banner = docdirect_get_package_check($product->get_id(),'dd_banner');
                    $insurance = docdirect_get_package_check($product->get_id(),'dd_insurance');
                    $favourite = docdirect_get_package_check($product->get_id(),'dd_favorites');
                    $team_management = docdirect_get_package_check($product->get_id(),'dd_teams');
                    $schedules = docdirect_get_package_check($product->get_id(),'dd_hours');
                    $questing_answer =  docdirect_get_package_check($product->get_id(),'dd_qa');
                    $dd_articles = get_post_meta($product->get_id(), 'dd_articles', true);
                    $package_duration = get_post_meta($product->get_id(), 'dd_duration', true);
                    $item['title'] = $product->get_title();
                    $item['price'] = "$".$product->get_price();
                    $item['duration'] = $package_duration;
                    $item['featured listing'] = get_post_meta($product->get_id(), 'dd_featured', true);
                    $item['articles'] = intval($dd_articles) + intval($article_limit);
                    //get appointment
                    if($appointment == "fa fa-check"){
                        $item['Appointments'] = "checked";
                    }else{
                        $item['Appointments'] = "unchecked";
                    }
                    //get banner
                    if($banner == "fa fa-check"){
                        $item['Profile banner'] = "checked";
                    }else{
                        $item['Profile banner'] = "unchecked";
                    }
                    //get insurance
                    if($insurance == "fa fa-check"){
                        $item['Insurance settings'] = "checked";
                    }else{
                        $item['Insurance settings'] = "unchecked";
                    }
                    //get favorite
                    if($favourite == "fa fa-check"){
                        $item['Favorite listings'] = "checked";
                    }else{
                        $item['Favorite listings'] = "unchecked";
                    }
                    //get team management
                    if($team_management == "fa fa-check"){
                        $item['Teams management'] = "checked";
                    }else{
                        $item['Teams management'] = "unchecked";
                    }
                    //get schedules
                    if($schedules == "fa fa-check"){
                        $item['Opening Hours/Schedules'] = "checked";
                    }else{
                        $item['Opening Hours/Schedules'] = "unchecked";
                    }
                    //get questions
                    if($questing_answer == "fa fa-check"){
                        $item['Question and Answers'] = "checked";
                    }else{
                        $item['Question and Answers'] = "unchecked";
                    }
                    $items[] = $item;
                    endwhile;

                }


            return new WP_REST_Response($items, 200);

        }

    }
}

add_action('rest_api_init',
        function () {
    $controller = new DocdirectAppAllPackagesRoutes;
    $controller->register_routes();
});
