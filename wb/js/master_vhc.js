function loaddata() {
	param = 'method=loaddata';
    tujuan = 'master_vhc_slave.php';
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
	
	tujuan = 'master_vhc_slave.php';
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

function editdata(jenis,kode,vendorcode,taramin,taramax,stat,nostnk,beratkendaraan){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&kode=' + kode;
	param += '&vendorcode=' + vendorcode;
	param += '&taramin=' + taramin;
	param += '&taramax=' + taramax;
	param += '&beratkendaraan=' + beratkendaraan;
	param += '&status=' + stat;
	param += '&nostnk=' + nostnk;
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'master_vhc_slave.php';
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
					
					setValue2('kode',kode);
					setValue2('vendorcode',vendorcode);
					setValue2('taramin',taramin);
					setValue2('beratkendaraan',beratkendaraan);
					setValue2('taramax',taramax);
					setValue2('status',stat);
					setValue2('nostnk',nostnk);
					setValue2('method','update');
					document.getElementById('vendorcode').disabled=true;
					document.getElementById('kode').disabled=true;
					document.getElementById('tipekendaraan').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	vendorcode=document.getElementById('vendorcode').value;
	kode=document.getElementById('kode').value;
	taramin=document.getElementById('taramin').value;
	beratkendaraan=document.getElementById('beratkendaraan').value;
	taramax=document.getElementById('taramax').value;
	stat=document.getElementById('status').value;
	nostnk=document.getElementById('nostnk').value;
	method=document.getElementById('method').value;
	
	validate([
        ["vendorcode","Transportir harus dipilih."],
        ["kode","Kode Kendaraan harus diisi"],
        ["status","Status tidak boleh kosong"],
        ["beratkendaraan","berat kendaraan tidak boleh kosong"]
	]);

	if(beratkendaraan <= 0){
		alert('Berat Kendaraan tidak boleh 0...');
		return false;
	}

	
	param  = '';
	param += '&vendorcode=' + vendorcode;
	param += '&kode=' + kode;
	param += '&taramin=' + taramin;
	param += '&beratkendaraan=' + beratkendaraan;
	param += '&taramax=' + taramax;
	param += '&status=' + stat;
	param += '&nostnk=' + nostnk;
	param += '&method=' + method;
	
	tujuan = 'master_vhc_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
                    alertify.success('Success');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}