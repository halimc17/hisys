function previewBapb(notransaksi,ev)
{
        param='notransaksi='+notransaksi;
        tujuan = 'log_slave_print_bapb_supplier_pdf.php?'+param;	
 //display window
   title=notransaksi;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);

}

function delht(notransaksi){
	param='method=delht'+'&notransaksi='+notransaksi;
	tujuan = 'log_slave_catatterimapo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
					getlistdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function postht(notransaksi){
	param='method=postht'+'&notransaksi='+notransaksi;
	tujuan = 'log_slave_catatterimapo.php';
	
	if(confirm("Are you sure posting this item?")){
		post_response_text(tujuan, param, respog);	
    }
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
					getlistdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function editht(notransaksi,tanggal,idsupplier,namasupplier,nopo,gudang){
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('idsupplier').value=idsupplier;
	document.getElementById('namasupplier').value=namasupplier;
	document.getElementById('nopo').value=nopo;
	document.getElementById('gudang').value=gudang;
	// param = 'nopo=' + nopo+'&tipedata=edit&notransaksi='+notransaksi;
	// getPOContent(param);
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);//jangan tanya darimana
	getdatapo();
}


function selesaiBapb(){
	document.getElementById('gudang').value='';
	document.getElementById('nopo').value='';
	document.getElementById('idsupplier').value='';
	document.getElementById('namasupplier').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('container').innerHTML='';
	document.getElementById('containerlist').innerHTML='';
	getnotransaksi();
	getlistdata();
}




function getnotransaksi(){
	//param='gudang='+gudang;
	gudang=document.getElementById('gudang').value;
	param='method=getnotransaksi'+'&gudang='+gudang;
	tujuan = 'log_slave_catatterimapo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
					document.getElementById('notransaksi').value=con.responseText;
					//getlistdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getlistdata(){
	//param='gudang='+gudang;
	//gudang=document.getElementById('gudang').value;
	param='method=getlistdata';
	//param='method=getlistdata'+'&gudang='+gudang;
	tujuan = 'log_slave_catatterimapo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
					document.getElementById('containerlist').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function cariBapb(num){
	schnotransaksi=trim(document.getElementById('schnotransaksi').value);
	
		param='method=getlistdata';
		param+='&page='+num;
		if(schnotransaksi!='')
				param+='&schnotransaksi='+schnotransaksi;
		tujuan = 'log_slave_catatterimapo.php';
		post_response_text(tujuan, param, respog);			
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
						document.getElementById('containerlist').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function cariPO(title,ev){
	//kosongkan();
	content= "<div>";
	content+="<fieldset>Search : <input type=text id=textpo  class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariPo()>Go</button> ";
	content+="<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";
	//display window
	title=title+' PO :';
	width='';
	height='';
	showDialog1(title,content,width,height,ev);	
}

function goCariPo(){
	nopo=trim(document.getElementById('textpo').value);
	if(nopo.length<1){
		alert('Text too short');
	} else {   
		param='method=goCariPo'+'&nopo='+nopo;
		tujuan = 'log_slave_catatterimapo.php';
		post_response_text(tujuan, param, respog);			
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function goPickPo(nopo,idsupplier,namasupplier){
	document.getElementById('nopo').value=nopo;
	document.getElementById('idsupplier').value=idsupplier;
	document.getElementById('namasupplier').value=namasupplier;
	closeDialog();
	getdatapo();
}

function getdatapo(){
	//param='gudang='+gudang;
	nopo=trim(document.getElementById('nopo').value);	
	param='method=getdatapo'+'&nopo='+nopo;
	tujuan = 'log_slave_catatterimapo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function saveItemPo(kodebarang,sisa,nopp,satuan,no){
	//get all data
	gudang=document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
	notransaksi = trim(document.getElementById('notransaksi').value);
	idsupplier = document.getElementById('idsupplier').value;
	tanggal = document.getElementById('tanggal').value;
	nopo = document.getElementById('nopo').value;
	qty = document.getElementById('qty'+no).value;
	param='method=simpan'+'&notransaksi='+notransaksi;
	param += '&idsupplier=' + idsupplier + '&tanggal=' + tanggal+'&nopo=' + nopo;
	param += '&qty=' + qty+'&kodebarang='+kodebarang+'&gudang='+gudang+'&satuan='+satuan+'&nopp='+nopp;
	tujuan = 'log_slave_catatterimapo.php';
	if (notransaksi == '' || parseFloat(qty) < 0 || parseFloat(qty) == 'NaN') {
	  alert('Volume or document number is obligatory');
	} else {
		if(parseFloat(qty)>sisa){
			alert('Sorry, volume greater than volmun on PO');
		}else{
			document.getElementById('qty'+no).style.backgroundColor='orange';
			post_response_text(tujuan, param, respog);
		}
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('qty'+no).style.backgroundColor='red';
				} else {
					document.getElementById('qty'+no).style.backgroundColor='#E8F4F4';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}