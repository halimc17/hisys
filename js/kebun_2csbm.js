function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}
function hiderow(awal,akhir,sumber,tipe){
	if(tipe=='det'){
		rowid="row_det_";
	}else{
		rowid="row_";
	}
	
	if(sumber=='est'){
		dis = document.getElementById(rowid+awal).getAttribute("style");
	}
	awal = parseFloat(awal);
	akhir = parseFloat(akhir);
	
	for (var i=awal;i<=akhir;i++){
		if(sumber!='est'){
			dis = document.getElementById(rowid+i).getAttribute("style");
		}
		if(dis=="display:none" || dis=="display: none;"){
			document.getElementById(rowid+i).style.display="";
		}else{			
			document.getElementById(rowid+i).style.display="none";
		}
	}
}

function previewpica(kodeorg,divisi,periode,tipe,jenis){
	// width    = '';
	// height   = '';
	// title    = "Preview";
	// content = "<div id=container style=\"width:100%;max-height:385px;overflow:auto;\"></div>";
    // ev = 'event';
    // showDialog6(title, content, width, height, ev); 
	
	param = 'method=preview';
	param += '&tipe=' + tipe;
	param += "&kodeorg=" + kodeorg;
	param += "&divisi=" + divisi;
	param += "&periode=" + periode;
	param += "&jenis=" + jenis;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('container').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function detailExcel(kodeorg,divisi,periode,tipe,jenis){
	param = 'method=preview' + '&tipe=' + tipe;
	param += "&kodeorg=" + kodeorg;
	param += "&divisi=" + divisi;
	param += "&periode=" + periode;
	param += "&jenis=" + jenis;
	tujuan = 'kebun_slave_csbmissue.php' + "?" + param;
	if(tipe=='pdf'){
		width = '950';
		height = '400';
	}else{		
		width = '';
		height = '';
	}
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	if(tipe=='pdf'){
		showDialog1(title, content, width, height, ev);
	}else{
		showDialog6(title, content, width, height, ev);
	}
}

function getpopup(nmorg,periode,blok,kolom,tmtd,prod){
	param = '';
	param += '&blok=' + blok;
	param += "&method=" + kolom;
	param += "&tmtd=" + tmtd;
	param += "&prod=" + prod;
	param += "&periode=" + periode;
	
	tujuan = 'kebun_slave_csbmpopup.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail Blok "+nmorg,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function postExcel(val) {
	param = '';
	var e = val.split('##');
	for (i = 1; i < e.length; i++) {
		var tmp = document.getElementById(e[i]);
		if (i == 1) {
			param += e[i] + "=" + getValue(e[i]);
		} else {
			param += "&" + e[i] + "=" + getValue(e[i]);
		}
	}
	
	param += '&proses=excel';
	//tujuan = 'kebun_slave_2csbm.php';
	
	tujuan = 'kebun_slave_2csbm.php' + "?" + param;
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1("Excel",content,'150','100','event'); 	
		
	//printnopopup(tujuan+"?"+param);
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

function postPreview(val) {
	param = '';
	var e = val.split('##');
	for (i = 1; i < e.length; i++) {
		var tmp = document.getElementById(e[i]);
		if (i == 1) {
			param += e[i] + "=" + getValue(e[i]);
		} else {
			param += "&" + e[i] + "=" + getValue(e[i]);
		}
	}
	
	param += '&proses=preview';
	
	tujuan = 'kebun_slave_2csbm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML=con.responseText;
					
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							fixedColumns:   {
								leftColumns: 4
								// rightColumns: 2
							},
							fixedHeader: true,
							// pake paging atau tidak
							paging: false,
							ordering: false,
							columnDefs: [
								{"className": "dt-body-nowrap", "targets": [2,3]}
							],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							//"iDisplayLength": 50,
							// tinggi / height
							scrollX: true,
							scrollY: '50vh',
							scrollCollapse: true,
							//fixedColumns: true,
							//popup pencarian / filter
							dom: 'Bfrtip',
							searching: false,
							// buttons: [
								// {
									// extend: 'searchPanes',
									// config: {
										// cascadePanes: true
									// }
								// }
							// ]
							//end popup pencarian / filter
							
							//<!--popup pencarian / filter / like sql search-->
							language: {
								searchBuilder: {
									button: 'Filter',
								}
							},
							buttons: [{
									text: 'Show',
									action: function () {
										showfilter('new');
									}
								}
							]
							// dom: 'Bfrtip',
							//select: true
							// buttons: ['colvis']
							
							//tanpa popup
							// dom: 'QBfrtip',
							// buttons:['csv', 'excel', 'print'],
							//tanpa popup
							
							//<!--popup pencarian / filter / like sql search-->
							
						});
						
						//double click header untuk freeze column
						$(table.table().container()).on('dblclick', 'th', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
									}); 
						});
						
						$('td').attr("title", "double click pada header / judul untuk freeze column");
						
						//buat nomor urut
						// table.on( 'order.dt search.dt', function () {
							// table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
								// cell.innerHTML = i+1;
							// } );
						// } ).draw();
						//buat nomor urut
						
					} );
					
					document.getElementById('formfilter').style.display="none";
					document.getElementById('buttonshow').style.display="";
					document.getElementById('buttonshow').innerHTML="Show Form Filter";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showfilter(e){
	if(document.getElementById('formfilter').style.display=="none"){		
		document.getElementById('formfilter').style.display="block";
		document.getElementById('buttonshow').innerHTML="Hide Form Filter";
	}else{
		document.getElementById('formfilter').style.display="none";
		document.getElementById('buttonshow').innerHTML="Show Form Filter";
	}
}

function exportTableToExcel(tableID, filename = ''){
	var downloadLink;
	var dataType   = 'application/vnd.ms-excel';
	var tableSelect= document.getElementById(tableID);
		tableSelect.border='1';
	var x = tableSelect.querySelectorAll(".rowcontent"); 
	for(i=0;i<x.length;i++){
		x[i].style.display = "";
		r = x[i].getElementsByTagName("td");
		for(e=0;e<r.length;e++){
			r[e].style.backgroundColor = "";
		}
	}
	var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
	var filename='csbm';
	filename    = filename?filename+'.xls':'excel_data.xls';
	downloadLink= document.createElement("a");
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