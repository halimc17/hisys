<script>
  var site_url_php = "<?php echo $this->site_url();?>";
	var base_url_php = "<?php echo $this->base_url();?>";
  if(typeof options !== 'undefined'){
			if($ !== null){
				// console.log('modulses/reset');
				$.resetProject(options);
			}else{
				// console.log('modulses/new');
				$ = new OwlProject(options);
			}
		}else{
			if($ !== null){
				// console.log('/reset');
				$.resetProject();
			}else{
				// console.log('/new');
				$ = new OwlProject();
			}
		}
</script>