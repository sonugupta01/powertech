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
              <input type="hidden" name="inventory_id" value="{{@$minimum_stock->id}}">             
              <input type="hidden" name="dealer_id" value="{{$dealer_id}}">             
              <input type="hidden" name="selectedMonth" value="{{Session::get('selectedMonth')}}">               
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered table-hover" id="inventoryTbl">
                    <thead>
                      <tr>
                        <td><b>Name</b></td>
                        <td><b>Minimum Stock</b></td>
                        <td><b>Stock in Hand</b></td>
                        <td><b>Unit</b></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="col-sm-3"><input type="hidden" name="product_id" value="{{$product_id}}">{{get_product_name($product_id)}}</td>
                        <td class="col-sm-3"><input type="text" name="minimum_stock" id="minimum_stock" class="minimum_stock" value="{{@$minimum_stock->minimum_stock!=''?@$minimum_stock->minimum_stock:''}}" OnKeypress="return isNumber(event)" required/></td>
                        <td class="col-sm-3"><input type="text" name="stock_in_hand" id="stock_in_hand" class="stock_in_hand" value="{{@$minimum_stock->stock_in_hand!=''?@$minimum_stock->stock_in_hand:''}}" OnKeypress="return isNumber(event)"/></td>
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
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Update History</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <table id="exa" class="table table-bordered table-striped" id="targetsTbl">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Minimum Stock</th>
                  <th>Stock in Hand</th>
                  <th>Last update</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($updateHistory) >= 1) {
                  $i = 0;
                  // $s = $productDetail->perPage() * ($productDetail->currentPage() - 1);
                  foreach ($updateHistory as $value) {
                    if (!empty($value->minimum_stock) || $value->minimum_stock!=0) {
                      $minimum_stock = $value->minimum_stock;
                    } else {
                      $minimum_stock = 0;
                    } 
                ?>

                    <tr id="defaultData">
                      <td>{{++$i}}</td>
                      <td>
                      @if(!empty($minimum_stock))
                      {{$minimum_stock}} {{get_unit_name($value->uom)}}
                      @else
                      {{'-'}}
                      @endif
                      </td>
                      <td>
                      @if(!empty($value->stock_in_hand))
                      {{$value->stock_in_hand}} {{get_unit_name($value->uom)}}
                      @else
                      {{'-'}}
                      @endif
                      </td>
                      <td>
                      @if(!empty($value->updated_at))
                      {{$value->updated_at}} </td>
                      @else
                      {{'-'}}
                      @endif
                    </tr>
                  <?php
                  //   $i++;
                  }
                } else { ?>
                  <tr>
                    <td colspan="7">No Record</td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Sr. No.</th>
                  <th>Minimum Stock</th>
                  <th>Stock in Hand</th>
                  <th>Last update</th>
                </tr>
              </tfoot>
            </table>
            <?php // echo $productDetail->links(); ?>
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