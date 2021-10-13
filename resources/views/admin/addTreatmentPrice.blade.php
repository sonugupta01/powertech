@extends('layouts.dashboard')

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
      <li><a href="{{url('/admin/treatmentProducts/')}}/{{$treatment_id}}"><i class="fa fa-wrench"></i> Treatments({{get_treatment_name($treatment_id)}})</a></li>
      <li class="active">Price Update</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Update Price For Treatment <b>{{get_treatment_name($treatment_id)}}</b></h3>
          </div><!-- /.box-header -->
          <div class="box-body">

            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif

            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/updateTreatmentPrice')}}/{{$treatment_id}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="treatment_id" value="{{$treatment_id}}">
                <div class="box-body">
                  <div class="form-group{{ $errors->has('customer_price') ? ' has-error' : '' }}">
                    <label for="customer_price">Customer Price<span class="required-title">*</span></label>

                    <input type="text" class="form-control required" value="@if(!empty($result->customer_price )){{old('customer_price', $result->customer_price)}}@else{{old('customer_price', $product_price)}}@endif" id="customer_price" name="customer_price" placeholder="Enter customer price" OnKeypress="return isNumber(event)">
                    @if ($errors->has('customer_price'))
                      <span class="help-block">
                        <strong>{{ $errors->first('customer_price') }}</strong>
                      </span>
                    @endif
                  </div>

                  <!-- <div class="form-group{{ $errors->has('dealer_price') ? ' has-error' : '' }}">
                    <label for="dealer_price">Dealer Price<span class="required-title">*</span></label>
                    <input type="text" class="form-control required" value="{{ old('dealer_price', $result->dealer_price) }}" id="dealer_price" name="dealer_price" placeholder="Enter dealer price" OnKeypress="return isNumber(event)">
                    @if ($errors->has('dealer_price'))
                      <span class="help-block">
                        <strong>{{ $errors->first('dealer_price') }}</strong>
                      </span>
                    @endif
                  </div>

                  <div class="form-group{{ $errors->has('incentive') ? ' has-error' : '' }}">
                    <label for="incentive">Incentive<span class="required-title">*</span></label>
                    <input type="text" class="form-control required" value="{{ old('incentive', $result->incentive) }}" id="incentive" name="incentive" placeholder="Enter incentive" OnKeypress="return isNumber(event)">
                    @if ($errors->has('incentive'))
                      <span class="help-block">
                        <strong>{{ $errors->first('incentive') }}</strong>
                      </span>
                    @endif
                  </div>
                </div> -->
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
</script>  
@endsection