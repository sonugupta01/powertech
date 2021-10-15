@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Dealer Templates            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management</a></li>
      <li><a href="{{url('/admin/dealerTemplates/')}}/{{$dealer_id}}"><i class="fa fa-users"></i> Templates</a></li>
      <li class="active">Edit Template</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Template</h3>
            <a href="{{url('/admin/dealerTemplates/')}}/{{$dealer_id}}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="templateForm" method="POST" action="{{url('/admin/updateDealerTemplate')}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="temp_id" value="{{$result->id}}">
                <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
                <div class="box-body">
                  <div class="form-group{{ $errors->has('dealer_name') ? ' has-error' : '' }}">
                    <label for="dealer_name">Dealer Name</label>
                    <input type="text" class="form-control required" value="{{get_dealer_name($dealer_id)}}" id="dealer_name" name="dealer_name" disabled>
                    @if ($errors->has('dealer_name'))
                      <span class="help-block">
                        <strong>{{ $errors->first('dealer_name') }}</strong>
                      </span>
                    @endif
                  </div>

                  <div class="form-group{{ $errors->has('template_id') ? ' has-error' : '' }}">
                    <label for="template_id">Template</label>
                    <select class="form-control required" id="template_id" name="template_id">
                      <option value="">Select Template</option>
                      @foreach($templates as $template)
                        <option value="{{$template->id}}" {{ $result->template_id==$template->id? 'selected':''}}>{{ucwords($template->temp_name)}}</option>
                      @endforeach
                      </select>
                    @if ($errors->has('template_id')) 
                      <span class="help-block">
                        <strong>{{ $errors->first('template_id') }}</strong>
                      </span>
                    @endif
                  </div>

                <!-- /.box-body -->
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