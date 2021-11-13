@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Consumption Report
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Consumption Report</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="tab-button-outer">
        <ul id="tab-button">
          <li class="is-active">
            <a href="#tab01">Consumption Report</a>
          </li>
        </ul>
      </div>
      <div class="tab-select-outer">
        <select id="tab-select">
          <option value="#tab01">Consumption Report</option>
        </select>
      </div>
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="text-align: center; margin: 0 auto;">
            <h3 class="box-title">Consumption Report List</h3>
          </div><!-- /.box-header -->
          <div id="tab01" class="tab-contents">
            <form action="" class="" method="GET" id="filterForm"> 
              <div class="row">
                <div class="col-xs-12 col-md-offset-2 col-md-8">
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Firms</label>
                    <select name="firm_id" class="form-control" id="firm_id" onchange="this.form.submit()">
                      <option value="">Select Firms</option>
                      @foreach($result['allFirms'] as $firm)
                        <option value="{{$firm->id}}" @if(@request()->firm_id == $firm->id) {{'selected'}} @endif>{{$firm->firm_name}}</option>
                      @endforeach
                    </select>
                  </div>
                  @if(Auth::user()->role==1)
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>ASM</label>
                    <select name="asm_id" class="form-control" id="asm_id" onchange="this.form.submit()">
                      <option value="">Select ASM</option>
                      @foreach($result['allAsms'] as $asm)
                        <option value="{{$asm->id}}" @if(@request()->asm_id == $asm->id) {{'selected'}} @endif>{{$asm->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  @endif
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>OEM</label>
                    {{-- {{dd($result['allOems'])}} --}}
                    <select name="oem_id" class="form-control" onchange="this.form.submit()">
                      <option value="">Select OEM</option>
                      @foreach($result['allOems'] as $value)
                        <option value="{{$value->id}}" @if(@request()->oem_id  == $value->id) {{'selected'}} @endif>{{$value->oem}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Dealer</label>
                    <select class="form-control" id="dealer_id" name="dealer_id">
                      <option value="">Select Dealer</option>
                      @foreach($result['allDealers'] as $value)
                      <option {{(@request()->dealer_id==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Brands</label>
                    <select class="form-control" id="brand_id" name="brand_id">
                      <option value="">Select Brands</option>
                      @foreach($result['allBrands'] as $value)
                      <option {{(@request()->brand_id==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Select Month</label>
                    <input type="text" id="month" name="month" placeholder="Select Month" value="{{!empty(request()->month)?request()->month:date("Y-m")}}" class="datePicker form-control" autocomplete="off" />
                </div>
                <div class="input-group form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                  <input class="btn btn-primary" type="submit" value="Submit">
                </div>
                </div>
            

              </div>
            </form>
            <!-- <button class="btn btn-success" id="all_dealers" style="margin-left: 48px;">Download Whole Report</button> -->


            {{-- table --}}
            <div class="box-body table-responsive" style="overflow: auto;">
            <table class="table table-bordered table-striped report-table datatable">
              <thead>
                <th style="text-align:center;">
                 
                  @if (!empty($result['productConsumptionData']))
                    
                    <button onclick="addUrlParameter('excel', '1')" class="btn btn-success" type="">Download</button>
   
                  @endif
                </th>
                <th colspan="8" style="font-size: 12px;">Count: {{count($result['productConsumptionData'])}}</th>
                <tr>
                    <th>Sr.no</th>
                    <th>Product Name</th>
                    {{-- <th>Minimum Stock</th> --}}
                    <th>Total Quantity</th>
                    <th>Total Price</th>
                </tr>
            </thead>
              <tbody>
       
              @if (!empty($result['productConsumptionData']))
              @php
                  $i=0;
              @endphp
                  @foreach ($result['productConsumptionData'] as $key => $value)
                      <tr>
                        <td>{{++$i}}</td>
                        <td>{{get_product_name($value->product_id)}}</td>
                        <td>{{$value->quantity}} {{get_unit_name($value->uom)}}</td>
                        <td>{{$value->price}}</td>
                      </tr>
                  @endforeach

              @else
              <tr>
                <td colspan="8">
                  No Record
                </td>
               
              </tr>
              
              @endif
                                     
                                      
                                      
              
              </tbody>
              <tfoot>
              </tfoot>
            </table>
          </div>
          
          </div>
        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">

// $("#filterForm").change(function() {
//   // alert("Dfc");
//   $(this).form.submit();
// });

function addUrlParameter(name, value) {
  var searchParams = new URLSearchParams(window.location.search)
  searchParams.set(name, value)
  window.location.search = searchParams.toString()
}

  // dates scripts

  $(document).on('click', '.datePicker', function(){
     $(this).datepicker({
        autoclose: true,
        format: "yyyy-mm",
        startView: "months", 
        minViewMode: "months"
     }).focus();
   });


</script> 
@endsection