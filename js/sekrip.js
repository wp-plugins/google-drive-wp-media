function jumBaris(maxBaris){

jQuery('table.paginasi').each(function() {
    var halSkrg = 0;
  if (typeof maxBaris === 'undefined') { maxBaris = 20; }
    var table = jQuery(this);
    table.bind('repaginate', function() {
        table.find('tbody tr').hide().slice(halSkrg * maxBaris, (halSkrg + 1) * maxBaris).show();
    });
    table.trigger('repaginate');
    var jmlBaris = table.find('tbody tr').length;
    var jmlLaman = Math.ceil(jmlBaris / maxBaris);
	if (jQuery('.halpager').length) {
		jQuery('.halpager').remove();
	}
	if (jmlLaman > 1){
		var halPager = jQuery('<div class="halpager"></div>');
		for (var hal = 0; hal < jmlLaman; hal++) {
			jQuery('<span class="urut-laman"></span>').text(hal + 1).bind('click', {
				halBaru: hal
			}, function(event) {
				halSkrg = event.data['halBaru'];
				table.trigger('repaginate');
				jQuery(this).addClass('active').siblings().removeClass('active');
			}).appendTo(halPager).addClass('clickable');
		}
		halPager.insertAfter(table).find('span.urut-laman:first').addClass('active');
	}
});
}

jQuery(document).ready(function($) {
	$('#gdwpm_loading_gbr').hide();
	$('#gdwpm_masuk_perpus_teks').hide();
	$('#gdwpm_add_to_media_gbr').hide();
    $("#golek_seko_folder").click(function(){
        $("#gdwpm_loading_gbr").show();
	$('.sukses').empty();
	$('.sukses').hide();
	$('#tombol-donat').remove();
	$('.updated').hide();
		$('#hasil').empty();
		$('#vaginasi').empty();
		$('#gdwpm_info_folder_thumbs').hide();
		$('#gdwpm_masuk_perpus_teks').hide();
		$('#hasil').hide();
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian: $('select[name=folder_pilian]').val(),
		pilmaxres: $('#pilihMaxRes').val()
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		$("#gdwpm_loading_gbr").hide();
		$('#hasil').show();
		if(response.indexOf("radio") > 1){
			if($('#folder_pilian option:selected').text() != 'gdwpm-thumbnails'){
				$('#gdwpm_masuk_perpus_teks').show();
			}else{
				$('#gdwpm_info_folder_thumbs').show();
			}
		}
		$('#gdwpm_info_masuk_perpus').empty();
		var $holder = $('<div/>').html(response);
$('.sukses').html($('.sukses', $holder).html());
$('#hasil').html($('#hasil', $holder).html());
$('#vaginasi').html($('#vaginasi', $holder).html());
$("#hasil").tooltip({
    items: "td.kolom_file",
	track: true,
	position: { my: "left+15 top-5", at: "left bottom" },
	show: { effect: 'slideDown' },
    open: function (event, ui) { setTimeout(function () {
			$(ui.tooltip).hide('slideUp');
        }, 7000); },
    content: function(){
           var src = $(this).attr('title');
			if(src == ''){
				return 'No thumbnail found';
			}else{
				return '<img src="'+ src +'" />';
			}
    }
});
	$('.sukses').show();	
		$('#vaginasi').buttonset({});
	$('#gdwpm_add_to_media_gbr').hide();
	var pilBaris = $('#pilihBaris').val();
		jumBaris(pilBaris);
	});
    }); 
	$('body').on('click', '#halaman', function() {
        $("#gdwpm_loading_gbr").show();
		$('.sukses').empty();
		$('.sukses').hide();
		$('.updated').hide();
		$('#hasil').empty();
		$('#gdwpm_info_folder_thumbs').hide();
		$('#gdwpm_masuk_perpus_teks').hide();
		$('#hasil').hide();		
		var clicked = $(this);
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian: $('#folid').val(),
		pilmaxres: $('#maxres').val(),
		pagetoken: clicked.val()
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		$("#gdwpm_loading_gbr").hide();
		$('#hasil').show();
		if(response.indexOf("radio") > 1){
			if($('#folder_pilian option:selected').text() != 'gdwpm-thumbnails'){
				$('#gdwpm_masuk_perpus_teks').show();
			}else{
				$('#gdwpm_info_folder_thumbs').show();
			}
		}
		$('#gdwpm_info_masuk_perpus').empty();
		$('#vaginasi').empty();
		var $holder = $('<div/>').html(response);
		$('.sukses').html($('.sukses', $holder).html());
		$('#hasil').html($('#hasil', $holder).html());
		$('#vaginasi').html($('#vaginasi', $holder).html());
		$('.sukses').show();
		$('#vaginasi').buttonset({});
		$('#gdwpm_add_to_media_gbr').hide();
		var pilBaris = $('#pilihBaris').val();
			jumBaris(pilBaris);
	});
    }); 
	$("#gdwpm_berkas_masuk_perpus").click(function(){
        $("#gdwpm_add_to_media_gbr").show();
		$('#gdwpm_info_masuk_perpus').empty();
	var data = {
		action: 'gdwpm_on_action',
		masuk_perpus: $("input:radio[name='gdwpm_berkas_terpilih[]']:checked").val()
	};
	jQuery.post(ajax_object.ajax_url, data, function(dataPerpus) {
		$('#gdwpm_add_to_media_gbr').hide();
		$('#gdwpm_info_masuk_perpus').html(dataPerpus);
	});
	
	});
	
	 $("#gdwpm_file_dr_folder").click(function(){
        $("#gdwpm_loading_gbr_del").show();
	$('.sukses_del').empty();
	$('.sukses_del').hide();
	$('#hasil_del').empty();
	$('#gdwpm_info_del').hide();
	$('#hasil_del').hide();
		$('#vaginasi_del').empty();
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian_file_del: $('select[name=folder_pilian_file_del]').val(),
		pilmaxres: $('#pilihMaxResdel').val()
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(responsedel) {
		$("#gdwpm_loading_gbr_del").hide();
		$('#hasil_del').show();
		var holder = $('<div/>').html(responsedel);
$('.sukses_del').html($('.sukses_del', holder).html());
$('#hasil_del').html($('#hasil_del', holder).html());
$('#vaginasi_del').html($('#vaginasi_del', holder).html());
$("#hasil_del").tooltip({
    items: "td.kolom_file",
	track: true,
	show: { effect: 'slideDown' },
    open: function (event, ui) { setTimeout(function () {
			$(ui.tooltip).hide('slideUp');
        }, 7000); },
    content: function(){
           var src = $(this).attr('title');
			if(src == ''){
				return 'No thumbnail found';
			}else{
				return '<img src="'+ src +'" />';
			}
    }
});
	$('.sukses_del').show();
		$('#vaginasi_del').buttonset({});
		
	});
    }); 
  
	
	 $("#hasil_del").click(function(){
    var len = $("#hasil_del input[name='gdwpm_buang_berkas_terpilih[]']:checked").length;
	if (len>0){
		$('#gdwpm_info_del').show();
	}else{
		$('#gdwpm_info_del').hide();
	}
    }); 
	
	$('body').on('click', '#halaman_del', function() {
        $("#gdwpm_loading_gbr_del").show();
		$('.sukses_del').empty();
		$('.sukses_del').hide();
		$('#hasil_del').empty();
		$('#hasil_del').hide();		
		$('#gdwpm_info_del').hide();
		var clicked = $(this);
	var data = {
		action: 'gdwpm_on_action',
		folder_pilian_file_del: $('#folid_del').val(),
		pilmaxres: $('#maxres_del').val(),
		pagetoken: clicked.val()
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(responsedel) {
		$("#gdwpm_loading_gbr_del").hide();
		$('#hasil_del').show();
		$('#vaginasi_del').empty();
		var holder = $('<div/>').html(responsedel);
		$('.sukses_del').html($('.sukses_del', holder).html());
		$('#hasil_del').html($('#hasil_del', holder).html());
		$('#vaginasi_del').html($('#vaginasi_del', holder).html());
		$('.sukses_del').show();
		$('#vaginasi_del').buttonset({});
	});
    }); 
	
	$("#gdwpm_tombol_bersih").click(function(){
		$('#filelist').empty();	
		$('#console').empty();
		$(this).hide();
	})
	
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

if(ajax_object.opsi_kategori == 'checked') {
	var gdwpmMedia = wp.media;
	if ( gdwpmMedia ) {
		jQuery.each(mediaTaxonomies,function(key,label){

			gdwpmMedia.view.AttachmentFilters[key] = gdwpmMedia.view.AttachmentFilters.extend({
				className: key,

				createFilters: function() {
					var filters = {};

					_.each( mediaTerms[key] || {}, function( term ) {

						var query = {};

						query[key] = {
							taxonomy: key,
							term_id: parseInt( term.id, 10 ),
							term_slug: term.slug
						};

						filters[ term.slug ] = {
							text: term.label,
							props: query
						};
					});

					this.filters = filters;
				}


			});

			var gdwpmBar = gdwpmMedia.view.AttachmentsBrowser;

			gdwpmMedia.view.AttachmentsBrowser = gdwpmMedia.view.AttachmentsBrowser.extend({
				createToolbar: function() {

					gdwpmMedia.model.Query.defaultArgs.filterSource = 'filter-media-taxonomies';

					gdwpmBar.prototype.createToolbar.apply(this,arguments);

					this.toolbar.set( key, new gdwpmMedia.view.AttachmentFilters[key]({
						controller: this.controller,
						model:      this.collection.props,
						priority:   -80
						}).render()
					);
				}
			});

		});
	}
}

 });