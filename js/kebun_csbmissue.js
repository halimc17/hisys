function copy(){
	dari  = document.getElementById('daricopy').value;
	ke    = document.getElementById('kecopy').value;
	divisi= document.getElementById('divisicopy').value;
	
	param = 'method=copy';
	param += '&dari=' + dari;
	param += '&ke=' + ke;
	param += '&divisi=' + divisi;
	
	if(dari==ke){
		alertify.alert("Periode dari dan periode tujuan tidak boleh sama"); return;
	}
	
	if(ke<dari){
		alertify.alert("Periode tujuan tidak boleh lebih kecil dari periode dari/sumber."); return;
	}
	
	validate([
        ["daricopy","Periode dari tidak boleh kosong."],
        ["kecopy","Periode tujuan tidak boleh kosong."],
        ["divisicopy","Divisi tidak boleh kosong"]
	]);
	
	tujuan = 'kebun_slave_csbmissue.php';
	if(confirm("Proses ini akan meng-copy data dari periode "+dari+" ke periode "+ke+" untuk divisi "+divisi+", anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(con.responseText!=''){
						if(confirm("Data untuk periode "+ke+" divisi "+divisi+" sudah ada, click OK untuk mereplace data yg sudah ada.")){
							prosescopy();
						}
					}else{						
						prosescopy();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function prosescopy(){
	dari  = document.getElementById('daricopy').value;
	ke    = document.getElementById('kecopy').value;
	divisi= document.getElementById('divisicopy').value;
	
	param = 'method=prosescopy';
	param += '&dari=' + dari;
	param += '&ke=' + ke;
	param += '&divisi=' + divisi;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alert("Done");
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function viewlist(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	//document.getElementById('viewlist').style.display = 'block';
	
	viewlistdata();
}
function getPagelist() {
	pg = document.getElementById('pagelist');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	viewlistdata(paged);
}
function viewlistdata(page){
	jenis       = document.getElementById('jenisview').value;
	kodeorg     = document.getElementById('kodeorgview').value;
	tgl         = document.getElementById('tglview').value;
	notransaksi = document.getElementById('notransaksiview').value;
	namakaryawan= document.getElementById('namaview').value;
	sumber    = document.getElementById('sumberview').value;
	
	param = 'method=viewlistdata';
	param += '&page=' + page;
	param += '&notransaksi=' + notransaksi;
	param += '&kodeorg=' + kodeorg;
	param += '&namakaryawan=' + namakaryawan;
	param += '&jenis=' + jenis;
	param += '&tgl=' + tgl;
	param += '&sumber=' + sumber;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('containview').innerHTML = data[0];
					if(data[1]!=undefined){						
						document.getElementById('footDataview').innerHTML = data[1];
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	//document.getElementById('viewlist').style.display = 'none';
	document.getElementById('mode').value='baru';
	cancel();
}
function cancel(){
	document.getElementById('kodeorg').value='';
	document.getElementById('divisi').value='';
	document.getElementById('periode').value='';
	
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('detail').innerHTML='';
}

function previewdata() {
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	periode= document.getElementById('periode').value;
	jenis= document.getElementById('jenis').value;
	
	if (kodeorg == '') {
		notif('kodeorg','Kode Organisasi wajib diisi.'); return;
	}
	if (divisi == '') {
		notif('divisi','Divisi wajib diisi.'); return;
	}
	if (periode == '') {
		notif('periode','Periode wajib diisi.'); return;
	}
	
	param = 'method=previewdata';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&periode=' + periode;
	param += '&jenis=' + jenis;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('detail_cont').style.display = '';
					document.getElementById('detail').innerHTML=data[0];
					//loaddatadetail();
					if(jenis=='pica'){
						loaddatapica();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function simpanpica(){
	kodeorg    = document.getElementById('kodeorg').value;
	periode    = document.getElementById('periode').value;
	divisi     = document.getElementById('divisi').value;
	problem    = document.getElementById('problem').value;
	corrective = document.getElementById('corrective').value;
	outcome    = document.getElementById('outcome').value;
	milestone  = document.getElementById('milestone').value;
	deptsupport= document.getElementById('deptsupport').value;
	pic        = document.getElementById('pic').value;
	id        = document.getElementById('idpica').value;
	method     = document.getElementById('methodpica').value;
	if(problem==''){
		alert("problem wajib diisi."); return;
	}
	if(corrective==''){
		alert("corrective wajib diisi."); return;
	}
	if(outcome==''){
		alert("outcome wajib diisi."); return;
	}
	if(milestone==''){
		alert("milestone wajib diisi."); return;
	}
	if(deptsupport==''){
		alert("deptsupport wajib diisi."); return;
	}
	if(pic==''){
		alert("pic wajib diisi."); return;
	}
	
	param = '';
	param += '&id=' + id;
	param += '&method=' + method;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&divisi=' + divisi;
	param += '&problem=' + problem;
	param += '&corrective=' + corrective;
	param += '&outcome=' + outcome;
	param += '&milestone=' + milestone;
	param += '&deptsupport=' + deptsupport;
	param += '&pic=' + pic;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					batalpica();
					loaddatapica();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function batalpica(){
	document.getElementById('problem').value='';
	document.getElementById('corrective').value='';
	document.getElementById('outcome').value='';
	document.getElementById('milestone').value='';
	document.getElementById('deptsupport').value='';
	document.getElementById('pic').value='';
	document.getElementById('idpica').value='';
	document.getElementById('methodpica').value='simpanpica';
}
function loaddatapica() {
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	periode= document.getElementById('periode').value;
	jenis= document.getElementById('jenis').value;
	
	param = 'method=loaddatapica';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&periode=' + periode;
	param += '&jenis=' + jenis;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatapica').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fieldfill(id) {
	param = 'method=fieldfill';
	param += '&id=' + id;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('problem').value=data[0];
					document.getElementById('corrective').value=data[1];
					document.getElementById('outcome').value=data[2];
					document.getElementById('milestone').value=data[3];
					document.getElementById('deptsupport').value=data[4];
					document.getElementById('pic').value=data[5];
					document.getElementById('idpica').value=id;
					document.getElementById('methodpica').value='updatepica';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delpica(id) {
	param = 'method=delpica';
	param += '&id=' + id;
	if(confirm("Are you sure ???")){		
		tujuan = 'kebun_slave_csbmissue.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatapica();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPageSrc() {
	pg = document.getElementById('pagesrc');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	getnopjd(paged);
}

function detailDataPJD(notransaksi,ev,jenis) {
	width = 1024;
	height = 400;
	
	content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Preview";
	showDialog4(title, content, width, height, ev);
	
	param = 'method=previewdata' + '&notransaksi=' + notransaksi+ '&jenis=' + jenis;
	tujuan = 'sdm_slave_pjdx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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



function notif(idkolom,isipesan){
	col = idkolom.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.focus();
		kolom.style.borderColor='red';
		kolom.style.backgroundColor='#F2F94D';
		kolom.style.fontWeight='bold';
		kolom.value='';
	}
	alert(isipesan);
}
function hapuswarna(id){
	col = id.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.style.borderColor='';
		kolom.style.backgroundColor='';
		kolom.style.fontWeight='';
	}
}


function simpandetail(maxrow,maxcolom){  
	if(maxrow =='' || maxrow ==0){
        alert('Data tidak ditemukan, proses dibatalkan.');
        return;
    }
	if(confirm("Simpan semua ???")){
		simpan(1,maxrow,1,maxcolom);
	}
}


function simpan(currow,maxrow,curcol,maxcolom) {
	kodeorg= document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	periode= document.getElementById('periode').value;
	jenis= document.getElementById('jenis').value;
	
	blok   = document.getElementById('blok_'+currow).innerHTML;
	id     = document.getElementById('id_'+currow+'_'+curcol).value;
	nilai  = document.getElementById('nilai_'+currow+'_'+curcol).value;
	
	
	param = '';
	param += '&id=' + id;
	param += '&method=simpandetail';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&periode=' + periode;
	param += '&blok=' + blok;
	param += '&nilai=' + nilai;
	param += '&jenis=' + jenis;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(curcol!=undefined){
						if(document.getElementById('nilai_'+currow+'_'+curcol).style.backgroundColor!='grey'){
							if(document.getElementById('nilai_'+currow+'_'+curcol).style.backgroundColor=='cyan'){
								document.getElementById('nilai_'+currow+'_'+curcol).style.backgroundColor='';
							}else{								
								document.getElementById('nilai_'+currow+'_'+curcol).style.backgroundColor='cyan';
							}
						}
					}
					
					
					curcol+=1;
					if(curcol>maxcolom){						
						currow+=1;
						if((currow>maxrow) || (maxrow == undefined)){
							alert("Done");
						} else {
							simpan(currow,maxrow,1,maxcolom);
						}
					}else{
						simpan(currow,maxrow,curcol,maxcolom);
					}
					
					// document.getElementById('methoddetail').value='simpandetail';
					// loaddatadetail();
					// canceldetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ceknilai(idmin,idmax,idinput){
	nilaimin = document.getElementById(idmin).value;
	nilaimax = document.getElementById(idmax).value;
	nilai = document.getElementById(idinput).value;
	
	if(nilai<nilaimin || nilai>nilaimax){
		alert("Nilai tidak boleh kurang dari "+nilaimin+" dan tidak boleh lebih dari "+nilaimax+""); 
		document.getElementById(idinput).value='';
		return;
	}
}

function loaddatadetail() {
	notransaksi= document.getElementById('notransaksi').value;
	
	param = 'method=loaddatadetail';
	param += '&notransaksi=' + notransaksi;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(kodeorg,divisi,periode){
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('formpencarianheader').style.display = 'none';
	
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('divisi').value=divisi;
	document.getElementById('periode').value=periode;
	previewdata();
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);
}

function loaddata(page){
	kodeorg    =document.getElementById('kodeorgsch').value;
	divisi        =document.getElementById('divisisch').value;
	periode      =document.getElementById('periodesch').value;

	param = 'method=loaddata&page=' + page;
	param += '&divisi=' + divisi;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	
    tujuan = 'kebun_slave_csbmissue.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    //document.getElementById('footData').innerHTML = isdt[1];
					
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							// fixedColumns:   {
								// leftColumns: 4
								// //rightColumns: 2
							// },
							fixedHeader: true,
							// pake paging atau tidak
							paging: false,
							ordering: false,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [2]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							//"iDisplayLength": 50,
							// tinggi / height
							scrollX: true,
							scrollY: '50vh',
							scrollCollapse: true,
							
							//popup pencarian / filter
							// dom: 'Bfrtip',
							// buttons: [
								// {
									// extend: 'searchPanes',
									// config: {
										// cascadePanes: true
									// }
								// }
							// ]
							//end popup pencarian / filter
							
							//<!--popup pencarian / filter / like sql search-->
							language: {
								searchBuilder: {
									button: 'Filter',
								}
							}
							// buttons:[
								// 'searchBuilder','csv', 'excel', 'print'
							// ],
							//dom: 'Bfrtip',
							//select: true
							//buttons: ['colvis']
							
							//tanpa popup
							// dom: 'QBfrtip',
							// buttons:['csv', 'excel', 'print'],
							//tanpa popup
							
							//<!--popup pencarian / filter / like sql search-->
							
						});
						
						//buat nomor urut
						// table.on( 'order.dt search.dt', function () {
							// table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
								// cell.innerHTML = i+1;
							// } );
						// } ).draw();
						//buat nomor urut
					} );
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function displayList() {
	document.getElementById('formpencarianheader').style.display = 'block';
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('detail_cont').style.display = 'none';
	document.getElementById('header').style.display = 'none';
	//document.getElementById('viewlist').style.display = 'none';
	
	
	batallist();
}
function batallist(){
	// document.getElementById('notransaksisch').value='';
	//document.getElementById('kodeorgsch').value='';
	// document.getElementById('tglsch').value='';
	// document.getElementById('nopjdsch').value='';
	// document.getElementById('namasch').value='';
	// document.getElementById('sumbersch').value='';
	loaddata();
}


function deldetail(notransaksi,id){
	param = 'method=deldetail&id=' + id;
	param+= '&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_csbmissue.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function del(kodeorg,divisi,periode){
	param = 'method=del&kodeorg=' + kodeorg;
	param+= '&divisi=' + divisi;
	param+= '&periode=' + periode;
    tujuan = 'kebun_slave_csbmissue.php';
	if(confirm("Anda yakin ???")){		
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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
function preview(kodeorg,divisi,periode,tipe){
	// width    = '';
	// height   = '';
	// title    = "Preview";
	// content = "<div id=container style=\"width:100%;max-height:385px;overflow:auto;\"></div>";
    // ev = 'event';
    // showDialog6(title, content, width, height, ev); 
	
	param = 'method=preview';
	param += '&tipe=' + tipe;
	param += "&kodeorg=" + kodeorg;
	param += "&divisi=" + divisi;
	param += "&periode=" + periode;
	
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('container').innerHTML = con.responseText;
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function detailExcel(kodeorg,divisi,periode,tipe){
	param = 'method=preview' + '&tipe=' + tipe;
	param += "&kodeorg=" + kodeorg;
	param += "&divisi=" + divisi;
	param += "&periode=" + periode;
	tujuan = 'kebun_slave_csbmissue.php' + "?" + param;
	if(tipe=='pdf'){
		width = '950';
		height = '400';
	}else{		
		width = '';
		height = '';
	}
	ev = 'event';
	title = "Preview";
	if(tipe=='pdf'){
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
	}else{
		printnopopup(tujuan);
		//showDialog6(title, content, width, height, ev);
	}
}


function posting(kodeorg,divisi,periode) {
	param = "method=posting";
	param += "&kodeorg=" + kodeorg;
	param += "&divisi=" + divisi;
	param += "&periode=" + periode;
	tujuan = 'kebun_slave_csbmissue.php';
	post_response_text(tujuan, param, respog);
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
