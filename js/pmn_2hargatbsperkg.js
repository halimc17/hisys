/**
 * @author repindra.ginting
 */
 
function canceldetail(){
	document.getElementById('container').innerHTML='';
	document.getElementById('unitdt').value='';
	document.getElementById('tanggal1dt').value='';
}	

function cancelrekap(){
	document.getElementById('container').innerHTML='';
	document.getElementById('unitrekap').value='';
	document.getElementById('tanggal1rekap').value='';
}	
 
 
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
laporandetail

function laporanrekap(tipe,method){
	unit	=document.getElementById('unitrekap').value;
	jenis	=document.getElementById('jenisrekap').value;
	tanggal1	=document.getElementById('tanggal1rekap').value;
	tanggal2	=document.getElementById('tanggal2rekap').value;
	
	jam1	=document.getElementById('jam1rekap').value;
	jam2	=document.getElementById('jam2rekap').value;
	
	menit1	=document.getElementById('menit1rekap').value;
	menit2	=document.getElementById('menit2rekap').value;
	
	param='unit='+unit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&method='+method+'&tipe='+tipe+'&jenis='+jenis;
	param+='&jam1='+jam1+'&jam2='+jam2+'&menit1='+menit1+'&menit2='+menit2;
	tujuan='pmn_2hargatbsperkg_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function laporandetail(tipe,method){
	unit	=document.getElementById('unitdt').value;
	tanggal1	=document.getElementById('tanggal1dt').value;
	tanggal2	=document.getElementById('tanggal2dt').value;
	
	jam1	=document.getElementById('jam1dt').value;
	jam2	=document.getElementById('jam2dt').value;
	
	menit1	=document.getElementById('menit1dt').value;
	menit2	=document.getElementById('menit2dt').value;
	
	
	param='unit='+unit+'&tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&method='+method+'&tipe='+tipe;
	param+='&jam1='+jam1+'&jam2='+jam2+'&menit1='+menit1+'&menit2='+menit2;
	tujuan='pmn_2hargatbsperkg_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

 
//get unit
function getUnit() {
    regional=document.getElementById('regional').value;
    pt=document.getElementById('pt').value;
    param='proses=getUnit'+'&regional='+regional+'&pt='+pt;
    //alert(param);
    tujuan='keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);    
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('gudang').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}
