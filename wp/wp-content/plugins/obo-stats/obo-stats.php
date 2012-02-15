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

// ====================  BUILDING THE ADMIN MENU ===============================
add_action('admin_menu', 'on_admin_menu');
function on_admin_menu()
{
	$user = wp_get_current_user();

	if(current_user_can('view_obo_data'))
	{
		//add the stats page
		add_menu_page('Obojobo Stats', 'Obojobo Stats', 'view_obo_data', 'obojobo_stats', 'write_stats_page');
	
		if(user_has_role('super_stats'))
		{
			//remove WP update message:
			remove_action('admin_notices', 'update_nag', 3);

			// remove dashboard:
			global $menu;

			foreach($menu as $menu_index=>$menu_item_arr)
			{
				if($menu_item_arr[0] == 'Dashboard' || $menu_item_arr[0] == 'Profile')
				{
					unset($menu[$menu_index]);
				}
			}
		}
	}
}

function write_stats_page()
{
	require_once('includes/stats.php');
}

function user_has_role($role)
{
	global $current_user;
	return isset($current_user) && is_array($current_user->roles) && in_array($role, $current_user->roles);
}

// ======================== SUPER STATS REDIRECT DIRECTLY TO THE STATS PAGE ===============
add_filter('login_redirect', 'your_login_redirect');
function your_login_redirect()
{
	global $user;   

	// does the user have 'super_stats' role?
	if(user_has_role('super_stats'))
	{
		return admin_url('admin.php?page=obojobo_stats');
	}
	else
	{
		return admin_url();	
	}
}


/**
 * this prevent from non authorized user ( public )
 * to pointing to the profile page by writing into
 * the address bar.
 *
 * added @Evan version here to be more WP friendly
 */
add_action('admin_init', 'force_profile_redirect');
function force_profile_redirect()
{
	global $pagenow, $current_user;
	get_currentuserinfo();
	if($pagenow == 'profile.php' && !user_has_role('administrator'))
	{
		wp_redirect('admin.php?page=obojobo_stats');
	}
}


/**
 * this remove the profile links from
 * the top nav menu
 */
 add_action('wp_before_admin_bar_render', 'remove_edit_profile', 0);
function remove_edit_profile()
{
	global $wp_admin_bar, $current_user;
	get_currentuserinfo();

	if(!user_has_role('administrator'))
	{
		$wp_admin_bar->remove_menu('edit-profile');
	}
}




?>