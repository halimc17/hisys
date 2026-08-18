function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('detail').style.display = 'none';
    // document.getElementById('persetujuan').style.display = 'none';
    document.getElementById('listdata').style.display = 'none';
    cancelheader();  
}

function cancelheader(){
    // document.getElementById("kodeorg").value = "";
    document.getElementById("nmLaporan").value = "";
    document.getElementById("ketDt1").value = "";
    document.getElementById("kodeorg").disabled = false;
    document.getElementById("nmLaporan").disabled = false;
}

function displayList(){
    // document.getElementById('oldNourut').value='';
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('namalaporan').value='';
    loadData(0);
}

function saveData(){
    // unit    = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    kdHo  = document.getElementById('kodeorg').value;
    method  = document.getElementById('method').value;
    nmLaporan=document.getElementById('nmLaporan').value;
    ket1=document.getElementById('ketDt1').value;
	// ket2=document.getElementById('ketDt2').value;
    // ket3=document.getElementById('ketDt3').value;
    // ket4=document.getElementById('ketDt4').value;
    // ket5=document.getElementById('ketDt5').value;
    // ket6=document.getElementById('ketDt6').value;
    param   ='';
    param  +='&ket1='+ket1;
    //param  +='&ket2='+ket2+'&ket3='+ket3;
    //param  +='&ket4='+ket4+'&ket5='+ket5+'&ket6='+ket6;
    param  +='&kdHo='+kdHo+'&method='+method;
    param  +='&nmLaporan='+nmLaporan;
    tujuan  ='keu_slave_5mesinlaporan.php';
    post_response_text(tujuan, param, respog);

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                }else{
                    //alert(con.responseText);
                    //document.getElementById('notrans').value=trim(con.responseText);
                    detail(nmLaporan);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}

function detail(isinmLaporan){
    param='nmLaporan='+isinmLaporan+'&method=detail';
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function saveDetHead(){
    tipeDt=document.getElementById('tipeDt').value;
    datadt=document.getElementById('datadt').value;
    nmlap=document.getElementById('namaLaporanDt').value;
    nourut=document.getElementById('nourut').value;
    ketdata=document.getElementById('ketdata').value;
    colspandt=document.getElementById('colspandt').value;
    oldNourut=document.getElementById('oldNourut').value;
    kdOrg=document.getElementById('kodeorg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
	posisidt=document.getElementById('posisidt').value;
	
	detaildt=document.getElementById('detaildt').value;
	tampildt=document.getElementById('tampildt').value;
	methoddt=document.getElementById('methoddt').value;
	tipeunitdt=document.getElementById('tipeunitdt').value;
	
    param='method=saveDetHead'+'&namaLaporanDt='+nmlap+'&nourut='+nourut+'&kdOrg='+kdOrg+'&tipeDt='+tipeDt+'&datadt='+datadt+'&methoddt='+methoddt;
    param+='&ketdata='+ketdata+'&colspandt='+colspandt+'&oldNourut='+oldNourut+'&detaildt='+detaildt+'&tampildt='+tampildt+'&tipeunitdt='+tipeunitdt+'&posisidt='+posisidt;
    
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    detail(nmlap);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editdet(nourut,ketDt,colspan,tipeDt,datadt,detaildt,tampildt,tipeunitdt,posisidt){
    // document.getElementById('nourut').disabled=true;
    document.getElementById('oldNourut').value=nourut;
    document.getElementById('nourut').value=nourut;
    document.getElementById('ketdata').value=ketDt;
    document.getElementById('colspandt').value=colspan;
    document.getElementById('datadt').value=datadt;
	document.getElementById('tipeDt').value=tipeDt;
	document.getElementById('detaildt').value=detaildt;
	document.getElementById('tampildt').value=tampildt;
	document.getElementById('tipeunitdt').value=tipeunitdt;
	document.getElementById('posisidt').value=posisidt;
	document.getElementById('methoddt').value='updatedt';
}
function delDet(kdOrg,nourut,nmLaporan){
    param='method=delDetHead'+'&namaLaporanDt='+nmLaporan+'&nourut='+nourut+'&kdOrg='+kdOrg;
    tujuan='keu_slave_5mesinlaporan.php';
    // if(confirm("Anda Yakin?")){
    //     post_response_text(tujuan, param, respog);    
    // }else{
    //     return;
    // }
    alertify.confirm("Infomation","Anda Yakin?",
		function(){
			post_response_text(tujuan, param, respog);  
		},
		function(){
			return;
		}
	);
    
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                   alertify.alert("Informasi",con.responseText);
                } else {
                    detail(nmLaporan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editht(notrans,unit){
    document.getElementById('nmLaporan').value=notrans;
    document.getElementById('nmLaporan').disabled=true;
    document.getElementById('ketDt1').value=unit;
    document.getElementById('listdata').style.display='none';
    document.getElementById('header').style.display='block';
    detail(notrans);
}

 
function form(titledt){
    width = '780px';
    height = 'auto';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd style=width:780px></div></fieldset>";
    ev = 'event';
    title = titledt;//"Detail HTML";
    showDialog1(title, content, width, height, ev); 
}


function viewdetail(kdOrg,nourut,nmLaporan){
    titl="Detail Induk :"+nourut+",Nama Laporan :"+nmLaporan;
    form(titl);
    param='method=viewdetail'+'&namaLaporanDt='+nmLaporan+'&nourut='+nourut+'&kdOrg='+kdOrg;
    tujuan = 'keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                }
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
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


function saveDetDetail(){
    nmlap=document.getElementById('namaLaporanDt').value;
    nourutdt=document.getElementById('nourutdt').value;
    nourutht=document.getElementById('nourutht').value;
    ketdt=document.getElementById('ketdt').value;
    colsdt=document.getElementById('colsdt').value;
    datadt=document.getElementById('datadt').value;
    statusdt=document.getElementById('statusdt');
    statusdt=statusdt.options[statusdt.selectedIndex].value;
    tipeDtdt=document.getElementById('tipeDtdt');
    tipeDtdt=tipeDtdt.options[tipeDtdt.selectedIndex].value;
    noakundari=document.getElementById('noakundari');
    noakundari=noakundari.options[noakundari.selectedIndex].value;
    noakunsampai=document.getElementById('noakunsampai');
    noakunsampai=noakunsampai.options[noakunsampai.selectedIndex].value;
    kdOrg=document.getElementById('kodeorg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
    param='method=saveDetDetail'+'&namaLaporanDt='+nmlap+'&nourut='+nourutdt+'&nourutht='+nourutht+'&kdOrg='+kdOrg;
    param+='&ketdata='+ketdt+'&colspandt='+colsdt+'&tipeDtdt='+tipeDtdt+'&noakundari='+noakundari+'&noakunsampai='+noakunsampai;
    param+='&datadt='+datadt+'&statusdt='+statusdt;
    
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    viewdetail(kdOrg,nourutht,nmlap);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editdetdt(nourutht,nourut,tipe,ketDt,nodari,nosampai,data,status,colspan){
    document.getElementById('nourutht').value=nourutht;
    document.getElementById('nourutdt').value=nourut;
    document.getElementById('tipeDtdt').value=tipe;
    document.getElementById('ketdt').value=ketDt;
    document.getElementById('noakundari').value=nodari;
    document.getElementById('noakunsampai').value=nosampai;
    document.getElementById('datadt').value=data;
    document.getElementById('statusdt').value=status;
    document.getElementById('colsdt').value=colspan;

    ubahdt();
}
function delDetdt(nourutht,kdOrg,nourut,nmLaporan){
    param='method=delDetdt'+'&namaLaporanDt='+nmLaporan+'&nourut='+nourut+'&nourutht='+nourutht+'&kdOrg='+kdOrg;
    tujuan='keu_slave_5mesinlaporan.php';
    // if(confirm("Anda Yakin?")){
    //     post_response_text(tujuan, param, respog);    
    // }else{
    //     return;
    // }
    alertify.confirm("Infomation","Anda Yakin?",
		function(){
			post_response_text(tujuan, param, respog);  
		},
		function(){
			return;
		}
	);
    
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                   alertify.alert("Informasi",con.responseText);
                } else {
                    viewdetail(kdOrg,nourutht,nmLaporan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ubahdt(){
    statusdt=document.getElementById('statusdt');
    statusdt=statusdt.options[statusdt.selectedIndex].value;
    if (statusdt==1){
        document.getElementById('noakundari').value='';
        document.getElementById('noakundari').disabled=true;
        document.getElementById('noakunsampai').value='';
        document.getElementById('noakunsampai').disabled=true;
    }else{
        document.getElementById('noakundari').disabled=false;
        document.getElementById('noakunsampai').disabled=false;
    }
}

function searchBrg(title,content,ev)
{
    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    //alert('asdasd');
}

function findBrg(){
    txt=trim(document.getElementById('no_brg').value);
    if(txt==''){
        alertify.alert("Informasi",'Text is obligatory');
    }else if(txt.length<1){
        alertify.alert("Informasi",'Too short words');
    }else{
        param='txtfind='+txt+'&method=cariBarangDlmDtBs';
        // alertify.alert("Informasi",param);
        // return;
        tujuan='keu_slave_5mesinlaporan.php';
        post_response_text(tujuan, param, respog);
    }

    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert("Informasi",con.responseText);
                } else {
                        //alert(con.responseText);
                        document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }      
}

function setBrg(kdbrg,nmbrg){
     document.getElementById('kdbrg').value=kdbrg;
     document.getElementById('nmbrg').value=nmbrg;
     closeDialog();
}

function savedetail(){
    notrans=document.getElementById('notrans').value;
    kdbrg=document.getElementById('kdbrg').value;
    jumlah=document.getElementById('jumlah').value;
    hrgsatuan=document.getElementById('hrgsatuan').value;
    tgleta=document.getElementById('tgleta').value;
    catatan=document.getElementById('catatan').value;
    method=document.getElementById('methoddt').value;

    param='kdbrg='+kdbrg+'&jumlah='+jumlah+'&hrgsatuan='+hrgsatuan+'&tgleta='+tgleta+'&catatan='+catatan+'&notrans='+notrans;
    param+='&method='+method;
    //alert(param);
    tujuan='log_slave_pengajuan_formcapex.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    cleardt();
                    loaddatadetail(notrans);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardt()
{
    document.getElementById('kdbrg').value='';
    document.getElementById('nmbrg').value='';
    document.getElementById('jumlah').value='';
    document.getElementById('hrgsatuan').value='';
    document.getElementById('tgleta').value='';
    document.getElementById('catatan').value='';
    document.getElementById('methoddt').value='insertdt'
}

function loaddatadetail(notrans){
    param = 'method=loaddatadetail';
    param += '&namalaporan=' +notrans;
    tujuan = 'keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                }
                else {
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
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

function deldt(notrans,kdbrg)
{
    //alertify.alert("Informasi",'masukk');
    param='method=deldt'+'&notrans='+notrans+'&kdbrg='+kdbrg;
    //alertify.alert("Informasi",param);
    tujuan='log_slave_pengajuan_formcapex.php';
    // if(confirm(' Anda yakin ingin menghapus pengajuan ini?'))
    // {
    //     post_response_text(tujuan, param, respog);  
    // }
    alertify.confirm("Infomation","Anda yakin ingin menghapus pengajuan ini?",
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
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alertify.alert("Informasi",con.responseText);
                                    }
                                    else 
                                    {
                                       loaddatadetail(notrans);
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
}

function editdt(notrans,kdbrg,nmbrg,jumlah,hrgsatuan,tgleta,catatan){
    document.getElementById('kdbrg').value=kdbrg;
    document.getElementById('nmbrg').value=nmbrg;
    document.getElementById('jumlah').value=jumlah;
    document.getElementById('hrgsatuan').value=hrgsatuan;
    document.getElementById('tgleta').value=tgleta;
    document.getElementById('catatan').value=catatan;
	    
    document.getElementById('methoddt').value='updatedt';
    loaddatadetail(notrans);
}

function formpersetujuan(){
    notrans=document.getElementById('notrans').value;
    param='method=formpersetujuan'+'&notrans='+notrans;
    //alertify.alert("Informasi",param);
    tujuan='log_slave_pengajuan_formcapex.php';
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alert("Informasi",con.responseText);
                }
                else
                {
                    document.getElementById('persetujuan').style.display = 'block';
                    document.getElementById('detail').style.display = 'none';
                    document.getElementById('header').style.display = 'none';
                    document.getElementById('persetujuan').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }  
        post_response_text(tujuan, param, respog);  
}

function simpan(subtotal) {
    notrans=document.getElementById('notrans').value;
    diperiksa1=document.getElementById('diperiksa1').value;
    diperiksa2=document.getElementById('diperiksa2').value;
    budget=document.getElementById('budget').value;
    menyetujui1=document.getElementById('menyetujui1').value;
    method=document.getElementById('methodht').value;

    param='diperiksa1='+diperiksa1+'&diperiksa2='+diperiksa2+'&budget='+budget+'&menyetujui1='+menyetujui1+'&notrans='+notrans;
    param+='&method='+method;

    if (subtotal>50000000){
    menyetujui2=document.getElementById('menyetujui2').value;
    param+='&menyetujui2='+menyetujui2;
    }
    //alertify.alert("Informasi",param);
    tujuan='log_slave_pengajuan_formcapex.php';
    post_response_text(tujuan, param, respon);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    cancel(subtotal);
                    cancelheader();
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    //post_response_text('keu_slave_tagihan.php?proses=add', param, respon);
}

function cancel(subtotal){
    document.getElementById("diperiksa1").selectedIndex = "0";
    document.getElementById("diperiksa2").selectedIndex = "0";
    document.getElementById("budget").selectedIndex = "0";
    document.getElementById("menyetujui1").selectedIndex = "0";
    if (subtotal>50000000){
    document.getElementById("menyetujui2").selectedIndex = "0";
    }
}

function exceldetail(namalaporan) {
	param = 'method=exceldetail' + '&namalaporan=' + namalaporan;
	tujuan='keu_slave_5mesinlaporan.php';
	printnopopup(tujuan+'?'+param);
	/*
	param = 'method=exceldetail' + '&namalaporan=' + namalaporan;
	tujuan='keu_slave_5mesinlaporan.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
	*/
}

function loadData(num){
    namalaporan=document.getElementById('namalaporan').value;

    param='method=loadData';
    param+='&page='+num;

    if (namalaporan != '') {
        param += '&namalaporan=' + namalaporan;
    }
    
    tujuan='keu_slave_5mesinlaporan.php';
    // alertify.alert("Informasi",param);
    // return;
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert("Informasi",con.responseText);
                }else{
                    //alertify.alert("Informasi",con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
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

function delht(notrans)
{
    param='method=delht'+'&notrans='+notrans;
    tujuan='log_slave_pengajuan_formcapex.php';
    // if(confirm(' Anda yakin ingin menghapus pengajuan ini?'))
    // {
    //     post_response_text(tujuan, param, respog);  
    // }
    alertify.confirm("Infomation","Anda yakin ingin menghapus pengajuan ini?",
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
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert("Informasi",con.responseText);
                }
                else 
                {
                   displayList();  
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}























////////


//baru

function viewdetailbaru(kdOrg,nourut,nmLaporan){
    titl="Detail COA/Aruskas ; Induk :"+nourut+",Nama Laporan :"+nmLaporan;
    // form(titl);
    param='method=viewdetailbaru'+'&namaLaporanDt='+nmLaporan+'&nourut='+nourut+'&kdOrg='+kdOrg;
    tujuan = 'keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                }
                else
                {
                    // document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
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



function viewdetailbarukodejurnal(kdOrg,nourut,nmLaporan){
    titl="Detail Kodejurnal ; Induk :"+nourut+",Nama Laporan :"+nmLaporan;
    // form(titl);
    param='method=viewdetailbarukodejurnal'+'&namaLaporanDt='+nmLaporan+'&nourut='+nourut+'&kdOrg='+kdOrg;
	// alert(param);
    tujuan = 'keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                }
                else
                {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                    $(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
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


function deldt2(kodeorg2,namalaporan2,nourut2,noakun2){
   
    param='method=deldt2'+'&kodeorg2='+kodeorg2+'&namalaporan2='+namalaporan2+'&nourut2='+nourut2+'&noakun2='+noakun2;
    
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    viewdetailbaru(kodeorg2,nourut2,namalaporan2);
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




function getkodejurnal(){
	tipe3=document.getElementById('tipe3').value;
    param='method=getkodejurnal'+'&tipe3='+tipe3;
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
					  document.getElementById('kodejurnal3').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deldt3(kodeorg3,namalaporan3,nourut3,tipe3,kodejurnal3){
   
    param='method=deldt3'+'&kodeorg3='+kodeorg3+'&namalaporan3='+namalaporan3+'&nourut3='+nourut3+'&tipe3='+tipe3+'&kodejurnal3='+kodejurnal3;
    tujuan='keu_slave_5mesinlaporan.php';
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
                    alertify.alert("Informasi",con.responseText);
                } else {
                    viewdetailbarukodejurnal(kodeorg3,nourut3,namalaporan3);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function savedt2(){
    kodeorg2=document.getElementById('kodeorg2').value;
    namalaporan2=document.getElementById('namalaporan2').value;
    nourut2=document.getElementById('nourut2').value;
    noakun2=document.getElementById('noakun2').value;
    keterangan2=document.getElementById('keterangan2').value;
   
    param='method=savedt2'+'&kodeorg2='+kodeorg2+'&namalaporan2='+namalaporan2+'&nourut2='+nourut2+'&noakun2='+noakun2+'&keterangan2='+keterangan2;
    
    tujuan='keu_slave_5mesinlaporan.php';
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
                } else {
                    viewdetailbaru(kodeorg2,nourut2,namalaporan2);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function savedt3(){
    kodeorg3=document.getElementById('kodeorg3').value;
    namalaporan3=document.getElementById('namalaporan3').value;
    nourut3=document.getElementById('nourut3').value;
    kodejurnal3=document.getElementById('kodejurnal3').value;
    tipe3=document.getElementById('tipe3').value;
    param='method=savedt3'+'&kodeorg3='+kodeorg3+'&namalaporan3='+namalaporan3+'&nourut3='+nourut3+'&kodejurnal3='+kodejurnal3+'&tipe3='+tipe3;
    tujuan='keu_slave_5mesinlaporan.php';
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
                } else {
                    viewdetailbarukodejurnal(kodeorg3,nourut3,namalaporan3);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
