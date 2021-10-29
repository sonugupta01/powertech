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
            <li><a href="{{url('/asm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Reports</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="tab-button-outer">
              <ul id="tab-button">
                <li class="">
                  <a href="#tab03">DCF</a>
                </li>
              </ul>
            </div>
            <div class="tab-select-outer">
              <select id="tab-select">
                <option value="#tab03">DCF</option>
              </select>
            </div>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header" style="text-align: center; margin: 0 auto;">
                  <h3 class="box-title">Reports List</h3>
                </div><!-- /.box-header -->
              <div class="tab-contents" id="tab03">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                <form action="" class="" method="GET"> 
                  <div class="row">
                        <div class="input-group form-group report-field col-md-4 col-sm-4 col-xs-12" style="float: left !important;">
                          <label>Select Month</label>
                          <input type="text"  id="selectMonth" name="selectMonth" placeholder="Select Month" value="" class="datePicker1 form-control" autocomplete="off" />
                        </div>

                        
                        
                    <div class="form-group report-field col-md-4 col-sm-4 col-xs-12">
                      <label>Firms</label>
                      <select name="firm" class="form-control" id="firm">
                        <option value="">Select Firms</option>
                        @foreach($firmsList as $firm)
                          <option value="{{$firm->id}}" @if(@$oldFirm == $firm->id) {{'selected'}} @endif>{{$firm->firm_name}}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group report-field col-md-4 col-sm-4 col-xs-12">
                      <label>Brands</label>
                      <select class="form-control" id="brand" name="brand">
                        <option value="">Select Brand</option>
                        @foreach($brandList as $value)
                        <option {{(@request()->brand==$value->id)?'selected':''}} value="{{$value->id}}">{{$value->brand_name}}</option>
                        @endforeach
                      </select>
                    </div>
                        
                          <div class="form-group report-field col-md-6 col-sm-6 col-xs-12" style="margin-top:25px;">
                            <input class="btn btn-success" type="submit" value="Download">
                          </div>
                  </div>
                </form>
              </div>
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
<script type="text/javascript">
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);

  $(document).on('click', '.datePicker1', function(){
     $(this).datepicker({
        autoclose: true,
        format: "yyyy-mm",
        startView: "months", 
        minViewMode: "months"
     }).focus();
   });
  $(function() {
  var $tabButtonItem = $('#tab-button li'),
      $tabSelect = $('#tab-select'),
      $tabContents = $('.tab-contents'),
      activeClass = 'is-active';
//  $tabButtonItem.first().addClass(activeClass);
  $tabContents.not(':first').hide();

  $tabSelect.on('change', function() {
    var target = $(this).val(),
        targetSelectNum = $(this).prop('selectedIndex');
    $tabButtonItem.removeClass(activeClass);
    $tabButtonItem.eq(targetSelectNum).addClass(activeClass);
    $tabContents.hide();
    $(target).show();
  });
});
  $('#tab-button li a').on('click', function(e) {
     e.preventDefault();
     var $tabButtonItem = $('#tab-button li'),
      $tabSelect = $('#tab-select'),
      $tabContents = $('.tab-contents'),
      activeClass = 'is-active';
    var target = $(this).attr('href');   
    $tabButtonItem.removeClass(activeClass);
    $(this).parent().addClass(activeClass);
    $tabSelect.val(target);
    $tabContents.hide();
    $(target).show();
    
  });
 
</script> 
@endsection