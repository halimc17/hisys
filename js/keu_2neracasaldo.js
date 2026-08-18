function detail(nodok,noakun,ev) {
    param='nodok='+nodok+'&method=detail';
	 param+="&noakun="+noakun;
	width='500px';
    height='450px';
    content="<div id=containerdetail></div>";
    ev='event';
    title=nodok;
    showDialog1(title,content,width,height,ev);
    tujuan='keu_2jurnalpendapatan_slave.php';
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
                            document.getElementById('containerdetail').innerHTML=con.responseText;
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              }
     }
}



// function showDetailData(nodok){
    // width='500px';
    // height='450px';
    // content="<div id=containerdetail></div>";
    // ev='event';
    // title=nodok;
    // showDialog1(title,content,width,height,ev);
// }


function preview(){

	pt=trim(document.getElementById('pt').value);
	gudang=trim(document.getElementById('gudang').value);
	periode=trim(document.getElementById('periode').value);
	periode1=trim(document.getElementById('periode1').value);
	revisi=trim(document.getElementById('revisi').value);
	regional=trim(document.getElementById('regional').value);
	akundari=trim(document.getElementById('akundari').value);
	akunsampai=trim(document.getElementById('akunsampai').value);
	tampilanId=trim(document.getElementById('tampilanId').value);
	
	tipe='html';
	param='method=preview'+'&pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
	param+='&regional='+regional+'&tampilanId='+tampilanId+'&tipe='+tipe;
	param+='&akundari='+akundari+'&akunsampai='+akunsampai;
	tujuan='keu_2neracasaldo_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function getpt(){
    pt=trim(document.getElementById('pt').value);
	param='method=getpt'+'&pt='+pt;
	tujuan='keu_2jurnalpendapatan_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {	
					 document.getElementById('pt').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



function excel(){
	
	pt=trim(document.getElementById('pt').value);
	gudang=trim(document.getElementById('gudang').value);
	periode=trim(document.getElementById('periode').value);
	periode1=trim(document.getElementById('periode1').value);
	revisi=trim(document.getElementById('revisi').value);
	regional=trim(document.getElementById('regional').value);
	tampilanId=trim(document.getElementById('tampilanId').value);
	akundari=trim(document.getElementById('akundari').value);
	akunsampai=trim(document.getElementById('akunsampai').value);
	
	tipe='excel';
	param='method=preview'+'&pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
	param+='&regional='+regional+'&tampilanId='+tampilanId+'&tipe='+tipe;
	param+='&akundari='+akundari+'&akunsampai='+akunsampai;
	ev='event';
	tujuan='keu_2neracasaldo_slave.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}



function batal(){
    document.getElementById('pt').value='';		
    document.getElementById('tgl1').value='';	
    document.getElementById('tgl2').value='';
	document.getElementById('tgljurnal11').value='';	
    document.getElementById('tgljurnal12').value='';	
    document.getElementById('kodebarang').value='';	
    document.getElementById('kodecustomer').value='';	
    document.getElementById('noakun').value='';	
    document.getElementById('printContainer').innerHTML='';	
}


