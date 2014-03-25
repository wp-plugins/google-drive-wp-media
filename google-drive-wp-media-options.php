<?php 
if(!function_exists('is_admin')){
     die('You do not have sufficient permissions to access this page.');
}
if ( !is_admin() ) {
     wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
$gdwpm_opsi_kategori = get_option('gdwpm_opsi_kategori_dr_folder'); 
$gdwpm_override_nonce = wp_create_nonce( "gdwpm_override_dir" );
?>
				<div class="ui-widget-content ui-corner-all" style="padding:1em;">	
				<form id="gdwpm_form_opsi_kategori" name="gdwpm_form_opsi_kategori" method="post">
				<p>
					<a onclick="gdwpm_cekbok_opsi_kategori_eksen();"><input type='checkbox' id='gdwpm_cekbok_opsi_kategori' name='gdwpm_cekbok_opsi_kategori' value='1' <?php echo $gdwpm_opsi_kategori;?> /></a> 
					Enable GDWPM Categories. (Google Drive folder names as Media Library category names)<br />
					&nbsp;<dfn>This option will create GDWPM Categories in your Media Library & Add Media filtering files.</dfn>
					<input type="hidden" name="gdwpm_opsi_kategori_nonce" value="<?php echo $gdwpm_override_nonce;?>">
				<p>
				<div id="gdwpm_folder_opsi_kategori_eksen" style="margin-left:15px;display: <?php if ($gdwpm_opsi_kategori == 'checked') { echo 'block;';}else{echo 'none;';}?>">
					<p>
					<dfn>Every time any Google Drive files added to Media Library, the folder name of these files will be category name of GDWPM Categories in the Media Library.
					</dfn>
					</p>
				</div>
				<button id="gdwpm_tombol_opsi_kategori" name="gdwpm_tombol_opsi_kategori">Save & Reload</button>
				</form>
				</div>
				<br />
				<?php 
				$gdwpm_override = get_option('gdwpm_override_dir_bawaan'); // cekbok, polder
				?>
				<div class="ui-widget-content ui-corner-all" style="padding:1em;">	
				<p>
					<a onclick="gdwpm_cekbok_opsi_override_eksen();"><input type='checkbox' id='gdwpm_cekbok_opsi_override' name='gdwpm_cekbok_opsi_override' value='1' <?php echo $gdwpm_override[0];?> /></a> 
					Google Drive as Default Media Upload Storage. (experimental)<br />
					&nbsp;<dfn>This option will change your default upload dir (<?php $def_upload_dir = wp_upload_dir(); echo $def_upload_dir['baseurl'];?>) to Google Drive. 
					This mean, when you upload files through default uploader (eg: Media >> Add New) it will automatically uploading your files to Google Drive.</dfn>
				</p>
				<div id="gdwpm_folder_opsi_override_eksen" style="margin-left:15px;display: <?php if ($gdwpm_override[0] == 'checked') { echo 'block;';}else{echo 'none;';}?>">
					<p>
						Google Drive folder name<br />
						<input type="text" id="gdwpm_folder_opsi_override_teks" name="gdwpm_folder_opsi_override_teks" value="<?php echo $gdwpm_override[1];?>" size="35" placeholder="Required (auto create if not exist)" />
					</p>
					<p>
						<input type='checkbox' id='gdwpm_cekbok_masukperpus_override' name='gdwpm_cekbok_masukperpus_override' value='1' <?php echo $gdwpm_override[2];?> /> Add to Media Library.
					</p>
				</div>
				<button onclick="gdwpm_tombol_opsi_override_eksen();" id="gdwpm_tombol_opsi_override" name="gdwpm_tombol_opsi_override">Save</button>&nbsp;&nbsp;&nbsp; 
				<span style="display: none" id="gdwpm_cekbok_opsi_override_gbr">
					<img src="<?php echo plugins_url( '/images/animation/loading-bar-image.gif', __FILE__ );?>" />
				</span>
				<span id="gdwpm__cekbok_opsi_override_info"></span>
				</div>
<script type="text/javascript">	
function gdwpm_cekbok_opsi_kategori_eksen(){
	if (jQuery('#gdwpm_cekbok_opsi_kategori').prop('checked')){
		document.getElementById("gdwpm_folder_opsi_kategori_eksen").style.display = "block";
	}else{
		document.getElementById("gdwpm_folder_opsi_kategori_eksen").style.display = "none";
	}
}

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
	if (jQuery('#gdwpm_cekbok_masukperpus_override').prop('checked')){
		var gdwpm_cekbok_masukperpus = 'checked';
	}else{
		var gdwpm_cekbok_masukperpus = '';
	}
		jQuery("#gdwpm_cekbok_opsi_override_gbr").show();
		jQuery('#gdwpm__cekbok_opsi_override_info').empty();
		var data = {
			action: 'gdwpm_on_action',
			gdwpm_override_nonce: '<?php echo $gdwpm_override_nonce; ?>',
			gdwpm_cekbok_opsi_value: gdwpm_cekbok ,
			gdwpm_folder_opsi_value: jQuery('#gdwpm_folder_opsi_override_teks').val() ,
			gdwpm_cekbok_masukperpus_override: gdwpm_cekbok_masukperpus
		};
		jQuery.post(ajax_object.ajax_url, data, function(hasil) {
			jQuery('#gdwpm_cekbok_opsi_override_gbr').hide();
			jQuery('#gdwpm__cekbok_opsi_override_info').html(hasil);
		});
}
</script>		
				<br />
<?php 
$nama_ploder = 'gdwpm_images';
$gdwpm_aplot_dir = $def_upload_dir['baseurl'] . '/' . $nama_ploder;
$fulldir = $def_upload_dir['basedir'] . '/' . $nama_ploder;

$gdwpm_homebase_arr = parse_url($gdwpm_aplot_dir);
$gdwpm_homebase = $gdwpm_homebase_arr['scheme'] . '://' . $gdwpm_homebase_arr['host'] . '/';
$gdwpm_aplot_dir = str_replace($gdwpm_homebase, '', $def_upload_dir['baseurl'] . '/' . $nama_ploder);

$tulis_htacc = '<textarea rows="4" cols="55">RewriteEngine on' . "\n";
$tulis_htacc .= 'RewriteBase /' . "\n";
$tulis_htacc .= 'RewriteCond %{QUERY_STRING} !^imgid= [NC]' . "\n";
$tulis_htacc .= 'RewriteRule ^(.*)$ ' . $gdwpm_aplot_dir . '/index.php?imgid=$1 [L,NC,QSA]</textarea>';

$tulis_php = '<textarea rows="7" cols="55"><?php' . "\n";
$tulis_php .= 'if (isset($_GET["imgid"])){' . "\n";
$tulis_php .= '$gdwpm_ekst_gbr = explode(".", $_GET["imgid"]);' . "\n";
$tulis_php .= 'if($gdwpm_ekst_gbr[1] == "png" || $gdwpm_ekst_gbr[1] == "gif" || $gdwpm_ekst_gbr[1] == "bmp"){' . "\n";
$tulis_php .= 'header("Content-Type: image/" . $gdwpm_ekst_gbr[1]);' . "\n";
$tulis_php .= '}else{' . "\n";
$tulis_php .= 'header("Content-Type: image/jpg");' . "\n";
$tulis_php .= '}' . "\n";
$tulis_php .= '$gdurl = "https://docs.google.com/uc?id=".$gdwpm_ekst_gbr[0]."&export=view";' . "\n";
$tulis_php .= '@readfile($gdurl);' . "\n";
$tulis_php .= '}' . "\n";	
$tulis_php .= '?></textarea>';

$gdwpm_cek_folder_dummy = 'not exist. <dfn>Because this plugin was failed to create this folder, you have to create it manually. Create a new directory inside your uploads directory.</dfn><hr>';
$gdwpm_cek_index_dummy = 'not exist. <dfn>Because this plugin was failed to create this file, you have to create it manually. Copy the following codes:</dfn><br />'.$tulis_php.'<br /><dfn>and save it as</dfn> <code>index.php</code>, <dfn>put it inside folder named</dfn> <code>gdwpm_images</code>.<hr>';	
$gdwpm_cek_htaccess_dummy = 'not exist. <dfn>Because this plugin was failed to create this file, you have to create it manually. Copy the following codes:</dfn><br />'.$tulis_htacc.'<br /><dfn>and save it as</dfn> <code>.htaccess</code>, <dfn>put it inside folder named</dfn> <code>gdwpm_images</code>.<hr>';	

if(is_dir($fulldir)){
	$gdwpm_cek_folder_dummy = 'exist (ok!)';
	if(file_exists($fulldir . '/index.php')){
		$gdwpm_cek_index_dummy = 'exist (ok!)';
	}
	if(file_exists($fulldir . '/.htaccess')){
		$gdwpm_cek_htaccess_dummy = 'exist (ok!)';
	}
}
if($gdwpm_cek_folder_dummy == 'exist (ok!)' && $gdwpm_cek_index_dummy == 'exist (ok!)' && $gdwpm_cek_htaccess_dummy == 'exist (ok!)'){
	$gdwpm_tombolsimpen_siap = '';
}else{
	$gdwpm_tombolsimpen_siap = 'disabled';
	update_option('gdwpm_dummy_folder', '');	
}
$gdwpm_dummy_fol = get_option('gdwpm_dummy_folder'); 
?>
				<div class="ui-widget-content ui-corner-all" style="padding:1em;">	
					<p>
					<a onclick="gdwpm_cekbok_opsi_dummy_eksen();"><input type='checkbox' id='gdwpm_cekbok_opsi_dummy' name='gdwpm_cekbok_opsi_dummy' value='1' <?php echo $gdwpm_dummy_fol;?> /></a>
					Enable Dummy Image URL. (Rewrite original Google Drive image URL)<br />
					&nbsp;<dfn>When you add an image into Media Library (auto or manually), this option will rewrite original Google Drive Image URL to internal dummy URL. (eg: 'https://docs.google.com/uc?id=google-drive-file-id&export=view' will be something like '<?php echo $def_upload_dir['baseurl'];?>/gdwpm_images/google-drive-file-id.jpg'). 
					<!-- With this feature (internal URLs), it makes more flexible to working with another plugins/themes. -->
					</dfn>
					</p>
					<div id="gdwpm_folder_opsi_dummy_eksen" style="margin-left:15px;display: <?php if ($gdwpm_dummy_fol == 'checked') { echo 'block;';}else{echo 'none;';}?>">
						<p>
						</p>
						<p>
						To enable this option, you have to meet the following requirements:<br />
						<table>
							<tr>
							<th>Required</th><th></th><th>Status</th>
							</tr>
							<tr>
								<td><code>gdwpm_images</code> folder</td><td> : </td>
								<td><?php echo $gdwpm_cek_folder_dummy;?></td>
							</tr>
							<tr>
								<td><code>index.php</code> file</td><td> : </td>
								<td><?php echo $gdwpm_cek_index_dummy;?></td>
							</tr>
							<tr>
								<td><code>.htaccess</code> file</td><td> : </td>
								<td><?php echo $gdwpm_cek_htaccess_dummy;?></td>
							</tr>
						</table>
						</p>
					</div>
					<button onclick="gdwpm_tombol_opsi_dummy_eksen();" id="gdwpm_tombol_opsi_dummy" name="gdwpm_tombol_opsi_dummy" <?php echo $gdwpm_tombolsimpen_siap;?>>Save</button>&nbsp;&nbsp;&nbsp; 
					<span style="display: none" id="gdwpm_cekbok_opsi_dummy_gbr">
						<img src="<?php echo plugins_url( '/images/animation/loading-bar-image.gif', __FILE__ );?>" />
					</span>
					<span id="gdwpm__cekbok_opsi_dummy_info"></span>
				</div>
<script type="text/javascript">	
function gdwpm_cekbok_opsi_dummy_eksen(){
	if (jQuery('#gdwpm_cekbok_opsi_dummy').prop('checked')){
		document.getElementById("gdwpm_folder_opsi_dummy_eksen").style.display = "block";
	}else{
		document.getElementById("gdwpm_folder_opsi_dummy_eksen").style.display = "none";
	}
}
function gdwpm_tombol_opsi_dummy_eksen(){
	if (jQuery('#gdwpm_cekbok_opsi_dummy').prop('checked')){
		var gdwpm_cekbok = 'checked';
	}else{
		var gdwpm_cekbok = '';
	}
		jQuery("#gdwpm_cekbok_opsi_dummy_gbr").show();
		jQuery('#gdwpm__cekbok_opsi_dummy_info').empty();
		var data = {
			action: 'gdwpm_on_action',
			gdwpm_override_nonce: '<?php echo $gdwpm_override_nonce; ?>',
			gdwpm_cekbok_opsi_dummy: gdwpm_cekbok
		};
		jQuery.post(ajax_object.ajax_url, data, function(hasil) {
			jQuery('#gdwpm_cekbok_opsi_dummy_gbr').hide();
			jQuery('#gdwpm__cekbok_opsi_dummy_info').html(hasil);
		});
}
  jQuery(function() {
    jQuery( "#gdwpm_tombol_opsi_kategori" )
      .button({
      icons: {
        primary: "ui-icon-disk"
      }
    });	
	
    jQuery( "#gdwpm_tombol_opsi_override" )
      .button({
      icons: {
        primary: "ui-icon-disk"
      }
    });	
	
    jQuery( "#gdwpm_tombol_opsi_dummy" )
      .button({
      icons: {
        primary: "ui-icon-disk"
      }
    });
  });		
</script>