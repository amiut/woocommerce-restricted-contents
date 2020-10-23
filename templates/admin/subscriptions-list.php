<?php
?>


<div class="wrap restricted-content-admin-ui">
    <h1><?php _e('User Subscriptions', 'dwrestricted'); ?></h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        \DW_RESTRICTED_CONTENTS\Admin\Admin::$subscriptions_object->prepare_items();
                        \DW_RESTRICTED_CONTENTS\Admin\Admin::$subscriptions_object->display(); ?>
                    </form>
                </div>
            </div>
        </div>

        <br class="clear">
    </div>

    <div class="add-package-wrap">
        <h2><?php _e('Add subscription for user', 'dwrestricted'); ?></h2>

        <form class="restricted-create-ajax-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
            <p class="form-row">
                <label for="package_id"><?php _e('Package', 'dwrestricted'); ?></label><br>
                <input id="package_id" type="text" name="package_id" value="">
            </p>

            <p class="form-row">
                <label for="user_id"><?php _e('User', 'dwrestricted'); ?></label><br>
                <input id="user_id" type="text" name="user_id" value="">
            </p>

            <p class="form-row">
                <label for="is_active"><input id="is_active" type="checkbox" value="yes" name="active" value=""> <?php _e('Activate Subscription (Without payment)', 'dwrestricted'); ?></label>
            </p>

            <input type="hidden" name="action" value="dwrestricted_create_subscription">
            <?php wp_nonce_field('dwrestricted_create_subscription_nonce', 'rsct_nonce'); ?>
            <button type="submit" class="button primary"><?php _e('Assign user', 'dwrestricted'); ?></button>
        </form>
    </div><!-- .add-package-wrap -->
</div>
