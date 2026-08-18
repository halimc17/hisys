function simpan(){
    jenisvhc        = document.getElementById('jenisvhc').value;
    namajenisvhc    = document.getElementById('namajenisvhc').value;
    noakun          = document.getElementById('noakun').value;
    kelompok        = document.getElementById('kelompokvhc').options[document.getElementById('kelompokvhc').selectedIndex].value;
    met             = document.getElementById('method').value;
    var file        = document.getElementById("upload").files[0];
    if (trim(jenisvhc) == '') {
        alert('Tipe tidak boleh kosong');
        document.getElementById('jenisvhc').focus();
    } else {
        // if (getValue('upload') != "") {
            if (confirm('Apakah anda yakin ingin menyimpan data ...?')) {
                jenisvhc = trim(jenisvhc);
                namajenisvhc = trim(namajenisvhc);
                param = 'jenisvhc=' + jenisvhc + '&namajenisvhc=' + namajenisvhc + '&method=' + met;
                param += '&kelompok=' + kelompok + '&noakun=' + noakun;
                tujuan = 'vhc_slave_5jeniskendaraan.php';
                post_response_text(tujuan, param, respog);
            }
        // }else{
        //     alert("File upload tidak ada.");
        //     return;
        // }
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    submitfile();
                    alertify.popup().destroy();
                    alertify.set("notifier", "position", "top-right");
                    alertify.set("notifier", "delay", 2);
                    alertify.success("Berhasil Simpan");
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
    tujuan = 'vhc_slave_5jeniskendaraan.php';
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
							paging: true,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 10,
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
	
	tujuan = 'vhc_slave_5jeniskendaraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('40%','30%');
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

function cancelVhc() {
    setValue2('noakun',null);
    setValue2('jenisvhc',null);
    setValue2('namajenisvhc',null);
    document.getElementById('jenisvhc').disabled = false;
    document.getElementById('method').value = 'insert';
    document.getElementById('hasilupload').value = '';
    alertify.popup().destroy();
}

function editdata(jenis,kode, nama, noakun, kelompok, file) {
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'vhc_slave_5jeniskendaraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('40%','30%');
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
                    document.getElementById('method').value = 'update';
                    setValue2('kelompokvhc',kelompok);
                    document.getElementById('jenisvhc').value = kode;
                    document.getElementById('namajenisvhc').value = nama;
                    document.getElementById('noakun').value = noakun;
                    if(file == ''){
                        file = "kosong";
                    }
                    document.getElementById('hasilupload').value = file;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function isifile(doc, ev) {
    param = 'method=isifile' + '&doc=' + doc;

    tujuan = 'vhc_slave_5jeniskendaraan.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    title = "Gambar Tipe";
                    alertify.popup().destroy();
                    alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var kvhc = document.getElementById("kelompokvhc").value;
    var jvhc = document.getElementById("jenisvhc").value;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("kvhc", kvhc);
    formdata.append("jvhc", jvhc);

    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }

    var con = createXMLHttpRequest();
    con.open("POST", "vhc_slave_5jeniskendaraan.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cancelVhc();
                    document.getElementById("upload").value = "";
					alertify.popup().destroy();
					alertify.alert("Data Berhasil disimpan.");
                    setTimeout(function() {
                        loaddata();
                    }, 100);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}