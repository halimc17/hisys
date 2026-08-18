function getper2(bulan){
	periode2 = document.getElementById('periode2').value;
	if(periode2<bulan || periode2==''){		
		document.getElementById('periode2').value=bulan;
	}
}
function sumber(jenis) {	
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=sumber';
	
	tujuan = 'sdm_slave_salarylist2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('info').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function data() {
	kodeorg = document.getElementById('gudang').value;
	tipekaryawan = document.getElementById('tipekaryawan').value;
	periode = document.getElementById('periode').value;
	periode2 = document.getElementById('periode2').value;
	tipe    = document.getElementById('tipe').value;
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
	
	tujuan = 'sdm_slave_salarylist2.php';
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

function pivot() {
	kodeorg     = document.getElementById('gudang').value;
	periode     = document.getElementById('periode').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	tipe        = document.getElementById('tipe').value;
	validate([
        ["gudang","Kodeorg tidak boleh kosong."],
        ["periode","Periode tidak boleh kosong."]
    ]);
	
	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&method=' + tipe;
	
	tujuan = 'sdm_slave_salarylist2.php';
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
					
					$(function(){
						var renderers  = $.extend($.pivotUtilities.renderers,$.pivotUtilities.subtotal_renderers,$.pivotUtilities.c3_renderers,$.pivotUtilities.plotly_renderers);
						
						var sum = $.pivotUtilities.aggregatorTemplates.sum;
						var numberFormat = $.pivotUtilities.numberFormat;
						var intFormat = numberFormat({digitsAfterDecimal: 0});
						
						$("#output").pivot(JSON.parse(isi[0]),{
							rows: JSON.parse(isi[1]),
							cols: JSON.parse(isi[2]),
							aggregator: sum(intFormat)(JSON.parse(isi[3])),
							vals: JSON.parse(isi[3]),
							rendererName: "Table",
							// rendererOptions: {
								// rowSubtotalDisplay: {
									// displayOnTop: false
								// }
							// },
							sorters: {"Komponen": $.pivotUtilities.sortAs(JSON.parse(isi[4]))}
						});
					});
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
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
