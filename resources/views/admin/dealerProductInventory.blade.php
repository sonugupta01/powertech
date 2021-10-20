@extends('layouts.dashboard')
@section('content')
<?php 
  $selectedMonth = request()->month; 
  Session::put('selectedMonth', $selectedMonth);
?>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Minimum Inventory Level
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-dashboard"></i> Dealers</a></li>
      <li class="active">Minimum Inventory Level</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <div class="row">
              <div class="col-md-6">
                <h3 class="box-title pull-right">Minimum Inventory Level for <b>{{get_dealer_name($dealer_id)}}</b> For Month: </h3>
              </div>
              <div class="col-md-2">
                <form action="" id="inventoryForm" method="GET">
                  {{-- <input type="hidden" name="dealer_id" id="dealer_id" value="{{$dealer_id}}"> --}}
                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <input type="text" id="month" name="month" placeholder="Select Month" value="{{@$selectedDate}}" class="datePicker form-control" autocomplete="off" readonly />
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="col-md-3">
                  <form action="{{url('admin/downloadProductInventory')}}/{{$dealer_id}}" id="downloadProductInventory" method="GET">
                  <input type="hidden" name="month" value="{{@$selectedDate}}">
                  <input type="submit" class="btn btn-success floatright btn-div pull-left" name="download" id="download" value="Download" style="margin-right: 10px;">
                </form>
              </div>
            </div>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <table id="exa" class="table table-bordered table-striped" id="targetsTbl">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Product Name</th>
                  <th>Minimum Stock</th>
                  <th>Treatmentwise Consumption</th>
                  <th>Expected Stock</th>
                  <th>Stock in Hand</th>
                  <th>Last update</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($productDetail) >= 1) {
                  $i = 0;
                  // $s = $productDetail->perPage() * ($productDetail->currentPage() - 1);
                  foreach ($productDetail as $value) {
                    if (!empty($value->minimum_stock) || $value->minimum_stock!=0) {
                      $minimum_stock = $value->minimum_stock;
                    } else {
                      $minimum_stock = 0;
                    }

                    if (!empty($value->consumedQuantity)) {
                      $consumedQuantity = $value->consumedQuantity;
                    } else {
                      $consumedQuantity = 0;
                    }
                ?>
                    <tr id="defaultData">
                      <td>{{++$i}}</td>
                      <td>{{$value->pro_name}}</td>
                      <td>
                      @if(!empty($minimum_stock))
                      {{$minimum_stock}} {{$value->unit_name}}
                      @else
                      {{'-'}}
                      @endif
                      </td>
                      <td>
                      @if(!empty($consumedQuantity))
                      {{$consumedQuantity}} {{$value->unit_name}}
                      @else
                      {{'-'}}
                      @endif
                      </td>
                      <?php 
                        if (!empty($minimum_stock)) {
                          $expectedStock = ($value->minimum_stock - $consumedQuantity).' '.$value->unit_name;
                        } else {
                          $expectedStock = '-';
                        }                        
                      ?>
                      <td>{{$expectedStock}} </td>
                      <td>
                      @if(!empty($value->stock_in_hand))
                      {{$value->stock_in_hand}} {{$value->unit_name}}
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
                      <td>
                        <a href="{{ url('/admin/dealerProductInventory')}}/{{$dealer_id}}/{{$value->id}}" class="btn btn-success" {{$selectedDate != date('Y-m')?'disabled':''}}>Update</a>
                      </td>
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
                  <th>Product Name</th>
                  <th>Minimum Stock</th>
                  <th>Treatmentwise Consumption</th>
                  <th>Expected Stock</th>
                  <th>Stock in Hand</th>
                  <th>Last update</th>
                  <th>Action</th>
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
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 3000);

  $(document).on('click', '.datePicker', function() {
    $(this).datepicker({
      autoclose: true,
      format: "yyyy-mm",
      startView: "months",
      minViewMode: "months"
    }).focus();
  });

  $('#month').on('change', function() {
    $('#inventoryForm').submit();
  });

  // $('#temp_id').on('change', function() {
  //   $('#inventoryForm').submit();
  // });
</script>
@endsection