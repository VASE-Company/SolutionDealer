<!-- Sidebar user panel (optional) -->
<?php
	if ($loggedIn) {		
?>
<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
        <img src="<?php echo base_url().$image."?".filemtime($image); ?>" class="user-img">
    </div>
    <div class="info">
        <a href="<?php echo $link; ?>" class="d-block"><?php echo $name; ?></a>
    </div>
</div>
<?php
	}
?>
<?php
/* End of file user_data_view.php */
/* Location: ./application/views/main/user_data_view.php */