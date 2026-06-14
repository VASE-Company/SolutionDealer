<?php 
	if (isset($byAjax) && $byAjax == true) {		
		
		echo $output;

		if (isset($callback) && $callback != "") {
		?>		
		<script>
		$(document).ready(function() {  				    
			<?php echo $callback; ?>			
			});
		</script>   	
		<?php
		} 				
	} else {		
		$iconFile = "assets/images/icon.ico";
		$baseColorsFile = "assets/css/baseColorsMyApp.css";				
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title; ?></title>            
    <?php 		
    	if (file_exists($iconFile)) {
    ?>
    <link href="<?php echo base_url().$iconFile."?".filemtime($iconFile); ?>" rel="shortcut icon">
    <?php 
    	}
    ?>
    <!-- Metadatos y Canonicos (SEO) -->
	<?php

    if(!empty($meta))
		foreach($meta as $name=>$content){
			echo "\n\t\t";
	?>
    		<meta name="<?php echo $name; ?>" content="<?php echo $content; ?>" />
	<?php
    	}
	echo "\n";

	if(!empty($canonical))
	{
		echo "\n\t\t";
	?>
    	<link rel="canonical" href="<?php echo $canonical?>" />
	<?php
	}
	echo "\n\t";
	?>
	<!-- CSS -->	    
    <!-- bootstrap -->
    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css?<?php echo filemtime("assets/css/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/bootstrap-grid.min.css?<?php echo filemtime("assets/css/bootstrap-grid.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/bootstrap-reboot.min.css?<?php echo filemtime("assets/css/bootstrap-reboot.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/bootstrap-multiselect.css?<?php echo filemtime("assets/css/bootstrap-multiselect.css"); ?>" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="<?php echo base_url(); ?>assets/plugins/fontawesome-free/css/all.min.css?<?php echo filemtime("assets/plugins/fontawesome-free/css/all.min.css"); ?>" rel="stylesheet">    
    <!-- overlayScrollbars -->
    <link href="<?php echo base_url(); ?>assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css?<?php echo filemtime("assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css"); ?>" rel="stylesheet">        
  	<!-- Theme style AdminLTE -->  
  	<link href="<?php echo base_url(); ?>assets/css/adminlte.min.css?<?php echo filemtime("assets/css/adminlte.min.css"); ?>" rel="stylesheet">
  	<!-- Google Font: Source Sans Pro -->
  	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- Dropzone -->
    <link href="<?php echo base_url(); ?>assets/plugins/dropzone/dropzone.min.css?<?php echo filemtime("assets/plugins/dropzone/dropzone.min.css"); ?>" rel="stylesheet">    
  	<!-- My App-->
  	<link href="<?php echo base_url(); ?>assets/css/stylesMyApp.css?<?php echo filemtime("assets/css/stylesMyApp.css"); ?>" rel="stylesheet">
    <?php
    foreach($css as $file){
		$auxFile = str_replace(base_url(), "", $file);
		$updatedDate = filemtime($auxFile);
		
	 	echo "\n\t\t";
		?><link rel="stylesheet" href="<?php echo $file."?".$updatedDate; ?>" type="text/css" /><?php
	} echo "\n\t";
	?>
    <!-- Javascript -->    
    <!-- jQuery -->
    <script src="<?php echo base_url(); ?>assets/js/jquery-3.4.1.min.js?<?php echo filemtime("assets/js/jquery-3.4.1.min.js"); ?>"></script>     
    <!-- bootstrap -->    
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js?<?php echo filemtime("assets/js/bootstrap.bundle.min.js"); ?>"></script>    
    <script src="<?php echo base_url(); ?>assets/js/bootstrap-multiselect.min.js?<?php echo filemtime("assets/js/bootstrap-multiselect.min.js"); ?>"></script>    
    <!-- overlayScrollbars -->
    <script src="<?php echo base_url(); ?>assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js?<?php echo filemtime("assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url(); ?>assets/js/adminlte.min.js?<?php echo filemtime("assets/js/adminlte.min.js"); ?>"></script>
    <!-- Dropzone -->
    <script src="<?php echo base_url(); ?>assets/plugins/dropzone/dropzone.min.js?<?php echo filemtime("assets/plugins/dropzone/dropzone.min.js"); ?>"></script>
    <script src="<?php echo base_url(); ?>assets/js/dropzone.js?<?php echo filemtime("assets/js/dropzone.js"); ?>"></script>            
    <!-- Solution Dealer -->
    <script src="<?php echo base_url(); ?>assets/js/ajax.js?<?php echo filemtime("assets/js/ajax.js"); ?>"></script>
	<script src="<?php echo base_url(); ?>assets/js/generalHelpers.js?<?php echo filemtime("assets/js/generalHelpers.js"); ?>"></script>         
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->    
    <!-- cdn for modernizr, if you haven't included it already -->
	<script src="<?php echo base_url(); ?>assets/plugins/webshim/modernizr-custom.js"></script>
    <!-- polyfiller file to detect and load polyfills -->
    <script src="<?php echo base_url(); ?>assets/plugins/webshim/polyfiller.js"></script>           
    <?php
		foreach($js as $file){
			$auxFile = str_replace(base_url(), "", $file);
			$updatedDate = filemtime($auxFile);

			echo "\n\t\t";			
			?><script src="<?php echo $file."?".$updatedDate; ?>"></script><?php
		} echo "\n\t";
	?>
	<script>
		$(document).ready(function() {  
			initializeGeneral(); 
		    <?php 
			if (isset($callback) && $callback != "") {
			?>
			<?php echo $callback; ?>
			<?php
			} 
			?> 
			});
	</script>    
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-collapse">
	<!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" id="myModalDialog">
            <div class="modal-content">            
                <div class="modal-header" id="headerModalFull" style="padding-top: 5px; padding-bottom: 5px;">                    
                    <h6 class="modal-title" id="headerModal" style="font-weight:bold;text-transform: uppercase;">Modal title</h6>
                    <button type="button" id="btnClose" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div id="_prebodyModal"><!-- Hay algun ERROR con el preModal, le agrege el _ -->
                    <div class="modal-body" id="bodyModal">
                        ...
                    </div>
                    <div id="preleyendabodyModal" style="padding-left:7px;"></div>
                    <div id="prebodyModal"></div>
                </div>
                <div class="modal-footer" id="footerModal">
                </div>
            </div><!-- /.modal-content -->            
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->  
	<?php echo $output;?>    	
    <input type="hidden" id="baseUrl" name="baseUrl" value="<?php echo base_url(); ?>"/>
    <div id="divKeepAlive" class="hide"></div>   
    <div id="divAuxAjax" class="hide"></div>   
</body>
</html>
<?php 	
	}
 ?>