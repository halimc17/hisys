function getsukubunga(value,kodebank,funct){
	param = 'method=getsukubunga&kodebank='+kodebank+'&tanggal='+value;
	tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respog);		
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                } else {
                   eval(funct(con.responseText));
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getbunga(value,kodebank){
	var saldoakhir = document.getElementById('saldoakhir');
	valsaldo = saldoakhir.value;
	getsukubunga(value,kodebank,result);
	function result(data){
		var bunga = document.getElementById('bunga');
		var strbunga = document.getElementById('strbunga');
		var totalbunga = document.getElementById('totalbunga');
		var strtotalbunga = document.getElementById('strtotalbunga');
		valbunga = parseFloat(data);
		bunga.value 		= valbunga;
		strbunga.innerHTML 	= valbunga+"%";
		if(valsaldo !== ""){
			totalbunga.value = (valbunga*valsaldo)/100;
			strtotalbunga.innerHTML = (valbunga*valsaldo)/100;
		}else{
			totalbunga.value = "";
			strtotalbunga.innerHTML = "";
		}			
	}
}
function gettotalbunga(val,jumlahharibank){
	var totalbunga 	= document.getElementById('totalbunga');
	var strtotalbunga 	= document.getElementById('strtotalbunga');
	var bunga 		= document.getElementById('bunga');
	valbunga		= bunga.value;
	if(valbunga !== ""){
		valbunga = parseFloat(valbunga);
		totalbunga.value = (((val*valbunga)/100)/jumlahharibank);
		strtotalbunga.innerHTML = (((val*valbunga)/100)/jumlahharibank);
	}else{
		totalbunga.value = "";
		strtotalbunga.innerHTML = "";
	}
}
function popupdetail(notransaksi,periode,jatuhtempo){
	content= "<div id=formpopupdetail style=\"height:450px;width:100%;overflow:auto;\"></div>";
    title='Detail Data';
	ev='event';
    height='';
    width=600;
    showDialog1(title,content,width,height,ev);	
    datapopupdetail(notransaksi,periode,jatuhtempo);
}
function toSubmit(){
	var notransaksi = document.getElementById('notransaksi').value;
	var periode 	= document.getElementById('periode').value;
	var tanggal 	= document.getElementById('tanggal').value;
	var saldoakhir 	= document.getElementById('saldoakhir').value;
	var bunga 		= document.getElementById('bunga').value;
	var totalbunga 	= document.getElementById('totalbunga').value;
	param = 'method=savedatecalculate&notransaksi=' + notransaksi + '&periode=' + periode; 
	param += '&tanggal=' + tanggal + '&saldoakhir=' + saldoakhir; 
	param += '&bunga=' + bunga + '&totalbunga=' + totalbunga; 
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					var dataArr = JSON.parse(con.responseText);
					if(dataArr.err == "false"){
						getdata(notransaksi,periode);
					}else{
						alert(dataArr.mssg);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 		
}
function getdata(notransaksi,periode){
	if(typeof periode !== 'undefined' && document.getElementById('periode')){
		document.getElementById('periode').value = periode;
	}
	var strnotransaksi = "";
	if(typeof notransaksi !== 'undefined'){
		strnotransaksi = '&notransaksi=' + notransaksi;
	}
	var strperiode = "";
	if(typeof periode !== 'undefined'){
        tglAkhir=document.getElementById('tglAkhir').value;
		strperiode = '&periode=' + periode+'&tglAkhir='+tglAkhir;
	}
	param = 'method=getDataPinjaman'+strnotransaksi+strperiode;
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    document.getElementById('resulthead').innerHTML=con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardt(){
	document.getElementById('tanggal').value = "";
	document.getElementById('saldoakhir').value= "";
	document.getElementById('bunga').value= "";
	document.getElementById('totalbunga').value= "";
	document.getElementById('strbunga').innerHTML= "";
	document.getElementById('strtotalbunga').innerHTML= "";
}

function deletedata(notransaksi,periode,tanggal){
    param='method=deletedatacalculate'+'&notransaksi='+notransaksi + '&periode=' + periode; 
	param += '&tanggal=' + tanggal;
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   getdata(notransaksi,periode);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function datapopupdetail(notransaksi,periode,jatuhtempo){
	param = 'method=datapopupdetail&notransaksi=' + notransaksi + '&periode=' + periode + '&jatuhtempo=' + jatuhtempo; 
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formpopupdetail').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 		
}




























function saveall(maxRow){    
	maxf=maxRow;
	loopsave(1,maxRow);
}


function loopsave(currRow,maxRow){
	
	notransaksi=document.getElementById('notransaksi').value;
    periode=document.getElementById('periode').value;
	
    tanggal=trim(document.getElementById('tanggal'+currRow).innerHTML);
    saldoakhir=trim(document.getElementById('saldoakhir'+currRow).innerHTML);
    bunga=trim(document.getElementById('bunga'+currRow).innerHTML);
    totalbunga=trim(document.getElementById('totalbunga'+currRow).innerHTML);
	
	saldoakhir=remove_comma_var(saldoakhir);
	bunga=remove_comma_var(bunga);
	totalbunga=remove_comma_var(totalbunga);
	
	param='tanggal='+tanggal+'&saldoakhir='+saldoakhir+'&bunga='+bunga+'&totalbunga='+totalbunga;
	param+="&method=savedata"+'&notransaksi='+notransaksi+'&periode='+periode;
	tujuan = 'keu_slave_pmkalkulasi.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                        document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                } else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
						alert('Done');
						displaylist();
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}



function displaylist(){
    document.getElementById('notransaksisch').value='';
    document.getElementById('periodesch').value='';
    document.getElementById('ptsch').value='';
    document.getElementById('jenissch').value='';
    document.getElementById('noakunsch').value='';
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('header').style.display = 'none';
    loaddata(0);
}

function loaddata(page){
    notransaksisch=document.getElementById('notransaksisch').value;
    periodesch=document.getElementById('periodesch').value;
    ptsch=document.getElementById('ptsch').value;
	jenissch=document.getElementById('jenissch').value;
    noakunsch=document.getElementById('noakunsch').value;
	param = 'method=loaddata&page=' + page;
    if (notransaksisch != '') {
        param += '&notransaksisch=' + notransaksisch;
    }
    if (ptsch != '') {
        param += '&ptsch=' + ptsch;
    }
	if (jenissch != '') {
        param += '&jenissch=' + jenissch;
    }
    if (noakunsch != '') {
        param += '&noakunsch=' + noakunsch;
    }
	if (periodesch != '') {
        param += '&periodesch=' + periodesch;
    }
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
					isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);  
}

function getVariance(value,jml){
	var hasil = 0;
	console.log(value);
	var variance = document.getElementById('variance');
	var varianceshow = document.getElementById('varianceshow');
	if(value !== "" && parseFloat(value) !== NaN){
		hasil = jml-parseFloat(value);
	}else{
		hasil = 0;
	}
	variance.value=hasil.toFixed(2);
	varianceshow.innerHTML = hasil.toFixed(2);
}
function saveangsuran(){
	notransaksi=document.getElementById('notransaksi').value;
    pokokangsuran=document.getElementById('pokokangsuran').value;
    sukubungaangsuran=document.getElementById('sukubungaangsuran').value;
		tgl1angsuran=document.getElementById('tgl1angsuran').value;
		tgl2angsuran=document.getElementById('tgl2angsuran').value;
		harihutangangsuran=document.getElementById('harihutangangsuran').value;
    bungaangsuran=document.getElementById('bungaangsuran').value;
	totalbungaangsuran=document.getElementById('totalbungaangsuran').value;
    totalpembayaranangsuran=document.getElementById('totalpembayaranangsuran').value;
		tanggalpembayaranangsuran=document.getElementById('tanggalpembayaranangsuran').value;
	
	if(pokokangsuran=='' || sukubungaangsuran=='' || tgl1angsuran=='' || tgl2angsuran=='' || harihutangangsuran=='' || bungaangsuran=='' || totalbungaangsuran=='' || totalpembayaranangsuran=='' || tanggalpembayaranangsuran==''){
		alert('Lengkapi Pengisian');return;
	}
		
    param = 'method=saveangsuran' + '&notransaksi=' + notransaksi + '&pokokangsuran=' + pokokangsuran+ '&sukubungaangsuran=' + sukubungaangsuran;
	param +='&tgl1angsuran=' + tgl1angsuran + '&tgl2angsuran=' + tgl2angsuran + '&harihutangangsuran=' + harihutangangsuran;
	param +='&bungaangsuran=' + bungaangsuran + '&totalbungaangsuran=' + totalbungaangsuran + '&totalpembayaranangsuran=' + totalpembayaranangsuran;
	param +='&tanggalpembayaranangsuran=' + tanggalpembayaranangsuran;
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   loadangsuran(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
    //document.getElementById('nopdo').value='';
    //document.getElementById('per').value='';
	cancelhead();
}

function detaildata(){
	notransaksi=document.getElementById('notransaksi').value;
    pt=document.getElementById('pt').value;
    jenis=document.getElementById('jenis').value;
    noakun=document.getElementById('noakun').value;
    jumlahfasilitas=document.getElementById('jumlahfasilitas').value;
    jangkawaktu=document.getElementById('jangkawaktu').value;
    jatuhtempo=document.getElementById('jatuhtempo').value;
    periode=document.getElementById('periode').value;
	
	if(pt=='' || jenis=='' || noakun=='' || jangkawaktu=='' || periode==''){
		alert('Lengkapi pengisian');return;
	}
	param = 'method=detaildata' + '&notransaksi=' + notransaksi + '&pt=' + pt;
	param +='&jenis=' + jenis + '&noakun=' + noakun + '&jumlahfasilitas=' + jumlahfasilitas;
	param +='&jangkawaktu=' + jangkawaktu + '&jatuhtempo=' + jatuhtempo + '&periode=' + periode;
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   document.getElementById('detaildata').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function disablehead(){
	document.getElementById('notransaksi').disabled=true;
	document.getElementById('pt').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('noakun').disabled=true;
	document.getElementById('jumlahfasilitas').disabled=true;
	document.getElementById('jangkawaktu').disabled=true;
	document.getElementById('jatuhtempo').disabled=true;
	// document.getElementById('savehead').disabled=true;
	document.getElementById('periode').disabled=true;
}

function deleteht(notransaksi){
    param='method=deleteht'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_pmkalkulasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cancelhead(){
	/*document.getElementById('notransaksi').disabled=false;
	document.getElementById('pt').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('noakun').disabled=false;
	document.getElementById('jumlahfasilitas').disabled=false;
	document.getElementById('jangkawaktu').disabled=false;
	document.getElementById('jatuhtempo').disabled=false;
	document.getElementById('periode').disabled=false;
	
	document.getElementById('notransaksi').value='';
	document.getElementById('pt').value='';
	document.getElementById('jenis').value='';
	document.getElementById('noakun').value='';
	document.getElementById('jumlahfasilitas').value='';
	document.getElementById('jangkawaktu').value='';
	document.getElementById('jatuhtempo').value='';*/
}
