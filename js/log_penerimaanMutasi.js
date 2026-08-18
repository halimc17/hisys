/**
 * @author repindra.ginting
 */
function setSloc(x){
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    tglstart=document.getElementById(gudang+'_start').value;
	tglend=document.getElementById(gudang+'_end').value;
	tglstart=tglstart.substr(6,2)+"-"+tglstart.substr(4,2)+"-"+tglstart.substr(0,4);
	tglend=tglend.substr(6,2)+"-"+tglend.substr(4,2)+"-"+tglend.substr(0,4);
	document.getElementById('displayperiod').innerHTML=tglstart+" - "+tglend;
	
	if (gudang != '') {
		if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			tujuan = 'log_slave_getBapbNumber.php';
			param = 'gudang=' + gudang;
			post_response_text(tujuan, param, respog);
		}
		else {
			document.getElementById('nodok').value='';
			document.getElementById('sloc').disabled = false;
			document.getElementById('sloc').options[0].selected=true;
			document.getElementById('btnsloc').disabled = false;
		}	
		
	}	
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						//alert(con.responseText);
						vsplit = con.responseText.split("####");
						document.getElementById('nodok').value = vsplit[0];
					    getMutasiList(gudang,tglstart);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
}

function getMutasiList(gudang,tglstart)
{
		param='gudang='+gudang+'&tanggal='+tglstart;
		tujuan = 'log_slave_getMutasiReceiveAbleList.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('containerlist').innerHTML=con.responseText;
					    cariBapbReceived(0);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}
function cariBast(num)
{
	tex=trim(document.getElementById('txtbabp').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if(gudang =='')
	{
		alert('Storage Location  is obligatory')
	}
	else
	{
		tglstart=document.getElementById(gudang+'_start').value;
		tglstart=tglstart.substr(6,2)+"-"+tglstart.substr(4,2)+"-"+tglstart.substr(0,4);
		param='gudang='+gudang+'&tanggal='+tglstart;
		param+='&page='+num;
		if(tex!='')
			param+='&tex='+tex;
		tujuan = 'log_slave_getMutasiReceiveAbleList.php';
		post_response_text(tujuan, param, respog);			
	}
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('containerlist').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}


function previewMutasi(notransaksi,ev)
{
   	param='notransaksi='+notransaksi;
	tujuan = 'log_slave_print_mutasi_pdf.php?'+param;	
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_print_mutasi_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
 //display window
   // title=notransaksi;
   // width='700';
   // height='400';
   // content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   // showDialog1(title,content,width,height,ev); 
}


function previewReceived(notransaksi,ev)
{
   	param='notransaksi='+notransaksi;
	tujuan = 'log_slave_print_received_pdf.php?'+param;	
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_print_received_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
 // //display window
   // title=notransaksi;
   // width='700';
   // height='400';
   // content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   // showDialog1(title,content,width,height,ev);
	
}
function processReceipt(notransaksi,tanggal){
	gudang = document.getElementById('sloc').value;	
	param='notransaksi='+notransaksi+'&gudang='+gudang;
	tujuan = 'log_slave_getMutasiForReceive.php';
	post_response_text(tujuan, param, respog);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('tanggal').value=tanggal;
					document.getElementById('containerReceipt').innerHTML=con.responseText;
					tabAction(document.getElementById('tabFRM1'),1,'FRM',0);//jangan tanya darimana
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function mulaiSimpan(jlhbaris){
	document.getElementById('btnmulaisimpan').style.display='none';
	document.getElementById('tanggal').disabled=true;
	// if (confirm('Saving this Document..?')) {
		this.disabled=true;
		saveItemTerimaMutasi(1, jlhbaris);
	// }  
}

//max saya buat menjadi 1 karena sudah pakai commit di php
//terjadi bug ketika ada barang yang tidak sukses diterima
function saveItemTerimaMutasi(currRow,max){
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	x=tanggal;
	_start=document.getElementById(gudang+'_start').value;
	_end=document.getElementById(gudang+'_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x=x.replace("/","");
	}
	
	curdateY=x.substr(4,4).toString();
	curdateM=x.substr(2,2).toString();
	curdateD=x.substr(0,2).toString();
	curdate=curdateY+curdateM+curdateD;	
	curdate=parseInt(curdate);
	
	//====================
	nodok		=trim(document.getElementById('nodok').value);
	tanggal		=trim(document.getElementById('tanggal').value);
	//=======================
	
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Date out of range');
		document.getElementById('tanggal').disabled=false;
		document.getElementById('btnmulaisimpan').style.display='';
	} else { 
		kodebarang	=trim(document.getElementById('kodebarang'+currRow).innerHTML);
		referensi	=trim(document.getElementById('notransaksi'+currRow).innerHTML);		
		pemilikbarang=trim(document.getElementById('kodept'+currRow).innerHTML);		
		jumlah		=trim(document.getElementById('jumlah'+currRow).innerHTML);
		sebelum		=trim(document.getElementById('sebelum'+currRow).innerHTML);		
		diterima	=trim(document.getElementById('diterima'+currRow).value);
		satuan		=trim(document.getElementById('satuan'+currRow).innerHTML);		
		gudangx		=trim(document.getElementById('asalgudang'+currRow).innerHTML);
		nopp		=trim(document.getElementById('nopp'+currRow).innerHTML);
		kodegudang	=trim(document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value);
		
		
		jumlah=remove_comma_var(jumlah);
		
		sebelum=remove_comma_var(sebelum);
		
		if(nodok =='' || tanggal ==''){
	        alert('Document number and date are obligatiry');
			document.getElementById('tanggal').disabled=false;
			document.getElementById('btnmulaisimpan').style.display='';
		}else if(gudangx=='') {
			alert('Storage source is obligatory');
			document.getElementById('tanggal').disabled=false;
			document.getElementById('btnmulaisimpan').style.display='';
		// } else if(kodebarang=='' || satuan=='' || parseFloat(jumlah)<0.001) {
		} else if(kodebarang=='' || satuan=='' || parseFloat(jumlah)<=0) {
			alert('Material, UOM and volume is obligatory');
			document.getElementById('tanggal').disabled=false;
			document.getElementById('btnmulaisimpan').style.display='';
		} else if (parseFloat(diterima) > (parseFloat(jumlah)-parseFloat(sebelum))) {
			alert('Jumlah diterima tidak boleh lebih dari sisa '+(parseFloat(jumlah)-parseFloat(sebelum)));
			document.getElementById('tanggal').disabled=false;
			document.getElementById('btnmulaisimpan').style.display='';
		} else {
			if(currRow>1 || confirm('Are you sure?')){
				document.getElementById('row'+currRow).style.backgroundColor='orange';
				lockScreen('wait');
				param='nodok='+nodok+'&tanggal='+tanggal+'&kodebarang='+kodebarang;
				param+='&gudangx='+gudangx+'&satuan='+satuan+'&jumlah='+diterima;
				param+='&kodegudang='+kodegudang+'&referensi='+referensi;
				param+='&pemilikbarang='+pemilikbarang+'&nopp='+nopp;
				tujuan='log_slave_savePenerimaanMutasi.php';
				post_response_text(tujuan, param, respog);
			}
		}
	}
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if(con.responseText=='reloadpage'){
					alert("Gagal. No Dokumen sudah dipakai, reload frame dan input ulang transaksi.");
					document.location.reload();
					return;
				}
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
				}else {
					document.getElementById('row'+currRow).style.backgroundColor='green';
					currRow+=1;
					if(currRow>max){
						currRow=1;
						setSloc('simpan');							
						alert('Done');
						document.getElementById('containerReceipt').innerHTML='';
						document.getElementById('tanggal').disabled=false;
						unlockScreen();
					}else{
						saveItemTerimaMutasi(currRow,max);
					}
				}
			}
			else {
				unlockScreen();
				busy_off();
				error_catch(con.status);
				document.getElementById('tanggal').disabled=false;
			}
		}
	}	
}
function cariBapbReceived(num)
 {
	tex=trim(document.getElementById('txtrece').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    if(gudang =='')
	{
		alert('Storage Location  is obligatory')
	}
	else
	{
		param='gudang='+gudang;
		param+='&page='+num+'&tex='+tex;
		if(tex!='')
			param+='&tex='+tex;
		tujuan = 'log_slave_getMutasiReceivedList.php';
		post_response_text(tujuan, param, respog);			
	}
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('containerlistreceived').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	 	
 }
 
//tambahan indra, delete penerimaan mutasi
function del(notransaksi)
{//indra
	gudang=document.getElementById('sloc').value;
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='log_slave_getMutasi_del.php';
	if(confirm('Are You sure Delete this document'))
    post_response_text(tujuan, param, respog);	
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    cariBapbReceived();
                    getMutasiList(gudang);
					   
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}
 
//============================================================================