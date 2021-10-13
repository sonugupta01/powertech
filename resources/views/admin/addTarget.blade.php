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
      <li class="active">Add Target</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Target</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            @if(Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            <form role="form" id="targetForm" name="targetForm" method="POST" action="{{url('/admin/insertTarget')}}">
              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <div class="box-body">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group{{ $errors->has('dealer_id') ? ' has-error' : '' }}">
                      <label for="dealer_id">Dealer<span class="required-title">*</span></label>
                      <select class="form-control required" id="dealer_id" name="dealer_id">
                        <option value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                        <option value="{{$dealer->dealer_id}}">{{ucwords($dealer->dealer_name)}}</option>
                        @endforeach
                      </select>
                      @if ($errors->has('dealer_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('dealer_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div>
                  <!-- <div class="col-md-6">
                    <div class="form-group{{ $errors->has('template_id') ? ' has-error' : '' }}" id="templateSelector">
                      <label for="template_id">Templates<span class="required-title">*</span></label>
                      <select class="form-control required" id="template_id" name="template_id" >
                        <option value="">Select Template</option>
                      </select>
                      @if ($errors->has('template_id'))
                      <span class="help-block">
                        <strong>{{ $errors->first('template_id') }}</strong>
                      </span>
                      @endif
                    </div>
                  </div> -->
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

                <div id ="template" name="template">
                  
                </div>

                <!-- /.box-body -->

                <!-- <div class="box-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div> -->
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

$("#dealer_id").on("change",function() {
  var selectedDealer = $("#dealer_id").val();
  var html ="";
  $.ajax({
      type: 'GET',
      url: './getDealerTemplates',
      data: {'dealerData': selectedDealer},
      success: function (data) {
        if (data.length>0) {
          $('#template').empty();
          html += '<table class="table table-bordered table-hover" id="treatmentsTbl">';
          html += '<thead><tr><td><b>Template Name</b></td><td><b>Action</b></td></tr></thead><tbody>';
          for(var i=0;i<data.length;i++){
            html += '<tr>';
            html += '<td>'+data[i].tempName+'</td><td><a href="{{url('admin/addTempTarget')}}/'+selectedDealer+'/'+data[i].tempId+'" name="add[]" class="btn btn-md btn-success add" id="add'+data[i].id+'"><b>Add Target</b></a></td></tr>';
          }
          html += '</tbody></table>';
        } else{
          html += '<p>No Record Found.</p>'
        }
        $('#template').html(html);        
        // console.log(html);
        // $('#template').append(html);
      },
      error: function (data) {
        console.error(data);
      }
    });
});

// $("#model").on("change",function() {
//   var selectedModel = $("#model").val();
//   $.ajax(
//     {
//       type: 'get',
//       url: './getModelTreatments',
//       data: {'modelData': selectedModel},
//       success: function (data) {
//         $('#treatments').empty();
//         $('#treatments').append(data);
//       },
//       error: function (data) {
//         console.error(data);
//       }
//     }
//   );
// });


// $(document).ready(function(){
//     $("#dealer_id").change(function(){
//       var d_id = $(this).val();
//       $.ajax({
//           url: './template/getByDealer?d_id=' + d_id,
//           method: 'GET',
//           success: function(data) {
//             // console.log(data.html);
//             $("#template_id").html(data.html);
//           }
//       });
//   });
// });
</script>
@endsection