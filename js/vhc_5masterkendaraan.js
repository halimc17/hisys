function del(kelompok,kegiatan){

	param = 'method=delete';
	param += '&kelompok=' + kelompok;
	param += '&kodekeg=' + kegiatan;
	
	
	tujuan='vhc_slave_5masterkendaraan.php';
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
    ob = document.getElementById('kodeorg');
    if(ob.value == ''){
        alert('Kode Organisasi (Owner) harus dipilih !');
        return false;
    }
    kodeorg = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kelompokvhc');
    if(ob.value == ''){
        alert('Kode Kelompok harus dipilih !');
        return false;
    }
    
    kelompokvhc = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('jenisvhc');
    if(ob.value == ''){
        alert('Tipe Mesin,Kend & Alat Berat harus dipilih !');
        return false;
    }
    jenisvhc = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kodebarang');
    kodebarang = ob.options[ob.selectedIndex].value;
    ob = document.getElementById('kepemilikan');
    kepemilikan = ob.options[ob.selectedIndex].value;
    kodetraksi = document.getElementById('kodetraksi').value;
    ob = document.getElementById('kodeasset');
    kodeasset = ob.options[ob.selectedIndex].value;

    kodevhc = trim(document.getElementById('kodevhc').value);
    tahunperolehan = trim(document.getElementById('tahunperolehan').value);
    beratkosong = trim(document.getElementById('beratkosong').value);
    nomorrangka = trim(document.getElementById('nomorrangka').value);
    nomormesin = trim(document.getElementById('nomormesin').value);
    detailvhc = trim(document.getElementById('detailvhc').value);
    method = trim(document.getElementById('method').value);
    tglakhirstnk = document.getElementById('tglakhirstnk').value;
    tglakhirkir = document.getElementById('tglakhirkir').value;
    tglakhirijinbm = document.getElementById('tglakhirijinbm').value;
    tglakhirijinang = document.getElementById('tglakhirijinang').value;
    nopol = document.getElementById('nopol').value;
    tahunproduksi = document.getElementById('tahunproduksi').value;
    warna = document.getElementById('warna').value;
    tglakhirleasing = document.getElementById('tglakhirleasing').value;
    tglakhirasuransi = document.getElementById('tglakhirasuransi').value;
    nobpkb = document.getElementById('nobpkb').value;
    statusvhc = document.getElementById('statusvhc').checked;
    kodesupplier = document.getElementById('kodesupplier').value;
    if (trim(kelompokvhc) == '') {
        alert('Kode Kelompok harus dipilih !');
        document.getElementById('kelompokvhc').focus();
        return;
    }
    if (trim(kodeorg) == '') {
        alert('Kodeorg Organisasi harus dipilih !');
        document.getElementById('kodeorg').focus();
        return;
    }
    if (trim(jenisvhc) == '') {
        alert('Tipe Mesin,Kend & Alat Berat harus dipilih !');
        document.getElementById('jenisvhc').focus();
        return;
    }
    // if(jenisvhc == 'DT'){
    //     if (trim(kapasitasangkut) == '') {
    //         alert('Kapasitas Angkutan tidak boleh kosong');
    //         document.getElementById('kapasitasangkut').focus();
    //         return;
    //     }
    //     if (trim(minangkut) == '') {
    //         alert('Minimal Angkutan tidak boleh kosong');
    //         document.getElementById('minangkut').focus();
    //         return;
    //     }
    //     if (trim(maxangkut) == '') {
    //         alert('Maksimal Angkutan tidak boleh kosong');
    //         document.getElementById('maxangkut').focus();
    //         return;
    //     }
    // }
    if(tahunperolehan == ''){
        alert('Tahun perolehan tidak boleh kosong');
        document.getElementById('tahunperolehan').focus();
        return;
    }
    if (tahunperolehan.length != 4) {
        alert('Tahun Perolehan Harus 4 Digit');
        document.getElementById('tahunperolehan').focus();
        return;
    }
    if(tahunproduksi == ''){
        alert('Tahun produksi tidak boleh kosong');
        document.getElementById('tahunproduksi').focus();
        return;
    }
    if(tahunproduksi>tahunperolehan){
        alert('Tahun produksi lebih besar dari tahun perolehan');
        document.getElementById('tahunproduksi').focus();
        return;
    }
    if (tahunproduksi.length != 4) {
        alert('Tahun Produksi Harus 4 Digit');
        document.getElementById('tahunproduksi').focus();
        return;
    }
    if(statusvhc == true){
        statusvhc = 1;
    }else{
        statusvhc = 0;
    }

    if (confirm('Apakah anda yakin ingin menyimpan data..?')) {
        param = 'kodeorg=' + kodeorg + '&kelompokvhc=' + kelompokvhc + '&method=' + method;
        param += '&jenisvhc=' + jenisvhc + '&kodevhc=' + kodevhc;
        param += '&tahunperolehan=' + tahunperolehan;
        param += '&kodeasset=' + kodeasset;
        param += '&nobpkb=' + nobpkb;
        param += '&statusvhc=' + statusvhc + '&kodesupplier=' + kodesupplier;
        param += '&beratkosong=' + beratkosong + '&nomorrangka=' + nomorrangka;
        param += '&nomormesin=' + nomormesin + '&detailvhc=' + detailvhc;
        param += '&kodebarang=' + kodebarang + '&kepemilikan=' + kepemilikan + '&kodetraksi=' + kodetraksi;
        param += '&tglakhirstnk=' + tglakhirstnk + '&tglakhirkir=' + tglakhirkir;
        param += '&tglakhirijinbm=' + tglakhirijinbm + '&tglakhirijinang=' + tglakhirijinang;
        param += '&nopol=' + nopol + '&tahunproduksi=' + tahunproduksi + '&warna=' + warna + '&tglakhirleasing=' + tglakhirleasing + '&tglakhirasuransi=' + tglakhirasuransi;
        tujuan = 'vhc_slave_5masterkendaraan.php';
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
                    alertify.set('notifier','position', 'top-right');
                    alertify.set('notifier','delay', 4);
                    if(method == 'insert'){
                        alertify.success("Data Berhasil disimpan dengan kodevhc "+con.responseText+" ");
                    }else{
                        alertify.success("Kodevhc  "+con.responseText+" Berhasil diubah.  ");
                    }
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getNotransaksi() {
    jenisvhc    = document.getElementById('jenisvhc').options[document.getElementById('jenisvhc').selectedIndex].value;
    kodeorg     = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    param       = 'kodeorg=' + kodeorg + '&jenisvhc=' + jenisvhc + '&method=getNotransaksi';
    tujuan      = 'vhc_slave_5masterkendaraan.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodevhc').value = trim(con.responseText)
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loadJenis(kelompok) {
    param = 'kelompok=' + kelompok;
    param += '&method=loadjenis';
    tujuan = 'vhc_slave_5masterkendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('jenisvhc').innerHTML = con.responseText;
                    getList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getList(kodeasset) {
    if (typeof kodeasset == 'undefined'){
        kodeasset = "";
	}
	kodeorg    = document.getElementById('kodeorg').value;
	kelompokvhc= document.getElementById('kelompokvhc').value;
	jenisvhc   = document.getElementById('jenisvhc').value;
    param      = 'kelompokvhc=' + kelompokvhc + '&jenisvhc=' + jenisvhc + '&kodeorg=' + kodeorg + '&method=getList';

    if (kodeasset != ''){
        param += '&kodeasset=' + kodeasset;
	}
    tujuan = 'vhc_slave_5masterkendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodeasset').innerHTML = con.responseText;
                    // loaddata();
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
    tujuan = 'vhc_slave_5masterkendaraan.php';
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
							stateSave: true,
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
							dom: 'Blfrtip',
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
	
	tujuan = 'vhc_slave_5masterkendaraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('70%','60%');
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

function cancelMasterVhc() {
    setValue2('kelompokvhc',null);
    setValue2('jenisvhc',null);
    setValue2('kodeorg',null);
    setValue2('kodebarang',null);
    setValue2('kodeasset',null);
    setValue2('kepemilikan',null);
    document.getElementById('kodevhc').disabled = true;
    document.getElementById('kodevhc').value = '';
    document.getElementById('tahunperolehan').value = '';
    document.getElementById('tglakhirstnk').value = '';
    document.getElementById('tglakhirkir').value = '';
    document.getElementById('tglakhirijinbm').value = '';
    document.getElementById('tglakhirijinang').value = '';

    document.getElementById('beratkosong').value = '';
    document.getElementById('nomorrangka').value = '';
    document.getElementById('nobpkb').value = '';
    document.getElementById('nomormesin').value = '';
    document.getElementById('detailvhc').value = '';

    document.getElementById('nopol').value = '';
    document.getElementById('tahunproduksi').value = '';
    document.getElementById('warna').value = '';
    document.getElementById('tglakhirleasing').value = '';
    document.getElementById('tglakhirasuransi').value = '';
    document.getElementById('method').value = 'insert';
    loaddata();
    alertify.popup().destroy();
}

function deleteMasterVhc(kodeorg, kelompokvhc, jenisvhc, kodevhc) {
    method = 'delete';
    if (confirm('Deleting ' + kodevhc + ' ..?')) {
        if (confirm('Are you sure..?')) {
            param = 'kodeorg=' + kodeorg + '&kelompokvhc=' + kelompokvhc + '&method=' + method;
            param += '&jenisvhc=' + jenisvhc + '&kodevhc=' + kodevhc;
            tujuan = 'vhc_slave_5masterkendaraan.php';
            post_response_text(tujuan, param, respog);
        }
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    res = con.responseText;
                    res = res.split('#####');
                    opt = JSON.parse(res[1]);
                    document.getElementById('container').innerHTML = res[0];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deAktif(kdvhc, stat) {
    method = 'deactive';
    dert = "";
    if (stat == '1') {
        dert = "Menonaktifkan";
    } else {
        dert = "Mengaktifkan";
    }
    
    alertify.confirm(dert + ' ' + kdvhc + ' ..?',
        function(){
            param = 'method=' + method + '&kodevhc=' + kdvhc;
            param += '&status=' + stat;
            tujuan = 'vhc_slave_5masterkendaraan.php';
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );

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

function editdata(jenis, kodeorg, kelompokvhc, jenisvhc, kodevhc, beratkosong, nomorrangka, nobpkb, nomormesin, tahunperolehan,kodebarang, kepemilikan, kodetraksi, tglakhirstnk, tglakhirkir, tglakhirijinbm, tglakhirijinang, kodeasset, detailvhc, nopol, tahunproduksi, warna, tglakhirleasing, tglakhirasuransi, status) {
    param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'vhc_slave_5masterkendaraan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('75%','60%');
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

                    // document.getElementById('kodevhc').disabled = true;
                    setValue2('kodetraksi',kodetraksi);
                    setValue2('kodevhc',kodevhc);
                    setValue2('kodeorg',kodeorg);
                    setValue2('kelompokvhc',kelompokvhc);
                    setTimeout(function(){
                        gettipekendaraan();
                        setTimeout(function() {
                            setValue2('jenisvhc',jenisvhc);
                            getList(kodeasset);
                            setValue2('kodeasset',kodeasset);
                        }, 800);
                    }, 300);
                    setValue2('kodebarang',kodebarang);
                    setValue2('kepemilikan',kepemilikan);
                    document.getElementById('tahunperolehan').value = tahunperolehan;
                    document.getElementById('beratkosong').value = beratkosong;
                    document.getElementById('nomorrangka').value = nomorrangka;
                    document.getElementById('nobpkb').value = nobpkb;
                    document.getElementById('nomormesin').value = nomormesin;
                    document.getElementById('detailvhc').value = '';
                    document.getElementById('method').value = 'update';
                    document.getElementById('tglakhirstnk').value = tglakhirstnk;
                    document.getElementById('tglakhirkir').value = tglakhirkir;
                    document.getElementById('tglakhirijinbm').value = tglakhirijinbm;
                    document.getElementById('tglakhirijinang').value = tglakhirijinang;
                    document.getElementById('detailvhc').value = detailvhc;
                    document.getElementById('nopol').value = nopol;
                    document.getElementById('tahunproduksi').value = tahunproduksi;
                    document.getElementById('warna').value = warna;
                    document.getElementById('tglakhirleasing').value = tglakhirleasing;
                    document.getElementById('tglakhirasuransi').value = tglakhirasuransi;
                    if(status == 1){
                        document.getElementById('statusvhc').checked = true;
                    }else{
                        document.getElementById('statusvhc').checked = false;
                    }
                    // getList(kodeasset);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettipekendaraan() {
	kelompokvhc   = document.getElementById('kelompokvhc').value;
    param      = 'kelompokvhc=' + kelompokvhc + '&method=gettipekendaraan';

    tujuan = 'vhc_slave_5masterkendaraan.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('jenisvhc').innerHTML = con.responseText;
                    getList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}