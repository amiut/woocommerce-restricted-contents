<?php
/**
 * Restricted content shortcode class
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS\Shortcodes;

defined('ABSPATH') || exit;


class Restricted_Shortcode
{
    /**
     * $shortcode_tag
     * holds the name of the shortcode tag
     * @var string
     */
    public static $shortcode_tag = 'dwrestricted';

    /**
     * class init will set the needed filter and action hooks
     *
     * @param array $args
     */
    public static function init($args = [])
    {
        //add shortcode
        add_shortcode(self::$shortcode_tag, [__CLASS__, 'shortcode_handler']);
    }

    /**
     * shortcode_handler
     * @param  array  $atts shortcode attributes
     * @param  string $content shortcode content
     * @return string
     */
    public static function shortcode_handler($atts, $content = null)
    {
        // Attributes
        $atts = extract(shortcode_atts(
            [
                'type'      => 'content',
                'id'   => 0
            ],
            $atts
        ));

        if (dwrestricted_user_has_access($id)) {
            return $content;
        }

        ob_start();
        dwrestricted_post_show_restricted_message($id, $type);
        return ob_get_clean();
    }
}
