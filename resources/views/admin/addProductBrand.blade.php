@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        Product Brands            <!-- <small>advanced tables</small> -->
        </h1>
        <ol class="breadcrumb">
        <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{url('/admin/product_brands')}}"><i class="fa fa-product-hunt"></i> Brands</a></li>
        <li class="active">Add Product Brand</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
            <div class="box">
            <div class="box-header">
                <h3 class="box-title">Add Product Brand</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                @if(Session::has('error'))
                <div class="alert alert-danger">{{ Session::get('error') }}</div>
                @endif
                @if(Session::has('success'))
                <div class="alert alert-success">{{ Session::get('success') }}</div>
                @endif
                <form role="form" id="productbrandform" method="POST" action="{{url('/admin/insertProductBrand')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">
                        <div class="form-group{{ $errors->has('brand_name') ? ' has-error' : '' }}">
                          <label for="brand_name">Brand Name<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('brand_name') }}" id="brand_name" name="brand_name" placeholder="Enter brand name">
                          @if ($errors->has('brand_name'))
                            <span class="help-block">
                              <strong>{{ $errors->first('brand_name') }}</strong>
                            </span>
                          @endif
                        </div>
                      <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                  </form>
            </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->  
@endsection