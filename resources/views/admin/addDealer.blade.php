@extends('layouts.dashboard')
<?php
$districts = array(); 
if(session()->has('district_id')){
  $district_id = session()->get('district_id');
}
if(@$district_id){
  $districts = DB::table('districts')->where('district_id', $district_id)->get();
}
?>
@section('content')
<link rel="stylesheet" href="{{asset('css/select2.min.css')}}">
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Dealers/Offices            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer/Office</a></li>
      <li class="active">Add Dealer/Office</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Dealer/Office</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="dealerform" method="POST" action="{{url('/admin/insertDealer')}}" enctype="multipart/form-data">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                      <label for="type">Type<span class="required-title">*</span></label>
                      <select class="form-control required" id="type" name="type">
                        <option value="">Select Type</option>
                        <option value="dealer" @if(old('type') == 'dealer') {{ 'selected' }} @endif>Dealer</option>
                        <option value="office" @if(old('type') == 'office') {{ 'selected' }} @endif>Office</option>
                      </select>
                      @if ($errors->has('type'))
                      <span class="help-block">
                        <strong>{{ $errors->first('type') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('firm_id') ? ' has-error' : '' }}">
                      <label for="Firm">Firm<span class="required-title">*</span></label>
                      <select class="form-control required" id="firm_id" name="firm_id">
                        <option value="">Select Firm</option>
                        @foreach($firms as $firm)
                        <option value="{{$firm->id}}" @if(old('firm_id') == $firm->id) {{ 'selected' }} @endif>{{ucwords($firm->firm_name)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('firm_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('firm_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('authority_id') ? ' has-error' : '' }}" id="authority">
                      <label for="authority_id">ASM/RSM/SSE<span class="required-title">*</span></label>
                      <select class="form-control required select2" id="authority_id" name="authority_id[]" multiple="">
                        {{-- <option value="">Select</option>
                        @foreach($authorities as $authority)
                        <option @if(old('authority_id') == $authority->uid) {{ 'selected' }} @endif value="{{$authority->uid}}">{{$authority->uname}} - {{get_designation_by_userid($authority->uid)}}</option>
                        @endforeach --}}
                      </select>
                      @if ($errors->has('authority_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('authority_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('center_code') ? ' has-error' : '' }}">
                      <label for="center_code">Center Code<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('center_code') }}" id="center_code" name="center_code" placeholder="Enter Center Code">
                      @if ($errors->has('center_code'))
                      <span class="help-block">
                        <strong>{{ $errors->first('center_code') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                      <label for="name">Name<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('name') }}" id="name" name="name" placeholder="Enter dealer name">
                      @if ($errors->has('name'))
                      <span class="help-block">
                        <strong>{{ $errors->first('name') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group{{ $errors->has('share_percentage') ? ' has-error' : '' }}" id="share_div">
                      <label for="share_percentage">Share Percentage<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('share_percentage') }}" id="share_percentage" name="share_percentage" placeholder="Enter share percentage" maxlength="2" OnKeypress="return isNumber(event)">
                      @if ($errors->has('share_percentage'))
                      <span class="help-block">
                        <strong>{{ $errors->first('share_percentage') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                      <label for="email">Email<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('email') }}" id="email" name="email" placeholder="Enter email">
                      @if ($errors->has('email'))
                      <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">
                      <label for="mobile_no">Mobile No.<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('mobile_no') }}" id="mobile_no" name="mobile_no" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">
                      @if ($errors->has('mobile_no'))
                      <span class="help-block">
                        <strong>{{ $errors->first('mobile_no') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                      <label for="address">Address<span class="required-title">*</span></label>
                      <textarea class="form-control required" id="address" name="address" placeholder="Enter address"></textarea>
                      @if ($errors->has('address'))
                      <span class="help-block">
                        <strong>{{ $errors->first('address') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <a href="#" class="btn btn-primary" id="latlong">Auto Populate Lat Long</a>
                  Or
                  <a href="https://www.latlong.net/convert-address-to-lat-long.html" class="btn btn-primary" target="_blank">Get Lat Long From Address</a>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('latitude') ? ' has-error' : '' }}">
                      <label for="latitude">Latitude<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('latitude') }}" id="latitude" name="latitude" placeholder="Enter Latitude">
                      @if ($errors->has('latitude'))
                      <span class="help-block">
                        <strong>{{ $errors->first('latitude') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('longitude') ? ' has-error' : '' }}">
                      <label for="longitude">Longitude<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('longitude') }}" id="longitude" name="longitude" placeholder="Enter Longitude">
                      @if ($errors->has('longitude'))
                      <span class="help-block">
                        <strong>{{ $errors->first('longitude') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('state_id') ? ' has-error' : '' }}">
                      <label for="state_id">State<span class="required-title">*</span></label>
                      <select class="form-control required" id="state_id" name="state_id">
                        <option value="">Select State</option>
                        @foreach($states as $state)
                        <option @if(old('state_id') == $state->s_id) {{ 'selected' }} @endif value="{{$state->s_id}}">{{$state->state_name}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('state_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('state_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('district_id') ? ' has-error' : '' }}">
                      <label for="district_id">District<span class="required-title">*</span></label>
                      <select class="form-control required" id="district_id" name="district_id">
                        <option value="">Select District</option>
                        @foreach($districts as $district)
                        <option @if(old('district_id') == $district->district_id) {{ 'selected' }} @endif value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('district_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('district_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                      <label for="city">City<span class="required-title">*</span></label>
                      <input type="text" class="form-control required" value="{{ old('city') }}" id="city" name="city" placeholder="Enter city">
                      @if ($errors->has('city'))
                      <span class="help-block">
                        <strong>{{ $errors->first('city') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}" id="group_id">
                      <label for="group">Group</label>
                      <select class="form-control required" id="group_id" name="group">
                        <option value="">Select Group</option>
                        @foreach($grouplist as $grouplist)
                        <option @if(old('group') == $grouplist->id) {{ 'selected' }} @endif value="{{$grouplist->id}}">{{$grouplist->group_name}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('group'))
                      <span class="help-block">
                        <strong>{{ $errors->first('group') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('OEM') ? ' has-error' : '' }}" id="oem_id">
                      <label for="OEM">OEM<span class="required-title">*</span></label>
                      <select class="form-control required" id="oem_id" name="OEM">
                        <option value="">Select OEM</option>
                        @foreach($oemlist as $oemlist)
                        <option @if(old('OEM') == $oemlist->id) {{ 'selected' }} @endif value="{{$oemlist->id}}">{{$oemlist->oem}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('OEM'))
                      <span class="help-block">
                        <strong>{{ $errors->first('OEM') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }}" id="start_time">
                      <label for="start_time">Start Time</label>
                      <input type="time" name="start_time" class="form-control">
                      @if ($errors->has('start_time'))
                      <span class="help-block">
                        <strong>{{ $errors->first('start_time') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('end_time') ? ' has-error' : '' }}" id="end_time">
                      <label for="end_time">End Time</label>
                      <input type="time" name="end_time" class="form-control">
                      @if ($errors->has('end_time'))
                      <span class="help-block">
                        <strong>{{ $errors->first('end_time') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>

                <table id="contact" class=" table contact-list">
                  <thead>
                    <tr>
                      <td><b>Name</b></td>
                      <td><b>Email</b></td>
                      <td><b>Phone</b></td>
                      <td><b>Designation</b></td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="col-sm-3">
                        <input type="text" name="cname[]" class="form-control" placeholder="Enter Contact Person's Name" />
                      </td>
                      <td class="col-sm-3">
                        <input type="mail" name="cemail[]" value="" class="form-control" placeholder="Enter Contact Person's Email"/>
                      </td>
                      <td class="col-sm-2">
                        <input type="text" name="cmobile[]" class="form-control" placeholder="Enter Contact's Number" maxlength="10" OnKeypress="return isNumber(event)"/>
                      </td>
                      <td class="col-sm-3">
                        <input type="text" name="cdesignation[]" class="form-control" placeholder="Enter Contact Person's Designation"/>
                      </td>
                      <td class="col-sm-1"><a class="deleteRow"></a>
                        <input type="button" class="btn btn-md btn-block btn-success" id="addrow" value="Add" />
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                      <!-- <tr>
                        <td colspan="5" style="text-align: left;">
                          <input type="button" class="btn btn-lg btn-block btn-success" id="addrow" value="Add More Contact Person" />
                        </td>
                      </tr> -->
                    </tfoot>
                  </table>
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
  <div class="loading">Loading&#8230;</div>
<script src="{{asset('js/select2.full.min.js')}}"></script>
<style type="text/css">
/* Absolute Center Spinner */
.loading {
display: none;
position: fixed;
z-index: 999;
height: 2em;
width: 2em;
overflow: visible;
margin: auto;
top: 0;
left: 0;
bottom: 0;
right: 0;
}

/* Transparent Overlay */
.loading:before {
content: '';
display: block;
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background-color: rgba(0,0,0,0.3);
}

/* :not(:required) hides these rules from IE9 and below */
.loading:not(:required) {
/* hide "loading..." text */
font: 0/0 a;
color: transparent;
text-shadow: none;
background-color: transparent;
border: 0;
}

.loading:not(:required):after {
content: '';
display: block;
font-size: 10px;
width: 1em;
height: 1em;
margin-top: -0.5em;
-webkit-animation: spinner 1500ms infinite linear;
-moz-animation: spinner 1500ms infinite linear;
-ms-animation: spinner 1500ms infinite linear;
-o-animation: spinner 1500ms infinite linear;
animation: spinner 1500ms infinite linear;
border-radius: 0.5em;
-webkit-box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.5) -1.5em 0 0 0, rgba(0, 0, 0, 0.5) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
}

/* Animation */

@-webkit-keyframes spinner {
0% {
-webkit-transform: rotate(0deg);
-moz-transform: rotate(0deg);
-ms-transform: rotate(0deg);
-o-transform: rotate(0deg);
transform: rotate(0deg);
}
100% {
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-ms-transform: rotate(360deg);
-o-transform: rotate(360deg);
transform: rotate(360deg);
}
}
@-moz-keyframes spinner {
0% {
-webkit-transform: rotate(0deg);
-moz-transform: rotate(0deg);
-ms-transform: rotate(0deg);
-o-transform: rotate(0deg);
transform: rotate(0deg);
}
100% {
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-ms-transform: rotate(360deg);
-o-transform: rotate(360deg);
transform: rotate(360deg);
}
}
@-o-keyframes spinner {
0% {
-webkit-transform: rotate(0deg);
-moz-transform: rotate(0deg);
-ms-transform: rotate(0deg);
-o-transform: rotate(0deg);
transform: rotate(0deg);
}
100% {
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-ms-transform: rotate(360deg);
-o-transform: rotate(360deg);
transform: rotate(360deg);
}
}
@keyframes spinner {
0% {
-webkit-transform: rotate(0deg);
-moz-transform: rotate(0deg);
-ms-transform: rotate(0deg);
-o-transform: rotate(0deg);
transform: rotate(0deg);
}
100% {
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-ms-transform: rotate(360deg);
-o-transform: rotate(360deg);
transform: rotate(360deg);
}
}
</style>
<script type="text/javascript">
$(".select2").select2();

if ($('#type').val() == 'office') {
  $("#authority").hide();
  $("#group_id").hide();
  $("#oem_id").hide();
  $("#share_div").hide();
} else {
  $("#authority").show();
  $("#group_id").show();
  $("#oem_id").show();
  $("#share_div").show();
}
$(document).ready(function(){
  $('#type').on('change', function() {
    if (this.value == 'office') {
      $("#authority").hide();
      $("#group_id").hide();
      $("#oem_id").hide();
      $("#share_div").hide();
    } else {
      $("#authority").show();
      $("#group_id").show();
      $("#oem_id").show();
      $("#share_div").show();
    }
  });
});
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

$('#firm_id').on("change",function(e) {
  var firm_id = $("#firm_id").val();
  token = $('input[name=_token]').val();
  data = {
    firm_id: firm_id,
  };
  $.ajax({
    url: "{{ url('admin/getAuthorities') }}",
    headers: {'X-CSRF-TOKEN': token},
    data: data,
    type: 'POST',
    datatype: 'JSON',
    success: function (resp) {
      $("#authority_id").html(resp);
        return false;
    }
  });
  return false;
});

var state = $("#state_id").val();
token = $('input[name=_token]').val();
url = '<?php echo url("/"); ?>/getDistrict';
data = {
  state: state,
};
$.ajax({
  url: url,
  headers: {'X-CSRF-TOKEN': token},
  data: data,
  type: 'POST',
  datatype: 'JSON',
  success: function (resp) {
    $("#district_id").html(resp);
    return false;
  }
});
$('#state_id').on("change",function(e) {
  var state = $("#state_id").val();
  token = $('input[name=_token]').val();
  url = '<?php echo url("/"); ?>/getDistrict';
  data = {
    state: state,
  };
  $.ajax({
    url: url,
    headers: {'X-CSRF-TOKEN': token},
    data: data,
    type: 'POST',
    datatype: 'JSON',
    success: function (resp) {
      $("#district_id").html(resp);
      return false;
    }
  });
  return false;
});
</script>
<!-- Add multiple rows Script -->
<script> 
  $(document).ready(function () {
    var newRow = '<tr><td class="col-sm-3"><input type="text" name="cname[]" class="form-control" placeholder="Enter Contact Person Name" /></td><td class="col-sm-3"><input type="mail" name="cemail[]" class="form-control" placeholder="Enter Contact Person Email" value=""/></td><td class="col-sm-2"><input type="text" name="cmobile[]" class="form-control" placeholder="Enter Contact Number" maxlength="10" OnKeypress="return isNumber(event)"/></td><td class="col-sm-3"><input type="text" name="cdesignation[]" class="form-control" placeholder="Enter Contact Person Designation"/></td><td><input type="button" class="ibtnDel btn btn-md btn-danger" style="width:85.6px" id="delete" value="Delete"></td></tr>';
    $('#addrow').click(function() {
      $("#contact").append(newRow);
    });
    $("#contact").on("click", "#delete", function (event) {
      $(this).closest("tr").remove();
    });
  });
</script> 
<script type="text/javascript">  
  $('#latlong').click(function(e) {
    $('.loading').show();
    var address = $("#address").val();
    token = $('input[name=_token]').val();
    url = '<?php echo url("/"); ?>admin/getlatlong';
    data = {
      address: address,
    };
    $.ajax({
      url: url,
      headers: {'X-CSRF-TOKEN': token},
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function (resp) {
        console.log(resp);
        var obj = JSON.parse(resp);
        error = obj.code;
        if (error == 200) {

          $('#latitude').val(obj.result.lat);            
          $('#longitude').val(obj.result.long);
          $('.loading').hide();
        }else{
          alert(obj.result);
          $('.loading').hide();
        }

        return false;
      }
    });

    return false;
  });
</script>
@endsection