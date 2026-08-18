<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup_app extends OWL_Model
{
    function getParamAppM($user)
    {
        $data = array();
        $d = array();
        $q = "select * from " . $this->db->dbname . ".setup_parameterappl order by kodeaplikasi";
        $r = $this->fetchdata($q);


        if (count($r) > 0) {
            for ($i = 0; $i < count($r); $i++) {
                $expl = explode(',', $r[$i]['nilai']);
                if (count($expl) > 0) {
                    // print_r($expl);
                    for ($j = 0; $j < count($expl); $j++) {
                        $data[] = array(
                            'kodeaplikasi' => $r[$i]['kodeaplikasi'],
                            'kodeparameter' => $r[$i]['kodeparameter'],
                            'kodeorg' => $r[$i]['kodeorg'],
                            'keterangan' => $r[$i]['keterangan'],
                            'nilai' => $expl[$j]
                        );
                        // print_r($data);
                    }
                } else {
                    $data[] = array(
                        'kodeaplikasi' => $r[$i]['kodeaplikasi'],
                        'kodeparameter' => $r[$i]['kodeparameter'],
                        'kodeorg' => $r[$i]['kodeorg'],
                        'keterangan' => $r[$i]['keterangan'],
                        'nilai' => $r[$i]['nilai']
                    );
                }
            }
        }


        $a = "select * from " . $this->db->dbname . ".kebun_5pejabatbkm order by tipe, kolom";
        $b = $this->fetchdata($a);
        $pejabatbkm = [];
        foreach ($b as $value) {
            $pejabatbkm[$value['tipe'] . "_" . $value['kolom']][] = $value;
        }

        $setupPejabat = array(
            'MNDRWT' => 'BKM_mandor',
            'ASST' => 'BKM_asst',
            'MNDR' => 'PNN_mandor',
            'ASSTPNN' => 'PNN_asst',
            'KRNI' => 'PNN_kerani',
        );

        $kodeParam = array_column($r, 'kodeparameter');
        foreach ($setupPejabat as $code => $type) {
            if (in_array($code, $kodeParam)) {
                $key = array_search($code, $kodeParam);
                $dataOld = $r[$key];
                $datasaatini =  $pejabatbkm[$type];
                //UNSET

                unset($r[$key]);
                //EXEC                
                foreach ($datasaatini as $i => $val) {
                    foreach (explode(',', $val['jabatan']) as $j => $nil) {
                        $dataaWAL[] = array(
                            'kodeaplikasi' => $dataOld['kodeaplikasi'],
                            'kodeparameter' => $dataOld['kodeparameter'],
                            'kodeorg' => $val['kodeorg'],
                            'keterangan' => $dataOld['keterangan'],
                            'nilai' => $nil
                        );
                    }
                }
            }
        }
        // var_dump($dataaWAL);
        $data = array_merge($data, $dataaWAL);
        if (
    $user['lokasitugas'] === "CARE" ||
    $user['lokasitugas'] === "LANE" ||
    $user['lokasitugas'] === "DMAE" ||
    $user['lokasitugas'] === "MHAE" ) {
    $kodeparam = array_column($data, 'kodeparameter');
    $key = array_search('premi_show', $kodeparam);
    if ($key !== false) {
        $data[$key]['nilai'] = '1';
        }
    }

    return $data;
    }
}


