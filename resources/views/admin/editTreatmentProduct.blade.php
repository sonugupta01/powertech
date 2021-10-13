@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Product            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/treatments')}}"><i class="fa fa-users"></i> Treatments</a></li>
      <li><a href="{{url('/admin/treatmentProducts/')}}/{{$treatment_id}}"><i class="fa fa-users"></i> Treatment Products</a></li>
      <li class="active">Edit Treatment Product</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Treatment Product</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="TreatmentProductform" method="POST" action="{{url('/admin/updateTreatmentProduct')}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="treatment_id" value="{{$treatment_id}}">
                <input type="hidden" name="tp_id" value="{{$result->id}}">
                <input type="hidden" name="pro_quantity" id="pro_quantity" value="">
                <input type="hidden" name="pro_price" id="pro_price" value="">
                <input type="hidden" name="pro_uom" id="pro_uom" value="">
                <div class="box-body">
                  <div class="form-group{{ $errors->has('product_id') ? ' has-error' : '' }}">
                    <label for="product_id">Product<span class="required-title">*</span></label>
                    <select class="form-control required" id="product_id" name="product_id">
                      <option value="">Select Product</option>
                      @foreach($products as $product)
                      <option value="{{$product->id}}" @if($result->pro_id == $product->id){{'selected'}}@endif>{{$product->name}} | Unit: {{$product->quantity}}{{get_uom($product->uom)}} | Price: {{$product->price}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('product_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('product_id') }}</strong>
                      </span>
                    @endif
                  </div>

                  <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}">
                    <label for="quantity">Quantity<span class="required-title">*</span></label>
                    <input type="text" class="form-control required" value="{{ old('quantity', $result->quantity) }}" id="quantity" name="quantity" placeholder="Enter quantity" OnKeypress="return isNumber(event)" autocomplete="off">
                    @if ($errors->has('quantity'))
                      <span class="help-block">
                        <strong>{{ $errors->first('quantity') }}</strong>
                      </span>
                    @endif
                  </div>

                  <!-- <div class="form-group{{ $errors->has('uom') ? ' has-error' : '' }}">
                    <label for="uom">Unit of Measurement<span class="required-title">*</span></label>
                    <select class="form-control required" id="uom" name="uom">
                      <option value="2">ML</option>
                      <option value="1">Litre</option>
                    </select>
                    @if ($errors->has('uom'))
                      <span class="help-block">
                        <strong>{{ $errors->first('uom') }}</strong>
                      </span>
                    @endif
                  </div> -->

                  <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                    <label for="price">Price<span class="required-title">*</span></label>
                    <input type="text" class="form-control required" value="{{ old('price') }}" id="price" name="price" placeholder="Enter price" readonly>
                    @if ($errors->has('price'))
                      <span class="help-block">
                        <strong>{{ $errors->first('price') }}</strong>
                      </span>
                    @endif
                  </div>

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
function isNumber(evt)
{
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31 
    && (charCode < 48 || charCode > 57))
     return false;

  return true;
}

// $("#product_id").on("change", function(){
//   var product_id = $("#product_id").val();
//   $.ajax({
//     type: 'GET',
//     url: '{{url("/admin/getProductData")}}',
//     data: {'p_id': product_id},
//     success: function (data) {
//       for(var i=0;i<data.length;i++){
//         $("#pro_quantity").val(data[i].quantity);
//         $("#pro_price").val(data[i].price);
//         $("#pro_uom").val(data[i].uom);
//       }
//     },
//     error: function (data) {
//       console.error(data);
//     }
//   });
// });  

var product_id = $("#product_id").val();
var quantity = $("#quantity").val();
  $.ajax({
    type: 'GET',
    url: '{{url("/admin/getProductData")}}',
    data: {'p_id': product_id,'qty': quantity},
    success: function (data) {
      $("#pro_uom").val(data.uom);
      $("#price").val(data.finalPrice);
    },
    error: function (data) {
      console.error(data);
    }
  });

$(document).ready(function(){
  $("#quantity").on("keyup", function(){
    var product_id = $("#product_id").val();
    var quantity = $("#quantity").val();
    $.ajax({
      type: 'GET',
      url: '{{url("/admin/getProductData")}}',
      data: {'p_id': product_id,'qty': quantity},
      success: function (data) {
        $("#pro_uom").val(data.uom);
        $("#price").val(data.finalPrice);
      },
      error: function (data) {
        console.error(data);
      }
    });
  });
});

$(document).ready(function(){
  $("#product_id").on("change", function(){
    $("#quantity").val('');
    $("#price").val('');
    $("#quantity").on("keyup", function(){
      var product_id = $("#product_id").val();
      var quantity = $("#quantity").val();
        $.ajax({
          type: 'GET',
          url: '{{url("/admin/getProductData")}}',
          data: {'p_id': product_id,'qty': quantity},
          success: function (data) {
            $("#pro_uom").val(data.uom);
            $("#price").val(data.finalPrice);
          },
          error: function (data) {
            console.error(data);
          }
        });
    });
  });
});
</script>    
@endsection