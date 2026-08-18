function simpan(types)
{
    var statuslokasi;


    var notransaksi = document.getElementById('notrans').value;
    var kodeorg = document.getElementById('kodeorg').value;
    var provinsi = document.getElementById('provinsi').value;
    var kabupaten = document.getElementById('kabupaten').value;
    var kecamatan = document.getElementById('kecamatan').value;
    var desa = document.getElementById('desa').value;

    var tanggalmulai = document.getElementById('tanggalmulai_from').value;
    var tanggalselesai = document.getElementById('tanggalmulai_until').value;


    var jumlahtenagakerja = document.getElementById('jumlahtenagakerja').value;
    var satuantenagakerja = document.getElementById('satuantenagakerja').value;
    var hargatenagakerja = remove_comma_var(document.getElementById('hargatenagakerja').value);
    var hktenagakerja = document.getElementById('hktenagakerja').value;
    var subtotaltenagakerja = remove_comma_var(document.getElementById('subtotaltenagakerja').value);
    var keterangantenagakerja = document.getElementById('keterangantenagakerja').value;


    var jumlahkonsumsisurveyor = document.getElementById('jumlahkonsumsisurveyor').value;
    var satuankonsumsisurveyor = document.getElementById('satuankonsumsisurveyor').value;
    var hargakonsumsisurveyor = remove_comma_var(document.getElementById('hargakonsumsisurveyor').value);
    var hkkonsumsisurveyor = document.getElementById('hkkonsumsisurveyor').value;
    var subtotalkonsumsisurveyor = remove_comma_var(document.getElementById('subtotalkonsumsisurveyor').value);
    var keterangankonsumsisurveyor = document.getElementById('keterangankonsumsisurveyor').value;

    var jumlahkonsumsitkr = document.getElementById('jumlahkonsumsitkr').value;
    var satuankonsumsitkr = document.getElementById('satuankonsumsitkr').value;
    var hargakonsumsitkr = remove_comma_var(document.getElementById('hargakonsumsitkr').value);
    var hkkonsumsitkr = document.getElementById('hkkonsumsitkr').value;
    var subtotalkonsumsitkr = remove_comma_var(document.getElementById('subtotalkonsumsitkr').value);
    var keterangankonsumsitkr = document.getElementById('keterangankonsumsitkr').value;

    var jumlahtransport = document.getElementById('jumlahtransport').value;
    var satuantransport = document.getElementById('satuantransport').value;
    var hargatransport = remove_comma_var(document.getElementById('hargatransport').value);
    var hktransport = document.getElementById('hktransport').value;
    var subtotaltransport = remove_comma_var(document.getElementById('subtotaltransport').value);
    var keterangantransport = document.getElementById('keterangantransport').value;

    var jumlahbbm = document.getElementById('jumlahbbm').value;
    var satuanbbm = document.getElementById('satuanbbm').value;
    var hargabbm = remove_comma_var(document.getElementById('hargabbm').value);
    var hkbbm = document.getElementById('hkbbm').value;
    var subtotalbbm = remove_comma_var(document.getElementById('subtotalbbm').value);
    var keteranganbbm = document.getElementById('keteranganbbm').value;

    var jumlahbcd = document.getElementById('jumlahbcd').value;
    var satuanbcd = document.getElementById('satuanbcd').value;
    var hargabcd = remove_comma_var(document.getElementById('hargabcd').value);
    var hkbcd = document.getElementById('hkbcd').value;
    var subtotalbcd = remove_comma_var(document.getElementById('subtotalbcd').value);
    var keteranganbcd = document.getElementById('keteranganbcd').value;


    var penjelasan = document.getElementById('penjelasan').value;


    var Total = remove_comma_var(document.getElementById('Total').value);


    var jlhsurveyor= parseInt(document.getElementById('jlhsurveyor').innerHTML);
    var jlhpendamping= parseInt(document.getElementById('jlhpendamping').innerHTML);
    var jlhsaksi= parseInt(document.getElementById('jlhsaksi').innerHTML);
    var jlhalat= parseInt(document.getElementById('jlhalat').innerHTML);
    var namasurveyor='';
    var namapendamping='';
    var namasaksi='';
    var jenisalat='';
    var kodetipe='';

    if(document.getElementById('checkstatus').checked==true)
    {
        statuslokasi=1;
    }
    else
    {
        statuslokasi=0;
    }
    nox=0;
    while(document.getElementById('kodetipe_'+nox))
    {
        if(document.getElementById('checktipe_'+nox).checked==true)
        {
            if(kodetipe=='')
            {
                kodetipe=document.getElementById('kodetipe_'+nox).innerHTML;
            }
            else
            {
                kodetipe+="###"+document.getElementById('kodetipe_'+nox).innerHTML;
            }
        }
        nox++;
    }
    for(i=0;i<=jlhsurveyor;i++)
    {   
        if(document.getElementById('listdet_surveyor_'+i))
        {
            if(namasurveyor=='')
            {
                namasurveyor=trim(document.getElementById('listiddet_surveyor_'+i).innerHTML);
                namasurveyor+="/"+trim(document.getElementById('listteamdet_surveyor_'+i).innerHTML);
            }
            else
            {
                namasurveyor+="###"+trim(document.getElementById('listiddet_surveyor_'+i).innerHTML);
                namasurveyor+="/"+trim(document.getElementById('listteamdet_surveyor_'+i).innerHTML);
            }
        }
    }
    for(i=0;i<=jlhpendamping;i++)
    {   
        if(document.getElementById('listdet_pendamping_'+i))
        {
            if(namapendamping=='')
            {
                namapendamping=trim(document.getElementById('listiddet_pendamping_'+i).innerHTML);
                namapendamping+="/"+trim(document.getElementById('listteamdet_pendamping_'+i).innerHTML);
            }
            else
            {
                namapendamping+="###"+trim(document.getElementById('listiddet_pendamping_'+i).innerHTML);
                namapendamping+="/"+trim(document.getElementById('listteamdet_pendamping_'+i).innerHTML);      }
        }
    }

    for(i=0;i<=jlhsaksi;i++)
    {   
        if(document.getElementById('listdet_saksi_'+i))
        {
            if(namasaksi=='')
            {
                namasaksi=trim(document.getElementById('listdet_saksi_'+i).innerHTML);
                namasaksi+="/"+trim(document.getElementById('listteamdet_saksi_'+i).innerHTML);
            }
            else
            {
                namasaksi+="###"+trim(document.getElementById('listdet_saksi_'+i).innerHTML);
                namasaksi+="/"+trim(document.getElementById('listteamdet_saksi_'+i).innerHTML);
            }
        }
    }

    for(i=0;i<=jlhalat;i++)
    {   
        if(document.getElementById('listdet_alat_'+i))
        {
            if(jenisalat=='')
            {
                jenisalat=trim(document.getElementById('listdet_alat_'+i).innerHTML);
                jenisalat+='/'+trim(document.getElementById('alatstatus_'+i).innerHTML);
                if(trim(document.getElementById('alatstatus_'+i).innerHTML)=='Consumable'){
                jenisalat+='/'+document.getElementById('jumlahalat_'+i).value;
                jenisalat+='/'+document.getElementById('satuanalat_'+i).value;
                jenisalat+='/'+remove_comma_var(document.getElementById('hargaalat_'+i).value);
                jenisalat+='/'+remove_comma_var(document.getElementById('subtotalalat_'+i).value);
                jenisalat+='/'+document.getElementById('keteranganalat_'+i).value;
                }
            }
            else
            {
                jenisalat+="###"+trim(document.getElementById('listdet_alat_'+i).innerHTML);
                jenisalat+='/'+trim(document.getElementById('alatstatus_'+i).innerHTML);
                if(trim(document.getElementById('alatstatus_'+i).innerHTML)=='Consumable'){
                jenisalat+='/'+document.getElementById('jumlahalat_'+i).value;
                jenisalat+='/'+document.getElementById('satuanalat_'+i).value;
                jenisalat+='/'+remove_comma_var(document.getElementById('hargaalat_'+i).value);
                jenisalat+='/'+remove_comma_var(document.getElementById('subtotalalat_'+i).value);
                jenisalat+='/'+document.getElementById('keteranganalat_'+i).value;
                }
            }
        }
    }
    //alert(namasurveyor);
    var param='notransaksi='+notransaksi+'&kodetipe='+kodetipe+'&statuslokasi='+statuslokasi+'&kodeorg='+kodeorg+'&provinsi='+provinsi+'&kabupaten='+kabupaten+'&kecamatan='+kecamatan+'&desa='+desa;
    param+='&tanggalmulai='+tanggalmulai+'&tanggalselesai='+tanggalselesai+'&namasurveyor='+namasurveyor+'&namapendamping='+namapendamping+'&namasaksi='+namasaksi+'&jenisalat='+jenisalat;
    param+='&jumlahtenagakerja='+jumlahtenagakerja+'&satuantenagakerja='+satuantenagakerja+'&hargatenagakerja='+hargatenagakerja+'&hktenagakerja='+hktenagakerja+'&subtotaltenagakerja='+subtotaltenagakerja+'&keterangantenagakerja='+keterangantenagakerja;
    param+='&jumlahkonsumsisurveyor='+jumlahkonsumsisurveyor+'&satuankonsumsisurveyor='+satuankonsumsisurveyor+'&hargakonsumsisurveyor='+hargakonsumsisurveyor+'&hkkonsumsisurveyor='+hkkonsumsisurveyor+'&subtotalkonsumsisurveyor='+subtotalkonsumsisurveyor;
    param+='&keterangankonsumsisurveyor='+keterangankonsumsisurveyor+'&jumlahkonsumsitkr='+jumlahkonsumsitkr+'&satuankonsumsitkr='+satuankonsumsitkr+'&hargakonsumsitkr='+hargakonsumsitkr+'&hkkonsumsitkr='+hkkonsumsitkr+'&subtotalkonsumsitkr='+subtotalkonsumsitkr;
    param+='&keterangankonsumsitkr='+keterangankonsumsitkr+'&jumlahtransport='+jumlahtransport+'&satuantransport='+satuantransport+'&hargatransport='+hargatransport+'&hktransport='+hktransport+'&subtotaltransport='+subtotaltransport;
    param+='&keterangantransport='+keterangantransport+'&jumlahbbm='+jumlahbbm+'&satuanbbm='+satuanbbm+'&hargabbm='+hargabbm+'&hkbbm='+hkbbm+'&subtotalbbm='+subtotalbbm+'&keteranganbbm='+keteranganbbm+'&jumlahbcd='+jumlahbcd;
    param+='&satuanbcd='+satuanbcd+'&hargabcd='+hargabcd+'&hkbcd='+hkbcd+'&subtotalbcd='+subtotalbcd+'&keteranganbcd='+keteranganbcd;
    param+='&penjelasan='+penjelasan+'&Total='+Total;
    //alert(param);
    if(hktenagakerja=='0' ||namasurveyor==''||jenisalat==''||tanggalmulai==''||tanggalselesai==''||provinsi==''||kecamatan==''||kabupaten==''||desa==''||kodetipe=='')
    {
        alert('provinsi,kabupaten,kecamatan,desa,tipe survey,pekerja,alat,tanggalmulai,tanggalselesai is obligatory');
    }
    else{
    post_response_text('gis_slave_survey.php?proses='+types,param,respon);
    }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loadData();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}

function hitunghk()
{
    tanggalmulai=document.getElementById('tanggalmulai_from').value;
    tanggalselesai=document.getElementById('tanggalmulai_until').value;
    param='tanggalmulai='+tanggalmulai+'&tanggalselesai='+tanggalselesai;
    post_response_text('gis_slave_survey.php?proses=hitunghk',param,respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('hktenagakerja').value=con.responseText;
                    document.getElementById('hkkonsumsisurveyor').value=con.responseText;
                    document.getElementById('hkkonsumsitkr').value=con.responseText;
                    document.getElementById('hktransport').value=con.responseText;
                    document.getElementById('hkbbm').value=con.responseText;
                    document.getElementById('hkbcd').value=con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function html(notransaksi,kodeorg) {
    width = '';
    height = '';
    content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
    param = 'notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    tujuan = 'gis_slave_survey.php?proses=html';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contviewx').innerHTML = con.responseText;
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function edit(notransaksi)
{
    
    var container = document.getElementById('container');
    var form = document.getElementById('formdata');
    var dataform = document.getElementById('dataform');
    var param='notransaksi='+notransaksi;
   
    post_response_text('gis_slave_survey.php?proses=editform',param,respon);
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    
                    container.style.display="none";
                    form.style.display="block";
                    dataform.innerHTML=con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletes(notransaksi)
{
    var param='notransaksi='+notransaksi;
   
    post_response_text('gis_slave_survey.php?proses=deletes',param,respon);
    //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    loadData();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form_ajukan(notransaksi, kodeorg, numrow) {
    width = '400';
    height = '';
    content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog2(title, content, width, height, ev);
    param = 'notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&numrow=' + numrow;
    post_response_text('gis_slave_survey.php?proses=pengajuan', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan() {
    kepada = document.getElementById('kepada').value;
    notransaksi = document.getElementById('notran_aju').innerHTML;
    numrow = document.getElementById('numrow').value;
    param = 'notransaksi='+notransaksi+'&kepada='+kepada;
    if (kepada == '') {
        alert('Isikan nama penyetuju.');
        return;
    }
    closeDialog2();
    post_response_text('gis_slave_survey.php?proses=ajukan', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    x = document.getElementById('tr_' + numrow);
                    //alert(x.cells[8].innerHTML);
                    x.cells[7].innerHTML = '';
                    x.cells[8].innerHTML = '';
                    x.cells[11].innerHTML = '';
                    alert('Succes');
                    closeDialog2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function batal()
{
    addDataForm();
}
function tambahpekerja(types)
{
    var tenagakerjabody = document.getElementById('tenagakerjabody');
    var team = document.getElementById('team').value;
    var listpekerja = document.getElementById('list'+types);
    var namapekerja;

    nox=0;
    namapekerja = document.getElementById(types).value;
    while(document.getElementById('list_'+types+'_'+nox))
    {
        nox++;
    }


    x=0;
    var jlhs=parseInt(document.getElementById('jlhsurveyor').innerHTML);
    for(i=0;i<=jlhs;i++)
    {
    if(document.getElementById('list_surveyor_'+i))
        {

           if(parseInt(document.getElementById('listteamdet_surveyor_'+i).innerHTML)==team){
           x++;}
        }
    }

    a=0;
    var jlhs=parseInt(document.getElementById('jlhsurveyor').innerHTML);
    for(i=0;i<=jlhs;i++)
    {
    if(document.getElementById('list_surveyor_'+i))
        {

           if(parseInt(document.getElementById('listiddet_surveyor_'+i).innerHTML)==namapekerja){
           a++;}
        }
    }

    b=0;
    var jlhs=parseInt(document.getElementById('jlhpendamping').innerHTML);
    for(i=0;i<=jlhs;i++)
    {
    if(document.getElementById('list_pendamping_'+i))
        {

           if(parseInt(document.getElementById('listiddet_pendamping_'+i).innerHTML)==namapekerja){
           b++;}
        }
    }


    //alert(x);
    var param='num='+nox+'&namapekerja='+namapekerja+'&type='+types+'&team='+team;
    //alert(param);
    if(namapekerja=='')
    {
        alert('nama pekerja is obligatory');
    }
    else if(x==0 && types!='surveyor')
    {
        alert('surveyor pada team '+team+' tidak ada, silahkan isi nama surveyor terlebih dahulu');
    }
    else if(x>0 && types=='surveyor')
    {
        alert('surveyor pada team '+team+' sudah ada, hanya 1 surveyor dalam 1 team');
    }
    else if(a>0)
    {
        alert('karyawan sudah terdaftar di surveyor');
    }
    else if(b>0)
    {
        alert('karyawan sudah terdaftar di pendamping');
    }
    else
    {
    //alert(param);
    post_response_text('gis_slave_survey.php?proses=tambahpekerja',param,respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if(types=='surveyor')
                    {
                        //alert(listsurveyor);
                        document.getElementById('surveyor').value='';
                        listpekerja.innerHTML+=con.responseText;
                        if(document.getElementById('jlhsurveyor').innerHTML=='')
                        {
                        document.getElementById('jlhsurveyor').innerHTML=0;
                        document.getElementById('jlhsurveyor2').innerHTML=0;
                        }
                        else
                        {
                        document.getElementById('jlhsurveyor').innerHTML=(parseInt(document.getElementById('jlhsurveyor').innerHTML)+1);
                        document.getElementById('jlhsurveyor2').innerHTML=(parseInt(document.getElementById('jlhsurveyor2').innerHTML)+1);
                        }
                        document.getElementById('jumlahkonsumsisurveyor').value=(parseInt(document.getElementById('jumlahkonsumsisurveyor').value)+1);
                    }
                    if(types=='pendamping')
                    {
                        document.getElementById('pendamping').value='';
                        listpekerja.innerHTML+=con.responseText;
                        if(document.getElementById('jlhpendamping').innerHTML=='')
                        {
                        document.getElementById('jlhpendamping').innerHTML=0;
                        document.getElementById('jlhpendamping2').innerHTML=0;
                        }
                        else
                        {
                        document.getElementById('jlhpendamping').innerHTML=(parseInt(document.getElementById('jlhpendamping').innerHTML)+1);
                        document.getElementById('jlhpendamping2').innerHTML=(parseInt(document.getElementById('jlhpendamping2').innerHTML)+1);
                        }
                        document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)+1);
                    }
                    if(types=='saksi')
                    {
                        document.getElementById('saksi').value='';
                        listpekerja.innerHTML+=con.responseText;
                        if(document.getElementById('jlhsaksi').innerHTML=='')
                        {
                        document.getElementById('jlhsaksi').innerHTML=0;
                        document.getElementById('jlhsaksi2').innerHTML=0;
                        }
                        else
                        {
                        document.getElementById('jlhsaksi').innerHTML=(parseInt(document.getElementById('jlhsaksi').innerHTML)+1);
                        document.getElementById('jlhsaksi2').innerHTML=(parseInt(document.getElementById('jlhsaksi2').innerHTML)+1);
                        }
                        document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)+1);
                        document.getElementById('jumlahtenagakerja').value=(parseInt(document.getElementById('jumlahtenagakerja').value)+1);
                    }

                    hitungsubtotal('tenagakerja');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

   
}
function hapuspekerja(team,types,num)
{
        var jlh=document.getElementById('jlh'+types+'2').innerHTML
        var x=0;
        if(types=='surveyor')
        {
            if(confirm('Penghapusan surveyor akan menghapus seluruh anggota team, apakah anda yakin ?')){
                var jlhs=parseInt(document.getElementById('jlhpendamping').innerHTML);
                //alert(jlh);
                var jlh2=parseInt(document.getElementById('jlhsaksi').innerHTML);
                
                for(i=0;i<=jlhs;i++)
                {   
                    if(document.getElementById('list_pendamping_'+i))
                    {
                       if(parseInt(document.getElementById('listteamdet_pendamping_'+i).innerHTML)==team)
                       {
                           var thead = document.getElementById('list_pendamping_'+i);
                            thead.parentNode.removeChild(thead);
                            if(jlh==0)
                            {
                                document.getElementById('jlhpendamping').innerHTML='';
                                document.getElementById('jlhpendamping2').innerHTML='';
                            }
                            else
                            {
                                document.getElementById('jlhpendamping2').innerHTML=(parseInt(document.getElementById('jlhpendamping2').innerHTML)-1); 
                            }
                            document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)-1);
                        }
                    }
                }
                for(i=0;i<=jlh2;i++)
                {   
                    if(document.getElementById('list_saksi_'+i))
                    {
                       if(parseInt(document.getElementById('listteamdet_saksi_'+i).innerHTML)==team)
                       {
                           var thead = document.getElementById('list_saksi_'+i);
                            thead.parentNode.removeChild(thead);
                            if(jlh==0)
                            {
                                document.getElementById('jlhsaksi').innerHTML='';
                                document.getElementById('jlhsaksi2').innerHTML='';
                            }
                            else
                            {
                                document.getElementById('jlhsaksi2').innerHTML=(parseInt(document.getElementById('jlhpendamping2').innerHTML)-1); 
                            }
                            document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)-1);
                            document.getElementById('jumlahtenagakerja').value=(parseInt(document.getElementById('jumlahtenagakerja').value)-1);
                        }
                    }
                }

            }
            else
            {
                x=1;
            }
        }
        
        if(x==0){
            var thead = document.getElementById('list_'+types+'_'+num);
            thead.parentNode.removeChild(thead);
            if(jlh==0)
            {
                document.getElementById('jlh'+types).innerHTML='';
                document.getElementById('jlh'+types+'2').innerHTML='';
            }
            else
            {
                document.getElementById('jlh'+types+'2').innerHTML=(parseInt(document.getElementById('jlh'+types+'2').innerHTML)-1); 
            }

            if(types=='surveyor')
            {   
                document.getElementById('jumlahkonsumsisurveyor').value=(parseInt(document.getElementById('jumlahkonsumsisurveyor').value)-1);             
            }
            else if(types=='pendamping')
            {
                document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)-1);

            }
            else
            {
                document.getElementById('jumlahkonsumsitkr').value=(parseInt(document.getElementById('jumlahkonsumsitkr').value)-1);
                document.getElementById('jumlahtenagakerja').value=(parseInt(document.getElementById('jumlahtenagakerja').value)-1);
            }

            
            hitungsubtotal('tenagakerja');}
        
}
function hitungsubtotal(type,num)
{
    var jumlah;
    var hk;
    var harga;
    var subtotal;

    if(type=='tenagakerja')
    {
        jumlah = parseInt(document.getElementById('jumlahtenagakerja').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargatenagakerja').value));
        hk= parseInt(document.getElementById('hktenagakerja').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotaltenagakerja').value=numberFormat(subtotal);
        hitungsubtotal('konsumsisurveyor');
    }
    if(type=='alat')
    {
        jumlah = parseInt(document.getElementById('jumlahalat_'+num).value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargaalat_'+num).value));
        subtotal = jumlah * harga;

        document.getElementById('subtotalalat_'+num).value=numberFormat(subtotal);
    }
    if(type=='konsumsisurveyor')
    {
        jumlah = parseInt(document.getElementById('jumlahkonsumsisurveyor').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargakonsumsisurveyor').value));
        hk= parseInt(document.getElementById('hkkonsumsisurveyor').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotalkonsumsisurveyor').value=numberFormat(subtotal);
        hitungsubtotal('konsumsitkr');
    }
    if(type=='konsumsitkr')
    {
        jumlah = parseInt(document.getElementById('jumlahkonsumsitkr').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargakonsumsitkr').value));
        hk= parseInt(document.getElementById('hkkonsumsitkr').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotalkonsumsitkr').value=numberFormat(subtotal);
    }
    if(type=='transport')
    {
        jumlah = parseInt(document.getElementById('jumlahtransport').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargatransport').value));
        hk= parseInt(document.getElementById('hktransport').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotaltransport').value=numberFormat(subtotal);
    }
    if(type=='bbm')
    {
        jumlah = parseInt(document.getElementById('jumlahbbm').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargabbm').value));
        hk= parseInt(document.getElementById('hkbbm').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotalbbm').value=numberFormat(subtotal);
    }
    if(type=='bcd')
    {
        jumlah = parseInt(document.getElementById('jumlahbcd').value);
        harga = parseFloat(remove_comma_var(document.getElementById('hargabcd').value));
        hk= parseInt(document.getElementById('hkbcd').value);
        subtotal = jumlah * harga * hk;

        document.getElementById('subtotalbcd').value=numberFormat(subtotal);
    }

    subtottk=parseFloat(remove_comma_var(document.getElementById('subtotaltenagakerja').value));
    var subtotalat=0;
    var jlhalat= parseInt(document.getElementById('jlhalat').innerHTML);
    for(i=0;i<=jlhalat;i++)
    {   
        if(document.getElementById('subtotalalat_'+i))
        {
            subtotalat=subtotalat+parseFloat(remove_comma_var(document.getElementById('subtotalalat_'+i).value));
        }
    }
    
    subtotks=parseFloat(remove_comma_var(document.getElementById('subtotalkonsumsisurveyor').value));
    subtotkr=parseFloat(remove_comma_var(document.getElementById('subtotalkonsumsitkr').value));
    subtottrans=parseFloat(remove_comma_var(document.getElementById('subtotaltransport').value));
    subtotbbm=parseFloat(remove_comma_var(document.getElementById('subtotalbbm').value));
    subtotbcd=parseFloat(remove_comma_var(document.getElementById('subtotalbcd').value));

    Total=subtottk+subtotalat+subtotks+subtotkr+subtottrans+subtotbbm+subtotbcd;

    document.getElementById('Total').value=numberFormat(Total);


}

function appendStringAsNodes(element, html) {
    var frag = document.createDocumentFragment(),
        tmp = document.createElement('body'), child;
    tmp.innerHTML = html;
    // Append elements in a loop to a DocumentFragment, so that the browser does
    // not re-render the document for each node
    while (child = tmp.firstChild) {
        frag.appendChild(child);
    }
    element.appendChild(frag); // Now, append all elements at once
    frag = tmp = null;
}

function tambahalat()
{
    var alatbody = document.getElementById('alatdiv');
    var listalat = document.getElementById('listalat');
    var namaalat = document.getElementById('alat').value;
    var statusalat = document.getElementById('statusalat').value;
    var vals;
    var alatnyo='';
    
   
    
    nox=0;
    while(document.getElementById('daftar_alat_'+nox))
    {
       
        nox++;
    }

    var param='num='+nox+'&namaalat='+namaalat+'&statusalat='+statusalat;
    //alert(param);
    if(namaalat=='')
    {
        alert('nama alat is obligatory');
    }
    else
    {
    post_response_text('gis_slave_survey.php?proses=tambahalat',param,respon);
    }

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    
                    if(nox==0)
                    {   //alert(con.responseText);
                        document.getElementById('alat').value='';
                        vals=con.responseText.split("###");
                        if(statusalat=='Inventory')
                        {
                        listalat.innerHTML+=vals[1];
                        }
                        else
                        {
                        alatbody.innerHTML=vals[0];
                        listalat.innerHTML+=vals[1];
                        }
                    }
                    else
                    {
                        document.getElementById('alat').value='';
                        vals=con.responseText.split("###");
                        if(statusalat=='Inventory')
                        {
                        listalat.innerHTML+=vals[1];
                        }
                        else
                        {
                        appendStringAsNodes(alatbody,vals[0]);
                        listalat.innerHTML+=vals[1];
                        }
                    }
                    if(document.getElementById('jlhalat').innerHTML=='')
                    {
                    document.getElementById('jlhalat').innerHTML=0;
                    document.getElementById('jlhalat2').innerHTML=0;
                    }
                    else
                    {
                    document.getElementById('jlhalat').innerHTML=(parseInt(document.getElementById('jlhalat').innerHTML)+1);
                    document.getElementById('jlhalat2').innerHTML=(parseInt(document.getElementById('jlhalat2').innerHTML)+1);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

   
}
function hapusalat(num)
{

        var thead = document.getElementById('daftar_alat_'+num);
        thead.parentNode.removeChild(thead);
        var therow = document.getElementById('list_alat_'+num);
        therow.parentNode.removeChild(therow);
        var jlhalat = parseInt(document.getElementById('jlhalat2').innerHTML);
        if(jlhalat>0){
        document.getElementById('jlhalat2').innerHTML=(parseInt(document.getElementById('jlhalat2').innerHTML)-1);
        }
        else{
        document.getElementById('jlhalat').innerHTML='';
        document.getElementById('jlhalat2').innerHTML='';
        document.getElementById('alatbody').innerHTML="<tr><td style=width:1200px;></td></tr>";
        }

       
        hitungsubtotal();
    
}

function addDataForm()
{
    
    var container = document.getElementById('container');
    var form = document.getElementById('formdata');
    var dataform = document.getElementById('dataform');
    var param;
   
    post_response_text('gis_slave_survey.php?proses=addDataForm',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    
                    container.style.display="none";
                    form.style.display="block";
                    dataform.innerHTML=con.responseText;

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loadData()
{
    
    var container = document.getElementById('container');
    var param;
   
    post_response_text('gis_slave_survey.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formdata').style.display='none';
                    container.style.display="block";
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, notransaksi,posting) {
    showformupload(ev);
    param = "";
    param += "notransaksi=" + notransaksi+"&posting=" + posting;
    //alert(param);
    post_response_text('gis_slave_survey.php?proses=showupload', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(notransaksi,posting);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadfiles(notransaksi,posting) {
    param = 'notransaksi=' + notransaksi+"&posting=" + posting;
    post_response_text('gis_slave_survey.php?proses=loadfiles', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = "notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    post_response_text('gis_slave_survey.php?proses=deletefile', param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById('noupload').innerHTML;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("file", file);
    formdata.append("fileupload", document.getElementById("upload").value);
    alert(document.getElementById("upload").value);
    if (document.getElementById("upload").value == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "gis_slave_survey.php?proses=submitfile", true);
    busy_on();
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
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function posting(notransaksi,kodeorg)
{
     var param ='notransaksi='+notransaksi+'&kodeorg='+kodeorg;
    //alert(param);
    post_response_text('gis_slave_survey.php?proses=posting',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    loadData();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}
function cariBast(num)
{
    var param ='page='+num;
    //alert(param);
    post_response_text('gis_slave_survey.php?proses=loadData',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('container').innerHTML=con.responseText;
                    document.getElementById('pages').value=num;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}

function caridata()
{
   
    var container = document.getElementById('container');

    var unit = document.getElementById('unitcr').value;
    var tanggal = document.getElementById('tanggalcr').value;

    var param = 'unit='+unit+'&tanggal='+tanggal;
   
    post_response_text('gis_slave_survey.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}