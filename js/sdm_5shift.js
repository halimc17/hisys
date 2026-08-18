function getnamashift(id,namashift){
	param = 'method=getnamashift';
	param += '&id=' + id;
	param += '&namashift=' + namashift;
	
	tujuan='sdm_slave_5shift.php';
	post_response_text(tujuan, param, respog);
	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('namashift').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function del(id){
	param = 'method=delete';
	param += '&id=' + id;
	
	tujuan='sdm_slave_5shift.php';
	alertify.confirm("Delete","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function simpan(){
	id        = document.getElementById('id').value;
	kodeorg   = document.getElementById('kodeorg').value;
	shift     = document.getElementById('shift').value;
	namashift = document.getElementById('namashift').value;
	masuk     = document.getElementById('masuk').value;
	keluar    = document.getElementById('keluar').value;
	keluar_ist= document.getElementById('istirahatmulai').value;
	masuk_ist = document.getElementById('istirahatselesai').value;
	toleransi = document.getElementById('toleransi').value;
	batasawal = document.getElementById('batasawal').value;
	tipe_shift = document.getElementById('tipe_shift').value;
	method    = document.getElementById('method').value;
	
	validate([
        ["kodeorg","Kodeorg tidak boleh kosong"],
        ["shift","Shift tidak boleh kosong"],
        ["namashift","Nama Shift tidak boleh kosong"],
        ["masuk","Jam masuk tidak boleh kosong"],
        ["keluar","Jam keluar tidak boleh kosong"],
        ["toleransi","Toleransi tidak boleh kosong"],
        ["batasawal","Batas awal tidak boleh kosong"]
	]);
	
	param  = '';
	param += '&id=' + id;
	param += '&kodeorg=' + kodeorg;
	param += '&shift=' + shift;
	param += '&namashift=' + namashift;
	param += '&masuk=' + masuk;
	param += '&keluar=' + keluar;
	param += '&keluar_ist=' + keluar_ist;
	param += '&masuk_ist=' + masuk_ist;
	param += '&toleransi=' + toleransi;
	param += '&batasawal=' + batasawal;
	param += '&tipe_shift=' + tipe_shift;
	param += '&method=' + method;
	
	tujuan = 'sdm_slave_5shift.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(method=='update'){
						alertify.popup().destroy();
					}
					alertify.alert("Done");
					document.getElementById('method').value='insert';
					document.getElementById('id').value='';
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
    param = 'method=loaddata';
    tujuan = 'sdm_slave_5shift.php';
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
							ordering: false,
							fixedHeader: true,
							paging: true,
							"iDisplayLength": 10,
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
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
							],
							//pakai option pagging
							dom: 'Blfrtip'
							
							//tanpa option pagging
							//dom: 'Bfrtip'
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
						
						//nomor urut
						table.on( 'order.dt search.dt', function () {
							table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
								cell.innerHTML = i+1;
							} );
						} ).draw();
						
						//tinggi option pagging
						$('select[name*="mytable_length"]').attr("style", "height:30px;");
					} );
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
	
	tujuan = 'sdm_slave_5shift.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('800px','400px');
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,kodeorg,shift,namashift,masuk,keluar_ist,masuk_ist,keluar,toleransi,id,batasawal,tipe_shift){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_5shift.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('800px','400px');
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
					
					setValue2('id',id);
					setValue2('kodeorg',kodeorg);
					setValue2('shift',shift);
					setValue2('namashift',namashift);
					setValue2('masuk',masuk);
					setValue2('keluar',keluar);
					setValue2('istirahatmulai',keluar_ist);
					setValue2('istirahatselesai',masuk_ist);
					setValue2('toleransi',toleransi);
					setValue2('batasawal',batasawal);
					setValue2('tipe_shift',tipe_shift);
					setValue2('method','update');
					document.getElementById('kodeorg').disabled=true;
					
					getnamashift(shift,namashift);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
