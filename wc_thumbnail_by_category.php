    <?php
    /**
     * Plugin Name: WC Product Thumbnail By Category (ACF)
     * Plugin URI: https://plugins.defaultsettings.co.uk/wc-product-by-thumbnail
     * Description: Dynamic product thumbnail by category using ACF fields
     * Version: 1.0.1
     * Author: Default Settings
     * Author URI: https://plugins.defaultsettings.co.uk
     * Text Domain: ds
     */

    require 'inc/acf-fields.php';

    add_action('admin_init', 'wc_ptbc_has_required_plugins' );
    add_action('init', 'wc_ptbc_init', 20);
    function wc_ptbc_init(){
        add_filter('acf/load_field/name=category_id', 'wc_ptbc_acf_load_color_field_choices');
        add_filter('wp_get_attachment_image_src', 'wc_ptbc_set_product_thumbnail_image_link');
    }

    function wc_ptbc_has_required_plugins() {
        if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
            add_action( 'admin_notices', 'wc_ptbc_has_required_plugins_notification' );

            deactivate_plugins( plugin_basename( __FILE__ ) );

            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
    }

    function wc_ptbc_has_required_plugins_notification(){
        print '<div class="error"><p>Sorry, but WC Product Thumbnail By Category requires the Advanced Custom Fields plugin (pro) to be installed and active.</p></div>';
    }

    function wc_ptbc_acf_load_color_field_choices( $field ) {

        // reset choices
        $field['choices'] = array();


        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        );
        foreach( get_categories( $args ) as $category ) {
            $field['choices'][$category->term_id] = $category->name;
        }

        return $field;
    }

    function wc_ptbc_set_product_thumbnail_image_link($thumbnail){

        if (is_product_category()) {
            $cate = get_queried_object();
            $category_id = $cate->term_id;

            $category_images = get_field("field_6277a46fdfa5a");

            if (is_array($category_images)) {
                foreach ($category_images as $category_image){
                    if ($category_image['category_id'] == $category_id) {
                        $image_path = $category_image['product_thumbnail'];
                        $size = getimagesize(str_replace('/wp-content/themes', '', get_theme_root()) . parse_url($image_path, PHP_URL_PATH));

                        return [$category_image['product_thumbnail'], $size[0], $size[1]];
                    }
                }
            }
        }

        return $thumbnail;
    }
