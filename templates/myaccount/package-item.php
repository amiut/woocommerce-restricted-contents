<?php
$current_user = wp_get_current_user();
?>
<div class="subscription-package-item">
    <h3 class="package-title"><?php echo $package->get_title(); ?></h3>
    <p class="package-time"><?php echo __('No time limit', 'dwrestricted'); ?></p>

    <div class="access-count">
        <span class="count"><?php echo $package->get_access_count(); ?></span>
        <p class="txt"><?php echo __('Access VIP content', 'dwrestricted'); ?></p>
    </div><!-- .access-count -->

    <p class="price">
        <?php echo wc_price($package->get_price()); ?>
    </p>

    <ul class="specs">
        <li><?php echo __('No time limit', 'dwrestricted'); ?></li>
        <li><?php echo __('Access VIP Content', 'dwrestricted'); ?></li>
        <li><?php echo __('No time limit', 'dwrestricted'); ?></li>
    </ul>

    <a class="buy-button subscription-buy-button" href="javascript: void(0);" data-package="<?php echo $package->get_id(); ?>" data-nonce="<?php echo wp_create_nonce("dwrestricted_user_{$current_user->ID}_buy_subscription_nonce"); ?>">
        <svg class="icon icon-shopping-cart1"><use xlink:href="#shopping-cart1"></use></svg>
        <?php _e('Purchase', 'dwrestricted'); ?>
    </a>
</div><!-- .subscription-package-item -->
