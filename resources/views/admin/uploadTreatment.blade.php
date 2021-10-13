@extends('layouts.dashboard')
<?php

  // $models = array(); 
  // if(session()->has('model_id')){
  //   $model_id = session()->get('model_id');
  // }
  // if(@$model_id){
  //   $models = DB::table('models')->where('id', $model_id)->get();
  // }
?>
@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Treatments            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/treatments')}}"><i class="fa fa-users"></i> Treatments</a></li>
            <li class="active">Upload Treatment</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Upload Treatment</h3>
                  <a download="" href="{{asset('/sample-format.xlsx')}}" class="floatright btn btn-warning">Download Sample</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Treatmentform" method="POST" action="{{url('/admin/importTreatment')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <div class="box-body">

                        <div class="form-group{{ $errors->has('tempId') ? ' has-error' : '' }}">
                          <label for="tempId">Treatment Template<span class="required-title">*</span></label>
                          <select class="form-control required" id="tempId" name="tempId">
                            <option value="">Select Template</option>
                            @foreach($templates as $template)
                              <option @if(old('tempId') == $template->id) {{ 'selected' }} @endif value="{{ $template->id }}">{{ $template->temp_name }}</option>
                            @endforeach
                          </select>
                          @if ($errors->has('tempId'))
                            <span class="help-block">
                              <strong>{{ $errors->first('tempId') }}</strong>
                            </span>
                          @endif
                        </div>

                        <div class="form-group{{ $errors->has('model_id') ? ' has-error' : '' }}">
                          <label for="model_id">Model<span class="required-title">*</span></label>
                          <select class="form-control required" id="model_id" name="model_id">
                            <option value="">Select Model</option>
                            @foreach($models as $model)
                              <option @if(old('model_id') == $model->id) {{ 'selected' }} @endif value="{{ $model->id }}">{{ $model->model_name }}</option>
                            @endforeach
                          </select>
                          @if ($errors->has('model_id'))
                            <span class="help-block">
                              <strong>{{ $errors->first('model_id') }}</strong>
                            </span>
                          @endif
                        </div>

                        <div class="form-group{{ $errors->has('csv') ? ' has-error' : '' }}">
                          <label for="csv">Upload File<span class="required-title">*</span></label><br>
                          <label class="ad">(File must be type of xlsx)</label>
                          <input type="file" class="form-control required" value="{{ old('csv') }}" id="csv" name="csv">
                          @if ($errors->has('csv'))
                            <span class="help-block">
                              <strong>{{ $errors->first('csv') }}</strong>
                            </span>
                          @endif
                        </div>
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
<script type="text/javascript">  
  // $('#dealer_id').on("change",function(e) {
  //   var dealer = $("#dealer_id").val();
  //   token = $('input[name=_token]').val();
  //   url = '<?php //echo url("/"); ?>/getModels';
  //       data = {
  //         dealer: dealer,
  //       };
  //       $.ajax({
  //           url: url,
  //           headers: {'X-CSRF-TOKEN': token},
  //           data: data,
  //           type: 'POST',
  //           datatype: 'JSON',
  //           success: function (resp) {
  //             $("#model_id").html(resp);
  //             return false;
  //           }
  //       });
  //       return false;
  // });
</script>   
@endsection