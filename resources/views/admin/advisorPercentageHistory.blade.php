@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Incentive History
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-dashboard"></i>Dealer Management</a></li>
      <li><a href="{{url('/admin/advisors')}}/{{$dealer_id}}"><i class="fa fa-users"></i>Advisors({{get_dealer_name($dealer_id)}})</a></li>
      <li class="active">Incentive History</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Incentive History Of <b>{{advisor_name($advisor_id)}}</b> Of <b>{{get_dealer_name($dealer_id)}}</b></h3>
            <a href="{{url('/admin/addAdvisorPercentage')}}/{{$dealer_id}}/{{$advisor_id}}" class="btn btn-success floatright">Add Incentive</a>
          </div>

          <div class="box-body table-responsive">
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
                  <th>Incentive</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                
                <?php if(count($percentages)>=1){
                  $i=1;
                  foreach($percentages as $percentage) { 
                    ?>
                    <tr>
                      <td>{{$i++}}</td>                            
                      <td>{{$percentage->advisor_share}}</td>
                      <td>{{$percentage->created_at}}</td>
                    </tr>   
                    <?php
                    // $i++;
                  }
                }else{?>
                <tr>
                  <td colspan="3">No Record</td>                          
                </tr>
                <?php }?>
              </tbody>
              <tfoot>
                <tr>
                  <th>Sr. No.</th>
                  <th>Incentive</th>
                  <th>Date</th>
                </tr>
              </tfoot>
            </table>
            <?php echo $percentages->links(); ?>
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