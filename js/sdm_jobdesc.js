function getkaryawanid(karyawanid)
{
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param='proses=getkaryawanid&unit='+unit;
	if(karyawanid!='')
	{
		param+='&karyawanid='+karyawanid;
	}
	tujuan='sdm_slave_jobdesc.php';
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
					document.getElementById('karyawanid').innerHTML=con.responseText;
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

function addbawahan()
{
	bawahan=document.getElementById('bawahan').options[document.getElementById('bawahan').selectedIndex].value;
	if(bawahan=='')
	{
		alert("field cannot be blank.");
		return;
	}
	param='proses=addbawahan&bawahan='+bawahan;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlistbawahan('skip');
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

function loadlistbawahan(tipe)
{
	param='proses=loadlistbawahan';
	tujuan='sdm_slave_jobdesc.php';
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
					if(tipe=='skip')
					{
						document.getElementById('listbawahan').innerHTML=con.responseText;
					}
					else
					{
						document.getElementById('listbawahan').innerHTML=con.responseText;
						loadlisttujuanjabatan('next');
					}
					document.getElementById('bawahan').selectedIndex=0;
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

function deletebawahan(karyawanid)
{
	param='proses=deletebawahan&bawahan='+karyawanid;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlistbawahan('skip');
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

function addtujuanjabatan()
{
	tujuanjabatan=document.getElementById('tujuanjabatan').value;
	if(tujuanjabatan=='')
	{
		alert("field cannot be blank.");
		return;
	}
	param='proses=addtujuanjabatan&tujuanjabatan='+tujuanjabatan;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisttujuanjabatan('skip');
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

function loadlisttujuanjabatan(tipe)
{
	param='proses=loadlisttujuanjabatan';
	tujuan='sdm_slave_jobdesc.php';
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
					if(tipe=='skip')
					{
						document.getElementById('listtujuanjabatan').innerHTML=con.responseText;
					}
					else
					{
						document.getElementById('listtujuanjabatan').innerHTML=con.responseText;
						loadlisttanggungjawab('next');
					}
					document.getElementById('tujuanjabatan').value='';
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

function deletetujuanjabatan(val)
{
	param='proses=deletetujuanjabatan&tujuanjabatan='+val;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisttujuanjabatan('skip');
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

function addtanggungjawab()
{
	tipetgg=document.getElementById('tipetgg').options[document.getElementById('tipetgg').selectedIndex].value;
	tugas=document.getElementById('tugas').value;
	indkin=document.getElementById('indkin').value;
	deadline=document.getElementById('deadline').value;
	if(tipetgg==''||tugas==''||indkin=='')
	{
		alert("field cannot be blank.");
		return;
	}
	param='proses=addtanggungjawab&tipetgg='+tipetgg+'&tugas='+tugas+'&indkin='+indkin+'&deadline='+deadline;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisttanggungjawab('skip');
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

function loadlisttanggungjawab(tipe)
{
	param='proses=loadlisttanggungjawab';
	tujuan='sdm_slave_jobdesc.php';
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
					if(tipe=='skip')
					{
						document.getElementById('listtanggungjawab').innerHTML=con.responseText;
					}
					else
					{
						document.getElementById('listtanggungjawab').innerHTML=con.responseText;
						loadlistwewenang('next');
					}
					document.getElementById('tugas').value='';
					document.getElementById('indkin').value='';
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

function deletetanggungjawab(tipetgg,tugas,indkin,deadline)
{
	param='proses=deletetanggungjawab&tipetgg='+tipetgg+'&tugas='+tugas+'&indkin='+indkin+'&deadline='+deadline;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisttanggungjawab('skip');
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

function addwewenang()
{
	wewenang=document.getElementById('wewenang').value;
	if(wewenang=='')
	{
		alert("field cannot be blank.");
		return;
	}
	param='proses=addwewenang&wewenang='+wewenang;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlistwewenang('skip');
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

function loadlistwewenang(tipe)
{
	param='proses=loadlistwewenang';
	tujuan='sdm_slave_jobdesc.php';
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
					if(tipe=='skip')
					{
						document.getElementById('listwewenang').innerHTML=con.responseText;
					}
					else
					{
						document.getElementById('listwewenang').innerHTML=con.responseText;
						loadlisthubungankerja('next');
					}
					document.getElementById('wewenang').value='';
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

function deletewewenang(val)
{
	param='proses=deletewewenang&wewenang='+val;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlistwewenang('skip');
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

function addhubungankerja()
{
	tipehubker=document.getElementById('tipehubker').options[document.getElementById('tipehubker').selectedIndex].value;
	deskripsihubker=document.getElementById('deskripsihubker').value;
	hubungankerja=document.getElementById('hubungankerja').value;
	if(hubungankerja=='')
	{
		alert("field cannot be blank.");
		return;
	}
	param='proses=addhubungankerja&tipehubker='+tipehubker+'&deskripsihubker='+deskripsihubker+'&hubungankerja='+hubungankerja;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisthubungankerja('skip');
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

function loadlisthubungankerja(tipe)
{
	param='proses=loadlisthubungankerja';
	tujuan='sdm_slave_jobdesc.php';
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
					if(tipe=='skip')
					{
						document.getElementById('listhubungankerja').innerHTML=con.responseText;
					}
					else
					{
						document.getElementById('listhubungankerja').innerHTML=con.responseText;
					}
					document.getElementById('deskripsihubker').value='';
					document.getElementById('hubungankerja').value='';
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

function deletehubungankerja(tipehubker,deskripsihubker,hubungankerja)
{
	param='proses=deletehubungankerja&tipehubker='+tipehubker+'&deskripsihubker='+deskripsihubker+'&hubungankerja='+hubungankerja;
	tujuan='sdm_slave_jobdesc.php';
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
					loadlisthubungankerja('skip');
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

function displayFormInput()
{
    clearData();
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function canceldata(){
	// document.getElementById('formInput').style.display='none';
	// document.getElementById('listData').style.display='block';
	clearData();
}

function clearData()
{
	document.getElementById('jabatan').selectedIndex=0;
	document.getElementById('departmen').selectedIndex=0;
	document.getElementById('unit').selectedIndex=0;
	document.getElementById('tglefektif').value=getdatenow(1);
	document.getElementById('karyawanid').selectedIndex=0;
	
	document.getElementById('atasan').selectedIndex=0;
	document.getElementById('rekan').selectedIndex=0;
	document.getElementById('bawahan').selectedIndex=0;
	
	document.getElementById('tujuanjabatan').value='';
	
	document.getElementById('tipetgg').selectedIndex=0;
	document.getElementById('tugas').value='';
	document.getElementById('indkin').value='';
	document.getElementById('deadline').value=getdatenow(1);
	
	document.getElementById('wewenang').value='';
	
	document.getElementById('tipehubker').selectedIndex=0;
	document.getElementById('deskripsihubker').value='';
	document.getElementById('hubungankerja').value='';
	
	document.getElementById('pendidikan').selectedIndex=0;
	document.getElementById('pengalamankerja').value='';
	document.getElementById('pelatihan').value='';
	document.getElementById('kompetensi').value='';
	
	document.getElementById('notransaksi').value='';
	document.getElementById('proses').value='insert';
	
	param='proses=clearData';
	tujuan='sdm_slave_jobdesc.php';
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
					loadlistbawahan('next');
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

function savedata() 
{
	notransaksi=document.getElementById('notransaksi').value;
	jabatan=document.getElementById('jabatan').options[document.getElementById('jabatan').selectedIndex].value;
	departmen=document.getElementById('departmen').options[document.getElementById('departmen').selectedIndex].value;
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	tglefektif=document.getElementById('tglefektif').value;
	karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
	
	atasan=document.getElementById('atasan').options[document.getElementById('atasan').selectedIndex].value;
	rekan=document.getElementById('rekan').options[document.getElementById('rekan').selectedIndex].value;
	
	pendidikan=document.getElementById('pendidikan').options[document.getElementById('pendidikan').selectedIndex].value;
	pengalamankerja=document.getElementById('pengalamankerja').value;
	pelatihan=document.getElementById('pelatihan').value;
	kompetensi=document.getElementById('kompetensi').value;
	
	proses=document.getElementById('proses').value;
	
	param='proses='+proses+'&notransaksi='+notransaksi+'&jabatan='+jabatan+'&departmen='+departmen+'&unit='+unit+'&tglefektif='+tglefektif+'&karyawanid='+karyawanid+'&atasan='+atasan+'&rekan='+rekan+'&pendidikan='+pendidikan+'&pengalamankerja='+pengalamankerja+'&pelatihan='+pelatihan+'&kompetensi='+kompetensi;
	tujuan='sdm_slave_jobdesc.php';
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
					loadData();
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

function loadData(page)
{
	caript=document.getElementById('caript');
	caript=caript.options[caript.selectedIndex].value;
	caritanggal=document.getElementById('caritanggal').value;
	
    param='proses=loadData'+'&page='+page;
	
	if(caript!=''){
		param+='&caript='+caript;
	}
	if(caritanggal!=''){
		param+='&caritanggal='+caritanggal;
	}
    
	tujuan='sdm_slave_jobdesc.php';
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
                    // document.getElementById('formUpload').style.display='none';
                    document.getElementById('listData').style.display='block';
                    document.getElementById('continerlist').innerHTML=con.responseText;
                    clearData();
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

function deletejobdesc(notransaksi)
{
	param='proses=deletejobdesc&notransaksi='+notransaksi;
	tujuan='sdm_slave_jobdesc.php';
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

function showalllist(pg){
	document.getElementById('caript').selectedIndex=0;
	document.getElementById('caritanggal').value = '';
	loadData(pg);
}

function getPage()
{
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function editjobdesc(notransaksi,jabatan,departemen,unit,tanggalefektif,karyawanid,atasan,rekan,pendidikan,pengalamankerja,pelatihan,kompetensi)
{
	document.getElementById('listData').style.display = 'none';
    document.getElementById('formInput').style.display = 'block';
    
	document.getElementById('proses').value = 'update';
	
	document.getElementById('notransaksi').value=notransaksi;
	
	document.getElementById('jabatan').value=jabatan;
	document.getElementById('departmen').value=departemen;
	document.getElementById('unit').value=unit;
	document.getElementById('tglefektif').value=tanggalefektif;
	document.getElementById('karyawanid').value=karyawanid;
	
	document.getElementById('atasan').value=atasan;
	document.getElementById('rekan').value=rekan;
	
	document.getElementById('pendidikan').value=pendidikan;
	document.getElementById('pengalamankerja').value=pengalamankerja;
	document.getElementById('pelatihan').value=pelatihan;
	document.getElementById('kompetensi').value=kompetensi;
	
	param='proses=editjobdesc&notransaksi='+notransaksi;
	tujuan='sdm_slave_jobdesc.php';
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
					getkaryawanid2(karyawanid);
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

function cariData(pg)
{
	loadData(pg);
}

function getkaryawanid2(karyawanid)
{
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param='proses=getkaryawanid&unit='+unit;
	if(karyawanid!='')
	{
		param+='&karyawanid='+karyawanid;
	}
	tujuan='sdm_slave_jobdesc.php';
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
					document.getElementById('karyawanid').innerHTML=con.responseText;
					loadlistbawahan('next');
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

function postingjobdesc(notransaksi)
{
	param='proses=postingjobdesc&notransaksi='+notransaksi;
	tujuan='sdm_slave_jobdesc.php';
	if(confirm("Are you sure submitted this item?"))
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

function printpdf(notransaksi,ev)
{
	param='proses=printpdf2&notransaksi='+notransaksi;
	tujuan='sdm_slave_jobdesc.php';
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
					var canvas = document.getElementById("myCanvas");
					ctx = canvas.getContext("2d");
					ctx.fillStyle = "white";
					ctx.fillRect(0,0,400,200);
					rasterizeHTML.drawHTML(con.responseText, canvas).then(function success(result){
						printpdf3(notransaksi,canvas,ev);
					});
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

function printpdf3(notransaksi,img,ev)
{
	dataURL = "";
	//var data = document.getElementById('myCanvas');
	var dataURL = img.toDataURL('image/jpeg',1.0);
	// dataURL = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
	
	param='proses=printpdf3&notransaksi='+notransaksi+'&myimage='+dataURL;
	tujuan='sdm_slave_jobdesc.php';
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
					printpdf2(notransaksi,ev)
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

function printpdf2(notransaksi,ev)
{
	// dataURL = "";
	// var data = document.getElementById('myCanvas');
	// var dataURL = data.toDataURL("image/png");
	//dataURL = dataURL.replace(/^data:image\/(png|jpg);base64,/, "")
	
	param = "proses=printpdf&notransaksi="+notransaksi;
	// param = "proses=printpdf&notransaksi="+notransaksi;
	 
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='sdm_slave_jobdesc.php?"+param+"'></iframe>",'','',ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}



//#########################################################