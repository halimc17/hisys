function loaddata() {
	param = 'method=loaddata';
    tujuan = 'master_unit_slave.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
                }else{
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
							"iDisplayLength": 20,
							// tinggi / height
							scrollY: '60vh',
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
							buttons: ['searchBuilder','csv', 'excel', 'print',{
								text: '<i class="fa fa-refresh"></i>',
								titleAttr: 'Refresh',
								action: function (e, dt, node, config){
									refreshdata('unit');
								}
							}]
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
					
					

					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getvalstt(no){
	kode=document.getElementById('kodeform_'+no).innerHTML;
	actstt = document.getElementById('actstt_'+no);
	if(actstt.checked == true){
		chk='1';
	}else{
		chk='0';
	}
	
	param  = '';
	param += '&status=' + chk;
	param += '&kode=' + kode;
	param += '&method=getvalstt';
	
	tujuan = 'master_unit_slave.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.set('notifier','position', 'top-right');
					alertify.success('Success').delay(3);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'master_unit_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});
					setValue2('status','1');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,kodewilayah,kodeperusahaan,kode,deskripsi,stat){
	param  = '';
	param += '&jenis=' + jenis;	
	param += '&kodewilayah=' + kodewilayah;
	param += '&kodeperusahaan=' + kodeperusahaan;
	param += '&kode=' + kode;
	param += '&deskripsi=' + deskripsi;
	param += '&status=' + stat;
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'master_unit_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});
					
					setValue2('kodewilayah',kodewilayah);
					setValue2('kode',kode);
					setValue2('deskripsi',deskripsi);
					setValue2('status',stat);
					setValue2('method','update');
					
					getcompany(kodewilayah,kodeperusahaan);
					
					document.getElementById('kode').disabled=true;
					document.getElementById('kodewilayah').disabled=true;
					document.getElementById('kodeperusahaan').disabled=true;
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	kodewilayah=document.getElementById('kodewilayah').value;
	kodeperusahaan=document.getElementById('kodeperusahaan').value;
	kode=document.getElementById('kode').value;
	deskripsi=document.getElementById('deskripsi').value;
	stat=document.getElementById('status').value;
	method=document.getElementById('method').value;
	
	validate([
        ["kodewilayah","Wilayah harus dipilih."],
        ["kodeperusahaan","Perusahaan harus dipilih."],
        ["kode","Kode Unit harus diisi"],
        ["deskripsi","Nama Unit harus diisi"],
        ["status","Status tidak boleh kosong"]
	]);
	
	param  = '';
	param += '&kodewilayah=' + kodewilayah;
	param += '&kodeperusahaan=' + kodeperusahaan;
	param += '&kode=' + kode;
	param += '&deskripsi=' + deskripsi;
	param += '&status=' + stat;
	param += '&method=' + method;
	
	tujuan = 'master_unit_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert("Done");
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getcompany(kodewilayah,kodeperusahaan){
	param  = '';
	param += '&kodewilayah=' + kodewilayah;
	param += '&kodeperusahaan=' + kodeperusahaan;
	param += '&method=getcompany';
	
	tujuan = 'master_unit_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kodeperusahaan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}