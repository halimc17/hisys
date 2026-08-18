const btnpreview = document.getElementById('btnpreview');
if (btnpreview) {
	btnpreview.addEventListener('click', function () {
	  loaddata();
	});
}

function loaddata() {
	pt = getValue('pt');
	unit = getValue('unit');
	tanggal = getValue('tanggal');
	tanggal2 = getValue('tanggal2');
	jenis = getValue('jenis');
	
	validate([
		["pt","PT harus dipilih."]
	]);

    param = 'method=loaddata&pt='+pt+'&unit='+unit+'&tanggal='+tanggal+'&tanggal2='+tanggal2+'&jenis='+jenis;
    tujuan = 'keu_2pph_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							ordering: false,
							fixedHeader: true,
							// pake paging atau tidak
							paging: false,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							// "iDisplayLength": 10,
							// tinggi / height
							scrollY: '45vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
							//select: true,
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['csv', 'excel', 'print']
						});
						
						//double click untuk freeze column
						$(table.table().container()).on('dblclick', 'td', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
										//   rightColumns: 1
									}); 
							//console.log('Row Index = ' + row.index());
						});
						
						//right click untuk freeze column
						$(table.table().container()).on('dblclick', 'th', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
									}); 
							//console.log('Row Index = ' + row.index());
						});	
					} );
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

$('#pt').on("select2:select", function(e) { 
	getunit();
});

function getunit(){
	pt = getValue('pt');
	
	param='method=getunit&pt='+pt;
    tujuan='keu_2pph_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					document.getElementById('unit').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function detailkasbank(notransaksi,page=0){
	method = 'formajukan';
	param='';
	param += '&notransaksi=' + notransaksi + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'keu_kasdanbank_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
                   alertify.popup().set({'title':'Detail','resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 