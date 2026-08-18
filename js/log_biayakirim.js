
//JS 

function tambahBarang(title,ev)
{
    content= "<div id=formBarang style=\"height:300px;width:400px;overflow:scroll;\"></div>";
    title='Material';
    width='400';
    height='300';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang()
{
	param='method=getListBarang';
	//alert(param);
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
	function respog(){
			if (con.readyState == 4) {
					if (con.status == 200) {
							busy_off();
							if (!isSaveResponse(con.responseText)) {
									alert(con.responseText);
							}
							else {
								//alert(con.responseText);
									document.getElementById('formBarang').innerHTML=con.responseText;
							}
					}
					else {
							busy_off();
							error_catch(con.status);
					}
			}
	} 
		
}

function cariListBarang()
{
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
                    if (con.readyState == 4) {
                                    if (con.status == 200) {
                                                    busy_off();
                                                    if (!isSaveResponse(con.responseText)) {
                                                                    alert(con.responseText);
                                                    }
                                                    else {
                                                            //alert(con.responseText);
                                                                    document.getElementById('formBarang').innerHTML=con.responseText;
                                                    }
                                    }
                                    else {
                                                    busy_off();
                                                    error_catch(con.status);
                                    }
                    }
    } 
		
}


function moveDataBarang(kodebarang,namabarang)
{
    document.getElementById('kodebarang').value=kodebarang;
    document.getElementById('namabarang').value=namabarang;
    closeDialog();
}



/////////////////////////////
////// document
/////////////////////////////////////

function tambahDok(title,ev)
{
    content= "<div id=formDok style=\"max-height:400px;width:auto;overflow:auto;\"></div>";
    title='Document';
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);	
    getListDok();
}

function getListDok()
{
	param='method=getListDok';
	//alert(param);
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					//alert(con.responseText);
					document.getElementById('formDok').innerHTML=con.responseText;
				}
		}
		else {
				busy_off();
				error_catch(con.status);
			}
		}
	} 
		
}

function cariListDok()
{
    namaDokCari=document.getElementById('namaDokCari').value;
    jenis=document.getElementById('jenis').value;
    param='method=getListDok'+'&namaDokCari='+namaDokCari+'&jenis='+jenis;
    tujuan = 'log_slave_biayakirim.php';
    console.log('param : '+param);
    post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					console.log(con.responseText);
					document.getElementById('formDok').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    } 
		
}

function getakunpajak()
{
	transporter=document.getElementById('transporter').value;
	param='method=getakunpajak'+'&transporter='+transporter;
	//alert(param);
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					data = con.responseText;
					arrdata = data.split('###');
					console.log(con.responseText);
					document.getElementById('pajak').value=arrdata[1];
					document.getElementById('noakun').value=arrdata[0];
					document.getElementById('noaruskas').value=arrdata[2];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function moveDataDok(nodok,nilai,transporter,jenis,kodeorg)
{
	//console.log(nodok);
	document.getElementById('nodok').value=nodok;
	document.getElementById('jenisx').value=jenis;
	document.getElementById('transporter').value=transporter;
	document.getElementById('jumlah').value=numberFormat(parseFloat(nilai));
	document.getElementById('kodeorg').value=kodeorg;
	getakunpajak();
}

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}
				else {
						//displayList();

						document.getElementById('container').innerHTML=con.responseText;
						//loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }	
}
function hitungtotal()
{
	var totalbiayakirim=0;
	nox=0;
	while(document.getElementById('notransaksix_'+nox)){
		totalbiayakirim=totalbiayakirim+parseFloat(document.getElementById('biayakirim_'+nox).value);
		nox++;
	}
	document.getElementById('totalbiayakirim').value=totalbiayakirim;
}
function simpandetail()
{	
	dataarray='';
	nox=0;
	while(document.getElementById('notransaksix_'+nox)){
		if(dataarray=='')
		{
			dataarray=document.getElementById('notransaksix_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('kodebarang_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('jumlahpesan_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('kodegudang_'+nox).value;
			dataarray+='#%#'+document.getElementById('biayakirim_'+nox).value;
		}
		else
		{
			dataarray+='###'+document.getElementById('notransaksix_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('kodebarang_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('jumlahpesan_'+nox).innerHTML;
			dataarray+='#%#'+document.getElementById('kodegudang_'+nox).value;
			dataarray+='#%#'+document.getElementById('biayakirim_'+nox).value;
		}
		nox++;
	}
	method=document.getElementById('methoddetail').innerHTML;
	param='dataarray='+dataarray+'&method='+method;
    tujuan='log_slave_biayakirim.php';
    //console.log(param);
    if(document.getElementById('totalbiayakirimx').value != document.getElementById('totalbiayakirim').value)
    {
    	alert('Total biaya kirim berbeda dengan Total biaya kirim pada header');
    }
    else
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
					alert(con.responseText);
				}
				else {
					console.log(con.responseText);
					closeDialog5();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}
function simpan()
{
    
    notransaksi=document.getElementById('notransaksi').value;
    nodok=document.getElementById('nodok').value;
    jenis=document.getElementById('jenisx').value;
    jumlah=remove_comma_var(document.getElementById('jumlah').value);
    kodeorg=document.getElementById('kodeorg').value;
    transporter=document.getElementById('transporter').value;
    tanggalinput=document.getElementById('tanggalinput').value;
    tanggalposting=document.getElementById('tanggalposting').value;

    method=document.getElementById('method').value;

    if(nodok=='' || tanggalposting=='' || jumlah=='0')
    {
		alert('Semua field harus diisi');
		return;
    }

    param='notransaksi='+notransaksi+'&nodok='+nodok+'&jenis='+jenis+'&jumlah='+jumlah+'&kodeorg='+kodeorg+'&transporter='+transporter+'&tanggalinput='+tanggalinput+'&tanggalposting='+tanggalposting+'&method='+method;
    tujuan='log_slave_biayakirim.php';
    //alert(param);
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
					cancel();
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function form(data,nodialog){
    width = '100%';
    height = '100%';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset style=\"width:98%\"><legend>Data</legend><div id="+data+" align=left style=\"width:100%;max-height:600px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    if(nodialog==1){
        showDialog1(title, content, width, height, ev); 
    }
    else if (nodialog==2){
        showDialog2(title, content, width, height, ev); 
    }
    else if (nodialog==3){
        showDialog5(title, content, width, height, ev); 
    }
    
}


function showdetail(data,nodialog,notransaksi,nodok,biayakirim){
    form(data,nodialog);
    param = 'method='+data;
    param+='&notransaksi='+notransaksi;
    param+='&nopo='+nodok;
    param+='&totalbiayakirim='+biayakirim;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }  else {
                	//console.log(con.responseText);
                    document.getElementById(data).innerHTML = con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancel()
{
    document.getElementById('notransaksi').value='';
	document.getElementById('nodok').value='';
	document.getElementById('transporter').selectedIndex=0;
    document.getElementById('jumlah').value='0';
    document.getElementById('pajak').value='';
    document.getElementById('tanggalposting').value='';
    document.getElementById('method').value='insert';
    document.getElementById('tmblCariNoDok').disabled=false;
	document.getElementById('simpan').disabled=false;
	    //document.getElementById('tmblCariNoGudang').disabled=false;
}

function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_biayakirim.php';
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
                                   // alert(con.responseText);
                                    document.getElementById('container').innerHTML=con.responseText;
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function edit(notransaksi,nodok,tanggalinput,tanggalposting,transporter,jumlah)
{	
	document.getElementById('jumlah').value=jumlah;
	document.getElementById('nodok').value=nodok;
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('tanggalinput').value=tanggalinput;
	document.getElementById('tanggalposting').value=tanggalposting;
	document.getElementById('transporter').value=transporter;
	document.getElementById('method').value='update';
	document.getElementById('tmblCariNoDok').disabled=true;
	getakunpajak();
	
}

function del(notransaksi,nodok)
{
	param='method=delete'+'&notransaksi='+notransaksi+'&nodok='+nodok;
	tujuan='log_slave_biayakirim.php';
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
					else 
					{
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}



function cari()
{
    nodoksch=document.getElementById('nodoksch').value;
    if(nodoksch=='')
    {
            alert('Field Was Empty');
            return;
    }
    param='method=loadData'+'&nodoksch='+nodoksch;
    tujuan='log_slave_biayakirim.php';
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
							
                                                        document.getElementById('container').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function posting(notransaksi, nodok,jenis,tanggalposting) {
	param='method=posting'+'&notransaksi='+notransaksi+'&nodok='+nodok+'&jenis='+jenis+'&tanggalpriode='+tanggalposting;;
	tujuan = 'log_slave_biayakirim.php';
	if(jenis==1){
	if(confirm("Anda akan melakukan posting biaya kirim untuk PO "+nodok+" \nAnda yakin?"))
		post_response_text(tujuan, param, respog);
	}
	else
	{
	if(confirm("Anda akan melakukan posting biaya kirim untuk Nomor Surat Jalan "+nodok+
			   " \nAnda yakin?"))
		post_response_text(tujuan, param, respog);	
	}
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					console.log(con.responseText);
				}
				else {

					alert(con.responseText);
					if(con.responseText=='Success'){
						var icon = document.getElementById(notransaksi+nodok),
						iconEdit = document.getElementById(notransaksi+nodok+'_edit'),
						iconDel = document.getElementById(notransaksi+nodok+'_delete');
						icon.removeAttribute('src');
						icon.setAttribute('src','images/buttongreen.png')
						icon.removeAttribute('onclick');
						iconEdit.style.display = 'none';
						iconEdit.removeAttribute('onclick');
						iconDel.style.display = 'none';
						iconDel.removeAttribute('onclick');
						if(jenis==1)
						{
						iconDet = document.getElementById(notransaksi+nodok+'_showdetail');
						iconDet.style.display = 'none';
						iconDet.removeAttribute('onclick');
						}
					}
					
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function getGudang(gudang) {
	if(typeof gudang=='undefined') gudang='';
	var kodebarang = getValue('kodebarang'),
		nodok = getValue('nodok'),
		param='method=getGudang'+'&nopo='+nodok+'&kodebarang='+kodebarang,
		tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					var res = JSON.parse(con.responseText),
						el = document.getElementById('kodegudang'),
						selIndex = 0;
					el.options.length = 0;
					for(i in res) {
						if(gudang == i) selIndex = el.options.length;
						el.options[el.options.length] = new Option(res[i],i);
					}
					el.selectedIndex = selIndex;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}