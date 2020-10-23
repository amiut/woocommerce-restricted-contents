# Restricted contents by credit for WooCommerce
With this plugin you can sell subscriptions based on credit count, e.g you can specify how much credit is needed to unlock a certain content and users can buy and spend credits to access restricted contents.

# Available shortcodes
If you want to partialy restrict some contents in your post you can use `[dwrestricted]` shortcode, if you want to restrict the entire post, you can use below PHP functions:

## Check if post is restricted
``dwrestricted_post_is_restricted( int|WP_Post $post ) : bool``

## Check how much credit is needed to unlock the post
``dwrestricted_post_credit_needed( int|WP_Post $post ) : int``

## Check if user has access to post
Thi will return true if user has already spent credits to access the post

``dwrestricted_user_has_access( int|WP_Post $post ) : bool``

## Output Restricted Message once the post is restricted and user doesn't have access
``dwrestricted_post_show_restricted_message( int|WP_Post $post ) : void``

The template can be overrided in `woocommerce/myaccount/restricted-message.php` in your theme directory

