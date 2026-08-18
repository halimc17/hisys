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
    idr2=document.getElementById('statJrnl');
    for(a=0;a<idr2.length;a++){
        if(idr2.options[a].value=='')
            {
                idr2.options[a].selected=true;
            }
    }
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
    jrnl=document.getElementById('statJrnl');
    jrnl=jrnl.options[jrnl.selectedIndex].value;

    param = 'method=loaddata&page=' + num;
    param+='&millcode='+mil+'&tglcari='+tglCr+'&jurnalId='+jrnl;
    tujuan = 'keu_slave_penbytbs.php';
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
    document.getElementById('tgl').value='';    
    idr=document.getElementById('unit');
    for(a=0;a<idr.length;a++){
        if(idr.options[a].value=='')
            {
                idr.options[a].selected=true;
            }
    }
    document.getElementById('suppId').innerHTML="<option value=''>"+pild+"</option>";
    document.getElementById('listdetail').innerHTML="";
    unLockForm();
}

function cancel(){
	idr=document.getElementById('unit');
    for(a=0;a<idr.length;a++){
        if(idr.options[a].value=='')
            {
                idr.options[a].selected=true;
            }
    }
    document.getElementById('suppId').innerHTML="<option value=''>"+pild+"</option>";
    document.getElementById('notransaksi').value='';
    document.getElementById('tgl').value='';
    displaylist();
    unLockForm();
}

function savehead(){
    unit=document.getElementById('unit');
    unit=unit.options[unit.selectedIndex].value;
    tgl=document.getElementById('tgl').value;
    suppId=document.getElementById('suppId');
    suppId=suppId.options[suppId.selectedIndex].value;
    param = 'method=notran' + '&unit=' + unit + '&tgl=' + tgl;
    param+='&suppId='+suppId;
    tujuan = 'keu_slave_penbytbs.php';
    if(suppId==''){
        alert("Supplier Kosong");
        return;
    }else{
        post_response_text(tujuan, param, respon);
        function respon(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    }
                    else {
                        document.getElementById('detail').style.display='block';
                        detail(unit,tgl,suppId);
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
}
function detail(unit,tgl,suppId){
    param = 'method=detail' + '&millcode=' + unit + '&tglnormal=' + tgl;
    param+='&suppId='+suppId;
    hrgPerkg=document.getElementById('hrgkgAll').value;
    bonuskgAll=document.getElementById('bonuskgAll').value;
    subsidiangkut=document.getElementById('subsidiangkut').value;
	if(subsidiangkut=='')
	{
		subsidiangkut = 0;
	}
    prsnAll=document.getElementById('prsnAll').value;
    bbnPajak=document.getElementById('bbnPajak');
    bbnPajak=bbnPajak.options[bbnPajak.selectedIndex].value;
    param+='&hrgkgAll='+hrgPerkg+'&prsnAll='+prsnAll+'&bonuskgAll='+bonuskgAll+'&bbnPajak='+bbnPajak+'&subsidiangkut='+subsidiangkut;
    //alert(param);
    tujuan = 'keu_slave_penbytbs.php';
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
    " src='keu_slave_penbytbs.php?"+param+"'></iframe>",'1050','400',ev); 
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function getPabrik(regid,kdpabrik){
    tgl=document.getElementById('tgl').value;
    if(regid==0){
        rgid=document.getElementById('unit');
        rgid=rgid.options[rgid.selectedIndex].value;
        param='kdPabrik='+rgid+'&method=getPabrik';
        param+='&tgl='+tgl;
    }else{
        param='kdPabrik='+regid+'&method=getPabrik';
        param+='&kdcust='+kdpabrik
        param+='&tgl='+tgl;
    }
    if(tgl==''){
            alert("Tanggal Kosong");
            return;
    }else{
        tujuan = 'keu_slave_penbytbs.php';
        post_response_text(tujuan, param, respon);
            function respon(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                        }
                        else {
                            document.getElementById('suppId').innerHTML=con.responseText;
							getpricetbs();
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
    
}
function getRup(iddt){
    jmlhfisik=document.getElementById('beratnormal'+iddt);
    jmlhRupiah=document.getElementById('harga'+iddt);
    jmlhbonus=document.getElementById('bonus'+iddt);
    jmlhSubsidi=document.getElementById('subsidi'+iddt);
	if(jmlhSubsidi=='')
	{
		jmlhSubsidi=0;
	}

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
    totRupiah=(parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhRupiah.value))+parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhSubsidi.value))+parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhbonus.value)));
    totRupiahtnpbonus=(parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhRupiah.value))+parseFloat(remove_comma_var(jmlhfisik.innerHTML))*parseFloat(remove_comma_var(jmlhSubsidi.value)));
    if(isNaN(totRupiah)){
        totRupiah=0;
        totRupiahtnpbonus=0;
    }
    

    //rupiah pph22
	if(stat==1)
	{
		rppersn=(parseFloat(totRupiahtnpbonus) * (100/(100- parseFloat(persen)))) - parseFloat(totRupiahtnpbonus);
	}
	else
	{
		rppersn=parseFloat(totRupiahtnpbonus)*(parseFloat(persen)/100);
	}
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
                +'&bonus[]='+encodeURIComponent(remove_comma_var(document.getElementById('bonus'+i).value))
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
                +'&bonus[]='+encodeURIComponent(remove_comma_var(document.getElementById('bonus'+i).value))
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
            tujuan='keu_slave_penbytbs.php';
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
function deletehead(notrans){
    param='method=deleteData'+'&notransaksi='+notrans;
    tujuan='keu_slave_penbytbs.php';
    if(confirm("Anda Yakin Menghapus Data Ini?")){
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
function edit(notrans,regional,tgl,tglnormal,kdmill,suppId,hrgPerkg,subsidi,bbnPajak,persenpph,bonusperkg){
    param='method=detail'+'&notransaksi='+notrans+'&tglnormal='+tglnormal;
    param+='&millcode='+kdmill+'&tgl='+tgl+'&regional='+regional+'&suppId='+suppId;
    tujuan='keu_slave_penbytbs.php';
    post_response_text(tujuan, param, respog);    
    function respog(){
      if(con.readyState==4){
            if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else {                                                         
                            document.getElementById('header').style.display = 'block';
                            document.getElementById('listdata').style.display = 'none';
                            document.getElementById('detail').style.display='block';
                            document.getElementById('tgl').value=tglnormal;
                            document.getElementById('notransaksi').value=notrans;
                            document.getElementById('hrgkgAll').value=hrgPerkg;
                            document.getElementById('bonuskgAll').value=bonusperkg;
                            document.getElementById('subsidiangkut').value=subsidi;
                            document.getElementById('prsnAll').value=persenpph;
                            bbnPjk=document.getElementById('bbnPajak');
                            for(a=0;a<bbnPjk.length;a++){
                                if(bbnPjk.options[a].value==bbnPajak){
                                        bbnPjk.options[a].selected=true;
                                }
                            }
                            document.getElementById('listdetail').innerHTML=con.responseText;
                            idr=document.getElementById('unit');
                            for(a=0;a<idr.length;a++){
                                if(idr.options[a].value==kdmill)
                                    {
                                        idr.options[a].selected=true;
                                    }
                            }
                            getPabrik(kdmill,suppId);
                            lockFormDt();
                    }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      } 
    } 




}
function posting(notrans){
    param='method=postData'+'&notransaksi='+notrans;
    tujuan='keu_slave_penbytbs.php';
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
        " src='keu_slave_penbytbs.php?"+param+"'></iframe>",'800','400',ev);
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
    if(subsidiangkut=='')
	{
		subsidiangkut=0;
	}
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
    document.getElementById('tgl').disabled=true;
    document.getElementById('unit').disabled=true;
    document.getElementById('suppId').disabled=true;
    document.getElementById('savehead').disabled=true;
}
function unLockForm(){
    document.getElementById('tgl').disabled=false;
    document.getElementById('unit').disabled=false;
    document.getElementById('suppId').disabled=false;   
    document.getElementById('savehead').disabled=false;
}

function getpricetbs(){
	tgl = document.getElementById('tgl').value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	suppId = document.getElementById('suppId').options[document.getElementById('suppId').selectedIndex].value;

	param = 'method=getpricetbs&unit=' + unit + '&suppId=' + suppId+'&tgl='+tgl;
	tujuan = 'keu_slave_penbytbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('hrgkgAll').value = isdt[0];
					document.getElementById('subsidiangkut').value = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formapp(notransaksi,unit){
    content= "<div id=formapp style=\"height:100%;width:100%;\"></div>";
    title='Approval';
    height='';
    width='';
    showDialog1(title,content,width,height,'event');
    
    param = "method=formapp&notransaksi="+notransaksi+"&unit="+unit;
    tujuan = 'keu_slave_penbytbs.php';
    post_response_text(tujuan, param, respog); 
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   document.getElementById('formapp').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
}

function Submitted(notrans,unit,countapproval){
    strUrl='';
    if(countapproval=='0')
    {
        alert('Please contact administrator to setup Approval');
        return false;
    }
    else
    {
        for(i=1;i<=countapproval;i++)
        {
            persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
            if(persetujuan=='')
            {
                alert("Please complete Approval");
                return;
            }
            strUrl += '&persetujuan['+i+']='+persetujuan;
        }
    }

    param='method=Submitted'+'&notransaksi='+notrans+'&unit='+unit;
    param+=strUrl;
    tujuan='keu_slave_penbytbs.php';
    if(confirm("Anda Ingin Mengajukan Bonus Transaksi ini?")){
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
                            closeDialog();                                        
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

function simpan()
{
    param="";
    notransaksi = document.getElementById('notransaksi').value;
    tgl = document.getElementById('tgl').value;
    pabrik= document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].value;
    supplier= document.getElementById('supplier').options[document.getElementById('supplier').selectedIndex].value;
    harga = document.getElementById('harga').value;
    countapproval = document.getElementById('countapproval').value;
    method=document.getElementById('method').value;
    strUrl='';
    if(tgl==''||supplier==''||harga==''||pabrik==''||notransaksi=='')
    {
        alert('Field Was Empty');
        return false;
    }
    
    if(countapproval=='0')
    {
        alert('Please contact administrator to setup Approval');
        return false;
    }
    else
    {
        for(i=1;i<=countapproval;i++)
        {
            persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
            if(persetujuan=='')
            {
                alert("Please compelete Approval");
                return;
            }
            strUrl += '&persetujuan['+i+']='+persetujuan;
        }
    }
    
    param+='tgl='+tgl+'&supplier='+supplier+'&harga='+harga+'&pabrik='+pabrik+'&notransaksi='+notransaksi+'&method='+method;
    param+=strUrl;
    tujuan='log_slave_hargabelitbs.php';
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
                    batal();
                    loaddata(0);
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