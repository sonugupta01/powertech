@extends('layouts.dashboard')

<?php



$districts = array(); 

if(session()->has('district_id')){

  $district_id = session()->get('district_id');

}

if(@$district_id){

  $districts = DB::table('districts')->where('district_id', $district_id)->get();

}

?>

@section('content')

<div class="content-wrapper">

  <!-- Content Header (Page header) -->

  <section class="content-header">

    <h1>

      Dealers            <!-- <small>advanced tables</small> -->

    </h1>

    <ol class="breadcrumb">

      <li><a href="{{url('/asm')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>

      <li><a href="{{url('/asm/dealer_management')}}"><i class="fa fa-users"></i> Dealer</a></li>

      <li class="active">Add Dealer</li>

    </ol>

  </section>



  <!-- Main content -->

  <section class="content">

    <div class="row">

      <div class="col-xs-12">

        <div class="box">

          <div class="box-header">

            <h3 class="box-title">Add Dealer</h3>

          </div><!-- /.box-header -->

          <div class="box-body">

            @if(Session::has('error'))

            <div class="alert alert-danger">{{ Session::get('error') }}</div>

            @endif

            @if(Session::has('success'))

            <div class="alert alert-success">{{ Session::get('success') }}</div>

            @endif

            <form role="form" id="dealerform" method="POST" action="{{url('/asm/insertDealer')}}" enctype="multipart/form-data">

              <input type="hidden" name="_token" value="<?= csrf_token(); ?>">

              <div class="box-body">

                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">

                  <label for="name">Name<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ old('name') }}" id="name" name="name" placeholder="Enter name">

                  @if ($errors->has('name'))

                  <span class="help-block">

                    <strong>{{ $errors->first('name') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                  <label for="email">Email<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ old('email') }}" id="email" name="email" placeholder="Enter email">

                  @if ($errors->has('email'))

                  <span class="help-block">

                    <strong>{{ $errors->first('email') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">

                  <label for="mobile_no">Mobile No.<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ old('mobile_no') }}" id="mobile_no" name="mobile_no" placeholder="Enter mobile no." maxlength="10" OnKeypress="return isNumber(event)">

                  @if ($errors->has('mobile_no'))

                  <span class="help-block">

                    <strong>{{ $errors->first('mobile_no') }}</strong>

                  </span>

                  @endif

                </div>



                <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">

                  <label for="address">Address<span class="required-title">*</span></label>

                  <textarea class="form-control required" id="address" name="address" placeholder="Enter address"></textarea>

                  @if ($errors->has('address'))

                  <span class="help-block">

                    <strong>{{ $errors->first('address') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('latitude') ? ' has-error' : '' }}">
                  <label for="latitude">Latitude</label>
                  <input type="text" class="form-control required" value="{{ old('latitude') }}" id="latitude" name="latitude" placeholder="Enter Latitude">
                  @if ($errors->has('latitude'))
                  <span class="help-block">
                    <strong>{{ $errors->first('latitude') }}</strong>
                  </span>
                  @endif
                </div>

                <div class="form-group{{ $errors->has('longitude') ? ' has-error' : '' }}">
                  <label for="longitude">Longitude</label>
                  <input type="text" class="form-control required" value="{{ old('longitude') }}" id="longitude" name="longitude" placeholder="Enter Longitude">
                  @if ($errors->has('longitude'))
                  <span class="help-block">
                    <strong>{{ $errors->first('longitude') }}</strong>
                  </span>
                  @endif
                </div>

                <div class="form-group{{ $errors->has('state_id') ? ' has-error' : '' }}">

                  <label for="state_id">State<span class="required-title">*</span></label>

                  <select class="form-control required" id="state_id" name="state_id">

                    <option value="">Select State</option>

                    @foreach($states as $state)

                    <option @if(old('state_id') == $state->s_id) {{ 'selected' }} @endif value="{{$state->s_id}}">{{$state->state_name}}</option>

                    @endforeach

                  </select>

                  @if ($errors->has('state_id'))

                  <span class="help-block">

                    <strong>{{ $errors->first('state_id') }}</strong>

                  </span>

                  @endif

                </div>

                <div class="form-group{{ $errors->has('district_id') ? ' has-error' : '' }}">

                  <label for="district_id">District<span class="required-title">*</span></label>

                  <select class="form-control required" id="district_id" name="district_id">

                    <option value="">Select District</option>

                    @foreach($districts as $district)

                    <option @if(old('district_id') == $district->district_id) {{ 'selected' }} @endif value="{{ $district->district_id }}">{{ $district->district_name }}</option>

                    @endforeach

                  </select>

                  @if ($errors->has('district_id'))

                  <span class="help-block">

                    <strong>{{ $errors->first('district_id') }}</strong>

                  </span>

                  @endif

                </div>



                <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">

                  <label for="city">City<span class="required-title">*</span></label>

                  <input type="text" class="form-control required" value="{{ old('city') }}" id="city" name="city" placeholder="Enter city">

                  @if ($errors->has('city'))

                  <span class="help-block">

                    <strong>{{ $errors->first('city') }}</strong>

                  </span>

                  @endif

                </div>



                <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}">

                  <label for="group">Group</label>

                  <select class="form-control required" id="district_id" name="group">

                    <option value="">Select Group</option>

                    @foreach($grouplist as $grouplist)

                    <option @if(old('group') == $grouplist->id) {{ 'selected' }} @endif value="{{$grouplist->id}}">{{$grouplist->group_name}}</option>

                    @endforeach

                  </select>

                  @if ($errors->has('group'))

                  <span class="help-block">

                    <strong>{{ $errors->first('group') }}</strong>

                  </span>

                  @endif

                </div>

                



                <div class="form-group{{ $errors->has('OEM') ? ' has-error' : '' }}">

                  <label for="OEM">OEM<span class="required-title">*</span></label>

                  <select class="form-control required" id="district_id" name="OEM">

                    <option value="">Select OEM</option>

                    @foreach($oemlist as $oemlist)

                    <option @if(old('OEM') == $oemlist->id) {{ 'selected' }} @endif value="{{$oemlist->id}}">{{$oemlist->oem}}</option>

                    @endforeach

                  </select>

                  @if ($errors->has('OEM'))

                  <span class="help-block">

                    <strong>{{ $errors->first('OEM') }}</strong>

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

  $('#state_id').on("change",function(e) {

    var state = $("#state_id").val();

    token = $('input[name=_token]').val();

    url = '<?php echo url("/"); ?>/getDistrict';

    data = {

      state: state,

    };

    $.ajax({

      url: url,

      headers: {'X-CSRF-TOKEN': token},

      data: data,

      type: 'POST',

      datatype: 'JSON',

      success: function (resp) {

        $("#district_id").html(resp);

        return false;

      }

    });

    return false;

  });

</script>   

@endsection