function viewpdf(id) {
	param = 'method=viewpdf' + '&id=' + id;
	tujuan = 'pajak_slave_suratpenunjukpajak.php?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}

function batal(){
	document.getElementById('method').value = 'insert';
	document.getElementById('pemberikuasa').value = '';
	document.getElementById('kuasadariwajibpajak').value = '';
	document.getElementById('nomorsuratkhusus').value='';
 	document.getElementById('tanggalsuratkhusus').value='';
	document.getElementById('penerimakuasa').value = '';
	document.getElementById('berupa7').value = '';
	document.getElementById('berupa8').value = '';
	document.getElementById('kota').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('id').value = '';
	
}

function loadData(){
	param='method=loaddata';
	tujuan='pajak_slave_suratpenunjukpajak.php';
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


function caridata(){
	method='caridata';
   	tanggal1=trim(document.getElementById('tanggal1').value);
	tanggal2=trim(document.getElementById('tanggal2').value);
    param = 'tanggal1='+tanggal1+'&tanggal2='+tanggal2+'&method='+method;
    tujuan = 'pajak_slave_suratpenunjukpajak.php';
    
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
	 post_response_text(tujuan,param,respog);
}



function fillfield(kuasadariwajibpajak,nomorsuratkhusus,tanggalsuratkhusus,berupa7,berupa8,kota,tanggal,id){
 	document.getElementById('kuasadariwajibpajak').value=kuasadariwajibpajak;
 	document.getElementById('nomorsuratkhusus').value=nomorsuratkhusus;
 	document.getElementById('tanggalsuratkhusus').value=tanggalsuratkhusus;
	document.getElementById('berupa7').value=berupa7;
 	document.getElementById('berupa8').value=berupa8;
	document.getElementById('kota').value=kota;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('id').value=id;
	document.getElementById('method').value='update';
}


function simpan(){
	method = trim(document.getElementById('method').value);
	id = trim(document.getElementById('id').value);
	pemberikuasa = trim(document.getElementById('pemberikuasa').value);
	kuasadariwajibpajak = trim(document.getElementById('kuasadariwajibpajak').value);
	nomorsuratkhusus = trim(document.getElementById('nomorsuratkhusus').value);
	tanggalsuratkhusus = trim(document.getElementById('tanggalsuratkhusus').value);
	penerimakuasa = trim(document.getElementById('penerimakuasa').value);
	berupa7 = trim(document.getElementById('berupa7').value);
	berupa8 = trim(document.getElementById('berupa8').value);
	kota = trim(document.getElementById('kota').value);
	tanggal = trim(document.getElementById('tanggal').value);

	param='method='+method+'&id='+id+'&pemberikuasa='+pemberikuasa+
	'&kuasadariwajibpajak='+kuasadariwajibpajak+'&nomorsuratkhusus='+nomorsuratkhusus+
	'&tanggalsuratkhusus='+tanggalsuratkhusus+'&penerimakuasa='+penerimakuasa+
	'&berupa7='+berupa7+'&berupa8='+berupa8+'&kota='+kota+'&tanggal='+tanggal;

	tujuan='pajak_slave_suratpenunjukpajak.php';
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
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}