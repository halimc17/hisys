function formfav() {
	tipe   = document.getElementById('tipe').value;
	kodeorg= document.getElementById('gudang').value;
	periode= document.getElementById('periode').value;
	periode2= document.getElementById('periode2').value;
	output = document.getElementById('output').innerHTML;
	tipekaryawan= document.getElementById('tipekaryawan').value;

	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&periode2=' + periode2;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&tipe=' + tipe;
	param += '&method=formfav';

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Add Favorit","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('500px','70%');
					loadformfav();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadformfav(){
	tipe   = document.getElementById('tipe').value;
	kodeorg= document.getElementById('gudang').value;
	periode= document.getElementById('periode').value;
	periode2= document.getElementById('periode2').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	output = document.getElementById('output').innerHTML;

	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&periode2=' + periode2;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&tipe=' + tipe;
	param += '&method=loadformfav';

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('loadformfav').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function favorit2() {
	kodeorg= document.getElementById('gudang').value;
	periode= document.getElementById('periode').value;
	periode2= document.getElementById('periode2').value;
	output = document.getElementById('output').innerHTML;
	tipe   = document.getElementById('tipe').value;
	namafav= document.getElementById('namafav').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	param  = '';
	var config = $("#output").data("pivotUIOptions");
	var config_copy = JSON.parse(JSON.stringify(config));
	delete config_copy["aggregators"];
	delete config_copy["renderers"];
	delete config_copy["rendererOptions"]["localeStrings"]["renderError"];
	delete config_copy["rendererOptions"]["localeStrings"]["computeError"];
	delete config_copy["rendererOptions"]["localeStrings"]["uiRenderError"];
	delete config_copy["localeStrings"]["renderError"];
	delete config_copy["localeStrings"]["computeError"];
	delete config_copy["localeStrings"]["uiRenderError"];
	if(config_copy["rendererName"]!="Table"){
		alertify.alert("Jenis ini tidak bisa disave ke favorit, silahkan rubah ke jenis : Table");
		return;
	}
	param += '&data=' + JSON.stringify(config_copy);
	param += '&tipe=' + tipe;
	param += '&namafav=' + namafav;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&periode2=' + periode2;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&sumber=sdm_pivot';
	param += '&method=favorit';
	param += '&jenis=popupkirim';
	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert("Sudah ditambahkan ke favorit.");
					loadformfav();
				}

			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getfromfav(){
	fromfavorit = document.getElementById('fromfavorit').value;
	output = document.getElementById('output').innerHTML;
	if(output!=''){
		location.reload();
	}

	param  = '';
	param += '&fromfavorit=' + fromfavorit;
	param += '&method=getfromfav';
	param += '&jenis=popupkirim';
	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isi = con.responseText;
					mn = JSON.parse(isi);
					$('#gudang').val(mn['kodeorg']).trigger('change');
					$('#periode').val(mn['periode']).trigger('change');
					$('#periode2').val(mn['periode2']).trigger('change');
					$('#tipe').val(mn['tipe']).trigger('change');
					$('#tipekaryawan').val(mn['tipekaryawan']).trigger('change');

					alertify.confirm("Show From Favorit ?","Click <b>OK</b> untuk menampilkan Pivot tanpa merubah Filter data terlebih dahulu.<br><br>Click <b>CANCEL</b> untuk menyesuaikan Filter data terlebih dahulu, kemudian click tombol <button class=mybutton>Pivot</button> untuk menampilkan.",
						function(){
							pivot(fromfavorit);
						},
						function(){
							return;
						}
					).set('resizable',false);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getper2(bulan){
	periode2 = document.getElementById('periode2').value;
	if(periode2<bulan || periode2==''){
		document.getElementById('periode2').value=bulan;
		setValue2('periode2',bulan);
	}
}
function sumber(jenis) {
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=sumber';

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('info').innerHTML=con.responseText;

					if(jenis=='source'){
						document.getElementById('rowkary').style.display='';
					}else{
						document.getElementById('rowkary').style.display='none';
						document.getElementById('karyawanid').value='';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkary(jenis) {
	kodeorg     = document.getElementById('gudang').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	tipe        = document.getElementById('tipe').value;
	
	
	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&tipe=' + tipe;
	param += '&method=getkary';

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('karyawanid').innerHTML=con.responseText;

					if(tipe=='source'){
						document.getElementById('rowkary').style.display='';
					}else{
						document.getElementById('rowkary').style.display='none';
						document.getElementById('karyawanid').value='';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function data() {
	kodeorg     = document.getElementById('gudang').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	periode     = document.getElementById('periode').value;
	periode2    = document.getElementById('periode2').value;
	tipe        = document.getElementById('tipe').value;
	validate([
        ["periode","Periode dari tidak boleh kosong."],
        ["periode2","Periode sampai tidak boleh kosong."],
        ["tipe","Jenis tidak boleh kosong."]
    ]);

	param  = '';
	param += '&jenis=data';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&periode2=' + periode2;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&method=' + tipe;

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tombolexport').style.display="none";
					dt = con.responseText.split("####");
					document.getElementById('output').innerHTML=dt[0];
					// untuk grouping row
					// https://datatables.net/examples/advanced_init/row_grouping.html

					// buat jumlah di baris bawah / footer
					// https://datatables.net/examples/advanced_init/footer_callback.html

					// tambah kolom pencarian
					// https://datatables.net/examples/plug-ins/range_filtering.html

					// supaya kolom judul bisa menjadi pencarian
					// https://datatables.net/examples/api/multi_filter.html
					// https://datatables.net/examples/api/multi_filter_select.html

					// Scrolling and Bootstrap tabs
					// https://datatables.net/examples/api/tabs_and_scrolling.html

					// buat form input
					// https://datatables.net/examples/api/form.html

					// matiin kolom search dan ordering
					// https://datatables.net/examples/advanced_init/defaults.html
					// $.extend( true, $.fn.dataTable.defaults, {
						// "searching": false,
						// "ordering": false
					// } );

					// $(document).ready(function() {
						// $('#pvtTable').DataTable( {
							// // kalau kolom pakai colspan
							// // https://datatables.net/examples/advanced_init/complex_header.html
							// // "columnDefs": [ {
								// // "visible": false,
								// // "targets": -1
							// // } ],

							// // kalau mau pakai searchpanel
							// // searchPanes: {
								// // cascadePanes: true,
								// // viewTotal: true
							// // },
							// // dom: 'Plfrtip',
							// responsive: true,
							// fixedColumns: false,
							// fixedHeader: true,
							// colReorder: true,
							// paging: true,
							// "iDisplayLength": 50,
							// scrollY: 380,
							// dom: 'Bfrtip',
							// buttons: [
								// 'copy', 'csv', 'excel', 'pdf', 'print'
							// ]
						// } );
					// } );

					// kolom jadi ada pencarian
					$(document).ready(function() {
						 // Setup - add a text input to each footer cell
						// $('#pvtTable thead tr').clone(true).appendTo( '#pvtTable thead' );
						// $('#pvtTable thead tr:eq(1) th').each( function (i) {
							// var title = $(this).text();
							// $(this).html( '<input type="text" placeholder="Search '+title+'" />' );

							// $( 'input', this ).on( 'keyup change', function () {
								// if ( table.column(i).search() !== this.value ) {
									// table
										// .column(i)
										// .search( this.value )
										// .draw();
								// }
							// } );
						// } );

						// Setup - add a text input to each footer cell
						$('#pvtTable tfoot th').each( function () {
							var title = $(this).text();
							$(this).html( '<input type="text" class="myinputtextdt" placeholder="Cari '+title+'" />' );
						} );
						var columns = $('#pvtTable thead th.immediate');

						// DataTable
						var table = $('#pvtTable').DataTable({
							// supaya tidak ada overflow horisontal
							responsive: true,
							//fixedColumns: true,
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
							// drag kolom
							colReorder: true,
							data : JSON.parse(dt[1]),
							columnDefs: [
								{"className": "dt-right", "targets": JSON.parse(dt[2]), render: $.fn.dataTable.render.number(',', '.', 2, '')}
							],

							// jumlah per page
							"iDisplayLength": 50,
							// tinggi / height
							scrollY: 380,
							// untuk tombol
							dom: 'Bfrtip',
							buttons: [
								'copy', 'csv', 'excel', 'pdf', 'print'
							],
							// buat pencarian
							initComplete: function () {
								// Apply the search
								this.api().columns().every( function () {
									var that = this;
									$( 'input', this.footer() ).on( 'keyup change clear', function () {
										if ( that.search() !== this.value ) {
											that
												.search( this.value )
												.draw();
										}
									} );
								} );
							}
						});
						//var columnData = table.columns().data();
						//column = table.column( $(this).attr('data-column') );

						// Row selection (multiple rows)
						$('#pvtTable tbody').on( 'click', 'tr', function () {
							$(this).toggleClass('selected');
						} );

						// show hide kolom
						$('button.dt-button').on( 'click', function (e) {
							e.preventDefault();
							// Get the column API object
							var column = table.column( $(this).attr('data-column') );
							// Toggle the visibility
							column.visible( ! column.visible() );
						} );
					} );
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function pivot(fromfavorit) {
	kodeorg     = document.getElementById('gudang').value;
	periode     = document.getElementById('periode').value;
	periode2    = document.getElementById('periode2').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	karyawanid  = document.getElementById('karyawanid').value;
	tipe        = document.getElementById('tipe').value;
	validate([
        ["periode","Periode dari tidak boleh kosong."],
        ["periode2","Periode sampai tidak boleh kosong."],
        ["tipe","Jenis tidak boleh kosong."]
    ]);

	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&periode2=' + periode2;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&karyawanid=' + karyawanid;
	param += '&method=' + tipe;

	tujuan = 'sdm_slave_pivot.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					dt = "";
					isi = con.responseText.split("####");
					document.getElementById('tombolexport').style.display="";
					document.getElementById('optfavorit').innerHTML=isi[5];
					document.getElementById('output').innerHTML="";
					sessionStorage.clear();

					$(function(){
						n = isi[6].split("$$$$");
						e = isi[7].split("$$$$");
						if(n.length>0){
							for(i=0;i<n.length;i++){
								sessionStorage.setItem(n[i], e[i]);
							}
						}

						$("#optfavorit").change(function(){
							var judul = $("#optfavorit").val();
							var localdata = sessionStorage.getItem(judul);
							$("#output").pivotUI(JSON.parse(isi[0]), JSON.parse(localdata), true);
						});

						var renderers  = $.extend($.pivotUtilities.renderers,$.pivotUtilities.subtotal_renderers,$.pivotUtilities.c3_renderers,$.pivotUtilities.plotly_renderers);

						var dataClass  = $.pivotUtilities.SubtotalPivotData;
						var derivers   = $.pivotUtilities.derivers;
						var my_aggregators = {
								"Integer Sum": $.pivotUtilities.aggregators["Integer Sum"],
								"Sum": $.pivotUtilities.aggregators["Sum"],
								"Sum over Sum": $.pivotUtilities.aggregators["Sum over Sum"],
								"Count": $.pivotUtilities.aggregators["Count"],
								"Count Unique Values": $.pivotUtilities.aggregators["Count Unique Values"],
								"Average": $.pivotUtilities.aggregators["Average"]
							};
						// //nreco freeze
						// var nrecoPivotExt = new NRecoPivotTableExtensions({
						// wrapWith: '<div class="pvtTableRendererHolder"></div>',  // special div is needed by fixed headers when used with pivotUI
							// fixedHeaders : true,
							// onSortHandler : function(sortOpts) {
								// // save changed sort options in pivotUI state
								// var pvtUIOpts = $('#output').data("pivotUIOptions");
								// if (!pvtUIOpts.rendererOptions) pvtUIOpts.rendererOptions = {};
								// pvtUIOpts.rendererOptions.sort = sortOpts;
							// }
						// });

						// var stdRendererNames = ["Table","Table Barchart","Heatmap","Row Heatmap","Col Heatmap"];
						// var wrappedRenderers = $.extend( {}, $.pivotUtilities.renderers);
						// $.each(stdRendererNames, function() {
							// var rName = this;
							// wrappedRenderers[rName] = nrecoPivotExt.wrapTableRenderer(wrappedRenderers[rName]);
						// });

						// //nreco freeze
						$("#output").pivotUI(JSON.parse(isi[0]),{
							dataClass: dataClass,
							renderers: renderers,
							//renderers: wrappedRenderers,
							//unusedAttrsVertical:1, //paksa filter selalu di kiri
							aggregators: my_aggregators,
							rows: JSON.parse(isi[1]),
							cols: JSON.parse(isi[2]),
							aggregatorName: "Integer Sum",
							vals: JSON.parse(isi[3]),
							rendererName: "Table",
							menuLimit: "5000",
							rendererOptions: {
								rowSubtotalDisplay: {
									displayOnTop: false
								}
							},
							sorters: {"Komponen": $.pivotUtilities.sortAs(JSON.parse(isi[4]))}
							// ,onRefresh: function (pivotUIOptions) {
								// // this is correct way to apply fixed headers with pivotUI
								// nrecoPivotExt.initFixedHeaders($('#output table.pvtTable'));
							// }
							,onRefresh: function (pivotUIOptions) {
								setTimeout(function(){
									hidetotal('r');
								}, 10);
							}
						});

						if(fromfavorit!=undefined){
							$('#optfavorit').val(fromfavorit).trigger('change');
						}
					});

					document.getElementById('tableheader').style.display="none";
					document.getElementById('showhead').innerHTML="Show";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hidetotal(n,e){
	$(document).ready(function(){
	var awal = $("#temphidetotal").val(); // 1 show 0 hide
	var awalcol = $("#temphidetotalcol").val(); // 1 show 0 hide
		if(n=='r'){
			if(e=='1' && awal=='1'){
				$("#temphidetotal").val('0');
			}
			if(e=='1' && awal=='0'){
				$("#temphidetotal").val('1');
			}
			if($("#temphidetotal").val()!="1"){
				$('.pvtTotalLabel.pvtRowTotalLabel').attr("style", "display:none");
				$('.pvtTotal.rowTotal').attr("style", "display:none");
				$('.pvtGrandTotal').attr("style", "display:none");
				$('#hidetotal').html("Show Total Row");
			}else{
				$('.pvtTotalLabel.pvtRowTotalLabel').removeAttr("style");
				$('.pvtTotal.rowTotal').removeAttr("style");
				$('.pvtGrandTotal').removeAttr("style");
				$('#hidetotal').html("Hide Total Row");
			}
			if($('.pvtTotalLabel.pvtRowTotalLabel').attr("style")!='display:none'){
				if($('.pvtTotalLabel.pvtColTotalLabel').attr("style")!='display:none'){
					$('.pvtGrandTotal').removeAttr("style");
				}else{
					$('.pvtGrandTotal').attr("style", "display:none");
				}
			}else{
				$('.pvtGrandTotal').attr("style", "display:none");
			}
		}

		if(n=='c'){
			if(e=='1' && awalcol=='1'){
				$("#temphidetotalcol").val('0');
			}
			if(e=='1' && awalcol=='0'){
				$("#temphidetotalcol").val('1');
			}
			if($("#temphidetotalcol").val()!="1"){
				$('.pvtTotalLabel.pvtColTotalLabel').attr("style", "display:none");
				$('.pvtTotal.colTotal').attr("style", "display:none");
				$('#hidetotalcol').html("Show Total Col");
			}else{
				$('.pvtTotalLabel.pvtColTotalLabel').removeAttr("style");
				$('.pvtTotal.colTotal').removeAttr("style");
				$('#hidetotalcol').html("Hide Total Col");
			}
			if($('.pvtTotalLabel.pvtRowTotalLabel').attr("style")!='display:none'){
				if($('.pvtTotalLabel.pvtColTotalLabel').attr("style")!='display:none'){
					$('.pvtGrandTotal').removeAttr("style");
				}else{
					$('.pvtGrandTotal').attr("style", "display:none");
				}
			}else{
				$('.pvtGrandTotal').attr("style", "display:none");
				// $('.pvtGrandTotal').removeAttr("style");
			}
		}
	});
}


function exportTableToExcel(tableID, filename = ''){

	var downloadLink;
	var dataType = 'application/vnd.ms-excel';
	var tableSelect = document.getElementById(tableID);
		tableSelect.border='1';
	var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

	filename = filename?filename+'.xls':'excel_data.xls';
	downloadLink = document.createElement("a");
	document.body.appendChild(downloadLink);

	if(navigator.msSaveOrOpenBlob){
		var blob = new Blob(['\ufeff', tableHTML], {
			type: dataType
		});
		navigator.msSaveOrOpenBlob( blob, filename);
	}else{
		downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
		downloadLink.download = filename;
		downloadLink.click();
	}
}

function ExportPdf(){
	if(document.getElementById('tipe')!=undefined){
		var nama = document.getElementById('tipe');
		var tipe = nama.options[nama.selectedIndex].text;
		var namafile=tipe;
	}else{
		var namafile='pivot';
	}


	var doc = new jsPDF('l','pt','A4');
	var totalPagesExp = '{total_pages_count_string}';
	doc.autoTable({
		html: '#pvtTable',
		theme: 'grid',
		useCss: true,
		didDrawPage: function (data) {
			// Header
			doc.setFontSize(15)
			doc.setTextColor(40)
			doc.text(namafile, data.settings.margin.left + 15, 22)

			// Footer
			var str = 'Page ' + doc.internal.getNumberOfPages()
			// Total page number plugin only available in jspdf v1.0+
			if (typeof doc.putTotalPages === 'function') {
				str = str + ' of ' + totalPagesExp
			}
			doc.setFontSize(10)

			// jsPDF 1.4+ uses getWidth, <1.4 uses .width
			var pageSize = doc.internal.pageSize
			var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight()
			doc.text(str, data.settings.margin.left, pageHeight - 10)
		}
		// headStyles: {
			// fillColor: [39, 83, 112],
			// halign: 'center',
			// fontStyle: 'bold',
			// fontSize: 8,
			// textColor: [255, 255, 255]
		// },
		// bodyStyles: {
			// cellWidth: 'wrap',
			// fontSize: 8,
			// textColor: [0, 0, 0]
		// },
		// columnStyles: { text: { cellWidth: 'auto' } },
	})

	if (typeof doc.putTotalPages === 'function') {
		doc.putTotalPages(totalPagesExp);
	}
	doc.save(namafile+'.pdf');
}


function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('showhead').innerHTML="Show";
	}
}