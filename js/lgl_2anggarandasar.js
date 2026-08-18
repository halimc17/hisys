/**
 * @author repindra.ginting
 */
 
function detailData(ev,tujuan,tglawal,tglakhir,pur)
{
  
        unit=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
        prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
  param='tglAwal='+tglawal+'&tglAkhir='+tglakhir+'&purchasing='+pur;
        param+='&proses=getDetail'+'&kdUnit='+unit+'&periode='+prd;
  judul="Detail ";
  // alert(param);
  printFile(param,tujuan,judul,ev)
}
function detailData(ev,tujuan,tglawal,tglakhir,pur)
{
	
        unit=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
        prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	param='tglAwal='+tglawal+'&tglAkhir='+tglakhir+'&purchasing='+pur;
        param+='&proses=getDetail'+'&kdUnit='+unit+'&periode='+prd;
	judul="Detail ";
	// alert(param);
	printFile(param,tujuan,judul,ev)
}
function gettahunakhir()
{
  tahundari=document.getElementById('tahundari').value;
  param='tahundari='+tahundari;
  param+='&proses=gettahunakhir';  
  tujuan='lgl_2slave_anggarandasar.php';
  post_response_text(tujuan, param, respog);
        function respog()
        {
                  if(con.readyState==4)
                  {
                                if (con.status == 200)
                                {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) 
                                        {
                                                alert(con.responseText);
                                        }
                                        else 
                                        {
                                                document.getElementById('tahunsampai').innerHTML=con.responseText;
                                        }
                                }
                                else 
                                {
                                        busy_off();
                                        error_catch(con.status);
                                }
                  } 
        }   
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function detailPP(ev,tujuan,pur,stat)
{
        unit=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
        prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	param='purchasing='+pur+'&statSql='+stat;
        param+='&proses=getPP'+'&kdUnit='+unit+'&periode='+prd;
	judul="Detail PP";
	// alert(param);
	printFile(param,tujuan,judul,ev)
}
function fisikKeExcel2(ev,tujuan,tglawal,tglakhir,pur)
{
	unit=document.getElementById('kdUnit').value;
        prd=document.getElementById('periode').value;
	param='tglAwal='+tglawal+'&tglAkhir='+tglakhir+'&purchasing='+pur;
        param+='&proses=excelDetail'+'&kdUnit='+unit+'&periode='+prd;
	judul='Report Ms.Excel';	
	printFile2(param,tujuan,judul,ev)	
}
function dataPPexcel(ev,tujuan,pur,stat)
{
	unit=document.getElementById('kdUnit').value;
        prd=document.getElementById('periode').value;
	param='purchasing='+pur+'&statSql='+stat;
        param+='&proses=getPPExcel'+'&kdUnit='+unit+'&periode='+prd;
	judul='Report Ms.Excel';	
	printFile2(param,tujuan,judul,ev)	
}

function printFile2(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='450';
   height='350';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}