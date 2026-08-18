function saveht() 
{
	unit=document.getElementById('unit').value;
	tanggal=document.getElementById('tanggal').value;
	station=document.getElementById('station').value;
	proses=document.getElementById('proses').value;
	
	if(unit==''){
		alert("Unit harus dipilih");
		return;
	}
	if(station==''){
		alert("Station harus dipilih");
		return;
	}
	
	param='proses='+proses+'&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin.php';
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
	unit=document.getElementById('unit').value;
	tanggal=document.getElementById('tanggal').value;
	station=document.getElementById('station').value;
	
	substation=document.getElementById('substation_'+current).innerHTML;
	hour=document.getElementById('hour_'+current).value;
	hournonpararel=document.getElementById('hournonpararel_'+current).value;
	hourproses=document.getElementById('hourproses_'+current).value;
	keterangan=document.getElementById('keterangan_'+current).value;
	
	if(hour>24){
		document.getElementById("tr_"+current).style.backgroundColor = "red";
		document.getElementById("hour_"+current).focus();
		alert("Jumlah jam harus lebih kecil dari 24");
		return;
	}
	
	if(hourproses>24){
		document.getElementById("tr_"+current).style.backgroundColor = "red";
		document.getElementById("hourproses_"+current).focus();
		alert("Jumlah jam harus lebih kecil dari 24");
		return;
	}
	
	param='proses=savedt'+'&unit='+unit+'&tanggal='+tanggal+'&station='+station+'&substation='+substation+'&hour='+hour+'&hournonpararel='+hournonpararel+'&hourproses='+hourproses+'&keterangan='+keterangan+'&current='+current;
	tujuan='pabrik_slave_hmmesin.php';
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
						document.getElementById("keterangan_"+current).focus();
						alert("Success");
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

function showdt(content){
	document.getElementById('simpanht').style.display = 'none';
	document.getElementById('cancelht').style.display = 'none';
	document.getElementById('tanggal').disabled = true;
	document.getElementById('station').disabled = true;
	document.getElementById('formdt').style.display = '';
	document.getElementById('formdt').innerHTML = content;
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
	tujuan='pabrik_slave_hmmesin.php';
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
    
	tujuan='pabrik_slave_hmmesin.php';
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
                    document.getElementById('continerlist').innerHTML=con.responseText;
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
	
	saveht();
}

function showdetail(unit,tanggal,station,ev)
{
	param = "proses=showdetail&unit="+unit+"&tanggal="+tanggal+"&station="+station;
    title="Data Detail";
	showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px' src='pabrik_slave_hmmesin.php?"+param+"'></iframe>",'auto','auto',ev);	
    var dialog = document.getElementById('dynamic1');
    clientWidth = document.getElementById("dynamic1").clientWidth;
	clientHeight = document.getElementById("dynamic1").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);
	
	dialog.style.top = pos[1]+'px';
	dialog.style.left = (pos[0]-clientWidth)+'px';
}

function postall(unit,tanggal,station)
{
	param='proses=postall&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin.php';
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

function unpostall(unit,tanggal,station)
{
	param='proses=unpostall&unit='+unit+'&tanggal='+tanggal+'&station='+station;
	tujuan='pabrik_slave_hmmesin.php';
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
