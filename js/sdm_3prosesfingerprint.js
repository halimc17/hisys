function postingba(){
	noba  = document.getElementById('noba').value;
	param = "method=postingba&noba="+noba;
	tujuan= 'sdm_slave_bafinger.php';
	
	if (confirm('Anda yakin posting No. BA '+noba+'?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					baris  = document.getElementById('tempbaris').value;
					colom  = document.getElementById('tempcolom').value;
					absen  = document.getElementById('absen').value;
					jam    = document.getElementById('jam').value;
					mnt    = document.getElementById('mnt').value;
					jam2   = document.getElementById('jam2').value;
					mnt2   = document.getElementById('mnt2').value;
					jam3   = document.getElementById('jam3').value;
					mnt3   = document.getElementById('mnt3').value;
					jam4   = document.getElementById('jam4').value;
					mnt4   = document.getElementById('mnt4').value;
					
					document.getElementById('masuk_'+baris+'_'+colom).innerHTML=jam+":"+mnt;
					document.getElementById('masuk_'+baris+'_'+colom).style.color="green";
				
					document.getElementById('istout_'+baris+'_'+colom).innerHTML=jam2+":"+mnt2;
					document.getElementById('istout_'+baris+'_'+colom).style.color="green";
				
					document.getElementById('istin_'+baris+'_'+colom).innerHTML=jam3+":"+mnt3;
					document.getElementById('istin_'+baris+'_'+colom).style.color="green";
					
					document.getElementById('pulang_'+baris+'_'+colom).innerHTML=jam4+":"+mnt4;
					document.getElementById('pulang_'+baris+'_'+colom).style.color="green";
					
					document.getElementById('absen_'+baris+'_'+colom).innerHTML=absen;
					document.getElementById('absen_'+baris+'_'+colom).style.color="green";
					
					alertify.popup2().destroy();
					alertify.popup().destroy();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpanba(){
	method    = document.getElementById('method').value;
	noba      = document.getElementById('noba').value;
	tanggal   = document.getElementById('tanggal').value;
	unit      = document.getElementById('unit').value;
	karyawan  = document.getElementById('karyawan').value;
	keterangan= document.getElementById('keterangan').value;
	absen     = document.getElementById('absen').value;
	jam       = document.getElementById('jam').value;
	mnt       = document.getElementById('mnt').value;
	jam2      = document.getElementById('jam2').value;
	mnt2      = document.getElementById('mnt2').value;
	jam3      = document.getElementById('jam3').value;
	mnt3      = document.getElementById('mnt3').value;
	jam4      = document.getElementById('jam4').value;
	mnt4      = document.getElementById('mnt4').value;
	
	param = 'method='+method+'&noba='+noba+'&tanggal='+tanggal+'&unit='+unit+'&karyawan='+karyawan+'&absen='+absen+'&jam='+jam+'&mnt='+mnt+'&jam2='+jam2+'&mnt2='+mnt2+'&jam3='+jam3+'&mnt3='+mnt3+'&jam4='+jam4+'&mnt4='+mnt4+'&keterangan='+keterangan;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Data berhasil di simpan.");
					document.getElementById('noba').value=con.responseText;
					document.getElementById('tombolsimpan').style.display="none";
					document.getElementById('tombolposting').style.display="block";
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showbaabsensi(kodeorg, karyawanid, tanggal, baris, colom){
	param  = "method=showbaabsensi";
	param += '&kodeorg='+kodeorg;
	param += '&karyawanid='+karyawanid;
	param += '&tanggal='+tanggal;
	param += '&baris='+baris;
	param += '&colom='+colom;
	
	tujuan="sdm_slave_3prosesfingerprint.php";	
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					alertify.popup2("BA Absensi",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','500px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}
function html(kodeorg, subbagian, tanggal, sumber){
	param  = "method=html";
	param += '&kodeorg='+kodeorg;
	param += '&subbagian='+subbagian;
	param += '&tanggal='+tanggal;
	param += '&sumber='+sumber;
	
	tujuan="sdm_slave_3prosesfingerprint.php";	
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
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

function unposting(kodeorg, subbagian, tanggal, sumber){
	param  = "method=unposting";
	param += '&kodeorg='+kodeorg;
	param += '&subbagian='+subbagian;
	param += '&tanggal='+tanggal;
	param += '&sumber='+sumber;
	
	tujuan="sdm_slave_3prosesfingerprint.php";
	
	if(confirm('Data fingerprint dan absensi akan di hapus, anda yakin ???')){		
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function postingx(kodeorg, subbagian, tanggal, sumber){
	param  = 'method=posting';
	param += '&kodeorg='+kodeorg;
	param += '&subbagian='+subbagian;
	param += '&tanggal='+tanggal;
	param += '&sumber='+sumber;
	
	tujuan="sdm_slave_3prosesfingerprint.php";
	
	if(confirm('Anda yakin ???')){		
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function del(kodeorg, subbagian, tanggal, sumber){
	param  = "method=delete";
	param += '&kodeorg='+kodeorg;
	param += '&subbagian='+subbagian;
	param += '&tanggal='+tanggal;
	param += '&sumber='+sumber;
	
	tujuan="sdm_slave_3prosesfingerprint.php";
	
	if(confirm('Anda yakin ???')){		
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					getPage();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}
function cancel(){
	setValue2('unit',null);
	setValue2('tglawal',null);
	setValue2('tglakhir',null);
	setValue2('subunit',null);
	
	document.getElementById('printContainer').innerHTML="";
}

function edit(kodeorg, subbagian, tanggal, sumber, divisi){
	document.getElementById('header').style.display = 'block';
    document.getElementById('listtransaksi').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
	
	setValue2('unit',kodeorg);
	setValue2('tglawal',tanggal);
	setValue2('tglakhir',tanggal);
	// setValue2('subunit',subbagian);
	document.getElementById('subunit').innerHTML="<option value='"+ subbagian +"'>"+ divisi +"</option>";
	//getsubunit(subbagian);
	
	preview('html','event');
}	
function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listtransaksi').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    //cancel();  
}
function displayList(){
    document.getElementById('header').style.display = 'none';
    document.getElementById('listtransaksi').style.display = 'none';
    document.getElementById('listData').style.display = 'block';
    loaddata();  
}

function getPage(){
	pg   =document.getElementById('pages').value;
	paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
	kodeorg= document.getElementById('kodeorgsch').value;
	divisi = document.getElementById('divsch').value;
	tanggal= document.getElementById('tanggalsrc').value;
	posting= document.getElementById('postingsrc').value;
	karyawanid= document.getElementById('namasch').value;
	
	
	param="method=loaddata&page="+page;
	param += '&kodeorg='+kodeorg;
	param += '&divisi='+divisi;
	param += '&tanggal='+tanggal;
	param += '&posting='+posting;
	param += '&karyawanid='+karyawanid;
	tujuan="sdm_slave_3prosesfingerprint.php";
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('listData').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function updateabsen(no,countshift){
	karyawanid= document.getElementById('karypopup').value;
	baris     = document.getElementById('barispopup').value;
	colom     = document.getElementById('colompopup').value;
	penjelasan= document.getElementById('penjelasan').value;
	
	let arr = new Array();
	for(i=1;i<=no;i++){
		tanggal = document.getElementById('tanggalx_'+i).innerHTML;
		jamkerja= document.getElementById('jamkerja_'+i).value;
		if(jamkerja!=''){			
			arr.push(jamkerja);
		}
	}
	
	let err = "";
	if(arr.length==0){
		alertify.alert("Silahkan pilih Absensi terlebih dahulu.");return;
		err ++ ;
	}
	
	if(arr.length!==countshift){
		alertify.alert("Jumlah absensi tidak sesuai.");return;
		err ++ ;
	}
	
	if(checkIfDuplicateExists(arr)==false){
		alertify.alert("Absensi ada yang double.");return;
		err ++ ;
	}
	if(penjelasan==''){
		alertify.alert("Penjelasan harus diisi.");return;
		err ++ ;
	}
	
	if(penjelasan.length<7){
		alertify.alert("Penjelasan harus diisi dengan jelas.");return;
		err ++ ;
	}
	
	if(err==""){		
		for(i=1;i<=no;i++){
			kodeabsensi = document.getElementById('kodeabsensixxx').value;
			jamkerja= document.getElementById('jamkerja_'+i).value;
			tanggal = document.getElementById('tanggalx_'+i).innerHTML;
			if(jamkerja=='masuk' && jamkerja!=''){
				document.getElementById('masukx_'+baris+'_'+colom).innerHTML=tanggal;
				document.getElementById('masuk_'+baris+'_'+colom).innerHTML=tanggal.substr(11,5);
				document.getElementById('masuk_'+baris+'_'+colom).style.color="orange";
			}
			if(jamkerja=='outist' && jamkerja!=''){
				document.getElementById('istoutx_'+baris+'_'+colom).innerHTML=tanggal;
				document.getElementById('istout_'+baris+'_'+colom).innerHTML=tanggal.substr(11,5);
				document.getElementById('istout_'+baris+'_'+colom).style.color="orange";
			}
			if(jamkerja=='inist' && jamkerja!=''){
				document.getElementById('istinx_'+baris+'_'+colom).innerHTML=tanggal;
				document.getElementById('istin_'+baris+'_'+colom).innerHTML=tanggal.substr(11,5);
				document.getElementById('istin_'+baris+'_'+colom).style.color="orange";
			}
			if(jamkerja=='pulang' && jamkerja!=''){
				document.getElementById('pulangx_'+baris+'_'+colom).innerHTML=tanggal;
				document.getElementById('pulang_'+baris+'_'+colom).innerHTML=tanggal.substr(11,5);
				document.getElementById('pulang_'+baris+'_'+colom).style.color="orange";
			}
			document.getElementById('penjelasan_'+baris+'_'+colom).innerHTML=penjelasan;
			document.getElementById('absen_'+baris+'_'+colom).innerHTML=kodeabsensi;
			document.getElementById('absen_'+baris+'_'+colom).style.color="orange";
		}
	}
	
	alertify.popup().destroy();
}

function checkIfDuplicateExists(arr) {
    return new Set(arr).size === arr.length
}

function checkDuplicates(array) {
    var valuesSoFar = Object.create(null);
    for (var i = 0; i < array.length; ++i) {
        var value = array[i];
        if (value in valuesSoFar) {
            return false;
        }
        valuesSoFar[value] = false;
    }
    return true;
}

function getsubunit(subbagian){
	unit=document.getElementById('unit').value;
	
	param="method=getsubunit&unit="+unit;
	param += '&subbagian='+subbagian;
	tujuan="sdm_slave_3prosesfingerprint.php";
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('subunit').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function preview(tipeprint,ev){
	unit    =document.getElementById('unit').value;
	subunit =document.getElementById('subunit').value;
	tglawal =document.getElementById('tglawal').value;
	tglakhir=document.getElementById('tglakhir').value;
	//tipekary=document.getElementById('tipekary').value;
	
	var tipekary = $('#tipekary').val();
	
	
	param="method=preview&tipeprint="+tipeprint+'&unit='+unit+'&subunit='+subunit+'&tglakhir='+tglakhir+'&tglawal='+tglawal+'&tipekary='+tipekary;
	tujuan="sdm_slave_3prosesfingerprint.php";
	if(tipeprint!='html'){ 
		judul=tipeprint;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}else{		
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					document.getElementById('printContainer').innerHTML=con.responseText;
					leftFixedTable();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='';
   height='';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

function viewdetail(tanggal,nik,karyawanid,idshift,masuk,istout,istin,pulang,baris, colom,shifttiga){
	var param = "tanggal="+tanggal+'&nik='+nik+'&karyawanid='+karyawanid+'&idshift='+idshift+'&baris='+baris+'&colom='+colom;
	param += '&method=viewdetail';
	param += '&masuk='+masuk;
	param += '&istout='+istout;
	param += '&istin='+istin;
	param += '&pulang='+pulang;
	param += '&shifttiga='+shifttiga;
	tujuan = 'sdm_slave_3prosesfingerprint.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					//document.getElementById('containerdetail').innerHTML = con.responseText;
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':false}).resizeTo('700px','500px');
					getSelect2();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function viewdetail2(tanggal,nik,karyawanid,idshift,baris, colom){
	masuk = document.getElementById('masukx_'+baris+'_'+colom).innerHTML;
	istout= document.getElementById('istoutx_'+baris+'_'+colom).innerHTML;
	istin = document.getElementById('istinx_'+baris+'_'+colom).innerHTML;
	pulang= document.getElementById('pulangx_'+baris+'_'+colom).innerHTML;
	absen = document.getElementById('absen_'+baris+'_'+colom).innerHTML;
	
	
	var param = "tanggal="+tanggal+'&nik='+nik+'&karyawanid='+karyawanid+'&baris='+baris+'&colom='+colom+'&idshift='+idshift;
	param += '&method=viewdetail2';
	param += '&masuk='+masuk;
	param += '&istout='+istout;
	param += '&istin='+istin;
	param += '&pulang='+pulang;
	param += '&absen='+absen;
	
	tujuan = 'sdm_slave_3prosesfingerprint.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
				}else{
					//document.getElementById('containerdetail').innerHTML = con.responseText;
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':false}).resizeTo('700px','500px');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefp(tanggal,karyawanid, baris, colom){
	document.getElementById('masuk_'+baris+'_'+colom).innerHTML="";
	document.getElementById('masukx_'+baris+'_'+colom).innerHTML="";
	document.getElementById('istout_'+baris+'_'+colom).innerHTML="";
	document.getElementById('istoutx_'+baris+'_'+colom).innerHTML="";
	document.getElementById('istin_'+baris+'_'+colom).innerHTML="";
	document.getElementById('istinx_'+baris+'_'+colom).innerHTML="";
	document.getElementById('pulang_'+baris+'_'+colom).innerHTML="";
	document.getElementById('pulangx_'+baris+'_'+colom).innerHTML="";
	document.getElementById('absen_'+baris+'_'+colom).innerHTML="";
	document.getElementById('absen_'+baris+'_'+colom).style.backgroundColor="red";
	
	alertify.popup().destroy();
}

function simpanshift(maxrow,col){
	if (maxrow == '' || maxrow == 0) {
		alertify.alert('Info','Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	window.scrollTo({top: 0, behavior: 'smooth'});
	alertify.confirm("Warning","Simpan seluruhnya ?",
		function(){
			simpan(1, maxrow, col);
		},
		function(){
			return;
		}
	);
}

function simpan(row,maxrow,col) {
	kodeorg   = document.getElementById('unit').value;
	subbagian = document.getElementById('subunit').value;
	karyawanid= document.getElementById('karyawanid_'+row).innerHTML;
	
	param = 'method=insert';
	for(i=1;i<=col;i++){
		tgl = document.getElementById('tanggal_'+i).innerHTML;
		param += '&tanggal['+i+']=' + tgl;
		
		masuk = document.getElementById('masukx_'+row+'_'+i).innerHTML;
		param += '&masuk['+i+']=' + masuk;
		
		istout = document.getElementById('istoutx_'+row+'_'+i).innerHTML;
		param += '&istout['+i+']=' + istout;
		
		istin = document.getElementById('istinx_'+row+'_'+i).innerHTML;
		param += '&istin['+i+']=' + istin;
		
		pulang = document.getElementById('pulangx_'+row+'_'+i).innerHTML;
		param += '&pulang['+i+']=' + pulang;
		
		absen = document.getElementById('absen_'+row+'_'+i).innerHTML;
		param += '&absen['+i+']=' + absen;
		
		penjelasan = document.getElementById('penjelasan_'+row+'_'+i).innerHTML;
		param += '&penjelasan['+i+']=' + penjelasan;
		
		idshift = document.getElementById('idshift_'+row+'_'+i).innerHTML;
		param += '&idshift['+i+']=' + idshift;
	}
	
	validate([
        ["unit","Kode organisasi tidak boleh kosong"]
	]);
	
	
	param += '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian + '&karyawanid=' + karyawanid;
	
	if (absen != 'H') {
		document.getElementById('row'+row).style.backgroundColor='yellow';
	}else{
		document.getElementById('row'+row).style.backgroundColor='green';
	}
	
	tujuan = 'sdm_slave_3prosesfingerprint.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('row'+row).style.display='none';
					row += 1;
					if ((row > maxrow) || (maxrow == undefined)) {
						alertify.alert("Done");
						document.getElementById('detail').innerHTML = "";
					} else {
						simpan(row, maxrow, col);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}