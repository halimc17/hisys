function simpanSubTipeAset()
{
	tipeasset = getValue('tipeasset');
	kodesubasset = getValue('kodesubasset');
	namasubasset = getValue('namasubasset');
	nama = getValue('nama');
	umurpenyusutan = getValue('umurpenyusutan');
	tarifpenyusutan = getValue('tarifpenyusutan');
	metodepenyusutan = getValue('metodepenyusutan');
	kodeorg = getValue('unit');
	proses = getValue('save');
	
	if(trim(namasubasset)=='' || trim(nama)=='' || metodepenyusutan=='' || kodeorg=='')
	{
		alertify.alert("Informasi",'All fields must be filled');
		//document.getElementById('kodesubasset').focus();
	//}else if(kodesubasset.length <= 1){
	//	alert('Field kode sub asset must be 2 character');
	//	document.getElementById('kodesubasset').focus();
	}else{

		if(metodepenyusutan == 'Menurun') {
			if(umurpenyusutan != '') {
				alert("Jika memilih Metode Penyusutan Menurun, pastikan hanya mengisi Tarif Penyusutan saja!");
			}
		} else if(metodepenyusutan == 'Garis Lurus') {
			if(tarifpenyusutan != '') {
				alert("Jika memilih Metode Penyusutan Garis Lurus, pastikan hanya mengisi Umur Penyusutan saja!");
			}
		}

		kodesubasset=trim(kodesubasset);
		namasubasset=trim(namasubasset);
		nama=trim(nama);
		umurpenyusutan=trim(umurpenyusutan);
		tarifpenyusutan=trim(tarifpenyusutan);
		metodepenyusutan=metodepenyusutan;
		kodeorg=kodeorg;
		param='tipeasset='+tipeasset+'&kodesubasset='+kodesubasset+'&namasubasset='+namasubasset+'&nama='+nama+'&umurpenyusutan='+umurpenyusutan+'&proses='+proses;
		param+='&tarifpenyusutan=' + tarifpenyusutan + '&metodepenyusutan=' + metodepenyusutan + '&kodeorg=' + kodeorg;
		tujuan='sdm_slave_5subtipeasset.php';
		post_response_text(tujuan, param, respog);		
	}

	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				}else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
					cancelSubTipeAsset();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function editSubTipeAset(kodesubasset,namasubasset,nama,umurpenyusutan,tipeasset,metodepenyusutan,tarifpenyusutan,kodeorg)
{
	setValue('save','edit');
	document.getElementById('kodesubasset').value=kodesubasset;
	document.getElementById('kodesubasset').disabled=true;
	document.getElementById('namasubasset').value=namasubasset;
	document.getElementById('umurpenyusutan').value=umurpenyusutan;
	document.getElementById('metodepenyusutan').value=metodepenyusutan;
	document.getElementById('tarifpenyusutan').value=tarifpenyusutan;
	document.getElementById('kodeorg').value=kodeorg;
	y=document.getElementById('nama');
	for(b=0;b<y.length;b++)
	{
		if(y.options[b].value==nama)
		{
				y.options[b].selected=true;
		}
	}

	x=document.getElementById('tipeasset');
	for(a=0;a<x.length;a++)
	{
		if(x.options[a].value==tipeasset)
		{
				x.options[a].selected=true;
		}
	}
	document.getElementById('tipeasset').disabled=true;
}

function cancelSubTipeAsset()
{
	//document.location.reload();
	document.getElementById('tipeasset').options[0].selected=true;
	document.getElementById('tipeasset').disabled=false;
	document.getElementById('kodesubasset').value='';
	//document.getElementById('kodesubasset').disabled=false;
	document.getElementById('namasubasset').value='';
	document.getElementById('nama').options[0].selected=true;
	document.getElementById('umurpenyusutan').value='';	
	document.getElementById('kodeorg').value='';	
	document.getElementById('metodepenyusutan').value='';	
	document.getElementById('tarifpenyusutan').value='';	
}

function getMP() {
	metodepenyusutan = document.getElementById('metodepenyusutan').value;

	if(metodepenyusutan == 'Menurun') {
		document.getElementById('umurpenyusutan').disabled = true;	
		document.getElementById('tarifpenyusutan').disabled = false;	
	} else if(metodepenyusutan == 'Garis Lurus') {
		document.getElementById('tarifpenyusutan').disabled = true;	
		document.getElementById('umurpenyusutan').disabled = false;	
	}
}