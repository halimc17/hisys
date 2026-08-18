function loadAllTabData(){
	cariNoTransaksi = document.getElementById('cariNoTransaksi').value;
	cariNoTransaksi2 = document.getElementById('cariNoTransaksi2').value;
	param='method=loadAllTabData';
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					isdt=con.responseText.split("####");
					document.getElementById('containData').innerHTML=isdt[0];
					document.getElementById('footData').innerHTML=isdt[1];
					document.getElementById('containData2').innerHTML=isdt[2];
					document.getElementById('footData2').innerHTML=isdt[3];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

/* ################ BEGIN TAB SENSUS ################ */
function defaultList(){
	document.getElementById('frm1').style.display = '';
	document.getElementById('frmDetail1').style.display = 'none';
	document.getElementById('cariNoTransaksi').value = '';
	loadData(0);
}

function loadData(page){
	document.getElementById('frm1').style.display = '';
	document.getElementById('frmDetail1').style.display = 'none';
	cariNoTransaksi = document.getElementById('cariNoTransaksi').value;
	param='method=loadData'+'&page='+page;
	if(cariNoTransaksi!=''){
        param+='&cariNoTransaksi='+cariNoTransaksi;
    }
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					isdt=con.responseText.split("####");
					document.getElementById('containData').innerHTML=isdt[0];
					document.getElementById('footData').innerHTML=isdt[1];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function sus_dt_batal(){
	document.getElementById('sus_dt_method').value = 'sus_dt_insert';
	document.getElementById('sus_dt_jenishama').selectedIndex = 0;
	document.getElementById('sus_dt_jumlah').value = '0';
	document.getElementById('sus_satuan').innerHTML = '';
	document.getElementById('sus_dt_jenishama').disabled = false;
}

function af_sus_ht_simpan(){
	document.getElementById('sus_ht_srcnotransaksi').style.display = 'none';
	document.getElementById('sus_ht_save').style.display = 'none';
	document.getElementById('sus_ht_notransaksi').disabled = true;
	document.getElementById('sus_ht_tanggal').disabled = true;
}

function sus_fillfield(nosensus,notransaksi,tanggal,blok,luas){
	document.getElementById('frm1').style.display = 'none';
	document.getElementById('frmDetail1').style.display = '';
	document.getElementById('sus_ht_nosensus').value = nosensus;
	document.getElementById('sus_ht_notransaksi').value = notransaksi;
	document.getElementById('sus_ht_tanggal').value = tanggal;
	document.getElementById('sus_ht_blok').value = blok;
	document.getElementById('sus_ht_luas').value = luas;
	
	af_sus_ht_simpan();
	document.getElementById('sus_dt_frm').style.display = '';
	sus_dt_batal();
	sus_dt_list();
}

function sus_dt_list(){
	sus_ht_nosensus = document.getElementById('sus_ht_nosensus').value;
	param='method=sus_dt_loaddata&sus_ht_nosensus='+sus_ht_nosensus;
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					document.getElementById('sus_dt_list').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showAdd(){
	today = new Date();
	dd = ("0" + today.getDate()).slice(-2);
	mm = ("0" + (today.getMonth() + 1)).slice(-2);
	yyyy = today.getFullYear();
	
	document.getElementById('frm1').style.display = 'none';
	document.getElementById('frmDetail1').style.display = '';
	
	document.getElementById('sus_ht_nosensus').value = '';	
	document.getElementById('sus_ht_srcnotransaksi').style.display = '';
	document.getElementById('sus_ht_save').style.display = '';
	document.getElementById('sus_ht_notransaksi').value = '';
	document.getElementById('sus_ht_tanggal').value = dd+'-'+mm+'-'+yyyy;
	document.getElementById('sus_ht_blok').value = '';	
	document.getElementById('sus_ht_luas').value = '';	
	document.getElementById('sus_ht_method').value = 'sus_ht_insert';		
	
	document.getElementById('sus_ht_notransaksi').disabled = false;
	document.getElementById('sus_ht_tanggal').disabled = false;
	
	document.getElementById('sus_dt_frm').style.display = 'none';
	sus_dt_batal();
	sus_dt_list();
}

function delData(nosensus){
	param='method=delData&nosensus='+nosensus;
	tujuan='kebun_slave_hpt.php';
	if(confirm("Anda yakin menghapus no item ini? "+ nosensus)){
		post_response_text(tujuan+'?'+'', param, respog);
	}	
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					loadData();
					getPage2();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sus_ht_simpan(){
	sus_ht_nosensus = document.getElementById('sus_ht_nosensus').value;
	sus_ht_notransaksi = document.getElementById('sus_ht_notransaksi').value;
	sus_ht_tanggal = document.getElementById('sus_ht_tanggal').value;
	sus_ht_blok = document.getElementById('sus_ht_blok').value
	sus_ht_method = document.getElementById('sus_ht_method').value
	
	param='sus_ht_nosensus='+sus_ht_nosensus+'&sus_ht_notransaksi='+sus_ht_notransaksi+'&sus_ht_tanggal='+sus_ht_tanggal+'&sus_ht_blok='+sus_ht_blok;
	param+='&method='+sus_ht_method;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					// isdt=con.responseText.split("####");
					document.getElementById('sus_ht_nosensus').value = con.responseText;
					af_sus_ht_simpan();
					document.getElementById('sus_dt_frm').style.display = '';
					sus_dt_batal();
					// document.getElementById('footData').innerHTML=isdt[1];
					// document.getElementById('containData2').innerHTML=isdt[2];
					// document.getElementById('footData2').innerHTML=isdt[3];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sus_dt_simpan(){
	sus_ht_nosensus = document.getElementById('sus_ht_nosensus').value;
	sus_dt_jenishama = document.getElementById('sus_dt_jenishama').value;
	sus_dt_jumlah = document.getElementById('sus_dt_jumlah').value;
	sus_dt_method = document.getElementById('sus_dt_method').value;
	
	param='sus_ht_nosensus='+sus_ht_nosensus+'&sus_dt_jenishama='+sus_dt_jenishama+'&sus_dt_jumlah='+sus_dt_jumlah;
	param+='&method='+sus_dt_method;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{					
					sus_dt_batal();
					sus_dt_list();
					// document.getElementById('sus_ht_nosensus').value = con.responseText;
					// af_sus_ht_simpan();
					// document.getElementById('sus_dt_frm').style.display = '';
					// sus_dt_batal();
					// document.getElementById('footData').innerHTML=isdt[1];
					// document.getElementById('containData2').innerHTML=isdt[2];
					// document.getElementById('footData2').innerHTML=isdt[3];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sus_dt_delete(kodehama){
	sus_ht_nosensus = document.getElementById('sus_ht_nosensus').value;
	param='method=sus_dt_delete&sus_ht_nosensus='+sus_ht_nosensus+'&sus_dt_jenishama='+kodehama;
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					sus_dt_list();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function sus_dt_fillfield(kodehama,jumlah){
	document.getElementById('sus_dt_jenishama').disabled = true;
	document.getElementById('sus_dt_jenishama').value = kodehama;
	document.getElementById('sus_dt_jumlah').value = jumlah;
	document.getElementById('sus_dt_method').value = "sus_dt_update";
}
/* ################ END TAB SENSUS ################ */

/* ################ BEGIN TAB PENANGGULANGAN ################ */
function defaultList2(){
	document.getElementById('frm2').style.display = '';
	document.getElementById('frmDetail2').style.display = 'none';
	document.getElementById('cariNoTransaksi2').value = '';
	loadData2(0);
}

function loadData2(page){
	cariNoTransaksi2 = document.getElementById('cariNoTransaksi2').value;
	param='method=loadData2'+'&page2='+page;
	if(cariNoTransaksi2!=''){
        param+='&cariNoTransaksi2='+cariNoTransaksi2;
    }
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					isdt=con.responseText.split("####");
					document.getElementById('containData2').innerHTML=isdt[0];
					document.getElementById('footData2').innerHTML=isdt[1];
					loadData(0);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage2(){
    pg=document.getElementById('pages2');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData2(paged);	
}

function png_dt_batal(){
	document.getElementById('png_dt_method').value = 'png_dt_insert';
	document.getElementById('png_dt_jenishama').selectedIndex = 0;
	document.getElementById('png_dt_jumlah').value = '0';
	document.getElementById('png_satuan').innerHTML = '';
	document.getElementById('png_dt_jenishama').disabled = false;
}

function af_png_ht_simpan(){
	document.getElementById('png_ht_srcNoSensus').style.display = 'none';
	document.getElementById('png_ht_srcNoTransaksi2').style.display = 'none';
	document.getElementById('png_ht_save').style.display = 'none';
	document.getElementById('png_ht_nosensus').disabled = true;
	document.getElementById('png_ht_notransaksi').disabled = true;
	document.getElementById('png_ht_tanggal').disabled = true;
}

function png_fillfield(nopenanggulangan,nosensus,notransaksi,tanggal,blok,luas){
	document.getElementById('frm2').style.display = 'none';
	document.getElementById('frmDetail2').style.display = '';
	document.getElementById('png_ht_nopenanggulangan').value = nopenanggulangan;
	document.getElementById('png_ht_nosensus').value = nosensus;
	document.getElementById('png_ht_notransaksi').value = notransaksi;
	document.getElementById('png_ht_tanggal').value = tanggal;
	document.getElementById('png_ht_blok').value = blok;
	document.getElementById('png_ht_luas').value = luas;
	
	af_png_ht_simpan();
	document.getElementById('png_dt_frm').style.display = '';
	png_dt_batal();
	png_dt_list();
}

function png_dt_list(){
	png_ht_nopenanggulangan = document.getElementById('png_ht_nopenanggulangan').value;
	param='method=png_dt_loaddata&png_ht_nopenanggulangan='+png_ht_nopenanggulangan;
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					document.getElementById('png_dt_list').innerHTML=con.responseText;
					getPage();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showAdd2(){
	today = new Date();
	dd = ("0" + today.getDate()).slice(-2);
	mm = ("0" + (today.getMonth() + 1)).slice(-2);
	yyyy = today.getFullYear();
	
	document.getElementById('frm2').style.display = 'none';
	document.getElementById('frmDetail2').style.display = '';
	
	document.getElementById('png_ht_nopenanggulangan').value = '';	
	document.getElementById('png_ht_nosensus').value = '';	
	document.getElementById('png_ht_nosensus').disabled = false;	
	document.getElementById('png_ht_srcNoSensus').style.display = '';
	document.getElementById('png_ht_srcNoTransaksi2').style.display = '';
	document.getElementById('png_ht_save').style.display = '';
	document.getElementById('png_ht_notransaksi').value = '';
	document.getElementById('png_ht_tanggal').value = dd+'-'+mm+'-'+yyyy;
	document.getElementById('png_ht_blok').value = '';	
	document.getElementById('png_ht_luas').value = '';	
	document.getElementById('png_dt_method').value = 'png_dt_insert';		
	
	document.getElementById('png_ht_notransaksi').disabled = false;
	document.getElementById('png_ht_tanggal').disabled = false;
	
	document.getElementById('png_dt_frm').style.display = 'none';
	png_dt_batal();
	png_dt_list();
}

function delData2(nopenanggulangan){
	param='method=delData2&nopenanggulangan='+nopenanggulangan;
	tujuan='kebun_slave_hpt.php';
	if(confirm("Anda yakin menghapus no item ini? "+ nopenanggulangan)){
		post_response_text(tujuan+'?'+'', param, respog);
	}	
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					getPage2();
					batal2();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function png_ht_simpan(){
	png_ht_nopenanggulangan = document.getElementById('png_ht_nopenanggulangan').value;
	png_ht_nosensus = document.getElementById('png_ht_nosensus').value;
	png_ht_notransaksi = document.getElementById('png_ht_notransaksi').value;
	png_ht_tanggal = document.getElementById('png_ht_tanggal').value
	png_ht_blok = document.getElementById('png_ht_blok').value
	png_ht_method = document.getElementById('png_ht_method').value
	
	param='png_ht_nopenanggulangan='+png_ht_nopenanggulangan+'&png_ht_nosensus='+png_ht_nosensus+'&png_ht_notransaksi='+png_ht_notransaksi+'&png_ht_tanggal='+png_ht_tanggal+'&png_ht_blok='+png_ht_blok;
	param+='&method='+png_ht_method;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					document.getElementById('png_ht_nopenanggulangan').value = con.responseText;
					af_png_ht_simpan();
					document.getElementById('png_dt_frm').style.display = '';
					png_dt_batal();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function png_dt_simpan(){
	png_ht_nopenanggulangan = document.getElementById('png_ht_nopenanggulangan').value;
	png_ht_nosensus = document.getElementById('png_ht_nosensus').value;
	png_dt_jenishama = document.getElementById('png_dt_jenishama').value;
	png_dt_jumlah = document.getElementById('png_dt_jumlah').value;
	png_dt_method = document.getElementById('png_dt_method').value;
	
	param='png_ht_nopenanggulangan='+png_ht_nopenanggulangan+'&png_dt_jenishama='+png_dt_jenishama+'&png_dt_jumlah='+png_dt_jumlah+'&png_ht_nosensus='+png_ht_nosensus;
	param+='&method='+png_dt_method;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{					
					png_dt_batal();
					png_dt_list();
					// document.getElementById('sus_ht_nosensus').value = con.responseText;
					// af_sus_ht_simpan();
					// document.getElementById('sus_dt_frm').style.display = '';
					// sus_dt_batal();
					// document.getElementById('footData').innerHTML=isdt[1];
					// document.getElementById('containData2').innerHTML=isdt[2];
					// document.getElementById('footData2').innerHTML=isdt[3];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function png_dt_delete(kodehama){
	png_ht_nopenanggulangan = document.getElementById('png_ht_nopenanggulangan').value;
	param='method=png_dt_delete&png_ht_nopenanggulangan='+png_ht_nopenanggulangan+'&png_dt_jenishama='+kodehama;
	tujuan='kebun_slave_hpt.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{
					png_dt_list();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function png_dt_fillfield(kodehama,jumlah){
	document.getElementById('png_dt_jenishama').disabled = true;
	document.getElementById('png_dt_jenishama').value = kodehama;
	document.getElementById('png_dt_jumlah').value = jumlah;
	document.getElementById('png_dt_method').value = "png_dt_update";
}
/* ################ END TAB PENANGGULANGAN ################ */



/* ################ BEGIN POP UP ################ */
/* BEGIN TAB SENSUS */
function getPopUpNoTransaksi(title,content,ev){
	width='';
	height='';
	showDialog2(title,content,width,height,ev);
	
    getFormNoTransaksi();
}

function getFormNoTransaksi(){
	param='method=getFormNoTransaksi';
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('formPencarianTransaksi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function csearch(){
	cnotransaksi = document.getElementById('cnotransaksi').value;
	param='method=csearch&cnotransaksi='+cnotransaksi;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('listnotransaksi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillfield(notransaksi,blok,luas){
	document.getElementById('sus_ht_notransaksi').value = notransaksi;
	document.getElementById('sus_ht_blok').value = blok;
	document.getElementById('sus_ht_luas').value = luas;
	closeDialog();
}
/* END TAB SENSUS */

/* BEGIN TAB PENANGGULANGAN */
function getPopUpNoSensus(title,content,ev){
	width='';
	height='';
	showDialog1(title,content,width,height,ev);
	
    getFormNoSensus();
}

function getFormNoSensus(){
	param='method=getFormNoSensus';
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('formPencarianTransaksi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function csearchsensus(){
	cnosensus = document.getElementById('cnosensus').value;
	param='method=csearchsensus&cnosensus='+cnosensus;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('listnosensus').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillSearchSensus(nosensus){
	document.getElementById('png_ht_nosensus').value = nosensus;
	closeDialog();
}

function getPopUpNoTransaksi2(title,content,ev){
	width='';
	height='';
	showDialog5(title,content,width,height,ev);
	
    getFormNoTransaksi2();
}

function getFormNoTransaksi2(){
	param='method=getFormNoTransaksi2';
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('formPencarianTransaksi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function csearch2(){
	cnotransaksi2 = document.getElementById('cnotransaksi2').value;
	param='method=csearch2&cnotransaksi2='+cnotransaksi2;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('listnotransaksi2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillfield2(notransaksi,blok,luas){
	document.getElementById('png_ht_notransaksi').value = notransaksi;
	document.getElementById('png_ht_blok').value = blok;
	document.getElementById('png_ht_luas').value = luas;
	closeDialog();
}
/* END TAB PENANGGULANGAN */

function detailSensus(title,nosensus,content,ev){
	width='650';
	height='400';
	showDialog1(title,content,width,height,ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0] - width) +'px';
	document.getElementById('dynamic1').style.display='';
    getDetailSensus(nosensus);
}

function getDetailSensus(nosensus){
	param='method=getDetailSensus&nosensus='+nosensus;
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else{
					//alert(con.responseText);
					document.getElementById('formPencarianTransaksi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
/* ################ END POP UP ################ */
function display_number(id,evt){
	// alert(id);
	txb = document.getElementById(id);
	if(txb.value == ''){
		txb.value = 0;
	}     
}

function sus_change_satuan(){
	sus_dt_jenishama = document.getElementById('sus_dt_jenishama').value;
	param='sus_dt_jenishama='+sus_dt_jenishama;
	param+='&method=sus_change_satuan';
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{					
					document.getElementById('sus_satuan').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function png_change_satuan(){
	png_dt_jenishama = document.getElementById('png_dt_jenishama').value;
	param='png_dt_jenishama='+png_dt_jenishama;
	param+='&method=png_change_satuan';
	tujuan='kebun_slave_hpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}
				else{					
					document.getElementById('png_satuan').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}