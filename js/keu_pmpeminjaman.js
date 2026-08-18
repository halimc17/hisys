function getNamabank(value){
	param='method=namabank'+'&unit='+value;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   document.getElementById('namabank').innerHTML = con.responseText;
                   document.getElementById('noakun').innerHTML = "";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getNoakun(value){
	var unit = document.getElementById('unit');
	var unitVal = unit.value;
	param='method=noakun'+'&unit='+unitVal+'&kodebank='+value;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
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

function sukubunga(){
	tanggalpembayaranangsuran=document.getElementById('tanggalpembayaranangsuran').value;
	periode = tanggalpembayaranangsuran.split("-");
	if(periode.length > 0){
		document.getElementById('periodecalculate').value = periode[2]+"-"+periode[1]; //menyesuaikan secara otomatis
	}
	kodebank=document.getElementById('namabank').value;
    noloan=document.getElementById('noloanangsuran');
    noloan=noloan.options[noloan.selectedIndex].value;
	param='method=sukubunga'+'&tanggalpembayaranangsuran='+tanggalpembayaranangsuran+'&kodebank='+kodebank;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   document.getElementById('sukubungaangsuran').value = con.responseText;
                   if(noloan!=''){
                        getByrKe();
                   }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function change_angka(e){
	val = numberFormat(e.value);
	e.value = val;
}
function totalangsuran(){
    x=trim(document.getElementById('pokokangsuran').value);
    y=trim(document.getElementById('bungaangsuran').value);
	x=Number(remove_comma_var(x));
	y=Number(remove_comma_var(y));
	if(x == "" ){
		x = 0;
	}
	if(y == "" ){
		y = 0;
	}
	var z = 0;
	//console.log(x);
	try{
		z=x+y;
		document.getElementById('totalpembayaranangsuran').value = numberFormat(z);	
	}catch(e){		
		console.log(e);
	}
}
function cancelangsuran(){
	document.getElementById('bulankeangsuran').value='';
    document.getElementById('noloanangsuran').value='';
    document.getElementById('pokokangsuran').value='';
	document.getElementById('periodecalculate').value='';
	document.getElementById('sukubungaangsuran').value='';
	document.getElementById('harihutangangsuran').value='';
	document.getElementById('bungaangsuran').value='';
	document.getElementById('totalbungaangsuran').value='';
	document.getElementById('totalpembayaranangsuran').value='';
	document.getElementById('tanggalpembayaranangsuran').value='';
}
function cancelpencairan(){
	document.getElementById('tanggalpencairan').value='';
	document.getElementById('jumlahpencairan').value='';
	document.getElementById('noloanpencairan').value='';
}
function cloneht(notransaksi){
	param='method=getdata'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					var data = {};
					try{
						var data = JSON.parse(con.responseText);
					}catch(e){
						alert(e);
						return false;
					}
				  //=========================================================
					if(document.getElementById('notransaksi').disabled == true){
						document.getElementById('notransaksi').disabled = false;
					}
					if(document.getElementById('savehead').disabled == true){
						document.getElementById('savehead').disabled = false;
					}
					document.getElementById('notransaksi').value = '';
					document.getElementById('unit').value=data.kodeunit;
					document.getElementById('jenis').value=data.jenis;
					document.getElementById('namabank').innerHTML = "<option value='"+data.bankcode+"' selected>"+data.bankname+"</option>";
					document.getElementById('noakun').innerHTML = "<option value='"+data.rekeningcode+"' selected>"+data.norekening+"</option>";
					document.getElementById('jumlahfasilitas').value=numberFormat(data.jumlahfasilitas,2);
					date_data = data.jangkawaktu.split('-');
					document.getElementById('jangkawaktu').value= date_data[2]+"-"+date_data[1]+"-"+date_data[0];
					document.getElementById('jatuhtempo').value=data.jatuhtempo;
					document.getElementById('jenisfasilitas').value=data.jenisfasilitas;
					document.getElementById('tujuan').value=data.tujuan;
					document.getElementById('komitmenperiode').value=data.commitment_period;
					document.getElementById('availabilityperiode').value=data.availability_period;
					document.getElementById('graceperiode').value=data.grace_period;
					document.getElementById('biayakredit').value=data.biayakredit;
					document.getElementById('sukubunga').value=data.sukubunga;
					document.getElementById('pinalti').value=data.pinalti;
					document.getElementById('keterangan').value=data.keterangan;
					document.getElementById('header').style.display = 'block';
					document.getElementById('listdata').style.display = 'none';
					cancel_disabled();
				   //==================================================================
                }
            }else{
				busy_off();
				error_catch(con.status);
            }
        }
    }	
}
function editht(notransaksi){
	param='method=getdata'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					var data = {};
					try{
						var data = JSON.parse(con.responseText);
					}catch(e){
						alert(e);
						return false;
					}
				  //=========================================================
				    document.getElementById('notransaksi').value=data.notransaksi;
					document.getElementById('unit').value=data.kodeunit;
					document.getElementById('jenis').value=data.jenis;
					document.getElementById('namabank').innerHTML = "<option value='"+data.bankcode+"' selected>"+data.bankname+"</option>";
					document.getElementById('noakun').innerHTML = "<option value='"+data.rekeningcode+"' selected>"+data.norekening+"</option>";
					document.getElementById('jumlahfasilitas').value=numberFormat(data.jumlahfasilitas,2);
					document.getElementById('jangkawaktu').value=data.jangkawaktu;
					document.getElementById('jatuhtempo').value=data.jatuhtempo;
					document.getElementById('jenisfasilitas').value=data.jenisfasilitas;
					document.getElementById('tujuan').value=data.tujuan;
					document.getElementById('komitmenperiode').value=data.commitment_period;
					document.getElementById('availabilityperiode').value=data.availability_period;
					document.getElementById('graceperiode').value=data.grace_period;
					document.getElementById('biayakredit').value=data.biayakredit;
					document.getElementById('sukubunga').value=data.sukubunga;
					document.getElementById('pinalti').value=data.pinalti;
                    document.getElementById('keterangan').value=data.keterangan;
                    document.getElementById('jumlahbulan').value=data.jumlahbulan;
					document.getElementById('jenispinjaman').value=data.jenispinjaman;
					document.getElementById('notransaksi').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('jenis').disabled=true;
					document.getElementById('namabank').disabled=true;
					document.getElementById('noakun').disabled=true;
					document.getElementById('jumlahfasilitas').disabled=true;
					document.getElementById('jangkawaktu').disabled=true;
					document.getElementById('jatuhtempo').disabled=true;
					document.getElementById('tujuan').disabled=true;
					document.getElementById('jenisfasilitas').disabled=true;
					
					document.getElementById('komitmenperiode').disabled=true;
					document.getElementById('availabilityperiode').disabled=true;
					document.getElementById('graceperiode').disabled=true;
					document.getElementById('biayakredit').disabled=true;
					document.getElementById('sukubunga').disabled=true;
					document.getElementById('pinalti').disabled=true;
                    document.getElementById('keterangan').disabled=true;
                    document.getElementById('jumlahbulan').disabled=true;
					document.getElementById('jenispinjaman').disabled=true;
					
					//document.getElementById('savehead').disabled=true;
					document.getElementById('header').style.display = 'block';
					document.getElementById('detail').style.display = 'block';
					document.getElementById('listdata').style.display = 'none';
					loadpencairan(data.notransaksi);
					cancelangsuran();
					cancelpencairan();
				   //==================================================================
                }
            }else{
				busy_off();
				error_catch(con.status);
            }
        }
    }	
}
function displaylist(){
    document.getElementById('notransaksisch').value='';
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
    tujuan = 'keu_slave_pmpeminjaman.php';
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
    page=document.getElementById('pages');
    page=page.options[page.selectedIndex].value;
    halmn=page-1;
    loaddata(halmn);
}

function saveangsuran(){
	notransaksi=document.getElementById('notransaksi').value;
    jenis=document.getElementById('jenis').value;
    noloanangsuran=document.getElementById('noloanangsuran').value;
	bulankeangsuran=document.getElementById('bulankeangsuran').value;
    pokokangsuran=remove_comma_var(document.getElementById('pokokangsuran').value);
    sukubungaangsuran=document.getElementById('sukubungaangsuran').value;
	harihutangangsuran=document.getElementById('harihutangangsuran').value;
    bungaangsuran=remove_comma_var(document.getElementById('bungaangsuran').value);
	totalbungaangsuran=document.getElementById('totalbungaangsuran').value;
    totalpembayaranangsuran=remove_comma_var(document.getElementById('totalpembayaranangsuran').value);
	tanggalpembayaranangsuran=document.getElementById('tanggalpembayaranangsuran').value;
	periodecalculate=document.getElementById('periodecalculate').value;
	jenisfasilitas= document.getElementById('jenisfasilitas').value;
	tujuanfasilitas=document.getElementById('tujuan').value;
	
	if(pokokangsuran=='' || sukubungaangsuran=='' || bungaangsuran=='' || totalpembayaranangsuran=='' || tanggalpembayaranangsuran=='' || periodecalculate==''){
		alert('Lengkapi Pengisian');return;
	}
		
    param = 'method=saveangsuran' + '&notransaksi=' + notransaksi + '&pokokangsuran=' + pokokangsuran+ '&sukubungaangsuran=' + sukubungaangsuran;
	param +='&harihutangangsuran=' + harihutangangsuran;
	param +='&jenisfasilitas=' + jenisfasilitas;
	param +='&tujuanfasilitas=' + tujuanfasilitas;
	param +='&bungaangsuran=' + bungaangsuran + '&totalbungaangsuran=' + totalbungaangsuran + '&totalpembayaranangsuran=' + totalpembayaranangsuran+ '&periodecalculate=' + periodecalculate;
    param +='&tanggalpembayaranangsuran=' + tanggalpembayaranangsuran;
    param +='&noloanangsuran=' + noloanangsuran;
	param +='&bulanke=' + bulankeangsuran;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   loadangsuran(notransaksi);
				   cancelangsuran();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loadangsuran(notransaksi){
    param = 'method=loadangsuran' + '&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
                    document.getElementById('noloanangsuran').innerHTML=data[1];
                    document.getElementById('bulankeangsuran').innerHTML=data[2];
                    document.getElementById('listangsuran').innerHTML=data[0];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deleteangsuran(notransaksi,noloan,bulanke){
    param='method=deleteangsuran'+'&notransaksi='+notransaksi+'&noloanangsuran='+noloan+'&bulanke='+bulanke;
    tujuan = 'keu_slave_pmpeminjaman.php';
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

function savepencairan(){
	notransaksi=document.getElementById('notransaksi').value;
	jenis=document.getElementById('jenis').value;
    tanggalpencairan=document.getElementById('tanggalpencairan').value;
    jumlahpencairan=remove_comma_var(document.getElementById('jumlahpencairan').value);
    noloanpencairan=document.getElementById('noloanpencairan').value;
    jatuhtempo=document.getElementById('jatuhtempoCair');
    jatuhtempo=jatuhtempo.options[jatuhtempo.selectedIndex].value;
	if(jenis == 'KRK'){
		if(tanggalpencairan=='' || jumlahpencairan==''){
			alert('Lengkapi Pengisian');return;
		}
	}else{
		if(tanggalpencairan=='' || jumlahpencairan=='' || noloanpencairan==''){
			alert('Lengkapi Pengisian');return;
		}
	}
	
    param = 'method=savepencairan' + '&notransaksi=' + notransaksi + '&tanggalpencairan=' + tanggalpencairan;
	param +='&jumlahpencairan=' + jumlahpencairan + '&noloanpencairan=' + noloanpencairan+'&jatuhtempoCair='+jatuhtempo;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   loadpencairan(notransaksi);
				   cancelpencairan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadpencairan(notransaksi){
    param = 'method=loadpencairan' + '&notransaksi=' + notransaksi;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   document.getElementById('listpencairan').innerHTML=con.responseText;
				   loadangsuran(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletepencairan(notransaksi,tanggalpencairan){
    param='method=deletepencairan'+'&notransaksi='+notransaksi+'&tanggalpencairan='+tanggalpencairan;
    tujuan = 'keu_slave_pmpeminjaman.php';
    if(confirm(bahasa.notifandayakin+" "+notransaksi)){
        post_response_text(tujuan, param, respon);    
    }
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                   loadpencairan(notransaksi);
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
	if(document.getElementById('savehead').disabled == true){
		document.getElementById('savehead').disabled = false;
	}
    document.getElementById('listpencairan').innerHTML='';
    document.getElementById('listangsuran').innerHTML='';
	cancelhead();
}

function savehead(notransaksi,unit){
    notransaksi=document.getElementById('notransaksi').value;
    unit=document.getElementById('unit').value;
    jenis=document.getElementById('jenis').value;
    noakun=document.getElementById('noakun').value;
    jenisfasilitas=document.getElementById('jenisfasilitas').value;
    tujuantrans=document.getElementById('tujuan').value;
    jumlahfasilitas=document.getElementById('jumlahfasilitas').value;
    jangkawaktu=document.getElementById('jangkawaktu').value;
    jatuhtempo=document.getElementById('jatuhtempo').value;
    komitmenperiode=document.getElementById('komitmenperiode').value;
    availabilityperiode=document.getElementById('availabilityperiode').value;
    graceperiode=document.getElementById('graceperiode').value;
    biayakredit=document.getElementById('biayakredit').value;
    sukubunga=document.getElementById('sukubunga').value;
    pinalti=document.getElementById('pinalti').value;
    keterangan=document.getElementById('keterangan').value;
    jumlahbulan=document.getElementById('jumlahbulan').value;
    jenispinjaman=document.getElementById('jenispinjaman').value;
    tpPokok=document.getElementById('tpPokok');
    tpPokok=tpPokok.options[tpPokok.selectedIndex].value;
	
	if(notransaksi=='' || unit=='' || jenis=='' || noakun=='' || jangkawaktu==''){
		alert('Lengkapi pengisian');return;
	}
	
    param = 'method=savehead' + '&notransaksi=' + notransaksi + '&unit=' + unit;
	param +='&jenis=' + jenis + '&noakun=' + noakun + '&jumlahfasilitas=' + remove_comma_var(jumlahfasilitas);
	param +='&jangkawaktu=' + jangkawaktu + '&jatuhtempo=' + jatuhtempo;
	param +='&komitmenperiode=' + komitmenperiode;
	param +='&jenisfasilitas=' + jenisfasilitas;
	param +='&tujuan=' + tujuantrans+'&tpPokok='+tpPokok;
	param +='&availabilityperiode=' + availabilityperiode;
	param +='&graceperiode=' + graceperiode;
	param +='&biayakredit=' + biayakredit;
	param +='&sukubunga=' + sukubunga;
	param +='&pinalti=' + pinalti;
    param +='&keterangan=' + keterangan;
    param +='&jumlahbulan=' + jumlahbulan;
	param +='&jenispinjaman=' + jenispinjaman;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('notransaksi').value=con.responseText;
                    document.getElementById('detail').style.display='block';
					disablehead();
					cancelangsuran();
					cancelpencairan();
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
	document.getElementById('unit').disabled=true;
	document.getElementById('jenis').disabled=true;
	document.getElementById('noakun').disabled=true;
	document.getElementById('jumlahfasilitas').disabled=true;
	document.getElementById('jangkawaktu').disabled=true;
	document.getElementById('jatuhtempo').disabled=true;
	document.getElementById('jenisfasilitas').disabled=true;
	document.getElementById('tujuan').disabled=true;
	document.getElementById('namabank').disabled=true;
	document.getElementById('komitmenperiode').disabled=true;
    document.getElementById('availabilityperiode').disabled=true;
    document.getElementById('graceperiode').disabled=true;
    document.getElementById('biayakredit').disabled=true;
    document.getElementById('sukubunga').disabled=true;
    document.getElementById('pinalti').disabled=true;
    document.getElementById('keterangan').disabled=true;
    document.getElementById('jumlahbulan').disabled=true;
    document.getElementById('jenispinjaman').disabled=true;
	
	document.getElementById('savehead').disabled=true;
}

function deleteht(notransaksi){
    param='method=deleteht'+'&notransaksi='+notransaksi;
    tujuan = 'keu_slave_pmpeminjaman.php';
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

function cancel_disabled(){
	document.getElementById('notransaksi').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('noakun').disabled=false;
	document.getElementById('jumlahfasilitas').disabled=false;
	document.getElementById('jangkawaktu').disabled=false;
	document.getElementById('jatuhtempo').disabled=false;
	document.getElementById('jenisfasilitas').disabled=false;
	document.getElementById('tujuan').disabled=false;
	
	document.getElementById('namabank').disabled=false;
	document.getElementById('komitmenperiode').disabled=false;
    document.getElementById('availabilityperiode').disabled=false;
    document.getElementById('graceperiode').disabled=false;
    document.getElementById('biayakredit').disabled=false;
    document.getElementById('sukubunga').disabled=false;
    document.getElementById('pinalti').disabled=false;
    document.getElementById('keterangan').disabled=false;
    document.getElementById('jumlahbulan').disabled=false;
    document.getElementById('jenispinjaman').disabled=false;
		
}
function cancel_clear(){
	document.getElementById('notransaksi').value='';
	document.getElementById('unit').value='';
	document.getElementById('jenis').value='';
	document.getElementById('noakun').innerHTML='';
	document.getElementById('jumlahfasilitas').value='';
	document.getElementById('jangkawaktu').value='';
	document.getElementById('jatuhtempo').value='';
	document.getElementById('jenisfasilitas').value='';
	document.getElementById('tujuan').value='';
	
	document.getElementById('namabank').innerHTML='';
	document.getElementById('komitmenperiode').value='';
    document.getElementById('availabilityperiode').value='';
    document.getElementById('graceperiode').value='';
    document.getElementById('biayakredit').value='';
    document.getElementById('sukubunga').value='';
    document.getElementById('pinalti').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('jumlahbulan').value=0;
    document.getElementById('jenispinjaman').value='';
}
function cancelhead(){
	cancel_disabled();
	cancel_clear();
}


function loadsukubunga(){
    notransaksi=document.getElementById('notransaksi').value;
    bank=document.getElementById('namabank');
    bank=bank.options[bank.selectedIndex].value;
    param='kodebank='+bank+'&kirim=daripinjaman'+'&notransaksi='+notransaksi;
	tujuan='keu_slave_pmsukubunga.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					title 	= "Suku Bunga";
					width	= "500px";
					height	= "400px";
                    ev = 'event';
					content = con.responseText;
                    showDialog1(title,content,width,height,ev);
					bungaloadData(0);
				}
  			}else{
  				busy_off();
				error_catch(con.status);
  			}
  		}	
  	}
}



// function form()
// {
//     width = '';
//     height = '';
//     content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
//     ev = 'event';
//     title = "Detail HTML";
//     showDialog1(title, content, width, height, ev); 
// }

// function adddetail(notransaksi){
//     form();
//     // param = 'method=viewdetail'+'&notransaksi='+notransaksi;
//     // tujuan = 'keu_slave_pmpeminjaman.php';
//     // post_response_text(tujuan, param, respog);
//     // function respog()
//     // {
//     //     if (con.readyState == 4)
//     //     {
//     //         if (con.status == 200)
//     //         {
//     //             busy_off();
//     //             if (!isSaveResponse(con.responseText))
//     //             {
//     //                 alert(con.responseText);
//     //             }
//     //             else
//     //             {
//     //                 document.getElementById('containerd').innerHTML = con.responseText;
//     //             }
//     //         }
//     //         else
//     //         {
//     //             busy_off();
//     //             error_catch(con.status);
//     //         }
//     //     }
//     // }
// }

function savedetail(totrow,notransaksi,noloan){
    noloan=document.getElementById('nopencairan').value;
    param='method=insertdt'+'&notransaksi='+notransaksi;
    param+='&noloan='+noloan;
    var kirimArr;
    for(ditung=1;ditung<=totrow;ditung++){
        kirimArr+="&arrBulan[]="+document.getElementById('bulanke_'+ditung).value;
        kirimArr+="&arrAngsuran[]="+document.getElementById('rupiahangsuran_'+ditung).value;
    }
    param+=kirimArr;
    tujuan='keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    data="viewdetail";
                    nodialog=3;
                    adddetail(data,nodialog,notransaksi,noloan)
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function updatedetail(notransaksi,urutan){
    document.getElementById('bulanke_'+urutan).disabled=false;
    document.getElementById('rupiahangsuran_'+urutan).disabled=false;
}

function getByrKe(){
    noloan=document.getElementById('noloanangsuran');
    noloan=noloan.options[noloan.selectedIndex].value;
    unit=document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;
    namabank=document.getElementById('namabank');
    namabank=namabank.options[namabank.selectedIndex].value;
    periodecalculate=document.getElementById('periodecalculate').value;
    jatuhtempo=document.getElementById('jatuhtempo');
    jatuhtempo=jatuhtempo.options[jatuhtempo.selectedIndex].value;
    tglbyr=document.getElementById('tanggalpembayaranangsuran').value;
    notrans=document.getElementById('notransaksi').value;
    param='method=getByrKe'+'&noloan='+noloan+'&notransaksi='+notrans+'&periodecalculate='+periodecalculate;
    param+='&jatuhtempo='+jatuhtempo+'&unit='+unit+'&tanggalpembayaranangsuran='+tglbyr+'&kodebank='+namabank;
    tujuan='keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    bulanke=con.responseText.split("####");
                    tenor=document.getElementById('bulankeangsuran');
                    for(a=0;a<tenor.length;a++){
                            if(tenor.options[a].value==bulanke[0]){
                                    tenor.options[a].selected=true;
                            }
                    }
                    tenor.disabled=true;
                    document.getElementById('harihutangangsuran').value=bulanke[1];
                    document.getElementById('pokokangsuran').value=bulanke[2];
                    document.getElementById('bungaangsuran').value=bulanke[3];
                    document.getElementById('totalpembayaranangsuran').value=bulanke[4];
                    document.getElementById('sukubungaangsuran').value=bulanke[5];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadAngsuran2(page){
    noloan=document.getElementById('noLoanCr');
    noloan=noloan.options[noloan.selectedIndex].value;

    blnCr=document.getElementById('blnCr');
    blnCr=blnCr.options[blnCr.selectedIndex].value;
    notransaksi=document.getElementById('notransaksi');
    param='method=loadAngsuran2'+'&noLoanCr='+noloan+'&blnCr='+blnCr+'&page='+parseInt(page)+'&notransaksi='+notransaksi.value;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    isdt = con.responseText.split("####");
                    document.getElementById('detailDataAngsuran').innerHTML = isdt[0];
                    document.getElementById('footerAngsuran').innerHTML = isdt[1];
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getPage2(){
    page=document.getElementById('pagesAngsran');
    page=page.options[page.selectedIndex].value;
    halmn=page-1;
    loadAngsuran2(halmn);
}
function resetAngsuran2(page){
    document.getElementById('blnCr').value='';
    document.getElementById('noLoanCr').value='';
    loadAngsuran2(page);
}
function getBungaIsi(){
    noloan=document.getElementById('noLoanCr');
    noloan=noloan.options[noloan.selectedIndex].value;
    jatuhtempo=document.getElementById('jatuhtempo');
    jatuhtempo=jatuhtempo.options[jatuhtempo.selectedIndex].value;
    tgl=document.getElementById('periodecalculate').value;
    skBnga=document.getElementById('sukubungaangsuran').value;
    notransaksi=document.getElementById('notransaksi');
    param='method=getBungaIsi'+'&noLoanCr='+noloan+'&periodecalculate='+tgl+'&notransaksi='+notransaksi.value;
    param+='&skBnga='+skBnga+'&jatuhtempo='+jatuhtempo;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    document.getElementById('bungaangsuran').value=con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function adddetail(data,nodialog,notransaksi,noloan){
    form(data,nodialog);
    param = 'method='+data;
    param+='&notransaksi='+notransaksi;
    param+='&noloan='+noloan;
    tujuan = 'keu_slave_pmpeminjaman.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                    document.getElementById(data).innerHTML = con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function form(data,nodialog){
    width = '';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><legend>Data</legend><div id="+data+" align=left style=\"width:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    if(nodialog==1){
        showDialog1(title, content, width, height, ev); 
    }
    else if (nodialog==2){
        showDialog2(title, content, width, height, ev); 
    }
    else if (nodialog==3){
        showDialog2(title, content, width, height, ev); 
    }
    
}