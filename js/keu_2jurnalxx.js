// JS khusus untuk halaman Laporan Jurnal (keu_2jurnalxx.php).
// Dipisah dari js/keu_laporanxx.js (dipakai 20+ laporan lain) supaya perubahan di sini
// tidak pernah menyenggol laporan lain. Fungsi di bawah adalah salinan versi terbaru
// (sudah termasuk fix server-side pagination, streaming export, dsb) dari file bersama itu.

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

function lapjurnal() {
  gudang = document.getElementById("gudang").value;
  periode = document.getElementById("periode").value;
  periode1 = document.getElementById("periode1").value;

  jlhhari = hitungSelisihHari(periode, periode1);

  getLaporanJurnalv2("json");
}

function showhide(jenis) {
  fo = document.getElementById("formfilter");
  if (fo.style.display == "none") {
    fo.style.display = "block";
  } else {
    fo.style.display = "none";
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

          document.getElementById("containerr").innerHTML = con.responseText;
          $(document).ready(function () {
            var table = $("#pvtTable").DataTable({
              fixedHeader: true,
              serverSide: true,
              processing: true,
              ordering: false,
              searching: false,
              paging: true,
              iDisplayLength: 50,
              scrollY: "65vh",
              dom: "Blfrtip",
              buttons: [
                {
                  text: "Show",
                  action: function () {
                    showhide("show");
                  },
                },
                {
                  text: "CSV/Excel",
                  action: function () {
                    // export SEMUA data sesuai filter yang lagi aktif (bukan cuma 50 baris yang lagi tampil) -
                    // pakai jalur export yang sama dengan tombol Excel di form (streaming, cepat, aman untuk data besar).
                    getLaporanJurnal("excel");
                  },
                },
              ],
              ajax: {
                url: "keu_laporanJurnalxx.php",
                type: "POST",
                data: function (d) {
                  d.pt = ptV;
                  d.gudang = gudangV;
                  d.periode = periodeV;
                  d.periode1 = periode1;
                  d.revisi = revisi;
                  d.regional = regional;
                  d.kdKel = kdKel;
                  d.ref = ref;
                  d.ket = ket;
                  d.nojurnal = nojurnal;
                  d.nik = nik;
                  d.noakun = noakun;
                  d.nodok = nodok;
                  d.tipelaporan = "json";
                },
              },
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

  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert(con.responseText);
        } else {
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
          });
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }
}

function printFile(param, tujuan, title, ev) {
  tujuan = tujuan + "?" + param;
  width = "900";
  height = "400";
  // proses di server bisa makan waktu (generate laporan besar) - kasih indikator loading
  // di atas iframe supaya user tahu masih diproses, bukan diem/macet.
  // sebagian endpoint langsung redirect ke file download, dan event 'onload' iframe tidak
  // selalu reliable untuk kasus itu, jadi tambah batas waktu aman: otomatis disembunyikan
  // kalau lewat 90 detik walau onload tidak kepanggil.
  content =
    "<div style='position:relative;width:100%;height:100%;'>" +
    "<div id='printFileLoading' style='position:absolute;top:0;left:0;width:100%;height:100%;background:#fff;display:flex;align-items:center;justify-content:center;flex-direction:column;z-index:2;'>" +
    "<img src='images/progress.gif'>" +
    "<div style='margin-top:8px;font-size:13px;color:#333;'>Sedang memproses laporan, mohon tunggu...</div>" +
    "</div>" +
    "<iframe frameborder=0 width=100% height=100% style='position:relative;z-index:1;' src='" +
    tujuan +
    "' onload=\"var el=document.getElementById('printFileLoading'); if(el){el.style.display='none';}\"></iframe>" +
    "</div>";
  showDialog1(title, content, width, height, ev);
  setTimeout(function () {
    var el = document.getElementById("printFileLoading");
    if (el) {
      el.style.display = "none";
    }
  }, 90000);
}
