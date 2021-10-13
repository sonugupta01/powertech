@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">

        <!-- Content Header (Page header) -->

        <section class="content-header">

          <h1>

            Dealer Management

            <!-- <small>advanced tables</small> -->

          </h1>

          <ol class="breadcrumb">

            <li><a href="{{url('/asm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>

            <li class="active">Dealer Management</li>

          </ol>

        </section>



        <!-- Main content -->

        <section class="content">

          <div class="row">

            <div class="col-xs-12">

              <div class="box">

                <div class="box-header">

                  <h3 class="box-title">Dealers List</h3>

                  <!-- <a href="{{url('/asm/addDealer')}}" class="btn btn-success floatright">Add Dealer</a> -->

                </div><!-- /.box-header -->

                <form action="" method="GET"> 

                  <div class="row">

                    <div class="col-xs-12 col-md-12">

                      <div class="col-xs-12 col-sm-6 col-md-6 pull-left">

                        <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$dealers->total()}}</b>

                      </div>

                      

                      <div class="col-xs-12 col-sm-6 col-md-4 pull-right">

                        <div class="input-group from-group">

                          <input type="text" class="form-control" name="search" placeholder="Search by name, email or mobile no." id="txtSearch">

                          <div class="input-group-btn">

                            <button class="btn btn-primary" type="submit">

                              <span class="glyphicon glyphicon-search"></span>

                            </button>

                          </div>

                        </div>

                      </div>

                    </div>

                  </div>

                </form>

                <div class="box-body " style="overflow-x:auto;">

                  @if(Session::has('error'))

                    <div class="alert alert-danger">{{ Session::get('error') }}</div>

                  @endif

                  @if(Session::has('success'))

                    <div class="alert alert-success">{{ Session::get('success') }}</div>

                  @endif

                  <div class="table-responsive">                  

                    <table id="exa" class="table table-bordered table-striped">

                      <thead>

                        <tr>

                          <th>Name</th>

                          <th>Email</th>

                          <th>Mobile No.</th>

                          <th>Status</th> 

                          <!-- <th>Action</th> -->

                        </tr>

                      </thead>

                      <tbody>

                        <?php if(count($dealers)>=1){

                          $i=1;

                          foreach($dealers as $value) { 



                        ?>

                            <tr>

                              <td>{{ucwords($value->name)}}</td>

                              <td class="email-id">{{$value->email}}</td>

                              <td>{{$value->mobile_no}}</td>

                              <td>@if($value->status == "1") Activate @else Deactivate @endif</td> 

                              <td>

                                  @if($value->status == "1")

                                    <!-- <a href="{{ url('/asm/statusDealer/deactivate/')}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-danger">Deactivate</a> --> 

                                  @else 

                                    <!-- <a href="{{ url('/asm/statusDealer/activate/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.15px">Activate</a> -->

                                  @endif 

                                    <!-- <a href="{{ url('/asm/editDealer/')}}/{{$value->id}}" class="btn btn-success">Edit</a> -->

                                    <!-- <a href="{{ asset('images')}}/{{ $value->qrcode}}" class="btn btn-primary" download="">QR Code</a> -->

                                    <!--<a href="{{ url('/admin/statusDealer/delete/')}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a>-->

                              </td>

                            </tr>   

                        <?php

                          $i++;

                          }

                         }else{?>

                          <tr>

                            <td colspan="6">No Record</td>                          

                          </tr>

                        <?php }?>

                      </tbody>

                      <tfoot>

                        <tr>

                          <th>Name</th>

                          <th>Email</th>

                          <th>Mobile No.</th>

                          <!-- <th>Active Advisors</th> -->

                          <th>Status</th> 

                          <!-- <th>Action</th> -->

                          <!-- <th>View</th> -->

                        </tr>

                      </tfoot>

                    </table>

                  </div>



                  <?php echo $dealers->links(); ?>

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