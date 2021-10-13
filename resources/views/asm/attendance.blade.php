@extends('layouts.dashboard')
@section('content')
  
  <div class="content-wrapper">
    @if(Session::has('error'))
    <div class="alert alert-danger">{{ Session::get('error') }}</div>
    @endif
    @if(Session::has('success'))
    <div class="alert alert-success">{{ Session::get('success') }}</div>
    @endif
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Attendance
        <!-- <small>advanced tables</small> -->
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('/asm') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Attendance</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- <div class="tab-button-outer">
          <ul id="tab-button" class="tab-button1">
            <li>
              <a href="#tab01">Attendance</a>
            </li>
          </ul>
        </div>
        <div class="tab-select-outer">
          <select id="tab-select">
            <option value="#tab01">Attendance</option>
          </select>
        </div> -->
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header" style="text-align: center; margin: 0 auto;">
              <h3 class="box-title">Attendance Report</h3>
            </div><!-- /.box-header -->
            <div id="tab01" class="tab-contents">
              <form action="" class="" method="GET">
                <div class="row">
                  
                  <div class="col-xs-6 col-md-6">
                    <label>Dealer</label>
                    <select class="form-control" name="dealer" id="dealer_id">
                      <option value="">Select Dealer</option>
                      @foreach(@$dealers as $value)
                      <div class="form-group report-field col-md-3 ol-sm-3 ol-xs-12">
                        
                        <option {{ @$value == @$_GET['dealer'] ? 'selected' : '' }} value="{{ $value }}">{{ get_name($value) }}
                        </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group report-field col-md-3 ol-sm-3 ol-xs-12">
                      <label>Employees</label>
                      <select class="form-control" name="employee" id="employee">
                        <option value="">Select Employee</option>
                        @foreach(@$employees as $employee)
                        <option {{ @$emp == $employee->id ? 'selected' : '' }} value="{{ $employee->id }}">
                          {{ $employee->name }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="form-group report-field col-md-1 col-sm-1 ol-xs-12"
                      style="margin-top: 24px; text-align: center;"><b>Or</b></div>
                      <div class="form-group report-field col-md-2 ol-sm-2 ol-xs-12">
                        <label>Employee Codes</label>
                        <select class="form-control" name="employeecode" id="employeecode">
                          <option value="">Select Code</option>
                          @foreach(@$empcodes as $code)
                          <option {{ @$empcode == $code->user_id ? 'selected' : '' }} value="{{ $code->user_id }}">
                            {{ $code->emp_code }}</option>
                            @endforeach
                          </select>
                        </div>
                        <!-- <div class="col-xs-12 col-sm-6 col-md-4">
                          <label>Employee </label>
                          <div class="input-group from-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by Employee name or Employee code" id="txtSearch" value="{{ old('search') }}">
                            <div class="input-group-btn">
                              <button class="btn btn-primary" type="submit" style="height: 34px;">
                                <span class="glyphicon glyphicon-search"></span>
                              </button>
                            </div>
                          </div>
                        </div> -->
                        <div class="orm-group report-field col-md-2 col-sm-2 ol-xs-12">
                          <label>Start Date</label>
                          <input type="text" id="start_date" name="start_date"  placeholder="Start Date"
                          value="{{( @$start_date ) ? $start_date : old('start_date') }}" class="datePicker form-control" autocomplete="off" />
                        </div>
                        <div class="orm-group report-field col-md-1 col-sm-1 ol-xs-12" >
                         <label>
                           <input type="checkbox" value="" id="end_date_check" @if(!empty($_GET['end_date'])) checked @endif> Monthly Report 
                          </label>
                        </div>
                        <script>
                          $('#end_date_check').click(function () {

                          if($(this).prop("checked") == true){
                          $('#end_date_input').show(0);
                          }
                          else if($(this).prop("checked") == false){
                            $('#end_date').val('');
                         $('#end_date_input').hide(0);
                          }

                          });
                          </script>

                        <div class="orm-group report-field col-md-2 col-sm-2 ol-xs-12" id="end_date_input" @if(empty($_GET['end_date']))style="display: none;"@endif>
                          <label>End Date</label>
                          <input type="text" id="end_date" name="end_date"  placeholder="End Date" value="{{( @$end_date ) ? $end_date : old('end_date') }}"
                          class="datePicker form-control" autocomplete="off" />
                        </div>
                        <div class="form-group report-field col-md-1 col-sm-1 ol-xs-12" style="margin-top: 24px;">
                          <input class="btn btn-primary btn-div" type="submit" value="Submit">
                        </div>
                     

                        <div class="form-group report-field col-md-1 col-sm-1 ol-xs-12" style="margin-top: 24px;">
                          <a href="{{url('asm/attendance')}}" class="btn btn-primary">Reset</a> 
                        </div>
                      </div>
                      
                    </div>
                  </div>
                </form>
                       <form action="{{url('asm/attendance')}}" method="GET" id="get_employees">
                    <input type="hidden" value="" id="dealer_id_hidden" name="dealer">
                    <input type="hidden" value="" id="employee_hidden" name="employee">
                    <input type="hidden" value="" id="employeecode_hidden" name="employeecode">
                    <input type="hidden" value="" id="start_date_hidden" name="start_date">
                    <input type="hidden" value="" id="end_date_hidden" name="end_date">
                  </form>
                    <script>
                      $("#dealer_id").change(function () {
                        
                        var dealer_id = $('#dealer_id').val();
                        var employee = $('#employee').val();
                        var employeecode = $('#employeecode').val();
                        var start_date = $('#start_date').val();
                        var end_date = $('#end_date').val();

                        $('#dealer_id_hidden').val(dealer_id);
                        $('#employee_hidden').val(employee);
                        $('#employeecode_hidden').val(employeecode);
                        $('#start_date_hidden').val(start_date);
                        $('#end_date_hidden').val(end_date);
                        $('#get_employees').submit();
                      });
                    </script>
                <div class="box-body" style="overflow: auto;">
                  <div class="table-resposive">
                    <table class="table table-bordered table-striped report-table">
                      <thead>
                        <tr>
                          @if ($view==1)
                          <th colspan="4" style="font-size: 12px;">Total Records: {{ count(@$result) }}
                          </th>
                          
                          <th><button id="download" class="btn btn-success"
                            style="margin: 0 auto; display: inline-block;">Download</button></th>
                            
                            @elseif($view==2)
                            <th colspan="7" style="font-size: 12px;">Total Records: {{ count(@$result) }}
                            </th>
                            
                            <th style="text-align: center;"><button id="download" class="btn btn-success"
                              style="margin: 0 auto; display: inline-block;">Download</button>
                              
                              @if (@$result[0][0])
                              
                              <a href="{{ url('asm/view_attendance') }}/{{ $result[0][0]->user_id }}" target="_blank" class="btn btn-primary">
                                view
                              </a>                                  
                              @endif
                              
                            </th>
                            
                            @else
                            @if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date']) ) 
                              @if($_GET['start_date'] != $_GET['end_date'])
                              <th colspan="10" style="font-size: 12px;">Total Records: {{ count(@$result) }} </th>
                              @else
                               <th colspan="8" style="font-size: 12px;">Total Records: {{ count(@$result) }} </th>
                              @endif  
                            

                                @else
                          <th colspan="8" style="font-size: 12px;">Total Records: {{ count(@$result) }} </th>
                            @endif
 
                            
                            <th><button id="download" class="btn btn-success"
                              style="margin: 0 auto; display: inline-block;">Download</button>
                              
                              
                            </th>
                            
                            @endif
                            
                            
                            <!-- <th><button id="downloadHVT" class="btn btn-success" style="margin: 0 auto; display: block;">Download Dealer Wise</button></th> -->
                          </tr>
                          <tr>

                            @if ($view==1)

                            <th>S. No.</th>
                            <th>User</th>
                            <th>Emp Code</th>
                            <th>Dealer</th>
                            <th>Hours This Month</th>

                            @elseif($view == 2)

                            <th>Date</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>User</th>
                            <th>Emp Code</th>
                            <th>Dealer</th>
                            <th>This Dealer Hours</th>
                            <th>Total Hours Today</th>

                            @else
                            
                            @if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) 
                            @if($_GET['start_date'] != $_GET['end_date'])
                                   <th>Sr. No.</th>
                            <th>Emp Name</th>
                            <th>Emp Code</th>
                            <th>Dealer</th>
                            <th>Total Hours</th>
                            <th>Full Days</th>
                            <th>Half Days</th>
                            <th>Absent Days</th>
                            <th>Attendance Not Marked</th>
                            
                            <th>Sales Report Not Filled</th>
                            <th>Action</th>

                              @else
                            <th>Date</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>User</th>
                            <th>Emp Code</th>
                            <th>Dealer</th>
                            <th>This Dealer Hours</th>
                            <th>Total Hours Today</th>
                            <th>Action</th>

                            @endif
                     
                            @else

                            <th>Date</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>User</th>
                            <th>Emp Code</th>
                            <th>Dealer</th>
                            <th>This Dealer Hours</th>
                            <th>Total Hours Today</th>
                            <th>Action</th>

                            @endif

                            @endif                        
                            
                          </tr>
                          
                        </thead>
                        
                        
                        @if ($view==1)
                        <tbody>
                          <?php if (count($result) >= 1) { ?>
                            @foreach(@$result as $key => $value)
                            <tr>
                              <td>{{$key+1}}</td>
                              <td>{{ get_name($value->user_id) }}</td>
                              <td>{{ get_emp_code($value->user_id) }}</td>
                              <td>{{ get_name($value->dealer_id) }}</td>
                              
                              <td><?php 
                                $month = ((@$_GET['selectMonth']) ? date('m',strtotime(@$_GET['selectMonth'])) : date('m'));
                                
                                @$mydata1 = month_hours_date($value->user_id,$start_date,$end_date); 
                                
                                ?>
                                {{ @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes' }}
                                <a href="{{ url('asm/view_attendance') }}/{{ $value->user_id }}" target="_blank" class="btn btn-primary">
                                  view
                                </a>
                              </td>
                            </tr>
                            @endforeach
                            <?php } else { ?>
                              <tr>
                                <td colspan="9">No Record</td>
                              </tr>
                              <?php } ?>
                        </tbody>

                        @elseif($view == 2)
                        <tbody>
                              <?php
                              if (count($result) > 0) {
                                
                                ?>
                                @foreach ($result as $key1 => $value1)
                                <?php  $row = count($result[$key1])?>
                                @foreach ($result[$key1] as $key => $value)
                                <tr>
                                  @if ($key == 0)
                                  <td rowspan="{{$row}}" style="vertical-align: middle">{{ date('d M, Y', strtotime($result[$key1][0]->date)) }}</td>                    
                                  @endif
                                  
                                  @if (isset($value->user_id))
                                   <td>{{ (@$value->in_time) ? @$value->in_time : "00:00:00" }}</td>
                                      <td>{{ (@$value->out_time) ? @$value->out_time : "00:00:00" }}</td>
                                  <td>{{ get_name($value->user_id) }}</td>
                                  <td>{{ get_emp_code($value->user_id) }}</td>
                                  <td>{{ get_name($value->dealer_id) }}</td>
                                  <td>
                                    <?php
                                    $timeFirst = strtotime($value->in_time);
                                    $timeSecond = strtotime($value->out_time);
                                    if(!empty($timeFirst) && !empty($timeSecond)){
                                      @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                      echo  @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes'  ;                   
                                    }
                                    // else{
                                    //   $notmarked += 1; 
                                    //   echo 'Attendance Not Marked';                      
                                    // }
                                    ?>
                                  </td>
                                  
                                  @if ($row > 1)
                                  @if ($key == 0)
                                  <td rowspan="{{$row}}" style="vertical-align: middle">
                                    <?php
                                    if(!empty($timeFirst) && !empty($timeSecond)){
                                      @$data_total_hours = day_hours($value->user_id, $value->date);
                                      echo @$data_total_hours['hours'] . ' hours ' . 
                                      @$data_total_hours['minutes'] . ' minutes';
                                      
                                      
                                      
                                    }else{
                                      echo 'Attendance Not Marked';
                                    }
                                    
                                    ?>
                                  </td>                   
                                  @endif
                                  @else
                                  <td>
                                    <?php
                                    if(!empty($timeFirst) && !empty($timeSecond)){
                                      @$data_total_hours = day_hours($value->user_id, $value->date);
                                      echo @$data_total_hours['hours'] . ' hours ' .
                                      @$data_total_hours['minutes'] . ' minutes';
                                    }else{
                                      echo 'Attendance Not Marked';
                                    }
                                    
                                    
                                    ?>
                                  </td> 
                                  @endif
                                  
                                  @else
                                  <td colspan="6" style="text-align: center;background-color:#dd4b39;">Absent</td>
                                  @endif
                                </tr>   
                                @endforeach 
                                
                                @endforeach
                                <?php } else { ?>
                                  <tr>
                                    <td colspan="9">No Record</td>
                                  </tr>
                                  <?php } ?>
                        </tbody>
                        
                        @else
                              @if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) 
                                    @if($_GET['start_date'] != $_GET['end_date'])
                                 <tbody>
                                  <?php if (count($result) >= 1) { ?>
                                    @foreach(@$result as $key => $value)
                                    <tr>
                                     <td>{{$key+1}}</td>
                                      <td>{{ get_name($value->user_id) }}</td>
                                      <td>{{ get_emp_code($value->user_id) }}</td>
                                      <td>{{ get_name($value->dealer_id) }}</td>
                                      <td><?php $total_hours = total_hours_range($value->user_id,$_GET['start_date'],$_GET['end_date']) ;
                                      echo $total_hours['hours'].'hours '.$total_hours['minutes'].'minutes ';
                                      ?> </td>
                                      <td>
                                        {{full_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date'])}} days
                                      </td>
                                      <td>
                                        {{half_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date'])}} days
                                      </td>
                                      <td>
                                        <?php
                                        $absent = strtok(absent_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date']), '-');
                                        $three = substr(absent_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date']), strrpos(absent_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date']), '-' )+1);
                                        ?>
                                        {{$three+$absent." days"."(".$absent." Absent + ".$three." Less Than 3 Hours ".")"}} 
                                      </td>
                                      </td>
                                      <td>
                                        {{notmarked_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date'])}} days
                                      </td>
                                      <td>
                                        <?php 
                                          $role = getUserRole($value->user_id);
                                          if ($role ==3) {
                                        echo salesreportnotfilled_days_in_range($value->user_id,$_GET['start_date'],$_GET['end_date']) ;
                                            
                                          }else{
                                            echo "N/A";
                                          }
                                          ?>
                                      </td>
                                      <td>
                                        <a href="{{ url('asm/view_attendance') }}/{{ $value->user_id }}" target="_blank" class="btn btn-primary">
                                          view
                                        </a>
                                      </td>
                                    </tr>
                                    @endforeach
                                    <?php } else { ?>
                                      <tr>
                                        <td colspan="9">No Record</td>
                                      </tr>
                                      <?php } ?>
                                    </tbody>
                                    @else

                                <tbody>
                                  <?php if (count($result) >= 1) { ?>
                                    @foreach(@$result as $value)
                                    <tr>
                                      <td>{{ date('d M, Y', strtotime($value->date)) }}</td>
                                      <td>{{ (@$value->in_time) ? @$value->in_time : "00:00:00" }}</td>
                                      <td>{{ (@$value->out_time) ? @$value->out_time : "00:00:00" }}</td>
                                      <td>{{ get_name($value->user_id) }}</td>
                                      <td>{{ get_emp_code($value->user_id) }}</td>
                                      <td>{{ get_name($value->dealer_id) }}</td>
                                      <td><?php
                                        $timeFirst = strtotime($value->in_time);
                                        $timeSecond = strtotime($value->out_time);
                                        
                                        if (!empty($value->in_time) && !empty($value->out_time) ) {
                                          @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                          
                                          echo @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes' ;
                                        }else{
                                          echo '0 hours ' .'0 minutes' ;
                                          
                                        }
                                        ?>
                                        
                                      </td>
                                      
                                      <td>
                                        <?php
                                        @$data_total_hours = day_hours($value->user_id, $value->date);
                                        echo @$data_total_hours['hours'] . ' hours ' .
                                        @$data_total_hours['minutes'] . ' minutes';
                                        ?>
                                      </td>
                                      
                                      <td>
                                        <a href="{{ url('asm/view_attendance') }}/{{ $value->user_id }}" target="_blank" class="btn btn-primary">
                                          view
                                        </a>
                                      </td>
                                    </tr>
                                    @endforeach
                                    <?php } else { ?>
                                      <tr>
                                        <td colspan="9">No Record</td>
                                      </tr>
                                      <?php } ?>
                                    </tbody>
                                    @endif
                       
                                @else
                                   
                                <tbody>
                                  <?php if (count($result) >= 1) { ?>
                                    @foreach(@$result as $value)
                                    <tr>
                                      <td>{{ date('d M, Y', strtotime($value->date)) }}</td>
                                      <td>{{ (@$value->in_time) ? @$value->in_time : "00:00:00" }}</td>
                                      <td>{{ (@$value->out_time) ? @$value->out_time : "00:00:00" }}</td>
                                      <td>{{ get_name($value->user_id) }}</td>
                                      <td>{{ get_emp_code($value->user_id) }}</td>
                                      <td>{{ get_name($value->dealer_id) }}</td>
                                      <td><?php
                                        $timeFirst = strtotime($value->in_time);
                                        $timeSecond = strtotime($value->out_time);
                                        
                                        if (!empty($value->in_time) && !empty($value->out_time) ) {
                                          @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                          
                                          echo @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes' ;
                                        }else{
                                          echo '0 hours ' .'0 minutes' ;
                                          
                                        }
                                        ?>
                                        
                                      </td>
                                      
                                      <td>
                                        <?php
                                        @$data_total_hours = day_hours($value->user_id, $value->date);
                                        echo @$data_total_hours['hours'] . ' hours ' .
                                        @$data_total_hours['minutes'] . ' minutes';
                                        ?>
                                      </td>
                                      
                                      <td>
                                        <a href="{{ url('asm/view_attendance') }}/{{ $value->user_id }}" target="_blank" class="btn btn-primary">
                                          view
                                        </a>
                                      </td>
                                    </tr>
                                    @endforeach
                                    <?php } else { ?>
                                      <tr>
                                        <td colspan="9">No Record</td>
                                      </tr>
                                      <?php } ?>
                                    </tbody>

                                @endif
                              

                                    
                                    @endif
                                    
                                    
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div><!-- /.box -->
                        </div><!-- /.col -->
                      </div><!-- /.row -->
                    </section><!-- /.content -->
                  </div><!-- /.content-wrapper -->
                  
                  <form method="get" id="form1" action="{{ url('/asm/attendance') }}">
                    <input type="hidden" name="start_date" value="{{ @$start_date }}">
                    <input type="hidden" name="end_date" value="{{ @$end_date }}">
                    <input type="hidden" name="dealer" value="{{ @$dealer }}">
                    <input type="hidden" name="employee" value="{{ @$emp }}">
                    <input type="hidden" name="employeecode" value="{{ @$empcode }}">
                    <input type="hidden" name="download" value="yes">
                  </form>
                  
                  <script type="text/javascript">
                    $(document).on('click', '#download', function () {
                      $('#form1').submit();
                    });
                    
                    $(document).on('click', '.datePicker', function () {
                      $(this).datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        endDate: '+0d'
                      }).focus();
                    });
                    
                    $(document).on('click', '.start_date', function () {
                      $(this).datepicker({
                        autoclose: true,
                        format: "yyyy-mm-dd",
                        startView: "months",
                        minViewMode: "months",
                      }).focus();
                    });
                    
                    $(function () {
                      var $tabButtonItem = $('#tab-button li'),
                      $tabSelect = $('#tab-select'),
                      $tabContents = $('.tab-contents'),
                      activeClass = 'is-active';
                      $tabButtonItem.first().addClass(activeClass);
                      $tabContents.not(':first').hide();
                      $tabButtonItem.find('a').on('click', function (e) {
                        var target = $(this).attr('href');
                        $tabButtonItem.removeClass(activeClass);
                        $(this).parent().addClass(activeClass);
                        $tabSelect.val(target);
                        $tabContents.hide();
                        $(target).show();
                        e.preventDefault();
                      });
                      
                      $tabSelect.on('change', function () {
                        var target = $(this).val(),
                        targetSelectNum = $(this).prop('selectedIndex');
                        $tabButtonItem.removeClass(activeClass);
                        $tabButtonItem.eq(targetSelectNum).addClass(activeClass);
                        $tabContents.hide();
                        $(target).show();
                      });
                    });
                  </script>
                  @endsection