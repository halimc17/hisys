$('#tipe').on("select2:select", function(e) { 
	tipe=document.getElementById('tipe').value;
	tipe=document.getElementById('tipe').value;
	if(tipe=='I'){
		document.getElementById('supplier').disabled=false;
		document.getElementById('customer').disabled=true;
		document.getElementById('sambungso').disabled=false;
		setValue2('supplier','');
		setValue2('customer','');
		setValue2('sambungso','');
		getkontrakbeli('','','');
	}else if(tipe=='O'){
		document.getElementById('supplier').disabled=true;
		document.getElementById('customer').disabled=false;
		document.getElementById('sambungso').disabled=false;
		setValue2('supplier','');
		setValue2('customer','');
		setValue2('sambungso','');
		getkontrakjual('','','','','');
	}else if(tipe=='II'){
		document.getElementById('supplier').disabled=true;
		document.getElementById('customer').disabled=false;
		document.getElementById('sambungso').disabled=false;
		setValue2('supplier','');
		setValue2('customer','');
		setValue2('sambungso','');
		getkontrakjual('','','','','');
	}else if(tipe=='OO'){
		document.getElementById('supplier').disabled=true;
		document.getElementById('customer').disabled=false;
		document.getElementById('sambungso').disabled=false;
		setValue2('supplier','');
		setValue2('customer','');
		setValue2('sambungso','');
		getkontrakjual('','','','','');
	}
});

$('#supplier').on("select2:select", function(e) { 
	setValue2('customer','');
	getkontrakbeli('','','');
});

$('#customer').on("select2:select", function(e) { 
	setValue2('supplier','');
	getkontrakjual('','','','','');
});

$('#produk').on("select2:select", function(e) { 
	tipe = getValue('tipe');
	if(tipe=='O'){
		getkontrakjual('','','','','');		
	}
});

$('#so').on("select2:select", function(e) { 
	tipe = getValue('tipe');
	if(tipe=='O'){
		getsambungso('');		
	}
});

$('#transportir').on("select2:select", function(e) { 
	// setValue2('supplier','');
	// getdriver('','');
});

function getdriver(transportir,nokendaraan){
	if(transportir==''){
		transportir = getValue('transportir');		
	}
	
	param='method=getdriver&transportir='+transportir+'&nokendaraan='+nokendaraan;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('nokendaraan').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getkontrakbeli(tipe,supplier,so){
	if(tipe==''){
		tipe = getValue('tipe');		
	}
	if(supplier==''){
		supplier = getValue('supplier');		
	}
	param='method=getkontrakbeli&tipe='+tipe+'&supplier='+supplier+'&so='+so;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('so').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getkontrakjual(tipe,customer,produk,so,sambungso){
	if(tipe==''){
		tipe = getValue('tipe');		
	}
	if(customer==''){
		customer = getValue('customer');		
	}
	if(produk==''){
		produk = getValue('produk');		
	}
	param='method=getkontrakjual&tipe='+tipe+'&customer='+customer+'&produk='+produk+'&so='+so;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('so').innerHTML=con.responseText;
					getsambungso(sambungso);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function getsambungso(sambungso){
	tipe = getValue('tipe');
	produk = getValue('produk');
	customer = getValue('customer');
	// if(tipe=='O'){
		so = getValue('so');
		param='method=getsambungso&so='+so+'&produk='+produk+'&customer='+customer+'&sambungso='+sambungso;
		tujuan='wb_slave.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert("Info",con.responseText);
					} else {
						document.getElementById('sambungso').innerHTML=con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}   
		}
	// }
}

// inspectelementOff();
let code = '';
let reading = false;
document.addEventListener('keypress', e=>{
    if (e.keyCode===13){
        if (code) {     
            document.getElementById('nospb').value=code;
        }
  }else{
       code+=e.key;
  }
   
  if(!reading){
         reading=true;
         setTimeout(()=>{
          code='';
          reading=false;
      }, 2000);
  }
});

function getdivisi(divisi) {
	unit = getValue('unit');
	divisi = divisi;
    param='method=getdivisi&unit='+unit+'&divisi='+divisi;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

const btngetweight1 = document.getElementById('getweight1');
if (btngetweight1) {
  btngetweight1.addEventListener('click', function () {
	tipe=getValue('tipe');
	ambil_tanggal('datein','wei1st',tipe);
  });
}

const btngetweight2 = document.getElementById('getweight2');
if (btngetweight2) {
  btngetweight2.addEventListener('click', function () {
	tipe=getValue('tipe');
    ambil_tanggal('dateout','wei2nd',tipe);
  });
}

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}

const tabquality = document.getElementById('tabquality');
if (tabquality) {
	tabquality.addEventListener('click', function () {
	  formquality();
	});
}

const tabgrading = document.getElementById('tabgrading');
if (tabgrading) {
	tabgrading.addEventListener('click', function () {
	  formgrading();
	});
}

const tabsortasi = document.getElementById('tabsortasi');
if (tabsortasi) {
	tabsortasi.addEventListener('click', function () {
	  formsortasi();
	});
}

function ambil_tanggal(idel1,idel2,tipe)
{
	var myDate = new Date();
    var tanggal,bulan,tahun,jam,menitdetik;
    var output;
    
	tanggal= myDate.getDate().toString();
    bulan  = (myDate.getMonth()+1).toString();
    tahun  = myDate.getFullYear().toString();
    jam     = myDate.getHours().toString();
    menit  = myDate.getMinutes().toString();
    detik  = myDate.getSeconds().toString();
	
	if(tanggal.length<2)
		tanggal="0"+tanggal;
	if(bulan.length<2)
		bulan="0"+bulan;
	if(jam.length<2)
		jam="0"+jam;
	if(menit.length<2)
		menit="0"+menit;
	if(detik.length<2)
		detik="0"+detik;
	
	output=tanggal+"-"+bulan+"-"+tahun+" "+jam+":"+menit+":"+detik;
    document.getElementById(idel1).value=output;
    weigh=document.getElementById('weight').value;
    document.getElementById(idel2).value = weigh;
	

	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	if (tipe == 'I' || tipe == 'II') {
		bruto = wei1st-wei2nd;
	}else{
		bruto = wei2nd-wei1st;
	}
	netto = bruto-kgpotongan;
	if (wei1st !== '' && wei2nd !== '') {
		document.getElementById('bruto').value=bruto;
		document.getElementById('netto').value=netto;
	}

}

function batal(){
	setValue2('tipe','');
	setValue2('qrcode','');
	setValue2('produk','');
	setValue2('nokendaraan','');
	setValue2('supplier','');
	setValue2('supir','');
	setValue2('nosim','');
	setValue2('customer','');
	setValue2('so','');
	setValue2('transportir','');
	setValue2('sambungso','');
	setValue2('keterangan','');
	setValue2('segel','');

	document.getElementById('supplier').disabled=true;
	document.getElementById('customer').disabled=true;
	document.getElementById('sambungso').disabled=true;
  
	document.getElementById('bruto').value='';
	document.getElementById('kgpotongan').value='';
	document.getElementById('netto').value='';
	document.getElementById('datein').value='';
	document.getElementById('dateout').value='';
	document.getElementById('wei1st').value='';
	document.getElementById('wei2nd').value='';
	document.getElementById('getweight1').disabled=false;
	document.getElementById('getweight2').disabled=true;
	document.getElementById('method').value='timbang1';
}


function simpan(){
	method = document.getElementById('method').value;
	ticketno = document.getElementById('ticketno').value;
	tipe = getValue('tipe');
	produk = getValue('produk');
	supplier = getValue('supplier');
	customer = getValue('customer');
	transportir = getValue('transportir');
	qrcode = getValue('qrcode');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	nosim = getValue('nosim');
	so = getValue('so');
	sambungso = getValue('sambungso');
	keterangan = getValue('keterangan');
	segel = getValue('segel');
	
	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	datein = document.getElementById('datein').value;
	dateout = document.getElementById('dateout').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	if(method=='timbang1'){
		validate([
			["nokendaraan","No. Kendaraan tidak boleh kosong"],
			["supir","Nama Driver tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"]
		]);
	}

	paramgp='';
	if(method=='timbang2'){
		validate([
			["wei1st","Berat timbang 1 tidak boleh kosong"],
			["wei2nd","Berat timbang 2 tidak boleh kosong"]
		]);
		
		var gp = document.getElementsByName('jjg');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
		var gp = document.getElementsByName('persen');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
		var gp = document.getElementsByName('kg');
		for (var i = 0; i < gp.length; i++){
			if(gp[i].value > 0 && gp[i].value != ''){
				paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			}
		}
	}

	param='ticketno='+ticketno+'&tipe='+tipe+'&produk='+produk+'&supplier='+supplier+'&customer='+customer+'&transportir='+transportir+'&qrcode='+qrcode+'&nokendaraan='+nokendaraan+'&supir='+supir+'&nosim='+nosim+'&so='+so+'&sambungso='+sambungso+'&keterangan='+keterangan+'&segel='+segel;
	param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='wb_slave.php';
	tujuan2=tujuan+"?"+param2+'&method=printticket';
	post_response_text(tujuan, param, respog);

    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText.replace("error", ""));
                } else {
                    alertify.set('notifier','position', 'top-right');
                    alertify.success('Success').delay(3);
                    
                    if (method=='timbang1') {
						showtobottom();
					}
                    if (method=='timbang2') {
                        alertify.popuppdf("<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan2+"'></iframe>").set({'frameless':true,'resizable':true,'maximizable':true, 'overflow':false}).resizeTo('80%','85%');
                    }

                    // batal();
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					// document.getElementById('tdgrading').style.display='block';
					showontop();
					var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
					setValue2('ticketno',arrlist[0]['notransaksi']);
					setValue2('tipe',arrlist[0]['inout']);
					setValue2('produk',arrlist[0]['kodebarang']);
					setValue2('supplier',arrlist[0]['supplier']);
					setValue2('customer',arrlist[0]['customer']);
					setValue2('transportir',arrlist[0]['transportir']);
					setValue2('nokendaraan',arrlist[0]['nokendaraan']);
					setValue2('supir',arrlist[0]['supir']);
					setValue2('nosim',arrlist[0]['nosim']);
					setValue2('qrcode',arrlist[0]['qr']);
					so=arrlist[0]['nopo'];
					if(so==''){
						so=arrlist[0]['kontrakbeli'];
					}
					if(so==''){
						so=arrlist[0]['kontrakbeli2'];
					}
					if(so==''){
						so=arrlist[0]['kontrakjual'];
					}
					if(so==''){
						so=arrlist[0]['spb'];
					}
					
					if(arrlist[0]['inout']=='I' || arrlist[0]['inout']=='II'){
						setValue2('keterangan',arrlist[0]['keterangan']);
						if(arrlist[0]['inout']=='I'){
							getkontrakbeli(arrlist[0]['inout'],arrlist[0]['supplier'],so);							
						}else{
							getkontrakjual(arrlist[0]['inout'],arrlist[0]['supplier'],arrlist[0]['kodebarang'],so,arrlist[0]['kontrakjual2']);					
						}
						if(arrlist[0]['kodebarang']=='90100001' || arrlist[0]['kodebarang']=='90100002'){
							document.getElementById('tabquality').style.display='';
							document.getElementById('tabgrading').style.display='none';
							document.getElementById('tabsortasi').style.display='none';
						}else if(arrlist[0]['kodebarang']=='90100000'){
							document.getElementById('tabquality').style.display='none';
							document.getElementById('tabgrading').style.display='';
							document.getElementById('tabsortasi').style.display='';
						}else{
							document.getElementById('tabquality').style.display='';
							document.getElementById('tabgrading').style.display='';
							document.getElementById('tabsortasi').style.display='';
						}
					}else{
						setValue2('keterangan',arrlist[0]['notekirim']);						
						getkontrakjual(arrlist[0]['inout'],arrlist[0]['customer'],arrlist[0]['kodebarang'],so,arrlist[0]['kontrakjual2']);
						document.getElementById('tabquality').style.display='';
						document.getElementById('tabgrading').style.display='none';
						document.getElementById('tabsortasi').style.display='none';
					}
					setValue2('segel',arrlist[0]['segel']);
					
					setValue2('datein',arrlist[0]['waktumasuk']);
					setValue2('wei1st',arrlist[0]['beratmasuk']);
					
					
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto','');
					setValue2('netto','');
					setValue2('kgpotongan','');
					
					// document.getElementById('tipe').disabled=true;
					
					document.getElementById('sambungso').disabled=false;
					document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
					setValue2('method','timbang2');	

					// //GRADING
					// var gp = document.getElementsByName('jjg');
					// for (var i = 0; i < gp.length; i++){
						// gp[i].value='';
					// }
					// var gp = document.getElementsByName('persen');
					// for (var i = 0; i < gp.length; i++){
						// gp[i].value='';
					// }
					// var gp = document.getElementsByName('kg');
					// for (var i = 0; i < gp.length; i++){
						// gp[i].value='';
					// }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function hitungkg(id){
	bruto=document.getElementById('bruto').value;
	if(bruto==''){bruto=0;}
	
	if(bruto!=0){
		var pr = document.getElementsByName('persen');
		var kg = document.getElementsByName('kg');
		for (var i = 0; i < pr.length; i++){
			if(pr[i].value > 0 && pr[i].value != ''){
				hsl = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
				if(typeof kg[i]!='undefined'){
					if(pr[i].id==id){
						kg[i].value = hsl;					
					}
				}
			}
		}
	}
	
	hitungpr(id);
}

function hitungpr(id){
	bruto=document.getElementById('bruto').value;
	potongan=0;
	if(bruto==''){bruto=0;}
	
	if(bruto!=0){
		var pr = document.getElementsByName('persen');
		var kg = document.getElementsByName('kg');
		for (var i = 0; i < kg.length; i++){
			if(kg[i].value > 0 && kg[i].value != ''){
				hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;
				if(typeof pr[i]!='undefined'){
					if(kg[i].id==id){
						pr[i].value = Math.round(parseFloat(hsl) * 100) / 100;					
					}
				}
				potongan=parseFloat(potongan) + parseFloat(kg[i].value);
			}
		}
	}
	// document.getElementById('ttlkg').value=Math.round(potongan);
	// netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    // document.getElementById('netto').value=netto;
}

function generatenotiket() {
    param='method=generatenotiket';
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('ticketno').value=con.responseText;    
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getkontrak() {
	kodeproduk = document.getElementById('kodeproduk').value;
    param='method=getkontrak'+'&kodeproduk='+kodeproduk;
    tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('nokontrak').innerHTML=con.responseText;
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function loadData() {
    param='method=loadData';
	tujuan='wb_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
                    generatenotiket();
                    // formsortasi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function formsortasi() {
    kodeproduk = document.getElementById('kodeproduk').value;

    if (kodeproduk=='40000003') {
        document.getElementById('tdsortasi').style.display="block";
        document.getElementById('tdkualitas').style.display="none";
    }else if (kodeproduk=='40000001' || kodeproduk=='40000002'){
        document.getElementById('tdkualitas').style.display="block";
        document.getElementById('tdsortasi').style.display="none";
    }else{
        document.getElementById('tdsortasi').style.display="none";
        document.getElementById('tdkualitas').style.display="none";
    }
}




document.addEventListener('DOMContentLoaded', function () {
	loadData();
});


function formquality(){
	param = 'method=formquality';
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Form Kualitas",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','80%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formgrading(){
	param = 'method=formgrading';
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Form Grading",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','85%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formsortasi(){
	param = 'method=formsortasi';
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Form Sortasi",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','80%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanquality(){
	storage=getValue('storage');
	ffa=getValue('ffa');
	moist=getValue('moist');
	dirt=getValue('dirt');
	dobi=getValue('dobi');
	
	param = 'method=simpanquality&storage='+storage+'&ffa='+ffa+'&moist='+moist+'&dirt='+dirt+'&dobi='+dobi;
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup().close();
					document.getElementById('showquality').innerHTML=con.responseText; 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpangrading(){
	paramgp='';
	var gp = document.getElementsByName('jjg');
	for (var i = 0; i < gp.length; i++){
		if(gp[i].value > 0 && gp[i].value != ''){
			paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
		}
	}
	var gp = document.getElementsByName('persen');
	for (var i = 0; i < gp.length; i++){
		if(gp[i].value > 0 && gp[i].value != ''){
			paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
		}
	}
	var gp = document.getElementsByName('kg');
	for (var i = 0; i < gp.length; i++){
		if(gp[i].value > 0 && gp[i].value != ''){
			paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
		}
	}
	
	param = 'method=simpangrading';
	param+=paramgp;
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup().close();
					document.getElementById('showquality').innerHTML=con.responseText; 
					document.getElementById('kgpotongan').value=0; 
					ambil_tanggal('dateout','wei2nd','I');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpansortasi(){
	kgpot=0;
	paramgp='';
	var gp = document.getElementsByName('persen');
	for (var i = 0; i < gp.length; i++){
		if(gp[i].value > 0 && gp[i].value != ''){
			paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
		}
	}
	var gp = document.getElementsByName('kg');
	for (var i = 0; i < gp.length; i++){
		if(gp[i].value > 0 && gp[i].value != ''){
			paramgp+='&kriteria[]='+gp[i].id+'&nilai[]='+gp[i].value;
			kgpot+=parseFloat(gp[i].value);
		}
	}
	
	param = 'method=simpansortasi';
	param+=paramgp;
	post_response_text('wb_slave.php', param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup().close();
					document.getElementById('showquality').innerHTML=con.responseText; 
					document.getElementById('kgpotongan').value=Math.round(kgpot);
					ambil_tanggal('dateout','wei2nd','I');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}