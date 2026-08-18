/**
 * @author repindra.ginting
 */

function getunit()
{
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	
	param='proses=getunit&pt='+pt;
    tujuan='sdm_slave_2summarykaryawan.php'; 
    post_response_text(tujuan, param, respog);

    function respog()
	{
        if (con.readyState == 4) 
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
                    document.getElementById('unit').innerHTML=con.responseText;
                    getperiode();
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

function getperiode()
{
    pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    
    param='proses=getperiode&pt='+pt+'&unit='+unit;
    tujuan='sdm_slave_2summarykaryawan.php'; 
    //alert(param);
    post_response_text(tujuan, param, respog);

    function respog()
    {
        if (con.readyState == 4) 
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
                    document.getElementById('periode').innerHTML=con.responseText;
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

 
function getlevel0(tipeprint)
{
	pt=document.getElementById('pt').value;
	unit=document.getElementById('unit').value;
    pilrep=document.getElementById('pilrep').value;
    periode=document.getElementById('periode').value;
	
	if(pt=='')
	{
		alert("Nama PT harus dipilih.");
		return false;
	}
    if(periode=='')
    {
        alert("Periode harus dipilih.");
        return false;
    }
	
	param='proses='+tipeprint+'&pt='+pt+'&unit='+unit+'&pilrep='+pilrep+'&periode='+periode;	
    tujuan='sdm_slave_2summarykaryawan.php'; 
	//alert(param);
	if(tipeprint=='excel'){
        printnopopup(tujuan+'?'+param);
	}else{
	
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
						document.getElementById('printContainer0').innerHTML=con.responseText;
                        leftFixedTable()
						document.getElementById('printContainer1').innerHTML='';
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




function getlevel1(tanggal,region)
{
    param='proses=level1&tanggal='+tanggal+'&region='+region;
    tujuan='sdm_slave_2summarykaryawan.php'; 
//    alert(param);
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('printContainer1').innerHTML=con.responseText;
                }
            }
            else {
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

function level1excel(ev,tujuan,tanggal,region)
{
    param='proses=excel2&tanggal='+tanggal+'&region='+region;

    judul='Report Ms.Excel';	
    printFile(param,tujuan,judul,ev)	
}
function getUnit2(){
    pro=document.getElementById('ptId2');
    prod=pro.options[pro.selectedIndex].value;
    param='proses=getUnit'+'&ptId2='+prod;
    tujuan='log_slave_2gdangAccounting2.php';
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
                               document.getElementById('unitId2').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }	
	 }  
}
function getlevel2(tanggal,region){
    param='proses=level1&prdIdDr2='+tanggal+'&region='+region;
    tujuan='sdm_slave_2summarykaryawan2.php'; 
//    alert(param);
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {                     
                    document.getElementById('printContainer5').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}
function windAta(title,tujuan,param,ev){
  tujuan=tujuan+"?"+param;  
   width='455';
   height='';
   content="<fieldset><iframe frameborder=0 width=100% height=100% src='"+tujuan+"'>\n\
            <div id='detailData' style='overflow:auto;min-height:300px;width:450px'></div></iframe></fieldset>"
   showDialog1(title,content,width,height,ev); 	
}
function getKary(reg,kary,ev){
    judul='Detail Karyawan '+reg;
    tgl =document.getElementById('tanggal').value;
    param='proses=getNmKar'+'&tanggal='+tgl;
    param+='&tipekary='+kary+'&lokTgs='+reg;
    tujuan='sdm_slave_2summarykaryawan.php'; 
    windAta(judul,tujuan,param,ev);	
}
function printFile2(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='190';
   height='45';
   //alert(tujuan);
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   
   showDialog2(title,content,width,height,ev); 	
}

function detexcel(ev,reg,kary,tanggal){
    param='proses=excelDt'+'&lokTgs='+reg+'&tipekary='+kary+'&tanggal='+tanggal;
    judul='Report Ms.Excel';	
    tujuan='sdm_slave_2summarykaryawan.php'; 
    printFile2(param,tujuan,judul,ev)	
}
function zPreview(fileTarget,passParam,idCont) {
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
  // alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById(idCont);
                    res.innerHTML = con.responseText;
                    document.getElementById('printContainer5').innerHTML="";
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php?proses=preview', param, respon);

}
function zExcel(ev,tujuan,passParam)
{
	judul='Report Excel';
	//alert(param);	
	var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	param+='&proses=excel';
	//alert(param);
	printFile(param,tujuan,judul,ev)	
}
function level2excel(ev,tujuan,tanggal,region){
    param='proses=excel&prdIdDr2='+tanggal+'&region='+region;

    judul='Report Ms.Excel';	
    printFile(param,tujuan,judul,ev)	
}


function lihatdetail(unit,golongan,tipekaryawan,jenis,isijenis,jab)
{
    periode = document.getElementById('periode').value;
    param='proses=lihatdetail&golongan='+golongan+'&unit='+unit+'&tipekaryawan='+tipekaryawan+'&jenis='+jenis+'&periode='+periode+'&isijenis='+isijenis+'&jab='+jab;
    tujuan='sdm_slave_2summarykaryawan.php';
    post_response_text(tujuan, param, respog);

    function respog()
    {
        if (con.readyState == 4) 
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
                    if(jenis == 'KELAMIN'){jenis = 'JENIS KELAMIN';}
                    alertify.popup().destroy();
                    alertify.popup("Detail Karyawan "+jenis, con.responseText).set({ resizable: true, overflow: true }).resizeTo("80%", "80%");
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