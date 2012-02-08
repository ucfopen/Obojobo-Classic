<?php
/*
Plugin Name: obo-stats
Description: Enables whitelisted users to access the Obojobo Stats page
Version: 0.1
*/

register_activation_hook(__FILE__, 'on_activate');
register_deactivation_hook(__FILE__, 'on_deactivate');


function on_activate()
{
	// we create our custom role with our custom capability
	add_role('super_stats', 'SuperStats', array('read', 'view_obo_data'));
	// stupid hack since the array in add_role doesn't seem to allow users with
	// this role to view the admin area:
	$role = get_role('super_stats');
	$role->add_cap('view_obo_data');
	$role->add_cap('read');

	// bolt in super_stats to the Admin role:
	$role = get_role('administrator');
	$role->add_cap('view_obo_data');
}

function on_deactivate()
{
	remove_role('super_stats');

	$role = get_role('administrator');
	$role->remove_cap('view_obo_data');
}

add_action('admin_menu', 'on_admin_menu');

function on_admin_menu()
{
	//@TODO: check role/cap

	//remove WP update message:
	remove_action('admin_notices', 'update_nag', 3);

	//add the stats page
	add_menu_page('Obojobo Stats', 'Obojobo Stats', 'view_obo_data', 'obojobo_stats', 'write_stats_page');

	// remove dashboard:
	global $menu;//, $submenu, $user_ID;
	//$user = new WP_User($user_ID);
	foreach($menu as $menu_index=>$menu_item_arr)
	{
		if($menu_item_arr[0] == 'Dashboard' || $menu_item_arr[0] == 'Profile')
		{
			unset($menu[$menu_index]);
		}
	}
	//wp_redirect('/wp/wp-admin/admin.php?page=obo_data_menu');
	/*

        $the_user = new WP_User($user_ID);
        reset($menu); $page = key($menu);
        while ((__('Dashboard') != $menu[$page][0]) && next($menu))
                $page = key($menu);
        if (__('Dashboard') == $menu[$page][0]) unset($menu[$page]);
        reset($menu); $page = key($menu);
        while (!$the_user->has_cap($menu[$page][1]) && next($menu))
                $page = key($menu);
        //if (preg_match('#wp-admin/?(index.php)?$#',$_SERVER['REQUEST_URI']) && ('index.php' != $menu[$page][2]))
                //wp_redirect(get_option('siteurl') . '/wp-admin/post-new.php');*/
}

function write_stats_page()
{
	require_once('includes/stats.php');
}

/*
add_action('admin_menu', 'my_users_menu');

function my_users_menu()
{
	// Page only visible to those who can add_users (typically SuperAdmins and Admins)
	add_users_page('Obojobo Stats Access Whitelist', 'Obojobo Stats Access Whitelist', 'add_users', 'obo-stats-menu', 'my_plugin_function');
}

function my_plugin_function()
{
	// grab the whitelisted users who have the custom 'view_obo_stats' capability
	$search = new WP_User_Query(array('role' => 'administrator'));
	$users = $search->get_results();
print_r($users);
	echo '<h1>Whitelist:</h1>';
	echo '<ul>';
	foreach($users as $user)
	{
		echo '<li>Guy</li>';
		print_r($user);
	}
	echo '</ul>';

	echo '<h2>Add/Remove:</h2>';
}*/

?>