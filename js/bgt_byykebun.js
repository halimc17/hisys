function exportTableToExcel(tableID){
	var filename = tableID;
	
	var downloadLink;
	var dataType = 'application/vnd.ms-excel';
	var tableSelect = document.getElementById(tableID);
		tableSelect.border='1';
		
	var x = tableSelect.querySelectorAll(".rowcontent"); 
	for(i=0;i<x.length;i++){
		x[i].style.display = "";
		r = x[i].getElementsByTagName("td");
		for(e=0;e<r.length;e++){
			r[e].style.backgroundColor = "";
		}
	}
		
	var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

	filename = filename?filename+'.xls':'excel_data.xls';
	downloadLink = document.createElement("a");
	document.body.appendChild(downloadLink);

	if(navigator.msSaveOrOpenBlob){
		var blob = new Blob(['\ufeff', tableHTML], {
			type: dataType
		});
		navigator.msSaveOrOpenBlob( blob, filename);
	}else{
		downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
		downloadLink.download = filename;
		downloadLink.click();
	}
}

function formatupload() {
	tahun     = document.getElementById('tahun').value;
	kodeorg   = document.getElementById('kodeorg').value;
	
	param  = 'tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&method=formatupload';
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Download Form",con.responseText).set({'resizable':false,'maximizable':false}); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hapuspersen(){
	for(i=1;i<=12;i++){
		document.getElementById('persen_'+i).value=0;
	}
}

function setdata(kdbrg, nama, sat,rupiah) {
	sumber = document.getElementById('sumbermat').value;
	if(sumber=='mat'){		
		document.getElementById('hargamat').value = rupiah;
		document.getElementById('kodebarang').value = kdbrg;
		document.getElementById('namabarang').innerHTML = nama;
		document.getElementById('satuanmat').innerHTML = sat;
		getharga(sumber);
	}
	if(sumber=='alat'){
		document.getElementById('hargaalat').value = rupiah;
		document.getElementById('kodebarangalat').value = kdbrg;
		document.getElementById('namabarangalat').innerHTML = nama;
		document.getElementById('satuanalat').innerHTML = sat;
		getharga(sumber);
	}
	closeDialog();
}

function caribarang(sumber) {
	tahun     = document.getElementById('tahun').value;
	kodeorg   = document.getElementById('kodeorg').value;
	kodebarang= document.getElementById('kodebarangcari').value;
	sumber    = document.getElementById('sumbermat').value;
	if(sumber=='mat'){
		klbarang  = document.getElementById('kodebarang').value;		
	}
	if(sumber=='alat'){
		klbarang  = document.getElementById('kodebarangalat').value;
	}

	
	param  = 'kodebarang=' + kodebarang + '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&klbarang=' + klbarang;
	param += '&sumber=' + sumber;
	param += '&method=caribarang';
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contcaribarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formcaribarang(sumber) {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd style=\"max-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	if(sumber=='mat'){
		klbarang= document.getElementById('kdbudgetmat').value;
		klbrg = klbarang.substr(2,3);
		document.getElementById('kodebarang').value=klbrg;
		
		document.getElementById('hargamat').value = '';
		document.getElementById('ttlbyymat').value = '';
		document.getElementById('namabarang').innerHTML = '';
		document.getElementById('satuanmat').innerHTML = '';
	}
	if(sumber=='alat'){		
		klbarang= document.getElementById('klbarangalat').value;
		klbrg = klbarang.substr(2,3);
		document.getElementById('kodebarangalat').value=klbrg;
		
		document.getElementById('hargaalat').value = '';
		document.getElementById('ttlbyyalat').value = '';
		document.getElementById('namabarangalat').innerHTML = '';
		document.getElementById('satuanalat').innerHTML = '';
	}
	
	param  = 'klbarang=' + klbarang + '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&sumber=' + sumber;
	param += '&method=formcaribarang';
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
					caribarang(sumber);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editsdm(index,blok,aruskas,kdbudget,jhk,rupiah,volume,rotasi,fisik){
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	document.getElementById('aruskassdm').value=aruskas;
	document.getElementById('kdbudgetsdm').value=kdbudget;
	
	norma = parseFloat(jhk)/parseFloat(volume);
	document.getElementById('normasdm').value = numberFormat(norma,2);
	document.getElementById('jhksdm').value=jhk;
	document.getElementById('ttlbyysdm').value=rupiah;
	document.getElementById('index').value=index;
	if(index!=''){		
		document.getElementById('update').value='update';
		document.getElementById('blok').value=blok;
	}else{
		document.getElementById('update').value='';
		document.getElementById('blok').value='';
	}
}
function editmat(index,blok,aruskas,kdbudget,jlh,rupiah,volume,rotasi,fisik,kodebarang,namabarang,satuan){
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	document.getElementById('aruskasmat').value=aruskas;
	document.getElementById('kdbudgetmat').value=kdbudget;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('namabarang').innerHTML=namabarang;
	document.getElementById('satuanmat').innerHTML=satuan;
	
	norma = parseFloat(jlh)/parseFloat(volume);
	document.getElementById('normamat').value = numberFormat(norma,2);
	document.getElementById('jumlahmat').value=jlh;
	document.getElementById('hargamat').value = numberFormat(parseFloat(rupiah)/parseFloat(jlh),2);
	document.getElementById('ttlbyymat').value=rupiah;
	document.getElementById('index').value=index;
	if(index!=''){		
		document.getElementById('update').value='update';
		document.getElementById('blok').value=blok;
	}else{
		document.getElementById('update').value='';
		document.getElementById('blok').value='';
	}
}
function editalat(index,blok,aruskas,kdbudget,jlh,rupiah,volume,rotasi,fisik,kodebarang,namabarang,satuan){
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	document.getElementById('aruskasalat').value=aruskas;
	document.getElementById('kdbudgetalat').value=kdbudget;
	document.getElementById('klbarangalat').value="M-"+kodebarang.substr(0,3);
	document.getElementById('kodebarangalat').value=kodebarang;
	document.getElementById('namabarangalat').innerHTML=namabarang;
	document.getElementById('satuanalat').innerHTML=satuan;
	
	
	norma = parseFloat(jlh)/parseFloat(volume);
	document.getElementById('normaalat').value = numberFormat(norma,2);
	document.getElementById('jumlahalat').value=jlh;
	document.getElementById('hargaalat').value = numberFormat(parseFloat(rupiah)/parseFloat(jlh),2);
	document.getElementById('ttlbyyalat').value=rupiah;
	document.getElementById('index').value=index;
	if(index!=''){		
		document.getElementById('update').value='update';
		document.getElementById('blok').value=blok;
	}else{
		document.getElementById('update').value='';
		document.getElementById('blok').value='';
	}
}
function editkont(index,blok,aruskas,kdbudget,jlh,rupiah,volume,rotasi,fisik){
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	document.getElementById('aruskaskont').value=aruskas;
	document.getElementById('kodebudgetkont').value=kdbudget;
	
	norma = parseFloat(jlh)/parseFloat(volume)*100;
	document.getElementById('volpersen').value = numberFormat(norma,2);
	document.getElementById('volkont').value=jlh;
	document.getElementById('hargakontrak').value=numberFormat(rupiah/jlh,2);
	document.getElementById('ttlbyykont').value=rupiah;
	document.getElementById('satuankont').value=document.getElementById('satuan').value;
	
	document.getElementById('index').value=index;
	if(index!=''){		
		document.getElementById('update').value='update';
		document.getElementById('blok').value=blok;
	}else{
		document.getElementById('update').value='';
		document.getElementById('blok').value='';
	}
}
function editvhc(index,blok,aruskas,kdbudget,jlh,rupiah,volume,rotasi,fisik,kodevhc,satuan){
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	document.getElementById('aruskasvhc').value=aruskas;
	document.getElementById('kdbudgetvhc').value=kdbudget;
	document.getElementById('kodevhc').value=kodevhc;
	//setValue2('kodevhc',kodevhc);
	document.getElementById('satuanvhc').innerHTML=satuan;
	
	norma = parseFloat(jlh)/parseFloat(volume);
	document.getElementById('normavhc').value = numberFormat(norma,2);
	document.getElementById('jlhvhc').value=jlh;
	document.getElementById('ttlbyyvhc').value=rupiah;
	document.getElementById('index').value=index;
	if(index!=''){		
		document.getElementById('update').value='update';
		document.getElementById('blok').value=blok;
	}else{
		document.getElementById('update').value='';
		document.getElementById('blok').value='';
	}
}
function deldetail(sumber,tahun,divisi,tt,kdbudget,kegiatan,noakun,kodebarang,kodevhc){
	param  = 'method=deldetail';
	param += '&tahun=' + tahun + '&kdbudget=' + kdbudget;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&kodebarang=' + kodebarang;
	param += '&kodevhc=' + kodevhc;
	
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (sumber == 'sdm') {
						loaddatasdm();
					}
					if (sumber == 'mat') {
						loaddatamat();
					}
					if (sumber == 'alat') {
						loaddataalat();
					}
					if (sumber == 'kont') {
						loaddatakont();
					}
					if (sumber == 'vhc') {
						loaddatavhc();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delbyindex(index,sumber){
	param  = 'method=delbyindex';
	param += '&index=' + index;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (sumber == 'sdm'){
						loaddatasdm();
					}
					if (sumber == 'mat'){
						loaddatamat();
					}
					if (sumber == 'alat'){
						loaddataalat();
					}
					if (sumber == 'kont') {
						loaddatakont();
					}
					if (sumber == 'vhc') {
						loaddatavhc();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showhide(awal,akhir,sumber){
	if(sumber=='sdm'){
		rowid = 'row_';
		colid = 'plussdm';
	}
	if(sumber=='mat'){
		rowid = 'mat_';
		colid = 'plusmat';
	}
	if(sumber=='alat'){
		rowid = 'alat_';
		colid = 'plusalat';
	}
	if(sumber=='kont'){
		rowid = 'kont_';
		colid = 'pluskont';
	}
	if(sumber=='vhc'){
		rowid = 'vhc_';
		colid = 'plusvhc';
	}

	dis = document.getElementById(rowid+awal).getAttribute("style");
	if(dis=="display:none" || dis=="display: none;"){
		document.getElementById(colid+awal).innerHTML="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('"+awal+"','"+akhir+"','"+sumber+"');\">";
	}else{
		document.getElementById(colid+awal).innerHTML="<img src=images/menu/symbol_1.gif class=zImgBtn title='Expand' onclick=\"showhide('"+awal+"','"+akhir+"','"+sumber+"');\">";
	}
	
	awal = parseFloat(awal);
	akhir = parseFloat(akhir);
	for (var i=awal;i<=akhir;i++){
		if(dis=="display:none" || dis=="display: none;"){
			document.getElementById(rowid+i).style.display="";
		}else{			
			document.getElementById(rowid+i).style.display="none";
		}
	}
}


function getdatadetail(tipe,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan){
	if(tipe=='sdm'){
		loaddatasdm('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	if(tipe=='mat'){
		loaddatamat('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	if(tipe=='tool'){
		loaddataalat('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	if(tipe=='kont'){
		loaddatakont('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	if(tipe=='vhc'){
		loaddatavhc('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	
	if(tipe=='luas'){
		loaddataluas('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
	if(tipe=='prd'){
		loaddataprd('',tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,'popup');
	}
}

function loaddataluas(x,thndget,kodeorg, blkid){	
	
	param = 'proses=printExcel' + '&thnAngrn=' + thndget + '&sumber=LAMA&afdId=' + blkid+ '&jenis=html';
	
	tujuan = 'budget_slave_5blok.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function loaddataprd(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	param  = 'method=loaddataprd';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	// param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatasdm(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	if(tipe=='popup'){
		tahun   = tahun;
		kodeorg = kodeorg;
		divisi  = divisi;
		blok    = blok;
		tt      = tt;
		noakun  = noakun;
		kegiatan= kegiatan;
		jenis   = '';
	}else{		
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		divisi  = document.getElementById('divisi').value;
		tt      = document.getElementById('tt').value;
		noakun  = document.getElementById('noakun').value;
		kegiatan= document.getElementById('kegiatan').value;
		blok    = document.getElementById('blok').value;
		jenis   = document.getElementById('jenis').value;
	}
	
	
	
	param  = 'method=loaddatasdm';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					}else{						
						if(document.getElementById('listdatasdm')!=undefined){						
							document.getElementById('listdatasdm').innerHTML = con.responseText;
						}
						leftFixedTable();
						if(reloadall=='all'){
							loaddatamat(reloadall);
						}
					}
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatamat(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	if(tipe=='popup'){
		tahun   = tahun;
		kodeorg = kodeorg;
		divisi  = divisi;
		blok    = blok;
		tt      = tt;
		noakun  = noakun;
		kegiatan= kegiatan;
		jenis   = '';
	}else{		
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		divisi  = document.getElementById('divisi').value;
		tt      = document.getElementById('tt').value;
		noakun  = document.getElementById('noakun').value;
		kegiatan= document.getElementById('kegiatan').value;
		blok    = document.getElementById('blok').value;
		jenis    = document.getElementById('jenis').value;
	}
	
	param  = 'method=loaddatamat';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					}else{						
						if(document.getElementById('listdatamat')!=undefined){						
							document.getElementById('listdatamat').innerHTML = con.responseText;
						}
						leftFixedTable();
						if(reloadall=='all'){
							loaddataalat(reloadall);
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddataalat(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	if(tipe=='popup'){
		tahun   = tahun;
		kodeorg = kodeorg;
		divisi  = divisi;
		blok    = blok;
		tt      = tt;
		noakun  = noakun;
		kegiatan= kegiatan;
		jenis   = '';
	}else{	
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		divisi  = document.getElementById('divisi').value;
		tt      = document.getElementById('tt').value;
		noakun  = document.getElementById('noakun').value;
		kegiatan= document.getElementById('kegiatan').value;
		blok    = document.getElementById('blok').value;
		jenis    = document.getElementById('jenis').value;
	}

	
	param  = 'method=loaddataalat';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					}else{	
						if(document.getElementById('listdataalat')!=undefined){						
							document.getElementById('listdataalat').innerHTML = con.responseText;
						}
						leftFixedTable();
						if(reloadall=='all'){
							loaddatakont(reloadall);
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatakont(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	if(tipe=='popup'){
		tahun   = tahun;
		kodeorg = kodeorg;
		divisi  = divisi;
		blok    = blok;
		tt      = tt;
		noakun  = noakun;
		kegiatan= kegiatan;
		jenis   = '';
	}else{	
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		divisi  = document.getElementById('divisi').value;
		tt      = document.getElementById('tt').value;
		noakun  = document.getElementById('noakun').value;
		kegiatan= document.getElementById('kegiatan').value;
		blok    = document.getElementById('blok').value;
		jenis    = document.getElementById('jenis').value;
	}

	
	param  = 'method=loaddatakont';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					}else{	
						if(document.getElementById('listdatakont')!=undefined){						
							document.getElementById('listdatakont').innerHTML = con.responseText;
						}
						leftFixedTable();
						if(reloadall=='all'){
							loaddatavhc(reloadall);
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatavhc(reloadall,tahun,kodeorg,divisi,blok,tt,noakun,kegiatan,tipe) {
	if(tipe=='popup'){
		tahun   = tahun;
		kodeorg = kodeorg;
		divisi  = divisi;
		blok    = blok;
		tt      = tt;
		noakun  = noakun;
		kegiatan= kegiatan;
		jenis   = '';
	}else{	
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		divisi  = document.getElementById('divisi').value;
		tt      = document.getElementById('tt').value;
		noakun  = document.getElementById('noakun').value;
		kegiatan= document.getElementById('kegiatan').value;
		blok    = document.getElementById('blok').value;
		jenis    = document.getElementById('jenis').value;
	}

	
	param  = 'method=loaddatavhc';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tipe=='popup'){
						alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					}else{	
						if(document.getElementById('listdatavhc')!=undefined){						
							document.getElementById('listdatavhc').innerHTML = con.responseText;
						}
						leftFixedTable();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardatasdm(){
	document.getElementById('kdbudgetsdm').value='';
	document.getElementById('normasdm').value=0;
	document.getElementById('jhksdm').value=0;
	document.getElementById('ttlbyysdm').value=0;
}
function cleardatamat(){
	document.getElementById('normamat').value='';
	document.getElementById('jumlahmat').value=0;
	document.getElementById('ttlbyymat').value=0;
}
function cleardataalat(){
	document.getElementById('normaalat').value='';
	document.getElementById('jumlahalat').value=0;
	document.getElementById('ttlbyyalat').value=0;
}
function cleardatakont(){
	document.getElementById('volpersen').value='';
	document.getElementById('volkont').value=0;
	document.getElementById('hargakontrak').value=0;
	document.getElementById('ttlbyykont').value=0;
}
function cleardatavhc(){
	document.getElementById('normavhc').value='';
	document.getElementById('jlhvhc').value=0;
	document.getElementById('ttlbyyvhc').value=0;
	// document.getElementById('kodevhc').value='';
	//setValue2('kodevhc',null);
}
function simpandetail(sumber) {
	tahun      = document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	divisi     = document.getElementById('divisi').value;
	jenis      = document.getElementById('jenis').value;
	tt         = document.getElementById('tt').value;
	blok       = document.getElementById('blok').value;
	kegiatan   = document.getElementById('kegiatan').value;
	satuanv    = document.getElementById('satuan').value;
	volume     = document.getElementById('volume').value;
	rotasi     = document.getElementById('rotasi').value;
	totalvolume= document.getElementById('totalvolume').value;
	update     = document.getElementById('update').value;
	index      = document.getElementById('index').value;
	
	param  = '';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	param += '&kegiatan=' + kegiatan;
	param += '&satuanv=' + satuanv;
	param += '&volume=' + volume;
	param += '&rotasi=' + rotasi;
	param += '&totalvolume=' + totalvolume;
	param += '&update=' + update;
	param += '&index=' + index;
	
	if (sumber == 'sdm') {
		aruskas = document.getElementById('aruskassdm').value;
		kdbudget= document.getElementById('kdbudgetsdm').value;
		hke     = document.getElementById('hkesdm').value;
		norma   = document.getElementById('normasdm').value;
		jhk     = document.getElementById('jhksdm').value;
		rupiah  = document.getElementById('ttlbyysdm').value;
		
		param += '&method=simpansdm';
		param += '&kdbudget=' + kdbudget + '&hke=' + hke + '&norma=' + norma + '&jhk=' + jhk+ '&rupiah=' + rupiah;
	}
	if (sumber == 'mat') {
		aruskas   = document.getElementById('aruskasmat').value;
		kdbudget  = document.getElementById('kdbudgetmat').value;
		kodebarang= document.getElementById('kodebarang').value;
		norma     = document.getElementById('normamat').value;
		jumlah    = document.getElementById('jumlahmat').value;
		rupiah    = document.getElementById('ttlbyymat').value;
		satuan    = document.getElementById('satuanmat').innerHTML;
		
		param += '&method=simpanmat';
		param += '&satuan=' + satuan;
		param += '&norma=' + norma;
		param += '&kdbudget=' + kdbudget + '&kodebarang=' + kodebarang + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
	}
	if (sumber == 'alat') {
		aruskas   = document.getElementById('aruskasalat').value;
		kdbudget  = document.getElementById('kdbudgetalat').value;
		kodebarang= document.getElementById('kodebarangalat').value;
		norma     = document.getElementById('normaalat').value;
		jumlah    = document.getElementById('jumlahalat').value;
		rupiah    = document.getElementById('ttlbyyalat').value;
		satuan    = document.getElementById('satuanalat').innerHTML;
		
		param += '&method=simpanalat';
		param += '&satuan=' + satuan;
		param += '&norma=' + norma;
		param += '&kdbudget=' + kdbudget + '&kodebarang=' + kodebarang + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
		
	}
	if (sumber == 'kont') {
		aruskas   = document.getElementById('aruskaskont').value;
		kdbudget  = document.getElementById('kodebudgetkont').value;
		jumlah    = document.getElementById('volkont').value;
		rupiah    = document.getElementById('ttlbyykont').value;
		satuan    = document.getElementById('satuankont').value;
		
		param += '&method=simpankont';
		param += '&satuan=' + satuan;
		param += '&kdbudget=' + kdbudget  + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
		
	}
	if (sumber == 'vhc') {
		aruskas   = document.getElementById('aruskasvhc').value;
		kdbudget  = document.getElementById('kdbudgetvhc').value;
		kodevhc    = document.getElementById('kodevhc').value;
		jumlah    = document.getElementById('jlhvhc').value;
		rupiah    = document.getElementById('ttlbyyvhc').value;
		satuan    = document.getElementById('satuanvhc').innerHTML;
		
		param += '&method=simpanvhc';
		param += '&satuan=' + satuan;
		param += '&kodevhc=' + kodevhc;
		param += '&kdbudget=' + kdbudget  + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
		
	}
	param += '&aruskas=' + aruskas;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('update').value='';
					document.getElementById('index').value='';
					if(index!=''){reloadall='all';}else{reloadall='';}
					
					if (sumber == 'sdm') {
						cleardatasdm();
						loaddatasdm(reloadall);
					}
					if (sumber == 'mat') {
						cleardatamat();
						loaddatamat(reloadall);
					}
					if (sumber == 'alat') {
						cleardataalat();
						loaddataalat(reloadall);
					}
					if (sumber == 'kont') {
						cleardatakont();
						loaddatakont(reloadall);
					}
					if (sumber == 'vhc') {
						cleardatavhc();
						loaddatavhc(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getharga(sumber,id){
	tahun      = document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	rotasi     = document.getElementById('rotasi').value;
	totalvolume= document.getElementById('totalvolume').value;
	param     = 'tahun=' + tahun + '&kodeorg=' + kodeorg;
	param    += '&rotasi=' + rotasi + '&totalvolume=' + totalvolume;
	
	if (sumber == 'sdm') {
		hke = document.getElementById('hkesdm').value;
		if(id=='jhksdm'){
			jhk  = document.getElementById('jhksdm').value;
			norma= parseFloat(totalvolume)/parseFloat(jhk);
			document.getElementById('normasdm').value=numberFormat(norma,2);
		}else{
			norma= document.getElementById('normasdm').value;
			jhk  = parseFloat(totalvolume)*parseFloat(norma);
			document.getElementById('jhksdm').value=numberFormat(jhk,2);
			if (jhk == '') {
				document.getElementById('jhksdm').value = '0';
			}			
		}
		
		kdbudget = document.getElementById('kdbudgetsdm').value;
		param += '&method=getupah' + '&jhk=' + jhk + '&kdbudget=' + kdbudget + '&hke=' + hke;
	}
	if (sumber == 'mat') {
		harga    = document.getElementById('hargamat').value;
		harga= remove_comma_var(harga);
		if(id=='jumlahmat'){
			jumlahmat= document.getElementById('jumlahmat').value;
			norma    = parseFloat(totalvolume)/parseFloat(jumlahmat);
			rp       = parseFloat(harga)*parseFloat(jumlahmat);
			if(isNaN(norma)){norma=0;}
			document.getElementById('normamat').value = numberFormat(norma,2);
		}else{			
			norma= document.getElementById('normamat').value;
			jlh  = parseFloat(totalvolume)*parseFloat(norma);
			rp   = parseFloat(harga)*parseFloat(jlh);
			if(isNaN(jlh)){jlh=0;}
			document.getElementById('jumlahmat').value = numberFormat(jlh,2);
		}
		
		if(isNaN(rp)){rp=0;}
		document.getElementById('ttlbyymat').value = numberFormat(rp);
	}
	if (sumber == 'alat') {
		harga= document.getElementById('hargaalat').value;
		harga= remove_comma_var(harga);
		if(id=='jumlahalat'){
			jlh  = document.getElementById('jumlahalat').value;
			norma= parseFloat(totalvolume)/parseFloat(jlh);
			rp   = parseFloat(harga)*parseFloat(jlh);
			if(isNaN(norma)){norma=0;}
			document.getElementById('normaalat').value = numberFormat(norma,2);			
		}else{
			norma= document.getElementById('normaalat').value;
			jlh  = parseFloat(totalvolume)*parseFloat(norma);
			rp   = parseFloat(harga)*parseFloat(jlh);
			if(isNaN(jlh)){jlh=0;}
			document.getElementById('jumlahalat').value = numberFormat(jlh,2);			
		}
		if(isNaN(rp)){rp=0;}
		document.getElementById('ttlbyyalat').value = numberFormat(rp);
	}
	if (sumber == 'kont') {
		volpersen   = document.getElementById('volpersen').value;
		satuan      = document.getElementById('satuan').value;
		hargakontrak= document.getElementById('hargakontrak').value;
		hargakontrak= remove_comma_var(hargakontrak);
		volpersen   = remove_comma_var(volpersen);
		if(volpersen>100){
			alertify.alert("Tidak boleh lebih dari 100 %"); 
			document.getElementById('volpersen').value='';
			document.getElementById('ttlbyykont').value='';
			return;
		}
		
		totalvol = parseFloat(volpersen)*parseFloat(totalvolume)/100;
		totalrp = parseFloat(hargakontrak)*parseFloat(totalvol);
		if(isNaN(totalvol)){totalvol=0;}
		if(isNaN(totalrp)){totalrp=0;}
		document.getElementById('satuankont').value=satuan;
		document.getElementById('volkont').value=totalvol;
		document.getElementById('ttlbyykont').value=numberFormat(totalrp);
	}
	
	if (sumber == 'vhc') {
		kodevhc = document.getElementById('kodevhc').value;
		kdbudget= document.getElementById('kdbudgetvhc').value;
		param += '&method=gethargavhc' + '&kodevhc=' + kodevhc + '&kdbudget=' + kdbudget;
	}
	
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (sumber == 'sdm') {
						document.getElementById('ttlbyysdm').value = trim(con.responseText);
					}
					if (sumber == 'vhc') {
						if(id=='norma'){
							norma= document.getElementById('normavhc').value;
							jlh   = parseFloat(totalvolume)*parseFloat(norma);
							if(isNaN(jlh)){jlh=0;}
							document.getElementById('jlhvhc').value=numberFormat(jlh,2);
						}else{
							jlh= document.getElementById('jlhvhc').value;
							nor  = parseFloat(jlh)/parseFloat(totalvolume);
							if(isNaN(nor)){nor=0;}
							document.getElementById('normavhc').value=numberFormat(nor,2);
						}
						
						data = con.responseText.split("####");
						rp = parseFloat(trim(data[0]))*parseFloat(jlh);
						if(isNaN(rp)){rp=0;}
						document.getElementById('ttlbyyvhc').value = numberFormat(rp);
						document.getElementById('satuanvhc').innerHTML = trim(data[1]);
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function disableheader(){
	document.getElementById('tahun').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('divisi').disabled = true;
	document.getElementById('tt').disabled = true;
	document.getElementById('jenis').disabled = true;
	document.getElementById('blok').disabled = true;
	document.getElementById('kegiatan').disabled = true;
	document.getElementById('satuan').disabled = true;
	document.getElementById('volume').disabled = true;
	document.getElementById('rotasi').disabled = true;
	document.getElementById('totalvolume').disabled = true;
}
function enableheader(){
	document.getElementById('tahun').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('divisi').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('tt').disabled = false;
	document.getElementById('blok').disabled = false;
	document.getElementById('kegiatan').disabled = false;
	document.getElementById('volume').disabled = false;
	document.getElementById('rotasi').disabled = false;
	document.getElementById('totalvolume').disabled = false;
}
function simpanheader() {
	tahun      = document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	divisi     = document.getElementById('divisi').value;
	tt         = document.getElementById('tt').value;
	jenis      = document.getElementById('jenis').value;
	blok       = document.getElementById('blok').value;
	kegiatan   = document.getElementById('kegiatan').value;
	satuan     = document.getElementById('satuan').value;
	volume     = document.getElementById('volume').value;
	rotasi     = document.getElementById('rotasi').value;
	totalvolume= document.getElementById('totalvolume').value;
	
	param  = 'method=simpanheader';
	param += '&tahun=' + tahun;
	param += '&jenis=' + jenis;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	param += '&satuan=' + satuan;
	param += '&volume=' + volume;
	param += '&rotasi=' + rotasi;
	param += '&totalvolume=' + totalvolume;
	
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("###");
					document.getElementById('hkesdm').value = data[0];
					document.getElementById('kodevhc').innerHTML = data[1];
					document.getElementById('kdbudgetsdm').innerHTML = data[2];
					document.getElementById('aruskassdm').innerHTML = data[3];
					document.getElementById('aruskasmat').innerHTML = data[3];
					document.getElementById('aruskasalat').innerHTML = data[3];
					document.getElementById('aruskaskont').innerHTML = data[3];
					document.getElementById('aruskasvhc').innerHTML = data[3];
					disableheader();
					
					loaddatasdm('all');
					
					cleardatasdm();
					cleardatamat();
					cleardataalat();
					cleardatakont();
					cleardatavhc();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function gettotalfisik(sumber){
	fis = document.getElementById('volume').value;
	rot = document.getElementById('rotasi').value;
	ttl = document.getElementById('totalvolume').value;
	
	
	if(sumber=='fis' || sumber=='rot'){
		if(fis!='' && rot !=''){
			document.getElementById('totalvolume').value=parseFloat(fis)*parseFloat(rot);
		}else if(ttl!='' && rot !=''){
			document.getElementById('volume').value=parseFloat(ttl)/parseFloat(rot);
		}else{
			document.getElementById('totalvolume').value=0;
		}
	}else if(sumber=='ttl'){
		if(ttl!='' && fis !=''){
			hasil = parseFloat(ttl)/parseFloat(fis);
			document.getElementById('rotasi').value=hasil;
		}else if(ttl!='' && rot !=''){
			hasil = parseFloat(ttl)/parseFloat(rot);
			document.getElementById('volume').value=hasil;
		}else{
			document.getElementById('rotasi').value=0;
		}
	}
}
function preview(tahunbudget,divisi,thntnm,kegiatan,tipe){	
	// width = '';
	// height = '';
	// content = "<fieldset><div id=contpreview align=center style=\"max-width:1000px;max-height:500px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	
	param  = 'method=rekapperblok';
	param += '&tahun=' + tahunbudget;
	param += '&divisi=' + divisi;
	param += '&tt=' + thntnm;
	param += '&kegiatan=' + kegiatan;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('contpreview').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function preview2(tahunbudget,divisi,thntnm,kegiatan,tipe){	
	width = '';
	height = '';
	content = "<fieldset style=\"width:1000px;\"><div id=contpreview2 align=center style=\"width:1000px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);
	
	param  = 'method=rekapperblok';
	param += '&tahun=' + tahunbudget;
	param += '&divisi=' + divisi;
	param += '&tt=' + thntnm;
	param += '&kegiatan=' + kegiatan;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contpreview2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(tahunbudget,divisi,tt,kegiatan) {
	param = 'method=del';
	param += '&tahun=' + tahunbudget;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&kegiatan=' + kegiatan;
	tujuan = 'bgt_slave_byykebun.php';
	alertify.confirm("Warning","Anda yakin ingin menghapus ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delrekapblok(tahunbudget,blok,kegiatan) {
	param = 'method=delrekapblok';
	param += '&tahun=' + tahunbudget;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	tujuan = 'bgt_slave_byykebun.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					thntnm = con.responseText;
					preview(tahunbudget,blok.substr(0,6),thntnm,kegiatan,'html');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(tahunbudget,divisi) {
	param = 'method=unposting';
	param += '&tahun=' + tahunbudget;
	param += '&divisi=' + divisi;
	tujuan = 'bgt_slave_byykebun.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(tahunbudget,divisi){
	param = 'method=posting';
	param += '&tahun=' + tahunbudget;
	param += '&divisi=' + divisi;
	tujuan = 'bgt_slave_byykebun.php';
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					showposting();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function batalheader(){
	//document.getElementById('kodeorg').value='';
	//document.getElementById('divisi').value='';
	document.getElementById('tt').value='';
	document.getElementById('jenis').value='';
	document.getElementById('blok').value='';
	document.getElementById('kegiatan').value='';
	document.getElementById('satuan').value='';
	document.getElementById('rotasi').value='';
	document.getElementById('totalvolume').value='';
	document.getElementById('volume').value='';
	document.getElementById('listdatasdm').innerHTML = "";
	document.getElementById('listdatamat').innerHTML = "";
	document.getElementById('listdataalat').innerHTML = "";
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	setValue2('blok',null);
	setValue2('kegiatan',null);
	enableheader();
}

function sebarkan(row,maxrow,jenis){
	row   = document.getElementById('awalsebar').value;
	maxrow= document.getElementById('akhirsebar').value;
	
	if(maxrow =='' || maxrow ==0){
		alertify.alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if(jenis=='1'){
		//per tahun tanam
		alertify.confirm("Warning","Anda yakin ???",
			function(){
				sebartt(row,maxrow);
			},
			function(){
				return;
			}
		);
	}else if(jenis=='2'){
		//per detail
		alertify.confirm("Warning","Anda yakin ???",
			function(){
				limitrow = 100;
				sebardetail(row,maxrow,limitrow,limitrow);
			},
			function(){
				return;
			}
		);
	}
}

function sebartt(row,maxrow){
	row     = parseFloat(row);
	param   = '';
	tahun   = document.getElementById('tahun'+row).innerHTML;
	divisi  = document.getElementById('divisi'+row).innerHTML;
	tt      = document.getElementById('tt'+row).innerHTML;
	kegiatan= document.getElementById('kegiatan'+row).innerHTML;
	
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}

	param += '&tahun=' + tahun;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&kegiatan=' + kegiatan;
	param += '&method=sebartt';

	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rowsebar'+row).style.display='none';
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebartt(row,maxrow);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function sebardetail(row,maxrow,limitrow,limitawal){
	row     = parseFloat(row);
	// if(limitawal>maxrow){
		// limitawal=maxrow;
		// limitrow=maxrow;
	// }
	param  = '';
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}
	
	// if((limitrow+row)>maxrow){
		// limitrow=maxrow;
	// }else{
		// limitrow=limitrow;
	// }
	
	// for (e = row; e <= (limitrow+row); e++) {
		// index= document.getElementById('index'+e).innerHTML;
		// param += '&index[' + e + ']=' + index;
		
		// document.getElementById('rowsebar'+e).style.backgroundColor='cyan';
		// document.getElementById('chkboxsebar'+e).checked=true;
	// }
	
	index= document.getElementById('index'+row).innerHTML;
	param += '&index[]=' + index;
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	param += '&method=sebardetail';
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// sisa=0;
					// limitrow+=limitawal;
					// if(limitrow>maxrow){
						// limitrow=maxrow;
					// }
					// if(row<=maxrow){
						// row+=limitawal;						
					// }else{
						// sisa=maxrow-row;
						// row+sisa;
						// limitrow+=sisa;
					// }
					document.getElementById('rowsebar'+row).style.display='none';
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebardetail(row,maxrow,limitrow,limitawal);
                    }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function numberFormat(number,digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	var components = (parseFloat(number).toFixed(digit)).split(".");
	components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return components.join(".");
}

function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}

function get_div_tt_blok(sumber,idhasil,bahasa){
	tahun  = document.getElementById('tahun').value;
	kodeorg= sumber.value;
	param = 'method=getdivttblok';
	param += '&kodeorg=' + kodeorg;
	param += '&bahasa=' + bahasa;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					id = idhasil.split(",");
					data = con.responseText.split("####");
					document.getElementById(id[0]).innerHTML = data[0];
					document.getElementById(id[1]).innerHTML = data[1];
					document.getElementById(id[2]).innerHTML = data[2];
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function get_tt_blok(sumber,idhasil,bahasa){
	tahun  = document.getElementById('tahun').value;
	kodeorg  = sumber.value;
	param = 'method=getttblok';
	param += '&kodeorg=' + kodeorg;
	param += '&bahasa=' + bahasa;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					id = idhasil.split(",");
					data = con.responseText.split("####");
					document.getElementById(id[0]).innerHTML = data[0];
					document.getElementById(id[1]).innerHTML = data[1];
					document.getElementById(id[2]).innerHTML = data[2];
					document.getElementById(id[3]).innerHTML = data[3];
					document.getElementById(id[4]).innerHTML = data[4];
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function getblok(sumber){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	blok   = document.getElementById('blok').value;
	param = 'method=getblok';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&tahun=' + tahun;
	param += '&blok=' + blok;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					data = con.responseText.split("####");
					if(sumber!='blok'){						
						document.getElementById('blok').innerHTML = trim(data[0]);
					}
					document.getElementById('volume').value = trim(data[1]);
					document.getElementById('jenis').innerHTML = trim(data[2]);
					get_kegiatan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function get_kegiatan(){
	jenis  = document.getElementById('jenis').value;
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	blok   = document.getElementById('blok').value;
	
	param = 'method=getkegiatan';
	param += '&jenis=' + jenis;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&tahun=' + tahun;
	param += '&blok=' + blok;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					data = con.responseText.split("####");
					document.getElementById('kegiatan').innerHTML = trim(data[0]);
					document.getElementById('volume').value = trim(data[1]);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function get_noakun(){
	kegiatan  = document.getElementById('kegiatan').value;
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	blok   = document.getElementById('blok').value;
	jenis  = document.getElementById('jenis').value;
	
	param = 'method=getnoakun';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&tahun=' + tahun;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	param += '&jenis=' + jenis;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					data = con.responseText.split("####");
					e = document.getElementById('noakun');
					for(i=0;i<e.length;i++){
						if(e.options[i].value==trim(data[0])){
							e.options[i].selected=true;
						}
					}
					document.getElementById('satuan').value = trim(data[1]);
					
					
					if(trim(data[2])>0){
						if(trim(data[1])=='KG'){							
							document.getElementById('totalvolume').value = trim(data[2]);
							document.getElementById('volume').value = '';
						}else{
							document.getElementById('volume').value = trim(data[2]);
							document.getElementById('totalvolume').value = "";
						}
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function editdetail(tahunbudget,kodeorg,divisi,thntnm,kodeblok,noakun,kegiatan,satuanv,volume,rotasi,fisik,jenis){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('listdatasdm').innerHTML = "";
	document.getElementById('listdatamat').innerHTML = "";
	document.getElementById('listdataalat').innerHTML = "";
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	
	document.getElementById('tahun').value=tahunbudget;
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('divisi').value=divisi;
	document.getElementById('tt').value=thntnm;
	document.getElementById('jenis').value=jenis;
	document.getElementById('blok').value=kodeblok;
	document.getElementById('noakun').value=noakun;
	document.getElementById('kegiatan').value=kegiatan;
	document.getElementById('satuan').value=satuanv;
	document.getElementById('volume').value=fisik;
	document.getElementById('rotasi').value=rotasi;
	document.getElementById('totalvolume').value=volume;
	
	setValue2('blok',kodeblok);
	setValue2('kegiatan',kegiatan);
	simpanheader();
}

function add_new_data(){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	document.getElementById('listdatasdm').innerHTML = "";
	document.getElementById('listdatamat').innerHTML = "";
	document.getElementById('listdataalat').innerHTML = "";
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	
	//loaddatasdm('all');
	batalheader();
}

function add_sebaran(){
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'block';
	document.getElementById('formcarisebaran').style.display = 'block';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	showsebaran();
}
function add_posting(){
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('contposting').style.display = 'block';
	document.getElementById('formcariposting').style.display = 'block';
	showposting();
}

function displayList() {
	document.getElementById('formcari').style.display = 'block';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('listsebaran').style.display = 'none';
	document.getElementById('formcarisebaran').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	loaddata(0);
}
function showsebaran(page){
	tahun   = document.getElementById('tahunsbr').value;
	kodeorg = document.getElementById('kodeorgsbr').value;
	divisi  = document.getElementById('divisisbr').value;
	tt      = document.getElementById('ttsbr').value;
	noakun  = document.getElementById('noakunsbr').value;
	kegiatan= document.getElementById('kegiatansbr').value;
	sebaran = document.getElementById('sebaran').value;
	jlhbaris= document.getElementById('jlhbaris').value;
	tampilkan= document.getElementById('tampilkan').value;
	
	
	param  = 'method=showsebaran&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakunsch=' + noakun + '&kegiatan=' + kegiatan;
	param += '&sebaran=' + sebaran + '&jlhbaris=' + jlhbaris;
	param += '&tampilkan=' + tampilkan;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tampilkan=='2'){
						document.getElementById('bloksebar').style.display="";
						document.getElementById('kdbgtsebar').style.display="";
						document.getElementById('kdbrgsebar').style.display="";
						document.getElementById('kdvhcsebar').style.display="";		
					}else{
						document.getElementById('bloksebar').style.display="none";
						document.getElementById('kdbgtsebar').style.display="none";
						document.getElementById('kdbrgsebar').style.display="none";
						document.getElementById('kdvhcsebar').style.display="none";		
					}
					isdt = con.responseText.split("####");
					//document.getElementById('listsebaran').innerHTML = con.responseText;
					document.getElementById('containsebar').innerHTML = isdt[0];
					document.getElementById('footDatasebar').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showposting(){
	tahun  = document.getElementById('tahunpostsch').value;
	kodeorg= document.getElementById('kodeorgpostsch').value;
	
	param  = 'method=showposting';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contpostingdata').innerHTML = con.responseText;
					leftFixedTable();
					// $(document).ready(function() {
						// var table = $('#pvtTable').DataTable({
							// //fixedColumns: true,
							// fixedHeader: true,
							// colReorder: true,
							// paging: true,
							// "iDisplayLength": 50,
							// scrollY: 380,
							// dom: 'Bfrtip',
							// buttons: [
								// 'csv', 'excel'
							// ],
							// // sub total
							// order: [[0, 'asc']],
							// rowGroup: {
								// startRender: null,
								// endRender: function ( rows, group ) {
									// var luas = rows.data().pluck(4).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// luas = $.fn.dataTable.render.number(',', '.', 0, '').display( luas );	
									// var sdm = rows.data().pluck(5).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// sdm = $.fn.dataTable.render.number(',', '.', 0, '').display( sdm );
									
									// var mat = rows.data().pluck(6).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// mat = $.fn.dataTable.render.number(',', '.', 0, '').display( mat );
									
									// var alt = rows.data().pluck(7).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// alt = $.fn.dataTable.render.number(',', '.', 0, '').display( alt );
									
									// var kont = rows.data().pluck(8).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// kont = $.fn.dataTable.render.number(',', '.', 0, '').display( kont );
									
									// var vra = rows.data().pluck(9).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// vra = $.fn.dataTable.render.number(',', '.', 0, '').display( vra );
									
									// var ttl = rows.data().pluck(10).reduce( function (a, b) {
											// return a + b.replace(/[^\d]/g, '')*1;
										// }, 0);
									// ttl = $.fn.dataTable.render.number(',', '.', 0, '').display( ttl );
									
									// var avg = rows.data().pluck(10).reduce( function (a, b) {return a + b.replace(/[^\d]/g, '')*1;}, 0)/rows.data().pluck(4).reduce( function (a, b) {return a + b.replace(/[^\d]/g, '')*1;}, 0);
									// avg = $.fn.dataTable.render.number(',', '.', 0, '').display( avg );
									// return $('<tr/>')
										// .append( '<td colspan="4">Total for '+group+'</td>' )
										// .append( '<td style=text-align:right;>'+luas+'</td>' )
										// .append( '<td style=text-align:right;>'+sdm+'</td>' )
										// .append( '<td style=text-align:right;>'+mat+'</td>' )
										// .append( '<td style=text-align:right;>'+alt+'</td>' )
										// .append( '<td style=text-align:right;>'+kont+'</td>' )
										// .append( '<td style=text-align:right;>'+vra+'</td>' )
										// .append( '<td style=text-align:right;>'+ttl+'</td>' )
										// .append( '<td style=text-align:right;>'+avg+'</td>' )
										// .append( '<td style=text-align:right;></td>' )
										// ;
								// },
								// dataSrc: 2
							// }
						// } );
					// } );
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form(){
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
}
function html(tahun,kodeorg) {
	form();
	param = 'method=html'  + '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPageSbr() {
	pg = document.getElementById('pagessbr');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	showsebaran(paged);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	tahun   = document.getElementById('tahunsch').value;
	kodeorg = document.getElementById('kodeorgsch').value;
	divisi  = document.getElementById('divisisch').value;
	tt      = document.getElementById('ttsch').value;
	noakun  = document.getElementById('noakunsch').value;
	kegiatan= document.getElementById('kegiatansch').value;
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&noakunsch=' + noakun + '&kegiatan=' + kegiatan;
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadexcel(page) {
	tahun  = document.getElementById('tahunsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	divisi = document.getElementById('divisisch').value;
	tt     = document.getElementById('ttsch').value;
	sebaran= document.getElementById('sebaransch').value;
	ip     = document.getElementById('ipsch').value;
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&sebaran=' + sebaran + '&ip=' + ip;
	param += '&jenis=excel';
	
	tujuan= 'bgt_slave_byykebun.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '300';
	height = '100';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function batalcari() {
	document.getElementById('kodeorgsch').value='';
	document.getElementById('divisisch').value='';
	document.getElementById('ttsch').value='';
	document.getElementById('sebaransch').value='';
	document.getElementById('ipsch').value='';
	loaddata(0);
}