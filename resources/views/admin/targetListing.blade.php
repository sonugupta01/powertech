@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Target Management
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/targets')}}"><i class="fa fa-dashboard"></i> Targets</a></li>
      <li class="active">Treatment List</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <div class="row">
              <div class="col-md-5">
                <h3 class="box-title pull-right">Treatment List of <b>{{get_dealer_name($dealer_id)}} </b>for Month:</h3>
              </div>
              <div class="col-md-7">
                <form action="" id="form" method="GET">
                  {{-- <input type="hidden" name="dealer_id" id="dealer_id" value="{{$dealer_id}}"> --}}
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <input type="text" id="month" name="month" placeholder="Select Month" value="{{$search!=''?$search:$currentMonth}}" class="datePicker form-control" autocomplete="off" readonly />
                      </div>
                    </div>
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
        <form action="" method="GET">
          <div class="row">
            <div class="col-xs-12 col-md-12">
              <div class="col-xs-6 col-md-6 pull-left">
              </div>
              {{-- <div class="col-xs-4 col-md-4 pull-right">
                  <div class="input-group ">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, or mobile no. or Designation" id="txtSearch">
                    <div class="input-group-btn">
                      <button class="btn btn-primary" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                      </button>
                    </div>
                  </div>
                </div> --}}
            </div>
          </div>
        </form>
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
                <th>Treatment</th>
                <th>Total Target</th>
                <th>Done</th>
                <th>Pending</th>
                {{-- <th>Customer Price</th> --}}
                <th>Total Price</th>
                <th>Achieve Price</th>
                <th>Balance</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($result) >= 1) {
                $i = 1;
                $s = $result->perPage() * ($result->currentPage() - 1);
                foreach ($result as $value) {
              ?>

                  <tr id="defaultData">
                    <td>{{++$s}}</td>
                    <td>{{get_treatment_name($value->treatment_id)}}</td>
                    <td>{{$value->target_num}}</td>
                    @if(isset($value->countdone))
                    <td>{{$done = count(@$value->countdone)}}</td>
                    @else
                    <td>{{$done = 0}}</td>
                    @endif
                    <td>{{$pending = $value->target_num - $done}}</td>
                    {{-- <td>{{$value->customer_price}}</td> --}}
                    <td>{{$value->total_target}}</td>
                    <td>{{$achieveprice = $done*$value->customer_price}}</td>
                    <td>{{$balnce = $value->total_target - $achieveprice}}</td>
                    <td>
                      <a href="{{ url('/admin/editTempTarget')}}/{{$dealer_id}}/{{$template_id}}/{{$value->id}}" class="btn btn-success">Edit</a>
                      {{-- <a href="{{ url('/admin/statusTarget/delete')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> --}}
                    </td>
                  </tr>

                  {{-- <tr id="getDataByFilters"></tr> --}}
                <?php
                  $i++;
                }
              } else { ?>
                <tr>
                  <td colspan="9">No Record</td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Sr. No.</th>
                <th>Treatment</th>
                <th>Total Target</th>
                <th>Done</th>
                <th>Pending</th>
                {{-- <th>Customer Price</th> --}}
                <th>Total Price</th>
                <th>Achieve Price</th>
                <th>Balance</th>
                <th>Action</th>
              </tr>
            </tfoot>
          </table>
          <?php echo $result->links(); ?>
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


  // $('#template_id').on('change',function(){
  //   var template_id = $(this).val();
  //   var dealer_id = $("#dealer_id").val();
  //   var html = '';
  //   // alert(dealer_id);
  //     $.ajax({
  //       type: 'GET',
  //       url: '{{url("/admin/getTarget")}}',
  //       data: {
  //         'dealer_id': dealer_id,
  //         'template_id': template_id
  //       },
  //       success: function (data) {
  //         console.log(data);
  //         if (data.length>0) {
  //           $('#defaultData').hide();

  //           for(var i=0;i<data.length;i++){
  //             html += '<td>'+(i+1)+'</td><td>'+data[i].treatment_id+'</td><td>'+data[i].target_num+'</td><td>'+data[i].customer_price+'</td><td>'+data[i].total_target+'</td><td><a href="{{url('admin/editTempTarget')}}/'+dealer_id+'/'+template_id+'" class="btn btn-md btn-success add" id="add'+data[i].id+'"><b>Edit</b></a></td>';
  //           }
  //           $('#getDataByFilters').html(html);
  //         }else{
  //           $('#defaultData').show();
  //         }
  //       },
  //       error: function (data) {
  //         console.error(data);
  //       }
  //     });
  //   // $( "#form1" ).submit();
  // });

  $('#month').on('change', function() {
    $('#form').submit();
  });

  // $('#temp_id').on('change', function() {
  //   $('#form').submit();
  // });
</script>
@endsection