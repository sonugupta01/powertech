@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Advisors            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management</a></li>
            <li><a href="{{url('/admin/advisors/')}}/{{$dealer_id}}"><i class="fa fa-users"></i> Advisors ({{get_name($dealer_id)}})</a></li>
            <li class="active">Edit Advisor</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Edit Advisor of <b>{{get_name($dealer_id)}}</b></h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Staffform" method="POST" action="{{url('/admin/updateAdvisor')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
                      <input type="hidden" name="id" value="{{$result->id}}">
                      <div class="box-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                              <label for="name">Name & Father Name<span class="required-title">*</span></label>
                              <input type="text" class="form-control required" value="{{ old('name',$result->name) }}" id="name" name="name" placeholder="Enter name">
                              @if ($errors->has('name'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('name') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('pan_no') ? ' has-error' : '' }}">
                              <label for="pan_no">PAN No.</label>
                              <input type="text" class="form-control required" value="{{ old('pan_no',$result->pan_no) }}" id="pan_no" name="pan_no" placeholder="Enter PAN no.">
                              @if ($errors->has('pan_no'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('pan_no') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">
                              <label for="mobile_no">Mobile No.<!-- <span class="required-title">*</span> --></label>
                              <input type="text" class="form-control required" value="{{ old('mobile_no',$result->mobile_no) }}" id="mobile_no" name="mobile_no" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">
                              @if ($errors->has('mobile_no'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('mobile_no') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('department') ? ' has-error' : '' }}">
                              <label for="department">Department<span class="required-title">*</span></label>
                              <select class="form-control required" id="department" name="department">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                  <option value="{{$department->id}}" {{ $result->department==$department->id? 'selected':''}}>{{ucwords($department->name)}}</option>
                                @endforeach
                                </select>
                              @if ($errors->has('department')) 
                                <span class="help-block">
                                  <strong>{{ $errors->first('department') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                        </div>
                        {{-- <div class="row">
                          <div class="col-md-6">
                            <div class="form-group{{ $errors->has('advisor_share') ? ' has-error' : '' }}">
                              <label for="advisor_share">Percentage Share<span class="required-title">*</span></label>
                              <input type="text" class="form-control required" value="{{ old('advisor_share',$result->advisor_share) }}" id="advisor_share" name="advisor_share" placeholder="Enter percentage share" maxlength="2" OnKeypress="return isNumber(event)">
                              @if ($errors->has('advisor_share'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('advisor_share') }}</strong>
                                </span>
                              @endif
                            </div>
                          </div>
                        </div> --}}
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