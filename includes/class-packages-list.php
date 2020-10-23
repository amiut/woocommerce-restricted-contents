<?php
/**
 * Book info list
 *
 * @uses WP_List_Table
 */
namespace DW_RESTRICTED_CONTENTS;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Packages_List extends \WP_List_Table
{
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Newsletter', 'dwrestricted' ),
			'plural'   => __( 'Newsletter', 'dwrestricted' ),
			'ajax'     => false
        ] );

        $this->process_bulk_action();
    }


	/**
	 * Retrieve info data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_info( $per_page = 15, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}dwrestricted_packages";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Delete a packages record.
	 *
	 * @param int $id packages ID
	 */
	public static function delete_info( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}dwrestricted_packages",
			[ 'ID' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}dwrestricted_packages";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no packages data is available */
	public function no_items() {
		_e( 'No info avaliable.', 'dwrestricted' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
			case 'post_id':
			case 'isbn':
				return $item[ $column_name ];
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_title( $item ) {
		$delete_nonce = wp_create_nonce( 'sp_delete_info' );

		$title = '<strong>' . $item['title'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&info=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
    }

    function column_price($item) {
        echo wc_price($item['price']);
	}

    function column_access_count($item) {
        echo $item['access_count'];
    }


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'title'    => __( 'Title', 'dwrestricted' ),
			'access_count' => __( 'Access count', 'dwrestricted' ),
			'price' => __( 'Price', 'dwrestricted' ),
		];

		return $columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */

		$per_page     = $this->get_items_per_page( 'info_per_page', 15 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_info( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_info' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_info( absint( $_GET['info'] ) );

                wp_redirect( $_SERVER['HTTP_REFERER'] );
				exit;
			}

        }

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_info( $id );

            }

            wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;
		}
	}
}
