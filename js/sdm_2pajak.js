
function Clear1()
{
	document.getElementById('kodeorg').value='';
	document.getElementById('tahun').value=''; 
	document.getElementById('printContainer').innerHTML='';
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='1000';
   height='250';
   content="<fieldset><iframe frameborder=0 width=100% height=230px src='"+tujuan+"'></iframe></fieldset>"
   showDialog1(judul,content,width,height,ev); 	
}
function detaildata(ev,tujuan,passParam)
{
	var passP = passParam.split('##');
	var param = "";
	param+='proses=getDetail';
        param += "&karid="+passP[0];
        param += "&gajihsthn="+passP[1];
        param += "&byjbt="+passP[2];
        param += "&penghasilanjbt="+passP[3];
        param += "&pensiun="+passP[4];
        param += "&jml_ptkp="+passP[5];
        param += "&pkp="+passP[6];
        param += "&pph21bln="+passP[7];
        param += "&premiasuransi="+passP[8];
        param += "&pajakterhutang="+passP[9];
        param += "&nama="+passP[10];
        param += "&statuspajak="+passP[11];
        param += "&jabatan="+passP[12];
        param += "&npwp="+passP[13];
        param += "&gajipokok="+passP[14];
        param += "&lemburpremi="+passP[15];
        param += "&gajih="+passP[16];
        param += "&tipekaryawan="+passP[17];
        param += "&totrapel="+passP[18];
        param += "&totbonus="+passP[19];
        param += "&totthr="+passP[20];
        param += "&lemburpremi="+passP[21];
     
	judul="Detail "+passP[0];
//	alert(param);
	printFile(param,tujuan,judul,ev)
}

function detailExcel(ev,tujuan)
{
   width='250';
   height='100';
  
   content="<iframe frameborder=0 src='"+tujuan+"'></iframe>"
   parent.showDialog2('Detail in Excel',content,width,height,ev); 
}