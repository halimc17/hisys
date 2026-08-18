function createNew(){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('method').value ='insert';
    batalcari();
    hapus();
}

function displayList(){
    hapus();
    batalcari();
    document.getElementById('addNew').style.display ='none';
    document.getElementById('listData').style.display ='block';
    loadData(0);
}

function batalcari(){
	document.getElementById('nospkcr').value='';
	document.getElementById('divsch').value='';
	document.getElementById('tglsch').value='';
	document.getElementById('kontrakcr').value='';
}

function hapus(){
	setValue2('kodeorg',null)
	setValue2('periode',null)
	setValue2('tglmulai',null)
	setValue2('tglselesai',null)
	setValue2('periodebyr',null)
	document.getElementById('method').value='insert';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('periode').disabled=false;
    document.getElementById('tglmulai').disabled=false;
    document.getElementById('tglselesai').disabled=false;
    document.getElementById('periodebyr').disabled=false;
    document.getElementById('spk').disabled=false;
    document.getElementById('tomboldetail').disabled = false;
    document.getElementById('detail').style.display = 'none';
}

function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	paged   = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num){
	nospkcr     = document.getElementById('nospkcr').value;
	divsch      = document.getElementById('divsch').value;
	tglsch      = document.getElementById('tglsch').value;
	kontrakcr   = document.getElementById('kontrakcr').value;

    param   ='method=loadData&page=' + num;
    if(nospkcr != ''){      
        param  +='&nospkcr=' + nospkcr.trim();
    }
    if(divsch != ''){
        param  +='&divsch=' + divsch.trim();
    }
    if(tglsch != ''){
        param  +='&tglsch=' + tglsch.trim();
    }
    if(kontrakcr != ''){
        param  +='&kontrakcr=' + kontrakcr.trim();
    }
    tujuan  ='lgl_slave_rekapsewahm.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                    dataSlave = JSON.parse(con.responseText);
                    document.getElementById('addNew').style.display ='none';
                    document.getElementById('listData').style.display ='block';
                    document.getElementById('container').innerHTML      = dataSlave.tab;
                    document.getElementById('footData').innerHTML       = dataSlave.foot;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}

function edit(kodeorg,periode,spk,periodebyr){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    setValue2('kodeorg',kodeorg)
    setValue2('periode',periode)
    setValue2('periodebyr',periodebyr)
    getnospk()
    param = 'method=getedit';
    param += '&kodeorg=' + kodeorg;
    param += '&periode=' + periode;
    param += '&spk=' + spk;

    post_response_text(tujuan, param, respog);
    
    function respog()
    {
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    data = JSON.parse(con.responseText);
                    setValue2('tglmulai',data.tgldari)
                    setValue2('tglselesai',data.tglsampai)
                    setValue2('spk',spk)
                    detail();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function getnospk() {
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	periodebyr= document.getElementById('periodebyr').value;
	
	param = 'method=getnospk';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('spk').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function detail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	tglmulai = document.getElementById('tglmulai').value;
	tglselesai = document.getElementById('tglselesai').value;
	spk = document.getElementById('spk').value;
	tgl = document.getElementById('tgl').value;
	if (kodeorg == '' || periode == '' || spk=='' || periodebyr=='' ) {
		alertify.alert('Kode Organisasi, Periode Bulan, Periode Bayar dan SPK Wajib diisi !');
		return;
	}
	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('spk').disabled = true;
	document.getElementById('periode').disabled = true;
	document.getElementById('tgl').disabled = true;
	document.getElementById('periodebyr').disabled = true;
	document.getElementById('tglmulai').disabled = true;
	document.getElementById('tglselesai').disabled = true;
	param = 'method=detail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&tgl=' + tgl;
	param += '&periodebyr=' + periodebyr;
	param += '&tglmulai=' + tglmulai;
	param += '&tglselesai=' + tglselesai;
	tujuan = 'lgl_slave_rekapsewahm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
                    document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
                    leftFixedTable()
                    loaddatadetail()
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
        simpan(0, maxRow);
	}
}

function simpan(currRow,maxRow){
    method      = document.getElementById('method').value;
    kodeorg     = document.getElementById('kodeorg').value;
    periode     = document.getElementById('periode').value;
    tglmulai    = document.getElementById('tglmulai').value;
    tglselesai  = document.getElementById('tglselesai').value;
    periodebyr	= document.getElementById('periodebyr').value;
    spk	        = document.getElementById('spk').value;
	
	e = document.getElementsByName("click[]");
    if(e[currRow].checked == true){
        cek = 1
    }else{
        cek = 0
    }
    param       ='method=' + method;
    param       +='&cek=' + cek;
    if(e[currRow].checked==true){
        param       +='&kodeorg='       + kodeorg;
        param       +='&periode='       + periode;
        param       +='&tglmulai='      + tglmulai;
        param       +='&tglselesai='    + tglselesai;
        param       +='&periodebyr='    + periodebyr;
        param       +='&spk='           + spk;

        param       +='&notraksi='      + getValue('notraksi_'+currRow);
        param       +='&jeniskegiatan=' + getValue('jenispekerjaan_'+currRow);
        param       +='&blok='          + getValue('blok_'+currRow);
        param       +='&tanggal='       + getValue('tanggal_'+currRow);
        param       +='&prestasi='      + remove_comma_var(getValue('beratmuatan_'+currRow));
        param       +='&harga='         + remove_comma_var(getValue('harga_'+currRow));
        param       +='&hm='            + getValue('hm_'+currRow);
        param       +='&rupiah='        + remove_comma_var(getValue('rupiah_'+currRow));
    }
    tujuan      ='lgl_slave_rekapsewahm.php';
    post_response_text(tujuan, param, respog);		

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('tr_' + currRow).style.backgroundColor = 'red';
				} else {
					if (currRow != undefined) {
						document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
                    if(e[currRow].checked==true){
                        document.getElementById('tr_' + currRow).style.display = 'none';
                    }
					currRow += 1;
					if ((currRow > (parseInt(maxRow)-1)) || (maxRow == undefined)) {
                        alertify.set('notifier','position', 'top-right');
                        alertify.set('notifier','delay', 4);
                        alertify.success("Data Berhasil disimpan");
                        for (let i = 0; i < e.length; i++) {
                            document.getElementById('tr_' + i).style.backgroundColor = '#d7ebfa';
                        }
						detail();
                        setTimeout(() => {
                            loaddatadetail();
                        }, 400);
					} else {
						simpan(currRow, maxRow);
					}
				}
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function del(kodeorg,periode,spk,periodebyr) {
    param   ='method=delete'+'&kodeorg='+kodeorg+'&periode='+periode+'&spk='+spk+'&periodebyr='+periodebyr;
    tujuan  ='lgl_slave_rekapsewahm.php';

    
	alertify.confirm("Informasi","Anda Yakin Menghapus : "+spk+" periode : "+periode+" Termin : "+periodebyr+"???",
    function(){
        post_response_text(tujuan, param, respon);
    },
    function(){
        return;
    }
    );

    function respon()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                    hapus();
                    document.getElementById('container').innerHTML=con.responseText;
                    alertify.alert("Data has been deleted !!!");
                    loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
                hapus();
            }
        }	
    }	
}


function getformnotraksi() {
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	periodebyr= document.getElementById('periodebyr').value;
	spk       = document.getElementById('spk').value;
	tgl       = document.getElementById('tglmulai').value;
	tgl2      = document.getElementById('tglselesai').value;
	
	param  = 'method=getformnotraksi';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&tglmulai=' + tgl;
	param += '&tglselesai=' + tgl2;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// width = '';
					// height = '';
					// content = "<fieldset><div id=containerd style=\"max-height:500px;width:100%;overflow:auto;\"></div></fieldset>";
					// ev = 'event';
					// title = "";
					// showDialog1(title, content, width, height, ev);
					document.getElementById('getformnotraksi').innerHTML = con.responseText;
					// alertify.popup().destroy();
					// alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','80%');
					getnotraksi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnotraksi() {
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	periodebyr= document.getElementById('periodebyr').value;
	spk       = document.getElementById('spk').value;
	tgl       = document.getElementById('tglmulai').value;
	tgl2      = document.getElementById('tglselesai').value;
	
	
	param = 'method=getnotraksi';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&tglmulai=' + tgl;
	param += '&tglselesai=' + tgl2;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('getformnotraksi').innerHTML = con.responseText;
					document.getElementById('clickall').checked = false;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	spk = document.getElementById('spk').value;
	
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function clickall(){
	e = document.getElementsByName("click[]");
	h = document.getElementsByName('cekharga[]');
	for(i=0;i<e.length;i++){
        if(document.getElementById('clickall').checked == true){
            if(e[i].checked==false){
                e[i].checked=true;
            }else{
                e[i].checked=true;
            }
        }else{
            if(e[i].checked==true){
                e[i].checked=false;
            }else{
                e[i].checked=false;
            }
        }
	}
}
function hitungclick() {
	e = document.getElementsByName("click[]");
    no=0;
	for(i=0;i<e.length;i++){
        if(e[i].checked==true){
            no += 1;
        }
	}
    if((parseInt(no)) == e.length){
        document.getElementById('clickall').checked = true;
    }else{
        document.getElementById('clickall').checked = false;
    }
}

function posting(kodeorg,periode,spk,periodebyr) {
	param = 'method=posting' + '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	if (confirm('Anda yakin ingin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexcel(kodeorg, periode,spk,periodebyr,jenis){
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&spk=' + spk+ '&periodebyr=' + periodebyr+ '&jenis=' + jenis;
	tujuan = 'lgl_slave_rekapsewahm.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// printFile(param,tujuan,title,ev);
	printnopopup(tujuan);

}


function getdetailjurnal(notransaksi,nobapp,kodeorg,tanggal){
	param = "method=getdetailjurnal&notransaksi="+notransaksi+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&nobapp="+nobapp;
    
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function formpostingDataAll(nopengajuan,notransaksi,nobapp,kodeorg,tanggal,termin,numRow){
	param = "method=formpostingDataAll&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin+"&nobapp="+nobapp;
    // width = '';
    // height = '';
	// ev = 'event';
    // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    // title = "Posting All";
    // showDialog2(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic2').style.right = (80) + 'px';
	// document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                    //document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postingDataAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Posting semua ???")) {
		savepostingDataAll(1, maxRow);
	}
}
function savepostingDataAll(currRow, maxRow) {
	keg            = document.getElementById('kegpost'+ currRow).innerHTML;
	blok           = document.getElementById('blokpost' + currRow).innerHTML;
	nobapppost     = document.getElementById('nobapppost' + currRow).innerHTML;
	tanggal        = document.getElementById('tglpost' + currRow).innerHTML;
	jumlahrealisasi= document.getElementById('realpost' + currRow).innerHTML;
	termin         = document.getElementById('termin' + currRow).innerHTML;
	notransaksi    = document.getElementById('notrpost' + currRow).value;
	kodeorg        = document.getElementById('kdorgpost' + currRow).value;
	koderekanan    = document.getElementById('kdrekpost' + currRow).value;
	nobapp         = document.getElementById('nobapppost' + currRow).innerHTML;
	
	ev ='event';
	
	var segment = '0000000001';
	var kodeblok = blok;
	var unit = kodeorg;
	
	var param = "kodeorg="+kodeorg+"&koderekanan="+koderekanan+"&termin="+termin;
    param += "&notransaksi="+notransaksi+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    
	param += "&nobapp="+nobapp;
	param += "&blokalokasi="+kodeblok;
	param += "&nobapppost="+nobapppost;
    param += "&tanggal="+tanggal;
    param += "&jumlahrealisasi="+jumlahrealisasi;
	
	tujuan = 'log_slave_realisasispk_posting.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('tr_' + currRow).style.backgroundColor = 'red';
					//unlockScreen();
				} else {
					if (currRow != undefined) {
						document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						//tipeview='viewhtml';
						//viewdetail(notransaksi,unit,tipeview,ev)
						// closeDialog();
						// closeDialog2();
						alertify.popup().destroy();
						alertify.popup2().destroy();
						getpage();
						alertify.alert('Done');
					} else {
						savepostingDataAll(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function viewdetailbapp(notransaksi,kodeorg,tipeview,ev,nobapp){
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview+'&nobapp='+nobapp;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					// document.getElementById('contRekap').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe){
	param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin;
    // width = '';
    // height = '';
    // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    // title = "View";
    // showDialog2(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic2').style.right = (80) + 'px';
	// document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                    //document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function showhidedetail(rows,total){
	for(var i = 1; i <= total; i++) {
		key = document.getElementById('tr_dt2_'+rows+'_'+i).style.display;
		if(key=='none'){
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='';
		}else{
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='none';
		}
    }
}

function UploadFile(notransaksi,tanggal,termin,numRow,ev,nopengajuan) {
	title = "List File";
	//formajukan(title,ev);
	param = 'method=UploadFile' + '&notransaksi=' + notransaksi+ '&tanggal=' + tanggal+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containervoid').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('35%','50%'); 
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles(notransaksi,termin,nopengajuan) {
	param = 'method=loadfiles&notransaksi=' + notransaksi+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').innerHTML;
	var pengajuanspk = document.getElementById('pengajuanspk').innerHTML;
	var tanggal = document.getElementById('tanggal').innerHTML;
	var termin = document.getElementById('terminup').innerHTML;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("pengajuanspk", pengajuanspk);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("termin", termin);
	formdata.append("tanggal", tanggal);
	if (getValue('upload') == "") {
		alertify.alert("warning : Upload file has been empty.");
		return false;
	}
	if (notransaksi == "" || pengajuanspk=="") {
		alertify.alert("warning : Nomor Transaksi di Perlukan !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "log_slave_realisasispkx.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('btnsubmit').disabled=false;
				} else {
					//=== Success Response
					alertify.alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,termin,pengajuanspk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile,termin,nopengajuan) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	param += "&nopengajuan=" + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function form_ajukan(kodeorg,notransaksi, tanggal,termin, numrow,jlhrealisasi,nobapp) {
	if(jlhrealisasi==0){
		alertify.alert("Gagal, Jumlah Realisasi masih 0");
		return false;
	}
	// width = '300';
	// height = '';
	// content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:180px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog5(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&tanggal=' + tanggal + '&termin=' + termin + '&numrow=' + numrow+ '&kodeorg=' + kodeorg;
	param += "&nobapp="+nobapp;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containeraju').innerHTML = con.responseText;
					alertify.popup2("Ajukan",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30%','40%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(ev) {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	tanggal = document.getElementById('tgljurnal').value;
	unit = document.getElementById('unitdt2').value;
	termin = document.getElementById('termin_aju').innerHTML;
	nopengajuan = document.getElementById('nopengajuan_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += "&tanggal="+tanggal;
	param += "&termin="+termin;
	param += "&nopengajuan="+nopengajuan;
	
	if (kepada == '') {
		alertify.alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					tipeview='viewhtml';
					alertify.popup().destroy();
					alertify.popup2().destroy();
					//viewdetail(notransaksi,unit,tipeview,ev)
					//closeDialog5();
					//alertify.alert('Sucses');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewdetail(notransaksi,kodeorg,tipeview,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	// title = "";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	// document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					//document.getElementById('contRekap').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getapprovaldetail(nopengajuan,kodeorg,ev) {
	param = 'method=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('35%','50%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deletedetail(kodeorg, periode, notraksi) {
	param = 'method=deletedetail' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notraksi=' + notraksi;
	tujuan = 'lgl_slave_rekapsewahm.php';
	if(confirm(' Anda yakin ingin menghapus Notransaksi Traksi '+notraksi+' ?')){
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(kodeorg,periode,spk,nobapp,periodebyr, numrow) {
	param = 'method=unposting' + '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&nobapp=' + nobapp;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'lgl_slave_rekapsewahm.php';
	if (confirm('Anda yakin ingin unposting ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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