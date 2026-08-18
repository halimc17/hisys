// JavaScript Document
function batal()
{
	document.getElementById('method').value='insert';
	document.getElementById("kdKlBarang").selectedIndex = "0";
	document.getElementById("kdKlBarang").disabled = false;
	document.getElementById('kdSubKl').value='';
	document.getElementById('namaSubKl').value='';
}

function getKodeSubKelompok(){
	kdKlBarang=document.getElementById('kdKlBarang').options[document.getElementById('kdKlBarang').selectedIndex].value;
	
	param='kdKlBarang='+kdKlBarang+'&method=getKodeSub';
	tujuan='log_slave_5subkelompokbarang.php';
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
							document.getElementById('kdSubKl').value=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function loadData(){
	param='method=loaddata';
	tujuan='log_slave_5subkelompokbarang.php';
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
							document.getElementById('container').innerHTML=con.responseText;
							leftFixedTable();
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(jmlkategorix, kelompok, kode, namaSubKl,kodevhc){
	Lkd_org=document.getElementById('kdKlBarang');
    for(ard=0;ard<Lkd_org.length;ard++){
        if(Lkd_org.options[ard].value==kelompok){
			Lkd_org.options[ard].selected=true;
		}
    }
	all = document.querySelectorAll('.jmlkb');
	for(i=0; i<all.length; i++){
		all[i].checked = false;
	}
	
	jmlkategorix = jmlkategorix.split(",")
	countKategori = jmlkategorix.length
	
	for (x = 0; x < jmlkategorix.length; x++) {
		cek = jmlkategorix[x];
		kategoriAll = document.querySelectorAll('.jmlkb');
		kategori = document.getElementById('kategori' + cek);
		if(kategori.checked == false) {
			kategori.checked = true;
		}
	}
	document.getElementById('kdKlBarang').disabled=true;
	document.getElementById('kdSubKl').disabled=true;
	document.getElementById('kdSubKl').value=kode;
	document.getElementById('namaSubKl').value=namaSubKl;
	document.getElementById('kodevhc').value=kodevhc;
	document.getElementById('status').value='1';
	document.getElementById('method').value='edit';
}

function check() {
	alert('a');
}

function simpan(){
	kdKlBarang=document.getElementById('kdKlBarang').value;
	kdSubKl=trim(document.getElementById('kdSubKl').value);
	namaSubKl=trim(document.getElementById('namaSubKl').value);
	status=document.getElementById('status').value;
	kodevhc=document.getElementById('kodevhc').value;
	method=trim(document.getElementById('method').value);
	
	jmlkategori = document.querySelectorAll('.jmlkb')
	
	trapproval=document.getElementById('trapproval').innerHTML;
	
	if(trapproval=='')
	{
		alert("Please contact administrator to setup Approval.");
		return;
	}
	
	var tbl = document.getElementById("trapproval");
	var row = parseFloat(tbl.rows.length)+1;
	strUrl = '';
	for(i=1;i<row;i++)
	{
		if(document.getElementById('persetujuan'+i).innerHTML=='')
		{
			alert("Please contact administrator to setup Approval.");
			return false;
		}
		persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
		if(persetujuan=='')
		{
			alert("Please compelete Approval");
			return;
		}
		strUrl += '&persetujuan['+i+']='+persetujuan;
	}
	
	param = 'kdKlBarang=' + kdKlBarang + '&kdSubKl=' + kdSubKl + '&namaSubKl=' + namaSubKl + '&status=' + status + '&method='+method+ '&kodevhc='+kodevhc;
	param += strUrl;

	for (var kb = 1; kb <= jmlkategori.length; kb++) {
		kategori = document.getElementById('kategori' + kb);
		if (kategori.checked == true) {
			if(kategori.checked > 1) {
				kategori += document.getElementById('kategori' + kb).value+", ";
			} 
			kategori = document.getElementById('kategori' + kb).value;
			param += '&idkategori[]=' + kategori;
		}
	}

	tujuan='log_slave_5subkelompokbarang.php';
	// alert(param);
	if(confirm("Anda Yakin Menyimpan Data Ini?")){
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
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function deletefield(kode){
	param='kdSubKl='+kode+'&method=delete';
	tujuan='log_slave_5subkelompokbarang.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}