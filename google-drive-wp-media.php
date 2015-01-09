<?php
/*
Plugin Name: Google Drive WP Media
Plugin URI: http://wordpress.org/plugins/google-drive-wp-media/
Description: WordPress Google Drive integration plugin. Google Drive on Wordpress Media Publishing. Upload files to Google Drive from WordPress blog.
Author: Moch Amir
Author URI: http://www.mochamir.com/
Version: 2.0
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*
			  Copyright (c)2014 Moch Amir.

			  This program is free software; you can redistribute it and/or modify
			  it under the terms of the GNU General Public License as published by
			  the Free Software Foundation; either version 2 of the License, or
			  (at your option) any later version.

			  This program is distributed in the hope that it will be useful,
			  but WITHOUT ANY WARRANTY; without even the implied warranty of
			  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			  GNU General Public License for more details.

			  You should have received a copy of the GNU General Public License
			  along with this program; if not, write to the Free Software
			  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


define( 'NAMA_GDWPM', 'Google Drive WP Media' );
define( 'ALMT_GDWPM', 'google-drive-wp-media' );
define( 'MINPHP_GDWPM', '5.3.0' );
define( 'VERSI_GDWPM', '2.0' );
define( 'MY_TEXTDOMAIN', 'gdwpm' );

require_once 'gdwpm-api/Google_Client.php';
require_once 'gdwpm-api/contrib/Google_DriveService.php';

$gdwpm_override_optional = get_option('gdwpm_override_dir_bawaan'); // cekbok, polder

if($gdwpm_override_optional[0] == 'checked' && !empty($gdwpm_override_optional[1])){

	add_filter('wp_handle_upload_prefilter', 'gdwpm_custom_upload_filter' );

	function gdwpm_custom_upload_filter( $file ){
		$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, service akun, private key
		if(!empty($gdwpm_opt_akun[1]) && !empty($gdwpm_opt_akun[2]) && !empty($gdwpm_opt_akun[3])){
		
			global $gdwpm_override_optional;
			
			$gdwpm_service_ride = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
			
			$filename = $file['name'];
			$path = $file['tmp_name'];
			$mime_berkas = $file['type'];
			//$mime_berkas = sanitize_mime_type($mime_berkas);
			$folder_ortu = preg_replace("/[^a-zA-Z0-9]+/", " ", $gdwpm_override_optional[1]);
			$folder_ortu = sanitize_text_field($folder_ortu);
			
			if($folder_ortu != ''){
				$folderId = $gdwpm_service_ride->getFileIdByName( $folder_ortu );
			}
			
			$content = '';

			if( ! $folderId ) {
				$folderId = $gdwpm_service_ride->createFolder( $folder_ortu );
				$gdwpm_service_ride->setPermissions( $folderId, $gdwpm_opt_akun[0] );
			}

			$fileParent = new Google_ParentReference();
			$fileParent->setId( $folderId );
			$fileId = $gdwpm_service_ride->createFileFromPath( $path, $filename, $content, $fileParent );
			$gdwpm_service_ride->setPermissions( $fileId, 'me', 'reader', 'anyone' );
			
			$sukinfo = '';
			if(!empty($mime_berkas) && $gdwpm_override_optional[2] == 'checked'){
				gdwpm_ijin_masuk_perpus($mime_berkas, $filename, $fileId, $content, $folder_ortu);
				$sukinfo = ' and added into your Media Library';
			}
			
			echo '<div class="updated"><p>Done! <strong>'.$fileId.'</strong> successfully uploaded into <strong>'.$folder_ortu.'</strong>'.$sukinfo.'.</p></div>';
			exit();
		}else{
			return $file;
		}
	}
}

if(isset($_REQUEST['gdwpm_opsi_kategori_nonce'])){
	require_once(ABSPATH .'wp-includes/pluggable.php');
	if(!wp_verify_nonce( $_REQUEST['gdwpm_opsi_kategori_nonce'], 'gdwpm_override_dir' )) {
		die( '<div class="error"><p>Security check not verified!</p></div>' ); 
	} else {
		if($_POST['gdwpm_cekbok_opsi_kategori'] == 1){
			update_option('gdwpm_opsi_kategori_dr_folder', 'checked');
			echo '<div class="updated"><p>GDWPM Categories has been enabled.</p></div>';
		}else{
			update_option('gdwpm_opsi_kategori_dr_folder', '');
			echo '<div class="updated"><p>GDWPM Categories has been disabled.</p></div>';
		}
	}
}

// SHORTCODE  ===> [gdwpm id="GOOGLE-DRIVE-FILE-ID" w="640" h="385"]
function gdwpm_iframe_shortcode($gdwpm_kode_berkas) {
	$gdwpm_kode_berkas = shortcode_atts( array( 'id' => '', 'w' => '640', 'h' => '385'), $gdwpm_kode_berkas, 'gdwpm' );
    return '<iframe src="https://docs.google.com/file/d/' . $gdwpm_kode_berkas['id'] . '/preview" width="' . $gdwpm_kode_berkas['w'] . '" height="' . $gdwpm_kode_berkas['h'] . '"></iframe>';	 
}
add_shortcode('gdwpm', 'gdwpm_iframe_shortcode'); 

//////////// ADMIN INIT ///////////
add_action( 'admin_init', 'gdwpm_admin_init' );
function gdwpm_admin_init() {
	$gdwpm_theme_css_pilian = get_option('gdwpm_nama_theme_css');
	if(empty($gdwpm_theme_css_pilian)){$gdwpm_theme_css_pilian = 'smoothness';}
    wp_register_style( 'gdwpm-jqueryui-theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/' . $gdwpm_theme_css_pilian . '/jquery-ui.css', false, VERSI_GDWPM );
}


function gdwpm_init() {
	$cek_taxonomy = taxonomy_exists('gdwpm_category');
	if(!$cek_taxonomy){
        $labels = array(
            'name'              => _x( 'GDWPM Categories', 'taxonomy general name' ),
            'singular_name'     => _x( 'GDWPM Category', 'taxonomy singular name' ),
            'search_items'      => __( 'Search GDWPM Categories' ),
            'all_items'         => __( 'All GDWPM Categories' ),
            'parent_item'       => __( 'Parent GDWPM Category' ),
            'parent_item_colon' => __( 'Parent GDWPM Category:' ),
            'edit_item'         => __( 'Edit GDWPM Category' ), 
            'update_item'       => __( 'Update GDWPM Category' ),
            'add_new_item'      => __( 'Add New GDWPM Category' ),
            'new_item_name'     => __( 'New GDWPM Category Name' ),
            'menu_name'         => __( 'GDWPM Categories' ),
        );

        $args = array(
            'hierarchical' => TRUE,
            'labels'       => $labels,
            'show_ui'      => TRUE,
            'show_admin_column' => TRUE,
			'update_count_callback' => '_update_generic_term_count',
            'query_var'    => TRUE,
            'rewrite'      => TRUE,
        );	
	
		register_taxonomy('gdwpm_category', 'attachment', $args);
	}
}
function gdwpm_filter_kategori_media_tab($query) {
  if ( 'attachment' != $query->query_vars['post_type'] )
			return;

			if ( isset( $_REQUEST['query']['gdwpm_category'] ) && $_REQUEST['query']['gdwpm_category']['term_slug'] )	 :
				$query->set( 'gdwpm_category', $_REQUEST['query']['gdwpm_category']['term_slug'] );
			elseif ( isset( $_REQUEST['gdwpm_category'] ) && is_numeric( $_REQUEST['gdwpm_category'] ) && 0 != intval( $_REQUEST['gdwpm_category'] ) ) :
				$term = get_term_by( 'id', $_REQUEST['gdwpm_category'], 'gdwpm_category' );
				set_query_var( 'gdwpm_category', $term->slug );
			endif;
}
function gdwpm_admin_head(){
global $wpdb;
$attachment_taxonomies = $attachment_terms = array();
$attachment_taxonomies['gdwpm_category'] = 'gdwpm_category';
$terms = get_terms( 'gdwpm_category', array(
				'orderby'       => 'name',
				'order'         => 'ASC',
				'hide_empty'    => true,
			) );

			$attachment_terms[ 'gdwpm_category' ][] = array( 'id' => 0, 'label' => 'View all GDWPM Categories', 'slug' => '' );
			foreach ( $terms as $term )
				$attachment_terms[ 'gdwpm_category' ][] = array( 'id' => $term->term_id, 'label' => $term->name, 'slug' => $term->slug );

		?>
<style type="text/css">
.wp-admin .media-toolbar-secondary select {
	margin: 11px 16px 0 0;
}
</style>
		<script type="text/javascript">
			var mediaTaxonomies = <?php echo json_encode( $attachment_taxonomies ) ?>,
				mediaTerms = <?php echo json_encode( $attachment_terms ) ?>;
		</script>
<?php
}
function gdwpm_image_category_filter() {
    $screen = get_current_screen();
    if ( 'upload' == $screen->id ) {
		$args = array(
	'show_option_all'    => 'View all GDWPM Categories',
	'orderby'            => 'name', 
	'hide_empty'         => 1, 
	'selected' => ( isset( $wp_query->query['gdwpm_category'] ) ? $wp_query->query['gdwpm_category'] : '' ),
	'hierarchical'       => 1, 
	'name'               => 'gdwpm_category',
	'taxonomy'           => 'gdwpm_category',
); 
				wp_dropdown_categories($args);
	}
}

$gdwpm_opsi_kategori = get_option('gdwpm_opsi_kategori_dr_folder'); 
if($gdwpm_opsi_kategori == 'checked'){
add_action( 'init', 'gdwpm_init' );

add_action('pre_get_posts','gdwpm_filter_kategori_media_tab');

add_action('admin_head', 'gdwpm_admin_head');

add_action( 'restrict_manage_posts', 'gdwpm_image_category_filter' );
}

//////////////// HALAMAN MEDIA MENU ///////////////
add_action( 'admin_menu', 'gdwpm_menu_media' );
function gdwpm_menu_media() {
	add_media_page( NAMA_GDWPM, NAMA_GDWPM, 'read', ALMT_GDWPM, 'gdwpm_halaman_media' );
	add_action( 'admin_enqueue_scripts', 'gdwpm_sekrip_buat_mimin' );
}
function gdwpm_halaman_media() {
	if ( !current_user_can( 'read' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h2>'. NAMA_GDWPM .'</h2>';
	gdwpm_halaman_utama();
	echo '</div>';
}

/////////////// HALAMAN TAB UPLOAD /////////////////////////
add_action('media_upload_gdwpm_nama_tab', 'gdwpm_halaman_media_upload');
function gdwpm_halaman_media_upload() {
	wp_iframe( 'gdwpm_halaman_utama' );
}

add_filter('media_upload_tabs', 'gdwpm_tab_media_upload');
function gdwpm_tab_media_upload($tabs) {
	$tabs['gdwpm_nama_tab'] = NAMA_GDWPM;
	return $tabs;
}

////////////////////////// ADMIN SEKRIP ////////////////////////////////
function gdwpm_sekrip_buat_mimin() {
	wp_enqueue_style( 'gdwpm-jqueryui-theme' );
	
	wp_enqueue_script('jquery');                    
	wp_enqueue_script('jquery-ui-core');       
	wp_enqueue_script('jquery-ui-accordion');         
	wp_enqueue_script('jquery-ui-dialog');   
	wp_enqueue_script('plupload-all');          
	wp_enqueue_script('jquery-ui-tooltip');              
	wp_enqueue_script('jquery-ui-tabs');        
	wp_enqueue_script( 'gdwpm-ajax-script', plugins_url( '/js/sekrip.js?v=20', __FILE__ ), array('jquery'), array(), VERSI_GDWPM, true );	
		
$gdwpm_opsi_kategori = get_option('gdwpm_opsi_kategori_dr_folder'); 
	wp_localize_script( 'gdwpm-ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'opsi_kategori' => $gdwpm_opsi_kategori ) );
}

/////////////////////// KASTEM ATTACHMENT URL //////////////////
add_filter( 'wp_get_attachment_url', 'gdwpm_filter_gbrurl');
function gdwpm_filter_gbrurl( $url ){
	$upload_dir = wp_upload_dir();
    if (strpos($url, 'G_D_W_P_M-file_ID/') !== false) {
		$url = str_replace( $upload_dir['baseurl'] . '/G_D_W_P_M-file_ID/', 'https://docs.google.com/uc?id=', $url ) . '&export=view';
    }
	return $url; 
}


function gdwpm_halaman_utama() {
	global $cek_kunci, $gdwpm_opt_akun, $gdwpm_service, $gdwpm_apiConfig;
$cek_kunci = 'true'; // kosong
	if (isset($_POST['gdwpm_akun_nonce']))
	{
		$nonce = $_POST['gdwpm_akun_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_akun_nonce' ) ) {
			die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			
		} else {
			if (!EMPTY($_POST['gdwpm_imel']) && !EMPTY($_POST['gdwpm_klaen_aidi']) && !EMPTY($_POST['gdwpm_nama_service']) && !EMPTY($_POST['gdwpm_kunci_rhs']))
			{
				$gdwpm_opt_imel = sanitize_email($_POST['gdwpm_imel']);
				$gdwpm_opt_klaen_aidi = sanitize_text_field($_POST['gdwpm_klaen_aidi']);
				$gdwpm_opt_nama_service = sanitize_email($_POST['gdwpm_nama_service']);
				$gdwpm_opt_kunci_rhs = esc_url($_POST['gdwpm_kunci_rhs']);
				
				$gdwpm_opt = array($gdwpm_opt_imel, $gdwpm_opt_klaen_aidi, $gdwpm_opt_nama_service, $gdwpm_opt_kunci_rhs);
				update_option('gdwpm_akun_opt', $gdwpm_opt);
				echo '<div class="updated"><p>Great! All API settings successfully saved.</p></div>';
			}else{
				echo '<div class="error"><p>All fields are required.</p></div>';
			}
		}
	}
$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, gdwpm_service akun, private key
if($gdwpm_opt_akun){
$cek_kunci = 'false';
//$gdwpm_apiConfig['use_objects'] = true;
$gdwpm_service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
}

if (isset($_POST['buang_folder_pilian']))
{
	$gdwpm_nama_folder = $gdwpm_service->getNameFromId( $_POST['buang_folder_pilian'] );
	$gdwpm_tong_sampah = $gdwpm_service->buangFile( $_POST['buang_folder_pilian'] );
	if($gdwpm_tong_sampah){
		echo '<div class="updated"><p>Folder <strong>'.$_POST['buang_folder_pilian'].' '.$gdwpm_nama_folder.'</strong> successfully deleted.</p></div>';
		sleep(3);
	}else{
		echo '<div class="error"><p>' . $_POST['buang_folder_pilian'] . ' ' . $gdwpm_nama_folder . ' fail to delete.</p></div>';
	}
}

if (isset($_POST['gdwpm_buang_berkas_terpilih']))
{
	$gdwpm_info_files = '';
	if (is_array($_POST['gdwpm_buang_berkas_terpilih'])) {
		foreach($_POST['gdwpm_buang_berkas_terpilih'] as $value){
			$gdwpm_berkas_terpilih_array = explode(' | ', $value); // mime, name, id, desc, folder
			$gdwpm_tong_sampah = $gdwpm_service->buangFile( $gdwpm_berkas_terpilih_array[2] );
			if($gdwpm_tong_sampah){
				$gdwpm_info_files .= '<strong>' . $gdwpm_berkas_terpilih_array[1] . '</strong> deleted, ';
			}else{
				$gdwpm_info_files .= '<strong>' . $gdwpm_berkas_terpilih_array[1] . '</strong> failed, ';
			}
			//sleep(0.5);
		}
	} else {
		$gdwpm_berkas_terpilih_array = explode(' | ', $_POST['gdwpm_buang_berkas_terpilih']); // mime, name, id, desc, folder
		$gdwpm_tong_sampah = $gdwpm_service->buangFile( $gdwpm_berkas_terpilih_array[2] );
		if($gdwpm_tong_sampah){
			$gdwpm_info_files = '<strong>' . $gdwpm_berkas_terpilih_array[1] . '</strong> deleted, ';
		}else{
			$gdwpm_info_files = '<strong>' . $gdwpm_berkas_terpilih_array[1] . '</strong> failed, ';
		}
	}
    echo '<div class="updated"><p>'.$gdwpm_info_files.'.. Done!.</p></div>';
	sleep(3);
}

if (isset($_POST['gdwpm_gawe_folder_nonce']))
{
		$nonce = $_POST['gdwpm_gawe_folder_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_gawe_folder_nonce' ) ) {
			die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			
		} else {
			if (!EMPTY($_POST['gdwpm_gawe_folder']))
			{
				$gawe_folder = preg_replace("/[^a-zA-Z0-9]+/", " ", $_POST['gdwpm_gawe_folder']);
				$gawe_folder = sanitize_text_field($gawe_folder);
				$folderId = $gdwpm_service->getFileIdByName( $gawe_folder );

				if( ! $folderId ) {
					$folderId = $gdwpm_service->createFolder( $gawe_folder );
					$gdwpm_service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
					echo '<div class="updated"><p>Great! Folder name <strong>'.$gawe_folder.'</strong> successfully created.</p></div>';
				}else{
					echo '<div class="error"><p>Folder '.$gawe_folder.' already exist</p></div>';
				}
			}else{
					echo '<div class="error"><p>Folder name cannot be empty!</p></div>';
			}
		}
}

?>
<script>
jQuery(function() {
    var icons = {
		header: "ui-icon-wrench",
		activeHeader: "ui-icon-lightbulb"
    };
    jQuery( "#accordion" ).accordion({
		heightStyle: "content",
		icons: icons
    });
  
    var tooltips = jQuery( "[title]" ).tooltip();
 
	jQuery( "#tabs" ).tabs({
		beforeLoad: function( event, ui ) {
			ui.jqXHR.error(function() {
				ui.panel.html(
				"Opening Options tab, please wait.." );
			});
		}
    });
  
	jQuery( "#doktabs" ).tabs({
		beforeLoad: function( event, ui ) {
			ui.jqXHR.error(function() {
				ui.panel.html(
				"Opening plugin documentation tab, please wait.." );
			});
		}
    });
	
	jQuery( "#gdwpm-settingtabs" ).tabs({
		beforeLoad: function( event, ui ) {
			ui.jqXHR.error(function() {
				ui.panel.html(
				"Opening Themes Setting tab, please wait.." );
			});
		}	
    });
});
</script>
<style type="text/css">
#box-table-a
{
	font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
	font-size: 12px;
	width:100%;
	text-align: left;
	border-collapse: collapse;
}
#box-table-a th
{
	font-size: 13px;
	font-weight: normal;
	padding: 8px;
	background: #B6F0F6;
	border-top: 4px solid #DAF8FB;
	border-bottom: 1px solid #fff;
	color: #039;
}
#box-table-a td
{
	padding: 8px;
	background: #EAFAFC; 
	border-bottom: 1px solid #fff;
	color: #669;
	border-top: 1px solid transparent;
}
#box-table-a tr:hover td
{
	background: #FFFFCF;
	color: #339;
}

div.halpager {
    text-align: center;
    margin: 1em 0;
}

div.halpager span {
    display: inline-block;
    width: 1.8em;
    height: 1.8em;
    line-height: 1.8;
    text-align: center;
    cursor: pointer;
    background: #EAFAFC;
    color: #039;
    margin-right: 0.5em;
}

div.halpager span.active {
    background: #B6F0F6;
}
</style>
<?php

	$gdwpm_apiConfig['use_objects'] = true;
	
	if($gdwpm_opt_akun){
	$result = array();
	$pageToken = NULL;

	do {
		try {
			$parameters = array();
			$parameters['q'] = "mimeType = 'application/vnd.google-apps.folder'";
			if ($pageToken) {
				$parameters['pageToken'] = $pageToken;
			}
			$files = $gdwpm_service->files->listFiles($parameters);

			$result = array_merge($result, $files->getItems());
			$pageToken = $files->getNextPageToken();
		} catch (Exception $e) {
			echo '<div class="error">An error occurred: ' . wp_strip_all_tags($e->getMessage()) . '</div>';
			$pageToken = NULL;
		}
	} while ($pageToken);
	$folderpil = '<select id="folder_pilian" name="folder_pilian">';
	$foldercek = array();
	foreach( $result as $obj )
	{//description, title
		if($fld == $obj->id){$selek = ' selected';}else{$selek = '';}
		$folderpil .=  '<option value="'.$obj->id.'"'.$selek.'>'.$obj->title.'</option>';
		$foldercek[] = $obj->title; 
	}
	$folderpil .= '</select>';
	$foldercek = array_filter($foldercek);
	if (empty($foldercek)) {
		$folderpil = '';
	}
	}
?>
<div id="accordion" style="margin:10px 10px 5px 10px;">
<?php
//cek versi php
$gdwpm_cek_php = 'true';
if(version_compare(PHP_VERSION, MINPHP_GDWPM) >= 0) {
	$gdwpm_cek_php = 'false';
	global $cek_kunci;
if($cek_kunci == 'false'){ ?>
		<h3>My Google Drive</h3>
	<div>
		<?php
			$gdwpm_tab_opsi_nonce = wp_create_nonce( "gdwpm_tab_opsi_key" );
			$gdwpm_url_tab_opsi = admin_url( 'admin-ajax.php?action=gdwpm_on_action&gdwpm_tabulasi=opsyen&gdwpm_tab_opsi_nonce=') . $gdwpm_tab_opsi_nonce;
		?>
		<div id="tabs" style="margin:0 -12px 0 -12px;">
		<ul>
 <?php if (!empty($foldercek)) { ?>
			<li><a href="#tabs-1"><span style="float:left" class="ui-icon ui-icon-script"></span>&nbsp;File & Folder List</a></li>
			<li><a href="#tabs-2"><span style="float:left" class="ui-icon ui-icon-star"></span>&nbsp;Upload</a></li>
			<li><a href="<?php echo $gdwpm_url_tab_opsi; ?>"><span style="float:left" class="ui-icon ui-icon-clipboard"></span>&nbsp;Options</a></li>
			<li><a href="#tabs-4"><span style="float:left" class="ui-icon ui-icon-heart"></span>&nbsp;Account Information</a></li>
			<li><a href="#tabs-5"><span style="float:left" class="ui-icon ui-icon-trash"></span>&nbsp;Removal Tool (Beta)</a></li>
<?php }else{ ?>
			<li><a href="#tabs-6"><span style="float:left" class="ui-icon ui-icon-folder-collapsed"></span>&nbsp;Create Folder</a></li>
<?php } ?>
		</ul>
 <?php if (!empty($foldercek)) { ?>
			<div id="tabs-1">
				<div id="tombol-donat" class="ui-widget-content ui-corner-all" style="width:200px; float:right; padding:1em;">	
					<p>If you like this plugin and you feel that this plugin is useful, help keep this plugin free by clicking the donate button. Your donations help keep the plugin updated, maintained and the development motivated. :)
					</p>
					<p style="text-align: center;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZZNNMX3NZM2G2" target="_blank">
					<img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="Donate Button with Credit Cards" /></a>
					</p>						
				</div>
				<p>Select folder: <?php echo $folderpil; ?> <button id="golek_seko_folder" name="golek_seko_folder"><?php _e('Get Files') ?></button> &nbsp;&nbsp;
					<span id="gdwpm_info_folder_baru" style="display:none;">
						You have created at least one folder.
						<a href=""><button id="gdwpm_tombol_info_folder_baru" name="gdwpm_tombol_info_folder_baru"><?php _e('Reload Now') ?></button></a>
					</span>
				</p>
				<?php add_thickbox();?>
				<p>
					<span class="sukses">Please select folder and click Get Files, to show all files belongs to it.<br /><br />
						Shortcode: <code>[gdwpm id="<strong>GOOGLE-DRIVE-FILE-ID</strong>"]</code>
						<br />
						Shortcode with specific width & height: <code>[gdwpm id="<strong>GOOGLE-DRIVE-FILE-ID</strong>" w="<strong>640</strong>" h="<strong>385</strong>"]</code>
						<br />
						Link URL of your file: https://docs.google.com/uc?id=<strong>GOOGLE-DRIVE-FILE-ID</strong>&export=view 
						<br />
						Preview: https://docs.google.com/file/d/<strong>GOOGLE-DRIVE-FILE-ID</strong>/preview
						<br />
						Google Docs Viewer: https://docs.google.com/viewer?url=https%3A%2F%2Fdocs.google.com%2Fuc%3Fid%3D<strong>GOOGLE-DRIVE-FILE-ID</strong>%26export%3Dview
						<?php
							$ebot = $gdwpm_service->getAbout();
							echo '<br /><br />Storage Usage<br />Total quota: '.size_format($ebot->getQuotaBytesTotal(), 2).'<br />
							Used quota: '.size_format($ebot->getQuotaBytesUsed(), 2).'<br />
							Available space: '.size_format($ebot->getQuotaBytesTotal() - $ebot->getQuotaBytesUsed(), 2).'<br />';
						?>
					</span>
				</p>		
				<div style="display: none" id="gdwpm_loading_gbr">
				  <center><img src="<?php echo plugins_url( '/images/animation/ajax_loader_blue_256.gif', __FILE__ );?>" /><br />Please wait...</center>
				</div>
				<div id="hasil"></div>
				<div style="display: none" id="gdwpm_masuk_perpus_teks"><p>Pick a file to include it in the Media Library.</p>
					<p>
						<button id="gdwpm_berkas_masuk_perpus" name="gdwpm_berkas_masuk_perpus">Add to Media Library</button>&nbsp;&nbsp;&nbsp; 
						<span style="display: none" id="gdwpm_add_to_media_gbr">
							<img src="<?php echo plugins_url( '/images/animation/loading-bar-image.gif', __FILE__ );?>" />
						</span>
						<span id="gdwpm_info_masuk_perpus"></span>
					</p>
				</div>
			</div>
			<div id="tabs-2">
				<p>
					Select folder: <?php echo str_replace('folder_pilian', 'folder_pilian_aplod', $folderpil); ?> or create a new folder: <input type="text" id="gdwpm_folder_anyar" name="gdwpm_folder_anyar" value="" size="37" placeholder="Ignore this field if you use existing folder">
				</p>
					<!--<p>Short Description: <input type="text" name="gdwpm_aplod_deskrip" value="" size="65" placeholder="Optional"></p>-->
				<p>
					<ul>
						<li><dfn>Your Uploaded files will be listed in "Shared with Me" view (<a href="https://drive.google.com/?authuser=0#shared-with-me" target="_blank">https://drive.google.com/?authuser=0#shared-with-me</a>).
						</dfn></li>
						<li><dfn>Accepted Media MIME types: */*</dfn>
						<!--	<br />&nbsp;<dfn>All Filetypes are allowed.</dfn>
						--></li>
					</ul> 
				</p>
				
<?php
				$gdwpm_satpam_buat_nonce = wp_create_nonce( 'gdwpm_satpam_aplod_berkas' );
				?> 
				<ul id="filelist"></ul>
				<br />
				<br />
				<pre id="console"></pre>
				<div id="gdwpm_upload_container"><p id="gdwpm-pilih-kt">Choose your files: 
					<a id="gdwpm_tombol_browse" href="javascript:;"><button id="gdwpm_tombol_bk_folder">Browse</button></a></p>
					<input type='checkbox' id='gdwpm_cekbok_masukperpus' name='gdwpm_cekbok_masukperpus' value='1' checked /> Add to Media Library. (just linked to, all files still remain in Google Drive)<!-- (Image only: <i>*.jpg, *.jpeg, *.png, & *.gif</i>)--><p>
					<a style="display:none;" id="gdwpm_start-upload" href="javascript:;"><button id="gdwpm_tombol_upload">Upload to Google Drive</button></a>
				</div>
				<img id="gdwpm_loding_128" style="margin: 0 0 0 111px;display:none;" src="<?php echo plugins_url( '/images/animation/ajax_loader_blue_128.gif', __FILE__ );?>">
 
<script type="text/javascript"> 
	var uploader = new plupload.Uploader({
		browse_button: 'gdwpm_tombol_browse', 
		url: '<?php echo admin_url( 'admin-ajax.php?action=gdwpm_on_action&gdwpm_nonce_aplod_berkas=') . $gdwpm_satpam_buat_nonce; ?>',
		chunk_size: '700kb',
		max_retries: 3
	});
 
	uploader.init();
 
	uploader.bind('FilesAdded', function(up, files) {
		var html = '';
		plupload.each(files, function(file) {
			html += '<li id="' + file.id + '"><strong><font color="maroon">' + file.name + '</font></strong> (' + plupload.formatSize(file.size) + ') <b></b> <input type="text" id="' + file.id + 'gdwpm_aplod_deskrip" name="' + file.id + 'lod_deskrip" value="" size="55" placeholder="Short Description (optional)"></li>';
		});
		
		document.getElementById('filelist').innerHTML += html;
		jQuery('#console').empty();
		jQuery('#gdwpm_start-upload').show();
	});
 
	uploader.bind('UploadProgress', function(up, file) {
		document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span><font color="blue">' + file.percent + "%</font></b>  " +  jQuery('#' + file.id + 'gdwpm_aplod_deskrip').val() + "<b></span><hr>";
		
		jQuery('#' + file.id + 'gdwpm_aplod_deskrip').hide();
		jQuery('#gdwpm_upload_container').hide();
		jQuery('#gdwpm_loding_128').show();
	});
 
	uploader.bind('Error', function(up, err) {
		document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
		
		jQuery('#gdwpm_upload_container').show();
		jQuery('#gdwpm_loding_128').hide();
		jQuery('#gdwpm_start-upload').hide();
	});
	
	document.getElementById('gdwpm_start-upload').onclick = function() {
		uploader.start();
	};
	
	uploader.bind('FileUploaded', function(up, file, response ) {
        response=response["response"];
		jQuery('#console').html(response);
		
		jQuery('#gdwpm_upload_container').show();
		jQuery('#gdwpm_loding_128').hide();
		jQuery('#gdwpm_start-upload').hide();
		
		if(jQuery('#gdwpm_folder_anyar').val() != ''){
			jQuery('#gdwpm_info_folder_baru').show();
		}
	});
 
	uploader.bind('BeforeUpload', function (up, file) {
		up.settings.multipart_params = {gdpwm_nm_bks: jQuery("#folder_pilian_aplod option:selected").text(), gdpwm_nm_id: jQuery('select[name=folder_pilian_aplod]').val(), 
		gdpwm_nm_br: jQuery('#gdwpm_folder_anyar').val(), gdpwm_sh_ds: jQuery('#' + file.id + 'gdwpm_aplod_deskrip').val(), gdpwm_med_ly: jQuery('#gdwpm_cekbok_masukperpus:checked').val(),
		gdpwm_nama_file: file.name};
	});  
	
	uploader.bind('ChunkUploaded', function(up, file, info) {
		response=info["response"];
		jQuery('#console').html(response);
		
		jQuery('#gdwpm_upload_container').show();
		jQuery('#gdwpm_loding_128').hide();
		jQuery('#gdwpm_start-upload').hide();
	}); 
</script>
			</div>
			<!-- tabs-3 ajax -->
			<div id="tabs-4">
						<table>
							<tr>
								<td>Service Account Name</td><td>: </td>
								<td><?php echo $ebot->getName();?></td>
							</tr>
							<tr>
								<td>Total quota</td><td>: </td>
								<td><?php echo size_format($ebot->getQuotaBytesTotal(), 2) . ' ('. $ebot->getQuotaBytesTotal() . ' bytes)';?></td>
							</tr>
							<tr>
								<td>Used quota</td><td>: </td>
								<td><?php echo size_format($ebot->getQuotaBytesUsed(), 2) . ' ('. $ebot->getQuotaBytesUsed() . ' bytes)';?></td>
							</tr>
							<tr>
								<td>Available space</td><td>: </td>
								<td><?php $sisakuota = $ebot->getQuotaBytesTotal() - $ebot->getQuotaBytesUsed(); echo size_format($sisakuota, 2) . ' ('. $sisakuota . ' bytes)';?></td>
							</tr>
							<tr>
								<td>Root folder ID</td><td>: </td>
								<td><?php echo $ebot->getRootFolderId();?></td>
							</tr>
							<tr>
								<td>Domain Sharing Policy</td><td>: </td>
								<td><?php echo $ebot->getDomainSharingPolicy();?></td>
							</tr>
							<tr>
								<td>Permission Id</td><td>: </td>
								<td><?php echo $ebot->getPermissionId();?></td>
							</tr>
						</table>
			</div>
			<div id="tabs-5">
				<p>What do you want to do?</p>
				 <p style="margin-left:17px;"><a onclick="gdwpm_cekbok_opsi_buang_folder_eksen();"><input type='radio' name='gdwpm_cekbok_opsi_buang_folder' value='1' /></a> 
					Delete folder</p>
				<p style="margin-left:17px;"><a onclick="gdwpm_cekbok_opsi_buang_file_eksen();"><input type='radio' name='gdwpm_cekbok_opsi_buang_folder' value='1' /></a> 
					Delete files</p>
				<br />
				<div id="gdwpm_kotak_buang_folder" class="ui-widget-content ui-corner-all" style="padding:1em;display:none;">	
				<form id="gdwpm_form_buang_folder" name="gdwpm_form_buang_folder" method="post">
					<p>Select folder to delete: <?php echo str_replace('folder_pilian', 'buang_folder_pilian', $folderpil); ?> <button id="gdwpm_buang_folder" name="gdwpm_buang_folder"><?php _e('Delete Now') ?></button> &nbsp;&nbsp;
						</form>
					</p>
				</div>
				<div id="gdwpm_kotak_buang_file" class="ui-widget-content ui-corner-all" style="padding:1em;display:none;">					
				<p>Select folder: <?php echo str_replace('folder_pilian', 'folder_pilian_file_del', $folderpil); ?> <button id="gdwpm_file_dr_folder" name="gdwpm_file_dr_folder"><?php _e('Get Files') ?></button> &nbsp;&nbsp;
					
				<p>
					<span class="sukses_del">Please select folder and click Get Files, to show all files belongs to it.
				</span>
				</p>
				
				<div style="display: none" id="gdwpm_loading_gbr_del">
				  <center><img src="<?php echo plugins_url( '/images/animation/ajax_loader_blue_256.gif', __FILE__ );?>" /><br />Please wait...</center>
				</div>
				<form id="gdwpm_form_buang_berkas" name="gdwpm_form_buang_berkas" method="post">	
				<div id="hasil_del"></div>
				<div style="display: none" id="gdwpm_info_del"><p>Selected file(s) will be permanently deleted. Are you ready?</p>
					<p>
						<button id="gdwpm_berkas_buang" name="gdwpm_berkas_buang">Delete Selected</button>
					</p>
				</div>
				</form>
				</div>
<div id="dialog-buang-folder" title="Confirm Deletion" style="display: none;">
  <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
    Are you sure want to delete this folder?
  </p>
  <p>
    All files in this folder will be permanently deleted and cannot be recovered.
  </p>
</div>
<div id="dialog-buang-berkas" title="Confirm Deletion" style="display: none;">
  <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
    Are you sure want to delete selected file(s)?
  </p>
  <p>
    All selected files will be permanently deleted and cannot be recovered.
  </p>
</div>
			</div>
<script>
function gdwpm_cekbok_opsi_buang_folder_eksen(){
		document.getElementById("gdwpm_kotak_buang_folder").style.display = "block";
		document.getElementById("gdwpm_kotak_buang_file").style.display = "none";
}
function gdwpm_cekbok_opsi_buang_file_eksen(){
		document.getElementById("gdwpm_kotak_buang_file").style.display = "block";
		document.getElementById("gdwpm_kotak_buang_folder").style.display = "none";
}
jQuery(function(){
        jQuery('#dialog-buang-folder').dialog({
            autoOpen: false,
            modal: true,
            width: 350,
            resizable: false,
            buttons: {
                "Yes, sure!": function() {
                    document.gdwpm_form_buang_folder.submit();
                },
                "Cancel": function() {
                    jQuery(this).dialog("close");
                }
            }
        });
         
        jQuery('form#gdwpm_form_buang_folder').submit(function(e){
            e.preventDefault();
 
            jQuery('#dialog-buang-folder').dialog('open');
        });
		
        jQuery('#dialog-buang-berkas').dialog({
            autoOpen: false,
            modal: true,
            width: 350,
            resizable: false,
            buttons: {
                "Yes, I do!": function() {
                    document.gdwpm_form_buang_berkas.submit();
                },
                "Cancel": function() {
                    jQuery(this).dialog("close");
                }
            }
        });
         
        jQuery('form#gdwpm_form_buang_berkas').submit(function(e){
            e.preventDefault();
 
            jQuery('#dialog-buang-berkas').dialog('open');
        });
});

</script>
<?php }else{ ?>
			<div id="tabs-6">
				<p>
					No folder exist/detected in your drive.
				</p>
				<p>
					This plugin requires at least 1 folder to store your files.
				</p>
				<form name="gdwpm_form_gawe_folder" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					
					<?php $gdwpm_gawe_folder_nonce = wp_create_nonce( "gdwpm_gawe_folder_nonce" ); ?>
		
					<input type="hidden" name="gdwpm_gawe_folder_nonce" value="<?php echo $gdwpm_gawe_folder_nonce;?>">
					
					<p>
						Folder Name: <input type="text" name="gdwpm_gawe_folder" value=""> <button id="simpen_gawe_folder"><?php _e('Create Folder') ?></button>
					</p>
				</form>
			</div>
<?php } ?>
		</div>
	</div>
<?php } ?>
		<h3>Settings</h3>
	<div>
		<?php
			$gdwpm_tabulasi_themeset_nonce = wp_create_nonce( "gdwpm_tabulasi_themeset_nonce" );
			$gdwpm_url_tab_themeset = admin_url( 'admin-ajax.php?action=gdwpm_on_action&gdwpm_tabulasi=themeset&gdwpm_tabulasi_themeset_nonce=') . $gdwpm_tabulasi_themeset_nonce;
		?>
		<div id="gdwpm-settingtabs" style="margin:0 -12px 0 -12px;">
			<ul>
				<li><a href="#gdwpm-settingtabs-1"><span style="float:left" class="ui-icon ui-icon-key"></span>&nbsp;Google Drive API Key</a></li>
				<li><a href="<?php echo $gdwpm_url_tab_themeset; ?>"><span style="float:left" class="ui-icon ui-icon-video"></span>&nbsp;Themes</a></li>
			</ul>
			<div id="gdwpm-settingtabs-1">
			
				<table>
					<tr>
						<td>
				<form name="gdwpm_isi_akun" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
							Google Email
						</td>
						<td>: </td>
						<td>
							<input type="text" name="gdwpm_imel" value="<?php echo $gdwpm_opt_akun[0];?>"  title="Use this Email to share with. eg: youremail@gmail.com" size="35">
						</td>
					</tr>
					<tr>
						<td>
							Client ID
						</td>
						<td>: </td>
						<td>
							<input type="text" name="gdwpm_klaen_aidi" value="<?php echo $gdwpm_opt_akun[1];?>"  title="eg: 123456789.apps.googleusercontent.com" size="55">
						</td>
					</tr>
					<tr>
						<td>
							Service Account Name
						</td>
						<td>: </td>
						<td>
							<input type="text" name="gdwpm_nama_service" value="<?php echo $gdwpm_opt_akun[2];?>"  title="eg: 123456789@developer.gserviceaccount.com" size="55">
						</td>
					</tr>
					<tr>
						<td>
					Private Key Url Path
						</td>
						<td>: </td>
						<td>
							<input type="text" name="gdwpm_kunci_rhs" value="<?php echo $gdwpm_opt_akun[3];?>"  title="eg: http://yourdomain.com/path/to/123xxx-privatekey.p12." size="75">
						</td>
					</tr>
				</table>
					<br />
					
					<?php $gdwpm_akun_nonce = wp_create_nonce( "gdwpm_akun_nonce" ); ?>
			
						<input type="hidden" name="gdwpm_akun_nonce" value="<?php echo $gdwpm_akun_nonce;?>">
						<p style="margin-left:35px;">
							<button type="submit" id="simpen_gdwpm_akun"><?php _e('Save') ?></button>
						</p>		
				</form>
			</div>
		</div>
	</div>
<?php }else{$cek_kunci = 'false';} ?>
		<h3>Documentation</h3>
	<div>
		<?php
			$gdwpm_tabulasi_nonce = wp_create_nonce( "gdwpm_tabulasi_ajax" );
			$gdwpm_url_tab_dok = admin_url( 'admin-ajax.php?action=gdwpm_on_action&gdwpm_tabulasi=apidoku&gdwpm_tabulasi_nonce=') . $gdwpm_tabulasi_nonce;
		?>
		<div id="doktabs" style="margin:0 -12px 0 -12px;">
			<ul>
				<li><a href="#doktabs-1"><span style="float:left" class="ui-icon ui-icon-note"></span>&nbsp;Requirements</a></li>
				<li><a href="<?php echo $gdwpm_url_tab_dok; ?>"><span style="float:left" class="ui-icon ui-icon-suitcase"></span>&nbsp;Google Drive API</a></li>
			</ul>
			<div id="doktabs-1">
				<h3>Minimum requirements</h3>
				<p>
					PHP <?php echo MINPHP_GDWPM;?> with cURL enabled.
				</p>
				<p>
					<span style="float:left" class="ui-icon ui-icon-info"></span>&nbsp;Your PHP version is <b><?php echo phpversion();?></b> and cURL <?php if(function_exists('curl_version')){$curlver = curl_version(); echo 'version is '.$curlver['version'];}else{echo 'was disabled';} ?>.
				</p>
			</div>
		</div>
	</div>
		<h3>About</h3>
	<div>
		<p>
			<?php echo NAMA_GDWPM;?> current installed version is <?php echo VERSI_GDWPM;?>
		</p>
		<p>
			<?php echo NAMA_GDWPM;?> was created and developed by Moch Amir. <br />It is licensed as Free Software under GNU General Public License 2 (GPL 2).
			You can find more information about <?php echo NAMA_GDWPM;?> on its page in the WordPress Plugin Directory. Please rate and review the plugin in the WordPress Plugin Directory.
		</p>
		<p>
			If you have any question, there's support forum provided in its page too. Before asking for support, please carefully read the Frequently Asked Questions, where you will find answers to the most common questions, and search through the forums.
		</p>
		<p>
			If you do not find an answer there, please open a new thread in the WordPress Support Forums.
		</p>
		<p>
			Credits:
			<ul>
				<li>Google Drive API & products are owned by Google inc.</li> 
				<li>Table Style credit to R. Christie (SmashingMagazine).</li> 
				<li>DriveServiceHelper Class credit to Lukasz Kujawa.</li> 
				<li>Loading images animation credit to mytreedb project.</li> 
				<li>JQuery Upload credit to PLUpload by Moxiecode Systems AB.</li> 
				<li>JQuery User Interface credit to JQueryUI.</li> 
				<li>Alternative openssl sign function credit to Rochelle Alder.</li> 
			</ul>
		</p>
		<p style="margin-top:57px;">
			Donations and good ratings encourage me to further develop the plugin and to provide countless hours of support. <br />Any amount is appreciated! 
		</p>
		<p>
			Thank You!
		</p>
	</div>
</div>
<div id="dialog-message" title="Warning" style="display: none;">
  <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
    This plugin requires api key to authorize your drive.
  </p>
  <p>
    Click the documentation tab for more info.
  </p>
</div>
<div id="gdwpm_pringatan_versi_php" title="Warning" style="display: none;">
  <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
    Sorry, You can't use this plugin with your current PHP version.<br>This plugin requires <b>PHP <?php echo MINPHP_GDWPM;?></b>.<br>
	Your PHP version is <b><?php echo phpversion();?></b>.
  </p>
  <p>
    Please upgrade your PHP to <?php echo MINPHP_GDWPM;?>.
  </p>
</div>
<script>
   function gantiBaris() {
    var selectBox = document.getElementById("pilihBaris");
    var jumlahBaris = selectBox.options[selectBox.selectedIndex].value;
    jumBaris(jumlahBaris);
   }

  jQuery(function() {
    jQuery( "#gdwpm_pringatan_versi_php" ).dialog({
      autoOpen: <?php echo $gdwpm_cek_php;?>,
      modal: true,
      width: 350,
      resizable: false,
      buttons: {
        Ok: function() {
          jQuery( this ).dialog( "close" );
        }
      }
    });
    jQuery( "#dialog-message" ).dialog({
      autoOpen: <?php echo $cek_kunci;?>,
      modal: true,
      width: 350,
      resizable: false,
      buttons: {
        Ok: function() {
          jQuery( this ).dialog( "close" );
        }
      }
    });
  
    jQuery( "#golek_seko_folder" )
      .button({
      icons: {
        primary: "ui-icon-circle-arrow-s"
      }
    })
  
    jQuery( "#gdwpm_buang_folder" )
      .button({
      icons: {
        primary: "ui-icon-trash"
      }
    })
  
    jQuery( "#gdwpm_file_dr_folder" )
      .button({
      icons: {
        primary: "ui-icon-circle-arrow-s"
      }
    })
	
    jQuery( "#gdwpm_berkas_buang" )
      .button({
      icons: {
        primary: "ui-icon-trash"
      }
    })
	
    jQuery( "#gdwpm_tombol_info_folder_baru" )
      .button({
      icons: {
        primary: "ui-icon-refresh"
      }
    })
	
    jQuery( "#gdwpm_berkas_masuk_perpus" )
      .button({
      icons: {
        primary: "ui-icon-circle-plus"
      }
    })
	
    jQuery( "#gdwpm_tombol_bk_folder" )
      .button({
      icons: {
        primary: "ui-icon-folder-open"
      }
    })
	
    jQuery( "#gdwpm_tombol_upload" )
      .button({
      icons: {
        primary: "ui-icon-arrowthickstop-1-n"
      }
    })
	
    jQuery( "#simpen_gdwpm_akun" )
      .button({
      icons: {
        primary: "ui-icon-person"
      }
    })
		
    jQuery( "#simpen_gawe_folder" )
      .button({
      icons: {
        primary: "ui-icon-folder-collapsed"
      }
    })
		
  });
</script>
<?php

}

function gdwpm_ijin_masuk_perpus($jenis_berkas, $nama_berkas, $id_berkas, $deskrip_berkas, $jeneng_folder = 'Uncategorized', $gdwpm_lebar_gbr = '', $gdwpm_tinggi_gbr = ''){  
	// ADD TO LIBRARY
	
	$gdwpm_fol_n_id = 'G_D_W_P_M-file_ID/' . $id_berkas;
	
	if(strpos($jenis_berkas, 'image') !== false){
		$gdwpm_ukur_gambar    = getimagesize('https://docs.google.com/uc?id=' . $id_berkas. '&export=view');
		$gdwpm_lebar_gbr  = $gdwpm_ukur_gambar[0];
		$gdwpm_tinggi_gbr = $gdwpm_ukur_gambar[1];
		
		$gdwpm_dummy_fol = get_option('gdwpm_dummy_folder');
		
		if($gdwpm_dummy_fol == 'checked'){
			$gdwpm_ekst_gbr = explode('/', $jenis_berkas);
			if($gdwpm_ekst_gbr[1] == 'png' || $gdwpm_ekst_gbr[1] == 'gif' || $gdwpm_ekst_gbr[1] == 'bmp'){
				$gdwpm_fol_n_id = 'gdwpm_images/' . $id_berkas . '.' . $gdwpm_ekst_gbr[1];
			}else{
				$gdwpm_fol_n_id = 'gdwpm_images/' . $id_berkas . '.jpg';
			}
		}
		//$ukuran = array('thumbnail' => array('file' => 'G_D_W_P_M-file_ID/'.$id_berkas, 'width' => '150', 'height' => '150'));
		//, 'size' => $ukuran
	}
	
	$meta = array('aperture' => 0, 'credit' => '', 'camera' => '', 'caption' => $nama_berkas, 'created_timestamp' => 0, 'copyright' => '',  
				'focal_length' => 0, 'iso' => 0, 'shutter_speed' => 0, 'title' => $nama_berkas); 
	$attachment = array( 'post_mime_type' => $jenis_berkas, 'guid' => $gdwpm_fol_n_id,
				'post_parent' => 0,	'post_title' => $nama_berkas, 'post_content' => $deskrip_berkas);
	$attach_id = wp_insert_attachment( $attachment, $gdwpm_fol_n_id, 0 );

	
	$metadata = array("image_meta" => $meta, "width" => $gdwpm_lebar_gbr, "height" => $gdwpm_tinggi_gbr, 'file' => $gdwpm_fol_n_id, "gdwpm"=>TRUE); 
    wp_update_attachment_metadata( $attach_id,  $metadata);	
	
$gdwpm_opsi_kategori = get_option('gdwpm_opsi_kategori_dr_folder'); 
	if($attach_id != 0 && $gdwpm_opsi_kategori == 'checked')
	{//TAXONOMY GANTI MASUK LEWAT SINI AJAH, 
		wp_set_object_terms( $attach_id, $jeneng_folder, 'gdwpm_category' );
	}
}

///////////// AJAX EKSYEN /////////////// ajax admin url =====>  gdwpm_on_action
add_action( 'wp_ajax_gdwpm_on_action', 'gdwpm_action_callback' );
function gdwpm_action_callback() {
	global $wpdb, $cek_kunci, $gdwpm_opt_akun, $gdwpm_service, $gdwpm_apiConfig;
	$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, gdwpm_service akun, private key

	if(isset($_POST['folder_pilian'])){
	$gdwpm_apiConfig['use_objects'] = true;
	$gdwpm_service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
		$folder_pilian =  $_POST['folder_pilian'] ;
		$fld = $_POST['folder_pilian'];
		$daftar_berkas = $gdwpm_service->getFilesInFolder($fld);
		$daftarfile = $daftar_berkas[0];
		$i = $daftar_berkas[1];
		
		if($i <= 0){
			$daftarfile = '<p style="color:red; font-weight:bold">Your folder is empty.</p>';
		}
		
		echo '<div class="sukses"><p>Your current Folder ID is <strong>'.$fld.'</strong> and <strong>'.$i.' files</strong> detected in this folder.<select style="float:right;" id="pilihBaris" onchange="gantiBaris();"><option value="5">5 rows/page</option><option value="10" selected="selected">10 rows/page</option>   <option value="15">15 rows/page</option><option value="20">20 rows/page</option><option value="25">25 rows/page</option><option value="30">30 rows/page</option></select></p></div>';
			
		echo $daftarfile;
		
	}elseif(isset($_POST['folder_pilian_file_del'])){
	$gdwpm_apiConfig['use_objects'] = true;
	$gdwpm_service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
		$fld = $_POST['folder_pilian_file_del'];
		$daftar_berkas = $gdwpm_service->getFilesInFolder($fld, 'checkbox');
		$daftarfile = $daftar_berkas[0];
		$i = $daftar_berkas[1];
		
		if($i <= 0){
			$daftarfile = '<p style="color:red; font-weight:bold">Your folder is empty.</p>';
		}
		echo '<div class="sukses_del"><p>Your current Folder ID is <strong>'.$fld.'</strong> and <strong>'.$i.' files</strong> detected in this folder.</p></div>';
		//$daftarfile = str_replace('radio', 'checkbox', $daftarfile);
		//$daftarfile = str_replace('<div id="hasil">', '<div id="hasil_del">', $daftarfile);
		echo $daftarfile;
		
	}elseif(isset($_POST['masuk_perpus'])){
		$gdwpm_berkas_terpilih_arr = explode(' | ', $_POST['masuk_perpus']);
		gdwpm_ijin_masuk_perpus(sanitize_mime_type($gdwpm_berkas_terpilih_arr[0]), $gdwpm_berkas_terpilih_arr[1], $gdwpm_berkas_terpilih_arr[2], $gdwpm_berkas_terpilih_arr[3], $gdwpm_berkas_terpilih_arr[4]);
				
		echo '<strong>'.$gdwpm_berkas_terpilih_arr[1] . '</strong> has been added to your Media Library';
		
	}elseif(isset($_POST['gdwpm_cekbok_opsi_value'])){
		$nonce = $_REQUEST['gdwpm_override_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_override_dir' ) ) {
			die('<strong>Oops... failed!</strong>'); 
		} else {
			$folder_bawaan = preg_replace("/[^a-zA-Z0-9]+/", " ", $_POST['gdwpm_folder_opsi_value']);
			$folder_bawaan = sanitize_text_field($folder_bawaan);
			
			if(empty($folder_bawaan) && $_POST['gdwpm_cekbok_opsi_value'] == 'checked'){
				echo 'Folder name cannot be empty!';
			}else{
				$gdwpm_cekbok = array($_POST['gdwpm_cekbok_opsi_value'], $folder_bawaan, $_POST['gdwpm_cekbok_masukperpus_override']);
				update_option('gdwpm_override_dir_bawaan', $gdwpm_cekbok);	
				echo 'Option saved.';
			}
		}
	}elseif(isset($_POST['gdwpm_cekbok_opsi_dummy'])){
		$nonce = $_REQUEST['gdwpm_override_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_override_dir' ) ) {
			die('<strong>Oops... failed!</strong>'); 
		} else {			
				update_option('gdwpm_dummy_folder', $_POST['gdwpm_cekbok_opsi_dummy']);	
				echo 'Option saved.';
		}
	}elseif(isset($_REQUEST['gdwpm_tabulasi'])){
		if($_REQUEST['gdwpm_tabulasi'] == 'opsyen'){
			$nonce = $_REQUEST['gdwpm_tab_opsi_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'gdwpm_tab_opsi_key' ) ) {
				die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			} else {
				require_once 'google-drive-wp-media-options.php';
			}
		}elseif($_REQUEST['gdwpm_tabulasi'] == 'apidoku'){
			$nonce = $_REQUEST['gdwpm_tabulasi_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'gdwpm_tabulasi_ajax' ) ) {
				die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			} else {
				require_once 'google-drive-wp-media-documentation.php';
			}
		}elseif($_REQUEST['gdwpm_tabulasi'] == 'themeset'){
			$nonce = $_REQUEST['gdwpm_tabulasi_themeset_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'gdwpm_tabulasi_themeset_nonce' ) ) {
				die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			} else {
				require_once 'google-drive-wp-media-themes.php';
			}
		}else{
			die('<div class="error"><p>Oops.. something goes wrong!</p></div>'); 
		}
	}elseif(isset($_POST['gdwpm_opsi_theme_css'])){
		$nonce = $_REQUEST['gdwpm_theme_setting_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_theme_setting_nonce' ) ) {
			die('<strong>Oops... failed!</strong>'); 
		} else {
			
				update_option('gdwpm_nama_theme_css', $_POST['gdwpm_opsi_theme_css']);	
				
		}
	}else{
		$nonce = $_REQUEST['gdwpm_nonce_aplod_berkas'];
		if ( ! wp_verify_nonce( $nonce, 'gdwpm_satpam_aplod_berkas' ) ) {
			die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			
		} else {
			if (empty($_FILES) || $_FILES["file"]["error"]) {
				die('<div class="error"><p>Oops.. error, upload failed! '.$_FILES["file"]["error"].'</p></div>');
			}
			
			$gdwpm_service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
			
			if (isset($_REQUEST["gdpwm_nama_file"])) {
				$filename = $_REQUEST["gdpwm_nama_file"];
			} elseif (!empty($_FILES)) {
				$filename = $_FILES["file"]["name"];
			} else {
				$filename = uniqid("file_");
			}
			
			$targetDir = ini_get("upload_tmp_dir");
			$maxFileAge = 5 * 3600; // Temp file age in seconds
			// Create target dir
			if (!file_exists($targetDir)) {
				//@mkdir($targetDir);
				$targetDir = sys_get_temp_dir();
			}

			$filePath = $targetDir . DIRECTORY_SEPARATOR . $filename;

			// Chunking might be enabled
			$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
			$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('<div class="error"><p>Oops.. error. Failed to open temp directory.</p></div>');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);

			// Open temp file
			if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
				die('<div class="error"><p>Oops.. error. Failed to open output stream.</p></div>');
			}

			if (!empty($_FILES)) {
				if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
					die('<div class="error"><p>Oops.. error. Failed to move uploaded file.</p></div>');
				}

				// Read binary input stream and append it to temp file
				if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
					die('<div class="error"><p>Oops.. error. Failed to open input stream.</p></div>');
				}
			} else {        
				if (!$in = @fopen("php://input", "rb")) {
					die('<div class="error"><p>Oops.. error. Failed to open input stream.</p></div>');
				}
			}

			while ($buff = fread($in, 4096)) {
				fwrite($out, $buff);
			}

			@fclose($out);
			@fclose($in);

			// Check if file has been uploaded
			if (!$chunks || $chunk == $chunks - 1) {
				// Strip the temp .part suffix off 
				rename("{$filePath}.part", $filePath);

				$mime_berkas_arr = wp_check_filetype($filename);
				$mime_berkas = $mime_berkas_arr['type'];
				
				$folder_ortu = preg_replace("/[^a-zA-Z0-9]+/", " ", $_POST['gdpwm_nm_br']);
				$folder_ortu = sanitize_text_field($folder_ortu);
				$folderId = $_POST['gdpwm_nm_id'];
				$nama_polder = $_POST['gdpwm_nm_bks'];
				
				if($folder_ortu != ''){
					$folderId = $gdwpm_service->getFileIdByName( $folder_ortu );
					$nama_polder = $_POST['gdpwm_nm_br'];
				}
				
				$content = $_POST['gdpwm_sh_ds'];

				if( ! $folderId ) {
						$folderId = $gdwpm_service->createFolder( $folder_ortu );
						$gdwpm_service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
				}

				$fileParent = new Google_ParentReference();
				$fileParent->setId( $folderId );				
				
				$fileId = $gdwpm_service->createFileFromPath( $filePath, $filename, $content, $fileParent );
				$gdwpm_service->setPermissions( $fileId, 'me', 'reader', 'anyone' );
				
				$sukinfo = '';
				if(!empty($mime_berkas) && $_POST['gdpwm_med_ly'] == '1'){
					gdwpm_ijin_masuk_perpus($mime_berkas, $filename, $fileId, $content, $nama_polder);
					$sukinfo = ' and added into your Media Library';
				}
				echo '<div class="updated"><p>Great! File <strong>'.$fileId.'</strong> successfully uploaded to <strong>'.$nama_polder.'</strong>'.$sukinfo.'.</p></div>';
				@unlink($filePath);
			}
			die();
		}
	}
	die();
}

class GDWPMBantuan {
        
        protected $scope = array('https://www.googleapis.com/auth/drive');
        
        private $_service;
        
        public function __construct( $clientId, $serviceAccountName, $key ) {
                $client = new Google_Client();
                $client->setClientId( $clientId );
                
                $client->setAssertionCredentials( new Google_AssertionCredentials(
                                $serviceAccountName,
                                $this->scope,
                                @file_get_contents( $key ) )
                );
                
                $this->_service = new Google_DriveService($client);
        }
        
        public function __get( $name ) {
                return $this->_service->$name;
        }
        
		private function formatBytes($fileId, $precision = 2)
		{
			$file_siap = $this->_service->files->get($fileId);
			$file_ukuran = $file_siap->fileSize;
			if($file_ukuran > 0){
				$base = log($file_ukuran, 1024);
				$suffixes = array('', ' KB', ' MB', ' GB', ' TB');   
				return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
			}else{
				return $file_ukuran;
			}
		}

        public function getAbout( ) {
                return $this->_service->about->get();
        }
        
        public function buangFile( $fileId ) {
                $result = $this->_service->files->delete($fileId);
				if(empty($result)){
					return true;
				}else{
					return false;
				}
        }
        
        public function getNameFromId( $fileId ) {
			$file_proper = $this->_service->files->get($fileId);
			$file_name = $file_proper->title;
			return $file_name;
        }
		
        public function createFile( $name, $mime, $description, $content, Google_ParentReference $fileParent = null ) {
                $file = new Google_DriveFile();
                $file->setTitle( $name );
                $file->setDescription( $description );
                $file->setMimeType( $mime );
                
                if( $fileParent ) {
                        $file->setParents( array( $fileParent ) );
                }
                
                $createdFile = $this->_service->files->insert($file, array(
                                'data' => $content,
                                'mimeType' => $mime,
                ));
                
                return $createdFile['id'];
        }
        
        public function createFileFromPath( $path, $fileName, $description, Google_ParentReference $fileParent = null ) {
				$mimeType = wp_check_filetype($fileName);
                return $this->createFile( $fileName, $mimeType['type'], $description, file_get_contents($path), $fileParent );
        }
                
        public function createFolder( $name ) {
                return $this->createFile( $name, 'application/vnd.google-apps.folder', null, null);
        }
		
        public function setPermissions( $fileId, $value, $role = 'writer', $type = 'user' ) {
                $perm = new Google_Permission();
                $perm->setValue( $value );
                $perm->setType( $type );
                $perm->setRole( $role );
                
                $this->_service->permissions->insert($fileId, $perm);
        }
        
        public function getFileIdByName( $name ) {
                $files = $this->_service->files->listFiles();
                foreach( $files['items'] as $item ) {
                        if( $item['title'] == $name ) {
                                return $item['id'];
                        }
                }
                
                return false;
        }
		
		public function getFilesInFolder($folderId, $in_type = 'radio') {
			if($in_type == 'radio'){
				$div_id = 'hasil';
				$in_name = 'gdwpm_berkas_terpilih[]';
			}else{
				$div_id = 'hasil_del';
				$in_name = 'gdwpm_buang_berkas_terpilih[]';
			}
			$folder_proper = $this->_service->files->get($folderId);
			$folder_name = $folder_proper->title;
			$pageToken = NULL;

			do {
				$parameters = array();
				if ($pageToken) {
					$parameters['pageToken'] = $pageToken;
				}
				$parameters['maxResults'] = 1000;
				$children = $this->_service->children->listChildren($folderId, $parameters);
				$daftarfile =  '<div id="'.$div_id.'"><table id="box-table-a" summary="File Folder" class="paginasi"><thead><tr><th scope="col"><span class="ui-icon ui-icon-check"></span></th><th scope="col">File ID</th><th scope="col">Title</th><!--<th scope="col">Description</th>--><th scope="col">Size</th><th scope="col">Action</th></tr></thead>';
				$i = 0;
				foreach ($children->getItems() as $child) {
					$i++; if($i == 1 && $in_type == 'radio'){$checked = 'checked';}else{$checked = '';}
					$fileId = $child->getId();
					$file = $this->_service->files->get($fileId); //getDescription getMimeType
					$view = '<a href="https://docs.google.com/uc?id='.$fileId.'&export=download" title="Open link in a new window" target="_blank">Download</a>';
					if(strpos($file->mimeType, 'image') !== false){$view = '<a href="https://docs.google.com/uc?id='.$fileId.'&export=view" title="Open link in a new window" target="_blank">View</a>';}
					$daftarfile .=  '<tbody><tr><td><input type="'.$in_type.'" name="'.$in_name.'" value="'.$file->mimeType.' | '.$file->title.' | '.$fileId.' | '.$file->description.' | '.$folder_name.'" ' . $checked . '></td><td>'.$fileId.'</td>';
					$daftarfile .=  '<td title="' . $file->description . '"><img src="' . $file->iconLink . '" title="' . $file->mimeType . '"> ' . $file->title . '</td>';
					$daftarfile .=  '<!--<td>' . $file->description . '</td>-->';
					$daftarfile .=  '<td title="md5Checksum : ' . $file->md5Checksum . '">' . $this->formatBytes($fileId) . '</td>';
					$daftarfile .=  '<td>' . $view . ' | <a href="https://docs.google.com/file/d/'.$fileId.'/preview?TB_iframe=true&width=600&height=550" title="'.$file->title.' ('.$fileId.')" class="thickbox">Preview</a></td></tr>';
				}
				$pageToken = $children->getNextPageToken();
			} while ($pageToken);
			$daftarfile .=  '</tbody></table>';
			$daftarfile .= '</div>';
		
			return array($daftarfile, $i);
		}
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', ' KB', ' MB', ' GB', ' TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

function gdwpm_activate() {
	$gdwpm_opsi_kategori = get_option('gdwpm_opsi_kategori_dr_folder'); 	
	if(!$gdwpm_opsi_kategori){update_option('gdwpm_opsi_kategori_dr_folder', 'checked');}
	
	$upload_dir = wp_upload_dir();
	$nama_ploder = 'gdwpm_images';
	$fulldir = $upload_dir['basedir'] . '/' . $nama_ploder;
	$gdwpm_images_url = $upload_dir['baseurl'] . '/' . $nama_ploder;
	$gdwpm_homebase_arr = parse_url($gdwpm_images_url);
	$gdwpm_homebase = $gdwpm_homebase_arr['scheme'] . '://' . $gdwpm_homebase_arr['host'] . '/';
	$gdwpm_images_url = str_replace($gdwpm_homebase, '', $upload_dir['baseurl'] . '/' . $nama_ploder);
$tulis_htacc = <<<HTAC
RewriteEngine on
RewriteBase /
RewriteCond %{QUERY_STRING} !^imgid= [NC]
RewriteRule ^(.*)$ {$gdwpm_images_url}/index.php?imgid=$1 [L,NC,QSA]
HTAC;
$tulis_php = <<<PHPG
<?php
if (isset(\$_GET['imgid'])){
	\$gdwpm_ekst_gbr = explode('.', \$_GET['imgid']);
	if(\$gdwpm_ekst_gbr[1] == 'png' || \$gdwpm_ekst_gbr[1] == 'gif' || \$gdwpm_ekst_gbr[1] == 'bmp'){
		header("Content-Type: image/" . \$gdwpm_ekst_gbr[1]);
	}else{
		header("Content-Type: image/jpg");
	}
	\$gdurl = "https://docs.google.com/uc?id=" . \$gdwpm_ekst_gbr[0] . "&export=view";
	@readfile(\$gdurl);
}
?>
PHPG;
	if(!is_dir($fulldir)){
		wp_mkdir_p($fulldir);
	}
	file_put_contents($fulldir . '/index.php', $tulis_php, LOCK_EX);
	file_put_contents($fulldir . '/.htaccess', $tulis_htacc, LOCK_EX);
}
register_activation_hook( __FILE__, 'gdwpm_activate' );
?>