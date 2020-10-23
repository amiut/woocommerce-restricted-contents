<?php
/**
 * User Subscription Class
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS;

class Subscription
{
    /**
     * package id
     */
    public $id = 0;

    /**
     * Package title
     *
     * @var string
     */
    protected $title;

    /**
     * Package price
     *
     * @var string
     */
    protected $package_id;

    /**
     * Package price
     *
     * @var string
     */
    protected $user_id;

    /**
     * Package access count
     *
     * @var string
     */
    protected $balance;

    /**
     * Package access count
     *
     * @var string
     */
    protected $purchase_date;

    /**
     * Package access count
     *
     * @var string
     */
    protected $status;

    /**
     * Constructor
     */
    public function __construct($id = 0) {
        if ($id) {
            $this->id = $id;
            $this->setup();
        }

        return $this;
    }

    public function exists() {
        return $this->id && $this->id > 0;
    }

    protected function setup() {
        global $wpdb;

        $data = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}dwrestricted_subscriptions WHERE ID = %d
            ",
            $this->id)
        );

        if ($data) {
            $data = $data[0];
            $this->user_id = $data->user_id;
            $this->package_id = $data->package_id;
            $this->invoice_id = $data->invoice_id;
            $this->purchase_date = $data->purchase_date;
            $this->balance = $data->balance;
            $this->status = $data->status;
            $this->invoice_id = $data->invoice_id;
        }
    }

    public function update_db_col($columns, $formats) {
        global $wpdb;

        $check = $wpdb->update(
            $wpdb->prefix . 'dwrestricted_subscriptions',
            $columns,
            ['ID' => $this->id],
            $formats,
            ['%d']
        );

        return $check;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_field($key) {
        return ! empty($this->$key) ? $this->$key : '';
    }

    protected function set_field($key, $value) {
        $this->$key = $value;
    }

    public function set_user($value) {
        $value = absint($value);
        $this->set_field('user_id', $value);
        return $this;
    }

    public function set_invoice($value) {
        $value = absint($value);
        $this->set_field('invoice_id', $value);
        return $this;
    }

    public function set_package($value) {
        $value = absint($value);
        $this->set_field('package_id', $value);
        return $this;
    }

    public function set_purchase_date($value) {
        $this->set_field('purchase_date', $value);
        return $this;
    }

    public function set_balance($value) {
        $this->set_field('balance', $value);
        return $this;
    }

    public function increase_balance($value = 1) {
        $balance = $this->get_balance();
        $balance += $value;

        $this->set_field('balance', $balance);

        $this->update_db_col([
            'balance' => $balance
        ], ['%d']);

        return $this;
    }

    public function spend_balance($amount = 1, $post_id = 0) {
        $balance  = $this->get_balance();
        if ($balance === 0) return;

        $balance -= $amount;

        $this->set_balance($balance);

        if ($this->get_balance() <= 0) {
            $this->set_balance(0);
            $this->set_status('inactive');
            $balance = 0;

            $this->update_db_col([
                'status' => 'inactive'
            ], ['%s']);
        }

        $this->update_db_col([
            'balance' => $balance
        ], ['%d']);

        if ($post_id) {
            $spent_credit = new Spent_Credit();
            $spent_credit->set_post_id($post_id)->set_user_id($this->get_user_id())->set_spent_credit($amount)->set_subscription_id($this->get_id())->set_date(current_time('Y-m-d H:i:s'))->save();
        }

        return $this;
    }

    public function set_balance_from_package($value) {
        $this->set_field('balance', $value);
        return $this;
    }

    public function set_status($value) {
        $this->set_field('status', $value);
        return $this;
    }

    public function get_title() {
        return $this->get_field('title');
    }

    public function get_user_id() {
        return $this->get_field('user_id');
    }

    public function get_user() {
        return get_user_by('ID', $this->get_user_id());
    }

    public function get_package_id() {
        return $this->get_field('package_id');
    }

    public function get_package() {
        return new Package($this->get_package_id());
    }

    public function get_balance() {
        return intval($this->get_field('balance'));
    }

    public function get_status() {
        return $this->get_field('status');
    }

    public function get_status_html() {
        switch ($this->get_status()) {
            case 'inactive':
                return __('<strong class="subscription-status red">Inactive</strong>', 'dwrestricted');

            case 'active':
                return __('<strong class="subscription-status green">Active</strong>', 'dwrestricted');

            default:
                return sprintf(__('<strong class="subscription-status">%s</strong>', 'dwrestricted'), $this->get_status());
        }
    }

    public function get_invoice_id() {
        return $this->get_field('invoice_id');
    }

    public function get_invoice() {
        return $this->get_invoice_id() ? wc_get_order($this->get_invoice_id()) : false;
    }

    public function is_paid() {
        return $this->get_invoice() && $this->get_invoice()->is_paid();
    }

    public function get_purchase_date($format = '') {
        return $this->get_field('purchase_date');
    }

    public function generate_invoice() {
        $order = wc_create_order([
            'customer_id'   => $this->get_user_id(),
            'customer_note' => __('Invoice Created for subscription', 'dwrestricted'),
            'created_via'   => __('Auto generated subscription invoice', 'dwrestricted'),
        ]);

        $this->set_invoice($order->get_id());

        update_post_meta($order->get_id(), '_subscription_id', $this->get_id());

        $item_id = wc_add_order_item($order->get_id(), [
            'order_item_name' =>   __('Subscription', 'dwrestricted'),
            'order_item_type'    =>   'fee'
        ]);

        if ($item_id) {
            wc_add_order_item_meta($item_id, '_line_total', $this->get_package()->get_price());
            wc_add_order_item_meta($item_id, '_line_tax', 0);
            wc_add_order_item_meta($item_id, '_line_subtotal', $this->get_package()->get_price());
            wc_add_order_item_meta($item_id, '_line_subtotal_tax', 0);
            wc_add_order_item_meta($item_id, '_tax_class', 'zero-rate');
        }

        $order->set_address(dwrestricted_get_customr_address_formatted($this->get_user_id()));
        $order->calculate_totals();
        $order->save();
    }

    public function save() {
        global $wpdb;

        // Update Existing
        if ($this->id) {
            $check = $wpdb->update(
                $wpdb->prefix . 'dwrestricted_subscriptions',
                [
                    'user_id'       => $this->get_user_id(),
                    'balance'       => $this->get_balance(),
                    'package_id'    => $this->get_package_id(),
                    'invoice_id'    => $this->get_invoice_id(),
                    'purchase_date' => $this->get_purchase_date(),
                    'status'        => $this->get_status(),
                ],
                ['ID' => $this->id],
                [
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                ],
                ['%d']
            );

        } else {
            // Create new
            $check = $wpdb->insert(
                $wpdb->prefix . 'dwrestricted_subscriptions',
                [
                    'user_id'       => $this->get_user_id(),
                    'balance'       => $this->get_balance(),
                    'package_id'    => $this->get_package_id(),
                    'invoice_id'    => $this->get_invoice_id(),
                    'purchase_date' => $this->get_purchase_date(),
                    'status'        => $this->get_status(),
                ],
                [
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                ]
            );
        }

        $check = (bool) $check;

        if ($check) {
            $this->id = $wpdb->insert_id;
            $this->setup();
        }

        return (bool) $check;
    }

    public function remove() {
        if (! $this->id) return false;

        return $wpdb->delete([
            $wpdb->prefix . 'dwrestricted_subscriptions',
            ['ID' => $this->id],
            ['%d']
        ]);
    }
}
