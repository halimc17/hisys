function previewbapdf(notransaksi) {
    param = "method=previewbapdf&notransaksi="+notransaksi;
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_bakontrakjasa.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}


function bataldt(){
	document.getElementById('itemdt').selectedIndex=0;
	document.getElementById('kuantitasdt').value='';
	document.getElementById('keterangandt').value='';
	document.getElementById('methoddt').value='insert';
	document.getElementById('itemdt').disabled=false;
	document.getElementById('ptdt').disabled=true;
	document.getElementById('unitdt').disabled=true;
	document.getElementById('tanggaldt').disabled=true;
	gethargasatuan();
}


function transaksibaru(){
	document.getElementById('itemdt').selectedIndex=0;
	document.getElementById('kuantitasdt').value='';
	document.getElementById('keterangandt').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('methoddt').value='insert';
	document.getElementById('itemdt').disabled=false;
	document.getElementById('ptdt').disabled=true;
	document.getElementById('unitdt').disabled=true;
	document.getElementById('tanggaldt').disabled=false;
	document.getElementById('tanggaldt').value='';
	document.getElementById('listdt').innerHTML='';
	gethargasatuan();
}

function simpan(){
	notransaksi   =document.getElementById('notransaksi').value;
	pt            =document.getElementById('pt').value;
	unit          =document.getElementById('unit').value;
	tanggalkontrak=document.getElementById('tanggalkontrak').value;
	deskripsi     =document.getElementById('deskripsi').value;
	supplier      =document.getElementById('supplier').value;
	tgldari       =document.getElementById('tgldari').value;
	tglsampai     =document.getElementById('tglsampai').value;
	spesifikasi   =document.getElementById('spesifikasi').value;
	retensi       =document.getElementById('retensi').value;
	
	param='method=simpan&notransaksi='+notransaksi+'&pt='+pt+'&unit='+unit+'&tanggalkontrak='+tanggalkontrak+'&deskripsi='+deskripsi+'&supplier='+supplier+'&tgldari='+tgldari+'&tglsampai='+tglsampai+'&spesifikasi='+spesifikasi+'&retensi='+retensi;
    tujuan = 'log_slave_kontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					if(notransaksi==''){
						document.getElementById('listitem').style.display='';
					}
					
					document.getElementById('notransaksi').value=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loaddata(pg){
	sckontrak = document.getElementById('sckontrak').value;
	
	param='method=loaddata&page='+pg+'&sckontrak='+sckontrak;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listdata').style.display='block';
					document.getElementById('forminput').style.display='none';
					vsplt = con.responseText.split("####");
					document.getElementById('contain').innerHTML=vsplt[0];
					document.getElementById('containft').innerHTML=vsplt[1];
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}		
}

function gethargasatuan(){
	nokontrak = document.getElementById('nokontrak').value;
	itemdt = document.getElementById('itemdt').value;
	notransaksi = document.getElementById('notransaksi').value;
	
	param='method=gethargasatuan&itemdt='+itemdt+'&nokontrak='+nokontrak;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					vsplt = con.responseText.split("####");
					document.getElementById('hargasatuandt').value=vsplt[0];
					document.getElementById('satuandt').innerHTML=vsplt[1];
					document.getElementById('noakundt').value=vsplt[2];
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}		
}

function tambahrealisasi(nokontrak,notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;'></div></fieldset>";
	// title = "&nbsp;&nbsp;Preview";
	// showDialog4(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic4').style.top = (pos[1]-100) + 'px';
	
	param='method=tambahrealisasi&nokontrak='+nokontrak+'&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					// document.getElementById('popuppreview').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getunitdt(unitdt){
	ptdt=document.getElementById('ptdt').value;
	
	param='method=getunitdt&ptdt='+ptdt+'&unitdt='+unitdt;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('unitdt').innerHTML=con.responseText;
					getsubunit();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getsubunit(subunitdt){
	unitdt=document.getElementById('unitdt').value;
	
	param='method=getsubunit&unitdt='+unitdt+'&subunitdt='+subunitdt;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('subunitdt').innerHTML=con.responseText;
					getblok();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getblok(blokdt,kegiatandt=''){
	unitdt=document.getElementById('unitdt').value;
	subunitdt=document.getElementById('subunitdt').value;
	
	param='method=getblok&unitdt='+unitdt+'&subunitdt='+subunitdt+'&blokdt='+blokdt;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('blokdt').innerHTML=con.responseText;
					getkegiatan(kegiatandt);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function getkegiatan(kegiatandt){
	unitdt=document.getElementById('unitdt').value;
	subunitdt=document.getElementById('subunitdt').value;
	blokdt=document.getElementById('blokdt').value;
	
	param='method=getkegiatan&unitdt='+unitdt+'&subunitdt='+subunitdt+'&blokdt='+blokdt+'&kegiatandt='+kegiatandt;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('kegiatandt').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function ubah(nokontrak){
	param='method=ubah&nokontrak='+nokontrak;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	document.getElementById('listdata').style.display='none';
	document.getElementById('forminput').style.display='block';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('showdata').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function simpandt(){
	nokontrak    =document.getElementById('nokontrak').value;
	methoddt     =document.getElementById('methoddt').value;
	hargasatuandt=document.getElementById('hargasatuandt').value;
	
	notransaksi  =document.getElementById('notransaksi').value;
	ptdt         =document.getElementById('ptdt').value;
	unitdt       =document.getElementById('unitdt').value;
	tanggaldt    =document.getElementById('tanggaldt').value;
	noakundt     =document.getElementById('noakundt').value;
	itemdt       =document.getElementById('itemdt').value;
	satuandt     =document.getElementById('satuandt').innerHTML;
	kuantitasdt  =document.getElementById('kuantitasdt').value;
	keterangandt =document.getElementById('keterangandt').value;
	subunitdt    =document.getElementById('subunitdt').value;
	blokdt       =document.getElementById('blokdt').value;
	kegiatandt   =document.getElementById('kegiatandt').value;
	
	param='method=simpandt&methoddt='+methoddt+'&nokontrak='+nokontrak+'&ptdt='+ptdt+'&unitdt='+unitdt+'&hargasatuandt='+hargasatuandt+'&notransaksi='+notransaksi+'&tanggaldt='+tanggaldt+'&noakundt='+noakundt+'&itemdt='+itemdt+'&satuandt='+satuandt+'&kuantitasdt='+kuantitasdt+'&subunitdt='+subunitdt+'&blokdt='+blokdt+'&kegiatandt='+kegiatandt+'&keterangandt='+keterangandt;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	document.getElementById('listdata').style.display='none';
	document.getElementById('forminput').style.display='block';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('notransaksi').value=con.responseText;
					loaddt(con.responseText);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loadba(){
	nokontrak=document.getElementById('vnokontrak').value;
	param='method=loadba&nokontrak='+nokontrak;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listba').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loaddt(notransaksi){
	bataldt();
	
	param='method=loaddt&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('listdt').innerHTML=con.responseText;
					loadba();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function ubahdt(notransaksi,tanggaldt,itemdt,satuandt,kuantitasdt,keterangandt,hargasatuandt,noakundt,subunitdt,blokdt,kegiatandt){
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('tanggaldt').value = tanggaldt;
	document.getElementById('itemdt').value = itemdt;
	document.getElementById('kuantitasdt').value = kuantitasdt;
	document.getElementById('satuandt').innerHTML = satuandt;
	document.getElementById('keterangandt').value = keterangandt;
	document.getElementById('hargasatuandt').value = hargasatuandt;
	document.getElementById('noakundt').value = noakundt;
	document.getElementById('methoddt').value = "update";

	document.getElementById('itemdt').disabled = true;
	document.getElementById('subunitdt').value = subunitdt;
	getblok(blokdt,kegiatandt);
}

function hapusdt(notransaksi,itemdt){
	bataldt();
	
	param='method=hapusdt&notransaksi='+notransaksi+'&itemdt='+itemdt;
    tujuan = 'log_slave_bakontrakjasa.php';
	if(confirm('Anda yakin hapus no item '+itemdt+'?')){
		post_response_text(tujuan, param, respog);		
	}
    function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loaddt(notransaksi);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function hapus(notransaksi){
	param='method=hapus&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
	if(confirm('Anda yakin hapus no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function posting(notransaksi){
	param='method=posting&notransaksi='+notransaksi;
    tujuan = 'log_slave_kontrakjasa.php';
	if(confirm('Anda yakin posting no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);		
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function displayforminput(){
	document.getElementById('listdata').style.display='none';
	document.getElementById('forminput').style.display='block';
	batal();
}

function displaylist(){
	document.getElementById('listdata').style.display='block';
	document.getElementById('forminput').style.display='none';
	document.getElementById('sckontrak').value='';
	loaddata(0);
}

function getpage(){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function hapusba(notransaksi){
	param='method=hapusba&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
	if(confirm('Anda yakin hapus no transaksi '+notransaksi+'?')){
		post_response_text(tujuan, param, respog);
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadba();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function ajukanba(){
	notransaksi=document.getElementById('notransaksiappp').value;
	karyawanidapp=document.getElementById('karyawanidapp').value;
	
	param='method=ajukanba&notransaksi='+notransaksi+'&karyawanidapp='+karyawanidapp;
    tujuan = 'log_slave_bakontrakjasa.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadba();
					alertify.closeAll();
					closeDialog5();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function postingba(notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreviewx style='overflow:auto;width:auto;height:auto;'></div></fieldset>";
	// title = "&nbsp;&nbsp;Ajukan BA";
	// showDialog5(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic5').style.top = (pos[1]-50) + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0]-300) + 'px';
	
	param='method=postingba&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('popuppreviewx').innerHTML=con.responseText;
					alertify.popup2("Ajukan BA",con.responseText).set({'resizable':true,'maximizable':false}).resizeTo('500px','10px'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function previewba(notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreviewba style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	// title = "&nbsp;&nbsp;Preview";
	// showDialog5(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic5').style.top = (pos[1]-100) + 'px';
	// document.getElementById('dynamic5').style.left = (pos[0]-700) + 'px';
	
	param='method=previewba&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('popuppreviewba').innerHTML=con.responseText;
					alertify.popup2("Preview",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function preview(notransaksi,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><div id=popuppreview style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	// title = "&nbsp;&nbsp;Preview";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);	
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-800) + 'px';
	
	param='method=preview&notransaksi='+notransaksi;
    tujuan = 'log_slave_bakontrakjasa.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('popuppreview').innerHTML=con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}