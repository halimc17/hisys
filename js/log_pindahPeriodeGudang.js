/**
 * @author repindra.ginting
 */
function setSloc(x)
{
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;

	if (gudang != '') {
        if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			tujuan = 'log_slave_getTutupBukuFisik.php';
			param='gudang='+gudang;
			post_response_text(tujuan, param, respog);
        }
        else {
			document.getElementById('sloc').disabled = false;
			document.getElementById('sloc').options[0].selected=true;
			document.getElementById('btnsloc').disabled = false;
			document.getElementById('infoDisplay').innerHTML='';
        }	
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						document.getElementById('infoDisplay').innerHTML=con.responseText;
						alertify.alert(con.responseText);
				}
				else {
						document.getElementById('infoDisplay').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// ==== Tutup buku fisik: per periode, dengan panel progres dan ringkasan hasil sebelum logout ====
// awal..akhir = nomor baris gudang pada satu periode; periodeTerlama = periode terlama yang masih terbuka
function pilihSemuaGudang(awal,akhir,status)
{
       for (var i=awal;i<=akhir;i++) {
              var cb=document.getElementById('pilihan'+i);
              if (cb) cb.checked=status;
       }
}
function saveSaldoFisik(awal,akhir,btn,periodeTerlama)
{
       var periodeIni=trim(document.getElementById('periode'+awal).innerHTML);
       var jumlah=0;
       for (var i=awal;i<=akhir;i++) {
              if (document.getElementById('pilihan'+i).checked) jumlah++;
       }
       if (jumlah==0) {
              alertify.alert('Belum ada gudang yang dipilih pada periode '+periodeIni+'.');
              return;
       }
       if (periodeTerlama!='' && periodeIni!=periodeTerlama) {
              if (!confirm('Periode '+periodeIni+' BUKAN periode terlama yang masih terbuka ('+periodeTerlama+'). Pastikan periode sebelumnya sudah selesai. Lanjutkan tutup buku periode '+periodeIni+'?')) {
                     return;
              }
       }
       if (!confirm('Tutup buku periode '+periodeIni+' untuk '+jumlah+' gudang yang dicentang, anda yakin..?')) {
              return;
       }
       btn.disabled=true;
       mulaiTutupBuku(awal,akhir,periodeIni);
}

function ptbEsc(teks)
{
       return String(teks).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
// panel di tengah layar: progres saat berjalan, lalu ringkasan hasil
function ptbPanel(isi)
{
       var p=document.getElementById('ptbOverlay');
       if (!p) {
              p=document.createElement('div');
              p.setAttribute('id','ptbOverlay');
              p.style.cssText='position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:100000;display:flex;align-items:center;justify-content:center;';
              document.body.appendChild(p);
       }
       p.style.display='flex';
       p.innerHTML="<div style='background:#fff;width:640px;max-width:92%;max-height:85%;overflow:auto;border-radius:6px;box-shadow:0 4px 24px rgba(0,0,0,0.4);font-size:13px;'>"+isi+"</div>";
}
function ptbTutupPanel()
{
       var p=document.getElementById('ptbOverlay');
       if (p) p.style.display='none';
}

hasilTutup=[];
function mulaiTutupBuku(awal,akhir,periodeIni)
{
       hasilTutup={periode:periodeIni,sukses:[],gagal:[],tidakDipilih:[],selesai:0,total:0,antrian:[]};
       for (var i=awal;i<=akhir;i++) {
              var kode=trim(document.getElementById('kodeorg'+i).innerHTML);
              if (document.getElementById('pilihan'+i).checked) {
                     hasilTutup.antrian.push(i);
              } else {
                     hasilTutup.tidakDipilih.push(kode);
              }
       }
       hasilTutup.total=hasilTutup.antrian.length;
       ptbTampilProgres('');
       tutupSatuGudang(0);
}
function ptbTampilProgres(sedang)
{
       var persen=hasilTutup.total>0 ? Math.round(hasilTutup.selesai*100/hasilTutup.total) : 0;
       var daftar='';
       for (var a=0;a<hasilTutup.sukses.length;a++) {
              daftar+="<div>&#10003; "+ptbEsc(hasilTutup.sukses[a])+" - sudah ditutup</div>";
       }
       for (var b=0;b<hasilTutup.gagal.length;b++) {
              daftar+="<div>&#10007; "+ptbEsc(hasilTutup.gagal[b].gudang)+" - belum bisa ditutup</div>";
       }
       ptbPanel(
              "<div style='padding:10px 14px;background:#3a5470;color:white;font-weight:bold;font-size:14px;'>Menutup buku periode "+ptbEsc(hasilTutup.periode)+"</div>"
              +"<div style='padding:14px;'>"
              +"<div style='margin-bottom:6px;'>"+(sedang!='' ? "Sedang memproses gudang <b>"+ptbEsc(sedang)+"</b> ("+(hasilTutup.selesai+1)+" dari "+hasilTutup.total+")..." : "Menyiapkan...")+"</div>"
              +"<div style='background:#dde6f3;height:16px;border-radius:8px;overflow:hidden;'><div style='background:#3a5470;height:16px;width:"+persen+"%;transition:width .3s;'></div></div>"
              +"<div style='margin:4px 0 10px;font-size:11px;color:#555;'>"+persen+"% selesai. Jangan tutup atau muat ulang halaman ini.</div>"
              +"<div style='max-height:180px;overflow:auto;font-size:12px;border-top:1px solid #dde6f3;padding-top:6px;'>"+daftar+"</div>"
              +"</div>"
       );
}
function tutupSatuGudang(urut)
{
       if (urut>=hasilTutup.antrian.length) {
              selesaiTutupBuku();
              return;
       }
       var no=hasilTutup.antrian[urut];
       var periode=trim(document.getElementById('periode'+no).innerHTML);
       var gudang=trim(document.getElementById('kodeorg'+no).innerHTML);
       var tanggalmulai=trim(document.getElementById('tanggalmulai'+no).innerHTML);
       var tanggalsampai=trim(document.getElementById('tanggalsampai'+no).innerHTML);
       var param='periode='+periode+'&gudang='+gudang+'&tanggalmulai='+tanggalmulai+'&tanggalsampai='+tanggalsampai;
       document.getElementById('row'+no).style.backgroundColor='#eef3fb';
       ptbTampilProgres(gudang);
       post_response_text('log_slave_saveTutupBukuFisik.php', param, respog);
       function respog(){
              if (con.readyState == 4) {
                     busy_off();
                     var baris=document.getElementById('row'+no);
                     var kolom=document.getElementById('status'+no);
                     if (con.status == 200 && isSaveResponse(con.responseText)) {
                            hasilTutup.sukses.push(gudang);
                            baris.style.backgroundColor='';
                            if (kolom) kolom.innerHTML="<b>&#10003; Sudah ditutup</b>";
                            document.getElementById('pilihan'+no).checked=false;
                            document.getElementById('pilihan'+no).disabled=true;
                     } else {
                            var pesan=(con.status == 200) ? trim(con.responseText) : ('kode '+con.status);
                            pesan=pesan.replace(/<br\s*\/?>/gi,' ; ').replace(/<[^>]+>/g,' ').replace(/^\s*Error,\s*/i,'').replace(/\s+/g,' ');
                            hasilTutup.gagal.push({gudang:gudang,pesan:pesan});
                            baris.style.backgroundColor='';
                            if (kolom) kolom.innerHTML="<b>&#10007; Belum bisa ditutup</b>";
                     }
                     hasilTutup.selesai++;
                     tutupSatuGudang(urut+1);
              }
       }
}
function selesaiTutupBuku()
{
       if (hasilTutup.sukses.length>0 && hasilTutup.gagal.length==0) {
              // seperti sebelumnya: pindahkan saldo awal ke periode berikutnya hanya kalau semua gudang terpilih berhasil ditutup
              saldoact(hasilTutup.periode);
       } else {
              ringkasanTutupBuku('');
       }
}
function ringkasanTutupBuku(catatan)
{
       var h=hasilTutup;
       var logoutSetelah=(h.sukses.length>0 && catatan=='');
       var s="<div style='padding:10px 14px;background:#3a5470;color:white;font-weight:bold;font-size:14px;'>Hasil tutup buku periode "+ptbEsc(h.periode)+"</div><div style='padding:14px;'>";
       s+="<div style='margin-bottom:8px;'><b>Berhasil ditutup: "+h.sukses.length+" gudang</b> &nbsp;|&nbsp; <b>Belum bisa ditutup: "+h.gagal.length+" gudang</b>"+(h.tidakDipilih.length>0 ? " &nbsp;|&nbsp; <b>Tidak dipilih: "+h.tidakDipilih.length+" gudang</b>" : "")+"</div>";
       if (h.sukses.length>0) {
              s+="<div style='font-weight:bold;margin-top:8px;'>&#10003; Sudah ditutup</div><div style='margin:3px 0 8px;'>";
              for (var a=0;a<h.sukses.length;a++) s+="<span style='display:inline-block;margin:2px 4px 2px 0;padding:1px 8px;background:#eef3fb;border:1px solid #9bb3d6;font-size:11px;'>"+ptbEsc(h.sukses[a])+"</span>";
              s+="</div>";
       }
       if (h.gagal.length>0) {
              s+="<div style='font-weight:bold;margin-top:8px;'>&#10007; Belum bisa ditutup (selesaikan kendalanya, lalu muat ulang daftar)</div>";
              s+="<table cellpadding=4 cellspacing=1 class=sortable style='width:100%;margin:4px 0 8px;'><thead><tr class=rowheader><th style='width:90px'>Gudang</th><th>Alasan</th></tr></thead><tbody>";
              for (var b=0;b<h.gagal.length;b++) {
                     var alasan=h.gagal[b].pesan; if (alasan.length>300) alasan=alasan.substring(0,300)+' ...';
                     s+="<tr class=rowcontent><td>"+ptbEsc(h.gagal[b].gudang)+"</td><td>"+ptbEsc(alasan)+"</td></tr>";
              }
              s+="</tbody></table>";
       }
       if (h.tidakDipilih.length>0) {
              s+="<div style='font-weight:bold;margin-top:8px;'>Tidak dipilih (belum diproses)</div><div style='margin:3px 0 8px;font-size:11px;color:#555;'>"+ptbEsc(h.tidakDipilih.join(', '))+"</div>";
       }
       if (catatan!='') {
              s+="<div style='padding:8px 10px;background:#eef3fb;border:1px solid #9bb3d6;margin:8px 0;'>"+catatan+"</div>";
       }
       if (logoutSetelah) {
              s+="<div style='margin:10px 0 0;font-size:12px;'>Setelah menekan OK anda akan keluar (logout) agar periode baru terbaca.</div>";
       }
       s+="<div style='text-align:right;margin-top:10px;'><button class=mybutton onclick=\""+(logoutSetelah ? "ptbTutupPanel();logout();" : "ptbTutupPanel();")+"\">OK</button></div></div>";
       ptbPanel(s);
}

function saldoact(periode){
	unit = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	param = 'proses=saldoact&periode=' + periode+'&unit='+unit;
    tujuan = 'log_slave_saveTutupBukuFisik_saldoact.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            busy_off();
            if (con.status == 200){
                if (!isSaveResponse(con.responseText)) {
                    var teks=trim(con.responseText).replace(/<[^>]+>/g,' ').replace(/\s+/g,' ');
                    ringkasanTutupBuku("<b>Pemindahan saldo awal ke periode berikutnya belum berhasil:</b> "+ptbEsc(teks)+". Gudang di atas sudah tertutup, hubungi IT sebelum melanjutkan.");
                }  else {
                    ringkasanTutupBuku('');
                }
            }  else {
                ringkasanTutupBuku("<b>Pemindahan saldo awal ke periode berikutnya belum berhasil</b> (kode "+con.status+"). Hubungi IT sebelum melanjutkan.");
            }
        }
    }
}