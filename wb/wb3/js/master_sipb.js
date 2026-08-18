function loaddata() {
	param = 'method=loaddata';
    tujuan = 'master_sipb_slave.php';
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
							ordering: true,
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
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
									text: 'New',
									action: function () {
										newdata('new');
									}
								}
							]
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

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'master_sipb_slave.php';
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

function editdata(jenis,nodo,nokontrak,tanggal,produk,transportir,kuantitas,keterangan,stat){
	param  = '';
	param += '&jenis=' + jenis;	
	param += '&nodo=' + nodo;
	param += '&nokontrak=' + nokontrak;
	param += '&tanggal=' + tanggal;
	param += '&produk=' + produk;
	param += '&transportir=' + transportir;
	param += '&kuantitas=' + kuantitas;
	param += '&keterangan=' + keterangan;
	param += '&status=' + stat;
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'master_sipb_slave.php';
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
					
					setValue2('nodo',nodo);
					setValue2('nokontrak',nokontrak);
					setValue2('tanggal',tanggal);
					setValue2('produk',produk);
					setValue2('transportir',transportir);
					setValue2('kuantitas',kuantitas);
					setValue2('keterangan',keterangan);
					setValue2('status',stat);
					setValue2('method','update');
					document.getElementById('nodo').disabled=true;
					document.getElementById('nokontrak').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('produk').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('kuantitas').disabled=true;
					
					getcustomer(nokontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	nodo=document.getElementById('nodo').value;
	tanggal=document.getElementById('tanggal').value;
	nokontrak=document.getElementById('nokontrak').value;
	produk=document.getElementById('produk').value;
	transportir=document.getElementById('transportir').value;
	kuantitas=document.getElementById('kuantitas').value;
	keterangan=document.getElementById('keterangan').value;
	stat=document.getElementById('status').value;
	method=document.getElementById('method').value;
	
	validate([
        ["nodo","No. DO harus diisi."],
        ["nokontrak","No. Kontrak harus dipilih."],
        ["produk","Produk harus dipilih."],
        ["transportir","Transportir harus dipilih."],
        ["kuantitas","Kuantitas harus diisi."],
        ["status","Status tidak boleh kosong."]
	]);
	
	param  = '';
	param += '&nodo=' + nodo;
	param += '&tanggal=' + tanggal;
	param += '&nokontrak=' + nokontrak;
	param += '&produk=' + produk;
	param += '&transportir=' + transportir;
	param += '&kuantitas=' + kuantitas;
	param += '&keterangan=' + keterangan;
	param += '&status=' + stat;
	param += '&method=' + method;
	
	tujuan = 'master_sipb_slave.php';
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

function getcustomer(nokontrak){
	param  = '';
	param += '&nokontrak=' + nokontrak;
	param += '&method=getcustomer';
	
	tujuan = 'master_sipb_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('customer').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}