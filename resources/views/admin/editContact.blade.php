@extends('layouts.dashboard')

@section('content')
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Contacts            <!-- <small>advanced tables</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{url('/admin/dealer_management')}}"><i class="fa fa-users"></i> Dealer Management</a></li>
            <li><a href="{{url('/admin/contacts/')}}/{{$dealer_id}}"><i class="fa fa-users"></i> Contacts</a></li>
            <li class="active">Edit Contact</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              

              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Edit Contact</h3>
                  <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  @if(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                  @endif
                  @if(Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                  @endif
                  <form role="form" id="Staffform" method="POST" action="{{url('/admin/updateContact')}}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
                      <input type="hidden" name="dealer_id" value="{{$dealer_id}}">
                      <input type="hidden" name="contact_id" value="{{$result->id}}">
                      <div class="box-body">
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                          <label for="name">Name</label>
                          <input type="text" class="form-control required" value="{{ old('name',$result->name) }}" id="name" name="name" placeholder="Enter name">
                          @if ($errors->has('name'))
                            <span class="help-block">
                              <strong>{{ $errors->first('name') }}</strong>
                            </span>
                          @endif
                        </div>
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                          <label for="email">Email</label>
                          <input type="text" class="form-control required" value="{{ old('email',$result->email) }}" id="email" name="email" placeholder="Enter email">
                          @if ($errors->has('email'))
                            <span class="help-block">
                              <strong>{{ $errors->first('email') }}</strong>
                            </span>
                          @endif
                        </div>
                        <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
                          <label for="mobile">Mobile No.</label>
                          <input type="text" class="form-control required" value="{{ old('mobile',$result->mobile) }}" id="mobile" name="mobile" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">
                          @if ($errors->has('mobile'))
                            <span class="help-block">
                              <strong>{{ $errors->first('mobile') }}</strong>
                            </span>
                          @endif
                        </div>
                        <div class="form-group{{ $errors->has('designation') ? ' has-error' : '' }}">
                          <label for="designation">Designation</label>
                          <input type="text" class="form-control required" value="{{ old('designation',$result->designation) }}" id="designation" name="designation" placeholder="Enter Designation">
                          @if ($errors->has('designation'))
                            <span class="help-block">
                              <strong>{{ $errors->first('designation') }}</strong>
                            </span>
                          @endif
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