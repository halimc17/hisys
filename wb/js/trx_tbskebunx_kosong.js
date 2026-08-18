$('#unit').on("select2:select", function(e) { 
	getdivisi();
});
$('#divisi').on("select2:select", function(e) { 
	getnoSPB();
});
$('#customer').on("select2:select", function(e) { 
	getkontrak();
});

$('#tipe_angkutan').on("select2:select", function(e) { 
	gettransportir();
});
$('#transportir').on("select2:select", function(e) { 
	getkendaraan();
});

// inspectelementOff();
let code = '';
let reading = false;
document.addEventListener('keypress', e=>{
    if (e.keyCode===13){
        if (code) {     
            //document.getElementById('qrcode').value=code;
            // const splitcode=code.split("#");
            // const qr=splitcode[0].split("*");
            // var divisi=splitcode[6]+splitcode[7];
            // var divisi=divisi.substring(0, 6);
            // document.getElementById('qrcode').value=qr[1];
            // setValue2('unit',splitcode[6]);
            // getdivisi(divisi);
            // setValue2('divisi',divisi);
            // setValue2('nokendaraan',splitcode[2]);
            // setValue2('supir',splitcode[1]);
            // setValue2('jjg',splitcode[3]);
            // setValue2('brondol',splitcode[5]);
            // ambil_tanggal('datein','wei1st','penerimaan');

			// new
			// Memisahkan data berdasarkan karakter '#'
			// if(code == '8998866202930'){
			// 	alert('Warning QR tidak valid : SEDAAP MIE AYAM JERIT CUP 75G X 12');
			// 	return false;
			// }
			let parts = code.split('#');

			// Memisahkan header dan detail berdasarkan karakter '|'
			let header = parts[0].split('|');
			let detail = parts[1].split('|');

			if(parts.length != 5){
				alert('QR tidak valid...');
				return false;
			}
			if(header.length != 11){
				alert('QR tidak valid...');
				return false;
			}
			if(detail.length != 10){
				alert('QR tidak valid...');
				return false;
			}

			// console.log("Header:", header);
			// console.log("Detail:", detail);
			// set value
			if(document.getElementById('qrcode').disabled == true){
				if(document.getElementById('qrcode').value != header[0] ){
					// alert('Beda No QR : '+document.getElementById('qrcode').value+'_'+header[0]);
					// return false;
					document.getElementById('qrcode').value=header[0];
					document.getElementById('qrcode').disabled = true;
				}else{
					document.getElementById('qrcode').value=header[0];
					document.getElementById('qrcode').disabled = true;
				}
			}else{
				document.getElementById('qrcode').value=header[0];
				document.getElementById('qrcode').disabled = true;
			}
			
			document.getElementById('tgl_panen').value=header[1];
			document.getElementById('tph_d').value=detail[3];
			document.getElementById('blok_d').value=detail[2];
			document.getElementById('pemanen_d').value=detail[1];
			document.getElementById('janjang_d').value=detail[4];
			document.getElementById('brondolan_d').value=detail[5];
			document.getElementById('sesi_d').value=detail[8];

			tambahPanen_d();
           
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


function getdivisi(divisi='',customer='',so='',newFunc) {
	unit = getValue('unit');
	divisi = divisi;
    param='method=getdivisi&unit='+unit+'&divisi='+divisi;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					const loaddatadivisi=con.responseText.split("####");
                    document.getElementById('divisi').innerHTML=loaddatadivisi[0];
					if(loaddatadivisi[1] == '1'){
						setValue2('customer','');
						setValue2('so','');
						document.getElementById('customer').disabled=true;
						document.getElementById('so').disabled=true;
						getcustomer(customer,so,newFunc);
					}else{
						document.getElementById('customer').disabled=false;
						document.getElementById('so').disabled=false;
						getcustomer(customer,so,newFunc);
					}

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}
function getcustomer(customer='',so='',newFunc){
	unit = getValue('unit');
	ticketno = getValue('ticketno');
	nospb = getValue('nospb');
	supir = getValue('supir');
	customer = getValue('customer');
	so_k = getValue('so');
	param='method=getcustomer&so='+customer;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('customer').innerHTML=con.responseText;
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(newFunc());
					}

					getkontrak(so,newFunc);

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}

function getkontrak(so='',newFunc){
	unit = getValue('unit');
	ticketno = getValue('ticketno');
	nospb = getValue('nospb');
	customer = getValue('customer');
	param='method=getkontrak&unit='+unit+'&so='+so+'&customer='+customer;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('so').innerHTML=con.responseText;
					if(typeof newFunc !== 'undefined' && typeof newFunc == 'function'){
						eval(newFunc());
					}
						// cek
						if(unit != ''){
							if(nospb != ''){
									if(supir != ''){
										loadPanen(ticketno);
									}
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


function simpan(){
	method = getValue('method');
	ticketno = getValue('ticketno');
	nospb = getValue('nospb');
	unit = getValue('unit');
	divisi = getValue('divisi');
	so = getValue('so');
	transportir = getValue('transportir');
	customer = getValue('customer');
	nokendaraan = getValue('nokendaraan');
	supir = getValue('supir');
	qtysegel = getValue('qtysegel');
	segel = getValue('segel');
	jjg = getValue('jjg');
	brondol = getValue('brondol');
	keterangan = getValue('keterangan');
	tahuntanam = getValue('tahuntanam');
	tipeangkut = getValue('tipe_angkutan');
	
	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	datein = document.getElementById('datein').value;
	dateout = document.getElementById('dateout').value;
	bruto = document.getElementById('bruto').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	netto = document.getElementById('netto').value;

	adj_jjg = document.getElementById('adj_jjg').value;
	adj_brondol = document.getElementById('adj_brondol').value;

	if(method=='timbang1'){
		validate([
			["unit","Unit tidak boleh kosong"],
			["divisi","Divisi tidak boleh kosong"],
			["nokendaraan","No. Kendaraan tidak boleh kosong"],
			["supir","Nama Driver tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"],
			["nospb","QR Code / SPB tidak boleh kosong"]
		]);
	}

	paramgp='';
	if(method=='timbang2'){
		validate([
			["nospb","QR Code / SPB tidak boleh kosong"],
			["wei1st","Berat timbang 1 tidak boleh kosong"],
			["wei2nd","Berat timbang 2 tidak boleh kosong"],
			["tahuntanam","tahun tanam tidak boleh kosong"]
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

	param='ticketno='+ticketno+'&nospb='+nospb+'&unit='+unit+'&divisi='+divisi+'&so='+so+'&transportir='+transportir+'&nokendaraan='+nokendaraan+'&supir='+supir;
	param+='&jjg='+jjg+'&brondol='+brondol+'&qtysegel='+qtysegel+'&segel='+segel+'&keterangan='+keterangan+'&tahuntanam='+tahuntanam;
	param+='&wei1st='+wei1st+'&wei2nd='+wei2nd+'&datein='+datein+'&dateout='+dateout+'&kgpotongan='+kgpotongan+'&bruto='+bruto+'&netto='+netto;
	param+='&method='+method;
	param+='&customer='+customer;
	param+='&adj_jjg='+adj_jjg;
	param+='&adj_brondol='+adj_brondol;
	param+='&tipeangkut='+tipeangkut;
	param+=paramgp;
	param2='ticketno='+ticketno;
	
	tujuan='trx_tbskebunx_slave_kosong.php';
	tujuan2="printticket.php?"+param2+'&method=printticket';
	post_response_text(tujuan, param, respog);

    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    alertify.set('notifier','position', 'top-right');
                    alertify.success('Success');
					if (method=='timbang1') {
						showtobottom();
					}
                    if (method=='timbang2') {
						printticket(tujuan2);
                    }
					batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function batal(){
	showontop();
	setValue2('nospb','');
	setValue2('tahuntanam','');
	setValue2('unit','');
	setValue2('transportir','');
	setValue2('customer','');
	setValue2('nokendaraan','');
	setValue2('supir','');
	setValue2('qtysegel','');
	setValue2('segel','');
	setValue2('jjg','');
	setValue2('brondol','');
	setValue2('keterangan','');
  
	setValue2('bruto','');
	setValue2('kgpotongan','');
	setValue2('netto','');
	setValue2('datein','');
	setValue2('datein','');
	setValue2('dateout','');
	setValue2('wei1st','');
	setValue2('wei2nd','');
	
	setValue2('tipe_angkutan','');
	setValue2('adj_jjg','');
	setValue2('adj_brondol','');

	setValue2('weight','0');
	
	// document.getElementById('qrcode').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('divisi').disabled=false;
	document.getElementById('so').disabled=false;
	document.getElementById('transportir').disabled=false;
	document.getElementById('customer').disabled=false;
	document.getElementById('nokendaraan').disabled=false;
	document.getElementById('supir').disabled=false;
	// document.getElementById('qtysegel').disabled=false;
	// document.getElementById('segel').disabled=false;
	// document.getElementById('jjg').disabled=false;
	// document.getElementById('brondol').disabled=false;
	// document.getElementById('keterangan').disabled=false;
	
	document.getElementById('getweight1').disabled=false;
	document.getElementById('getweight2').disabled=true;
	
	document.getElementById('showgrading').style.display='none';
	document.getElementById('showsortasi').style.display='none';
	
	document.getElementById('dataPanen').style.display='none';
	
	document.getElementById('method').value='timbang1';
	
	getdivisi('','','',loadData);
}

function fillfield(ticketno) {
    param='method=showedit';
    param+='&ticketno='+ticketno;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					showontop();
					var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
					setValue2('ticketno',arrlist['notransaksi']);
					setValue2('nospb',arrlist['spb']);
					// setValue2('qrcode',arrlist['qr']);
					setValue2('unit',arrlist['unitcode']);
					getdivisi(arrlist['divcode'],arrlist['customer'],arrlist['kontrakjual']);
					setValue2('transportir',arrlist['transportir']);
					setValue2('customer',arrlist['customer']);
					setValue2('nokendaraan',arrlist['nokendaraan']);
					setValue2('supir',arrlist['supir']);
					setValue2('qtysegel',arrlist['qtysegel']);
					setValue2('segel',arrlist['segel']);
					setValue2('jjg',arrlist['janjang']);
					setValue2('brondol',arrlist['brondolan']);
					setValue2('keterangan',arrlist['keterangan']);
					
					setValue2('tipe_angkutan',arrlist['tipeangkut']);
					setValue2('datein',arrlist['waktumasuk']);
					setValue2('wei1st',arrlist['beratmasuk']);
					setValue2('dateout','');
					setValue2('wei2nd','');
					setValue2('bruto','');
					setValue2('netto','');
					setValue2('kgpotongan','');
					setValue2('method','timbang2');	
					
					// document.getElementById('qrcode').disabled=false;
					document.getElementById('tipe_angkutan').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('divisi').disabled=true;
					document.getElementById('so').disabled=true;
					document.getElementById('transportir').disabled=true;
					document.getElementById('customer').disabled=true;
					document.getElementById('supir').disabled=true;
					document.getElementById('qtysegel').disabled=false;
					document.getElementById('segel').disabled=false;
					document.getElementById('tahuntanam').disabled=false;
					
					document.getElementById('getweight1').disabled=true;
                    document.getElementById('getweight2').disabled=false;
					document.getElementById('jjg').disabled=true;
					document.getElementById('brondol').disabled=true;
					document.getElementById('keterangan').disabled=false;
					document.getElementById('adj_jjg').disabled=false;
					document.getElementById('adj_brondol').disabled=false;
					
					// document.getElementById('keterangan').focus();
					// document.getElementById('keterangan').value = '';
					
					// document.getElementById('qrcode').focus();
					// document.getElementById('tahuntanam').focus();
					
					// if(arrlist['tipeunit']=='PLASMA'){
					// 	document.getElementById('showgrading').style.display='none';
					// 	document.getElementById('showsortasi').style.display='';
					// }else{
					// 	document.getElementById('showgrading').style.display='';
					// 	document.getElementById('showsortasi').style.display='none';
					// }
					// cleargradsor();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}


// joki
function loadPanen(ticketno) {
    param='method=loadPanen';
	param+='&ticketno='+ticketno;
	tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
				} else {
					// document.getElementById('dataPanen').innerHTML=con.responseText;
					const loaddatapanen=con.responseText.split("####");
					document.getElementById('dataPanen').style.display='block';
					document.getElementById('dataPanen').innerHTML=loaddatapanen[0];
					// document.getElementById('qrcode').value=loaddatapanen[1];
					document.getElementById('jjg').value=loaddatapanen[2];
					document.getElementById('brondol').value=loaddatapanen[3];
					// if(document.getElementById('qrcode').value != ''){
					// 	document.getElementById('qrcode').disabled = true;
					// }else{
					// 	document.getElementById('qrcode').disabled = false;
					// }
					// cleargradsor();
					showontop();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function tambahPanen_d(){
	tph = document.getElementById('tph_d').value;
	tgl_panen = document.getElementById('tgl_panen').value;
	pemanen = document.getElementById('pemanen_d').value;
	blok = document.getElementById('blok_d').value;
	janjang = document.getElementById('janjang_d').value;
	brondolan = document.getElementById('brondolan_d').value;
	ticketno = getValue('ticketno');
	qrcode = document.getElementById('qrcode').value;
	sesi = document.getElementById('sesi_d').value;

	// Mengecek apakah ada nilai yang tidak terdefinisi
	if (tph === undefined || pemanen === undefined || blok === undefined || janjang === undefined || brondolan === undefined || ticketno === undefined || qrcode === undefined || tgl_panen === undefined || sesi === undefined) {
        // Jika ada nilai yang tidak terdefinisi, kembalikan false
		alert('Nilai Undefined');
        return false;
    }
	if(tph === 'undefined' || pemanen === 'undefined' || blok === 'undefined' || janjang === 'undefined' || brondolan === 'undefined' || ticketno === 'undefined' || qrcode === 'undefined' || tgl_panen === 'undefined' || sesi === 'undefined'){
		alert('Nilai Undefined...');
		return false;
	}


	if(tph === '' || pemanen === '' || blok === '' || janjang === '' || brondolan === '' || ticketno === '' || tgl_panen === '' || pemanen === '' || sesi === ''){
		alert('Lengkapi Form...');
		return false;
	}
	
	param='method=tambahPanen&tph='+tph+'&pemanen='+pemanen+'&blok='+blok+'&janjang='+janjang+'&brondolan='+brondolan+'&ticketno='+ticketno+'&qrcode='+qrcode+'&tgl_panen='+tgl_panen+'&sesi='+sesi;
	tujuan='trx_tbskebunx_slave_kosong.php';
	post_response_text(tujuan, param, respog);			
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
					// document.getElementById('qrcode').disabled = false;
					// document.getElementById('qrcode').value = '';
				}else{

					// jjg
					jjg_h = document.getElementById('jjg').value;
					if(jjg_h != 0){
						jjg_h=jjg_h;
					}else{
						jjg_h = 0;
					}
					jjg_h = parseFloat(jjg_h) + parseFloat(janjang);
					document.getElementById('jjg').value = jjg_h;
					// brondolan
					brondolan_h = document.getElementById('brondol').value;
					if(brondolan_h != 0){
						brondolan_h=brondolan_h;
					}else{
						brondolan_h = 0;
					}
					brondolan_h = parseFloat(brondolan_h) + parseFloat(brondolan);
					document.getElementById('brondol').value = brondolan_h;

					loadPanen(ticketno);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletepanen(qrcode,notiket,tph,blok,pemanen,jjg,brondolan){
	param='notiket='+notiket;
	param+='&qrcode='+qrcode;
	param+='&tph='+tph;
	param+='&blok='+blok;
	param+='&pemanen='+pemanen;
    param+='&method=deletepanen';
    tujuan='trx_tbskebunx_slave_kosong.php';
	function respog(){
		if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                }  else {

					// jjg
					jjg_h = document.getElementById('jjg').value;
					jjg_h = parseFloat(jjg_h) - parseFloat(jjg);
					document.getElementById('jjg').value = jjg_h;
					// brondolan
					brondolan_h = document.getElementById('brondol').value;
					brondolan_h = parseFloat(brondolan_h) - parseFloat(brondolan);
					document.getElementById('brondol').value = brondolan_h;


                    loadPanen(notiket);
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        } 
	} 
	post_response_text(tujuan, param, respog);
}

function getnoSPB() {
	unit = getValue('unit');
	divisi = getValue('divisi');
    param='method=getnoSPB&unit='+unit+'&divisi='+divisi;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
                    document.getElementById('nospb').value='';
                } else {
                    document.getElementById('nospb').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }  
}
function getkendaraan() {
	tipeangkut = getValue('tipe_angkutan');
	transportir = getValue('transportir');
    param='method=getkendaraan&tipeangkut='+tipeangkut+'&transportir='+transportir;
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert("Info",con.responseText);
                    document.getElementById('nokendaraan').innerHTML='';
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
function gettransportir() {
	tipeangkut = getValue('tipe_angkutan');
	if(tipeangkut == '0'){
		document.getElementById('transportir').disabled=false;
	}else{
		setValue2('transportir','');
		document.getElementById('transportir').disabled=true;
		getkendaraan();
	}
}

// end joki


function hitungkg(id=''){
	bruto=document.getElementById('bruto').value;
	if(bruto==''){bruto=0;}
	
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
	hitungpr(id);
}

function hitungpr(id=''){
	bruto=document.getElementById('bruto').value;
	ttljg=0;
	ttlpr=0;
	ttlkg=0;
	
	if(bruto==''){bruto=0;}
	
	var jg = document.getElementsByName('jjg');
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	for (var i = 0; i < kg.length; i++){
		if(kg[i].value > 0 && kg[i].value != ''){
			hsl=0;
			if(bruto > 0){
				hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;				
			}
			if(typeof pr[i]!='undefined'){
				if(kg[i].id==id){
					pr[i].value = Math.round(parseFloat(hsl) * 100) / 100;					
				}
			}
		}
		if(typeof jg[i]!='undefined'){
			if(jg[i].value != ''){
				ttljg=parseFloat(ttljg) + parseFloat(jg[i].value);					
			}
		}
		if(typeof pr[i]!='undefined'){
			if(pr[i].value != ''){
				ttlpr=parseFloat(ttlpr) + parseFloat(pr[i].value);					
			}
		}
		if(typeof kg[i]!='undefined'){
			if(kg[i].value != ''){
				ttlkg=parseFloat(ttlkg) + parseFloat(kg[i].value);
			}
		}
	}
		
	document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
	document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
	document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
	
	document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
	document.getElementById('ttlsorkg').value=Math.round(ttlkg);
	
	
	if(document.getElementById('showsortasi').style.display==''){
		document.getElementById('kgpotongan').value=Math.round(ttlkg);
	}else{
		document.getElementById('kgpotongan').value=0;
	}
	
	netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
    document.getElementById('netto').value=netto;
}

function hitungres(){

	bruto=document.getElementById('bruto').value;
	ttljg=0;
	ttlpr=0;
	ttlkg=0;
	if(bruto==''){bruto=0;}
	
	if(bruto > 0){
		var jg = document.getElementsByName('jjg');
		var pr = document.getElementsByName('persen');
		var kg = document.getElementsByName('kg');
		for (var i = 0; i < kg.length; i++){
			hsl=0;
			if(bruto > 0){
				if(pr[i].value!=''){
					hsl = parseFloat(bruto) * parseFloat(pr[i].value) / 100;
					if(typeof kg[i]!='undefined'){
						kg[i].value = hsl;					
					}
				}
				
				if(kg[i].value!=''){
					hsl = parseFloat(kg[i].value) / parseFloat(bruto) * 100;				
					if(typeof pr[i]!='undefined'){
						pr[i].value = Math.round(parseFloat(hsl) * 100) / 100;
					}
				}
				
				if(typeof jg[i]!='undefined'){
					if(jg[i].value != ''){
						ttljg=parseFloat(ttljg) + parseFloat(jg[i].value);					
					}
				}
				if(typeof pr[i]!='undefined'){
					if(pr[i].value != ''){
						ttlpr=parseFloat(ttlpr) + parseFloat(pr[i].value);					
					}
				}
				if(typeof kg[i]!='undefined'){
					if(kg[i].value != ''){
						ttlkg=parseFloat(ttlkg) + parseFloat(kg[i].value);
					}
				}
			}
		}
			
		document.getElementById('ttlgrdjjg').value=Math.round(ttljg);
		document.getElementById('ttlgrdpersen').value=Math.round(ttlpr * 100) / 100;
		document.getElementById('ttlgrdkg').value=Math.round(ttlkg);
		
		document.getElementById('ttlsorpersen').value=Math.round(ttlpr * 100) / 100;
		document.getElementById('ttlsorkg').value=Math.round(ttlkg);
		
		
		if(document.getElementById('showsortasi').style.display==''){
			document.getElementById('kgpotongan').value=Math.round(ttlkg);
		}else{
			document.getElementById('kgpotongan').value=0;
		}
		
		netto = document.getElementById('bruto').value-document.getElementById('kgpotongan').value;
		document.getElementById('netto').value=netto;

	}
}

function cleargradsor(){
	var jg = document.getElementsByName('jjg');
	var pr = document.getElementsByName('persen');
	var kg = document.getElementsByName('kg');
	for (var i = 0; i < kg.length; i++){
		if(typeof jg[i]!='undefined'){
			jg[i].value='';
		}
		if(typeof pr[i]!='undefined'){
			pr[i].value='';
		}
		if(typeof kg[i]!='undefined'){
			kg[i].value='';
		}
	}
	document.getElementById('ttlgrdjjg').value='';
	document.getElementById('ttlgrdpersen').value='';
	document.getElementById('ttlgrdkg').value='';
	
	document.getElementById('ttlsorpersen').value='';
	document.getElementById('ttlsorkg').value='';
}

function generatenotiket() {
    param='method=generatenotiket';
    tujuan='trx_tbskebunx_slave_kosong.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
					var arrlist = new Array();
					arrlist = JSON.parse(con.responseText);
					
                    document.getElementById('ticketno').value=arrlist['tiket'];
                    document.getElementById('jlhkendaraan0').innerHTML=arrlist['masuk'];
                    document.getElementById('jlhkendaraan1').innerHTML=arrlist['keluar'];
					// document.getElementById('qrcode').focus();
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
	tujuan='trx_tbskebunx_slave_kosong.php';
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



$('#kodeproduk').on("select2:selecting", function(e) { 
	alert('masuk');
	getkontrak();
});


const btngetweight1 = document.getElementById('getweight1');
if (btngetweight1) {
  btngetweight1.addEventListener('click', function () {
    ambil_tanggal('datein','wei1st','penerimaan');
  });
}

const btngetweight2 = document.getElementById('getweight2');
if (btngetweight2) {
  btngetweight2.addEventListener('click', function () {
    ambil_tanggal('dateout','wei2nd','I');
	hitungres();
  });
}

const btnsimpan = document.getElementById('simpan');
if (btnsimpan) {
	btnsimpan.addEventListener('click', function () {
	  simpan();
	});
}