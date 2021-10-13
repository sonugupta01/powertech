@extends('layouts.dashboard')

@section('content')
	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Gallery
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Gallery</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          	<div class="row">
	            <div class="col-xs-12">
	              	<div class="box">
						<div class="col-md-4 col-sm-6 col-xs-12 img-box" style="margin-left: 85px;">
							<div class="box box-widget widget-user">
								<div class="widget-user-header bg-aqua-active">
									<h3 class="widget-user-username">Images</h3>
									<h5 class="widget-user-desc"></h5>
								</div>
								<a href="{{url('/admin/images')}}">
									<div class="widget-user-image">
											<img class="img-circle gallery-box" alt="User Avatar" src="{{asset('images.png')}}">									
									</div>
								</a>

								<div class="box-footer">
									<div class="row">
										<div class="col-sm-4 border-right">
										</div>
										<div class="col-sm-4">
											<div class="description-block">
												<h5 class="description-header">{{$images}}</h5>
												<span class="description-text">Total</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4 col-sm-6 col-xs-12">
							<div class="box box-widget widget-user">
								<div class="widget-user-header bg-teal-active">
									<h3 class="widget-user-username">Videos</h3>
									<h5 class="widget-user-desc"></h5>
								</div>
								<a href="{{url('/admin/videos')}}">
									<div class="widget-user-image">
										<img class="img-circle gallery-box" alt="User Avatar" src="{{asset('youtube-symbol.png')}}" width="20">
									</div>
								</a>
								<div class="box-footer">
									<div class="row">
										<div class="col-sm-4 border-right">
										</div>
										<div class="col-sm-4">
											<div class="description-block">
												<h5 class="description-header">{{$videos}}</h5>
												<span class="description-text">Total</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div><!-- /.content-wrapper -->   
@endsection
