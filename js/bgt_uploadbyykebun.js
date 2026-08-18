function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}


function add_new_dataUpload(){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	
	setValue2('periode',null);
	setValue2('kodeorg',null);
	setValue2('tipekary',null);
	document.getElementById('upload').value='';
	document.getElementById('contdetail').innerHTML='';
}

function displayListUpload() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	loaddataupload(0);
}

function formupload(){
	periode = document.getElementById('periode').value;
	kodeorg = document.getElementById('kodeorg').value;
	tipekary= document.getElementById('tipekary').value;
	
	param  = 'method=formupload';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&tipekary=' + tipekary;
	tujuan = 'bgt_slave_uploadbyykebun.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function fileSelected(jenis){
	// kodeorg = document.getElementById('kodeorg').value;
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("jenis", jenis);
	// formdata.append("kodeorg", kodeorg);
	
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "bgt_slave_uploadbyykebun.php?method=fileSelected", true);
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
						document.getElementById('contdetail').innerHTML="";
						alertify.alert("Done");
					}else{						
						document.getElementById('contdetail').innerHTML=con.responseText;
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

function simpan(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alertify.alert('Info','Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	alertify.confirm("Warning","Proses ini akan me-replace data yg sudah ada, anda yakin ?",
		function(){
			savedetail(1, maxRow);
		},
		function(){
			return;
		}
	);
}
function savedetail(currRow, maxRow) {
	tahun      = document.getElementById('tahun_' + currRow).innerHTML;
	kodeorg    = document.getElementById('kodeorg_' + currRow).value;
	divisi     = document.getElementById('divisi_' + currRow).innerHTML;
	blok   	   = document.getElementById('blok_' + currRow).innerHTML;
	jenis      = document.getElementById('jenis_' + currRow).value;
	tt         = document.getElementById('tt_' + currRow).innerHTML;
	kegiatan   = document.getElementById('kodekeg_' + currRow).innerHTML;
	aruskas    = document.getElementById('aruskas_' + currRow).innerHTML;
	kdbudget   = document.getElementById('kodebudget_' + currRow).innerHTML;
	rotasi     = document.getElementById('rotasi_' + currRow).innerHTML;
	satuanv    = document.getElementById('satvol_' + currRow).innerHTML;
	totalvolume= document.getElementById('volume_' + currRow).innerHTML;
	kodebarang = document.getElementById('kodebarang_' + currRow).innerHTML;
	kodevhc    = document.getElementById('kodevhc_' + currRow).innerHTML;
	satuan     = document.getElementById('satjlh_' + currRow).innerHTML;
	jumlah     = document.getElementById('jumlah_' + currRow).innerHTML;
	rupiah     = document.getElementById('rupiah_' + currRow).innerHTML;
	
	method     = document.getElementById('method_' + currRow).value;
	
	param   = "";
	norma   = 'x';
	if(method=='simpansdm'){
		hke     = 'x';
		jhk     = jumlah;
		param += '&hke=' + hke +'&jhk=' + jhk;
	}
	
	param += '&method=' + method;
	param += '&aruskas=' + aruskas;
	param += '&satuan=' + satuan;
	param += '&norma=' + norma;
	param += '&kdbudget=' + kdbudget + '&kodebarang=' + kodebarang + '&jumlah=' + jumlah + '&rupiah=' + rupiah;
	param += '&jenis=' + jenis;
	param += '&tahun=' + tahun;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&blok=' + blok;
	param += '&tt=' + tt;
	param += '&kodevhc=' + kodevhc;
	param += '&kegiatan=' + kegiatan;
	param += '&satuanv=' + satuanv;
	param += '&volume=' + (parseFloat(totalvolume)/parseFloat(rotasi));
	param += '&rotasi=' + rotasi;
	param += '&totalvolume=' + totalvolume;
	param += '&keterangan=UPLOAD';
	
	
	
	tujuan = 'bgt_slave_byykebun.php';
	post_response_text(tujuan, param, respog);
	if (currRow != undefined) {
		document.getElementById('baris_'+currRow).style.backgroundColor='cyan';
		document.getElementById('baris_'+currRow).style.display='none';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
					if (currRow != undefined) {
						document.getElementById('validasi_' + currRow).style.backgroundColor = 'red';
					}
				} else {
					if (currRow != undefined) {
						document.getElementById('validasi_' + currRow).style.backgroundColor = '';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						alertify.alert("Done");
						location.reload();
					} else {
						savedetail(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddataupload() {
	param  = 'method=loaddataupload';
	
	
	tujuan = 'bgt_slave_uploadbyykebun.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML =  con.responseText;
					
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							ordering: false,
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 10,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
							//select: true,
							buttons: ['csv', 'excel', 'print']
						});
						
						//double click untuk freeze column
						$(table.table().container()).on('dblclick', 'td', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
										//   rightColumns: 1
									}); 
							//console.log('Row Index = ' + row.index());
						});
						
						//right click untuk freeze column
						$(table.table().container()).on('dblclick', 'th', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
									}); 
							//console.log('Row Index = ' + row.index());
						});	
					} );
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delupload(tahun,divisi){
	param  = '';
	param += '&tahun=' + tahun;
	param += '&divisi=' + divisi;
	param += '&method=delete';
	tujuan = 'bgt_slave_uploadbyykebun.php';
	alertify.confirm("Delete","Anda yakin?",
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
					loaddataupload();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
