//Cabang PPP: unit milik PT PPP memakai file *_ppp.php untuk langkah proses yang punya file itu; unit lain tetap file biasa.
//orgPPP dan filePPP diisi oleh keu_3posting.php. Untuk membuat cabang PPP suatu langkah: salin file-nya menjadi nama_ppp.php.
var orgProsesAktif = '';
function tujuanProses(file) {
    var q = '';
    var p = file.indexOf('?');
    if (p > -1) {
        q = file.substring(p);
        file = file.substring(0, p);
    }
    var org = orgProsesAktif;
    if (org == '' && document.getElementById('kodeorg')) {
        org = document.getElementById('kodeorg').value;
    }
    if (typeof orgPPP !== 'undefined' && typeof filePPP !== 'undefined' && org != '' && orgPPP.indexOf(org) > -1) {
        var filePpp = file.replace(/\.php$/, '_ppp.php');
        if (filePPP.indexOf(filePpp) > -1) {
            return filePpp + q;
        }
    }
    return file + q;
}

function exportTableToExcel(tableID, filename = ''){
	param  = '';
	tableID = 'mytable';
	var downloadLink;
	var dataType = 'application/vnd.ms-excel';
	var tableSelect = document.getElementById(tableID);
		//tableSelect.border='1';
	var tabelKlon = tableSelect.cloneNode(true);
	var sembunyiEx = tabelKlon.querySelectorAll('.kolom-sembunyi');
	for (var ix = 0; ix < sembunyiEx.length; ix++) {
		sembunyiEx[ix].parentNode.removeChild(sembunyiEx[ix]);
	}
	var tableHTML = tabelKlon.outerHTML.replace(/ /g, '%20');

	
	var tableHTML = tableHTML;
	var filename = document.getElementById('jenisData');
	var filename = filename.options[filename.selectedIndex].text; 
	
	filename = filename?filename+'.xls':'excel_data.xls';
	downloadLink = document.createElement("a");
	document.body.appendChild(downloadLink);

	if(navigator.msSaveOrOpenBlob){
		var blob = new Blob(['\ufeff', tableHTML], {
			type: dataType
		});
		navigator.msSaveOrOpenBlob( blob, filename);
	}else{
		downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
		downloadLink.download = filename;
		downloadLink.click();
	}
}

/* listPosting
 * Fungsi untuk men-generate list dari transaksi yang dapat di posting
 */
function listPosting(tipe) {
    orgProsesAktif = getValue('kodeorg');
    var listPost = document.getElementById('listPosting');
    var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode')+"&jenisdata="+getValue('jenisData')+"&tipe="+tipe;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    //=== Success Response
                    listPost.innerHTML = con.responseText;
                    listPost.style.height = '60vh';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	filealokasi='keu_slave_3gajikaryawan.php';
	//filealokasi='keu_slave_3gajikaryawanv2.php';
	
    x=getValue('jenisData');
    if(x=='gudang')
        post_response_text(tujuanProses('keu_slave_3posting.php'), param, respon);
    else if(x=='gaji')
        post_response_text(tujuanProses(filealokasi), param, respon);
    else if(x=='gajitrk')
        post_response_text(tujuanProses('keu_slave_3gajikaryawantrk.php'), param, respon);
    else if(x=='depresiasi') 
        // post_response_text('keu_slave_3depresiasi.php', param, respon);
        post_response_text(tujuanProses('keu_slave_3depresiasiv2.php'), param, respon);
    else if(x=='alokasi') 
        post_response_text(tujuanProses('keu_slave_3traksi.php'), param, respon);
    else if(x=='alokasi_idle') 
        post_response_text(tujuanProses('keu_slave_3traksi_idle.php?method=list'), param, respon);   
    else if(x=='sipilalokasi') 
        post_response_text(tujuanProses('keu_slave_3sipil.php'), param, respon);   
    else if(x=='gajiharilibur') 
        // post_response_text('keu_slave_3gajiharilibur.php', param, respon); 
		post_response_text(tujuanProses('keu_slave_3gajibelumalokasi.php?method=list'), param, respon);	
    else if(x=='potongan')
        post_response_text(tujuanProses('keu_slave_3pengakuanPotongan.php'), param, respon);
    else if(x=='kurs')
        post_response_text(tujuanProses('keu_slave_3selisihKurs.php?proses=list'), param, respon);
    else if(x=='mutasi')
        post_response_text(tujuanProses('keu_slave_3mutasiKary.php?proses=list'), param, respon);
    else if(x=='tbsramp')
        post_response_text(tujuanProses('keu_slave_3tbsramp.php?proses=list'), param, respon);
    else if(x=='hppolah')
          post_response_text(tujuanProses('keu_slave_3hppolah.php?proses=list'), param, respon);
    else if(x=='asuransi')
        post_response_text(tujuanProses('keu_slave_3asuransi.php?proses=list'), param, respon);
	else if(x=='gajiho')
        post_response_text(tujuanProses('keu_slave_3gajikaryawanho.php?proses=list'), param, respon);
	else if(x=='millmaintenance')
	    post_response_text(tujuanProses('keu_slave_3gajikaryawanho.php?proses=list'), param, respon);
	else if(x=='bibit')
        post_response_text(tujuanProses('keu_slave_3bibitpnkemn.php?proses=list'), param, respon);
	else if(x=='alkbibit')
        post_response_text(tujuanProses('keu_slave_3bibitalokasi.php?proses=list'), param, respon);
    else if(x=='fixtrans'){
        if(confirm("Ini akan memakan waktu, anda yakin?")){
          post_response_text(tujuanProses('keu_slave_3updateTransaksi.php'), param, respon);         
        }
    }     
}

function prosesGudang(row) {
    document.getElementById('btnproses').disabled = true;
    tipetransaksi = document.getElementById('tipetransaksi' + row).innerHTML;
    notransaksi = document.getElementById('notransaksi' + row).innerHTML;
    kodebarang = document.getElementById('kodebarang' + row).innerHTML;
    jumlah = document.getElementById('jumlah' + row).innerHTML;
    satuan = document.getElementById('satuan' + row).innerHTML;
    idsupplier = document.getElementById('idsupplier' + row).innerHTML;
    gudangx = document.getElementById('gudangx' + row).innerHTML;
    untukunit = document.getElementById('untukunit' + row).innerHTML;
    kodeblok = document.getElementById('kodeblok' + row).innerHTML;
    kodemesin = document.getElementById('kodemesin' + row).innerHTML;
    kodekegiatan = document.getElementById('kodekegiatan' + row).innerHTML;
    hartot = document.getElementById('hartot' + row).innerHTML;
    nopo = document.getElementById('nopo' + row).innerHTML;
    kodegudang = document.getElementById('kodegudang' + row).innerHTML;
    tanggal = document.getElementById('tanggal' + row).innerHTML;
    keterangan = document.getElementById('keterangan' + row).innerHTML;

    param = 'tipetransaksi=' + tipetransaksi + '&notransaksi=' + notransaksi +
        '&kodebarang=' + kodebarang +
        '&jumlah=' + jumlah + '&satuan=' + satuan + '&idsupplier=' + idsupplier +
        '&gudangx=' + gudangx + '&untukunit=' + untukunit + '&kodeblok=' + kodeblok +
        '&kodemesin=' + kodemesin + '&kodekegiatan=' + kodekegiatan +
        '&hartot=' + hartot + '&nopo=' + nopo + '&kodegudang=' + kodegudang + '&tanggal=' + tanggal +
        '&keterangan=' + keterangan;
    tujuan = tujuanProses('keu_slave_prosesGudangAkhirbulan.php');
    post_response_text(tujuan, param, respon);
    document.getElementById('row' + row).style.backgroundColor = 'orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            prosesGudang(row);
                        } else {
                            alertify.alert('Done');
                        }
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


//= HO
function prosesGajiho(row) {
    //Batal pada konfirmasi (baris pertama) menghentikan proses, tidak ada jurnal yang dihapus atau dibuat
    if (row == 1 && !confirm('Anda yakin melakukan proses pengalokasian gaji head office?')) {
        return;
    }
    document.getElementById('btnproses').disabled = true;

    if (document.getElementById('karyawanid' + row) == undefined) {
        karyawanid = '';
    } else {
        karyawanid = document.getElementById('karyawanid' + row).innerHTML;
    }
    if (document.getElementById('namakaryawan' + row) == undefined) {
        namakaryawan = '';
    } else {
        namakaryawan = document.getElementById('namakaryawan' + row).innerHTML;
    }
    kodeorg = document.getElementById('kodeorg').value;
    komponen = document.getElementById('komponen' + row).innerHTML;
    namakomponen = document.getElementById('namakomponen' + row).innerHTML;
    // subbagian      =document.getElementById('subbagian'+row).innerHTML;
    mesin = document.getElementById('mesin' + row).innerHTML;
    jumlah = document.getElementById('jumlah' + row).innerHTML;
    tipeorganisasi = document.getElementById('tipeorganisasi' + row).innerHTML;
    periode = document.getElementById('periode' + row).innerHTML;

    param = 'namakaryawan=' + namakaryawan + '&kodeorg=' + kodeorg + '&karyawanid=' + karyawanid +
        '&komponen=' + komponen + '&namakomponen=' + namakomponen +
        '&mesin=' + mesin + '&jumlah=' + jumlah +
        // '&subbagian='+subbagian+'&mesin='+mesin+'&jumlah='+jumlah+
        '&tipeorganisasi=' + tipeorganisasi + '&periode=' + periode + '&row=' + row;
    tujuan = tujuanProses('keu_slave_prosesAlokasiGajiAkhirbulanho.php');
    post_response_text(tujuan, param, respon);

    document.getElementById('row' + row).style.backgroundColor = 'orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            prosesGajiho(row);
                        } else {
                            alertify.alert('Done');
                        }
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//= HO


function prosesGaji(row) {
    //Batal pada konfirmasi (baris pertama) menghentikan proses, tidak ada jurnal yang dihapus atau dibuat
    if (row == 1 && !confirm('Anda yakin melakukan proses pengalokasian gaji?')) {
        return;
    }
    document.getElementById('btnproses').disabled = true;

    if (document.getElementById('karyawanid' + row) == undefined) {
        karyawanid = '';
    } else {
        karyawanid = document.getElementById('karyawanid' + row).innerHTML;
    }
    if (document.getElementById('namakaryawan' + row) == undefined) {
        namakaryawan = '';
    } else {
        namakaryawan = document.getElementById('namakaryawan' + row).innerHTML;
    }

    kodeorg = document.getElementById('kodeorg').value;
    komponen = document.getElementById('komponen' + row).innerHTML;
    namakomponen = document.getElementById('namakomponen' + row).innerHTML;
    subbagian = document.getElementById('subbagian' + row).innerHTML;
    mesin = document.getElementById('mesin' + row).innerHTML;
    jumlah = document.getElementById('jumlah' + row).innerHTML;
    tipeorganisasi = document.getElementById('tipeorganisasi' + row).innerHTML;
    periode = document.getElementById('periode' + row).innerHTML;

    param = 'namakaryawan=' + namakaryawan + '&kodeorg=' + kodeorg + '&karyawanid=' + karyawanid +
        '&komponen=' + komponen + '&namakomponen=' + namakomponen +
        '&mesin=' + mesin + '&jumlah=' + jumlah + '&subbagian=' + subbagian +
        // '&subbagian='+subbagian+'&mesin='+mesin+'&jumlah='+jumlah+
        '&tipeorganisasi=' + tipeorganisasi + '&periode=' + periode + '&row=' + row;
    tujuan = tujuanProses('keu_slave_prosesAlokasiGajiAkhirbulan.php');
    post_response_text(tujuan, param, respon);

    document.getElementById('row' + row).style.backgroundColor = 'orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            prosesGaji(row);
                        } else {
                            alertify.alert('Done');
                        }
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//Cabang PPP (langkah 3): baris komponen lanjutan milik satu karyawan (atribut data-induk="rowN") ikut berubah bersama baris rowN.
//Pada unit non-PPP tidak ada baris seperti itu sehingga tidak berpengaruh.
function aturBarisTerkaitTrk(row, gaya, nilai) {
    var el = document.querySelectorAll('[data-induk="row' + row + '"]');
    for (var i = 0; i < el.length; i++) {
        el[i].style[gaya] = nilai;
    }
}
function prosesGajiTrk(row) {
    //Batal pada konfirmasi (baris pertama) menghentikan proses, tidak ada jurnal yang dihapus atau dibuat
    if (row == 1 && !confirm('Anda yakin melakukan proses pengalokasian gaji?')) {
        return;
    }
    document.getElementById('btnproses').disabled = true;

    if (document.getElementById('karyawanid' + row) == undefined) {
        karyawanid = '';
    } else {
        karyawanid = document.getElementById('karyawanid' + row).innerHTML;
    }
    if (document.getElementById('namakaryawan' + row) == undefined) {
        namakaryawan = '';
    } else {
        namakaryawan = document.getElementById('namakaryawan' + row).innerHTML;
    }

    kodeorg = document.getElementById('kodeorg').value;
    subbagian = document.getElementById('subbagian' + row).innerHTML;
    mesin = document.getElementById('mesin' + row).innerHTML;
    komponen = document.getElementById('komponenall' + row).innerHTML;
    tipeorganisasi = document.getElementById('tipeorganisasi' + row).innerHTML;
    periode = document.getElementById('periode' + row).innerHTML;

    param = 'namakaryawan=' + namakaryawan + '&kodeorg=' + kodeorg + '&karyawanid=' + karyawanid +
        '&komponen=' + komponen +
        '&mesin=' + mesin +'&subbagian=' + subbagian +
        // '&subbagian='+subbagian+'&mesin='+mesin+'&jumlah='+jumlah+
        '&tipeorganisasi=' + tipeorganisasi + '&periode=' + periode + '&row=' + row;
    datakomp=komponen.split('###');
    for (var i = 0; i < datakomp.length; i++) {
        param+= '&jumlah'+datakomp[i]+'=' + document.getElementById('jumlah' + row+'-'+datakomp[i]).innerHTML ;
    }
    tujuan = tujuanProses('keu_slave_prosesAlokasiGajiAkhirbulantrk.php');
    post_response_text(tujuan, param, respon);

    document.getElementById('row' + row).style.backgroundColor = 'orange';
    aturBarisTerkaitTrk(row, 'backgroundColor', 'orange');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                    aturBarisTerkaitTrk(row, 'backgroundColor', 'red');
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    aturBarisTerkaitTrk(row, 'display', 'none');
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            prosesGajiTrk(row);
                        } else {
                            alertify.alert('Done');
                        }
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function prosesAlokasi(row) {
    periode = document.getElementById('periode' + row).innerHTML;
    param = 'periode=' + periode;
    tujuan = tujuanProses('vhc_slave_updateFlag.php');
    if (confirm('Anda yakin melakukan proses pengalokasian biaya Kendaraan?'))
        post_response_text(tujuan, param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(' Error:,\n' + con.responseText);
                } else {
                    doProsesAlokasi(row);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

//Cabang PPP (langkah 8): baris bernilai nol sudah tidak ditampilkan, parameter dikirim bersih (jumlah dihitung ulang di server),
//tombol dikunci selama proses dan aktif lagi bila gagal.
function doProsesAlokasiPPP(row) {
    var btn = document.getElementById('btnproses');
    if (btn) {
        btn.disabled = true;
    }
    var kodeorgP = trim(document.getElementById('kodeorg').value);
    var periodeP = trim(document.getElementById('periode' + row).innerHTML);
    var kodevhcP = trim(document.getElementById('kodevhc' + row).innerHTML);
    var jenisP = trim(document.getElementById('jenis' + row).innerHTML);
    var nourutP = trim(document.getElementById('nourut' + row).innerHTML);
    var paramP = 'periode=' + encodeURIComponent(periodeP) + '&kodeorg=' + encodeURIComponent(kodeorgP) + '&kodevhc=' + encodeURIComponent(kodevhcP) +
        '&jenis=' + encodeURIComponent(jenisP) + '&nourut=' + encodeURIComponent(nourutP);
    var tujuanP = tujuanProses('keu_slave_prosesAlokasiTraksi.php');
    document.getElementById('row' + row).style.backgroundColor = 'orange';
    post_response_text(tujuanP, paramP, responP);

    function responP() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                    if (btn) {
                        btn.disabled = false;
                    }
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    if (document.getElementById('row' + (row + 1))) {
                        doProsesAlokasiPPP(row + 1);
                    } else {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                if (btn) {
                    btn.disabled = false;
                }
                error_catch(con.status);
            }
        }
    }
}
function doProsesAlokasi(row) {
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        return doProsesAlokasiPPP(row);
    }
    document.getElementById('btnproses').disabled = true;
    kodeorg = document.getElementById('kodeorg').value;
    periode = document.getElementById('periode' + row).innerHTML;
    kodevhc = document.getElementById('kodevhc' + row).innerHTML;
    jumlah = document.getElementById('jumlah' + row).innerHTML;
    jenis = document.getElementById('jenis' + row).innerHTML;
    nourut = document.getElementById('nourut' + row).innerHTML;
    jumlahpembulatan = document.getElementById('jumlahpembulatan' + row).innerHTML;
    jumlahbiayakendaraan = document.getElementById('jumlahbiayakendaraan' + row).innerHTML;

    param = 'periode=' + periode + '&kodeorg=' + kodeorg + '+&kodevhc=' + kodevhc + '&jumlah=' + jumlah + '&jenis=' + jenis + '&nourut=' + nourut + '&jumlahbiayakendaraan=' + jumlahbiayakendaraan+ '&jumlahpembulatan=' + jumlahpembulatan;
    tujuan = tujuanProses('keu_slave_prosesAlokasiTraksi.php');
    if (jumlah != '0') {
        post_response_text(tujuan, param, respon);
    } else {
        //next
		 document.getElementById('row' + row).style.display = 'none';
        row++;
        doProsesAlokasi(row);
    }
    document.getElementById('row' + row).style.backgroundColor = 'orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            doProsesAlokasi(row);
                        } else {
                            alertify.alert('Done'); //jangan buang ini
                        }
                    } catch (e) {
                        alertify.alert('Done'); //jangan buang ini
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


maxf=0
sekarang=1;
function savedep(maxRow) {
    //Cabang PPP: konfirmasi dulu (Process ulang menghapus jurnal penyusutan unit ini pada periode itu) dan kunci tombol
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        if (!confirm('Process akan menghapus jurnal penyusutan unit ini pada periode ini lalu membuatnya lagi. Lanjutkan?')) {
            return;
        }
        if (document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
    }
    maxf = maxRow;
    loopsave(1, maxRow);
}



function loopsave(currRow, maxRow) {
    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);
    jenisData = trim(document.getElementById('jenisData').value);
    tipeasset = trim(document.getElementById('tipeasset' + currRow).innerHTML);
    keterangan = trim(document.getElementById('keterangan' + currRow).innerHTML);
    kodeasset = trim(document.getElementById('kodeasset' + currRow).innerHTML);
    namaaset = trim(document.getElementById('namaaset' + currRow).innerHTML);
    kodejurnal = trim(document.getElementById('kodejurnal' + currRow).innerHTML);
    jumlah = trim(document.getElementById('jumlah' + currRow).innerHTML);
    debet = trim(document.getElementById('debet' + currRow).innerHTML);
    kredit = trim(document.getElementById('kredit' + currRow).innerHTML);
    param += '&kodeorg=' + kodeorg + '&periode=' + periode + '&jenisData=' + jenisData + '&tipeasset=' + tipeasset;
    param += '&keterangan=' + keterangan + '&kodeasset=' + kodeasset + '&namaaset=' + namaaset + '&kodejurnal=' + kodejurnal;
    param += '&jumlah=' + jumlah + '&debet=' + debet + '&kredit=' + kredit + '&currRow=' + currRow;

    tujuan = tujuanProses('keu_slave_prosesDepresiasiAkhirbulanv2.php');
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = '';
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                } else {
                    //Cabang PPP: baris yang sudah selesai hilang (seperti langkah lain)
                    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
                        document.getElementById('row' + currRow).style.display = 'none';
                    }
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.alert('Done');
                        // loaddatadt();
                        /*
                        canceldt();

                         */
                    } else {
                        loopsave(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function prosesPenyusutan(row) {
    var awaldt = row;
    var jmlRowLang = document.getElementById('jmlRowLang').value;
    var totRowData = document.getElementById('totRowData').value;
    periode = document.getElementById('periode' + row).innerHTML;
    var strdt = "";
    kodeorg = document.getElementById('kodeorg').value;
    //ambil data dep
    for (row; row <= totRowData; row++) {
        kodejurnal = document.getElementById('kodejurnal' + row).innerHTML;
        keterangan = document.getElementById('keterangan' + row).innerHTML;
        jumlah = document.getElementById('jumlah' + row).innerHTML;
        tipeasset = document.getElementById('tipeasset' + row).innerHTML;
        strdt += "&kodejurnal[]=" + kodejurnal;
        strdt += "&keterangan[]=" + keterangan;
        strdt += "&rpjurnal[" + kodejurnal + "]=" + jumlah;
        strdt += "&tipeasset[]=" + tipeasset;
    }
    //ambil data dep
    for (awaldt; awaldt <= jmlRowLang; awaldt++) {
        tipeisi = document.getElementById('tipeisi_' + awaldt).value;
        nilaiisi = document.getElementById('nilaiisi_' + awaldt).value;
        strdt += "&tipeisi[]=" + tipeisi;
        strdt += "&nilaiisi[" + tipeisi + "]=" + nilaiisi;
    }
    param = 'periode=' + periode + '&kodeorg=' + kodeorg;
    param += strdt;
    tujuan = tujuanProses('keu_slave_prosesDepresiasiAkhirbulanv2.php');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    try {
                        if (con.responseText == 1) {
                            alertify.alert("Done");
                        }
                    } catch (e) {
                        alertify.alert('Done Error' + e);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

// function prosesPenyusutan(row)
// {
//     kodejurnal  =document.getElementById('kodejurnal'+row).innerHTML;
//     periode     =document.getElementById('periode'+row).innerHTML;
//     keterangan  =document.getElementById('keterangan'+row).innerHTML;
//     jumlah      =document.getElementById('jumlah'+row).innerHTML;

//     if(kodejurnal.substr(3,2)=='MS'){
//         var msDat;
//         msDat=document.getElementById('MS01').value;
//         jumlah=parseFloat(jumlah);
//     }
//     param='kodejurnal='+kodejurnal+'&periode='+periode+
//           '&keterangan='+keterangan+'&jumlah='+jumlah+'&row='+row;   
//     if(typeof msDat!='undefined'){
//       param+='&assetMesin='+msDat;
//     } 
//     tujuan='keu_slave_prosesDepresiasiAkhirbulan.php';
//     if(row==1 && confirm('Anda yakin melakukan proses penyusutan?')) {
//         document.getElementById('btnproses').disabled=true;
//         post_response_text(tujuan, param, respon);
//         document.getElementById('row'+row).style.backgroundColor='orange';
//     } else if (row>1) {
//         document.getElementById('btnproses').disabled=true;
//         post_response_text(tujuan, param, respon);
//         document.getElementById('row'+row).style.backgroundColor='orange';
//     }
    
//     function respon() {
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alertify.alert(con.responseText);
//                     document.getElementById('row'+row).style.backgroundColor='red';
//                 } else {
//                     document.getElementById('row'+row).style.display='none';
//                     try{
//                         x=row+1;
//                         if(document.getElementById('row'+x))
//                          {   
//                              row=x;
//                              prosesPenyusutan(row);
//                          }
//                          else
//                          {
//                             alertify.alert('Done');
//                          }
//                     }
//                     catch(e)
//                     {
//                         alertify.alert('Done');
//                     }
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }     
// }


function prosesGajiLangsung(row) {
    document.getElementById('btnproses').disabled = true;
    periode = document.getElementById('periode').value;
    kodeorg = document.getElementById('kodeorg').value;
    karyawanid = document.getElementById('karyawanid').value;
    jumlah = document.getElementById('jumlah').value;
    dari = document.getElementById('dari').value;
    sampai = document.getElementById('sampai').value;
    param = 'karyawanid=' + karyawanid + '&kodeorg=' + kodeorg + '&periode=' + periode + '&jumlah=' + jumlah +
        '&dari=' + dari + '&sampai=' + sampai + '&row=' + row;
    tujuan = tujuanProses('keu_slave_prosesAlokasiGajiKetinggalan.php');
    if (confirm('Anda yakin melakukan proses pengalokasian gaji?'))
        post_response_text(tujuan, param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    try {
                        x = row + 1;
                        //if(document.getElementById('row'+x))
                        // {
                        //     row=x;
                        //     prosesGajiLangsung(row);
                        // }
                        // else
                        // {
                        alertify.alert('Done');
                        //}
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function prosesPotongan(periode) {
    kodeorg = document.getElementById('kodeorg').value;
    param = 'periode=' + periode + '&kodeorg=' + kodeorg + '&method=post';
    tujuan = tujuanProses('keu_slave_3pengakuanPotongan.php');
    //Cabang PPP: baris list punya id row1, row2, ... (seperti langkah lain). Selama proses oranye, berhasil -> hilang, gagal -> merah.
    //Pada unit non-PPP baris tidak punya id sehingga perilaku lama tetap.
    var barisPot = [];
    var i = 1;
    while (document.getElementById('row' + i)) {
        barisPot.push(document.getElementById('row' + i));
        i++;
    }
    if (confirm('Anda yakin melakukan proses ini?')) {
        for (i = 0; i < barisPot.length; i++) {
            barisPot[i].style.backgroundColor = 'orange';
        }
        if (barisPot.length > 0 && document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    for (var k = 0; k < barisPot.length; k++) {
                        barisPot[k].style.backgroundColor = 'red';
                    }
                    if (barisPot.length > 0 && document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                    alertify.alert(' Error:,\n' + con.responseText);
                } else {
                    for (var m = 0; m < barisPot.length; m++) {
                        barisPot[m].style.display = 'none';
                    }
                    alertify.alert('Done');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function postKurs() {
    var param = "kodeorg=" + getValue('kodeorgKurs')
         + "&periode=" + getValue('periodeKurs'),
    tujuan = tujuanProses("keu_slave_3selisihKurs.php?proses=post");

    if (confirm('Anda yakin akan melakukan proses selisih kurs?')) {
        //Cabang PPP: tombol dikunci selama proses berjalan
        if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1 && document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert('Proses Selisih Kurs berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postMutasi() {
    var param = "kodeorg=" + getValue('kodeorg') + "&periode=" + getValue('periode'),
    tujuan = tujuanProses("keu_slave_3mutasiKary.php?proses=post");

    if (confirm("Anda yakin akan melakukan proses update Mutasi / Promosi / " +
            "Demosi Karyawan ?"))
        post_response_text(tujuan, param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert('Proses Mutasi / Promosi / Demosi Karyawan berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function prosesAlokasiTraksiIdle(method, kodeorg, periode) {
    param = 'periode=' + periode + '&kodeorg=' + kodeorg + '&method=' + method;
    tujuan = tujuanProses('keu_slave_3traksi_idle.php');
    //Cabang PPP: baris list punya id row1, row2, ... (sama seperti langkah 1). Selama proses baris berwarna oranye,
    //berhasil -> baris hilang, gagal -> baris merah. Jurnal tetap dibuat sekaligus dalam satu transaksi.
    var barisIdle = [];
    var i = 1;
    while (document.getElementById('row' + i)) {
        barisIdle.push(document.getElementById('row' + i));
        i++;
    }
    if (confirm('Anda yakin melakukan proses pengalokasian biaya Traksi Idle?')) {
        for (i = 0; i < barisIdle.length; i++) {
            barisIdle[i].style.backgroundColor = 'orange';
        }
        if (barisIdle.length > 0 && document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    for (var k = 0; k < barisIdle.length; k++) {
                        barisIdle[k].style.backgroundColor = 'red';
                    }
                    if (barisIdle.length > 0 && document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                    alertify.alert(' Error:,\n' + con.responseText);
                } else if (barisIdle.length > 0) {
                    for (var m = 0; m < barisIdle.length; m++) {
                        barisIdle[m].style.display = 'none';
                    }
                    alertify.alert('Done');
                } else {
                    alertify.alert('Proses berhasil');
                    document.getElementById('listPosting').innerHTML = '';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function prosesAlokasiSipil(row) {
    periode = document.getElementById('periode' + row).innerHTML;
    kdsipil = document.getElementById('kdsipil' + row).innerHTML;
    param = 'periode=' + periode + '&kdTraksi=' + kdsipil;
    tujuan = tujuanProses('vhc_slave_updateFlag.php');
    if (confirm('Anda yakin melakukan proses pengalokasian biaya Sipil?'))
        post_response_text(tujuan, param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(' Error:,\n' + con.responseText);
                } else {
                    doProsesAlokasiSipil(row);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function doProsesAlokasiSipil(row) {
    document.getElementById('btnproses').disabled = true;
    kodeorg = document.getElementById('kodeorg').value;
    periode = document.getElementById('periode' + row).innerHTML;
    kodevhc = document.getElementById('norumah' + row).innerHTML;
    jumlah = document.getElementById('jumlah' + row).innerHTML;
    jenis = document.getElementById('jenis' + row).innerHTML;
    kdsipil = document.getElementById('kdsipil' + row).innerHTML;
    jmlhhk = document.getElementById('hk' + row).innerHTML;

    param = 'periode=' + periode + '&kodeorg=' + kodeorg + '&kdrumah=' + kodevhc + '&jumlah=' + jumlah + '&jenis=' + jenis + '&kdsipil=' + kdsipil;
    param += '&jmlhhk=' + jmlhhk + '&row=' + row;
    tujuan = tujuanProses('keu_slave_prosesAlokasiSipil.php');
    if (jumlah != '0') {
        post_response_text(tujuan, param, respon);
    } else { //next
        row++;
        doProsesAlokasiSipil(row);
    }
    document.getElementById('row' + row).style.backgroundColor = 'orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            doProsesAlokasiSipil(row);
                        } else {
                            alertify.alert('Done'); //jangan buang ini
                        }
                    } catch (e) {
                        alertify.alert('Done'); //jangan buang ini
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function postSelisihRamp(rowdt) {
    var strdt;
    var param = "kodeorg=" + getValue('kodeorg') + "&periode=" + getValue('periode');
    for (a = 1; a <= rowdt; a++) {
        var kdSup = document.getElementById('rampId_' + a).innerHTML;
        var rpSup = document.getElementById('selisihRp_' + a).innerHTML;
        if (a == 1) {
            strdt = "&kdSup[]=" + kdSup + "&rpSup[]=" + rpSup;
        } else {
            strdt += "&kdSup[]=" + kdSup + "&rpSup[]=" + rpSup;
        }
    }
    param += strdt;
    tujuan = tujuanProses("keu_slave_3tbsramp.php?proses=post");
    post_response_text(tujuan, param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById("listPosting").innerHTML = "";
                    alertify.alert('Berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function postHppOlah(row) {
    var tglDt = document.getElementById('tanggalDt_' + row).innerHTML;
    var kgOlah = document.getElementById('kgOlah_' + row).innerHTML;
    var rpOlah = document.getElementById('rpOlah_' + row).innerHTML;
    var maxRow = document.getElementById('totRow').value;
    var dtTbsAkhir = document.getElementById('dtTbsAkhir').innerHTML;

    var param = "kodeorg=" + getValue('kodeorg') + "&periode=" + getValue('periode');
    param += '&tanggalDt=' + tglDt + '&kgOlah=' + kgOlah + '&rpOlah=' + rpOlah + '&row=' + row + '&maxRow=' + maxRow;
    param += '&dtTbsAkhir=' + dtTbsAkhir;
    tujuan = tujuanProses('keu_slave_3hppolah.php?proses=post');
    if (row == 1 && confirm('Anda yakin melakukan proses ini?')) {
        document.getElementById('btnproses').disabled = true;
        post_response_text(tujuan, param, respon);
        document.getElementById('row' + row).style.backgroundColor = 'orange';
    } else if (row > 1) {
        document.getElementById('btnproses').disabled = true;
        post_response_text(tujuan, param, respon);
        document.getElementById('row' + row).style.backgroundColor = 'orange';
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + row).style.backgroundColor = 'red';
                } else {
                    document.getElementById('row' + row).style.display = 'none';
                    try {
                        x = row + 1;
                        if (document.getElementById('row' + x)) {
                            row = x;
                            postHppOlah(row);
                        } else {
                            alertify.alert('Done');
                        }
                    } catch (e) {
                        alertify.alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postasuransi() {
    var param = "kodeorg=" + getValue('kodeorg') + "&periode=" + getValue('periode'),
    tujuan = tujuanProses("keu_slave_3asuransi.php?proses=post");

    //Cabang PPP: baris list punya id row1, row2, ... (seperti langkah lain). Selama proses oranye, berhasil -> hilang, gagal -> merah.
    //Pada unit non-PPP baris tidak punya id sehingga perilaku lama tetap.
    var barisRutin = [];
    var i = 1;
    while (document.getElementById('row' + i)) {
        barisRutin.push(document.getElementById('row' + i));
        i++;
    }

    if (confirm("Anda yakin akan melakukan proses ini ?")) {
        for (i = 0; i < barisRutin.length; i++) {
            barisRutin[i].style.backgroundColor = 'orange';
        }
        if (barisRutin.length > 0 && document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
        post_response_text(tujuan, param, respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    for (var k = 0; k < barisRutin.length; k++) {
                        barisRutin[k].style.backgroundColor = 'red';
                    }
                    if (barisRutin.length > 0 && document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                    alertify.alert(con.responseText);
                } else if (barisRutin.length > 0) {
                    for (var m = 0; m < barisRutin.length; m++) {
                        barisRutin[m].style.display = 'none';
                    }
                    alertify.alert('Done');
                } else {
                    alertify.alert('Proses berhasil');
                    document.getElementById('listPosting').innerHTML = '';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function changeperiode(kodeorg) {
    orgProsesAktif = kodeorg.value;
    param = 'kodeorg=' + kodeorg.value;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('periode').innerHTML = con.responseText;
                    if (window.jQuery) { $('#periode').trigger('change.select2'); }
                    document.getElementById('listPosting').innerHTML = "";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuanProses('keu_slave_3posting.php?proses=changeperiode'), param, respon);
}

/*save maintenance*/

maxf = 0
    sekarang = 1;

function savemaintenance(maxRow) {

    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);

    param += '&method=deletemaintenance' + '&kodeorg=' + kodeorg + '&periode=' + periode;
    // alertify.alert(param);return;
    tujuan = tujuanProses('keu_slave_3millmaintenance.php');
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    maxf = maxRow;
                    loopsavemaintenance(1, maxRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loopsavemaintenance(currRow, maxRow) {
    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);

    station = trim(document.getElementById('station' + currRow).innerHTML);
    jumlah = trim(document.getElementById('jumlah' + currRow).innerHTML);

    jumlah = remove_comma_var(jumlah);

    param += '&method=savemaintenance' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&station=' + station + '&jumlah=' + jumlah;
    // alertify.alert(param);return;
    tujuan = tujuanProses('keu_slave_3millmaintenance.php');
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.alert('Done');
                    } else {
                        loopsavemaintenance(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/*save gaji belum alokasi*/

maxf = 0
    sekarang = 1;

function savegajibelumalokasi(maxRow) {

    //Cabang PPP: minta konfirmasi (Process ulang menghapus jurnal periode ini) dan kunci tombol selama proses berjalan
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        if (!confirm('Process akan menghapus jurnal gaji belum teralokasi periode ini lalu membuatnya lagi. Lanjutkan?')) {
            return;
        }
        document.getElementById('btnproses').disabled = true;
    }
    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);

    param += '&method=delete' + '&kodeorg=' + kodeorg + '&periode=' + periode;
    // alertify.alert(param);return;
    tujuan = tujuanProses('keu_slave_3gajibelumalokasi.php');
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('ERROR TRANSACTION,\n' + con.responseText);
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                } else {
                    maxf = maxRow;
                    loopgajibelumalokasi(1, maxRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function loopgajibelumalokasi(currRow, maxRow) {
    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);
    karyawanid = trim(document.getElementById('karyawanid' + currRow).innerHTML);
    subbagian = trim(document.getElementById('subbagian' + currRow).innerHTML);
    gajisisa = trim(document.getElementById('gajisisa' + currRow).innerHTML);
    gajisisabpjs=trim(document.getElementById('gajisisabpjs'+currRow).innerHTML);
    gajisisabpjskes=trim(document.getElementById('gajisisabpjskes'+currRow).innerHTML);
    param += '&method=save' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&subbagian=' + subbagian + '&karyawanid=' + karyawanid + '&gajisisa=' + gajisisa+'&gajisisabpjs='+gajisisabpjs+'&gajisisabpjskes='+gajisisabpjskes;
    // alertify.alert(param);return;
    tujuan = tujuanProses('keu_slave_3gajibelumalokasi.php');
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('ERROR TRANSACTION,\n' + con.responseText);
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.alert('Done');
                    } else {
                        loopgajibelumalokasi(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

maxf = 0
    sekarang = 1;
function savedep(maxRow) {
    //Cabang PPP: konfirmasi dulu (Process ulang menghapus jurnal penyusutan unit ini pada periode itu) dan kunci tombol
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        if (!confirm('Process akan menghapus jurnal penyusutan unit ini pada periode ini lalu membuatnya lagi. Lanjutkan?')) {
            return;
        }
        if (document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
    }
    maxf = maxRow;
    loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {
    param = "";
    kodeorg = trim(document.getElementById('kodeorg').value);
    periode = trim(document.getElementById('periode').value);
    jenisData = trim(document.getElementById('jenisData').value);
    tipeasset = trim(document.getElementById('tipeasset' + currRow).innerHTML);
    keterangan = trim(document.getElementById('keterangan' + currRow).innerHTML);
    kodeasset = trim(document.getElementById('kodeasset' + currRow).innerHTML);
    namaaset = trim(document.getElementById('namaaset' + currRow).innerHTML);
    kodejurnal = trim(document.getElementById('kodejurnal' + currRow).innerHTML);
    jumlah = trim(document.getElementById('jumlah' + currRow).innerHTML);
    debet = trim(document.getElementById('debet' + currRow).innerHTML);
    kredit = trim(document.getElementById('kredit' + currRow).innerHTML);
    param += '&kodeorg=' + kodeorg + '&periode=' + periode + '&jenisData=' + jenisData + '&tipeasset=' + tipeasset;
    param += '&keterangan=' + keterangan + '&kodeasset=' + kodeasset + '&namaaset=' + namaaset + '&kodejurnal=' + kodejurnal;
    param += '&jumlah=' + jumlah + '&debet=' + debet + '&kredit=' + kredit + '&currRow=' + currRow;

    tujuan = tujuanProses('keu_slave_prosesDepresiasiAkhirbulanv2.php');
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = '';
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                } else {
                    //Cabang PPP: baris yang sudah selesai hilang (seperti langkah lain)
                    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
                        document.getElementById('row' + currRow).style.display = 'none';
                    }
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alertify.alert('Done');
                    } else {
                        loopsave(currRow, maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdetcaco(kodevhc,unitsumber,unitkirim,periode,jenis,tipe) {
    width = '';
    height = '';
    
    content = "<fieldset style=width:750px><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Preview";
    showDialog1(title, content, width, height, ev);

    param = 'method=alokasikelain&kodevhc=' + kodevhc + '&unitsumber=' + unitsumber + '&unitkirim=' + unitkirim + '&periode=' + periode+ '&jenis=' + jenis+ '&tipe=' + tipe;
    tujuan = tujuanProses('vhc_slave_alokasiunitlain.php');
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savemutasibibit(currRow, maxRow) {
	param     = "";
	kodeorg   = trim(document.getElementById('kodeorg').value);
	periode   = trim(document.getElementById('periode').value);
	
    param += '&kodeorg=' + kodeorg + '&periode=' + periode;
    param += '&proses=prosesjurnal';

    tujuan = tujuanProses('keu_slave_3bibitpnkemn.php');
    //Cabang PPP: konfirmasi dulu (Process ulang menghapus jurnal TPB unit ini pada periode itu) dan kunci tombol selama proses
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        if (!confirm('Process akan menghapus jurnal TPB unit ini pada periode ini lalu membuatnya lagi. Lanjutkan?')) {
            return;
        }
        if (document.getElementById('btnproses')) {
            document.getElementById('btnproses').disabled = true;
        }
    }
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    unlockScreen();
                    if (document.getElementById('btnproses')) {
                        document.getElementById('btnproses').disabled = false;
                    }
                } else {
					alertify.alert("Jurnal berhasil, berikut adalah nojurnalnya: "+con.responseText);
					document.getElementById('listPosting').innerHTML='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//Cabang PPP (langkah 10): hanya blok asal, blok tujuan, jenis tanam dan kegiatan yang dikirim (rupiah dan akun dihitung server);
//konfirmasi di baris pertama, tombol terkunci selama proses; baris berhasil berwarna hijau dan menampilkan nomor jurnalnya.
function savemutasibibittanamPPP(currRow, maxRow) {
    var btn = document.getElementById('btnproses');
    if (currRow == 1 || currRow == '1') {
        for (var i = 1; i <= maxRow; i++) {
            if (document.getElementById('kegiatan' + i) && trim(document.getElementById('kegiatan' + i).value) == '') {
                document.getElementById('baris' + i).style.backgroundColor = 'red';
                alertify.alert('Pilih kegiatan pada baris ' + i + ' dulu (semua baris harus punya kegiatan sebelum Process).');
                return;
            }
        }
        if (!confirm('Process akan menghapus jurnal alokasi bibit unit ini pada periode ini lalu membuatnya lagi. Lanjutkan?')) {
            return;
        }
    }
    var kegiatanP = trim(document.getElementById('kegiatan' + currRow).value);
    if (kegiatanP == '') {
        alertify.alert('Pilih kegiatan pada baris ' + currRow + ' dulu.');
        document.getElementById('baris' + currRow).style.backgroundColor = 'red';
        if (btn) {
            btn.disabled = false;
        }
        return;
    }
    if (btn) {
        btn.disabled = true;
    }
    var paramP = '&kodeorg=' + encodeURIComponent(trim(document.getElementById('kodeorg').value)) +
        '&periode=' + encodeURIComponent(trim(document.getElementById('periode').value)) +
        '&blokbbt=' + encodeURIComponent(trim(document.getElementById('kodeorg' + currRow).innerHTML)) +
        '&bloktnm=' + encodeURIComponent(trim(document.getElementById('tujuan' + currRow).innerHTML)) +
        '&jenis=' + encodeURIComponent(trim(document.getElementById('jenis' + currRow).innerHTML)) +
        '&kegiatan=' + encodeURIComponent(kegiatanP) + '&currRow=' + encodeURIComponent(currRow) + '&proses=prosesjurnal';
    var tujuanP = tujuanProses('keu_slave_3bibitalokasi.php');
    document.getElementById('baris' + currRow).style.backgroundColor = 'orange';
    post_response_text(tujuanP, paramP, responP);

    function responP() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    document.getElementById('baris' + currRow).style.backgroundColor = 'red';
                    if (btn) {
                        btn.disabled = false;
                    }
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('baris' + currRow).style.backgroundColor = '#c8e6c9';
                    document.getElementById('jurnal' + currRow).innerHTML = con.responseText;
                    var berikut = parseFloat(currRow) + 1;
                    if (berikut > maxRow) {
                        alertify.alert('Jurnal berhasil');
                    } else {
                        savemutasibibittanamPPP(berikut, maxRow);
                    }
                }
            } else {
                busy_off();
                if (btn) {
                    btn.disabled = false;
                }
                error_catch(con.status);
            }
        }
    }
}
function savemutasibibittanam(currRow, maxRow) {
    if (typeof orgPPP !== 'undefined' && orgPPP.indexOf(orgProsesAktif) > -1) {
        return savemutasibibittanamPPP(currRow, maxRow);
    }
	param     = "";
	kodeorg   = trim(document.getElementById('kodeorg').value);
	periode   = trim(document.getElementById('periode').value);
	console.info(currRow);
	
	blokbbt = document.getElementById('kodeorg'+currRow).innerHTML;
	bloktnm = document.getElementById('tujuan'+currRow).innerHTML;
	jenis = document.getElementById('jenis'+currRow).innerHTML;
	kegiatan = document.getElementById('kegiatan'+currRow).value;
	debet = document.getElementById('debet'+currRow).value;
	kredit = document.getElementById('kredit'+currRow).innerHTML;
	jumlah = document.getElementById('jumlah'+currRow).innerHTML;
	rupiah = document.getElementById('rupiah'+currRow).innerHTML;
	
    param += '&kodeorg=' + kodeorg + '&periode=' + periode;
    param += '&blokbbt=' + blokbbt + '&bloktnm=' + bloktnm;
    param += '&jenis=' + jenis + '&debet=' + debet;
    param += '&kredit=' + kredit + '&jumlah=' + jumlah;
    param += '&rupiah=' + rupiah + '&currRow=' + currRow;
    param += '&kegiatan=' + kegiatan + '&currRow=' + currRow;
    param += '&proses=prosesjurnal';

    tujuan = tujuanProses('keu_slave_3bibitalokasi.php');
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					document.getElementById('baris'+currRow).style.backgroundColor='red';
                    alertify.alert(con.responseText);
                    unlockScreen();
                } else {					
					document.getElementById('jurnal'+currRow).innerHTML=con.responseText;
					currRow=parseFloat(currRow)+parseFloat(1);
                    if((currRow>maxRow) || (maxRow == undefined)){
						alertify.alert("Jurnal berhasil");
						// document.getElementById('listPosting').innerHTML='';
					} else {
						savemutasibibittanam(currRow,maxRow);
                    }
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnoakun(no) {
	param     = "";
	kegiatan   = trim(document.getElementById('kegiatan'+no).value);
    param += '&kegiatan=' + kegiatan;
    param += '&proses=getnoakun';

    tujuan = tujuanProses('keu_slave_3bibitalokasi.php');
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                    unlockScreen();
                } else {					
					e = con.responseText.split("####");
					document.getElementById('debet'+no).value=e[0];
					document.getElementById('tempdebet'+no).innerHTML=e[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

