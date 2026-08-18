/**
 * @author repindra.ginting
 */
 
 
function cancel(){
	document.getElementById('pt').value='';
	document.getElementById('unit').value='';
	document.getElementById('supkontran').value='';
	document.getElementById('kodesupplier').value='';
	document.getElementById('tanggaljt').value='';
	document.getElementById('jenis').value='';
	document.getElementById('status').value='';
	document.getElementById('noinvoicesch').value='';
	document.getElementById('nopodt').value='';
	document.getElementById('container').innerHTML='';
}

function getUsiaHutang(tipelaporan)
{
	
	pt=document.getElementById('pt');
	statuspo=document.getElementById('statuspo');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
	tanggalpivot2 =document.getElementById('tanggalpivot2').value;
	tanggaljt =document.getElementById('tanggaljt').value;
	jenis=document.getElementById('jenis');
	unit=document.getElementById('unit');
	status2=document.getElementById('status');
		ptV		=pt.options[pt.selectedIndex].value;
		gudangV	=gudang.options[gudang.selectedIndex].value;
		statuspoV	=statuspo.options[statuspo.selectedIndex].value;
		jenisV		=jenis.options[jenis.selectedIndex].value;
		unitV		=unit.options[unit.selectedIndex].value;
		statusV		=status2.options[status2.selectedIndex].value;
                
            supkontran=document.getElementById('supkontran').value;
            kodesupplier=document.getElementById('kodesupplier').value;
            nopodt=document.getElementById('nopodt').value;
            noinvoicesch=document.getElementById('noinvoicesch').value;

	param='pt='+ptV+'&gudang='+gudangV+'&tanggalpivot='+tanggalpivot+'&tanggalpivot2='+tanggalpivot2+'&tanggaljt='+tanggaljt+'&statuspo='+statuspoV+'&supkontran='+supkontran+'&kodesupplier='+kodesupplier+'&nopodt='+nopodt+'&jenis='+jenisV+'&unit='+unitV+'&status='+statusV+'&tipelaporan='+tipelaporan+'&noinvoicesch='+noinvoicesch;
	tujuan='keu_laporanUsiaHutang.php';
	
	if(tipelaporan=='excel'){
		printnopopup(tujuan+'?'+param);
	}else{
	
		post_response_text(tujuan, param, respog);
//	alert(tujuan+param);
	
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
						/*
						showById('printPanel');
						isdt = con.responseText.split("######");
						document.getElementById('containerxtra').innerHTML=isdt[0];
						document.getElementById('container').innerHTML=isdt[1];
						*/
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}		
	}
}

function fisikKeExcel(ev,tujuan)
{
	pt	=document.getElementById('pt');
	gudang  =document.getElementById('gudang');
	jenis  =document.getElementById('jenis');
	unit  =document.getElementById('unit');
	status2  =document.getElementById('status');
	tanggalpivot =document.getElementById('tanggalpivot').value;
	tanggalpivot2 =document.getElementById('tanggalpivot2').value;
	tanggaljt =document.getElementById('tanggaljt').value;
		pt		=pt.options[pt.selectedIndex].value;
		gudang	=gudang.options[gudang.selectedIndex].value;
		jenis	=jenis.options[jenis.selectedIndex].value;
		unit	=unit.options[unit.selectedIndex].value;
		status	=status2.options[status2.selectedIndex].value;
                
        statuspo=document.getElementById('statuspo').value;       
        supkontran=document.getElementById('supkontran').value;        
        kodesupplier=document.getElementById('kodesupplier').value;        
        nopodt=document.getElementById('nopodt').value;
                
//		periode	=periode.options[periode.selectedIndex].value;
	judul='Report Ms.Excel';	
	param='pt='+pt+'&gudang='+gudang+'&tanggalpivot='+tanggalpivot+'&tanggalpivot2='+tanggalpivot2+'&tanggaljt='+tanggaljt+'&statuspo='+statuspo+'&supkontran='+supkontran+'&kodesupplier='+kodesupplier+'&nopodt='+nopodt+'&jenis='+jenis+'&unit='+unit+'&status='+status;
	printFile(param,tujuan,judul,ev)	
}

function fisikKePDF(ev,tujuan)
{
	pt	=document.getElementById('pt');
	gudang  =document.getElementById('gudang');
	jenis  =document.getElementById('jenis');
	unit  =document.getElementById('unit');
	status2  =document.getElementById('status');
	tanggalpivot =document.getElementById('tanggalpivot').value;
	tanggalpivot2 =document.getElementById('tanggalpivot2').value;
	tanggaljt =document.getElementById('tanggaljt').value;
		pt		=pt.options[pt.selectedIndex].value;
		gudang	=gudang.options[gudang.selectedIndex].value;
		jenis	=jenis.options[jenis.selectedIndex].value;
		unit	=unit.options[unit.selectedIndex].value;
		status	=status2.options[status2.selectedIndex].value;
                
        statuspo=document.getElementById('statuspo').value;       
        supkontran=document.getElementById('supkontran').value;        
        kodesupplier=document.getElementById('kodesupplier').value;        
        nopodt=document.getElementById('nopodt').value;       
//		periode	=periode.options[periode.selectedIndex].value;
	judul='Report PDF';	
	param='pt='+pt+'&gudang='+gudang+'&tanggalpivot='+tanggalpivot+'&tanggalpivot2='+tanggalpivot2+'&tanggaljt='+tanggaljt+'&statuspo='+statuspo+'&supkontran='+supkontran+'&kodesupplier='+kodesupplier+'&nopodt='+nopodt+'&jenis='+jenis+'&unit='+unit+'&status='+status;
	printFile(param,tujuan,judul,ev)	
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

function lihattagihan(noinvoice,ev)
{
   param='noinvoice='+noinvoice;
   tujuan='keu_slave_laporanusiahutang.php'+"?"+param;  
   width='600';
   height='100';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2('Data Tagihan '+noinvoice,content,width,height,ev); 
	
}


function ambilAnak(pt)
{
	param='pt='+pt;
	tujuan='keu_slave_getUnit.php';
	post_response_text(tujuan, param, respog);
	
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('gudang').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}

function getnilaisaldo()
{
	pt=document.getElementById('pt');
	statuspo=document.getElementById('statuspo');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
	ptV		=pt.options[pt.selectedIndex].value;
	gudangV	=gudang.options[gudang.selectedIndex].value;
	statuspoV	=statuspo.options[statuspo.selectedIndex].value;            
    supkontran=document.getElementById('supkontran').value;
    rekening=document.getElementById('rekening').value;

	param='pt='+ptV+'&gudang='+gudangV+'&tanggalpivot='+tanggalpivot+'&statuspo='+statuspoV+'&supkontran='+supkontran+'&rekening='+rekening;
	tujuan='keu_laporanUsiaHutang.php';
	post_response_text(tujuan, param, respog);
	//	alert(tujuan+param);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					showById('printPanel');
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

function getnilaisaldodireksi(unit)
{
	pt=document.getElementById('pt');
	statuspo=document.getElementById('statuspo');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
	ptV		=pt.options[pt.selectedIndex].value;
	gudangV	=gudang.options[gudang.selectedIndex].value;
	statuspoV	=statuspo.options[statuspo.selectedIndex].value;            
    supkontran=document.getElementById('supkontran').value;
    rekening=document.getElementById('rekening').value;

	param='pt='+ptV+'&gudang='+gudangV+'&tanggalpivot='+tanggalpivot+'&statuspo='+statuspoV+'&supkontran='+supkontran+'&rekening='+rekening;
	tujuan='keu_laporanUsiaHutang.php';
	post_response_text(tujuan, param, respog);
	//	alert(tujuan+param);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					showById('printPanel');
					document.getElementById('container').innerHTML=con.responseText;
					getsaldo(unit);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function getsaldo(unit)
{
	rekening=document.getElementById('rekening').value;
	tanggal=document.getElementById('tanggalpivot').value;
	param='unit='+unit+'&rekening='+rekening+'&tanggal='+tanggal+'&proses=getsaldo';
	tujuan='keu_slave_2prosesagingschedule.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					isdt = con.responseText.split("####");
                    document.getElementById('saldo').value = isdt[0];
                    document.getElementById('estimasi').value = isdt[1];
                    document.getElementById('ketsaldo').value = isdt[2];
                    document.getElementById('ketestimasi').value = isdt[3];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function checkAll()
{   
    totrow = document.getElementById('totrow').value;
    btn = document.getElementById('btnall');
    if (btn.checked == true){
        chk = true;
        for (i=1; i <= totrow; i++)
	    {
	    	if (document.getElementById('statbayar_'+i).value=='1') {
		    	document.getElementById('no_'+i).checked = chk;
		        nilai=document.getElementById('nilai_'+i).value;
				document.getElementById('bayar_'+i).value=numberFormat(nilai,2);
				document.getElementById('bayar_'+i).disabled=false;
	    	}else{
		    	document.getElementById('no_'+i).checked = false;
	    	}
	    }
    } else {
        chk = false;
        for (i=1; i <= totrow; i++)
	    {
	        document.getElementById('no_'+i).checked = chk;
			document.getElementById('bayar_'+i).disabled=true;
	    	if (document.getElementById('statbayar_'+i).value=='1') {
	    		document.getElementById('bayar_'+i).value='';
	    	}
	    }
    }
}

function check1(no)
{   
	if (document.getElementById('no_'+no).checked == true && document.getElementById('statbayar_'+no).value=='1'){
		nilai=document.getElementById('nilai_'+no).value;
		document.getElementById('bayar_'+no).value=numberFormat(nilai,2);
		document.getElementById('bayar_'+no).disabled=false;
	}else{
		document.getElementById('bayar_'+no).value='';
		document.getElementById('bayar_'+no).disabled=true;
	}
	
}

function adddetail(nokontrak,kodebarang,kdcust) {
    totrow=trim(document.getElementById('totrow').value);
    tanggal=trim(document.getElementById('tanggalpivot').value);
    rekening=document.getElementById('rekening').value;
    saldo=document.getElementById('saldo').value;
    ketsaldo=document.getElementById('ketsaldo').value;
    estimasi=document.getElementById('estimasi').value;
    ketestimasi=document.getElementById('ketestimasi').value;
    saldoblokir=document.getElementById('saldoblokir').value;
    ketblokir=document.getElementById('ketblokir').value;
    
    var allData='';
    var cekpil=0;
    for(dwc=1;dwc<=totrow;dwc++){
        if (document.getElementById('no_'+dwc).checked==true && parseInt(document.getElementById('bayar_'+dwc).value)>0) {
        	allData+="&bayar[]="+document.getElementById('bayar_'+dwc).value;
        	allData+="&noinvoice[]="+document.getElementById('noinv_'+dwc).innerHTML;
            cekpil+=1;
        }
    }

    if(cekpil==0){
        alert('Data belum terpilih.');
        return;
    }

    param='totrow='+cekpil+'&tanggal='+tanggal+'&saldo='+saldo+'&ketsaldo='+ketsaldo+'&estimasi='+estimasi+'&proses=adddetail';
    param+='&ketestimasi='+ketestimasi+'&saldoblokir='+saldoblokir+'&ketblokir='+ketblokir+'&rekening='+rekening;
    param+=allData;
    
    tujuan='keu_slave_2prosesagingschedule.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                	alert('Saved');
                	chk = false;
                	document.getElementById('rekening').value='';
                    document.getElementById('saldo').value ='';
                    document.getElementById('estimasi').value ='';
                    document.getElementById('saldoblokir').value ='';
                    document.getElementById('ketsaldo').value ='';
                    document.getElementById('ketestimasi').value ='';
                    document.getElementById('ketblokir').value ='';
			        for (i=1; i <= totrow; i++)
				    {
				        document.getElementById('no_'+i).checked = chk;
				        document.getElementById('no_'+i).disabled = chk;
						document.getElementById('bayar_'+i).value='';
						document.getElementById('bayar_'+i).disabled=true;
				    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
