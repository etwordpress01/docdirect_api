<?php
if (!class_exists('DocdirectCreateArticlesRoutes')) {

    class DocdirectCreateArticlesRoutes extends WP_REST_Controller
    {

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes()
        {
            $version = '1';
            $namespace = 'api/v' . $version;
            $base = 'articles';

            register_rest_route($namespace, '/' . $base . '/create_article',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'save_article'),
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
        function save_article($request)
        {
            if (!empty($request['user_id']))
            {
                $user_id = $request['user_id'];
                $type = !empty($request['type']) ? esc_attr($request['type']) : '';
                $current = !empty($request['current']) ? esc_attr($request['current']) : '';
                $provider_category = get_user_meta($user_id, 'directory_type', true);
                remove_all_filters("content_save_pre");

                if (function_exists('docdirect_is_demo_site')) {
                    docdirect_is_demo_site();
                }; //if demo site then prevent

                do_action('docdirect_is_action_allow'); //is action allow

                if (empty($request['article_title'])) {
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Title field should not be empty.', 'docdirect');
                    echo json_encode($json);
                    die;
                }

                $title = !empty($request['article_title']) ? esc_attr($request['article_title']) : esc_html__('unnamed', 'docdirect');
                $article_detail = force_balance_tags($request['article_detail']);

                $attachment_id = !empty($request['attachment_id']) ? intval($request['attachment_id']) : '';
                $article_tags = !empty($request['article_tags']) ? $request['article_tags'] : array();
                $article_categories = !empty($request['categories']) ? $request['categories'] : array();

                $dir_profile_page = '';
                if (function_exists('fw_get_db_settings_option')) {
                    $dir_profile_page = fw_get_db_settings_option('dir_profile_page', $default_value = null);
                }

                $profile_page = isset($dir_profile_page[0]) ? $dir_profile_page[0] : '';

                if (function_exists('fw_get_db_settings_option')) {
                    $approve_articles = fw_get_db_settings_option('approve_articles', $default_value = null);
                }

                //add/edit job
                if (isset($type) && $type === 'add') {

                    if (isset($approve_articles) && $approve_articles === 'need_approval') {
                        $status = 'pending';
                        $json['message'] = esc_html__('Your article has submitted and will be publish after the review.', 'docdirect');
                    } else {
                        $status = 'publish';
                        $json['message'] = esc_html__('Article added successfully.', 'docdirect');
                    }

                    $article_post = array(
                        'post_title' => $title,
                        'post_status' => $status,
                        'post_content' => $article_detail,
                        'post_author' => $user_id,
                        'post_type' => 'sp_articles',
                        'post_date' => current_time('Y-m-d H:i:s')
                    );

                    $post_id = wp_insert_post($article_post);

                    wp_set_post_terms($post_id, $article_tags, 'article_tags');
                    wp_set_post_terms($post_id, $article_categories, 'article_categories');

                    if (!empty($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }

                    $return_url = DocDirect_Scripts::docdirect_profile_menu_link($profile_page, 'articles', $user_id, 'true', 'listing');

                    $json['return_url'] = htmlspecialchars_decode($return_url);

                    update_post_meta($post_id, 'provider_category', $provider_category);

                    if (isset($approve_articles) && $approve_articles === 'need_approval') {
                        if (class_exists('DocDirectProcessEmail')) {
                            $email_helper = new DocDirectProcessEmail();
                            $emailData = array();
                            $emailData['article_name'] = $title;
                            $emailData['link'] = get_edit_post_link($post_id);

                            $email_helper->approve_article($emailData);
                        }
                    }

                } elseif (isset($type) && $type === 'update' && !empty($current)) {
                    $post_author = get_post_field('post_author', $current);
                    $post_id = $current;
                    $status = get_post_status($post_id);

                    if (intval($user_id) === intval($post_author)) {
                        $article_post = array(
                            'ID' => $current,
                            'post_title' => $title,
                            'post_content' => $article_detail,
                            'post_status' => $status,
                        );

                        wp_update_post($article_post);

                        wp_set_post_terms($post_id, $article_tags, 'article_tags');
                        update_post_meta($post_id, 'provider_category', $provider_category);
                        wp_set_post_terms($post_id, $article_categories, 'article_categories');

                        //delete prevoius attachment ID
                        $pre_attachment_id = get_post_thumbnail_id($post_id);
                        if (!empty($pre_attachment_id) && intval($pre_attachment_id) != intval($attachment_id)) {
                            wp_delete_attachment($pre_attachment_id, true);
                        }

                        //Set thumbnail
                        if (!empty($attachment_id)) {
                            delete_post_thumbnail($post_id);
                            set_post_thumbnail($post_id, $attachment_id);
                        } else if (!empty($pre_attachment_id)) {
                            wp_delete_attachment($pre_attachment_id, true);
                        }

                        $json['message'] = esc_html__('Article updated successfully.', 'docdirect');
                    } else {
                        $json['type'] = 'error';
                        $json['message'] = esc_html__('Some error occur, please try again later.', 'docdirect');
                        echo json_encode($json);
                        die;
                    }
                } else {
                    $json['type'] = 'error';
                    $json['message'] = esc_html__('Some error occur, please try again later.', 'docdirect');
                    echo json_encode($json);
                    die;
                }


                $json['type'] = 'success';
                echo json_encode($json);
                die;
            }

        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectCreateArticlesRoutes;
        $controller->register_routes();
    });
