@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Advisor Percentage            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management</a></li>
            <li><a href="{{url('/admin/advisor_percentage_history/')}}/{{$dealer_id}}/{{$advisor_id}}"><i class="fa fa-percent"></i> Incentive History</a></li>
            <li class="active">Add Incentive</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Add Incentive</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Percentageform" method="POST" action="{{url('/admin/insertAdvisorPercentage')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
                      <input type="hidden" name="advisor_id" value="{{$advisor_id}}">
                      <div class="box-body">
                        <div class="form-group{{ $errors->has('advisor_share') ? ' has-error' : '' }}">
                          <label for="advisor_share">Incentive<span class="required-title">*</span></label>
                          <input type="text" class="form-control required" value="{{ old('advisor_share') }}" id="advisor_share" name="advisor_share" placeholder="Enter percentage" maxlength="2" OnKeypress="return isNumber(event)">
                          @if ($errors->has('advisor_share'))
                            <span class="help-block">
                              <strong>{{ $errors->first('advisor_share') }}</strong>
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