function batal(){
	document.getElementById('method').value = "insert";
	document.getElementById("kdOrg").selectedIndex = "0";
	document.getElementById('absniId').selectedIndex='0';
	getkaryawan();
}

function batal2(){
	document.getElementById("kdOrg2").selectedIndex = "0";
	document.getElementById('container2').innerHTML="";
	document.getElementById('kdOrg2').disabled=false;
	document.getElementById('tglAbsen2').disabled=false;
}

function loadData(){
	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tglAbsen=trim(document.getElementById('tglAbsen').value);
	
	param='method=loaddata&kdOrg='+kdOrg+'&tglAbsen='+tglAbsen;
	tujuan='sdm_slave_uploadabsensi.php';
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
					document.getElementById('container').innerHTML=con.responseText;
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

function preview2(){
	kdOrg=document.getElementById('kdOrg2').options[document.getElementById('kdOrg2').selectedIndex].value;
	tglAbsen=trim(document.getElementById('tglAbsen2').value);
	
	param='method=preview2&kdOrg='+kdOrg+'&tglAbsen='+tglAbsen;
	tujuan='sdm_slave_uploadabsensi.php';
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
					document.getElementById('container2').innerHTML=con.responseText;
					document.getElementById('kdOrg2').disabled=true;
					document.getElementById('tglAbsen2').disabled=true;
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

function simpan(){
	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tglAbsen=trim(document.getElementById('tglAbsen').value);
	noba=trim(document.getElementById('noba').value);
	krywnId=document.getElementById('krywnId').options[document.getElementById('krywnId').selectedIndex].value;
	absniId=document.getElementById('absniId').options[document.getElementById('absniId').selectedIndex].value;
	method=trim(document.getElementById('method').value);
	
	param='kdOrg='+kdOrg+'&tglAbsen='+tglAbsen+'&krywnId='+krywnId+'&absniId='+absniId+'&noba='+noba+'&method='+method;
	tujuan='sdm_slave_uploadabsensi.php';
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
					// document.getElementById('container').innerHTML=con.responseText;
					loadData();
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

function fillfield(kdOrg,tglAbsen,krywnId,absniId){
	document.getElementById('method').value = "edit";
	document.getElementById('tglAbsen').value = tglAbsen;
	akdOrg=document.getElementById('kdOrg');
    for(ard=0;ard<akdOrg.length;ard++)
    {
        if(akdOrg.options[ard].value==kdOrg)
		{
			akdOrg.options[ard].selected=true;
		}
    }
	aabsniId=document.getElementById('absniId');
    for(ard=0;ard<aabsniId.length;ard++)
    {
        if(aabsniId.options[ard].value==absniId)
        {
			aabsniId.options[ard].selected=true;
		}
    }
	akrywnId=document.getElementById('krywnId');
    for(ard=0;ard<akrywnId.length;ard++)
    {
        if(akrywnId.options[ard].value==krywnId)
        {
			akrywnId.options[ard].selected=true;
		}
    }
}

function hapus(kdOrg,tglAbsen,krywnId){
	param='kdOrg='+kdOrg+'&tglAbsen='+tglAbsen+'&krywnId='+krywnId+'&method=hapus';
	tujuan='sdm_slave_uploadabsensi.php';
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
					loadData();
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

function getkaryawan(){
	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tglAbsen=trim(document.getElementById('tglAbsen').value);
	
	param='kdOrg='+kdOrg+'&tglAbsen='+tglAbsen+'&method=getkaryawan';
	tujuan='sdm_slave_uploadabsensi.php';
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
					document.getElementById('krywnId').innerHTML = con.responseText;
					loadData();
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

function uploaddata(rows)
{
	loopingdata(1,rows)
}

function loopingdata(row,rows)
{
	kdOrg=document.getElementById('kdOrg2').options[document.getElementById('kdOrg2').selectedIndex].value;
	tglAbsen=trim(document.getElementById('tglAbsen2').value);
	updfr=document.getElementById('tdabsen_'+row).innerHTML;
	document.getElementById('trabsen_'+row).style.backgroundColor='orange';
	
	param='kdOrg='+kdOrg+'&tglAbsen='+tglAbsen+'&updfr='+updfr+'&method=uploaddata';
	tujuan='sdm_slave_uploadabsensi.php';
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
					document.getElementById('trabsen_'+row).style.display='none';
					x=row+1;
					if(x<=rows){
						loopingdata(x,rows);
					}
					else
					{
						alert("Absensi berhasil di upload.");
						document.getElementById('container2').innerHTML="";
					}
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