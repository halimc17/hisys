function format(repo) {
	if (repo.loading) {
		return repo.text;
	}

	var $container = $(
		"<div class='select2-result-repository clearfix'>" +
		"<div class='select2-result-repository__title'></div>" +
		"</div>"
	);

	$container.find(".select2-result-repository__title").text(repo.text);

	return $container;
}

function formatSelection(repo) {
	return repo.full_name || repo.text;
}

function newdata(){
    document.getElementById('header').style.display='block';
    document.getElementById('loadpreview').style.display='none';
    reset();
}

function displaylist() {
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display='none';
    document.getElementById('loadpreview').style.display='block';
    reset();
    loaddata(0);
}

function reset(){
    setValue2('notransaksi',null);
    setValue2('kodeorg',null);
    setValue2('jenis',null);
    document.getElementById('notransaksi').value='';
    document.getElementById('container').innerHTML='';
    document.getElementById('method').value='insert';
	
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('jenis').disabled=false;
	document.getElementById('kelompokasset').disabled=false;
	document.getElementById('subkelasset').disabled=false;
	document.getElementById('tipelokasi').disabled=false;
	document.getElementById('jumlah').disabled=false;
	document.getElementById('satuan').disabled=false;
	document.getElementById('tanggaldari').disabled=false;
	document.getElementById('tanggalsampai').disabled=false;
}
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}
function loaddata(page) {
    let notransaksi      = document.getElementById('scnotransaksi').value;
    let namaasset        = document.getElementById('scnamaasset').value;
    let ket              = document.getElementById('scket').value;
    let kodeorg          = document.getElementById('sckodeorg').value;
    let jenis            = document.getElementById('scjenis').value;
    let post             = document.getElementById('scpost').value;

    param  = 'method=loaddata';
    param += '&notransaksi=' + notransaksi;
    param += '&post=' + post;
    param += '&namaasset=' + namaasset;
    param += '&ket=' + ket;
    param += '&kodeorg=' + kodeorg;
    param += '&page=' + page;
    param += '&jenis='+jenis;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function simpan(){
	let notransaksi       = document.getElementById('notransaksi').value;
	let kodeorg           = document.getElementById('kodeorg').value;
	let tanggal           = document.getElementById('tanggal').value;
	let jenis             = document.getElementById('jenis').value;
	let id                = document.getElementById('id').value;
	
	let kodeasset         = document.getElementById('kodeasset').value;
	let namaasset         = document.getElementById('namaasset').value;
	let keterangan        = document.getElementById('keterangan').value;
	let kelompokasset     = document.getElementById('kelompokasset').value;
	let subkelasset       = document.getElementById('subkelasset').value;
	let tipelokasi        = document.getElementById('tipelokasi').value;
	let jumlah            = document.getElementById('jumlah').value;
	let satuan            = document.getElementById('satuan').value;
	let tanggaldari       = document.getElementById('tanggaldari').value;
	let tanggalsampai     = document.getElementById('tanggalsampai').value;
	
	let method            = document.getElementById('method').value;

    validate([
        ["kodeorg","Kode Organisasi tidak boleh kosong"],
        ["tanggal","Tanggal tidak boleh kosong"],
        ["jenis","Jenis tidak boleh kosong"]
    ]);
	
	if(jenis=='newasset'){
		if(kodeorg.substr(-2)=='HO' && tipelokasi==''){
			validate([
				["tipelokasi","Tipe Lokasi tidak boleh kosong"]
			]);
		}
		
		validate([
			["namaasset","Nama Asset tidak boleh kosong"],
			["keterangan","Keterangan tidak boleh kosong"],
			["kelompokasset","Kelompok Asset tidak boleh kosong"],
			["subkelasset","Sub Kelompok Asset tidak boleh kosong"],
			["jumlah","Jumlah tidak boleh kosong"],
			["satuan","Satuan tidak boleh kosong"],
			["tanggaldari","Tanggal dari tidak boleh kosong"],
			["tanggalsampai","Tanggal sampai tidak boleh kosong"]
		]);
	}
	if(jenis=='pemelasset'){
		validate([
			["kodeasset","Kode Asset tidak boleh kosong"],
			["keterangan","Keterangan tidak boleh kosong"],
			["tanggaldari","Tanggal dari tidak boleh kosong"],
			["tanggalsampai","Tanggal sampai tidak boleh kosong"]
		]);
	}
	if(jenis=='nonasset'){
		validate([
			["keterangan","Keterangan tidak boleh kosong"],
			["tanggaldari","Tanggal dari tidak boleh kosong"],
			["tanggalsampai","Tanggal sampai tidak boleh kosong"]
		]);
	}


    param  = '';
    param += '&notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    param += '&tanggal='+tanggal+'&jenis='+jenis;
    param += '&id='+id+'&method='+method;
    param += '&kodeasset='+kodeasset+'&namaasset='+namaasset;
    param += '&keterangan='+keterangan+'&kelompokasset='+kelompokasset;
    param += '&subkelasset='+subkelasset+'&tipelokasi='+tipelokasi;
    param += '&jumlah='+jumlah+'&satuan='+satuan;
    param += '&tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai;
    
	
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.set('notifier','position', 'top-center');
                    alertify.success('Done');
					
					e = con.responseText.split("##");
					document.getElementById('notransaksi').value = trim(e[0]);
                    loaddatadetail();
					
					document.getElementById('kodeorg').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('jenis').disabled=true;
					document.getElementById('kelompokasset').disabled=true;
					document.getElementById('subkelasset').disabled=true;
					document.getElementById('tipelokasi').disabled=true;
					document.getElementById('jumlah').disabled=true;
					document.getElementById('satuan').disabled=true;
					document.getElementById('tanggaldari').disabled=true;
					document.getElementById('tanggalsampai').disabled=true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpankeg(){
	let notransaksi       = document.getElementById('notransaksi').value;
	let kodeorg           = document.getElementById('kodeorg').value;
	let jenis             = document.getElementById('jenis').value;
	let idkeg             = document.getElementById('idkeg').value;
	let lokasi            = document.getElementById('lokasi').value;
	let alokasibiaya      = document.getElementById('alokasibiaya').value;
	let kegiatan          = document.getElementById('kegiatan').value;
	let keterangan        = document.getElementById('keterangandt').value;
	let tanggal           = document.getElementById('tanggaldt').value;
	let jumlah            = document.getElementById('jumlahdt').value;
	let satuan            = document.getElementById('satuandt').value;
	let method            = document.getElementById('methodkeg').value;

    validate([
        ["kegiatan"    ,"Kode Kegiatan tidak boleh kosong"],
        ["keterangandt","Keterangan tidak boleh kosong"],
        ["tanggaldt"   ,"Tanggal tidak boleh kosong"],
        ["jumlahdt"    ,"Jumlah tidak boleh kosong"],
        ["satuandt"    ,"Satuan tidak boleh kosong"]
    ]);
	
	if(jenis=='nonasset'){
		validate([
			["lokasi","Lokasi tidak boleh kosong"],
			["alokasibiaya","Alokasi Biaya dari tidak boleh kosong"]
		]);
	}


    param  = '';
    param += '&notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    param += '&tanggal='+tanggal+'&idkeg='+idkeg;
    param += '&jenis='+jenis+'&method='+method;
    param += '&lokasi='+lokasi+'&alokasibiaya='+alokasibiaya;
    param += '&kegiatan='+kegiatan+'&keterangan='+keterangan;
    param += '&jumlah='+jumlah+'&satuan='+satuan;
	
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.set('notifier','position', 'top-center');
                    alertify.success('Done');
					loaddatadetailkeg(notransaksi);
					batalkeg();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function batalkeg(){
	document.getElementById('tanggaldt').value='';
	document.getElementById('jumlahdt').value='';
	setValue2('satuandt',null);
}

function loaddatadetail() {
	let notransaksi      = document.getElementById('notransaksi').value;
	let kodeorg          = document.getElementById('kodeorg').value;
	let tanggal          = document.getElementById('tanggal').value;
	let jenis            = document.getElementById('jenis').value;
	let id               = document.getElementById('id').value;
	let kelompokasset    = document.getElementById('kelompokasset').value;
	let namaasset        = document.getElementById('namaasset').value;
	let kodeasset        = document.getElementById('kodeasset').value;
	let keterangan       = document.getElementById('keterangan').value;
	let subkelasset      = document.getElementById('subkelasset').value;
	let tipelokasi       = document.getElementById('tipelokasi').value;
	let jumlah           = document.getElementById('jumlah').value;
	let satuan           = document.getElementById('satuan').value;
	let tanggaldari      = document.getElementById('tanggaldari').value;
	let tanggalsampai    = document.getElementById('tanggalsampai').value;

    param  = 'method=loaddatadetail';
    param += '&notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    param += '&tanggal='+tanggal+'&jenis='+jenis;
    param += '&kelompokasset='+kelompokasset+'&namaasset='+namaasset;
    param += '&kodeasset='+kodeasset+'&keterangan='+keterangan;
    param += '&subkelasset='+subkelasset+'&tipelokasi='+tipelokasi;
    param += '&jumlah='+jumlah+'&satuan='+satuan;
    param += '&tanggaldari='+tanggaldari+'&tanggalsampai='+tanggalsampai;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('detail').style.display='block';
                    document.getElementById('container').innerHTML = con.responseText;
					setTimeout(function(){										
						$(document).ready(function() {
							$('#kodebarang').select2({
								ajax: {
									url: 'vhc_workorder_slave.php?method=caribarang&jenis='+jenis,
									dataType: "json",
									type: "post",
									delay: 250,
									data: function (params) {
										 return {
											search: params.term,
											page: params.page
										}
									},
									processResults: function(data, params) {
										params.page = params.page || 1;
										return {
											results: data.items,
											pagination: {
												more: data.total_count
											}
										};
									},
									cache: true
								},
								placeholder: 'Ketikan Nama atau Kode barang',
								minimumInputLength: 3,
								templateResult: format,
								templateSelection: formatSelection,
								dropdownAutoWidth: true,
								width: 'resolve'
							});
						});
					}, 1000);
					loaddatadetailkeg(notransaksi);
					getSelect2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getFormBarang() {
	val = document.getElementById('kodebarang').value;
	setValue2('kodebarangx',val);
	
	// let jenis   = document.getElementById('jenis').value;
	
    // param  = 'method=getFormBarang';
    // param += '&jenis='+jenis;
    // tujuan = 'vhc_workorder_slave.php';

    // post_response_text(tujuan, param, respon);
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alertify.alert(con.responseText);
                // } else {
					// alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
}
function loaddatadetailkeg(notransaksi) {
	//let notransaksi      = document.getElementById('notransaksi').value;
	
    param  = 'method=loaddatadetailkeg';
    param += '&notransaksi='+notransaksi;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('containerdetailkeg').innerHTML = con.responseText;
					loaddatadetailbrg(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editdetailkeg(notransaksi,nomor,lokasikerja,alokasibiaya,kodekegiatan,satuan,jumlah,tanggal,keterangan) {
	notr = document.getElementById('notransaksi').value;
	if(notr!=notransaksi){
		alertify.alert('Warning', 'Nomor transaksi tidak sesuai, edit dibatalkan.', function(){ alertify.success('Ok'); });
	}else{		
		document.getElementById('idkeg').value = nomor;
		setValue2('lokasi',lokasikerja);
		setTimeout(function(){
			setValue2('alokasibiaya',alokasibiaya);
			setTimeout(function(){
				setValue2('kegiatan',kodekegiatan);
				document.getElementById('keterangandt').value = keterangan;
				document.getElementById('tanggaldt').value = tanggal;
				document.getElementById('jumlahdt').value = jumlah;
				document.getElementById('methodkeg').value = 'updatekeg';
				setTimeout(function(){
					setValue2('satuandt',satuan);
				}, 250);
			}, 750);
		}, 750);
		
	}
}



function gettipelokasi() {
	let kodeorg   = document.getElementById('kodeorg').value;
	
    param  = 'method=gettipelokasi';
    param += '&kodeorg='+kodeorg;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					e = con.responseText.split("##");
                    document.getElementById('tipelokasi').innerHTML = e[0];
                    document.getElementById('kodeasset').innerHTML = e[2];
					if(trim(e[1])==''){
						document.getElementById('tipelokasi').disabled=false;
					}else{
						document.getElementById('tipelokasi').disabled=true;
						document.getElementById('tipelokasi').innerHTML = '';
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdetailasset() {
	let kodeasset   = document.getElementById('kodeasset').value;
	let kelompokasset   = document.getElementById('kelompokasset').value;
	let subkelasset   = document.getElementById('subkelasset').value;
	let tipelokasi   = document.getElementById('tipelokasi').value;
	
    param  = 'method=getdetailasset';
    param += '&kodeasset='+kodeasset;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					e = con.responseText.split("##");
                    document.getElementById('keterangan').value = e[0];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getsubklasset() {
	let kelompokasset   = document.getElementById('kelompokasset').value;
	
    param  = 'method=getsubklasset';
    param += '&kelompokasset='+kelompokasset;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('subkelasset').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getsatuan(jenis,value,idtujuan) {
    param  = 'method=getsatuan';
    param += '&jenis='+jenis;
    param += '&value='+value;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById(idtujuan).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkodeasset() {
	let kodeorg   = document.getElementById('kodeorg').value;
	let kelompokasset   = document.getElementById('kelompokasset').value;
	let subkelasset   = document.getElementById('subkelasset').value;
	let tipelokasi   = document.getElementById('tipelokasi').value;
	
    param  = 'method=getkodeasset';
    param += '&kodeorg='+kodeorg;
    param += '&kelompokasset='+kelompokasset;
    param += '&subkelasset='+subkelasset;
    param += '&tipelokasi='+tipelokasi;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('kodeasset').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getalokasibyy(e) {	
	lokasi = document.getElementById('lokasi').value;
    param  = 'method=getalokasibyy';
    param += '&lokasi='+lokasi;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('alokasibiaya').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkegiatan(e) {
	let kodeorg   = document.getElementById('kodeorg').value;
	let lokasi   = document.getElementById('lokasi').value;
	let alokasibiaya   = document.getElementById('alokasibiaya').value;
	
    param  = 'method=getkegiatan';
    param += '&alokasibiaya='+alokasibiaya;
    param += '&kodeorg='+kodeorg;
    param += '&lokasi='+lokasi;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('kegiatan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdetailcont(){
	let jenis = document.getElementById('jenis').value;
	
	form = document.getElementsByName('contnewasset[]');
	for(i=0; i<form.length; i++){
		if(jenis=='newasset'){
			form[i].style.display='';
		}else{
			form[i].style.display='none';
			
			setValue2('kelompokasset',null);
			setValue2('subkelasset',null);
			setValue2('tipelokasi',null);
			setValue2('kodeasset',null);
			setValue2('satuan',null);
			document.getElementById('namaasset').value = '';
			document.getElementById('jumlah').value = '';
			document.getElementById('keterangan').value = '';
			if(jenis=='nonasset'){				
				document.getElementById('namaasset').disabled = true;
			}else{				
				document.getElementById('namaasset').disabled = false;
			}
		}
	}
	if(jenis=='pemelasset'){
		document.getElementById('divkodeasset').style.display='';
		document.getElementById('namaasset').style.display='none';
	}else{
		document.getElementById('divkodeasset').style.display='none';
		document.getElementById('namaasset').style.display='';
	}
	
}

function fillField(notransaksi,kodeorg,tanggal,jenis,kelasset,subklasset,lokasi,tipelokasi,kodeasset,namaasset,satuan,jumlah,tanggalmulai,tanggalsampai,keterangan) {
    newdata();
	
	// var timesRun = 0;
	// var interval = setInterval(function(){
		// timesRun += 1;
		// if(timesRun === 60){
			// clearInterval(interval);
		// }
		// //do whatever here..
	// }, 2000); 
	
	setValue2('notransaksi',notransaksi);
	setValue2('kodeorg',kodeorg);
	setTimeout(function(){
		setValue2('jenis',jenis);
		setTimeout(function(){
			setValue2('kelompokasset',kelasset); 
			setTimeout(function(){
				setValue2('subkelasset',subklasset);
				setValue2('kodeasset',kodeasset); 
				setTimeout(function(){
					document.getElementById('keterangan').value=keterangan;
					setValue2('tanggal',tanggal);
					setValue2('tipelokasi',tipelokasi);
					setValue2('namaasset',namaasset);
					setValue2('satuan',satuan);
					setValue2('jumlah',jumlah);
					setValue2('tanggaldari',tanggalmulai);
					setValue2('tanggalsampai',tanggalsampai);
					
					setTimeout(function(){					
						setValue2('kodeasset',kodeasset);
						setTimeout(function(){					
							simpan();
							setTimeout(function(){					
								loaddatadetail();
							}, 750);
						}, 750);
					}, 750);
				}, 750);
			}, 750);
		}, 750);
	}, 750);
	// setValue2('method','update');
}

function simpanbrg(){
	let notransaksi       = document.getElementById('notransaksi').value;
	let kodeorg           = document.getElementById('kodeorg').value;
	let jenis             = document.getElementById('jenis').value;
	let kodebarang        = document.getElementById('kodebarang').value;
	let jumlahbrg         = document.getElementById('jumlahbrg').value;
	let satuanbrg         = document.getElementById('satuanbrg').value;
	let kodebarangold     = document.getElementById('kodebarangold').value;
	let method            = document.getElementById('methodbrg').value;

    validate([
        ["notransaksi"  ,"Notransaksi tidak boleh kosong"],
        ["kodeorg"      ,"Kode Organisasi tidak boleh kosong"],
        ["kodebarang"   ,"Nama Barang tidak boleh kosong"],
        ["jumlahbrg"    ,"Jumlah tidak boleh kosong"],
        ["satuanbrg"    ,"Satuan tidak boleh kosong"]
    ]);
	

    param  = '';
    param += '&notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    param += '&jenis='+jenis+'&method='+method;
    param += '&kodebarang='+kodebarang+'&jumlah='+jumlahbrg;
    param += '&satuan='+satuanbrg;
    param += '&kodebarangold='+kodebarangold;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					batalbrg();
					alertify.set('notifier','position', 'top-center');
                    alertify.success('Done');
					loaddatadetailbrg(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function batalbrg(){
	setValue2('kodebarang',null);
	setValue2('satuanbrg',null);
	document.getElementById('kodebarangold').value='';
	document.getElementById('jumlahbrg').value='';
	document.getElementById('methodbrg').value='insertbrg';
	
}

function loaddatadetailbrg(notransaksi) {
    param  = 'method=loaddatadetailbrg';
    param += '&notransaksi='+notransaksi;
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('containerdetailbrg').innerHTML = con.responseText;
					loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editdetailbrg(notransaksi,kodebarang,namabarang,satuan,jumlah) {
	notr = document.getElementById('notransaksi').value;
	if(notr!=notransaksi){
		alertify.alert('Warning', 'Nomor transaksi tidak sesuai, edit dibatalkan.', function(){ alertify.success('Ok'); });
	}else{
		document.getElementById('kodebarang').innerHTML = "<option value='"+ kodebarang +"'>"+ namabarang +"</option>";
		document.getElementById('kodebarangold').value = kodebarang;
		document.getElementById('satuanbrg').innerHTML = "<option value='"+ satuan +"'>"+ satuan +"</option>";
		document.getElementById('jumlahbrg').value = jumlah;
		document.getElementById('methodbrg').value = 'updatebrg';
	}
}




// fungsi untuk progress bar
function progressHandler(event) {
	document.getElementById("rowstatus").style.display="";
	document.getElementById("progressBar").style.display="block";
	bytesUploaded = Math.round(event.loaded/1024);
	totalupload = Math.round(event.total/1024);
	
	if (totalupload > 1024*1024){
		bytesTransfered = numberFormat(Math.round(bytesUploaded * 100/(1024*1024))/100) + ' GB';
		totupload = numberFormat(Math.round(totalupload * 100/(1024*1024))/100) + ' GB';
	}else if (totalupload > 1024){
		bytesTransfered = numberFormat(Math.round(bytesUploaded * 100/1024)/100) + ' MB';		
		totupload = numberFormat(Math.round(totalupload * 100/1024)/100) + ' MB';		
	}else{
		bytesTransfered = numberFormat(Math.round(bytesUploaded * 100)/100) + ' KB';
		totupload = numberFormat(Math.round(totalupload * 100)/100) + ' KB';
	}
	
	//document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded/1024)) + " KB of " + numberFormat(Math.round(event.total/1024))+" KB";
	document.getElementById("loaded_n_total").innerHTML = "Uploaded " + bytesTransfered + " of " + totupload;
	
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
	document.getElementById("rowstatus").style.display="none";
	document.getElementById("progressBar").style.display="none";
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
	document.getElementById("status").innerHTML = "Upload Failed";
}
function abortHandler(event) {
	document.getElementById("status").innerHTML = "Upload Aborted";
	document.getElementById("progressBar").style.display="none";
	document.getElementById("loaded_n_total").innerHTML="";
}

function submitfile(notransaksi) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alertify.alert("Upload file has been empty.");
		return false;
	}
	if(notransaksi==''){
		alertify.alert("Nomor transaksi tidak ditemukan.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').style.display="none";
	batal = document.getElementById('btnbtlupload');
	batal.addEventListener('click', () => {
		con.abort();
		document.getElementById('btnsubmit').style.display="";
	})
	
	//tambahan progress bar
	con.upload.addEventListener("progress", progressHandler, false);
	con.addEventListener("load", completeHandler, false);
	con.addEventListener("error", errorHandler, false);
	con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "vhc_workorder_slave.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('btnsubmit').style.display="";
				} else {
					//=== Success Response
					alertify.set('notifier','position', 'top-center');
					alertify.success('Uploaded Success.');
					document.getElementById('btnsubmit').style.display="";
					document.getElementById("upload").value = "";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalupload(){	
	const btn = document.getElementById('btnbtlupload');
	const controller = new AbortController();
	const signal = controller.signal;
	btn.addEventListener('click', () => {
		controller.abort();
	})
}

function loadfiles(notransaksi) {
	param  = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'vhc_workorder_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'vhc_workorder_slave.php';
	
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	).set('resizable',false).resizeTo(100,250);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewfile(idfile,sumber) {
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'vhc_workorder_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deldt(idkpi,idht){
    param = 'method=deldt';
    param += '&idkpi=' + idkpi;
    param += '&idht=' + idht;
    tujuan = 'vhc_workorder_slave.php';
    alertify.confirm("Hapus","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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

function deletedata(notransaksi) {
    param = 'method=hapus';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'vhc_workorder_slave.php';
    alertify.confirm("Informasi","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
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

function posting(notransaksi) {
    param = 'method=posting';
    param += '&notransaksi=' + notransaksi;
    tujuan = 'vhc_workorder_slave.php';
    alertify.confirm("Informasi","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
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

function detail(notransaksi){
    param = 'method=detail';
    param += '&notransaksi=' + notransaksi;
    param += '&tipeprint=html';
    tujuan = 'vhc_workorder_slave.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {                    
                    alertify.popup().set({'resizable':true,'maximizable':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function pdf(notransaksi){
    param = 'method=detail';
    param += '&notransaksi=' + notransaksi;
    param += '&tipeprint=pdf';
    tujuan = 'vhc_workorder_slave.php';
	tujuan = tujuan + "?" + param;
	alertify.popup("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}