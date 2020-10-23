<?php
/**
 * PDFGEN Post Types Class class
 * Post types, Taxonomies, meta boxes, post columns are registered here
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS;

defined('ABSPATH') || exit;

/**
 * Post Types class
 */
class Post_Types{

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('init', [__CLASS__, 'register_taxonomies']);

        // Register Metaboxes
        add_action('add_meta_boxes', [__CLASS__, 'metaboxes']);

        // Save Post metas
        add_action('save_post', [__CLASS__, 'save_metas'], 10, 3);
    }

    public static function register_post_types() {
        // PDF Generated Documents
    }

    /**
     * Register Metaboxes
     */
    public static function metaboxes() {
        $restricted_mb_post_types = apply_filters('dwrestricted_restricted_metabox_post_types', ['post', 'product']);
        add_meta_box('dwrestricted_metabox', __('Restricted Content', 'dwrestricted'), [__CLASS__, 'restricted_metabox'], $restricted_mb_post_types, 'advanced', 'high');
    }

    public static function restricted_metabox($post) {
        include DW_RESTRICTED_CONTENTS_ABSPATH . '/templates/admin/restricted-metabox.php';
    }

    public static function move_metaboxes() {

    }

    public static function register_taxonomies() {

    }

    public static function save_metas($post_id, $post, $update) {
        if (! isset($_POST["dwrestricted_nonce"]) || ! wp_verify_nonce($_POST["dwrestricted_nonce"], "dwrestricted_{$post->ID}_nonce")) return;

        if(! current_user_can("edit_post", $post_id)) return;

        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) return;

        $restricted_mb_post_types = apply_filters('dwrestricted_restricted_metabox_post_types', ['post', 'product']);
        if(! in_array($post->post_type, $restricted_mb_post_types)) return;

        if (isset($_POST['is_restricted'])) {
            update_post_meta($post_id, '_dwrestricted_active', 'yes');

        } else {
            update_post_meta($post_id, '_dwrestricted_active', 'no');
            delete_post_meta($post_id, '_dwrestricted_active');
        }

        if (isset($_POST['restricted_credit'])) {
            update_post_meta($post_id, '_dwrestricted_credit', absint(trim($_POST['restricted_credit'])));
        }
    }

}
