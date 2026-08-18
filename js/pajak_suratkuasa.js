function viewpdf(id) {
	param = 'method=viewpdf' + '&id=' + id;
	tujuan = 'pajak_slave_suratkuasa.php?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}

function batal(){
	document.getElementById('method').value = 'insert';
	document.getElementById('pemberikuasa').value = '';
	document.getElementById('penerimakuasa1').value = '';
	document.getElementById('penerimakuasa2').value = '';
	
	document.getElementById('kota').value = '';
	document.getElementById('tanggal').value = '';
	document.getElementById('id').value = '';
	
}

function loadData(){
	param='method=loaddata';
	tujuan='pajak_slave_suratkuasa.php';
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
    tujuan = 'pajak_slave_suratkuasa.php';
    
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



function fillfield(kota,tanggal,id){
 	document.getElementById('kota').value=kota;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('id').value=id;
	document.getElementById('method').value='update';
}


function simpan(){
	method = trim(document.getElementById('method').value);
	id = trim(document.getElementById('id').value);
	pemberikuasa = trim(document.getElementById('pemberikuasa').value);
	penerimakuasa1 = trim(document.getElementById('penerimakuasa1').value);
	penerimakuasa2 = trim(document.getElementById('penerimakuasa2').value);
	kota = trim(document.getElementById('kota').value);
	tanggal = trim(document.getElementById('tanggal').value);

	param='method='+method+'&id='+id+'&pemberikuasa='+pemberikuasa+
	'&penerimakuasa1='+penerimakuasa1+'&penerimakuasa2='+penerimakuasa2+
	'&kota='+kota+'&tanggal='+tanggal;

	tujuan='pajak_slave_suratkuasa.php';
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