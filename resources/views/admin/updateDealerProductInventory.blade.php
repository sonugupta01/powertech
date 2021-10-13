@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Update Minimum Inventory Level</>
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealerProducts')}}/{{$dealer_id}}"><i class="fa fa-dashboard"></i> Dealer Products</a></li>
      <!-- <li><a href="{{url(Session::get('prevUrl'))}}"><i class="fa fa-dashboard"></i> Treatment List</a></li> -->
      <li class="active">Update inventory</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title"></h3>
            <a href="{{url('/admin/dealerProducts')}}/{{$dealer_id}}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="inventoryForm" name="inventoryForm" method="POST" action="{{url('/admin/updateDealerProductInventory')}}">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="dealer_id" value="{{$dealer_id}}">               
              <input type="hidden" name="selectedMonth" value="{{Session::get()}}">               
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered table-hover" id="inventoryTbl">
                    <thead>
                      <tr>
                        <td><b>Name</b></td>
                        <td><b>Minimum Inventory</b></td>
                        <td><b>Unit</b></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="col-sm-3"><input type="hidden" name="product_id" value="{{$product_id}}">{{get_product_name($product_id)}}</td>
                        <td class="col-sm-3"><input type="text" name="minimum_stock" id="minimum_stock" class="minimum_stock" value="{{@$minimum_stock->minimum_stock!=''?@$minimum_stock->minimum_stock:''}}" OnKeypress="return isNumber(event)" required/></td>
                        <td class="col-sm-3"><input type="hidden" name="pro_unit" value="{{get_product_unit($product_id)}}" readonly/>@if(get_product_unit($product_id)==1){{'Litre'}}@elseif(get_product_unit($product_id)==2){{'ML'}}@elseif(get_product_unit($product_id)==3){{'Pcs.'}}@elseif(get_product_unit($product_id)==4){{'Gms.'}}@endif</td>
                      </tr>
                    </tbody>
                    <!-- <tfoot>
                      <td style="text-align:right;">Total = </td>
                      <td><input type="text" class="totalTargetNum" name="totalTargetNum" value="" readonly></td>
                      <td style="text-align:right;">Total = </td>
                      <td><input type="text" class="grdtot" name="grdtot" value="" readonly></td>
                    </tfoot> -->
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
</script>
@endsection