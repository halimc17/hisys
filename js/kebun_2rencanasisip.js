// JavaScript Document

function getAfd()
{
	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	param='&kdOrg='+kdOrg;
	tujuan='kebun_slave_2rencanasisip';
	post_response_text(tujuan+'.php?proses=getAfdAll', param, respon);
//	alert(tujuan+'.php?proses=getAfd'+param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('kdAfd').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function pdf(ev,tujuan)
{
    kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    kdAfd=document.getElementById('kdAfd').options[document.getElementById('kdAfd').selectedIndex].value;
    periode    =document.getElementById('periode').value;
//    periode2    =document.getElementById('periode2').value;
    judul='Report PDF';	
    param='kdOrg='+kdOrg+'&kdAfd='+kdAfd+'&periode='+periode+'&proses=pdf';
//    alert(param);
    printFile(param,tujuan,judul,ev)	
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function detailsisip(kdAfd,periode){
        param='kdAfd='+kdAfd+'&periode='+periode;
//        alert(param);
	function respon()
	{
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                
                                document.getElementById('printContainer1').innerHTML=con.responseText;
//                                alert(con.responseText);
                                document.getElementById('printContainer1').style.display="block";
                                document.getElementById('printContainer').style.display="none";
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }	
	 }  
          post_response_text('kebun_slave_2rencanasisip_detail.php?proses=detailsisip', param, respon);
}
function zPreviewd(){
    zPreview('kebun_slave_2rencanasisip','##kdOrg##kdAfd##periode','printContainer');
            document.getElementById('printContainer').style.display="block";
            document.getElementById('printContainer1').style.display="none";
    
}
function zExcelDt(ev,tujuan,kdAfd,periode){
	judul='Detail Excel';
	param='kdAfd='+kdAfd;
        param+='&periode='+periode;
        param+='&proses=excelgetDetail2';        
	printFile(param,tujuan,judul,ev)	
//        alert(printFile);
}
function kembali(pil){
    if(pil==1){
            document.getElementById('printContainer').style.display="block";
            document.getElementById('printContainer1').style.display="none";
    }
   
}

