@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Staff Management            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/staff_management')}}"><i class="fa fa-users"></i> Staff Management</a></li>
      <li class="active">Edit Staff Member</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Staff Member</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger"><b>{{ Session::get('error') }}</b></div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="StaffForm" method="POST" action="{{url('/admin/updateStaff')}}" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="id" id="userId" value="{{$result->id}}">
                <input type="hidden" name="hidden_authority_id" value="{{@$authority_id->authority_id}}">
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('firm_id') ? ' has-error' : '' }}">
                        <label for="Firm">Firm<span class="required-title">*</span></label>
                        <select class="form-control required" id="firm_id" name="firm_id">
                          <option value="">Select Firm</option>
                          @foreach($firms as $firm)
                          <option value="{{$firm->id}}" {{$result->firm_id == $firm->id ? 'selected':''}}>{{ucwords($firm->firm_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('firm_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('firm_id') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('depart_id') ? ' has-error' : '' }}">
                        <label for="depart_id">Department<span class="required-title">*</span></label>
                        <select class="form-control" id="depart_id" name="depart_id">
                          <option value="">Select Department</option>
                          @foreach($departments as $depart)
                            <option value="{{$depart->depart_id}}" {{@$dep_des->department_id == $depart->depart_id ? 'selected':''}}>{{ucwords($depart->depart_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('depart_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('depart_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('level') ? ' has-error' : '' }}">
                        <label for="level">Designation Level<span class="required-title">*</span></label>
                        <select class="form-control required level" id="level" name="level">
                          <option value="">Select Level</option>
                          @foreach($levels as $key=>$level)
                          <option value="{{$level->id}}" {{$level->id==(getlevelbydesignation(@$dep_des->designation_id))?'selected':''}}>{{ucwords($level->level)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('level'))
                        <span class="help-block">
                          <strong>{{ $errors->first('level') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group{{ $errors->has('designation') ? ' has-error' : '' }}">
                        <label for="Designation">Designation<span class="required-title">*</span></label>
                        <select class="form-control required designation" id="designation" name="designation">
                          <option value="">Select Designation</option>
                          @foreach($designations as $designation)
                          <option value="{{ $designation->id }}" {{(@$dep_des->designation_id == $designation->id)?'selected':'' }}>{{ $designation->designation }}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('designation'))
                        <span class="help-block">
                          <strong>{{ $errors->first('designation') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    {{-- <div class="col-md-2" style="width: 10%;">
                      <div class="form-group{{ $errors->has('is_office') ? ' has-error' : '' }}" id="officecheckbox">
                        <label for="is_office">Is Office</label>
                        <div class="checkbox">
                          <label><input name="is_office" id="is_office" type="checkbox" value="{{ old('is_office') }}" @if(!empty($result->dealer_office)) {{'checked'}} @endif>Is Office</label>
                        </div>
                        @if ($errors->has('is_office'))
                        <span class="help-block">
                          <strong>{{ $errors->first('is_office') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div> --}}
                    <div class="col-md-3">
                      <div class="form-group{{ $errors->has('dealer_office') ? ' has-error' : '' }}" id="dealerofficeselector" style="display: none;">
                        <label for="dealer_office">Office/Dealer<span class="required-title">*</span></label>
                        <select class="form-control required dealer_office" id="dealer_office" name="dealer_office" style="width: auto;">
                          <option value="">Select Office</option>
                          @foreach($offices as $office)
                          <option value="{{$office->office_id}}" {{$result->dealer_office == $office->office_id?'selected':''}}>{{ucwords($office->office_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('dealer_office'))
                        <span class="help-block">
                          <strong>{{ $errors->first('dealer_office') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
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
                        <select class="form-control required" id="authority" name="authority" >
                          <option value="">Select Reporting Authority</option>
                          @foreach($reporting_authorities as $reporting_authority)
                          <option value="{{ $reporting_authority->uid }}" {{($result->reporting_authority == $reporting_authority->uid)?'selected':'' }}>{{ $reporting_authority->uname }} - {{get_designation_by_userid($reporting_authority->uid)}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}" id="dealerselecter" style="display: none;">
                        <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                        <select class="form-control required" id="dealer_id" name="dealer_id">
                          <option value="">Select Dealer</option>
                          @foreach($dealers as $dealer)
                          <option value="{{$dealer->dealer_id}}" {{$result->dealer_id==$dealer->dealer_id?'selected':''}}>{{ucwords($dealer->dealer_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('dealer_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('dealer_id') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('dealer_authid') ? ' has-error' : '' }}" id="dealer_authority" style="display: none;">
                        <label for="dealer_authid">Report Authority<span class="required-title">*</span></label>
                        <select class="form-control required" id="dealer_authid" name="dealer_authid">
                          <option value="">Select Reporting Authority</option>
                          @foreach($dealer_authorities as $del_auth)
                          <option value="{{$del_auth}}" {{$result->reporting_authority==$del_auth?'selected':''}}>{{ucwords(get_name($del_auth))}} - {{get_designation_by_userid($del_auth)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('dealer_authid'))
                        <span class="help-block">
                          <strong>{{ $errors->first('dealer_authid') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('access') ? ' has-error' : '' }}" id="accessByDesignation">
                        <label for="Access">Access Rights<span class="required-title">*</span></label>
                        <select class="form-control required" id="access" name="access_rights">
                          <option value="3" @if($result->role==3) {{'selected'}} @endif>All</option>
                          <option value="4" @if($result->role==4) {{'selected'}} @endif>Attendance only</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('emp_code') ? ' has-error' : '' }}">
                        <label for="emp_code">Emp. Code<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('emp_code',@$dep_des->emp_code) }}" id="emp_code" name="emp_code" placeholder="Enter Employee Code">
                        @if ($errors->has('emp_code'))
                          <span class="help-block">
                            <strong>{{ $errors->first('emp_code') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="name">Name<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('name',$result->name) }}" id="name" name="name" placeholder="Enter name">
                        @if ($errors->has('name'))
                          <span class="help-block">
                            <strong>{{ $errors->first('name') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label for="email">Email</label>
                        <input type="text" class="form-control required" value="{{ old('email', $result->email) }}" id="email" name="email" placeholder="Enter email">
                        @if ($errors->has('email'))
                        <span class="help-block">
                          <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <label for="password">Password</label>
                        <input type="password" class="form-control required" value="{{ old('password') }}" id="password" name="password" placeholder="Enter Password">
                        @if ($errors->has('password'))
                        <span class="help-block">
                          <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('salary') ? ' has-error' : '' }}">
                        <label for="salary">Salary<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('salary', @$emp_salary->emp_salary) }}" id="salary" name="salary" placeholder="Enter salary" OnKeypress="return isNumber(event)">
                        @if ($errors->has('salary'))
                        <span class="help-block">
                          <strong>{{ $errors->first('salary') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">
                        <label for="mobile_no">Mobile No.<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('mobile_no',$result->mobile_no) }}" id="mobile_no" name="mobile_no" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">
                        @if ($errors->has('mobile_no'))
                          <span class="help-block">
                            <strong>{{ $errors->first('mobile_no') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('alt_mobile_no') ? ' has-error' : '' }}">
                        <label for="alt_mobile_no">Alternate Mobile No.</label>
                        <input type="text" class="form-control required" value="{{ old('alt_mobile_no',@$result->alt_mobile_no) }}" id="alt_mobile_no" name="alt_mobile_no" placeholder="Enter Alternate mobile no. if any " maxlength="10" OnKeypress="return isNumber(event)">
                        @if ($errors->has('alt_mobile_no'))
                        <span class="help-block">
                          <strong>{{ $errors->first('alt_mobile_no') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  {{-- <div class="row">
                    <div class="col-md-12">
                      <div class="form-group{{ $errors->has('user_group') ? ' has-error' : '' }}" id="user_group">
                        <label for="user_group">User Group<span class="required-title">*</span></label>
                        <select class="form-control required" id="user_group" name="user_group">
                          <option value="">Select Group</option>
                          @foreach($groups as $group)
                          <option value="{{$group->id}}" {{$result->group_id==$group->id?'selected':''}}>{{ucwords($group->group_name)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('user_group'))
                        <span class="help-block">
                          <strong>{{ $errors->first('user_group') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                  </div> --}}
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('doj') ? ' has-error' : '' }}">
                        <label for="doj">Date Of Joining<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('doj',@$dep_des->doj) }}" id="doj" name="doj" placeholder="Enter Employee Joining Date" autocomplete="off" readonly>
                        @if ($errors->has('doj'))
                        <span class="help-block">
                          <strong>{{ $errors->first('doj') }}</strong>
                        </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('dol') ? ' has-error' : '' }}">
                        <label for="dol">Date Of Leaving<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('dol',@$dep_des->dol) }}" id="dol" name="dol" placeholder="Enter Employee Leaving Date" autocomplete="off" readonly>
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
$('#doj').datepicker({ autoclose: true, format: 'yyyy-mm-dd', });
$('#dol').datepicker({ autoclose: true, format: 'yyyy-mm-dd', });

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


$(document).ready(function(){
  if ( $('#level').val() == '1') {
    $("#reportingLevelSelector").hide();
    $("#authoritySelector").hide();
  } else {
    $("#reportingLevelSelector").show();
    $("#authoritySelector").show();
  }
  $('#level').on('change', function() {
    if (this.value == '1') {
      $("#reportingLevelSelector").hide();
      $("#authoritySelector").hide();
    } else {
      $("#reportingLevelSelector").show();
      $("#authoritySelector").show();
    }
  });
});

$(document).ready(function(){
  $(".level").on('change',function(){
    var level = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      level: level,
    };
    $.ajax({
      url: "{{ url('admin/getdesbylevel') }}",
      headers: {'X-CSRF-TOKEN': token},
      method: 'POST',
      data: data,
      datatype: 'JSON',
      success: function (resp) {
        $("#designation").html(resp);
        return false;
      }
    });
  });
});

$(document).ready(function(){
  $(".level").on('change',function(){
    var level = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      level: level,
    };
    $.ajax({
      url: "{{ url('admin/getreportinglevel') }}",
      headers: {'X-CSRF-TOKEN': token},
      method: 'POST',
      data: data,
      datatype: 'JSON',
      success: function (resp) {
        $("#reporting_level").html(resp);
        return false;
      }
    });
  });
});

$(document).ready(function(){
  $("#reporting_level").on('change',function(){
    var user_id = $('#userId').val();
    var reportinglevel = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      user_id: user_id,
      reportinglevel: reportinglevel,
    };
    $.ajax({
      url: "{{ url('admin/getreportingauthority') }}",
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

$(document).ready(function(){
  $("#authority").on('change',function(){
    var auth_id = $(this).val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('admin/getauthority') }}/"+ user_id + "/" + auth_id,
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
  $("#dealer_id").on('change',function(){
    var del_id = $(this).val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('admin/getdealerpermission') }}/"+ user_id + "/" + del_id,
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
    var del_id = $('#dealer_id').val();
    var user_id = $('#userId').val();
    $.ajax({
        url: "{{ url('admin/getreportingpermission') }}/"+ user_id + "/" + del_id + "/" + del_authid,
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
  $("#dealer_id").on('change',function(){
    var user_id = $('#userId').val();
    var del_id = $(this).val();
    token = $('input[name=_token]').val();
    data = {
      user_id: user_id,
      del_id: del_id,
    };
    $.ajax({
      url: "{{ url('admin/getdealerauthority') }}",
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

// if ($('#is_office').is(':checked')) {
//   $("#dealerofficeselector").show();
//   $(".dealer_office").attr("name","dealer_office");
// } else {
//   $("#dealerofficeselector").hide();
//   $(".dealer_office").removeAttr("name");
// }

// $(document).ready(function(){
//   $('#is_office').on('change', function() {
//     if ($('#is_office').is(':checked')) {
//       $("#dealerofficeselector").show();
//       $(".dealer_office").attr("name","dealer_office");
//     } else {
//       $("#dealerofficeselector").hide();
//       $(".dealer_office").removeAttr("name");
//     }
//   });
// });

$(document).ready(function(){
if($('#designation').val() == '14') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else if($('#designation').val() == '15') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else if($('#designation').val() == '16') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else if($('#designation').val() == '17') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else if($('#designation').val() == '19') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else if($('#designation').val() == '21') {
  $(".level").removeAttr("id");
  $("#authority").removeAttr("name");
  $("#dealerselecter").show();
  $("#dealer_authority").show();
  $("#officecheckbox").hide();
  $("#dealerofficeselector").hide();
  $(".dealer_office").removeAttr("name");
  $("#authoritySelector").hide();
  $("#reportingLevelSelector").hide();
}else {
  $(".level").attr("id","level");
  $("#authority").attr("name","authority");
  $("#dealerselecter").hide();
  $("#dealer_authority").hide();
  $("#officecheckbox").show();
  $("#dealerofficeselector").show();
  $(".dealer_office").attr("name","dealer_office");
  $("#authoritySelector").show();
  $("#reportingLevelSelector").show();
}
});


$(document).ready(function(){
  $('#designation').on('change', function() {
    if(this.value == '14') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#dealer_authority").show();
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else if(this.value == '15') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#dealer_authority").show();
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else if(this.value == '16') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#dealer_authority").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else if(this.value == '17') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#dealer_authority").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else if(this.value == '19') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#dealer_authority").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else if(this.value == '21') {
      $(".level").removeAttr("id");
      $("#authority").removeAttr("name");
      $("#dealerselecter").show();
      $("#dealer_authority").show();
      $("#officecheckbox").hide();
      $("#dealerofficeselector").hide();
      $(".dealer_office").removeAttr("name");
      $("#authoritySelector").hide();
      $("#reportingLevelSelector").hide();
    }else {
      $(".level").attr("id","level");
      $("#authority").attr("name","authority");
      $("#dealerselecter").hide();
      $("#dealer_authority").hide();
      $("#officecheckbox").show();
      $("#dealerofficeselector").show();
      $(".dealer_office").attr("name","dealer_office");
      $("#authoritySelector").show();
      $("#reportingLevelSelector").show();
    }
  });
});

function validateForm() {
  if ( $('#level').val() >= '2'){
    if ($('#authority').val() == '') {
      var w = document.forms["StaffForm"]["authority"].value;
      if (w == "" || w == null) {
        alert("Please Select reporting authority");
        return false;
      }
    } else {
      var x = document.forms["StaffForm"]["reporting_level"].value;
      if (x == "" || x == null) {
        alert("Please Select reporting level");
        return false;
      }
    }
  } else if ($('#designation').val() == '14' || $('#designation').val() == '15' || $('#designation').val() == '16' || $('#designation').val() == '17' || $('#designation').val() == '19' || $('#designation').val() == '21') {
    if ($('#dealer_id').val() == '') {
      var y = document.forms["StaffForm"]["dealer_id"].value;
      if (y == "" || y == null) {
        alert("Please Select Dealer");
        return false;
      }
    } else {
      var z = document.forms["StaffForm"]["dealer_authid"].value;
      if (z == "" || z == null) {
        alert("Please Select Dealer Authority");
        return false;
      }
    }
  }
  
  if ($('.designation').val() == '14' || $('.designation').val() == '15' || $('.designation').val() == '16' || $('.designation').val() == '17' || $('.designation').val() == '19' || $('.designation').val() == '21') {
  } else {
    if ($('.dealer_office').val() == '') {
        alert("Please Select Office");
        return false;
    }
  }
  // if ($('#is_office').is(':checked')) {
  //   if ($('#dealer_office').val() == '') {
  //     var w = document.forms["StaffForm"]["dealer_office"].value;
  //     if (w == "" || w == null) {
  //       alert("Please Select Office");
  //       return false;
  //     }
  //   }
  // }
}

setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 2000);
</script>    
@endsection