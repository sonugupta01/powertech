@extends('layouts.dashboard')
<?php
  $models = array();
  $advisors=array() ;
  $treatments=array() ;
  if(session()->has('model_id')){
    $model_id = session()->get('model_id');
  }
  if(@$model_id){
    $models = DB::table('models')->where('id', $model_id)->get();
  }
  if(session()->has('advisor_id')){
    $advisor_id = session()->get('advisor_id');
  }
  if(@$advisor_id){
    $advisors = DB::table('advisors')->where('id', $advisor_id)->get();
  }
?>
@section('content')
<link rel="stylesheet" href="{{asset('css/select2.min.css')}}">
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Jobs            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/sse')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/sse/jobs')}}"><i class="fa fa-tasks"></i> Jobs</a></li>
      <li class="active">Add Job</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Job</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
           @if(Session::has('addComErrmsg'))
            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">
               <?php $msg=Session::get('addComErrmsg');
               foreach ($msg as $key => $value) {
                echo $value;?>
                <br>
               <?php }
               ?>
              </p>
            <?php Session::forget('addComErrmsg'); ?>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="jobform" method="POST" action="{{url('/sse/insertJob')}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('job_date') ? ' has-error' : '' }}">
                        <label for="job_date">Job Date<span class="required-title">*</span></label>
                          <div class="input-group date">
                          <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                          </div>
                          <input onkeydown="return false" autocomplete="off" class="form-control pull-right" id="datepicker" value="{{ old('job_date') }}" type="text" name="job_date">
                        </div>
                        @if ($errors->has('job_date'))
                          <span class="help-block">
                            <strong>{{ $errors->first('job_date') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('job_card_no') ? ' has-error' : '' }}">
                        <label for="job_card_no">Job Card No.<span class="required-title">*</span></label>
                        <input type="text" name="job_card_no" class="form-control required" value="{{ old('job_card_no') }}" id="job_card_no" placeholder="Enter job card no.">
                        @if ($errors->has('job_card_no'))
                          <span class="help-block">
                            <strong>{{ $errors->first('job_card_no') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('bill_no') ? ' has-error' : '' }}">
                        <label for="bill_no">Bill No.<span class="required-title">*</span></label>
                        <input type="text" name="bill_no" class="form-control required" value="{{ old('bill_no') }}" id="bill_no" placeholder="Enter bill no">
                        @if ($errors->has('bill_no'))
                          <span class="help-block">
                            <strong>{{ $errors->first('bill_no') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('regn_no') ? ' has-error' : '' }}">
                        <label for="regn_no">Regn No.<span class="required-title">*</span></label>
                        <input type="text" name="regn_no" class="form-control required" value="{{ old('regn_no') }}" id="regn_no" placeholder="Enter regn no">
                        @if ($errors->has('regn_no'))
                          <span class="help-block">
                            <strong>{{ $errors->first('regn_no') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                        <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                        <select class="form-control required" id="dealer_id" name="dealer_id">
                          <option value="">Select Dealer</option>
                          @foreach($dealers as $dealer)
                            <option value="{{$dealer}}">{{get_dealer_name($dealer)}}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('dealer_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('dealer_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group{{ $errors->has('model_id') ? ' has-error' : '' }}">
                        <label for="model_id">Model<span class="required-title">*</span></label>
                        <select class="form-control required" id="model_id" name="model_id">
                          <option value="">Select Model</option>
                          @foreach($models as $model)
                            <option @if(old('model_id') == $model->id) {{ 'selected' }} @endif value="{{ $model->id }}">{{ $model->model_name }}</option>
                          @endforeach
                        </select>
                        @if ($errors->has('model_id'))
                          <span class="help-block">
                            <strong>{{ $errors->first('model_id') }}</strong>
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="form-group{{ $errors->has('advisor_id') ? ' has-error' : '' }}">
                    <label for="advisor_id">Advisor<span class="required-title">*</span></label>
                    <select class="form-control required" id="advisor_id" name="advisor_id">
                      <option value="">Select Advisor</option>
                      @foreach($advisors as $advisor)
                        <option @if(old('advisor_id') == $advisor->id) {{ 'selected' }} @endif value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('advisor_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('advisor_id') }}</strong>
                      </span>
                    @endif
                  </div>
                </div>
                <!-- <div class="form-group{{ $errors->has('treatment_id') ? ' has-error' : '' }}">
                  <label for="treatment_id">Treatment<span class="required-title">*</span></label>
                  <select class="form-control required select2" id="treatment_id" name="treatment_id[]" multiple="">
                    <option value="">Select Treatments</option>
                    @foreach($treatments as $treatment)
                      <option @if(old('treatment_id') == $treatment->id) {{ 'selected' }} @endif value="{{ $treatment->id }}">{{ $treatment->treatment }}</option>
                    @endforeach
                  </select>
                  @if ($errors->has('treatment_id'))
                    <span class="help-block">
                      <strong>{{ $errors->first('treatment_id') }}</strong>
                    </span>
                  @endif
                </div> -->
                <table id="myTable" class=" table order-list">
                  <thead>
                      <tr>
                          <td>Treatment</td>
                          <td>Job Type</td>
                          <td>Customer Price</td>
                          <td>Actual Price</td>
                          <td>Difference (+/-)</td>
                          {{-- <td>Dealer Price</td>
                          <td>Incentive</td> --}}
                      </tr>
                  </thead>
                  <tbody>
                      <tr id="selectData">
                          <td class="col-sm-4">
                            <select class="form-control" id="treatment_id0" name="treatment_id[]" onchange="getprice(0)" required>
                              <option value="">Select Treatment</option>
                            </select>
                          </td>
                          <td class="col-sm-2">
                            <select class="form-control" id="job_type0" name="job_type[]">
                              <option value="5" selected>Paid</option>
                              <option value="1">Free of Cost</option>
                              <option value="2">Demo</option>
                              <option value="3">Recheck</option>
                              <option value="4">Repeat Work</option>
                            </select>
                          </td>
                          <td class="col-sm-2">
                              <input type="text" value="" class="form-control customer0" name="customer[]" id="customer0" readonly/>
                          </td>
                          <td class="col-sm-2">
                              <input type="text" value="0" class="form-control actualPrice0" name="actualPrice[]" OnKeypress="return isNumber(event)" onkeyup="getdifference(0)" required/>
                          </td>
                          <td class="col-sm-2">
                              <input type="text" value="" class="form-control difference0" name="difference[]" readonly/>
                          </td>
                          {{-- <td class="col-sm-3">
                              <input type="text" name="dealer[]" value="" class="form-control" required/>
                          </td>
                          <td class="col-sm-3">
                              <input type="text" name="incentive[]" value="" class="form-control" required/>
                          </td> --}}
                          {{-- <td class="col-sm-2">
                              <input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete">
                          </td> --}}
                      </tr>
                  </tbody>
                  <tfoot>
                      <tr>
                          <td colspan="6" style="text-align: left;">
                              <input type="button" class="btn btn-success btn-lg btn-block " id="addrow" value="Add Row" />
                          </td>
                      </tr>
                      <tr>
                      </tr>
                  </tfoot>
                </table>
                <div class="form-group">
                  <label for="remark">Remark</label>
                  <textarea type="text" name="remark" class="form-control required" id="remark" placeholder="Enter remark">{{ old('remark') }}</textarea>
                  @if ($errors->has('remark'))
                    <span class="help-block">
                      <strong>{{ $errors->first('remark') }}</strong>
                    </span>
                  @endif
                </div>
                  <!-- <div class="form-group">
                    <label for="foc">Free of Cost</label>
                    <input type="checkbox" name="foc" value="1">
                  </div> -->
                  <!-- <div class="form-group">
                    <label class="radio-inline" for="paid">
                      <input type="radio" name="option" value="5" checked>Paid
                    </label>
                    <label class="radio-inline" for="foc">
                      <input type="radio" name="option" value="1">Free of Cost
                    </label>
                    <label class="radio-inline" for="demo">
                      <input type="radio" name="option" value="2">Demo
                    </label>
                    <label class="radio-inline" for="recheck">
                      <input type="radio" name="option" value="3">Recheck
                    </label>
                    <label class="radio-inline" for="repeat">
                      <input type="radio" name="option" value="4">Repeat Work
                    </label>
                  </div> -->
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
<script src="{{asset('js/select2.full.min.js')}}"></script>
<script src="{{asset('js/bootstrap-datepicker.js')}}"></script>
<script type="text/javascript"> 
$(".select2").select2();
$('#datepicker').datepicker({ autoclose: true, startDate: '-0d', format: 'yyyy-mm-dd', endDate: '+0d' });

$(document).ready(function () {
  var counter = 1;
  $("#addrow").on("click", function () {
    var newRow = $("<tr>");
    var cols = "";
    var selected=$('#myTable').find('tbody select:first').clone();
    $(selected).find('option').removeAttr('selected');
    cols+= '<td><select id="treatment_id'+counter+'" name="treatment_id[]" class="form-control" onchange="getprice('+counter+')" required>'+$(selected).html()+'</select></td>';
    cols+="<td><select class='form-control' id='job_type"+counter+"' onchange='disable_func("+counter+")' name='job_type[]'><option value='5' selected>Paid</option><option value='1'>Free of Cost</option><option value='2'>Demo</option><option value='3'>Recheck</option><option value='4'>Repeat Work</option></select></td>";
    cols += '<td><input type="text" value="" class="form-control customer'+counter+'" name="customer[]" id="customer'+counter+'" readonly/></td>';
    cols += '<td><input type="text" value="0" class="form-control actualPrice'+counter+'" name="actualPrice[]" OnKeypress="return isNumber(event)" onkeyup="getdifference('+counter+')" required/></td>';
    cols += '<td><input type="text" value="" class="form-control difference'+counter+'" name="difference[]" readonly/></td>';
    // cols += '<td><input type="text" value="" class="form-control" name="dealer[]" required/></td>';
    // cols += '<td><input type="text" value="" class="form-control" name="incentive[]" required/></td>';
    cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
    newRow.append(cols);
    // console.log(selected);
    $("table.order-list").append(newRow);
    counter++;
  });
  $("table.order-list").on("click", ".ibtnDel", function (event) {
      $(this).closest("tr").remove();       
      counter -= 1
  });
});

$('#job_type0').on("change",function(e) {
  var job_type = $(this).val();
  if ( this.value == 1 ) {
    $(".actualPrice0").val("0").attr("disabled", "disabled");
    $(".difference0").val("0");
  } else if ( this.value == 2 ) {
    $(".actualPrice0").val("0").attr("disabled", "disabled");
    $(".difference0").val("0");
  } else if ( this.value == 3 ) {
    $(".actualPrice0").val("0").attr("disabled", "disabled");
    $(".difference0").val("0");
  } else if ( this.value == 4 ) {
    $(".actualPrice0").val("0").attr("disabled", "disabled");
    $(".difference0").val("0");
  } else {
    $(".actualPrice0").removeAttr("disabled");
    $(".difference0").val();
  }
});

function disable_func(counter){
  // $('#job_type'+counter).on("change",function(e) {
    var job_type = $('#job_type'+counter).val();
    if ( job_type == 1 ) {
      $(".actualPrice"+counter).val("0").attr("disabled", "disabled");
      $(".difference"+counter).val("0");
    } else if ( job_type == 2 ) {
      $(".actualPrice"+counter).val("0").attr("disabled", "disabled");
      $(".difference"+counter).val("0");
    } else if ( job_type == 3 ) {
      $(".actualPrice"+counter).val("0").attr("disabled", "disabled");
      $(".difference"+counter).val("0");
    } else if ( job_type == 4 ) {
      $(".actualPrice"+counter).val("0").attr("disabled", "disabled");
      $(".difference"+counter).val("0");
    } else {
      $(".actualPrice"+counter).removeAttr("disabled");
      $(".difference"+counter).val();
    }
  // });
}

$('#dealer_id').on("change",function(e) {
  var dealer = $("#dealer_id").val();
  token = $('input[name=_token]').val();
  url1 = '<?php echo url("/"); ?>/getModels';
  url2 = '<?php echo url("/"); ?>/getAdvisors';
      data = {
        dealer: dealer,
      };
      $.ajax({
          url: url1,
          headers: {'X-CSRF-TOKEN': token},
          data: data,
          type: 'POST',
          datatype: 'JSON',
          success: function (resp) {
            $("#model_id").html(resp);
            return false;
          }
      });
      $.ajax({
          url: url2,
          headers: {'X-CSRF-TOKEN': token},
          data: data,
          type: 'POST',
          datatype: 'JSON',
          success: function (resp) {
            $("#advisor_id").html(resp);
            return false;
          }
      });
      return false;
});

$('#model_id').on("change",function(e) {
  var dealer = $("#dealer_id").val();
  var model = $("#model_id").val();
  token = $('input[name=_token]').val();
  url = '<?php echo url("/"); ?>/getTreatments';
      data = {
        dealer: dealer,
        model: model,
      };
      $.ajax({
          url: url,
          headers: {'X-CSRF-TOKEN': token},
          data: data,
          type: 'POST',
          datatype: 'JSON',
          success: function (resp) {
            $("#treatment_id0").html(resp);
            return false;
          }
      });
      return false;
});

function getprice(i){
  var id = "#treatment_id"+i;
  var treatment_id = $(id).val();
  token = $('input[name=_token]').val();
  url = '<?php echo url("/"); ?>/getTreatmentPrice';
  data = {
    treatment_id: treatment_id,
  };
  $.ajax({
      url: url,
      headers: {'X-CSRF-TOKEN': token},
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function (resp) {
        $('#customer'+i).val(resp);
        return false;
      }
  });
  return false;
}

function getdifference(a){
  var customer = ".customer"+a;
  var actualclass = ".actualPrice"+a;
  var diff = ".difference"+a;
  var actualPrice = $(actualclass).val();
  var customerPrice = $(customer).val();
  var difference = parseInt(actualPrice,10) - parseFloat(customerPrice);
  $(diff).val(isNaN(difference)? 0 : difference);
}
</script>    
@endsection