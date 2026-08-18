var fileTarget;
fileTarget='log_slave_5hargaterakhir.php';

function getunit(unit){
    pt=document.getElementById('pt').value;
    param='pt='+pt+'&unit='+unit;

    function respon(){
        if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
                }else{
					  document.getElementById('unit').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text(fileTarget+'?method=getunit', param, respon);
}

function getunitsrc(){
	ptscr=document.getElementById('ptscr');
	ptscr=ptscr.options[ptscr.selectedIndex].value;
    param='ptscr='+ptscr;
   
    function respon(){
        if(con.readyState == 4){
			if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('unitsrc').innerHTML=con.responseText;
					loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text(fileTarget+'?method=getunitsrc', param, respon);
}

function simpan(){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    kodebrg=document.getElementById('kodebrg').value;
    tgl=document.getElementById('tgl').value;
    harga=document.getElementById('harga').value;
    hargaestimasi=document.getElementById('hargaestimasi').value;
    nopo=document.getElementById('nopo').innerHTML;
    method=document.getElementById('method').value;

    param='pt='+pt+'&unit='+unit+'&kodebrg='+kodebrg+'&tgl='+tgl+'&harga='+harga+'&method='+method+'&hargaestimasi='+hargaestimasi+'&nopo='+nopo;
    post_response_text(fileTarget, param, respog);		
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
                    loadData(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function batalcari(){
    document.getElementById('ptscr').selectedIndex=0;
    document.getElementById('kodebrgsrc').value='';
    document.getElementById('tglsrc').value='';
	getunitsrc();
}

function cancel(){
	document.getElementById('pt').value='';
	document.getElementById('pt').disabled=false;
    document.getElementById('unit').value='';
	document.getElementById('unit').disabled=false;
	document.getElementById('trunit').style.display='none';
    document.getElementById('kodebrg').value='';
	document.getElementById('imgkodebrg').style.display='';
    document.getElementById('namabrg').innerHTML='';
    document.getElementById('tgl').value='';
    document.getElementById('harga').value='';
    document.getElementById('method').value='insert';
    document.getElementById('myid').value='';
	document.getElementById('trnopo').style.display='none';
	document.getElementById('nopo').innerHTML='';
	document.getElementById('hargaestimasi').value='';
    
}

function loadData(num){
	ptscr=document.getElementById('ptscr').value;
    unitsrc=document.getElementById('unitsrc').value;
    kodebrgsrc=document.getElementById('kodebrgsrc').value;
    tglsrc=document.getElementById('tglsrc').value;
	
	param='method=loadData&page='+num;
	param+='&ptscr='+ptscr+'&unitsrc='+unitsrc;
	param+='&kodebrgsrc='+kodebrgsrc+'&tglsrc='+tglsrc;
	post_response_text(fileTarget, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					cancel();
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

function edit(pt,unit,kodebrg,tgl,harga,namabrg,hargaestimasi,nopo){
	showontop();
    document.getElementById('pt').value=pt;
    document.getElementById('pt').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('kodebrg').value=kodebrg;
    document.getElementById('imgkodebrg').style.display='none';
    document.getElementById('namabrg').innerHTML=namabrg;
    document.getElementById('tgl').value=tgl;
    document.getElementById('harga').value=harga;
    document.getElementById('hargaestimasi').value=hargaestimasi;
	if(nopo!=''){
		document.getElementById('trnopo').style.display='';
		document.getElementById('nopo').innerHTML=nopo;
	}else{
		document.getElementById('trnopo').style.display='none';
		document.getElementById('nopo').innerHTML='';
	}
    document.getElementById('method').value='update';
	setBrg(kodebrg, namabrg, unit);
}

function searchBrg(title, content, ev) {
    width = 'auto';
    height = 'auto';
    showDialog1(title, content, width, height, ev);
}

function findBrg() {
    txt = trim(document.getElementById('no_brg').value);
    if (txt == '') {
        alert('Text is obligatory');
    } else if (txt.length < 1) {
        alert('Too short words');
    } else {
        param = 'txtfind=' + txt  + '&method=cariBarangDlmDtBs';
        tujuan = 'log_slave_5hargaterakhir.php';
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container1').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setBrg(no_brg, namabrg,unit) {
    document.getElementById('kodebrg').value = no_brg;
    document.getElementById('namabrg').innerHTML = namabrg;
	
	param = 'kodebrg='+no_brg+'&method=setBrg&unit='+unit;
	tujuan = 'log_slave_5hargaterakhir.php';
	post_response_text(tujuan, param, respog);
    
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					data=con.responseText.split("##");
					if(data[0]==1){
						document.getElementById('trunit').style.display='';
						document.getElementById('unit').innerHTML=data[1];
					}else{
						document.getElementById('trunit').style.display='none';
					}
                    closeDialog();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function addfrompo(){
	kodebrg = document.getElementById('kodebrg').value;
	if(kodebrg==''){
		alertify.alert("Kode barang harus diisi");
		return false;
	}
	
	param = 'method=addfrompo'+'&kodebrg='+kodebrg;
	tujuan = 'log_slave_5hargaterakhir.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Add from PO",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setpo(nopo,hargasatuan,tanggal){
	document.getElementById('trnopo').style.display='';
	document.getElementById('nopo').innerHTML=nopo;
	document.getElementById('harga').value=hargasatuan;
	document.getElementById('tgl').value=tanggal;
	alertify.popup2().close();
}

function showdetail(pt,unit,kodebrg,ev) {
	title = "History Harga Pembelian";
	width = '';
	height = '';
	content = "<fieldset><legend>History</legend><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0] - 250) + 'px';
	// document.getElementById('dynamic1').style.display = '';
	
	param = 'pt='+pt+'&unit='+unit+'&kodebrg='+kodebrg+'&method=showdetail';
	post_response_text(fileTarget, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contDetail').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}