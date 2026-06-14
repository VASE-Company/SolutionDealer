<!-- Sidebar user panel (optional) -->
<?php
	if ($loggedIn) {		
		if (isset($companyLogo) && $companyLogo != "") {
?>
<img src="<?php echo base_url().$companyLogo; ?>?<?php echo filemtime($companyLogo); ?>" class="brand-image img-circle elevation-3" style="opacity: .8;">
<?php
		} else {
?>
<span class="company-logo"><?php echo substr($companyName,0,1); ?></span>		
<?php		
		}
?>
<span class="brand-text"><?php echo $companyName; ?></span>
<?php		
	}
?>
<?php
/* End of file company_data_view.php */
/* Location: ./application/views/main/company_data_view.php */