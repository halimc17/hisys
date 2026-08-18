function createNew(){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('detail').style.display ='none';
    document.getElementById('detail2').style.display ='none';
    document.getElementById('listData').style.display ='none';
    document.getElementById('methodht').value ='saveheader';
    batalcari();
    bersih();
}

function displayList(){
    document.getElementById('addNew').style.display ='none';
    document.getElementById('detail').style.display ='none';
    document.getElementById('detail2').style.display ='none';
    document.getElementById('listData').style.display ='block';
    loadData(getValue('halaman'));
    bersih();
    batalcari();
}

function batalcari(){
	document.getElementById('notransaksisch').value='';
	document.getElementById('tanggalsch').value='';
	document.getElementById('kodeorgsch').value='';
	document.getElementById('statussch').value='';
}

function bersih(){
	setValue('notransaksi','');
	setValue('notransaksidt','');
	setValue('tanggal','');
	setValue('nama','');
	setValue('supplierid','');
	setValue('halaman','0');
	setValue('methodht','saveheader');
    document.getElementById('tanggal').disabled=false;
    document.getElementById('nama').disabled=false;
}

function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	paged   = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num){
	notransaksisch  = document.getElementById('notransaksisch').value;
	tanggalsch      = document.getElementById('tanggalsch').value;
	kodeorgsch      = document.getElementById('kodeorgsch').value;
	statussch       = document.getElementById('statussch').value;

    param   ='method=loadData&page=' + num;
    if(notransaksisch != ''){      
        param  +='&notransaksisch=' + notransaksisch.trim();
    }
    if(tanggalsch != ''){
        param  +='&tanggalsch=' + tanggalsch.trim();
    }
    if(kodeorgsch != ''){
        param  +='&kodeorgsch=' + kodeorgsch.trim();
    }
    if(statussch != ''){
        param  +='&statussch=' + statussch.trim();
    }
    tujuan  ='lgl_slave_penawaranharga.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    dataSlave = con.responseText.split("####");
                    document.getElementById('addNew').style.display ='none';
                    document.getElementById('listData').style.display ='block';
                    document.getElementById('container').innerHTML      = dataSlave[0];
                    document.getElementById('footData').innerHTML       = dataSlave[1];
                    setValue('halaman',num);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}

function fillField(notransaksi,tanggal,nama){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('detail').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('btnnext').style.display ='';
    document.getElementById('tanggal').disabled = true;
    document.getElementById('nama').disabled = true;
    setValue('notransaksi',notransaksi);
    setValue('tanggal',tanggal);
    setValue('nama',nama);
    opendetail(notransaksi);
}

function opendetail(notransaksi){
    document.getElementById('detail').style.display = 'block';
    param       ='notransaksi='+notransaksi.trim()+'&method=opendetail';
    tujuan      ='lgl_slave_penawaranharga.php';
    post_response_text(tujuan,param,respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    a = con.responseText.split('#');
                    document.getElementById('containerdetail').innerHTML = a[0];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function saveheader(){
    notransaksi = getValue('notransaksi');
    tanggal     = getValue('tanggal');
    nama        = getValue('nama');
    supplierid  = getValue('supplierid');
    method      = getValue('methodht');

    if(tanggal=='')
    {
        alert('Harap mengisi tanggal.');return;
	}
    else if(nama=='')
    {
        alert('Harap mengisi nama project.');return;
	}
	else if(supplierid=='')
    {
        alert('Supplierid');return;
	}
	
    param   ='method=' + method;
    param  +='&notransaksi=' + notransaksi;
    param  +='&tanggal=' + tanggal;
    param  +='&nama=' + nama;
    param  +='&supplierid=' + supplierid;
    tujuan  ='lgl_slave_penawaranharga.php';
    post_response_text(tujuan, param, respog);		

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    setValue('notransaksi',con.responseText);
                    setValue('supplierid',null);
                    document.getElementById('tanggal').disabled = true;
                    document.getElementById('nama').disabled = true;
                    document.getElementById('btnnext').style.display ='';
					opendetail(con.responseText);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function hapus(notransaksi,supplierid,nmsup){
    param   ='method=delete'+'&notransaksi='+notransaksi+'&supplierid='+supplierid;
    tujuan  ='lgl_slave_penawaranharga.php';

	alertify.confirm("Informasi","Anda Yakin Menghapus : "+notransaksi+" dan supplier "+nmsup+" ???",
    function(){
        post_response_text(tujuan, param, respon);
    },
    function(){
        return;
    }
    );

    function respon()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    d = con.responseText.split('#');
                    document.getElementById('containerdetail').innerHTML=d[0];
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
                    // if(d[1] == 1){  
                        // bersih();
                        // document.getElementById('detail').style.display ='none';
                        alertify.success('Data berhasil di hapus, silahkan inputkan lagi.');
                        opendetail(notransaksi)
                    // }else{
                    //     alertify.success('Data berhasil di hapus');
                    // }
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}

function del(notransaksi,page){
    param   ='method=del'+'&notransaksi='+notransaksi;
    tujuan  ='lgl_slave_penawaranharga.php';

	alertify.confirm("Informasi","Anda Yakin ingin Menghapus : "+notransaksi+" ?",
    function(){
        post_response_text(tujuan, param, respon);
    },
    function(){
        return;
    }
    );

    function respon()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containerdetail').innerHTML=con.responseText;
                    alertify.set('notifier','position', 'top-right');
                    alertify.set('notifier','delay', 5);
                    alertify.success('Data dengan notransaksi '+notransaksi+' Berhasil dihapus');
                    loadData(page);
                }
            }else{
                busy_off();
                error_catch(con.status);
                bersih();
            }
        }	
    }	
}

function bukaharga(notransaksi,max) {
    if(notransaksi == undefined){
        notransaksi = getValue('notransaksi');
    }else{
        notransaksi = notransaksi;
    }
    if(notransaksi == ''){
        alertify.alert('Informasi',"Harap mengisi minimal 1 Kode Assignment (Kontraktor)");
        return;
    }
    document.getElementById('listData').style.display ='none';
    document.getElementById('addNew').style.display ='none';
    document.getElementById('detail').style.display ='none';
    document.getElementById('detail2').style.display = 'block';
    
    param   ='method=bukaharga';
    param  +='&notransaksi=' + notransaksi;
    tujuan  ='lgl_slave_penawaranharga.php';
    post_response_text(tujuan, param, respog);		

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('listharga').innerHTML = con.responseText;
                    document.getElementById('notransaksidt').value = notransaksi;
                    hitungtax(max)
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function hitungrat(){
    rpsat   = getValue('rpsat');
    luas    = getValue('luas');

    if(rpsat == '' || rpsat == 0){
        alertify.alert('Rp/Sat harus diisi terlebih dahulu.');
    }

    //Perhitungan Nominal
    nominal = parseFloat(remove_comma_var(rpsat)) * parseFloat(remove_comma_var(luas));
    if(isNaN(nominal) == true){
        nominal = 0;
    }else{
        nominal = numberFormat(nominal)
    }
    setValue('rupiah',nominal);
}

function hitungoff(sup,nama){
    luas    = getValue('luas');
    rpsat   = getValue('rpsatoff_'+sup);

    if(luas == '' || luas == 0){
        alertify.alert('Luas harus diisi terlebih dahulu.');
        setValue('rpsatoff_'+sup,'');
    }else if(getValue('tax') == '' || getValue('tax') == 0){
        alertify.alert('Pajak harus diisi terlebih dahulu.');
        setValue('rpsatoff_'+sup,'');
    }else if(rpsat == '' || rpsat == 0){
        // alertify.alert('RP/Sat Penawaran '+nama+' harus diisi terlebih dahulu.');
        // setValue('rpsatoff_'+sup,'');
    }

    //Perhitungan pajak
    pajak   = parseFloat(remove_comma_var(getValue('tax'))) * (parseFloat(remove_comma_var(rpsat)) * parseFloat(remove_comma_var(luas)))/100;

    //Perhitungan Nominal
    nominal =   (parseFloat(remove_comma_var(rpsat)) * parseFloat(remove_comma_var(luas)));
    if(isNaN(nominal) == true){
        nominal = 0;
    }else{
        nominal = numberFormat(nominal)
    }
    setValue('rupiahoff_'+sup,nominal);
    
    //Perhitungan Varian
    varian = parseFloat(remove_comma_var(nominal)) - parseFloat(remove_comma_var(getValue('rupiahnego_'+sup)))
    if(isNaN(varian) == true){
        varian = '';
    }else{
        varian = numberFormat(varian)
    }
    document.getElementById('varrp_'+sup).innerHTML = varian
}

function hitungnego(sup,nama){
    luas    = getValue('luas');
    rpsat   = getValue('rpsatnego_'+sup);

    if(luas == '' || luas == 0){
        alertify.alert('Luas harus diisi terlebih dahulu.');
        setValue('rpsatnego_'+sup,'');
    }else if(getValue('tax') == '' || getValue('tax') == 0){
        alertify.alert('Pajak harus diisi terlebih dahulu.');
        setValue('rpsatnego_'+sup,'');
    }else if(rpsat == '' || rpsat == 0){
        alertify.alert('RP/Sat Negosiasi '+nama+' harus diisi terlebih dahulu.');
    }

    //Perhitungan pajak
    pajak   = parseFloat(remove_comma_var(getValue('tax'))) * (parseFloat(remove_comma_var(rpsat)) * parseFloat(remove_comma_var(luas)))/100;

    //Perhitungan Nominal
    nominal = parseFloat(remove_comma_var(rpsat)) * parseFloat(remove_comma_var(luas));
    if(isNaN(nominal) == true){
        nominal = 0;
    }else{
        nominal = numberFormat(nominal)
    }
    setValue('rupiahnego_'+sup,nominal);
    
    //Perhitungan Varian
    varian = parseFloat(remove_comma_var(getValue('rupiahoff_'+sup))) - parseFloat(remove_comma_var(nominal));
    if(isNaN(varian) == true){
        varian = '';
    }else{
        varian = numberFormat(varian)
    }
    document.getElementById('varrp_'+sup).innerHTML = varian
}

function hitungtax(max){
    pajak   = getValue('tax');
    luas    = getValue('luas');
    rpsat   = remove_comma_var(getValue('rpsat'));
    
    taxrpsat= parseFloat(rpsat) * parseFloat(pajak)/100;
    for (let i = 1; i <= max; i++) {
        rpsatoff    = remove_comma_var(getValue('rpsatoff_'+i));
        rpoff       = remove_comma_var(getValue('rupiahoff_'+i));
        rptaxoff    = parseFloat(rpsatoff) * parseFloat(luas);
        rupiahtaxoff= parseFloat(rptaxoff) * parseFloat(pajak)/100;
        fixrupiahoff= parseFloat(rpoff) - parseFloat(rupiahtaxoff)
        fixrpsatoff = parseFloat(fixrupiahoff) / parseFloat(luas);

        if(pajak == '' || pajak == 0){
            setValue('taxrupiahoff_'+i,'');
            setValue('fixrupiahoff_'+i,'');
            setValue('fixrpsatoff_'+i,'');
        }else{
            if(isNaN(rupiahtaxoff) == true || isNaN(fixrupiahoff) == true || isNaN(fixrpsatoff) == true){
                rupiahtaxoff = ''; 
                fixrupiahoff = ''; 
                fixrpsatoff = ''; 
            }else{
                rupiahtaxoff = rupiahtaxoff; 
                fixrupiahoff = fixrupiahoff; 
                fixrpsatoff = fixrpsatoff; 
            }
            setValue('taxrupiahoff_'+i,numberFormat(rupiahtaxoff));
            setValue('fixrupiahoff_'+i,numberFormat(fixrupiahoff));
            setValue('fixrpsatoff_'+i,numberFormat(fixrpsatoff));
        }

        rpsatnego    = remove_comma_var(getValue('rpsatnego_'+i));
        rpnego       = remove_comma_var(getValue('rupiahnego_'+i));
        rptaxnego    = parseFloat(rpsatnego) * parseFloat(luas);
        rupiahtaxnego= parseFloat(rptaxnego) * parseFloat(pajak)/100;
        
        fixrupiahnego= parseFloat(rpnego) - parseFloat(rupiahtaxnego)
        fixrpsatnego = parseFloat(fixrupiahnego) / parseFloat(luas);

        if(pajak == '' || pajak == 0){
            setValue('taxrupiahnego_'+i,'');
            setValue('fixrupiahnego_'+i,'');
            setValue('fixrpsatnego_'+i,'');
        }else{
            if(isNaN(rupiahtaxnego) == true || isNaN(fixrupiahnego) == true || isNaN(fixrpsatnego) == true){
               rupiahtaxnego = ''; 
               fixrupiahnego = ''; 
               fixrpsatnego = ''; 
            }
            setValue('taxrupiahnego_'+i,numberFormat(rupiahtaxnego));
            setValue('fixrupiahnego_'+i,numberFormat(fixrupiahnego));
            setValue('fixrpsatnego_'+i,numberFormat(fixrpsatnego));
        }
        
        //Perhitungan Varian
        varian = 0;
        varian = parseFloat(rpoff) - parseFloat(rpnego)
        if(isNaN(varian) == true){
            varian = '';
        }else{
            varian = numberFormat(varian)
        }
        document.getElementById('varrp_'+i).innerHTML = varian
        
        //Perhitungan Varian sebelumpajak
        taxvarian = 0;
        taxvarian = parseFloat(fixrupiahoff) - parseFloat(fixrupiahnego)
        if(isNaN(taxvarian) == true){
            taxvarian = '';
        }else{
            taxvarian = numberFormat(taxvarian)
        }
        document.getElementById('fixvarrp_'+i).innerHTML = taxvarian
    }
}

function phitungtax(max){
    pajak   = getValue('ptax');
    luas    = getValue('pluas');
    rpsat   = remove_comma_var(getValue('prpsat'));
    
    taxrpsat= parseFloat(rpsat) * parseFloat(pajak)/100;
    for (let i = 1; i <= max; i++) {
        rpsatoff    = remove_comma_var(getValue('prpsatoff_'+i));
        rpoff       = remove_comma_var(getValue('prupiahoff_'+i));
        rptaxoff    = parseFloat(rpsatoff) * parseFloat(luas);
        rupiahtaxoff= parseFloat(rptaxoff) * parseFloat(pajak)/100;
        fixrupiahoff= parseFloat(rpoff) - parseFloat(rupiahtaxoff)
        fixrpsatoff = parseFloat(fixrupiahoff) / parseFloat(luas);

        if(pajak == '' || pajak == 0){
            setValue('ptaxrupiahoff_'+i,'');
            setValue('pfixrupiahoff_'+i,'');
            setValue('pfixrpsatoff_'+i,'');
        }else{
            if(isNaN(rupiahtaxoff) == true || isNaN(fixrupiahoff) == true || isNaN(fixrpsatoff) == true){
                rupiahtaxoff = ''; 
                fixrupiahoff = ''; 
                fixrpsatoff = ''; 
            }else{
                rupiahtaxoff = rupiahtaxoff; 
                fixrupiahoff = fixrupiahoff; 
                fixrpsatoff = fixrpsatoff; 
            }
            setValue('ptaxrupiahoff_'+i,numberFormat(rupiahtaxoff));
            setValue('pfixrupiahoff_'+i,numberFormat(fixrupiahoff));
            setValue('pfixrpsatoff_'+i,numberFormat(fixrpsatoff));
        }

        rpsatnego    = remove_comma_var(getValue('prpsatnego_'+i));
        rpnego       = remove_comma_var(getValue('prupiahnego_'+i));
        rptaxnego    = parseFloat(rpsatnego) * parseFloat(luas);
        rupiahtaxnego= parseFloat(rptaxnego) * parseFloat(pajak)/100;
        
        fixrupiahnego= parseFloat(rpnego) - parseFloat(rupiahtaxnego)
        fixrpsatnego = parseFloat(fixrupiahnego) / parseFloat(luas);

        if(pajak == '' || pajak == 0){
            setValue('ptaxrupiahnego_'+i,'');
            setValue('pfixrupiahnego_'+i,'');
            setValue('pfixrpsatnego_'+i,'');
        }else{
            if(isNaN(rupiahtaxnego) == true || isNaN(fixrupiahnego) == true || isNaN(fixrpsatnego) == true){
               rupiahtaxnego = ''; 
               fixrupiahnego = ''; 
               fixrpsatnego = ''; 
            }
            setValue('ptaxrupiahnego_'+i,numberFormat(rupiahtaxnego));
            setValue('pfixrupiahnego_'+i,numberFormat(fixrupiahnego));
            setValue('pfixrpsatnego_'+i,numberFormat(fixrpsatnego));
        }
        
        //Perhitungan Varian
        varian = 0;
        varian = parseFloat(rpoff) - parseFloat(rpnego)
        if(isNaN(varian) == true){
            varian = '';
        }else{
            varian = numberFormat(varian)
        }
        document.getElementById('pvarrp_'+i).innerHTML = varian
        
        //Perhitungan Varian sebelumpajak
        taxvarian = 0;
        taxvarian = parseFloat(fixrupiahoff) - parseFloat(fixrupiahnego)
        if(isNaN(taxvarian) == true){
            taxvarian = '';
        }else{
            taxvarian = numberFormat(taxvarian)
        }
        document.getElementById('pfixvarrp_'+i).innerHTML = taxvarian
    }
}


function simpandetail(max){
    if(remove_comma_var(document.getElementById('varrp_1').innerHTML) == '' || remove_comma_var(document.getElementById('varrp_1').innerHTML) == '0')
    {
        alertify.alert('Informasi','Harap lengkapi pengisian.');return;
	}

    method      = getValue('methoddt');
    rpsat       = getValue('rpsat');
    luas        = getValue('luas');
    rupiah      = getValue('rupiah');
    tax         = getValue('tax');

    param   ='method=' + method;
    param  +='&rpsat=' + remove_comma_var(rpsat);
    param  +='&luas=' + remove_comma_var(luas);
    param  +='&rupiah=' + remove_comma_var(rupiah);
    param  +='&tax=' + remove_comma_var(tax);
    param  +='&max=' + max;
    param  +='&notransaksi=' + getValue('notransaksidt');

    for (let i = 1; i <= max; i++) {
        rpsatoff        = getValue('rpsatoff_'+i);
        rupiahoff       = getValue('rupiahoff_'+i);
        rpsatnego       = getValue('rpsatnego_'+i);
        rupiahnego      = getValue('rupiahnego_'+i);
        taxrupiahoff    = getValue('taxrupiahoff_'+i);
        taxrupiahnego   = getValue('taxrupiahnego_'+i);
    
        param  +='&rpsatoff_'+i+'=' + remove_comma_var(rpsatoff);
        param  +='&rupiahoff_'+i+'=' + remove_comma_var(rupiahoff);
        param  +='&rpsatnego_'+i+'=' + remove_comma_var(rpsatnego);
        param  +='&rupiahnego_'+i+'=' + remove_comma_var(rupiahnego);
        param  +='&taxrupiahoff_'+i+'=' + remove_comma_var(taxrupiahoff);
    }
    tujuan  ='lgl_slave_penawaranharga.php';
    post_response_text(tujuan, param, respog);		

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alertify.set('notifier','position', 'top-right');
                    alertify.set('notifier','delay', 5);
                    alertify.success('Data Berhasil disimpan');
                    displayList()
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}


function html(notransaksi,max) {
	param = 'method=html' + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_penawaranharga.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup().set({'title':'Detail Perbandingan Harga Project','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('85%','55%').show();
                    phitungtax(max)
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function ajukan() {
    jumlahlevel = document.getElementById('numrow').value;
    kepada = '';
    for (var i = 1; i <= jumlahlevel; i++) {
        if (kepada == '') {
            kepada = document.getElementById('kepada' + i).value;
        } else {
            kepada += '###' + document.getElementById('kepada' + i).value;
        }
    }
    notransaksi 		= document.getElementById('notran_aju').innerHTML;
    jenispersetujuanx 	= document.getElementById('jenispersetujuanx').value;
    param 				= 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada + '&jenispersetujuanx=' + jenispersetujuanx;
    if (kepada == '') {
        alert('Isikan nama penyetuju.');
        return;
    }
    tujuan = 'lgl_slave_penawaranharga.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil ajukan.');
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showupload(notrans,jenisupload){
	var notransaksi = document.getElementById('notransaksi').value;

	//Untuk upload SPK Final, tombol pada list data
	if (jenisupload=='1') {
		notransaksi=notrans;
	}

	// if (notransaksi == "") {
	// 	alert("warning : Silahkan isikan detail pengajuan terlebih dahulu !");
	// 	return false;
	// }
	param='method=showupload&notransaksi='+notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan='lgl_slave_penawaranharga.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					alertify.popup().set({'title':'Upload File Perbandingan Harg Project','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('25%','25%').show();
                    // document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi,jenisupload);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}
function submitfile(notrans,jenisupload) {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').value;
	var formdata = new FormData();

	if (jenisupload=='1') {
		notransaksi=notrans;
	}

	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("jenisupload", jenisupload);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}

	if (notransaksi == "") {
		alert("warning : Silahkan isikan detail pengajuan terlebih dahulu !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "lgl_slave_penawaranharga.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function viewlistfile(notransaksi) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog4(title, content, width, height, ev);
	param = 'method=viewlistfile&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_penawaranharga.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(notransaksi,jenisupload) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan = 'lgl_slave_penawaranharga.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
						
						
					}
					// loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(notransaksi, namafile,jenisupload) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'lgl_slave_penawaranharga.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (ext[1].trim() == 'jpg' || ext[1].trim() == 'jpeg' || ext[1].trim() == 'png') {
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_penawaranharga.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alertify.popup().set({'title':'View File','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('85%','55%').show();
					// alertify.popup2("View File",con.responseText).set({resizable:true,maximizable:true,startMaximized: true}).resizeTo('80%','70%'); 
                    alertify.popup2().set({resizable: false,maximizable: true,startMaximized: true,message: con.responseText,})
                    .resizeTo("80%", "70%")
                    .show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}