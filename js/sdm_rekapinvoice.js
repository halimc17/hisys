
function saveData(){
	notransaksi=trim(document.getElementById('notransaksi').value);
	jenisdata=trim(document.getElementById('jenisdata').value);
	noinvoice=trim(document.getElementById('noinvoice').value);
	rute=trim(document.getElementById('rute').value);
    jumlah=trim(document.getElementById('jumlah').value);
    keterangan=trim(document.getElementById('keterangan').value);
    tanggalrute=trim(document.getElementById('tanggalrute').value);
    oldnotransaksi=trim(document.getElementById('oldnotransaksi').value);
    oldnoinvoice=trim(document.getElementById('oldnoinvoice').value);
    oldtanggalrute=trim(document.getElementById('oldtanggalrute').value);
    periode=trim(document.getElementById('periode').value);
	method=trim(document.getElementById('method').value);


	param='notransaksi='+notransaksi+'&jenisdata='+jenisdata+'&method='+method+'&noinvoice='+noinvoice;
    param+='&rute='+rute+'&jumlah='+jumlah+'&keterangan='+keterangan+'&tanggalrute='+tanggalrute+'&periode='+periode;
    param+='&oldnotransaksi='+oldnotransaksi+'&oldnoinvoice='+oldnoinvoice+'&oldtanggalrute='+oldtanggalrute;
    tujuan='sdm_slave_rekapinvoice.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					clearData();
					displaylist();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldt(notrans,noinvoice,tanggalrute)
{
    param='method=deldt'+'&notransaksi='+notrans+'&noinvoice='+noinvoice+'&tanggalrute='+tanggalrute;
    tujuan='sdm_slave_rekapinvoice.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
		if(con.readyState==4)
		{
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   displaylist();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
		} 
    }
}

function editdt(jenisdata,notrans,noinvoice,rute,jumlah,keterangan,tanggalrute,periode){
    document.getElementById('notransaksi').value=notrans;
    document.getElementById('oldnotransaksi').value=notrans;
    document.getElementById('jenisdata').value=jenisdata;
    document.getElementById('jenisdata').disabled=true;
    document.getElementById('noinvoice').value=noinvoice;
    document.getElementById('oldnoinvoice').value=noinvoice;
    document.getElementById('tanggalrute').value=tanggalrute;
    document.getElementById('oldtanggalrute').value=tanggalrute;
    document.getElementById('rute').value=rute;
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('periode').value=periode;
    document.getElementById('method').value='updatedt';
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function clearData(){
	document.getElementById('notransaksi').value='';
	document.getElementById('jenisdata').value='';
    document.getElementById('jenisdata').disabled=false;
	document.getElementById('noinvoice').value='';
    document.getElementById('rute').value='';
    document.getElementById('jumlah').value='';
    document.getElementById('keterangan').value='';
    document.getElementById('tanggalrute').value='';
    document.getElementById('oldnotransaksi').value='';
    document.getElementById('oldnoinvoice').value='';
    document.getElementById('oldtanggalrute').value='';
    document.getElementById('periode').value='';
	document.getElementById('method').value='insert';
}

function displayFormInput(){
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    clearData();
}

function displaylist(){
    document.getElementById('tipecr').value='';
    document.getElementById('rutecr').value='';
    document.getElementById('notransaksicr').value='';
    document.getElementById('noinvoicecr').value='';
	document.getElementById('listData').style.display='block';
	document.getElementById('formInput').style.display='none';
    clearData();
	loadData(0);
}

function loadData(num){
    tipecr=document.getElementById('tipecr').value;
    rutecr=document.getElementById('rutecr').value;
    notransaksicr=document.getElementById('notransaksicr').value;
    noinvoicecr=document.getElementById('noinvoicecr').value;

    param='method=loadData';
    param+='&page='+num;
    param+='&tipecr='+tipecr;
    param+='&rutecr='+rutecr;
    param+='&notransaksicr='+notransaksicr;
    param+='&noinvoicecr='+noinvoicecr;

    tujuan='sdm_slave_rekapinvoice.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
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

function posting(notrans,noinvoice,tanggalrute)
{
    param='method=posting'+'&notransaksi='+notrans+'&noinvoice='+noinvoice+'&tanggalrute='+tanggalrute;
    tujuan='sdm_slave_rekapinvoice.php';
    if(confirm('Anda yakin ingin memposting data ini ??'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else 
                {
                    displaylist();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function searchnotrans(title,content,ev)
{
    jenisdata=trim(document.getElementById('jenisdata').value);
    if (jenisdata=='') {
        alert('Type of data may not empty.');
        return;
    }
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformnotrans();
}

function getformnotrans(){
    jenisdata=trim(document.getElementById('jenisdata').value);
    param='jenisdata='+jenisdata+'&method=getformnotrans';
    tujuan='sdm_slave_rekapinvoice.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formPencariandata').innerHTML=con.responseText;
                    findnotrans();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findnotrans(){
    jenisdata=trim(document.getElementById('jenisdata').value);
    param='method=getdatanotrans'+'&jenisdata='+jenisdata;

    if (jenisdata=='Dinas') {
        notran=trim(document.getElementById('notran').value);
        param+='&notran='+notran;
    }
    if (jenisdata=='Cuti') {
        tanggal=trim(document.getElementById('tanggal').value);
        param+='&tanggal='+tanggal;
    }
    
    tujuan='sdm_slave_rekapinvoice.php';
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
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdata(notran,tanggalrute,no,periode) {
    rute=document.getElementById('datarute_'+no).innerHTML;
    document.getElementById('notransaksi').value=notran;
    document.getElementById('rute').value=rute;
    document.getElementById('tanggalrute').value=tanggalrute;
    document.getElementById('periode').value=periode;
    closeDialog();
}

function searchnoinvoice(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getformnoinvoice();
}

function getformnoinvoice(){
    param='method=getformnoinvoice';
    tujuan='sdm_slave_rekapinvoice.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('formnoinvoice').innerHTML=con.responseText;
                    findnoinvoice();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function findnoinvoice(){
    notran=trim(document.getElementById('notran').value);
    param='method=getdatanoinvoice'+'&jenisdata='+jenisdata;
    param+='&notran='+notran;
    
    tujuan='sdm_slave_rekapinvoice.php';
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
                    document.getElementById('container3').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdatainv(notran,jumlah,no) {
    keterangan=document.getElementById('ketinvoice_'+no).innerHTML;
    document.getElementById('noinvoice').value=notran;
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('keterangan').value=keterangan;
    closeDialog();
}

function prexcel(tujuan,ev)
{

    tipecr=document.getElementById('tipecr').value;
    rutecr=document.getElementById('rutecr').value;
    notransaksicr=document.getElementById('notransaksicr').value;
    noinvoicecr=document.getElementById('noinvoicecr').value;

    param='method=excel';
    param+='&tipecr='+tipecr;
    param+='&rutecr='+rutecr;
    param+='&notransaksicr='+notransaksicr;
    param+='&noinvoicecr='+noinvoicecr;

    tujuan=tujuan+"?"+param;  
    width='700';
    height='250';
    title='Excel';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1(title,content,width,height,ev);  
}