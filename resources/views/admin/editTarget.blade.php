@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Target Management            <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('/admin/targets')}}"><i class="fa fa-dashboard"></i> Targets</a></li>
      <li class="active">Edit Target</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Target</h3>
            <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="targetForm" name="targetForm" method="POST" action="{{url('/admin/updateTarget')}}">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="target_id" id="target_id" value="{{$result->id}}">
                <div class="box-body">
                  <div class="form-group{{ $errors->has('month') ? ' has-error' : '' }}">
                    <label for="month">Select Month<span class="required-title">*</span></label>
                      <input type="text" id="month" name="month" placeholder="Select Month" value="{{$result->month}}" class="datePicker1 form-control" autocomplete="off" />
                    @if ($errors->has('month'))
                    <span class="help-block">
                      <strong>{{ $errors->first('month') }}</strong>
                    </span>
                    @endif
                  </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                      <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                      <select class="form-control required" id="dealer_id" name="dealer_id">
                        <option value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                        <option value="{{$dealer->dealer_id}}" @if($result->dealer_id == $dealer->dealer_id) {{ 'selected' }} @endif>{{ucwords($dealer->dealer_name)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('dealer_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('dealer_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group{{ $errors->has('model') ? ' has-error' : '' }}" id="modelSelecter">
                      <label for="model">Model<span class="required-title">*</span></label>
                      <select class="form-control required" id="model" name="model" >
                        <option value="">Select Model</option>
                        @foreach($models as $model) 
                        <option value="{{$model->id}}" @if($result->model_id == $model->id) {{ 'selected' }} @endif>{{$model->model_name}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('model'))
                      <span class="help-block">
                        <strong>{{ $errors->first('model') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
                <!-- <div class="form-group{{ $errors->has('totaltreatments') ? ' has-error' : '' }}">
                  <label for="totaltreatments">No. of Treatments<span class="required-title">*</span></label>
                  <input type="text" class="form-control required" value="{{ old('totaltreatments') }}" id="totaltreatments" name="totaltreatments" readonly>
                  @if ($errors->has('totaltreatments'))
                  <span class="help-block">
                    <strong>{{ $errors->first('totaltreatments') }}</strong>
                  </span>
                  @endif
                </div> -->
                <!-- <div class="form-group{{ $errors->has('treatmentsvalue') ? ' has-error' : '' }}">
                  <label for="treatmentsvalue">Treatments Value<span class="required-title">*</span></label>
                  <input type="text" class="form-control required" value="{{ old('treatmentsvalue') }}" id="treatmentsvalue" name="treatmentsvalue" readonly>
                  @if ($errors->has('treatmentsvalue'))
                  <span class="help-block">
                    <strong>{{ $errors->first('treatmentsvalue') }}</strong>
                  </span>
                  @endif
                </div> -->
                <div id ="treatments" name="treatments">
                  
                </div>

                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
        </div><!-- /.col -->
      </div><!-- /.row -->
    </section><!-- /.content -->
  </div><!-- /.content-wrapper --> 
<script>
$(document).on('click', '.datePicker1', function(){
  $(this).datepicker({
    autoclose: true,
    format: "yyyy-mm",
    startView: "months", 
    minViewMode: "months"
  }).focus();
});

$("#model").on("change",function() {
  var selectedModel = $("#model").val();
  var id = $('#target_id').val();
  $.ajax(
    {
      type: 'get',
      url: './getModelTreatments?target_id='+id ,
      data: {'modelData': selectedModel},
      success: function (data) {
        $('#treatments').empty();
        $('#treatments').append(data);
      },
      error: function (data) {
        console.error(data);
      }
    }
  );
});
</script>
@endsection