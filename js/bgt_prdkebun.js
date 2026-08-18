function showbutton(){
	document.getElementById('formuploaddt').style.display = 'block';
}
function del(tahunbudget,kodeorg,blok) {
	param = 'method=del';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&blok=' + blok;
	tujuan = 'bgt_slave_prdkebun.php';
	if (confirm('Anda yakin ??')) {
		if(blok==''){
			if (confirm('Anda yakin menghapus semua transaksi satu kebun ???')) {				
				post_response_text(tujuan, param, respog);
			}
		}else{			
			post_response_text(tujuan, param, respog);
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(blok==''){						
						showposting();
					}else{						
						getPage();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(tahunbudget,kodeorg) {
	param = 'method=unposting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_prdkebun.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
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


function posting(tahunbudget,kodeorg,sebaran,varjjg,varkg){
	if(sebaran=='x' && varjjg!=0){
		alertify.alert("Jjg Total tidak sama dengan Jjg Sebaran,\nTerdapat selisih : "+varjjg+" Jjg.");
		return;
	}
	if(sebaran=='x' && varkg!=0){
		alertify.alert("Kg Total tidak sama dengan Kg Sebaran,\nTerdapat selisih : "+varkg+" Kg.");
		return;
	}
	param = 'method=posting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_prdkebun.php';
	if (confirm('Anda yakin ??')) {
		post_response_text(tujuan, param, respog);
	}
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


function bataladd(){
	document.getElementById('divisi').value='';
	document.getElementById('tt').value='';
	document.getElementById('blok').value='';
	document.getElementById('tahun').disabled=false;
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('continputdata').innerHTML='';
	getblok();
}
function hapuspersen(){
	for(i=1;i<=12;i++){
		document.getElementById('persen_'+i).value=0;
	}
	hitungsebaran('','1');
}

function saveall(maxrow){  
	if(maxrow =='' || maxrow ==0){
        alertify.alert('Data tidak ditemukan, proses dibatalkan !');
        return;
    }
	row = document.getElementById('awalbaris').value;
	if(confirm("Data akan disimpan semua mulai dari baris ke "+row+", Anda yakin ???")){
		simpan(row,maxrow);
	}
}

function simpan(row,maxrow){
	row    = parseFloat(row);
	param  = '';
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	jenisbudget= document.getElementById('jenisbudget').value;
	tt     = document.getElementById('tt_'+row).innerHTML;
	blok   = document.getElementById('blok_'+row).innerHTML;
	ttljjg = document.getElementById('jjg_'+row).value;
	ttlkg  = document.getElementById('kg_'+row).value;
	ttlkgbruto  = remove_comma_var(document.getElementById('kgbruto_'+row).value);
	
	for (i = 1; i <= 12; i++) {
		jjg= document.getElementById('jjg_'+row+'_'+i).value;
		kg = document.getElementById('kg_'+row+'_'+i).value
		kgbruto = document.getElementById('kgbruto_'+row+'_'+i).value
		jjg= remove_comma_var(jjg);
		kg = remove_comma_var(kg);
		kgbruto = remove_comma_var(kgbruto);
		param += '&jjg[' + i + ']=' + jjg;
		param += '&kg[' + i + ']=' + kg;
		param += '&kgbruto[' + i + ']=' + kgbruto;
	}

	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&tt=' + tt;
	param += '&jenisbudget=' + jenisbudget;
	param += '&blok=' + blok;
	param += '&ttljjg=' + ttljjg;
	param += '&ttlkg=' + ttlkg;
	param += '&ttlkgbruto=' + ttlkgbruto;
	param += '&method=simpan';

	tujuan = 'bgt_slave_prdkebun.php';
	post_response_text(tujuan, param, respog);
	if(row!=undefined){		
		document.getElementById('btnsave_' + row).style.backgroundColor='cyan';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('btnsave_' + row).style.backgroundColor = 'red';
					if(maxrow != undefined){
						document.getElementById('awalbaris').value=row;
					}	
				} else {
					row+=1;
                    if((row>maxrow) || (maxrow == undefined)){
						alertify.alert("done");
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						simpan(row,maxrow);
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

function hitungsebaran(row,bln){
	if(bln!=''){
		//sumber isi persen sebaran
		e = document.getElementById('persen_'+bln).value;
		if(e==''){document.getElementById('persen_'+bln).value=0;}
		
		ttlrow = document.getElementById('ttlbaris').value;
		t = 0;
		for(i=1;i<=12;i++){
			e = document.getElementById('persen_'+i).value;
			if(e==''){e=0;}
			t = t + parseFloat(e);
		}
		
		for(row=1;row<=ttlrow;row++){
			tjjg= 0; jjgsebar=0;
			tkg = 0; kgsebar =0;
			tkgbruto = 0; kgbrutosebar =0;

			jjg = document.getElementById('jjg_'+row).value;
			kg  = document.getElementById('kg_'+row).value;
			kgbruto  = document.getElementById('kgbruto_'+row).value;

			jjg = remove_comma_var(jjg);
			kg  = remove_comma_var(kg);
			kgbruto  = remove_comma_var(kgbruto);
			for(i=1;i<=12;i++){
				n = document.getElementById('persen_'+i).value;
				if(i<12){
					j   = Math.round(parseFloat(jjg)*(parseFloat(n)/parseFloat(t)));
					k   = Math.round(parseFloat(kg)*(parseFloat(n)/parseFloat(t)));
					kb   = Math.round(parseFloat(kgbruto)*(parseFloat(n)/parseFloat(t)));


					tjjg=  tjjg + parseFloat(j);
					tkg =  tkg + parseFloat(k);
					tkgbruto =  tkgbruto + parseFloat(kb);
				}else{
					j = jjg - tjjg;
					k = kg - tkg;
					kb = kgbruto - tkgbruto;
				}
				if(isNaN(j)){j=0;}
				if(j<0){j=0;}
				if(isNaN(k)){k=0;}
				if(k<0){k=0;}
				if(isNaN(kb)){kb=0;}
				if(kb<0){kb=0;}
				document.getElementById('jjg_'+row+'_'+i).value = j;
				document.getElementById('kg_'+row+'_'+i).value = k;
				document.getElementById('kgbruto_'+row+'_'+i).value = kb;
				
				jjgsebar = jjgsebar+parseFloat(j);
				kgsebar = kgsebar+parseFloat(k);
				kgbrutosebar = kgbrutosebar+parseFloat(kb);
			}
			if((jjg-jjgsebar)!=0 || (kg-kgsebar)!=0){
				document.getElementById('btnsave_' + row).style.backgroundColor = 'red';
			}
		}
	}else{
		//sumber isi ttljjg atau ttlkg
		t = 0;
		for(i=1;i<=12;i++){
			e = document.getElementById('persen_'+i).value;
			if(e==''){e=0;}
			t = t + parseFloat(e);
		}
		tjjg= 0; jjgsebar=0;
		tkg = 0; kgsebar =0;
		tkgbruto = 0; kgbrutosebar =0;
		
		jjg = document.getElementById('jjg_'+row).value;
		kg  = document.getElementById('kg_'+row).value;
		kgbruto  = document.getElementById('kgbruto_'+row).value;

		jjg = remove_comma_var(jjg);
		kg  = remove_comma_var(kg);
		kgbruto  = remove_comma_var(kgbruto);
		for(i=1;i<=12;i++){
			n = document.getElementById('persen_'+i).value;
			if(i<12){
				j   = Math.round(parseFloat(jjg)*(parseFloat(n)/parseFloat(t)));
				k   = Math.round(parseFloat(kg)*(parseFloat(n)/parseFloat(t)));
				kb   = Math.round(parseFloat(kgbruto)*(parseFloat(n)/parseFloat(t)));

				tjjg=  tjjg + parseFloat(j);
				tkg =  tkg + parseFloat(k);
				tkgbruto =  tkgbruto + parseFloat(kb);
			}else{
				j = jjg - tjjg;
				k = kg - tkg;
				kb = kgbruto - tkgbruto;
			}
			if(isNaN(j)){j=0;}
			if(j<0){j=0;}
			if(isNaN(k)){k=0;}
			if(k<0){k=0;}
			if(isNaN(kb)){kb=0;}
			if(kb<0){kb=0;}
			
			b = parseFloat(kg) / parseFloat(jjg);
			if(isNaN(b)){b=0;}
			
			document.getElementById('jjg_'+row+'_'+i).value = j;
			document.getElementById('kg_'+row+'_'+i).value = k;
			document.getElementById('kgbruto_'+row+'_'+i).value = kb;
			document.getElementById('bjr_'+row).innerHTML = numberFormat(b,2);
			
			jjgsebar = jjgsebar+parseFloat(j);
			kgsebar = kgsebar+parseFloat(k);
			kgbrutosebar = kgbrutosebar+parseFloat(kb);
		}
		// alertify.alert("jjgttl : "+jjg+" jjgsebar : "+jjgsebar+" ttlkg : "+kg+" kgsebar "+kgsebar+" ttlkgbruto : "+kgbruto+" kgsebar "+kgbrutosebar);
		if((jjg-jjgsebar)!=0 || (kg-kgsebar)!=0 || (kgbruto-kgbrutosebar)!=0){
			document.getElementById('btnsave_' + row).style.backgroundColor = 'red';
		}
	}
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
	tujuan = 'bgt_slave_prdkebun.php';
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
	tujuan = 'bgt_slave_prdkebun.php';
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
	param = 'method=getblok';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tt=' + tt;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_prdkebun.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('blok').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function editdetail(tahunbudget,kodeorg,blok){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	
	document.getElementById('tahun').value=tahunbudget;
	document.getElementById('kodeorg').value=kodeorg;
	setValue2('kodeorg',kodeorg);
	document.getElementById('blok').innerHTML="<option value='"+ blok +"'>"+ blok +"</option>";
	
	document.getElementById('divisi').value='';
	document.getElementById('tt').value='';
	document.getElementById('tahun').disabled=true;
	document.getElementById('kodeorg').disabled=true;
	adddata();
}

function add_new_data(){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';

	document.getElementById('inputdataex').style.display = 'none';
	document.getElementById('contdetailex').style.display = 'none';

	bataladd();
}

function add_sebaran(){
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('contposting').style.display = 'block';
	document.getElementById('formcariposting').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
		
	document.getElementById('inputdataex').style.display = 'none';
	document.getElementById('contdetailex').style.display = 'none';

	showposting();
}

function displayList() {
	document.getElementById('formcari').style.display = 'block';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';

	document.getElementById('inputdataex').style.display = 'none';
	document.getElementById('contdetailex').style.display = 'none';

	loaddata(0);
}

function showposting(){
	tahun  = document.getElementById('tahunpostsch').value;
	kodeorg= document.getElementById('kodeorgpostsch').value;
	
	param  = 'method=showposting';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	tujuan = 'bgt_slave_prdkebun.php';
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formupload(){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	
	param  = 'method=formupload';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	tujuan = 'bgt_slave_prdkebun.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function adddata(){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	tt     = document.getElementById('tt').value;
	blok   = document.getElementById('blok').value;
	
	param  = 'method=adddata';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi + '&tt=' + tt;
	param += '&blok=' + blok;
	tujuan = 'bgt_slave_prdkebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('continputdata').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getmark(no){
	namacol = document.getElementsByName('baris[]');
	for (var r = 0; r < namacol.length; r++) {
		namacol[r].style.backgroundColor="";
	}
	
	dis = document.getElementById('notran'+no).style.backgroundColor;
	if(dis!=''){
		document.getElementById('notran'+no).style.backgroundColor="";		
	}else{		
		document.getElementById('notran'+no).style.backgroundColor="cyan";
	}
	
}

function form() {
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
	tujuan = 'bgt_slave_prdkebun.php';
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

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
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
	tujuan = 'bgt_slave_prdkebun.php';
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
	
	tujuan= 'bgt_slave_prdkebun.php';
	judul = 'excel';
	ev    = 'event';
	//printFile(param, tujuan, judul, ev)
	printnopopup(tujuan+"?"+param);
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

function showformupload(){
	document.getElementById('formcari').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	document.getElementById('contposting').style.display = 'none';
	document.getElementById('formcariposting').style.display = 'none';
	
	document.getElementById('inputdataex').style.display = 'block';
	document.getElementById('contdetailex').style.display = 'block';

	// document.getElementById('upload').value='';
	// document.getElementById('contdetail').innerHTML='';
}

function fileSelected(jenis){
	// kodeorg = document.getElementById('kodeorg').value;
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("jenis", jenis);
	// formdata.append("kodeorg", kodeorg);
	
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "bgt_slave_prdkebun.php?method=fileSelected", true);
	con.onreadystatechange = eval(respon);
	console.log(formdata)
	con.send(formdata);
    
    function respon(){
        if (con.readyState == 4){
			if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
					if(jenis=='simpan'){
						document.getElementById('contdetailex').innerHTML="";
						alertify.alert("Done");
					}else{						
						document.getElementById('contdetailex').innerHTML=con.responseText;
						leftFixedTable();
					}
                }
            }else{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanupload(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert('Info','Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	alertify.confirm("Warning","Proses ini akan me-replace data yg sudah ada, anda yakin ?",
		function(){
			savedetail(1, maxRow);
		},
		function(){
			return;
		}
	);
}
function savedetail(currRow, maxRow) {
	tahunbudget      	= document.getElementById('tahunbudget_' + currRow).innerHTML;
	kodeunit      		= document.getElementById('kodeunit_' + currRow).innerHTML;
	kodeblok		    = document.getElementById('kodeblok_' + currRow).innerHTML;
	tahuntanam      	= document.getElementById('tahuntanam_' + currRow).innerHTML;
	jenisbudget      	= document.getElementById('jenisbudget_' + currRow).innerHTML;
	totaljjg      		= document.getElementById('totaljjg_' + currRow).innerHTML;
	totalkg      		= document.getElementById('totalkg_' + currRow).innerHTML;
	totalkgbruto	    = document.getElementById('totalkgbruto_' + currRow).innerHTML;
	
	method     = document.getElementById('method_' + currRow).value;
	
	param   = "";
	param += 'method=' + method;

	for (i = 1; i <= 12; i++) {

		if(i < 10) {
			var nourut = i.toString().padStart(2, '0'); // Menggunakan padStart untuk memastikan dua digit
			// alert(nourut)
		} else {
			var nourut = i;
		}
		

		console.log('jjg'+nourut+'_'+currRow);	
		jjg		= document.getElementById('jjg'+nourut+'_'+currRow).innerHTML;
		kg 		= document.getElementById('kg'+nourut+'_'+currRow).innerHTML;
		kgbruto = document.getElementById('kgbruto'+nourut+'_'+currRow).innerHTML;

		jjg= remove_comma_var(jjg);
		kg = remove_comma_var(kg);
		kgbruto = remove_comma_var(kgbruto);
		
		param += '&jjg[' + i + ']=' + jjg;
		param += '&kg[' + i + ']=' + kg;
		param += '&kgbruto[' + i + ']=' + kgbruto;
	}
	
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeunit;
	param += '&blok=' + kodeblok;
	param += '&tt=' + tahuntanam;
	param += '&jenisbudget=' + jenisbudget;
	param += '&ttljjg=' + totaljjg;
	param += '&ttlkg=' + totalkg;
	param += '&ttlkgbruto=' + totalkgbruto;
	
	// alert(param);
	console.log(param)
	
	tujuan = 'bgt_slave_prdkebun.php';
	post_response_text(tujuan, param, respog);
	if (currRow != undefined) {
		document.getElementById('baris_'+currRow).style.backgroundColor='cyan';
		document.getElementById('baris_'+currRow).style.display='none';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
					if (currRow != undefined) {
						document.getElementById('validasi_' + currRow).style.backgroundColor = 'red';
					}
				} else {
					if (currRow != undefined) {
						document.getElementById('validasi_' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alertify.alert("Done");
						location.reload();
					} else {
						savedetail(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}