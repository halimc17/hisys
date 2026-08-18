function excel(ev,tujuan){
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
	if(unitexp==''||perexp==''){
		alert('Lengkapi unit dan periode.');
		return;
	}
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);	
}


function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function viewexcel(pt,tipe){
	ev = 'event';
	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe;
	tujuan = 'sdm_kontrakkary_slave.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function displayList(){
    document.getElementById('divsch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    loaddata(0);
}

function edit(notransaksi,jenis,pt,karyawanid,atasanlangsung,gajipokok,tunjjabatan,konsumsi,transport,uangdaerah,cuti,poh,tiketcuti,perumahan,telekomunikasi,tanggal,pihakpertama,dikeluarkan,tanggaldari,tanggalsampai,jangkawaktu,satjangka){
    document.getElementById('notransaksi').value=notransaksi
	document.getElementById('notransaksi').disabled=true;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
	document.getElementById('jenis').value=jenis;
	document.getElementById('pt').value=pt;
	document.getElementById('karyawanid').value=karyawanid;
	document.getElementById('atasanlangsung').value=atasanlangsung;
	document.getElementById('gajipokok').value=gajipokok;
	document.getElementById('tunjjabatan').value=tunjjabatan;
	document.getElementById('konsumsi').value=konsumsi;
	document.getElementById('transport').value=transport;
	document.getElementById('uangdaerah').value=uangdaerah;
	document.getElementById('cuti').value=cuti;
	document.getElementById('poh').value=poh;
	document.getElementById('tiketcuti').value=tiketcuti;
	document.getElementById('perumahan').value=perumahan;
	document.getElementById('telekomunikasi').value=telekomunikasi;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('pihakpertama').value=pihakpertama;
	document.getElementById('dikeluarkan').value=dikeluarkan;
	document.getElementById('tanggaldari').value=tanggaldari;
	document.getElementById('tanggalsampai').value=tanggalsampai;
	document.getElementById('jangkawaktu').value=jangkawaktu;
	document.getElementById('satjangka').value=satjangka;
	document.getElementById('method').value='update';
	gettglkont(jenis);
}

function del(notransaksi){
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='sdm_kontrakkary_slave.php';
    if(confirm('Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
	  if(con.readyState==4){
		if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
			}else {
			   loaddata();
			   
			}
		}else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

function getpoh(){
    karyawanid = document.getElementById('karyawanid').value;
    param='method=getpoh'+'&karyawanid='+karyawanid;
    tujuan='sdm_kontrakkary_slave.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
	  if(con.readyState==4){
		if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
			}else {
				//alert(con.responseText);
			   document.getElementById('poh').value=con.responseText;
			   
			}
		}else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

//==================================================================//
function getkary(pt){
	param='pt='+pt;
    param+='&method=getkary';

    tujuan='sdm_kontrakkary_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   document.getElementById('karyawanid').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function gettglkont(jenis){
	jenis=document.getElementById('jenis').value;
	if(jenis=='PKWTT'){
		document.getElementById('tanggaldari').value='';
		document.getElementById('tanggaldari').disabled=true;
		document.getElementById('tanggalsampai').value='';
		document.getElementById('tanggalsampai').disabled=true;
		document.getElementById('jangkawaktu').value='';
		document.getElementById('jangkawaktu').disabled=true;
		document.getElementById('satjangka').value='';
		document.getElementById('satjangka').disabled=true;
	}else{
		document.getElementById('tanggaldari').disabled=false;
		document.getElementById('tanggalsampai').disabled=false;
		document.getElementById('jangkawaktu').disabled=false;
		document.getElementById('satjangka').disabled=false;
	}
}
function save(){
    notransaksi=document.getElementById('notransaksi').value;
	jenis=document.getElementById('jenis').value;
	pt=document.getElementById('pt').value;
	karyawanid=document.getElementById('karyawanid').value;
	atasanlangsung=document.getElementById('atasanlangsung').value;
	gajipokok=document.getElementById('gajipokok').value;
	tunjjabatan=document.getElementById('tunjjabatan').value;
	konsumsi=document.getElementById('konsumsi').value;
	transport=document.getElementById('transport').value;
	uangdaerah=document.getElementById('uangdaerah').value;
	cuti=document.getElementById('cuti').value;
	poh=document.getElementById('poh').value;
	tiketcuti=document.getElementById('tiketcuti').value;
	perumahan=document.getElementById('perumahan').value;
	telekomunikasi=document.getElementById('telekomunikasi').value;
	tanggal=document.getElementById('tanggal').value;
	pihakpertama=document.getElementById('pihakpertama').value;
	dikeluarkan=document.getElementById('dikeluarkan').value;
	tanggaldari=document.getElementById('tanggaldari').value;
	tanggalsampai=document.getElementById('tanggalsampai').value;
	jangkawaktu=document.getElementById('jangkawaktu').value;
	satjangka=document.getElementById('satjangka').value;
    method=document.getElementById('method').value;
    
    if((pt=='' || jenis==''|| pihakpertama==''|| karyawanid==''|| tanggal=='')){
        alert('PT, Jenis, Pihak Pertama, Karyawan, Tanggal wajib di isi !!!');
        return;
    }

	param='&notransaksi='+notransaksi;
	param+='&jenis='+jenis;
	param+='&pt='+pt;
	param+='&karyawanid='+karyawanid;
	param+='&atasanlangsung='+atasanlangsung;
	param+='&gajipokok='+gajipokok;
	param+='&tunjjabatan='+tunjjabatan;
	param+='&konsumsi='+konsumsi;
	param+='&transport='+transport;
	param+='&uangdaerah='+uangdaerah;
	param+='&cuti='+cuti;
	param+='&poh='+poh;
	param+='&tiketcuti='+tiketcuti;
	param+='&perumahan='+perumahan;
	param+='&telekomunikasi='+telekomunikasi;
	param+='&tanggal='+tanggal;
	param+='&pihakpertama='+pihakpertama;
	param+='&dikeluarkan='+dikeluarkan;
	param+='&tanggaldari='+tanggaldari;
	param+='&tanggalsampai='+tanggalsampai;
	param+='&jangkawaktu='+jangkawaktu;
	param+='&satjangka='+satjangka;
    param+='&method='+method;

    tujuan='sdm_kontrakkary_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('hasil').innerHTML=con.responseText;
                    loaddata();

				   //cancel();
                }
            } else {
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
    loaddata(paged);	
}



function loaddata(page) {
	divsch = document.getElementById('divsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}

	tujuan = 'sdm_kontrakkary_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function pdf(jenis,notransaksi) {
	param = 'notransaksi=' + notransaksi;
	param += '&jenis=' + jenis;
	param += '&method=pdf';
	tujuan = 'sdm_kontrakkary_slave_pdf.php?' + param;
	title = '';
	width = '1000';
	height = '400';
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}
//=====================================================
function cancel(){
    document.getElementById('pt').value='';
    document.getElementById('notransaksi').value='';
	document.getElementById('jenis').value='';
	document.getElementById('karyawanid').value='';
	document.getElementById('atasanlangsung').value='';
	document.getElementById('gajipokok').value='';
	document.getElementById('tunjjabatan').value='';
	document.getElementById('konsumsi').value='';
	document.getElementById('transport').value='';
	document.getElementById('uangdaerah').value='';
	document.getElementById('cuti').value='';
	document.getElementById('poh').value='';
	document.getElementById('tiketcuti').value='';
	document.getElementById('perumahan').value='';
	document.getElementById('telekomunikasi').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('pihakpertama').value='';
	document.getElementById('dikeluarkan').value='';
	document.getElementById('tanggaldari').value='';
	document.getElementById('tanggalsampai').value='';
	document.getElementById('jangkawaktu').value='';
	document.getElementById('satjangka').value='';
	document.getElementById('tanggaldari').disabled=false;
	document.getElementById('tanggalsampai').disabled=false;
	document.getElementById('jangkawaktu').disabled=false;
	document.getElementById('satjangka').disabled=false;
}

function form(){
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
}