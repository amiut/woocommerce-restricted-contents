<?php
/**
 * Spent credit package Class
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS;

class Spent_Credit
{
    /**
     * package id
     *
     * @var int
     */
    public $id = 0;

    /**
     * post id
     *
     * @var int
     */
    public $post_id = 0;

    /**
     * user id
     *
     * @var int
     */
    public $user_id = 0;

    /**
     * subscription id
     *
     * @var int
     */
    public $subscription_id = 0;

    /**
     * Spent credits
     *
     * @var int
     */
    public $spent_credits = 0;

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
                SELECT * FROM {$wpdb->prefix}dwrestricted_used_credits WHERE ID = %d
            ",
            $this->id)
        );

        if ($data) {
            $data = $data[0];
            $this->id = $data->ID;
            $this->user_id = $data->user_id;
            $this->subscription_id = $data->subscription_id;
            $this->post_id = $data->post_id;
            $this->spent_credit = $data->spent_credit;
            $this->date_created = $data->date_created;
        }
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

    public function set_user_id($value) {
        $this->set_field('user_id', $value);
        return $this;
    }

    public function set_subscription_id($value) {
        $this->set_field('subscription_id', $value);
        return $this;
    }

    public function set_post_id($value) {
        $this->set_field('post_id', $value);
        return $this;
    }

    public function set_spent_credit($value) {
        $this->set_field('spent_credit', $value);
        return $this;
    }

    public function set_date($value) {
        $this->set_field('date_created', $value);
        return $this;
    }

    public function get_user_id() {
        return absint($this->get_field('user_id'));
    }

    public function get_post_id() {
        return absint($this->get_field('post_id'));
    }

    public function get_subscription_id() {
        return absint($this->get_field('subscription_id'));
    }

    public function get_date($format = '') {
        return $this->get_field('date_created');
    }

    public function get_spent_credit() {
        return absint($this->get_field('spent_credit'));
    }

    public function find_user_post($user_id, $post_id) {
        global $wpdb;

        $data = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}dwrestricted_used_credits WHERE user_id = %d AND post_id = %d
            ",
            $user_id,
            $post_id)
        );

        if ($data) {
            $data = $data[0];
            $this->id = $data->ID;
            $this->user_id = $data->user_id;
            $this->post_id = $data->post_id;
            $this->subscription_id = $data->subscription_id;
            $this->spent_credit = $data->spent_credit;
            $this->date_created = $data->date_created;
        }

        return $this;
    }

    public function save() {
        global $wpdb;

        // Update Existing
        if ($this->id) {
            $check = $wpdb->update(
                $wpdb->prefix . 'dwrestricted_used_credits',
                [
                    'post_id'         => $this->get_post_id(),
                    'user_id'         => $this->get_user_id(),
                    'subscription_id' => $this->get_subscription_id(),
                    'spent_credit'    => $this->get_spent_credit(),
                    'date_created'    => $this->get_date(),
                ],
                ['ID' => $this->id],
                [
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                ],
                ['%d']
            );

        } else {
            // Create new
            $check = $wpdb->insert(
                $wpdb->prefix . 'dwrestricted_used_credits',
                [
                    'post_id'         => $this->get_post_id(),
                    'user_id'         => $this->get_user_id(),
                    'subscription_id' => $this->get_subscription_id(),
                    'spent_credit'    => $this->get_spent_credit(),
                    'date_created'    => $this->get_date(),
                ],
                [
                    '%d',
                    '%d',
                    '%d',
                    '%d',
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
            $wpdb->prefix . 'dwrestricted_used_credits',
            ['ID' => $this->id],
            ['%d']
        ]);
    }
}
