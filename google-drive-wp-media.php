<?php
/*
Plugin Name: Google Drive WP Media
Plugin URI: http://www.mochamir.com/
Description: Google Drive on Wordpress Media Publishing.
Author: Moch Amir
Author URI: http://www.mochamir.com/
Version: 0.1
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

define( 'NAMA_GDWPM', 'Google Drive WP Media' );
define( 'ALMT_GDWPM', 'google-drive-wp-media' );
define( 'VERSI_GDWPM', '0.1' );
define( 'GDWPM', 'gdwpm' );

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

$cek_kunci = 'true'; // kosong
	if ($_POST['simpen_gdwpm_akun'])
	{
		if (!EMPTY($_POST['gdwpm_imel']) && !EMPTY($_POST['gdwpm_klaen_aidi']) && !EMPTY($_POST['gdwpm_nama_service']) && !EMPTY($_POST['gdwpm_kunci_rhs']))
		{
			$gdwpm_opt = array($_POST['gdwpm_imel'], $_POST['gdwpm_klaen_aidi'], $_POST['gdwpm_nama_service'], $_POST['gdwpm_kunci_rhs']);
				update_option('gdwpm_akun_opt', $gdwpm_opt);
				echo '<div class="updated"><p>Great! All API settings successfuly saved.</p></div>';
		}else{
			echo '<div class="error"><p>All fields are required.</p></div>';
		}
	}
$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, service akun, private key
if($gdwpm_opt_akun){
$cek_kunci = 'false';
//$apiConfig['use_objects'] = true;
$service = new GDWPMBantuan( $gdwpm_opt_akun[1], $gdwpm_opt_akun[2], $gdwpm_opt_akun[3] );
}


	if ($_POST['simpen_gawe_folder'])
	{
		if (!EMPTY($_POST['gdwpm_gawe_folder']))
		{
			$gawe_folder = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['gdwpm_gawe_folder']);
			$gawe_folder = sanitize_text_field($gawe_folder);
			$folderId = $service->getFileIdByName( $gawe_folder );

			if( ! $folderId ) {
					$folderId = $service->createFolder( $gawe_folder );
					$service->setPermissions( $folderId );
				echo '<div class="updated"><p>Great! Folder with name '.$gawe_folder.' successfuly created.</p></div>';
			}else{
				echo '<div class="error"><p>Folder '.$gawe_folder.' already exist</p></div>';
			}
		}else{
			echo '<div class="error"><p>Folder name cannot be empty!</p></div>';
		}
	}
if(isset($_FILES["gdwpm_aplod_file"]))
{
	echo '<script>$(\'#gdwpm_loading_gbr\').hide();	$(\'#gdwpm_masuk_perpus_teks\').hide();	$(\'#gdwpm_add_to_media_gbr\').hide();</script>';
	if($_FILES["gdwpm_aplod_file"]["error"] > 0)
	{
		echo '<div class="error"><p>Oops.. Upload failed! error: '. $_FILES["gdwpm_aplod_file"]["error"] .'</p></div>';
	}else{
       $filename = $_FILES['gdwpm_aplod_file']['name'];
       $path = $_FILES['gdwpm_aplod_file']['tmp_name'];
       $mime_berkas = $_FILES['gdwpm_aplod_file']['type'];
       //$mime_berkas = sanitize_mime_type($mime_berkas);
		$folder_ortu = preg_replace("/[^a-zA-Z0-9]+/", " ", $_POST['gdwpm_folder_anyar']);
		$folder_ortu = sanitize_text_field($folder_ortu);
		$folderId = $_POST['folder_pilian_aplod'];
		if($folder_ortu != ''){
			$folderId = $service->getFileIdByName( $folder_ortu );
		}
		$content = sanitize_text_field($_POST['gdwpm_aplod_deskrip']);

		if( ! $folderId ) {
				$folderId = $service->createFolder( $folder_ortu );
				$service->setPermissions( $folderId, $gdwpm_opt_akun[0] );
		}

		$fileParent = new Google_ParentReference();
		$fileParent->setId( $folderId );
		$fileId = $service->createFileFromPath( $path, $filename, $content, $fileParent );
		$service->setPermissions( $fileId, 'me', 'reader', 'anyone' );
		$sukinfo = '';
		if(!empty($mime_berkas) && isset($_POST['masukperpus'])){
			gdwpm_ijin_masuk_perpus($mime_berkas, $filename, $fileId, $content);
			$sukinfo = ' and added into your Media Library';
		}
		echo '<div class="updated"><p>Great! File <strong>'.$fileId.'</strong> successfuly uploaded'.$sukinfo.'.</p></div>';
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
	wp_enqueue_script('jquery');                    // Enque jQuery http://codex.wordpress.org/Function_Reference/wp_register_script
	wp_enqueue_script('jquery-ui-core');            // Enque jQuery UI Core
	wp_enqueue_script('jquery-ui-accordion');            // Enque jQuery UI Tabs
	wp_enqueue_script('jquery-ui-dialog'); 
	wp_enqueue_script('jquery-ui-tooltip');              
	wp_enqueue_script('jquery-ui-tabs');  
	wp_enqueue_script('jquery');                    // Enque jQuery http://codex.wordpress.org/Function_Reference/wp_register_script
	wp_enqueue_script('jquery-ui-core');            // Enque jQuery UI Core
	wp_enqueue_script( 'ajax-script', plugins_url( '/js/sekrip.js', __FILE__ ), array('jquery') );

	// in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
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
	global $cek_kunci, $gdwpm_opt_akun, $service, $apiConfig;

$gdwpm_skriphed = <<<WER
<script src="http://code.jquery.com/jquery-1.9.1.js"></script><link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
  <script>
  $(function() {
    $( "#dialog-message" ).dialog({
      autoOpen: {$cek_kunci},
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });
  $(function() {
    var icons = {
      header: "ui-icon-wrench",
      activeHeader: "ui-icon-lightbulb"
    };
    $( "#accordion" ).accordion({
      heightStyle: "content",
      icons: icons
    });
  $(function() {
    var tooltips = $( "[title]" ).tooltip();
  });
  });
  </script>
   <script>
  $(function() {
    $( "#tabs" ).tabs();
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
WER;
	echo $gdwpm_skriphed;

	$apiConfig['use_objects'] = true;
	
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
			echo '<div class="error">An error occurred: ' . $e->getMessage() . '</div>';
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
global $cek_kunci;
if($cek_kunci == 'false'){ ?>
		<h3>Management</h3>
	<div>
		<div id="tabs" style="margin:0 -12px 0 -12px;">
		<ul>
 <?php if (!empty($foldercek)) { ?>
			<li><a href="#tabs-1">File & Folder List</a></li>
			<li><a href="#tabs-2">Upload</a></li>
			<li><a href="#tabs-3">Setting</a></li>
<?php }else{ ?>
			<li><a href="#tabs-4">Create Folder</a></li>
<?php } ?>
		</ul>
 <?php if (!empty($foldercek)) { ?>
			<div id="tabs-1">
				<p>Select folder: <?php echo $folderpil; ?> <button id="golek_seko_folder" name="golek_seko_folder" class="button-primary"><?php _e('Get Files') ?></button></p> 
				<p><span class="sukses">Please select folder and click Get Files, to show all files belongs to it.<br />
				Link URL of your file: https://docs.google.com/uc?id=<b>YOUR-FILE-ID-HERE</b>&export=view </span></p>		
<div id="gdwpm_loading_gbr">
  <center><img src="https://docs.google.com/uc?id=0B2Or6CnfqndYdXoyZEk5eFczTXc&export=view" /><br />Please wait...</center>
</div><!-- 0B2Or6CnfqndYdXNET3pfamxFcGM  0B2Or6CnfqndYNHJlQUF1SWhlY2c 0B2Or6CnfqndYdmROcUNwQlNobU0 0B2Or6CnfqndYdXoyZEk5eFczTXc 0B2Or6CnfqndYZWlmZ3RZdmZrODA -->
				<div id="hasil"></div><div id="gdwpm_masuk_perpus_teks"><p>Pick a file to include it in the Media Library.</p>
				<p><button id="gdwpm_berkas_masuk_perpus" name="gdwpm_berkas_masuk_perpus" class="button-primary">Add to Media Library</button>&nbsp;&nbsp;&nbsp; 
				<span id="gdwpm_add_to_media_gbr"><img src="https://docs.google.com/uc?id=0B2Or6CnfqndYZUpXcmdOeGIxZU0&export=view" /></span>
				<span id="gdwpm_info_masuk_perpus"></span></p></div>
				<?php
echo $daftarfile;
?> 
				</form>
				<p></p> 
			</div>
			<div id="tabs-2">
				<form enctype="multipart/form-data" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<p>Select folder: <?php echo str_replace('folder_pilian', 'folder_pilian_aplod', $folderpil); ?> or create a new folder: <input type="text" name="gdwpm_folder_anyar" value="" size="37" placeholder="Ignore this field if you use existing folder"></p>
				<p>Short Description: <input type="text" name="gdwpm_aplod_deskrip" value="" size="65" placeholder="Optional"></p>
				<p><ul><li>Maximum file size: 10GB.</li><li>Accepted Media MIME types: */*</li></ul> </p>
				<p>Choose your file here:
				<input name="gdwpm_aplod_file" type="file"/></p> 
				<input type='checkbox' name='masukperpus' value='1' checked /> Add to Media Library.<!-- (Image only: <i>*.jpg, *.jpeg, *.png, & *.gif</i>)--><p>
				
<input type="hidden" id="nama_folder_pilian_aplod" value="" />
				<input type="submit" onclick="document.getElementById('nama_folder_pilian_aplod').value=document.getElementById('folder_pilian_aplod').options[document.getElementById('folder_pilian_aplod').selectedIndex].text" 
				name="gdwpm_aplot_masuk" class="button-primary" value="Upload to Google Drive"/></p>
				</form>
			</div>
			<div id="tabs-3">
				<p>Nothing found.</p><p>option to remote images</p>
			</div>
<?php }else{ ?>
			<div id="tabs-4">
				<p>No folder exist/detected in your drive.</p>
				<p>This plugin requires at least 1 folder to store your files.</p>
				<form name="gdwpm_form_gawe_folder" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<p>Folder Name: <input type="text" name="gdwpm_gawe_folder" value=""> <input name="simpen_gawe_folder" type="submit" class="button-primary" value="Create Folder"/></p>
				</form>
			</div>
<?php } ?>
		</div>
	</div>
<?php } ?>
		<h3>Google Drive API Key Setting</h3>
	<div>
		<div style="margin-left:15px;">
			<table>
				<tr>
					<td width="30%">
			<form name="gdwpm_isi_akun" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
						Google Email: 
					</td>
					<td>
						<input type="text" name="gdwpm_imel" value="<?php echo $gdwpm_opt_akun[0];?>"  title="Use this Email to share with. ex: youremail@gmail.com" size="55">
					</td>
				</tr>
				<tr>
					<td>
						Client ID: 
					</td>
					<td>
						<input type="text" name="gdwpm_klaen_aidi" value="<?php echo $gdwpm_opt_akun[1];?>"  title="ex: 123456789.apps.googleusercontent.com" size="55">
					</td>
				</tr>
				<tr>
					<td>
						Service Account Name: 
					</td>
					<td>
						<input type="text" name="gdwpm_nama_service" value="<?php echo $gdwpm_opt_akun[2];?>"  title="ex: 123456789@developer.gserviceaccount.com" size="55">
					</td>
				</tr>
				<tr>
					<td>
				Private Key Url Path: 
					</td>
					<td>
						<input type="text" name="gdwpm_kunci_rhs" value="<?php echo $gdwpm_opt_akun[3];?>"  title="ex: http://yourdomain.com/path/to/123xxx-privatekey.p12." size="55">
					</td>
				</tr>
			</table>
		</div>
				<br />
			<p style="margin-left:35px;"><input type="submit" name="simpen_gdwpm_akun" class="button-primary" value="<?php _e('Save') ?>" /></p>		
			</form>
	</div>
		<h3>Documentation</h3>
	<div>
		<p>How to generate your Google Drive API Key.</p>
		<p>1. Go to https://code.google.com/apis/console/, sign in with your google account. Click Create Project. 
		<br /><img src="https://docs.google.com/uc?id=0B4hkh-PEv0ZZb1J1aV9QX3NiZ0U&export=view"></p>
		<p>2. Now click Drive API button to <b>enabling</b> Drive API Services.
		<br /><img src="https://docs.google.com/uc?id=0B4hkh-PEv0ZZRVJzZHFENFA3bXM&export=view"></p>
		<p>3. On the left pane of your screen click 'Credentials', click 'CREATE NEW CLIENT ID'. On the popup, pick <b>Service Account</b>, cick 'Create Client ID'.
		<br /><img src="https://docs.google.com/uc?id=0B4hkh-PEv0ZZV1VZaXlxZkdLSFU&export=view"></p>
		<p>4. Your *.-privatekey.p12 file will automaticaly downloaded, save it.</p>
		<p>5. Now you have a Client ID, Email address (Service Account Name), and *.-privatekey.p12 file that recently downloaded.</p>
		<p>6. Upload your *.-privatekey.p12 file into your web host and remember its Url path.</p>
		<p><br /></p>
		<p>File Permissions uploded by this plugin are automatically set to public, which everybody can view or download your files.</p>
		<p>For more info, please visit https://developers.google.com/drive/web/</p>
	</div>
		<h3>About</h3>
	<div>
		<p><?php echo NAMA_GDWPM;?> current installed version is <?php echo VERSI_GDWPM;?></p>
		<p><?php echo NAMA_GDWPM;?> was created and developed by Moch Amir. <br />It is licensed as Free Software under GNU General Public License 2 (GPL 2).
You can find more information about <?php echo NAMA_GDWPM;?> on its page in the WordPress Plugin Directory. Please rate and review the plugin in the WordPress Plugin Directory.
</p>
<p>If you have any question, there's support forum provided in its page too. Before asking for support, please carefully read the Frequently Asked Questions, where you will find answers to the most common questions, and search through the forums.
</p>
<p>If you do not find an answer there, please open a new thread in the WordPress Support Forums.</p>
<p>Credits:</p>
		<ul>
			<li>Google Drive API & products are owned by Google inc.</li> 
			<li>Table Style credit to R. Christie (SmashingMagazine).</li> 
			<li>DriveServiceHelper Class credit to Lukasz Kujawa.</li> 
		</ul>
		<p style="margin-top:57px;">Donations and good ratings encourage me to further develop the plugin and to provide countless hours of support. <br />Any amount is appreciated! </p>
		<p>Thank You!</p>
</p> 
	</div>
</div>
<div id="dialog-message" title="Warning">
  <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
    It seems your api key haven't properly saved yet. This plugin require api key to authorize your drive.
  </p>
  <p>
    Click the documentation tab for more info.</b>.
  </p>
</div>
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
	global $wpdb, $cek_kunci, $gdwpm_opt_akun, $service, $apiConfig;
$gdwpm_opt_akun = get_option('gdwpm_akun_opt'); // imel, client id, service akun, private key

if(isset($_POST['folder_pilian'])){
$apiConfig['use_objects'] = true;
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
	//$gdwpm_berkas_terpilih = array_values($_POST['masuk_perpus']);
	$gdwpm_berkas_terpilih_arr = explode(' | ', $_POST['masuk_perpus']);
	//		if(!empty($gdwpm_berkas_terpilih_arr[0]) && !empty($gdwpm_berkas_terpilih_arr[1]) && !empty($gdwpm_berkas_terpilih_arr[2]) && !empty($gdwpm_berkas_terpilih_arr[3])){
	gdwpm_ijin_masuk_perpus(sanitize_mime_type($gdwpm_berkas_terpilih_arr[0]), $gdwpm_berkas_terpilih_arr[1], $gdwpm_berkas_terpilih_arr[2], $gdwpm_berkas_terpilih_arr[3]);
			
	echo '<strong>'.$gdwpm_berkas_terpilih_arr[1] . '</strong> has been added to your Media Library';
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
                $fi = new finfo( FILEINFO_MIME );
                $mimeType = explode( ';', $fi->buffer(file_get_contents($path)));
                return $this->createFile( $fileName, $mimeType[0], $description, file_get_contents($path), $fileParent );
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
?>