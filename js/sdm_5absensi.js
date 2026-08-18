/**
 * @author repindra.ginting
 */
function simpanJ()
{
	kode=document.getElementById('kode').value;
	keterangan=document.getElementById('keterangan').value;
	jumlahhk=remove_comma(document.getElementById('jumlahhk'));
	pengali=remove_comma(document.getElementById('pengali'));
	grup=document.getElementById('grup');
	status=document.getElementById('status').checked;
	potongan=document.getElementById('potongan').checked;
	validasiDokumen=document.getElementById('validasiDokumen').checked;
	//alert(status);
	if(status == 'true')
	{
		status=1;
		//alert(status);
	}
	else
	{
		status=0;
	}	

	if(potongan == true)
	{
		potongan=1;
		//alert(potongan);
	}
	else
	{
		potongan=0;
	}

	if(validasiDokumen == true)
	{
		validasiDokumen=1;
		//alert(validasiDokumen);
	}
	else
	{
		validasiDokumen=0;
	}
	grup=grup.options[grup.selectedIndex].value;	
	met=document.getElementById('method').value;
	if(trim(kode)=='' || jumlahhk=='' || keterangan==''|| pengali=='')
	{
		alert('Each Field are obligatory');
		document.getElementById('kode').focus();
	}
	else
	{
		param='kode='+kode+'&keterangan='+keterangan+'&method='+met;
		param+='&jumlahhk='+jumlahhk+'&pengali='+pengali+'&grup='+grup+'&status='+status+'&potongan='+potongan+'&validasiDokumen='+validasiDokumen;
		tujuan='sdm_slave_save_5absensi.php';
        post_response_text(tujuan, param, respog);		
	}
	
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
							document.getElementById('container').innerHTML=con.responseText;
							cancelJ();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
		
}

function fillField(kode,keterangan,kelompok,nilai,pengali,status,potongan,validasiDokumen)
{
	document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('jumlahhk').value=nilai;
	document.getElementById('pengali').value=pengali;
	document.getElementById('validasiDokumen').value=validasiDokumen;
	grup=document.getElementById('grup');
	for(x=0;x<grup.length;x++)
	{
		if(grup.options[x].value==kelompok)
		{
			grup.options[x].selected=true;
		}
	}

	if(status == 1)
	{
		document.getElementById('status').checked=true;
	}
	else
	{
		document.getElementById('status').checked=false;
	}
	if(potongan == 1)
	{
		document.getElementById('potongan').checked=true;
	}
	else
	{
		document.getElementById('potongan').checked=false;
	}
	if(validasiDokumen == 1)
	{
		document.getElementById('validasiDokumen').checked=true;
	}
	else
	{
		document.getElementById('validasiDokumen').checked=false;
	}

	document.getElementById('method').value='update';
}

function cancelJ()
{
    document.getElementById('kode').disabled=false;
	document.getElementById('kode').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('jumlahhk').value=0;
	document.getElementById('pengali').value=0;
	grup=document.getElementById('grup');
	grup=grup.options[0].selected=true;
	document.getElementById('status').checked=true;
	document.getElementById('potongan').checked=false;
	document.getElementById('validasiDokumen').checked=false;
	document.getElementById('method').value='insert';		
}
