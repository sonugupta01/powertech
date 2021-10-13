@extends('layouts.dashboard')

@section('content')

<div class="content-wrapper">

  <!-- Content Header (Page header) -->

  <section class="content-header">

    <h1>

      ASM            <!-- <small>advanced tables</small> -->

    </h1>

    <ol class="breadcrumb">

      <li><a href="{{url('/admin')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>

      <li><a href="{{url('/admin/asm')}}"><i class="fa fa-file"></i> ASM</a></li>

      <li class="active">Edit ASM</li>

    </ol>

  </section>



  <!-- Main content -->

  <section class="content">

    <div class="row">

      <div class="col-xs-12">

        <div class="box">

          <div class="box-header">

            <h3 class="box-title">Edit ASM</h3>
            <a href="{{ URL::previous() }}" class="btn btn-info floatright" style="margin-right: 10px;">Back</a>

          </div><!-- /.box-header -->

          <div class="box-body">

            @if(Session::has('error'))

            <div class="alert alert-danger">{{ Session::get('error') }}</div>

            @endif

            @if(Session::has('success'))

            <div class="alert alert-success">{{ Session::get('success') }}</div>

            @endif

            <form role="form" id="ASMform" method="POST" action="{{url('/admin/updateASM')}}" enctype="multipart/form-data">

              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="asm_id" value="{{$user->id}}">

              <div class="box-body">

                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">

                  <label for="name">Name<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{$user->name}}" id="name" name="name" placeholder="Enter name">

                  @if ($errors->has('name'))

                  <span class="help-block">

                    <strong>{{ $errors->first('name') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                  <label for="email">Email<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ $user->email }}" id="email" name="email" placeholder="Enter email">

                  @if ($errors->has('email'))

                  <span class="help-block">

                    <strong>{{ $errors->first('email') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">

                  <label for="mobile_no">Mobile No.<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ $user->mobile_no }}" id="mobile_no" name="mobile_no" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">

                  @if ($errors->has('mobile_no'))

                  <span class="help-block">

                    <strong>{{ $errors->first('mobile_no') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">

                  <label for="email">Password<span class="required-title">*</span></label>

                  <input type="password" class="form-control required" value="{{ $user->password }}" id="password" name="password" placeholder="Enter Password">

                  @if ($errors->has('password'))

                  <span class="help-block">

                    <strong>{{ $errors->first('password') }}</strong>

                  </span>

                  @endif

                </div>

              </div>

              <!-- /.box-body -->

              <div class="box-footer">

                <button type="submit" class="btn btn-primary">Update</button>

              </div>

            </form>

          </div><!-- /.box-body -->

        </div><!-- /.box -->

      </div><!-- /.col -->

    </div><!-- /.row -->

  </section><!-- /.content -->

</div><!-- /.content-wrapper --> 

<script type="text/javascript">

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