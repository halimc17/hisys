function getform(){
	unit=trim(document.getElementById('unit').value);
	notransaksi=trim(document.getElementById('notransaksi').value);
	tipetransaksi=trim(document.getElementById('tipetransaksi').value);
	param='unit='+unit+'&notransaksi='+notransaksi+'&tipetransaksi='+tipetransaksi+'&method=getformdt';
    tujuan='keu_slave_transaksi_rutin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formdt').style.display='block';
					document.getElementById('formdt').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function getoption(kodevhc,noso) {
	
	unit= document.getElementById('unit').value;
	method = 'getoption';
	param='';
	param += '&unit=' + unit+'&kodevhc=' + kodevhc+'&noso=' + noso;
	param += '&method=' + method;
	tujuan = 'keu_slave_transaksi_rutin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('noso').innerHTML=ar[0];
					document.getElementById('kodevhc').innerHTML=ar[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function editht(notransaksi) {
	param = 'method=editht' + '&notransaksi=' + notransaksi;
	tujuan = 'keu_slave_transaksi_rutin.php';
	// alert(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					// document.getElementById('method').value = 'update';
					// alert(con.responseText.split);
					
					document.getElementById('formInput').style.display='block';
					document.getElementById('listData').style.display='none';
					
					ar = con.responseText.split("###");
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('unit').value = ar[1];
					
					document.getElementById('tipetransaksi').value = ar[2];
					document.getElementById('jenistransaksi').value = ar[3];
					document.getElementById('jenistipe').value = ar[4];
					document.getElementById('tipewaktu').value = ar[5];
					document.getElementById('nodokumen').value = ar[6];
					document.getElementById('noakun').value = ar[7];
					document.getElementById('tglmulai').value = ar[8];
					document.getElementById('totrup').value = ar[9];
					document.getElementById('rpperbulan').value = ar[10];
					document.getElementById('kredit').value = ar[11];
					document.getElementById('kodevhc').value = ar[12];
					document.getElementById('noso').value = ar[13];
					document.getElementById('pihakketiga').value = ar[14];
					document.getElementById('tglselesai').value = ar[15];
					document.getElementById('totbln').value = ar[16];
					document.getElementById('keterangan').value = ar[17];
					document.getElementById('debit').value = ar[18];
					document.getElementById('method').value='update';
					getoption(ar[12],ar[13]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}




function gettotbulan(){
    tglmulai=trim(document.getElementById('tglmulai').value);
	tglselesai=trim(document.getElementById('tglselesai').value);
	param='tglmulai='+tglmulai+'&tglselesai='+tglselesai+'&method=gettotbulan';
    tujuan='keu_slave_transaksi_rutin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('totbln').value=con.responseText;
                    getrpperbulan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getrpperbulan(){
	totrup=document.getElementById('totrup').value;
    totrup=totrup.replace(new RegExp(/,/i, "gm"),'');
	totbln=document.getElementById('totbln').value;

	if (totbln=='' || totbln==0 || totrup=='' || totrup==0){
		document.getElementById('rpperbulan').value=0;
	}else{
		rpperbulan=totrup/totbln;
		document.getElementById('rpperbulan').value=numberFormat(rpperbulan);
	}
}

function saveData(){
	jenistransaksi=trim(document.getElementById('jenistransaksi').value);
	unit=trim(document.getElementById('unit').value);
	notransaksi=trim(document.getElementById('notransaksi').value);
    tipetransaksi=trim(document.getElementById('tipetransaksi').value);
	jenistipe=trim(document.getElementById('jenistipe').value);
	noakun=trim(document.getElementById('noakun').value);
	pihakketiga=trim(document.getElementById('pihakketiga').value);
	totrup=trim(document.getElementById('totrup').value);
	totbln=trim(document.getElementById('totbln').value);
	rpperbulan=trim(document.getElementById('rpperbulan').value);
	keterangan=trim(document.getElementById('keterangan').value);
	kredit=trim(document.getElementById('kredit').value);
	debit=trim(document.getElementById('debit').value);
	tglmulai=trim(document.getElementById('tglmulai').value);
    tglselesai=trim(document.getElementById('tglselesai').value);
    kodevhc=trim(document.getElementById('kodevhc').value);
    noso=trim(document.getElementById('noso').value);
    nodokumen=trim(document.getElementById('nodokumen').value);
	tipewaktu=trim(document.getElementById('tipewaktu').value);
	method=trim(document.getElementById('method').value);
    if (unit=='' || tipetransaksi==''|| jenistipe=='' || tipewaktu=='' || pihakketiga=='' || tglmulai=='' || nodokumen==''
        || tglselesai=='' || totrup=='' || totbln=='' || rpperbulan=='' || keterangan=='' || kredit=='' || debit=='' || jenistransaksi=='') {
        alert('Lengkapi pengisian yang diberikan tanda petir');
        return;
    }
	param='unit='+unit+'&notransaksi='+notransaksi+'&tipetransaksi='+tipetransaksi+'&jenistransaksi='+jenistransaksi+'&method='+method;
	param+='&noakun='+noakun+'&pihakketiga='+pihakketiga+'&totrup='+totrup+'&totbln='+totbln;
    param+='&rpperbulan='+rpperbulan+'&keterangan='+keterangan+'&kredit='+kredit+'&debit='+debit;
	param+='&kodevhc='+kodevhc+'&noso='+noso+'&nodokumen='+nodokumen+'&tipewaktu='+tipewaktu;
	param+='&tglmulai='+tglmulai+'&tglselesai='+tglselesai+'&jenistipe='+jenistipe;
    tujua='keu_slave_transaksi_rutin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					clearData();
					displaylist();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearData(){
	document.getElementById('unit').value='';
    document.getElementById('unit').disabled=false;
	document.getElementById('notransaksi').value='';
    // document.getElementById('tipetransaksi').value='';
    // document.getElementById('tipetransaksi').disabled=false;
	// document.getElementById('jenistipe').value='';
    // document.getElementById('jenistipe').disabled=false;
	document.getElementById('noakun').value='';
	document.getElementById('pihakketiga').value='';
	document.getElementById('totrup').value=0;
	document.getElementById('totbln').value=0;
	document.getElementById('rpperbulan').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('kredit').value='';
	document.getElementById('debit').value='';
	document.getElementById('tglmulai').value='';
    document.getElementById('tglselesai').value='';
    document.getElementById('kodevhc').value='';
    document.getElementById('nodokumen').value='';
    document.getElementById('noso').value='';
	// document.getElementById('tipewaktu').value='';
	document.getElementById('method').value='insert';
	document.getElementById('formdt').style.display='none';
}

function displayFormInput(){
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
	clearData();
}




function getstop(notransaksi){
	param='method=getstop'+'&notransaksi='+notransaksi;
	tujuan = 'keu_slave_transaksi_rutin.php';
	post_response_text(tujuan, param, respon);
	function respon(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('75%','50%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function savestop()
{
    tanggalstop=document.getElementById('tanggalstop').value;
    notransaksi=document.getElementById('notransaksistop').value;
    param='method=savestop'+'&notransaksi='+notransaksi+'&tanggalstop='+tanggalstop;
    tujuan='keu_slave_transaksi_rutin.php';
	alertify.confirm("Informasi","Anda yakin stop jurnal perulangan untuk transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
					alertify.popup().destroy();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}



function displaylist(){
	document.getElementById('notranscr').value='';
    // document.getElementById('tipecr').value='';
	document.getElementById('listData').style.display='block';
	document.getElementById('formInput').style.display='none';
	document.getElementById('formdt').style.display='none';
	loadData(0);
}

function loadData(num){
    notranscr=document.getElementById('notranscr').value;
    keterangancr=document.getElementById('keterangancr').value;
    nodokumencr=document.getElementById('nodokumencr').value;
    // tipecr=document.getElementById('tipecr').value;
// alert('masuk');
    param='method=loadData';
    param+='&page='+num;

    if (notranscr != '') {
        param += '&notranscr=' + notranscr;
    }
	if (keterangancr != '') {
        param += '&keterangancr=' + keterangancr;
    }
	if (nodokumencr != '') {
        param += '&nodokumencr=' + nodokumencr;
    }
    // if (tipecr != '') {
        // param += '&tipecr=' + tipecr;
    // }
    tujuan='keu_slave_transaksi_rutin.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    //alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
					
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
                }
            }else{
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
    loadData(paged);  
}

function delht(notransaksi)
{
    param='method=delht'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_transaksi_rutin.php';
    // if(confirm(' Anda yakin ingin menghapus transaksi ini?'))
    // {
        // post_response_text(tujuan, param, respog);  
    // }
	
	alertify.confirm("Informasi","Anda yakin menghapus transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
                   loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}


function posting(notransaksi)
{
    // tglposting=document.getElementById('tglposting').value;
    param='method=posting'+'&notransaksi='+notransaksi;
    tujuan='keu_slave_transaksi_rutin.php';
    // if(confirm(' Anda yakin ingin memposting transaksi ini?'))
    // {
        // post_response_text(tujuan, param, respog);  
    // }
	
	alertify.confirm("Informasi","Anda yakin posting transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

/*
function posting(notransaksi)
{
    tglposting=document.getElementById('tglposting').value;
    param='method=posting'+'&notransaksi='+notransaksi+'&tglposting='+tglposting;
    tujuan='keu_slave_transaksi_rutin.php';
    if(confirm(' Anda yakin ingin memposting transaksi ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else 
                {
                    closeDialog2();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}
*/

/*
function editht(notransaksi,unit,tipetransaksi)
{
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('notransaksi').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('tipetransaksi').value=tipetransaksi;
    document.getElementById('tipetransaksi').disabled=true;
    document.getElementById('method').value='update';
    displayFormInput();
    // getform();
}
*/

function gettglposting(notransaksi){
    content= "<div id=formpost  style=\"height:10px;width:325px;\"></div>";
    title='Posting';
    height='20';
    width='329';
    showDialog2(title,content,width,height,'event');    
    getformposting(notransaksi);
} 

function getformposting(notransaksi){
     var param = "notransaksi="+notransaksi;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                   document.getElementById('formpost').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_transaksi_rutin.php?method=getformposting', param, respon);     
} 