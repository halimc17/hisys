
function loaddata() {
    param = 'method=loaddata';
    tujuan = 'setup_slave_posting.php';

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
							ordering: true,
							fixedHeader: true,
							paging: true,
							"iDisplayLength": 10,
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Blfrtip',
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['csv', 'excel', 'print',{
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
					
					
					$('select[name*="mytable_length"]').attr("style", "height:30px;");
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
	
	tujuan = 'setup_slave_posting.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','50%');
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

					setValue2('kodeaplikasi','');
					setValue2('kodeaplikasiold','');
					setValue2('kodejabatan','');
					setValue2('kodejabatanold','');
					setValue2('mode','insert');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	kodeaplikasi     = document.getElementById('kodeaplikasi').value;
	kodeaplikasiold  = document.getElementById('kodeaplikasiold').value;
	kodejabatan      = document.getElementById('kodejabatan').value;
	kodejabatanold   = document.getElementById('kodejabatanold').value;
	method           = document.getElementById('mode').value;
	
	validate([
        ["kodeaplikasi","Kode aplikasi tidak boleh kosong."],
        ["kodejabatan","Kode jabatan tidak boleh kosong"]
	]);
	
	param = 'kodeaplikasi=' + kodeaplikasi;
	param += '&kodeaplikasiold=' + kodeaplikasiold;
	param += '&kodejabatan=' + kodejabatan;
	param += '&kodejabatanold=' + kodejabatanold;
	param += '&method=' + method;
	tujuan = 'setup_slave_posting.php';

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

function editdata(jenis,kodeaplikasi,kodejabatan){
	param = 'method=addnew';
	
	tujuan = 'setup_slave_posting.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','50%');
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
					
					setValue2('kodejabatan',kodejabatan);
					setValue2('kodeaplikasi',kodeaplikasi);
					setValue2('kodeaplikasiold',kodeaplikasi);
					setValue2('kodejabatanold',kodejabatan);
					
					setValue2('mode','update');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function del(kodeaplikasi,kodejabatan){
	param = 'method=delete';
	param += '&kodeaplikasi=' + kodeaplikasi;	
	param += '&kodejabatan=' + kodejabatan;	
	tujuan = 'setup_slave_posting.php';

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