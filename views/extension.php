<?php
return;
$this->verify_instance( $_instance ) or die;

if ( ! defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'plugin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

if ( ! $this->can_do_settings() )
	wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );

$list_table = $this->get_list_table( 'TSF_Extension_Manager_Install_List_Table', $args );

if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	$location = remove_query_arg( '_wp_http_referer', wp_unslash( $_SERVER['REQUEST_URI'] ) );

	if ( ! empty( $_REQUEST['paged'] ) )
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );

	wp_redirect( $location );
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

wp_enqueue_script( 'plugin-install' );
wp_enqueue_script( 'updates' );

?>
<h2>Network Extensions</h2>
<hr>
<?php

$list_table->prepare_items( 'network' );
$list_table->display_rows();

?>
<h2>Free Extensions</h2>
<hr>
<?php

$list_table->prepare_items( 'free' );
$list_table->display_rows();

?>
<br class="clear" />
	<span class="spinner"></span>
</div>

<?php
//require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
