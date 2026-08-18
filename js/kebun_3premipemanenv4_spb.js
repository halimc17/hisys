function gettanggal(){
	prd  = document.getElementById('prd').value;
	tahap= document.getElementById('tahap').value;
	
	tahun = prd.substr(0,4);
	bulan = prd.substr(5,2);
	if(tahap=='1'){
		tglawal = "01";
		tglakhir = "15";
	}else{
		tglawal = "16";
		var date = new Date(tahun, parseFloat(bulan)-1, 1);
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
		tglakhir = lastDay.getDate();
	}
	
	document.getElementById('tgl1').value=tglawal+"-"+bulan+"-"+tahun;
	document.getElementById('tgl2').value=tglakhir+"-"+bulan+"-"+tahun;
}

function gettanggal2(){
	tgl1  = document.getElementById('tgl1').value;
	document.getElementById('tgl2').value=tgl1;
}

function add_new_data(){
	document.getElementById('detail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	//document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	batallist();
	loaddata(0);
}

function previewdata(blok,tgl1, ev) {
	param = 'proses=previewdata&blok=' + blok;
	param += '&tgl1=' + tgl1;
	tujuan = 'kebun_slave_save_3premipemanenv4_spb.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('prev').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
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

	document.getElementById('contloaddata').style.display="";
	prdlist = document.getElementById('prdlist').value;
	unitlist = document.getElementById('unitlist').value;
	afdlist = document.getElementById('afdlist').value;
	param = 'proses=loaddata&page=' + page;

	if (prdlist != '') {
		param += '&prdlist=' + prdlist;
	}
	if (unitlist != '') {
		param += '&unitlist=' + unitlist;
	}
	if (afdlist != '') {
		param += '&afdlist=' + afdlist;
	}

	tujuan = 'kebun_slave_save_3premipemanenv4_spb.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('printContainerlist').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					
					document.getElementById('contpivot').style.display="none";
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batal() {
	document.getElementById('prd').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('afd').value = '';
	document.getElementById('printContainer').innerHTML = '';
}

function batallist() {
	document.getElementById('prdlist').value = '';
	document.getElementById('unitlist').value = '';
	document.getElementById('afdlist').value = '';
	document.getElementById('contloaddata').style.display="";
	document.getElementById('contpivot').style.display="none";
	loaddata(0);
}

function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}

function del(notransaksi, prd, unit,tgl1,tgl2) {
	param = 'proses=deleteTrans&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	param += '&tgl1=' + tgl1;
	param += '&tgl2=' + tgl2;
	tujuan = 'kebun_slave_save_3premipemanenv4_spb.php';
	if (confirm(' Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(notransaksi, prd, unit) {
	param = 'proses=unposting&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_3premipemanenv4_spb.php';
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
					alertify.alert('Unposting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi, prd, unit, tanggalpanen) {
	param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&tanggalpanen=' + tanggalpanen;
	tujuan = 'kebun_slave_save_posting3premipemanen_v4.php';
	if (confirm('Posting akan memakan waktu cukup lama dan pastikan koneksi anda stabil, ingin tetap melanjutkan ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Posting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

maxf = 0
sekarang = 1;
function saveAll(maxRow) {
	maxf = maxRow;
	loopsave(1, maxRow);
}

function loopsave(currRow, maxRow) {

	prd           = document.getElementById('prd_'+ currRow).innerHTML;
	unit          = document.getElementById('unit_'+ currRow).innerHTML;
	afd           = document.getElementById('afd_'+ currRow).innerHTML;
	notransaksi   = document.getElementById('notransaksi_' + currRow).innerHTML;
	karid         = document.getElementById('karid_' + currRow).innerHTML;
	tglpnn        = document.getElementById('tglpnn_' + currRow).innerHTML;
	jenispremi    = document.getElementById('jenispremi_' + currRow).innerHTML;
	nospb         = document.getElementById('nospb_' + currRow).innerHTML;
	blokbesar     = document.getElementById('blokbesar_' + currRow).innerHTML;
	blokkecil     = document.getElementById('blokkecil_' + currRow).innerHTML;
	tahuntanam    = document.getElementById('tahuntanam_' + currRow).innerHTML;
	hektarpanen   = document.getElementById('hektarpanen_' + currRow).innerHTML;
	brondol       = document.getElementById('brondol_' + currRow).innerHTML;
	jjg        	  = document.getElementById('jjg_' + currRow).innerHTML;
	bjr           = document.getElementById('bjr_' + currRow).innerHTML;
	totalkg       = document.getElementById('totalkg_' + currRow).innerHTML;
	basistahuntanam = document.getElementById('basistahuntanam_' + currRow).innerHTML;
	hk              = document.getElementById('hk_' + currRow).innerHTML;
	pothk           = document.getElementById('pothk_' + currRow).innerHTML;
	basispakai      = document.getElementById('basispakai_' + currRow).innerHTML;
	basisbaru       = document.getElementById('basisbaru_' + currRow).innerHTML;
	lebihbasis      = document.getElementById('lebihbasis_' + currRow).innerHTML;
	upah            = document.getElementById('upah_' + currRow).innerHTML;
	upahpot         = document.getElementById('upahpot_' + currRow).innerHTML;
	upahlb          = document.getElementById('upahlb_' + currRow).innerHTML;
	upahbro         = document.getElementById('upahbro_' + currRow).innerHTML;
	premiks         = document.getElementById('premiks_' + currRow).innerHTML;
	premikh         = document.getElementById('premikh_' + currRow).innerHTML;
	dendapn         = document.getElementById('dendapn_' + currRow).innerHTML;
	totalupah       = document.getElementById('totalupah_' + currRow).innerHTML;

	mandor1         = document.getElementById('mandor1_' + currRow).innerHTML;
	mandor          = document.getElementById('mandor_' + currRow).innerHTML;
	kerani          = document.getElementById('kerani_' + currRow).innerHTML;
	
	if (prd == '' || unit == '' || afd == '') {
		alertify.alert("Data tidak lengkap");
		return;
	} else {
		param  = 'prd=' + prd;
		param += '&unit=' + unit;
		param += '&afd=' + afd;
		param += '&notransaksi=' + notransaksi;
		param += '&karid=' + karid;
		param += '&tglpnn=' + tglpnn;
		param += '&jenispremi=' + jenispremi;
		param += '&nospb=' + nospb;
		param += '&blokbesar=' + blokbesar;
		param += '&blokkecil=' + blokkecil;
		param += '&tahuntanam=' + tahuntanam;
		param += '&hektarpanen=' + hektarpanen;
		param += '&brondol=' + brondol;
		param += '&jjg=' + jjg;
		param += '&bjr=' + bjr;
		param += '&totalkg=' + totalkg;
		param += '&basistahuntanam=' + basistahuntanam;
		param += '&hk=' + hk;
		param += '&pothk=' + pothk;
		param += '&basispakai=' + basispakai;
		param += '&basisbaru=' + basisbaru;
		param += '&lebihbasis=' + lebihbasis;
		param += '&upah=' + upah;
		param += '&upahpot=' + upahpot;
		param += '&upahlb=' + upahlb;
		param += '&upahbro=' + upahbro;
		param += '&premiks=' + premiks;
		param += '&premikh=' + premikh;
		param += '&dendapn=' + dendapn;
		param += '&totalupah=' + totalupah;
		param += '&mandor1=' + mandor1;
		param += '&mandor=' + mandor;
		param += '&kerani=' + kerani;
		param += '&currRow=' + currRow;

		param   += "&proses=savedata";
		tujuan = 'kebun_slave_save_3premipemanenv4_spb.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alertify.alert('Done');
						document.getElementById('printContainer').innerHTML = '';
						loaddata(0);
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisi(){
	unit          = document.getElementById('unit').value;
	
	param='unit='+unit+'&proses=getdivisi';
	param += '&unitlist=' + unitlist;
	tujuan='kebun_slave_save_3premipemanenv4_spb.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('afd').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getdivisiList(){
	
	unitlist = document.getElementById('unitlist').value;
	
	param='unitlist='+unitlist+'&proses=getdivisiList';

	tujuan='kebun_slave_save_3premipemanenv4_spb.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('afdlist').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function previewdataBaru() {

	unit =	document.getElementById('unit').value;
	prd =	document.getElementById('prd').value;
	tahap =	document.getElementById('tahap').value;
	tgl1 =	document.getElementById('tgl1').value;
	tgl2 =	document.getElementById('tgl2').value;
	afd =	document.getElementById('afd').value;


	param +='&proses=preview';
	param +='&unit=' + unit;
	param +='&prd=' + prd;
	param +='&tahap=' + tahap;
	param +='&tgl1=' + tgl1;
	param +='&tgl2=' + tgl2;
	param +='&afd=' + afd;
	tujuan='kebun_slave_3premipemanenv4_spb.php';
	
	post_response_text(tujuan,param,respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML=con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexcel(){

	unit =	document.getElementById('unit').value;
	prd =	document.getElementById('prd').value;
	tahap =	document.getElementById('tahap').value;
	tgl1 =	document.getElementById('tgl1').value;
	tgl2 =	document.getElementById('tgl2').value;
	afd =	document.getElementById('afd').value;


	param +='&proses=excel';
	param +='&unit=' + unit;
	param +='&prd=' + prd;
	param +='&tahap=' + tahap;
	param +='&tgl1=' + tgl1;
	param +='&tgl2=' + tgl2;
	param +='&afd=' + afd;

	tujuan = 'kebun_slave_3premipemanenv4_spb.php';
	ev='event';
	judul='Report Ms.Excel';	
    printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}


