<?
defined('BASEPATH') or exit('No direct script access allowed');
class buku_panduan extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
            case 'excel':
            case 'csv':
            default:

                echo '<fieldset style="min-height:100%;margin:0">
                        <div class="col-xl-11 col-md-12 col-sm-12 col-xs-12" style="margin-right:500px;">
                            <div class="notif-frame">
                                <div class="title">Download Buku Panduan<span id="tanggalposting"></span>&nbsp;&nbsp;<span id="jam"></span></div>
                                <div class="body-frame">
                                    <div class="row">
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">BKM Rawat</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="kegiatanperawatan" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">BKM Panen</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="bukupanen" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">Verifikasi</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="verify" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">Mutu Hancak</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="mutuancak" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">SPB</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="suratpengantarbuah" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="sub-notif-frame">
                                                <div class="title">HA Panen</div>
                                                <div class="body-frame font-bigger">
                                                    <button id="hapanen" class="notif-frame" onclick="openPdf(this.id)">Open PDF</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>';
                break;
            case 'form':
                break;
            case 'view':
                break;
            case 'Filter':
                break;
            case 'insert':
                break;
        }
    }

    function options($SELF, $breadcrumb)
    {
        $option = array();
        $option['master']       = '#bodymaster';
        $option['slave']        = $this->site_url() . $this->uri->uri_string . "_slave";
        $option['getpage']      = 'switcher';
        $option['type']         = '';
        $option['javascript']['src'] = array($this->base_url() . 'js/' . $SELF . '.js?version=' . time() . '');

        $d = array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = false;
        $d['isEnable'] = true;
        $option['buatbaru'] = $d;

        $d = array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = false;
        $d['isEnable'] = true;
        $option['listdata'] = $d;

        $d = array();
        $d['title'] = "Filter";
        $d['slave'] = "Filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = false;
        $d['isEnable'] = true;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show'] = false;
        $option['pdf']['show'] = false;
        $option['csv']['show'] = false;
        $option['fixHeader']['show'] = false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}
