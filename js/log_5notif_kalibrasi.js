function posting(id){
	param = 'method=posting';
	param += '&id=' + id;
	
	tujuan='setup_slave_5notif_kalibrasi.php';
	alertify.confirm("posting","Anda yakin?",
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
function del(id){
	param = 'method=delete';
	param += '&id=' + id;
	
	tujuan='setup_slave_5notif_kalibrasi.php';
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
	method  = document.getElementById('method').value;
	
	// validate([
    //     ["kodeorg","Unit tidak boleh kosong."],
    //     ["tgl_kalibrasi","Tanggal Kalibrasi tidak boleh kosong."],
    //     ["tgl_kalibrasi_selanjutnya","Tanggal Kalibrasi Selanjutnya tidak boleh kosong."],
    //     ["kriteriaefil","Kriteria File tidak boleh kosong"],
    //     ["file","File tidak boleh kosong"]
	// ]);

	if(getValue('kodeorg') == '' || getValue('tgl_kalibrasi') == '' || getValue('tgl_kalibrasi_selanjutnya') == '' || getValue('kriteriaefil') == '' || getValue('upload') == ''){
		alert('Lengkapi Form Inputan...');
		return false;
	}

	
	
	// param  = '';
	// param += '&kodeorg=' + getValue('kodeorg');
	// param += '&tgl_kalibrasi=' + getValue('tgl_kalibrasi');
	// param += '&tgl_kalibrasi_selanjutnya=' + getValue('tgl_kalibrasi_selanjutnya');
	// param += '&kriteriaefil=' + getValue('kriteriaefil');
	// param += '&upload=' + getValue('upload');
	// param += '&file=' + file;
	// param += '&id=' + getValue('id');
	// param += '&method=' + method;
	
	// tujuan = 'setup_slave_5notif_kalibrasi.php';
	// post_response_text(tujuan, param, respog);
	
var file = document.getElementById("upload").files[0];
  var formdata = new FormData();
  formdata.append("file", file);
  formdata.append("upload", getValue("upload"));
  formdata.append("kodeorg", getValue("kodeorg"));
  formdata.append("tgl_kalibrasi", getValue("tgl_kalibrasi"));
  formdata.append("tgl_kalibrasi_selanjutnya", getValue("tgl_kalibrasi_selanjutnya"));
  formdata.append("kriteriaefil", getValue("kriteriaefil"));
  formdata.append("id", getValue("id"));
  if (getValue("upload") == "") {
    alert("warning : Upload file has been empty.");
    return false;
  }
  document.getElementsByClassName("mybutton").disabled = true;
  busy_on();
  var con = createXMLHttpRequest();
  con.open("POST", "setup_slave_5notif_kalibrasi.php?method="+method+"", true);
  con.onreadystatechange = eval(respon);
  con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-center');
					alertify.success("Done.",'3');
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
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'setup_slave_5notif_kalibrasi.php';
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
							//responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							ordering: false,
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 25,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Blfrtip',
							//select: true,
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['searchBuilder', 'excel', 'print',{
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
	
	tujuan = 'setup_slave_5notif_kalibrasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('800px','200px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
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

function editdata(jenis,id,kodeorg,tanggal,tanggalb){
	param  = '';
	param += '&jenis=' + jenis;	
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'setup_slave_5notif_kalibrasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('800px','200px');
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
					setValue2('method','update');
					setValue2('kodeorg',kodeorg);
					setValue2('tgl_kalibrasi',tanggal);
					setValue2('tgl_kalibrasi_selanjutnya',tanggalb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
