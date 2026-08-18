<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses','');
$param = $_POST;

switch($proses) {
        case 'list':
                // Prepare Data
                $qData = "SELECT a.*,b.namakegiatan,
                        c.rp01,c.rp02,c.rp03,c.rp04,
                        c.rp05,c.rp06,c.rp07,c.rp08,
                        c.rp09,c.rp10,c.rp11,c.rp12
                        FROM ".$dbname.".bgt_budget a
                        LEFT JOIN ".$dbname.".bgt_distribusi c ON a.kunci=c.kunci
                        LEFT JOIN ".$dbname.".setup_kegiatan b ON a.kegiatan=b.kodekegiatan
                        WHERE a.tahunbudget='".$param['tahunbudget']."' and a.kodeorg='".
                        $param['blok']."' and a.tipebudget='ESTATE' and a.kodebudget='".
                        $param['kodebudget']."'";
                $resData = fetchData($qData);

                // Table
                $tab = "<table>";
                $tab .= "<thead>";
                $tab .= "<tr class=rowheader>";
                $tab .= "<td>".$_SESSION['lang']['kodekegiatan']."</td>";
                $tab .= "<td>".$_SESSION['lang']['namakegiatan']."</td>";
                $tab .= "<td>".$_SESSION['lang']['tipe']."</td>";
                $tab .= "<td colspan=2>Fisik 1 tahun</td>";
                $tab .= "<td>Rp 1 tahun</td>";
                $tab .= "<td>Jan</td><td>Feb</td><td>Mar</td>";
                $tab .= "<td>Apr</td><td>Mei</td><td>Jun</td>";
                $tab .= "<td>Jul</td><td>Agt</td><td>Sep</td>";
                $tab .= "<td>Okt</td><td>Nov</td><td>Des</td>";
                $tab .= "<td>".$_SESSION['lang']['pilih']."</td>";
                $tab .= "</tr>";
                $tab .= "</thead>";

                if(!empty($resData)) {
                        $tab .= "<tbody id=tbody>";
                        foreach($resData as $key=>$row) {
                                $tab .= "<tr class=rowcontent>";
                                $tab .= "<td>".$row['kegiatan']."</td>";
                                $tab .= "<td>".$row['namakegiatan']."</td>";
                                $tab .= "<td>".$row['kodebudget']."</td>";
                                $tab .= "<td align=right>".$row['volume']."</td>";
                                $tab .= "<td>".$row['satuanv']."</td>";
                                $tab .= "<td align=right>".number_format($row['rupiah'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp01'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp02'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp03'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp04'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp05'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp06'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp07'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp08'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp09'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp10'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp11'])."</td>";
                                $tab .= "<td align=right>".number_format($row['rp12'])."</td>";
                                $tab .= "<td>".makeElement('row'.$key,'checkbox',0,array('data-kunci'=>$row['kunci']))."</td>";
                                $tab .= "</tr>";
                        }
                        $tab .= "</tbody>";

                        $tab .= "<tfoot>";
                        $tab .= "<tr><td colspan=19 align=center>";
                        $tab .= makeElement('btnProses','btn',$_SESSION['lang']['proses'],
                                                                array('onclick'=>'proses()'));
                        $tab .= "</td></tr>";
                        $tab .= "</tfoot>";
                } else {
                        $tab .= "<tbody><tr><td colspan=19>";
                        $tab .= $_SESSION['lang']['dataempty'];
                        $tab .= "</td></tr></tbody>";
                }

                $tab .= "</table>";

                echo $tab;
                break;

        case 'proses':
                // Get Data Jumlah Total per Kunci
                $qJumlah = selectQuery($dbname,'bgt_budget','kunci,volume,rupiah',"kunci in ('".implode("','",$param['kunci'])."')");
                $resJumlah = fetchData($qJumlah);

                // Get Total Sebaran
                $totalSebaran = 0;
                foreach($param['sebar'] as $k=>$r) {
                        $totalSebaran += $r;
                }

                // Delete bgt_distribusi
                $qDel = deleteQuery($dbname,'bgt_distribusi',"kunci in ('".implode("','",$param['kunci'])."')");
                try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                $dataIns = array();
                foreach($resJumlah as $row) {
                        $dataIns[] = array(
                                'kunci' => $row['kunci'],
                                'rp01' => $row['rupiah'] * $param['sebar']['01'] / $totalSebaran,
                                'fis01' => $row['volume'] * $param['sebar']['01'] / $totalSebaran,
                                'rp02' => $row['rupiah'] * $param['sebar']['02'] / $totalSebaran,
                                'fis02' => $row['volume'] * $param['sebar']['02'] / $totalSebaran,
                                'rp03' => $row['rupiah'] * $param['sebar']['03'] / $totalSebaran,
                                'fis03' => $row['volume'] * $param['sebar']['03'] / $totalSebaran,
                                'rp04' => $row['rupiah'] * $param['sebar']['04'] / $totalSebaran,
                                'fis04' => $row['volume'] * $param['sebar']['04'] / $totalSebaran,
                                'rp05' => $row['rupiah'] * $param['sebar']['05'] / $totalSebaran,
                                'fis05' => $row['volume'] * $param['sebar']['05'] / $totalSebaran,
                                'rp06' => $row['rupiah'] * $param['sebar']['06'] / $totalSebaran,
                                'fis06' => $row['volume'] * $param['sebar']['06'] / $totalSebaran,
                                'rp07' => $row['rupiah'] * $param['sebar']['07'] / $totalSebaran,
                                'fis07' => $row['volume'] * $param['sebar']['07'] / $totalSebaran,
                                'rp08' => $row['rupiah'] * $param['sebar']['08'] / $totalSebaran,
                                'fis08' => $row['volume'] * $param['sebar']['08'] / $totalSebaran,
                                'rp09' => $row['rupiah'] * $param['sebar']['09'] / $totalSebaran,
                                'fis09' => $row['volume'] * $param['sebar']['09'] / $totalSebaran,
                                'rp10' => $row['rupiah'] * $param['sebar']['10'] / $totalSebaran,
                                'fis10' => $row['volume'] * $param['sebar']['10'] / $totalSebaran,
                                'rp11' => $row['rupiah'] * $param['sebar']['11'] / $totalSebaran,
                                'fis11' => $row['volume'] * $param['sebar']['11'] / $totalSebaran,
                                'rp12' => $row['rupiah'] * $param['sebar']['12'] / $totalSebaran,
                                'fis12' => $row['volume'] * $param['sebar']['12'] / $totalSebaran,
                                'updateby' => $_SESSION['standard']['userid'],
                                'lastupdate' => date('Y-m-d H:i:s')
                            );
                        }

                        $qIns = insertQuery($dbname,'bgt_distribusi',$dataIns);
                        try{$owlPDO->exec($qIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                break;
}