@extends('layouts.dashboard')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Daily Report
            <!-- <small>advanced tables</small> -->
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/sse') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Daily Report</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        @if(@$employees)
                            <h3 class="box-title">{{ get_name($employees[0]->dealer_id) }} Daily Attendance</h3>
                        @else
                            <h3 class="box-title">Dealers-Employee Attendance</h3>
                        @endif

                    </div><!-- /.box-header -->
                    <div class="row">
                        <div class="col-md-12">
                            <form method="GET" action="">
                                <div class="col-md-2">
                                    <input type="text" id="selectDate" name="selectDate"
                                        value="{{ (@$_GET['selectDate']) ? @$_GET['selectDate'] : date('Y-m-d') }}"
                                        placeholder="Select Date" value="" class="datePicker1 form-control"
                                        autocomplete="off" />

                                </div>
                                 @if(!isset($employees))
                                <div class="col-md-2">
                                    <select class="form-control" id="firm_id" name="firm_id">
                                        <option value="">Select Firm</option>
                                        @if (count($firms)>0)
                                        @foreach($firms as $firm)
                                            <option value="{{ @$firm->id }}" @if (@$firm->id == @$_GET['firm_id'])
                                                selected
                                            @endif>
                                                {{ $firm->short_code }}</option>
                                        @endforeach                                            
                                        @endif

                                    </select>
                                </div>

                          
                                @endif


                                <div class="col-md-4">
                                    <input class="btn btn-primary" type="submit" value="Submit">
                                    @if(!isset($employees))
                                    <a href="{{ url('sse/daily_attendance') }}" class="btn btn-info">Reset</a>
                                    @endif
                                    @if(@$employees)
                                        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
                                    @endif
                                </div>



                            </form>
                        </div>
                    </div>
                    <br>
                    <form action="" method="GET">
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="col-xs-6 col-md-6 pull-left">
                                </div>
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
                        @if(@$employees)
                            <table id="exa" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>S. No.</th>
                                        <th>Employee Name</th>
                                        <th>Present Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($employees) > 0)
                                        @foreach($employees as $key => $value)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ ($employees[$key]->name) }}</td>
                                                <td @if($employees[$key]->present_status == "Present")
                                                    style="background-color:#2ecc71;" @else
                                                    style="background-color:#e74c3c;" @endif>
                                                    {{ ($employees[$key]->present_status) }} </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>No Data Found</tr>
                                    @endif

                                </tbody>
                            </table>
                        @else
                            <table id="exa" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Center Name</th>
                                        <th>Total Employees</th>
                                        <th>Present Employees</th>
                                        <th>Absent Employees</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($result) > 0)
                                        @foreach($result as $key => $value)
                                            <tr>
                                            <td>{{$key+1}}</td>
                                                <td>{{ get_name($result[$key]->dealer_id) }}</td>
                                                <td>{{ count($result[$key]->total) }}</td>
                                                <td> {{ ($result[$key]->present) }} </td>
                                                <td> {{ count($result[$key]->total) - ($result[$key]->present) }}
                                                </td>
                                                <td> <a href="{{ url('sse/daily_attendance') }}/{{ $result[$key]->dealer_id }}"
                                                        class="btn btn-primary">view detail</a> </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>No Data Found</tr>
                                    @endif

                                </tbody>
                            </table>
                        @endif
                        <script>
                            $('#datepicker').datepicker({
                                autoclose: true,
                                format: 'yyyy-mm-dd',
                                endDate: '+0d'
                            });
                            $(document).on('click', '.datePicker1', function () {
                                $(this).datepicker({
                                    autoclose: true,
                                    format: "yyyy-mm-dd",
                                }).focus();
                            });
                        </script>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection