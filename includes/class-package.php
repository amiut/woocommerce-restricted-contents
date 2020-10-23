<?php
/**
 * Restriction Package Class
 *
 * @package DW_RESTRICTED_CONTENTS
 * @since   1.0
 */

namespace DW_RESTRICTED_CONTENTS;

class Package
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
    protected $price;

    /**
     * Package access count
     *
     * @var string
     */
    protected $access_count;

    /**
     * Instance of $wpdb
     */
    protected $db;

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
                SELECT * FROM {$wpdb->prefix}dwrestricted_packages WHERE ID = %d
            ",
            $this->id)
        );

        if ($data) {
            $data = $data[0];
            $this->id = $data->ID;
            $this->title = $data->title;
            $this->price = $data->price;
            $this->access_count = $data->access_count;
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

    public function set_title($value) {
        $this->set_field('title', $value);
        return $this;
    }

    public function set_price($value) {
        $this->set_field('price', $value);
        return $this;
    }

    public function set_access_count($value) {
        $this->set_field('access_count', $value);
        return $this;
    }

    public function get_title() {
        return $this->get_field('title');
    }

    public function get_price() {
        return $this->get_field('price');
    }

    public function get_access_count() {
        return $this->get_field('access_count');
    }

    public function save() {
        global $wpdb;

        // Update Existing
        if ($this->id) {
            $check = $wpdb->update(
                $wpdb->prefix . 'dwrestricted_packages',
                [
                    'title'         => $this->get_title(),
                    'price'         => $this->get_price(),
                    'access_count'  => $this->get_access_count(),
                ],
                ['ID' => $this->id],
                [
                    '%s',
                    '%s',
                    '%s',
                ],
                ['%d']
            );

        } else {
            // Create new
            $check = $wpdb->insert(
                $wpdb->prefix . 'dwrestricted_packages',
                [
                    'title'         => $this->get_title(),
                    'price'         => $this->get_price(),
                    'access_count'  => $this->get_access_count(),
                ],
                [
                    '%s',
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
            $wpdb->prefix . 'dwrestricted_packages',
            ['ID' => $this->id],
            ['%d']
        ]);
    }
}
