<?php
/**
 * Restricted Main class
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS;

defined('ABSPATH') || exit;

/**
 * DW_RESTRICTED_CONTENTS main class
 */
final class App
{
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
    public $version = '1.0';

    /**
     * Plugin instance.
     *
     * @since 1.0
     * @var null|DW_RESTRICTED_CONTENTS\App
     */
    public static $instance = null;

    /**
     * Plugin API.
     *
     * @since 1.0
     * @var WP_PDFGEN\API\API
     */
    public $api = '';

    /**
     * Return the plugin instance.
     *
     * @return DW_RESTRICTED_CONTENTS
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * DW_RESTRICTED_CONTENTS constructor.
     */
    private function __construct() {
        add_action('init', [$this, 'i18n']);

        $this->define_constants();
        $this->init();
        $this->includes();

        add_action('wp_ajax_dwrestricted_create_package', [$this, 'create_package']);
        add_action('wp_ajax_dwrestricted_create_subscription', [$this, 'create_subscription']);
        add_action('wp_ajax_dwrestricted_buy_subscription', [$this, 'buy_subscription']);
        add_action('wp_ajax_dwrestricted_spend_credit_for_post', [$this, 'spend_credit_for_post']);

        $this->woocommerce_tweaks();
    }

    /**
     * Make Translatable
     *
     */
    public function i18n() {
        load_plugin_textdomain( 'dwrestricted', false, dirname( plugin_basename( DW_RESTRICTED_CONTENTS_FILE ) ) . "/languages" );
    }

    /**
     * Include required files
     *
     */
    public function includes() {
        include DW_RESTRICTED_CONTENTS_ABSPATH . 'includes/functions.php';
    }

    /**
     * Define constant if not already set.
     *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
    }

    /**
     * Define constants
     */
    public function define_constants() {
		$this->define('DW_RESTRICTED_CONTENTS_ABSPATH', dirname(DW_RESTRICTED_CONTENTS_FILE) . '/');
		$this->define('DW_RESTRICTED_CONTENTS_PLUGIN_BASENAME', plugin_basename(DW_RESTRICTED_CONTENTS_FILE));
		$this->define('DW_RESTRICTED_CONTENTS_BOOKING_VERSION', $this->version);
		$this->define('DW_RESTRICTED_CONTENTS_PLUGIN_URL', $this->plugin_url());
		$this->define('DW_RESTRICTED_CONTENTS_API_TEST_MODE', true);
    }

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit(plugins_url('/', DW_RESTRICTED_CONTENTS_FILE));
    }

    /**
     * Do initial stuff
     */
    public function init() {
        // Install
        register_activation_hook(DW_RESTRICTED_CONTENTS_FILE, ['DW_RESTRICTED_CONTENTS\\Install', 'install']);

        // Post types
        Post_Types::init();

        // Admin
        Admin\Admin::init();

        // Shortcodes
        Shortcodes\Restricted_Shortcode::init();

        // Add scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'public_dependencies']);
        add_action('admin_enqueue_scripts', [$this, 'admin_dependencies']);
    }

    /**
     * Register scripts and styles for public area
     */
    public function public_dependencies() {

    }

    public function get_version() {
        return $this->version;
    }

    /**
     * Register scripts and styles for admin area
     */
    public function admin_dependencies($screen) {
        if (! in_array($screen, ['toplevel_page_dwrestricted-packages']) && strpos($screen, 'dwrestricted-subscriptions') === false) return;

        wp_enqueue_style('dw-restricted-admin', $this->plugin_url() . '/assets/css/admin.css', [], $this->get_version());
        wp_enqueue_script('dw-restricted-admin', $this->plugin_url() . '/assets/js/admin.js', [], null, true);
    }

    public function create_package() {
        header('Content-type: application/json; charset = utf-8');

        $title = ! empty($_POST['title']) ? sanitize_text_field($_POST['title']) : false;
        $price = ! empty($_POST['price']) ? floatval($_POST['price']) : false;
        $access_count = ! empty($_POST['access_count']) ? absint($_POST['access_count']) : false;

        if (! current_user_can('administrator')) {
            wp_send_json_error([
                'message' => __('Insuficient access', 'dwrestricted'),
            ]);
        }

        if (! wp_verify_nonce($_POST['rsct_nonce'], 'dwrestricted_create_package_nonce')) {
            wp_send_json_error([
                'message' => __('Insuficient access', 'dwrestricted'),
            ]);
        }

        if (! $title) {
            wp_send_json_error([
                'message' => __('Enter package title', 'dwrestricted'),
                'where'   => 'package_title'
            ]);
        }

        if (! $price) {
            wp_send_json_error([
                'message' => __('Enter package price', 'dwrestricted'),
                'where'   => 'package_price'
            ]);
        }

        if (! $access_count) {
            wp_send_json_error([
                'message' => __('Enter package access count', 'dwrestricted'),
                'where'   => 'package_access_count'
            ]);
        }

        $package = new Package();
        $package->set_title($title)->set_price($price)->set_access_count($access_count);
        $package->save();
        wp_send_json_success([
            'message' => __('Package Created', 'dwrestricted'),
            'id'      => $package->get_id()
        ]);
    }

    public function create_subscription() {
        header('Content-type: application/json; charset = utf-8');

        $user    = ! empty($_POST['user_id']) ? absint($_POST['user_id']) : false;
        $package = ! empty($_POST['package_id']) ? absint($_POST['package_id']) : false;
        $active  = ! empty($_POST['active']);

        if (! current_user_can('administrator')) {
            wp_send_json_error([
                'message' => __('Insuficient access', 'dwrestricted'),
            ]);
        }

        if (! wp_verify_nonce($_POST['rsct_nonce'], 'dwrestricted_create_subscription_nonce')) {
            wp_send_json_error([
                'message' => __('Insuficient access', 'dwrestricted'),
            ]);
        }

        if (! $user) {
            wp_send_json_error([
                'message' => __('Choose a user', 'dwrestricted'),
                'where'   => 'user_id'
            ]);
        }

        if (! $package) {
            wp_send_json_error([
                'message' => __('Choose a package', 'dwrestricted'),
                'where'   => 'package_id'
            ]);
        }

        $subscription = new Subscription();
        $package = new Package($package);
        $subscription->set_user($user)->set_package($package)->set_purchase_date(current_time('Y-m-d H:i:s'))->set_balance($package->get_access_count());

        if ($active) {
            $subscription->set_status('active');
        }

        $subscription->save();

        if (! $active) {
            $subscription->set_status('inactive');
            $subscription->set_balance(0);
            $subscription->generate_invoice();
            $subscription->save();
        }

        wp_send_json_success([
            'message' => __('Subscription Created', 'dwrestricted'),
            'id'      => $subscription->get_id()
        ]);
    }

    public function buy_subscription() {
        header('Content-type: application/json; charset = utf-8');
        delete_transient( 'dw_restricted_purchasing_subscription' );
        if ( 'yes' === get_transient( 'dw_restricted_purchasing_subscription' ) ) {
            wp_send_json_error([
                'message' => __('Another purchase is in progress, please try again in 3 minutes', 'dwrestricted'),
            ]);
        }

        set_transient('dw_restricted_purchasing_subscription', 'yes', MINUTE_IN_SECONDS * 3);

        $package = ! empty($_POST['package']) ? absint($_POST['package']) : false;
        $current_user = wp_get_current_user();

        if (! $current_user->ID || ! wp_verify_nonce($_POST['nonce'], "dwrestricted_user_{$current_user->ID}_buy_subscription_nonce")) {
            wp_send_json_error([
                'message' => __('Invalid Request', 'dwrestricted'),
            ]);
        }

        $subscription = new Subscription();
        $package = new Package($package);
        $subscription->set_user($current_user->ID)->set_package($package->get_id())->set_purchase_date(current_time('Y-m-d H:i:s'))->set_balance($package->get_access_count());
        $subscription->save();

        if (! $active) {
            $subscription->set_status('inactive');
            $subscription->set_balance(0);
            $subscription->generate_invoice();
            $subscription->save();
        }

        delete_transient( 'dw_restricted_purchasing_subscription' );

        wp_send_json_success([
            'message'           => __('Your subscription is created an will be activated once you pay regarding invoice', 'dwrestricted'),
            'payment_link'      => $subscription->get_invoice()->get_checkout_payment_url(),
            'view_invoice_link' => $subscription->get_invoice()->get_view_order_url(),
        ]);
    }

    public function spend_credit_for_post() {
        header('Content-type: application/json; charset = utf-8');

        $post_id = ! empty($_POST['post_id']) ? absint($_POST['post_id']) : false;

        if (! is_user_logged_in()) {
            wp_send_json_error([
                'message' => __('You are not logged in', 'dwrestricted'),
            ]);
        }

        if (! wp_verify_nonce($_POST['nonce'], "dw_restricted_{$post_id}_spend_credit")) {
            wp_send_json_error([
                'message' => __('Invalid Request', 'dwrestricted'),
            ]);
        }

        if (! $post_id || ! get_post_status($post_id)) {
            wp_send_json_error([
                'message' => __('Post does not exist', 'dwrestricted'),
            ]);
        }

        if (dwrestricted_user_has_access($post_id)) {
            wp_send_json_error([
                'message' => __('You already have access to this content', 'dwrestricted'),
            ]);
        }

        $current_user = wp_get_current_user();
        $credit = dwrestricted_post_credit_needed($post_id);
        dwrestricted_user_spend_subscription_balance($credit, $post_id);

        wp_send_json_success([
            'redirect' => get_permalink($post_id),
            'message'  => __('You can now have access to this content', 'dwrestricted'),
        ]);

    }

    public function woocommerce_tweaks() {
        add_action('woocommerce_payment_complete', [$this, 'activate_subscription'], 9999);
		
		add_action( 'woocommerce_order_status_changed', function($order_id, $old_status, $new_status) {
			if( $new_status == "completed" ) { 
				$this->activate_subscription($order_id);
			}
			
		}, 10, 3 );

        add_filter( 'woocommerce_account_menu_items', [$this, 'account_links'], 10, 1 );
        add_action( 'init', [$this, 'account_endpoints'] );

        add_action( 'woocommerce_account_subscriptions_endpoint', function() {
            dwrestricted_template_part('myaccount/subscriptions');
        });
    }

    public function account_links($links) {
        $new_links = array(
            'subscriptions' => __('My subscriptions', 'dwrestricted'),
        );

        $new_links = array_slice( $links, 0, 1, true ) +
            $new_links +
            array_slice( $links, 1, count( $links ), true );

        return $new_links;
    }

    public static function account_endpoints() {
        add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
    }

    public function activate_subscription( $order_id ){
        $subscription_id = absint(get_post_meta($order_id, '_subscription_id', true));

        if ($subscription_id) {
            $order = wc_get_order( $order_id );
            $subscription = new Subscription($subscription_id);
            $subscription->set_status('active');
            $subscription->set_balance($subscription->get_package()->get_access_count());
            $subscription->save();
        }
    }
}
