@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Products
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/treatments')}}"><i class="fa fa-wrench"></i> Treatments</a></li>
      <li><a href="{{url('/admin/editTreatment')}}/{{$treatment_id}}"><i class="fa fa-users"></i> Treatment <span class="active">({{get_treatment_name($treatment_id)}})</span></a></li>
      <li class="active">Treatment Products</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Products List of Treatment <b>{{get_treatment_name($treatment_id)}}</b></h3>
            <a href="{{url('/admin/addTreatmentProduct')}}/{{$treatment_id}}" class="btn btn-success floatright">Add Treatment Product</a>
          </div><!-- /.box-header -->
          <form action="" method="GET"> 
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-xs-12 col-sm-6 col-md-6 pull-left">
                  <b style="margin-top: 10px; font-size: 12px; display: block;">Total Records: {{$result->total()}}</b>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 pull-right">
                  <div class="input-group from-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name" id="txtSearch">
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
              <table id="productsTbl" class="table table-bordered table-striped" >
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Name</th>
                    <th>Quantity(Unit of Measurement)</th>
                    <th>Price</th>
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
                        <td>{{$i}}</td>
                        <td>{{get_product_name(ucwords($value->pro_id))}}</td>
                        <td>{{$value->quantity}}@if($value->uom==1){{'Litre'}}@elseif($value->uom==2){{'ML'}}@elseif($value->uom==3){{'Pcs.'}}@elseif($value->uom==4){{'Gms.'}}@endif</td>
                        <td class="product_price" name="product_price">{{$value->price}}</td>
                        <td>@if($value->status == "1") Activate @else Deactivate @endif</td> 
                        <td>
                            @if($value->status == "1")
                              <a href="{{ url('/admin/statusTreatmentProduct/deactivate/')}}/{{$treatment_id}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-danger">Deactivate</a> 
                            @else 
                              <a href="{{ url('/admin/statusTreatmentProduct/activate/')}}/{{$treatment_id}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info">Activate</a>
                            @endif 
                              <a href="{{ url('/admin/editTreatmentProduct/')}}/{{$treatment_id}}/{{$value->id}}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                              <a href="{{ url('/admin/statusTreatmentProduct/delete/')}}/{{$treatment_id}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-warning"><i class="fa fa-trash"></i></a>
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
                    <th></th>
                    <th></th>
                    <th style="text-align: right;">Total Price =</th>
                    <th class="totalPrice" name="totalPrice"></th>
                    <th></th> 
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <a href="{{url('/admin/editTreatment')}}/{{$treatment_id}}" class="btn btn-md btn-warning pull-left"><span class="glyphicon glyphicon-chevron-left"></span><b>Back</b></a>
            <a href="{{url('/admin/updateTreatmentPrice')}}/{{$treatment_id}}" class="btn btn-md btn-success pull-right"><b>Next</b><span class="glyphicon glyphicon-chevron-right"></span></a>
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


var $tblrows = $("#productsTbl tbody tr");
$tblrows.each(function (index) {
  var $tblrow = $(this);
  var price = $tblrow.find("[name*=product_price]").text();
  var sum = 0;
  $(".product_price").each(function() {
    // alert("yes");
      var value = $(this).text();
      // alert();
      if(!isNaN(value) && value.length != 0) {
          sum += parseFloat(value);
      }
  });

  $(".totalPrice").text(sum.toFixed(2)); 

});

</script>  
@endsection