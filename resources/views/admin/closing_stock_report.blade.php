@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Reports
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Reports</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="tab-button-outer">
        <ul id="tab-button">
          <li class="is-active">
            <a href="#tab01">Closing Stock Report</a>
          </li>
        </ul>
      </div>
      <div class="tab-select-outer">
        <select id="tab-select">
          <option value="#tab01">Closing Stock Report</option>
        </select>
      </div>
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="text-align: center; margin: 0 auto;">
            <h3 class="box-title">Reports List</h3>
          </div><!-- /.box-header -->
          <div id="tab01" class="tab-contents">
            <form action="" class="" method="GET" id="dailyreportform"> 
              <div class="row">
                <div class="col-xs-12 col-md-offset-2 col-md-8">
                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Firms</label>
                    <select name="firm" class="form-control" id="firm">
                      <option value="">Select Firms</option>
                      @foreach($firms as $firm)
                        <option value="{{$firm->id}}" @if(@$oldFirm == $firm->id) {{'selected'}} @endif>{{$firm->firm_name}}</option>
                      @endforeach
                    </select>
                  </div> --}}
                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>ASM</label>
                    <select name="asm" class="form-control" id="asm">
                      <option value="">Select ASM</option>
                      @foreach($asms as $asm)
                        <option value="{{$asm->id}}" @if(@$oldAsm == $asm->id) {{'selected'}} @endif>{{$asm->name}}</option>
                      @endforeach
                    </select>
                  </div> --}}
                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>OEM</label>
                    <select name="oem" class="form-control">
                      <option value="">Select OEM</option>
                      @foreach($oems as $val)
                        <option value="{{$val->oem_id}}" @if(@$oldOem == $val->oem_id) {{'selected'}} @endif>{{get_oem_name($val->oem_id)}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Group</label>
                    <select name="group" class="form-control">
                      <option value="">Select Group</option>
                      @foreach($groups as $val)
                        <option value="{{$val->group_id}}" @if(@$oldGroup == $val->group_id) {{'selected'}} @endif>{{get_group_name($val->group_id)}}</option>
                      @endforeach
                    </select>
                  </div> --}}
                </div>
                <hr style="clear: both;" />
                <div class="col-md-offset-2 col-md-8 col-sm-12 resport-tabs" >
                    {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>Start Date</label>
                      <input type="text"  id="fromDate from" name="from" placeholder="From Date" value="{{@request()->from}}" class="datePicker form-control" autocomplete="off" />
                    </div>
                    <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>End Date</label>
                      <input type="text" id="toDate to" name="to" placeholder="To Date" value="{{@request()->to}}" class="datePicker form-control" autocomplete="off" />
                    </div> --}}
                    {{-- <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                        <label>or</label>
                    </div> --}}
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                        <label>Select Date</label>
                        <input type="text" id="date" name="date" placeholder="Select Date" value="{{@request()->date}}" class="datePicker form-control" autocomplete="off" />
                    </div>
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                      <label>Dealer</label>
                      <select class="form-control" id="dealer_id" name="dealer_id">
                        <option value="">Select Dealer</option>
                        @foreach($result['dealers'] as $value)
                        <option {{(@request()->dealer_id==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                        @endforeach
                      </select>
                    </div>
                    {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                      <label>Departments</label>
                      <select class="form-control" id="department" name="department">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                        <option {{(@$oldDepartment==$department->id)?'selected':''}} value="{{$department->id}}">{{$department->name}}</option>
                        @endforeach
                      </select>
                    </div> --}}
                    {{-- <div class="form-group report-field col-md-4 col-sm-4 col-xs-12">
                      <label>Dealer's Advisors</label>
                      <select class="form-control" id="advisor" name="advisor">
                        <option value="">Select Advisor</option>
                        @foreach($allAdvisors as $advisor)
                        <option {{(@$oldAdvisor==$advisor->advisor_id)?'selected':''}} value="{{$advisor->advisor_id}}" style="font-size: 16px;">{{get_advisor_name($advisor->id)}} - {{get_dealer_name($advisor->dealer_id)}}</option>
                        @endforeach
                      </select>
                    </div> --}}

                    {{-- brand dropdown start filter --}}
                    {{-- <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                      <label>Brands</label>
                      <select class="form-control" id="brand" name="brand">
                        <option value="">Select Brand</option>
                        @foreach($brands as $value)
                        <option {{(@request()->brand==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                        @endforeach
                      </select>
                    </div> --}}
                    {{-- brand dropdown end filter --}}
                    
                    {{-- <div class="form-group report-field col-md-12 col-sm-12 col-xs-12">
                      <label>Report Type</label>
                      <div class="form-control required">
                        <input type="radio" {{(@$oldReport=='dealer')?'checked':''}} value="dealer" name="report_type" checked> Dealer Wise
                        <input type="radio" {{(@$oldReport=='advisor')?'checked':''}} value="advisor" name="report_type"> Advisor Wise
                        <input type="radio" {{(@$oldReport=='asm_id')?'checked':''}} value="asm_id" name="report_type"> ASM Wise
                        <input type="radio" {{(@$oldReport=='firm_id')?'checked':''}} value="firm_id" name="report_type"> Firm Wise
                      </div>
                    </div> --}}
                    <div class="form-group report-field col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
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
                  {{-- {{dd($result['productDetail'])}} --}}
                  @if(count($result['productDetail'][0])>0 || empty(request()->dealer_id))
                    
                    <button onclick="addUrlParameter('excel', '1')" class="btn btn-success" type="">Download</button>
   
                  @endif
                </th>
                <th colspan="8" style="font-size: 12px;">Dealer Count: {{count($result['productDetail'])}}</th>
                <tr>
                    <th>Sr.no</th>
                    <th>Product Name</th>
                    {{-- <th>Minimum Stock</th> --}}
                    <th>Closing Stock</th>
                    <th>LastUpdated At</th>
                    <th>LastUpdated By</th>
                </tr>
            </thead>
              <tbody>
{{-- {{dd($result['productDetail'])}} --}}
                @if (count($result['productDetail'][0])>0  && !empty(request()->dealer_id))
                @foreach ($result['productDetail'][0] as $key=> $value)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{@$value->pro_name}}</td>
                  {{-- <td>{{!empty($value->minimum_stock) ?$value->minimum_stock: "0"}} {{$value->unit_name}}</td> --}}
                  <td>{{!empty($value->stock_in_hand) ?$value->stock_in_hand: "0"}} {{$value->unit_name}}</td>
                  <td>
                    @if(!empty($value->updated_at))
                    {{$value->updated_at}} </td>
                    @else
                    {{'-'}}
                    @endif

                  </td>
                  <td>
                    {{-- {{dd(@$value)}} --}}
                    @if(!empty($value->updated_by))
                    {{get_name($value->updated_by)}} </td>
                    @else
                    {{'-'}}
                    @endif

                  </td>
                </tr> 
                @endforeach
             
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

// $(document).ready(function() {
//     $('.datatable').DataTable({
//       "paging":   false,
//         "ordering": false,
//         "info":     false,
//         "searching": false,
//       dom: 'Bfrtip',
//         buttons: [
//            'excel'
//         ]
//     });
// } );





function addUrlParameter(name, value) {
  var searchParams = new URLSearchParams(window.location.search)
  searchParams.set(name, value)
  window.location.search = searchParams.toString()
}



// dates scripts
  $(document).on('change','.datePicker',function(){
      $('.datePicker1').val('');
  });

  $(document).on('change','.datePicker1',function(){
      $('.datePicker').val('');
  });

  $(document).on('click', '.datePicker', function(){
     $(this).datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        endDate: "today"
     }).focus();
   });
  
  $(document).on('click', '.datePicker1', function(){
     $(this).datepicker({
        autoclose: true,
        format: "yyyy-mm",
        startView: "months", 
        minViewMode: "months",
     }).focus();
   });

</script> 
@endsection