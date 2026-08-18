function showme(id,sumber){
	color = sumber.style.backgroundColor;
	
	if(id!=''){		
		iddet = id.split("##");
		for(e=0;e<iddet.length;e++){
			if(e==0){			
				dis = document.getElementById(iddet[e]).getAttribute("style");
			}
			if(document.getElementById(iddet[e])!= undefined){				
				norow = iddet[e].substr(4,9);
				if(dis=="display:none" || dis=="display: none;"){
					document.getElementById(iddet[e]).style.display="";
					document.getElementById(iddet[e]).style.backgroundColor=color;
					//document.getElementById('kdblok_'+norow).style.fontWeight="bold";
				}else{			
					document.getElementById(iddet[e]).style.display="none";
					document.getElementById(iddet[e]).style.backgroundColor="";
					//document.getElementById('kdblok_'+norow).style.fontWeight="normal";
				}
			}
		}
	}else{
		alert("Data tidak ditemukan.");
	}
}


function getinfo(path){
	width = '';
	height = '';
	content = "<div id=containerd align=center style=\"width:100%;height:100%;overflow:auto;\"><img src="+path+"></div>";
	ev = 'event';
	title = "info";
	showDialog5(title, content, width, height, ev);
}

function kembali() {
	document.getElementById('getdetail').style.display="none";
	document.getElementById('both_report').style.display="block";
}


function showdetail(id) {
	document.getElementById('getdetail').style.display="none";
	document.getElementById('printContainer').innerHTML="";
	document.getElementById('both_report').style.display="block";
}
function getdetail(id,blok,akun) {
	document.getElementById('row_'+id).style.backgroundColor="cyan";
	document.getElementById('both_report').style.display="none";
	document.getElementById('getdetail').style.display="block";
	document.getElementById('getdetail').innerHTML = "";
	
	pt    = document.getElementById('pt').value;
	kdorg = document.getElementById('kdorg').value;
	prd   = document.getElementById('prd').value;
	divisi= document.getElementById('divisi').value;
	tt    = document.getElementById('tt').value;
	ip    = document.getElementById('ip').value;
	jenis = document.getElementById('jenis').value;
	col   = document.getElementById('kolomhide');
	row   = document.getElementById('barishide');
	if(col.checked==true){
		kolomhide='1'
	}
	if(row.checked==true){
		barishide='1'
	}

	param = '';
	param += '&pt=' + pt;	
	param += '&kdorg=' + kdorg;	
	param += '&prd=' + prd;	
	param += '&divisi=' + divisi;	
	param += '&tt=' + tt;	
	param += '&ip=' + ip;	
	param += '&blok=' + blok;	
	param += '&barishide=' + barishide;	
	param += '&kolomhide=' + kolomhide;	
	param += '&akun=' + akun;	
	param += '&jenis=' + jenis;	
	param += '&proses=getdetail';	
	tujuan = 'kebun_slave_2perblockmainmature_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('getdetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}


function hiderow(awal,akhir,sumber,tipe){
	if(tipe=='det'){
		rowid="row_det_";
	}else{
		rowid="row_";
	}
	
	if(sumber=='est'){
		dis = document.getElementById(rowid+awal).getAttribute("style");
	}
	awal = parseFloat(awal);
	akhir = parseFloat(akhir);
	
	for (var i=awal;i<=akhir;i++){
		if(sumber!='est'){
			dis = document.getElementById(rowid+i).getAttribute("style");
		}
		if(dis=="display:none" || dis=="display: none;"){
			document.getElementById(rowid+i).style.display="";
		}else{			
			document.getElementById(rowid+i).style.display="none";
		}
	}
}

function hideest(id){
	namacol = document.getElementsByName(id);
	for (var r = 0; r < namacol.length; r++) {
		if(r =='0'){			
			dis = namacol[r].getAttribute("style");
		}
		if(dis=="display:none" || dis=="display: none;"){		
			namacol[r].style.display="";
		}else{			
			namacol[r].style.display="none";
		}
	}
}

function showalldetail(id,clas,idini,idhead){
	var valueEve = "hideall('"+id+"','"+clas+"',this.id,'"+idhead+"')";
		ele = document.getElementById(idini);
		ele.setAttribute('onclick',valueEve);
	
	iddet = id.split("#");
	for(e=0;e<iddet.length;e++){
		namacol = document.getElementsByName(iddet[e]);
		for (var i = 0; i < namacol.length; i++) {
			dis = namacol[i].getAttribute("style");
			if(dis=="display:none" || dis=="display: none;"){			
				namacol[i].style.display="";
			}else{
				namacol[i].style.display="none";
			}
		}
	}
}

function hideall(id,clas,idini,idhead){
	
	var valueEve = "showalldetail('"+id+"','"+clas+"',this.id,'"+idhead+"')";
		ele = document.getElementById(idini);
		ele.setAttribute('onclick',valueEve);
	head = idhead.split("#");	
	for(i=0;i<head.length;i++){
		colhide = document.getElementById('colhide').value;
		document.getElementById(head[i]).colSpan = colhide;
	}
	
	namacol = document.getElementsByName(id);
	for(i=0;i<namacol.length;i++){
		colsp = namacol[i].colSpan;
		colunhide = document.getElementById('colunhide').value;
		colhide = document.getElementById('colhide').value;
		if(colsp==colunhide){
			namacol[i].colSpan=colhide;
		}
	}

	
	idclas = clas.split("#");
	for(n=0;n < idclas.length;n++){
		namaclas = document.getElementsByClassName(idclas[n]);
		for (var r = 0; r < namaclas.length; r++) {
			namaclas[r].style.display="none";
		}
	}
}


function showhidesubv2(nmbln,thisid,idhead1){
	//ini adalah name untuk subheader
	//nmsubhead1 = document.getElementsByName('cost'+clas);
	//nmsubheadbln = document.getElementsByClassName('bln'+clas);
	if(idhead1!=''){		
		spanawal = document.getElementById(idhead1).getAttribute("colSpan");
	}
	nm = document.getElementsByName(nmbln);
	for (var i = 0; i < nm.length; i++) {
		dis = nm[i].getAttribute("style");
		
		if(dis=="display:none" || dis=="display: none;"){
			col = document.getElementById('colhide').value;
			colhead1 = document.getElementById('colhead1').value;
			colhead2 = document.getElementById('colhead2').value;
			jlhbulan = document.getElementById('jlhbulan').value;
			jlhcol = parseFloat(colhead2-1)*parseFloat(col);
			
			document.getElementById(thisid).colSpan = parseFloat(colhead2);
			if(idhead1!=''){				
				document.getElementById(idhead1).colSpan = parseFloat(spanawal)+parseFloat(jlhbulan);
			}
			nm[i].style.display="";
		}else{
			col = document.getElementById('colhide').value;
			colhead1 = document.getElementById('colhead1').value;
			colhead2 = document.getElementById('colhead2').value;
			jlhbulan = document.getElementById('jlhbulan').value;
			jlhcol = parseFloat(colhead2)*parseFloat(col);
			
			document.getElementById(thisid).colSpan = parseFloat(col);
			if(idhead1!=''){				
				document.getElementById(idhead1).colSpan = parseFloat(spanawal)-parseFloat(jlhbulan);
			}
			
			nm[i].style.display="none";
		}
	}
}

function showhidev2(clas,name,thisid){
	//ini adalah name untuk subheader
	nmsubhead1 = document.getElementsByName('cost'+clas);
	nmsubheadbln = document.getElementsByClassName('bln'+clas);
	
	nnclass = document.getElementsByClassName(clas);
	for (var i = 0; i < nnclass.length; i++) {
		dis = nnclass[i].getAttribute("style");
		if(dis=="display:none" || dis=="display: none;"){
			col = document.getElementById('colhide').value;
			colhead1 = document.getElementById('colhead1').value;
			colhead2 = document.getElementById('colhead2').value;
			jlhcol = parseFloat(col)*parseFloat(colhead1);
			
			document.getElementById(thisid).colSpan = parseFloat(jlhcol);
			for(r=0;r<nmsubhead1.length;r++){
				nmsubhead1[r].colSpan = parseFloat(col);				
			}
			for(n=0;n<nmsubheadbln.length;n++){
				nmsubheadbln[n].style.display="none";
			}
			nnclass[i].style.display="";
		}else{
			col = document.getElementById('colhide').value;
			document.getElementById(thisid).colSpan = parseFloat(col);
			akun = clas.replace("[]","");
			
			document.getElementById("sub"+akun+"ttl").colSpan = parseFloat(col);
			
			for(r=0;r<nmsubhead1.length;r++){
				nmsubhead1[r].colSpan = parseFloat(col);				
			}
			for(n=0;n<nmsubheadbln.length;n++){
				nmsubheadbln[n].style.display="none";
			}
			nnclass[i].style.display="none";
		}
	}
}


function showhide(id,head,kali){
	namacol = document.getElementsByName(id);
	for (var i = 0; i < namacol.length; i++) {
		dis = namacol[i].getAttribute("style");
		idhead = head.split("#");
		if(dis=="display:none" || dis=="display: none;"){
			col = document.getElementById('colunhide').value;
			namacol[i].style.display="";
			for(r=0;r<idhead.length;r++){
				document.getElementById(idhead[r]).colSpan = (parseFloat(col)*kali);
			}
		}else{
			col = document.getElementById('colhide').value;
			namacol[i].style.display="none";
			for(r=0;r<idhead.length;r++){
				document.getElementById(idhead[r]).colSpan = (parseFloat(col)*kali);
			}
		}
	}
}
