function loaddata() {
	param = 'method=loaddata';
    tujuan = 'pabrik_slave_5sortasi.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
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
							paging: false,
							// "pagingType": "simple_numbers",
							columnDefs: [
								{"targets": "no-sort", "orderable": false}
							],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							// "iDisplayLength": 1,
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
										newdata('New Data');
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
	
	tujuan = 'pabrik_slave_5sortasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('40%','50%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true,
							width: 'auto'
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '30px'
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

function editdata(jenis,kode,uraian,janjang,persen,kg,persentasepengali,stat){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'pabrik_slave_5sortasi.php';
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
							dropdownAutoWidth:true,
							width: 'auto'
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
					setValue2('uraian',uraian);
					if(janjang==''){
						document.getElementById('janjang').checked=false;
					}
					if(persen==''){
						document.getElementById('persen').checked=false;
					}
					if(kg==''){
						document.getElementById('kg').checked=false;
					}
					setValue2('status',stat);
					setValue2('persentasepengali',persentasepengali);
					setValue2('method','update');
					document.getElementById('kode').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	kode=document.getElementById('kode').value;
	uraian=document.getElementById('uraian').value;
	if(document.getElementById('janjang').checked==true){
		janjang=1;
	}else{
		janjang=0;
	}
	if(document.getElementById('persen').checked==true){
		persen=1;
	}else{
		persen=0;
	}
	if(document.getElementById('kg').checked==true){
		kg=1;
	}else{
		kg=0;
	}
	stat=document.getElementById('status').value;
	method=document.getElementById('method').value;
	
	validate([
        ["kode",bahasa.kode+" "+bahasa.diisi],
        ["uraian",bahasa.uraian+" "+bahasa.diisi]
	]);
	
	param  = '';
	param += '&kode=' + kode;
	param += '&uraian=' + uraian;
	param += '&janjang=' + janjang;
	param += '&persen=' + persen;
	param += '&persentasepengali=' + getValue('persentasepengali');
	param += '&kg=' + kg;
	param += '&status=' + stat;
	param += '&method=' + method;
	
	tujuan = 'pabrik_slave_5sortasi.php';
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
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}