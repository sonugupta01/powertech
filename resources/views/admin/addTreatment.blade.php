@extends('layouts.dashboard')
<?php
  // $models = array(); 
  // if(session()->has('model_id')){
  //   $model_id = session()->get('model_id');
  // }
  // if(@$model_id){
  //   $models = DB::table('models')->where('id', $model_id)->get();
  // }
?>
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatments            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/treatments')}}"><i class="fa fa-wrench"></i> Treatments</a></li>
      <li class="active">Add Treatment</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Treatment</h3>
          </div><!-- /.box-header -->
          <div class="box-body">

            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif

            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/insertTreatment')}}" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('labour_code') ? ' has-error' : '' }}">
                        <label for="labour_code">Labour Code</label>
                        <input type="text" class="form-control required" value="{{ old('labour_code') }}" id="labour_code" name="labour_code" placeholder="Enter labour code">
                        @if ($errors->has('labour_code'))
                          <span class="help-block">
                            <strong>{{ $errors->first('labour_code') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('oem_id') ? ' has-error' : '' }}">
                        <label for="oem_id">OEM<span class="required-title">*</span></label>
                        <select class="form-control required" id="oem_id" name="oem_id">
                          <option value="">Select OEM</option>
                          @foreach($oemlist as $oem)
                            <option value="{{ $oem->id }}">{{ $oem->oem }}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('oem_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('oem_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('tempId') ? ' has-error' : '' }}">
                        <label for="tempId">Treatment Template<span class="required-title">*</span></label>
                        <select class="form-control required" id="tempId" name="tempId">
                          <option value="">Select template</option>
                          <!-- @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->temp_name }}</option>
                          @endforeach -->
                        </select>
                        @if ($errors->has('tempId'))
                          <span class="help-block">
                            <strong>{{ $errors->first('tempId') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('model_id') ? ' has-error' : '' }}">
                        <label for="model_id">Model<span class="required-title">*</span></label>
                        <select class="form-control required" id="model_id" name="model_id">
                          <option value="">Select Model</option>
                        </select>
                        @if ($errors->has('model_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('model_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('treatment') ? ' has-error' : '' }}">
                        <label for="treatment">Treatment<span class="required-title">*</span></label>
                        <input type="text" class="form-control required" value="{{ old('treatment') }}" id="treatment" name="treatment" placeholder="Enter treatment">
                        @if ($errors->has('treatment'))
                          <span class="help-block">
                            <strong>{{ $errors->first('treatment') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('treatment_type') ? ' has-error' : '' }}">
                        <label for="treatment_type">Type of Treatment<span class="required-title">*</span></label>
                        <div class="form-control required">
                          <input type="radio" value="0" name="treatment_type" checked> Normal
                          <input type="radio" value="1" name="treatment_type"> Heavy
                        </div>                     
                        @if ($errors->has('treatment_type'))
                          <span class="help-block">
                            <strong>{{ $errors->first('treatment_type') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group{{ $errors->has('treatment_option') ? ' has-error' : '' }}">
                        <label for="treatment_option">Treatment Option</label>
                        <select class="form-control required" id="treatment_option" name="treatment_option">
                          <option value="">Select Option</option>
                          <!-- <option value="5">Paid</option> -->
                          <!-- <option value="1">Free of Cost</option>
                          <option value="2">Demo</option> -->
                          <option value="3">Recheck</option>
                          <option value="4">Repeat Work</option>
                        </select>
                        @if ($errors->has('treatment_option'))
                          <span class="help-block">
                            <strong>{{ $errors->first('treatment_option') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-6" id="timePeriodSelector" style="display: none;">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group{{ $errors->has('time_period') ? ' has-error' : '' }}">
                            <label for="time_period">Time Period<span class="required-title">*</span></label>
                            <input type="text" class="form-control required" value="{{ old('time_period') }}" id="time_period" name="time_period" placeholder="Enter time period" OnKeypress="return isNumber(event)" maxlength="3">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group" style="margin-top:25px">
                            <select class="form-control required" id="time_period_unit" name="time_period_unit">
                              <option value="">Select</option>
                              <option value="1">Year</option>
                              <option value="2">Month</option>
                              <option value="3">Days</option>
                            </select>
                          </div>
                        </div>
                      </div>
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

$(document).ready(function(){
  $('#treatment_option').on('change', function() {
    if (this.value == '') {
      $("#timePeriodSelector").hide();
    } else {
      $("#timePeriodSelector").show();
    }
  });
});

function validateForm() {
  if ($('#treatment_option').val() != '') {
    if ($('#time_period').val() == '' || $('#time_period_unit').val() == '') {
        alert("Please insert time period");
        return false;
    }
  }
}


$('#oem_id').on("change",function(e) {
  var oem_id = $("#oem_id").val();
  token = $('input[name=_token]').val();
  url = '<?php echo url("/"); ?>/getOEMtemplates';
  data = {
    oem_id: oem_id,
  };
  $.ajax({
    url: url,
    headers: {'X-CSRF-TOKEN': token},
    data: data,
    type: 'POST',
    datatype: 'JSON',
    success: function (resp) {
      $("#tempId").html(resp);
      return false;
    }
  });
  return false;
});

// $('#oem_id').on("change",function(e) {
$('#tempId').on("change",function(e) {
  var oem_id = $("#oem_id").val();
  var template_id = $("#tempId").val();
  token = $('input[name=_token]').val();
  url = '<?php echo url("/"); ?>/getOemModels';
  data = {
    oem_id: oem_id,
    template_id: template_id,
  };
  $.ajax({
    url: url,
    headers: {'X-CSRF-TOKEN': token},
    data: data,
    type: 'POST',
    datatype: 'JSON',
    success: function (resp) {
      $("#model_id").html(resp);
      return false;
    }
  });
  return false;
});
  // $('#dealer_id').on("change",function(e) {
  //   var dealer = $("#dealer_id").val();
  //   token = $('input[name=_token]').val();
  //   url = '<?php //echo url("/"); ?>/getModels';
  //       data = {
  //         dealer: dealer,
  //       };
  //       $.ajax({
  //           url: url,
  //           headers: {'X-CSRF-TOKEN': token},
  //           data: data,
  //           type: 'POST',
  //           datatype: 'JSON',
  //           success: function (resp) {
  //             $("#model_id").html(resp);
  //             return false;
  //           }
  //       });
  //       return false;
  // });
</script>   
@endsection