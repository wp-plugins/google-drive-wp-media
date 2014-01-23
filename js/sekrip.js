jQuery(document).ready(function($) {
	$('#gdwpm_loading_gbr').hide();
	$('#gdwpm_masuk_perpus_teks').hide();
	$('#gdwpm_add_to_media_gbr').hide();
    $("#golek_seko_folder").click(function(){
        $("#gdwpm_loading_gbr").show();
	$('.sukses').empty();
	$('.sukses').hide();
	$('.updated').hide();
		$('#hasil').empty();
	$('#gdwpm_masuk_perpus_teks').hide();
		$('#hasil').hide();
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian: $('select[name=folder_pilian]').val()
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		$("#gdwpm_loading_gbr").hide();
		$('#hasil').show();
		if(response.indexOf("radio") > 1){
	$('#gdwpm_masuk_perpus_teks').show();}
	//alert('Got this from the server: ' + response);
		$('#gdwpm_info_masuk_perpus').empty();
		var $holder = $('<div/>').html(response);
$('.sukses').html($('.sukses', $holder).html());
$('#hasil').html($('#hasil', $holder).html());
	$('.sukses').show();
	$('#gdwpm_add_to_media_gbr').hide();
		
	});
    }); 
	$("#gdwpm_berkas_masuk_perpus").click(function(){
        $("#gdwpm_add_to_media_gbr").show();
		$('#gdwpm_info_masuk_perpus').empty();
	var data = {
		action: 'gdwpm_on_action',
		masuk_perpus: $("input:radio[name='gdwpm_berkas_terpilih[]']:checked").val()      // We pass php values differently!
	};
	jQuery.post(ajax_object.ajax_url, data, function(dataPerpus) {
		$('#gdwpm_add_to_media_gbr').hide();
		$('#gdwpm_info_masuk_perpus').html(dataPerpus);
	});
	
	});
	
	$("#gdwpm_aplot_masuk").click(function(){
//function uploadfile(){
	alert('Got this from the server: ');
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian_aplod: $('select[name=folder_pilian_aplod]').val(), 
		gdwpm_folder_anyar: $('.gdwpm_folder_anyar').val(), 
		gdwpm_aplod_file: $("#gdwpm_aplod_file").val(), 
		gdwpm_aplod_deskrip: $("#gdwpm_aplod_deskrip").val()      // We pass php values differently!
	};
	jQuery.post(ajax_object.ajax_url, data, function(data) {
		//$('#gdwpm_add_to_media_gbr').hide();
		$('#gdwpm_hsl_aplod_gbr').html(data);
	});
	

})

 });