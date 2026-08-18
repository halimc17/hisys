function add_new_data() {
    document.getElementById('detail').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('formpencarianheader').style.display = 'none';
    //batalheader();
}

function displayList() {
    document.getElementById('listData').style.display = 'block';
    document.getElementById('formpencarianheader').style.display = '';
    document.getElementById('detail').style.display = 'none';
    cancelHeader();
}

function batallist() {
    document.getElementById('perSch').value = '';
    loadData(0);
}

function previewx(tanggal,idjenis,kodeorg,tipekar){
    param   =  'method=preview';
    param   += '&tanggalx=' + tanggal;
    param   += '&kom=' + idjenis;
    param   += '&org=' + kodeorg;
    param   += '&tipekar=' + tipekar;
    tujuan  =  'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    // title   = 'Detail Cuti';
                    // width   = '';
                    // height  = '';
                    // ev      = 'event';
                    // content = "<fieldset style=max-width:600px><legend><b>"+ notransaksi +"</b></legend>"+con.responseText+"</fieldset>";
                    // closeDialog();
                    // showDialog2(title, content, width, height, ev);
                    alertify.popup().destroy();
                    alertify.popup('PREVIEW',"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('65%','80%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '600';
    height = '400';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog2(title, content, width, height, ev);
}
function excel(ev, per, kom, org, tipekar) {
    param = 'method=excel' + '&per=' + per + '&kom=' + kom + '&org=' + org+ '&tipekar=' + tipekar;
    //alert(param);
    tujuan = 'sdm_slave_3ctbsExcel.php';
    judul = 'Print Excel';
    printFile(param, tujuan, judul, ev)
}

function bataldetail() {
    document.getElementById('kar').selectedIndex = 0;
	document.getElementById('saveDetail').value='saveDetail';
    loadDataDetail();
}


function form_ajukan(id){
    width = '300';
    height = '';
    content = "<fieldset style=width:95%><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
    
    param = 'method=form_ajukan&idxj='+id;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan(){
    notransaksi     =document.getElementById('notransaksi_ajukan').value;

    jlh         =document.getElementById('jlh').value;
    var param   = 'method=ajukan';
    param       += '&idxj=' + notransaksi;
    param       += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
    }
    if(jlh==0){
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_3ctbs.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alert('Done');
                    loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function saveDetail() {
    tanggalx = document.getElementById('tanggalx').value;
    kom = document.getElementById('kom').value;
    kar = document.getElementById('kar').value;
    org = document.getElementById('org').value;
    tipekar = document.getElementById('tipekar').value;
    met = document.getElementById('saveDetail').value;

    param = 'method='+ met + '&tanggalx=' + tanggalx + '&tipekar=' + tipekar + '&kom=' + kom + '&kar=' + kar + '&org=' + org ;
    //alert(param);
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    //lockHeader();
                    //document.getElementById('detailForm').style.display='block';
                    bataldetail();
                    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function cancelFormBarang() {
    document.getElementById('nobpb').value = '';
    document.getElementById('nopo').value = '';
    document.getElementById('nopp').value = '';
    document.getElementById('kodebarang').value = '';
    document.getElementById('kurs').value = '';
    document.getElementById('namabarang').value = '';
    document.getElementById('jumlah').value = '';
    document.getElementById('satuan').value = '';
    document.getElementById('matauang').value = 'IDR';
    document.getElementById('hargasatuan').value = '';

}

function loadDataDetail() {
    //alert('masuk');
    org = document.getElementById('org').value;
    tanggalx = document.getElementById('tanggalx').value;
    kom = document.getElementById('kom').value;
    tipekar = document.getElementById('tipekar').value;
    param = 'method=loadDetail' + '&tanggalx=' + tanggalx + '&kom=' + kom +'&tipekar=' + tipekar + '&org=' + org;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    //return;
                    //document.getElementById('contentDetail').innerHTML=con.responseText;
                    document.getElementById('loaddatadetail').style.display = 'block';
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancel() {
    document.location.reload();
    document.getElementById('tipekar').value = '';
}
function editdetail(kar,jum,ket){
	document.getElementById('kar').value = kar;
	document.getElementById('jum').value = jum;
	document.getElementById('ket').value = ket;
	document.getElementById('saveDetail').value = 'updatedetail';
}


function edit(tanggal, kom, org,tipekar,keterangan) {
    document.getElementById('displayinsert').style.display = 'none';
    document.getElementById('inputdetail').style.display = 'block';

	document.getElementById('detail').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    document.getElementById('formpencarianheader').style.display = 'none';
	
    document.getElementById('tanggalx').value = tanggal;
    document.getElementById('kom').value = kom;
    document.getElementById('org').value = org;
    document.getElementById('tipekar').value = tipekar;
    document.getElementById('keterangan').value = keterangan;
    //document.getElementById('displayall').style.display = 'block';
    //document.getElementById('detailForm').style.display='block';
    lockHeader(org,tipekar);

}

function saveHeader() {
    tipekar = document.getElementById('tipekar').value;
    org = document.getElementById('org').value;
    tanggalx = document.getElementById('tanggalx').value;
    kom = document.getElementById('kom').value;

    param = 'tanggalx=' + tanggalx + '&kom=' + kom + '&org=' + org + '&method=cekHeader' + '&tipekar=' + tipekar;

    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    detail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function detail() {
    org = document.getElementById('org').value;
    tanggalx = document.getElementById('tanggalx').value;
    kom = document.getElementById('kom').value;
    tipekar = document.getElementById('tipekar').value;
    param = 'tanggalx=' + tanggalx + '&kom=' + kom + '&org=' + org + '&method=detail';
    if (tipekar != '') {
        param += '&tipekar=' + tipekar;
    }
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('displayinsert').style.display='block';
                    document.getElementById('displayinsert').innerHTML = con.responseText;
                    lockHeader(org,tipekar);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedt() {
    org = document.getElementById('org').value;
    tanggalx = document.getElementById('tanggalx').value;
    kom = document.getElementById('kom').value;
    keterangan = document.getElementById('keterangan').value;
    totRow = document.getElementById('totrows').value;
    var allData = '';
    for (dwc = 0; dwc < totRow; dwc++) {
        allData += "&kar[" + dwc + "]=" + document.getElementById('kar_' + dwc).value;
    }

    param = 'tanggalx=' + tanggalx + '&kom=' + kom + '&org=' + org + '&keterangan=' + keterangan + '&method=savedt' + '&totRow=' + totRow;
    param += allData;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //cancel();
					document.getElementById('inputdetail').style.display='block';
					document.getElementById('displayinsert').style.display='none';
                    document.getElementById('displayinsert').innerHTML = '';
					
					loadDataDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function delHead(tanggal, kom, org,tipekar) {
    param = 'method=delHead' + '&tanggalx=' + tanggal + '&kom=' + kom + '&org=' + org+ '&tipekar=' + tipekar;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function cariBast(num) {

    tanggalxsch = document.getElementById('tanggalxsch').value;

    param = 'method=loadData' + '&tanggalxsch=' + tanggalxsch + '&page=' + num;

    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //displayList();

                    document.getElementById('container').innerHTML = con.responseText;
                    //loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(page) {
    tanggalxsch = document.getElementById('tanggalxsch').value;
    param = 'method=loadData' + '&tanggalxsch=' + tanggalxsch+'&page=' + page;;
    //alert(param);
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
					document.getElementById('container').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function lockHeader(org,tipekar) {
    document.getElementById('saveHeader').disabled = true;
    document.getElementById('tipekar').disabled = true;
    //document.getElementById('cancelHeader').disabled=true;
    document.getElementById('tanggalx').disabled = true;
    document.getElementById('kom').disabled = true;
    document.getElementById('org').disabled = true;
    document.getElementById('keterangan').disabled = true;
    if ((tipekar == '')) {
        loadKar(org);
    }else{
        loadKar(org,tipekar);
    }

}

function cancelHeader(org) {
    document.getElementById('saveHeader').disabled = false;
    document.getElementById('tanggalx').disabled = false;
    document.getElementById('kom').disabled = false;
    document.getElementById('org').disabled = false;
    document.getElementById('keterangan').disabled = false;
    cancel();

}

//load kary
function loadKar(org,tipekar) {
    // jabatan = document.getElementById('jabatan').options[jabatan.selectedIndex].value;
    // tipekar = document.getElementById('tipekar').options[tipekar.selectedIndex].value;
    tanggalx = document.getElementById('tanggalx').value;
    param = 'method=loadKar' + '&org=' + org + '&tipekar=' + tipekar+ '&tanggalx=' + tanggalx;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('kar').innerHTML = con.responseText;
                    loadDataDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function DelDetail(tanggalx, kar, kom) {
    param = 'method=deleteDetail' + '&kar=' + kar + '&tanggalx=' + tanggalx + '&kom=' + kom;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDataDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function postingData(per, kom, org,tipekar) {
    param = 'method=posting' + '&org=' + org + '&per=' + per + '&kom=' + kom+ '&tipekar=' + tipekar;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function unposting(per, kom, org,tipekar) {
    param = 'method=unposting' + '&org=' + org + '&per=' + per + '&kom=' + kom+ '&tipekar=' + tipekar;
    tujuan = 'sdm_slave_3ctbs.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}