@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Dealer Percentage            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management</a></li>
            <li><a href="{{url('/admin/dealer_percentage_history/')}}/{{$dealer_id}}"><i class="fa fa-percent"></i> Percentage History</a></li>
            <li class="active">Add Percentage</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Add Percentage</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Percentageform" method="POST" action="{{url('/admin/insertDealerPercentage')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
                      <div class="box-body">
                        <div class="form-group{{ $errors->has('share_percentage') ? ' has-error' : '' }}">
                          <label for="share_percentage">Percentage<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('share_percentage') }}" id="share_percentage" name="share_percentage" placeholder="Enter percentage" maxlength="2" OnKeypress="return isNumber(event)">
                          @if ($errors->has('share_percentage'))
                            <span class="help-block">
                              <strong>{{ $errors->first('share_percentage') }}</strong>
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
<script type="text/javascript"> 
  function isNumber(evt, element) 
  {
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (
          (charCode != 45 || $(element).val().indexOf('-') != -1) &&      // “-” CHECK MINUS, AND ONLY ONE.
          /*(charCode != 46 || $(element).val().indexOf('.') != -1) && */     // “.” CHECK DOT, AND ONLY ONE.
          (charCode < 48 || charCode > 57))
          return false;
      else
      {
          return true;    
      }
  }
  </script>   
@endsection