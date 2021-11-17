@extends('layouts.dashboard')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatment Not Done Report
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Treatment Not Done Report</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="tab-button-outer">
        <ul id="tab-button">
          <li class="is-active">
            <a href="#tab01">Treatment Not Done Report</a>
          </li>
        </ul>
      </div>
      <div class="tab-select-outer">
        <select id="tab-select">
          <option value="#tab01">Treatment Not Done Report</option>
        </select>
      </div>
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="text-align: center; margin: 0 auto;">
            <h3 class="box-title">Treatment Not Done Report List</h3>
          </div><!-- /.box-header -->
          <div id="tab01" class="tab-contents">
            <form action="" class="" method="GET" id="filterForm"> 
              <div class="row">
                <div class="col-xs-12 col-md-offset-2 col-md-8">

                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Firms</label>
                    <select name="firm_id" class="form-control" id="firm_id" onchange="this.form.submit()">
                      <option value="">Select Firms</option>
                      @foreach($result['allFirms'] as $firm)
                        <option value="{{$firm->id}}" @if(@request()->firm_id == $firm->id) {{'selected'}} @endif>{{$firm->firm_name}}</option>
                      @endforeach
                    </select>
                  </div> --}}

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


                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>OEM</label>
                    <select name="oem_id" class="form-control" onchange="this.form.submit()">
                      <option value="">Select OEM</option>
                      @foreach($result['allOems'] as $value)
                        <option value="{{$value->id}}" @if(@request()->oem_id  == $value->id) {{'selected'}} @endif>{{$value->oem}}</option>
                      @endforeach
                    </select>
                  </div> --}}


                  <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Dealer</label>
                    <select class="form-control" id="dealer_id" name="dealer_id">
                      <option value="">Select Dealer</option>
                      @foreach($result['allDealers'] as $value)
                      <option {{(@request()->dealer_id==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->name}}</option>
                      @endforeach
                    </select>
                  </div>

                  {{-- <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>Brands</label>
                    <select class="form-control" id="brand_id" name="brand_id">
                      <option value="">Select Brands</option>
                      @foreach($result['allBrands'] as $value)
                      <option {{(@request()->brand_id==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                      @endforeach
                    </select>
                  </div> --}}

                <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>From</label>
                    <input type="text" id="from" name="from" placeholder="From" value="{{!empty(request()->from)?request()->from:""}}" class="datePickerDate form-control" autocomplete="off" />
                </div>
                <div class="form-group report-field col-md-6 col-sm-6 col-xs-12">
                    <label>To</label>
                    <input type="text" id="to" name="to" placeholder="To" value="{{!empty(request()->to)?request()->to:""}}" class="datePickerDate form-control" autocomplete="off" />
                </div>


                <input type="hidden" name="type" value="1">

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
                 
                  @if (!empty($result['notDoneTreatments']))
                    
                    <button onclick="addUrlParameter('excel', '1')" class="btn btn-success" type="">Download</button>
   
                  @endif

                </th>
                <th colspan="1" style="font-size: 12px;">Count:
                   {{request()->type == 1 ? count($result['notDoneTreatments']) : 0}} 
                  
                </th>
                {{-- <th colspan="1" style="">Total consumption value: </th> --}}
                <tr>
                  
                    <th>Sr.no</th>
                    <th>Treatment Name</th>
                  
                </tr>
            </thead>
              <tbody>
       
              @if (!empty($result['notDoneTreatments']))
              @php
                  $i=0;
              @endphp
                  @foreach ($result['notDoneTreatments'] as $key => $value)
                      <tr>
                        <td>{{++$i}}</td>
                        <td>{{get_treatment_name($value)}}</td>
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
                {{-- {{ $total}} --}}
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

  $(document).on('click', '.datePickerDate', function(){
     $(this).datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
     }).focus();
   });


</script> 
@endsection