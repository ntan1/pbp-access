<?php

/**
 * The file that defines the User Access Table under the settings page class
 *
 *
 * @link       fes.yorku.ca
 * @since      1.0.0
 *
 * @package    Pbp_Access
 * @subpackage Pbp_Access/admin
 */
//This class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Pbp_Access_Users_Table extends WP_List_Table {

    /**
     * Delete a user access record from the database.
     *
     * @param $id Id of the access entry in the pbp_access_users table
     */
    public static function delete_user_access($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "pbp_access_users";
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name
                WHERE id = %d", $id
            )
        );
    }

    /**
     * Seting up the constructor that references the parent constructor. We use
     * this to set up default configs.
     * 
     * @global type $status
     * @global type $page
     */
    function __construct() {
        global $status, $page;
        //Set parent defaults
        parent::__construct(array(
          'singular' => 'access_user', //singular name of the listed records
          'plural' => 'access_users', //plural name of the listed records
          'ajax' => false        //does this table support ajax?
        ));
    }

    /**
     * This method defines the output of each column, except the title column.
     * This is defined later on in column_title(), since it will have a special output.
     * 
     * The column names correspond to the column titles given in the database.
     * 
     * @param type $item A full row's worth of data
     * @param type $column_name The name or slug of the column to be processed
     * @return string HTML or text that will be placed between <td></td> tags of the table
     */
    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'time':
                // Format the date
                return date("F j, Y", $item['time']);
            case 'post_modified':
                // Return the date the post was modified and format it
                return date("F j, Y", strtotime($item['post_modified']));
            case 'admin_id':
                // Pull information about the Admin from the ID
                return get_user_by('id', $item['admin_id'])->user_login;
            case 'page_id':
                // Get page details
                return '<a href="' . get_post($item['page_id'])->guid . '">' . get_post($item['page_id'])->post_title . '</a>';
            default:
                //Show the whole array for troubleshooting purposes
                return print_r($item, true);
        }
    }

    /**
     * This function defines the output for the title column. Actions for Delete
     * and Edit functionality of each individual row are added.
     * 
     * @param type $item A full row's worth of data
     * @return HTML that will be placed in the title column
     */
    function column_title($item) {
        // create a nonce, a WordPress security feature to verify where the request is coming from
        $delete_nonce = wp_create_nonce('pbp_access_delete_access');

        //Build row actions. These are the actions that appear when you hover over the row
        $actions = array(
          'delete' => sprintf('<a href="?page=%s&action=%s&access_user=%s&_wpnonce=%s">Delete Access</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce),
          'edit' => sprintf('<a href="%s/wp-admin/post.php?post=%s&action=%s">Edit Page</a>', get_site_url(), absint($item['page_id']), 'edit'),
        );
        //Return the title contents
        return sprintf('<strong>%1$s (%4$s) %2$s %3$s</strong> %5$s',
            /* %1$s */ $item['user_login'],
            /* %2$s */ get_user_by('id', $item['user_id'])->user_firstname,
            /* %3$s */ get_user_by('id', $item['user_id'])->user_lastname,
            /* %4$s */ $item['user_email'],
            /* %5$s */ $this->row_actions($actions)
        );
    }

    /**
     * This function is used for the bulk actions feature
     * 
     * @param type $item  A full row's worth of data
     * @return string HTML tags to be placed in each row that generate 
     * checkboxes with the corresponsding values of the key indentifier in the row
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label 
            /* $2%s */ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    /**
     * This method defines the table's columns and titles. It also generates 
     * a checkbox to be used with bulk actions.
     * 
     * @return array An Associative array containing column information such as title, slug, etc.
     */
    function get_columns() {
        $columns = array(
          'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
          'title' => 'User',
          'page_id' => 'Page',
          'post_modified' => 'Page Last Modified',
          'time' => 'Granted Access On',
          'admin_id' => 'Granted By'
        );
        return $columns;
    }

    /**
     * Defines Sortable columns.
     * 
     * @return array An Associaive array of columns that should be sortable
     */
    function get_sortable_columns() {
        $sortable_columns = array(
          'title' => array('user_id', true), //true means it's already sorted
          'page_id' => array('page_id', false)
        );
        return $sortable_columns;
    }

    /**
     * This method defines the bulk actions. It deteremined the slug
     * as well as the text that will show up in the Drop down Bulk actions
     * section of the table. 
     * 
     * @return array Array that defines the Bulk Action identifier slug
     */
    function get_bulk_actions() {
        $actions = array(
          'bulk-delete' => 'Delete Access'
        );
        return $actions;
    }

    /**
     * This method handles the Bulk actions when they are triggered.
     */
    function process_bulk_action() {

        /**
         * Since there will be two tables on the same page that use the delete actions
         * we need to verify which arguments are present in the $_REQUEST. 
         */
        if (('delete' === $this->current_action()) && (isset($_GET['access_user']))) {

            // Verify the nonce we set in the delete_user_access() method.
            $nonce = esc_attr($_GET['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'pbp_access_delete_access')) {
                die("Oops, that's not right!");
            }

            // Call the delete method, and pass the singular argument we set in the delete actions link.
            self::delete_user_access(esc_attr($_GET[$this->_args['singular']]));
        }

        // If the delete bulk action is triggered, verify which bulk action it is.
        if (( isset($_POST['action']) && $_POST['action'] == 'bulk-delete' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete' )
        ) {

            // Get the it's from the $_REQUEST. If they are an array, call the delete method for each.
            $ids = isset($_REQUEST[$this->_args['singular']]) ? $_REQUEST[$this->_args['singular']] : array();
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    self::delete_user_access($id);
                }
            }
        }
    }

    /**
     * Method that defines the text when no items are available in the table.
     */
    public function no_items() {
        _e('No access entries have been found.');
    }

    /**
     * This is where we prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed.
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    function prepare_items() {

        global $wpdb;

        // Set the rows to be displayed per page
        $per_page = 20;

        // Get the comlumns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Process the Bulk Actions
        $this->process_bulk_action();

        // Each table has a search. Check if the search arguments is set.
        $search = ( isset($_REQUEST['s']) ) ? $_REQUEST['s'] : false;

        // If it is, generate the search filter query, and append it to the query.
        $do_search = ( $search ) ? $wpdb->prepare(" AND user_login LIKE '%%%s%%' OR post_title LIKE '%%%s%%'", $search, $search) : '';
        $query = "
            SELECT {$wpdb->prefix}pbp_access_users.*, $wpdb->posts.post_modified, $wpdb->posts.post_title, $wpdb->users.user_login, $wpdb->users.user_email, $wpdb->users.display_name
            FROM {$wpdb->prefix}pbp_access_users
            INNER JOIN $wpdb->users
                ON {$wpdb->prefix}pbp_access_users.user_id = $wpdb->users.ID
            INNER JOIN $wpdb->posts
                ON {$wpdb->prefix}pbp_access_users.page_id = $wpdb->posts.ID
            WHERE $wpdb->posts.post_status IN ('publish', 'draft') 
            AND $wpdb->posts.post_type = 'page'
            $do_search   
            AND $wpdb->posts.post_status NOT IN ('trash') 
         ";

        // Get the data
        $data = $wpdb->get_results($query, ARRAY_A);

        /**
         * Order the data. This method uses a few extra checks for arguments to make sure it does not interfere
         * with other tables on the page. 
         */
        function usort_reorder_users($a, $b) {
            $orderby = (!empty($_REQUEST['orderby']) && $_REQUEST['orderby'] != 'role_id') ? $_REQUEST['orderby'] : 'user_id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder_users');

        // Get the current page
        $current_page = $this->get_pagenum();

        // Determine the total number of rows
        $total_items = count($data);

        // Paginate the data
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        // Load the data
        $this->items = $data;

        // Set the Pagination parameters
        $this->set_pagination_args(array(
          'total_items' => $total_items, // We have to calculate the total number of items
          'per_page' => $per_page, // We have to determine how many items to show on a page
          'total_pages' => ceil($total_items / $per_page)   // We have to calculate the total number of pages
        ));
    }

}
