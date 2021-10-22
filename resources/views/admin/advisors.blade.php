@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Advisors
            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management <span class="active">({{get_name($dealer_id)}})</span></a></li>
            <li class="active">Advisors </li>
          </ol>
        </section>

        <div class="modal fade" id="myModal" role="dialog" style="z-index: inherit;">
          <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Incentive</h4>
              </div>
              <div class="modal-body">
                <div class=" ro-box box box-primary" style=" margin-top: 10px;">
                  <div class="box-body">
                    <form method="POST" action="{{url('/admin/addAdvisorIncentive')}}/{{$dealer_id}}">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="form-group{{ $errors->has('incentive') ? ' has-error' : '' }}">
                        <label for="incentive">Incentive<span class="required-title">*</span></label>
                        <input type="text" name="incentive" maxlength="3" OnKeypress="return isNumber(event)" class="form-control required" value="{{ old('incentive') }}" id="incentive" placeholder="Enter Incentive" required>
                        @if ($errors->has('incentive'))
                                <span class="help-block">
                                  <strong>{{ $errors->first('incentive') }}</strong>
                                </span>
                              @endif
                      </div>
                      <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Advisors List of <b>{{get_name($dealer_id)}}</b></h3>
                  <a href="{{url('/admin/addAdvisor')}}/{{$dealer_id}}" class="btn btn-success floatright">Add Advisor</a>
                  @if (count($result) > 0)
                  <button type="button" class="btn btn-info floatright" data-toggle="modal" data-target="#myModal" style="margin-right: 20px;">Add Incentive</button>
                  @endif
                  
                </div><!-- /.box-header -->
                <form action="" method="GET"> 
                  <div class="row">
                    <div class="col-xs-12 col-md-12">
                      <div class="col-xs-6 col-md-6 pull-left">
                      </div>
                      <div class="col-xs-4 col-md-4 pull-right">
                        <div class="input-group ">
                          <input type="text" class="form-control" name="search" placeholder="Search by name, PAN no. or mobile no." id="txtSearch">
                          <div class="input-group-btn">
                            <button class="btn btn-primary" type="submit" style="height:34px">
                              <span class="glyphicon glyphicon-search"></span>
                            </button>
                          </div>
                        </div>
                      </div>
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
                        <th>Name</th>
                        <th>Pan No.</th>
                        <th>Mobile No.</th>
                        <th>Department</th>
                        <th>Incentive</th>
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
                            <td>{{ucwords($value->name)}}</td>
                            <td>{{strtoupper($value->pan_no)}}</td>
                            <td>{{$value->mobile_no}}</td>
                            <td>{{ucwords($value->department_name)}}</td>
                            @if(!empty(getAdvisorPercentage($value->id)))
                            <td>{{getAdvisorPercentage($value->id)}}%</td>
                            @else
                            <td>{{'-'}}</td>
                            @endif
                            <td>@if($value->status==1) Activate @else Deactivate @endif</td>
                            <td>
                                  @if($value->status == "1")
                                    <a href="{{ url('/admin/statusAdvisor/deactivate/')}}/{{$dealer_id}}/{{$value->id}}"  onclick="return confirm('Are you sure want to deactivate?')" class="btn btn-warning">Deactivate</a> 
                                  @else 
                                    <a href="{{ url('/admin/statusAdvisor/activate/')}}/{{$dealer_id}}/{{$value->id}}" onclick="return confirm('Are you sure want to activate?')" class="btn btn-info" style="width: 92.16px">Activate</a>
                                  @endif
                                  <a href="{{ url('/admin/editAdvisor/')}}/{{$dealer_id}}/{{$value->id}}" class="btn btn-success">Edit</a>
                                  {{--  <a href="{{ url('/admin/advisor_percentage_history/')}}/{{$dealer_id}}/{{$value->id}}" class="btn btn-danger" title="Percentage History"><i class="fa fa-percent"></i> History</a>  --}}
                                  <!-- <a href="{{ url('/admin/statusAdvisor/delete/')}}/{{$dealer_id}}/{{$value->id}}" onclick="return confirm('Are you sure want to delete?')" class="btn btn-danger">Delete</a> -->
                            </td>
                          </tr>   
                      <?php
                        $i++;
                        }
                       }else{?>
                        <tr>
                          <td colspan="7">No Record</td>                          
                        </tr>
                      <?php }?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Name</th>
                        <th>Pan No.</th>
                        <th>Mobile No.</th>
                        <th>Department</th>
                        <th>Incentive</th>
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

      <script>
        function isNumber(evt, element) {
          var charCode = (evt.which) ? evt.which : event.keyCode
          if (
          (charCode != 45 || $(element).val().indexOf('-') != -1) && // “-” CHECK MINUS, AND ONLY ONE.
          /*(charCode != 46 || $(element).val().indexOf('.') != -1) && */ // “.” CHECK DOT, AND ONLY ONE.
          (charCode < 48 || charCode > 57))
          return false;
          else {
            return true;
          }
        }
      </script>
@endsection