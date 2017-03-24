<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       fes.yorku.ca
 * @since      1.0.0
 *
 * @package    Pbp_Access
 * @subpackage Pbp_Access/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pbp_Access
 * @subpackage Pbp_Access/admin
 * @author     Calin Armenean <calin13@yorku.ca>
 */
class Pbp_Access_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pbp_Access_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pbp_Access_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        // wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pbp-access-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name, plugins_url() . '/pbp-access-master/css/pbp-access-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pbp_Access_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pbp_Access_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pbp-access-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * This hook add's styling to the settings page
     *
     * @since    1.0.0
     */
    public function admin_header() {
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        if ('pbp-access-settings' != $page) {
            return;
        }

        echo '<style type="text/css">';
        echo '.wp-list-table .column-page_id { width: 20%; }';
        echo '.wp-list-table .column-admin_id { width: 10%; }';
        echo '.wp-list-table .column-post_modified { width: 10%; }';
        echo '.wp-list-table .column-time { width: 10%; }';
        echo '.wp-list-table .column-post_author { width: 10%; }';
        echo '.wp-list-table .column-post_date { width: 10%; }';
        echo '</style>';
    }

    /**
     * This hook creates the settings page, as well as the My Pages page, if
     * the current user is not an admin.
     *
     * @since 1.0.0
     * @global type $wpdb
     */
    public function pbp_access_pages() {

        // Setting page for Admins
        function pbp_access_settings() {
            $wp_pbp_access_users_table = new Pbp_Access_Users_Table();
            $wp_pbp_access_roles_table = new Pbp_Access_Roles_Table();
            $wp_pbp_access_grant_table = new Pbp_Access_Grant_Table();
            $wp_pbp_access_users_table->prepare_items();
            $wp_pbp_access_roles_table->prepare_items();
            $wp_pbp_access_grant_table->prepare_items();
            $message = '';
            if (('delete' === $wp_pbp_access_users_table->current_action()) && (isset($_REQUEST['access_user']))) {
                $message = '<div class="updated below-h2" id="message"><p>User Access Deleted</p></div>';
            }

            if (('delete' === $wp_pbp_access_roles_table->current_action()) && (isset($_REQUEST['access_role']))) {
                $message = '<div class="updated below-h2" id="message"><p>Role Access Deleted</p></div>';
                $_GET['tab'] = 'second';
            }

            if ('bulk-delete' === $wp_pbp_access_users_table->current_action()) {
                $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('User(s) Access deleted: %d', 'pbp_access'), count($_REQUEST['access_user'])) . '</p></div>';
            }

            if ('bulk-delete-roles' === $wp_pbp_access_roles_table->current_action()) {
                $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Role(s) Access deleted: %d', 'pbp_access'), count($_REQUEST['access_role'])) . '</p></div>';
                $_GET['tab'] = 'second';
            }

            if ('bulk-grant-access' === $wp_pbp_access_grant_table->current_action()) {
                if ('bulk-grant-access' === $wp_pbp_access_grant_table->current_action()) {
                    if (($_REQUEST['select-users'] == 0) && ($_REQUEST['select-roles'] == '0')) {
                        $message = '<div class="error below-h2" id="message"><p>Please Select a User or a Role</p></div>';
                    }
                    elseif ($_REQUEST['select-users'] != 0) {
                        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Access granted to <strong>%s</strong> for %d page(s)', 'pbp_access'), get_user_by('id', $_REQUEST['select-users'])->user_login, count($_REQUEST['access_page'])) . '</p></div>';
                    }
                    elseif ($_REQUEST['select-roles'] != '0') {
                        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Access granted to <strong>%s</strong> for %d page(s)', 'pbp_access'), ucwords(str_replace('_', ' ', $_REQUEST['select-roles'])), count($_REQUEST['access_page'])) . '</p></div>';
                    }
                }
            }
            ?>
            
                <h2>Page by Page Access</h2>

                <?php echo '<br/>' . $message; ?>
                <?php
                $tab = (!empty($_GET['tab'])) ? esc_attr($_GET['tab']) : 'first';
                page_tabs($tab);

                if ($tab == 'first') {
                    ?>
                    <br/>
                    <em>This section displays all of the access entries for individual users.</em>
                    <form id="table-search" method="get">
                        <?php
                        $wp_pbp_access_users_table->search_box(__('Search'), 'pbp-user-access');
                        foreach ($_GET as $key => $value) { // http://stackoverflow.com/a/8763624/1287812
                            if ('s' !== $key) {// don't include the search query
                                echo("<input type='hidden' name='$key' value='$value' />");
                            }
                        }
                        ?>
                    </form>
                    <form id="users-settings" method="POST">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <!-- Now we can render the completed list table -->
                        <?php $wp_pbp_access_users_table->display(); ?>
                    </form>

                    <?php
                }
                elseif ($tab == 'second') {
                    ?>
                    <br/>
                    <em>This section displays all of the access entries for individual roles.</em>
                    <form id="table-search" method="get">
                        <?php
                        $wp_pbp_access_users_table->search_box(__('Search'), 'pbp-role-access');
                        foreach ($_GET as $key => $value) { // http://stackoverflow.com/a/8763624/1287812
                            if ('s' !== $key) {// don't include the search query
                                echo("<input type='hidden' name='$key' value='$value' />");
                            }
                        }
                        ?>
                    </form>
                    <form id="roles-settings" method="POST">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <!-- Now we can render the completed list table -->
                        <?php $wp_pbp_access_roles_table->display(); ?>
                    </form>
                    <?php
                }
                elseif ($tab == 'third') {
                    ?>
                    <br/>
                    <em>This section allows you to add access in bulk to a single user or role.</em>
                    <form id="page-search" method="get">
                        <?php
                        $wp_pbp_access_grant_table->search_box(__('Search'), 'pbp-user-access');
                        foreach ($_GET as $key => $value) { // http://stackoverflow.com/a/8763624/1287812
                            if ('s' !== $key) {// don't include the search query
                                echo("<input type='hidden' name='$key' value='$value' />");
                            }
                        }
                        ?>
                    </form>
                    <form id="grant-settings" method="POST">
                        <!-- Now we can render the completed list table -->
                        <?php $wp_pbp_access_grant_table->display(); ?>
                    </form>
                <?php 
                } else {
                    /**
                     * code below for the help tab was added by Nick Tan on March 22, 2017
                     */
                    ?>
                    <br />
                    <em>This section provides instructions on how to grant and delete access through a page or through the bulk actions feature.</em>
                    <h3>Page functions</h3>
                    To give access to a single page:<br/>
                    <ol>    
                        <li>Navigate to that page via the sidebar.</li>
                        <li>Once on the page, look for the Page Access box which contains two sub-boxes: Users and Roles. (see image below)</li>
                        <li>Check the checkboxes beside the user or role you want to give page access to.<br/>
                        Checking a box in the User section will grant access to only those people, whereas checking a box under Roles will give access to everyone who has that role.
                        <br/>Additionally, Unchecking a box will delete access.</li>
                        <li>Once you've checked/unchecked the boxes, click the blue update button which is above the Page Access box to apply those changes.</li>
                    </ol>
                    <img src="<?php echo plugins_url() . '/pbp-access-master/images/pbp-access-box.jpg';?>" class="pbp-image"/>
                    
                    <h3>Viewing Access</h3>
                    You can view a list of access permissions by clicking on either the "Users" or "Roles" tabs beside this tab.<br/><br/>  
                    The Users Table.
                   <img src="<?php echo plugins_url() . '/pbp-access-master/images/users-tab.jpg';?>" class="pbp-image image-margins"/><br/>
                   
                   The Roles Table.
                   <img src="<?php echo plugins_url() . '/pbp-access-master/images/roles-tab.jpg';?>" class="pbp-image image-margins"/><br/>
                    <h3>Bulk Actions</h3>
                    <strong>Grant Access</strong><br/>
                    Instead of granting access to pages individually, you can do this through bulk actions which can be done under the "Grant Access" tab beside this tab.<br/>
                    With this you can give access in bulk either to certain users or to certain roles. 
                    <img src="<?php echo plugins_url() . '/pbp-access-master/images/grant-access-tab.jpg';?>" class="pbp-image image-margins"/><br/>
                    <strong>Delete Access</strong>
                    <ol>
                        <li>Go to either the Users or Roles tabs.</li>
                        <li>Check the boxes of those you want to delete access from.</li>
                        <li>Select "delete access" from the select box.</li>
                        <li>Click apply.</li>
                    </ol>
                    <?php
                }
                ?>
            
            <?php
        }

        function page_tabs($current = 'first') {
            $tabs = array(
              'first' => __("Users", 'pbp-access'),
              'second' => __("Roles", 'pbp-access'),
              'third' => __("Grant Access", 'pbp-access'),
              'fourth' => __("Help", 'pbp-access'),
            );
            $html = '<h2 class="nav-tab-wrapper">';
            foreach ($tabs as $tab => $name) {
                $class = ($tab == $current) ? 'nav-tab-active' : '';
                $html .= '<a class="nav-tab ' . $class . '" href="?page=pbp-access-settings&tab=' . $tab . '">' . $name . '</a>';
            }
            $html .= '</h2>';
            echo $html;
        }

        function pbp_access_init() {
            $wp_pbp_access_table = new Pbp_Access_My_Pages_Table();
            $wp_pbp_access_table->prepare_items();
            ?>
            <div class="wrap">
                <h2>My Pages</h2>
                <em>Here you will find all the pages that you can edit.</em>
                <form id="table-search" method="get">
                    <?php
                    $wp_pbp_access_table->search_box(__('Search'), 'pbp-access-my-pages');
                    foreach ($_GET as $key => $value) { // http://stackoverflow.com/a/8763624/1287812
                        if ('s' !== $key) {// don't include the search query
                            echo("<input type='hidden' name='$key' value='$value' />");
                        }
                    }
                    ?>
                </form>
                <?php $wp_pbp_access_table->display(); ?>
            </div>
            <?php
        }

        global $wpdb;
        $has_role = false;
        $current_user = wp_get_current_user();
        $has_access_user = $wpdb->get_results('SELECT page_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $current_user->ID);
        $has_access_role = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . "pbp_access_roles");
        foreach ($has_access_role as $role) {
            if (in_array($role->role_id, (array) $current_user->roles)) {
                $has_role = true;
            }
        }

        if ((!empty($has_access_user)) || ($has_role)) {
            add_menu_page('My Pages', 'My Pages', 'read', 'pbp-access-my-pages', 'pbp_access_init', $icon_url = 'dashicons-unlock', 4);
        }

        add_options_page('Page by Page Access', 'Page by Page Access', 'manage_options', 'pbp-access-settings', 'pbp_access_settings', $icon_url = 'dashicons-unlock', 4);
    }

    public function author_cap_filter($allcaps, $caps, $args) {
        global $wpdb;

        if (isset($args[2])) {
            $has_access_user = $wpdb->get_results('SELECT page_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $args[1] . ' AND ' . 'page_id=' . $args[2]);
            $has_access_role = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . "pbp_access_roles WHERE page_id=" . $args[2]);
            $user = wp_get_current_user();
            $has_role = false;
            foreach ($has_access_role as $role) {
                if (in_array($role->role_id, (array) $user->roles)) {
                    $has_role = true;
                }
            }

            if ((empty($has_access_user)) && (!$has_role)) {
                return $allcaps;
            }
        }

        $allcaps['edit_page'] = 1;
        $allcaps['edit_others_pages'] = 1;
        $allcaps['edit_published_pages'] = 1;
        return $allcaps;
    }

    public function add_pbp_access_meta_box() {

        function pbp_access_meta_box_markup($page) {
            wp_nonce_field(basename(__FILE__), "meta-box-nonce");

            global $wpdb;

            $user_args = array(
              'role__not_in' => array('administrator'),
            );
            $users = get_users($user_args);
            ?>
            <i>Select the users or roles that should have edit capabilities to this page.</i>
            <br/><br/>
            <label><strong>Users</strong></label><br/><br/>
            <div style="max-height: 100px; overflow-y: auto; border: 1px #ddd solid; padding: 10px;">
                <?php foreach ($users as $user) {
                    ?>
                    <?php $has_access = $wpdb->get_results('SELECT user_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $user->ID . ' AND ' . ' page_id=' . $page->ID); ?>
                    <?php if (!empty($has_access)) {
                        ?>
                        <?php
                        /**
                         * Added by Nick Tan on March 8th, 2017
                         * 
                         * Fixed issues whereby both a user and role would have the same input name if the user had the same user_login as a role
                         */
                        ?>
                        <input name="<?php echo 'pbp_user_' . $user->user_login; ?>" id="<?php echo $user->ID; ?>" type="checkbox" value="true" checked><label>
                            <?php
                            echo $user->user_login;
                            echo($user->user_firstname != '' || $user->user_lasttname != '' ? ' (' . $user->user_firstname . ' ' . $user->user_lastname . ')' : '');
                            ?>
                        </label><br/>
                        <?php
                    }
                    else {
                        ?>
                        <?php 
                        /**
                         * Added by Nick Tan on March 8th, 2017
                         * 
                         * Fixed issues whereby both a user and role would have the same input name if the user had the same user_login as a role
                         */
                        ?>
                        <input name="<?php echo 'pbp_user_' . $user->user_login; ?>" id="<?php echo $user->ID; ?>" type="checkbox" value="true" ><label>
                            <?php
                            echo $user->user_login;
                            echo($user->user_firstname != '' || $user->user_lasttname != '' ? ' (' . $user->user_firstname . ' ' . $user->user_lastname . ')' : '');
                            ?>
                        </label><br/>
                    <?php }
                    ?>
                <?php }
                ?>
            </div>
            <br/>
            <label><strong>Roles</strong></label><br/><br/>
            <div style="max-height: 100px; overflow-y: auto; border: 1px #ddd solid; padding: 10px;">
                <?php foreach (get_editable_roles() as $role_name => $name) {
                    ?>
                    <?php if (($role_name !== 'administrator') && ($role_name !== 'editor')) {
                        ?>
                        <?php $has_access = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . "pbp_access_roles WHERE role_id='" . str_replace(' ', '_', $role_name) . "' AND " . " page_id=" . $page->ID); ?>
                        <?php if (!empty($has_access)) {
                            ?>
                            <input name="<?php echo str_replace(' ', '_', $role_name); ?>" type="checkbox" value="true" checked><label><?php echo ucwords($role_name); ?></label><br/>
                            <?php
                        }
                        else {
                            ?>     
                            <input name="<?php echo str_replace(' ', '_', $role_name); ?>" type="checkbox" value="true"><label><?php echo ucwords($role_name); ?></label><br/>
                        <?php }
                        ?>
                    <?php }
                    ?>
                <?php }
                ?>
            </div>

            <br/>
            <em>For more options, please view the Page by Page Settings.</em>
            <?php
        }

        $user = wp_get_current_user();
        if (in_array('administrator', (array) $user->roles)) {
            add_meta_box("pbp-access-meta-box", "Page Access", "pbp_access_meta_box_markup", "page", "side", "default", null);
        }
    }

    public function save_pbp_access_meta($post_id, $post) {
        global $wpdb;
        $user_args = array(
          'role__not_in' => array('administrator'),
        );
        $users = get_users($user_args);

        if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__))) {
            return $post_id;
        }

        if (!current_user_can("edit_page", $post_id)) {
            return $post_id;
        }

        if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return $post_id;
        }

        $slug = "page";
        if ($slug != $post->post_type) {
            return $post_id;
        }


        foreach ($users as $user) {
            $exists = $wpdb->get_results('SELECT user_id FROM ' . $wpdb->prefix . 'pbp_access_users WHERE user_id=' . $user->ID . ' AND ' . ' page_id=' . $post_id);
            if (isset($_POST["pbp_user_" . $user->user_login])) {
                if (empty($exists)) {
                    $table_name = $wpdb->prefix . "pbp_access_users";
                    $wpdb->insert($table_name, array(
                      'time' => time(),
                      'user_id' => $user->ID,
                      'page_id' => $post_id,
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
            elseif (!empty($exists)) {
                $table_name = $wpdb->prefix . "pbp_access_users";
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $table_name
                WHERE user_id = %d
                AND page_id = %d", $user->ID, $post_id
                    )
                );
            }
        }

        foreach (get_editable_roles() as $role_name => $name) {
            if (($role_name !== 'administrator') && ($role_name !== 'editor')) {
                $role_name = str_replace(' ', '_', $role_name);
                $exists = $wpdb->get_results('SELECT role_id FROM ' . $wpdb->prefix . "pbp_access_roles WHERE role_id='" . $role_name . "' AND " . " page_id=" . $post_id);
                if (isset($_POST[$role_name])) {
                    if (empty($exists)) {
                        $table_name = $wpdb->prefix . "pbp_access_roles";
                        $wpdb->insert($table_name, array(
                          'time' => time(),
                          'role_id' => $role_name,
                          'page_id' => $post_id,
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
                elseif (!empty($exists)) {
                    $table_name = $wpdb->prefix . "pbp_access_roles";
                    $wpdb->query(
                        $wpdb->prepare(
                            "DELETE FROM $table_name
                WHERE role_id = %s
                AND page_id = %d", $role_name, $post_id
                        )
                    );
                }
            }
        }
    }

    public function delete_pbp_access($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "pbp_access_roles";
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name
                WHERE page_id = %d", $post_id
            )
        );
        $table_name = $wpdb->prefix . "pbp_access_users";
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name
                WHERE page_id = %d", $post_id
            )
        );
    }

}
