<?php

/**
 * Fired during plugin activation
 *
 * @link       fes.yorku.ca
 * @since      1.0.0
 *
 * @package    Pbp_Access
 * @subpackage Pbp_Access/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pbp_Access
 * @subpackage Pbp_Access/includes
 * @author     Calin Armenean <calin13@yorku.ca>
 */
class Pbp_Access_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'pbp_access_users';

        $sql_users = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time int(8) NOT NULL,
		user_id smallint(5) NOT NULL,
		page_id smallint(5) NOT NULL,
                admin_id smallint(5) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";


        dbDelta($sql_users);

        $table_name = $wpdb->prefix . 'pbp_access_roles';

        $sql_roles = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time int(8) NOT NULL,
		role_id char(30) NOT NULL,
		page_id smallint(5) NOT NULL,
                admin_id smallint(5) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

        dbDelta($sql_roles);
    }

    function pbp_access_create_db() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'pbp_access_users';

        $sql_users = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time int(8) NOT NULL,
		user_id smallint(5) NOT NULL,
		page_id smallint(5) NOT NULL,
                admin_id smallint(5) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";


        dbDelta($sql_users);

        $table_name = $wpdb->prefix . 'pbp_access_roles';

        $sql_roles = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time int(8) NOT NULL,
		role_id char(30) NOT NULL,
		page_id smallint(5) NOT NULL,
                admin_id smallint(5) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

        dbDelta($sql_roles);
    }

}
