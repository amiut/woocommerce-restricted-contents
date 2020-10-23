<?php
/**
 * Admin Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace DW_RESTRICTED_CONTENTS\Admin;

class Admin
{
    public static $packages_object = null;
    public static $subscriptions_object = null;

    public static function init()
    {
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'], 9 );
    }

    public static function admin_menu() {
        $rc_main_menu = add_menu_page(__('Restricted contents', 'dwrestricted'), __('Restricted contents', 'dwrestricted'), 'manage_options', 'dwrestricted-packages', [__CLASS__,'packages_list_page'], 'dashicons-lock', 10);
        add_action( "load-$rc_main_menu", [__CLASS__, 'rc_packages_screen_option' ] );

        add_submenu_page('dwrestricted-packages', __('Subscriptio packages', 'dwrestricted'), __('Packages', 'dwrestricted'), 'manage_options', 'dwrestricted-packages', [__CLASS__, 'packages_list_page']);

        $rc_subscriptions_menu = add_submenu_page('dwrestricted-packages', __('User subscriptions', 'dwrestricted'), __('User subscriptions', 'dwrestricted'), 'manage_options', 'dwrestricted-subscriptions', [__CLASS__, 'user_subscriptions_list_page']);
        add_action( "load-$rc_subscriptions_menu", [__CLASS__, 'rc_subscriptions_scren_options' ] );
    }

    public static function packages_list_page() {
        include DW_RESTRICTED_CONTENTS_ABSPATH . '/templates/admin/packages-list.php';
    }

    public static function user_subscriptions_list_page() {
        include DW_RESTRICTED_CONTENTS_ABSPATH . '/templates/admin/subscriptions-list.php';
    }

    public static function rc_packages_screen_option() {
        $option = 'per_page';
		$args   = [
			'label'   => 'Packages',
			'default' => 15,
			'option'  => 'packages_per_page'
		];

		add_screen_option( $option, $args );

		self::$packages_object = new \DW_RESTRICTED_CONTENTS\Packages_List();
    }

    public static function rc_subscriptions_scren_options() {
        $option = 'per_page';
		$args   = [
			'label'   => 'Subscriptions',
			'default' => 15,
			'option'  => 'subscriptions_per_page'
		];

		add_screen_option( $option, $args );

        self::$subscriptions_object = new \DW_RESTRICTED_CONTENTS\Subscriptions_List();
    }
}
