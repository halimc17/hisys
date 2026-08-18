<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

// $str="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'PT'";
// $res=fetchdata($str);
// $pt = array();
// foreach($res as $row){
//     $pt[$row['kodeorganisasi']] = 0;
// }

// echo '<pre>';
// print_r($pt);
// echo '</pre>';
?>

<script src='js_chart/Chart.min.js'></script>
<style>
    <?php include 'css_dashboard/for_ksp.css'; ?>
</style>



<main class="content" style="left: 0px; right: 0px; bottom: 0px; top: 0px; overflow: none; background: white; z-index: 1000; position: ;">
    <!-- position: fixed; untuk unable scroll -->
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3 id="tanggalNow"></h3>
            </div>

            <div class="col-auto ms-auto text-end mt-n1">
                <a href="#" class="btn btn-light bg-white me-2" disabled="">Previous</a>
                <a href="master_front2.php" class="btn btn-primary">Next</a>
            </div>
        </div>
        <div class="row" id="untukModal">
        </div>
        <div class="row" id="testing">
            
        </div>
        <div class="row">
            <div class="col-xl-6 col-xxl-5 d-flex">
                <div class="w-100">
                    <h5 class="card-title mt-2">ANGGARAN vs REALISASI BULAN INI</h5>
                    <div class="row" id="testing2">
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-xxl-7">
                <div class="card flex-fill w-100">
                    <div class="card-header">
                        <div class="float-end">
                            <form class="row g-2">
                                <div class="col-auto">
                                    <select id="selectMenu" class="form-select form-select-sm bg-light border-0">
                                        <option >Jan</option>
                                        <option value="1" >Feb</option>
                                        <option value="2">Mar</option>
                                        <option value="3">Apr</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <input type="text" class="form-control form-control-sm bg-light rounded-2 border-0" style="width: 100px;" placeholder="Search..">
                                </div>
                            </form>
                        </div>
                        <h5 class="card-title mb-0">TREND PRODUKSI PERBULAN </h5>
                    </div>
                    <div class="card-body pt-2 pb-3">
                        <div class="chart chart-sm"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
                            <canvas id="chartjs1"  style="display: block; height: 505px; width: 855px;" width="910" height="644" class="chartjs-render-monitor"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    var chartjs1 = document.getElementById("chartjs1");

    document.addEventListener("DOMContentLoaded", function(event) { 
        console.log('we ready baby');
        datee();
        data_digunakan1();
        data_kedua();
    });

    function refresh(){
        setTimeout(() => {
            data_pertama();
            data_kedua();
        }, 2000);
    }

    var DataPerHari=[];
    var DataSekarang=[];
    var anggaranHmin1;
    var anggaranData;
    let bulanNow=datee();

    function datee() {
            
        let date = new Date();
        let dd = String(date.getDate()).padStart(2, '0');
        let mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = date.getFullYear();

            var month = new Array();
            month[0] = "Januari";
            month[1] = "Februari";
            month[2] = "Maret";
            month[3] = "April";
            month[4] = "Mei";
            month[5] = "Juni";
            month[6] = "Juli";
            month[7] = "Agustus";
            month[8] = "September";
            month[9] = "Oktober";
            month[10] = "November";
            month[11] = "December";
            var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
            var thisDay = date.getDay();

            thisDay = myDays[thisDay];

            let n = month[date.getMonth()];
            let today = mm + ' ' + dd + ' ' + yyyy;
            document.getElementById("tanggalNow").innerHTML = '<strong>'+thisDay.toUpperCase()+'</strong>' + ' ' + dd +' ' + n + ' '+ yyyy;
            let arr=[mm,yyyy,dd];
            return arr;
    }

    function data_digunakan1(){
        try{
            let tujuan1="api.php?method=biGraphic&type=kebun&bahasa=ID&tahun="+bulanNow[1]+"&bulan="+bulanNow[0]+"";
            console.log('tujuan1',tujuan1);
            get_response_text(tujuan1,respon);
            function respon(){
                if(con.readyState == 4){
                    var res_tujuan1 = JSON.parse(con.responseText);
                    console.log('api 1',res_tujuan1);

                    let filterTanggal1          = res_tujuan1.data.realisasi.listdata.detail;
                    var realisasiHariMin1       = filterTanggal1.filter(realisasi1 => realisasi1.Tanggal == String(bulanNow[2]-1));
                    var realisasiHariMin1PT     = Object.keys(realisasiHariMin1[0].listdetail);
                    var realisasiHariMin1PTval  = Object.values(realisasiHariMin1[0].listdetail);
                    var realisasiHariMin2       = filterTanggal1.filter(realisasi1 => realisasi1.Tanggal == String(bulanNow[2]-2));
                    var realisasiHariMin2PT     = Object.keys(realisasiHariMin2[0].listdetail);
                    var realisasiHariMin2PTval  = Object.values(realisasiHariMin2[0].listdetail)
                    
                    var filteredResult;
                    var filteredResult2=[];
                    for(let i in realisasiHariMin1PT ){
                        DataPerHari.push({
                            pt:realisasiHariMin1PT[i],
                            realisasi_min1:realisasiHariMin1PTval[i]
                        })
                        if(realisasiHariMin2PT.includes(DataPerHari[i].pt)){
                            Object.assign(DataPerHari[i], {realisasi_min2: realisasiHariMin2[0].listdetail[realisasiHariMin1PT[i]]});
                        }
                    }

                    filteredResult2 = realisasiHariMin1PT.filter(item1 =>item1 != realisasiHariMin2PT);
                    
                    console.log('DataPerHari',DataPerHari);
                    console.log('filteredResult2',filteredResult2);
                    console.log('realisasiHariMin2PT',realisasiHariMin2PT);

                    // ----  untuk ngambil pt yang tidak di pt hari ini
                    // for(let i=0; i<realisasiHariMin2PT.length; i++){
                    //     if(!filteredResult2.includes(realisasiHariMin2PT[i])){
                    //         DataPerHari.push({NOT_PT: realisasiHariMin2PT[i]});
                    //         //Object.assign(DataPerHari, {NOT_PT: realisasiHariMin2PT[i]});
                    //     }
                    // }
                    // for(let i=0; i<DataPerHari.length; i++){
                    //     if(!filteredResult2.includes(realisasiHariMin2PT[i])){
                    //         DataPerHari.push({NOT_PT: realisasiHariMin2PT[i]});
                    //         Object.assign(DataPerHari, {NOT_PT: realisasiHariMin2PT[i]});
                    //     }
                    // }

                    let perUnitHmin1=res_tujuan1.data.realisasi.listdata.detail.slice(-1).pop();
                    let perUnitHmin2=res_tujuan1.data.realisasi.listdata.detail.slice(-2,-1).pop();
                    var perPT=Object.keys(perUnitHmin1.listdetail); //value perunit sekarang
                    var perPT_val=Object.values(perUnitHmin1.listdetail); //value perunit sekarang
                    var perPT_y=Object.keys(perUnitHmin2.listdetail); //value perunit kemarin
                    var perPT_val_y=Object.values(perUnitHmin2.listdetail); //value perunit kemarin
                    
                    var iconify="";
                    for(let i=0; i<DataPerHari.length; i++){
                        let filterPT            = DataPerHari[i].pt;
                        let PT_realisasi_min1   = parseInt(DataPerHari[i].realisasi_min1);
                        let PT_realisasi_min2   = parseInt(DataPerHari[i].realisasi_min2);
                    
                            if(PT_realisasi_min1 > PT_realisasi_min2){
                                //console.log('lebih besar');
                                iconify = '<div class="stat text-primary">'+
                                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#65b605" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+
                                                        ' <path d="M12 19V6M5 12l7-7 7 7"></path>'+
                                                '</svg>'+
                                            '</div>';
                            }else{
                                iconify ='<div class="stat text-primary">'+
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d0021b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+
                                            '   <path d="M12 5v13M5 12l7 7 7-7"></path>'+
                                            '</svg>'+
                                        '</div>';
                                //console.log('lebih kecil');
                            }
                            Object.assign(DataPerHari[i], {icon: iconify});
                    }
                    let tujuan2="api.php?method=biGraphic&type=budget&bahasa=ID&tahun="+bulanNow[1]+"&bulan="+bulanNow[0]+"";
                    console.log('tujuan2',tujuan2);
                    get_response_text(tujuan2,respon);
                    function respon(){
                        if(con.readyState == 4){
                            var res_tujuan3 = JSON.parse(con.responseText);
                            console.log('res_tujuan3',res_tujuan3);
                            let testing=res_tujuan3.data.anggaran.listdata.detail;
                            anggaranHmin1 = testing.filter(anggaran => anggaran.Tanggal == bulanNow[2]-1);
                            //console.log('anggaranHmin1',anggaranHmin1);
                            anggaranData=anggaranHmin1;
                            //console.log('anggaran data',anggaranData);
                            //console.log('anggaran data detail',anggaranData[0].listdetail);
                            let listPTanggaran= Object.keys(anggaranData[0].listdetail);
                            //console.log('listPTanggaran',listPTanggaran);
                            var anggaranVal=Object.values(anggaranData[0].listdetail);
                            
                            for(let i=0; i<DataPerHari.length; i++){
                                let filterAnggaran = listPTanggaran.filter(pt => pt == String(DataPerHari[i].pt));
                                //console.log('filterAnggaran',filterAnggaran);
                                if(filterAnggaran.includes(DataPerHari[i].pt)){
                                    //console.log('anggaranVal cars',anggaranData[0].listdetail[filterAnggaran]);
                                    Object.assign(DataPerHari[i], {anggaranHmin1: anggaranData[0].listdetail[filterAnggaran]});
                                }
                            }
                           

                            let total=res_tujuan1.data.realisasi.jumlah;
                            let div2="";
                            var lebar_col1 = ((6-DataPerHari.length) <= 0) ? "col-sm-2" : "col-sm-"+((6-DataPerHari.length)*2);
                            var lebar_col = "col-sm-2";
                            div2+= '<h5 class="card-title mt-2">REALISASI PRODUKSI H-1</h5>';
                            div2+='<div class='+lebar_col1+'>'+
                                    '<div class="card">'+
                                        '<div class="card-body">'+
                                        ' <div class="row" id="rowPT">'+
                                            '<div class="col mt-0">'+
                                                    '<h5 class="card-title" style="font-size: 2rem; font-weight: 700;">TOTAL</h5>'+
                                            '</div>'+
                                        ' </div>'+
                                            '<h1 class="mt-1 mb-3" id="jml_pt">-</h1>'+
                                            '<div class="mb-0">'+
                                                '<span class="badge badge-success-light"> <i class="mdi mdi-arrow-bottom-right"></i>Total Anggaran h-1</span>'+
                                            ' <span class="text-muted" id="totalAnggaranHmin1"></span>'+
                                        '  </div>'+
                                            '<div class="mb-0">'+
                                                '<span class="badge badge-success-light"> <i class="mdi mdi-arrow-bottom-right"></i>Total Realisasi h-1</span>'+
                                            ' <span class="text-muted" id="totalRealisasiHmin1"></span>'+
                                        '  </div>'+
                                    '  </div>'+
                                ' </div>'+
                                '</div>';
                                document.getElementById('testing').innerHTML+=div2;
                                
                                var tot_percent=[];
                                var tot_perTGL = 0;
                                var tot_AnggaranHmin1 = 0;
                                for (let i =0; i<DataPerHari.length; i++){
                                    var divPT = DataPerHari[i].pt;
                                    var divPT_val = DataPerHari[i].realisasi_min1;
                                    var anggaran_perPT = DataPerHari[i].anggaranHmin1;
                                    tot_perTGL = tot_perTGL + parseFloat(divPT_val);
                                    
                                    let div="";
                                        div+='<div class='+lebar_col+' id="divPT'+i+'" onclick="global_modal(event);">'+
                                        '<div class="card">'+
                                            '<div class="card-body">'+
                                            ' <div class="row" id="rowPT">'+
                                                '<div class="col mt-0">'+
                                                        '<h5 class="card-title" style="font-size: 2rem; font-weight: 700;">'+divPT+'</h5>'+
                                                ' </div>'+
                                                DataPerHari[i].icon +
                                            ' </div>'+
                                                '<h1 class="mt-1 mb-3 idPT" id="'+divPT+[i]+'" value="'+convertToRupiah(parseFloat(divPT_val))+'">'+convertToRupiah(parseFloat(divPT_val))+'</h1>'+
                                                '<div class="mb-0">'+
                                                    '<span class="badge badge-success-light"> <i class="mdi mdi-arrow-bottom-right"></i>Anggaran</span>'+
                                                ' <span class="text-muted anggaran" id="anggaran'+divPT+[i]+'" value="'+parseFloat(DataPerHari[i].anggaranHmin1)+'">'+(parseFloat(DataPerHari[i].anggaranHmin1).toFixed(2))+'</span>'+
                                            '  </div>'+
                                                '<div class="mb-0">'+
                                                    '<span class="badge badge-success-light"> <i class="mdi mdi-arrow-bottom-right"></i>Realisasi H-1</span>'+
                                                ' <span class="text-muted realisasi" id="valRealisasiMin1'+divPT+'">'+convertToRupiah(parseFloat(divPT_val))+'</span>'+
                                                '</div>'+
                                                '<div class="mb-0">'+
                                                    '<span class="badge badge-success-light"> <i class="mdi mdi-arrow-bottom-right"></i>Realisasi H-2</span>'+
                                                ' <span class="text-muted" id="">'+convertToRupiah(parseFloat(DataPerHari[i].realisasi_min2))+'</span>'+
                                                '</div>'+
                                        '  </div>'+
                                    ' </div>'+
                                    '</div>';
                                    tot_percent.push(convertToRupiah(parseFloat(divPT_val)));
                                    document.getElementById('testing').innerHTML+=div;
                                }
                                
                                let getID=document.getElementsByClassName("idPT");
                                let getAnggaran=document.getElementsByClassName("anggaran");
                                let getRealisasi=document.getElementsByClassName("realisasi");
                                var eachID;
                                var eachAnggaran;
                                var eachRealisasi;
                                var jmlTotalPersen=0;
                                for(let i=0; i<getID.length; i++){
                                    let perID=getID[i].id;
                                    let perAnggaran=getAnggaran[i].id;
                                    //console.log('perAnggaran',perAnggaran);
                                    if(perID !== "jml_pt"){
                                        eachID=document.getElementById(perID).innerHTML;
                                        eachAnggaran=document.getElementById(perAnggaran).innerHTML;
                                        tot_AnggaranHmin1 = tot_AnggaranHmin1 + parseFloat(eachAnggaran);
                                    }
                                    jmlTotalPersen=jmlTotalPersen+parseFloat((eachID/eachAnggaran)*100);
                                    document.getElementById(perID).innerHTML=parseFloat((eachID/eachAnggaran)*100).toFixed(2)+' %';
                                    document.getElementById('jml_pt').innerHTML=jmlTotalPersen.toFixed(2) + '%';
                                    document.getElementById('totalRealisasiHmin1').innerHTML=convertToRupiah(tot_perTGL);
                                    document.getElementById('totalAnggaranHmin1').innerHTML=tot_AnggaranHmin1.toFixed(2);
                                    // console.log('each',document.getElementById(perID))
                                    // console.log('perID',perID)
                                    // console.log('eachID',eachID)
                                    // console.log('eachAnggaran',eachAnggaran)
                                    // console.log('persenan ID',parseFloat((eachID/eachAnggaran)*100))
                                }
                                var month_display = new Array();
                                month_display[01] = "Jan";
                                month_display[02] = "Feb";
                                month_display[03] = "Mar";
                                month_display[04] = "Apr";
                                month_display[05] = "Mei";
                                month_display[06] = "Jun";
                                month_display[07] = "Jul";
                                month_display[08] = "Ags";
                                month_display[09] = "Sep";
                                month_display[10] = "Okt";
                                month_display[11] = "Nov";
                                month_display[12] = "Des";
                                var dataAnggaranDonut=[];
                                var tot_AnggaranDonut=0;
                                var tot_RealisasiDonut=0;
                                let tujuan3="api.php?method=biGraphic&type=kebun&bahasa=ID&tahun="+bulanNow[1]+"";
                                console.log('tujuan3',tujuan3);
                                get_response_text(tujuan3,respon);
                                function respon(){
                                    if(con.readyState == 4){
                                        let date = new Date();
                                        var data3=JSON.parse(con.responseText);
                                        let filterBulan3=data3.data.realisasi.listdata.detail;
                                        var realisasiBulanIni = filterBulan3.filter(realisasi3 => realisasi3.Bulan == String(bulanNow[0]));
                                        var fullmoonRealisasi = filterBulan3.filter(realisasi3 => realisasi3.Bulan <= String(bulanNow[0]));
                                        var getBulan;
                                        var getNamaBulan;
                                        var getJumlahGroupPT;
                                        var graphicBarData=[];
                                        for(let i=0; i<fullmoonRealisasi.length; i++){
                                            getBulan = fullmoonRealisasi[i].Bulan;
                                            getNamaBulan = month_display[parseInt(getBulan)];
                                            getJumlahGroupPT = fullmoonRealisasi[i].jumlah;
                                            graphicBarData.push({
                                                Bulan        :getNamaBulan,
                                                jumlahGroup :getJumlahGroupPT
                                            })
                                            // console.log('getBulan',getBulan);
                                            // console.log('getNamaBulan',getNamaBulan);
                                            // console.log('getJumlahGroupPT',getJumlahGroupPT);

                                        }
                                        console.log('graphicBarData',Object.values(graphicBarData));
                                        var setGraphicBarLabels=[];
                                        var setGraphicBarDataRealisasi=[];
                                        for(let i=0; i<graphicBarData.length; i++){
                                            //console.log('graphicBarData data',graphicBarData)
                                            setGraphicBarLabels.push(graphicBarData[i].Bulan,);
                                            setGraphicBarDataRealisasi.push(graphicBarData[i].jumlahGroup);
                                        }
                                        var keyRealisasiDonut= Object.keys(realisasiBulanIni[0].listdetail);
                                        var valRealisasiDonut= Object.values(realisasiBulanIni[0].listdetail);
                                        console.log('realisasiBulanIni',realisasiBulanIni);
                                        let tujuan4="api.php?method=biGraphic&type=budget&bahasa=ID&tahun="+bulanNow[1]+"";
                                        console.log('tujuan4',tujuan4);
                                        get_response_text(tujuan4,respon);
                                        function respon(){
                                            if(con.readyState == 4){
                                                var data4=JSON.parse(con.responseText);
                                                console.log('data4',data4);
                                                let filterBulan4=data4.data.anggaran.listdata.detail;
                                                var anggaranBulanIni = filterBulan4.filter(anggaran4 => anggaran4.Bulan == String(bulanNow[0]));
                                                var fullmoonAnggaran = filterBulan4.filter(moon => moon.Bulan <= String(bulanNow[0]));
                                                var graphicBarData2=[];
                                                var getJumlahGroupPTanggaran;
                                                for(let i=0; i<fullmoonRealisasi.length; i++){
                                                    getJumlahGroupPTanggaran = fullmoonAnggaran[i].jumlah;
                                                    graphicBarData2.push({
                                                        jumlahGroup :getJumlahGroupPTanggaran
                                                    })
                                                    
                                                    //console.log('getJumlahGroupPTanggaran',getJumlahGroupPTanggaran);

                                                }
                                                var setGraphicBarDataAnggaran=[];
                                                for(let i=0; i<graphicBarData2.length; i++){
                                                    // console.log('graphicBarData data',graphicBarData)
                                                    setGraphicBarDataAnggaran.push(graphicBarData2[i].jumlahGroup);
                                                }
                                                //console.log('setGraphicBarDataRealisasi',setGraphicBarDataRealisasi);
                                                //console.log('setGraphicBarDataAnggaran',setGraphicBarDataAnggaran);
                                                let lineGraph=[
                                                    {
                                                    labels:setGraphicBarLabels,
                                                    data:setGraphicBarDataRealisasi,
                                                    colors:['#edc7b4','#50342c','#34211b','#f44d2b','#39709f','#a82a0f','#4e8288']
                                                    },
                                                    {
                                                    labels:setGraphicBarLabels,
                                                    data:setGraphicBarDataAnggaran,
                                                    colors:['#edc7b4','#50342c','#34211b','#f44d2b','#39709f','#a82a0f','#4e8288']
                                                    }

                                                ]
                                                buildChartJs(chartjs1,'bar',lineGraph);
                                                var keyAnggaranDonut= Object.keys(anggaranBulanIni[0].listdetail);
                                                var valAnggaranDonut= Object.values(anggaranBulanIni[0].listdetail);
                                                for(let i in keyAnggaranDonut ){
                                                    tot_AnggaranDonut=tot_AnggaranDonut+parseFloat(valAnggaranDonut[i]);
                                                    dataAnggaranDonut.push({
                                                        ptDonut:keyAnggaranDonut[i],
                                                        anggaranDonut:valAnggaranDonut[i]
                                                    })
                                                    if(keyRealisasiDonut.includes(dataAnggaranDonut[i].ptDonut)){
                                                        Object.assign(dataAnggaranDonut[i], {realisasi: realisasiBulanIni[0].listdetail[keyRealisasiDonut[i]]});
                                                    }
                                                }
                                                Object.assign(dataAnggaranDonut, {totalAnggaran:anggaranBulanIni[0].jumlah,totRealisasi:realisasiBulanIni[0].jumlah});
                                                console.log('dataAnggaranDonut',dataAnggaranDonut);
                                                let div="";
                                                let div2="";
                                                div += '<div class="col-sm-4">'+
                                                    '     <div class="card">'+
                                                    '         <div class="card-body">'+
                                                    '             <div class="row">'+
                                                    '                 <div class="card mb-0" style="border-width: thick; padding: 5px; font-size: x-large;">'+
                                                    '                     <span class="badge badge-secondary-light"> <i class="mdi mdi-arrow-bottom-right"></i>TOTAL</span>'+
                                                    '                 </div>'+
                                                    '             </div>'+
                                                    '             <div class="py-3">'+
                                                    '                 <div class="chart chart-xs centeringItemEl" style="justify-content: center; align-items: center; display: flex;">'+
                                                    '                     <div class="chartjs-size-monitor">'+
                                                    '                         <div class="chartjs-size-monitor-expand"><div class=""></div></div>'+
                                                    '                         <div class="chartjs-size-monitor-shrink"><div class=""></div></div>'+
                                                    '                     </div>'+
                                                    '                     <div class="chartjs-size-monitor">'+
                                                    '                         <div class="chartjs-size-monitor-expand">'+
                                                    '                             <div class=""></div>'+
                                                    '                         </div>'+
                                                    '                         <div class="chartjs-size-monitor-shrink">'+
                                                    '                             <div class=""></div>'+
                                                    '                         </div>'+
                                                    '                     </div>'+
                                                    '                     <canvas id="chartjs2oi" style="display: block; height: 132px; width: 155px;" width="310" height="264" class="chartjs-render-monitor"></canvas>'+
                                                    '                 </div>'+
                                                    '             </div>'+
                                                    '         </div>'+
                                                    '     </div>'+
                                                    ' </div>';

                                                
                                                for(let i=0; i<dataAnggaranDonut.length; i++){
                                                    
                                                    let PT_donut = dataAnggaranDonut[i].ptDonut;
                                                    let divPT2_val = dataAnggaranDonut[i].realisasi;
                                                    //let tot_perTGL2 = divPT2_val/jumlahpertahun;
                                                    //console.log('eh',tot_perTGL2);
                                                    div2 += '<div class="col-sm-4" id="radial'+PT_donut+'">'+
                                                        '     <div class="card">'+
                                                        '         <div class="card-body">'+
                                                        '             <div class="row">'+
                                                        '                 <div class="card mb-0" style="border-width: thick; padding: 5px; font-size: x-large;">'+
                                                        '                     <span class="badge badge-secondary-light"> <i class="mdi mdi-arrow-bottom-right"></i>'+PT_donut+'</span>'+
                                                        '                 </div>'+
                                                        '             </div>'+
                                                        '             <div class="py-3">'+
                                                        '                 <div class="masterfront1 chart chart-xs centeringItemEl" style="justify-content: center; align-items: center; display: flex;">'+
                                                        '                     <div class=" chartjs-size-monitor">'+
                                                        '                         <div class="chartjs-size-monitor-expand">'+
                                                        '                             <div class=""></div>'+
                                                        '                         </div>'+
                                                        '                         <div class="chartjs-size-monitor-shrink">'+
                                                        '                             <div class=""></div>'+
                                                        '                         </div>'+
                                                        '                     </div>'+
                                                        '                     <canvas id="chartjs'+PT_donut+'" style="display: block; height: 132px; width: 155px;" width="310" height="264" class="chartjs-render-monitor"></canvas>'+
                                                        '                 </div>'+
                                                        '             </div>'+
                                                        '         </div>'+
                                                        '     </div>'+
                                                        ' </div>';
                                                }

                                                    document.getElementById('testing2').innerHTML=div;
                                                    document.getElementById('testing2').innerHTML+=div2;
                                                    let keyID=document.getElementsByClassName('masterfront1');
                                                    let color=['blue','orange','green','brown','grey'];
                                                    let color1=['','',''];
                                                    for(let x=0; x<keyID.length; x++){
                                                        let eachDonutID=keyID[x].children[1].id;
                                                        let DonutID=document.getElementById(eachDonutID);
                                                        let chartjs2oi=document.getElementById('chartjs2oi');
                                                        let arr= [{
                                                                labels:'realisasi '+ dataAnggaranDonut[x].ptDonut,
                                                                value: 50,
                                                                color: color[x],
                                                                data:dataAnggaranDonut[x].realisasi,
                                                            },{
                                                                labels:'Anggaran '+ dataAnggaranDonut[x].ptDonut,
                                                                value: 50,
                                                                data:dataAnggaranDonut[x].anggaranDonut,
                                                                color:color1[x]
                                                        }]
                                                        let arr2= [{
                                                                labels:'Total realisasi',
                                                                value: 50,
                                                                color: color[x],
                                                                data:dataAnggaranDonut.totRealisasi,
                                                            },{
                                                                labels:'Total Anggaran',
                                                                value: 50,
                                                                data:dataAnggaranDonut.totalAnggaran,
                                                                color:color1[x]
                                                        }]

                                                        ChartJsRadialDonut(DonutID,'doughnut',arr);
                                                        ChartJsRadialDonut(chartjs2oi,'doughnut',arr2);
                                                    }
                                                        
                                                    console.log('luar',dataAnggaranDonut);
                                            }
                                        }
                                    }
                                }
                        }
                        
                    }
                    
                }
            }
                   
        }
        catch(e){
            alert('error',e);
        }
    }

    function data_kedua(){
        try{
            let tujuan2="api.php?method=biGraphic&type=kebun&bahasa=ID&tahun="+bulanNow[1]+"";
            fetch(tujuan2).then(response => {
                            console.log(response);
                            // var res=response.json();
                            // console.log('line',res);
                            return response.json();
                        }).then(dataFetch => {
                            console.log('dataFetch',dataFetch)
            });
        }
        catch(e){
            alert('error',e);
        }
    }

    function buildChartJs(ele, tipe,data) {
        if (typeof myChart !== "undefined") {
            myChart.destroy();
        }
        console.log('bar',data)
        var ctx = ele.getContext("2d");
        var myChart = new Chart(ctx, {
            type: tipe,
            data: {
                labels: data[0].labels,
                datasets: [
                    {
                        //type: "line",
                        label: "Realisasi",
                        data:data[0].data,
                        backgroundColor:'#a82a0f', //"rgba(75, 192, 192, 0.2)"
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1,
                        fill: false,
                        
                    },
                    {
                        //type: "line",
                        label: "Anggaran",
                        data: data[1].data,
                        backgroundColor:'#4e8288', //"rgba(75, 192, 192, 0.2)"
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1,
                        fill: false,
                        
                    }
                    // ,
                    // {
                    //     label: "# of Per PT",
                    //     data: [291040, 306320, 360960, 52880, 222360, 282760],
                    //     backgroundColor: [
                    //         "rgba(255, 99, 132, 1)",
                    //         "rgba(54, 162, 235, 1)",
                    //         "rgba(255, 206, 86, 1)",
                    //         "rgba(75, 192, 192, 1)",
                    //         "rgba(153, 102, 255, 1)",
                    //         "rgba(255, 159, 64, 1)",
                    //         "rgba(255, 99, 132, 0.2)",
                    //         "rgba(54, 162, 235, 0.2)",
                    //         "rgba(255, 206, 86, 0.2)",
                    //         "rgba(75, 192, 192, 0.2)",
                    //         "rgba(153, 102, 255, 0.2)",
                    //         "rgba(255, 159, 64, 0.2)",
                    //     ],
                    //     borderColor: [
                    //         "rgba(255,99,132,1)",
                    //         "rgba(54, 162, 235, 1)",
                    //         "rgba(255, 206, 86, 1)",
                    //         "rgba(75, 192, 192, 1)",
                    //         "rgba(153, 102, 255, 1)",
                    //         "rgba(255, 159, 64, 1)",
                    //         "rgba(255,99,132,1)",
                    //         "rgba(54, 162, 235, 1)",
                    //         "rgba(255, 206, 86, 1)",
                    //         "rgba(75, 192, 192, 1)",
                    //         "rgba(153, 102, 255, 1)",
                    //         "rgba(255, 159, 64, 1)",
                    //     ],
                    //     borderWidth: 1,
                    // },
                ],
            },
            options: {
                responsive: true,
                // plugins: {
                // datalabels: {
                //         color: 'black',
                //         anchor: "end",
                //         align: "right",
                //         offset: 10,
                //         display: function (context) {
                //             return context.dataset.data[context.dataIndex];
                //         },
                //         /* Adjust data label font size according to chart size */
                //             font: function(context) {
                //             var width = context.chart.width;
                //             var size = Math.round(width / 32);

                //             return {
                //                 weight: 'bold',
                //                 size: size
                //             };
                //         },
                //         formatter: Math.round
                //     }
                // },
                tooltips: {
                    backgroundColor: "#FFF",
                    borderColor: "#00094b",
                    borderWidth: 2,
                    titleFontSize: 20,
                    titleFontColor: "#0066ff",
                    bodyFontColor: "#000",
                    bodyFontSize: 20,
                    displayColors: false,
                },
                legend: {
                    //labels:[data.label],
                    //text: 'tes',
                    display: true,
                    position: "top",
                    boxWidth:100,
                    labels: {
                        "fontSize": 15,
                    }
                },
                scales: {
                    xAxes: [
                        {
                            display: true,
                            gridLines: {
                                offsetGridLines: true,
                                display: false,
                                borderDash: [6, 2],
                                tickMarkLength: 5,
                            },
                            scaleLabel: {
                                fontColor: "#595959",
                                display: true,
                                labelString: "Bulan",
                                fontSize: 20,
                                fontStyle: "bold",
                            },
                            ticks: {
                                fontColor: "rgb(13, 49, 75)",
                                fontSize: 20,
                                stepSize: 1,
                                beginAtZero: true,
                            }
                            // ,
                            // afterFit: function(scale) {
                            //     //console.log('scale',scale);
                            //     let chartWidth = scale.chart.width;
                            //     let chartTop = scale.top;
                            //     const new_width=chartWidth*0.08;
                            //     const new_top=chartTop*0.08;

                            //     scale.width = new_width;
                            //     scale.top = new_top;
                            //     scale.fullWidth = true;
                            // }
                        },
                    ],
                    yAxes: [
                        {
                            display: true,
                            gridLines: {
                                display: true,
                            },
                            scaleLabel: {
                                fontColor: "#595959",
                                display: true,
                                labelString: "KG",
                                fontSize: 15,
                                fontStyle: "bold",
                            },
                            ticks: {
                                fontColor: "BLACK",
                                fontSize: 15,
                                //display:false
                            }
                            // ,
                            // afterFit: function(scale) {
                            //     //console.log('scale',scale);
                            //     let chartWidth = scale.chart.width;
                            //     let chartTop = scale.paddingTop;
                            //     const new_width=chartWidth*0.1;
                            //     const new_top=chartTop*3;

                            //     scale.width = new_width;
                            //     scale.paddingTop = new_top;
                            //     scale.fullWidth = true;
                            // }
                            // ,
                            // gridLines: {
                            // 	color: "rgba(0, 0, 0, 0)",
                            // }
                        },
                    ],
                },
            },
            
        // plugins: [{
        //    /* Adjust axis labelling font size according to chart size */
        //    beforeDraw: function(c) {
        //        var chartHeight = c.chart.height;
        //        var size = chartHeight * 5 / 100;
        //        c.scales['y-axis-0'].options.ticks.minor.fontSize = size;
        //        c.scales['x-axis-0'].options.ticks.minor.fontSize = size;
        //    }
        // }]
        });
    }

    function ChartJsRadialDonut(ele, tipe, data,percent) {
        if (typeof myChart !== "undefined") {
            myChart.destroy();
        }
        //console.log('data chart donut',data);
        //console.log('ele chart donut',ele);
        var colorr=[];
        var datas=[];
        var labels=[];
        
        // //let	total=Math.round(~~totalPersenData.toString());
        
        for(let i in data){
            datas.push(data[i].data);
            labels.push(data[i].labels);
            colorr.push(data[i].color);
        }
        // console.log('datas',datas);
        var ctx = ele.getContext("2d");
        var canvas = document.getElementById(ele.id);

        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    // Get ctx from string
                    var ctx = chart.chart.ctx;

                    // Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || "Arial";
                    var txt = centerConfig.text;
                    var color = centerConfig.color || "#000";
                    var maxFontSize = centerConfig.maxFontSize || 75;
                    var sidePadding = centerConfig.sidePadding || 20;
                    var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2);
                    // Start with a base font of 30px
                    ctx.font = "30px " + fontStyle;

                    // Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    var stringWidth = ctx.measureText(txt).width;
                    var elementWidth = chart.innerRadius * 2 - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    var widthRatio = elementWidth / stringWidth;
                    var newFontSize = Math.floor(30 * widthRatio);
                    var elementHeight = chart.innerRadius * 2;

                    // Pick a new font size so it will not be larger than the height of label.
                    var fontSizeToUse = Math.min(newFontSize, elementHeight, maxFontSize);
                    var minFontSize = centerConfig.minFontSize;
                    var lineHeight = centerConfig.lineHeight || 25;
                    var wrapText = false;

                    if (minFontSize === undefined) {
                        minFontSize = 20;
                    }

                    if (minFontSize && fontSizeToUse < minFontSize) {
                        fontSizeToUse = minFontSize;
                        wrapText = true;
                    }

                    // Set font settings to draw it correctly.
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                    var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                    ctx.font = fontSizeToUse + "px " + fontStyle;
                    ctx.fillStyle = color;

                    if (!wrapText) {
                        ctx.fillText(txt, centerX, centerY);
                        return;
                    }

                    var words = txt.split(" ");
                    var line = "";
                    var lines = [];

                    // Break words up into multiple lines if necessary
                    for (var n = 0; n < words.length; n++) {
                        var testLine = line + words[n] + " ";
                        var metrics = ctx.measureText(testLine);
                        var testWidth = metrics.width;
                        if (testWidth > elementWidth && n > 0) {
                            lines.push(line);
                            line = words[n] + " ";
                        } else {
                            line = testLine;
                        }
                    }

                    // Move the center up depending on line height and number of lines
                    centerY -= (lines.length / 2) * lineHeight;

                    for (var n = 0; n < lines.length; n++) {
                        ctx.fillText(lines[n], centerX, centerY);
                        centerY += lineHeight;
                    }
                    //Draw text in center
                    ctx.fillText(line, centerX, centerY);
                }
            },
        });

        var myChart = new Chart(ctx, {
            type: tipe,
            data: {
                label:labels,
                datasets: [
                    {
                        label: "# Report Tahunan",
                        data: datas,
                        backgroundColor: colorr,
                        borderColor: [
                            "rgba(255,99,132,1)",
                            
                        ],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                cutoutPercentage: 75,
                legend: {
                    // labels:[data.label],
                    //text: 'tes',
                    display: true,
                    position: "bottom",
                },
                tooltips: {
                    callbacks: {
                    label: function(tooltipItem,data) {
                        console.log('tooltipItem', tooltipItem);
                        console.log('data', data);
                    
                        return data['label'][tooltipItem['index']];
                        //return tooltipItem.yLabel;
                    }, afterLabel: function(tooltipItem, data) {
                        var dataset = data['datasets'][0];
                        var datameta = dataset["_meta"];
                        // for(var i=0; i<datameta.length; i++){
                        // 	datameta++;
                        // }
                        var datameta1 = Object.keys(datameta)[0];
                        
					    console.log('data after ==  ', data);
                        console.log('data meta key array [0] ==  ', datameta1);
                        // var datameta = dataset["_meta"].map(meta1 => {
                        // 	return meta1._meta + ': ' + meta1.data[tooltipItem.index];
                        // });
                        var percent = Math.round((dataset['data'][tooltipItem['index']] / dataset["_meta"][datameta1]['total']) * 100)
                        return '(' + percent + '%)';
                        }
                    },
                    backgroundColor: "#FFF",
                    borderColor: "#00094b",
                    borderWidth: 2,
                    titleFontSize: 16,
                    titleFontColor: "#0066ff",
                    bodyFontColor: "#000",
                    bodyFontSize: 16,
                    displayColors: false,
                },
                elements: {
                    center: {
                        text: (parseFloat(parseFloat(data[0].data)/parseFloat(data[1].data))*100).toFixed(2)+"%", //data[0].produksiPabrik[5].val
                        color: "#66B6DB ", // Default is #000000
                        fontStyle: "Arial", // Default is Arial
                        sidePadding: 35, // Default is 20 (as a percentage)
                        minFontSize: 20, // Default is 20 (in px), set to false and text will not wrap.
                        lineHeight: 25, // Default is 25 (in px), used for when text wraps
                    },
                },
                layout: {
                    padding: 0,
                },
            },
        });

        
        // canvas.onmouseover = function(evt) {
        //     console.log('evt',evt)
        //     var activePoints = myChart.getElementsAtEvent(evt);
        //     console.log(activePoints);
        //         if (activePoints[0]) {    
        //             global_modal();
        //             activePoints[0]._model.backgroundColor="red";
        //             var chartData = activePoints[0]['_chart'].config.data;
        //             var idx = activePoints[0]['_index'];

        //             var label = chartData.labels[idx];
        //             var value = chartData.datasets[0].data[idx];

        //             var url = "http://example.com/?label=" + label + "&value=" + value;
        //             console.log(url);
        //             //alert(url);
        //         }
        // };
    }

    function convertToRupiah(number){
        
	    return number.toLocaleString('id-ID');


    }

    function global_modal(event){
        console.log('kenak');
        console.log('event',event );
        let div_modal ="";
        div_modal +='<div id="myModal" class="modal">'+
                    '  <div class="modal-content">'+
                    '      <span class="close centeringItemEl">&times;</span>'+
                    '      <p class="centeringItemEl">Some text in the Modal..</p>'+
                        ' <div class="col-12 col-xl-12">'+
                        '     <div class="card">'+
                    //    '         <div class="card-header">'+
                    //    '             <h5 class="card-title">Basic Table</h5>'+
                    //    '             <h6 class="card-subtitle text-muted">Using the most basic table markup, here’s how .table-based tables look in Bootstrap.'+
                    //    '             </h6>'+
                    //    '         </div>'+
                        '         <table class="table">'+
                        '             <thead>'+
                        '                 <tr>'+
                        '                     <th style="width:40%;">Tanggal</th>'+
                        '                     <th style="width:25%">Realisasi</th>'+
                        '                     <th class="d-none d-md-table-cell" style="width:25%">Anggaran</th>'+
                        '                     <th>Actions</th>'+
                        '                 </tr>'+
                        '             </thead>'+
                        '             <tbody>'+
                        '                 <tr>'+
                        '                     <td>Vanessa Tucker</td>'+
                        '                     <td>864-348-0485</td>'+
                        '                     <td class="d-none d-md-table-cell">June 21, 1961</td>'+
                        '                     <td class="table-action">'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash align-middle"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>'+
                        '                     </td>'+
                        '                 </tr>'+
                        '                 <tr>'+
                        '                     <td>William Harris</td>'+
                        '                     <td>914-939-2458</td>'+
                        '                     <td class="d-none d-md-table-cell">May 15, 1948</td>'+
                        '                     <td class="table-action">'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash align-middle"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>'+
                        '                     </td>'+
                        '                 </tr>'+
                        '                 <tr>'+
                        '                     <td>Sharon Lessman</td>'+
                        '                     <td>704-993-5435</td>'+
                        '                     <td class="d-none d-md-table-cell">September 14, 1965</td>'+
                        '                     <td class="table-action">'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash align-middle"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>'+
                        '                     </td>'+
                        '                 </tr>'+
                        '                 <tr>'+
                        '                     <td>Christina Mason</td>'+
                        '                     <td>765-382-8195</td>'+
                        '                     <td class="d-none d-md-table-cell">April 2, 1971</td>'+
                        '                     <td class="table-action">'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash align-middle"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>'+
                        '                     </td>'+
                        '                 </tr>'+
                        '                 <tr>'+
                        '                     <td>Robin Schneiders</td>'+
                        '                     <td>202-672-1407</td>'+
                        '                     <td class="d-none d-md-table-cell">October 12, 1966</td>'+
                        '                     <td class="table-action">'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>'+
                        '                         <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash align-middle"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>'+
                        '                     </td>'+
                        '                 </tr>'+
                        '             </tbody>'+
                        '         </table>'+
                        '     </div>'+
                        ' </div>'+
                    '  </div>'+
                    ' </div>';
        document.getElementById('untukModal').innerHTML=div_modal;
        let modal =  document.getElementById('myModal');
        let span = document.getElementsByClassName("close")[0];

        modal.style.display = "block";

        span.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
    
    function showMenuDropDown(ev){
       console.log(ev);
    }

    function createXMLHttpRequest() {
        try { return new ActiveXObject("Msxml2.XMLHTTP"); } 
        catch (e) {}
        try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
        catch (e) {}
        try { return new XMLHttpRequest(); } 
        catch(e) {}
        alert("XMLHttpRequest Tidak didukung oleh browser");
        return null;
    }

    var con = createXMLHttpRequest();
    function get_response_text(tujuan, funct) {   
        con.open("GET", tujuan, true);
        //con.setRequestHeader("Connection", "close");
        con.onreadystatechange= eval(funct);
        con.send(null);
    }
</script>

