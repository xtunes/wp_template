<?php
/**
 * Dashboard Administration Panel
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Bootstrap */
require_once('./admin.php');

/** Load WordPress dashboard API */
require_once(ABSPATH . 'wp-admin/includes/dashboard.php');

wp_dashboard_setup();

wp_enqueue_script( 'dashboard' );
wp_enqueue_script( 'plugin-install' );
wp_enqueue_script( 'media-upload' );
wp_admin_css( 'dashboard' );
wp_admin_css( 'plugin-install' );
add_thickbox();

$title = __('Dashboard');
$parent_file = 'index.php';

if ( is_user_admin() )
	add_screen_option('layout_columns', array('max' => 4, 'default' => 1) );
else
	add_screen_option('layout_columns', array('max' => 4, 'default' => 2) );

add_contextual_help($current_screen,	
	'<p>' . __( '<a href="http://xtunes.cc" target="_blank">技术支持：上海炫律科技</a>' ) . '</p>'
);

include (ABSPATH . 'wp-admin/admin-header.php');

$today = current_time('mysql', 1);
?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<div id="dashboard-widgets-wrap">

<?php wp_dashboard(); ?>

<div class="clear"></div>
</div><!-- dashboard-widgets-wrap -->

</div><!-- wrap -->

<?php require(ABSPATH . 'wp-admin/admin-footer.php'); ?>
