<!-- Notifications Dropdown Menu -->
<li class="nav-item dropdown">
	<a class="nav-link" data-toggle="dropdown" href="#">
		<i class="far fa-bell"></i>
		<?php if ($countOfNotifications > 0) { ?>
		<span class="badge badge-info navbar-badge"><?php echo $countOfNotifications; ?></span>
		<?php } ?>
	</a>
	<?php if ($countOfNotifications > 0) { ?>
	<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">	
		<?php for ($i=0; $i < count($notifications); $i++) { ?>	
		<?php if ($i > 0) { ?>
		<div class="dropdown-divider"></div>
		<?php } ?>
		<a href="<?php echo ($notifications[$i]['link'] != ""?$notifications[$i]['link']:"#"); ?>" class="dropdown-item" style="font-size:14px;">
			<?php echo $notifications[$i]['observation']; ?>		
			<span class="float-right text-muted text-sm"><?php echo getTimeFromDate($notifications[$i]['date'],true); ?></span>	
		</a>		
		<?php } ?>		
		<div class="dropdown-divider"></div>
        <a href="<?php echo  base_url(); ?>panel" class="dropdown-item dropdown-footer">Ver todas las notificaciones (<?php echo $countOfNotifications; ?>)</a>        
	</div>
	<?php } ?>
</li>
<?php
/* End of file main_header_notifications_view.php */
/* Location: ./application/views/main/main_header_notifications_view.php */