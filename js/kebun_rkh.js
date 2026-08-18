/*
function ajukanrkh(idrkh,e){
	getSlave('ajukanrkh',e,idrkh);
}
*/


function ajukanrkh(idrkh,e){
	alert(idrkh);
	alert(e);
	// getSlave('ajukanrkh',e,idrkh);
	getSlave();
}


function form_ajukan(notransaksi, unit, numrow) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	// param = 'proses=form_ajukan' + '&notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow;
	param = 'notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow;
	// alert(param);
	post_response_text('kebun_slave_rkh.php?proses=form_ajukan',param,respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan() {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	param = 'notransaksi=' + notransaksi + '&kepada=' + kepada;
	post_response_text('kebun_slave_rkh.php?proses=ajukan',param,respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[4].innerHTML = 'Posted';
					alert('Sucses');
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}






function dataKeExcel(ev,tujuan,nomortransaksi,tanggal,divisi){
	judul='Report Ms.Excel';	
	param='nomortransaksi='+nomortransaksi+'&proses=excel';
	param+= "&divisi="+divisi;
	param+= "&tanggal="+tanggal;
	printFile(param,tujuan,judul,ev)	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev); 	
}
function dataKePDF(ev,tujuan,nomortransaksi){
	judul='RENCANA KERJA HARIAN';	
	param='notransaksi='+nomortransaksi+'&proses=view&for=excel';
	printFile(param,tujuan,judul,ev)
}

function viewdata(ev,tujuan,nomortransaksi){
	param='view&notransaksi='+nomortransaksi+'&for=viewdetail';
	getSlave(param,'',ev,viewFile);
}

function deleteall(nomortransaksi){
	param='nomortransaksi='+nomortransaksi+'';
	//alert(param);
	post_response_text('kebun_slave_rkh.php?proses=deleteall',param,respon);
	function respon() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                busy_off();
                if (!isSaveResponse(this.responseText)) {
                    alert('ERROR TRANSACTION,\n' + this.responseText);
                }
                else{
                		alert(con.responseText);
                		getSlave();
                	}
                }else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
}
function viewFile(response,ev){
	title='RENCANA KERJA HARIAN';	
	width='';
	height='400';
	content= response;
	showDialog1(title,content,width,height,ev); 	
}
function inputData(fileform){
	var nospk = document.getElementById('nospk');
	var pb = document.getElementById('pb');
	var khl = document.getElementById('khl');
	if(nospk == "0" && khl == "0"&& pb == "0"){
		alert('HK tidak Boleh Kosong');
		return false;
	}
	busy_on();
	var xhr = new XMLHttpRequest();
	xhr.onload = respon;
	xhr.open("post", 'kebun_slave_rkh.php?proses=insertdata');
	xhr.send(new FormData(fileform));
	function respon() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                busy_off();
                if (!isSaveResponse(this.responseText)) {
                    alert('ERROR TRANSACTION,\n' + this.responseText);
                }else{
					try{
						var dataArr = JSON.parse(this.responseText);
						if(dataArr.err !== 'false'){
							alert(dataArr.err);
						}
						document.getElementById('matrialbox').innerHTML="";
						document.getElementById('rotasi').value=0;
						document.getElementById('target').value=0;
						document.getElementById('nospk').value=0;
						document.getElementById('pb').value=0;
						document.getElementById('khl').value=0;
						document.getElementById('kegiatan').options[0].selected = true;
						document.getElementById('unitangkut').value=0;
						document.getElementById('mandor').options[0].selected = true;
						getSlave('listprestasi');
					}catch (e) {
						console.log(this.responseText);
					}
				}
			}else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
}

function getSlave(switchcase,ele,valuefor,funct) {
    var param = "";
	var prosees = ""
	var workwarp = document.getElementById('workwarp');
	var datadetail = document.getElementById('datadetail');
	var tanggal = document.getElementById('tanggal');
	var vr = "";
	if(typeof valuefor !== 'undefined'){
		vr = valuefor;
	}
	if(typeof switchcase !== 'undefined'){
		prosees = "?proses="+switchcase;
		if(switchcase == 'findblok' || switchcase == 'findblokinfo' || switchcase == 'findbarang' || switchcase == 'findsatuanmaterial'){
			if(typeof ele !== 'undefined'){
				param += "value="+ele.options[ele.selectedIndex].value;
				if(ele.id == "kegiatan"){
					var khususpemanen = document.getElementById('khususpemanen');
					if(ele.options[ele.selectedIndex].value == "611010101"){
						khususpemanen.style.display = "inline block";
					}else{
						khususpemanen.style.display = "none";
					}
					if(vr==""){
						var matrialbox = document.getElementById('matrialbox');
						var tr = matrialbox.getElementsByTagName('tr');
						for(i=0; i<tr.length; i++){
							tr[i].remove();
						}
					}else{
						for(i=0; i<vr.length; i++){
							param += "&kodebarang[]="+vr[i].kodebarang;
						}
					}
				}else if(ele.id == "blok"){
					janjangtbs.value = 0;
					var tbskg = document.getElementById('tbskg');
					tbskg.innerHTML = 0	;
				}else if(ele.id == "divisi"){
					var statusblok = document.getElementById('statusblok');
					statusblok.value = "";
				}
			}
		}else if(switchcase == 'findbjr'){
			if(typeof ele !== 'undefined'){
				param += "value="+ele.value;
				var blok = document.getElementById('blok');
				if(tanggal.value !== ""){
					param += "&tanggal="+tanggal.value;
				}
				if(typeof blok.options[blok.selectedIndex] !== "undefined"){
					param += "&blok="+blok.options[blok.selectedIndex].value;
				}
			}
		}else if(switchcase == 'listprestasi'){
			
			var asisten = document.getElementById('asisten');
			if(typeof asisten.options[asisten.selectedIndex] !== "undefined"){
				asisten	= asisten.options[asisten.selectedIndex].value;
			}else{
				asisten = "";
			}
			var divisi = document.getElementById('divisi');
			if(typeof divisi.options[divisi.selectedIndex] !== "undefined"){
				divisi	= divisi.options[divisi.selectedIndex].value;
			}else{
				divisi = "";
			}
			
			if(tanggal.value !=="" && asisten !=="" && divisi !==""){
				param = "tanggal="+tanggal.value+"&asisten="+asisten+"&divisi="+divisi;
				//POST 
				post_response_text('kebun_slave_rkh.php'+prosees, param, respon);
			}
		}else if(switchcase == 'showadd'){
			if(typeof ele !== "undefined" && ele !== ""){
				dataPar = ele.split(',');
				param = "nomortransaksi="+dataPar[0];
				param += "&nomorurut="+dataPar[1];
			}
		}else if(switchcase == 'ajukanrkh'){
			if(typeof valuefor !== "undefined"){
				param = "notransaksi="+valuefor;
			}
		}
	if(switchcase !== 'listprestasi'){
		//POST 
		post_response_text('kebun_slave_rkh.php'+prosees, param, respon);
	}
}else{
	var carinorhk 	= document.getElementById('carinorhk').value;
	var cariDivisi 	= document.getElementById('cariDivisi').value;
	var cariTanggal = document.getElementById('cariTanggal').value;
	param ="default=true";
	if(cariTanggal.trim() !==""){
		param += "&tanggal="+cariTanggal;
	}
	if(cariDivisi.trim() !==""){
		param += "&divisi="+cariDivisi;
	}
	if(carinorhk.trim() !==""){
		param += "&nomortransaksi="+carinorhk;
	}
	post_response_text('kebun_slave_rkh.php', param, respon);
}
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
					if(funct){
						eval(funct(con.responseText,valuefor));
					}else{
						if(typeof switchcase !== 'undefined'){
							if(switchcase == 'findblok'){
								var blockid = document.getElementById('blok');
								blockid.innerHTML = con.responseText;
								getSlave('listprestasi');
							}else if(switchcase == 'findblokinfo'){
								findblokinfo(con.responseText);
								if(vr !== ""){
									setValue(vr.prestasi[0].kodekegiatan,'kegiatan','input');
									getSlave('findbarang',document.getElementById('kegiatan'),vr.material);								
								}
							}else if(switchcase == 'findbarang'){
								onchangeKegiatan(con.responseText);
								if(vr !== ""){
									if(vr.length > 0){
										createMaterial(vr);
									}
								}
							}else if(switchcase == 'findsatuanmaterial'){
								var satuanmaterial = document.getElementById('satuanmaterial');
								satuanmaterial.innerHTML = con.responseText;
							}else if(switchcase == 'findbjr'){
								var tbskg = document.getElementById('tbskg');
								tbskg.innerHTML = con.responseText;
							}else if(switchcase == 'showadd'){
								workwarp.innerHTML = con.responseText;
							}
							else if(switchcase == 'listprestasi'){
								datadetail.innerHTML = con.responseText;
							}
							else if(switchcase == 'ajukanrkh'){
								setelahajukan(con.responseText,ele);
							}else{
								workwarp.innerHTML = con.responseText;
							}
							
						}else{
							workwarp.innerHTML = con.responseText;
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


function setelahajukan(data,e){
	var dataArr = JSON.parse(data);
	if(dataArr.err == 'false'){
		e.parentNode.innerHTML = dataArr.caption;
	}else{
		alert(dataArr.err);
	}	
}
function onchangeKegiatan(data){
	var dataArr = JSON.parse(data);
	var material = document.getElementById('material');
	var satuankegiatan = document.getElementById('satuankegiatan');
	material.innerHTML = dataArr.barang;
	satuankegiatan.innerHTML = dataArr.satuan;
	
}
function findblokinfo(data){
		datablok = JSON.parse(data);
		var nf = new Intl.NumberFormat();
		var kegiatan = document.getElementById('kegiatan');
		var namablok = document.getElementById('namablok');
		var statusblok = document.getElementById('statusblok');
		var luasareaproduktif = document.getElementById('luasareaproduktif');
		var tahuntanam = document.getElementById('tahuntanam');
		var sph = document.getElementById('sph');
		
		kegiatan.innerHTML = datablok.kegiatan;
		if(typeof datablok.blok.kodeorg !== 'undefined'){
			namablok.innerHTML = datablok.blok.kodeorg;
			luasareaproduktif.innerHTML = datablok.blok.luasareaproduktif;
			tahuntanam.innerHTML = datablok.blok.tahuntanam;
			sph.innerHTML = nf.format(parseInt(datablok.blok.jumlahpokok)/parseInt(datablok.blok.luasareaproduktif));
			statusblok.value = datablok.blok.statusblok;
		}else{
			namablok.innerHTML = "?";
			luasareaproduktif.innerHTML = "?";
			tahuntanam.innerHTML = "?";
			sph.innerHTML = "?";
		}
}		
function addMaterial(){
	var material = document.getElementById('material');
	var matrialbox = document.getElementById('matrialbox');
	var normamaterial = document.getElementById('normamaterial');//Norma
	var satuanmaterial = document.getElementById('satuanmaterial');
	var jumlahmaterial = document.getElementById('jumlahmaterial');//jml
	var _val = [];
	var name = [];
	var list = matrialbox.getElementsByTagName('tr');
	if(material.value == ""){
		return false;
	}
	if(jumlahmaterial.value == ""){
		return false;
	}
	if(normamaterial.value == ""){
		return false;
	}
	if(typeof material.options[material.selectedIndex] !== 'undefined'){
		_val.push(material.options[material.selectedIndex].value+"#"+material.options[material.selectedIndex].text);
		_val.push(normamaterial.value);	
		_val.push(jumlahmaterial.value);
		_val.push(satuanmaterial.innerText);
		name.push('material');
		name.push('normamaterial');
		name.push('jumlahmaterial');
		name.push('satuanmaterial');
		var tr = document.createElement('tr');
		tr.setAttribute('class','rowcontent');
		for(i=0; i<_val.length; i++){
			var isi = _val[i].split("#");
			input = document.createElement('input');
			input.setAttribute('type','hidden');
			input.setAttribute('name',name[i]+'[]');
			input.value = isi[0];
			td = document.createElement('td');
			if(typeof isi[1] !== 'undefined'){
				td.innerText = isi[1];
			}else{
				td.innerText = isi[0];
			}
			td.appendChild(input);
			tr.appendChild(td);
		}
		td = document.createElement('td');
		td.style ="text-align:center;";
		td.innerHTML = '<a onclick="deletelist(this);" style="width:3px;height:3px;"><img width="10" src="images/delete1.png"></a>';
		tr.appendChild(td);
		matrialbox.appendChild(tr);
		material.options[material.selectedIndex].style.display = "none";
		material.value = "";
		jumlahmaterial.value = 0;
		normamaterial.value = 0;
	}
}
function deletelist(ele){
	var tr = ele.parentNode.parentNode;
	var kodebarang = tr.getElementsByTagName('input')[0].value;
	var material = document.getElementById('material');
	for(i=0; i<material.length; i++){
		if(material.options[i].value == kodebarang){
			material.options[i].style.display = null;
		}
	}
	tr.remove();
}
function resetNorma(){
	var target = document.getElementById('target');
	var norma = document.getElementById('norma');
	var khl = document.getElementById('khl');
	var nospk = document.getElementById('nospk');
	var pb = document.getElementById('pb');
	if(target.value == ""){
		target.value = 0;
	}
	if(norma.value == ""){
		norma.value = 0;
	}
	if(khl.value == ""){
		khl.value = 0;
	}
	if(nospk.value == ""){
		nospk.value = 0;
	}
	if(pb.value == ""){
		pb.value = 0;
	}

	var normamaterial = document.getElementById('normamaterial');
	var jumlahmaterial = document.getElementById('jumlahmaterial');
	if(normamaterial.value == ""){
		normamaterial.value = 0;
	}
	if(jumlahmaterial.value == ""){
		jumlahmaterial.value = 0;
	}
	jml = (parseFloat(khl.value) + parseFloat(nospk.value) + parseFloat(pb.value))/parseFloat(target.value);
	jmlmat = parseFloat(jumlahmaterial.value)/parseFloat(target.value);
	norma.value = jml;
	normamaterial.value = jmlmat;
}
function resetNorma2(){
	var target = document.getElementById('target');
	var normamaterial = document.getElementById('normamaterial');
	var jumlahmaterial = document.getElementById('jumlahmaterial');
	jmlmat = parseFloat(normamaterial.value)/parseFloat(target.value);
	jumlahmaterial.value = jmlmat;
}

/*function setnumber(data){
	if(data.value == "0"){
		data.value = "0";
	}
}*/

function isidata(e,newData){
	var detaildatainput = document.getElementById('detaildatainput');
	var tanggal =document.getElementById('tanggal');
	var asisten = document.getElementById('asisten');
	var divisi = document.getElementById('divisi');
	var insert_rkh = document.getElementById('insert_rkh');
	if(tanggal.value !== "" && asisten.value !== "" && divisi.value !== ""){
	param = "tanggal="+tanggal.value+"&asisten="+asisten.value+"&divisi="+divisi.value;
	post_response_text('kebun_slave_rkh.php?proses=checkasisten',param, respon);	
	//alert(param);				
	}else{
	alert('Lengkapi Field diatas!');
	}	
	function respon(){
		if (this.readyState == 4) {
            if (this.status == 200) {
                busy_off();
                if (!isSaveResponse(this.responseText)) {
                    alert('ERROR TRANSACTION,\n' + this.responseText);
                }else{
                	if(con.responseText==''){
                		if(e.getAttribute('position') == "isidata"){
							detaildatainput.style.display ="block";
							e.setAttribute('position','tutupisidata');
							e.innerHTML = "Cancel";
							//insert_rkh.reset();
						}else if(e.getAttribute('position') == "tutupisidata"){
							detaildatainput.style.display= "none";
							e.setAttribute('position','isidata');
							e.innerHTML = "Tambah Data";
						}
					}
					else
					{
						alert(con.responseText);
					}
					if(typeof newData !== 'undefined'){
						exectEditdata(newData.norkh,newData.norut,newData.kontan);
					}
                }
			}else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
	
}

/*function fillfield(e,){
	var detaildatainput = document.getElementById('detaildatainput');
	var update_rkh = document.getElementById('update_rkh');

	if(con.responseText==''){
		if(e.getAttribute('position') == "isidata"){
			detaildatainput.style.display ="block";
			e.setAttribute('position','tutupisidata');
			e.innerHTML = "Cancel";
		}
		else if(e.getAttribute('position') == "tutupisidata")
		{
			detaildatainput.style.display= "none";
			e.setAttribute('position','isidata');
			e.innerHTML = "Tambah Data";
		}
					}

}*/

function setValue(data,id,type){
	switch(type){
		case 'input':
			document.getElementById(id).value = data;
		break;
	}
}
function editdataHeader(type,data){
	getSlave(type,data,'',function(res,val){
		var workwarp = document.getElementById('workwarp');
		workwarp.innerHTML = res;
		var eleDiv = document.getElementById("divisi");
		getSlave('findblok',eleDiv);
	});
}
function editdata(norkh_,norut_,kontan_){
	btnisi = document.getElementById('btn_isidata');
	var newData = {
		norkh : norkh_,
		norut : norut_,
		kontan : kontan_
	};
	if(btnisi.getAttribute('position') == "isidata"){
		isidata(btnisi,newData);
	}else{
		exectEditdata(norkh_,norut_,kontan_);
	}
}
function exectEditdata(norkh,norut,kontan){
	var btnisi = document.getElementById('btn_isidata');
	console.log(norkh,norut,kontan);
	
	if(btnisi.getAttribute('position') == "isidata"){
		btnisi.click();
	}
	param = "notransaksi="+norkh+"&nourut="+norut+"&kontan="+kontan;
	post_response_text('kebun_slave_rkh.php?proses=finddataprestasi',param, respon);
	function respon(){
		if (this.readyState == 4) {
            if (this.status == 200) {
                busy_off();
                if (!isSaveResponse(this.responseText)) {
                    alert('ERROR TRANSACTION,\n' + this.responseText);
                }else{
					try{
						var dataArr = JSON.parse(this.responseText);
						if(dataArr.err !== 'false'){
							alert(dataArr.err);
						}
						if(dataArr.data.prestasi.length > 0){
							var d = dataArr.data.prestasi[0];
							setValue(d.kodeblok,'blok','input');
							getSlave('findblokinfo',document.getElementById('blok'),dataArr.data); //return find data
							setValue(d.rotasi,'rotasi','input');
							setValue(d.hk_pb,'pb','input');
							setValue(d.hk_bor,'nospk','input');
							setValue(d.hk_khl,'khl','input');
							setValue(d.jmlh_tbs,'janjangtbs','input');
							setValue(d.mandor,'mandor','input');
							setValue(d.target,'target','input');
							setValue(d.kontan,'kontan','input');
							setValue(d.rpsatuan,'rpsatuan','input');
							setValue(d.unitangkut,'unitangkut','input');
							resetNorma();
						}
					}catch(e){
						console.log(this.responseText);
					}
				}
			}else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
}

function createMaterial(data){
	var materiallist = document.getElementById('matrialbox');
	materiallist.innerHTML = "";
	var eMat = document.getElementById('material');
	//var tr = "";
	for(x=0; x<data.length; x++){
		var _val = [];
		var name = [];
		_val.push(data[x].kodebarang+"#"+data[x].namabarang);
		_val.push(data[x].norma);	
		_val.push(data[x].jumlah);
		_val.push(data[x].satuan);
		name.push('material');
		name.push('normamaterial');
		name.push('jumlahmaterial');
		name.push('satuanmaterial');
		tr = document.createElement('tr');
		tr.id	=	"row_"+data[x].kodebarang;
		tr.setAttribute('class','rowcontent');
		for(i=0; i<_val.length; i++){
			isi = _val[i].split("#");
			input = document.createElement('input');
			input.setAttribute('type','hidden');
			input.setAttribute('name',name[i]+'[]');
			input.value = isi[0];
			td = document.createElement('td');
			if(typeof isi[1] !== 'undefined'){
				td.innerText = isi[1];
			}else{
				td.innerText = isi[0];
			}
			td.appendChild(input);
			tr.appendChild(td);
		}
		td = document.createElement('td');
		td.style ="text-align:center;";
		td.innerHTML = '<a onclick="deletelist(this);" style="width:3px;height:3px;"><img width="10" src="images/delete1.png"></a>';
		tr.appendChild(td);
		materiallist.appendChild(tr);
	}
	jumlahmaterial.value = 0;
	normamaterial.value = 0;
}
