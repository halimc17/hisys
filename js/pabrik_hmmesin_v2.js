function saveht(tp='') 
{
	unit=document.getElementById('unit').value;
	tanggal=document.getElementById('tanggal').value;
	station=document.getElementById('station').value;
	proses=document.getElementById('proses').value;
	
	if(unit==''){
		alertify.alert("Unit harus dipilih");
		return;
	}
	if(station==''){
		alertify.alert("Station harus dipilih");
		return;
	}
	
	param='proses='+proses+'&unit='+unit+'&tanggal='+tanggal+'&station='+station+'&tp='+tp;
	tujuan='pabrik_slave_hmmesin_v2.php';
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
					alertify.alert(con.responseText);
				}
				else 
				{
                    // alert(con.responseText);
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

function checkAll(jlhbaris){
	allCheck=document.getElementById('allCheck');
	if(allCheck.checked==true){
		valcek=true;
	}else{
		valcek=false;
	}
	
	for(i=1;i<=jlhbaris;i++){
		ar=document.getElementById('hmchecked_'+i);
		awal=remove_comma_var(document.getElementById('hmawal_'+i).value);
		proses=remove_comma_var(document.getElementById('hourproses_'+i).value);
		
		star=ar.disabled;
		if(star==false){
			if(valcek==true){
				akhirx=parseFloat(awal)+parseFloat(proses);
			}else{
				akhirx=parseFloat(awal);
			}
			document.getElementById('hmakhir_'+i).value=akhirx;
			ar.checked=valcek;
		}
	}
}

function checkone(baris){
	hmchecked=document.getElementById('hmchecked_'+baris);
	if(hmchecked.checked==true){
		valcek=true;
	}else{
		valcek=false;
	}
	
	awal=remove_comma_var(document.getElementById('hmawal_'+baris).value);
	proses=remove_comma_var(document.getElementById('hourproses_'+baris).value);
	if(valcek==true){
		akhirx=parseFloat(awal)+parseFloat(proses);
	}else{
		akhirx=parseFloat(awal);
	}
	document.getElementById('hmakhir_'+baris).value=akhirx;
}

function savedt(count) 
{
	loopsavedt(count,1);
}

function loopsavedt(count,current) 
{
	unit=document.getElementById('unit').value;
	tanggal=document.getElementById('tanggal').value;
	station=document.getElementById('station').value;
	
	substation=document.getElementById('substation_'+current).value;
	subkodemesin=document.getElementById('subkodemesin_'+current).innerHTML;
	hmawal=document.getElementById('hmawal_'+current).value;
	hmakhir=document.getElementById('hmakhir_'+current).value;
	hourproses=document.getElementById('hourproses_'+current).value;
	keterangan=document.getElementById('keterangan_'+current).value;
	
	if (hmawal>hmakhir) {
		// alertify.alert("HMAWAL tidak boleh lebih besar dari HMAKHIR");
		// return;
	}
	
/*	if(hourproses>24){
		document.getElementById("tr_"+current).style.backgroundColor = "red";
		document.getElementById("hourproses_"+current).focus();
		alertify.alert("Jumlah jam harus lebih kecil dari 24");
		return;
	}
	*/
	param='proses=savedt'+'&unit='+unit+'&tanggal='+tanggal+'&station='+station+'&substation='+subkodemesin+'&hourproses='+hourproses+'&keterangan='+keterangan+'&current='+current+'&subkodemesin='+subkodemesin;
	param+='&hmawal='+hmawal;
	param+='&hmakhir='+hmakhir;
	tujuan='pabrik_slave_hmmesin_v2.php';
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
					alertify.alert(con.responseText);
					document.getElementById("tr_"+current).style.backgroundColor = "red";
				}
				else 
				{
					if(count==current){
						document.getElementById("tr_"+current).style.backgroundColor = "#D8FFD4";
						document.getElementById("keterangan_"+current).focus();
						alertify.alert("Success");
						showalllist();
					}
					else{
						document.getElementById("tr_"+current).style.backgroundColor = "#D8FFD4";
						document.getElementById("keterangan_"+current).focus();
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

function gethour(urut){
	hmawal=document.getElementById('hmawal_'+urut).value;
	hmakhir=document.getElementById('hmakhir_'+urut).value;
	total = parseFloat(hmakhir) - parseFloat(hmawal);
	document.getElementById('hourproses_'+urut).value=total.toFixed(2);
}

function showdt(content){
	document.getElementById('simpanht').style.display = 'none';
	document.getElementById('cancelht').style.display = 'none';
	document.getElementById('tanggal').disabled = true;
	document.getElementById('station').disabled = true;
	document.getElementById('formdt').style.display = '';
	document.getElementById('formdt').innerHTML = content;
    leftFixedTable();
}

function canceldt(){
	document.getElementById('simpanht').style.display = '';
	document.getElementById('cancelht').style.display = '';
	document.getElementById('tanggal').disabled = false;
	document.getElementById('station').disabled = false;
	document.getElementById('formdt').style.display = 'none';
	document.getElementById('formdt').innerHTML = '';
	showontop();
}

function deleteall(unit,tanggal,station)
{
	param='proses=deleteall&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin_v2.php';
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
					alertify.alert(con.responseText);
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
    
	tujuan='pabrik_slave_hmmesin_v2.php';
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
					alertify.alert(con.responseText);
                }
                else 
				{
                    dt = con.responseText.split('####');
					document.getElementById('formInput').style.display='none';
                    document.getElementById('listData').style.display='block';
                    document.getElementById('continerlist').innerHTML=dt[0];
                    document.getElementById('footData').innerHTML=dt[1];
                    leftFixedTable();
					showontop();
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

function editall(unit,tanggal,station)
{
	document.getElementById('listData').style.display = 'none';
    document.getElementById('formInput').style.display = 'block';
	
	document.getElementById('unit').value=unit;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('station').value=station;
	
	saveht('edit');
}

function showdetail(unit,tanggal,station,ev)
{
	param = "proses=showdetail&unit="+unit+"&tanggal="+tanggal+"&station="+station;
    tujuan = 'pabrik_slave_hmmesin_v2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('contDetail').innerHTML = con.responseText;
					alertify.popup().set({'title':'Data Detail','resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postall(unit,tanggal,station)
{
	param='proses=postall&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin_v2.php';
	alertify.confirm("Are you sure posted this item?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);	
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alertify.alert(con.responseText);
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

function unpostall(unit,tanggal,station)
{
	param='proses=unpostall&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin_v2.php';
	if(confirm("Are you sure unposted this item?"))
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
					alertify.alert(con.responseText);
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
