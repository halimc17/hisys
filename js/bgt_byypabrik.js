function delTK(nomor){
	document.getElementById("gapoknaik_"+nomor).value='';
	document.getElementById("premi_"+nomor).value='';
	document.getElementById("tuntap_"+nomor).value='';
	document.getElementById("bpjs_"+nomor).value='';
	document.getElementById("thr_"+nomor).value='';
	document.getElementById("bonus_"+nomor).value='';
	document.getElementById("rumah_"+nomor).value='';
	document.getElementById("totalkanan_"+nomor).innerHTML='';
	
	document.getElementById("lembur_"+nomor).value='';
	document.getElementById("tidaktetap_"+nomor).value='';
	document.getElementById("extrafooding_"+nomor).value='';
	document.getElementById("persengapok_"+nomor).value='';
	
	gp=pr=tt=th=bn=rm=bp=gt=l=tottidaktetap=totextrafooding=0;
	n = document.getElementsByName("gapokawal[]");
	for (i = 0; i < n.length; i++){
		a = document.getElementsByName("gapoknaik[]")[i].value; 
		if(a==''){a=0;}else{a=remove_comma_var(a);}
		gp = gp+parseFloat(a);
		
		b = document.getElementsByName("premi[]")[i].value; 
		if(b==''){b=0;}else{b=remove_comma_var(b);}
		pr = pr+parseFloat(b);
		
		m = document.getElementsByName("lembur[]")[i].value; 
		if(m==''){m=0;}else{m=remove_comma_var(m);}
		l = l+parseFloat(m);
		
		c = document.getElementsByName("tuntap[]")[i].value; 
		if(c==''){c=0;}else{c=remove_comma_var(c);}
		tt = tt+parseFloat(c);
		
		tidaktetap = document.getElementsByName("tidaktetap[]")[i].value; 
		if(tidaktetap==''){tidaktetap=0;}else{tidaktetap=remove_comma_var(tidaktetap);}
		tottidaktetap = tottidaktetap+parseFloat(tidaktetap);
		
		extrafooding = document.getElementsByName("extrafooding[]")[i].value; 
		if(extrafooding==''){extrafooding=0;}else{extrafooding=remove_comma_var(extrafooding);}
		totextrafooding = totextrafooding+parseFloat(extrafooding);
		
		d = document.getElementsByName("thr[]")[i].value; 
		if(d==''){d=0;}else{d=remove_comma_var(d);}
		th = th+parseFloat(d);
		
		f = document.getElementsByName("bonus[]")[i].value; 
		if(f==''){f=0;}else{f=remove_comma_var(f);}
		bn = bn+parseFloat(f);
		
		g = document.getElementsByName("rumah[]")[i].value; 
		if(g==''){g=0;}else{g=remove_comma_var(g);}
		rm = rm+parseFloat(g);
		
		h = document.getElementsByName("bpjs[]")[i].value; 
		if(h==''){h=0;}else{h=remove_comma_var(h);}
		bp = bp+parseFloat(h);
		
		k = document.getElementsByName("totalkanan[]")[i].innerHTML; 
		if(k==''){k=0;}else{k=remove_comma_var(k);}
		gt = gt+parseFloat(k);
	}
	
	document.getElementById('gapoknaikbawah').value=numberFormat(gp);
	document.getElementById('premibawah').value=numberFormat(pr);
	document.getElementById('lemburbawah').value=numberFormat(l);
	document.getElementById('tidaktetapbawah').value=numberFormat(tottidaktetap);
	document.getElementById('extrafoodingbawah').value=numberFormat(totextrafooding);
	document.getElementById('tuntapbawah').value=numberFormat(tt);
	document.getElementById('thrbawah').value=numberFormat(th);
	document.getElementById('bonusbawah').value=numberFormat(bn);
	document.getElementById('rumahbawah').value=numberFormat(rm);
	document.getElementById('bpjsbawah').value=numberFormat(bp);
	document.getElementById('gtotalkanan').innerHTML=numberFormat(gt);
}
function copygapok(nomor){
	nilaigapokawal = document.getElementById('nilaigapokawal_'+nomor).innerHTML;
	if(nilaigapokawal==''){nilaigapokawal=0;}
	nilaigapokawal = remove_comma_var(nilaigapokawal);
	document.getElementById('gapoknaik_'+nomor).value=nilaigapokawal; 
	
	hitungupah(nomor,'gapoknaik_'+nomor);
}
function hitungupah(nomor,e){
	// alert(nomor);
	// alert(e);
	gapoknaik    = document.getElementById('gapoknaik_'+nomor).value; 
	tuntap       = document.getElementById('tuntap_'+nomor).value;
	gapoknaik    = remove_comma_var(gapoknaik);
	tuntap       = remove_comma_var(tuntap);
		
	if('persengapok_'+nomor==e){
		nilaigapokawal = document.getElementById('nilaigapokawal_'+nomor).innerHTML;
		if(nilaigapokawal==''){nilaigapokawal=0;}
		nilaigapokawal = remove_comma_var(nilaigapokawal);
		
		persengapok = document.getElementById('persengapok_'+nomor).value;
		if(persengapok==''){persengapok=0;}
		
		gapoknaik = parseFloat(nilaigapokawal)*(parseFloat(persengapok)/(100))+parseFloat(nilaigapokawal);
		if(gapoknaik==''){gapoknaik=0;}
		document.getElementById('gapoknaik_'+nomor).value=numberFormat(gapoknaik);
	}
	
	if('gapoknaik_'+nomor==e){
		// premi = parseFloat(gapoknaik)*3/100;
		// if(premi==''){premi=0;}
		// document.getElementById('premi_'+nomor).value=numberFormat(premi);
		document.getElementById('thr_'+nomor).value=numberFormat(gapoknaik);
		// bonus = parseFloat(gapoknaik)*(55/100);
		// if(bonus==''){bonus=0;}
		// document.getElementById('bonus_'+nomor).value=numberFormat(bonus);
		bpjs = parseFloat(gapoknaik)*(4.5/100);
		if(bpjs==''){bpjs=0;}
		document.getElementById('bpjs_'+nomor).value=numberFormat(bpjs);
		
		
		nilaigapokawal = document.getElementById('nilaigapokawal_'+nomor).innerHTML;
		if(nilaigapokawal==''){nilaigapokawal=0;}
		nilaigapokawal = remove_comma_var(nilaigapokawal);
		
		persengapok = (parseFloat(gapoknaik)-parseFloat(nilaigapokawal))/parseFloat(nilaigapokawal)*100;
		if(persengapok==''){persengapok=0;}
		document.getElementById('persengapok_'+nomor).value=numberFormat(persengapok,2);
	}
	
	extrafooding= document.getElementById('extrafooding_'+nomor).value;
	tidaktetap  = document.getElementById('tidaktetap_'+nomor).value;
	lembur      = document.getElementById('lembur_'+nomor).value;
	premi       = document.getElementById('premi_'+nomor).value;
	thr         = document.getElementById('thr_'+nomor).value;
	bonus       = document.getElementById('bonus_'+nomor).value;
	rumah       = document.getElementById('rumah_'+nomor).value;
	bpjs        = document.getElementById('bpjs_'+nomor).value;
	extrafooding= remove_comma_var(extrafooding);
	tidaktetap  = remove_comma_var(tidaktetap);
	lembur      = remove_comma_var(lembur);
	premi       = remove_comma_var(premi);
	thr         = remove_comma_var(thr);
	bonus       = remove_comma_var(bonus);
	rumah       = remove_comma_var(rumah);
	if(extrafooding==''){extrafooding=0;}
	if(tidaktetap==''){tidaktetap=0;}
	if(lembur==''){lembur=0;}
	if(gapoknaik==''){gapoknaik=0;}
	if(tuntap==''){tuntap=0;}
	if(premi==''){premi=0;}
	if(thr==''){thr=0;}
	if(bonus==''){bonus=0;}
	if(rumah==''){rumah=0;}
	if(bpjs==''){bpjs=0;}
	
	totalkanan  = ((parseFloat(lembur)+parseFloat(extrafooding)+parseFloat(tidaktetap)+parseFloat(gapoknaik)+parseFloat(tuntap)+parseFloat(premi)+parseFloat(bpjs))*12)+parseFloat(thr)+parseFloat(bonus)+parseFloat(rumah);
	document.getElementById('totalkanan_'+nomor).innerHTML=numberFormat(totalkanan);
	
	
	gp=pr=tt=th=bn=rm=bp=gt=l=tottidaktetap=totextrafooding=0;
	n = document.getElementsByName("gapokawal[]");
	for (i = 0; i < n.length; i++){
		a = document.getElementsByName("gapoknaik[]")[i].value; 
		if(a==''){a=0;}else{a=remove_comma_var(a);}
		gp = gp+parseFloat(a);
		
		b = document.getElementsByName("premi[]")[i].value; 
		if(b==''){b=0;}else{b=remove_comma_var(b);}
		pr = pr+parseFloat(b);
		
		m = document.getElementsByName("lembur[]")[i].value; 
		if(m==''){m=0;}else{m=remove_comma_var(m);}
		l = l+parseFloat(m);
		
		c = document.getElementsByName("tuntap[]")[i].value; 
		if(c==''){c=0;}else{c=remove_comma_var(c);}
		tt = tt+parseFloat(c);
		
		tidaktetap = document.getElementsByName("tidaktetap[]")[i].value; 
		if(tidaktetap==''){tidaktetap=0;}else{tidaktetap=remove_comma_var(tidaktetap);}
		tottidaktetap = tottidaktetap+parseFloat(tidaktetap);
		
		extrafooding = document.getElementsByName("extrafooding[]")[i].value; 
		if(extrafooding==''){extrafooding=0;}else{extrafooding=remove_comma_var(extrafooding);}
		totextrafooding = totextrafooding+parseFloat(extrafooding);
		
		d = document.getElementsByName("thr[]")[i].value; 
		if(d==''){d=0;}else{d=remove_comma_var(d);}
		th = th+parseFloat(d);
		
		f = document.getElementsByName("bonus[]")[i].value; 
		if(f==''){f=0;}else{f=remove_comma_var(f);}
		bn = bn+parseFloat(f);
		
		g = document.getElementsByName("rumah[]")[i].value; 
		if(g==''){g=0;}else{g=remove_comma_var(g);}
		rm = rm+parseFloat(g);
		
		h = document.getElementsByName("bpjs[]")[i].value; 
		if(h==''){h=0;}else{h=remove_comma_var(h);}
		bp = bp+parseFloat(h);
		
		k = document.getElementsByName("totalkanan[]")[i].innerHTML; 
		if(k==''){k=0;}else{k=remove_comma_var(k);}
		gt = gt+parseFloat(k);
	}
	
	document.getElementById('gapoknaikbawah').value=numberFormat(gp);
	document.getElementById('premibawah').value=numberFormat(pr);
	document.getElementById('lemburbawah').value=numberFormat(l);
	document.getElementById('tidaktetapbawah').value=numberFormat(tottidaktetap);
	document.getElementById('extrafoodingbawah').value=numberFormat(totextrafooding);
	document.getElementById('tuntapbawah').value=numberFormat(tt);
	document.getElementById('thrbawah').value=numberFormat(th);
	document.getElementById('bonusbawah').value=numberFormat(bn);
	document.getElementById('rumahbawah').value=numberFormat(rm);
	document.getElementById('bpjsbawah').value=numberFormat(bp);
	document.getElementById('gtotalkanan').innerHTML=numberFormat(gt);
}	

function getTk(){
	tahunbudget= document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	station    = document.getElementById('station').value;
	tipekary   = document.getElementById('kdbudgetsdm').value;
	hkefektif  = document.getElementById('hkesdm').value;
	aruskassdm = document.getElementById('aruskassdm').value;
	
	validate([
        ["tahun","Tahun budget tidak boleh kosong."],
        ["kodeorg","Unit tidak boleh kosong"],
        ["station","Station tidak boleh kosong"]
	]);
	
	param  = 'method=getTk&tahunbudget=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&tipekary=' + tipekary;
	param += '&hkefektif=' + hkefektif;
	param += '&aruskas=' + aruskassdm;
	tujuan = 'bgt_slave_byypabrik.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					alertify.popup().destroy();
				} else {
					alertify.popup().destroy();
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':'Karyawan'}).resizeTo('85%','70%').show();
					
					//alertify.popup(con.responseText).set({'pinnable': true, 'modal':false,'resizable':true,'maximizable':true}).resizeTo('80%','70%').unpin(); 
					
					document.getElementById('kdbudgetsdm').value='EXPL-UPAH';
					getaruskas('sdm','aruskassdm','x');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanTk(){
	tahunbudget= document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	station    = document.getElementById('station').value;
	tipekary   = document.getElementById('kdbudgetsdm').value;
	hkefektif  = document.getElementById('hkesdm').value;
	aruskassdm = document.getElementById('aruskassdm').value;
	
	param  = 'method=simpanTk&tahunbudget=' + tahunbudget;
	param += '&tahun=' + tahunbudget;
	param += '&aruskas=' + aruskassdm;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&tipekary=' + tipekary;
	param += '&tipebudget=' + tipekary;
	param += '&hkefektif=' + hkefektif;
	e = document.getElementsByName("gapokawal[]");
	for (i = 0; i < e.length; i++){
		param += "&tipekary["+i+"]="+document.getElementsByName("tipekary[]")[i].innerHTML;
		param += "&idkary["+i+"]="+document.getElementsByName("idkary[]")[i].innerHTML;
		param += "&gapoknaik["+i+"]="+document.getElementsByName("gapoknaik[]")[i].value;
		param += "&premi["+i+"]="+document.getElementsByName("premi[]")[i].value;
		param += "&tuntap["+i+"]="+document.getElementsByName("tuntap[]")[i].value;
		param += "&bpjs["+i+"]="+document.getElementsByName("bpjs[]")[i].value;
		param += "&thr["+i+"]="+document.getElementsByName("thr[]")[i].value;
		param += "&bonus["+i+"]="+document.getElementsByName("bonus[]")[i].value;
		param += "&rumah["+i+"]="+document.getElementsByName("rumah[]")[i].value;
		param += "&totalkanan["+i+"]="+document.getElementsByName("totalkanan[]")[i].innerHTML;
		
		param += "&lembur["+i+"]="+document.getElementsByName("lembur[]")[i].value;
		param += "&tidaktetap["+i+"]="+document.getElementsByName("tidaktetap[]")[i].value;
		param += "&extrafooding["+i+"]="+document.getElementsByName("extrafooding[]")[i].value;
		param += "&persengapok["+i+"]="+document.getElementsByName("persengapok[]")[i].value;
		param += "&gapokbefore["+i+"]="+document.getElementsByName("nilaigapokawal[]")[i].innerHTML;
	}
	
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					loaddatasdm();
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
	if(sumber=='kont'){
		document.getElementById('hargakontrak').value = rupiah;
		document.getElementById('kodebarangkont').value = kdbrg;
		document.getElementById('namabarangkont').innerHTML = nama;
		document.getElementById('satuankont').value = trim(sat);
	}
	alertify.popup().destroy();
	// closeDialog();
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
	if(sumber=='kont'){
		klbarang  = '8';
	}

	
	param  = 'kodebarang=' + kodebarang + '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&klbarang=' + klbarang;
	param += '&sumber=' + sumber;
	param += '&method=caribarang';
	tujuan = 'bgt_slave_byypabrik.php';
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
	// width = '';
	// height = '';
	// content = "<fieldset><div id=containerd style=\"max-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	
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
	if(sumber=='kont'){		
		document.getElementById('namabarangkont').innerHTML = '';
		klbarang = '8';
	}
	
	param  = 'klbarang=' + klbarang + '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&sumber=' + sumber;
	param += '&method=formcaribarang';
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Cari Barang",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','500px');
					caribarang(sumber);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editsdm(index,mesin,aruskas,kdbudget,jhk,rupiah){
	document.getElementById('kdbudgetsdm').value=kdbudget;
	document.getElementById('aruskassdm').value=aruskas;
	
	setValue2('kdbudgetsdm',kdbudget);
	setTimeout(function(){
		setValue2('aruskassdm',aruskas);
		setTimeout(function(){
			hkesdm=document.getElementById('hkesdm').value;
			jlhtk = parseFloat(jhk)/parseFloat(hkesdm);
			document.getElementById('jlhtk').value = numberFormat(jlhtk,2);
			document.getElementById('jhksdm').value=jhk;
			document.getElementById('ttlbyysdm').value=rupiah;
			document.getElementById('index').value=index;
			if(index!=''){		
				document.getElementById('update').value='update';
				document.getElementById('mesin').value=mesin;
				setValue2('mesin',mesin);
			}else{
				document.getElementById('update').value='';
				document.getElementById('mesin').value='';
				setValue2('mesin',null);
			}		
		}, 250);
	}, 250);
	
}
function editmat(index,mesin,aruskas,kdbudget,jlh,rupiah,kodebarang,namabarang,satuan,jenis,noakun){
	document.getElementById('noakunmat').value=noakun;
	document.getElementById('jenismat').value=jenis;
	document.getElementById('aruskasmat').value=aruskas;
	document.getElementById('kdbudgetmat').value=kdbudget;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('namabarang').innerHTML=namabarang;
	document.getElementById('satuanmat').innerHTML=satuan;
	
	setValue2('noakunmat',noakun);
	setTimeout(function(){
		setValue2('aruskasmat',aruskas);
		setTimeout(function(){
			setValue2('jenismat',jenis);
			setTimeout(function(){
				setValue2('kdbudgetmat',kdbudget);
				alertify.closeAll();
				setTimeout(function(){
					alertify.popup().destroy();
					setValue2('kodebarang',kodebarang);
					setTimeout(function(){
						document.getElementById('jumlahmat').value=jlh;
						document.getElementById('hargamat').value = numberFormat(parseFloat(rupiah)/parseFloat(jlh),2);
						document.getElementById('ttlbyymat').value=rupiah;
						document.getElementById('index').value=index;
						if(index!=''){		
							document.getElementById('update').value='update';
							document.getElementById('mesin').value=mesin;
							setValue2('mesin',mesin);
						}else{
							document.getElementById('update').value='';
							document.getElementById('mesin').value='';
							setValue2('mesin',null);
						}
					}, 250);
				}, 250);
			}, 250);
		}, 250);
	}, 250);
	
	
}
function editalat(index,mesin,aruskas,kdbudget,jlh,rupiah,volume,rotasi,fisik,kodebarang,namabarang,satuan){
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
		document.getElementById('mesin').value=mesin;
	}else{
		document.getElementById('update').value='';
		document.getElementById('mesin').value='';
	}
}
function editkont(index,mesin,aruskas,kdbudget,jlh,rupiah,kodews){
	document.getElementById('aruskasmain').value=aruskas;
	document.getElementById('kdbudgetmain').value=kdbudget;
	document.getElementById('kodews').value=kodews;
	//getkodews('kdbudgetmain','kodews','edit');
	
	setValue2('kdbudgetmain',kdbudget);
	setTimeout(function(){
		setValue2('aruskasmain',aruskas);
		setTimeout(function(){
			setValue2('kodews',kodews);
			setTimeout(function(){
				document.getElementById('jamws').value=jlh;
				document.getElementById('ttlbyymain').value=rupiah;
				
				document.getElementById('index').value=index;
				if(index!=''){		
					document.getElementById('update').value='update';
					document.getElementById('mesin').value=mesin;
					setValue2('mesin',mesin);
				}else{
					document.getElementById('update').value='';
					document.getElementById('mesin').value='';
					setValue2('mesin',null);
				}
			}, 500);
		}, 500);
	}, 500);
	
	
}
function editvhc(index,mesin,aruskas,kdbudget,jlh,rupiah,kodevhc,satuan){
	document.getElementById('aruskasvhc').value=aruskas;
	document.getElementById('kdbudgetvhc').value=kdbudget;
	document.getElementById('kodevhc').value=kodevhc;
	
	setValue2('kdbudgetvhc',kdbudget);
	setTimeout(function(){
		setValue2('aruskasvhc',aruskas);
		setTimeout(function(){
			setValue2('kodevhc',kodevhc);
			setTimeout(function(){
				document.getElementById('satuanvhc').innerHTML=satuan;
				document.getElementById('jlhvhc').value=jlh;
				document.getElementById('ttlbyyvhc').value=rupiah;
				document.getElementById('index').value=index;
				if(index!=''){		
					document.getElementById('update').value='update';
					document.getElementById('mesin').value=mesin;
					setValue2('mesin',mesin);
				}else{
					document.getElementById('update').value='';
					document.getElementById('mesin').value='';
					setValue2('mesin',null);
				}
			}, 500);
		}, 500);
	}, 500);
}
function editkontrak(index,mesin,aruskas,kdbudget,jlh,rupiah,satuan, noakun, keterangan, kodebarang, namabarang){
	document.getElementById('keterangankontrak').value=keterangan;
	document.getElementById('noakunkont').value=noakun;
	document.getElementById('aruskaskont').value=aruskas;
	document.getElementById('kodebudgetkont').value=kdbudget;
	document.getElementById('satuankont').value=satuan;
	
	
	setValue2('kodebudgetkont',kdbudget);
	setTimeout(function(){
		setValue2('noakunkont',noakun);
		setTimeout(function(){
			setValue2('aruskaskont',aruskas);
			setTimeout(function(){
				setValue2('satuankont',satuan);
				setTimeout(function(){
					document.getElementById('volkont').value=jlh;
					document.getElementById('kodebarangkont').value=kodebarang;
					document.getElementById('namabarangkont').innerHTML=namabarang;
					document.getElementById('ttlbyykont').value=rupiah;
					hargakontrak=parseFloat(rupiah)/parseFloat(jlh);
					document.getElementById('hargakontrak').value=hargakontrak;
					document.getElementById('index').value=index;
					if(index!=''){		
						document.getElementById('update').value='update';
						document.getElementById('mesin').value=mesin;
						setValue2('mesin',mesin);
					}else{
						document.getElementById('update').value='';
						document.getElementById('mesin').value='';
						setValue2('mesin',null);
					}
				}, 500);
			}, 500);
		}, 500);
	}, 500);
	
}
function editlain(index,mesin,aruskas,kdbudget,jlh,rupiah,satuan, noakun, keterangan){
	document.getElementById('kodebudgetlain').value=kdbudget;
	document.getElementById('aruskaslain').value=aruskas;
	document.getElementById('noakunlain').value=noakun;
	
	setValue2('kodebudgetlain',kdbudget);
	setTimeout(function(){
		setValue2('aruskaslain',aruskas);
		setTimeout(function(){
			setValue2('noakunlain',noakun);
			setTimeout(function(){
				document.getElementById('keteranganlain').value=keterangan;
				document.getElementById('ttlbyylain').value=rupiah;
				document.getElementById('index').value=index;
				if(index!=''){		
					document.getElementById('update').value='update';
					document.getElementById('mesin').value=mesin;
					setValue2('mesin',mesin);
				}else{
					document.getElementById('update').value='';
					document.getElementById('mesin').value='';
					setValue2('mesin',null);
				}
			}, 500);
		}, 500);
	}, 500);
	
}
function deldetail(sumber,tahun,station,kdbudget,noakun,kodebarang,kodevhc,keterangan){
	param  = 'method=deldetail';
	param += '&tahun=' + tahun + '&kdbudget=' + kdbudget;
	param += '&station=' + station;
	param += '&noakun=' + noakun;
	param += '&kodebarang=' + kodebarang;
	param += '&kodevhc=' + kodevhc;
	param += '&keterangan=' + keterangan;
	
	tujuan = 'bgt_slave_byypabrik.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
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
					if (sumber == 'main') {
						loaddatamain();
					}
					if (sumber == 'vhc') {
						loaddatavhc();
					}
					if (sumber == 'kont') {
						loaddatakont();
					}
					if (sumber == 'lain') {
						loaddatalain();
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
	tujuan = 'bgt_slave_byypabrik.php';
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
					if (sumber == 'main') {
						loaddatamain();
					}
					if (sumber == 'vhc') {
						loaddatavhc();
					}
					if (sumber == 'kont') {
						loaddatakont();
					}
					if (sumber == 'lain') {
						loaddatalain();
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
	if(sumber=='main'){
		rowid = 'main_';
		colid = 'plusmain';
	}
	if(sumber=='vhc'){
		rowid = 'vhc_';
		colid = 'plusvhc';
	}
	if(sumber=='kont'){
		rowid = 'kont_';
		colid = 'pluskont';
	}
	if(sumber=='lain'){
		rowid = 'lain_';
		colid = 'pluslain';
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

function getdatadetail(tipe,tahun,kodeorg,station,mesin){	
	// width = '';
	// height = '';
	// content = "<fieldset style=\"width:98%;\"><div id=contpreviewdet align=center style=\"max-width:1000px;max-height:500px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog5(title, content, width, height, ev);
	
	if(tipe=='sdm'){
		loaddatasdm('',tahun,kodeorg,station,mesin,'popup');
	}
	if(tipe=='mat'){
		loaddatamat('',tahun,kodeorg,station,mesin,'popup');
	}
	if(tipe=='main'){
		loaddatamain('',tahun,kodeorg,station,mesin,'popup');
	}
	if(tipe=='vhc'){
		loaddatavhc('',tahun,kodeorg,station,mesin,'popup');
	}
	if(tipe=='kont'){
		loaddatakont('',tahun,kodeorg,station,mesin,'popup');
	}
	if(tipe=='lain'){
		loaddatalain('',tahun,kodeorg,station,mesin,'popup');
	}
}

function loaddatasdm(reloadall,tahun,kodeorg,station,mesin,tipe) {
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{		
		tahun  = document.getElementById('tahun').value;
		kodeorg= document.getElementById('kodeorg').value;
		station= document.getElementById('station').value;
		mesin  = document.getElementById('mesin').value;
	}
	
	
	param  = 'method=loaddatasdm';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('listdatasdm')!=undefined){						
						document.getElementById('listdatasdm').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					
					leftFixedTable();
					if(reloadall=='all'){
						loaddatamat(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatamat(reloadall,tahun,kodeorg,station,mesin,tipe){
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{		
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		station  = document.getElementById('station').value;
		mesin    = document.getElementById('mesin').value;
	}
	
	
	param  = 'method=loaddatamat';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('listdatamat')!=undefined){						
						document.getElementById('listdatamat').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					leftFixedTable();
					if(reloadall=='all'){
						loaddatamain(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatamain(reloadall,tahun,kodeorg,station,mesin,tipe){
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{		
		tahun   = document.getElementById('tahun').value;
		kodeorg = document.getElementById('kodeorg').value;
		station  = document.getElementById('station').value;
		mesin    = document.getElementById('mesin').value;
	}
	
	param  = 'method=loaddatamain';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('listdatakont')!=undefined){						
						document.getElementById('listdatakont').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					leftFixedTable();
					if(reloadall=='all'){
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
function loaddatavhc(reloadall,tahun,kodeorg,station,mesin,tipe){
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{  
		tahun  = document.getElementById('tahun').value;
		kodeorg= document.getElementById('kodeorg').value;
		station= document.getElementById('station').value;
		mesin  = document.getElementById('mesin').value;
	}
	
	param  = 'method=loaddatavhc';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('listdatavhc')!=undefined){						
						document.getElementById('listdatavhc').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					leftFixedTable();
					if(reloadall=='all'){
						loaddatakont(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatakont(reloadall,tahun,kodeorg,station,mesin,tipe){
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{  
		tahun  = document.getElementById('tahun').value;
		kodeorg= document.getElementById('kodeorg').value;
		station= document.getElementById('station').value;
		mesin  = document.getElementById('mesin').value;
	}
	
	param  = 'method=loaddatakont';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('listdatakontrak')!=undefined){						
						document.getElementById('listdatakontrak').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					leftFixedTable();
					if(reloadall=='all'){
						loaddatalain(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatalain(reloadall,tahun,kodeorg,station,mesin,tipe){
	if(tipe=='popup'){
		tahun  = tahun;
		kodeorg= kodeorg;
		station= station;
		mesin  = mesin;
	}else{  
		tahun  = document.getElementById('tahun').value;
		kodeorg= document.getElementById('kodeorg').value;
		station= document.getElementById('station').value;
		mesin  = document.getElementById('mesin').value;
	}
	
	param  = 'method=loaddatalain';
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&tipe='+tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(document.getElementById('loaddatalain')!=undefined){						
						document.getElementById('loaddatalain').innerHTML = con.responseText;
					}
					if(tipe=='popup'){
						alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					}
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardatasdm(){
	document.getElementById('jlhtk').value=0;
	document.getElementById('jhksdm').value=0;
	document.getElementById('ttlbyysdm').value=0;
}
function cleardatamat(){
	document.getElementById('jumlahmat').value=0;
	document.getElementById('ttlbyymat').value=0;
}

function cleardatamain(){
	//document.getElementById('kdbudgetmain').value='';
	document.getElementById('kodews').value='';
	document.getElementById('jamws').value='';
	document.getElementById('ttlbyymain').value=0;
}
function cleardatavhc(){
	document.getElementById('kodevhc').value='';
	document.getElementById('jlhvhc').value=0;
	document.getElementById('ttlbyyvhc').value=0;
}
function cleardatakont(){
	document.getElementById('kodebarangkont').value='';
	document.getElementById('namabarangkont').innerHTML='';
	document.getElementById('volkont').value='';
	document.getElementById('keterangankontrak').value='';
	document.getElementById('hargakontrak').value=0;
	document.getElementById('ttlbyykont').value=0;
}
function cleardatalain(){
	document.getElementById('keteranganlain').value='';
	document.getElementById('ttlbyykont').value=0;
}
function simpandetail(sumber) {
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	station= document.getElementById('station').value;
	mesin  = document.getElementById('mesin').value;
	update = document.getElementById('update').value;
	index  = document.getElementById('index').value;
	
	param  = '';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	param += '&update=' + update;
	param += '&index=' + index;
	
	if (sumber == 'sdm') {
		aruskas = document.getElementById('aruskassdm').value;
		kdbudget= document.getElementById('kdbudgetsdm').value;
		hke     = document.getElementById('hkesdm').value;
		jlhtk   = document.getElementById('jlhtk').value;
		jhk     = document.getElementById('jhksdm').value;
		rupiah  = document.getElementById('ttlbyysdm').value;
		
		param += '&method=simpansdm';
		param += '&kdbudget=' + kdbudget + '&hke=' + hke + '&jlhtk=' + jlhtk + '&jhk=' + jhk+ '&rupiah=' + rupiah;
	}
	if (sumber == 'mat') {
		aruskas   = document.getElementById('aruskasmat').value;
		noakun      = document.getElementById('noakunmat').value;
		kdbudget  = document.getElementById('kdbudgetmat').value;
		kodebarang= document.getElementById('kodebarang').value;
		jenis     = document.getElementById('jenismat').value;
		jumlah    = document.getElementById('jumlahmat').value;
		rupiah    = document.getElementById('ttlbyymat').value;
		satuan    = document.getElementById('satuanmat').innerHTML;
		
		param += '&method=simpanmat';
		param += '&satuan=' + satuan;
		param += '&noakun=' + noakun;
		param += '&jenis=' + jenis;
		param += '&kdbudget=' + kdbudget + '&kodebarang=' + kodebarang + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
	}
	if (sumber == 'main') {
		kdbudget  = document.getElementById('kdbudgetmain').value;
		aruskas   = document.getElementById('aruskasmain').value;
		kodews    = document.getElementById('kodews').value;
		jumlah    = document.getElementById('jamws').value;
		rupiah    = document.getElementById('ttlbyymain').value;
		
		param += '&method=simpanmain';
		param += '&aruskas=' + aruskas;
		param += '&kodews=' + kodews;
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
	
	if (sumber == 'kont') {
		aruskas   = document.getElementById('aruskaskont').value;
		kdbudget  = document.getElementById('kodebudgetkont').value;
		noakun    = document.getElementById('noakunkont').value;
		jumlah    = document.getElementById('volkont').value;
		rupiah    = document.getElementById('ttlbyykont').value;
		satuan    = document.getElementById('satuankont').value;
		keterangan= document.getElementById('keterangankontrak').value;
		kodebarang= document.getElementById('kodebarangkont').value;
		
		param += '&method=simpankont';
		param += '&satuan=' + satuan;
		param += '&noakun=' + noakun;
		param += '&keterangan=' + keterangan;
		param += '&kodebarang=' + kodebarang;
		param += '&kdbudget=' + kdbudget  + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
		if(keterangan==''){
			alertify.alert("Keterangan tidak boleh kosong"); return;
		}
	}
	if (sumber == 'lain') {
		kdbudget  = document.getElementById('kodebudgetlain').value;
		aruskas   = document.getElementById('aruskaslain').value;
		noakun    = document.getElementById('noakunlain').value;
		keterangan= document.getElementById('keteranganlain').value;
		rupiah    = document.getElementById('ttlbyylain').value;
		
		param += '&method=simpanlain';
		param += '&noakun=' + noakun;
		param += '&keterangan=' + keterangan;
		param += '&kdbudget=' + kdbudget  +'&rupiah=' + rupiah;
		if(keterangan==''){
			alertify.alert("Keterangan tidak boleh kosong"); return;
		}
	}
	
	param += '&aruskas=' + aruskas;
	tujuan = 'bgt_slave_byypabrik.php';
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
					if (sumber == 'main') {
						cleardatamain();
						loaddatamain(reloadall);
					}
					if (sumber == 'vhc') {
						cleardatavhc();
						loaddatavhc(reloadall);
					}
					if (sumber == 'kont') {
						cleardatakont();
						loaddatakont(reloadall);
					}
					if (sumber == 'lain') {
						cleardatalain();
						loaddatalain(reloadall);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getaruskas(sumber,idtujuan,akun,kontpks){
	param = '';
	if (sumber == 'sdm') {
		kdbudget = document.getElementById('kdbudgetsdm').value;
		param += '&kdbudget=' + kdbudget;
	}
	if (sumber == 'main') {
		kdbudget = document.getElementById('kdbudgetmain').value;
		param += '&kdbudget=' + kdbudget;
	}
	if (sumber == 'kont') {
		kdbudget = document.getElementById('kodebudgetkont').value;
		param += '&kdbudget=' + kdbudget;
	}
	if (sumber == 'vhc') {
		kdbudget = document.getElementById('kdbudgetvhc').value;
		param += '&kdbudget=' + kdbudget;
	}
	if (sumber == 'lain') {
		kdbudget = document.getElementById('kodebudgetlain').value;
		param += '&kdbudget=' + kdbudget;
	}
	
    param += '&akun=' + akun;
    param += '&kontpks=' + kontpks;
    param += '&method=getaruskas';
    tujuan = 'bgt_slave_byypabrik.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
					alertify.popup().destroy();
                } else {
					data=con.responseText.split("###");
                    document.getElementById(idtujuan).innerHTML = data[0];
					setValue2(idtujuan,data[0]);
					
					if (sumber == 'main') {
						getkodews('kdbudgetmain','kodews',sumber)
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getakunfromak(aruskas, id){
	param = '';
    param += '&kodeorg=' + getValue('kodeorg');
    param += '&aruskas=' + aruskas;
    param += '&method=getakun';
    tujuan = 'bgt_slave_byypabrik.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById(id).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkodews(idsumber,idtujuan,sumber){
	kodebgt = document.getElementById(idsumber).value;
	kodeorg = document.getElementById('kodeorg').value;
	param  = '';
    param += '&method=getkodews';
	param += '&kodeorg=' + kodeorg;
    tujuan = 'bgt_slave_byypabrik.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data=con.responseText.split("###");
                    document.getElementById(idtujuan).innerHTML = data[0];
					if(kodebgt=='PKSM'){
						document.getElementById('jamws').disabled = true;
						document.getElementById('kodews').disabled = true;
						document.getElementById('ttlbyymain').disabled = false;
						document.getElementById('kodews').innerHTML = '';
						if(sumber!='edit'){
							document.getElementById('ttlbyymain').value=0;
							document.getElementById('jamws').value = '';							
						}
					}else{
						if(sumber!='edit'){
							document.getElementById('jamws').value = '';
							document.getElementById('ttlbyymain').value=0;
						}
						document.getElementById('kodews').disabled = false;
						document.getElementById('ttlbyymain').disabled = true;
						document.getElementById('jamws').disabled = false;
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getjumlahws(){
	kodews = document.getElementById('kodews').value;
	kodebgt= document.getElementById('kdbudgetmain').value;
	tahun  = document.getElementById('tahun').value;
	jamws  = document.getElementById('jamws').value;
	
    param  = '';
    param += '&method=getjumlahws';
	param += '&kodews=' + kodews;
	param += '&kodebgt=' + kodebgt;
	param += '&tahun=' + tahun;
	param += '&jamws=' + jamws;
    tujuan = 'bgt_slave_byypabrik.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('ttlbyymain').value = trim(numberFormat(con.responseText));
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
	param     = 'tahun=' + tahun + '&kodeorg=' + kodeorg;
	
	if (sumber == 'sdm') {
		jlhtk = document.getElementById('jlhtk').value;
		hke = document.getElementById('hkesdm').value;
		jhk  = parseFloat(jlhtk)*parseFloat(hke);
		document.getElementById('jhksdm').value=numberFormat(jhk,2);
		if (jhk == '') {
			document.getElementById('jhksdm').value = '0';
		}
		kdbudget = document.getElementById('kdbudgetsdm').value;
		param += '&method=getupah' + '&jhk=' + jhk + '&kdbudget=' + kdbudget + '&hke=' + hke;
	}
	if (sumber == 'mat') {
		harga= document.getElementById('hargamat').value;
		jlh  = document.getElementById('jumlahmat').value;
		harga= remove_comma_var(harga);
		jlh  = remove_comma_var(jlh);
		rp   = parseFloat(harga)*parseFloat(jlh);
		if(isNaN(rp)){rp=0;}
		document.getElementById('ttlbyymat').value = numberFormat(rp);
	}
	if (sumber == 'alat') {
		norma= document.getElementById('normaalat').value;
		harga= document.getElementById('hargaalat').value;
		harga= remove_comma_var(harga);
		jlh  = parseFloat(totalvolume)*parseFloat(norma);
		rp   = parseFloat(harga)*parseFloat(jlh);
		if(isNaN(jlh)){jlh=0;}
		if(isNaN(rp)){rp=0;}
		document.getElementById('jumlahalat').value = numberFormat(jlh,2);
		document.getElementById('ttlbyyalat').value = numberFormat(rp);
	}
	
	if (sumber == 'vhc') {
		kodevhc = document.getElementById('kodevhc').value;
		kdbudget= document.getElementById('kdbudgetvhc').value;
		param += '&method=gethargavhc' + '&kodevhc=' + kodevhc + '&kdbudget=' + kdbudget;
	}
	
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (sumber == 'sdm') {
						data = con.responseText.split("####");
						document.getElementById('ttlbyysdm').value = trim(data[0]);
					}
					if (sumber == 'vhc') {
						jlh = document.getElementById('jlhvhc').value;
						data = con.responseText.split("####");
						rp = parseFloat(trim(data[0]))*parseFloat(jlh);
						if(isNaN(rp)){rp=0;}
						document.getElementById('ttlbyyvhc').value = numberFormat(rp);
						document.getElementById('satuanvhc').innerHTML = trim(data[1]);
					}
					if (sumber == 'kont') {
						vol  = document.getElementById('volkont').value;
						harga= document.getElementById('hargakontrak').value;
						rp = parseFloat(vol)*parseFloat(harga);
						document.getElementById('ttlbyykont').value = numberFormat(rp);
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
	document.getElementById('station').disabled = true;
	document.getElementById('mesin').disabled = true;
}
function enableheader(){
	document.getElementById('tahun').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('station').disabled = false;
	document.getElementById('mesin').disabled = false;
}
function simpanheader() {
	tahun      = document.getElementById('tahun').value;
	kodeorg    = document.getElementById('kodeorg').value;
	station     = document.getElementById('station').value;
	mesin       = document.getElementById('mesin').value;
	
	param  = 'method=simpanheader';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&mesin=' + mesin;
	
	tujuan = 'bgt_slave_byypabrik.php';
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
					// document.getElementById('aruskassdm').innerHTML = data[3];
					// document.getElementById('aruskasmat').innerHTML = data[3];
					// document.getElementById('aruskasalat').innerHTML = data[3];
					// document.getElementById('aruskaskont').innerHTML = data[3];
					// document.getElementById('aruskasvhc').innerHTML = data[3];
					disableheader();
					
					loaddatasdm('all');
					
					cleardatasdm();
					cleardatamat();
					cleardatamain();
					cleardatavhc();
					cleardatakont();
					cleardatalain();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function gettotalfisik(sumber){
	if(sumber=='fis' || sumber=='rot'){
		fis = document.getElementById('volume').value;
		rot = document.getElementById('rotasi').value;
		if(fis!='' && rot !=''){
			document.getElementById('totalvolume').value=parseFloat(fis)*parseFloat(rot);
		}else{
			document.getElementById('totalvolume').value=0;
		}
	}
	if(sumber=='ttl'){
		ttl = document.getElementById('totalvolume').value;
		fis = document.getElementById('volume').value;
		if(ttl!='' && fis !=''){
			hasil = parseFloat(ttl)/parseFloat(fis);
			document.getElementById('rotasi').value=hasil;
		}else{
			document.getElementById('rotasi').value=0;
		}
	}
}
function preview(tahunbudget,station,tipe){	
	// width = '';
	// height = '';
	// content = "<fieldset><div id=contpreview align=center style=\"max-width:1000px;max-height:500px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	
	param  = 'method=rekappermesin';
	param += '&tahun=' + tahunbudget;
	param += '&station=' + station;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contpreview').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function preview2(tahunbudget,station,tipe){	
	width = '';
	height = '';
	content = "<fieldset style=\"width:1000px;\"><div id=contpreview2 align=center style=\"width:1000px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog5(title, content, width, height, ev);
	
	param  = 'method=rekappermesin';
	param += '&tahun=' + tahunbudget;
	param += '&station=' + station;
	param += '&tipe=' + tipe;
	tujuan = 'bgt_slave_byypabrik.php';
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

function del(tahunbudget,station) {
	param = 'method=del';
	param += '&tahun=' + tahunbudget;
	param += '&station=' + station;
	tujuan = 'bgt_slave_byypabrik.php';
	if (confirm('Anda yakin ???')) {
		if (confirm('Anda yakin ingin menghapus ???')) {				
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
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delrekapmesin(tahunbudget,mesin) {
	param = 'method=delrekapmesin';
	param += '&tahun=' + tahunbudget;
	param += '&mesin=' + mesin;
	tujuan = 'bgt_slave_byypabrik.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					preview(tahunbudget,mesin.substr(0,6),'html');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(tahunbudget,kodeorg, station) {
	param = 'method=unposting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	tujuan = 'bgt_slave_byypabrik.php';
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
					setTimeout(function(){
						getPage();
					}, 500);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(tahunbudget,kodeorg,station){
	param = 'method=posting';
	param += '&tahun=' + tahunbudget;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	tujuan = 'bgt_slave_byypabrik.php';
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
					setTimeout(function(){
						getPage();
					}, 500);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function batalheader(){
	document.getElementById('kodeorg').value='';
	document.getElementById('station').value='';
	document.getElementById('mesin').value='';
	document.getElementById('listdatasdm').innerHTML = "";
	document.getElementById('listdatamat').innerHTML = "";
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	enableheader();
}

function sebarkan(row,maxrow,jenis){
	row   = document.getElementById('awalsebar').value;
	maxrow= document.getElementById('akhirsebar').value;
	
	if(jenis=='1'){
		//per station
		if(maxrow =='' || maxrow ==0){
			alert('Data tidak ditemukan, proses dibatalkan !');
			return;
		}
		if(confirm("Anda yakin ???")){
			sebartt(row,maxrow);
		}
	}else if(jenis=='2'){
		//per detail
		limitrow = 100;
		sebardetail(row,maxrow,limitrow,limitrow);
	}else if(jenis=='3'){
		//rekap per station
		limitrow = 100;
		sebardetail(row,maxrow,limitrow,limitrow);
	}
}

function sebartt(row,maxrow){
	row     = parseFloat(row);
	param   = '';
	tahun   = document.getElementById('tahun'+row).innerHTML;
	station  = document.getElementById('station'+row).innerHTML;
	
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}

	param += '&tahun=' + tahun;
	param += '&station=' + station;
	param += '&method=sebartt';

	tujuan = 'bgt_slave_byypabrik.php';
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
						alert("done");
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
	param  = '';
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}
	
	
	index= document.getElementById('index'+row).innerHTML;
	param += '&index[]=' + index;
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	param += '&method=sebardetail';
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
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
						alert("done");
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

function sebarperstation(row,maxrow,limitrow,limitawal){
	row     = parseFloat(row);
	param  = '';
	for (i = 1; i <= 12; i++) {
		persen= document.getElementById('persen_'+i).value;
		param += '&persen[' + i + ']=' + persen;
	}
	
	
	index= document.getElementById('index'+row).innerHTML;
	param += '&index[]=' + index;
	
	document.getElementById('rowsebar'+row).style.backgroundColor='cyan';
	document.getElementById('chkboxsebar'+row).checked=true;
	
	tahun   = document.getElementById('tahun'+row).innerHTML;
	station  = document.getElementById('station'+row).innerHTML;
	kodebudget  = document.getElementById('kodebudget'+row).innerHTML;
	kodebarang  = document.getElementById('kodebarang'+row).innerHTML;
	kodevhc  = document.getElementById('kodevhc'+row).innerHTML;
	
	param += '&tahun=' + tahun;
	param += '&station=' + station;
	param += '&kodebudget=' + kodebudget;
	param += '&kodebarang=' + kodebarang;
	param += '&kodevhc=' + kodevhc;
	param += '&method=sebarperstation';
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
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
						alert("done");
						getPageSbr();
						if(maxrow != undefined){
							//document.getElementById('awalbaris').value=row;
						}
					} else {
						sebarperstation(row,maxrow,limitrow,limitawal);
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

function getstation(sumber,idhasil,bahasa){
	tahun  = document.getElementById('tahun').value;
	kodeorg= sumber.value;
	param = 'method=getstation';
	param += '&kodeorg=' + kodeorg;
	param += '&bahasa=' + bahasa;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_byypabrik.php';
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

function getmesin(sumber,idhasil,bahasa){
	tahun  = document.getElementById('tahun').value;
	station= sumber.value;
	param = 'method=getmesin';
	param += '&station=' + station;
	param += '&bahasa=' + bahasa;
	param += '&tahun=' + tahun;
	tujuan = 'bgt_slave_byypabrik.php';
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
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function editdetail(tahunbudget,kodeorg,station){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formcari').style.display = 'none';
	document.getElementById('listdatasdm').innerHTML = "";
	document.getElementById('listdatamat').innerHTML = "";
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatakontrak').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	
	document.getElementById('tahun').value=tahunbudget;
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('station').value=station;
	document.getElementById('mesin').value='';
	
	setValue2('kodeorg',kodeorg);
	setTimeout(function(){
		setValue2('station',station);	
		setTimeout(function(){
			simpanheader();
		},200);		
	},200);
	
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
	document.getElementById('listdatakont').innerHTML = "";
	document.getElementById('listdatakontrak').innerHTML = "";
	document.getElementById('listdatavhc').innerHTML = "";
	
	loaddatasdm('all');
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
	station  = document.getElementById('stationsbr').value;
	
	sebaran = document.getElementById('sebaran').value;
	jlhbaris= document.getElementById('jlhbaris').value;
	tampilkan= document.getElementById('tampilkan').value;
	
	
	param  = 'method=showsebaran&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&sebaran=' + sebaran + '&jlhbaris=' + jlhbaris;
	param += '&tampilkan=' + tampilkan;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(tampilkan=='2'){
						document.getElementById('mesinsebar').style.display="";
						document.getElementById('kdbgtsebar').style.display="";
						document.getElementById('kdbrgsebar').style.display="";
						document.getElementById('kdvhcsebar').style.display="";		
					}else if(tampilkan=='3'){
						document.getElementById('mesinsebar').style.display="none";
						document.getElementById('kdbgtsebar').style.display="";
						document.getElementById('kdbrgsebar').style.display="";
						document.getElementById('kdvhcsebar').style.display="";		
					}else{
						document.getElementById('mesinsebar').style.display="none";
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
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contpostingdata').innerHTML = con.responseText;
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
	tujuan = 'bgt_slave_byypabrik.php';
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
	tahun  = document.getElementById('tahunsch').value;
	kodeorg= document.getElementById('kodeorgsch').value;
	station= document.getElementById('stationsch').value;
	
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	tujuan = 'bgt_slave_byypabrik.php';
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
	station = document.getElementById('stationsch').value;
	tt     = document.getElementById('ttsch').value;
	sebaran= document.getElementById('sebaransch').value;
	ip     = document.getElementById('ipsch').value;
	
	
	param  = 'method=loaddata&page=' + page;
	param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
	param += '&station=' + station + '&tt=' + tt;
	param += '&sebaran=' + sebaran + '&ip=' + ip;
	param += '&jenis=excel';
	
	tujuan= 'bgt_slave_byypabrik.php';
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
	document.getElementById('stationsch').value='';
	document.getElementById('ttsch').value='';
	document.getElementById('sebaransch').value='';
	document.getElementById('ipsch').value='';
	loaddata(0);
}


function showformupload(ev) {
	ev = 'event';
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;'></div></fieldset>";
	showDialog2(title, content, width, height, ev);
}

function showupload(ev){
	tahun  = document.getElementById('tahun').value;
	kodeorg= document.getElementById('kodeorg').value;
	station= document.getElementById('station').value;
	noakun = document.getElementById('noakunmat').value;
	aruskas= document.getElementById('aruskasmat').value;
	jenis  = document.getElementById('jenismat').value;
	if(tahun==''){
		alert("Tahun wajib diisi."); return;
	}
	if(kodeorg==''){
		alert("Kode unit wajib diisi."); return;
	}
	if(station==''){
		alert("Kode station wajib diisi."); return;
	}
	if(noakun==''){
		//alert("Kode akun wajib diisi."); return;
	}
	if(aruskas==''){
		//alert("Kode aruskas wajib diisi."); return;
	}
	if(jenis==''){
		//alert("Jenis wajib diisi."); return;
	}
	
	// showformupload(ev);
	param  = 'method=showupload';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&noakun=' + noakun;
	param += '&aruskas=' + aruskas;
	param += '&jenis=' + jenis;
	tujuan = 'bgt_slave_byypabrik.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}


function fileSelected(jenis){
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	station = document.getElementById('station').value;
	noakun  = document.getElementById('noakunmat').value;
	aruskas = document.getElementById('aruskasmat').value;
	jenismat= document.getElementById('jenismat').value;
	
	if(tahun==''){
		alert("Tahun wajib diisi."); return;
	}
	if(kodeorg==''){
		alert("Kode unit wajib diisi."); return;
	}
	if(station==''){
		alert("Kode station wajib diisi."); return;
	}
	if(noakun==''){
		//alert("Kode akun wajib diisi."); return;
	}
	if(aruskas==''){
		//alert("Kode aruskas wajib diisi."); return;
	}
	if(jenis==''){
		//alert("Jenis wajib diisi."); return;
	}
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("tahun", tahun);
	formdata.append("kodeorg", kodeorg);
	formdata.append("station", station);
	formdata.append("noakun", noakun);
	formdata.append("aruskas", aruskas);
	formdata.append("jenismat", jenismat);
	formdata.append("jenis", jenis);
	
	if(jenis=='simpan'){
		alert("Hanya barang yang memiliki harga yg akan disimpan.");
	}
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "bgt_slave_byypabrik.php?method=fileSelected", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon(){
        if (con.readyState == 4){
			if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
					if(jenis=='simpan'){
						//closeDialog2();
						alertify.popup().destroy();
						loaddatamat();
						alert("Done");
					}else{						
						document.getElementById('listfiles').innerHTML=con.responseText;
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

function downloadmaster(){
	tahun   = document.getElementById('tahun').value;
	kodeorg = document.getElementById('kodeorg').value;
	station = document.getElementById('station').value;
	noakun  = document.getElementById('noakunmat').value;
	aruskas = document.getElementById('aruskasmat').value;
	jenismat= document.getElementById('jenismat').value;

	param  = 'method=downloadmaster';
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&station=' + station;
	param += '&noakun=' + noakun;
	param += '&aruskas=' + aruskas;
	param += '&jenis=' + jenis;
	ev   = 'event';
	title="Master Data";
	printnopopup("bgt_slave_byypabrik.php?"+param);
	
	//showDialog1(title,"<iframe frameborder=0 style='width:890px;min-height:400px'"+"src='bgt_slave_byypabrik.php?"+param+"'></iframe>",'900','400',ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';
}