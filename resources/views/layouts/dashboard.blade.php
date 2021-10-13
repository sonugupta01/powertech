{{-- <!DOCTYPE html> --}}
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'PowerTechnologies | Admin') }}</title>
	<!-- Styles -->
	<!-- Bootstrap 3.3.2 -->
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
	<!-- Font Awesome Icons -->
	<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
	<!-- Theme style -->
	<link href="{{ asset('css/datepicker3.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('css/skins/_all-skins.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('css/red.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('css/custom_admin.css') }}" rel="stylesheet" type="text/css" />
	<script src="{{ asset('js/jquery.js') }}"></script>
	<!-- <script src="{{ asset('js/jquery-ui.min.js') }}" type="text/javascript"></script> -->
	<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
	<script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<script>
		$.widget.bridge('uibutton', $.ui.button);
	</script>
	<script src="{{ asset('js/bootstrap.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
	<script src="{{ asset('js/app.min.js') }}" type="text/javascript"></script>
</head>
<body class="skin-blue sidebar-mini">
	<div class="wrapper">
		<header class="main-header">
			<?php $designation = DB::table('staff_detail')->where('user_id',Auth::id())->first(); ?>
			<!-- Logo -->
			@if(Auth::user()->role==1)
			<a href="{{url('/admin')}}" class="logo">
				<span class="logo-mini">Admin</span>
				<span class="logo-lg"><img style="width: 190px; height: 50px;" src="{{asset('powertech2.png')}}"></span>
			</a>
			@elseif(Auth::user()->role==5)
			<a href="{{url('/asm')}}" class="logo">
				<span class="logo-mini">ASM</span>
				<span class="logo-lg"><img style="width: 190px;  height: 50px;" src="{{asset('powertech2.png')}}"></span>
			</a>
			@elseif(@$designation->designation_id == 13)
			<a href="{{url('/rsm')}}" class="logo">
				<span class="logo-mini">RSM</span>
				<span class="logo-lg"><img style="width: 190px;  height: 50px;" src="{{asset('powertech2.png')}}"></span>
			</a>
			@elseif(@$designation->designation_id == 23)
			<a href="{{url('/rsm')}}" class="logo">
				<span class="logo-mini">SSE</span>
				<span class="logo-lg"><img style="width: 190px;  height: 50px;" src="{{asset('powertech2.png')}}"></span>
			</a>
			@else
			<a href="{{url('/')}}" class="logo"><b>Power Technologies</b></a>
			@endif
			<!-- Header Navbar: style can be found in header.less -->
			
			<nav class="navbar navbar-static-top" role="navigation">
				
				<!-- Sidebar toggle button-->
				
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					
					<span class="sr-only">Toggle navigation</span>
					
				</a>
				
				<div class="navbar-custom-menu">
					
					<ul class="nav navbar-nav">
						
						<!-- Messages: style can be found in dropdown.less-->
						
						<!-- User Account: style can be found in dropdown.less -->
						
						<li class="dropdown user user-menu">
							
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								
								<span class="hidden-xs"><?php echo @Auth::user()->name;?></span>
								
							</a>
							
							<ul class="dropdown-menu">
								
								<!-- Menu Footer-->
								
								<li class="user-footer">
									
									<div class="pull-left">
										
										<a href="{{url('/changepassword')}}" class="btn btn-default btn-flat">Change Password</a>
										
									</div>
									
									<div class="pull-right">
										
										<a href="{{url('/logout')}}" class="btn btn-default btn-flat">Sign out</a>
										
									</div>
									
								</li>
								
							</ul>
							
						</li>
						
					</ul>
					
				</div>
				
			</nav>
			
		</header>
		
		<aside class="main-sidebar">
			
			<!-- sidebar: style can be found in sidebar.less -->
			
			<section class="sidebar">
				
				<!-- sidebar menu: : style can be found in sidebar.less -->
				
				<ul class="sidebar-menu">
					
					<li class="header">MAIN NAVIGATION</li>
					
					
					
					@if(Auth::user()->role==1)
					
					<li class="">
						
						<a href="{{url('/admin')}}">
							
							<i class="fa fa-dashboard"></i> <span>Dashboard</span>
							
						</a>	              
						
					</li>
					
					<li class="">
						
						<a href="{{url('/admin/dealer_management')}}">
							
							<i class="fa fa-users"></i> <span>Dealer/Office Management</span>
							
						</a>	              
						
					</li>
					
					<li <?php 
					$url = '';
					if(($url=="/admin/department") || ($url=="/admin/designation") || ($url=="/admin/staff_management")) {
						echo "class='treeview start active open'";
					} 
					?>>
					<a href="#">
						<i class="fa fa-file"></i>
						<span>Organisation</span>
						<span class="pull-right-container">
							<i class="fa fa-angle-down pull-right"></i>
						</span>
					</a>
					<ul class="treeview-menu">
						<li><a href="{{url('/admin/department')}}"><i class="fa fa-file"></i> Department</a></li>
						<li><a href="{{url('/admin/designation')}}"><i class="fa fa-file"></i> Designation</a></li>
						<li><a href="{{url('/admin/staff_management')}}"><i class="fa fa-users"></i> <span>Staff Management</span></a></li>
					</ul>
				</li>
				
				
				<!-- <li class="">
					<a href="{{url('/admin/designation')}}">
						<i class="fa fa-tasks"></i> <span>Designation</span>
					</a>	              
				</li> -->
				<li <?php 
				$url = '';
				if(($url=="/admin/groups") || ($url=="/admin/oems")) {
					echo "class='treeview start active open'";
				} 
				?>>
				<a href="#">
					<i class="fa fa-file"></i>
					<span>Grouping</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-down pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li><a href="{{url('/admin/groups')}}"><i class="fa fa-file"></i> Groups</a></li>
					<li><a href="{{url('/admin/oems')}}"><i class="fa fa-file"></i> OEM</a></li>
					<li><a href="{{url('/admin/models')}}"><i class="fa fa-file"></i> Models</a></li>
				</ul>
			</li>
			<li class="">
				<a href="#">
					<i class="fa fa-tasks"></i> <span>Job Module</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-down pull-right"></i>
					</span>
					<ul class="treeview-menu">
						<li><a href="{{url('/admin/jobs')}}"><i class="fa fa-file"></i> Jobs</a></li>
						<li><a href="{{url('/admin/jobs_treatment_list')}}"><i class="fa fa-file"></i> Jobs Treatment</a></li>	
					</ul>
				</a>	              
			</li>
			
			<!-- <li class="">
				<a href="{{url('/admin/history_jobs')}}">
					<i class="fa fa-tasks"></i> <span>History Jobs</span>
				</a>
			</li> -->
			
			

			<li <?php 
				$url = '';
				if(($url=="/admin/productBrands") || ($url=="/admin/products")) {
					echo "class='treeview start active open'";
				} 
				?>>
				<a href="#">
					<i class="fa fa-file"></i>
					<span>Product Master</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-down pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li class="">
						<a href="{{url('/admin/product_brands')}}">
							<i class="fa fa-product-hunt"></i> <span>Product Brands</span>
						</a>	
					</li>
					<li class="">
						<a href="{{url('/admin/products')}}">
							<i class="fa fa-product-hunt"></i> <span>Products</span>
						</a>	
					</li>
				</ul>
			</li>
			
			<li <?php 
				$url = '';
				if(($url=="/admin/treatments") || ($url=="/admin/treatmentTemplates") || ($url=="admin/targets")) {
					echo "class='treeview start active open'";
				} 
				?>>
				<a href="#">
					<i class="fa fa-file"></i>
					<span>Treatments Master</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-down pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li class="">
						<a href="{{url('/admin/treatmentTemplates')}}">
							<i class="fa fa-wrench"></i> <span>Treatments Template</span>
						</a>	              
					</li>
					<li class="">
						<a href="{{url('/admin/treatments')}}">
							<i class="fa fa-wrench"></i> <span>Treatments</span>
						</a>	              
					</li>
					<li class="">
						<a href="{{url('/admin/targets')}}">
							<i class="fa fa-wrench"></i> <span>Target</span>
						</a>	              
					</li>
				</ul>
			</li>
		
		
		
		<li class="">
			
			<a href="{{url('/admin/gallery')}}">
				
				<i class="fa fa-folder-open"></i> <span>Gallery</span>
				
			</a>	              
			
		</li>
		
		
		<li>
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Attendance</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li class="">
					<a href="{{url('/admin/attendance')}}">
						<i class="fa fa-file"></i> <span>Attendance Report</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/admin/daily_attendance')}}">
						<i class="fa fa-file"></i> <span>Daily Attendance</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/admin/relax_attendance')}}">
						<i class="fa fa-file"></i> <span>Relaxation Time</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/admin/late_attendance?interval=1')}}">
						<i class="fa fa-file"></i> <span>Late Attendance</span>
					</a>	              
				</li>
			</ul>
		</li>
		
		
		<!-- <li class="">
			
			<a href="{{url('/admin/reports')}}">
				
				<i class="fa fa-file"></i> <span>Reports</span>
				
			</a>	              
			
		</li> -->
		<li class="treeview">
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Reports</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li><a href="{{url('/admin/daily_report')}}"><i class="fa fa-file"></i> Daily Report</a></li>
				<li><a href="{{url('/admin/mis_report')}}"><i class="fa fa-file"></i> MIS Report</a></li>
				<li><a href="{{url('/admin/dcf_report')}}"><i class="fa fa-file"></i> DCF Report</a></li>
				<li><a href="{{url('/admin/consumption_report')}}"><i class="fa fa-file"></i> Consumption Report</a></li>
				<li><a href="{{url('/admin/performance_reports')}}"><i class="fa fa-file"></i> Performance Report</a></li>
				
			</ul>
		</li>
		
		{{-- <li class="">
			
			<a href="{{url('/admin/consumption_report')}}">
				
				<i class="fa fa-file"></i> <span>Consumption Report</span>
				
			</a>	              
			
		</li>
		
		<li class="">
			
			<a href="{{url('/admin/performance_reports')}}">
				
				<i class="fa fa-file"></i> <span>Performance Reports</span>
				
			</a>	              
			
		</li> --}}
		
		
		@endif
		
		@if(Auth::user()->role==5)
		
		<li class="">
			
			<a href="{{url('/asm')}}">
				
				<i class="fa fa-dashboard"></i> <span>Dashboard</span>
				
			</a>	              
			
		</li>
		
		{{-- <li class="">
			
			<a href="{{url('/asm/dealer_management')}}">
				
				<i class="fa fa-users"></i> <span>Dealer Management</span>
				
			</a>	              
			
		</li> --}}
		
		<li class="">
			<a href="{{url('/asm/staff_management')}}">
				<i class="fa fa-users"></i> <span>Staff Management</span>
			</a>
		</li>
		
		<li class="">
			<a href="{{url('/asm/targets')}}">
				<i class="fa fa-wrench"></i> <span>Target</span>
			</a>	              
		</li>
		
		<li class="">
			<a href="#">
				<i class="fa fa-tasks"></i> <span>Job Module</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
				<ul class="treeview-menu">
					<li><a href="{{url('/asm/jobs')}}"><i class="fa fa-file"></i> Jobs</a></li>
					<li><a href="{{url('/asm/jobs_treatment_list')}}"><i class="fa fa-file"></i> Jobs Treatment</a></li>	
				</ul>
			</a>	              
		</li>

		<li>
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Attendance</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li class="">
					<a href="{{url('/asm/attendance')}}">
						<i class="fa fa-file"></i> <span>Attendance Report</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/asm/daily_attendance')}}">
						<i class="fa fa-file"></i> <span>Daily Attendance</span>
					</a>	              
				</li>
			</ul>
		</li>

		<li class="treeview">
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Reports</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li><a href="{{url('/asm/daily_report')}}"><i class="fa fa-file"></i> Daily Report</a></li>
				<li><a href="{{url('/asm/mis_report')}}"><i class="fa fa-file"></i> MIS Report</a></li>
				<li><a href="{{url('/asm/dcf_report')}}"><i class="fa fa-file"></i> DCF Report</a></li>
				<li><a href="{{url('/asm/consumption_report')}}"><i class="fa fa-file"></i> Consumption Report</a></li>
				<li><a href="{{url('/asm/performance_reports')}}"><i class="fa fa-file"></i> Performance Report</a></li>
				
			</ul>
		</li>
		
		@endif 
		
		@if(@$designation->designation_id == 13)
		
		<li class="">
			
			<a href="{{url('/rsm')}}">
				
				<i class="fa fa-dashboard"></i> <span>Dashboard</span>
				
			</a>	              
			
		</li>
		
		{{-- <li class="">
			
			<a href="{{url('/rsm/dealer_management')}}">
				
				<i class="fa fa-users"></i> <span>Dealer Management</span>
				
			</a>	              
			
		</li> --}}
		
		<li class="">
			<a href="{{url('/rsm/staff_management')}}">
				<i class="fa fa-users"></i> <span>Staff Management</span>
			</a>
		</li>
		
		<li class="">
			<a href="{{url('/rsm/targets')}}">
				<i class="fa fa-wrench"></i> <span>Target</span>
			</a>	              
		</li>

		<li class="">
			<a href="#">
				<i class="fa fa-tasks"></i> <span>Job Module</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
				<ul class="treeview-menu">
					<li><a href="{{url('/rsm/jobs')}}"><i class="fa fa-file"></i> Jobs</a></li>
					<li><a href="{{url('/rsm/jobs_treatment_list')}}"><i class="fa fa-file"></i> Jobs Treatment</a></li>	
				</ul>
			</a>	              
		</li>
		
			<li>
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Attendance</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li class="">
					<a href="{{url('/rsm/attendance')}}">
						<i class="fa fa-file"></i> <span>Attendance Report</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/rsm/daily_attendance')}}">
						<i class="fa fa-file"></i> <span>Daily Attendance</span>
					</a>	              
				</li>
			</ul>
		</li>

		<li class="treeview">
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Reports</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li><a href="{{url('/rsm/daily_report')}}"><i class="fa fa-file"></i> Daily Report</a></li>
				<li><a href="{{url('/rsm/mis_report')}}"><i class="fa fa-file"></i> MIS Report</a></li>
				<li><a href="{{url('/rsm/dcf_report')}}"><i class="fa fa-file"></i> DCF Report</a></li>
				<li><a href="{{url('/rsm/consumption_report')}}"><i class="fa fa-file"></i> Consumption Report</a></li>
				<li><a href="{{url('/rsm/performance_reports')}}"><i class="fa fa-file"></i> Performance Report</a></li>
				
			</ul>
		</li>
		
		@endif
		
		@if(@$designation->designation_id == 23)
		
		<li class="">
			
			<a href="{{url('/sse')}}">
				
				<i class="fa fa-dashboard"></i> <span>Dashboard</span>
				
			</a>	              
			
		</li>
		
		{{-- <li class="">
			
			<a href="{{url('/sse/dealer_management')}}">
				
				<i class="fa fa-users"></i> <span>Dealer Management</span>
				
			</a>	              
			
		</li> --}}
		
		<li class="">
			<a href="{{url('/sse/staff_management')}}">
				<i class="fa fa-users"></i> <span>Staff Management</span>
			</a>
		</li>
		
		<li class="">
			<a href="{{url('/sse/targets')}}">
				<i class="fa fa-wrench"></i> <span>Target</span>
			</a>	              
		</li>

		<li class="">
			<a href="#">
				<i class="fa fa-tasks"></i> <span>Job Module</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
				<ul class="treeview-menu">
					<li><a href="{{url('/sse/jobs')}}"><i class="fa fa-file"></i> Jobs</a></li>
					<li><a href="{{url('/sse/jobs_treatment_list')}}"><i class="fa fa-file"></i> Jobs Treatment</a></li>	
				</ul>
			</a>	              
		</li>
		
		<li>
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Attendance</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li class="">
					<a href="{{url('/sse/attendance')}}">
						<i class="fa fa-file"></i> <span>Attendance Report</span>
					</a>	              
				</li>
				<li class="">
					<a href="{{url('/sse/daily_attendance')}}">
						<i class="fa fa-file"></i> <span>Daily Attendance</span>
					</a>	              
				</li>
			</ul>
		</li>

		<li class="treeview">
			<a href="#">
				<i class="fa fa-file"></i>
				<span>Reports</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-down pull-right"></i>
				</span>
			</a>
			<ul class="treeview-menu">
				<li><a href="{{url('/sse/daily_report')}}"><i class="fa fa-file"></i> Daily Report</a></li>
				<li><a href="{{url('/sse/mis_report')}}"><i class="fa fa-file"></i> MIS Report</a></li>
				<li><a href="{{url('/sse/dcf_report')}}"><i class="fa fa-file"></i> DCF Report</a></li>
				<li><a href="{{url('/sse/consumption_report')}}"><i class="fa fa-file"></i> Consumption Report</a></li>
				<li><a href="{{url('/sse/performance_reports')}}"><i class="fa fa-file"></i> Performance Report</a></li>
				
			</ul>
		</li>
		
		@endif
		
		
		
	</ul>
	
	<div>
		
		<img style="width: 200px; margin-top: 10px; margin-left: 13px; height: 70px;" src="{{asset('powertech2.png')}}">
		
	</div>
	
	<div style="color: #fff; padding: 0px 8px; margin-top: 20px;">Address:</div>
	
	<div style="color: #b8c7ce; padding: 0px 8px;">Power Technologies</div>
	
	<div style="color: #b8c7ce; padding: 0px 8px;">#1589, H.B.Colony ,Sector-9,</div>
	
	<div style="color: #b8c7ce; padding: 0px 8px;">Ambala City, Haryana.</div>
	
	<!-- <div style="color: #b8c7ce; padding: 0px 8px;">info@autosolutions.in</div>
		
		<div style="color: #b8c7ce; padding: 0px 8px;">9501326500 & 9888245715</div> -->
		
		<div style="color: #b8c7ce; padding: 0px 8px;">GST No. 06DBMPS6613D1ZJ</div>
		
	</section>
	
	<!-- /.sidebar -->
	
</aside>

@yield('content') 

</div><!-- ./wrapper -->

</body>

</html>