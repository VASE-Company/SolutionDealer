<!-- Sidebar user panel (optional) -->
<?php
	if ($loggedIn) {		
?>
<!-- Sidebar Menu -->
<nav class="mt-2">
	<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
		<!-- Add icons to the links using the .nav-icon class
		with font-awesome or any other icon font library -->
		<?php
			for ($i=0; $i < count($menu); $i++) {
				if (isset($menu[$i]['submenu'])) {
					$submenu = $menu[$i]['submenu'];
				} else {
					$submenu = NULL;
				}

				if (!isset($menu[$i]['active'])) $menu[$i]['active'] = false;

				if (isset($submenu) && count($submenu) > 0) {
		?>
		<li class="nav-item has-treeview"> <?php //echo ($menu[$i]['active']?"menu-open":""); //PARA QUE QUEDE ABIERTO EL SUBMENU ?>
			<a href="#" class="nav-link <?php echo ($menu[$i]['active']?"active":""); ?>">
				<i class="nav-icon <?php echo $menu[$i]['icon']; ?>"></i>
				<p>
					<?php echo $menu[$i]['text']; ?>
					<i class="right fas fa-angle-left"></i>
				</p>
			</a>
			<ul class="nav nav-treeview">
				<?php
					for ($j=0; $j < count($submenu); $j++) {
						if (!isset($submenu[$j]['active'])) $submenu[$j]['active'] = false;
				?>
				<li class="nav-item">
					<a href="<?php echo $submenu[$j]['link']; ?>" class="nav-link <?php echo ($submenu[$j]['active']?"active":""); ?>">
						<i class="fas fa-caret-right nav-icon"></i>
						<p><?php echo $submenu[$j]['text']; ?></p>
					</a>
				</li>	
				<?php
					}
				?>		
			</ul>
		</li>
		<?php
				} else {
		?>
		<li class="nav-item">
			<a href="<?php echo $menu[$i]['link']; ?>" class="nav-link <?php echo ($menu[$i]['active']?"active":""); ?>">
				<i class="nav-icon <?php echo $menu[$i]['icon']; ?>"></i>
				<p><?php echo $menu[$i]['text']; ?></p>
			</a>
		</li>   
		<?php
				}
			}
		?>
	</ul>
</nav>
<!-- /.sidebar-menu -->
<?php
	}
?>
<?php
/* End of file sidebar_menu_view.php */
/* Location: ./application/views/main/sidebar_menu_view.php */