@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        Product Brands
        <!-- <small>advanced tables</small> -->
        </h1>
        <ol class="breadcrumb">
        <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Product Brands</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Product Brands</h3>
                        <a href="{{url('/admin/addProductBrand')}}" class="btn btn-success floatright">Add Brand</a>
                    </div><!-- /.box-header -->
                    <form action="" method="GET"> 
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="col-xs-6 col-md-6 pull-left"></div>
                                <!-- <div class="col-xs-4 col-md-4 pull-right">
                                    <div class="input-group ">
                                    <input type="text" class="form-control" name="search" placeholder="Search by name, email or mobile no." id="txtSearch">
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
                                    <th>Brand Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($result)>=1){
                                    $i=1;
                                    foreach($result as $value) { 
                                    ?>
                                    <tr>
                                        <td>{{ucwords($value->brand_name)}}</td>
                                        <td>@if($value->status == "1") Activate @else Deactivate @endif</td>
                                        <td>
                                            <a href="{{ url('/admin/editProductBrand/')}}/{{$value->id}}" class="btn btn-success">Edit</a>
                                            @if($value->status == "0")
                                            <a href="{{url('admin/statusProductBrand/activate')}}/{{$value->id}}" class="btn btn-success" onclick="return confirm('Are you sure want to Activate?')">Activate</a>
                                            @else
                                            <a href="{{url('admin/statusProductBrand/deactivate')}}/{{$value->id}}" class="btn btn-danger" onclick="return confirm('Are you sure want to Deactivate?')">Deactivate</a>
                                            @endif
                                        </td>
                                    </tr>   
                                    <?php
                                    $i++;
                                    }
                                } else{?>
                                <tr>
                                    <td colspan="2">No Record</td>                          
                                </tr>
                                <?php }?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Brand Name</th>
                                    <th>Status</th>
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
<script type="text/javascript">
setTimeout(function() {
  $('.alert').fadeOut('slow');
}, 3000);
</script> 
@endsection