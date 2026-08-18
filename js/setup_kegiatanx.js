function select2(){
	a = document.getElementById('noakunx2').value;
	b = document.getElementById('noakun22').value;
	
	alert("noakunx2: "+a+", noakun22: "+b);
}

function setvalselect2(){
	// $('#noakun22').val('1160303').trigger('change');
	// $('#noakunx2').val('1160303').trigger('change');
	
	setValue2('noakun22','');
}

function clearselect2(){
	$('#noakun22').val(null).trigger('change');
	$('#noakunx2').val(null).trigger('change');
}

function del(kelompok,kegiatan){

	param = 'method=delete';
	param += '&kelompok=' + kelompok;
	param += '&kodekeg=' + kegiatan;
	
	
	tujuan='setup_slave_kegiatanx.php';
	alertify.confirm("Delete","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog(){
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


function simpan(){
	kelompok= document.getElementById('kelkeg').value;
	noakun  = document.getElementById('noakun').value;
	kodekeg = document.getElementById('kodekeg').value;
	nmkegid = document.getElementById('nmkegid').value;
	nmkegen = document.getElementById('nmkegen').value;
	satuan  = document.getElementById('satuan').value;
	pilihanluas  = document.getElementById('pilihanluas').value;
	premi   = document.getElementById('premi').value;
	stat    = document.getElementById('status').value;
    oldstat = document.getElementById('oldstatus').value;
	method  = document.getElementById('method').value;
	
	validate([
        ["kelkeg","Kelompok tidak boleh kosong."],
        ["noakun","Akun tidak boleh kosong"],
        ["kodekeg","Kode kegiatan tidak boleh kosong"],
        ["nmkegid","Nama kegiatan ID tidak boleh kosong"],
        ["nmkegen","Nama kegiatan EN tidak boleh kosong"],
        ["satuan","Satuan tidak boleh kosong"],
        ["premi","Premi tidak boleh kosong"],
        ["status","Status tidak boleh kosong"]
	]);


	param  = '';
	param += '&kelompok=' + kelompok;
	param += '&noakun=' + noakun;
	param += '&kodekeg=' + kodekeg;
	param += '&nmkegid=' + nmkegid;
	param += '&nmkegen=' + nmkegen;
	param += '&satuan=' + satuan;
	param += '&premi=' + premi;
	param += '&status=' + stat;
	param += '&pilihanluas=' + pilihanluas;
	param += '&method=' + method;
	
	tujuan = 'setup_slave_kegiatanx.php';

	if(satuan!='HA' && pilihanluas!='0'){
		alertify.alert('Untuk Pilihan luas harus - , jika bukan sataun HA');
	}else{
		post_response_text(tujuan, param, respog);
	}

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

// Function XMLHTTP to request with promise
function postAsync(tujuan, param) {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", tujuan, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    resolve(xhr.responseText);
                } else {
                    reject(xhr.status);
                }
            }
        };
        xhr.send(param);
    });
}
async function getnoakunAsync(kelompok, valakun = '') {
    let param = `&kelompok=${kelompok}&valakun=${valakun}&method=getnoakun`;
    try {
        let response = await postAsync('setup_slave_kegiatanx.php', param);
        return response; // Mengembalikan string <option>...</option>
    } catch (error) {
        console.error("Gagal mengambil data noakun", error);
        return "";
    }
}
async function getkegiatanAsync(noakun, valkeg, mode) {
    let param = `&noakun=${noakun}&valkeg=${valkeg}&mode=${mode}&method=getkegiatan`;
    try {
        let response = await postAsync('setup_slave_kegiatanx.php', param);
        return response; // Mengembalikan string kodekegiatan
    } catch (error) {
        console.error("Gagal mengambil data kegiatan", error);
        return valkeg; // Fallback ke nilai awal jika error
    }
}
async function editdata(jenis, noakun, kodekegiatan, namakegiatan, nmkegen, kelompok, satuan, pilihanluas, premi, stat) {
    busy_on();
    
    let param = `&jenis=${jenis}&kelompok=${kelompok}&noakun=${noakun}&kodekeg=${kodekegiatan}&nmkegid=${namakegiatan}&nmkegen=${nmkegen}&satuan=${satuan}&pilihanluas=${pilihanluas}&premi=${premi}&status=${stat}&mode=update&method=addnew`;
    
    try {
        let htmlResponse = await postAsync('setup_slave_kegiatanx.php', param);
        
        busy_off();
        
        if (!isSaveResponse(htmlResponse)) {
            alertify.alert(htmlResponse);
            return;
        }

        alertify.popup().destroy();
        alertify.popup(jenis, "<center>" + htmlResponse + "</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
        
        $(document).ready(function() {
            $('.select2').select2({ dropdownAutoWidth: false });
            $('.select2-selection--single').height(30).css({ cursor: "auto" });
            $('.select2-selection__arrow b').css({ top: "70%" });
            $('.select2-selection__rendered').css({ 'line-height': '31px' });
        });
        
        setValue2('kelkeg', kelompok);

        let noakunHtml = await getnoakunAsync(kelompok, noakun);
        document.getElementById('noakun').innerHTML = noakunHtml;
        
        $('#noakun').val(noakun).trigger('change.select2');
        
        document.getElementById('noakun').disabled = true;
        document.getElementById('kelkeg').disabled = true;


        let finalKodekeg = kodekegiatan;
        if (kodekegiatan === '' || kodekegiatan === undefined) {
            finalKodekeg = await getkegiatanAsync(noakun, '', 'update');
        }
        setTimeout(function() {
            setValue2('kodekeg', finalKodekeg);
        }, 300);
        
        setValue2('kodekeg', finalKodekeg);
        setValue2('nmkegid', namakegiatan);
        setValue2('nmkegen', nmkegen);
        setValue2('satuan', satuan);
        setValue2('premi', premi);
        setValue2('pilihanluas', pilihanluas);
        setValue2('status', stat);
        setValue2('method', 'update');
        
        document.getElementById('oldstatus').value = stat;

    } catch (error) {
        busy_off();
        console.error("Error pada editdata:", error);
        alertify.alert("Terjadi kesalahan saat memuat data. Silakan coba lagi.");
    }
}

function getnoakun(kelompok, valakun){
	param  = '';
	param += '&kelompok=' + kelompok;
	param += '&method=getnoakun';
	
	tujuan = 'setup_slave_kegiatanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('noakun').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getkegiatan(noakun,valkeg){
	jenis  = document.getElementById('method').value;
	param  = '';
	param += '&noakun=' + noakun;
	param += '&valkeg=' + valkeg;
	param += '&mode=' + jenis;
	param += '&method=getkegiatan';
	
	tujuan = 'setup_slave_kegiatanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(valkeg!=''){
						document.getElementById('kodekeg').value = valkeg;
					}else{						
						document.getElementById('kodekeg').value = con.responseText;
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
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'setup_slave_kegiatanx.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
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
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['searchBuilder','csv', 'excel', 'print',{
									text: 'New',
									action: function () {
										newdata('new');
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
	
	tujuan = 'setup_slave_kegiatanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showNorma(kodeorg,kodekegiatan,kelompok,namakegiatan,satuan) {
	param  = '';
	param += '&kodeorg=' + kodeorg;	
	param += '&kodekegiatan=' + kodekegiatan;
	param += '&kelompok=' + kelompok;
	param += '&namakegiatan=' + namakegiatan;
	param += '&satuan=' + satuan;
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','90%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					
					// showDialog1('Material dan Premi',con.responseText,'800','',event);
                    // var dialog = document.getElementById('dynamic1');
                    // dialog.style.top = '10%';
                    // dialog.style.left = '15%';
					
					loadpr(kodekegiatan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan.php', param, respon);
}

function loadpr(kegpr){
	kegpr=document.getElementById('kodekegiatan_norma').value;
    param='kegpr='+kegpr;
    param+='&method=loadpr';
    tujuan='setup_slave_kegiatan_pr.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                }
                else {
					document.getElementById('containerpr').innerHTML=con.responseText;	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
        }	
    } 	
}	
	

function simpanpr(){
	kegpr=document.getElementById('kodekegiatan_norma').value;
	unitpr=document.getElementById('unitpr').value;
	basispr=document.getElementById('basispr').value;
	premilbpr=document.getElementById('premilbpr').value;
	param='kegpr='+kegpr+'&unitpr='+unitpr+'&basispr='+basispr+'&premilbpr='+premilbpr;
    param+="&method=savepr";
    tujuan='setup_slave_kegiatan_pr.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loadpr();
					document.getElementById('unitpr').value='';
					document.getElementById('basispr').value='';
					document.getElementById('premilbpr').value='';
				   
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}


	
function delpr(unitpr,kegpr)
{
    param='method=delpr'+'&unitpr='+unitpr+'&kegpr='+kegpr;
    tujuan='setup_slave_kegiatan_pr.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    loadpr(kegpr);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
	
}	
	

/* Function getInv
 * Override dari zSearch.js
 * Fungsi untuk pop up window, untuk memilih barang
 * I : id target
 * O : Pop up window untuk pencarian barang
 */
function getInv(event,num) {
    var cont = "<fieldset><legend><b>Search</b></legend>";
    cont += "<input class=myinputtext id='invSearch' type='text' onkeypress=\"if(getKey(event)==13){searchInv('invSearch','"+num+"');} else {return tanpa_kutip(event)}\" />";
    cont += "<button class=mybutton onclick=\"searchInv('invSearch','"+num+"')\" style='cursor:pointer'>Find</button>";
    cont += "</fieldset>";
    
    cont += "<fieldset><legend><b>Result</b></legend><div id='sResult' style='max-height:315px;overflow:auto'>";
    cont += "</div></fieldset><input id='currNum' type='hidden' value='"+num+"' />";
	
	alertify.popup2("Search",cont).set({'resizable':true,'maximizable':true}).resizeTo('300px','70%');
    //showDialog2('Search Inventory',cont,'','',event);
}

/* Function searchInv
 * Override dari zSearch.js
 * Fungsi untuk mencari barang
 * I : id search text, id target
 * O : Tampilkan hasil pencarian
 */

function ambilkegiatan(){
    noakun=document.getElementById('noakun');
    noakun=noakun.options[noakun.selectedIndex].value;
    param="ngapain=ambilkegiatan";
    param+="&noakun="+noakun;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById('kodekegiatan');
                    res.value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
    post_response_text('setup_slave_kegiatan_kegiatan.php', param, respon);
}

function searchInv(id,targetId) {
    var sText = document.getElementById(id);
    
    if(sText.value=='' || sText.value.length<3) {
        alert('Min 3 Char');
        exit;
    }
    
    var param = "keyword="+sText.value;
    param += "&target="+targetId;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById('sResult');
                    res.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan_barang.php', param, respon);
}

/* Function passInvValue
 * Fungsi untuk mengirim nilai ke element tertentu
 * I : nilai, id target
 * O : nilai terupdate
 */
function passValue(kode,nama,satuan) {
    var num = document.getElementById('currNum').value;
    var tKode = document.getElementById('kodebarang_'+num);
    var tNama = document.getElementById('namabarang_'+num);
    //var tSatuan = document.getElementById('uom1_'+num);
    
    tKode.value = kode;
    tNama.value = nama;
    //tSatuan.innerHTML = satuan;
    //closeDialog2();
	alertify.popup2().destroy();
}

/* Function addNewRow
 * Fungsi untuk menambah row baru ke dalam table
 * I : id dari tbody tabel
 * P : Persiapan row dalam bentuk HTML
 * O : Tambahan row pada akhir tabel (append)
 */
function addNewRow(body,primary,field) {
    var tabBody = document.getElementById(body);
    
    // Search Available numRow
    var numRow = 0;
    while(document.getElementById('detail_tr_'+numRow)) {
	numRow++;
    }
    
    // Add New Row
    var newRow = document.createElement("tr");
    tabBody.appendChild(newRow);
    newRow.setAttribute("id","detail_tr_"+numRow);
    newRow.setAttribute("class","rowcontent");
    
    var param = "proses=addRow";
    param += "&numRow="+numRow;
    param += "&primary="+primary;
    param += "&field="+field;
    
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    newRow.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan_norma.php', param, respon);
}

/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id,primary,field,theme) {
    var idField = document.getElementById('addNorma_'+id);
    var delImg = document.getElementById('deleteNorma_'+id);
    var invBtn = document.getElementById('getInvBtn_'+id);
    var primaryJs = primary.split('##');
    primaryJs.push('namabarang');
    
    if(idField) {
        idField.removeAttribute('id');
        idField.removeAttribute('name');
        idField.removeAttribute('onclick');
        idField.removeAttribute('src');
        idField.removeAttribute('title');
        
	// Set Edit Image Attr
	idField.setAttribute('style','display:none');
	//idField.setAttribute('id','editNorma_'+id);
	//idField.setAttribute('name','editNorma_'+id);
	//idField.setAttribute('onclick','editNorma(\''+id+'\',\''+primary+'\',\''+field+'\')');
    //idField.setAttribute('src','images/'+theme+'/save.png');
    //idField.removeAttribute('src');
	
	// Set Delete Image Attr
	delImg.setAttribute('class','zImgBtn');
        delImg.setAttribute('title','Hapus');
	delImg.setAttribute('name','deleteNorma_'+id);
	delImg.setAttribute('onclick','deleteNorma(\''+id+'\',\''+primary+'\',\''+field+'\')');
        delImg.setAttribute('src','images/'+theme+'/delete.png');
	
	// Disabled various field
	for(i=1;i<primaryJs.length;i++) {
	    tmp = document.getElementById(primaryJs[i]+'_'+id);
	    if(tmp) {
		tmp.setAttribute('disabled','disabled');
	    }
	}
	invBtn.setAttribute('disabled','disabled');
    } else {
        alert('DOM Definition Error');
    }
}

/* Function addNorma(id,field)
 * Fungsi untuk menambah data Detail
 * I : id row (urutan row pada table Detail), field yang berhubungan
 * P : Menambah data pada tabel Detail
 * O : Menambah baris pada tabel Detail
 */
function addNorma(id,primary,field) {
    var fieldJs = field.split('##');
    var kodeorg = document.getElementById('kodeorg_norma').value;
    var kodekegiatan = document.getElementById('kodekegiatan_norma').value;
    var kelompok = document.getElementById('kelompok_norma').value;
    
    param = "proses=add";
    param += "&kodeorg="+kodeorg;
    param += "&kodekegiatan="+kodekegiatan;
    param += "&kelompok="+kelompok;
    for(i=1;i<fieldJs.length;i++) {
        tmp = document.getElementById(fieldJs[i]+"_"+id);
        param += "&"+fieldJs[i]+"="+tmp.value;
    }
    
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
		    var theme = con.responseText;
                    switchEditAdd(id,primary,field,theme);
                    addNewRow('normaBody',primary,field);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan_norma.php', param, respon);
}

/* Function editNorma(id,primary,field)
 * Fungsi untuk mengubah data Detail
 * I : id row (urutan row pada table Detail),primary key, semua field
 * P : Mengubah data pada tabel Detail
 * O : Notifikasi data telah berubah
 */
function editNorma(id,primary,field) {
    var fieldJs = field.split('##');
    var primJs = primary.split('##');
    
    param = "proses=edit";
    param += "&primary="+primary;
    param += "&primVal=";
    for(i=1;i<primJs.length;i++) {
        tmp = document.getElementById(primJs[i]+"_norma");
        if(!tmp) {
            tmp = document.getElementById(primJs[i]+"_"+id);
        }
        param += "##"+tmp.value;
    }
    
    for(i=1;i<fieldJs.length;i++) {
        tmp = document.getElementById(fieldJs[i]+"_"+id);
        param += "&"+fieldJs[i]+"=";
        if(tmp.options) {
            param += tmp.options[tmp.options.selectedIndex].value;
        } else {
            param += tmp.value;
        }
    }
    
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
		    alert('Data Saved');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan_norma.php', param, respon);
}

/* Function deleteNorma(id,primary,field)
 * Fungsi untuk menghapus data norma
 * I : id row (urutan row pada table norma), primary field, semua field
 * P : Menghapus data pada tabel norma
 * O : Menghapus baris pada tabel norma
 */
function deleteNorma(id,primary,field) {
    var fieldJs = field.split('##');
    var primJs = primary.split('##');
    
    param = "proses=delete";
    param += "&primary="+primary;
    param += "&primVal=";
    for(i=1;i<primJs.length;i++) {
        tmp = document.getElementById(primJs[i]+"_norma");
        if(!tmp) {
            tmp = document.getElementById(primJs[i]+"_"+id);
        }
        param += "##"+tmp.value;
    }
    
    for(i=1;i<fieldJs.length;i++) {
        tmp = document.getElementById(fieldJs[i]+"_"+id);
        param += "&"+fieldJs[i]+"=";
        if(tmp.options) {
            param += tmp.options[tmp.selectedIndex].value;
        } else {
            param += tmp.value;
        }
    }
    
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
		    row = document.getElementById("detail_tr_"+id);
		    if(row) {
			row.style.display="none";
		    } else {
			alert("Row undetected");
		    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kegiatan_norma.php', param, respon);
}


function cekAkun(defAkun) {
	var kelompok = getValue('kelompok'),
		klpAkun = JSON.parse(getValue('klpAkun')),
		param="ngapain=getAkun";
    param+="&noakun="+klpAkun[getValue('kodeorg')][kelompok];
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var akun = document.getElementById('noakun'),
						res = JSON.parse(con.responseText);
					akun.options.length = 0;
                    for(i in res) {
						akun.options[akun.options.length] = new Option(res[i],i);
					}
					if(typeof defAkun != 'undefined') {
						setValue('noakun',defAkun);
					} else {
						setValue('noakun','');
						setValue('kodekegiatan','');
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
    post_response_text('setup_slave_kegiatan_kegiatan.php', param, respon);
}		