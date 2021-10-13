@extends('layouts.dashboard')

@section('content')
<script src="{{ asset('js/jquery.js') }}"></script>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Treatment
      <!-- <small>advanced tables</small> -->
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{url('admin/treatmentTemplates')}}"><i class="fa fa-dashboard"></i> Treatment Template</a></li>
      <li class="active">Treatment</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Update Percentage Price for Template <b>{{get_template_name($template_id)}}</b></h3>
          </div><!-- /.box-header --> 
          <div class="box-body">
            @if(Session::has('error'))
              <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('success'))
              <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
              <form method="POST" action="{{url('/admin/updatePercentagePrice')}}">
                <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                <input type="hidden" name="template_id" id="template_id" value="{{$template_id}}">
                <div class="form-group">
                  <label for="change">Change Options<span class="required-title">*</span></label>
                  <select class="form-control required" id="change" name="change" required>
                    <option value="">Select Option</option>
                    <option value="increase">Increase</option>
                    <option value="decrease">Decrease</option> 
                  </select>
                </div>
                <div class="form-group">
                  <label for="service_date">How Much<span class="required-title">*</span></label>
                    <div class="input-group date">
                     <div class="input-group-addon">
                       <i class="fa fa-calendar"></i>
                     </div>
                     <input type="text" class="form-control pull-right" id="amount" name="amount" value="{{ old('amount') }}" maxlength="2" OnKeypress="return isNumber(event)" required>
                   </div>
                </div>
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
function isNumber(evt, element) 
  {
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (
          (charCode != 45 || $(element).val().indexOf('-') != -1) &&      // “-” CHECK MINUS, AND ONLY ONE.
          /*(charCode != 46 || $(element).val().indexOf('.') != -1) && */     // “.” CHECK DOT, AND ONLY ONE.
          (charCode < 48 || charCode > 57))
          return false;
      else
      {
          return true;    
      }
  }
</script>   
@endsection