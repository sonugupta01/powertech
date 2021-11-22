<?php

function day_hours($id, $that_date)
{

	$user_time_data = DB::table('attendance')->where('user_id', $id)->where('date', $that_date)->get();
	foreach ($user_time_data as $key => $value) {
		$timeFirst  = strtotime($value->in_time);
		$timeSecond = strtotime($value->out_time);
		if (!empty($value->in_time) && !empty($value->out_time)) {
			$differenceInSeconds = $timeSecond - $timeFirst;
		} else {
			$differenceInSeconds = 0;
		}
		$sum += $differenceInSeconds;
	}

	return secToHR($sum);
}

function day_hours_dealers($id, $that_date)
{

	$user_time_data = DB::table('attendance')->where('user_id', $id)->where('date', $that_date)->get();
	foreach ($user_time_data as $key => $value) {
		$timeFirst  = strtotime($value->in_time);
		$timeSecond = strtotime($value->out_time);
		if (!empty($value->in_time) && !empty($value->out_time)) {
			$differenceInSeconds = $timeSecond - $timeFirst;
		} else {
			$differenceInSeconds = 0;
		}
		$sum += $differenceInSeconds;
	}

	return secToHR($sum);
}

function secToHR($sum)
{
	$hours = floor($sum / 3600);
	$minutes = floor(($sum / 60) % 60);
	$seconds = $sum % 60;
	$data = ['hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds];
	return $data;
}

function total_hours_range($id, $start_date = null, $end_date = null)
{
	if (empty($end_date)) {
		$end_date = date('Y-m-d');
	}

	if (empty($start_date)) {
		$start_date = date('Y-m-01', strtotime($end_date));
	}

	$attendance = DB::table('attendance')->where('user_id', $id)->whereBetween('date', [$start_date, $end_date])->get();
	$sum = 0;
	foreach ($attendance as $key => $value) {
		$timeFirst  = strtotime($value->in_time);
		$timeSecond = strtotime($value->out_time);
		if (!empty($value->in_time) && !empty($value->out_time)) {
			$differenceInSeconds = $timeSecond - $timeFirst;
		} else {
			$differenceInSeconds = 0;
		}
		$sum += $differenceInSeconds;
	}

	return secToHR($sum);
}

function full_days_in_range($id, $start_date = null, $end_date = null)
{
	// if (empty($end_date)) {
	// 	$end_date = date('Y-m-d');
	// }

	// if (empty($start_date)) {
	// 	$start_date = date('Y-m-01', strtotime($end_date));
	// }

	$attendance = DB::table('attendance')->where('user_id', $id)->whereBetween('date', [$start_date, $end_date])->get();

	$half = 0;
	$full = 0;

	foreach ($attendance as $key => $value) {

		if (!empty($value->in_time) && !empty($value->out_time)) {

			@$data_total_hours = day_hours($value->user_id, $value->date);
			// echo @$data_total_hours['hours'] . ' hours ' . 
			// @$data_total_hours['minutes'] . ' minutes';

			if ((@$data_total_hours['hours'] < 6 && (@$data_total_hours['hours'] >= 3))) {
				$half += 1;
			} elseif (@$data_total_hours['hours'] > 6) {
				$full += 1;
			}
		}
	}
	return $full;
}

function absent_days_in_range($id, $start_date = null, $end_date = null)
{
	// if (empty($end_date)) {
	// 	$end_date = date('Y-m-d');
	// }

	// if (empty($start_date)) {
	// 	$start_date = date('Y-m-01', strtotime($end_date));
	// }
	$absent = 0;
	$three = 0;
	$dates = array();
	$interval = new DateInterval('P1D');
	$realEnd = new DateTime($end_date);
	$realEnd->add($interval);

	$period = new DatePeriod(new DateTime($start_date), $interval, $realEnd);

	// Use loop to store date into array 
	foreach ($period as $date) {
		$dates[] = $date->format('Y-m-d');
	}

	foreach ($dates as $key => $value) {
		$attendance = DB::table('attendance')->where('user_id', $id)->whereDate('date', $value)->first();
		if (!empty($attendance)) {
			if (!empty($attendance->in_time) && !empty($attendance->out_time)) {
				@$data_total_hours = day_hours($attendance->user_id, $attendance->date);
				if ($data_total_hours['hours'] < 3) {
					$three += 1;
				}
			}
		} else {
			$absent += 1;
		}
	}

	return $absent . '-' . $three;
}

function notmarked_days_in_range($id, $start_date = null, $end_date = null)
{
	if (empty($end_date)) {
		$end_date = date('Y-m-d');
	}

	if (empty($start_date)) {
		$start_date = date('Y-m-01', strtotime($end_date));
	}

	$attendance = DB::table('attendance')->where('user_id', $id)->whereBetween('date', [$start_date, $end_date])->get();

	$notmarked = 0;

	foreach ($attendance as $key => $value) {
		if (!empty($value->in_time) && empty($value->out_time)) {
			$notmarked += 1;
		}
	}
	return $notmarked;
}

function salesreportnotfilled_days_in_range($id, $start_date = null, $end_date = null)
{
	if (empty($end_date)) {
		$end_date = date('Y-m-d');
	}

	if (empty($start_date)) {
		$start_date = date('Y-m-01', strtotime($end_date));
	}

	$dates = array();
	$interval = new DateInterval('P1D');
	$realEnd = new DateTime($end_date);
	$realEnd->add($interval);

	$period = new DatePeriod(new DateTime($start_date), $interval, $realEnd);

	// Use loop to store date into array 
	foreach ($period as $date) {
		$dates[] = $date->format('Y-m-d');
	}
	$filled = 0;
	foreach ($dates as $key => $value) {
		$jobs = DB::table('jobs')->where('user_id', $id)->whereDate('job_date', $value)->get();
		if (count($jobs) > 0) {
			$filled += 1;
		}
	}
	return count($dates) - $filled;
}

function half_days_in_range($id, $start_date = null, $end_date = null)
{
	if (empty($end_date)) {
		$end_date = date('Y-m-d');
	}

	if (empty($start_date)) {
		$end_date = date('Y-m-01', strtotime($end_date));
	}

	$attendance = DB::table('attendance')->where('user_id', $id)->whereBetween('date', [$start_date, $end_date])->get();
	$half = 0;
	$full = 0;

	foreach ($attendance as $key => $value) {

		if (!empty($value->in_time) && !empty($value->out_time)) {

			@$data_total_hours = day_hours($value->user_id, $value->date);
			// echo @$data_total_hours['hours'] . ' hours ' . 
			// @$data_total_hours['minutes'] . ' minutes';

			if ((@$data_total_hours['hours'] < 6 && (@$data_total_hours['hours'] >= 3))) {
				$half += 1;
			} elseif (@$data_total_hours['hours'] > 6) {
				$full += 1;
			}
		}
	}
	return $half;
}

function month_hours($id, $month = null)
{
	if (!empty($month)) {
		$user_time_data = DB::table('attendance')->where('user_id', $id)->whereMonth('date', $month)->get();
	} else {
		$user_time_data = DB::table('attendance')->where('user_id', $id)->whereMonth('date', date('m'))->get();
	}

	foreach ($user_time_data as $key => $value) {
		$timeFirst  = strtotime($value->in_time);
		$timeSecond = strtotime($value->out_time);
		if (!empty($value->in_time) && !empty($value->out_time)) {
			// echo $value->date . " " . $value->in_time . " " . $value->out_time . "<br>";
			$differenceInSeconds = $timeSecond - $timeFirst;
		} else {
			$differenceInSeconds = 0;
		}
		$sum += $differenceInSeconds;
	}

	return secToHR($sum);
}

function month_hours_date($id, $start_date = null, $end_date = null)
{
	if (!empty($start_date) || !empty($end_date)) {
		if (empty($end_date)) {
			$end_date = date('Y-m-d');
		}
		$user_time_data = DB::table('attendance')->where('user_id', $id)->whereBetween('date', [$start_date, $end_date])->get();
	} else {
		$user_time_data = DB::table('attendance')->where('user_id', $id)->whereMonth('date', date('m'))->get();
	}

	foreach ($user_time_data as $key => $value) {
		$timeFirst  = strtotime($value->in_time);
		$timeSecond = strtotime($value->out_time);
		if (!empty($value->in_time) && !empty($value->out_time)) {
			// echo $value->date . " " . $value->in_time . " " . $value->out_time . "<br>";
			$differenceInSeconds = $timeSecond - $timeFirst;
		} else {
			$differenceInSeconds = 0;
		}
		$sum += $differenceInSeconds;
	}

	return secToHR($sum);
}

function searchForDate($date, $array)
{
	$data = [];
	foreach ($array as $key => $val) {
		if ($val->date === $date) {
			$data[] = $array[$key];
		}
	}
	return $data;
}

function get_dealer_name_by_id($id)
{
	$dealer = DB::table('users')->where('id', $id)->first(['dealer_id', 'dealer_office']);
	if (!empty($dealer->dealer_id)) {
		return get_name($dealer->dealer_id);
	} elseif (!empty($dealer->dealer_office)) {
		return get_name($dealer->dealer_office);
	}
}


function get_emp_code($id)
{
	$code =	DB::table('staff_detail')->where('user_id', $id)->first()->emp_code;
	return $code;
}

// get current time

function getCurrentTimestamp()
{

	date_default_timezone_set('Asia/Kolkata');

	$timestamp = date('Y-m-d H:i:s');

	return $timestamp;
}

function getCurrentDate()
{

	date_default_timezone_set('Asia/Kolkata');

	$timestamp = date('Y-m-d');

	return $timestamp;
}

function getCurrentTime()
{

	date_default_timezone_set('Asia/Kolkata');

	$timestamp = date('H:i:s');

	return $timestamp;
}

// hvt %
function hvt_in_percentage($val1, $val2)
{
	if ($val2 != 0) {
		$result = ($val1 / $val2) * 100;
		$return = round($result);
	} else {
		$return = 0;
	}
	return $return;
}

// value per treatment
function vas_in_percentage($amt, $tot)
{
	if ($tot != 0) {
		$result = ($amt / $tot);
		$return = round($result);
	} else {
		$return = 0;
	}
	return $return;
}

// get emails for dealers
function get_emails($user_id)
{
	$result = DB::table('users_email')
		->select(DB::raw('group_concat(email) as email'))
		->where('user_id', $user_id)
		->groupBy('user_id')
		->first();
	return @$result->email;
}

// get State name
function get_state_name($id = NULL)
{
	$result = DB::table('states')->select('state_name')->where('s_id', $id)->first();
	return @$result->state_name;
}

// get District name
function get_district_name($id = NULL)
{
	$result = DB::table('districts')->select('district_name')->where('district_id', $id)->first();
	return @$result->district_name;
}

// get total no. of advisors
function get_advisors($id)
{
	$result = DB::table('advisors')->where('dealer_id', $id)->where('status', 1)->count();
	return $result;
}

// get dealer id

function get_dealer_id($id = NULL)
{
	$result = DB::table('users')->select('dealer_id')->where('role', '3')->where('id', $id)->first();
	return $result->dealer_id;
}

// get user's dealer id

function get_users_dealer($id = NULL)
{
	$emp_hierarchy = DB::table('emp_hierarchy')->where('user_id', $id)
		->whereDate('to_date', '>=', date('Y-m-d'))
		->whereDate('from_date', '<=', date('Y-m-d'))->first();

	if (!empty($emp_hierarchy)) {
		return @$emp_hierarchy->dealer_id;
	} else {
		$result = DB::table('users')->select('dealer_id')->whereIn('role', [3, 4])->where('id', $id)->first();
		return $result->dealer_id;
	}
}

// get dealer name

function get_dealer_name($dealer_id = NULL)
{
	$result = DB::table('users')->select('name')
		->where('id', $dealer_id)
		->where(function ($query) {
			$query->where('role', '2');
			$query->orWhere('role', '6');
		})->first();
	return ucwords(@$result->name);
}

// get reporting authority name
function get_reporting_authority_name($authority_id = NULL)
{
	$result = DB::table('users')->select('name')->where('id', $authority_id)->first();
	return ucwords(@$result->name);
}

// get dealer name

function get_name($id = NULL)
{
	$result = DB::table('users')->select('name')->where('id', $id)->first();
	return ucwords(@$result->name);
}

//get asm
function get_asm($dealer_id)
{
	$result = DB::table('users')->select('reporting_authority')->where('id', $dealer_id)->where('status', 1)->first();
	return @$result->reporting_authority;
}

//get asm
function get_dealers($firm_id)
{
	$result = DB::table('users')->select('id', 'name')->where(['firm_id' => $firm_id, 'role' => 2, 'status' => 1])->get();
	return @$result;
}

// get Firm short code
function get_firm_short_code($id = NULL)
{
	$result = DB::table('firms')->select('short_code')->where('id', $id)->first();
	return @$result->short_code;
}

// get department name

function get_department_name($department_id = NULL)
{
	$result = DB::table('departments')->select('name')->where('id', $department_id)->first();
	return ucwords(@$result->name);
}

// get Ffirm name
function get_firm_name($id = NULL)
{
	$result = DB::table('firms')->select('firm_name')->where('id', $id)->first();
	return @$result->firm_name;
}

// get designation

function get_designation_name($designation_id = NULL)
{
	$result = DB::table('designations')->select('designation')->where('id', $designation_id)->first();
	return ucwords(@$result->designation);
}

// get designation

function get_designation($user_id = NULL)
{
	$result = DB::table('staff_detail')->select('designation_id')->where('user_id', $user_id)->first();
	return @$result->designation_id;
}

// get designation name by user id

function get_designation_by_userid($user_id = NULL)
{
	$result = DB::table('staff_detail as sd')->join('designations as d', 'd.id', '=', 'sd.designation_id')->select('d.designation')->where('sd.user_id', $user_id)->first();
	return @$result->designation;
}

// get advisor name
function advisor_name($advisor_id = NULL)
{
	$result = DB::table('advisors')->where('id', $advisor_id)->select('name')->first();
	return ucwords(@$result->name);
}

// get advisor name
function get_advisor_name($advisor_id = NULL)
{
	$result = DB::table('advisors as a')
		->join('dealer_department as d', 'd.id', '=', 'a.department')
		->select('a.name as name', 'd.name as department_name')->where('a.id', $advisor_id)->first();
	return ucwords(@$result->name . ' - ' . @$result->department_name);
}

// get advisor department
function get_advisor_department_id($advisor_id = NULL)
{
	$result = DB::table('advisors')
		->where('id', $advisor_id)->first();
	return @$result->department;
}

// get oem name
function get_oem_name($oem_id = NULL)
{
	$result = DB::table('oems as o')->where('id', $oem_id)->select('o.oem as name')->first();
	return ucwords(@$result->name);
}

// get groups name
function get_group_name($group_id = NULL)
{
	$result = DB::table('groups as g')->where('id', $group_id)->select('g.group_name as name')->first();
	return ucwords(@$result->name);
}

// get pan no.
function get_pan_no($advisor_id = NULL)
{
	$result = DB::table('advisors as a')
		->select('a.pan_no as pan_no')
		->where('a.id', $advisor_id)
		->first();
	return ucwords(@$result->pan_no);
}

// get product name
function get_product_name($product_id = NULL)
{
	$result = DB::table('products')
		->select('name')
		->where('id', $product_id)
		->first();
	return ucwords(@$result->name);
}

// get product unit
function get_product_unit($product_id = NULL)
{
	$result = DB::table('products')
		->select('uom')
		->where('id', $product_id)
		->first();
	return @$result->uom;
}

// get product unit
function get_unit_name($unit_id = NULL)
{

	if ($unit_id == 1) {
		$unit = 'Litre';
	} elseif ($unit_id == 2) {
		$unit = 'ML';
	} elseif ($unit_id == 3) {
		$unit = 'Pcs.';
	} elseif ($unit_id == 4) {
		$unit = 'Gms.';
	} else {
		$unit = '';
	}
	return $unit;
}

// get model name
function get_model_name($model_id = NULL)
{
	$result = DB::table('models')
		->select('model_name')
		->where('id', $model_id)
		->first();
	return ucwords(@$result->model_name);
}

// get treatment name
function get_treatment_name($treatment_id = NULL)
{
	$result = DB::table('treatments')
		->select('treatment')
		->where('id', $treatment_id)
		->first();
	return ucwords(@$result->treatment);
}

// get template name
function get_template_name($template_id = NULL)
{
	$result = DB::table('treatment_templates')
		->select('temp_name')
		->where('id', $template_id)
		->first();
	return ucwords(@$result->temp_name);
}

// get uom Name
function get_uom($id)
{
	if ($id == 1) {
		$result = "Liter";
	} elseif ($id == 3) {
		$result = "Pcs.";
	} elseif ($id == 4) {
		$result = "Gms.";
	} else {
		$result = "ML";
	}
	return $result;
}

// get product quantity
function get_quantity($id)
{
	$result = DB::table('products')->where('id', $id)->select('quantity')->first();
	return $result->quantity;
}

// get product price
function get_price($id)
{
	$result = DB::table('products')->where('id', $id)->select('price')->first();
	return $result->price;
}

// get total dealers
function total_dealers()
{
	$result = DB::table('users')->where('role', '2')->count();
	return $result;
}

// get level name
function get_level($id)
{
	$result = DB::table('designation_levels')->select('level')->where('id', $id)->first();
	return @$result->level;
}

// get level by designation
function getlevelbydesignation($id)
{
	$result = DB::table('designations')->select('level')->where('id', $id)->first();
	return @$result->level;
}

// SMS Gateway
function send_sms($phone, $message)
{

	$user = "01synergy";

	$password = "01@Synergy";

	$msisdn = $phone;

	$sid = "SMFNOW";

	$msg = $message;

	$msg = urlencode($msg);

	$fl = "0";

	$gwid = "2";

	$ch =

		curl_init("http://cloud.smsindiahub.in/vendorsms/pushsms.aspx?user=" . $user . "&password=" . $password . "&msisdn=" . $msisdn . "&sid=" . $sid . "&msg=" . $msg . "&fl=" . $fl . "&gwid=" . $gwid);

	curl_setopt($ch, CURLOPT_HEADER, 0);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$output = curl_exec($ch);

	curl_close($ch);

	return 1;
}

// get YouTube emdedded url
function getYoutubeEmbedUrl($url)
{
	$shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';

	$longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

	if (preg_match($longUrlRegex, $url, $matches)) {

		$youtube_id = $matches[count($matches) - 1];
	}

	if (preg_match($shortUrlRegex, $url, $matches)) {

		$youtube_id = $matches[count($matches) - 1];
	}

	return 'https://www.youtube.com/embed/' . @$youtube_id;
}

// draw calendar
function draw_calendar($month, $year)
{

	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

	/* table headings */
	$headings = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	$calendar .= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';

	/* days and weeks vars now ... */
	$running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
	$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar .= '<tr class="calendar-row">';

	/* print "blank" days until the first of the current week */
	for ($x = 0; $x < $running_day; $x++) :
		$calendar .= '<td class="calendar-day-np"> </td>';
		$days_in_this_week++;
	endfor;

	/* keep going with days.... */
	for ($list_day = 1; $list_day <= $days_in_month; $list_day++) :

		$todayclass = "";
		$d = $list_day . "-" . $month . "-" . $year;
		$md = date("j-n-Y");
		if ($d == $md) {
			$todayclass = ' today ';
		}

		$calendar .= '<td class="calendar-day' . $todayclass . '">';
		/* add in the day number */
		$calendar .= '<div class="day-number">' . $list_day . '</div>';

		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		$calendar .= str_repeat('<p> </p>', 2);

		$calendar .= '</td>';
		if ($running_day == 6) :
			$calendar .= '</tr>';
			if (($day_counter + 1) != $days_in_month) :
				$calendar .= '<tr class="calendar-row">';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++;
		$running_day++;
		$day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if ($days_in_this_week < 8) :
		for ($x = 1; $x <= (8 - $days_in_this_week); $x++) :
			$calendar .= '<td class="calendar-day-np"> </td>';
		endfor;
	endif;

	/* final row */
	$calendar .= '</tr>';

	/* end the table */
	$calendar .= '</table>';

	/* all done, return result */
	return $calendar;
}

function sendgrid()
{
	require '../vendor/autoload.php';
	//echo file_exists( '../vendor/autoload.php' );
	$sendgrid = new SendGrid('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');
	$email = new SendGrid\Email();
	$email
		->addTo('varinder.kaur@ldh.01s.in')
		->setFrom('test.01synergy@gmail.com')
		->setSubject('Subject goes here')
		->setText('Hello World!');
	$sendgrid->send($email);
}

// Calculate distance
function calculateDistanceOfLatLng($lat1, $lon1, $lat2, $lon2, $unit)
{
	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);
	//return $miles * 1609.34;
	if ($unit == "K") { // kilometers
		return round(($miles * 1.609344));
	} else if ($unit == "N") { // nautical miles
		return round(($miles * 0.8684));
	} else if ($unit == "MT") {
		return round(($miles * 1609.34)); // meters
	} else {
		return round($miles); // miles
	}
}

function getLocationofDealer($id)
{
	$data = DB::table('users')->where('id', $id)->first();
	return @$data;
}

function getUserRole($user_id)
{
	$data = DB::table('users')->select('role')->where('id', $user_id)->first();
	return @$data->role;
}

function getUserLogin($user_id)
{
	$data = DB::table('users')->select('is_login')->where('id', $user_id)->first();
	return @$data->is_login;
}

function getDealerDepartment($advisor_id)
{
	$data = DB::table('advisors')->where('id', $advisor_id)->select('department')->first();
	return @$data->department;
}
function getsalarybyid($id)
{
	$data = DB::table('employee_salary')->where('user_id', $id)->first();
	return $data->emp_salary;
}

function getBrandName($brand_id)
{
	$brand = DB::table('product_brands')->where(['id' => $brand_id, 'status' => '1'])->first();
	return @$brand->brand_name;
}

function getAdvisorPercentage($advisor_id)
{
	$percentage = DB::table('advisor_shares')->where('advisor_id', $advisor_id)->orderBy('created_at', 'DESC')->first();
	return @$percentage->advisor_share;
}

function get_dealer_ro($dealer_id)
{
	$date = explode('-', date('Y-m-d'));
	$dealer_ro = DB::table('jobs_by_date')
		->select(DB::raw('SUM(total_jobs) as total_jobs,dealer_id'))
		->where('dealer_id', @$dealer_id)
		->whereMonth('job_added_date', $date[1])
		->whereYear('job_added_date', $date[0])
		->groupBy('dealer_id')
		->first();
	return $dealer_ro;
}
