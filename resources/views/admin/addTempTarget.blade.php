@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Target Management            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/targets')}}"><i class="fa fa-dashboard"></i> Targets</a></li>
      <li class="active">Add Target</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Target</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="targetForm" name="targetForm" method="POST" action="{{url('/admin/insertTempTarget')}}">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="template_id" value="{{$temp_id}}">
              <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
              <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                  <input type="text" class="form-control" value="{{get_dealer_name($dealer_id)}}" disabled/>
                @if ($errors->has('dealer_id'))
                <span class="help-block">
                  <strong>{{ $errors->first('dealer_id') }}</strong>
                </span>
                @endif
              </div>
              <div class="form-group{{ $errors->has('temp_id') ? ' has-error' : '' }}">
                <label for="temp_id">Template<span class="required-title">*</span></label>
                  <input type="text" class="form-control" value="{{get_template_name($temp_id)}}" disabled/>
                @if ($errors->has('temp_id'))
                <span class="help-block">
                  <strong>{{ $errors->first('temp_id') }}</strong>
                </span>
                @endif
              </div>
              <div class="form-group{{ $errors->has('month') ? ' has-error' : '' }}">
                <label for="month">Select Month<span class="required-title">*</span></label>
                  <input type="text" id="month" name="month" placeholder="Select Month" value="{{old('month')}}" class="datePicker1 form-control" autocomplete="off" />
                @if ($errors->has('month'))
                <span class="help-block">
                  <strong>{{ $errors->first('month') }}</strong>
                </span>
                @endif
              </div>
                
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered table-hover" id="treatmentsTbl">
                    <thead>
                      <tr>
                        <td><b>Name</b></td>
                        <td><b>No. Of Treatments</b></td>
                        <td><b>Customer Price</b></td>
                        <td><b>Total Target</b></td>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($treatments as $treatment)
                      <tr>
                        <td class="col-sm-3"><input type="hidden" name="treatment_id[]" value="{{$treatment->id}}">{{$treatment->treatment}}</td>
                        <td class="col-sm-3"><input type="text" name="targetNum[]" id="targetNum{{$treatment->id}}" class="targetNum" value="" OnKeypress="return isNumber(event)" required/></td>
                        <td class="col-sm-3"><input type="text" class="dealer_price" id="dealer_price{{$treatment->id}}" name="customer_price[]" value="{{$treatment->customer_price}}" readonly></td>
                        <td class="col-sm-3"><input type="text" name="total_target[]" class="total_target" id="total_target{{$treatment->id}}" value="0" readonly/></td>
                      </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <td style="text-align:right;">Total = </td>
                      <td><input type="text" class="totalTargetNum" name="totalTargetNum" value="" readonly></td>
                      <td style="text-align:right;">Total = </td>
                      <td><input type="text" class="grdtot" name="grdtot" value="" readonly></td>
                    </tfoot>
                  </table>
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
$(document).on('click', '.datePicker1', function(){
  $(this).datepicker({
    autoclose: true,
    format: "yyyy-mm",
    startView: "months", 
    minViewMode: "months"
  }).focus();
});

var $tblrows = $("#treatmentsTbl tbody tr");
$tblrows.each(function (index) {
    var $tblrow = $(this);
    $tblrow.find(".targetNum").on("keyup", function () {
        var numbers = $tblrow.find("[name*=targetNum]").val();
        var price = $tblrow.find("[name*=customer_price]").val();
        var sum = 0;
        $(".targetNum").each(function() {
            var value = $(this).val();
            if(!isNaN(value) && value.length != 0) {
                sum += parseFloat(value);
            }
        });
        $(".totalTargetNum").val(sum);
        var subTotal = parseInt(numbers,10) * parseFloat(price);
        if (!isNaN(numbers)) {
            $tblrow.find(".total_target").val(isNaN(subTotal.toFixed(2))? 0 : subTotal.toFixed(2));
            var grandTotal = 0;
            $(".total_target").each(function () {
                var stval = parseFloat($(this).val());
                grandTotal += isNaN(stval) ? 0 : stval;
            });
            $(".grdtot").val(grandTotal.toFixed(2));
        } 
    });
});

</script>
@endsection