<?php $is_vip_user = dwrestricted_user_has_subscription_balance(); ?>
<div class="special-user">
    <div class="right-special-user">
        <div class="top-content-special">
            <?php
                if ($type == 'file') {
                    echo '<span>'. __('This File is for VIP users only', 'dwrestricted') .'</span>';

                } else {
                    echo '<span>'. __('This Content is for VIP users only', 'dwrestricted') .'</span>';

                }
            ?>

            <?php if (! is_user_logged_in()): ?>
                <a href="<?php echo get_permalink(dw_option('login_page')); ?>" class="special-login">
                    <svg class="icons add-user-button"><use xlink:href="#add-user-button"></use></svg>
                    ورود
                </a>
            <?php endif; ?>

            <?php if (! $is_vip_user): ?>
                <a href="<?php echo wc_get_account_endpoint_url('subscriptions'); ?>" class="special-register">
                    <svg class="icons crown"><use xlink:href="#crown"></use></svg>
                    عضویت ویژه
                </a>
            <?php endif; ?>

        </div><!--top-content-special-->

        <?php if (! is_user_logged_in()): ?>
            <p>
                <?php
                    if ($type == 'file') {
                        _e('To view this file you need to first register and then buy a VIP subscription', 'dwrestricted');

                    } else {
                        _e('To view this content you need to first register and then buy a VIP subscription', 'dwrestricted');

                    }
                ?>
            </p>

        <?php elseif(! $is_vip_user): ?>
            <p>
                <?php
                    if ($type == 'file') {
                        _e('To view this file you need to buy a VIP subscription', 'dwrestricted');

                    } else {
                        _e('To view this content you need to buy a VIP subscription', 'dwrestricted');

                    }
                ?>
            </p>

        <?php else: ?>
            <p>
                <?php
                    if ($type == 'file') {
                        _e('To unlock this file you need to use your credit', 'dwrestricted');

                    } else {
                        _e('To unlock this content you need to use your credit', 'dwrestricted');

                    }
                ?>
            </p>

            <p>
                <?php
                    if (($credits = dwrestricted_post_credit_needed()) > 1) {
                        _e('Credits needed: ', 'dwrestricted');
                        echo "<strong>$credits</strong>";
                    }
                ?>
            </p>

            <p>
                <a style="display: inline-block;" class="dw-blue-btn dwrestircted-spend-balance" href="javascript: void(0);" data-post="<?php echo $post->ID; ?>" data-nonce="<?php echo wp_create_nonce("dw_restricted_{$post->ID}_spend_credit"); ?>">
                    <?php _e('Spend credit and unlock', 'dwrestricted'); ?>
                </a>
            </p>

        <?php endif; ?>

    </div><!--right-special-user-->

    <figure class="left-pic-special">
        <img src="<?php echo THEMEURI; ?>/images/queeeen.png" alt="img">
    </figure>
</div><!--special-user-->
