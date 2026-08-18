function simpanPJD(){

    karyawanid = document.getElementById('karyawanid');
    karyawanid = karyawanid.options[karyawanid.selectedIndex].value;
    kodeorg	= document.getElementById('kodeorg');
    kodeorg	= kodeorg.options[kodeorg.selectedIndex].value;
    tujuan2	= document.getElementById('tujuan2');
    tujuan2	= tujuan2.options[tujuan2.selectedIndex].value;
    unit    = document.getElementById('unit');
    unit =unit.options[unit.selectedIndex].value;
    tujuan1	= document.getElementById('tujuan1');
    tujuan1	= tujuan1.options[tujuan1.selectedIndex].value;
    tanggalperjalanan = trim(document.getElementById('tanggalperjalanan').value);
    tanggalkembali = trim(document.getElementById('tanggalkembali').value);
    notransaksi = document.getElementById('notransaksi').value;
    jenis	= document.getElementById('jenis').value;
    method	= document.getElementById('method').value;
    countper= document.getElementById('countper').value;
	
	if(tanggalperjalanan == ""){
		alert('Tanggal perjalanan Harus di isi!');
		document.getElementById('tanggalperjalanan').focus();
		return false;
	}

	if(tanggalkembali == ""){
		alert('Tanggal kembali Harus di isi!');
		document.getElementById('tanggalkembali').focus();
		return false;
	}
	
    if(tujuan2 == ""){
        alert('Tujuan harus dipilih !');
        document.getElementById('tujuan2').focus();
        return false;
    }

	//Param Array, author - Atwal 
	dateperjalanan = spliterDate(tanggalperjalanan,"-");// convert to YYYYMMDD
	datekembali = spliterDate(tanggalkembali,"-");// convert to YYYYMMDD
	param_array ='';
	var tujuan 		= document.getElementById('rutetujuanfield');
	var sectionsT	= tujuan.getElementsByTagName("table");
	for(x=0; x<sectionsT.length; x++){
		var data_sections = sectionsT[x].getElementsByTagName("input");
		var rute_numT = sectionsT[x].getAttribute("rute-num");
		var datavalue = 0;
		var data_array = "";
		for(i=0; i<data_sections.length; i++){
			var nameT = data_sections[i].getAttribute("name");
			var valT = data_sections[i].value;
			var requiredT = data_sections[i].getAttribute("required");
			var forT = data_sections[i].getAttribute("for");
			if(requiredT){
				if(valT == ""){
					alert('field (' + forT + ") Harus di Isi!");
					data_sections[i].focus();
					return false;
				}
			}
				
			if(trim(valT) != ""){
				if(nameT == "rutewaktu[]"){
					var datetime = 	valT.split("_");
					var datetujuan = spliterDate(datetime[0],"-");
					if(datetujuan < dateperjalanan || datetujuan > datekembali){
						alert("Tanggal tidak sesuai dengan tanggal dinas!");
						data_sections[i].focus();
						return false;
					}
				}
				datavalue = parseInt(datavalue) + 1;
				data_array += "&"+nameT+'='+valT;
			}
		}

		if(datavalue == data_sections.length){
			param_array += data_array;
		}else{
			delete_new_field('rutetujuan',rute_numT);
		}
	}
	
	var rencana	  = document.getElementById('rencanafield');
	var sectionsR = rencana.getElementsByTagName("table");
	for(x=0; x<sectionsR.length; x++){
		var data_sectionsR = sectionsR[x].getElementsByTagName("input");
		var rute_numR = sectionsR[x].getAttribute("rute-num");
		var datavalue = 0;
		var data_array = "";
		for(i=0; i<data_sectionsR.length; i++){
			var nameR = data_sectionsR[i].getAttribute("name");
			var valR = data_sectionsR[i].value;
			var requiredR = data_sectionsR[i].getAttribute("required");
			var forR = data_sectionsR[i].getAttribute("for");
			if(requiredR){
				if(valR == ""){
					alert('field (' + forR + ") Harus di Isi!");
					data_sectionsR[i].focus();
					return false;
				}
			}
			if(trim(valR) != ""){
				if(nameR == "rencanatanggal[]"){
					var daterencana = spliterDate(valR,"-");
					if(daterencana < dateperjalanan || daterencana > datekembali){
						alert("Tanggal tidak sesuai dengan tanggal dinas!");
						data_sectionsR[i].focus();
						return false;
					}
				}
				datavalue = parseInt(datavalue) + 1;
				data_array += "&"+nameR+'='+valR;
			}
		}
		if(datavalue == data_sectionsR.length){
			param_array += data_array;
		}else{
			delete_new_field('rencana',rute_numR);
		}
	}
	//param Array END:

    if (karyawanid == '' || kodeorg == '' || tujuan1 == '' || tanggalperjalanan=='') {

        alert(' Employee, Org.Code, Traveling date, Approval, first destination are obligatory');

    } else {

        param +='&karyawanid='+karyawanid+'&kodeorg='+kodeorg; 
        param +='&tujuan2='+tujuan2+'&tanggalkembali='+tanggalkembali+'&unit='+unit;	
        param +='&tujuan1='+tujuan1+'&tanggalperjalanan='+tanggalperjalanan;	
        param += '&method='+method+'&notransaksi='+notransaksi+'&jenis='+jenis;

        for (var i = 1; i <=countper; i++) {
	        ele=document.getElementById('persetujuan'+i);
	        persetujuan=ele.options[ele.selectedIndex].value;
	        param +='&persetujuan'+i+'='+persetujuan;  
        }
        
		param += param_array;
        if (confirm('Saving, are you sure..?')) {
                tujuan = 'sdm_slave_pjdinasnonstaff.php';
                post_response_text(tujuan, param, respog);
        }

    }

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					loadList();
                } else {
                    alert('Saved');
					document.getElementById('notransaksi').value=con.responseText;
                    // clearForm();
                    cariPJD();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function getpersetujuan() {
	kodeorg=document.getElementById('kodeorg').value;
	notransaksi=document.getElementById('notransaksi').value;
	param='method=getpersetujuan';
	param+='&kodeorg='+kodeorg+'&notransaksi='+notransaksi;
	tujuan='sdm_slave_pjdinasnonstaff.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					data = con.responseText.split('####');
					document.getElementById('karyawanid').innerHTML=data[0];
					document.getElementById('formpersetujuan').innerHTML=data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function editPJD(notran,karid)
{
    if(karid==''){

    } else {
        param='karid='+karid+'&notransaksi='+notran;
        tujuan = 'sdm_slave_getPJDinasForEdit.php';
        post_response_text(tujuan, param, respog);		
    }

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    parseDong(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function parseDong(tex)
{
	
    xml=tex.toString();
    xmlobject = (new DOMParser()).parseFromString(xml, "text/xml");	
	
	//Create Element for Edit Author Atwal
	clearRencanaDanTujuan();
	//Route Field
	var rutetujuanfield = document.getElementById('rutetujuanfield');
	var rutetujuan = document.getElementById('rutetujuan');
	var html_ = "";
	var kump_tujuan = xmlobject.getElementsByTagName('kump_tujuan');
	rutetujuan.setAttribute('rute-count',kump_tujuan.length);
	
	for(i=0; i<kump_tujuan.length; i++){
		var tujuan = kump_tujuan[i].getElementsByTagName('tujuan');
			
			for(x=0; x<tujuan.length; x++){
				var tujuan_for = tujuan[x].getAttribute("for"); 
				if(tujuan_for == 'dari'){
					var titletxt_dari = tujuan[x].getAttribute("title"); 
					var val_dari = tujuan[x].textContent;
				}else if(tujuan_for == 'tujuan'){
					var titletxt_tujuan = tujuan[x].getAttribute("title"); 
					var val_tujuan = tujuan[x].textContent;
				}else if(tujuan_for == 'waktu'){
					var titletxt_waktu = tujuan[x].getAttribute("title"); 
					var val_waktu = tujuan[x].textContent;
				}else if(tujuan_for == 'transportasi'){
					var titletxt_trans = tujuan[x].getAttribute("title"); 
					var val_trans = tujuan[x].textContent;
				}
			}
		if(i== 0){
			table_ = rutetujuanfield.getElementsByTagName('table');
			allinput = table_[0].getElementsByTagName('input');
			
			for(al=0; al<allinput.length; al++){
				//alert(allinput[al].getAttribute("name"));
				if(allinput[al].getAttribute("name") == 'rutedari[]'){
					allinput[al].value = val_dari;
				}else if(allinput[al].getAttribute("name") == 'rutetujuan[]'){
					allinput[al].value = val_tujuan;
				}else if(allinput[al].getAttribute("name") == 'rutewaktu[]'){
					allinput[al].value = val_waktu;
				}else if(allinput[al].getAttribute("name") == 'rutetrans[]'){
					allinput[al].value = val_trans;
				}
			}
		}else{
			html_ +='<hr id=rutetujuanhr_'+i+' rute-num='+i+'>';
			html_ +='<table id=rutetujuan_'+i+' border=0 rute-num='+i+' width=100%>';
			html_ += "	<tr> ";
			html_ += "		<td style=width:70px>"+titletxt_dari+"</td><td width=1>:</td>";
			html_ += "		<td><input type=text name=rutedari[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254 value="+val_dari+"></td>";
			html_ += "		<td>"+titletxt_tujuan+"</td><td width=1>:</td>";
			html_ += "		<td><input type=text name=rutetujuan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254 value="+val_tujuan+"></td>";
			html_ += "		<td style=width:70px>"+titletxt_waktu+"</td><td width=1>:</td>";
			html_ += "		<td><input type=text name=rutewaktu[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this,'%d-%m-%Y_%H:%M:00') size=20 placeholder=d-m-y h:m readonly=readonly value="+val_waktu+"></td>";
			html_ += "		<td><a href=# style=float:right; title=delete onclick=delete_new_field('rutetujuan','"+i+"');><img src=images/delete1.png style=width:10px;></a></td>";
			html_ += "	</tr>";
			html_ += "	<tr> ";
			html_ += "		<td>"+titletxt_trans+"</td><td width=1>:</td>";
			html_ += "		<td colspan=8><input type=text name=rutetrans[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=width:100%;  maxlength=254 value="+val_trans+"></td>";
			html_ += "	</tr>"; 
			html_ +='</table>';	
		}	
	}
	rutetujuan.innerHTML = html_;
	
	//Rencana Kegiatan
	var rencanafield = document.getElementById('rencanafield');
	var ruterencana = document.getElementById('rencana');
	var html_2 = "";
	var kump_rencana = xmlobject.getElementsByTagName('kump_rencana');
	ruterencana.setAttribute('rute-count',kump_rencana.length);
	
	for(i=0; i<kump_rencana.length; i++){
		var rencana = kump_rencana[i].getElementsByTagName('rencana');
			for(x=0; x<rencana.length; x++){
				var rencana_for = rencana[x].getAttribute("for"); 
				if(rencana_for == 'tanggal'){
					var titletxt_tanggal = rencana[x].getAttribute("title"); 
					var val_tanggal = rencana[x].textContent;
				}else if(rencana_for == 'kegiatan'){
					var titletxt_kegiatan = rencana[x].getAttribute("title"); 
					var val_kegiatan = rencana[x].textContent;
				}
			}
		if(i== 0){
			table_ = rencanafield.getElementsByTagName('table');
			allinput = table_[0].getElementsByTagName('input');
			
			for(al=0; al<allinput.length; al++){
				//alert(allinput[al].getAttribute("name"));
				if(allinput[al].getAttribute("name") == 'rencanatanggal[]'){
					allinput[al].value = val_tanggal;
				}else if(allinput[al].getAttribute("name") == 'rencanakegiatan[]'){
					allinput[al].value = val_kegiatan;
				}
			}
		}else{
			html_2 +='<hr id=rencanahr_'+i+' rute-num='+i+'>';
			html_2 +='<table id=rencana_'+i+' border=0 rute-num='+i+' width=100%>';
			html_2 += "	<tr> ";
			html_2 += "		<td style=width:70px>"+titletxt_tanggal+"</td><td width=1>:</td>";
			html_2 += "		<td><input type=text name=rencanatanggal[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=15 placeholder=d-m-y readonly=readonly value="+val_tanggal+"></td>";
			html_2 += "		<td colspan=3></td>";
			html_2 += "		<td><a href=# style=float:right; title=delete onclick=delete_new_field('rencana','"+i+"');><img src=images/delete1.png style=width:10px;></a></td>";
			html_2 += "	</tr>";
			html_2 += "	<tr> ";
			html_2 += "		<td>"+titletxt_kegiatan+"</td><td width=1>:</td>";
			html_2 += "		<td colspan=5><input type=text name=rencanakegiatan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=width:100%  maxlength=254 value="+val_kegiatan+"></td>";
			html_2 += "	</tr>";
			html_2 +='</table>';	
		}
	}
	ruterencana.innerHTML = html_2;
	//END: Array Element Edit
	
    karyawanid	=xmlobject.getElementsByTagName('karyawanid')[0].firstChild.nodeValue;
    karyawanid=karyawanid.replace("*","");
    kodeorg=xmlobject.getElementsByTagName('kodeorg')[0].firstChild.nodeValue;
    kodeorg=kodeorg.replace("*","");
	persetujuan1=xmlobject.getElementsByTagName('persetujuan1')[0].firstChild.nodeValue;
    persetujuan1=persetujuan1.replace("*","");	
	tujuan2=xmlobject.getElementsByTagName('tujuan2')[0].firstChild.nodeValue;
    tujuan2=tujuan2.replace("*","");
	tujuan1=xmlobject.getElementsByTagName('tujuan1')[0].firstChild.nodeValue;
    tujuan1=tujuan1.replace("*","");
	tanggalperjalanan=xmlobject.getElementsByTagName('tanggalperjalanan')[0].firstChild.nodeValue;
    tanggalperjalanan=tanggalperjalanan.replace("*","");
	tanggalkembali=xmlobject.getElementsByTagName('tanggalkembali')[0].firstChild.nodeValue;
    tanggalkembali=tanggalkembali.replace("*","");	
	notransaksi=xmlobject.getElementsByTagName('notransaksi')[0].firstChild.nodeValue;
    notransaksi=notransaksi.replace("*","");
	jenis=xmlobject.getElementsByTagName('jenis')[0].firstChild.nodeValue;
    jenis=jenis.replace("*","");	
    
    jk=document.getElementById('karyawanid');
    for(x=0;x<jk.length;x++)
    {
        if(jk.options[x].value==karyawanid)
        {
            jk.options[x].selected=true;
        }
    }
	
    jk=document.getElementById('jenis');
    for(x=0;x<jk.length;x++)
    {
        if(jk.options[x].value==jenis)
        {
            jk.options[x].selected=true;
        }
    }
    jk=document.getElementById('kodeorg');
    for(x=0;x<jk.length;x++)
    {
        if(jk.options[x].value==kodeorg)
        {
            jk.options[x].selected=true;
        }
    }

    /*if (typeof document.getElementById('persetujuan1') !== 'undefined' && document.getElementById('persetujuan1') !== null ) {
        jk=document.getElementById('persetujuan1');
        for(x=0;x<jk.length;x++)
        {
            if(jk.options[x].value==persetujuan1)
            {
                jk.options[x].selected=true;
            }
        }
    }*/				

    jk=document.getElementById('tujuan1');
    for(x=0;x<jk.length;x++)
    {
        if(jk.options[x].value==tujuan1)
        {
            jk.options[x].selected=true;
        }
    }	

    jk=document.getElementById('tujuan2');
    for(x=0;x<jk.length;x++)
    {
        if(jk.options[x].value==tujuan2)
        {
            jk.options[x].selected=true;
        }
    }	
    document.getElementById('tanggalperjalanan').value=tanggalperjalanan;
    document.getElementById('tanggalkembali').value=tanggalkembali;
	document.getElementById('method').value='update';			
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('kodeorg').disabled=true;
    getpersetujuan();
    tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
    getrincian(); 
}

function spliterDate(str,flag){	
	fulldate = str.split(flag);
	date = fulldate[2]+fulldate[1]+fulldate[0]; 
	result = date;
	return result;
}

function clearForm()
{
    kodeorg		= document.getElementById('kodeorg');
    kodeorg.options[0].selected=true;
    // persetujuan1    = document.getElementById('persetujuan1');
    // persetujuan1.options[0].selected=true;
    tujuan2		= document.getElementById('tujuan2');
    tujuan2.options[0].selected=true;
    tujuan1		= document.getElementById('tujuan1');
    tujuan1.options[0].selected=true;
    document.getElementById('tanggalperjalanan').value='';
    document.getElementById('tanggalkembali').value='';
	document.getElementById('method').value='insert';
    document.getElementById('notransaksi').value='';
	document.getElementById('jenis').value='';
    document.getElementById('kodeorg').disabled=false;
	clearRencanaDanTujuan();
	byloaddata();
}

//Create new rute tujuan 
function create_new_field(id_name,caption){
	var captiontext =  caption;
	var bothForAppend = document.getElementById(id_name);
	var last_num = parseInt(bothForAppend.getAttribute('rute-count'));
	next_	= (last_num + 1); 
	var html_ = "";
	var hr = document.createElement('hr');
	hr.setAttribute("rute-num", next_);
	hr.setAttribute("id", id_name+'hr_'+next_);
	var tbl = document.createElement('table');
	tbl.setAttribute("id", id_name+'_'+next_);
	tbl.setAttribute("border", '0');
	tbl.setAttribute("rute-num", next_);
	
	if(id_name == 'rencana'){
		html_ += "	<tr> ";
		html_ += "		<td style=width:70px>"+captiontext.tanggal+"</td><td width=1>:</td>";
		html_ += "		<td><input type=text name=rencanatanggal[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) size=15  readonly=readonly placeholder=d-m-y></td>";
		html_ += "		<td colspan=3></td>";
		html_ += "		<td><a href=# style=float:right; title="+captiontext.delete+" onclick=delete_new_field('"+id_name+"','"+next_+"');><img src=images/delete1.png style=width:10px;></a></td>";
		html_ += "	</tr>";
		html_ += "	<tr> ";
		html_ += "		<td>"+captiontext.rencanakegiatan+"</td><td width=1>:</td>";
		html_ += "		<td colspan=5><input type=text name=rencanakegiatan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=width:100%;  maxlength=254></td>";
		html_ += "	</tr>"; 
	}else{
		html_ += "	<tr> ";
		html_ += "		<td style=width:70px>"+captiontext.dari+"</td><td width=1>:</td>";
		html_ += "		<td><input type=text name=rutedari[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254></td>";
		html_ += "		<td>"+captiontext.tujuan+"</td><td width=1>:</td>";
		html_ += "		<td><input type=text name=rutetujuan[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=254></td>";
		html_ += "		<td style=width:70px>"+captiontext.waktu+"</td><td width=1>:</td>";
		html_ += "		<td><input type=text name=rutewaktu[] class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this,'%d-%m-%Y_%H:%M:00') size=20 readonly=readonly placeholder=d-m-y h:m ></td>";
		html_ += "		<td><a href=# style=float:right; title="+captiontext.delete+" onclick=delete_new_field('"+id_name+"','"+next_+"');><img src=images/delete1.png style=width:10px;></a></td>";
		html_ += "	</tr>";
		html_ += "	<tr> ";
		html_ += "		<td>"+captiontext.transportasi+"</td><td width=1>:</td>";
		html_ += "		<td colspan=8><input type=text name=rutetrans[] class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=width:100%;  maxlength=254></td>";
		html_ += "	</tr>"; 
	}
	bothForAppend.setAttribute('rute-count', next_);
	tbl.innerHTML = html_;
	tbl.style.width = "100%";

	bothForAppend.appendChild(hr);
	bothForAppend.appendChild(tbl);
}

function clearRencanaDanTujuan(){
	var rencanafield 	= document.getElementById('rencanafield');
	var rutetujuanfield = document.getElementById('rutetujuanfield');
	var rencana 		= document.getElementById('rencana');
	var rutetujuan 		= document.getElementById('rutetujuan');
	
	rencana.innerHTML = "";
	rutetujuan.innerHTML = "";
	
	var allinput = rutetujuanfield.getElementsByTagName('input');
	for(i=0; i<allinput.length; i++){
		allinput[i].value="";	
	}
	var allinput = rencanafield.getElementsByTagName('input');
	for(i=0; i<allinput.length; i++){
		allinput[i].value="";	
	}
}

function delete_new_field(id_name,num){
	removeObj(id_name+'hr_'+num);
	removeObj(id_name+'_'+num);
}

//author - Atwal 
//remove Element + child
function removeObj(id){
	Element.prototype.remove = function() {
		this.parentElement.removeChild(this);
	}
	NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
		for(var i = this.length - 1; i >= 0; i--) {
			if(this[i] && this[i].parentElement) {
				this[i].parentElement.removeChild(this[i]);
			}
		}
	}
	document.getElementById(id).remove();
}

function bysimpan(){
    notransaksi=document.getElementById('notransaksi').value;
	bykel=document.getElementById('bykel').value;	
	byrp=document.getElementById('byrp').value;	
	byket=document.getElementById('byket').value;	
	if(notransaksi==''){
        alert('Simpan header terlebih dahulu');return;
    }

    if(bykel=='' || byrp==''){
        alert('Lengkapi pengisian');return;
    }
	param='method=insert'+'&notransaksi='+notransaksi;
	param+='&bykel='+bykel+'&byrp='+byrp+'&byket='+byket;
    tujuan='sdm_slave_bypjdinas.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    byclear();							
                    byloaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function byclear(){
	document.getElementById('bykel').value='';	
	document.getElementById('byrp').value='';	
	document.getElementById('byket').value='';	
}	

function bydel(notransaksi,bykel,bydet){
    param='method=delete'+'&notransaksi='+notransaksi+'&bykel='+bykel+'&bydet='+bydet;
    tujuan='sdm_slave_bypjdinas.php';
    post_response_text(tujuan, param, respog);	
    function respog(){
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                    byloaddata();	
					loadnamakelompok();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}
 
 function byloaddata() {
	notransaksi=document.getElementById('notransaksi').value;
	param='method=loaddata';
	param+='&notransaksi='+notransaksi;
	tujuan='sdm_slave_bypjdinas.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
						
					data = JSON.parse(con.responseText);
					
					document.getElementById('bycontainer').innerHTML=data.datatable;
					document.getElementById('bykel').innerHTML=data.option;
					getpersetujuan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function cariPJD(num){
    tex=trim(document.getElementById('txtbabp').value);
    param='page='+num+'&method=loaddata';
    if(tex!=''){
    	param+='&tex='+tex;previewPJD
    }
        
    tujuan = 'sdm_slave_pjdinasnonstaff.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function delPJD(nosk,karid)
{
    param='notransaksi='+nosk+'&method=delete&karyawanid='+karid;
    tujuan='sdm_slave_pjdinasnonstaff.php';
    if(confirm('Deleting Document '+nosk+', are you sure..?'))
      post_response_text(tujuan, param, respog);	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cariPJD();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function previewPJD(nosk,jeniskar,ev)
{
    param='notransaksi='+nosk;
	param+='&jeniskar='+jeniskar;
    tujuan = 'sdm_slave_printPJD_pdf.php?'+param;	
 	//display window
	title=nosk;
	width='700';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}


function getrincian(){
    jenis=document.getElementById('jenis').value;

    if (jenis=='ST') {
        document.getElementById('detailrincian').style.display='none';
    }else{
        document.getElementById('detailrincian').style.display='block';
    }
}

function getunit()
{
    //alert('masuk');
    tujuan2=document.getElementById('tujuan2').options[document.getElementById('tujuan2').selectedIndex].value;
    param='tujuan2='+tujuan2+'&method=getunit';
    tujuan='sdm_slave_pjdinasnonstaff.php';
     function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    var res = con.responseText.split("##");
                    document.getElementById('unit').innerHTML = res[0];
                    //document.getElementById('afdeling').innerHTML = res[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}


