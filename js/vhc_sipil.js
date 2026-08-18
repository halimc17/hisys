var showPerPage = 10;

function showAdd() {
    var workField = document.getElementById('workField');
    var param = "";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
					document.getElementById('detailField').style.display = 'none';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=showAdd', param, respon);
}

function addHead() {
	notransaksi = getValue('notransaksi');
    param = "&kodeorg="+getValue('kodeorg')+"&tanggal="+getValue('tanggal')+"&nikmandor="+getValue('nikmandor')+"&nikmandor1="+getValue('nikmandor1')+"&nikasisten="+getValue('nikasisten')+"&keranimuat="+getValue('keranimuat')+"&notransaksi="+notransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					if(notransaksi == ''){
						alert('Added Data Header');
						document.getElementById('notransaksi').value = con.responseText;
						document.getElementById('tanggal').disabled = true;
						detailFieldShow();
					}else{
						alert('Edited Data Header');
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=addHead', param, respon);
}

function detailFieldShow(){
	detailField = document.getElementById('detailField');
	param = "&notransaksi="+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.style.display = "";
                    detailField.innerHTML = con.responseText;
					loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=detailFieldShow', param, respon);
}

function pres_oc_getSatuan(){
	param = "&pres_kodekegiatan="+getValue('pres_kodekegiatan');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('pres_hasilkerja_satuan').value = con.responseText;
					getAbsMatKegiatan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=pres_oc_getSatuan', param, respon);
}

function getAbsMatKegiatan(){
	param = "&notransaksi="+getValue('notransaksi');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('abs_kodekegiatan').innerHTML = con.responseText;
                    document.getElementById('mat_kodekegiatan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=getAbsMatKegiatan', param, respon);
}

function pres_oc_getalokasi(sAlokasi){
	var pres_afdeling = document.getElementById('pres_afdeling').value;
	var pres_kodekegiatan = document.getElementById('pres_kodekegiatan').value;
	var kodeorg = document.getElementById('kodeorg').value;
	param = "&pres_afdeling="+pres_afdeling+"&pres_kodekegiatan="+pres_kodekegiatan+'&kodeorg='+kodeorg;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					document.getElementById('pres_alokasibiaya').innerHTML = con.responseText;
					vAlokasi = document.getElementById('pres_alokasibiaya');
					if(typeof sAlokasi !== 'undefined'){
						for(ard=0;ard<vAlokasi.length;ard++)
						{
							if(vAlokasi.options[ard].value==sAlokasi)
							{
								vAlokasi.options[ard].selected=true;
							}
						}
					}
					pres_oc_getSatuan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=pres_oc_getalokasi', param, respon);
}
function pres_oc_kegiatan(sKegiatan,sAlokasi){
	param = "&pres_afdeling="+getValue('pres_afdeling')+'&kodeorg='+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					document.getElementById('pres_kodekegiatan').innerHTML = con.responseText;
					vKegiatan = document.getElementById('pres_kodekegiatan');
					if(typeof sKegiatan !== 'undefined'){
						for(ard=0;ard<vKegiatan.length;ard++)
						{
							if(vKegiatan.options[ard].value==sKegiatan)
								{
									vKegiatan.options[ard].selected=true;
								}
						}
					}
					pres_oc_getalokasi(sAlokasi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=pres_oc_kegiatan', param, respon);
}
function pres_oc_afdeling(sKegiatan,sAlokasi){
	param = "&pres_afdeling="+getValue('pres_afdeling')+'&kodeorg='+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					document.getElementById('pres_kodekegiatan').innerHTML = con.responseText;
					vKegiatan = document.getElementById('pres_kodekegiatan');
					if(typeof sKegiatan !== 'undefined'){
						for(ard=0;ard<vKegiatan.length;ard++)
						{
							if(vKegiatan.options[ard].value==sKegiatan)
								{
									vKegiatan.options[ard].selected=true;
								}
						}
					}
					pres_oc_getalokasi(sAlokasi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=pres_oc_afdeling', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi_'+num);
    var param = "numRow="+num+"&notransaksi="+trans.innerHTML;
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					workField.innerHTML = con.responseText;
					document.getElementById('tanggal').disabled = true;
					detailFieldShow();
					//showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=showEdit', param, respon);
}

function deleteData(num) {
	var trans = document.getElementById('notransaksi_'+num);
    var param = "notransaksi="+trans.innerHTML;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					var tmp = document.getElementById('tr_'+num);
                    tmp.parentNode.removeChild(tmp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure to delete this item? '+trans.innerHTML))
		post_response_text('vhc_slave_sipil.php?proses=delete', param, respon);
}

function addPres(){
	var pres_jumlahhk = '0';
	var pres_hasilkerja = '0';
	if(getValue('pres_jumlahhk') == ''){
		pres_jumlahhk = '0';
	}else{
		pres_jumlahhk = getValue('pres_jumlahhk');
	}
	if(getValue('pres_hasilkerja') == ''){
		pres_hasilkerja = '0';
	}else{
		pres_hasilkerja = getValue('pres_hasilkerja');
	}
	if(pres_jumlahhk == '0' && pres_hasilkerja == '0'){
		alert('Isilah Field HK atau Hasil kerja')
		return false;
	}
	param = "&notransaksi="+getValue('notransaksi')+"&pres_kodekegiatan="+getValue('pres_kodekegiatan')+'&pres_alokasibiaya='+getValue('pres_alokasibiaya')+'&pres_kodesegment='+getValue('pres_kodesegment')+'&pres_hasilkerja='+getValue('pres_hasilkerja')+'&pres_jumlahhk='+getValue('pres_jumlahhk')+'&pres_method='+getValue('pres_method')+'&pres_upahpremi='+getValue('pres_upahpremi')+'&nourut='+getValue('pres_nourut');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('pres_alokasibiaya').innerHTML = con.responseText;
                    loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=addPres', param, respon);
}

function addAbs(){
	param = "&notransaksi="+getValue('notransaksi')+"&abs_nik="+getValue('abs_nik')+'&abs_jhk='+getValue('abs_jhk')+'&abs_umr='+getValue('abs_umr')+'&abs_insentif='+getValue('abs_insentif')+'&abs_method='+getValue('abs_method')+'&abs_kodekegiatan='+getValue('abs_kodekegiatan')+'&totalPresHk='+getValue('totalPresHk')+'&totalAbsHk='+getValue('totalAbsHk')+'&abs_temp_jhk='+getValue('abs_temp_jhk')+'&abs_temp_nik='+getValue('abs_temp_nik')+'&tanggal='+getValue('tanggal');;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('pres_alokasibiaya').innerHTML = con.responseText;
					loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=addAbs', param, respon);
}

function addMat(){
	param = "&notransaksi="+getValue('notransaksi')+"&mat_kodegudang="+getValue('mat_kodegudang')+'&mat_kodebarang='+getValue('mat_kodebarang')+'&mat_kwantitas='+getValue('mat_kwantitas')+'&kwantitasha='+getValue('kwantitasha')+'&mat_method='+getValue('mat_method')+'&mat_kodekegiatan='+getValue('mat_kodekegiatan');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('pres_alokasibiaya').innerHTML = con.responseText;
                    loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=addMat', param, respon);
}

function loadAllListTab(){
	loadListPrestasi();
}

function loadListPrestasi(){
	param = "&notransaksi="+getValue('notransaksi');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('listPrestasi').innerHTML = con.responseText;
					loadListAbsensi();
                    // document.getElementById('pres_kodekegiatan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=loadListPrestasi', param, respon);
}

function loadListAbsensi(){
	param = "&notransaksi="+getValue('notransaksi');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('listAbsensi').innerHTML = con.responseText;
					loadListMaterial();
					getAbsMatKegiatan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=loadListAbsensi', param, respon);
}

function loadListMaterial(){
	param = "&notransaksi="+getValue('notransaksi');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('listMaterial').innerHTML = con.responseText;
					getTotal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=loadListMaterial', param, respon);
}

function cancelPres(){
	document.getElementById('pres_afdeling').selectedIndex = 0;
	pres_oc_afdeling();
	document.getElementById('pres_kodekegiatan').selectedIndex = 0;
	document.getElementById('pres_kodesegment').value = '';
	document.getElementById('pres_kodesegment_name').value = '';
	document.getElementById('pres_hasilkerja').value = 0;
	document.getElementById('pres_jumlahhk').value = 0;
	document.getElementById('pres_umr').value = 0;
	document.getElementById('pres_upahpremi').value = 0;
	document.getElementById('pres_nourut').value = '';
	document.getElementById('pres_method').value = 'insert';
}

function cancelAbs(){
	document.getElementById('abs_kodekegiatan').selectedIndex = 0;
	document.getElementById('abs_kodekegiatan').disabled = false;
	document.getElementById('abs_nik').selectedIndex = 0;
	document.getElementById('abs_nik').disabled = false;
	document.getElementById('abs_nik_find').style.display = '';
	document.getElementById('abs_jhk').value = 0;
	document.getElementById('abs_umr').value = 0;
	document.getElementById('abs_insentif').value = 0;
	document.getElementById('abs_temp_jhk').value = '';
	document.getElementById('abs_temp_nik').value = '';
	document.getElementById('abs_nourut').value = '';
	document.getElementById('abs_method').value = 'insert';
}

function cancelMat(){
	document.getElementById('mat_kodekegiatan').selectedIndex = 0;
	document.getElementById('mat_kodegudang').selectedIndex = 0;
	document.getElementById('mat_kodebarang').value = '';
	document.getElementById('mat_kodebarang_name').value = '';
	document.getElementById('mat_kwantitas').value = 0;
	document.getElementById('mat_kodegudang').disabled = false;
	document.getElementById('mat_kodekegiatan').disabled = false;
	document.getElementById('mat_kodegudang_find').style.display = '';
	document.getElementById('mat_kodebarang_find').style.display = '';
	document.getElementById('mat_nourut').value = '';
	document.getElementById('mat_method').value = 'insert';
}

function deleteListPresitasi(nourut){
	param = "&notransaksi="+getValue('notransaksi')+'&nourut='+nourut;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Anda yakin hapus item ini..?'))
		post_response_text('vhc_slave_sipil.php?proses=deleteListPresitasi', param, respon);
}

function deleteListAbsensi(nourut,nik){
	param = "&notransaksi="+getValue('notransaksi')+'&nourut='+nourut+'&abs_nik='+nik;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Anda yakin hapus item ini..?'))
		post_response_text('vhc_slave_sipil.php?proses=deleteListAbsensi', param, respon);
}

function deleteListMaterial(nourut,kodebarang,kodegudang){
	param = "&notransaksi="+getValue('notransaksi')+'&nourut='+nourut+'&mat_kodebarang='+kodebarang+'&mat_kodegudang='+kodegudang;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    loadAllListTab();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Anda yakin hapus item ini..?'))
		post_response_text('vhc_slave_sipil.php?proses=deleteListMaterial', param, respon);
}

function editListPresitasi(kegiatan,alokasi,hasilkerja,jumlahhk,premi,nourut,kodesegment,namasegment,pres_umr,pres_premi){
	document.getElementById('pres_afdeling').selectedIndex = 0;
	pres_oc_afdeling(kegiatan,alokasi);
	document.getElementById('pres_hasilkerja').value = hasilkerja;
	document.getElementById('pres_jumlahhk').value = jumlahhk;
	document.getElementById('pres_upahpremi').value = premi;
	document.getElementById('pres_nourut').value = nourut;
	document.getElementById('pres_kodesegment').value = kodesegment;
	document.getElementById('pres_kodesegment_name').value = namasegment;
	document.getElementById('pres_umr').value = pres_umr;
	document.getElementById('pres_upahpremi').value = pres_premi;
	document.getElementById('pres_method').value = 'update';
}

function editListAbsensi(nik,jhk,umr,premi,nourut){
	cancelAbs();
	vNik = document.getElementById('abs_nik');
    for(ard=0;ard<vNik.length;ard++)
    {
        if(vNik.options[ard].value==nik)
            {
                vNik.options[ard].selected=true;
            }
    }
	
	document.getElementById('abs_kodekegiatan').disabled = true;
	document.getElementById('abs_nik').disabled = true;
	document.getElementById('abs_nik_find').style.display = 'none';
	document.getElementById('abs_temp_jhk').value = jhk;
	document.getElementById('abs_temp_nik').value = nik;
	
	document.getElementById('abs_jhk').value = jhk;
	document.getElementById('abs_umr').value = umr;
	document.getElementById('abs_insentif').value = premi;
	document.getElementById('abs_nourut').value = nourut;
	document.getElementById('abs_method').value = 'update';
}

function editListMaterial(nourut,kodebarang,kodegudang,namabarang,kuantitas){
	cancelMat();
	vNourut = document.getElementById('mat_kodekegiatan');
    for(ard=0;ard<vNourut.length;ard++)
    {
        if(vNourut.options[ard].value==nourut)
            {
                vNourut.options[ard].selected=true;
            }
    }
	vNourut.disabled = true;
	
	vKodegudang = document.getElementById('mat_kodegudang');
	for(ard=0;ard<vKodegudang.length;ard++)
    {
        if(vKodegudang.options[ard].value==kodegudang)
            {
                vKodegudang.options[ard].selected=true;
            }
    }
	vKodegudang.disabled = true;
	document.getElementById('mat_kodebarang').value = kodebarang;
	document.getElementById('mat_kodebarang_name').value = namabarang;
	document.getElementById('mat_kwantitas').value = kuantitas;
	document.getElementById('mat_kodegudang_find').style.display = "none";
	document.getElementById('mat_kodebarang_find').style.display = "none";
	document.getElementById('mat_nourut').value = nourut;
	document.getElementById('mat_method').value = 'update';
}

function totalVal(){
	var pres_jumlahhk = document.getElementById('pres_jumlahhk');
	var pres_hasilkerja = document.getElementById('pres_hasilkerja');
	var abs_jhk = document.getElementById('abs_jhk');
	var abs_umr = document.getElementById('abs_umr');
	var abs_insentif = document.getElementById('abs_insentif');
	var mat_kwantitas = document.getElementById('mat_kwantitas');
	
	if(isNaN(parseFloat(pres_jumlahhk.value))) {pres_jumlahhk.value = 0;}
	if(isNaN(parseFloat(pres_hasilkerja.value))) {pres_hasilkerja.value = 0;}
	if(isNaN(parseFloat(abs_jhk.value))) {abs_jhk.value = 0;}
	if(isNaN(parseFloat(abs_umr.value))) {abs_umr.value = 0;}
	if(isNaN(parseFloat(abs_insentif.value))) {abs_insentif.value = 0;}
	if(isNaN(parseFloat(mat_kwantitas.value))) {mat_kwantitas.value = 0;}
}

function updateUMR(){
	abs_jhk = document.getElementById('abs_jhk');
	
	if(abs_jhk.value > 1){
		alert('Nilai Maksimum HK adalah 1');
		abs_jhk.value = 1;
	}
	
	param = "&abs_nik="+getValue('abs_nik')+'&abs_jhk='+abs_jhk.value+'&tanggal='+getValue('tanggal');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('abs_umr').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	post_response_text('vhc_slave_sipil.php?proses=updateUMR', param, respon);
}

function getTotal(){
	param = "&notransaksi="+getValue('notransaksi');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					vSplit=con.responseText.split("###");
                    document.getElementById('totalPresHk').value = vSplit[0];
                    document.getElementById('totalPresUmr').value = vSplit[1];
                    document.getElementById('totalPresIns').value = vSplit[2];
                    document.getElementById('totalAbsHk').value = vSplit[3];
                    document.getElementById('totalAbsUmr').value = vSplit[4];
                    document.getElementById('totalAbsIns').value = vSplit[5];
					cancelPres();
					cancelAbs();
					cancelMat();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	post_response_text('vhc_slave_sipil.php?proses=getTotal', param, respon);
}

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='vhc_slave_sipil_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailPDF(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    param = "proses=pdf&notransaksi="+notransaksi;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='vhc_slave_sipil_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function getValue(id) {
    var tmp = document.getElementById(id);
    
    if(tmp) {
        if(tmp.options) {
            return tmp.options[tmp.selectedIndex].value;
        } else if(tmp.nodeType=='checkbox') {
            if(tmp.checked==true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return tmp.value;
        }
    } else {
        return false;
    }
}

/* Search
 * Filtering Data
 */
function searchTrans(){
	sNoTrans = document.getElementById('sNoTrans').value;
	goToPages(1,10,sNoTrans);
}

/* Paging
 * Paging Data
 */
function defaultList() {
    goToPages(1,showPerPage);
}

function goToPages(page,shows,where) {
    detailField = document.getElementById('detailField');
	if(detailField.innerHTML != 'undefined'){
		detailField.style.display = 'none';
	}
	
	if(typeof where != 'undefined') {
        var newWhere = where.replace(/'/g,'"');
    }
    var workField = document.getElementById('workField');
    var param = "page="+page;
    param += "&shows="+shows;
    if((typeof where != 'undefined')&&(where != '[]')) {
        param+="&where="+newWhere;
    }
    
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('vhc_slave_sipil.php?proses=showHeadList', param, respon);
}

function choosePage(obj,shows,where) {
    var pageVal = obj.options[obj.selectedIndex].value;
    goToPages(pageVal,shows,where);
}

function postingData(row)
{
    notransaksi=document.getElementById('notransaksi_'+row).innerHTML;
    param='notransaksi='+notransaksi;
	tujuan='vhc_slave_sipil.php';
	if(confirm('Are you sure confirm transaction : '+notransaksi+'? \nOnce confirmed, the data can not be edited.'))
		post_response_text(tujuan+'?'+'proses=postingdata', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					x=document.getElementById('tr_'+row);
					x.cells[10].innerHTML="<img class='zImgBtn' title=Lengkap' src='images/skyblue/posted.png'>";
					x.cells[9].innerHTML='';
					x.cells[8].innerHTML='';
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}    
}