<?php
/**
 * Useful functions
 *
 */
/**
 * Var_dump pre-ed!
 * For debugging purposes
 *
 * @param mixed $val desired variable to var_dump
 * @uses var_dump
 *
 * @return string
*/
if( !function_exists('dumpit') ) {
    function dumpit( $val ) {
        echo '<pre style="direction:ltr;text-align:left;">';
        var_dump( $val );
        echo '</pre>';
    }
}

if (! function_exists('dwrestricted_get_customr_address_formatted')) {
    function dwrestricted_get_customr_address_formatted($user_id) {
        $user_info = get_userdata($user_id);

        $address = [
            'first_name' => get_user_meta( $user_id, 'shipping_first_name', true ),
            'last_name'  => get_user_meta( $user_id, 'shipping_last_name', true ),
            'company'    => get_user_meta( $user_id, 'shipping_company', true ),
            'email'      => $user_id->user_email,
            'phone'      => get_user_meta( $user_id, 'shipping_phone', true ),
            'address_1'  => get_user_meta( $user_id, 'shipping_address_1', true ),
            'address_2'  => get_user_meta( $user_id, 'shipping_address_2', true ),
            'city'       => get_user_meta( $user_id, 'shipping_city', true ),
            'state'      => get_user_meta( $user_id, 'shipping_state', true ),
            'postcode'   => get_user_meta( $user_id, 'shipping_postcode', true ),
            'country'    => get_user_meta( $user_id, 'shipping_country', true )
        ];

        return $address;
    }
}


/**
 * Load a template part file
 *
 * @param string $path - relative path to the file (e.g includes/views/shortcode-login.php)
 * @param array  $args - Array of key=>value variables for passing to the included file
 */
if (! function_exists('dwrestricted_template_part')) {
    function dwrestricted_template_part($path = '', $args = []) {
        $theme_path = trailingslashit(get_stylesheet_directory()) . 'woocommerce/' . $path . '.php';
        $plugin_path = trailingslashit(DW_RESTRICTED_CONTENTS_ABSPATH) . 'templates/' . $path . '.php';


        if (file_exists($theme_path)) {
            extract($args);
            include $theme_path;

        } elseif (file_exists($plugin_path)) {
            extract($args);
            include $plugin_path;
        }
    }
}

if (! function_exists('dwrestricted_get_subscription_packages')) {
    function dwrestricted_get_subscription_packages() {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}dwrestricted_packages";
        $result = [];

        foreach ($wpdb->get_results( $sql, 'ARRAY_A' ) as $item) {
            $result[] = new DW_RESTRICTED_CONTENTS\Package($item['ID']);
        }

        return $result;
    }
}

if (! function_exists('dwrestricted_get_user_subscriptions')) {
    function dwrestricted_get_user_subscriptions($user_id = 0, $only_active = true) {
        global $wpdb;

        if (! $user_id) {
            global $current_user;
            $user_id = $current_user->ID;
        }


        if (! $user_id) {
            return [];
        }

        $sql = "SELECT * FROM {$wpdb->prefix}dwrestricted_subscriptions WHERE user_id=%d ";

        if ($only_active) {
            $sql .= " AND status='active'";
        }

        $sql = $wpdb->prepare(
            $sql,
            $user_id
        );
        $result = [];

        foreach ($wpdb->get_results( $sql, 'ARRAY_A' ) as $item) {
            $result[] = new DW_RESTRICTED_CONTENTS\Subscription($item['ID']);
        }

        return $result;
    }
}

if (! function_exists('dwrestricted_get_user_subscription_balance')) {
    function dwrestricted_get_user_subscription_balance($user_id = 0) {
        global $wpdb;

        if (! $user_id) {
            global $current_user;
            $user_id = $current_user->ID;
        }

        if (! $user_id) {
            return 0;
        }

        $sql = "SELECT SUM(balance) FROM {$wpdb->prefix}dwrestricted_subscriptions WHERE user_id=%d AND status='active'";
        $sql = $wpdb->prepare(
            $sql,
            $user_id
        );

        $result = absint($wpdb->get_var($sql));

        return $result;
    }
}

if (! function_exists('dwrestricted_user_has_subscription_balance')) {
    function dwrestricted_user_has_subscription_balance($user_id = 0) {
        if (! $user_id) {
            global $current_user;
            $user_id = $current_user->ID;
        }

        if (! $user_id) {
            return 0;
        }

        return dwrestricted_get_user_subscription_balance($user_id) > 0;
    }
}

if (! function_exists('dwrestricted_user_spend_subscription_balance')) {
    function dwrestricted_user_spend_subscription_balance($amount = 1, $post_id = 0, $subscription_id = 0, $user_id = 0) {
        if (! dwrestricted_user_has_subscription_balance()) return 0;

        global $wpdb;

        if (! $user_id) {
            global $current_user;
            $user_id = $current_user->ID;
        }

        if (! $user_id) {
            return [];
        }

        $sql = $wpdb->prepare(
            "SELECT ID,balance FROM {$wpdb->prefix}dwrestricted_subscriptions WHERE user_id=%d AND status='active'",
            $user_id
        );

        if ($subscription_id) {
            $sql .= " AND ID=%d";
            $sql = $wpdb->prepare($sql, $subscription_id);
        }

        $results = $wpdb->get_results($sql, 'ARRAY_A');

        if ($results) {
            $subscription = new DW_RESTRICTED_CONTENTS\Subscription((int) $results[0]['ID']);
            $subscription->spend_balance($amount, $post_id);
            return $subscription->get_balance();

        } else {
            return 0;

        }
    }
}

if (! function_exists('dwrestricted_post_is_restricted')) {
    function dwrestricted_post_is_restricted($post = null) {
        $post = get_post($post);

        return get_post_meta($post->ID, '_dwrestricted_active', true) == 'yes';
    }
}

if (! function_exists('dwrestricted_post_credit_needed')) {
    function dwrestricted_post_credit_needed($post = null) {
        $post = get_post($post);

        return absint(get_post_meta($post->ID, '_dwrestricted_credit', true)) ?: 1;
    }
}

if (! function_exists('dwrestricted_post_show_restricted_message')) {
    function dwrestricted_post_show_restricted_message($post = null, $type = 'file') {
        $post = get_post($post);

        if (! dwrestricted_post_is_restricted($post)) {
            return;
        }

        dwrestricted_template_part('myaccount/restricted-message', ['post' => $post, 'type' => $type]);
    }
}

if (! function_exists('dwrestricted_user_has_access')) {
    function dwrestricted_user_has_access($post = null) {
        $post = get_post($post);

        if (! is_user_logged_in()) return false;

        $current_user = wp_get_current_user();

        return (new DW_RESTRICTED_CONTENTS\Spent_Credit())->find_user_post((int) $current_user->ID, (int) $post->ID)->exists();
    }
}

if (! function_exists('dwrestricted_user_spent_credits')) {
    function dwrestricted_user_spent_credits($user_id = null) {
		global $wpdb;
		
		if (! $user_id && is_user_logged_in()) {
			$user_id = get_current_user_id();
		} else {
			return [];
		}
		
		$data = $wpdb->get_results(
            $wpdb->prepare("
                SELECT ID FROM {$wpdb->prefix}dwrestricted_used_credits WHERE user_id = %d
            ",
            $user_id)
        );
				
		return array_map(function($one) {
			return (new DW_RESTRICTED_CONTENTS\Spent_Credit($one->ID));
		}, $data);
	}
}
