function batal(){
	document.getElementById('pt').selectedIndex=0;
	document.getElementById('pt').disabled = false;
	
	document.getElementById('kelompokbarang').selectedIndex=0;
	document.getElementById('kelompokbarang').disabled = false;
	
	document.getElementById('gudang').disabled = false;
	document.getElementById('barang').disabled = false;
	
	document.getElementById('minstok').value='';
	document.getElementById('maxstok').value='';
	document.getElementById('satuan').value='';
	
	document.getElementById('method').value="insert";
	document.getElementById('myid').value="";
	getgudang('','');
}

function batalcari(){
	document.getElementById('crpt').selectedIndex=0;
	document.getElementById('crklbarang').selectedIndex=0;
	
	getcrgudang();
}

function loaddata(num)
{
	crpt=document.getElementById('crpt').options[document.getElementById('crpt').selectedIndex].value;
	crgudang=document.getElementById('crgudang').options[document.getElementById('crgudang').selectedIndex].value;
	crklbarang=document.getElementById('crklbarang').options[document.getElementById('crklbarang').selectedIndex].value;
	crbarang=document.getElementById('crbarang').options[document.getElementById('crbarang').selectedIndex].value;
	
	param='method=loaddata&crpt='+crpt+'&crgudang='+crgudang+'&crklbarang='+crklbarang+'&crbarang='+crbarang+'&page='+num;
    tujuan='log_slave_5minstock.php';
    post_response_text(tujuan, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					batal();
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
    loaddata(paged);	
}

function editfield(myid,pt,gudang,kelompokbarang,barang,satuan,stok,maxstok){
	document.getElementById('myid').value=myid;
	document.getElementById('pt').value=pt;
	document.getElementById('pt').disabled = true;
	
	document.getElementById('kelompokbarang').value=kelompokbarang;
	document.getElementById('kelompokbarang').disabled = true;
	
	document.getElementById('gudang').disabled = true;
	document.getElementById('barang').disabled = true;
	
	document.getElementById('minstok').value=stok;
	document.getElementById('maxstok').value=maxstok;
	document.getElementById('satuan').value=satuan;
	
	getgudang(gudang,barang)
	
	document.getElementById('method').value="update";
	showontop();
}

function simpan(){
	myid=document.getElementById('myid').value;
	method=document.getElementById('method').value;
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
	kelompokbarang=document.getElementById('kelompokbarang').options[document.getElementById('kelompokbarang').selectedIndex].value;
	barang=document.getElementById('barang').options[document.getElementById('barang').selectedIndex].value;
	minstok=document.getElementById('minstok').value;
	maxstok=document.getElementById('maxstok').value;
	satuan=document.getElementById('satuan').value;

	param='myid='+myid+'&method='+method+'&pt='+pt+'&gudang='+gudang+'&kelompokbarang='+kelompokbarang+'&barang='+barang+'&minstok='+minstok+'&satuan='+satuan+'&maxstok='+maxstok;
    tujuan='log_slave_5minstock.php';
	
	if(pt==''||gudang==''||kelompokbarang==''||barang==''){
		alert('Warning : Lengkapi Pengisian.');
		return;
	}
	
	if(confirm('Anda yakin simpan data ini?')){
		post_response_text(tujuan, param, respog);			
	}	
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Success");
					loaddata(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getgudang(gudang,barang){
	pt=document.getElementById('pt').value;
	
	param='method=getgudang&pt='+pt+'&gudang='+gudang;
    tujuan='log_slave_5minstock.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('gudang').innerHTML=con.responseText;
					getbarang(barang);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getcrgudang(){
	crpt=document.getElementById('crpt').value;
	
	param='method=getcrgudang&crpt='+crpt;
    tujuan='log_slave_5minstock.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('crgudang').innerHTML=con.responseText;
					getcrbarang();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getbarang(barang){
	kelompokbarang=document.getElementById('kelompokbarang').value;
	
	param='method=getbarang&kelompokbarang='+kelompokbarang+'&barang='+barang;
    tujuan='log_slave_5minstock.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('barang').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getcrbarang(){
	crklbarang=document.getElementById('crklbarang').value;
	
	param='method=getcrbarang&crklbarang='+crklbarang;
    tujuan='log_slave_5minstock.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('crbarang').innerHTML=con.responseText;
					loaddata(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getsatuan(){
	barang=document.getElementById('barang').value;
	
	param='method=getsatuan&barang='+barang;
    tujuan='log_slave_5minstock.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('satuan').value=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}