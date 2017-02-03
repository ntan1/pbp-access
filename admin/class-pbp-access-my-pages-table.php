<?php

/**
 * The file that defines the My Pages Table class, used for users that have access to a page
 *
 *
 * @link       fes.yorku.ca
 * @since      1.0.0
 *
 * @package    Pbp_Access
 * @subpackage Pbp_Access/admin
 */
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Pbp_Access_My_Pages_Table extends WP_List_Table {

    /**
     * This method is an extra step in order to filter the results based on
     * who has access via the pbp_access_users or pbp_access_roles tables.
     *
     * @global WPDB $wpdb
     * @return array Associative array containing all the formatted data
     * according to who has access.
     */
    public function get_records() {
        global $wpdb;

        // Each table has a search. Check if the search arguments is set.
        $search = (isset($_REQUEST['s'])) ? $_REQUEST['s'] : false;
        // If it is, generate the search filter query, and append it to the query.

        $do_search = ($search) ? $wpdb->prepare(" AND post_title LIKE '%%%s%%'", $search) : '';
        $query = "
            SELECT $wpdb->posts.*
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_type = 'page'
            AND $wpdb->posts.post_status IN ('publish', 'draft')
            $do_search
         ";

        // Get the data
        $db_data = $wpdb->get_results($query, ARRAY_A);

        // Filter the data according to who has access
        if (!empty($db_data)) {
            foreach ($db_data as $key => $rec) {
                $user = wp_get_current_user();
                $has_access_user = $wpdb->get_results('SELECT page_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $user->ID . ' AND ' . 'page_id=' . $rec['ID']);
                $has_access_role = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . "pbp_access_roles WHERE page_id=" . $rec['ID']);
                $has_role = false;
                foreach ($has_access_role as $role) {
                    if (in_array($role->role_id, (array) $user->roles)) {
                        $has_role = true;
                    }
                }

                if ((empty($has_access_user)) && (!$has_role)) {
                    unset($db_data[$key]);
                }
            }
        }

        return $db_data;
    }

    /**
     * Seting up the constructor that references the parent constructor. We use
     * this to set up default configs.
     *
     * @global type $status
     * @global type $page
     */
    public function __construct() {
        global $status, $page;
        //Set parent defaults
        parent::__construct(array(
          'singular' => 'access', //singular name of the listed records
          'plural' => 'access', //plural name of the listed records
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
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'post_date':
            case 'post_modified':
                return $item[$column_name];
            case 'post_status':
                // Determine Status and color code the Text
                if (($item['post_status']) == 'publish') {
                    return '<span style="color:#0B9400;">Published</span><br/>' . $item['post_date'];
                }
                elseif (($item['post_status']) == 'draft') {
                    return '<span style="color:#EA6300;">Draft</span><br/>' . $item['post_date'];
                }
                elseif (($item['post_status']) == 'private') {
                    return 'Private<br/>' . $item['post_date'];
                }
                elseif (($item['post_status']) == 'pending') {
                    return '<span style="color:#EA6300;">Pending Review<br/>' . $item['post_date'];
                }
                else {
                    return '';
                }
            case 'post_author':
                return get_user_by('id', $item['post_author'])->display_name;
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * This function defines the output for the title column. Actions for Edit
     * and View functionality of each individual row are added.
     *
     * @param type $item A full row's worth of data
     * @return HTML that will be placed in the title column
     */
    public function column_title($item) {

        //Build row actions. These are the actions that appear when you hover over the row
        $actions = array(
          'edit' => '<a href="' . site_url() . '/wp-admin/post.php?post=' . (int) $item['ID'] . '&action=edit">Edit</a>',
          'view' => '<a href="' . $item['guid'] . '">View</a>'
        );

        //Return the title contents
        return sprintf('<a href="%4$s"><strong>%1$s</strong></a> %3$s',
            /* $1%s */ $item['post_title'],
            /* $2%s */ $item['ID'],
            /* $3%s */ $this->row_actions($actions),
            /* $4%s */ site_url() . '/wp-admin/post.php?post=' . (int) $item['ID'] . '&action=edit'
        );
    }

    /**
     * This method defines the table's columns and titles.
     *
     * @return array An Associative array containing column information such as title, slug, etc.
     */
    public function get_columns() {
        $columns = array(
          'title' => 'Title',
          'post_status' => 'Status',
          'post_modified' => 'Last Modified',
          'post_author' => 'Author'
        );
        return $columns;
    }

    /**
     * Defines Sortable columns.
     *
     * @return array An Associaive array of columns that should be sortable
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
          'title' => array('post_title', true), // True means it's already sorted
          'post_modified' => array('post_modified', false)
        );
        return $sortable_columns;
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
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    public function prepare_items() {

        // Set the rows to be displayed per page
        $per_page = 20;

        // Get the comlumns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Get the data
        $data = $this->get_records();

        /**
         * Order the data. This method uses a few extra checks for arguments to make sure it does not interfere
         * with other tables on the page.
         */
        function usort_reorder_settings($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'post_title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder_settings');

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
