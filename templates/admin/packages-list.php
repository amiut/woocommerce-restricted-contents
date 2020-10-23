<?php
?>


<div class="wrap restricted-content-admin-ui">
    <h1><?php _e('Packages', 'dwrestricted'); ?></h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        \DW_RESTRICTED_CONTENTS\Admin\Admin::$packages_object->prepare_items();
                        \DW_RESTRICTED_CONTENTS\Admin\Admin::$packages_object->display(); ?>
                    </form>
                </div>
            </div>
        </div>

        <br class="clear">
    </div>

    <div class="add-package-wrap">
        <h2><?php _e('Add new package', 'dwrestricted'); ?></h2>

        <form class="restricted-create-ajax-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
            <p class="form-row">
                <label for="package_title"><?php _e('Package title', 'dwrestricted'); ?></label><br>
                <input id="package_title" type="text" name="title" value="">
            </p>

            <p class="form-row">
                <label for="package_price"><?php _e('Package price', 'dwrestricted'); ?></label><br>
                <input id="package_price" type="number" min="0" name="price" value="">
            </p>

            <p class="form-row">
                <label for="package_access_count"><?php _e('Access count', 'dwrestricted'); ?></label><br>
                <input id="package_access_count" type="number" min="1" name="access_count" value="">
            </p>

            <input type="hidden" name="action" value="dwrestricted_create_package">
            <?php wp_nonce_field('dwrestricted_create_package_nonce', 'rsct_nonce'); ?>
            <button type="submit" class="button primary"><?php _e('Create package', 'dwrestricted'); ?></button>
        </form>
    </div><!-- .add-package-wrap -->
</div>
