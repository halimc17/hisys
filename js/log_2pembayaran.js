// JavaScript Document
function getPt()
{
        prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	param='proses=getPt'+'&periode='+prd;
	tujuan='log_slave_2pembayaran.php';
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
                                    //alert(con.responseText);
                                    document.getElementById('kdUnit').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
         }
		
}
function getNopo()
{
     prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
     if(prd=='')
         {
             alert("Period required!");
             document.getElementById('jenisId').value='';
             return;
         }
     jns=document.getElementById('jenisId').options[document.getElementById('jenisId').selectedIndex].value;
    param='proses=getJenis'+'&periode='+prd+'&jenisId='+jns;
    tujuan='log_slave_2pembayaran.php';
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
                                //alert(con.responseText);
                                aer=con.responseText.split("###");
                                document.getElementById('lstPo').innerHTML=aer[0];
                                document.getElementById('suppId').innerHTML=aer[1];
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
          }
     }
}
function getAll()
{
    prd=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    unt=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
    jns=document.getElementById('jenisId').options[document.getElementById('jenisId').selectedIndex].value;
    param='proses=getJenis'+'&periode='+prd+'&jenisId='+jns+'&kdUnit='+unt;
    tujuan='log_slave_2pembayaran.php';
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
                                //alert(con.responseText);
                                document.getElementById('lstPo').innerHTML=con.responseText;
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
          }
     }
}
function searchSupplier(title,content,ev)
{
	width='';
	height='';
	showDialog5(title,content,width,height,ev);
	//alert('asdasd');
}
function findSupplier()
{
    nmSupplier=document.getElementById('nmSupplier').value;
    param='proses=getSupplierNm'+'&nmSupplier='+nmSupplier;
    tujuan='log_slave_2pembayaran.php';
    post_response_text(tujuan, param, respog);

    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                  document.getElementById('containerSupplier').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }
}
function setData(kdSupp)
{
    l=document.getElementById('suppId2');

    for(a=0;a<l.length;a++)
        {
            if(l.options[a].value==kdSupp)
                {
                    l.options[a].selected=true;
                }
        }

       closeDialog();
//	   get_supplier();
}

function detailpembayaran(tgl_cari,tgl_cari2,jenisId2,kdUnit2,suppId2){
        param='tgl_cari='+tgl_cari+'&tgl_cari2='+tgl_cari2;
        param+='&jenisId2='+jenisId2+'&kdUnit2='+kdUnit2+'&suppId2='+suppId2;
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
          post_response_text('log_slave_2pembayaran2detail.php?proses=preview', param, respon);
}

function zPreviewd(){
    zPreview('log_slave_2pembayaran2','##tgl_cari##tgl_cari2##jenisId2##kdUnit2##cariNopo##suppId2','printContainer');
            document.getElementById('printContainer').style.display="block";
            document.getElementById('printContainer1').style.display="none";
    
}

function kembali(pil){
//    if(pil==0){
//            document.getElementById('printContainer').style.display="block";
//            document.getElementById('printContainer1').style.display="none";
//    }
    if(pil==1){
            document.getElementById('printContainer').style.display="block";
            document.getElementById('printContainer1').style.display="none";
    }
   
}

function zExcelDt(ev,tujuan,tgl_cari,tgl_cari2,jenisId2,kdUnit2,suppId2){
	judul='Detail Excel';
	param='tgl_cari='+tgl_cari;
        param+='&tgl_cari2='+tgl_cari2+'&jenisId2='+jenisId2+'&kdUnit2='+kdUnit2;
        param+='&suppId2='+suppId2;
        param+='&proses=excelgetDetail2';
//        alert(param);
	printFile(param,tujuan,judul,ev)	
}

