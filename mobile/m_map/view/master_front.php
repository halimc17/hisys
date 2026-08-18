<script src="<? echo $this->base_url(); ?>assets/js/Chart.min.js"></script>
<script src="<? echo $this->base_url(); ?>assets/js/dashboard.js"></script>
<main class="content" style="left: 0px; right: 0px; bottom: 0px; top: 0px; overflow: none; z-index: 1000; position: center;padding:1rem 1rem 0.1rem">
    <div class="row" style="margin-bottom: 15px">
        <div class="col-xl-2 col-md-3 col-sm-4 col-xs-12">
            <div class="notif-frame">
                <div class="title" style="background:#2aa9a9;">Mobile App Status</div>
                <div class="body-frame">
                    <div class="sub-notif-frame">
                        <div class="title">Periode Aktif</div>
                        <div id="periode" class="body-frame font-bigger">-<span class="extension"></span></div>
                    </div>
                    <div class="sub-notif-frame">
                        <div class="title">Latest Version App</div>
                        <div class="body-frame font-bigger"><span id="appversion_name">-</span><span class="extension"> / No.<span id="appversion">-</span></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-9 col-sm-8 col-xs-12">
            
            <div class="notif-frame">
                <div class="title">Panen Vs SPB</div>
                <div class="body-frame">
                    <div class="col-xl-6">
                        <div class="sub-notif-frame">
                                <div class="title">Panen</div>
                                <div class="body-frame font-bigger"><span id="panen">-</span><span class="extension"> / <span id="panenext">Jjg</span></span></div>
                            </div>
                            <div class="sub-notif-frame">
                                <div class="title">Luas Panen</div>
                                <div class="body-frame font-bigger"><span id="luaspanen">-</span><span class="extension"> / <span id="luaspanenext">Ha</span></span></div>
                            </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="sub-notif-frame">
                            <div class="title">Kirim</div>
                            <div class="body-frame font-bigger"><span id="kirim">-</span><span class="extension"><span id="kirimext">%</span></span></div>
                        </div>
                        <div class="sub-notif-frame">
                            <div class="title">Restan</div>
                            <div class="body-frame font-bigger"><span id="restan">-</span><span class="extension"> / <span id="restanext">Jjg</span></span></div>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>

            </div>
        </div>
        <div class="col-xl-6 col-md-12 col-sm-12 col-xs-12">
            <div class="notif-frame">
                <div class="title">Data Synchronized<span id='tanggalposting'></span>&nbsp;&nbsp;<span id='jam'></span></div>
                <div class="body-frame">
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="sub-notif-frame">
                                <div class="title">Absensi</div>
                                <div class="body-frame font-bigger"><span id="Pabsensi">-</span><span class="extension"> / <span id="Pabsensiext">-</span></span></div>
                            </div>
                            <div class="sub-notif-frame">
                                <div class="title">BJR</div>
                                <div class="body-frame font-bigger"><span id="Pbjr">-</span><span class="extension"> / <span id="Pbjrext">-</span></span></div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="sub-notif-frame">
                                <div class="title">BKM Panen</div>
                                <div class="body-frame font-bigger"><span id="Pbkmpanen">-</span><span class="extension"> / <span id="Pbkmpanenext">-</span></span></div>
                            </div>
                            <div class="sub-notif-frame">
                                <div class="title">BKM Rawat</div>
                                <div class="body-frame font-bigger"><span id="Pbkmrawat">-</span><span class="extension"> / <span id="Pbkmrawatext">-</span></span></div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="sub-notif-frame">
                                <div class="title">Hancak Panen</div>
                                <div class="body-frame font-bigger"><span id="Phancakpanen">-</span><span class="extension"> / <span id="Phancakpanenext">-</span></span></div>
                            </div>
                            <div class="sub-notif-frame">
                                <div class="title">Taksasi</div>
                                <div class="body-frame font-bigger"><span id="Ptaksasi">-</span><span class="extension"> / <span id="Ptaksasiext">-</span></span></div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="sub-notif-frame">
                                <div class="title">SPB</div>
                                <div class="body-frame font-bigger"><span id="Pspb">-</span><span class="extension"> / <span id="Pspbext">-</span></span></div>
                            </div>
                            <div class="sub-notif-frame">
                                <div class="title">Sensus Produksi</div>
                                <div class="body-frame font-bigger"><span id="Psensusproduksi">-</span><span class="extension"> / <span id="Psensusproduksiext">-</span></span></div>
                            </div>
                        </div>
                        
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
	<div class="row" style="margin-bottom: 15px">
		<div class="col-sm-12 col-md-6 col-lg-8">
			<div class="notif-frame">
				<div class="title">Traffic Crop</div>
				<div class="body-frame">
					<canvas id="myChart" style="width:100%;height:200px"></canvas>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-3 col-lg-2">
			<div class="notif-frame">
				<div class="title">Activity</div>
				<div class="body-frame">
					<canvas id="myChart1" style="width:100%;height:200px"></canvas>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-3 col-lg-2">
			<div class="notif-frame">
				<div class="title" style="background-color:rgb(203 12 12);"><i class="fa fa-exclamation-triangle" style="color:yellow;"></i> Info Peringatan</div>
				<div class="body-frame">
					<div style="width:100%;height:200px"></div>
				</div>
			</div>
		</div>
	</div>
</main>