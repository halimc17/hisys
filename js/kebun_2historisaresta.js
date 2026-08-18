// JavaScript Document

function previewData()
{
    kdOrg=document.getElementById('idKbn').value;
    param='kdOrg='+kdOrg+'&tahun='+getValue('tahun')+'&proses=getData';
    //alert(param);
    tujuan='kebun_slave_2historisaresta.php';
    post_response_text(tujuan, param, respon);
    function respon(){
    if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //alert(con.responseText);
                    document.getElementById('result').style.display='block';
                    document.getElementById('list_ganti').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function cancelSave()
{
	document.getElementById('list_ganti').innerHTML='';
	document.getElementById('idKbn').disabled=false;
	document.getElementById('Tahun').disabled=false;
	document.getElementById('dtl_pem').disabled=false;
	document.getElementById('idKbn').value='';
	document.getElementById('Tahun').value='';
	document.getElementById('result').style.display='none';
	
}
function viewData(param,title,content,ev)
{
	width='400';
	height='400';
	showDialog1(title,content,width,height,ev);
	ar=param.split("###");
	dataDetail(ar[0],ar[1]);
}

function dataDetail(kodeorg,tahun)
{
	param='kdOrg='+kodeorg+'&Tahun='+tahun+'&proses=ShowData';
	tujuan='kebun_slave_3tutuparesta.php';
	post_response_text(tujuan, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function detexcel(ev){
    kdOrg=document.getElementById('idKbn').value;
    param='proses=excel'+'&kdOrg='+kdOrg+'&tahun='+getValue('tahun');
    judul='Report Ms.Excel';	
    tujuan='kebun_slave_2historisaresta.php'; 
    printFile2(param,tujuan,judul,ev)	
}

function printFile2(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='190';
   height='45';
//   alert(tujuan);
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   
   showDialog2(title,content,width,height,ev); 	
}