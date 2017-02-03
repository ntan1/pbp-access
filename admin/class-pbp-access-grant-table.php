<?php
/**
 * The file that defines the Bulk Grant Access Table under the settings page class
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
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Pbp_Access_Grant_Table extends WP_List_Table {

    /**
     * This method adds an entry into the pbp_access_users table to grant access
     * to a user.
     * 
     * @global WPDB $wpdb
     * @param int $id The id of the page
     * @param int $user The id of the user
     */
    public static function grant_user_access($id, $user) {
        global $wpdb;
        $exists = $wpdb->get_results('SELECT user_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $user . ' AND ' . ' page_id=' . $id);
        if (empty($exists)) {
            $table_name = $wpdb->prefix . "pbp_access_users";
            $wpdb->insert($table_name, array(
              'time' => time(),
              'user_id' => $user,
              'page_id' => $id,
              'admin_id' => get_current_user_id(),
                ), array(
              '%d',
              '%d',
              '%d',
              '%d',
                )
            );
        }
    }

    /**
     * This method adds an entry into the pbp_access_roles table to grant access
     * to a role.
     * 
     * @global WPDB $wpdb
     * @param int $id The id of the page
     * @param int $role The id of the role
     */
    public static function grant_role_access($id, $role) {
        global $wpdb;
        $exists = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . 'pbp_access_roles WHERE role_id="' . $role . '" AND ' . ' page_id=' . $id);
        if (empty($exists)) {
            $table_name = $wpdb->prefix . "pbp_access_roles";
            $wpdb->insert($table_name, array(
              'time' => time(),
              'role_id' => $role,
              'page_id' => $id,
              'admin_id' => get_current_user_id(),
                ), array(
              '%d',
              '%s',
              '%d',
              '%d',
                )
            );
        }
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

        parent::__construct(array(
          'singular' => 'access_page', //singular name of the listed records
          'plural' => 'access_pages', //plural name of the listed records
          'ajax' => false        //does this table support ajax?
        ));
    }

    /**
     * Add extra markup in the toolbars before or after the list. These two dropdown
     * menus are used to select either the role or user to be granted access.
     * jQuery is used to ensure that only one is selected at any given time.
     * 
     * @param string $which helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav($which) {
        if ($which == "top") {
            $user_args = array(
              'role__not_in' => array('administrator'),
            );
            $users = get_users($user_args);
            ?>
            <div style="float: left; margin: 3px 0 10px 0;">
                <select name="select-users" id="select-users">
                    <option value="0">-- Select a User --</option>
                    <?php foreach ($users as $user) { ?>
                        <option value="<?php echo $user->ID; ?>" >
                            <?php
                            echo $user->user_login;
                            echo ($user->user_firstname != '' || $user->user_lasttname != '' ? ' (' . $user->user_firstname . ' ' . $user->user_lastname . ')' : '');
                            ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <span style="float: left; margin: 8px 0 10px 10px;"> OR </span>
            <div style="float: left; margin: 3px 0 10px 10px;">
                <select name="select-roles" id="select-roles">
                    <option value="0">-- Select a Role --</option>
                    <?php foreach (get_editable_roles() as $role_name => $name) { ?>
                        <?php if (($role_name !== 'administrator') && ($role_name !== 'editor')) { ?>
                            <option value="<?php echo str_replace(' ', '_', $role_name); ?>" >
                                <?php
                                echo ucwords($role_name);
                                ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
            <?php
        }
    }

    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'post_author':
                return get_user_by('id', $item['post_author'])->user_login;
            case 'post_date':
                if ($item['post_status'] === 'publish') {
                    return 'Published <br/>' . date("F j, Y", strtotime($item[$column_name]));
                }
                elseif ($item['post_status'] === 'draft') {
                    return 'Draft <br/>' . date("F j, Y", strtotime($item[$column_name]));
                }
                else {
                    return date("F j, Y", strtotime($item[$column_name]));
                }
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item) {
// create a nonce
        $add_nonce = wp_create_nonce('pbp_access_add_access');
//Build row actions
        $actions = array(
          'edit' => sprintf('<a href="%s/wp-admin/post.php?post=%s&action=%s">Edit Page</a>', get_site_url(), absint($item['ID']), 'edit'),
        );
//Return the title contents
        return sprintf('<strong><a href="%2$s">%1$s</a></strong>%3$s',
            /* %1$s */ $item['post_title'],
            /* %2$s */ $item['guid'],
            /* %3$s */ $this->row_actions($actions)
        );
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
            /* $2%s */ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns() {
        $columns = array(
          'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
          'title' => 'Title',
          'post_author' => 'Author',
          'post_date' => 'Date'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
          'title' => array('post_title', true), //true means it's already sorted
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
          'bulk-grant-access' => 'Give Access'
        );
        return $actions;
    }

    function process_bulk_action() {
        // If the access bulk action is triggered
        if (( isset($_POST['action']) && $_POST['action'] == 'bulk-grant-access' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-grant-access' )
        ) {
            $ids = isset($_REQUEST[$this->_args['singular']]) ? $_REQUEST[$this->_args['singular']] : array();
            if ($_REQUEST['select-users'] != 0) {
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        self::grant_user_access($id, $_REQUEST['select-users']);
                    }
                }
            }

            if ($_REQUEST['select-roles'] != '0') {
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        self::grant_role_access($id, $_REQUEST['select-roles']);
                    }
                }
            }
        }
    }

    public function no_items() {
        _e('No access entries have been found.');
    }

    function prepare_items() {

        global $wpdb;

        $per_page = 10;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $search = ( isset($_REQUEST['s']) ) ? $_REQUEST['s'] : false;

        $do_search = ( $search ) ? $wpdb->prepare(" AND post_title LIKE '%%%s%%'", $search) : '';

        $query = "
            SELECT * 
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_status IN ('publish', 'draft') 
            AND $wpdb->posts.post_type = 'page'
            $do_search   
            ";
        $data = $wpdb->get_results($query, ARRAY_A);

        function usort_reorder_pages($a, $b) {
            $orderby = (!empty($_REQUEST['orderby']) && $_REQUEST['orderby'] != 'user_id' && $_REQUEST['orderby'] != 'role_id' && $_REQUEST['orderby'] != 'page_id') ? $_REQUEST['orderby'] : 'post_title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder_pages');
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data;
        $this->set_pagination_args(array(
          'total_items' => $total_items, //WE have to calculate the total number of items
          'per_page' => $per_page, //WE have to determine how many items to show on a page
          'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

}
