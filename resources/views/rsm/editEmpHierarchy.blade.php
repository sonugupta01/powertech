@extends('layouts.dashboard')
{{-- {{dd("edit blade")}} --}}
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Attendance Shifting            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/rsm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      {{-- <li><a href="{{url('/rsm/emp_hierarchy')}}"><i class="fa fa-users"></i> Attendance Shifting</a></li> --}}
      <li><a href="{{url('/rsm/staff_management')}}"><i class="fa fa-users"></i> Staff Management</a>
      <li class="active">Edit Attendance Shifting</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Attendance Shifting  </h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="EmpHierarchyForm" method="POST" action="{{url('/rsm/updateEmpHierarchy')}}" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="id" value="{{@$result->id}}">
                <input type="hidden" name="user_id" id="userId" value="{{@$result->user_id}}">
                <input type="hidden" id="des_id" value="{{get_designation(@$result->user_id)}}">
                {{-- <input type="hidden" name="dealer_id" value="{{$result->dealer_id}}"> --}}
                <div class="box-body">
                  <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                    <label for="name">Name<span class="required-title">*</span></label>
                    <input type="text" class="form-control required" value="{{get_name(@$result->user_id)}}" id="name" name="name" placeholder="Enter name" readonly>
                    @if ($errors->has('name'))
                      <span class="help-block">
                        <strong>{{ $errors->first('name') }}</strong>
                      </span>
                    @endif
                  </div>
                  @if($dep_des->designation_id == 14 || $dep_des->designation_id == 15 || $dep_des->designation_id == 16 || $dep_des->designation_id == 17 || $dep_des->designation_id == 19 || $dep_des->designation_id == 21)
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('del_id') ? ' has-error' : '' }}">
                        <label for="del_id">Dealer<span class="required-title">*</span></label>
                        <select class="form-control required" id="del_id" name="del_id">
                          <option value="">Select Dealer</option>
                          @foreach($dealers as $dealer)
                            <option value="{{$dealer->dealer_id}}" {{@$result->dealer_id==$dealer->dealer_id? 'selected':''}}>{{ucwords($dealer->dealer_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('del_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('del_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('dealer_authid') ? ' has-error' : '' }}" id="dealer_authority">
                        <label for="dealer_authid">Report Authority<span class="required-title">*</span></label>
                        <select class="form-control required" id="dealer_authid" name="dealer_authid">
                          <option value="">Select Reporting Authority</option>
                          <option value="{{$dealer_authorities}}" {{$result->reporting_authority==$dealer_authorities?'selected':''}}>{{ucwords(get_name($dealer_authorities))}} - {{get_designation_by_userid($dealer_authorities)}}</option>
                          {{-- @foreach($dealer_authorities as $del_auth)
                          <option value="{{$del_auth}}" {{$result->reporting_authority==$del_auth?'selected':''}}>{{ucwords(get_name($del_auth))}} - {{get_designation_by_userid($del_auth)}}</option>
                          @endforeach --}}
                        </select>
                        @if ($errors->has('dealer_authid'))
                        <span class="help-block">
                          <strong>{{ $errors->first('dealer_authid') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  @else
                  <div class="row">
                      <div class="col-md-6">
                        <div class="form-group{{ $errors->has('reporting_level') ? ' has-error' : '' }}" id="reportingLevelSelector">
                          <label for="reporting_level">Reporting Level<span class="required-title">*</span></label>
                          <select class="form-control required" id="reporting_level" name="reporting_level">
                            <option value="">Select Reporting Level</option>
                            @foreach($reportinglevels as $reportinglevel)
                            <option value="{{ $reportinglevel->id }}" {{(getlevelbydesignation(get_designation($result->reporting_authority)) == $reportinglevel->id)?'selected':'' }}>{{ $reportinglevel->level }}</option>
                            @endforeach
                          </select>
                          @if ($errors->has('reporting_level'))
                          <span class="help-block">
                            <strong>{{ $errors->first('reporting_level') }}</strong>
                          </span>
                          @endif
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group{{ $errors->has('authority') ? ' has-error' : '' }}" id="authoritySelector">
                          <label for="Authority">Report Authority<span class="required-title">*</span></label>
                          <input type="hidden" name="auth_hidden_id" id="auth_hidden_id" value="{{$result->reporting_authority}}">
                          <select class="form-control required" id="authority" name="authority" >
                            <option value="">Select Reporting Authority</option>
                            @foreach($reporting_authorities as $reporting_authority)
                            <option value="{{ $reporting_authority->uid }}" {{($result->reporting_authority == $reporting_authority->uid)?'selected':'' }}>{{ $reporting_authority->uname }} - {{get_designation_by_userid($reporting_authority->uid)}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                  @endif
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('fdate') ? ' has-error' : '' }}">
                        <label for="from_date">From<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('fdate', @$result->from_date) }}" id="fdate" name="fdate" placeholder="Enter Date" readonly>
                        @if ($errors->has('fdate'))
                        <span class="help-block">
                          <strong>{{ $errors->first('fdate') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('todate') ? ' has-error' : '' }}">
                        <label for="to_date">To<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('todate', @$result->to_date) }}" id="todate" name="todate" placeholder="Enter Date" readonly>
                        @if ($errors->has('todate'))
                        <span class="help-block">
                          <strong>{{ $errors->first('todate') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
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
<script>
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 2000);
$('#fdate').datepicker({ autoclose: true, startDate: new Date(), format: 'yyyy-mm-dd', });
$('#todate').datepicker({ autoclose: true, startDate: new Date(), format: 'yyyy-mm-dd', });

$(document).ready(function(){
  $("#del_id").on('change',function(){
    var del_id = $(this).val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('rsm/getDealerPermission') }}/"+ user_id + "/" + del_id,
        method: 'GET',
        success: function(data) {
          if (data.html) {
            alert(data.html);
          }
        }
    });
  });
});
$(document).ready(function(){
  $("#del_id").on('change',function(){
    var del_id = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      del_id: del_id,
    };
    $.ajax({
      url: "{{ url('rsm/getdealerauthority') }}",
      headers: {'X-CSRF-TOKEN': token},
      method: 'POST',
      data: data,
      datatype: 'JSON',
      success: function (resp) {
        $("#dealer_authid").html(resp);
        return false;
      }
    });
    return false;
  });
});
$(document).ready(function(){
  $("#authority").on('change',function(){
    var auth_id = $(this).val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('rsm/getauthority') }}/"+ user_id + "/" + auth_id,
        method: 'GET',
        success: function(data) {
          if (data.html) {
            alert(data.html);
          }
        }
    });
  });
});
$(document).ready(function(){
  $("#dealer_authid").on('change',function(){
    var del_authid = $(this).val();
    var del_id = $('#del_id').val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('rsm/getreportingpermission') }}/"+ user_id + "/" + del_id + "/" + del_authid,
        method: 'GET',
        success: function(data) {
          if (data.html) {
            alert(data.html);
          }
        }
    });
  });
});
$(document).ready(function(){
  $("#reporting_level").on('change',function(){
    var reportinglevel = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      reportinglevel: reportinglevel,
    };
    $.ajax({
      url: "{{ url('rsm/getreportingauthority') }}",
      headers: {'X-CSRF-TOKEN': token},
      method: 'POST',
      data: data,
      datatype: 'JSON',
      success: function (resp) {
        $("#authority").html(resp);
        return false;
      }
    });
    return false;
  });
});
function validateForm() {
  if ( $('#authority').val() == ''){
    var x = document.forms["EmpHierarchyForm"]["authority"].value;
    if (x == "" || x == null) {
      alert("Please Select Report Authority");
      return false;
    }
  } else if ($('#des_id').val() == '14' || $('#des_id').val() == '15' || $('#des_id').val() == '16' || $('#des_id').val() == '17' || $('#des_id').val() == '19' || $('#des_id').val() == '21') {
    if ($('#del_id').val() == '') {
      var y = document.forms["EmpHierarchyForm"]["del_id"].value;
      if (y == "" || y == null) {
        alert("Please Select Dealer");
        return false;
      }
    } else {
      var z = document.forms["EmpHierarchyForm"]["dealer_authid"].value;
      if (z == "" || z == null) {
        alert("Please Select Dealer Authority");
        return false;
      }
    }
  } 
}
</script>
@endsection