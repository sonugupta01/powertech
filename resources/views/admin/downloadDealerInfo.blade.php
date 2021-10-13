<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
  .container table th {
    text-align : left;
  }
  </style>
  </head>
  <body>
    {{ date('d/m/Y', strtotime($today))}}
    <div class="container">
    	<center><h2>Dealer Information</h2></center>
      <table class="table table-bordered">
          <thead>
            <tr>
              <th>Firm</th>
              <td>{{ get_firm_name($result->firm_id) }}</td>
            </tr>
            <tr>
              <th>Center Code</th>
              <td>{{ $result->center_code }}</td>
            </tr>
            <tr>
              <th>Dealer Name</th>
              <td>{{ $result->name }}</td>
            </tr>
            <tr>
              <th>Mobile</th>
              <td>{{ $result->mobile_no }}</td>
            </tr>
            <tr>
              <th>Adddress</th>
              <td>{{ $result->address }}</td>
            </tr>
            <tr>
              <th>Latitude</th>
              <td>{{ $result->latitude }}</td>
            </tr>
            <tr>
              <th>Longitude</th>
              <td>{{ $result->longitude }}</td>
            </tr>
            <tr>
              <th>District</th>
              <td>{{ get_district_name($result->district_id) }}</td>
            </tr>
            <tr>
              <th>State</th>
              <td>{{ get_state_name($result->state_id) }}</td>
            </tr>
            <tr>
              <th>Timings</th>
              <td>{{ date('h:ia', strtotime(@$timing->start_time)) }} To {{ date('h:ia', strtotime(@$timing->end_time)) }}</td>
            </tr>
          </thead>
      </table>
      <h2>Alternate Information</h2>
      <table class="table table-bordered">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Designation</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($contacts)>0) { ?>
            @foreach($contacts as $contact)
            <tr>
              <td>{{ $contact->name }}</td>
              <td>{{ $contact->email }}</td>
              <td>{{ $contact->mobile }}</td>
              <td>{{ $contact->designation }}</td>
            </tr>
            @endforeach
            <?php } else { ?>
            <tr>
              <td colspan="4">No alternate information.</td>                          
            </tr>
            <?php } ?>
          <tbody>
      </table>
      <center><p>This is computer generated receipt and requires no signature.</p></center>
    </div>
  </body>
</html>