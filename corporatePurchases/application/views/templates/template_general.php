<div class="wrapper">
	<!-- Navbar -->
	<nav class="main-header navbar navbar-expand navbar-white navbar-light">
		<!-- Left navbar links -->
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
			</li>
		</ul>

		<!-- SEARCH FORM -->
		<form method="post" accept-charset="utf-8" class="form-inline ml-3" action="<?php echo base_url().'orders/search'; ?>" onsubmit="return ($.trim($('#frmSearchHeader #numberFilter').val()) != '');" id="frmSearchHeader">
			<div class="input-group input-group-sm">
				<input class="form-control form-control-navbar" type="text" placeholder="Nº Pedido/Orden/Remito" aria-label="Search" id="numberFilter" name="numberFilter" maxlength="15" title="Búsqueda por Nº de pedido/orden de compra/remito">
				<div class="input-group-append">
					<button class="btn btn-navbar" type="submit">
						<i class="fas fa-search"></i>
					</button>
				</div>
			</div>
		</form>

		<!-- Right navbar links -->
		<ul class="navbar-nav ml-auto" id="divNotifications">						
			<?php echo $this->load->get_section('mainHeaderNotifications'); ?>			
		</ul>
	</nav>
	<!-- /.navbar -->

	<!-- Main Sidebar Container -->
	<aside class="main-sidebar sidebar-dark-primary elevation-4">
		
		<!-- Brand Logo -->		
		<div class="brand-link">
			<?php echo $this->load->get_section('companyData'); ?>			
		</div>

		<!-- Sidebar -->
		<div class="sidebar">
			<?php echo $this->load->get_section('userData'); ?>
			
			<?php echo $this->load->get_section('sidebarMenu'); ?>
		</div>
	<!-- /.sidebar -->
	</aside>

	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<?php echo $this->load->get_section('mainHeader'); ?>
		
		<!-- Main content -->
		<section class="content">
			<?php echo $this->load->get_section('mainContent'); ?>
		</section>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->

	<!-- Control Sidebar -->
	<aside class="control-sidebar control-sidebar-dark">
	<!-- Control sidebar content goes here -->
	</aside>
	<!-- /.control-sidebar -->

	<!-- Main Footer -->
	<!--
	<footer class="main-footer">
		<strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
		All rights reserved.
		<div class="float-right d-none d-sm-inline-block">
			<b>Version</b> 3.0.5
		</div>
	</footer>
	-->
</div>
<!-- ./wrapper -->