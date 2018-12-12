<?php
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
if (!class_exists('DocdirectAppImageUploaderRoutes')) {

    class DocdirectAppImageUploaderRoutes extends WP_REST_Controller{

        /**
         * Register the routes for the objects of the controller.
         */
        public function register_routes() {
            $version 	= '1';
            $namespace 	= 'api/v' . $version;
            $base 		= 'media';

            register_rest_route($namespace, '/' . $base . '/upload_media',
                array(
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'save_image'),
                        'args' => array(),
                    ),
                )
            );
        }


        /**
         * upload media
         *
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        function save_image($request)
        {
            if(!empty($request['user_id'])){
                $user_identity	= $request['user_id'];
                $submitted_file = $_FILES['media'];
                $type           = $request[ 'type' ];
                $json = array();
                
                if( empty( $submitted_file ) || empty( $type ) ) {
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Kindly fill all fields', 'docdirect');
                    return new WP_REST_Response($json, 200);
                }

                $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );                
                //return $submitted_file;
                if ( !empty( $submitted_file )) {
                    $file_name = basename( $submitted_file[ 'name' ] );
                    $file_type = wp_check_filetype( $uploaded_image[ 'file' ] );

                    // Prepare an array of post data for the attachment.
                    $attachment_details = array(
                        'guid' => $uploaded_image[ 'url' ],
                        'post_mime_type' => $file_type[ 'type' ],
                        'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment( $attachment_details, $uploaded_image[ 'file' ] );
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_image[ 'file' ] );
                    wp_update_attachment_metadata( $attach_id, $attach_data );

                    //Image Size
                    $image_size	= 'thumbnail';
                    if( !empty( $type ) && $type === 'profile_image' ){
                        $image_size	= 'docdirect_user_profile';
                    } if( !empty( $type ) && $type === 'profile_banner' ){
                        $image_size	= 'docdirect_user_banner';
                        docdirect_get_profile_image_url( $attach_data,$image_size ); //get image url
                        $image_size	= 'docdirect_user_profile';
                    } else if( !empty( $type ) && $type === 'user_gallery' ){
                        $image_size	= 'thumbnail';
                    }


                    $thumbnail_url = docdirect_get_profile_image_url( $attach_data,$image_size ); //get image url

                    if( !empty( $type ) && $type === 'profile_image' ){
                        $get_id  =  get_user_meta($user_identity, 'userprofile_media', true);
                        if( !empty( $get_id ) ){
                            wp_delete_attachment( $get_id, true ); //delete from media
                        }

                        update_user_meta($user_identity, 'userprofile_media', $attach_id);
                    } if( !empty( $type ) && $type === 'profile_banner' ){
                        $get_id	 =  get_user_meta($user_identity, 'userprofile_banner', true);
                        if( !empty( $get_id ) ){
                            wp_delete_attachment( $get_id, true ); //delete from media
                        }

                        update_user_meta($user_identity, 'userprofile_banner', $attach_id);
                    } else if( !empty( $type ) && $type === 'email_image' ){
                        $get_id  =  get_user_meta($user_identity, 'email_media', true);
                        if( !empty( $get_id ) ){
                            wp_delete_attachment( $get_id, true ); //delete from media
                        }

                        update_user_meta($user_identity, 'email_media', $attach_id);
                    } else if( !empty( $type ) && $type === 'user_gallery' ){
                        $gallery  =  get_the_author_meta('user_gallery',$user_identity);
                        if( !empty( $gallery ) ){
                            $gallery[$attach_id]['url']	= $thumbnail_url;
                            $gallery[$attach_id]['id']	= $attach_id;
                        } else{
                            $gallery	=  array();
                            $gallery[$attach_id]['url']	= $thumbnail_url;
                            $gallery[$attach_id]['id']	= $attach_id;
                        }

                        update_user_meta( $user_identity, 'user_gallery', $gallery );
                    }

                    $json = array(
                        'type' => 'success',
                        'message' => 'Image uploaded',
                        'url' => $thumbnail_url,
                        'attachment_id' => $attach_id
                    );

                    return new WP_REST_Response($json, 200); 

                } else {
                    $json['type']       = 'error';
                    $json['message']    = esc_html__('Image upload failed', 'docdirect');
                    return new WP_REST_Response($json, 200);                   
                }
            } else {
                $json['type']       = 'error';
                $json['message']    = esc_html__('User ID missing', 'docdirect');
                return new WP_REST_Response($json, 200);
            }
        }

    }
}

add_action('rest_api_init',
    function () {
        $controller = new DocdirectAppImageUploaderRoutes;
        $controller->register_routes();
    });
