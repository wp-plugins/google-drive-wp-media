<?php
/*
Plugin Name: Google Drive WP Media
Plugin URI: http://wordpress.org/plugins/google-drive-wp-media/
Description: Google Drive on Wordpress Media Publishing. Upload files to Google Drive directly from WordPress blog.
Author: Moch Amir
Author URI: http://www.mochamir.com/
Version: 1.0
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
define( 'VERSI_GDWPM', '1.0' );
define( 'MY_TEXTDOMAIN', 'gdwpm' );

require_once 'gdwpm-api/Google_Client.php';
require_once 'gdwpm-api/contrib/Google_DriveService.php';

$gdwpm_override_optional = get_option('gdwpm_override_dir_bawaan'); // cekbok, polder

if($gdwpm_override_optional[0] == 'checked' && !empty($gdwpm_override_optional[1])){

	add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );

	function custom_upload_filter( $file ){
		global $cek_kunci, $gdwpm_opt_akun, $service, $gdwpm_apiConfig, $gdwpm_override_optional;
		
		$filename = $file['name'];
		$path = $file['tmp_name'];
		$mime_berkas = $file['type'];
		//$mime_berkas = sanitize_mime_type($mime_berkas);
		$folder_ortu = preg_replace("/[^a-zA-Z0-9]+/", " ", $gdwpm_override_optional[1]);
		$folder_ortu = sanitize_text_field($folder_ortu);
		
		if($folder_ortu != ''){
			$folderId = $service->getFileIdByName( $folder_ortu );
		}
		
		$content = '';

		if( ! $folderId ) {
			$folderId = $service->createFolder( $folder_ortu );
			$service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
		}

		$fileParent = new Google_ParentReference();
		$fileParent->setId( $folderId );
		$fileId = $service->createFileFromPath( $path, $filename, $content, $fileParent );
		$service->setPermissions( $fileId, 'me', 'reader', 'anyone' );
		
		$sukinfo = '';
		if(!empty($mime_berkas)){// && isset($_POST['masukperpus'])){
			gdwpm_ijin_masuk_perpus($mime_berkas, $filename, $fileId, $content);
			$sukinfo = ' and added into your Media Library';
		}
		
		echo '<div class="updated"><p>Done! <strong>'.$fileId.'</strong> successfully uploaded into folder <strong>'.$folder_ortu.'</strong>'.$sukinfo.'.</p></div>';
		exit();
	}
	
}

//////////////// HALAMAN MEDIA MENU ///////////////
add_action( 'admin_menu', 'gdwpm_menu_media' );
function gdwpm_menu_media() {
	add_media_page( NAMA_GDWPM, NAMA_GDWPM, 'read', ALMT_GDWPM, 'gdwpm_halaman_media' );
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
	wp_enqueue_script('jquery');                    
	wp_enqueue_script('jquery-ui-core');       
	wp_enqueue_script('jquery-ui-accordion');         
	wp_enqueue_script('jquery-ui-dialog');   
	wp_enqueue_script('plupload-all');          
	wp_enqueue_script('jquery-ui-tooltip');              
	wp_enqueue_script('jquery-ui-tabs');        
	wp_enqueue_script( 'ajax-script', plugins_url( '/js/sekrip.js', __FILE__ ), array('jquery') );
	
	wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'gdwpm_sekrip_buat_mimin' );

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
	global $cek_kunci, $gdwpm_opt_akun, $service, $gdwpm_apiConfig;
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
				echo '<div class="updated"><p>Great! All API settings successfuly saved.</p></div>';
			}else{
				echo '<div class="error"><p>All fields are required.</p></div>';
			}
		}
	}
$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, service akun, private key
if($gdwpm_opt_akun){
$cek_kunci = 'false';
//$gdwpm_apiConfig['use_objects'] = true;
$service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
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
				$folderId = $service->getFileIdByName( $gawe_folder );

				if( ! $folderId ) {
					$folderId = $service->createFolder( $gawe_folder );
					$service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
					echo '<div class="updated"><p>Great! Folder name <strong>'.$gawe_folder.'</strong> successfully created.</p></div>';
				}else{
					echo '<div class="error"><p>Folder '.$gawe_folder.' already exist</p></div>';
				}
			}else{
					echo '<div class="error"><p>Folder name cannot be empty!</p></div>';
			}
		}
}

$gdwpm_theme_css_pilian = get_option('gdwpm_nama_theme_css');
if(empty($gdwpm_theme_css_pilian)){$gdwpm_theme_css_pilian = 'smoothness';}
?>
<link id='gdwpm_cs_style' type='text/css' rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/<?php echo $gdwpm_theme_css_pilian;?>/jquery-ui.css">
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
 
    jQuery( "#tabs" ).tabs();
  
	jQuery( "#doktabs" ).tabs({
		beforeLoad: function( event, ui ) {
			ui.jqXHR.error(function() {
				ui.panel.html(
				"Opening tab, please wait.." );
			});
		}
    });
	
		jQuery( "#gdwpm-settingtabs" ).tabs({
      beforeLoad: function( event, ui ) {
        ui.jqXHR.error(function() {
          ui.panel.html(
            "Opening tab, please wait.." );
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
			$files = $service->files->listFiles($parameters);

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
		<div id="tabs" style="margin:0 -12px 0 -12px;">
		<ul>
 <?php if (!empty($foldercek)) { ?>
			<li><a href="#tabs-1"><span style="float:left" class="ui-icon ui-icon-script"></span>&nbsp;File & Folder List</a></li>
			<li><a href="#tabs-2"><span style="float:left" class="ui-icon ui-icon-star"></span>&nbsp;Upload</a></li>
			<li><a href="#tabs-3"><span style="float:left" class="ui-icon ui-icon-clipboard"></span>&nbsp;Option</a></li>
			<li><a href="#tabs-4"><span style="float:left" class="ui-icon ui-icon-heart"></span>&nbsp;Account Information</a></li>
<?php }else{ ?>
			<li><a href="#tabs-5"><span style="float:left" class="ui-icon ui-icon-folder-collapsed"></span>&nbsp;Create Folder</a></li>
<?php } ?>
		</ul>
 <?php if (!empty($foldercek)) { ?>
			<div id="tabs-1">
				<p>Select folder: <?php echo $folderpil; ?> <button id="golek_seko_folder" name="golek_seko_folder"><?php _e('Get Files') ?></button> &nbsp;&nbsp;
					<span id="gdwpm_info_folder_baru" style="display:none;">
						You have created at least one folder.
						<button id="gdwpm_tombol_info_folder_baru" name="gdwpm_tombol_info_folder_baru" onclick="gdwpm_reload_hal()"><?php _e('Reload Now') ?></button>
					</span>
				</p>
<script>
function gdwpm_reload_hal()
{
	location.reload();
}
</script>
				<p>
					<span class="sukses">Please select folder and click Get Files, to show all files belongs to it.<br /><br />
						Link URL of your file: https://docs.google.com/uc?id=<b>YOUR-FILE-ID-HERE</b>&export=view 
						<br />
						Google Docs Viewer: https://docs.google.com/viewer?url=https%3A%2F%2Fdocs.google.com%2Fuc%3Fid%3D<b>YOUR-FILE-ID-HERE</b>%26export%3Dview
						<?php
							$ebot = $service->getAbout();
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
				<?php
echo $daftarfile;
?> 
				<p></p> 
			</div>
			<div id="tabs-2">
				<p>
					Select folder: <?php echo str_replace('folder_pilian', 'folder_pilian_aplod', $folderpil); ?> or create a new folder: <input type="text" id="gdwpm_folder_anyar" name="gdwpm_folder_anyar" value="" size="37" placeholder="Ignore this field if you use existing folder">
				</p>
					<!--<p>Short Description: <input type="text" name="gdwpm_aplod_deskrip" value="" size="65" placeholder="Optional"></p>-->
				<p>
					<ul>
						<!--<li>Your hosting maximum upload size allowed: <?php //$size_allowed = wp_max_upload_size(); echo $size_allowed / 1048576 ;?>MB. 
							<br />&nbsp;<dfn>It should be okay if your files more than maximum allowed size, since this plugin handle upload with chunks (700kb/chunk).</dfn>
						--></li>
						<li>Accepted Media MIME types: */*
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
			<div id="tabs-3">
				<?php 
				$gdwpm_override = get_option('gdwpm_override_dir_bawaan'); // cekbok, polder
				$gdwpm_override_nonce = wp_create_nonce( "gdwpm_override_dir" );
				?>
				<p>
					<a onclick="gdwpm_cekbok_opsi_override_eksen();"><input type='checkbox' id='gdwpm_cekbok_opsi_override' name='gdwpm_cekbok_opsi_override' value='1' <?php echo $gdwpm_override[0];?>/></a> 
					Google Drive as Default Media Upload Storage. (experimental)<br />
					&nbsp;<dfn>This option will change your default upload dir (<?php $def_upload_dir = wp_upload_dir(); echo $def_upload_dir['baseurl'];?>) to Google Drive. 
					That's mean, when you upload files through default uploader (eg: Media >> Add New) it will automatically uploading your files to Google Drive.</dfn>
				</p>
				<div id="gdwpm_folder_opsi_override_eksen" style="margin-left:15px;display: <?php if ($gdwpm_override[0] == 'checked') { echo 'block;';}else{echo 'none;';}?>">
					<p>
						Google Drive folder name<br />
						<input type="text" id="gdwpm_folder_opsi_override_teks" name="gdwpm_folder_opsi_override_teks" value="<?php echo $gdwpm_override[1];?>" size="35" placeholder="Required (auto create if not exist)" />	
					</p>					
				</div>
				<button onclick="gdwpm_tombol_opsi_override_eksen();" id="gdwpm_tombol_opsi_override" name="gdwpm_tombol_opsi_override">Save</button>&nbsp;&nbsp;&nbsp; 
				<span style="display: none" id="gdwpm_cekbok_opsi_override_gbr">
					<img src="<?php echo plugins_url( '/images/animation/loading-bar-image.gif', __FILE__ );?>" />
				</span>
				<span id="gdwpm__cekbok_opsi_override_info"></span>
<script type="text/javascript">	
function gdwpm_cekbok_opsi_override_eksen(){
	if (jQuery('#gdwpm_cekbok_opsi_override').prop('checked')){
		document.getElementById("gdwpm_folder_opsi_override_eksen").style.display = "block";
	}else{
		document.getElementById("gdwpm_folder_opsi_override_eksen").style.display = "none";
	}
}
function gdwpm_tombol_opsi_override_eksen(){
	if (jQuery('#gdwpm_cekbok_opsi_override').prop('checked')){
		var gdwpm_cekbok = 'checked';
	}else{
		var gdwpm_cekbok = '';
	}
		jQuery("#gdwpm_cekbok_opsi_override_gbr").show();
		jQuery('#gdwpm__cekbok_opsi_override_info').empty();
		var data = {
			action: 'gdwpm_on_action',
			gdwpm_override_nonce: '<?php echo $gdwpm_override_nonce; ?>',
			gdwpm_cekbok_opsi_value: gdwpm_cekbok ,
			gdwpm_folder_opsi_value: jQuery('#gdwpm_folder_opsi_override_teks').val()
		};
		jQuery.post(ajax_object.ajax_url, data, function(hasil) {
			jQuery('#gdwpm_cekbok_opsi_override_gbr').hide();
			jQuery('#gdwpm__cekbok_opsi_override_info').html(hasil);
		});
}
</script>		
			</div>
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
<?php }else{ ?>
			<div id="tabs-5">
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
		
		<div style="margin-left:15px;">
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
		</p>
		<ul>
			<li>Google Drive API & products are owned by Google inc.</li> 
			<li>Table Style credit to R. Christie (SmashingMagazine).</li> 
			<li>DriveServiceHelper Class credit to Lukasz Kujawa.</li> 
			<li>Loading images animation credit to mytreedb project.</li> 
			<li>JQuery Upload credit to PLUpload by Moxiecode Systems AB.</li> 
			<li>JQuery User Interface credit to JQueryUI.</li> 
		</ul>
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
    It seems your api keys haven't properly saved yet. This plugin requires api key to authorize your drive.
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
  jQuery(function() {
    jQuery( "#gdwpm_pringatan_versi_php" ).dialog({
      autoOpen: <?php echo $gdwpm_cek_php;?>,
      modal: true,
      buttons: {
        Ok: function() {
          jQuery( this ).dialog( "close" );
        }
      }
    });
    jQuery( "#dialog-message" ).dialog({
      autoOpen: <?php echo $cek_kunci;?>,
      modal: true,
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
		
    jQuery( "#gdwpm_tombol_opsi_override" )
      .button({
      icons: {
        primary: "ui-icon-disk"
      }
    })
	
  });
</script>
<?php

}

function gdwpm_ijin_masuk_perpus($jenis_berkas, $nama_berkas, $id_berkas, $deskrip_berkas){
	// ADD TO LIBRARY
	$meta = array('aperture' => 0, 'credit' => '', 'camera' => '', 'caption' => $nama_berkas, 'created_timestamp' => 0, 'copyright' => '',  
				'focal_length' => 0, 'iso' => 0, 'shutter_speed' => 0, 'title' => $nama_berkas); 
	$attachment = array( 'post_mime_type' => $jenis_berkas, 'guid' => 'G_D_W_P_M-file_ID/'.$id_berkas,
				'post_parent' => 0,	'post_title' => $nama_berkas, 'post_content' => $deskrip_berkas);
	$attach_id = wp_insert_attachment( $attachment, 'G_D_W_P_M-file_ID/'.$id_berkas, 0 );
	$metadata = array("image_meta" => $meta, "width" => '', "height" => '', "gdwpm"=>TRUE); 
    wp_update_attachment_metadata( $attach_id,  $metadata);	
}

///////////// AJAX EKSYEN /////////////// ajax admin url =====>  gdwpm_on_action
add_action( 'wp_ajax_gdwpm_on_action', 'gdwpm_action_callback' );
function gdwpm_action_callback() {
	global $wpdb, $cek_kunci, $gdwpm_opt_akun, $service, $gdwpm_apiConfig;
	$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, service akun, private key

	if(isset($_POST['folder_pilian'])){
	$gdwpm_apiConfig['use_objects'] = true;
	$service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
		$folder_pilian =  $_POST['folder_pilian'] ;
		$fld = $_POST['folder_pilian'];
		$daftar_berkas = $service->getFilesInFolder($fld);
		$daftarfile = $daftar_berkas[0];
		$i = $daftar_berkas[1];
		
		if($i <= 0){
			$daftarfile = '<p style="color:red; font-weight:bold">Your folder is empty.</p>';
		}
		echo '<div class="sukses"><p>Your current Folder ID is <strong>'.$fld.'</strong> and <strong>'.$i.' files</strong> detected in this folder.</p></div>';
			
		echo $daftarfile;
		
	}elseif(isset($_POST['masuk_perpus'])){
		$gdwpm_berkas_terpilih_arr = explode(' | ', $_POST['masuk_perpus']);
		gdwpm_ijin_masuk_perpus(sanitize_mime_type($gdwpm_berkas_terpilih_arr[0]), $gdwpm_berkas_terpilih_arr[1], $gdwpm_berkas_terpilih_arr[2], $gdwpm_berkas_terpilih_arr[3]);
				
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
				$gdwpm_cekbok = array($_POST['gdwpm_cekbok_opsi_value'], $folder_bawaan);
				update_option('gdwpm_override_dir_bawaan', $gdwpm_cekbok);	
				echo 'Option saved.';
			}
		}
	}elseif(isset($_REQUEST['gdwpm_tabulasi'])){
		if($_REQUEST['gdwpm_tabulasi'] == 'apidoku'){
			$nonce = $_REQUEST['gdwpm_tabulasi_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'gdwpm_tabulasi_ajax' ) ) {
				die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			} else {
				require_once 'google-drive-wp-media-documentation.php';
			}
		}
		if($_REQUEST['gdwpm_tabulasi'] == 'themeset'){
			$nonce = $_REQUEST['gdwpm_tabulasi_themeset_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'gdwpm_tabulasi_themeset_nonce' ) ) {
				die('<div class="error"><p>Oops.. security check is not ok!</p></div>'); 
			} else {
				require_once 'google-drive-wp-media-themes.php';
			}
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
			
			$service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
			
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
					$folderId = $service->getFileIdByName( $folder_ortu );
					$nama_polder = $_POST['gdpwm_nm_br'];
				}
				
				$content = $_POST['gdpwm_sh_ds'];

				if( ! $folderId ) {
						$folderId = $service->createFolder( $folder_ortu );
						$service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
				}

				$fileParent = new Google_ParentReference();
				$fileParent->setId( $folderId );				
				
				$fileId = $service->createFileFromPath( $filePath, $filename, $content, $fileParent );
				$service->setPermissions( $fileId, 'me', 'reader', 'anyone' );
				
				$sukinfo = '';
				if(!empty($mime_berkas) && $_POST['gdpwm_med_ly'] == '1'){
					gdwpm_ijin_masuk_perpus($mime_berkas, $filename, $fileId, $content);
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
        
        public function getAbout( ) {
                return $this->_service->about->get();
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
		
		public function getFilesInFolder($folderId) {
			$pageToken = NULL;

			do {
				$parameters = array();
				if ($pageToken) {
					$parameters['pageToken'] = $pageToken;
				}
				$children = $this->_service->children->listChildren($folderId, $parameters);
				$daftarfile =  '<div id="hasil"><table id="box-table-a" summary="File Folder"><thead><tr><th scope="col"><span class="ui-icon ui-icon-check"></span></th><th scope="col">File ID</th><th scope="col">Title</th><!--<th scope="col">Description</th>--><th scope="col">Shared</th><th scope="col">Option</th></tr></thead>';
				$i = 0;
				foreach ($children->getItems() as $child) {
					$i++; if($i == 1){$checked = 'checked';}else{$checked = '';}
					$fileId = $child->getId();
					$file = $this->_service->files->get($fileId); //getDescription getMimeType
					$view = '<a href="https://docs.google.com/uc?id='.$fileId.'&export=download" title="Open link in a new window" target="_blank">Download</a>';
					if(strpos($file->mimeType, 'image') !== false){$view = '<a href="https://docs.google.com/uc?id='.$fileId.'&export=view" title="Open link in a new window" target="_blank">View</a>';}
					$daftarfile .=  '<tbody><tr><td><input type="radio" name="gdwpm_berkas_terpilih[]" value="'.$file->mimeType.' | '.$file->title.' | '.$fileId.' | '.$file->description.'" ' . $checked . '></td><td>'.$fileId.'</td>';
					$daftarfile .=  '<td title="' . $file->description . '"><img src="' . $file->iconLink . '" title="' . $file->mimeType . '"> ' . $file->title . '</td>';
					$daftarfile .=  '<!--<td>' . $file->description . '</td>-->';
					$daftarfile .=  '<td title="md5Checksum : ' . $file->md5Checksum . '">' . str_replace('1', 'Yes', $file->shared) . '</td>';
					$daftarfile .=  '<td>' . $view . '</td></tr>';
				}
				$pageToken = $children->getNextPageToken();
			} while ($pageToken);
			$daftarfile .=  '</tbody></table>';
			$daftarfile .= '</div>';
		
			return array($daftarfile, $i);
		}
}

function gdwpm_cek_versi(){
	$gdwpm_svn_tags = @file_get_contents('http://plugins.svn.wordpress.org/google-drive-wp-media/tags/');
	$gdwpm_svn_tags = explode('<li><a href="', $gdwpm_svn_tags);
	$gdwpm_versi_baru = $gdwpm_svn_tags[count($gdwpm_svn_tags) - 1];
	$gdwpm_versi_baru = substr($gdwpm_versi_baru,0 , strpos($gdwpm_versi_baru, '/'));
	return $gdwpm_versi_baru;
}
?>