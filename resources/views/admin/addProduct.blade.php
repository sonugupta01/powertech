@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Product            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/products')}}"><i class="fa fa-product-hunt"></i> Products</a></li>
      <li class="active">Add Product</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Product</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="Productform" method="POST" action="{{url('/admin/insertProduct')}}" enctype="multipart/form-data">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                      <label for="name">Name<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('name') }}" id="name" name="name" placeholder="Enter name">
                      @if ($errors->has('name'))
                        <span class="help-block">
                          <strong>{{ $errors->first('name') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}">
                      <label for="quantity">Quantity<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('quantity') }}" id="quantity" name="quantity" placeholder="Enter quantity">
                      @if ($errors->has('quantity'))
                        <span class="help-block">
                          <strong>{{ $errors->first('quantity') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('uom') ? ' has-error' : '' }}">
                      <label for="uom">Unit of Measurement<span class="required-title">*</span></label>
                      <select class="form-control required" id="uom" name="uom">
                        <option value="2">ML</option>
                        <option value="1">Litre</option>
                        <option value="3">Pcs.</option>
                        <option value="4">Gms.</option>
                      </select>
                      @if ($errors->has('uom'))
                        <span class="help-block">
                          <strong>{{ $errors->first('uom') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                      <label for="price">Price<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('price') }}" id="price" name="price" placeholder="Enter price">
                      @if ($errors->has('price'))
                        <span class="help-block">
                          <strong>{{ $errors->first('price') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('brand_id') ? ' has-error' : '' }}">
                      <label for="brand_id">Brand<span class="required-title">*</span></label>
                      <select class="form-control required" id="brand_id" name="brand_id">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                        <option value="{{$brand->id}}" @if(old('brand_id') == $brand->id) {{ 'selected' }} @endif>{{ucwords($brand->brand_name)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('brand_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('brand_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="box-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </div>
            </form>
          </div><!-- /.box-body -->
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->    
@endsection