function cektipe(){
	tipe=document.getElementById('station').value;
	if(tipe=='1'){
		document.getElementById('trkebun').style.display='';
	}else{
		document.getElementById('trkebun').style.display='none';
	}
}

function gettotalfraksi(no){
	totjang=document.getElementById('totjang'+no).value;
	fraksi1=document.getElementById('fraksi1'+no).value;
	fraksi2=document.getElementById('fraksi2'+no).value;
	fraksi3=document.getElementById('fraksi3'+no).value;
	if(totjang==''){totjang=0};
	if(fraksi1==''){fraksi1=0};
	if(fraksi2==''){fraksi2=0};
	if(fraksi3==''){fraksi3=0};
	
	totalfraksi = parseFloat(totjang)-(parseFloat(fraksi1)+parseFloat(fraksi2)+parseFloat(fraksi3));
	
	document.getElementById('fraksi4'+no).value=totalfraksi;
}

function saveht() 
{ 
	pt=document.getElementById('pt').value;
	kebun=document.getElementById('kebun').value;
	tanggal=document.getElementById('tanggal').value;
	tipe=document.getElementById('station').value;
	method=document.getElementById('method').value;
	proses='insertht'
	
	/*if(unit==''){
		alert("Unit harus dipilih");
		return;
	}
	if(station==''){
		alert("Station harus dipilih");
		return;
	}*/
	
	param='proses='+proses+'&tipe='+tipe+'&unit='+pt+'&tanggal='+tanggal+'&kebun='+kebun+'&method='+method;
	tujuan='sortasi_slave_harian_tbs.php';
	post_response_text(tujuan, param, respog);

	
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					showdt(con.responseText);
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function savedt(count) 
{
	loopsavedt(count,1);
}

function loopsavedt(count,current) 
{
	pt=document.getElementById('pt').value;
	tanggal=document.getElementById('tanggal').value;
	tipe=document.getElementById('station').value;
	kebun=document.getElementById('kebun').value;
	iddivisi=document.getElementById('iddivisi'+current).value;
	jamsample=document.getElementById('jamsample'+current).value;
	menitsample=document.getElementById('menitsample'+current).value;
	tatam=document.getElementById('tatam'+current).value;
	totjang=document.getElementById('totjang'+current).value;
	tonase=document.getElementById('tonase'+current).value;
	fraksi1=document.getElementById('fraksi1'+current).value;
	fraksi2=document.getElementById('fraksi2'+current).value;
	fraksi3=document.getElementById('fraksi3'+current).value;
	fraksi4=document.getElementById('fraksi4'+current).value;
	abnormal=document.getElementById('abnormal'+current).value;
	emptybunch=document.getElementById('emptybunch'+current).value;
	dura=document.getElementById('dura'+current).value;
	comidel=document.getElementById('comidel'+current).value;
	tangjang=document.getElementById('tangjang'+current).value;
	tot=document.getElementById('tot'+current).value;
	brondol=document.getElementById('brondol'+current).value;
	oer=document.getElementById('oer'+current).value;
	curah=document.getElementById('curah'+current).value;
	
	param='proses=savedt'+'&current='+current+'&iddivisi='+iddivisi+'&jamsample='+jamsample+'&menitsample='+menitsample+'&tatam='+tatam+'&totjang='+totjang+'&tonase='+tonase+'&fraksi1='+fraksi1+'&fraksi2='+fraksi2+'&fraksi3='+fraksi3+'&fraksi4='+fraksi4+'&abnormal='+abnormal+'&emptybunch='+emptybunch+'&dura='+dura+'&tangjang='+tangjang+'&comidel='+comidel+'&tot='+tot+'&brondol='+brondol+'&oer='+oer+'&curah='+curah+'&pt='+pt+'&tanggal='+tanggal+'&tipe='+tipe+'&kebun='+kebun;
	tujuan='sortasi_slave_harian_tbs.php';
	post_response_text(tujuan, param, respog);
		
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
					document.getElementById("tr_"+current).style.backgroundColor = "red";
				}
				else 
				{
					if(count==current){
						document.getElementById("tr_"+current).style.backgroundColor = "#D8FFD4";
						//document.getElementById("keterangan_"+current).focus();
						alert("Success");
						showalllist();
					}
					else{
						document.getElementById("tr_"+current).style.backgroundColor = "#D8FFD4";
						//document.getElementById("keterangan_"+current).focus();
						current=parseFloat(current)+1;
						loopsavedt(count,current);
					}
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function showdt(content){
	document.getElementById('simpanht').style.display = 'none';
	document.getElementById('cancelht').style.display = 'none';
	document.getElementById('tanggal').disabled = true;
	document.getElementById('station').disabled = true;
	document.getElementById('kebun').disabled = true;
	document.getElementById('formdt').style.display = '';
	document.getElementById('formdt').innerHTML = content;
}

function canceldt(){
	document.getElementById('simpanht').style.display = '';
	document.getElementById('cancelht').style.display = '';
	document.getElementById('tanggal').disabled = false;
	document.getElementById('station').disabled = false;
	document.getElementById('kebun').disabled = false;
	document.getElementById('method').value = "insert";
	document.getElementById('formdt').style.display = 'none';
	document.getElementById('formdt').innerHTML = '';
	showontop();
}

function deleteall(id)
{
	param='proses=deleteall&id='+id;
	tujuan='sortasi_slave_harian_tbs.php';
	if(confirm("Are you sure delete this item?"))
	{
		post_response_text(tujuan, param, respog);
	}
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					getPage();
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function postingsortasi(id)
{
	param='proses=postingsortasi&id='+id;
	tujuan='sortasi_slave_harian_tbs.php';
	if(confirm("Are you sure posted this item?"))
	{
		post_response_text(tujuan, param, respog);
	}
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					getPage();
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function cariData(pg){
	loadData(pg);
}

function loadData(page){
	caritanggal=document.getElementById('caritanggal').value;
	
    param='proses=loadData'+'&page='+page;
	
	if(caritanggal!=''){
		param+='&caritanggal='+caritanggal;
	}
    
	tujuan='sortasi_slave_harian_tbs.php';
    post_response_text(tujuan, param, respog);
    function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
                }
                else 
				{
					document.getElementById('formInput').style.display='none';
                    document.getElementById('listData').style.display='block';
                    document.getElementById('container').innerHTML=con.responseText;

				}
            }
            else 
			{
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function edit(kodeorg,tanggal,tipe,kebun)
{
	document.getElementById('formInput').style.display='block';
	document.getElementById('listData').style.display='none';
	document.getElementById('pt').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('station').value=tipe;
	document.getElementById('kebun').value=kebun;
	document.getElementById('method').value='update';
	if(tipe=='1'){
		document.getElementById('trkebun').style.display='';
	}else{
		document.getElementById('trkebun').style.display='none';
	}
	saveht();
}

function showdetail(id,tipe,method,ev)
{ 
	param = "proses=showdetail&id="+id+"&method="+method+'&tipe='+tipe;

    title="Data Detail";
	if(method=='html'){
		showDialog1(title,"<iframe frameborder=0 style='width:795px;height:395px' src='sortasi_slave_harian_tbs.php?"+param+"'></iframe>",'','',ev);
		var dialog = document.getElementById('dynamic1');
	}else{
		showDialog2(title,"<iframe frameborder=0 style='width:100px;height:100px' src='sortasi_slave_harian_tbs.php?"+param+"'></iframe>",'','',ev);
		var dialog = document.getElementById('dynamic2');
	}
    dialog.style.top = '50px';
	dialog.style.left = '15%';
}





function cancelht(){
	document.getElementById('formInput').style.display='none';
	document.getElementById('listData').style.display='block';
}

function displayFormInput()
{
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
	canceldt();
}

function showalllist(pg){
	document.getElementById('caritanggal').value = '';
	loadData(pg);
}

function addfile(nofile) {
	var file = document.getElementById('upload'+nofile).files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("nofile", nofile);
	formdata.append("fileupload", getValue('upload'+nofile));
	if (getValue('upload'+nofile) == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "sortasi_slave_harian_tbs.php?proses=submitfile", true);
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
					document.getElementById("upload"+nofile).value = "";
					loadfiles(nofile);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(nofile) {
	param = 'proses=loadfiles&nofile='+nofile;
	tujuan = 'sortasi_slave_harian_tbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload'+nofile).innerHTML = con.responseText;
					// if (htdt == 'ht') {
						// document.getElementById('addfile').style.display = 'block';
						// document.getElementById('upload').disabled = false;
					// } else {
						// document.getElementById('addfile').style.display = 'none';
						// document.getElementById('upload').style.display = true;
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(namafile,nofile) {
	param = 'proses=deletefile&namafile='+namafile+'&nofile='+nofile;
	tujuan = 'sortasi_slave_harian_tbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(nofile);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
