function lihatDetailNeraca(
  noakuna,
  noakuns,
  periode,
  tipe,
  pt,
  unit,
  ev,
  codeurut,
  kodelaporan
) {
  gudang = document.getElementById("gudang");
  gudang = gudang.options[gudang.selectedIndex].value;
  param =
    "method=html" +
    "&noakuna=" +
    noakuna +
    "&noakuns=" +
    noakuns +
    "&periode=" +
    periode +
    "&tipe=" +
    tipe +
    "&pt=" +
    pt +
    "&unit=" +
    gudang +
    "&codeurut=" +
    codeurut +
    "&kodelaporan=" +
    kodelaporan;
  title = "Data Detail";
  // showDialog1(title, "<iframe frameborder=0 style='width:845px;height:395px'" +
  // 	" src='keu_slave_2neraca_detail_v2.php?" + param + "'></iframe>", '850', '400', ev);
  // var dialog = document.getElementById('dynamic1');
  // dialog.style.top = '50px';
  // dialog.style.left = '15%';

  alertify
    .popup(
      title,
      "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_slave_2neraca_detail_v2.php?" +
        param +
        "'></iframe>"
    )
    .set({ resizable: true, overflow: false })
    .resizeTo("80%", "70%");
}

//get karyawan
function getkaryawan() {
  pt = document.getElementById("pt").value;
  param = "proses=getkaryawan" + "&pt=" + pt;

  tujuan = "keu_slave_2jurnal_option.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("nik").innerHTML = con.responseText;
          getReg();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//onchange baru untuk ambil PT->Regional->unit

//get Regional
function getReg() {
  pt = document.getElementById("pt").value;
  param = "proses=getReg" + "&pt=" + pt;

  tujuan = "keu_slave_2jurnal_option.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //alertify.alert("Informasi",con.responseText);
          document.getElementById("regional").innerHTML = con.responseText;
          getUnit();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

//get unit
function getUnit() {
  regional = document.getElementById("regional").value;
  pt = document.getElementById("pt").value;
  param = "proses=getUnit" + "&regional=" + regional + "&pt=" + pt;

  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_2jurnal_option.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("gudang").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanKeuanganDetail(nourut, tipe) {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param =
    "pt=" +
    pt +
    "&unit=" +
    unit +
    "&periode=" +
    periode +
    "&nourut=" +
    nourut +
    "&tipe=" +
    tipe;
  tujuan = "keu_slave_2laporankeuangan_detail.php";

  document.getElementById(nourut).innerHTML = "";
  status = document.getElementById(nourut).style.display;
  if (status == "none") {
    document.getElementById(nourut).style.display = "block";
    post_response_text(tujuan, param, respog);
  } else {
    document.getElementById(nourut).style.display = "none";
  }
  //    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //                    showById('printPanel');
          document.getElementById(nourut).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanKeuanganLabaRugi() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  tujuan = "keu_slave_2laporankeuanganLabaRugi.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanKeuanganLabaRugi2() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  tujuan = "keu_slave_2laporankeuanganLabaRugi2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
//tutup

function switchHidden(id) {
  status = document.getElementById(id).style.display;
  if (status == "none") {
    document.getElementById(id).style.display = "block";
  } else {
    document.getElementById(id).style.display = "none";
  }
}

function getLaporanKeuangan() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  tujuan = "keu_slave_2laporankeuangan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function hitungSelisihHari(tgl1, tgl2) {
  tgl = tgl1.substr(0, 2);
  bln = tgl1.substr(3, 2);
  thn = tgl1.substr(6, 4);

  tg2 = tgl2.substr(0, 2);
  bln2 = tgl2.substr(3, 2);
  thn2 = tgl2.substr(6, 4);

  tgl11 = thn + "-" + bln + "-" + tgl;
  tgl22 = thn2 + "-" + bln2 + "-" + tg2;

  // varibel miliday sebagai pembagi untuk menghasilkan hari
  var miliday = 24 * 60 * 60 * 1000;
  //buat object Date
  var tanggal1 = new Date(tgl11);
  var tanggal2 = new Date(tgl22);
  // Date.parse akan menghasilkan nilai bernilai integer dalam bentuk milisecond
  var tglPertama = Date.parse(tanggal1);
  var tglKedua = Date.parse(tanggal2);
  var selisih = (tglKedua - tglPertama) / miliday;
  return selisih + 1;
}

function lapjurnal() {
  gudang = document.getElementById("gudang").value;
  periode = document.getElementById("periode").value;
  periode1 = document.getElementById("periode1").value;

  jlhhari = hitungSelisihHari(periode, periode1);

  //getLaporanJurnal('html');
  getLaporanJurnalv2("json");
  // if(gudang=='' || jlhhari>30){
  // getLaporanJurnalv2('json');
  // }else{
  // getLaporanJurnal('html');
  // }
}

function ambilPer2() {
  periode = document.getElementById("periode").value;
  periode2 = document.getElementById("periode2").value;
  if (periode2 == "")
    // alert(JSON.stringify(setValue2('periode2',periode)));
    setValue2("periode2", periode);
  // document.getElementById('periode2').value=periode;
}
var table;
function getLaporanBukuBesarv2(tipelaporan,num=0) {
  console.log(num);
  var result = new Array();
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode").value;
  periode2 = document.getElementById("periode2").value;
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;
  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&periode=" +
    periode +
    "&periode2=" +
    periode2 +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV +
    "&halaman=" +num;
  param += "&regional=" + regional + "&tipelaporan=" + tipelaporan;
  tujuan = "keu_slave_2bukubesarv2.php";
  if (tipelaporan == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
    busy_off();
    function respog() {
      busy_off();
      if (con.readyState == 4) {
        if (con.status == 200) {
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("Informasi", con.responseText);
          } else {
            try{
              result = JSON.parse(con.responseText);
            }catch(e){
              console.log(e);
              return false;
            }
            if(num == 0){
              showhide("show");
              try{
                document.getElementById("container").innerHTML = result.html;
                $(document).ready(function (){
                  table = $("#pvtTable").DataTable({
                    //fixedColumns: true,
                    data: result.data,
                    columnDefs: [
                      { width: 200, targets: 0 },
                      {
                        className: "dt-right",
                        targets: 17,
                        render: $.fn.dataTable.render.number(",", ".", 2, ""),
                      },
                      {
                        className: "dt-right",
                        targets: 18,
                        render: $.fn.dataTable.render.number(",", ".", 2, ""),
                      },
                      {
                        className: "dt-right",
                        targets: 16,
                        render: $.fn.dataTable.render.number(",", ".", 2, ""),
                      },
                    ],
                    order: [[3, "asc"]],
                    fixedHeader: true,
                    colReorder: true,
                    paging: true,
                    scrollX: true,
                    scrollCollapse: true,
                    iDisplayLength: result.rows,
                    scrollY: "65vh",
                    language: {
                      searchBuilder: {
                        button: "Filter",
                        title: "Filter",
                      },
                    },
                    buttons: [
                      {
                        text: "Show",
                        action: function () {
                          showhide("show");
                        },
                      },
                      "searchBuilder",
                      "csv",
                      "excel",
                      "print",
                    ],
                    dom: "Blfrtip",
                  });
                });
                table.table().container().style.borderTopWidth = '4px';
                table.table().container().style.borderLeftWidth = '0px';
                table.table().container().style.borderRightWidth = '0px';
                table.table().container().style.borderBottomWidth = '0px';
                table.table().container().style.borderStyle = 'solid';
                persenLoad = ((num+1)/parseInt(result.totalpage)*100);
                table.table().container().style.borderImage = 'linear-gradient(90deg, #f52f22 '+persenLoad+'%, #595a5c36 '+persenLoad+'% 100%) 1';
              }catch(e){
                console.log(e);
                return false;
              }
            }else if(num < result.totalpage){
              // add Rows
              console.log("add Row "+num+" FROM "+result.totalpage);
              try{
                if(table){
                  table.rows.add(result.data).draw();
                  persenLoad = ((num+1)/parseInt(result.totalpage)*100);
                  table.table().container().style.borderImage = 'linear-gradient(90deg, #f52f22 '+persenLoad+'%, #595a5c36 '+persenLoad+'% 100%) 1';
                }
              }catch(e){
                console.log(e);
                return false;
              }
            }
            num = num+1;
            if(num < result.totalpage){
              getLaporanBukuBesarv2(tipelaporan,num);
            }else{
              // console.log(table);
              table.table().container().style.borderTopWidth = '0px';
              $(table.table().container()).on("dblclick", "td", function () {
                var row = table.column(this);
                new $.fn.dataTable.FixedColumns(table, {
                  leftColumns: row.index() + 1,
                });
              });
              $("td").attr("title", "double click untuk freeze column");
              $('select[name*="pvtTable_length"]').attr(
                "style",
                "height:30px;"
              );
              //Ended
            }
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
}

function getLaporanJurnalv2(tipelaporan) {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  periodeV = periode.value;
  periode1 = document.getElementById("periode1");
  periode1 = periode1.value;
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  revisi = document.getElementById("revisi");
  revisi = revisi.options[revisi.selectedIndex].value;

  kdKel = document.getElementById("kdKel");
  kdKel = kdKel.options[kdKel.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;

  ref = document.getElementById("ref").value;
  ket = document.getElementById("ket").value;
  nojurnal = document.getElementById("nojurnal").value;
  nik = document.getElementById("nik").value;

  noakun = document.getElementById("noakun").value;
  nodok = document.getElementById("nodok").value;

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty !");
    return;
  }

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&periode=" +
    periodeV +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi +
    "&regional=" +
    regional;
  param +=
    "&kdKel=" +
    kdKel +
    "&ref=" +
    ref +
    "&ket=" +
    ket +
    "&nojurnal=" +
    nojurnal +
    "&nik=" +
    nik +
    "&tipelaporan=" +
    tipelaporan;
  param += "&noakun=" + noakun + "&nodok=" + nodok;
  tujuan = "keu_laporanJurnalxx.php";

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          fo = document.getElementById("formfilter");
          fo.style.display = "none";

          dt = con.responseText.split("####");
          document.getElementById("containerr").innerHTML = dt[0];
          //console.log(JSON.parse(dt[1]));
          $(document).ready(function () {
            // $('#pvtTable tfoot th').each( function () {
            // var title = $(this).text();
            // $(this).html( '<input type="text" class="myinputtextdt"  placeholder="Cari '+title+'" />' );
            // } );
            var table = $("#pvtTable").DataTable({
              //fixedColumns: true,
              fixedHeader: true,
              //colReorder: true,
              paging: true,
              iDisplayLength: 50,
              scrollY: "65vh",
              dom: "Blfrtip",
              // buttons: [
              // 'csv', 'excel'
              // ],
              //searchbuilder
              language: {
                searchBuilder: {
                  button: "Filter",
                  title: "Filter",
                },
              },
              buttons: [
                {
                  text: "Show",
                  action: function () {
                    showhide("show");
                  },
                },
                "searchBuilder",
                "csv",
                "excel",
              ],
              //searchbuilder
              data: JSON.parse(dt[1]),
              columnDefs: [
                {
                  className: "dt-body-right",
                  targets: 11,
                  render: $.fn.dataTable.render.number(",", ".", 2, ""),
                },
                {
                  className: "dt-body-right",
                  targets: 12,
                  render: $.fn.dataTable.render.number(",", ".", 2, ""),
                },
              ],
              order: [[0, "asc"]],
              rowGroup: {
                startRender: null,
                endRender: function (rows, group) {
                  var intVal = function (i) {
                    return typeof i === "string"
                      ? i.replace(",", "") * 1
                      : typeof i === "number"
                      ? i
                      : 0;
                  };

                  var totaldebet = rows
                    .data()
                    .pluck(11)
                    .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                    }, 0);
                  totaldebet = $.fn.dataTable.render
                    .number(",", ".", 2, "")
                    .display(totaldebet);

                  var totalkredit = rows
                    .data()
                    .pluck(12)
                    .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                    }, 0);
                  totalkredit = $.fn.dataTable.render
                    .number(",", ".", 2, "")
                    .display(totalkredit);

                  return $("<tr/>")
                    .append(
                      '<td colspan="11">Total for ' +
                        group +
                        " (in current page)</td>"
                    )
                    .append(
                      "<td style=text-align:right;>" + totaldebet + "</td>"
                    )
                    .append(
                      "<td style=text-align:right;>" + totalkredit + "</td>"
                    )
                    .append('<td/ colspan="12">');
                },
                dataSrc: 0,
              },
              // // GRAND TOTAL
              // "footerCallback": function ( row, data, start, end, display ) {
              // var api = this.api(), data;
              // var intVal = function (i) {
              // return typeof i === 'string' ?
              // i.replace(',', '') * 1 :
              // typeof i === 'number' ?
              // i : 0;
              // };

              // // debet
              // // Total over all pages
              // totalD = api.column( 11 ).data().reduce( function (a, b) {
              // return intVal(a) + intVal(b);
              // }, 0 );
              // totalD = $.fn.dataTable.render.number(',', '.', 2, '').display( totalD );

              // // Total over this page
              // pageTotal = api.column( 11, { page: 'current'} ).data().reduce( function (a, b) {
              // return intVal(a) + intVal(b);
              // }, 0 );
              // pageTotal = $.fn.dataTable.render.number(',', '.', 2, '').display( pageTotal );

              // // kredit
              // // Total over all pages
              // totalK = api.column( 12 ).data().reduce( function (a, b) {
              // return intVal(a) + intVal(b);
              // }, 0 );
              // totalK = $.fn.dataTable.render.number(',', '.', 2, '').display( totalK );

              // // Total over this page
              // pageTotalK = api.column( 12, { page: 'current'} ).data().reduce( function (a, b) {
              // return intVal(a) + intVal(b);
              // }, 0 );
              // pageTotalK = $.fn.dataTable.render.number(',', '.', 2, '').display( pageTotalK );

              // // Update footer
              // $( api.column( 11 ).footer() ).html('<input type="text" class="myinputtextnumberdt" style=\"width:100px;\" disabled=disabled value='+pageTotal+'><br><input type="text" class="myinputtextnumberdt" style=\"width:100px;\" disabled=disabled value='+totalD+'>');

              // $( api.column( 12 ).footer() ).html('<input type="text" class="myinputtextnumberdt" style=\"width:100px;\" disabled=disabled value='+pageTotalK+'><br><input type="text" class="myinputtextnumberdt" style=\"width:100px;\" disabled=disabled value='+totalK+'>');
              // },
              // // buat pencarian
              // initComplete: function () {
              // // Apply the search
              // this.api().columns().every( function () {
              // var that = this;
              // $( 'input', this.footer() ).on( 'keyup change clear', function () {
              // if ( that.search() !== this.value ) {
              // that
              // .search( this.value )
              // .draw();
              // }
              // } );
              // } );
              // }
            });

            $(table.table().container()).on("dblclick", "td", function () {
              var row = table.column(this);
              new $.fn.dataTable.FixedColumns(table, {
                leftColumns: row.index() + 1,
              });
            });

            $("td").attr("title", "double click untuk freeze column");
            $('select[name*="pvtTable_length"]').attr("style", "height:30px;");
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showhide(jenis) {
  fo = document.getElementById("formfilter");
  if (fo.style.display == "none") {
    fo.style.display = "block";
  } else {
    fo.style.display = "none";
  }
}

function getLaporanJurnal(tipelaporan) {
  pt = document.getElementById("pt");

  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  periodeV = periode.value;
  periode1 = document.getElementById("periode1");
  periode1 = periode1.value;
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  revisi = document.getElementById("revisi");
  revisi = revisi.options[revisi.selectedIndex].value;

  kdKel = document.getElementById("kdKel");
  kdKel = kdKel.options[kdKel.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;

  ref = document.getElementById("ref").value;
  ket = document.getElementById("ket").value;
  nojurnal = document.getElementById("nojurnal").value;
  nik = document.getElementById("nik").value;

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty !");
    return;
  }

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&periode=" +
    periodeV +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi +
    "&regional=" +
    regional;
  param +=
    "&kdKel=" +
    kdKel +
    "&ref=" +
    ref +
    "&ket=" +
    ket +
    "&nojurnal=" +
    nojurnal +
    "&nik=" +
    nik +
    "&tipelaporan=" +
    tipelaporan;
  tujuan = "keu_laporanJurnalxx.php";

  if (tipelaporan == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
  }

  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
          // showById('printPanel');
          document.getElementById("containerr").innerHTML = con.responseText;

          $(document).ready(function () {
            // Setup - add a text input to each footer cell
            $("#pvtTable tfoot th").each(function () {
              var title = $(this).text();
              $(this).html(
                '<input type="text" class="myinputtextdt" style="width:100px;" placeholder="Cari ' +
                  title +
                  '" />'
              );
            });

            // DataTable
            var table = $("#pvtTable").DataTable({
              //responsive: true,
              //fixedColumns: true,
              fixedHeader: true,
              colReorder: true,
              paging: true,
              iDisplayLength: 50,
              scrollY: 380,
              dom: "Bfrtip",
              buttons: ["csv", "excel"],
              // sub total
              order: [[0, "asc"]],
              rowGroup: {
                startRender: null,
                endRender: function (rows, group) {
                  var intVal = function (i) {
                    return typeof i === "string"
                      ? i.replace(/[\$,]/g, "") * 1
                      : typeof i === "number"
                      ? i
                      : 0;
                  };
                  var totaldebet = rows
                    .data()
                    .pluck(11)
                    .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                    }, 0);
                  totaldebet = $.fn.dataTable.render
                    .number(",", ".", 2, "")
                    .display(totaldebet);

                  var totalkredit = rows
                    .data()
                    .pluck(12)
                    .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                    }, 0);
                  totalkredit = $.fn.dataTable.render
                    .number(",", ".", 2, "")
                    .display(totalkredit);

                  return $("<tr/>")
                    .append('<td colspan="11">Total for ' + group + "</td>")
                    .append(
                      "<td style=text-align:right;>" + totaldebet + "</td>"
                    )
                    .append(
                      "<td style=text-align:right;>" + totalkredit + "</td>"
                    )
                    .append('<td/ colspan="11">');
                },
                dataSrc: 0,
              },

              // GRAND TOTAL
              footerCallback: function (row, data, start, end, display) {
                var api = this.api(),
                  data;
                var intVal = function (i) {
                  return typeof i === "string"
                    ? i.replace(/[\$,]/g, "") * 1
                    : typeof i === "number"
                    ? i
                    : 0;
                };
                // debet
                // Total over all pages
                total = api
                  .column(11)
                  .data()
                  .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                total = $.fn.dataTable.render
                  .number(",", ".", 2, "")
                  .display(total);

                // Total over this page
                pageTotal = api
                  .column(11, { page: "current" })
                  .data()
                  .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                pageTotal = $.fn.dataTable.render
                  .number(",", ".", 2, "")
                  .display(pageTotal);

                // kredit
                // Total over all pages
                totalK = api
                  .column(12)
                  .data()
                  .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                totalK = $.fn.dataTable.render
                  .number(",", ".", 2, "")
                  .display(totalK);

                // Total over this page
                pageTotalK = api
                  .column(12, { page: "current" })
                  .data()
                  .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                pageTotalK = $.fn.dataTable.render
                  .number(",", ".", 2, "")
                  .display(pageTotalK);

                // Update footer
                $(api.column(11).footer()).html(
                  '<input type="text" class="myinputtextnumberdt" style="width:100px;" disabled=disabled value=' +
                    pageTotal +
                    '><br><input type="text" class="myinputtextnumberdt" style="width:100px;" disabled=disabled value=' +
                    total +
                    ">"
                );

                $(api.column(12).footer()).html(
                  '<input type="text" class="myinputtextnumberdt" style="width:100px;" disabled=disabled value=' +
                    pageTotalK +
                    '><br><input type="text" class="myinputtextnumberdt" style="width:100px;" disabled=disabled value=' +
                    totalK +
                    ">"
                );
              },

              // buat pencarian
              initComplete: function () {
                // Apply the search
                this.api()
                  .columns()
                  .every(function () {
                    var that = this;
                    $("input", this.footer()).on(
                      "keyup change clear",
                      function () {
                        if (that.search() !== this.value) {
                          that.search(this.value).draw();
                        }
                      }
                    );
                  });
              },
            });
            // // Row selection (multiple rows)
            // $('#pvtTable tbody').on( 'click', 'tr', function () {
            // $(this).toggleClass('selected');
            // } );

            // // show hide kolom
            // $('button.dt-button').on( 'click', function (e) {
            // e.preventDefault();
            // // Get the column API object
            // var column = table.column( $(this).attr('data-column') );
            // // Toggle the visibility
            // column.visible( ! column.visible() );
            // } );
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getLaporanJurnalPiutangKaryawan() {
  tanggalmulai = document.getElementById("tanggalmulai");
  tanggalsampai = document.getElementById("tanggalsampai");
  noakun = document.getElementById("noakun");
  kodeorg = document.getElementById("kodeorg");
  tanggalmulaiV = tanggalmulai.value;
  tanggalsampaiV = tanggalsampai.value;
  noakunV = noakun.options[noakun.selectedIndex].value;
  kodeorgV = kodeorg.options[kodeorg.selectedIndex].value;
  param =
    "tanggalmulai=" +
    tanggalmulaiV +
    "&tanggalsampai=" +
    tanggalsampaiV +
    "&noakun=" +
    noakunV +
    "&kodeorg=" +
    kodeorgV;
  tujuan = "keu_laporanJurnalPiutangKaryawan.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getUsiaHutang() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;

  param = "pt=" + ptV + "&gudang=" + gudangV + "&periodeV=" + periodeV;
  tujuan = "keu_laporanUsiaHutang.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanBukuBesar(tipelaporan) {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  periode1 = document.getElementById("periode1");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  periodeV1 = periode1.options[periode1.selectedIndex].value;
  revisi = document.getElementById("revisi");
  revisi = revisi.options[revisi.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;
  tampilanId = document.getElementById("tampilanId");
  tampilanId = tampilanId.options[tampilanId.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&periode=" +
    periodeV +
    "&periode1=" +
    periodeV1 +
    "&revisi=" +
    revisi;
  param +=
    "&regional=" +
    regional +
    "&tampilanId=" +
    tampilanId +
    "&tipelaporan=" +
    tipelaporan;

  akundari = document.getElementById("akundari");
  if (akundari) {
    param += "&akundari=" + akundari.value;
  }
  akunsampai = document.getElementById("akunsampai");
  if (akunsampai) {
    param += "&akunsampai=" + akunsampai.value;
  }

  tujuan = "keu_slave_2bukubesar.php";

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty");
    return;
  }
  // alertify.alert("Informasi",param);
  if (tipelaporan == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
  }

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
          leftFixedTable();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getLaporanNeracaCoba() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;

  param = "pt=" + ptV + "&gudang=" + gudangV + "&periode=" + periodeV;
  tujuan = "keu_laporanNeracaCoba.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getMesinLaporan() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;

  param = "pt=" + ptV + "&gudang=" + gudangV + "&periode=" + periodeV;
  tujuan = "keu_slave_2mesinlaporan.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getLaporanNeraca() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  periode1 = document.getElementById("periode1");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;
  periode1 = periode1.options[periode1.selectedIndex].value;
  revisi = document.getElementById("revisi");
  revisi = revisi.options[revisi.selectedIndex].value;
  param =
    "pt=" +
    pt +
    "&unit=" +
    unit +
    "&periode=" +
    periode +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi;
  tujuan = "keu_slave_2neraca.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanNeracaPeriodik() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  tujuan = "keu_slave_2neracaPeriodik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanRugiLaba() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  gudang = gudang.options[gudang.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&gudang=" + gudang + "&periode=" + periode;
  tujuan = "keu_slave_2rugilaba.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanRugiLabaPeriodik() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  gudang = gudang.options[gudang.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param = "pt=" + pt + "&gudang=" + gudang + "&periode=" + periode;
  tujuan = "keu_slave_2rugilabaperiodik.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanArusKas() {
  pt = document.getElementById("pt").value;
  gudang = document.getElementById("gudang").value;
  periode = document.getElementById("periode").value;
  tanggal = document.getElementById("tgl_cari").value;

  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&proses=preview" +
    "&tanggal=" +
    tanggal;
  tujuan = "keu_slave_2aruskas.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function Excelaruskas(ev, tujuan) {
  pt = document.getElementById("pt").value;
  gudang = document.getElementById("gudang").value;
  periode = document.getElementById("periode").value;
  tanggal = document.getElementById("tgl_cari").value;

  judul = "Report Ms.Excel";
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&proses=excel" +
    "&tanggal=" +
    tanggal;

  printFile(param, tujuan, judul, ev);
}

function getLaporanArusKasLangsung() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;

  param = "pt=" + ptV + "&gudang=" + gudangV + "&periode=" + periodeV;
  tujuan = "keu_slave_2aruskasLangsung.php";
  post_response_text(tujuan, param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fisikKeExcel(ev, tujuan) {
  pt = document.getElementById("pt");

  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  revisi = "";
  try {
    periode1 = document.getElementById("periode1").value;
    revisi = document.getElementById("revisi");
    revisi = revisi.options[revisi.selectedIndex].value;
  } catch (err) {
    periode1 = "";
    ref = "";
    ket = "";
  }

  pt = pt.options[pt.selectedIndex].value;
  gudang = gudang.options[gudang.selectedIndex].value;
  periode = periode.value;

  regional = document.getElementById("regional");
  kdKel = document.getElementById("kdKel");

  ref = document.getElementById("ref");
  ket = document.getElementById("ket");

  nojurnal = document.getElementById("nojurnal");

  akun = document.getElementById("akun");
  kegiatan = document.getElementById("kegiatan");
  //tampilanId =document.getElementById('tampilanId').options[document.getElementById('tampilanId').selectedIndex].value;
  if (pt == "") {
    alertify.alert("Informasi", "Field PT empty!");
    return;
  }

  judul = "Report Ms.Excel";
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi; //'&tampilanId='+tampilanId;

  if (kdKel) {
    param += "&kdKel=" + kdKel.value;
  }

  if (regional) {
    param += "&regional=" + regional.options[regional.selectedIndex].value;
  }

  if (nojurnal) {
    param += "&nojurnal=" + nojurnal.value;
  }

  if (ref) {
    param += "&ref=" + ref.value;
  }

  if (ket) {
    param += "&ket=" + ket.value;
  }

  if (akun) {
    param += "&akun=" + akun.value;
  }

  if (kegiatan) {
    param += "&kegiatan=" + kegiatan.value;
  }

  printFile(param, tujuan, judul, ev);
}

function piutangKaryawanKeExcel(ev, tujuan) {
  tanggalmulai = document.getElementById("tanggalmulai");
  tanggalsampai = document.getElementById("tanggalsampai");
  noakun = document.getElementById("noakun");
  kodeorg = document.getElementById("kodeorg");
  namakaryawan = document.getElementById("namakaryawan");
  tanggalmulaiV = tanggalmulai.value;
  tanggalsampaiV = tanggalsampai.value;
  noakunV = noakun.options[noakun.selectedIndex].value;
  kodeorgV = kodeorg.options[kodeorg.selectedIndex].value;

  judul = "Report Ms.Excel";
  param =
    "tanggalmulai=" +
    tanggalmulaiV +
    "&tanggalsampai=" +
    tanggalsampaiV +
    "&noakun=" +
    noakunV +
    "&kodeorg=" +
    kodeorgV;
  printFile(param, tujuan, judul, ev);
}

function piutangKaryawanKePDF(ev, tujuan) {
  tanggalmulai = document.getElementById("tanggalmulai");
  tanggalsampai = document.getElementById("tanggalsampai");
  noakun = document.getElementById("noakun");
  namakaryawan = document.getElementById("namakaryawan");
  tanggalmulaiV = tanggalmulai.value;
  tanggalsampaiV = tanggalsampai.value;
  noakunV = noakun.options[noakun.selectedIndex].value;
  namakaryawanV = namakaryawan.options[namakaryawan.selectedIndex].value;
  judul = "Report PDF";
  param =
    "tanggalmulai=" +
    tanggalmulaiV +
    "&tanggalsampai=" +
    tanggalsampaiV +
    "&noakun=" +
    noakunV +
    "&namakaryawan=" +
    namakaryawanV;
  printFile(param, tujuan, judul, ev);
}

function detailMutasiBarang(
  ev,
  pt,
  periode,
  gudang,
  kodebarang,
  namabarang,
  satuan
) {
  tujuan = "log_laporanMutasiDetailPerBarang_pdf.php";
  judul = "Report PDF";
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&namabarang=" +
    namabarang +
    "&satuan=" +
    satuan +
    "&kodebarang=" +
    kodebarang;
  printFile(param, tujuan, judul, ev);
}
function detailMutasiBarangHarga(
  ev,
  pt,
  periode,
  gudang,
  kodebarang,
  namabarang,
  satuan
) {
  tujuan = "log_laporanMutasiDetailPerBarangHarga_pdf.php";
  judul = "Report PDF";
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&namabarang=" +
    namabarang +
    "&satuan=" +
    satuan +
    "&kodebarang=" +
    kodebarang;
  printFile(param, tujuan, judul, ev);
}

function fisikKePDF(ev, tujuan) {
  pt = document.getElementById("pt");

  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  revisi = "";
  try {
    periode1 =
      document.getElementById("periode1").options[
        document.getElementById("periode1").selectedIndex
      ].value;
    revisi = document.getElementById("revisi");
    revisi = revisi.options[revisi.selectedIndex].value;
  } catch (err) {
    periode1 = "";
  }
  pt = pt.options[pt.selectedIndex].value;
  gudang = gudang.options[gudang.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  regional = document.getElementById("regional");
  if (regional) regional = regional.options[regional.selectedIndex].value;

  kdKel = document.getElementById("kdKel");
  ref = document.getElementById("ref");
  ket = document.getElementById("ket");
  ref = ref ? ref.value : (ref = "");
  ket = ket ? ket.value : (ket = "");
  nojurnal = document.getElementById("nojurnal");

  if (pt == "") {
    alertify.alert("Informasi", "Field PT empty!");
    return;
  }

  tampilanId =
    document.getElementById("tampilanId").options[
      document.getElementById("tampilanId").selectedIndex
    ].value;
  judul = "Report PDF";
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi;
  param += "&ref=" + ref + "&ket=" + ket + "&tampilanId=" + tampilanId;

  param += "&regional=" + regional;
  if (kdKel) {
    param += "&kdKel=" + kdKel.value;
  }
  if (nojurnal) {
    param += "&nojurnal=" + nojurnal.value;
  }

  akundari = document.getElementById("akundari");
  if (akundari) {
    param += "&akundari=" + akundari.value;
  }
  akunsampai = document.getElementById("akunsampai");
  if (akunsampai) {
    param += "&akunsampai=" + akunsampai.value;
  }

  printFile(param, tujuan, judul, ev);
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "900";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1(title, content, width, height, ev);
}

function getLaporanFisikHarga() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  gudang = gudang.options[gudang.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;
  document.getElementById("orglegend").innerHTML = pt + "-" + gudang;
  param = "pt=" + pt + "&gudang=" + gudang + "&periode=" + periode;
  tujuan = "log_laporanPersediaanFisikHarga.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ambilAnak(pt) {
  param = "pt=" + pt;
  tujuan = "keu_slave_getUnit.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("gudang").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ambilAnakBB(pt) {
  // list untuk buku besar, lihat tipe lokasi tugas
  param = "pt=" + pt + "&tipe=bb";
  tujuan = "keu_slave_getUnit.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("gudang").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ambilAnakPA(pt) {
  // list untuk periode akuntansi, lihat tipe lokasi tugas
  param = "pt=" + pt + "&tipe=pa";
  tujuan = "keu_slave_getUnit.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("kodeunit").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function ambilAkun2(akun) {
  param = "pam=1&akun=" + akun;
  tujuan = "keu_slave_getAkun2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("akunsampai").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cekTanggal1(tanggal1) {
  tanggal2 = document.getElementById("tgl2").value;
  param = "pam=2&tanggal1=" + tanggal1 + "&tanggal2=" + tanggal2;
  //	param='pam=2&tanggal1='+tanggal1;
  tujuan = "keu_slave_getAkun2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
          document.getElementById("tgl1").value = "";
        } else {
          //						document.getElementById('akunsampai').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function cekTanggal2(tanggal2) {
  tanggal1 = document.getElementById("tgl1").value;
  param = "pam=3&tanggal1=" + tanggal1 + "&tanggal2=" + tanggal2;
  tujuan = "keu_slave_getAkun2.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
          document.getElementById("tgl2").value = "";
        } else {
          //						document.getElementById('akunsampai').innerHTML=con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

// indra
function getLaporanBukuBesarv1(tipelaporan) {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  tanggal1 = document.getElementById("tgl1");
  tanggal2 = document.getElementById("tgl2");
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  tanggal1V = tanggal1.value;
  tanggal2V = tanggal2.value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty");
    return;
  }
  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&tanggal1=" +
    tanggal1V +
    "&tanggal2=" +
    tanggal2V +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  param += "&regional=" + regional + "&tipelaporan=" + tipelaporan;
  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_2bukubesarv1.php";
  if (tipelaporan == "excel") {
    judul = "Report Ms.Excel";
    ev = "event";
    printFile(param, tujuan, judul, ev);
  } else {
    post_response_text(tujuan, param, respog);
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alertify.alert("Informasi", con.responseText);
          } else {
            showById("printPanel");
            document.getElementById("container").innerHTML = con.responseText;
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
}

function getLaporanBukuBesarHutangv1() {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  tanggal1 = document.getElementById("tgl1");
  tanggal2 = document.getElementById("tgl2");
  akundari = document.getElementById("akundari");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  tanggal1V = tanggal1.value;
  tanggal2V = tanggal2.value;
  akundariV = akundari.options[akundari.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&tanggal1=" +
    tanggal1V +
    "&tanggal2=" +
    tanggal2V +
    "&akundari=" +
    akundariV;
  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_2bukubesarhutangv1.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanCatatanNeraca() {
  pt = document.getElementById("pt");
  periode = document.getElementById("periode");
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&periode=" +
    periodeV +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_2catatanNeraca.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function showDetail(nourut, keterangan, ev) {
  judul = "Detail " + keterangan;
  param = "nourut=" + nourut;
  printFile(param, "keu_slave_2neraca_detail.php", judul, ev);
}

function lihatDetail(
  noakun,
  periode,
  periode1,
  lmperiode,
  pt,
  regional,
  gudang,
  revisi,
  ev
) {
  param = "noakun=" + noakun + "&periode=" + periode + "&periode1=" + periode1;
  param +=
    "&lmperiode=" +
    lmperiode +
    "&pt=" +
    pt +
    "&regional=" +
    regional +
    "&gudang=" +
    gudang +
    "&revisi=" +
    revisi;
  tujuan = "keu_slave_getBBDetail.php" + "?" + param;
  width = "950";
  height = "400";

  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1("Detail Jurnal" + noakun, content, width, height, ev);
}

function detailKeExcel(ev, tujuan) {
  width = "700";
  height = "400";

  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1("Detail Jurnal", content, width, height, ev);
}

function jurnalv1KeExcel(ev, tujuan) {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  tanggal1 = document.getElementById("tgl1");
  tanggal2 = document.getElementById("tgl2");
  akundari = document.getElementById("akundari");
  try {
    akunsampai = document.getElementById("akunsampai");
    akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;
  } catch (err) {
    akunsampaiV = "";
  }

  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  tanggal1V = tanggal1.value;
  tanggal2V = tanggal2.value;
  akundariV = akundari.options[akundari.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional ? regional.options[regional.selectedIndex].value : "";

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty");
    return;
  }

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&tanggal1=" +
    tanggal1V +
    "&tanggal2=" +
    tanggal2V +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  param += "&regional=" + regional;
  //alertify.alert("Informasi",param);

  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

function catatanNeracaKeExcel(ev, tujuan) {
  pt = document.getElementById("pt");
  periode = document.getElementById("periode");
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&periode=" +
    periodeV +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  //alertify.alert("Informasi",param);

  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

function catatanNeracaKePDF(ev, tujuan) {
  pt = document.getElementById("pt");
  periode = document.getElementById("periode");
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;

  param =
    "pt=" +
    ptV +
    "&periode=" +
    periodeV +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  //alertify.alert("Informasi",param);

  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function jurnalv1KePDF(ev, tujuan) {
  pt = document.getElementById("pt");
  gudang = document.getElementById("gudang");
  tanggal1 = document.getElementById("tgl1");
  tanggal2 = document.getElementById("tgl2");
  akundari = document.getElementById("akundari");
  akunsampai = document.getElementById("akunsampai");
  ptV = pt.options[pt.selectedIndex].value;
  gudangV = gudang.options[gudang.selectedIndex].value;
  tanggal1V = tanggal1.value;
  tanggal2V = tanggal2.value;
  akundariV = akundari.options[akundari.selectedIndex].value;
  akunsampaiV = akunsampai.options[akunsampai.selectedIndex].value;

  regional = document.getElementById("regional");
  regional = regional.options[regional.selectedIndex].value;

  if (ptV == "") {
    alertify.alert("Informasi", "Field PT empty");
    return;
  }

  param =
    "pt=" +
    ptV +
    "&gudang=" +
    gudangV +
    "&tanggal1=" +
    tanggal1V +
    "&tanggal2=" +
    tanggal2V +
    "&akundari=" +
    akundariV +
    "&akunsampai=" +
    akunsampaiV;
  param += "&regional=" + regional;
  //alertify.alert("Informasi",param);

  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function ambilJurnal() {
  unit = document.getElementById("unit");
  unitV = unit.options[unit.selectedIndex].value;
  periode = document.getElementById("periode");
  periodeV = periode.options[periode.selectedIndex].value;
  param = "pam=1&unit=" + unitV + "&periode=" + periodeV;
  tujuan = "keu_slave_getJurnal.php";
  if (unitV != "" && periodeV != "") post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          document.getElementById("jurnaldari").innerHTML = con.responseText;
          document.getElementById("jurnalsampai").innerHTML = con.responseText;
          hideById("printPanel");
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanPeriksaJurnal() {
  unit = document.getElementById("unit");
  periode = document.getElementById("periode");
  jurnaldari = document.getElementById("jurnaldari");
  jurnalsampai = document.getElementById("jurnalsampai");
  unitV = unit.options[unit.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  jurnaldariV = jurnaldari.options[jurnaldari.selectedIndex].value;
  jurnalsampaiV = jurnalsampai.options[jurnalsampai.selectedIndex].value;

  param =
    "unit=" +
    unitV +
    "&periode=" +
    periodeV +
    "&jurnaldari=" +
    jurnaldariV +
    "&jurnalsampai=" +
    jurnalsampaiV;
  //alertify.alert("Informasi",param);
  tujuan = "keu_slave_2periksaJurnal.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("containerr").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function periksajurnalKeExcel(ev, tujuan) {
  unit = document.getElementById("unit");
  periode = document.getElementById("periode");
  jurnaldari = document.getElementById("jurnaldari");
  jurnalsampai = document.getElementById("jurnalsampai");
  unitV = unit.options[unit.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  jurnaldariV = jurnaldari.options[jurnaldari.selectedIndex].value;
  jurnalsampaiV = jurnalsampai.options[jurnalsampai.selectedIndex].value;

  param =
    "unit=" +
    unitV +
    "&periode=" +
    periodeV +
    "&jurnaldari=" +
    jurnaldariV +
    "&jurnalsampai=" +
    jurnalsampaiV;
  //alertify.alert("Informasi",param);

  judul = "Report Ms.Excel";
  printFile(param, tujuan, judul, ev);
}

function periksajurnalKePDF(ev, tujuan) {
  unit = document.getElementById("unit");
  periode = document.getElementById("periode");
  jurnaldari = document.getElementById("jurnaldari");
  jurnalsampai = document.getElementById("jurnalsampai");
  unitV = unit.options[unit.selectedIndex].value;
  periodeV = periode.options[periode.selectedIndex].value;
  jurnaldariV = jurnaldari.options[jurnaldari.selectedIndex].value;
  jurnalsampaiV = jurnalsampai.options[jurnalsampai.selectedIndex].value;

  param =
    "unit=" +
    unitV +
    "&periode=" +
    periodeV +
    "&jurnaldari=" +
    jurnaldariV +
    "&jurnalsampai=" +
    jurnalsampaiV;
  //alertify.alert("Informasi",param);

  judul = "Report PDF";
  printFile(param, tujuan, judul, ev);
}

function lihatDetailHutang(
  kodesupplier,
  noakun,
  mulai,
  sampai,
  kodeorg,
  tipe,
  ev
) {
  param =
    "noakun=" +
    noakun +
    "&mulai=" +
    mulai +
    "&sampai=" +
    sampai +
    "&kodesupplier=" +
    kodesupplier +
    "&kodeorg=" +
    kodeorg;
  if (tipe) {
    param += "&tipe=" + tipe;
  }

  tujuan = "keu_slave_laporan_HutangPiutang.php" + "?" + param;
  width = "1000";
  height = "400";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog1("Detail Jurnal" + noakun, content, width, height, ev);
}

function getPeriodeAkuntansi() {
  kodept = document.getElementById("kodept");
  kodeptV = kodept.options[kodept.selectedIndex].value;
  kodeunit = document.getElementById("kodeunit");
  kodeunitV = kodeunit.options[kodeunit.selectedIndex].value;

  param = "kodept=" + kodeptV + "&kodeunit=" + kodeunitV;
  tujuan = "keu_slave_2periodeAkuntansi.php";

  if (kodeptV == "") {
    alertify.alert("Informasi", "Please choose...");
  } else post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //						showById('printPanel');
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function getLaporanKeuanganLabaRugiv1() {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;
  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  tujuan = "keu_slave_2laporankeuanganLabaRugiv1.php";
  post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          showById("printPanel");
          document.getElementById("container").innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function getLaporanKeuanganDetailv1(nourut, tipe) {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;

  param =
    "pt=" +
    pt +
    "&unit=" +
    unit +
    "&periode=" +
    periode +
    "&nourut=" +
    nourut +
    "&tipe=" +
    tipe;
  tujuan = "keu_slave_2laporankeuangan_detailv1.php";

  document.getElementById(nourut).innerHTML = "";
  status = document.getElementById(nourut).style.display;
  if (status == "none") {
    document.getElementById(nourut).style.display = "block";
    post_response_text(tujuan, param, respog);
  } else {
    document.getElementById(nourut).style.display = "none";
  }
  //    post_response_text(tujuan, param, respog);

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          //                    showById('printPanel');
          document.getElementById(nourut).innerHTML = con.responseText;
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}
function fisikKeExcel2(ev, tujuan) {
  pt = document.getElementById("pt");
  unit = document.getElementById("gudang");
  periode = document.getElementById("periode");
  pt = pt.options[pt.selectedIndex].value;
  unit = unit.options[unit.selectedIndex].value;
  periode = periode.options[periode.selectedIndex].value;
  akundari = document.getElementById("akundari");

  param = "pt=" + pt + "&unit=" + unit + "&periode=" + periode;
  judul = "Report Ms.Excel";
  //param='pt='+pt+'&gudang='+gudang+'&periode='+periode+'&periode1='+periode1+'&revisi='+revisi;
  printFile(param, tujuan, judul, ev);
}
function getNoakun() {
  kdpt = document.getElementById("kodeorg");
  kdpt = kdpt.options[kdpt.selectedIndex].value;
  param = "kodept=" + kdpt;
  tujuan = "keu_slave_2jurnalPiutangStaff.php";
  post_response_text(tujuan + "?proses=getNoakun", param, respog);
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert("Informasi", con.responseText);
        } else {
          dt = con.responseText.split("####");
          document.getElementById("noakun").innerHTML = dt[0];
          // document.getElementById('tanggalmulai').innerHTML=dt[1];
          // document.getElementById('tanggalsampai').innerHTML=dt[1];
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function fisikKePDFneraca() {
  pt = document.getElementById("pt").value;
  gudang = document.getElementById("gudang").value;
  periode = document.getElementById("periode").value;
  periode1 = document.getElementById("periode1").value;
  revisi = document.getElementById("revisi").value;
  param =
    "pt=" +
    pt +
    "&gudang=" +
    gudang +
    "&periode=" +
    periode +
    "&periode1=" +
    periode1 +
    "&revisi=" +
    revisi;
  tujuan = "keu_laporanNeraca_pdf.php?" + param;
  title = "";
  width = "1000";
  height = "700";
  ev = "event";
  content =
    "<iframe frameborder=0 width=100% height=100% src='" +
    tujuan +
    "'></iframe>";
  showDialog2(title, content, width, height, ev);
}