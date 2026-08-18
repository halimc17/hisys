<?php
#errornya dimatiin dulu, dompdf lumayan cerewet kalau ada notice error.
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$notransaksi = checkPostGet('notransaksi', '');
$method = checkPostGet('method', '');
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmalamat = makeOption($dbname, 'organisasi', 'kodeorganisasi,alamat');
$nmtelp = makeOption($dbname, 'organisasi', 'kodeorganisasi,telepon');

$str = "select * from " . $dbname . ".sdm_kontrakkary where notransaksi='" . $notransaksi . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$jenis=$bar['jenis'];
$pt=$bar['pt'];
$dikeluarkan=$bar['dikeluarkan'];

$strx = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['karyawanid'] . "'";
$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_ASSOC);
$barx = $resx->fetch();

$nmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$kdjab = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
$nmdep = makeOption($dbname, 'sdm_5departemen', 'kode,nama');

$nmjp=array('L'=>'Laki - Laki / Male','P'=>'Perempuan / Female');
switch($method){
	case'pdf':
		$tab='';	
		if($jenis=='PKWTT'){
			$judulid="PERJANJIAN KERJA WAKTU TIDAK TERTENTU";
			$judulen="INDEFINITE TERM EMPLOYMENT AGREEMENT";			
		}else{
			$judulid="PERJANJIAN KERJA WAKTU TERTENTU";
			$judulen="DEFINITE TERM EMPLOYMENT AGREEMENT";
		}

		$tab.="<table width=100% border=0>";
				$tab.="<tr><td align=center colspan=4 width=100%><img style=width:75px;height:75px; src=images/tml.jpg></td></tr>";
				$tab.="<tr><td align=center colspan=4><b><u><font size=3>".$judulid."</font></u></b></td></tr>";
				$tab.="<tr><td align=center colspan=4><b><font size=3>".$judulen."</font></b></td></tr>";
				$tab.="<tr><td align=center colspan=4><hr></td></tr>";
				$tab.="<tr><td align=center colspan=4><b><font size=3>".$nmorg[$pt]."</font></b></td></tr>";
				$tab.="<tr><td align=center colspan=4><hr></td></tr>";
				$tab.="<tr><td align=center colspan=4><font size=2>".$nmalamat[$pt]."</font></td></tr>";
				$tab.="<tr><td align=center colspan=4><font size=2>".$nmtelp[$pt]."</font></td></tr>";
				$tab.="<tr><td align=center colspan=4></td></tr>";
				$tab.="<tr><td align=center colspan=4><b><font size=3>No. ".$notransaksi."</font></b></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				
				$tab.="<tr><td align=left colspan=2 width=50% style=vertical-align:top><b>".$judulid." ini</b> dibuat di ".$dikeluarkan." oleh dan antara :</td>";
				$tab.="<td align=left colspan=2 width=50% style=vertical-align:top><b>".$judulen."</b> is made in ".$dikeluarkan." by and between :</td></tr>";
				$tab.="<tr><td align=center colspan=2><table><tr><td style=vertical-align:top>I</td><td style=vertical-align:top><b>".$nmorg[$pt].",</b> adalah Perusahaan yang didirikan berdasarkan hukum Republik Indonesia, yang bernaung di dalam STH GROUP INDONESIA, selanjutnya disebut sebagai: <b>PERUSAHAAN</b></td></tr></table></td>
				
				<td align=center colspan=2><table><tr><td style=vertical-align:top>I</td><td style=vertical-align:top><b>".$nmorg[$pt].",</b> a Company that is established under the laws of the Republic of Indonesia, which is controlled by STH GROUP INDONESIA, here in after referred to as the <b>COMPANY</b></td></tr></table></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>II</td><td style=vertical-align:top><b>Nama/ Name</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Jenis Kelamin/ Gender</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Tanggal Lahir/ Date of Birth</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>No. KTP/ ID Card</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Alamat/ Address</b></td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['namakaryawan']."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$nmjp[$barx['jeniskelamin']]."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['tempatlahir'].", ".tanggalnormal($barx['tanggallahir'])."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['noktp']."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><font size=2>".$barx['alamataktif']."</font></td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>&nbsp;&nbsp;</td><td style=vertical-align:top><b>Agama/ Religion</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Status Keluarga/ Registered Dependents</b></td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['agama']."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['statusperkawinan']."</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				
				$tab.="<tr><td align=left colspan=2 width=50% style=vertical-align:top>Selanjutnya disebut sebagai <b>KARYAWAN</b>.<br><br>
							Perusahaan dan Karyawan secara bersama-sama disebut sebagai <b>PARA PIHAK</b>.<br><br>
							Para pihak sepakat untuk mengadakan ikatan dalam Perjanjian Kerja untuk Waktu Tidak Tertentu (selanjutnya disebut dengan singkat sebagai <b>PERJANJIAN</b>), dengan ketentuan-ketentuan yang tercantum dalam pasal-pasal berikut ini:</td>
						<td align=left colspan=2 width=50% style=vertical-align:top>Here in after referred to as <b>EMPLOYEE</b>.<br><br>
							Company and Employee shall jointly be referred as the <b>PARTIES</b>.<br><br>
							Both parties agreed to engage in an Employment Agreement for an Indefinite Period (here in referred brief as <b>AGREEMENT</b>), according to the terms and conditions mentioned in the following articles:
						</td></tr>";
				
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>NIK/ Employee ID</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Jabatan/ Position </b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Tanggal Masuk Kerja/ Date of Start Work</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Golongan/ Grade</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Departemen/ Department</b></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Lokasi Kerja/ Work Location</b></td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$barx['nik']."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$nmjab[$barx['kodejabatan']]."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".tanggalnormal($barx['tanggalmasuk'])."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>".$nmgol[$barx['kodegolongan']]."</td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><font size=2>".$nmdep[$barx['bagian']]."</font></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><font size=2>".$nmorg[$barx['lokasitugas']]."</font></td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL II</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE II</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>RUANG LINGKUP PERJANJIAN</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>SCOPE OF AGREEMENT</b></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>2.1</td><td style=vertical-align:top>Karyawan wajib melapor langsung kepada Manajer/ Pimpinan Unit dan mencurahkan seluruh waktu dan kemampuannya atas pelaksanaan tugas dan tanggungjawabnya kepada <b>".$bar['atasanlangsung']."</b>, atau kepada orang lain yang ditentukan oleh Perusahaan sebagai atasan dari Karyawan.</td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>2.1</td><td style=vertical-align:top>Employee shall report directly to the Manager in Charge and devote his/her entire time and ability to the performance of his/her duties and responsibilities to <b>".$bar['atasanlangsung']."</b> or to other people assigned by the Company as the in-charged supervisor of the Employee.</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>2.2</td><td style=vertical-align:top>Perusahaan memberikan pekerjaan-pekerjaan (selanjutnya disebut dengan singkat sebagai “Tugas/Kerja”) sebagaimana tercantum dalam Uraian Tugas dan Standar Operasional Prosedur (SOP) yang memuat rincian tugas, tanggungjawab, dan tata cara kerja sebagai pedoman umum bagi Karyawan dalam melaksanakan tugas dan kewajibannya, yang merupakan satu kesatuan yang integral dan tidak terpisahkan dari Perjanjian ini.<br><br>
								Karyawan setuju untuk melaksanakan tugas/proyek ad-hoc dan kewajiban-kewajiban yang mungkin ditugaskan dari waktu ke waktu.
								</td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>2.2</td><td style=vertical-align:top>Company shall assign work (herein referred to briefly as “Tasks/Jobs”) listed in the Job Descriptions and Standard Operating Procedure (SOP) that contains details of tasks,  responsibilities, and procedures as general guidelines for Employees in performing duties and obiligations, to which as one of the essential and inseparable part of this Agreement.<br><br><br><br>
							Employee agrees to undertake ad-hoc jobs/projects and responsibilities that may be assigned from time-to-time.
						</td></tr>
				</table></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>2.3</td><td style=vertical-align:top>Para pihak setuju bahwa Perusahaan memiliki  hak dari waktu ke waktu untuk mengubah dan menambah jenis pekerjaan, termasuk dalam hal ini adalah jabatan serta tugas dan tanggung jawab yang dicantumkan dalam Uraian Tugas dan SOP oleh Perusahaan; sesuai dengan Kebutuhan Perusahaan.
								</td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>2.3</td><td style=vertical-align:top>Both parties agreed that Company has the right from time-to-time to change and add on the type of work, including the position as well as duties and responsibilities listed in the Job Descriptions and SOP by the Company; in accordance to  the needs of the Company.
						</td></tr>
				</table></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>2.4</td><td style=vertical-align:top>Karyawan menerima Perjanjian dan sanggup melaksanakan tugas/kerja dan kewajiban yang ditetapkan kepadanya oleh Perusahaan sesuai Uraian Tugas, SOP, dan tuntutan jabatan dengan sebaik-baiknya.<br><br>
								Target tahunan dalam bentuk Key Performance Index (KPI) dievaluasi oleh atasan langung yang dijalankan setahun  sekali. Penilaian tersebut tergantung dari kemampuan, prestasi kerja, dan hasil evaluasi dari Atasan langsung.
								</td></tr>
							</table>
						</td>
				
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>2.4</td><td style=vertical-align:top>Employee accepts the Agreement and capable to perform their tasks/jobs and obligations stipulated by the Company based on the Job Descriptions, SOP, and demands of the position to his/her best.<br><br>
							Annual target in the form of Key Performance Index (KPI) will be evaluated by the direct supervisor on annual basis. The assessment depends on the capabilities, performance, and evaluation from the direct superior.
						</td></tr>
				</table></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>2.5</td><td style=vertical-align:top>Karyawan setuju dan bersedia untuk bertugas, dirotasikan, dimutasikan, dan/atau berdinas di luar dari lokasi kerja yang di tentukan di Pasal I, sebagaimana keahliannya dibutuhkan untuk menjalankan tugas di unit usaha Perusahaan atau Perusahaan lainnya yang bernaung di dalam STH GROUP INDONESIA.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>2.5</td><td style=vertical-align:top>Employee agrees and willng to work, relocate, transfer, and/or engage on external job assignment outside of the designated work location stated in Article I, in which his/her services might be needed to complete any assignment  for the Company or any affiliated companies that is controlled by STH GROUP INDONESIA.
						</td></tr>
				</table></td></tr>";
				
				$pkwtt='';
				$pkwt='';
				$pkwtt.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$pkwtt.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL III</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE III</b></u></td></tr>";
				$pkwtt.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>MASA PERCOBAAN</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>PROBATIONARY PERIOD</b></td></tr>";
						
				$pkwtt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.1</td><td style=vertical-align:top>Karyawan akan menjalani masa percobaan selama 3 (tiga) bulan efektif mulai dari hari pertama dilaporkan telah bekerja sebagai tanggal masuk kerjanya.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.1</td><td style=vertical-align:top>Employee will undergo an effective 3 (three) months probationary period starting from the first day the Employee reports to have worked as the date of his/her entry. 
						</td></tr>
				</table></td></tr>";
				$pkwtt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.2</td><td style=vertical-align:top>Selama menjalani masa percobaan, Karyawan akan dievaluasi oleh atasannya sebelum dikonfirmasikan sebagai Karyawan permanen.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.2</td><td style=vertical-align:top>During the probationary period, the Employee’s performance shall be evaluated by the superiors prior to confirmation as permanent Employee.
						</td></tr>
				</table></td></tr>";
				$pkwtt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.3</td><td style=vertical-align:top>Selama masa percobaan ini, masing-masing pihak dapat memutuskan hubungan kerja dengan Pemberitahuan Tertulis 1 (satu) minggu sebelumnya untuk  mengakhiri Perjanjian ini. 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.3</td><td style=vertical-align:top>During this probationary period, both parties may terminate the employment by providing a 1 (one) week prior Written Notice, for the termination of this Agreement.
						</td></tr>
				</table></td></tr>";
				$pkwtt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.4</td><td style=vertical-align:top>Apabila Karyawan yang memutuskan hubungan kerja pada saat masa percobaan, Karyawan diwajibkan untuk ganti rugi atas biaya-biaya yang ada dan timbul dalam perekrutan/penerimaan Karyawan berikut tanggungannya (termasuk biaya transportasi dan akomodasi penjemputan  serta lainnya) kepada Perusahaan. Biaya ganti rugi tersebut akan dipotong secara otomatis pada gaji Karyawan bulan berjalan melalui Payroll.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.4</td><td style=vertical-align:top>If the Employee who voluntarily terminate employment during the probationary period, the Employee is required to compensate for the costs incurred during recruitment/Employee’s acceptance together with his/her dependents (including transportation, accomodation, and other costs) to the Company. The compensation amount will be deducted automatically in the current month salary through payroll.
						</td></tr>
				</table></td></tr>";
				
				$pkwtt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.5</td><td style=vertical-align:top>Apabila Karyawan diberhentikan selama masa percobaan, Karyawan berhak untuk menerima jumlah yang proporsional dari Gaji Pokok. Namun tidak berhak atas tunjangan, pesangon, keuntungan pengakhiran lainnya,  dan/atau ganti rugi berupa apapun.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.5</td><td style=vertical-align:top>If the Employee is terminated during the probationary period, the Employee shall be entitled to receive a proportional amount of his/her Base Salary. However, he/she is not entitled to benefits, severance benefits, other termination benefits, and/or  compensation in any form.
						</td></tr>
				</table></td></tr>";
				$pkwt.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$pkwt.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL III</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE III</b></u></td></tr>";
				$pkwt.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>JANGKA WAKTU PEKERJAAN</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>DURATION OF EMPLOYMENT</b></td></tr>";
						
				$pkwt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.1</td><td style=vertical-align:top>Jangka waktu perjanjian ini berlaku selama ".$bar['jangkawaktu']." ".$bar['satjangka']."  kerja/dinas berturut-turut, terhitung dimulai pada ".tanggalnormal($bar['tanggaldari']).", (“Tanggal Permulaan”) dan berakhir pada ".tanggalnormal($bar['tanggalsampai']).".
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.1</td><td style=vertical-align:top>The duration of this employment is valid for ".$bar['jangkawaktu']." ".$bar['satjangka']." for continuous jobs/assignments, effectively commencing on ".tanggalnormal($bar['tanggaldari'])." (“Commencement Date”) and expiring on ".tanggalnormal($bar['tanggalsampai']).". 
						</td></tr>
				</table></td></tr>";
				$pkwt.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>3.2</td><td style=vertical-align:top>Perjanjian ini dapat diakhiri dan batal demi hukum dengan kondisi-kondisi dan/ atau ketentuan-ketentuan tercantum pada Pasal XV dalam Perjanjian ini.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>3.2</td><td style=vertical-align:top>This Agreement will be terminated and annulled by law based on the conditions based on Article XV of this Agreement.
						</td></tr>
				</table></td></tr>";
				//$pkwt.="<tr><td align=center colspan=4><div style=height:100px></div</td></tr>";
				
				if($jenis=='PKWTT'){
					$tab.=$pkwtt;
				}else{
					$tab.=$pkwt;
				}
		
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL IV</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE IV</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>JAM KERJA & PEMBAYARAN LEMBUR</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>WORK HOURS & OVERTIME PAYMENT</b></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>4.1</td><td style=vertical-align:top>Jam kerja Karyawan ditetapkan sesuai dengan jam kerja yang berlaku atau di tetapkan kemudian sesuai dengan kebutuhan Perusahaan setempat.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>4.1</td><td style=vertical-align:top>The Employee working hours will be set by the Company, depending on the work location in which the Empoyee is assigned. 
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>4.2</td><td style=vertical-align:top>Sesuai ketentuan Pasal 4 Ayat (3) No KEP-102/MEN/VI/2004 dan sebagai pengelola jabatan gaji tetap, Karyawan sepakat bahwa ia tidak akan menuntut untuk mendapatkan pembayaran upah lembur apabila ia diperlukan untuk bekerja diluar waktu kerja tercantum di atas.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>4.2</td><td style=vertical-align:top>Based on Article 4 Point (3) No KEP-102/MEN/VI/2004 and as permanent exempt worker, the Employee agree that he/she will not bring lawsuits on overtime payment in case if he/she is required to work outside the above mentioned working hours. 
						</td></tr>
				</table></td></tr>";
				//$tab.="<tr><td align=center colspan=4>&nbsp;<div style=height:50px></div></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL V</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE V</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>UPAH</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>REMUNERATION</b></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>5.1</td><td style=vertical-align:top>Upah adalah pendapatan Karyawan yang diterima sebagai imbalan atas suatu pekerjaan yang telah atau akan dilakukan dan dibayarkan dalam bentuk mata uang Rupiah, melalui transfer kawat ke rekening Bank yang ditunjuk oleh Perusahaan untuk mempermudah pembayaran gaji.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>5.1</td><td style=vertical-align:top>Remuneration is income receives by Employee in exchange for the jobs he/she has or will perform and will be paid in Rupiah (IDR) currency, through direct transfer to a Bank account designated by the Company in order to better facilitate the payment of salaries. 
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>5.2</td><td style=vertical-align:top>Upah akan dihitung secara proporsional 30 (tiga puluh) hari sesuai dengan tanggal masuk kerja apabila Karyawan mengundurkan diri atau diberhentikan oleh Perusahaan pada bulan tersebut.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>5.2</td><td style=vertical-align:top>Salary will be calculated proportionally to 30 (thirty) days based on date of start work if the Employee resigned or terminated by the Company any period during the month.   
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>5.3</td><td style=vertical-align:top>Gaji Pokok adalah nominal yang akan di terima oleh Karyawan setiap bulan. Peninjauan gaji pokok akan dilaksanakan sesuai dengan Kebijakan Perusahaan.  
								</td></tr>
								<tr><td style=vertical-align:top>&nbsp;&nbsp;</td><td style=vertical-align:top>Gaji Pokok: Rp. ".number_format($bar['gajipokok'])." / bulan
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>5.3</td><td style=vertical-align:top>Base Salary is the basic amount that will be received by the Employee every month. Salary review will be in accordance with the Company Policy. 
							</td></tr>
							<tr><td style=vertical-align:top>&nbsp;&nbsp;</td><td style=vertical-align:top>Base Salary: Rp. ".number_format($bar['gajipokok'])." / Month
							</td></tr>
				</table></td></tr>";

				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>5.4</td><td style=vertical-align:top>Tunjangan Tetap adalah tunjangan yang diberikan kepada selektif Karyawan tertentu, tanpa dikaitkan dengan kehadiran. Berikut adalah tunjangan tetap Karyawan:
								</td></tr><tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1) Tunjangan Jabatan</b> adalah tunjangan 
								yang diberikan kepada selektif Karyawan dikaitkan dengan tingkat jabatan tertentu dan pengalaman kerja yang berharga. 
								</td></tr><tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Jabatan: Rp. ".number_format($bar['tunjjabatan'])."</b>/ bulan
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>5.4</td><td style=vertical-align:top>Fixed Allowance is an allowance that is given to selective Employee specifically, excluding consideration for attendance. Below are some of the fixed allowance for the Employee: 
						</td></tr><tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1) Position Alllowance</b> is an allowance that is given to selective Employee associated with specified position level and invaluable working experience. 
						</td></tr><tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Position: Rp. ".number_format($bar['tunjjabatan'])."</b>/ Month 
						</td></tr>
								
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL VI</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE VI</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>TUNJANGAN TIDAK TETAP</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>VARIABLE ALLOWANCE</b></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>6.1</td><td style=vertical-align:top>Tunjangan Tidak Tetap adalah tunjangan yang diberikan kepada selektif Karyawan tertentu, dikaitkan dengan kehadiran ataupun pencapaian prestasi kerja tertentu. 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>6.1</td><td style=vertical-align:top>Variable Allowance is an allowance that is given to selective Employee specifically, in consideration for the attendance or achievement in certain target performance. 
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>6.2</td><td style=vertical-align:top>Tunjangan tidak tetap akan dihitung secara proporsional 30 (tiga puluh) hari sesuai dengan tanggal masuk kerja apabila Karyawan mengundurkan diri atau diberhentikan oleh Perusahaan pada bulan tersebut.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>6.2</td><td style=vertical-align:top>Variable allowance will be calculated proportionally to 30 (thirty) days based on date of start work if the Employee resign or terminated by the Company any period during the month.  
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>6.3</td><td style=vertical-align:top>Pemberian tunjangan tidak tetap ditentukan sesuai Kebijakan Perusahaan. Jumlah nominal tunjangan ini dapat bertambah atau berkurang ataupun dicabut. Berikut adalah tunjangan tidak tetap Karyawan:
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)	Uang Konsumsi</b> adalah tunjangan yang diberikan sesuai dengan kehadiran kerja (proporsional per-jam kerja).
							</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Konsumsi: Rp. ".number_format($bar['konsumsi'])."</b>/ Hari Kerja
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)	Uang Transportasi</b> adalah tunjangan yang diberikan sesuai dengan kehadiran kerja (proporsional per-jam kerja). 
							</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Transportasi: Rp. ".number_format($bar['transport'])."</b>/ Hari Kerja
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>3)	Uang Daerah</b> adalah insentif yang diberikan kepada Karyawan saat dipindah ke lokasi kerja (Di Luar Propinsi) yang lain dari lokasi kerja (Propinsi Asal) yang ditentukan di <b>Pasal I</b>.   
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Daerah: Rp. ".number_format($bar['uangdaerah'])."</b>/ bulan
						</td></tr>
				</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>6.3</td><td style=vertical-align:top>The distribution of variable allowance is decided by the Company Policy. The nominal amount for this allowance can increase or decrease or even be dismissed. Below are the Employee’s variable allowance:
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)	Consumption Allowance</b> is an allowance that is given based on the Employee’s daily attendance (proportional per work hours). 
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Consumption: Rp. ".number_format($bar['konsumsi'])."</b>/ work day
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)	Tranportation Allowance</b> is an allowance that is given based on the Employee’s daily attendance (proportional per work hours).  
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Transportation: Rp. ".number_format($bar['transport'])."</b>/ work day
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>3)	Hardship Allowance</b> is an incentive that is given to Employee when he/ she is required  to work in another location (Outside Province) that is different from the work location (Province of Origin) stated in <b>Article I<b>.   
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Hardship: Rp. ".number_format($bar['uangdaerah'])."</b>/ month
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL VII</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE VII</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>TUNJANGAN HARI RAYA (THR)</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>STATUTORY RELIGIOUS ALLOWANCE</b></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>7.1</td><td style=vertical-align:top>Karyawan berhak mendapat <b>Tunjangan Hari Raya (THR)</b> yang besarnya dan perhitungannya sesuai dengan ketentuan No.PER-04/MEN/1994 untuk merayakan Hari Raya Keagamaan yang dianutnya.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>7.1</td><td style=vertical-align:top>Employee is entitled to receive <b>Statutory Religious Allowance</b> in which the amount and calculation is in accordance with the regulations No.PER-04/MEN/1994 to celebrate the adopted religious festivity. 
						</td></tr>
				</table></td></tr>";
				
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL VIII</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE VIII</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>CUTI</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>LEAVE</b></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>8.1</td><td style=vertical-align:top>Terhitung dari tanggal masuk kerja, Karyawan berhak mengambil cuti tahunan setelah bekerja secara berturut-turut selama 1 (satu) tahun.  Cuti tahunan Karyawan adalah sebagai berikut:
								</td></tr><tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Hak Cuti Tahunan: ".$bar['cuti']." </b>Hari/ Tahun   
							</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>8.1</td><td style=vertical-align:top>Commencing from the date of start work, the Employee is entitled to take his/ her annual leave after 1 (one) year of continuous service. The entitled annual leave for the Employee is as follow: 
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Annual Leave: ".$bar['cuti']."</b> Days/ year   
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>8.2</td><td style=vertical-align:top>Hak cuti tahunan yang belum digunakan oleh Karyawan hanya dapat diteruskan ke akhir tahun depannya dan akan dianggap hilang/ gugur apabila Karyawan masih tidak menggunakan hak cuti tahunan tersebut paling lambat pada akhir tahun depan tersebut. 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>8.2</td><td style=vertical-align:top>Annual leave entitlements which have not been used by the Employee can only be carry-forward to the end of next year and will be considered expired if Employee still has not used his/her annual leaves latest by the end of next year. 
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>8.3</td><td style=vertical-align:top>Karyawan mengakui dan menyetujui apabila ia meninggalkan pekerjaan tanpa permohonan cuti serta tidak dapat menunjukkan bukti-bukti kuat yang sah atas ketidakhadiran-nya, Perusahaan berhak mengurangi hak cuti tahunan yang tersisa atau memotong pembayaran upah secara otomatis jika tidak ada cuti tahunan yang tersisa.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>8.3</td><td style=vertical-align:top>Employee acknowledges and agrees if he/ she left the job without vacation request and unable to provide strong evidence of his/ her absences, Company reserves the right to deduct the remaining annual leaves or deduct his/ her remuneration automatically if there is none available annual leave to be deducted.
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>8.4</td><td style=vertical-align:top>Ketentuan cuti dan prosedur pengambilan cuti tahunan akan diatur sesuai dengan Peraturan Perusahaan, Buku Pedoman Karyawan dan Surat ketentuan yang berlaku atau yang akan ditentukan kemudian.  
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>8.4</td><td style=vertical-align:top>Annual leave provisions and procedure for leave requests will be regulated in the existing Company Regulation, Employee Handbook, Corporate Announcement Letter or any future regulations set by the Company.
						</td></tr>
				</table></td></tr>";
				$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL IX</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE IX</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>FASILITAS</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>FACILITIES</b></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>9.1</td><td style=vertical-align:top>Fasilitas adalah hak khusus dan bukan merupakan suatu tunjangan, yang diberikan kepada Karyawan untuk melaksanakan tugasnya tergantung pada situasi-situasi tertentu. Fasilitas-fasilitas yang diberikan akan berubah dari waktu ke waktu sesuai dengan Kebijakan Perusahaan. Berikut adalah fasilitas yang diberikan kepada Karyawan:  
								</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)	Perumahan dan Utilitas </b></td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top>Karyawan akan diberikan fasilitas perumahan dan utilitas (termasuk Transport, PLN, PAM, dan LPG) dalam bentuk mata uang Rupiah.</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Perumahan & Utilitas</b>: Rp. <b>".number_format($bar['perumahan'])."</b>/ bulan</td></tr>
								
								<tr><td>&nbsp;&nbsp;</td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)	Telekomunikasi</b></td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top>Untuk memperlancar komunikasi dan koordinasi di lapangan, maka Karyawan akan diberikan fasilitas telekomunikasi berupa Pulsa Pasca Bayar setiap bulannya yang hanya dipergunakan untuk kepentingan perusahaan<br>
								Penggunaan Pulsa bukan untuk kepentingan perusahaan menjadi beban karyawan sendiri 
								</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Telekomunikasi</b>: Rp. <b>".number_format($bar['telekomunikasi'])."</b>/ bulan</td></tr>
								
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>9.1</td><td style=vertical-align:top>Facilities are a special privilege and are not considered as an allowance, which will be provided to the Employee in order to execute his/her duties depending on different situations. These facilities are subject to changes at any period in accordance with Company Policy. The following are the facilities provided to Employee:
						</td></tr><tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)	Housing and Utilities</b></td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>The Employee will be given housing and utilities (including Transport, Electricity, Water, and Gas) facility in Indonesian Rupiah (IDR) currency.</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Housing & Utilities</b>: Rp. <b>".number_format($bar['perumahan'])."</b>/ month</td></tr>
							
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)	Phone Allowance</b></td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>To ease communication and coordination in the field, The Company will provide phone facility in terms of pre-paid plan monthly and this facility should only be used for any work related to The Company.<br>
							Any Usage that is unrelated to The Company shall be borne by The Employee. 
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Phone</b>: Rp. <b>".number_format($bar['telekomunikasi'])."</b>/ bulan</td></tr>
							
						</table></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top><font color=white>9.1</font></td><td style=vertical-align:top><b>3)	Tiket Transportasi untuk Cuti</b></td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top>Fasilitas Tiket Transportasi (Udara, laut atau darat) ini mencakup sebagai berikut:
								</td></tr>
								<tr><td></td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>a)</b>	Karyawan akan mendapat fasilitas ini apabila ditempatkan di luar dari asalnya. Transportasi cuti ini berlaku untuk lokasi:
								</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Lokasi cuti</b> : <b>".($bar['poh'])."</b></td></tr>
								
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top><font color=white>9.1</font></td><td style=vertical-align:top><b>3)	Transportation ticket for annual leave</b></td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top>This facility of Transportation ticket (Air, sea  or land)  covers as follow:
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>a)</b>	Employee is entitled for this facility if the work location is situated in a province that is different from the province of the employee’s origin. This facility applies on the following location:
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>Location</b> : <b>".($bar['poh'])."</b></td></tr>
							
						</table></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top><font style=color:white>9.1</font></td><td style=vertical-align:top><b>b)</b>	Fasilitas  ini berupa <b>".$bar['tiketcuti']."</b> kali tiket transportasi kelas ekonomi pergi-pulang untuk Karyawan dan keluarganya (sampai K/3) setiap tahun kalender.
								</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>c)</b>	Fasilitas tiket transportasi dapat diambil setelah hak cutinya timbul terhitung 1 (satu) tahun dinas berturut-turut. Jadwal pengajuan untuk pembelian tiket transportasi ditentukan dan diatur sesuai Kebijakan Perusahaan.
								</td></tr>
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>d)</b>	Fasilitas tiket transportasi yang belum digunakan oleh Karyawan tidak dapat diteruskan ke tahun depan dan akan dianggap hilang/ gugur. Fasilitas ini tidak dapat diuangkan apabila tidak diajukan pemesanan tiket transportasinya.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top><font style=color:white>9.1</font></td><td style=vertical-align:top><b>b)</b>	This facility consists of <b>".$bar['tiketcuti']."</b> times transportation ticket economy class round-trip ticket for the Employee and his family (up to K/3) per calendar year.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>c)</b>	Transportation ticket facility can be taken after the entitlement of the leave arises in 1 (one) year of continuous service. The schedule for requesting to book the transportation ticket is based on the applicable Company Policy.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>d)</b>	Transportation ticket entitlements which has not been used by the Employee will not be able to carry-forward to next year and will be considered as being forfeited. This facility will not be encashed if no transportation ticket is requested for booking. 
							</td></tr>
						</table></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top><font style=color:white>9.1</font></td><td style=vertical-align:top>Perusahaan berhak, dengan kebijakan yang mutlak, untuk mengubah syarat dan ketentuan tentang fasilitas tiket transportasi yang diberikan kepada Karyawan. 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font style=color:white>9.1</font></td><td style=vertical-align:top>The Company reserves the right, in its absolute discretion, to vary the terms and conditions for Transpotarion ticket facility provided to the Employee. 
							</td></tr>
						</table></td></tr>";
						
				$tab.="<tr><td align=center colspan=4></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL X</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE X</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>JAMINAN KESEHATAN & PENGOBATAN</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>HEALTH & MEDICAL BENEFITS</b></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>10.1</td><td style=vertical-align:top>Karyawan akan diikutsertakan program Badan Penyelenggara Jaminan Sosial (BPJS) Ketenagakerjaan sesuai dengan PP no. 44 Tahun 2015 tentang kecelakaan kerja & kematian, PP no. 45 tentang jaminan Pensiun, PP no. 46 jaminan hari tua dan PP no. 111 Tahun 2013 tentang program pemeliharaan kesehatan beserta peraturan pelaksanaannya, yaitu 3 (tiga) bulan setelah berhasil bekerja dengan perusahaan secara berturut-turut atau setelah karyawan tersebut dijadikan karyawan tetap. 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>10.1</td><td style=vertical-align:top>Employee will be registered in the Social Security and Medicare Contribution program (BPJS) after successfully passing the probationary period of 3 (three) months
							</td></tr>
						</table></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>10.2</td><td style=vertical-align:top>Kepesertaan karyawan dalam program Badan Penyelenggara Jaminan Sosial (BPJS) tidak akan di berhentikan atas alasan apapun kecuali pada saat terjadinya Pemutusan Hubungan Kerja (PHK). 
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>10.2</td><td style=vertical-align:top>Membership for social security and medicare will continue unless untill termination of employment.
							</td></tr>
						</table></td></tr>";
						
				//$tab.="<tr><td align=center colspan=4><div style=height:220px></div></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XI</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XI</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>EVALUASI PRESTASI</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>PERFORMANCE EVALUATIONS</b></td></tr>";
						
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>11.1</td><td style=vertical-align:top>Perusahaan berhak melakukan penilaian terhadap Karyawan, yang dimaksudkan untuk mengidentifikasikan kekuatan dan kelemahan Karyawan, membina dan mendorong perkembangan kemampuan dan pengetahuan Karyawan, dan mengidentifikasikan bidang-bidang dimana prestasi Karyawan yang berada dibawah standar minimum Perusahaan.<br><br>
								Sanksi dan penghargaan akan diberikan sesuai dengan evaluasi kinerja Karyawan yang mencakup sebagai berikut:
								</td></tr>
								
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)</b>	Perusahaan berhak memberikan sanksi-sanksi baik sanksi administratf maupun finansial kepada Karyawan apabila terbukti melakukan pelanggaran disiplin kerja dalam pelaksanaan kerja/ tugasnya.<br><br>
								Termasuk kategori sanksi adalah: penurunan golongan, penghapusan jabatan (demosi), fasilitas dan/atau tunjangan, pemberhentian, dan sanksi administrasi lainnya.<br><br>
								Seluruh pemberian tunjangan tetap dan/atau tunjangan tidak tetap merupakan kebijakan Perusahaan. Apabila Karyawan melanggar ketentuan yang tercantum di Pasal 15.1 Ayat (6) dibawah, Perusahaan dapat melakukan penghapusan tunjangan tersebut secara proporsional atau keseluruhan. Penghapusan tunjangan tersebut juga dapat terjadi apabila terjadinya demosi.
								</td></tr>
								
								<tr><td></td></tr>
								<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)</b>	Termasuk dalam kategori penghargaan adalah: peningkatan jabatan, penyesuaian gaji/ tunjangan/ fasilitas, pemberian bonus, dan lain-lainnya.
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>11.1</td><td style=vertical-align:top>The Company reserves the right to assess performance appraisal on the Employee, designed to identify strengths and weaknesses of the Employee, foster and encourage the development of the Employee’s skills and knowledge, and identify areas in which Employee’s performance is below the minimum standards of the Company.<br><br>
							Sanctions and rewards will be given based on performance evaluations which includes the following:
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)</b>	Company reserves the right to give either administrative or financial sanctions to Employee if proven guilty of violating disiciplinary matters in executing their jobs/ tasks.<br><br>
							Category  that includes sanctions are: downgrade, demotion, revocation of facilities and/or allowance, dismissal, and other administrative sanctions.<br><br>
							All of the fixed allowances and/or variable allowances are provided as part of a company policy. If Employee violates the provisions set forth in Article 15.1 Point (6) below, Company have the right to remove the allowance proportionally or entirely. The removal of allowance could happen if demotion is merited.
							</td></tr>
							
							<tr><td>&nbsp;</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)</b>	Category that includes rewards are: promotion, adjustment in salary/ allowance/ facilities, bonus, and etc.
							</td></tr>
						</table></td></tr>";
						
				//$tab.="<tr><td align=center colspan=4><div style=height:250px></div></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XII</b></u></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XII</b></u></td></tr>";
				$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KERAHASIAAN</b></td>
						<td align=center colspan=2 width=50% style=vertical-align:top><b>CONFIDENTIALITY</b></td></tr>";
				
				$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
							<table>
								<tr><td style=vertical-align:top>12.1</td><td style=vertical-align:top>Karyawan tidak dapat, pada setiap waktu atau dengan setiap cara, langsung atau tidak langsung, memanfaatkan untuk keuntungannya sendiri atau keuntungan setiap pihak atau badan hukum lain, atau mengungkapkan kepada setiap pihak atau badan hukum, dengan cara apapun, setiap informasi rahasia atau yang dimiliki oleh Perusahaan.  Telah dipahami dan disetujui bahwa semua data dan/ atau informasi keuangan, teknis, prosedural dan administrasi Perusahaan, baik yang dibuat dalam bentuk tertulis atau media elektronik maupun tidak, dan baik yang ditandai “rahasia” maupun tidak, harus diperlakukan sebagai kepemilikan dan dijaga ketat kerahasiaannya.  Kewajiban atas kerahasiaan ini harus tetap berlanjut setelah pengakhiran Perjanjian ini.  Setelah pengakhiran Perjanjian ini, Karyawan harus mengembalikan kepada Perusahaan semua material/bahan rahasia yang berada dalam penguasaannya.   
								</td></tr>
							</table>
						</td>
				<td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>12.1</td><td style=vertical-align:top>The Employee shall not, at any time or in any manner, directly or indirectly, use for his/her own benefit or the benefit of any other person or entity, or disclose to any person or entity, in any manner whatsoever, any confidential or proprietary information of the Company.  It is understood and agreed that all financial, technical, procedural and administrative data and/or information of the Company, whether or not reduced to writing or an electronic medium, and whether or not marked “confidential”, shall be treated as proprietary and kept strictly confidential.  This obligation of confidentiality shall survive the termination of this Agreement.  Upon termination of this Agreement, the Employee shall return to the Company all confidential materials in his/her possession.
							</td></tr>
						</table></td></tr>";
						
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>12.2</td><td style=vertical-align:top>Karyawan lebih jauh mengakui dan menyetujui bahwa, apabila diminta oleh Perusahaan, ia harus menandatangani perjanjian kerahasiaan terpisah, dalam bentuk dan isi yang memuaskan bagi Perusahaan.   
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>12.2</td><td style=vertical-align:top>The Employee further acknowledges and agrees that, if so requested by the Company, he/she shall promptly execute a separate confidentiality agreement, in form and substance satisfactory to the Company.
						</td></tr>
					</table></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>12.3</td><td style=vertical-align:top>Karyawan juga akan mentaati Undang-undang No. 11 Tahun 2008, tentang Informasi dan Transaksi Elektronik dan undang-undang No. 30 Tahun 2000, tentang Rahasia Dagang.   
						</td></tr>
					</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>12.3</td><td style=vertical-align:top>The Employee will also abide to the Indonesian Information and Electronic Transaction Law No. 11 Year 2008 and Trading Secret Law No. 30 Year 2000.
						</td></tr>
					</table></td></tr>";
			
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XIII</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XIII</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>HAK CIPTA</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>COPYRIGHT</b></td></tr>";
			
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>13.1</td><td style=vertical-align:top>Karyawan dengan ini menyetujui bahwa setiap kepentingan (termasuk semua hak atas kekayaan intelektual dalam setiap sifat) dalam ide, ciptaan, penemuan atau kemajuan yang berkembang dalam keseluruhan atau sebagian berkaitan dengan pekerjaannya pada Perusahaan merupakan milik satu-satunya dan eksklusif dari Perusahaan tanpa tindakan lebih jauh apapun dari Perusahaan.  Karyawan dengan ini mengalihkan kepada Perusahaan semua haknya, hak milik dan kepentingannya dalam semua ide, ciptaan, penemuan dan kemajuan tersebut dan menyetujui bahwa Perusahaan tidak dibawah kewajiban, moneter atau yang lain, kepada Karyawan atas pengalihan tersebut.  Karyawan dengan ini selanjutnya menyetujui untuk menandatangani dan menyerahkan semua dokumen kepada Perusahaan.   
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>13.1</td><td style=vertical-align:top>The Employee hereby agrees that any interest (including all intellectual property rights of any nature) in ideas, inventions, discoveries or improvements developed in whole or in part in connection with his/her employment by the Company shall be the sole and exclusive property of the Company without any further act of any kind by the Company.  The Employee hereby assigns to the Company all of his/her right, title and interest in all such ideas, inventions, discoveries and improvements and agrees that the Company is under no further obligations, monetary or otherwise, to the Employee for such assignment.  The Employee hereby further agrees to execute and deliver all documents to the Company.
						</td></tr>
					</table></td></tr>";
			
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>13.2</td><td style=vertical-align:top>Karyawan juga harus mentaati Undang-undang No. 19 Tahun 2002, tentang Hak Cipta.   
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>13.2</td><td style=vertical-align:top>The Employee will also abide to the Indonesian Copyright Law No. 19 Year 2002.
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XIV</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XIV</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>LARANGAN & TINDAKAN DISIPLINER</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>RESTRICTIONS & DISCIPLINARY ACTIONS</b></td></tr>";
			
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>14.1</td><td style=vertical-align:top>Selama dalam hubungan kerja Karyawan wajib mentaati dan melaksanakan ketentuan mengenai tata tertib, kedisiplinan dan kewajiban-kewajiban yang diberikan kepadanya, sesuai dengan ketentuan dalam Peraturan Perusahaan.   
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>14.1</td><td style=vertical-align:top>During in employment Employee is obliged to obey and implement the provisions of the code of conducts, disiciplinary, and obligations given to him/her, in accordance with the provisions in the Company Regulation.
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>14.2</td><td style=vertical-align:top>Tindakan pelanggaran kedisiplinan dapat diambil terhadap Karyawan, oleh Perusahaan dengan ketentuan-ketentuan yang tercantum dalam Peraturan Perusahaan yang berlaku, yang merupakan satu kesatuan yang tidak terpisahkan dari Perjanjian ini.   
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>14.2</td><td style=vertical-align:top>Disciplinary actions can be taken against the Employee, by the Company with the provisions stipulated in the prevailing Company Regulation, constituting as one of the essential and inseparable part of this Agreement.
						</td></tr>
					</table></td></tr>";
			
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>14.3</td><td style=vertical-align:top>Perusahaan akan menerapkan tindakan disipliner, sanksi, dan/atau peringatan (Mengacu pada Peraturan Perusahaan, Buku Pedoman Karyawan, Kebijakan dan Prosedur Perusahaan) atas setiap pelanggaran hukum atau peraturan lainnya yang tercantum dalam Pasal 14 Ayat (2) di atas.    
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>14.3</td><td style=vertical-align:top>Company shall apply disciplinary actions, sanctions, and/or warnings/notifications (Refer to Company Regulation, Employee Handbook, Company Policy and Procedure Manuals) due to for any violation of law or regulation mentioned in Article 14 Point (2) above.
						</td></tr>
					</table></td></tr>";
			
			//$tab.="<tr><td align=center colspan=4>&nbsp;<div style=height:100px></div></td></tr>";
			$tab.="<tr><td align=center colspan=4>&nbsp;</td></tr>";
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XV</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XV</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>PENGAKHIRAN</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>TERMINATION</b></td></tr>";
			
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>15.1</td><td style=vertical-align:top><b>Berakhirnya Hubungan Kerja:</b><br><br>
							Perjanjian ini akan berakhir apabila:
							</td></tr>
							
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)</b>	Karyawan meninggal dunia.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)</b>	Karyawan mengundurkan diri atau dikualifikasikan mengundurkan diri. 
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>3)</b>	Karyawan tidak memenuhi syarat dalam masa percobaan.  
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>4)</b>	Karyawan gagal untuk melaksanakan suatu tugas/ kewajiban pada persoalan-persoalan yang prinsipil, kode etik, serta tingkat ketidakpelaksanaan yang mendasar, yaitu: 
							</td></tr>
							
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Karyawan tidak mampu mencapai expektasi dan/ atau standar prestasi kerja Perusahaan.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Karyawan melakukan kelalaian, konduite yang tidak baik atau tindakan/ perbuatan yang merugikan Perusahaan.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Karyawan tidak  menunjukkan nilai-nilai dasar kode etik, yakni: konsisten kinerja, integritas, professionalisme, produktivitas, dan inisiatif.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Karyawan tidak bersedia untuk bertugas, berdinas, dirotasiKan, dan/ atau dimutasikan sebagaimana keahliannya dibutuhkan.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Ketidakpelaksanaan tugas/ kewajiban tersebut telah dilakukan secara sengaja atau karena kecerobohan.
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Pelanggaran disiplin kerja lainnya sebagaimana dimaksud dalam Peraturan Perusahaan yang berlaku. 
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Karyawan terbukti bersalah melakukan tindak pidana berdasarkan ketentuan Perundang-Undangan RI yang berlaku. 
							</td></tr>
							
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>15.1</td><td style=vertical-align:top><b>Termination of Employment:</b><br><br>
						This Agreement will end if:
						</td></tr>
						
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>1)</b>	Employee passed away.
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>2)</b>	Employee resigned or qualifying as resigned. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>3)</b>	Employee is not qualified on probation period.  
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>4)</b>	Employee fails to carry out tasks/ obligations on the issues of principles, code of ethics, as well as unenforceability level of basic fundamental, namely: 
						</td></tr>
						
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Employee is not able to achieve the Company’s expectations and/ or astandard work performance set by the Company. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Employee commits negligent, bad conducts or actions/ works that are detrimental to the company. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Employee does not show basic values code of conducts, which are: consistent performance, integrity, professionalism, productivity, and initiative. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Employee is not willing to work, engage assignment, rotate, and/or transfer as his/ her expertise is needed. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>		Unenforceability of tasks/ obligations have been conducted deliberately or due to carelessness. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Other disciplinary action based on the prevailing Company Regulation. 
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>•</b>	Employee is proven guilty of committing criminal acts based on Indonesia Labor Laws. 
						</td></tr>
					</table></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.1</font></td><td style=vertical-align:top>Apabila selama bekerja pada Perusahaan ternyata timbul kerugian yang disebabkan kelalaian atau kesengajaan Karyawan, maka Karyawan diwajibkan untuk ganti rugi baik pada saat masih bekerja atau sudah berakhir masa kontrak kerjanya. Biaya ganti rugi tersebut akan dipotong secara otomatis pada gaji Karyawan bulan berjalan melalui Payroll.    
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.1</font></td><td style=vertical-align:top>If during employment with the Company turns out to incur losses due to Employee’s negligence or deliberate action of Employee, hence the Employee will be subjected to compensate the damages incurred whilst still working or has ended his/her work contract. The compensation amount will be deducted automatically in the current month salary through payroll.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.1</font></td><td style=vertical-align:top>Perusahaan mempunyai hak untuk mengakhiri Perjanjian ini dengan alasan-alasan lain yang diperbolehkan menurut peraturan perundang-undangan yang berlaku, demikian termasuk  tindakan atas ketentuan-ketentuan yang tercantum dalam  Peraturan Perusahaan, Buku Pedoman Karyawan, Surat Keputusan Pimpinan Perushaan, Kebijakan dan Prosedur Perusahaan, serta Larangan-larangan dan tindakan disipliner lainnya, yang merupakan satu kesatuan yang tidak terpisahkan dari Perjanjian ini.    
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.1</font></td><td style=vertical-align:top>Company have the right to terminate this Agreement for other reasons allowed under the prevailing laws and regulations, including measures on provisions which included in the Company Regulation, Employee Handbook, Corporate Announcement Letter, Company Policy and Procedures, as well as Restrictions and other disciplinary actions, which is considered as one of the essential and inseparable part of this Agreement.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>15.2</td><td style=vertical-align:top><b>Pengakhiran dan Administrasi:    
							</b></td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>15.2</td><td style=vertical-align:top><b>Termination and Administration:
						</b></td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Karyawan dapat mengakhiri Perjanjian ini dengan pengunduran diri berdasarkan peraturan perundang-undangan yang berlaku.  Karyawan mengundurkan diri dari Perusahaan diwajibkan untuk memberitahukan kepada Manajer/ Pimpinan Unit dan Departemen Sumber Daya Manusia (SDM) Perusahaan secara tertulis paling lambat 30 (tiga puluh) hari sebelum tanggal pengunduran dirinya. Karyawan tidak dapat mengundurkan diri tanpa persetujuan pemberitahuan tertulis dari SDM.    
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Employee may terminate this Agreement voluntarily through resignation in accordance with the prevailing laws and regulations.   Employee resigns from the Company is obligated to notify his/ her Manager in Charge and the Human Resource Department (HRD) of the Company of his/her intention in writing at least 30 (thirty) days prior to the date of resignation. Employee is not allowed to resign without getting a written approval notification from the HRD. 
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Selama menjalani periode penyerahan pekerjaan, Karyawan tidak dapat mengambil cuti tahunannya dan wajib menyelesaikan serah terima pekerjaan kepada atasan langsung atau rekan kerja yang ditentukan (termasuk melakukan “Wawancara Keluar Kerja” dan “Daftar Pemeriksaan Keluar Kerja” kepada SDM). Sisa hak cuti yang masih berlaku akan dibayarkan secara proporsional dari Gaji Pokok.    
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>During the notice period, the Employee is not allowed to take his/ her annual leave and  expected to hand over his/her work completely to his/her direct superiors or appointed colleagues (includes completing the “Exit Interview” and “Exit Checklist” to HRD). The remaining leave entitlements that are still valid will be paid proportional amount of his/her Base Salary.
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Kegagalan dalam menyerahkan pekerjaannya sesuai dengan Prosedur Perusahaan akan mengakibatkan konsekuensi yang tidak menguntungkan seperti:     
							</td></tr>

							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(i)</b>	Hilangnya sebagian tunjangan tidak tetap dan fasilitas. 
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(ii)</b>	Tidak diberikan surat referensi kerja atau surat rekomendasi dari Perusahaan. 
							</td></tr>
							<tr><td></td></tr>
							<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(iii)</b>	Selain itu, semua sertifikat yang dihadiri untuk workshop, seminar, atau pelatihan yang dibayar oleh Perusahaan tidak akan diberikan kembali kepada Karyawan.  
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Failure to hand over his/her work according to Company’s Procedure will result in unfortunate consequences such as: 
						</td></tr>
						
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(i)</b>	Loss of partial variable allowances and benefits.  
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(ii)</b>	Not receiving a reference letter or recommendation letter from the Company.  
						</td></tr>
						<tr><td></td></tr>
						<tr><td style=vertical-align:top></td><td style=vertical-align:top><b>(iii)</b>	In addition, all certificates attended  on the workshops, seminar, or training that is paid by the Company will not be given to the Employee. 
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>Perusahaan dapat melepas Karyawan lebih awal dari periode pemberitahuan tersebut, dengan ketentuan Karyawan menyelesaikan tugas dan tanggungjawab dan disetujui oleh <b>Manajer/ Pimpinan Unit</b>.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>15.2</font></td><td style=vertical-align:top>The Company reserves the right to grant an early release from the mentioned notice period, provided that the Employee complete his/her handing over job duties and responsibility according and receiving approval from his/her <b>Manager in Charge</b>.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XVI</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XVI</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KETENTUAN LAIN</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>OTHER PROVISIONS</b></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>16.1</td><td style=vertical-align:top>Segala ketentuan yang belum atau tidak cukup diatur atau tidak tercakup memadai dalam Perjanjian  ini, akan diatur dan/ atau dirubah  lebih lanjut dalam  Peraturan Perusahaan, Buku Pedoman Karyawan, Surat Keputusan Pimpinan Perusahaan, Kebijakan Perusahaan serta Prosedur Perusahaan lainnya, yang merupakan adendum (perjanjian tambahan) sebagai satu kesatuan yang mengikat para pihak dengan bagian tidak terpisahkan dari Perjanjian ini.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>16.1</td><td style=vertical-align:top>All provisions that are not or not sufficiently regulated or not covered adequately in this Agreement, are stipulated in the Company Regulations, Employee Handbook, Corporate Announcement Letter, Company Policy, as well as Company Procedure, which is an addendum (additional agreements) as one of the essential and inseparable part of this Agreement that binds between the parties.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>16.2</td><td style=vertical-align:top>Perjanjian ini dibuat dalam Bahasa Indonesia dan Inggris, tetapi dalam hal terjadinya perselisihan atau perbedaan interpretasi mengenai penggunaan dua bahasa ini, Bahasa Indonesia yang akan berlaku.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>16.2</td><td style=vertical-align:top>This Agreement is made in both Bahasa Indonesia and English, however in the event of dispute or differences of interpretation thereto, the Bahasa Indonesia version shall prevail.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XVII</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XVII</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KETENTUAN PERALIHAN</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>TRANSITIONAL PROVISIONS</b></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>17.1</td><td style=vertical-align:top>Dalam hal pengakuan masa kerja Karyawan oleh Perusahaan, para pihak sepakat bahwa terhadap beberapa hal dalam pasal-pasal Perjanjian ini sepanjang tidak bertentangan dengan Peraturan Perundang-undangan tentang Ketenagakerjaan yang berlaku, akan berlaku surut hingga tanggal masuk kerja Karyawan.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>17.1</td><td style=vertical-align:top>In terms of recognition of the Employee’s period of employment by the Company, both parties agree that certain elements in articles of this Agreement so long as it does not conflict with the applicable Indonesian Labor Law, will apply retroactively to the date of entry of Employee.
						</td></tr>
					</table></td></tr>";
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>17.2</td><td style=vertical-align:top>Berkaitan dengan hubungan kerja saja, Perjanjian ini dapat menggantikan dan/atau melengkapi segala kesepakatan dan kesepahaman yang dibuat terdahulu oleh para pihak, baik secara lisan maupun tertulis.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>17.2</td><td style=vertical-align:top>In relation to work employment exclusively, this Agreement supersedes and/ or supplements all prior agreements and understandings arranged between the parties, whether written or oral.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XVIII</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XVIII</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KEADAAN MEMAKSA</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>FORCE MAJEURE</b></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>18.1</td><td style=vertical-align:top>Perjanjian ini batal dengan sendirinya jika karena keadaan atau situasi yang memaksa, seperti: bencana alam, pemberontakan, huru-hara, kerusuhan, Peraturan Pemerintah atau apa pun yang mengakibatkan Perjanjian ini tidak mungkin lagi untuk diwujudkan.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>18.1</td><td style=vertical-align:top>This Agreement is declared void by itself if due to circumstances or events outside its reasonable control, such as: natural disasters, insurrection, civil commotion, riot, Government Regulations or any events resulting this Agreement is no longer possible to be implemented.  
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XIX</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XIX</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KEDIAMAN HUKUM</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>LEGAL DOMICILE</b></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>19.1</td><td style=vertical-align:top>Segala perselisihan terhadap Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat. Apabila dengan cara musyawarah tidak tercapai kata sepakat, maka kedua belah pihak sepakat menyelesaikan perselisihan tersebut dengan memilih domisili hukum yang tetap di Kantor Kepaniteraan Pengadilan Hubungan Industrial di Jakarta Pusat.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>19.1</td><td style=vertical-align:top>
							All dispute arising from this Agreement shall be resolved through discussion and consensus. If consensus is not achieved, then both parties agree to settle the dispute by choosing a fixed legal domicile at the Industrial Relation Court in Central Jakarta.  
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XX</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XX</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>KETIDAKBERLAKUAN</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>INVALIDITY</b></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>20.1</td><td style=vertical-align:top>Apabila terhadap beberapa Pasal dari Perjanjian ini berdasarkan Keputusan dan/ atau Ketetapan badan peradilan Indonesia dinyatakan tidak mempunyai kekuatan hukum yang mengikat, maka para pihak sepakat dan mengakui atas Pasal-pasal lain dari Perjanjian ini tetap berlaku dan mengikat kedua belah pihak.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>20.1</td><td style=vertical-align:top>
							If any Articles of this Agreement, based on the decision and/ or judicial decree declared by Indonesia court did not have binding legal force, then both parties agree and acknowledge to the remaining Articles of this Agreement shall remain in force and binding on both parties. 
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>PASAL XXI</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>ARTICLE XXI</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>PENUTUP</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>CLOSING</b></td></tr>";

			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>21.1</td><td style=vertical-align:top>Perjanjian ini diatur dengan dan ditafsirkan berdasarkan hukum Republik Indonesia.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>21.1</td><td style=vertical-align:top>This Agreement governed by and construed in accordance with the laws of the Republic of Indonesia. 
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>22.1</td><td style=vertical-align:top>Perusahaan tidak bertanggung jawab atas janji-janji lisan atau pernyataan-pernyataan yang tidak tertulis dalam Perjanjian ini.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>22.1</td><td style=vertical-align:top>The Company is not responsible for any verbal promises or statements that are not written in this Agreement.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>23.1</td><td style=vertical-align:top>Perjanjian ini dibuat dan disetujui oleh para pihak dalam keadaan sadar dan tanpa paksaan atau tekanan oleh pihak manapun.
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>23.1</td><td style=vertical-align:top>This Agreement thus established and agreed by both parties in a state of conscious mind and without coercion or duress by any party. 
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>24.1</td><td style=vertical-align:top>Perjanjian ini dibuat dalam 2 (dua) rangkap, masing-masing bermaterai cukup dan memiliki kekuatan hukum yang sama. 
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top>24.1</td><td style=vertical-align:top>This Agreement is made in 2 (two) original copies, both copies with sufficientt stamp duties and having the same legal effect.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=2 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top><font color=white>24.1</font></td><td style=vertical-align:top><b>DENGAN DISAKSIKAN,</b> Perjanjian ini diadakan dan ditandatangani pada tanggal sebagaimana tertulis diatas dan Karyawan memahami serta menyetujui semua syarat dan kondisi dalam pasal-pasal yang tertera di atas. 
							</td></tr>
						</table>
					</td>
			<td align=center colspan=2 style=vertical-align:top>
					<table>
						<tr><td style=vertical-align:top><font color=white>24.1</font></td><td style=vertical-align:top><b>IN WITNESS WHEREOF,</b> this Agreement is entered and signed as of the date written above and Employee understands and agrees to all terms and conditions in the articles mentioned above.
						</td></tr>
					</table></td></tr>";
					
			$tab.="<tr><td align=center colspan=4 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>Disetujui dan diterima/ Agreed and accepted by,</td>
							</tr>
						</table>
					</td></tr>";
			$tab.="<tr><td align=center colspan=4 style=vertical-align:top>
						<table>
							<tr><td style=vertical-align:top>".$bar['dikeluarkan'].", ".tglnmbln($bar['tanggal'],'','')."</td>
							</tr>
						</table>
					</td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>".$nmorg[$bar['pt']]."</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>KARYAWAN/ EMPLOYEE</b></td></tr>";
			$tab.="<tr><td align=center colspan=4><div style=height:50px></div></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><u><b>".$nmkary[$bar['pihakpertama']]."</b></u></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><u><b>".$nmkary[$bar['karyawanid']]."</b></u></td></tr>";
			$tab.="<tr><td align=center colspan=2 width=50% style=vertical-align:top><b>".$nmjab[$kdjab[$bar['pihakpertama']]]."</b></td>
					<td align=center colspan=2 width=50% style=vertical-align:top><b>".$nmjab[$kdjab[$bar['karyawanid']]]."</b></td></tr>";
			
					
			$tab.="</table>";
			#echo $tab;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dompdf->stream("kontrak",array("Attachment"=>0));
	break;
	case'PKWT':
			$judulid="PERJANJIAN KERJA WAKTU TERTENTU";
			$judulen="DEFINITE TERM EMPLOYMENT AGREEMENT";
		echo "".$judulid."";
	break;
}
?>