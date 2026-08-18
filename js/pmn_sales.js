function pdfpanjang(nokontrak) {
    param = "method=pdfpanjang&nokontrak=" + nokontrak;
    alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_sales_slave.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}

function pdf(nokontrak) {
    param = "method=pdf&nokontrak=" + nokontrak;
    alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_sales_slave.php?" + param + "'></iframe>").set({ 'resizable': true, 'maximizable': true, 'startMaximized': true }).resizeTo('90%', '80%');
}

function viewlistfile(nokontrak) {
    param = 'method=viewlistfile&nokontrak=' + trim(nokontrak);

    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('500px', '400px');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function submitfile() {
// 	var nokontrak = document.getElementById("noKtrk").value;
// 	var kriteriaefil = document.getElementById("kriteriaefil").value;
// 	var file = document.getElementById("upload").files[0];
// 	var formdata = new FormData();
// 	formdata.append("file", file);
// 	formdata.append("fileupload", getValue('upload'));
// 	formdata.append("nokontrak", nokontrak);
// 	formdata.append("kriteriaefil", kriteriaefil);
// 	if (getValue('upload') == "") {
// 		alertify.alert("Informasi","warning : Upload file has been empty.");
// 		return false;
// 	}
// 	document.getElementsByClassName("mybutton").disabled=true;
// 	busy_on();
// 	var con = createXMLHttpRequest();
// 	con.open("POST", "pmn_sales_slave.php?method=submitfile", true);
// 	con.onreadystatechange = eval(respon);
// 	con.send(formdata);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alertify.alert("Informasi",con.responseText);
// 				} else {
// 					//=== Success Response
// 					document.getElementsByClassName("mybutton").disabled=false;
// 					alertify.alert("Informasi",'Uploaded Success.');
// 					document.getElementById("upload").value = "";
// 					loadfiles();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
//w
// function deletefile(nokontrak, namafile) {
// 	param = 'method=deletefile&nokontrak=' + nokontrak + '&namafile=' + namafile;
// 	tujuan = 'pmn_sales_slave.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alertify.alert("Informasi",con.responseText);
// 				} else {
// 					loadfiles(nokontrak);
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }


// function loadfiles(nokontrak){
// 	nokontrak = document.getElementById('noKtrk').value;
//     param       = 'method=loadfiles&nokontrak='+trim(nokontrak);

// 	tujuan      = 'pmn_sales_slave.php';
// 	post_response_text(tujuan, param, respog);

// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alertify.alert("Informasi",con.responseText);
// 				} else {

// 					if (document.getElementById('listfiles') !== null) {
// 						document.getElementById('listfiles').innerHTML = con.responseText;
// 					}
// 					// loaddatadetail();
// 					// document.getElementById('listfiles').innerHTML=con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }

maxf = 0
sekarang = 1;
function simpandtall(maxRow) {
    maxf = maxRow;
    loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {
    param = "";
    nokontrak = document.getElementById('noKtrk').value;
    kodebarang = document.getElementById('kdBrg').value;
    pasal = document.getElementById('pasal' + currRow).value;
    keterangan = document.getElementById('keterangan' + currRow).value;
    method = 'simpandt';
    param = 'kodebarang=' + kodebarang + '&pasal=' + pasal + '&keterangan=' + keterangan + '&nokontrak=' + nokontrak;
    param += '&method=' + method;

    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = '';
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow) {
                        alert('Done');
                        // datadetail();
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

function simpandt(no) {
    nokontrak = document.getElementById('noKtrk').value;
    kodebarang = document.getElementById('kdBrg').value;
    pasal = document.getElementById('pasal' + no).value;
    keterangan = document.getElementById('keterangan' + no).value;
    method = 'simpandt';
    param = 'kodebarang=' + kodebarang + '&pasal=' + pasal + '&keterangan=' + keterangan + '&nokontrak=' + nokontrak;
    param += '&method=' + method;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function updatedt(no) {
    nokontrak = document.getElementById('noKtrk').value;
    kodebarang = document.getElementById('kdBrg').value
    pasal = document.getElementById('pasal' + no).value;
    keterangan = document.getElementById('keterangan' + no).value;
    method = 'updatedt';
    param = 'kodebarang=' + kodebarang + '&pasal=' + pasal + '&keterangan=' + keterangan + '&nokontrak=' + nokontrak;
    param += '&method=' + method;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletedt(nokontrak, kodebarang, pasal) {
    method = 'deletedt';
    param = 'kodebarang=' + kodebarang + '&pasal=' + pasal + '&nokontrak=' + nokontrak;
    param += '&method=' + method;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function datadetail() {
    nokontrak = document.getElementById('noKtrk').value;
    kodebarang = document.getElementById('kdBrg').value;
    param = 'method=datadetail' + '&nokontrak=' + nokontrak + '&kodebarang=' + kodebarang;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('datadetail').innerHTML = con.responseText;
                    loadfiles();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function newdata() {
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    cancelht();
}

function displaylist() {
    cancelht();
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('header').style.display = 'none';
    document.getElementById('kodecustomersch').value = '';
    document.getElementById('nokontraksch').value = '';
    document.getElementById('tanggalmulaisch').value = '';
    document.getElementById('tanggalselesaisch').value = '';
    document.getElementById('kodeptsch').value = '';
    loaddata(0);
}

function cariData(pg) {
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('header').style.display = 'none';
    loaddata(pg);
}

function getpage() {
    pg = document.getElementById('pages');
    pg = pg.options[pg.selectedIndex].value;
    paged = parseFloat(pg) - 1;
    loaddata(paged);
}

function loaddata(num) {
    nokontraksch = document.getElementById('nokontraksch').value;
    tanggalmulaisch = document.getElementById('tanggalmulaisch').value;
    tanggalselesaisch = document.getElementById('tanggalselesaisch').value;
    kodeptsch = document.getElementById('kodeptsch').value;
    kodecustomersch = document.getElementById('kodecustomersch').value;

    if (tanggalmulaisch > tanggalselesaisch) {
        alertify.alert('Informasi', 'Tanggal dari tidak boleh lebih besar dari tanggal sampai.'); return;
    }
    if (tanggalselesaisch < tanggalmulaisch) {
        alertify.alert('Informasi', 'Tanggal sampai tidak boleh lebih kecil dari tanggal dari.'); return;
    }

    param = 'method=loaddata&page=' + num;
    param += '&nokontraksch=' + nokontraksch + '&kodeptsch=' + kodeptsch + '&kodecustomersch=' + kodecustomersch;
    param += '&tanggalmulaisch=' + tanggalmulaisch + '&tanggalselesaisch=' + tanggalselesaisch;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                    alertify.alert('Informasi', con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancelht() {
    document.getElementById('method').value = 'insert';

    document.getElementById('noreferensi').value = '';
    document.getElementById('noKtrk').value = '';
    document.getElementById('tlgKntrk').value = '';
    document.getElementById('tlgKntrk').disabled = false;
    document.getElementById('kdPt').value = '';
    document.getElementById('kdPt').disabled = false;
    document.getElementById('millcode').value = '';
    document.getElementById('millcode').disabled = false;
    document.getElementById('daerahctr').value = '';
    document.getElementById('noext').value = '';
    document.getElementById('custId').value = '';
    document.getElementById('custId').disabled = false;
    document.getElementById('berikat').checked = false;
}

function canceldt() {

    document.getElementById('HrgStn').disabled = false;
    document.getElementById('kurs').disabled = false;
    document.getElementById('jmlh').disabled = false;
    document.getElementById('ppnId').disabled = false;
    document.getElementById('tBlg').disabled = false;
    document.getElementById('tBlg').disabled = false;
    document.getElementById('tmbngn').disabled = false;
    document.getElementById('ffa').disabled = false;
    document.getElementById('dobi').disabled = false;
    document.getElementById('mdani').disabled = false;
    document.getElementById('moist').disabled = false;
    document.getElementById('dirt').disabled = false;
    document.getElementById('grading').disabled = false;
    document.getElementById('tlransi').disabled = false;
    document.getElementById('syrtByr').disabled = false;
    document.getElementById('ketplns').disabled = false;
    document.getElementById('termbyr').disabled = false;
    document.getElementById('byrKe').disabled = false;

    document.getElementById('kdBrg').value = '';
    document.getElementById('stn').value = '';
    // document.getElementById('nilaikontrak').value='0';
    document.getElementById('HrgStn').value = '0';
    document.getElementById('jmlh').value = '0';
    document.getElementById('kurs').value = 'IDR';
    document.getElementById('ppnId').value = '';
    document.getElementById('tBlg').innerHTML = '';

    // document.getElementById('tanggalmuat1').value='';
    // document.getElementById('tanggalmuat2').value='';

    document.getElementById('tglKrm0').value = '';
    document.getElementById('tglSd0').value = '';
    document.getElementById('jmlh0').value = '';
    document.getElementById('tglKrm1').value = '';
    document.getElementById('tglSd1').value = '';
    document.getElementById('jmlh1').value = '';
    document.getElementById('tglKrm2').value = '';
    document.getElementById('tglSd2').value = '';
    document.getElementById('jmlh2').value = '';
    document.getElementById('tglKrm3').value = '';
    document.getElementById('tglSd3').value = '';
    document.getElementById('jmlh3').value = '';

    document.getElementById('tmbngn').value = '';
    document.getElementById('ffa').value = '';
    document.getElementById('mdani').value = '';
    document.getElementById('dirt').value = '';
    document.getElementById('grading').value = '';
    document.getElementById('dobi').value = '';
    document.getElementById('moist').value = '';
    // document.getElementById('impu').value='';
    document.getElementById('tlransi').value = '';
    document.getElementById('syrtByr').value = '';
    document.getElementById('ketplns').value = '';
    document.getElementById('tglByr').value = '';
    document.getElementById('termbyr').value = '';
    document.getElementById('byrKe').value = '';
    document.getElementById('tndtng').value = '';
    document.getElementById('tndtng2').value = '';
    document.getElementById('tppenjualan').value = '';
    document.getElementById('texttppenjualan').value = '';
    document.getElementById('cttnLain').value = '';
    // document.getElementById('tipejualbeli').value='';

    // getDataCust();
}


function dataKeExcel(ev, tujuan, nokontrak) {
    judul = 'Report Ms.Excel';
    param = 'nokontrak=' + nokontrak + '&proses=excel';
    printFile(param, tujuan, judul, ev)
}


function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '700';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>";
    showDialog1(title, content, width, height, ev);
}
function formDetail(nokontrak, ev) {
    title = "Add " + nokontrak;
    width = '780';
    height = '320';
    content = "<div id=continerform style=width:600;height:320;overflow:auto;> </div>";
    showDialog1(title, content, width, height, ev);
}
function addDetail(nokontrak, totKnrtk, komoditi, ev) {
    formDetail(nokontrak, ev)
    param = 'method=getFormDet' + '&nokontrak=' + nokontrak;
    param += '&totKontrak=' + totKnrtk + '&komoditi=' + komoditi;
    //alert(param);
    tujuan = 'pmn_sales_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('continerform').innerHTML = con.responseText;
                    document.getElementById('nokntr_ref2').value = "";
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}
function loadNewData() {
    param = 'method=LoadNew';
    tujuan = 'pmn_sales_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('containerlist').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}
function cariBast(num) {
    txtSearch = document.getElementById('txtnokntrk').value;
    ptSch = document.getElementById('ptSch').value;
    param = 'txtSearch=' + txtSearch + '&ptSch=' + ptSch + '&method=LoadNew'
    param += '&page=' + num;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('containerlist').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function saveKP() {
    noKntrk = document.getElementById('noKtrk').value;
    kdPt = document.getElementById('kdPt').value;
    noext = document.getElementById('noext').value;
    millcode = document.getElementById('millcode').value;
    custid = document.getElementById('custId').value;
    berikat = document.getElementById('berikat');
    if (berikat.checked == true) {
        berikat = 1;
    } else {
        berikat = 0;
    }
    tglkntr = document.getElementById('tlgKntrk').value;
    daerahctr = document.getElementById('daerahctr').value;
    persenppn = remove_comma_var(document.getElementById('persenppn').value);

    param = 'noKntrk=' + noKntrk + '&kdPt=' + kdPt + '&noext=' + noext + '&millcode=' + millcode + '&custId=' + custid + '&berikat=' + berikat + '&tlgKntrk=' + tglkntr + '&daerahctr=' + daerahctr + '&persenppn=' + persenppn;


    kdbrg = document.getElementById('kdBrg').value;
    satuan = document.getElementById('stn').value;
    HrgStn = remove_comma_var(document.getElementById('HrgStn').value);
    kurs = document.getElementById('kurs').value;
    qty = remove_comma_var(document.getElementById('jmlh').value);
    ppn = document.getElementById('ppnId').value;
    tipekontrak = document.getElementById('tipekontrak').value;
    nokontrakpayung = document.getElementById('nokontrakpayung').value;
    tBlg = document.getElementById('tBlg').innerHTML;

    param += '&kdBrg=' + kdbrg + '&satuan=' + satuan + '&HrgStn=' + HrgStn + '&kurs=' + kurs + '&qty=' + qty + '&ppnId=' + ppn + '&tBlg=' + tBlg + '&tipekontrak=' + tipekontrak + '&nokontrakpayung=' + nokontrakpayung;

    tglKrm0 = document.getElementById('tglKrm0').value;
    tglKrm1 = document.getElementById('tglKrm1').value;
    tglKrm2 = document.getElementById('tglKrm2').value;
    tglKrm3 = document.getElementById('tglKrm3').value;
    tglSd0 = document.getElementById('tglSd0').value;
    tglSd1 = document.getElementById('tglSd1').value;
    tglSd2 = document.getElementById('tglSd2').value;
    tglSd3 = document.getElementById('tglSd3').value;
    jmlh0 = remove_comma_var(document.getElementById('jmlh0').value);
    jmlh1 = remove_comma_var(document.getElementById('jmlh1').value);
    jmlh2 = remove_comma_var(document.getElementById('jmlh2').value);
    jmlh3 = remove_comma_var(document.getElementById('jmlh3').value);

    param += '&tglKrm0=' + tglKrm0 + '&tglKrm1=' + tglKrm1 + '&tglKrm2=' + tglKrm2;
    param += '&tglKrm3=' + tglKrm3 + '&tglSd0=' + tglSd0 + '&tglSd1=' + tglSd1;
    param += '&tglSd2=' + tglSd2 + '&tglSd3=' + tglSd3 + '&jmlh0=' + jmlh0;
    param += '&jmlh1=' + jmlh1 + '&jmlh2=' + jmlh2 + '&jmlh3=' + jmlh3;


    franco = document.getElementById('tmbngn').value;
    kualitasffa = document.getElementById('ffa').value;
    kualitasmdani = document.getElementById('mdani').value;
    dirt = document.getElementById('dirt').value;
    kualitasdob = document.getElementById('dobi').value;
    moist = document.getElementById('moist').value;
    grading = document.getElementById('grading').value;
    tlransi = document.getElementById('tlransi').value;

    param += '&franco=' + franco + '&kualitasffa=' + kualitasffa + '&kualitasmdani=' + kualitasmdani + '&dirt=' + dirt + '&kualitasdob=' + kualitasdob + '&moist=' + moist + '&grading=' + grading + '&tlransi=' + tlransi;

    syrtByr = document.getElementById('syrtByr').value;
    ketplns = document.getElementById('ketplns').value;
    termbyr = document.getElementById('termbyr').value;
    byrKe = document.getElementById('byrKe').value;
    tndtng = document.getElementById('tndtng').value;
    tndtng2 = document.getElementById('tndtng2').value;
    tppenjualan = document.getElementById('tppenjualan').value;
    texttppenjualan = document.getElementById('texttppenjualan').value;

    cttnLain = document.getElementById('cttnLain').value;

    met = document.getElementById('method').value;

    param += '&syrtByr=' + syrtByr + '&ketplns=' + ketplns + '&termbyr=' + termbyr + '&byrKe=' + byrKe + '&tndtng=' + tndtng + '&tndtng2=' + tndtng2 + '&tppenjualan=' + tppenjualan + '&texttppenjualan=' + texttppenjualan + '&cttnLain=' + cttnLain + '&method=' + met;

    /* ELEMENT YANG DI HIDE */
    nmperson = document.getElementById('nmPerson').value;
    posisictr = document.getElementById('posisictr').value;
    ketdp = document.getElementById('ketdp').value;
    tndtngJbtn = document.getElementById('tndtngJbtn').value;
    tndtngPembli = document.getElementById('tndtngPembli').value;
    jtbnPembli = document.getElementById('jtbnPembli').value;
    tglbayar = document.getElementById('tglByr').value;
    kntrk = document.getElementById('noreferensi').value;
    forcemajuere = document.getElementById('forcemajuere').value;
    perselisihan = document.getElementById('perselisihan').value;

    param += '&nmperson=' + nmperson + '&posisictr=' + posisictr + '&ketdp=' + ketdp + '&tndtngJbtn=' + tndtngJbtn + '&tndtngPembli=' + tndtngPembli + '&jtbnPembli=' + jtbnPembli + '&tglByr=' + tglbayar + '&kntrkRef=' + kntrk + '&forcemajuere=' + forcemajuere + '&perselisihan=' + perselisihan;

    validate([
        ["kdPt", "Perusahaan harus dipilih."],
        // ["millcode","Pabrik harus dipilih."],
        ["custId", "Nama Pelanggan harus dipilih."],
        ["tlgKntrk", "Tanggal kontrak harus diisi."],
        ["daerahctr", "Tempat pembuatan kontrak harus dipilih."],
        ["kdBrg", "Nama barang harus dipilih."],
        ["stn", "Satuan harus dipilih."],
        ["kurs", "Mata uang harus dipilih."],
        ["ppnId", "Jenis PPN harus dipilih."]
    ]);

    if (HrgStn == '' || HrgStn <= 0) {
        alertify.alert("Validasi", "Harga satuan harus diisi dan lebih besar dari 0");
        document.getElementById('HrgStn').focus();
        return false;
    }

    if (qty == '' || qty <= 0) {
        alertify.alert("Validasi", "Banyaknya/Kuantitas harus diisi dan lebih besar dari 0");
        document.getElementById('jmlh').focus();
        return false;
    }

    validate([
        ["tglKrm0", "Tanggal kirim harus diisi."],
        ["tglSd0", "Tanggal kirim harus diisi."]
    ]);

    if (jmlh0 == '' || jmlh0 <= 0) {
        alertify.alert("Validasi", "Banyaknya/Kuantitas penyerahan harus diisi dan lebih besar dari 0");
        document.getElementById('jmlh0').focus();
        return false;
    }

    validate([
        ["tmbngn", "Tempat penyerahan harus dipilih."],
        ["syrtByr", "Pembayaran harus dipilih."],
        ["ketplns", "Tata cara pembayaran harus diisi."],
        ["termbyr", "Term bayar harus dipilih."],
        ["byrKe", "Bayar ke harus dipilih."],
        ["tndtng", "Tanda tangan penjual harus dipilih."],
        ["tppenjualan", "Tipe penjual harus dipilih."],
    ]);

    tujuan = 'pmn_sales_slave.php';

    alertify.confirm('Apakah anda yakin simpan transaksi kontrak jual beli?',
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Peringatan", con.responseText);
                } else {
                    document.getElementById('kdPt').disabled = true;
                    document.getElementById('millcode').disabled = true;
                    document.getElementById('custId').disabled = true;
                    document.getElementById('tlgKntrk').disabled = true;
                    document.getElementById('noKtrk').value = con.responseText;
                    document.getElementById('nokontraksch').value = con.responseText;
                    alertify.popup().destroy();
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success('Berhasil');
                    cariData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function clearFrom() {
    newdata();
}
function getSatuan(kdbrg, cust, sat, nopayung) {
    if ((kdbrg == 0) || (cust == 0) || (sat == 0)) {
        kdBrg = document.getElementById('kdBrg').value;
        param = 'kdBrg=' + kdBrg + '&method=getSatuan';
    }
    else {
        kdBrg = kdbrg;
        satuan = sat;
        param = 'kdBrg=' + kdBrg + '&method=getSatuan' + '&satuan=' + satuan;
    }

    //alert(param);
    tujuan = 'pmn_sales_slave.php';

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('stn').innerHTML = con.responseText;
                    if (cust != 0) {
                        getDataCust(cust, nopayung);
                    }

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}
function copyFromLast() {
    param = 'method=getLastData';
    tujuan = 'pmn_sales_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('noKtrk').disabled = false;
                    ar = con.responseText.split("###");
                    document.getElementById('noKtrk').value = ar[0];
                    document.getElementById('custId').value = ar[1];
                    document.getElementById('tlgKntrk').value = ar[2];
                    document.getElementById('kdBrg').value = ar[3];
                    document.getElementById('HrgStn').value = ar[4];
                    document.getElementById('tBlg').value = ar[5];
                    document.getElementById('jmlh').value = ar[6];
                    document.getElementById('tglKrm').value = ar[7];
                    document.getElementById('tglSd').value = ar[8];
                    document.getElementById('tlransi').value = ar[9];
                    document.getElementById('noDo').value = ar[10];
                    document.getElementById('kualitas').value = ar[11];
                    document.getElementById('syrtByr').value = ar[12];
                    document.getElementById('tndtng').value = ar[13];
                    document.getElementById('tndtng2').value = ar[23];
                    document.getElementById('tmbngn').value = ar[14];
                    document.getElementById('cttn1').value = ar[15];
                    document.getElementById('cttn2').value = ar[16];
                    document.getElementById('cttn3').value = ar[17];
                    document.getElementById('cttn4').value = ar[18];
                    document.getElementById('cttn5').value = ar[19];
                    document.getElementById('othCttn').value = ar[20];
                    getSatuan(ar[3], ar[1], ar[21]);
                    document.getElementById('kdPt').value = ar[22];

                    //document.getElementById('stn').value;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}
function getDataCust(dt, nopayung) {
    if (dt == 0) {
        custId = document.getElementById('custId').value;
    }
    else {
        custId = dt;
    }
    param = 'method=getCust' + '&custId=' + custId + '&tipekontrak=' + getValue('tipekontrak') + '&tanggal=' + getValue('tlgKntrk');
    tujuan = 'pmn_sales_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    ar = con.responseText.split("###");
                    document.getElementById('nmPerson').innerHTML = ar[0];
                    document.getElementById('kdBrg').innerHTML = ar[1];
                    document.getElementById('berikat').disabled = false;
                    document.getElementById('persenppn').disabled = false;
                    if (ar[2] == '1') {

                        document.getElementById('berikat').checked = true;
                        document.getElementById('persenppn').value = 0;
                        // document.getElementById('persenppn').disabled=false;
                    } else {
                        document.getElementById('berikat').checked = false;
                        document.getElementById('persenppn').value = 11;
                        // document.getElementById('persenppn').disabled=false;
                    }
                    document.getElementById('tlransi').value = ar[3];
                    if (getValue('tipekontrak') == 'LTC') {
                        if ((ar[4] != '' || ar[4] != 0) && (nopayung == '' || nopayung == undefined)) {
                            document.getElementById('nokontrakpayung').disabled = false;
                            document.getElementById('nokontrakpayung').innerHTML = ar[4];
                        }
                        if (getValue('method') == 'update') {
                            document.getElementById('nokontrakpayung').value = getValue('nokontrak_ref');
                        }
                    } else {
                        document.getElementById('nokontrakpayung').innerHTML = '';
                        document.getElementById('nokontrakpayung').disabled = true;
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function fillField(nokntrk) {
    noKntrk = nokntrk;
    param = 'method=getEditData' + '&noKntrk=' + noKntrk;

    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('listdata').style.display = 'none';
                    document.getElementById('header').style.display = 'block';

                    document.getElementById('method').value = 'update';
                    ar = con.responseText.split("###");

                    document.getElementById('noKtrk').value = ar[0];
                    document.getElementById('custId').value = ar[1];
                    document.getElementById('tlgKntrk').value = ar[2];

                    /*detail barang*/
                    document.getElementById('kdBrg').innerHTML = ar[3];
                    document.getElementById('stn').innerHTML = ar[4];
                    document.getElementById('HrgStn').value = ar[5];
                    document.getElementById('kurs').value = ar[6];
                    document.getElementById('tBlg').innerHTML = ar[7];
                    document.getElementById('jmlh').value = ar[8];

                    /*tanggal dan jumlah penyerahan */
                    document.getElementById('tglKrm0').value = ar[9];
                    document.getElementById('tglSd0').value = ar[10];
                    document.getElementById('tglKrm1').value = ar[11];
                    document.getElementById('tglSd1').value = ar[12];
                    document.getElementById('tglKrm2').value = ar[13];
                    document.getElementById('tglSd2').value = ar[14];
                    document.getElementById('tglKrm3').value = ar[15];
                    document.getElementById('tglSd3').value = ar[16];
                    document.getElementById('jmlh0').value = ar[17];
                    document.getElementById('jmlh1').value = ar[18];
                    document.getElementById('jmlh2').value = ar[19];
                    document.getElementById('jmlh3').value = ar[20];

                    /*toleransi,kualitas dan franco*/
                    document.getElementById('tmbngn').value = ar[21];
                    document.getElementById('ffa').value = ar[22];
                    document.getElementById('dobi').value = ar[23];
                    document.getElementById('mdani').value = ar[24];
                    document.getElementById('tlransi').value = ar[55];

                    /*syart,term pembayaran*/
                    document.getElementById('syrtByr').value = ar[26];
                    document.getElementById('byrKe').innerHTML = ar[27];
                    document.getElementById('tndtng').value = ar[28];
                    document.getElementById('tndtng2').value = ar[30];
                    document.getElementById('tndtngJbtn').value = ar[29];
                    document.getElementById('tndtngPembli').value = ar[30];
                    document.getElementById('jtbnPembli').value = ar[31];
                    document.getElementById('cttnLain').value = ar[32];
                    document.getElementById('nmPerson').innerHTML = ar[33];
                    jk = document.getElementById('kdPt');
                    for (x = 0; x < jk.length; x++) {
                        if (jk.options[x].value == ar[34]) {
                            jk.options[x].selected = true;
                        }
                    }
                    jk.disabled = true;
                    jk2 = document.getElementById('ppnId');
                    for (x = 0; x < jk2.length; x++) {
                        if (jk2.options[x].value == ar[35]) {
                            jk2.options[x].selected = true;
                        }
                    }
                    document.getElementById('tglByr').value = ar[36];
                    //alert(ar[3]);
                    document.getElementById('moist').value = ar[37];
                    document.getElementById('dirt').value = ar[38];
                    document.getElementById('grading').value = ar[39];
                    document.getElementById('kntrkRef').innerHTML = ar[40];
                    document.getElementById('ketdp').innerHTML = ar[41];
                    document.getElementById('ketplns').value = ar[42];
                    // alert(ar[42]);


                    if (ar[43] == '1') {
                        document.getElementById('berikat').checked = true;
                    } else {
                        document.getElementById('berikat').checked = false;
                    }

                    document.getElementById('forcemajuere').innerHTML = ar[44];
                    document.getElementById('perselisihan').innerHTML = ar[45];
                    document.getElementById('noext').innerHTML = ar[46];
                    document.getElementById('posisictr').innerHTML = ar[47];
                    document.getElementById('daerahctr').innerHTML = ar[48];
                    document.getElementById('noreferensi').value = ar[49];
                    document.getElementById('termbyr').value = ar[50];
                    document.getElementById('millcode').value = ar[51];
                    document.getElementById('tppenjualan').value = ar[52];
                    document.getElementById('persenppn').value = ar[53];
                    document.getElementById('texttppenjualan').value = ar[54];
                    document.getElementById('tipekontrak').value = ar[54];
                    document.getElementById('nokontrak_ref').value = ar[25];
                    getDataCust(ar[1]);
                    /* DISABLED ELEMET */
                    document.getElementById('millcode').disabled = true;
                    document.getElementById('custId').disabled = true;
                    document.getElementById('tlgKntrk').disabled = true;
                    // document.getElementById('detail').style.display = 'block';
                    // datadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function delData(nokontrk) {
    noKntrk = nokontrk;

    param = 'method=dataDel' + '&noKntrk=' + noKntrk;
    tujuan = 'pmn_sales_slave.php';

    alertify.confirm('Validasi', 'Apakah anda yakin hapus transaksi kontrak jual beli no ' + noKntrk + '?',
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    getpage();
                    document.getElementById('method').value = 'insert';
                    alertify.popup().destroy();
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success('Berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariNoKntrk() {
    txtSearch = document.getElementById('txtnokntrk').value;
    ptSch = document.getElementById('ptSch').value;
    //param='txtSearch='+txtSearch+'&method=cariNokntrk';
    param = 'txtSearch=' + txtSearch + '&ptSch=' + ptSch + '&method=LoadNew';

    //
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    //document.getElementById('stn').innerHTML=con.responseText;
                    //clearFrom();
                    //tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
                    //tabAction(document.getElementById('tabFRM1'),0,'FRM',1);	
                    document.getElementById('containerlist').innerHTML = con.responseText;

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getRek(kepilih = '') {
    pt = document.getElementById('kdPt');
    pt = pt.options[pt.selectedIndex].value;
    param = 'kdpt=' + pt + '&method=getRek' + '&kepilih=' + kepilih;

    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    dert = con.responseText.split("####");
                    document.getElementById('byrKe').innerHTML = dert[0];
                    document.getElementById('kntrkRef').innerHTML = dert[1];
                    document.getElementById('millcode').innerHTML = dert[2];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getBerat() {
    var isi;
    isi = document.getElementById('jmlh').value;
    document.getElementById('jmlh0').value = isi;
}

function hitungHarga() {
    var hargasatuan = remove_comma_var(getValue('HrgStn')),
        kuantitas = remove_comma_var(getValue('jmlh')),
        container = getById('tmpHarga');
    if (hargasatuan == '') hargasatuan = 0;
    if (kuantitas == '') kuantitas = 0;
    container.value = parseFloat(hargasatuan) * parseFloat(kuantitas);
    rupiahkan(getById('tmpHarga'), 'tBlg', true);
}
function saveDet() {
    nokontr = document.getElementById('nokontrak').value;
    jmlhnokontr = document.getElementById('jmlHnokontrak').value;
    nokntrkRef = document.getElementById('nokntr_ref');
    nokntrkRef = nokntrkRef.options[nokntrkRef.selectedIndex].value;
    kuota = document.getElementById('jmlhRef').value;
    nokRef = document.getElementById('nokntr_ref2').value;
    param = 'method=saveDet' + '&nokontrak=' + nokontr + '&jmlHnokontrak=' + jmlhnokontr;
    param += '&nokntr_ref=' + nokntrkRef + '&jmlhRef=' + kuota + '&nokntr_ref2=' + nokRef;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loadDetail(nokontr);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function loadDetail(nokontrak) {
    param = 'method=loadDet' + '&nokontrak=' + nokontrak;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('isidetail').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function delData2(nokontrak, nokntr_ref) {
    param = 'method=delDet';
    param += '&nokntr_ref=' + nokntr_ref + '&nokontrak=' + nokontrak;
    tujuan = 'pmn_sales_slave.php';
    if (confirm("Anda Yakin Menghapus No.Kontrak induk " + nokntr_ref + "?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loadDetail(nokontrak);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function fillField2(nokontrak, nokntr_ref) {
    param = 'method=editDet';
    param += '&nokntr_ref=' + nokntr_ref + '&nokontrak=' + nokontrak;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    isied = con.responseText.split("####");
                    document.getElementById('nokntr_ref').innerHTML = isied[1];
                    document.getElementById('jmlhRef').value = isied[2];
                    document.getElementById('nokntr_ref2').value = isied[3];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function posting(nokontrak, numrow) {
    param = 'method=posting' + '&nokontrak=' + nokontrak;
    tujuan = 'pmn_sales_slave.php';

    alertify.confirm('Apakah anda yakin posting transaksi kontrak jual beli no ' + nokontrak + '?',
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    getpage();
                    alertify.popup().destroy();
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success('Berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function carinorefrensi(title, ev) {
    content = "<div>";
    content += "<fieldset>Search : <input type=text id=textnoref class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=gocarinorefrensi()>Go</button><p>";
    content += "<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";

    width = '';
    height = '';
    showDialog1(title, content, width, height, ev);
}

function gocarinorefrensi() {
    textnoref = document.getElementById('textnoref').value;

    if (textnoref.length <= 2) {
        alert("No. Referensi too short text. Min 3 Char.");
        return;
    }

    param = 'method=gocarinorefrensi' + '&textnoref=' + textnoref;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('containercari').innerHTML = con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillnorefrensi(noreferensi, kodeorg, buyer, berikat, komoditi, kuantitas, harga, ppn, paymentdate, bayarke, kualitas1, kualitas2, kualitas3, kualitas4) {
    closeDialog();
    document.getElementById('noreferensi').value = noreferensi;
    kdPtl = document.getElementById('kdPt');
    for (a = 0; a < kdPtl.length; a++) {
        if (kdPtl.options[a].value == kodeorg) {
            kdPtl.options[a].selected = true;
        }
    }
    custIdl = document.getElementById('custId');
    for (a = 0; a < custIdl.length; a++) {
        if (custIdl.options[a].value == buyer) {
            custIdl.options[a].selected = true;
        }
    }
    document.getElementById('kurs').value = 'IDR';
    getSatuan2(komoditi, buyer, 'KG');
    if (berikat == '0') {
        document.getElementById('berikat').checked = false;
    }
    else {
        document.getElementById('berikat').checked = true;
    }

    document.getElementById('jmlh').value = kuantitas;
    document.getElementById('HrgStn').value = harga;
    document.getElementById('ppnId').value = ppn;


    document.getElementById('ffa').value = '';
    document.getElementById('mdani').value = '';
    document.getElementById('dirt').value = '';
    document.getElementById('dobi').value = '';
    document.getElementById('moist').value = '';
    document.getElementById('grading').value = '';
    if (komoditi == '400000001') {
        document.getElementById('ffa').value = kualitas1;
        document.getElementById('mdani').value = kualitas2;
        document.getElementById('dirt').value = kualitas3;
    }
    else {
        document.getElementById('ffa').value = kualitas4;
        document.getElementById('dobi').value = kualitas1;
        document.getElementById('moist').value = kualitas2;
        document.getElementById('grading').value = kualitas3;
    }

    document.getElementById('tglByr').value = paymentdate;
    //document.getElementById('byrKe').value=bayarke;
    byrKel = document.getElementById('byrKe');
    for (a = 0; a < byrKel.length; a++) {
        if (byrKel.options[a].value == bayarke) {
            byrKel.options[a].selected = true;
        }
    }
    document.getElementById('kdPt').disabled = true;
    document.getElementById('custId').disabled = true;
    document.getElementById('berikat').disabled = true;
    document.getElementById('kdBrg').disabled = true;
    document.getElementById('HrgStn').disabled = true;
    document.getElementById('kurs').disabled = true;
    document.getElementById('jmlh').disabled = true;
    document.getElementById('ppnId').disabled = true;
    document.getElementById('ffa').disabled = true;
    document.getElementById('mdani').disabled = true;
    document.getElementById('dirt').disabled = true;
    //document.getElementById('dobi').disabled=true;
    document.getElementById('moist').disabled = true;
    document.getElementById('grading').disabled = true;
    document.getElementById('tlransi').disabled = true;
    document.getElementById('tglByr').disabled = true;
    document.getElementById('byrKe').disabled = true;
}

function getSatuan2(kdbrg, cust, sat) {
    param = 'kdBrg=' + kdbrg + '&method=getSatuan' + '&satuan=' + sat;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('stn').innerHTML = con.responseText;
                    getDataCust2(cust, kdbrg);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getDataCust2(dt, komoditi) {
    param = 'method=getCust' + '&custId=' + dt;
    tujuan = 'pmn_sales_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    ar = con.responseText.split("###");
                    document.getElementById('nmPerson').innerHTML = ar[0];
                    document.getElementById('kdBrg').innerHTML = ar[1];
                    document.getElementById('tlransi').value = ar[2];
                    document.getElementById('kdBrg').value = komoditi;
                    hitungHarga();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);

}

//Umar
function form_ajukan(notransaksi) {
    let content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    let title = "Ajukan : " + notransaksi;

    alertify.popup(title, content).set({ 'resizable': true, 'maximizable': true }).resizeTo('20%', '10%');

    let param = "method=form_ajukan";
    param += "&notransaksi=" + notransaksi;
    let tujuan = "pmn_sales_slave.php";
    post_response_text(tujuan, param, function () {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    });
}

function ajukan() {
    let notransaksi = document.getElementById("notransaksi_ajukan");
    let jlh = document.getElementById("jlh");

    if (jlh.value == 0) {
        alertify.alert("Warning: Approval kosong");
        return;
    }

    let param = "method=ajukan";
    param += "&notransaksi=" + notransaksi.value;
    param += "&jlh=" + jlh.value;

    for (i = 1; i <= jlh.value; i++) {
        param += "&" + "kepada" + i + "=" + document.getElementById("kepada" + i).value;
    }

    let tujuan = "pmn_sales_slave.php";
    post_response_text(tujuan, param, () => {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.alert('Info', 'Success');
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    });
}
//End Umar


function showupload(notrans, jenisupload) {
    param = 'method=showupload&notransaksi=' + notrans;
    param += '&jenisupload=' + jenisupload;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('500px', '400px');
                    loadfiles(notrans, jenisupload);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile(notrans, jenisupload) {
    var kriteriaefil = document.getElementById("kriteriaefil").value;
    var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('noppupload').value;
    var formdata = new FormData();

    if (jenisupload == '1') {
        notransaksi = notrans;
    }

    formdata.append("fileupload", getValue('upload'));
    formdata.append("file", file);
    formdata.append("notransaksi", notransaksi);
    formdata.append("kriteriaefil", kriteriaefil);
    formdata.append("jenisupload", jenisupload);
    if (getValue('upload') == "") {
        alert("warning : Upload file has been emptyxxx.");
        return false;
    }

    if (notransaksi == "") {
        alert("warning : Silahkan isikan detail pengajuan terlebih dahulu !");
        return false;
    }
    var con = createXMLHttpRequest();
    document.getElementById('btnsubmit').disabled = true;
    busy_on();
    con.open("POST", "pmn_sales_slave.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    alert('Uploaded Success.');
                    document.getElementById('btnsubmit').disabled = false;
                    document.getElementById("upload").value = "";
                    loadfiles(notransaksi, jenisupload);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewlistfile(notransaksi) {
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog4(title, content, width, height, ev);
    param = 'method=viewlistfile&notransaksi=' + notransaksi;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contviewz').innerHTML = con.responseText;
                    loadfiles(notransaksi, jenisupload);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadfiles(notransaksi, jenisupload) {
    param = 'method=loadfiles&notransaksi=' + notransaksi;
    param += '&jenisupload=' + jenisupload;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletefile(notransaksi, namafile, jenisupload) {
    param = "method=deletefile";
    param += "&notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi, jenisupload);
                }
            } else {
                tanggalkontrak
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkontrakpayung() {
    nokontrakpayung = document.getElementById('nokontrakpayung').value;
    tanggalkontrak = document.getElementById('tlgKntrk').value;
    param = 'method=getkontrakpayung' + '&nokontrak=' + nokontrak + '&tanggalkontrak=' + tanggalkontrak + '&nokontrakpayung=' + nokontrakpayung;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('nokontrakpayung').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getdetail(notransaksi) {
    if (notransaksi != '') {
        param = 'method=getedit';
        param += '&notransaksi=' + notransaksi;

        tujuan = 'pmn_slave_kontrakpayung.php';
        post_response_text(tujuan, param, respog);

        function respog() {
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alertify.alert('ERROR TRANSACTION,\n' + con.responseText);
                    } else {
                        data = JSON.parse(con.responseText);
                        document.getElementById('HrgStn').value = data.HrgStn;
                        document.getElementById('kurs').value = data.kurs;
                        document.getElementById('jmlh').value = data.jmlh;
                        document.getElementById('ppnId').value = data.ppnId;
                        document.getElementById('tBlg').value = data.tBlg;
                        document.getElementById('tBlg').value = data.tBlg;
                        document.getElementById('tmbngn').value = data.tmbngn;
                        document.getElementById('ffa').value = data.ffa;
                        document.getElementById('dobi').value = data.dobi;
                        document.getElementById('mdani').value = data.mdani;
                        document.getElementById('moist').value = data.moist;
                        document.getElementById('dirt').value = data.dirt;
                        document.getElementById('grading').value = data.grading;
                        document.getElementById('tlransi').value = data.tlransi;
                        document.getElementById('syrtByr').value = data.syrtByr;
                        document.getElementById('ketplns').value = data.ketplns;
                        document.getElementById('termbyr').value = data.termbyr;
                        document.getElementById('byrKe').value = data.byrKe;
                        document.getElementById('tndtng').value = data.tndtng;
                        document.getElementById('tppenjualan').value = data.tppenjualan;
                        // document.getElementById('HrgStn').disabled=true;
                        document.getElementById('kurs').disabled = true;
                        // document.getElementById('jmlh').disabled=true;
                        document.getElementById('ppnId').disabled = true;
                        document.getElementById('tBlg').disabled = true;
                        document.getElementById('tBlg').disabled = true;
                        document.getElementById('tmbngn').disabled = true;
                        document.getElementById('ffa').disabled = true;
                        document.getElementById('dobi').disabled = true;
                        document.getElementById('mdani').disabled = true;
                        document.getElementById('moist').disabled = true;
                        document.getElementById('dirt').disabled = true;
                        document.getElementById('grading').disabled = true;
                        document.getElementById('tlransi').disabled = true;
                        document.getElementById('syrtByr').disabled = true;
                        document.getElementById('ketplns').disabled = true;
                        document.getElementById('termbyr').disabled = true;
                        document.getElementById('byrKe').disabled = true;
                        getSatuan(data.kdBrg, data.kodecustomer, data.stn, notransaksi);
                        setTimeout(() => {
                            document.getElementById('kdBrg').value = data.kdBrg;
                            document.getElementById('stn').value = data.stn;
                        }, 650);

                    }
                } else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    } else {
        canceldt()
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
    document.getElementById('dynamic2').style.display = '';
}



function showupload(ev,no) {
    showformupload(ev);
    nopp = document.getElementById('detail_kode'+no).innerHTML;
    param = 'method=showupload&rnopp=' + nopp;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(nopp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadfiles(nopp) {
    param = 'method=loadfiles&rnopp=' + nopp;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfilestop') !== null) {
                        document.getElementById('listfilestop').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('listfilesview') !== null) {
                        document.getElementById('listfilesview').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var nopp = document.getElementById("noppupload").innerHTML;
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    formdata.append("rnopp", nopp);
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    document.getElementsByClassName("mybutton").disabled=true;
    busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "pmn_sales_slave.php?method=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementsByClassName("mybutton").disabled=false;
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(nopp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletefile(nopp, namafile) {
    param = 'method=deletefile&rnopp=' + nopp + '&namafile=' + namafile;
    tujuan = 'pmn_sales_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(nopp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function downloadfile(path, filename) {
    param = 'path=' + path + '&filename=' + filename;
    tujuan = 'download.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
*/






