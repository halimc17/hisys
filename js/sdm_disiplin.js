function ambilkary(){

	thnnilai=document.getElementById('thnnilai').value;
	unit=document.getElementById('unit').value;

	param = 'method=ambilkary';
    param += '&thnnilai=' + thnnilai;
    param += '&unit=' + unit;
    tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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

function kalkulasinilai1(urut,jlh){

	nilai=document.getElementById('nilai'+urut).value;
	tipenilai=document.getElementById('tipenilai'+urut).value;
	kodetipenilai=document.getElementById('kodetipenilai'+urut).value;
	bobot=document.getElementById('bobot'+urut).value;

	param = 'method=kalkulasinilai1';
    param += '&nilai=' + nilai;
    param += '&tipenilai=' + tipenilai;
    param += '&kodetipenilai=' + kodetipenilai;
    param += '&bobot=' + bobot;
    tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                		document.getElementById('hasil'+urut).value=con.responseText;
                		kalkulasitotal(jlh);
                	}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function kalkulasitotal(jlhx){
	datax='';
	for (var i = 1; i <=jlhx; i++) {
	    if(typeof document.getElementById('dataonchange'+i) !== 'undefined' && document.getElementById('dataonchange'+i) !== null){
	    	if(datax==''){
	    		datax=document.getElementById('dataonchange'+i).value;
	    	}else{
	    		datax+=','+document.getElementById('dataonchange'+i).value;
	    	}

	    }
	}

	arrdata=datax.split(',');
	datax='';
	header='';
	subheader='';
	isianx='';
	totalsc='';
	arrdata=arrdata.filter((item,
            index) => arrdata.indexOf(item) === index);
	nox=0;
	let jlht=arrdata.length;
	arrdata.forEach((element) => {
	        //console.log(element)
	        if(typeof document.getElementById('tipe'+arrdata[nox]) !== 'undefined' && document.getElementById('tipe'+arrdata[nox]) !== null){
				tipe=document.getElementById('tipe'+arrdata[nox]).value;
			}else{
				tipe='';
			}
			if(tipe!=''){
				if(tipe==0){
			        if(nox==jlht){
			    		header+=""+element+"";
			    	}else{
			    		header+=""+element+",";
			    	}
				}else if(tipe==1){
			        if(nox==jlht){
			    		subheader+=""+element+"";
			    	}else{
			    		subheader+=""+element+",";
			    	}
				}else if(tipe==2){
			        if(nox==jlht){
			    		isianx+=""+element+"";
			    	}else{
			    		isianx+=""+element+",";
			    	}
				}else if(tipe==3){
			        if(nox==jlht){
			    		totalsc+=""+element+"";
			    	}else{
			    		totalsc+=""+element+",";
			    	}
				}
			}
			nox++;
	    }
	);

	let jlh=arrdata.length-1;
	datax=isianx+totalsc+subheader+header;
	console.log(datax+' dan jumlah :'+jlh);
	kalkulasiyok(datax,jlh,0);
}

function kalkulasiyok(no,jlh,urutan){
	param = 'method=kalkulasiyok';
	arrdata=no.split(',');
	if(typeof document.getElementById('nouruttotal'+arrdata[urutan]) !== 'undefined' && document.getElementById('nouruttotal'+arrdata[urutan]) !== null){
		nouruttotal=document.getElementById('nouruttotal'+arrdata[urutan]).value;
	}else{
		nouruttotal='';
	}
	//alert('data '+arrdata[urutan]);
	if(nouruttotal!=''){
		arrdatadata=nouruttotal.split(',');

		for (var j = 0; j < arrdatadata.length; j++) {
			if(typeof document.getElementById('hasil'+arrdatadata[j]) !== 'undefined' && document.getElementById('hasil'+arrdatadata[j]) !== null){
				// if(arrdata[urutan]=='1'){
				// 	console.log('arrdata[urutan] :'+arrdata[urutan]+'arrdatadata[j] :'+arrdatadata[j]+'j :'+j+'hasil'+document.getElementById('hasil'+arrdatadata[j]).value);
				// }
				//alert('data2 '+j+ '='+ arrdatadata[j]);
				param+="&hasilarr"+j+"="+document.getElementById('hasil'+arrdatadata[j]).value;	
			}

		}
	}
	
	if(typeof document.getElementById('bobot'+arrdata[urutan]) !== 'undefined' && document.getElementById('bobot'+arrdata[urutan]) !== null){
		bobot=document.getElementById('bobot'+arrdata[urutan]).value;
	}else{
		bobot=1;
	}

	if(typeof document.getElementById('tipe'+arrdata[urutan]) !== 'undefined' && document.getElementById('tipe'+arrdata[urutan]) !== null){
		tipe=document.getElementById('tipe'+arrdata[urutan]).value;
	}else{
		tipe='';
	}

	if(typeof document.getElementById('totaloperator'+arrdata[urutan]) !== 'undefined' && document.getElementById('totaloperator'+arrdata[urutan]) !== null){
		totaloperator=document.getElementById('totaloperator'+arrdata[urutan]).value;
	}else{
		totaloperator='';
	}

	if(typeof document.getElementById('kodetotaloperator'+arrdata[urutan]) !== 'undefined' && document.getElementById('kodetotaloperator'+arrdata[urutan]) !== null){
		kodetotaloperator=document.getElementById('kodetotaloperator'+arrdata[urutan]).value;
	}else{
		kodetotaloperator='';
	}
	
	

    param += '&total=' + j;
    param += '&bobot=' + bobot;
    param += '&tipe=' + tipe;
    param += '&totaloperator=' + totaloperator;
    param += '&kodetotaloperator=' + kodetotaloperator;
    tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                		if(typeof document.getElementById('hasil'+arrdata[urutan]) !== 'undefined' && document.getElementById('hasil'+arrdata[urutan]) !== null){
                		}else{
                			alert(urutan);

                		}
                		document.getElementById('hasil'+arrdata[urutan]).value=con.responseText;
                		if(urutan!=jlh){
                			urutanbaru=urutan+1;
                			kalkulasiyok(no,jlh,urutanbaru);
                		}
                	}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formajukan(noid){
    param = 'method=formajukan';
    param += '&noid=' + noid;
    tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alertify.popup("","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('300px','230px');
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
function ajukan(){
    notransaksi     =document.getElementById('notransaksi_ajukan').value;
    jlh         =document.getElementById('jlh').value;
    var param   = 'method=ajukan';
    param       += '&notransaksi=' + notransaksi;
    param       += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
    }
    if(jlh==0){
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_disiplin.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                	alertify.popup().destroy();
                    alert('Success');
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function posting(noid){
    param = 'method=posting';
    param += '&noid=' + noid;
    tujuan = 'sdm_slave_disiplin.php';
    alertify.confirm("Posting","Anda yakin ???",
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
					alertify.popup().destroy();
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formreject(idkpi){
	content="<table>";
	content+="<tr><td>Komentar :</td></tr>";
	content+="<tr><td><textarea class=myinputtext style='width:300px;height:100px;' id=komentar></textarea></td></tr>";
	content+="<tr><td align=center><button style=color:red;border-color:red; class=mybutton title='Reject' onclick=\"rejectkpi("+idkpi+");\">Reject</button></td></tr>";
	content+="</table>";
	
	//alertify.popup("Detail",content).set({'resizable':true,'maximizable':true}).resizeTo('400px','300px');
	
    alertify.popup().set({'resizable':true,'maximizable':false,'message':content,'title':'Reject ?'}).resizeTo('400px','300px').show();
}
function reject(idkpi){
	komentar = document.getElementById('komentar').value;
	
    param = 'method=reject';
    param += '&idkpi=' + idkpi;
	param += '&komentar=' + komentar;
    tujuan = 'sdm_slave_disiplin.php';
    alertify.confirm("Reject","Anda yakin ???",
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
					alertify.popup().destroy();
                    getdetail('KPI');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function approve(idkpi){
    param = 'method=approve';
    param += '&idkpi=' + idkpi;
    tujuan = 'sdm_slave_disiplin.php';
    alertify.confirm("Approve","Anda yakin ???",
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
					alertify.popup().destroy();
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function unposting(noid){
    param = 'method=unposting';
    param += '&noid=' + noid;
    tujuan = 'sdm_slave_disiplin.php';
    alertify.confirm("Unposting","Anda yakin ???",
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
                    loaddata();
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
    tujuan = 'sdm_slave_disiplin.php';
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

function cekedit(no){
	document.getElementById('tombolsave'+no).style.backgroundColor='red';
	
	bobot       = document.getElementById('bobot'+no).value;
	porsiatasan = document.getElementById('porsiatasan'+no).value;
	porsisendiri= document.getElementById('porsisendiri'+no).value;
	
	nilaiatasan = document.getElementById('nilaiatasan'+no).value;
	nilaisendiri= document.getElementById('nilaisendiri'+no).value;
	
	proporsiatasan = parseFloat(nilaiatasan)*(parseFloat(bobot)/100)*(parseFloat(porsiatasan)/100);
	if(isNaN(proporsiatasan)){proporsiatasan=0;}
	document.getElementById('proporsiatasan'+no).value=numberFormat(proporsiatasan,2);
	
	proporsisendiri = parseFloat(nilaisendiri)*(parseFloat(bobot)/100)*(parseFloat(porsisendiri)/100);
	if(isNaN(proporsisendiri)){proporsisendiri=0;}
	document.getElementById('proporsisendiri'+no).value=numberFormat(proporsisendiri,2);
	
	
	ttlproporsi = parseFloat(proporsisendiri)+parseFloat(proporsiatasan);
	if(isNaN(ttlproporsi)){ttlproporsi=0;}
	document.getElementById('ttlproporsi'+no).value=numberFormat(ttlproporsi,2);
	
	if(nilaiatasan>120){
		alertify.alert("Nilai maksimal 120 point");
		document.getElementById('nilaiatasan'+no).value=0;
		document.getElementById('proporsiatasan'+no).value=0;
		return;
	}
	if(nilaisendiri>120){
		alertify.alert("Nilai maksimal 120 point");
		document.getElementById('nilaisendiri'+no).value=0;
		document.getElementById('proporsisendiri'+no).value=0;
		return;
	}
	
	if(porsisendiri==0){
		document.getElementById('nilaisendiri'+no).value=0;
		document.getElementById('proporsisendiri'+no).value=0;
	}
	if(porsiatasan==0){
		document.getElementById('nilaiatasan'+no).value=0;
		document.getElementById('proporsiatasan'+no).value=0;
	}
	
	totalbobot()
}
function totalbobot(){
	bbt = document.getElementsByName('bobot[]');
	
	total = 0;
	for(i=0;i<bbt.length;i++){
		total = total + parseFloat(bbt[i].value);
	}
	if(isNaN(total)){total=0;}
	document.getElementById('totaldt').value=total;
	if(total!=100){
		document.getElementById('totaldt').style.backgroundColor='red';
	}else{
		document.getElementById('totaldt').style.backgroundColor='';
	}

}
function simpandt(method,jenis,no){
	idht           = document.getElementById('idht').value;
	idkpi          = document.getElementById('idkpi'+no).value;
	kpi            = document.getElementById('kpi'+no).innerHTML;
	bobot          = document.getElementById('bobot'+no).value;
	plusminus    	= encodeURIComponent(document.getElementById('plusminus'+no).value);
	target   		= document.getElementById('target'+no).value;
	satuan    = document.getElementById('satuan'+no).value;
	realisasi = document.getElementById('realisasi'+no).value;
	skor   = document.getElementById('skor'+no).value;
	nilaiakhir= document.getElementById('nilaiakhir'+no).value;
	totaldt        = document.getElementById('totaldt').value;
	
	//alert(plusminus);

	param  = '';
	param += '&penilaian=' + '';
	param += '&plusminus=' + plusminus;
	param += '&target=' + target;
	param += '&satuan=' + satuan;
	param += '&realisasi=' + realisasi;
	param += '&skor=' + skor;
	param += '&nilaiakhir=' + nilaiakhir;
	param += '&bobot=' + bobot;
	param += '&kpi=' + kpi;
	param += '&idht=' + idht;
	param += '&idkpi=' + idkpi;
	param += '&method=' + method;
	param += '&jenis=' + jenis;
	
	validate([
        ["bobot"+no,"Bobot tidak boleh kosong"]
    ]);
	
	if(totaldt>100){
		alertify.alert("Total bobot tidak boleh lebih besar dari 100%"); return;
	}
	// if(nilaiakhir<0){
	// 	alertify.alert("Nilai Akhir tidak boleh lebih kecil dari 0"); return;
	// }
	
	tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rowdt'+no).style.setProperty('color', 'black', 'important');
					document.getElementById('tombolsave'+no).style.backgroundColor='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveaddnew(jumlah){
	unit  		 = document.getElementById('unit').value;
	tglnilai     = document.getElementById('tglnilai').value;
	thnnilai     = document.getElementById('thnnilai').value;
	karyawanid    = document.getElementById('karyawanid').value;
	method      = document.getElementById('methodaddnew').value;

	param  = '';
	for (var i = 1; i <=jumlah; i++) {
	    if(typeof document.getElementById('noidtext'+i) !== 'undefined' && document.getElementById('noidtext'+i) !== null){
	    	param += '&noidtext'+i+'=' + document.getElementById('noidtext'+i).value;
	    }
	    if(typeof document.getElementById('nilai'+i) !== 'undefined' && document.getElementById('nilai'+i) !== null){
	    	param += '&nilai'+i+'=' + document.getElementById('nilai'+i).value;
	    }
	    if(typeof document.getElementById('hasil'+i) !== 'undefined' && document.getElementById('hasil'+i) !== null){
	    	param += '&hasil'+i+'=' + document.getElementById('hasil'+i).value;
	    }
	}

	
	param += '&unit=' + unit;
	param += '&tglnilai=' + tglnilai;
	param += '&thnnilai=' + thnnilai;
	param += '&karyawanid=' + karyawanid;
	param += '&jumlah=' + jumlah;
	param += '&method=' + method;
	
	
	tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
                    alertify.alert('Done')
                    displaylist()
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tambahkpi(jenis,idkpi,idht){
	param  = '';
	param += '&idkpi=' + idkpi;
	param += '&idht=' + idht;
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_disiplin.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','50%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						// $('.select2-selection--single').height(30).css({
							// cursor: "auto"
						// });
						// $('.select2-selection__arrow b').css({
							// top: "70%"
						// });
						// $('.select2-selection__rendered').css({
							// 'line-height': '31px'
						// });
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function newdata(){
	setValue2('noid','');
	setValue2('unit','');
	setValue2('thnnilai','');
	document.getElementById('karyawanid').innerHTML='';

	document.getElementById('unit').disabled = false;
	document.getElementById('thnnilai').disabled = false;
	document.getElementById('karyawanid').disabled = false;

    document.getElementById('simpanheader').style.display='';
    document.getElementById('entry').style.display='block';
    document.getElementById('listkriteria').style.display='block';
    document.getElementById('loadpreview').style.display='none';
    reset();
}

function editdata(){

    document.getElementById('simpanheader').style.display='none';
    document.getElementById('entry').style.display='block';
    document.getElementById('listkriteria').style.display='block';
    document.getElementById('loadpreview').style.display='none';
    reset();
}

function displaylist() {
    document.getElementById('entry').style.display = 'none';
    document.getElementById('listkriteria').style.display='none';
    document.getElementById('loadpreview').style.display='block';
    reset();
    loaddata(0);
}

function getDept(nama, iddept){
    param = 'method=getDept&nama='+nama;
    tujuan = 'sdm_slave_disiplin.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("####");
					
                    setValue2(iddept, data[0]);
                    setValue2('jabatan', data[1]);
                    setValue2('lokasitugas', data[2]);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	let unit  		 = document.getElementById('unit').value;
	let tglnilai     = document.getElementById('tglnilai').value;
	let thnnilai     = document.getElementById('thnnilai').value;
	let karyawanid    = document.getElementById('karyawanid').value;
	let method       = document.getElementById('method').value;

    validate([
        ["unit","Unit tidak boleh kosong."],
        ["thnnilai","Tahun Penilaian tidak boleh kosong"],
        ["karyawanid","karyawan tidak boleh kosong"]
    ]);

    param  = 'method='+method;
    param += '&unit='+unit+'&tglnilai='+tglnilai;
    param += '&karyawanid='+karyawanid+'&thnnilai='+thnnilai;
    
    tujuan = 'sdm_slave_disiplin.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.alert('Done');
					document.getElementById('method').value='update';
                    loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatadetail() {
	let noid  		 = document.getElementById('noid').value;
	let unit  		 = document.getElementById('unit').value;
	let tglnilai     = document.getElementById('tglnilai').value;
	let thnnilai     = document.getElementById('thnnilai').value;
	let karyawanid    = document.getElementById('karyawanid').value;

    param  = 'method=loaddatadetail';
    param += '&unit='+unit+'&tglnilai='+tglnilai+'&noid='+noid;
    param += '&thnnilai='+thnnilai+'&karyawanid='+karyawanid;
    tujuan = 'sdm_slave_disiplin.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('listkriteria').style.display="block";
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function reset(){
    
    setValue2('scthn',null);
    setValue2('schunit',null);

    document.getElementById('container').innerHTML='';
    document.getElementById('method').value='insert';
}
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}
function loaddata(page) {
    let thnnilai = document.getElementById('scthn').value;
    let unit = document.getElementById('scunit').value;
    let post = document.getElementById('scpost').value;

    param = 'method=loaddata';
    param += '&post=' + post;
    param += '&unit=' + unit;
    param += '&page=' + page;
    param += '&thnnilai='+thnnilai;
    tujuan = 'sdm_slave_disiplin.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML = con.responseText;
                    // document.getElementById('loadpreview').style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(noid,tanggal,unit,tahun,karyawanid,namakaryawan) {
    editdata();
	setValue2('noid',noid);
	setValue2('unit',unit);
	setValue2('thnnilai',tahun);
	setValue2('tglnilai',tanggal);
	document.getElementById('karyawanid').innerHTML='<option value='+karyawanid+'>'+namakaryawan+'</option>';
		setValue2('method','update');
	document.getElementById('unit').disabled = true;
	document.getElementById('thnnilai').disabled = true;
	document.getElementById('tglnilai').disabled = true;
	document.getElementById('karyawanid').disabled = true;
	
	validate([
        ["unit","Unit tidak boleh kosong."],
        ["thnnilai","Tahun Penilaian tidak boleh kosong"],
        ["tglnilai","Tanggal Penilaian tidak boleh kosong"],
        ["karyawanid","karyawan tidak boleh kosong"]
    ]);
	
	loaddatadetail();
}



function deletedata(noid) {
    param = 'method=hapus';
    param += '&noid=' + noid;
    tujuan = 'sdm_slave_disiplin.php';
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

function getskore(no) {
    skor=0;
    if(document.getElementById('plusminus'+no).value=='1'){
    	skor=(document.getElementById('target'+no).value/document.getElementById('target'+no).value)-((-1*(document.getElementById('realisasi'+no).value-document.getElementById('target'+no).value))/document.getElementById('target'+no).value);
    }else if(document.getElementById('plusminus'+no).value=='2'){
    	// if(document.getElementById('target'+no).value>document.getElementById('realisasi'+no).value){
    	// 	skor=((document.getElementById('target'+no).value-document.getElementById('realisasi'+no).value)/document.getElementById('target'+no).value);
    	// }else{
    		skor=((document.getElementById('target'+no).value-document.getElementById('realisasi'+no).value)/document.getElementById('target'+no).value);
    	// /	}
    }else{
    	skor=(document.getElementById('target'+no).value/document.getElementById('target'+no).value)-Math.abs(((document.getElementById('realisasi'+no).value-document.getElementById('target'+no).value)/document.getElementById('target'+no).value));
    }
    document.getElementById('skor'+no).value=skor;
    document.getElementById('nilaiakhir'+no).value=skor*document.getElementById('bobot'+no).value;
}

function getnilaiakhir(jumlahno) {
     totalxx=0;
    for (var i = 1; i <= jumlahno; i++) {
    	 totalxx=totalxx+parseFloat(document.getElementById('nilaiakhir'+i).value);
    }
    document.getElementById('ttlnilaiakhir').value=totalxx;
}

function detail(noid){
    param = 'method=detail';
    param += '&noid=' + noid;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_disiplin.php';

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

function pdf(noid){
    param = 'method=detail';
    param += '&noid=' + noid;
    param += '&tipeprint=pdf';
    tujuan = 'sdm_slave_disiplin.php';
	tujuan = tujuan + "?" + param;
	alertify.popup("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}