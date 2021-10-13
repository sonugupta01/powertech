@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Minimum Inventory Level
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealerTemplates')}}/{{$dealer_id}}"><i class="fa fa-dashboard"></i> Dealer Templates</a></li>
      <li class="active">Minimum Inventory Level</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Set Minimum Inventory Level for <b>{{get_dealer_name($dealer_id)}}</b> for template <b>{{get_template_name($template_id)}}</b></h3>
            <div class="row">
              <!-- <div class="col-md-5"> -->
              <!-- </div> -->
              <div class="col-md-7">
                <form action="" id="form" method="GET">
                  {{-- <input type="hidden" name="dealer_id" id="dealer_id" value="{{$dealer_id}}"> --}}
                  <div class="row">
                    {{-- <div class="col-md-3">
                      <div class="form-group">
                        <input type="text" id="month" name="month" placeholder="Select Month" value="{{$search!=''?$search:$currentMonth}}" class="datePicker form-control" autocomplete="off" readonly />
                      </div>
                    </div> --}}
                    {{-- <div class="col-md-3">
                      <h3 class="box-title pull-right">and Template :</h3>
                    </div>
                    <div class="col-md-5">
                      <select class="form-control" id="temp_id" name="temp_id">
                        <option value="">Select Template</option>
                        @foreach($templates as $template)
                        <option value="{{$template->id}}">{{$template->temp_name}}</option>
                    @endforeach
                    </select>
                   </div> --}}
                  </div>
                </form>
            </div>
          </div>
          {{-- <a href="{{url('/admin/addTarget')}}" class="btn btn-success floatright">Add Target</a> --}}
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
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($productDetail) >= 1) {
                $i = 0;
                // $s = $productDetail->perPage() * ($productDetail->currentPage() - 1);
                foreach ($productDetail as $value) {
              ?>

                  <tr id="defaultData">
                    <td>{{++$i}}</td>
                    <td>{{$value->pro_name}}</td>
                    <td>
                    @if(!empty($value->stock))
                    {{$value->stock}} {{$value->unit_name}}
                    @else
                    {{'-'}}
                    @endif
                    </td>
                    <td>
                      <a href="{{ url('/admin/set_min_level')}}/{{$dealer_id}}/{{$template_id}}/{{$value->id}}" class="btn btn-success">Update</a>
                    </td>
                  </tr>
                <?php
                //   $i++;
                }
              } else { ?>
                <tr>
                  <td colspan="3">No Record</td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Sr. No.</th>
                <th>Product Name</th>
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
    $('#form').submit();
  });

  // $('#temp_id').on('change', function() {
  //   $('#form').submit();
  // });
</script>
@endsection