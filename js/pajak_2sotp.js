
function getnpwp(){
    pt=document.getElementById('pt').value; 
    param='pt='+pt;
    param+='&method=getnpwp';
    tujuan='pajak_2sotp_slave.php';
 
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }  else {
                    //alert(con.responseText);
                    document.getElementById('npwp').innerHTML=con.responseText;
                }
            }  else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}


function preview(){
	tipe='html';
    pt=trim(document.getElementById('pt').value);
    thn=trim(document.getElementById('thn').value);
    npwp=trim(document.getElementById('npwp').value);
	if(pt=='' || thn=='' || npwp==''){
		alert('Lengkapi Pengisian');return;
	}
	param='method=preview'+'&pt='+pt+'&thn='+thn+'&npwp='+npwp+'&tipe='+tipe;
	tujuan='pajak_2sotp_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
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
    thn=trim(document.getElementById('thn').value);
    npwp=trim(document.getElementById('npwp').value);
	if(pt=='' || thn=='' || npwp==''){
		alert('Lengkapi Pengisian');return;
	}
	tipe='excel';
	ev='event';
	param='method=preview'+'&pt='+pt+'&thn='+thn+'&npwp='+npwp+'&tipe='+tipe;
	tujuan='pajak_2sotp_slave.php';
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
    document.getElementById('thn').value='';	
    document.getElementById('npwp').value='';	
    document.getElementById('printContainer').innerHTML='';	
}

function addTgl(noakun,prd,pt){
    isp=prd.split("-");
    bln=parseInt(isp[1]);
    //alert(noakun+"_"+bln);
    tgl=document.getElementById(noakun+"_"+bln).value;
    tglatasnya=document.getElementById("tgl_"+noakun+"_"+bln).innerHTML;
    if(tglatasnya!=''){
        param='&method=addTgl'+'&noakun='+noakun+'&periode='+prd+'&pt='+pt+'&tanggal='+tgl;
        tujuan='pajak_2sotp_slave.php';
        post_response_text(tujuan, param, respog);
        function respog(){
            if(con.readyState==4){
                if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }  else {
                        //alert(con.responseText);
                        preview();
                    }
                }  else {
                    busy_off();
                    error_catch(con.status);
                }
            }   
        }   
    }else{
        alert("Tanggal Pembayaran Kosong");
        preview();
    }   
}
