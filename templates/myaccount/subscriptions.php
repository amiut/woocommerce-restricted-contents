<div class="restricted-content-front-ui">
    <h2><?php _e('Subscriptions', 'dwrestricted'); ?></h2>

    <div class="subscription-packages-list">
        <?php if (dwrestricted_get_subscription_packages()) : ?>
            <?php
                foreach (dwrestricted_get_subscription_packages() as $package) {
                    dwrestricted_template_part('myaccount/package-item', ['package' => $package]);
                }
            ?>


        <?php else: ?>
            <p class="no-packages-found"><?php _e('We do not have any subscription plan right now', 'dwrestricted'); ?></p>
        <?php endif; ?>

    </div><!-- .subscription-packages-list -->

</div>
