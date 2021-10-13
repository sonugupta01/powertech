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
            <li class="active">Targets</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Target List</h3>
                  <a href="{{url('/admin/addTarget')}}" class="btn btn-success floatright">Add Target</a>
                </div><!-- /.box-header -->
                <form action="" method="GET"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-12">
                      <div class="col-xs-6 col-md-6 pull-left">
                      </div>
                      <!-- <div class="col-xs-4 col-md-4 pull-right">
                        <div class="input-group ">
                          <input type="text" class="form-control" name="search" placeholder="Search by name, or mobile no. or Designation" id="txtSearch">
                          <div class="input-group-btn">
                            <button class="btn btn-primary" type="submit">
                              <span class="glyphicon glyphicon-search"></span>
                            </button>
                          </div>
                        </div>
                      </div> -->
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
                  <table id="exa" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Sr. No.</th>
                        <th>Dealer</th>
                        <!-- <th>Template</th> -->
                        <th>Total Target</th>
                        <th>Achieve Target</th>
                        <th>Pending</th>
                        <th>Total Price</th>
                        <th>Achieve Price</th>
                        <th>Balance</th>
                        <th>Month</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($result)>=1){
                        $i=1;
                        $s = $result->perPage() * ($result->currentPage() - 1);
                        foreach($result as $value) { 
                      ?>
                          <tr>
                            <td>{{++$s}}</td>

                            <td>{{get_dealer_name($value->dealer_id)}}</td>
                            {{-- <td>{{get_template_name($value->template_id)}}</td>  --}}
                            <td>{{$value->total_treatments}}</td>
                            <td>{{$value->done_treatments}}</td>
                            <td>{{$pending = $value->total_treatments - $value->done_treatments}}</td>
                            <td>{{$value->total_treatments_price}}</td>
                            <td>{{$value->done_treatments_price}}</td>
                            <td>{{ $balance_target = $value->total_treatments_price - $value->done_treatments_price}}</td>
                            <td>{{$value->month}}</td>
                            <td>
                              <a href="{{ url('/admin/targetListing')}}/{{$value->id}}" class="btn btn-success"><span class="glyphicon glyphicon-chevron-right"></span></a>
                            {{-- <a href="{{ url('/admin/statusTarget/delete')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> --}}
                            </td> 
                          </tr>   
                      <?php
                        $i++;
                        }
                       }else{?>
                        <tr>
                          <td colspan="5">No Record</td>                          
                        </tr>
                      <?php }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr. No.</th>
                        <th>Dealer</th>
                        <!-- <th>Template</th> -->
                        <th>Total Target</th>
                        <th>Achieve Target</th>
                        <th>Pending</th>
                        <th>Total Price</th>
                        <th>Achieve Price</th>
                        <th>Balance</th>
                        <th>Month</th>
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
</script>  
@endsection