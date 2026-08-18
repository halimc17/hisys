function batal1()
{
	document.getElementById('method1').value='insert1';
	document.getElementById("idjenis1").value='';
	document.getElementById("jenis1").selectedIndex=0;
	document.getElementById('kriteria1').value='';
	document.getElementById('satuan1').value='';
	document.getElementById('satuan199').value='';
}

function simpan1()
{
	idjenis = trim(document.getElementById('idjenis1').value);
	jenis = trim(document.getElementById('jenis1').value);
	kriteria = trim(document.getElementById('kriteria1').value);
	satuan = trim(document.getElementById('satuan1').value);
	satuan99 = trim(document.getElementById('satuan199').value);
	method=trim(document.getElementById('method1').value);
	
	param='idjenis='+idjenis+'&jenis='+jenis+'&kriteria='+kriteria+'&satuan='+satuan+'&satuan99='+satuan99+'&method='+method;
	tujuan='kebun_slave_5jenismutu.php';
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
					loaddata1();
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

function loaddata1(num)
{
	param='method=loaddata1';
	param+='&page='+num;
	tujuan='kebun_slave_5jenismutu.php';
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
					batal1();
					document.getElementById('container1').innerHTML=con.responseText;
					loadDatamutu(0);	
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

function fillfield1(jenis,kriteria,idjenis,satuan,satuan99)
{
	document.getElementById('method1').value='update1';
	document.getElementById('idjenis1').value=idjenis;
	document.getElementById('jenis1').value=jenis;
	document.getElementById('kriteria1').value=kriteria;
	document.getElementById('satuan1').value=satuan;
	document.getElementById('satuan199').value=satuan99;
}

function hapus1(idjenis)
{
	param='idjenis='+idjenis+'&method=delete1';
	tujuan='kebun_slave_5jenismutu.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
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
					loaddata1();
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

function bataljenis(){
	document.getElementById('method').value='insert';
	document.getElementById("jenis").value='';
	document.getElementById('kriteria').value='';
}

function loadDatajenis(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='kebun_slave_5jenismutu.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
					loadDatamutu(0);	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function getkriteria(jenismutu,kriteria){
	if(jenismutu==0){
		jenismutu=document.getElementById('jenismutu').options[document.getElementById('jenismutu').selectedIndex].value;	
	}
	param='jenismutu='+jenismutu+'&method=getkriteria';
	if(kriteria!=0){
		param+='&kriteria='+kriteria;
	}
	tujuan='kebun_slave_5mutuancak.php';  
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('kriteriamutu').innerHTML = con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function simpanmutu(){
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	jenismutu=document.getElementById('jenismutu').options[document.getElementById('jenismutu').selectedIndex].value;
	kriteriamutu=document.getElementById('kriteriamutu').options[document.getElementById('kriteriamutu').selectedIndex].value;
	kodefill=document.getElementById('kodefill').value;
	rangedari=trim(document.getElementById('rangedari').value);
	rangesampai=trim(document.getElementById('rangesampai').value);
	rangetotaldari=trim(document.getElementById('rangetotaldari').value);
	rangetotalsampai=trim(document.getElementById('rangetotalsampai').value);
	keterangan=trim(document.getElementById('keterangan').value);
	nilai=trim(document.getElementById('nilai').value);
	method=trim(document.getElementById('method').value);
	
	param='pt='+pt+'&jenismutu='+jenismutu+'&kriteriamutu='+kriteriamutu+'&kodefill='+kodefill+'&rangedari='+rangedari+
	'&rangesampai='+rangesampai+'&rangetotaldari='+rangetotaldari+'&rangetotalsampai='+rangetotalsampai+
	'&keterangan='+keterangan+'&nilai='+nilai+'&method='+method;
	tujuan='kebun_slave_5mutuancak.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							loadDatamutu();
							batalmutu();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function batalmutu(){
	document.getElementById('method').value='insert';
	document.getElementById('pt').disabled = false;
	document.getElementById("rangedari").value='0';
	document.getElementById('rangesampai').value='0';
	document.getElementById('rangetotaldari').value='0';
	document.getElementById('rangetotaldari').disabled = false;
	document.getElementById('rangetotalsampai').value='0';
	document.getElementById('rangetotalsampai').disabled = false;
	document.getElementById('keterangan').value='';
	document.getElementById('keterangan').disabled = false;
	document.getElementById('nilai').value='0';
	document.getElementById('pt').selectedIndex=0;
	document.getElementById('jenismutu').selectedIndex=0;
	document.getElementById('jenismutu').disabled = false;
	document.getElementById('kriteriamutu').selectedIndex=0;
	document.getElementById('kriteriamutu').disabled = false;
	document.getElementById('kodefill').value='';
	document.getElementById('displaycolorfill').style.backgroundColor='';
}

function fillfieldmutu(pt,rangedari,rangesampai,keterangan,jenismutu,kriteriamutu,kodefill,rangetotaldari,rangetotalsampai,nilai){
	Lkd_pt=document.getElementById('pt');
    for(ard=0;ard<Lkd_pt.length;ard++)
    {
        if(Lkd_pt.options[ard].value==pt)
            {
                Lkd_pt.options[ard].selected=true;
            }
    }
	document.getElementById('pt').disabled=true;

	Lkd_jenismutu=document.getElementById('jenismutu');
    for(ard=0;ard<Lkd_jenismutu.length;ard++)
    {
        if(Lkd_jenismutu.options[ard].value==jenismutu)
            {
                Lkd_jenismutu.options[ard].selected=true;
            }
    }
	document.getElementById('jenismutu').disabled = true;
	document.getElementById('kriteriamutu').disabled=true;
	document.getElementById('kodefill').value=kodefill;
	document.getElementById('rangedari').value=rangedari;
	document.getElementById('rangesampai').value=rangesampai;
	document.getElementById('rangetotaldari').value=rangetotaldari;
	document.getElementById('rangetotaldari').disabled = true;
	document.getElementById('rangetotalsampai').value=rangetotalsampai;
	document.getElementById('rangetotalsampai').disabled = true;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('keterangan').disabled = true;
	document.getElementById('nilai').value=nilai;
	document.getElementById('method').value='update';
	getkriteria(jenismutu,kriteriamutu);
}

function delmutu(pt,idjenis,rangedari,rangesampai,rangetotaldari,rangetotalsampai){
    param='pt='+pt+'&idjenis='+idjenis+'&rangedari='+rangedari+'&rangesampai='+rangesampai+'&rangetotaldari='+rangetotaldari+
    '&rangetotalsampai='+rangetotalsampai+'&method=delete';
    tujuan='kebun_slave_5mutuancak.php';
    if(confirm("Are You Sure Want Delete Data?"))
        post_response_text(tujuan, param, respog);
				
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loadDatamutu();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function loadDatamutu(num) {
	param='method=loadData';
	param+='&page='+num;
	tujuan='kebun_slave_5mutuancak.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				} else {
					document.getElementById('containermutu').innerHTML=con.responseText;	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function cariwarna(jenis,ev)
{
    content = "<div id=listwarna style=\"height:400px;width:905px;\"></div>";
    title =' Tabel Warna :';
    width = '904';
    height = '377';
    showDialog1(title, content, width, height, ev);
	getwarna(jenis);
}

function getwarna(jenis)
{
    param = 'method=cariwarna&jenis=' + jenis;

    tujuan = 'kebun_slave_5mutuancak.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
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
                    document.getElementById('listwarna').innerHTML = con.responseText;
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


function movewarna(warna,jenis)
{
	document.getElementById('kode'+jenis).value=warna;
	document.getElementById('displaycolor'+jenis).style.backgroundColor=warna;
	closeDialog();
}