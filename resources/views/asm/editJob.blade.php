@extends('layouts.dashboard')
<?php
if (session()->has('model_id')) {
  $model_id = session()->get('model_id');
}
if (@$model_id) {
  $models = DB::table('models')->where('id', $model_id)->get();
}
if (session()->has('advisor_id')) {
  $advisor_id = session()->get('advisor_id');
}
if (@$advisor_id) {
  $advisors = DB::table('advisors')->where('id', $advisor_id)->get();
}
?>
@section('content')
<link rel="stylesheet" href="{{asset('css/select2.min.css')}}">
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Jobs
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/asm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/asm/jobs')}}"><i class="fa fa-tasks"></i> Jobs</a></li>
      <li class="active">Edit Job</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Job</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('addComErrmsg'))
            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">
              <?php $msg = Session::get('addComErrmsg');
              foreach ($msg as $key => $value) {
                echo $value; ?>
                <br>
              <?php }
              ?>
            </p>
            <?php Session::forget('addComErrmsg'); ?>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="jobform" method="POST" action="{{url('/asm/updateJob')}}" enctype="multipart/form-data" onsubmit="return validateForm()">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="id" value="{{$result->id}}">
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group{{ $errors->has('job_date') ? ' has-error' : '' }}">
                      <label for="job_date">Job Date<span class="required-title">*</span></label>
                      <div class="input-group date">
                        <div class="input-group-addon">
                          <i class="fa fa-calendar"></i>
                        </div>
                        <input onkeydown="return false" autocomplete="off" class="form-control pull-right" id="datepicker" value="{{ old('job_date',$result->job_date) }}" type="text" name="job_date">
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
                      <input type="text" name="job_card_no" class="form-control required" value="{{ old('job_card_no',$result->job_card_no) }}" id="job_card_no" placeholder="Enter job card no.">
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
                      <input type="text" name="bill_no" class="form-control required" value="{{ old('bill_no',$result->bill_no) }}" id="bill_no" placeholder="Enter bill no">
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
                      <input type="text" name="regn_no" class="form-control required" value="{{ old('regn_no',$result->regn_no) }}" id="regn_no" placeholder="Enter regn no">
                      @if ($errors->has('regn_no'))
                      <span class="help-block">
                        <strong>{{ $errors->first('regn_no') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                      <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                      <select class="form-control required" id="dealer_id" name="dealer_id">
                        <option value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                        <option @if($result->dealer_id == $dealer) {{ 'selected' }} @endif value="{{$dealer}}">{{get_dealer_name($dealer)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('dealer_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('dealer_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group{{ $errors->has('user_id') ? ' has-error' : '' }}">
                      <label for="user_id">User<span class="required-title">*</span></label>
                      <select class="form-control required" id="user_id" name="user_id">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                        <option value="{{$user->id}}" @if($result->user_id == $user->id) {{ 'selected' }} @endif>{{$user->name}} - {{get_designation_by_userid($user->id)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('user_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('user_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group{{ $errors->has('model_id') ? ' has-error' : '' }}">
                      <label for="model_id">Model<span class="required-title">*</span></label>
                      <select class="form-control required" id="model_id" name="model_id">
                        <option value="">Select Model</option>
                        @foreach($result_models as $model)
                        <option @if($result->model_id == $model->id) {{ 'selected' }} @endif value="{{ $model->id }}">{{ $model->model_name }}</option>
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
                    <option @if($result->advisor_id == $advisor->id) {{ 'selected' }} @endif value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                    @endforeach
                  </select>
                  @if ($errors->has('advisor_id'))
                  <span class="help-block">
                    <strong>{{ $errors->first('advisor_id') }}</strong>
                  </span>
                  @endif
                </div>
                <table id="myTable" class=" table order-list table-responsive">
                  <thead>
                    <tr>
                      <td>Treatment</td>
                      <td>Job Type</td>
                      <td>Customer Price</td>
                      <td>Discount</td>
                      <td>Actual Price</td>
                      {{-- <td>Dealer Price</td>
                        <td>Incentive</td> --}}
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($selectTreatment as $key => $value)
                    <tr id="selectData">
                      <td class="col-sm-4">
                        <select class="form-control" id="treatment_id{{$key}}" name="treatment_id[]" onchange="getAllPrice(this)" required>
                          <option value="">Select Treatment</option>
                          @foreach($treatments as $treatment)
                          <option @if($value['id']==$treatment->id) {{ 'selected' }} @endif value="{{ $treatment->id }}">{{ $treatment->treatment }}</option>
                          @endforeach
                        </select>
                      </td>
                      <td class="col-sm-2">
                        <select class="form-control" id="job_type{{$key}}" name="job_type[]">
                          <option value="5" @if($value['job_type']==5) {{ 'selected' }} @endif>Paid</option>
                          <option value="1" @if($value['job_type']==1) {{ 'selected' }} @endif>Free of Cost</option>
                          <option value="2" @if($value['job_type']==2) {{ 'selected' }} @endif>Demo</option>
                          <option value="3" @if($value['job_type']==3) {{ 'selected' }} @endif>Recheck</option>
                          <option value="4" @if($value['job_type']==4) {{ 'selected' }} @endif>Repeat Work</option>
                        </select>
                      </td>
                      <td class="col-sm-2">
                        <input type="text" value="{{$value['customer_price']}}" class="form-control customer{{$key}}" name="customer[]" id="customer{{$key}}" readonly />
                      </td>
                      <td class="col-sm-1">
                        <input type="text" value="{{$value['difference']}}" class="form-control difference{{$key}}" name="difference[]" maxlength="2" OnKeypress="return isNumber(event)" onkeyup="getdifference({{$key}})" required />
                      </td>
                      <td class="col-sm-2">
                        <input type="text" value="{{$value['actualPrice']}}" class="form-control actualPrice{{$key}}" name="actualPrice[]" readonly />
                      </td>
                      {{-- <input type="hidden" name="dealer[]" value="{{$value['dealer_price']}}" class="form-control" required />
                      <input type="hidden" name="incentive[]" value="{{$value['incentive']}}" class="form-control" required /> --}}
                      <td class="col-sm-2">
                        <input type="button" class="ibtnDel btn btn-md btn-danger " value="Delete">
                      </td>
                    </tr>
                    <script>
                      if ($('#job_type{{$key}}').val() == 1) {
                        $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                        $(".actualPrice{{$key}}").val("0");
                      } else if ($('#job_type{{$key}}').val() == 2) {
                        $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                        $(".actualPrice{{$key}}").val("0");
                      } else if ($('#job_type{{$key}}').val() == 3) {
                        $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                        $(".actualPrice{{$key}}").val("0");
                      } else if ($('#job_type{{$key}}').val() == 4) {
                        $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                        $(".actualPrice{{$key}}").val("0");
                      } else {
                        $(".difference{{$key}}").removeAttr("disabled");
                        $(".actualPrice{{$key}}").val();
                      }
                      $(document).ready(function() {
                        $('#job_type{{$key}}').on("change", function(e) {
                          var job_type = $(this).val();
                          if (this.value == 1) {
                            $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                            $(".actualPrice{{$key}}").val("0");
                          } else if (this.value == 2) {
                            $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                            $(".actualPrice{{$key}}").val("0");
                          } else if (this.value == 3) {
                            $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                            $(".actualPrice{{$key}}").val("0");
                          } else if (this.value == 4) {
                            $(".difference{{$key}}").val("0").attr("disabled", "disabled");
                            $(".actualPrice{{$key}}").val("0");
                          } else {
                            $(".difference{{$key}}").removeAttr("disabled");
                            $(".actualPrice{{$key}}").val();
                          }
                        });
                      });
                    </script>
                    @endforeach
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
                  <textarea type="text" name="remark" class="form-control required" id="remark" placeholder="Enter remark">{{ old('remark',$result->remarks) }}</textarea>
                  @if ($errors->has('remark'))
                  <span class="help-block">
                    <strong>{{ $errors->first('remark') }}</strong>
                  </span>
                  @endif
                </div>
                <!-- <div class="form-group">
                    <label for="foc">Free of Cost</label>
                    <input type="checkbox" name="foc" @if($result->foc == 1){{'checked'}}@endif>
                  </div> -->
                <!-- <div class="form-group">
                    <label class="radio-inline" for="paid">
                      <input type="radio" name="option" value="5" @if($result->foc_options == 5) {{'checked'}} @endif>Paid
                    </label>
                    <label class="radio-inline" for="foc">
                      <input type="radio" name="option" value="1" @if($result->foc_options == 1){{'checked'}}@endif>Free of Cost
                    </label>
                    <label class="radio-inline" for="demo">
                      <input type="radio" name="option" value="2" @if($result->foc_options == 2){{'checked'}}@endif>Demo
                    </label>
                    <label class="radio-inline" for="recheck">
                      <input type="radio" name="option" value="3" @if($result->foc_options == 3){{'checked'}}@endif>Recheck
                    </label>
                    <label class="radio-inline" for="repeat">
                      <input type="radio" name="option" value="4" @if($result->foc_options == 4){{'checked'}}@endif>Repeat Work
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
  $('#datepicker').datepicker({
    autoclose: true,
    startDate: '-3d',
    format: 'yyyy-mm-dd',
    endDate: '+0d'
  });

  function isNumber(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (
      (charCode != 45 || $(element).val().indexOf('-') != -1) && // “-” CHECK MINUS, AND ONLY ONE.
      /*(charCode != 46 || $(element).val().indexOf('.') != -1) && */ // “.” CHECK DOT, AND ONLY ONE.
      (charCode < 48 || charCode > 57))
      return false;
    else {
      return true;
    }
  }

  $(document).ready(function() {
    var counter = <?php echo count($selectTreatment) + 1; ?>;
    $("#addrow").on("click", function() {
      var newRow = $("<tr>");
      var cols = "";
      var selected = $('#myTable').find('tbody select:first').clone();
      $(selected).find('option').removeAttr('selected');
      cols += '<td><select onchange="getAllPrice(this)" name="treatment_id[]" class="form-control" required>' + $(selected).html() + '</select></td>';
      cols += "<td><select class='form-control' id='job_type" + counter + "' name='job_type[]' onchange='disable_func(" + counter + ")'><option value='5' selected>Paid</option><option value='1'>Free of Cost</option><option value='2'>Demo</option><option value='3'>Recheck</option><option value='4'>Repeat Work</option></select></td>";
      cols += '<td><input type="text" value="" class="form-control customer' + counter + '" name="customer[]" id="customer' + counter + '" readonly/></td>';
      cols += '<td><input type="text" value="" class="form-control difference' + counter + '" name="difference[]" OnKeypress="return isNumber(event)" onkeyup="getdifference(' + counter + ')" required/></td>';
      cols += '<td><input type="text" value="" class="form-control actualPrice' + counter + '" id="actualPrice' + counter + '" name="actualPrice[]" readonly/></td>';
      // cols += '<input type="hidden" value="" class="form-control" name="dealer[]" required/>';
      // cols += '<input type="hidden" value="" class="form-control" name="incentive[]" required/>';
      cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
      newRow.append(cols);
      console.log(selected);
      $("table.order-list").append(newRow);
      counter++;
    });
    $("table.order-list").on("click", ".ibtnDel", function(event) {
      $(this).closest("tr").remove();
      counter -= 1
    });
  });

  function disable_func(counter) {
    // $('#job_type'+counter).on("change",function(e) {
    var job_type = $('#job_type' + counter).val();
    if (job_type == 1) {
      $(".difference" + counter).val("0").attr("disabled", "disabled");
      $(".actualPrice" + counter).val("0");
    } else if (job_type == 2) {
      $(".difference" + counter).val("0").attr("disabled", "disabled");
      $(".actualPrice" + counter).val("0");
    } else if (job_type == 3) {
      $(".difference" + counter).val("0").attr("disabled", "disabled");
      $(".actualPrice" + counter).val("0");
    } else if (job_type == 4) {
      $(".difference" + counter).val("0").attr("disabled", "disabled");
      $(".actualPrice" + counter).val("0");
    } else {
      $(".difference" + counter).removeAttr("disabled");
      $(".actualPrice" + counter).val();
    }
    // });
  }

  function getAllPrice(e) {
    var complex = <?php echo json_encode($treatments); ?>;
    var ids = $(e).val();
    var price = getObjects(complex, 'id', ids);
    var c = $(e).attr('name').replace('name', '');
    console.log(price);
    // $(e).parents('tr').find('input[name^="dealer"]').val(price[0].dealer_price);
    $(e).parents('tr').find('input[name^="customer"]').val(price[0].customer_price);
    // $(e).parents('tr').find('input[name^="incentive"]').val(price[0].incentive);
  }

  function getObjects(obj, key, val) {
    var objects = [];
    for (var i in obj) {
      if (!obj.hasOwnProperty(i)) continue;
      if (typeof obj[i] == 'object') {
        objects = objects.concat(getObjects(obj[i], key, val));
      } else if (i == key && obj[key] == val) {
        objects.push(obj);
      }
    }
    return objects;
  }

  function calculateRow(row) {
    var customer = +row.find('input[name^="customer"]').val();
    // var dealer = +row.find('input[name^="dealer"]').val();
    // var incentive = +row.find('input[name^="incentive"]').val();
  }

  function calculateGrandTotal() {
    var grandTotal = 0;
    $("table.order-list").find('input[name^="customer"]').each(function() {
      grandTotal += +$(this).val();
    });
    // $("table.order-list").find('input[name^="dealer"]').each(function() {
    //   grandTotal += +$(this).val();
    // });
    // $("table.order-list").find('input[name^="incentive"]').each(function() {
    //   grandTotal += +$(this).val();
    // });
    $("#grandtotal").text(grandTotal.toFixed(2));
  }


  $('#dealer_id').on("change", function(e) {
    var dealer = $("#dealer_id").val();
    token = $('input[name=_token]').val();
    url1 = '<?php echo url("/"); ?>/getModels';
    url2 = '<?php echo url("/"); ?>/getAdvisors';
    url3 = '{{ url("asm/getdealerUsers") }}';
    data = {
      dealer: dealer,
    };
    $.ajax({
      url: url1,
      headers: {
        'X-CSRF-TOKEN': token
      },
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function(resp) {
        console.log(resp);
        $("#model_id").html(resp);
        return false;
      }
    });
    $.ajax({
      url: url3,
      headers: {
        'X-CSRF-TOKEN': token
      },
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function(resp) {
        $("#user_id").html(resp);
        return false;
      }
    });
    $.ajax({
      url: url2,
      headers: {
        'X-CSRF-TOKEN': token
      },
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function(resp) {
        $("#advisor_id").html(resp);
        return false;
      }
    });
    return false;
  });

  $('#model_id').on("change", function(e) {
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
      headers: {
        'X-CSRF-TOKEN': token
      },
      data: data,
      type: 'POST',
      datatype: 'JSON',
      success: function(resp) {
        $("#treatment_id").html(resp);
        return false;
      }
    });
    return false;
  });

  function getdifference(a) {
    var customer = ".customer" + a;
    var actualclass = ".actualPrice" + a;
    var diff = ".difference" + a;
    // var actualPrice = $(actualclass).val();
    var diffPrice = $(diff).val();
    var customerPrice = $(customer).val();
    // var difference = parseInt(actualPrice, 10) - parseFloat(customerPrice);
    var difference = parseFloat(customerPrice) - (parseFloat(customerPrice) * parseInt(diffPrice, 10) / 100);
    $(actualclass).val(isNaN(difference) ? 0 : difference.toFixed(2));
  }

  function validateForm() {
    if ($('#user_id').val() == '') {
      alert("Please Select User");
      return false;
    }
  }
</script>
@endsection