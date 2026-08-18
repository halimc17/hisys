


function gettotal(currRow){
	rp_muat = trim(document.getElementById('rp_muat_'+currRow).value);
	rp_angkut = trim(document.getElementById('rp_angkut_'+currRow).value);
	potonganrp = trim(document.getElementById('potonganrp_'+currRow).value);
	rp_muat=remove_comma_var(rp_muat);
	rp_angkut=remove_comma_var(rp_angkut);
	potonganrp=remove_comma_var(potonganrp);
	
	
	
	netto=(parseFloat(rp_muat)+parseFloat(rp_angkut))-parseFloat(potonganrp);
	document.getElementById('ttlrp_'+currRow).value=netto;
	
}



function loaddatadetail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function detail() {
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	periodebyr= document.getElementById('periodebyr').value;
	kud    = document.getElementById('kud').value;
	if (kodeorg == '' || periode == '' || kud=='' ) {
		alert('Kode Organisasi, Kud, Periode Bulan Wajib diisi.');
		return;
	}
	//document.getElementById('tomboldetail').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('periode').disabled = true;
	document.getElementById('periodebyr').disabled = true;
	document.getElementById('kud').disabled = true;
	param = 'method=detail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	param += '&kud=' + kud;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					getnotrans(kodeorg,periode,kud);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getnotrans(kodeorg,periode,kud) {

	param = "";
	method    = 'getnotrans';

	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&method=' + method;
	param += '&periode=' + periode;
	param += '&kud=' + kud;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {

		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}


	}

}

function saveAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Simpan semua ???")) {
		save(1, maxRow);
	}
}


function save(currRow, maxRow) {	
	param = "";
	method    = trim(document.getElementById('method').value);
	notransaksi   = trim(document.getElementById('notransaksi').value);
	kodeorg   = trim(document.getElementById('kodeorg').value);
	periode   = trim(document.getElementById('periode').value);
	periodebyr= trim(document.getElementById('periodebyr').value);
	kud= trim(document.getElementById('kud').value);
	nilaipph=  trim(document.getElementById('nilaipph_'+currRow).innerHTML);
	idkav=  trim(document.getElementById('id_kav_'+currRow).innerHTML);
	nama=  trim(document.getElementById('nama_'+currRow).innerHTML);
	baris=  currRow;

	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&periodebyr=' + periodebyr;
	param += '&method=' + method;
	param += '&periode=' + periode;
	param += '&notransaksi=' + notransaksi;
	param += '&kud=' + kud;
	param += '&nilaipph=' + nilaipph;
	param += '&idkav=' + idkav;
	param += '&nama=' + nama;
	param += '&baris=' + baris;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('tr_' + currRow).style.backgroundColor = 'red';
					//unlockScreen();
				} else {
					if (currRow != undefined) {
						document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
					document.getElementById('tr_' + currRow).style.display = 'none';
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alert('Done');
						displayList();
						//detail();
					} else {
						save(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getdetailspb() {
	kodeorg = document.getElementById('kodeorg').value;
	tglheader = document.getElementById('tgl').value;
	periode = document.getElementById('periode').value;
	periodebyr = document.getElementById('periodebyr').value;
	spk = document.getElementById('spk').value;
	nospb = document.getElementById('nospb').value;
	jnskend = document.getElementById('jeniskend').value;
	
	param = 'method=getdetailspb';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode;
	param += '&spk=' + spk;
	param += '&tglheader=' + tglheader;
	param += '&nospb=' + nospb;
	param += '&jnskend=' + jnskend;
	param += '&periodebyr=' + periodebyr;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					document.getElementById('notiket').value=trim(isi[0]);
					document.getElementById('sopir').value=trim(isi[1]);
					document.getElementById('divisi').value=trim(isi[2]);
					document.getElementById('tanggal').value=trim(isi[3]);
					document.getElementById('nopol').value=trim(isi[4]);
					document.getElementById('jjg').value=trim(isi[5]);
					document.getElementById('kgwb').value=trim(isi[6]);
					document.getElementById('inputharga').innerHTML=trim(isi[7]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hitungrupiah(i){
	kgwb   =document.getElementById('kgwb_'+i).innerHTML;
	kgwbbyr=document.getElementById('kgwbbyr_'+i).value;
	harga  =document.getElementById('harga'+i).innerHTML;
	kgwb   =remove_comma_var(kgwb);
	kgwbbyr=remove_comma_var(kgwbbyr);
	harga  =remove_comma_var(harga);
	
	if(parseFloat(kgwbbyr)>parseFloat(kgwb)){
		alert("Kg tidak boleh lebih dari Kg Netto.");
		kgwbbyr = kgwb;
		document.getElementById('kgwbbyr_'+i).value=kgwbbyr;
	}
	
	rupiah = parseFloat(kgwbbyr)*parseFloat(harga);
	if(isNaN(rupiah)){rupiah=0;}
	document.getElementById('rpfee'+i).value=rupiah;
}

function getharga(no,maxrow,nospbold) {
	blok    = document.getElementById('blok_'+no).innerHTML;
	namafee = document.getElementById('namafee'+no).value;
	jenisfee= document.getElementById('jenisfeex'+no).innerHTML;
	jenisvhc= document.getElementById('jenisvhc_'+no).value;
	nospb   = document.getElementById('nospb_'+no).innerHTML;
	kgwb    = document.getElementById('kgwbbyr_'+no).value;
	ttlrow  = document.getElementById('jumlahrow').value;
	jlspb   = document.getElementsByName(nospbold+'[]');
	jlhspb  = jlspb.length;
	
	if(maxrow=='' || maxrow==undefined){
		maxrow=ttlrow;
	}
	
	nox = parseFloat(no);
	n = (parseFloat(no)+1);
	if(n<=ttlrow){
		// for (i = n; i <= ttlrow; i++) {
			// document.getElementById('jenisvhc_' + i).value = jenisvhc;
		// }
		
		for (i = 0; i <= jlhspb; i++) {
			if(jlspb[i]!=undefined && nospb==nospbold){				
				jlspb[i].value = jenisvhc;
			}
		}
	}
	
	
	param = 'method=getharga';
	param += '&blok=' + blok;
	param += '&namafee=' + namafee;
	param += '&jenisfee=' + jenisfee;
	param += '&jenisvhc=' + jenisvhc;
	param += '&kgwb=' + kgwb;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("##");
					harga = value=trim(isi[0]);
					total = value=trim(isi[1]);
					
					document.getElementById('harga'+no).innerHTML=harga;
					document.getElementById('rpfee'+no).value=total;
					
					no = parseFloat(no)+1;
                    if((no>maxrow) || (maxrow == undefined)){
						
					} else {
						if(nospb==nospbold){							
							getharga(no,maxrow,nospbold);
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

function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}
function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	//cancelHead();
	//cancel();
	cancel();
}
function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}
function viewdetailx(kodeorg, periode,nospb) {
	width = '720';
	height = '';
	content = "<fieldset><div id=container align=center style=\"width:700px;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
	
	param = 'method=viewdetailx' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&nospb=' + nospb;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexcel(kodeorg, divisi,periode,periodebyr,jenis){
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&divisi=' + divisi+ '&periodebyr=' + periodebyr+ '&jenis=' + jenis;
	tujuan = 'kebun_3pphpetani_slave.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	//printFile(param,tujuan,title,ev);
	
	printnopopup(tujuan);

}

function html(notransaksi,jenis) {
	
	param = 'method=html' + '&notransaksi=' + notransaksi+'&jenis=' + jenis;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	document.getElementById('divsch').value = '';
	document.getElementById('tglsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}


function del(notransaksi) {
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_3pphpetani_slave.php';
	if (confirm(' Anda yakin ingin menghapus nomor transaksi')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
					// document.getElementById('contain').innerHTML = con.responseText;
					// loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(notransaksi) {
	param = 'method=posting' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_3pphpetani_slave.php';
	if (confirm('Anda yakin ingin ???')) {
		post_response_text(tujuan, param, respog);
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
function unposting(notransaksi) {
	param = 'method=unposting' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_3pphpetani_slave.php';
	if (confirm('Anda yakin ingin unposting ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	divsch   = document.getElementById('divsch').value;
	tglsch   = document.getElementById('tglsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'kebun_3pphpetani_slave.php';
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
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('periode').disabled = false;
	document.getElementById('periode').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('kud').disabled = false;
	document.getElementById('kud').value = '';
	document.getElementById('notransaksi').value = '';
	setValue2('periode',null);
	
}

function getdata() {
	blok = document.getElementById('blok').value;
	tgl = document.getElementById('tgl').value;
	param = 'method=getdata' + '&blok=' + blok + '&tgl=' + tgl;
	tujuan = 'kebun_3pphpetani_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//
					//
					isi = con.responseText.split("##");
					document.getElementById('thntnm').value = trim(isi[0]);
					document.getElementById('luasaresta').value = trim(isi[1]);
					document.getElementById('bjr').value = trim(isi[2]);
					getkg();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function cleardetail() {
	document.getElementById('blok').value = '';
	document.getElementById('blok').disabled = false;
	document.getElementById('thntnm').value = '';
	document.getElementById('luasaresta').value = '';
	document.getElementById('luaspnn').value = '';
	document.getElementById('tk').value = '';
	document.getElementById('jjgpnn').value = '';
	document.getElementById('afkirjjg').value = '';
	document.getElementById('afkirket').value = '';
	document.getElementById('bjr').value = '';
	document.getElementById('kgkebun').value = '';
}


function editdetail(divisi,tanggal,blok,tahuntanam,luasproduksi,luaspanen,tenagakerja,jjgpanen,bjr,kgkebun,jjgafkir,keterangan){
	document.getElementById('div').value = divisi;
	document.getElementById('tgl').value = tanggal;
	document.getElementById('blok').value = blok;
	document.getElementById('thntnm').value = tahuntanam;
	document.getElementById('luasaresta').value = luasproduksi;
	document.getElementById('luaspnn').value = luaspanen;
	document.getElementById('tk').value = tenagakerja;
	document.getElementById('jjgpnn').value = jjgpanen;
	document.getElementById('bjr').value = bjr;
	document.getElementById('kgkebun').value = kgkebun;
	document.getElementById('afkirjjg').value = jjgafkir;
	document.getElementById('afkirket').value = keterangan;
	document.getElementById('method').value = 'updatedetail';
}


function viewdetailbapp(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe){
	param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin;
    width = '';
    height = '';
    content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    title = "View";
    showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contviewx').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function showhidedetail(rows,total){
	for(var i = 1; i <= total; i++) {
		key = document.getElementById('tr_dt2_'+rows+'_'+i).style.display;
		if(key=='none'){
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='';
		}else{
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='none';
		}
    }
}

function UploadFile(notransaksi,tanggal,termin,numRow,ev,nopengajuan) {
	title = "List File";
	formajukan(title,ev);
	param = 'method=UploadFile' + '&notransaksi=' + notransaksi+ '&tanggal=' + tanggal+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containervoid').innerHTML = con.responseText;
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function formajukan(title,ev) {
	width = '';
	height = '';
	content = "<div id=containervoid ></div>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
}


function loadfiles(notransaksi,termin,nopengajuan) {
	param = 'method=loadfiles&notransaksi=' + notransaksi+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').innerHTML;
	var pengajuanspk = document.getElementById('pengajuanspk').innerHTML;
	var tanggal = document.getElementById('tanggal').innerHTML;
	var termin = document.getElementById('terminup').innerHTML;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("pengajuanspk", pengajuanspk);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("termin", termin);
	formdata.append("tanggal", tanggal);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	if (notransaksi == "" || pengajuanspk=="") {
		alert("warning : Nomor Transaksi di Perlukan !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "log_slave_realisasispkx.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('btnsubmit').disabled=false;
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,termin,pengajuanspk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}






function viewdetail(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

