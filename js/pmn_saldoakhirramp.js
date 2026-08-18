function displaylist(){
	document.getElementById('header').style.display = 'none';
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('detail').style.display='none';
    document.getElementById('tglcari').value='';
    idr=document.getElementById('unitMillCr');
    for(a=0;a<idr.length;a++){
        if(idr.options[a].value=='')
            {
                idr.options[a].selected=true;
            }
    }
    // idr2=document.getElementById('statJrnl');
    // for(a=0;a<idr2.length;a++){
        // if(idr2.options[a].value=='')
            // {
                // idr2.options[a].selected=true;
            // }
    // }
    loaddata(0);
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(num){
    mil=document.getElementById('unitMillCr');
    mil=mil.options[mil.selectedIndex].value;
    tglCr=document.getElementById('tglcari').value;
	
	param = 'method=loaddata&page=' + num;
    param+='&millcode='+mil+'&tglcari='+tglCr;
    tujuan = 'pmn_slave_saldoakhirramp.php';
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
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
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

function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
    document.getElementById('notransaksi').value='';  

	var tgl = new Date();
	var hari = tgl.getDate();
	var bulan = tgl.getMonth()+1;
	var tahun = tgl.getFullYear();
    document.getElementById('tanggal').value = pad_with_zeroes(hari,2)+'-'+pad_with_zeroes(bulan,2)+'-'+tahun;    
    
	idr=document.getElementById('unit');
    for(a=0;a<idr.length;a++)
	{
		if(idr.options[a].value=='')
		{
			idr.options[a].selected=true;
		}
    }
    document.getElementById('ramp').innerHTML="<option value=''>"+pild+"</option>";
    document.getElementById('listdetail').innerHTML="";
    unLockForm();
}



function pad_with_zeroes(number, length) 
{
	var my_string = '' + number;
    while (my_string.length < length) 
	{
        my_string = '0' + my_string;
    }

    return my_string;
}



function cancel(){
	idr=document.getElementById('unit');
    for(a=0;a<idr.length;a++){
        if(idr.options[a].value=='')
            {
                idr.options[a].selected=true;
            }
    }
    document.getElementById('ramp').innerHTML="<option value=''>"+pild+"</option>";
    document.getElementById('notransaksi').value='';
    document.getElementById('hrgkgAll').value='0';
    
	var tgl = new Date();
	var hari = tgl.getDate();
	var bulan = tgl.getMonth()+1;
	var tahun = tgl.getFullYear();
    document.getElementById('tanggal').value = pad_with_zeroes(hari,2)+'-'+pad_with_zeroes(bulan,2)+'-'+tahun;	
	
    displaylist();
    unLockForm();
}

function savehead()
{
    unit=document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;
    tgl=document.getElementById('tanggal').value;
    saldo=document.getElementById('hrgkgAll').value;
    notrans=document.getElementById('notransaksi').value;
    ramp=document.getElementById('ramp');
    ramp=ramp.options[ramp.selectedIndex].value;
    
	param = 'method=savehead'+'&unit='+unit+'&tgl='+tgl+'&saldo='+saldo;
    param+='&ramp='+ramp+'&notrans='+notrans;
    
    post_response_text(tujuan, param, respon);
	
	function respon()
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
					displaylist();
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

function detail(unit,tgl,suppId){
    param = 'method=detail' + '&millcode=' + unit + '&tglnormal=' + tgl;
    param+='&suppId='+suppId;
    hrgPerkg=document.getElementById('hrgkgAll').value;
    subsidiangkut=document.getElementById('subsidiangkut').value;
    prsnAll=document.getElementById('prsnAll').value;
    bbnPajak=document.getElementById('bbnPajak');
    bbnPajak=bbnPajak.options[bbnPajak.selectedIndex].value;
    param+='&hrgkgAll='+hrgPerkg+'&prsnAll='+prsnAll+'&bbnPajak='+bbnPajak+'&subsidiangkut='+subsidiangkut;
    //alert(param);
    tujuan = 'pmn_slave_saldoakhirramp.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('listdetail').innerHTML=con.responseText;
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


function detaildt(notransaksi,kdmill,tgl,suppId,ev){
	 param = 'method=detaildata' + '&notransaksi=' + notransaksi;
     param +='&millcode='+kdmill+'&tglnormal='+tgl+'&suppId='+suppId;
    title="Data Detail";
     showDialog1(title,"<iframe frameborder=0 style='width:1045px;height:395px'"+
    " src='pmn_slave_saldoakhirramp.php?"+param+"'></iframe>",'1050','400',ev); 
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function getkoderamp(ramp)
{
	unit=document.getElementById('unit');
	unit=unit.options[unit.selectedIndex].value;
	param='unit='+unit+'&ramp='+ramp+'&method=getkoderamp';
	
	tujuan = 'pmn_slave_saldoakhirramp.php';
    post_response_text(tujuan, param, respon);
    
	function respon()
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
					document.getElementById('ramp').innerHTML=con.responseText;
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

function getRup(iddt){
    jmlhfisik=document.getElementById('beratnormal'+iddt);
    jmlhRupiah=document.getElementById('harga'+iddt);
    jmlhSubsidi=document.getElementById('subsidi'+iddt);

    //status iya atau tidak
    stat=document.getElementById('optpph'+iddt);
    stat=stat.options[stat.selectedIndex].value;
    if(stat==1){
        // document.getElementById('persenpph'+iddt).value='0.5';
    }else{
        // document.getElementById('persenpph'+iddt).value='0.25';
    }
    persen=document.getElementById('persenpph'+iddt).value;
    

    //rupiah total bayar
    totRupiah=(parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhRupiah.value))+parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhSubsidi.value)));
    if(isNaN(totRupiah)){
        totRupiah=0;
    }
    

    //rupiah pph22
    rppersn=(parseFloat(totRupiah) * (100/(100- parseFloat(persen)))) - parseFloat(totRupiah);
    // rppersn=parseFloat(totRupiah)*(parseFloat(persen)/100);

    //hasil perhitungan
    stu=document.getElementById('totalharga'+iddt);
    stu.value=totRupiah;
    

    rph=document.getElementById('rppph'+iddt);
    rph.value=rppersn;
    
    
    if(stat==1){
        totSmRupiah=parseFloat(totRupiah)+parseFloat(rppersn);
    }else{        
        totSmRupiah=parseFloat(totRupiah)-parseFloat(rppersn);
    }
    if(isNaN(totSmRupiah)){
        totSmRupiah=0;
    }
    rph2=document.getElementById('totdgnpph'+iddt);
    rph2.value=totSmRupiah;

    change_number(stu);
    change_number(rph);
    change_number(rph2);
}
function saveDet(totRow){
    var strUrl2='';
    var unitmill='';
    tgldt=document.getElementById('tgl').value;
    notrans=document.getElementById('notransaksi').value;
    rgid=document.getElementById('unit');
    unitmill=rgid.options[rgid.selectedIndex].value;
    suppHeader=document.getElementById('suppId');
    suppHeader=suppHeader.options[suppHeader.selectedIndex].value;
     
    param='method=saveAll'+'&tgldt='+tgldt+'&unitmill='+unitmill+'&notransaksi='+notrans;
    param+='&suppId='+suppHeader;
    for(i=1;i<=totRow;i++){
        try{
            if(strUrl2 != ''){                   
                strUrl2 +='&kdcust[]='+trim(document.getElementById('kdcust'+i).value)
                +'&harga[]='+encodeURIComponent(remove_comma_var(document.getElementById('harga'+i).value))
                +'&subsidi[]='+encodeURIComponent(remove_comma_var(document.getElementById('subsidi'+i).value))
                +'&klasifikasi[]='+encodeURIComponent(remove_comma_var(document.getElementById('klasifikasi'+i).innerHTML))
                +'&beratnormal[]='+encodeURIComponent(remove_comma_var(document.getElementById('beratnormal'+i).innerHTML))
                +'&statusPajak[]='+encodeURIComponent(trim(document.getElementById('optpph'+i).options[document.getElementById('optpph'+i).selectedIndex].value))
                +'&persenpph[]='+document.getElementById('persenpph'+i).value
                +'&rppph[]='+encodeURIComponent(remove_comma_var(document.getElementById('rppph'+i).value))
                +'&totalharga[]='+encodeURIComponent(remove_comma_var(document.getElementById('totalharga'+i).value));
            }
            else{
                strUrl2 ='&kdcust[]='+trim(document.getElementById('kdcust'+i).value)
                +'&harga[]='+encodeURIComponent(remove_comma_var(document.getElementById('harga'+i).value))
                +'&subsidi[]='+encodeURIComponent(remove_comma_var(document.getElementById('subsidi'+i).value))
                +'&klasifikasi[]='+encodeURIComponent(trim(document.getElementById('klasifikasi'+i).innerHTML))
                +'&beratnormal[]='+encodeURIComponent(remove_comma_var(document.getElementById('beratnormal'+i).innerHTML))
                +'&statusPajak[]='+encodeURIComponent(trim(document.getElementById('optpph'+i).options[document.getElementById('optpph'+i).selectedIndex].value))
                +'&persenpph[]='+document.getElementById('persenpph'+i).value
                +'&rppph[]='+encodeURIComponent(remove_comma_var(document.getElementById('rppph'+i).value))
                +'&totalharga[]='+encodeURIComponent(remove_comma_var(document.getElementById('totalharga'+i).value));
            }

        }
        catch(e){}
    }
    param+=strUrl2;
            tujuan='pmn_slave_saldoakhirramp.php';
            post_response_text(tujuan, param, respog);
            function respog(){
              if(con.readyState==4){
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {                                                         
                                    displaylist();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              } 
            } 
}
function deletehead(unit,koderamp,tanggal){
    param='method=deleteData'+'&unit='+unit+'&ramp='+koderamp+'&tgl='+tanggal;
    tujuan='pmn_slave_saldoakhirramp.php';
    if(confirm("Anda Yakin Menghapus Data Ini?"))
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
					displaylist();
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

function edit(unit,ramp,tgl,saldo,notrans){
    document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	// document.getElementById('detail').style.display='block';
	document.getElementById('tanggal').value=tgl;
	document.getElementById('notransaksi').value=notrans;
	document.getElementById('hrgkgAll').value=saldo;
	
	idr=document.getElementById('unit');
	for(a=0;a<idr.length;a++)
	{
		if(idr.options[a].value==unit)
		{
			idr.options[a].selected=true;
		}
	}
	getkoderamp(ramp);
	lockFormDt();
}

function posting(notrans){
    param='method=postData'+'&notransaksi='+notrans;
    tujuan='pmn_slave_saldoakhirramp.php';
    if(confirm("Anda Ingin Memposting Transaksi ini?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
      if(con.readyState==4){
            if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else {                                                         
                            displaylist();
                    }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      } 
    } 
}
function printData(notrans,millcode,tglnormal,suppId,ev) {
    // Prep Param
    param = "method=excel"+"&notransaksi="+notrans;
    param+="&tglnormal="+tglnormal+"&millcode="+millcode+'&suppId='+suppId;
    showDialog2('Print Excel',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='pmn_slave_saldoakhirramp.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic2');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function itungSemua(){
    jmlhrow=document.getElementById('jmlhRow').value;
    optpph=document.getElementById('bbnPajak');
    optpph=optpph.options[optpph.selectedIndex].value;
    persenpph=document.getElementById('prsnAll').value;
    hrgkgAll=document.getElementById('hrgkgAll').value;
    subsidiangkut=document.getElementById('subsidiangkut').value;
    for(var i=1;i<=jmlhrow;i++){
        dor=document.getElementById('optpph'+i);
        for(a=0;a<dor.length;a++){
            if(dor.options[a].value==optpph)
                {
                    dor.options[a].selected=true;
                }
        }
        document.getElementById('persenpph'+i).value=persenpph
        document.getElementById('harga'+i).value=hrgkgAll
        document.getElementById('subsidi'+i).value=subsidiangkut
        getRup(i);       
    }
}
function lockFormDt(){
    document.getElementById('tanggal').disabled=true;
    document.getElementById('unit').disabled=true;
    document.getElementById('ramp').disabled=true;
}
function unLockForm(){
    document.getElementById('tanggal').disabled=false;
    document.getElementById('unit').disabled=false;
    document.getElementById('ramp').disabled=false;
}