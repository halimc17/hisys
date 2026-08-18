function loaddata(){
	param   = 'method=loaddata';
	tujuan  = 'vhc_slave_5jenisKegiatan.php';
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
							"iDisplayLength": 50,
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

function editdata(jenis, kode, nama, satuan, noakun, tipe,kelvhc,jnsvhc,nmjnsvhc,Kelompok,kegiatankebun,statuskeg) {
    param  = '';
	param += '&jenis=' + jenis;
	param += '&satuan=' + satuan;
	param += '&method=addnew';
	
	tujuan = 'vhc_slave_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('700px','500px');
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
					document.getElementById('kodekegiatan').value = kode;
					document.getElementById('namakegiatan').value = nama;
					document.getElementById('jnsvhc').innerHTML="<option value='"+ jnsvhc +"'>"+ nmjnsvhc +"</option>";
					setValue2('satuan',satuan);
					setValue2('tipe',tipe);
					setValue2('kelvhc',kelvhc);
					setValue2('jnsvhc',jnsvhc);
					setValue2('kelompok',Kelompok);
					setValue2('statuskeg',statuskeg);
					document.getElementById('method').value = 'update';
					getnoakun(kelompok);
					
					setTimeout(function(){
						setValue2('noakun',noakun);
						setTimeout(function(){
							setValue2('kegiatankebun',kegiatankebun);
						}, 500);
					}, 500);
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
	
	tujuan = 'vhc_slave_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('700px','500px');
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

function getjenisvhc(){
	kelvhc  = document.getElementById('kelvhc').value;
	methodx = document.getElementById('method').value;
	param   = 'kelvhc=' + kelvhc + '&method=getjenisvhc';
	tujuan  = 'vhc_slave_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jnsvhc').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(kodekegiatan) {
	param   = 'method=delete';
	param   += '&kodekegiatan=' + kodekegiatan;
	tujuan  = 'vhc_slave_5jenisKegiatan.php';
	if(confirm("Anda yakin untuk menghapus !")){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function getKode() {
	noakun  = document.getElementById('noakun').options[document.getElementById('noakun').selectedIndex].value;
	methodx = document.getElementById('method').value;
	param   = 'noakun=' + noakun + '&method=getKode&methodx=' + methodx;
	tujuan  = 'vhc_slave_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					if(methodx!='update'){				
						document.getElementById('kodekegiatan').value = data[0];
					}
					if(data[1]!=''){
						$("#kegiatankebun").html(data[1]);
						$("#kegiatankebun").prop("disabled",false);
					}else{
						$("#kegiatankebun").html();
						$("#kegiatankebun").prop("disabled",true);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getnoakun(kelompok) {
	if(kelompok == undefined){
		kelompok = document.getElementById('kelompok').value;
	}else{
		kelompok = kelompok;
	}
	param   = 'kelompok=' + kelompok + '&method=getnoakun';
	tujuan  = 'vhc_slave_5jenisKegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {					
					document.getElementById('noakun').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function simpan() {
	kodekegiatan    = document.getElementById('kodekegiatan').value;
	namakegiatan    = document.getElementById('namakegiatan').value;
	kelompok    	= document.getElementById('kelompok').value;
	satuan          = document.getElementById('satuan').value;
	noakun          = document.getElementById('noakun').value;
	tipe            = document.getElementById('tipe').value;
	met             = document.getElementById('method').value;
	jnsvhc          = document.getElementById('jnsvhc').value;
	kelvhc          = document.getElementById('kelvhc').value;
	kegiatankebun   = document.getElementById('kegiatankebun').value;
	statuskeg   	= document.getElementById('statuskeg').value;
	
	if(kelompok == ''){
		alert('Kelompok kegiatan wajib di pilih.');
		document.getElementById('kelompok').focus();
    }else if(noakun == ''){
		alert('Nomor Akun wajib di pilih.');
		document.getElementById('noakun').focus();
    }else if (namakegiatan == '') {
		alert('Nama Kegiatan wajib di isi.');
		document.getElementById('kodekegiatan').focus();
	}else if(satuan == ''){
		alert('Satuan wajib di pilih.');
		document.getElementById('satuan').focus();
    }else {
		kodekegiatan = trim(kodekegiatan);
		namakegiatan = trim(namakegiatan);
		param = 'kodekegiatan=' + kodekegiatan + '&namakegiatan=' + namakegiatan + '&satuan=' + satuan + '&method=' + met + '&noakun=' + noakun + '&kelompok=' + kelompok + '&tipe=' + tipe;
		param += '&jnsvhc=' + jnsvhc;
		param += '&kelvhc=' + kelvhc;
		param += '&kegiatankebun=' + kegiatankebun;
		param += '&statuskeg=' + statuskeg;
		tujuan = 'vhc_slave_5jenisKegiatan.php';
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
                    alertify.popup().destroy();
                    alertify.alert("Data Berhasil disimpan.");
                    setTimeout(function() {
						loaddata();
					},100);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function cancel() {
	document.getElementById('kodekegiatan').disabled = true;
	document.getElementById('kodekegiatan').value = '';
	document.getElementById('namakegiatan').value = '';
    setValue2('satuan',null);
    setValue2('noakun',null);
    setValue2('kelompok',null);
    setValue2('kelvhc',null);
    setValue2('jnsvhc',null);
    setValue2('tipe',null);
    setValue2('statuskeg','1');
	document.getElementById('method').value = 'insert';
}