function getParameterInput() {
    flyTable = document.getElementById('flyTable');
    numParam = flyTable.rows.length;
    paramX = '';
    for (dd = 0; dd < numParam; dd++) {
        if (paramX != '') {
            paramX += ' and ';
        }
		nullx= flyTable.rows[dd].cells[1].innerHTML.indexOf("null");
		betw = flyTable.rows[dd].cells[1].innerHTML.indexOf("betwee");
		likeX= flyTable.rows[dd].cells[1].innerHTML.indexOf("like");
		inX  = flyTable.rows[dd].cells[1].innerHTML.indexOf("in");

        opera = flyTable.rows[dd].cells[1].innerHTML;
        opera = opera.replace("&gt;", ">");
        opera = opera.replace("&lt;", "<");
        rt = document.getElementById('frmparam' + dd).getAttribute("onmousemove");
        if (betw > -1) {
            par1 = document.getElementById('frmparam' + dd).value;
            par2 = document.getElementById('frmparama' + dd).value;
            if (rt != null && rt.indexOf("setCalendar") > -1) {
                par1 = par1.split("-");
                par1 = par1[2] + "-" + par1[1] + "-" + par1[0];
                par2 = par2.split("-");
                par2 = par2[2] + "-" + par2[1] + "-" + par2[0];
            }
            paramX += " (" + flyTable.rows[dd].cells[0].getAttribute("value") + " " + opera + " '" + par1 + "' and '" + par2 + "') ";
        } else if (nullx > -1) {
            paramX += " " + flyTable.rows[dd].cells[0].getAttribute("value") + " " + opera;
        } else if (likeX > -1) {
            par1 = document.getElementById('frmparam' + dd).value;
            if (rt != null && rt.indexOf("setCalendar") > -1) {
                par1 = par1.split("-");
                par1 = par1[2] + "-" + par1[1] + "-" + par1[0];
            }
            paramX += " " + flyTable.rows[dd].cells[0].getAttribute("value") + " " + opera + " '::persen::" + par1 + "::persen::'";
        } else if (inX > -1) {
            raw = document.getElementById('frmparam' + dd).value;
            raw = raw.split(",");
            par1 = '';
            for (cd = 0; cd < raw.length; cd++) {
                if (cd == 0) {
                    par1 += "'" + raw[cd] + "'";
                } else {
                    par1 += ",'" + raw[cd] + "'";
                }
            }
            paramX += " " + flyTable.rows[dd].cells[0].getAttribute("value") + " " + opera + " (" + par1 + ")";
        } else {
            par1 = document.getElementById('frmparam' + dd).value;
            if (rt != null && rt.indexOf("setCalendar") > -1) {
                par1 = par1.split("-");
                par1 = par1[2] + "-" + par1[1] + "-" + par1[0];
            }
            paramX += " " + flyTable.rows[dd].cells[0].getAttribute("value") + " " + opera + " '" + par1 + "'";
        }
    }
    return paramX;
}

function getfilter(rnumber){
	validate([
        ["jenislaporan","Jenis Laporan tidak boleh kosong."]
    ]);
    param='method=getfilter&rnumber='+rnumber;
    tujuan='user_slave_customreport.php';
    post_response_text(tujuan, param, respog);
    function respog(){
	    if (con.readyState == 4) {
	        if (con.status == 200) {
	            busy_off();
	            if (!isSaveResponse(con.responseText)) {
	                alert(con.responseText);
	            } else {
	                document.getElementById('filterlap').style.display="";
					document.getElementById('contfilterlap').innerHTML=con.responseText;
	            }
	        } else {
	            busy_off();
	            error_catch(con.status);
	        }
	    }
	}
}

function excel(tipe) {
	jenislaporan= document.getElementById('jenislaporan').value;
	validate([
        ["jenislaporan","Jenis Laporan tidak boleh kosong."]
    ]);
	
	document.getElementById('output').innerHTML='';
	
	parameter = getParameterInput();
	param  = '';
    param += "&tipe=" + tipe + "&parameter=" + parameter;
	param += '&jenislaporan=' + jenislaporan;
	param += '&method=pivot';
	param += '&jenis=excel';
	
	tujuan = 'user_slave_customreport.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('tombolexport').style.display="none";
					document.getElementById('fileterpivot').style.display="none";
					dt = con.responseText.split("####");
					document.getElementById('output').innerHTML=dt[0];
					// kolom jadi ada pencarian
					$(document).ready(function() {
						// Setup - add a text input to each footer cell
						$('#pvtTable tfoot th').each( function () {
							var title = $(this).text();
							$(this).html( '<input type="text" class="myinputtextdt"  placeholder="Cari '+title+'" />' );
						} );
					 
						// DataTable
						var table = $('#pvtTable').DataTable({
							responsive: true,
							fixedColumns: true,
							fixedHeader: true,
							colReorder: true,
							paging: true,
							"iDisplayLength": 50,
							scrollY: 380,
							dom: 'Bfrtip',
							buttons: [
								'copy', 'csv', 'excel', 'pdf', 'print'
							],
							data : JSON.parse(dt[1]),
							"columnDefs": [
								{"className": "dt-right", "targets": JSON.parse(dt[2]), render: $.fn.dataTable.render.number(',', '.', 0, '')}
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



function pivot(tipe) {
	jenislaporan = document.getElementById('jenislaporan').value;
	row = document.getElementById('row').value;
	col = document.getElementById('col').value;
	val = document.getElementById('val').value;
	if(val=='' || col =='' || row==''){
		document.getElementById('fileterpivot').style.display="";
	}
	
	validate([
        ["jenislaporan","Jenis Laporan tidak boleh kosong."],
        ["row","Baris untuk tampilan pivot tidak boleh kosong."],
        ["col","Kolom untuk tampilan pivot tidak boleh kosong."],
        ["val","Data untuk tampilan pivot tidak boleh kosong."]
    ]);
	
	
	parameter = getParameterInput();
	param  = '';
    param += "&tipe=" + tipe + "&parameter=" + parameter;
	param += '&jenislaporan=' + jenislaporan;
	param += '&row=' + row;
	param += '&col=' + col;
	param += '&val=' + val;
	param += '&method=pivot';
	
	tujuan = 'user_slave_customreport.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {					
					isi = con.responseText.split("####");
					document.getElementById('tombolexport').style.display="";
					document.getElementById('fileterpivot').style.display="none";
					//document.getElementById('output').innerHTML=con.responseText;
					
					//alertify.alert(con.responseText);
					$(function(){
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
						$("#output").pivotUI(JSON.parse(isi[0]),{
							dataClass: dataClass,
							renderers: renderers,
							aggregators: my_aggregators,
							rows: [isi[1]],
							cols: [isi[2]],
							aggregatorName: "Integer Sum",
							vals: [isi[3]],
							rendererName: "Table With Subtotal",
							rendererOptions: {
								rowSubtotalDisplay: {
									displayOnTop: false
								}
							}
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
	var nama = document.getElementById('jenislaporan');
	var filename = nama.options[nama.selectedIndex].text; 
	
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
	if(document.getElementById('jenislaporan')!=undefined){		
		var nama = document.getElementById('jenislaporan');
		var tipe = nama.options[nama.selectedIndex].text; 
		var namafile=tipe;
	}else{
		var namafile='pivot';
	}
	
	doc = new jsPDF('l','pt','A4')
	doc.autoTable({ 
		html: '#pvtTable',
		theme: 'grid',
		useCss: true,
		// headStyles: { 
			// fillColor: [39, 83, 112],
			// halign: 'center',
			// fontStyle: 'bold',
			// fontSize: 10,
			// textColor: [255, 255, 255]
		// },
		// bodyStyles: { 
			// cellWidth: 'wrap',
			// fontSize: 9,
			// textColor: [0, 0, 0]
		// }
		//columnStyles: { text: { cellWidth: 'auto' } },
	})
	
	doc.save(namafile+'.pdf');
}