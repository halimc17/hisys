// JavaScript Document
//function refresh_data()
//{
//	param='method=list_new_data';
//	tujuan='log_slave_cetak_po.php';
//	function respog()
//		{
//				  if(con.readyState==4)
//				  {
//						if (con.status == 200) {
//							busy_off();
//							if (!isSaveResponse(con.responseText)) {
//								alert(con.responseText);
//							}
//							else {
//								//alert(con.responseText);
//								document.getElementById('contain').innerHTML=con.responseText;
//								document.getElementById('txtsearch').value='';
//								document.getElementById('tgl_cari').value='';
//								//alert('Berhasil');
//							}
//						}
//						else {
//							busy_off();
//							error_catch(con.status);
//						}
//				  }	
//		 } 	
//		 post_response_text(tujuan, param, respog);	
//}
function loadData()
{
	param='method=list_new_data';
	tujuan='log_slave_cetak_po.php';
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
								data = JSON.parse(con.responseText);
								//Go to function create html  - author : Atwal
								load_data_exc(data);
								document.getElementById('txtsearch').value='';
								document.getElementById('tgl_cari').value='';
								//alert('Berhasil');
							}
						}
						else {
							busy_off();
							error_catch(con.status);
						}
				  }	
		 } 	
		 post_response_text(tujuan, param, respog);	
}



function cariPo()
{
	txtSearch=trim(document.getElementById('txtsearch').value);
	tglCari=trim(document.getElementById('tgl_cari').value);
	statusreal=trim(document.getElementById('statusreal').value);
	nmsupplier=trim(document.getElementById('nmsupplier').value);
	//met=document.getElementById('method');
	//met=met.value='list_new_data';
        param='method=list_new_data'+'&txtSearch='+txtSearch+'&tglCari='+tglCari+'&statusreal='+statusreal+'&nmsupplier='+nmsupplier;
        
	tujuan='log_slave_cetak_po.php';
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
								//document.getElementById('contain').innerHTML=con.responseText;
								
								//Get data convert to json - author : Atwal
								data = JSON.parse(con.responseText);
								//Go to function create html  - author : Atwal
								load_data_exc(data);
							}
						}
						else {
							busy_off();
							error_catch(con.status);
						}
				  }	
		 }
		 post_response_text(tujuan, param, respog);
}
function load_data_exc(data){
	var contain = document.getElementById('contain');
	var foot = document.getElementById('containfoot');
	
	//html modification  - author : Atwal
	html = "";
	if(data.tbody.length > 0){
		for(i = 0; i < data.tbody.length; i++) { 
			print = "";
			html += '<tr class="rowcontent" id="tr_'+data.tbody[i].no+'">';
			html += '<td align=center>'+ data.tbody[i].no +'</td>';
			html += '<td id="td_'+data.tbody[i].no+'">'+ data.tbody[i].nopo+'</td>';
			html += '<td align=center><img src="images/zoom.png" title="Detail" style="cursor:pointer" onclick=\'previewlinkpemenang("'+data.tbody[i].nomordph+'", "'+data.tbody[i].kodesupplier+'","Detail Riwayat Perbandingan Harga" ,event)\'></td>';
			html += '<td align=center>'+ data.tbody[i].tanggal+'</td>';
			html += '<td align=center>'+ data.tbody[i].namaorganisasi+'</td>';
			html += '<td align=center>'+ data.tbody[i].vendor+'</td>';
			html += '<td align=center>'+ data.tbody[i].tipepo+'</td>';
			html += '<td align=center>'+ data.tbody[i].st+'</td>';
			
			print = "<button class=mybutton onclick=masterPDF('log_poht','"+data.tbody[i].nopo+"','','log_slave_print_detail_po',event)>"+data.tbody[i].print+"</button>";
			html += '<td align=center>'+ print +'</td>';	
			html += '<td align=center>'+ data.tbody[i].gudang+'</td>';			
		}
		
		htmlfoot = '<tr class=rowheader><td colspan=10 align=center>';
		htmlfoot += ((parseInt(data.tfoot.page)*parseInt(data.tfoot.limit))+1)+' to '+((parseInt(data.tfoot.page)+1)*parseInt(data.tfoot.limit))+' Of '+data.tfoot.jlhbrs;
		htmlfoot += '<br /><button class=mybutton onclick=cariPage('+(parseInt(data.tfoot.page)-1)+');>'+data.tfoot.pref+'</button>';
		htmlfoot += '<button class=mybutton onclick=cariPage('+(parseInt(data.tfoot.page)+1)+');>'+data.tfoot.lanjut+'</button>';
		htmlfoot += '<input type=hidden id="'+data.tfoot.nopp_no+'" name="'+data.tfoot.nopp_no+'" value="'+data.tfoot.nopp+'" />';
		htmlfoot += '</td></tr>';
		//Mengirim HTML by ID  - author : Atwal
		contain.innerHTML = html;
		foot.innerHTML = htmlfoot;
	}else{
		contain.innerHTML = "<tr class=rowheader><td colspan=9 align=left>Data Kosong</td></tr>";
	}
}
/*function cariBast(num)
{
		param='method=loadData';
		param+='&page='+num;
		tujuan = 'log_slave_cetak_po.php';
		post_response_text(tujuan, param, respog);			
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('contain').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}*/
function cariPage(num)
{
		txtSearch=trim(document.getElementById('txtsearch').value);
		tglCari=trim(document.getElementById('tgl_cari').value);
		statusreal=trim(document.getElementById('statusreal').value);
		nmsupplier=trim(document.getElementById('nmsupplier').value);
		met=document.getElementById('method');
		met=met.value='list_new_data';
		param='txtSearch='+txtSearch+'&tglCari='+tglCari+'&statusreal='+statusreal+'&nmsupplier='+nmsupplier+'&method='+met;
		param+='&page='+num;
		tujuan = 'log_slave_cetak_po.php';
		post_response_text(tujuan, param, respog);			
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						//document.getElementById('contain').innerHTML=con.responseText;
						//Get data convert to json - author : Atwal
						data = JSON.parse(con.responseText);
						//Go to function create html  - author : Atwal
						load_data_exc(data);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}