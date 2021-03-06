<?php
/**
 * Plugin Name: SiteOrigin CSS
 * Plugin URI: https://wordpress.org/plugins/so-css/
 *
 * Compatibility Description: Ensures compatibility with CSS files generated by SiteOrigin.
 *
 * @todo New file don't generate if exist in SyncNonMedia::registered_files list but not in GCS
 */

namespace wpCloud\StatelessMedia {

    if (!class_exists('wpCloud\StatelessMedia\SOCSS')) {
        
        class SOCSS extends ICompatibility {
            protected $id = 'so-css';
            protected $title = 'SiteOrigin CSS';
            protected $constant = 'WP_STATELESS_COMPATIBILITY_SOCSS';
            protected $description = 'Ensures compatibility with CSS files generated by SiteOrigin.';
            protected $plugin_file = 'so-css/so-css.php';

            public function module_init($sm){
                add_filter( 'set_url_scheme', array( $this, 'set_url_scheme' ), 20, 3 );
                add_action( 'admin_menu', array($this, 'action_admin_menu'), 3 );
            }

            /**
             * Change Upload BaseURL when CDN Used.
             *
             * @param $data
             * @return mixed
             */
            public function action_admin_menu() {
                if ( current_user_can('edit_theme_options') && isset( $_POST['siteorigin_custom_css_save'] ) ) {
                    try{
                        $object_list = ud_get_stateless_media()->get_client()->list_objects("prefix=so-css");
                        $files_array = $object_list->getItems();
                        foreach ($files_array as $file) {
                            do_action( 'sm:sync::deleteFile', $file->name );
                        }
                    }
                    catch(Exception $e){}
                }
            }

            /**
             * Change Upload BaseURL when CDN Used.
             *
             * @param $data
             * @return mixed
             */
            public function set_url_scheme( $url, $scheme, $orig_scheme ) {
                $position = strpos($url, 'so-css/');
                if( $position !== false ){
                    $upload_data = wp_upload_dir();
                    $name = substr($url, $position);
                    $absolutePath = $upload_data['basedir'] . '/' .  $name;
                    do_action( 'sm:sync::syncFile', $name, $absolutePath);
                    $url = ud_get_stateless_media()->get_gs_host() . '/' . $name;
                }
                return $url;
            }

        }

    }

}
