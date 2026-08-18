function gettanggallapor() {
	tanggalsurat=document.getElementById('tanggalsurat').value;
	masalapor=document.getElementById('masalapor').value;

	param  = '';
	param += '&tanggalsurat=' + tanggalsurat;
	param += '&masalapor=' + masalapor;
	param += '&method=gettanggallapor';
	tujuan = 'lgl_slave_efiling.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(tanggalsurat !== '' && masalapor !== '' ){	
						document.getElementById('tanggallapor').value = trim(con.responseText);
					} else {
						document.getElementById('tanggallapor').value = '';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
	param = 'method=loaddata';
    tujuan = 'lgl_slave_efiling.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
                }else{
					document.getElementById('output').innerHTML = con.responseText;
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							ordering: true,
							fixedHeader: true,
							// pake paging atau tidak
							paging: false,
							// "pagingType": "simple_numbers",
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							// "iDisplayLength": 1,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
							//select: true,
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['searchBuilder','csv', 'excel', 'print',{
									text: 'New',
									action: function () {
										newdata('New Data');
									}
								}
							]
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
					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','80%');
					$(document).ready(function() {
						$('.select2').select2({
							// dropdownAutoWidth:true,
							// width: 'auto'
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '30px'
						});
					});
					setValue2('status','1');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(idx,nosurat) {
	param = "";
	param += "method=posting";
	param += '&idx=' + idx;
	param += '&nosurat=' + nosurat;

	tujuan = 'lgl_slave_efiling.php';

  
	if (confirm("Are You Sure Posting This Data (" + nosurat + ") ?")) {
	  post_response_text(tujuan, param, function () {
		if (con.readyState == 4) {
		  busy_off();
		  if (con.status == 200) {
			if (!isSaveResponse(con.responseText)) {
			  alert(con.responseText);
			} else {
				alertify.alert("Done");
				loaddata();
			}
		  } else {
			error_catch(con.status);
		  }
		}
	  });
	}
  }

  function postingFile(kodept, nosurat, tipesurat, namafile) {
	param = "";
	param += "method=postingFile";
	param += '&kodept=' + kodept;
	param += '&nosurat=' + nosurat;
	param += '&tipesurat=' + tipesurat;
	param += '&namafile=' + namafile;

	tujuan = 'lgl_slave_efiling.php';
	if (confirm("Are You Sure Posting This Data ?")) {
	  post_response_text(tujuan, param, function () {
		if (con.readyState == 4) {
		  busy_off();
		  if (con.status == 200) {
			if (!isSaveResponse(con.responseText)) {
			  alert(con.responseText);
			} else {
				alertify.alert("Done");
				loadfiles(nosurat,kodept,tipesurat);
			}
		  } else {
			error_catch(con.status);
		  }
		}
	  });
	}
  }



function editdata(jenis,idx,nosurat,kodept,departemen,jenissurat,tanggalsurat,dari,jabatan,untuk,keterangan,masalapor,reminder,tanggallapor,tipesurat){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','80%');
					$(document).ready(function() {
						$('.select2').select2({
							// dropdownAutoWidth:true,
							// width: 'auto'
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});

					setValue2('idx',idx);
					setValue2('nosurat',nosurat);
					setValue2('kodept',kodept);
					setValue2('departemen',departemen);
					setValue2('jenissurat',jenissurat);
					setValue2('tanggalsurat',tanggalsurat);
					setValue2('dari',dari);
					setValue2('jabatan',jabatan);
					setValue2('untuk',untuk);
					setValue2('keterangan',keterangan);
					setValue2('masalapor',masalapor);
					setValue2('reminder',reminder);
					setValue2('tanggallapor',tanggallapor);
					setValue2('tipesurat',tipesurat);
					setValue2('method','update');
					document.getElementById('nosurat').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	idx=document.getElementById('idx').value;
	kodept=document.getElementById('kodept').value;
	departemen=document.getElementById('departemen').value;
	jenissurat=document.getElementById('jenissurat').value;
	tanggalsurat=document.getElementById('tanggalsurat').value;
	dari=document.getElementById('dari').value;
	jabatan=document.getElementById('jabatan').value;
	untuk=document.getElementById('untuk').value;
	keterangan=document.getElementById('keterangan').value;
	masalapor=document.getElementById('masalapor').value;
	reminder=document.getElementById('reminder').value;
	tanggallapor=document.getElementById('tanggallapor').value;
	tipesurat=document.getElementById('tipesurat').value;
	nosurat=document.getElementById('nosurat').value;
	method=document.getElementById('method').value;
	
	
	param  = '';
	param += '&idx=' + idx;
	param += '&kodept=' + kodept;
	param += '&departemen=' + departemen;
	param += '&jenissurat=' + jenissurat;
	param += '&tanggalsurat=' + tanggalsurat;
	param += '&dari=' + dari;
	param += '&jabatan=' + jabatan;
	param += '&untuk=' + untuk;
	param += '&keterangan=' + keterangan;
	param += '&masalapor=' + masalapor;
	param += '&reminder=' + reminder;
	param += '&tanggallapor=' + tanggallapor;
	param += '&tipesurat=' + tipesurat;
	param += '&nosurat=' + nosurat;
	param += '&method=' + method;
	
	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert("Done");
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedata(idx,kodept,nosurat,tipesurat){


	param  = '';
	param += '&idx=' + idx;
	param += '&nosurat=' + nosurat;
	param += '&kodept=' + kodept;
	param += '&tipesurat=' + tipesurat;
	param += '&method=deletedata' ;

	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert("Done");
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(ev, idx,nosurat,kodept,tipesurat) {
	showformupload(ev);
	param = "";
	param += "idx=" + idx;
	param += "&nosurat=" + nosurat;
	param += '&kodept=' + kodept;
	param += '&tipesurat=' + tipesurat;

	param += '&method=showupload';
	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(nosurat,kodept,tipesurat);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(kodept, nosurat, tipesurat, namafile) {
	param = "method=deletefile";
	param += "&kodept=" + kodept;
	param += "&nosurat=" + nosurat;
	param += "&tipesurat=" + tipesurat;
	param += "&namafile=" + namafile;

	tujuan = 'lgl_slave_efiling.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(kodept,nosurat,tipesurat,namafile);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var kodept = document.getElementById('ptupload').innerHTML;
	var tipesurat = document.getElementById('xxx').innerHTML;
	var nosurat = document.getElementById('iii').innerHTML;
	var formdata = new FormData();

	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kodept", kodept);
	formdata.append("nosurat", nosurat);
	formdata.append("tipesurat", tipesurat);

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_efiling.php?method=submitfile", true);
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
					document.getElementById("upload").value = "";
					loadfiles(nosurat,kodept,tipesurat);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(nosurat,kodept,tipesurat) {
	param = 'method=loadfiles&nosurat=' + nosurat + '&kodept=' + kodept + '&tipesurat=' + tipesurat;
	tujuan = 'lgl_slave_efiling.php';
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
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_efiling.php';
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
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}