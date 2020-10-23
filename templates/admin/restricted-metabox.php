<?php
$is_active = get_post_meta($post->ID, '_dwrestricted_active', true) == 'yes';
$credit = absint(get_post_meta($post->ID, '_dwrestricted_credit', true)) ?: 1;
?>
<div class="dwrestricted-metabox">
    <label><input <?php checked($is_active); ?> type="checkbox" name="is_restricted" value="yes"> <strong class="label"><?php _e('This post is restricted to users with subscription', 'dwrestricted'); ?></strong></label>

    <p class="form-row">
        <label for="restricted_points"><?php _e('Needed credit', 'dwrestricted'); ?></label><br>
        <span class="note"><?php _e('How much credit is needed to unlock this content?', 'dwrestricted'); ?></span><br>

        <input type="number" min="1" step="1" name="restricted_credit" id="restricted_points" value="<?php echo $credit; ?>">

    </p>

    <?php wp_nonce_field("dwrestricted_{$post->ID}_nonce", 'dwrestricted_nonce'); ?>
</div><!-- .dwrestricted-metabox -->
