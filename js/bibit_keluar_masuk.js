// JavaScript Document
function getNumberSPB() {
    tgl = document.getElementById('tglPnb').value;
    kodeorg = document.getElementById('kdOrgPnb').value;
    param = 'proses=getNumberSPB';
    param += '&tgl=' + tgl + '&kodeorg=' + kodeorg;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('ketPnb').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function lihatfile(doc, ev) {
    title = "Data Detail";
    //alertify.popuppdf(title, "<iframe frameborder=0 style='width:795px;height:395px' src='bibit_slave_keluar_masuk.php?" + param + "'></iframe>", '800', '400', ev);
  
    param = 'proses=lihatfile' + '&doc=' + doc;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popuppdf().destroy();
                    alertify.popuppdf("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('780px','370px');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savefile() {
    var fileup = document.getElementById("fileupload").files[0];
    var formdata = new FormData();
    formdata.append("fileup", fileup);
    formdata.append("fileupload", getValue('fileupload'));
    formdata.append("batchAfk", getValue('batchAfk'));
    formdata.append("tglAfkirBibit", getValue('tglAfkirBibit'));
    formdata.append("kdOrgAfk", getValue('kdOrgAfk'));
    var con = createXMLHttpRequest();
    con.open("POST", "bibit_slave_keluar_masuk.php?proses=savefile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //loadData3(2);
                    cancelData3();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchSupplier(title, content, ev) {
    width = '';
    height = '';
    showDialog1(title, content, width, height, ev);
    //alert('asdasd');
}
function findSupplier() {
    nmSupplier = document.getElementById('nmSupplier').value;
    param = 'proses=getSupplierNm' + '&nmSupplier=' + nmSupplier;
    tujuan = 'log_slave_save_po_lokal.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerSupplier').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setData(kdSupp) {
    l = document.getElementById('supplier_id');

    for (a = 0; a < l.length; a++) {
        if (l.options[a].value == kdSupp) {
            l.options[a].selected = true;
        }
    }
    closeDialog();
}
function cancelData1() {
    document.getElementById('batch').value = '';
    document.getElementById('kodeorgBibitan').value = '';
    document.getElementById('kodeorgBibitan').disabled = false;
    document.getElementById('kodeBatch').value = '';
    document.getElementById('kodeBatch').disabled = false;
    document.getElementById('tglTnm').disabled = false;
    document.getElementById('jmlhBibitan').value = '0';
    document.getElementById('afkirKcmbh').value = '0';
    document.getElementById('jmlhTrima').value = '0';
    document.getElementById('jmlh').value = '0';
    document.getElementById('nodo').value = '';
    document.getElementById('ket').value = '';
    document.getElementById('tglTnm').value = '';
    document.getElementById('jnsBibitan').value = '';
    document.getElementById('supplier_id').value = '';
    document.getElementById('tgl2').value = '';
    document.getElementById('proses1').value = 'saveTab1';

    setValue2('kodeorgBibitan', null);
    setValue2('kodeBatch', null);
    setValue2('jnsBibitan', null);
    setValue2('supplier_id', null);
	loadData1(2);
}

function cancelData2() {
    document.getElementById('batchTp').value = '';
    document.getElementById('batchTp').disabled = false;
    document.getElementById('kodeOrgTp').value = '';
    document.getElementById('kodeOrgTp').disabled = false;
    document.getElementById('tglTp').disabled = false;
    document.getElementById('tglTp').value = '';
    document.getElementById('jmlhTpBbtn').value = '0';
    document.getElementById('tglTp').value = '';
    document.getElementById('kodeOrgTjnTp').value = '';
    document.getElementById('kodeOrgTjnTp').disabled = false;
    document.getElementById('supplier_id').value = '';
    document.getElementById('ketTp').value = '';
    document.getElementById('proses1').value = 'saveTab2';
	
	setValue2('batchTp', null);
    setValue2('kodeOrgTp', null);
    setValue2('kodeOrgTjnTp', null);
	loadData2(2);
}
function cancelData3() {
    document.getElementById('fileupload').value = '';
    document.getElementById('batchAfk').value = '';
    document.getElementById('batchAfk').disabled = false;
    document.getElementById('kdOrgAfk').value = '';
    document.getElementById('kdOrgAfk').disabled = false;
    document.getElementById('tglAfkirBibit').disabled = false;
    document.getElementById('jmlhAfk').value = '0';
    document.getElementById('ketAfk').value = '';
    document.getElementById('tglAfkirBibit').value = '';
    document.getElementById('proses3').value = 'saveTab3';
	setValue2('batchAfk', null);
	setValue2('kdOrgAfk', null);
	loadData3(2);
}
function cancelData4() {
    document.getElementById('batch2').value     = "";
    document.getElementById('kodeBatch2').value = "";
    document.getElementById('kdBatchOld').value = "";
    document.getElementById('jmlhBibitan2').value= "";
    document.getElementById('tglTnm2').value     = "";
    document.getElementById('tglTnm2').disabled   = false;
    document.getElementById('kodeBatch2').disabled   = false;
    document.getElementById('proses4').value = 'saveTab4';

    setValue2('kdBatchOld', null);
    setValue2('kodeBatch2', null);
	loadData4(2);
}
function cancelData5() {

    document.getElementById('batchDbt').value = '';
    document.getElementById('batchDbt').disabled = false;
    document.getElementById('kdOrgDbt').value = '';
    document.getElementById('kdOrgDbt').disabled = false;
    document.getElementById('tglDbt').disabled = false;
    document.getElementById('jmlhDbt').value = '0';
    document.getElementById('ketDbt').value = '';
    document.getElementById('tglDbt').value = '';
    document.getElementById('proses5').value = 'saveTab5';
	setValue2('batchDbt', null);
	setValue2('kdOrgDbt', null);
	loadData5();
}
function cancelData7() {
    document.getElementById('batchPnb').value = '';
    document.getElementById('batchPnb').disabled = false;
    document.getElementById('kdOrgPnb').value = '';
    document.getElementById('kdOrgPnb').disabled = false;
    document.getElementById('tglPnb').disabled = false;
    document.getElementById('jmlhPnb').value = '0';
    document.getElementById('ketPnb').value = '';
    document.getElementById('tglPnb').value = '';
    document.getElementById('kdvhc').value = '';
    document.getElementById('nmSupir').value = '';
    document.getElementById('intexDt').value = '';
    document.getElementById('custId').innerHTML = "<option value=''>" + pilh + "</option>";
    document.getElementById('kdAfdeling').innerHTML = "<option value=''>" + pilh + "</option>";
    document.getElementById('detPeng').value = '';
    document.getElementById('assistenPnb').value = '';
    document.getElementById('kplDivBbt').value = '';
    document.getElementById('kplDivKbn').value = '';
    document.getElementById('kegId').value = '';
    document.getElementById('proses7').value = 'saveTab7';
	setValue2('batchPnb', null);
    setValue2('kdOrgPnb', null);
    setValue2('intexDt', null);
    setValue2('custId', null);
    setValue2('kdAfdeling', null);
    setValue2('kegId', null);
    setValue2('assistenPnb', null);
    setValue2('kplDivBbt', null);
    setValue2('kplDivKbn', null);
	loadData7(2);
}
function saveData(sTab) {
    if (sTab == '1') {
		kodeTrans    = document.getElementById('kdTransaksi').value;
		batchVar     = document.getElementById('batch').value;
		kodeBatch    = document.getElementById('kodeBatch').value;
		kdOrg        = document.getElementById('kodeorgBibitan').value;
		jmlhBibitan  = document.getElementById('jmlhBibitan').value;
		ket          = trim(document.getElementById('ket').value);
		tglTnm       = document.getElementById('tglTnm').value;
		jnsBibitan   = document.getElementById('jnsBibitan').value;
		supplierid   = document.getElementById('supplier_id').value;
		tglProduksi  = document.getElementById('tgl2').value;
		proses1      = document.getElementById('proses1').value;
		oldJenisBibit= document.getElementById('oldJnsbibit').value;
		nodo         = document.getElementById('nodo').value;
		jmlhdDo      = document.getElementById('jmlh').value;
		jmlhTrima    = document.getElementById('jmlhTrima').value;
		afkirKcmbh   = document.getElementById('afkirKcmbh').value;
        param = 'kodeTrans=' + kodeTrans + '&batchVar=' + batchVar + '&kodeBatch=' + kodeBatch + '&kdOrg=' + kdOrg + '&jmlhBibitan=' + jmlhBibitan + '&tglTnm=' + tglTnm;
        param += '&ket=' + ket + '&jnsBibitan=' + jnsBibitan + '&supplierid=' + supplierid + '&tglProduksi=' + tglProduksi + '&proses=' + proses1;
        param += '&jmlhTrima=' + jmlhTrima + '&nodo=' + nodo + '&afkirKcmbh=' + afkirKcmbh + '&jmlhdDo=' + jmlhdDo;
        if (oldJenisBibit != '') {
            param += '&oldJenisBibit=' + oldJenisBibit;
        }
    } else if (sTab == '2') {
		kodeTrans  = document.getElementById('kdTransaksiTp').value;
		batchVar   = document.getElementById('batchTp').value;
		kdOrg      = document.getElementById('kodeOrgTp').value;
		kdOrgTjn   = document.getElementById('kodeOrgTjnTp').value;
		jmlhBibitan= document.getElementById('jmlhTpBbtn').value;
		ket        = document.getElementById('ketTp').value;
		tglTnm     = document.getElementById('tglTp').value;
		proses2    = document.getElementById('proses2').value;
        param = 'kodeTrans=' + kodeTrans + '&batchVar=' + batchVar + '&kdOrg=' + kdOrg + '&jmlhBibitan=' + jmlhBibitan + '&tglTnm=' + tglTnm;
        param += '&ket=' + ket + '&kdOrgTjn=' + kdOrgTjn + '&proses=' + proses2;
        // param += '&ket=' + ket + '&proses=' + proses2;
    } else if (sTab == '3') {
		kodeTrans  = document.getElementById('kdTransAfk').value;
		batchVar   = document.getElementById('batchAfk').value;
		kdOrg      = document.getElementById('kdOrgAfk').value;
		jmlhBibitan= document.getElementById('jmlhAfk').value;
		ket        = document.getElementById('ketAfk').value;
		tglTnm     = document.getElementById('tglAfkirBibit').value;
		proses3    = document.getElementById('proses3').value;
        param = 'kodeTrans=' + kodeTrans + '&batchVar=' + batchVar + '&kdOrg=' + kdOrg + '&jmlhBibitan=' + jmlhBibitan + '&tglTnm=' + tglTnm;
        param += '&ket=' + ket + '&proses=' + proses3;
    } else if (sTab == '4') {
        kodeTrans    = document.getElementById('kdTransaksiSE').value;
		batchVar     = document.getElementById('batch2').value;
		kodeBatchNew = document.getElementById('kodeBatch2').value;
		kodeBatchOld = document.getElementById('kdBatchOld').value;
		kodeorgBibitan2 = document.getElementById('kodeorgBibitan2').value;
		jmlhBibitan  = document.getElementById('jmlhBibitan2').value;
		tglTnm       = document.getElementById('tglTnm2').value;
        oldBibit     = document.getElementById('oldBibit').value;
        proses4      = document.getElementById('proses4').value;

        param = "";
        param += "proses=" + proses4 
        param += "&kodeTrans=" + kodeTrans 
        param += "&batchVar=" + batchVar 
        param += "&kodeBatch=" + kodeBatchNew 
        param += "&kodeBatchOld=" + kodeBatchOld 
        param += "&kdOrgTjn2=" + kodeorgBibitan2 
        param += "&jmlhBibitan=" + jmlhBibitan 
        param += "&tglTnm=" + tglTnm;
        if (oldBibit != "") {
            param += '&oldJenisBibit=' + oldBibit;
        }
    } else if (sTab == '5') {
		kodeTrans  = document.getElementById('kdTransaksiDbt').value;
		batchVar   = document.getElementById('batchDbt').value;
		kdOrg      = document.getElementById('kdOrgDbt').value;
		jmlhBibitan= document.getElementById('jmlhDbt').value;
		ket        = document.getElementById('ketDbt').value;
		tglTnm     = document.getElementById('tglDbt').value;
		proses5    = document.getElementById('proses5').value;
        param = 'kodeTrans=' + kodeTrans + '&batchVar=' + batchVar + '&kdOrg=' + kdOrg + '&jmlhBibitan=' + jmlhBibitan + '&tglTnm=' + tglTnm;
        param += '&ket=' + ket + '&proses=' + proses5;
    } else if (sTab == '7') {
		kodeTrans  = document.getElementById('kdTransPnb').value;
		batchVar   = document.getElementById('batchPnb').value;
		kdOrg      = document.getElementById('kdOrgPnb').value;
		jmlhBibitan= document.getElementById('jmlhPnb').value;
		ket        = document.getElementById('ketPnb').value;
		tglTnm     = document.getElementById('tglPnb').value;
		kdvhc      = trim(document.getElementById('kdvhc').value);
		nmSupir    = document.getElementById('nmSupir').value;
		intexDt    = document.getElementById('intexDt').value;
		custId     = document.getElementById('custId').value;
		detPeng    = document.getElementById('detPeng').value;
		assistenPnb= document.getElementById('assistenPnb').value;
		kplDivBbt= document.getElementById('kplDivBbt').value;
		kplDivKbn= document.getElementById('kplDivKbn').value;
		assistenPnb= document.getElementById('assistenPnb').value;
		KegiatanId = document.getElementById('kegId').value;
		kodeAfd    = document.getElementById('kdAfdeling').value;
		jmlRit     = trim(document.getElementById('jmlRit').value);
		proses7    = document.getElementById('proses7').value;
        param = 'kodeTrans=' + kodeTrans + '&batchVar=' + batchVar + '&kdOrg=' + kdOrg + '&jmlhBibitan=' + jmlhBibitan + '&tglTnm=' + tglTnm;
        param += '&kdvhc=' + kdvhc + '&nmSupir=' + nmSupir + '&intexDt=' + intexDt + '&detPeng=' + detPeng + '&assistenPnb=' + assistenPnb;
        param += '&ket=' + ket + '&proses=' + proses7 + '&custId=' + custId + '&kodeAfd=' + kodeAfd + '&KegiatanId=' + KegiatanId;
        param += '&jmlRit=' + jmlRit + '&kplDivBbt=' + kplDivBbt+ '&kplDivKbn=' + kplDivKbn;
    }
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (sTab == '1') {
                        // loadData1(2);
                        cancelData1();
                    } else if (sTab == '2') {
                        // loadData2(2);
                        cancelData2();
                    } else if (sTab == '3') {
                        savefile();
                        // loadData3(2);
                        // cancelData3();
                    } else if (sTab == '4') {
                        cancelData4();
                    }else if (sTab == '5') {
                        cancelData5();
                        //loadData5(2);
                    } else if (sTab == '7') {
                        //loadData7(2);
                        cancelData7();
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function getBatchForAll() {
    param = 'proses=getBatch';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('batchTp').innerHTML = con.responseText;
                    document.getElementById('batchAfk').innerHTML = con.responseText;
                    document.getElementById('batchDbt').innerHTML = con.responseText
					document.getElementById('batchPnb').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

var mulai = 1;
function loadData1(stat) {
    statCar = document.getElementById('statCari2').value;
    batchCar = document.getElementById('batchCari2').value;
    tglCar = document.getElementById('tglCari2').value;

    param = 'proses=loadData1';
    param += '&statCari2=' + statCar + '&batchCari2=' + batchCar + '&tglCari2=' + tglCar;
    //    alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (stat == 2) {
                        document.getElementById('containData1').innerHTML = con.responseText;
                        getBatchForAll();
                    } else {
                        if (mulai == 1) {
                            document.getElementById('containData1').innerHTML = con.responseText;
                            loadData2(mulai);
                            mulai = 0;
                        } else {
                            document.getElementById('containData1').innerHTML = con.responseText;
                        }

                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast4(num) {
    statCar = document.getElementById('statCari1').value;
    batchCar = document.getElementById('batchCari1').value;
    tglCar = document.getElementById('tglCari1').value;

    param = 'proses=loadData4';
    param += '&statCari1=' + statCar + '&batchCari1=' + batchCar + '&tglCari1=' + tglCar;
    param += '&page='+num
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData4').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData4(stat) {
    statCar = document.getElementById('statCari1').value;
    batchCar = document.getElementById('batchCari1').value;
    tglCar = document.getElementById('tglCari1').value;

    param = 'proses=loadData4';
    param += '&statCari1=' + statCar + '&batchCari1=' + batchCar + '&tglCari1=' + tglCar;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (stat == 1) {
                        document.getElementById('containData4').innerHTML = con.responseText;
                        loadData7(stat);
                    } else if (stat == 2) {
                        document.getElementById('containData4').innerHTML = con.responseText;
                        loadDataStock();
                    } else {
                        document.getElementById('containData4').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBastStock(num) {
    param = 'proses=loadDataStock';
    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containDataStock').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast(num) {
    statCar = document.getElementById('statCari2').value;
    batchCar = document.getElementById('batchCari2').value;
    tglCar = document.getElementById('tglCari2').value;
    param = 'proses=loadData1';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//load data no 1

//load data no 2
function loadData2(stat) {
    statCar = document.getElementById('statCari3').value;
    batchCar = document.getElementById('batchCari3').value;
    tglCar = document.getElementById('tglCari3').value;
    param = 'proses=loadData2';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (stat == 1) {
                        document.getElementById('containData2').innerHTML = con.responseText;
                        loadData3(stat);
                    } else if (stat == 2) {
                        document.getElementById('containData2').innerHTML = con.responseText;
                        loadDataStock(2);
                    } else {
                        document.getElementById('containData2').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast2(num) {
    statCar = document.getElementById('statCari3').value;
    batchCar = document.getElementById('batchCari3').value;
    tglCar = document.getElementById('tglCari3').value;
    param = 'proses=loadData2';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//end load data no 2
//load data no 3
function loadData3(stat) {
    statCar = document.getElementById('statCari4').value;
    batchCar = document.getElementById('batchCari4').value;
    tglCar = document.getElementById('tglCari4').value;
    param = 'proses=loadData3';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (stat == 1) {
                        document.getElementById('containData3').innerHTML = con.responseText;
                        // loadData7(stat);
                        loadData4(stat);
                    } else if (stat == 2) {
                        document.getElementById('containData3').innerHTML = con.responseText;
                        loadDataStock();
                    } else {
                        document.getElementById('containData3').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cariBast3(num) {
    statCar = document.getElementById('statCari4').value;
    batchCar = document.getElementById('batchCari4').value;
    tglCar = document.getElementById('tglCari4').value;
    param = 'proses=loadData3';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData3').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//end load data no 3
//load data tab no 4
function loadData7(stat) {
    statCar = document.getElementById('statCari7').value;
    batchCar = document.getElementById('batchCari7').value;
    tglCar = document.getElementById('tglCari7').value;
    param = 'proses=loadData7';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    if (stat == 1) {
                        document.getElementById('containData7').innerHTML = con.responseText;
                        loadData5(stat);
                    } else if (stat == 2) {
                        document.getElementById('containData7').innerHTML = con.responseText;
                        loadDataStock();
                    } else {
                        document.getElementById('containData7').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cariBast7(num) {
    statCar = document.getElementById('statCari7').value;
    batchCar = document.getElementById('batchCari7').value;
    tglCar = document.getElementById('tglCari7').value;
    param = 'proses=loadData7';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;

    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData7').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//end load data no 4
//load data tab no 5
function loadData5(stat) {
    statCar = document.getElementById('statCari5').value;
    batchCar = document.getElementById('batchCari5').value;
    tglCar = document.getElementById('tglCari5').value;
    param = 'proses=loadData5';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (stat == 1) {
                        document.getElementById('containData5').innerHTML = con.responseText;
                        loadDataStock(stat);
                    } else if (stat == 2) {
                        document.getElementById('containData5').innerHTML = con.responseText;
                        loadDataStock();
                    } else {
                        document.getElementById('containData5').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cariBast5(num) {
    statCar = document.getElementById('statCari2').value;
    batchCar = document.getElementById('batchCari2').value;
    tglCar = document.getElementById('tglCari2').value;
    param = 'proses=loadData5';
    param += '&statCari=' + statCar + '&batchCari=' + batchCar + '&tglCari=' + tglCar;
    param += '&page=' + num;
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containData5').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//end load data no 5

//load data stock
function loadDataStock(ygnke) {

    param = 'proses=loadDataStock';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (ygnke == 2) {
                        document.getElementById('containDataStock').innerHTML = con.responseText;
                        loadData1(2);
                    } else {
                        document.getElementById('containDataStock').innerHTML = con.responseText;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function filFieldHead(kodetrans, btch, kodeBatch, kdeorg, jmlah, tgltnm, jnsbibit, supplerid, tglprodsi, nod, jmlpddo, dtrma, afkri) {

    param = 'kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&proses=getKet';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('kdTransaksi').value = kodetrans;
                    document.getElementById('batch').value = btch;
                    document.getElementById('kodeBatch').value = kodeBatch;
                    document.getElementById('kodeorgBibitan').disabled = true;
                    document.getElementById('tglTnm').disabled = true;
                    l = document.getElementById('kodeorgBibitan');
                    for (a = 0; a < l.length; a++) {
                        if (l.options[a].value == kdeorg) {
                            l.options[a].selected = true;
                        }
                    }
                    document.getElementById('jmlhBibitan').value = jmlah;
                    document.getElementById('tglTnm').value = tgltnm;
                    lrd = document.getElementById('jnsBibitan');

                    for (ard = 0; ard < lrd.length; ard++) {
                        if (lrd.options[ard].value == jnsbibit) {
                            lrd.options[ard].selected = true;
                        }
                    }
                    lrd2 = document.getElementById('supplier_id');

                    for (ard2 = 0; ard2 < lrd2.length; ard2++) {
                        if (lrd2.options[ard2].value == supplerid) {
                            lrd2.options[ard2].selected = true;
                        }
                    }
                    document.getElementById('oldJnsbibit').value = jnsbibit;
                    document.getElementById('tgl2').value = tglprodsi;
                    document.getElementById('ket').value = con.responseText;
                    document.getElementById('nodo').value = nod;
                    document.getElementById('jmlh').value = jmlpddo;
                    document.getElementById('jmlhTrima').value = dtrma;
                    document.getElementById('afkirKcmbh').value = afkri;
                    document.getElementById('proses1').value = 'update1';
					
					setValue2('kodeBatch',kodeBatch);
					setValue2('kodeorgBibitan',kdeorg);
					setValue2('jnsBibitan',jnsbibit);
					setValue2('supplier_id',supplerid);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function filField4(kodetrans, btch, kodeBatch, jmlah, tgltnm, kodeBatchOld, kodeorgBibitan2) {
    document.getElementById('kdTransaksiSE').value = kodetrans;
    document.getElementById('batch2').value = btch;
    document.getElementById('kodeBatch2').value = kodeBatch;
    document.getElementById('kodeBatch2').disabled = true;
    document.getElementById('kdBatchOld').value = kodeBatchOld;
    setValue2('kdBatchOld',kodeBatchOld);
    
    setTimeout(() => {
        getBlokSEB(kodeBatchOld)

        setTimeout(() => {
            // document.getElementById('kodeorgBibitan2').value = kodeorgBibitan2;
            document.getElementById('jmlhBibitan2').value = jmlah;
            document.getElementById('oldBibit').value = kodeBatchOld;
            document.getElementById('tglTnm2').value = tgltnm;
            document.getElementById('tglTnm2').disabled = true;
            document.getElementById('proses4').value = 'update4';
            setValue2('kodeBatch2',kodeBatch);
            setValue2('kodeorgBibitan2',kodeorgBibitan2);
        }, 500);
    }, 500);
    
    
}

function filField2(kodetrans, btch, kdeorg, tujn, tgltnm, jmlh){
    param = 'kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&proses=getKet' + '&kdOrgTjn=' + tujn;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('kdTransaksiTp').value = kodetrans;
                    //document.getElementById('batch').value=btch;
                    longdt = document.getElementById('batchTp');
                    for (along = 0; along < longdt.length; along++) {
                        if (longdt.options[along].value == btch) {
                            longdt.options[along].selected = true;
                        }
                    }
                    document.getElementById('batchTp').disabled = true;
                    longdt2 = document.getElementById('kodeOrgTp');
                    for (along2 = 0; along2 < longdt2.length; along2++) {
                        if (longdt2.options[along2].value == kdeorg) {
                            longdt2.options[along2].selected = true;
                        }
                    }
                    document.getElementById('kodeOrgTp').disabled = true;
                    document.getElementById('tglTp').value = tgltnm;
                    longdt25 = document.getElementById('kodeOrgTjnTp');
                    for (along25 = 0; along25 < longdt25.length; along25++) {
                        if (longdt25.options[along25].value == tujn) {
                            longdt25.options[along25].selected = true;
                        }
                    }

                    document.getElementById('kodeOrgTjnTp').disabled = true;
                    document.getElementById('tglTp').disabled = true;
                    document.getElementById('jmlhTpBbtn').value = jmlh;

                    document.getElementById('ketTp').value = con.responseText;
                    //document.getElementById('proses1').value='update1';
					
					document.getElementById('batchTp').innerHTML = "<option value='"+btch+"'>" + btch + "</option>";
					document.getElementById('kodeOrgTp').innerHTML = "<option value='"+kdeorg+"'>" + kdeorg + "</option>";
					document.getElementById('kodeOrgTjnTp').innerHTML = "<option value='"+tujn+"'>" + tujn + "</option>";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function filField3(kodetrans, btch, kdeorg, tgltnm, jmlh) {
    param = 'kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&proses=getKet';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('kdTransAfk').value = kodetrans;
                    //document.getElementById('batch').value=btch;
                    longdt = document.getElementById('batchAfk');
                    for (along = 0; along < longdt.length; along++) {
                        if (longdt.options[along].value == btch) {
                            longdt.options[along].selected = true;
                        }
                    }
                    document.getElementById('batchAfk').disabled = true;
                    longdt2 = document.getElementById('kdOrgAfk');
                    for (along2 = 0; along2 < longdt2.length; along2++) {
                        if (longdt2.options[along2].value == kdeorg) {
                            longdt2.options[along2].selected = true;
                        }
                    }
                    document.getElementById('kdOrgAfk').disabled = true;
                    document.getElementById('tglAfkirBibit').value = tgltnm;
                    document.getElementById('tglAfkirBibit').disabled = true;
                    document.getElementById('jmlhAfk').value = '';
                    document.getElementById('jmlhAfk').value = jmlh;

                    document.getElementById('ketAfk').value = con.responseText;
                    //document.getElementById('proses1').value='update1';
					
					
					document.getElementById('batchAfk').innerHTML = "<option value='"+btch+"'>" + btch + "</option>";
					document.getElementById('kdOrgAfk').innerHTML = "<option value='"+kdeorg+"'>" + kdeorg + "</option>";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function filField5(kodetrans, btch, kdeorg, tgltnm, jmlh) {
    param = 'kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&proses=getKet';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('kdTransaksiDbt').value = kodetrans;
                    //document.getElementById('batch').value=btch;
                    longdt = document.getElementById('batchDbt');
                    for (along = 0; along < longdt.length; along++) {
                        if (longdt.options[along].value == btch) {
                            longdt.options[along].selected = true;
                        }
                    }
                    document.getElementById('batchDbt').disabled = true;
                    longdt2 = document.getElementById('kdOrgDbt');
                    for (along2 = 0; along2 < longdt2.length; along2++) {
                        if (longdt2.options[along2].value == kdeorg) {
                            longdt2.options[along2].selected = true;
                        }
                    }
                    document.getElementById('kdOrgDbt').disabled = true;
                    document.getElementById('tglDbt').value = tgltnm;
                    document.getElementById('tglDbt').disabled = true;
                    document.getElementById('jmlhDbt').value = '';
                    document.getElementById('jmlhDbt').value = jmlh;

                    document.getElementById('ketDbt').value = con.responseText;
                    //document.getElementById('proses1').value='update1';
					
					document.getElementById('batchDbt').innerHTML = "<option value='"+btch+"'>" + btch + "</option>";
					document.getElementById('kdOrgDbt').innerHTML = "<option value='"+kdeorg+"'>" + kdeorg + "</option>";
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function filField7(kodetrans, btch, kdeorg, tgltnm, jmlh, kdVhc, nmsopir, inTex, kdcust, lokPeng, assist, afd, kegiatanid) {
    param = 'kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&proses=getKet';
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);

                    document.getElementById('kdTransPnb').value = kodetrans;
                    //document.getElementById('batch').value=btch;
                    longdt = document.getElementById('batchPnb');
                    for (along = 0; along < longdt.length; along++) {
                        if (longdt.options[along].value == btch) {
                            longdt.options[along].selected = true;
                        }
                    }
                    document.getElementById('batchPnb').disabled = true;
                    longdt2 = document.getElementById('kdOrgPnb');
                    for (along2 = 0; along2 < longdt2.length; along2++) {
                        if (longdt2.options[along2].value == kdeorg) {
                            longdt2.options[along2].selected = true;
                        }
                    }
                    longdt5 = document.getElementById('kegId');
                    for (along5 = 0; along5 < longdt5.length; along5++) {
                        if (longdt5.options[along5].value == kegiatanid) {
                            longdt5.options[along5].selected = true;
                        }
                    }

                    document.getElementById('kdOrgPnb').disabled = true;
                    document.getElementById('tglPnb').value = tgltnm;
                    document.getElementById('jmlhPnb').value = '';
                    document.getElementById('jmlhPnb').value = jmlh;
                    document.getElementById('kdvhc').value = kdVhc;
                    document.getElementById('nmSupir').value = nmsopir;
                    document.getElementById('detPeng').value = lokPeng;
                    longdt25 = document.getElementById('assistenPnb');
                    for (along25 = 0; along25 < longdt25.length; along25++) {
                        if (longdt25.options[along25].value == assist) {
                            longdt25.options[along25].selected = true;
                        }
                    }
                    document.getElementById('ketPnb').value = con.responseText;
                    longdt28 = document.getElementById('intexDt');
                    for (along28 = 0; along28 < longdt28.length; along28++) {
                        if (longdt28.options[along28].value == inTex) {
                            longdt28.options[along28].selected = true;
                        }
                    }
                    getCustdata(inTex, kdcust, afd);

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function delFieldHead(tanggal, kodetrans, btch, kdeorg, tgltnm, jnsbibit) {
    param = 'proses=delData';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&oldJenisBibit=' + jnsbibit + '&tglTnm=' + tgltnm;
    param += '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData1(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function delField4(tanggal, kodetrans, btch, kodeBatch, tgltnm) {
    param = 'proses=delData4';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kodeBatch=' + kodeBatch + '&tglTnm=' + tgltnm;
    param += '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData4(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function delField2(tanggal, kodetrans, btch, kdeorg, tjan) {
    param = 'proses=delData2';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&kdOrgTjn=' + tjan + '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData2(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function delField3(tanggal, kodetrans, btch, kdeorg, tjan) {
    param = 'proses=delData3';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&kdOrgTjn=' + tjan + '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData3(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function delField5(tanggal, kodetrans, btch, kdeorg, tjan) {
    param = 'proses=delData3';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&kdOrgTjn=' + tjan + '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData5(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function delField7(tanggal, kodetrans, btch, kdeorg, rit, kodevhc) {
    param = 'proses=delData7';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&rit=' + rit + '&tanggal=' + tanggal + '&kodevhc=' + kodevhc;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Delete, are you sure")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData7(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function postingData(kodetrans, btch, kdeorg, tgltnm) {
    param = 'proses=postData';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&tglTnm=' + tgltnm;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Are you sure?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData1(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function postingData4(kodetrans, btch, kodeBatchOld, tgltnm) {
    param = 'proses=postData4';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kodeBatchOld=' + kodeBatchOld + '&tglTnm=' + tgltnm;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Are you sure?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData4(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function postingData2(tanggal, kodetrans, btch, kdeorg, kdOrgTjn, jmlhBibitan) {
    param = 'proses=postData2';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&kdOrgTjn=' + kdOrgTjn + '&jmlhBibitan=' + jmlhBibitan;
    param += '&tanggal=' + tanggal;
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Proses transplanting dari PN ke MN akan membentuk Jurnal Otomatis, Anda yakin untuk melakukan ini ?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // alertify.alert("Proses selesai, silahkan cek jurnal yg terbentuk dengan kode jurnal "+kodetrans);
                    loadData2(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function postingData3(tanggal, kodetrans, btch, kdeorg, jmlhBibitan) {
    param = 'proses=postData3';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&tanggal=' + tanggal + '&jmlhBibitan=' + jmlhBibitan;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Are you sure?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData3(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function postingData5(tanggal, kodetrans, btch, kdeorg, tujuan, jmlhBibitan) {
    param = 'proses=postData5';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&tanggal=' + tanggal + '&jmlhBibitan=' + jmlhBibitan;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Are you sure.?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData5(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function postingData7(tanggal, kodetrans, btch, kdeorg, rit, kodevhc, jmlhBibitan,keterangan) {
    param = 'proses=postData7';
    param += '&kodeTrans=' + kodetrans + '&batchVar=' + btch + '&kdOrg=' + kdeorg + '&jmlhBibitan=' + jmlhBibitan + '&ketPnb=' + keterangan;
    param += '&jmlRit=' + rit + '&kdvhc=' + kodevhc + '&tanggal=' + tanggal;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';
    if (confirm("Are you sure..?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    loadData7(2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function showupload(ketPnb){
    ev = 'event';
    //showformupload(ev);
    param='proses=showuploadd&ketPnb='+ketPnb;
    
    tujuan='bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                }else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
                    alertify.popup().destroy();
                    alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('400px','400px');
                    
                    loadfiles(ketPnb);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}
// fungsi untuk progress bar
function progressHandler(event) {
    document.getElementById("progressBar").style.display="block";
    document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded/1024)) + " KB of " + numberFormat(Math.round(event.total/1024))+" KB";
    var percent = (event.loaded / event.total) * 100;
    document.getElementById("progressBar").value = Math.round(percent);
    document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
    document.getElementById("progressBar").style.display="none";
    document.getElementById("status").innerHTML = event.target.responseText;
    document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("status").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("status").innerHTML = "Upload Aborted";
}

function submitfile(ketPnb) {
    var file = document.getElementById("upload").files[0];
    var formdata = new FormData();
    formdata.append("fileupload", getValue('upload'));
    formdata.append("file", file);
    formdata.append("notransaksi", ketPnb);
    if (getValue('upload') == "") {
        alertify.alert("Upload file has been empty.");
        return false;
    }
    if(ketPnb==''){
        alertify.alert("Nomor transaksi tidak ditemukan.");
        return false;
    }

    var con = createXMLHttpRequest();
    document.getElementById('btnsubmit').style.display="none";
    //tambahan progress bar
    con.upload.addEventListener("progress", progressHandler, false);
    con.addEventListener("load", completeHandler, false);
    con.addEventListener("error", errorHandler, false);
    con.addEventListener("abort", abortHandler, false);
    //tambahan progress bar -end-
    con.open("POST", "bibit_slave_keluar_masuk.php?proses=submitfile", true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
                    alertify.alert('Uploaded Success.');
                    document.getElementById('btnsubmit').style.display="";
                    document.getElementById("upload").value = "";
                    loadfiles(ketPnb);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadfiles(ketPnb) {
    param = 'proses=loadfiles&ketPnb=' + ketPnb;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function deletefile(ketPnb, namafile) {
    param = "proses=deletefile";
    param += "&ketPnb=" + ketPnb;
    param += "&namafile=" + namafile;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    loadfiles(ketPnb);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKodeorg() {
    //alert("masuk");
    btch = document.getElementById('batchTp').value;
    param = 'proses=getKodeorg';
    param += '&batchVar=' + btch;
    //  alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (con.responseText != '') {
                        document.getElementById('kodeOrgTp').innerHTML = con.responseText;
                        //document.getElementById('kodeOrgTp').disabled=true;
                        document.getElementById('kodeOrgTjnTp').value = '';
                    } else {
                        document.getElementById('kodeOrgTp').innerHTML = "<option value=''>" + pilh + "</option>";
                        document.getElementById('kodeOrgTjnTp').value = '';
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKodeorg2() {
    btch = document.getElementById('batchAfk').value;
    param = 'proses=getKodeorg';
    param += '&batchVar=' + btch;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (con.responseText != '') {
                        document.getElementById('kdOrgAfk').innerHTML = con.responseText;
                        //document.getElementById('kdOrgAfk').disabled=true;
                    } else {
                        document.getElementById('kdOrgAfk').innerHTML = "<option value=''>" + pilh + "</option>";
                        document.getElementById('kdOrgAfk').disabled = false;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getKodeorg3(divisi) {
    btch = document.getElementById('batchDbt').value;
    param = 'proses=getKodeorg';
    param += '&batchVar=' + btch;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (con.responseText != '') {
                        document.getElementById('kdOrgDbt').innerHTML = con.responseText;
                        //document.getElementById('kdOrgDbt').disabled=true;
                    } else {
                        document.getElementById('kdOrgDbt').innerHTML = "<option value=''>" + pilh + "</option>";
                        document.getElementById('kdOrgDbt').disabled = false;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKodeorgN(divisi) {
    btch = document.getElementById('batchPnb').value;
    param = 'proses=getKodeorgN';
    param += '&batchVar=' + btch;
    
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('kdOrgPnb').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getKodeorg7() {
    btch = document.getElementById('batchPnb').value;
    param = 'proses=getKodeorg';
    param += '&batchVar=' + btch;
    //alert(param);
    tujuan = 'bibit_slave_keluar_masuk.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    if (con.responseText != '') {
                        document.getElementById('kdOrgPnb').innerHTML = con.responseText;
                        //document.getElementById('kdOrgPnb').disabled=true;
                    } else {
                        document.getElementById('kdOrgPnb').innerHTML = "<option value=''>" + pilh + "</option>";
                        document.getElementById('kdOrgPnb').disabled = false;
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function cekSamaGak() {
    kdOrg = document.getElementById('kodeOrgTjnTp').value;
    kodeOrgTp = document.getElementById('kodeOrgTp').value;
    if (kdOrg == kodeOrgTp) {
        document.getElementById('kodeOrgTjnTp').options[0].selected = true;
    }
}

function getCustdata(intx, kdorg, afd) {
    if ((intx == 0) || (kdorg == 0) || (afd == 0)) {
        intexDt = document.getElementById('intexDt').value;
        param = 'proses=getCust';
        param += '&intexDt=' + intexDt;
    } else {
        intexDt = intx;
        kdOrg = kdorg;
        if (intexDt == '') {
            intexDt = document.getElementById('intexDt').value;
        }
        param = 'proses=getCust';
        param += '&intexDt=' + intexDt;
        param += '&kdOrg=' + kdOrg;
    }
    // alert(param);
    document.getElementById('kdAfdeling').disabled = false;
    // if (intexDt != '2') {
    //     document.getElementById('kdAfdeling').disabled = false;
    // } else {
    //     document.getElementById('kdAfdeling').disabled = true;
    //     document.getElementById('kdAfdeling').innerHTML = "<option value=''>" + plh + "</option>";
    // }

    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    document.getElementById('custId').innerHTML = con.responseText
                    if (afd != 0) {
                        getKodeorg(kdorg, afd);
                    }

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getKodeorgAfd(kbnid, afdid) {
    if ((kbnid == 0) || (afdid == 0)) {
        kdKbn = document.getElementById('custId').value;
        param = 'proses=getAfd';
        param += '&kdOrg=' + kdKbn;
    } else {
        kdKbn = kbnid;
        kodeAfd = afdid;
        param = 'proses=getAfd';
        param += '&kdOrg=' + kdKbn + '&kodeAfd=' + kodeAfd;
    }
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    document.getElementById('kdAfdeling').innerHTML = con.responseText

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getKodeorgBlok() {
    kdKbn = document.getElementById('custId').value;
    intex = document.getElementById('intexDt').value;
    param = 'proses=getBlok';
    param += '&kdOrg=' + kdKbn;
    param += '&intexDt=' + intex;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //	alert(con.responseText);
                    isdt = con.responseText.split("###");
                    document.getElementById('kdAfdeling').innerHTML = isdt[0];
                    document.getElementById('kplDivKbn').innerHTML = isdt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getBlokSEB(kdBatchOld) {
    kdBatchOld = document.getElementById('kdBatchOld').value;
    param = 'proses=getBlokSEB';
    param += '&kodeBatchOld=' + kdBatchOld;
    tujuan = 'bibit_slave_keluar_masuk.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kodeorgBibitan2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}