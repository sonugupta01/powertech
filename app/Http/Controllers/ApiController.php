<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Hash;
use File;
use Mail;
use Schema;
use Auth;
use App\User;
use Illuminate\Support\Facades\Input;

class ApiController extends Controller
{

  // Login
  public function login(Request $decoded)
  {
    $mobile_no = $decoded->mobile_no;
    $result = User::whereIn('role', [3, 4, 5])->where('mobile_no', $mobile_no)->first();
    $digits = 4;
    $otp = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    $timestamp = getCurrentTimestamp();
    if (!empty($result) && ($result->status == 1)) {
      if (!empty($result->dealer_id)) {
        $message = "Supervisor Name " . $result->name . " Dealer Name " . get_dealer_name($result->dealer_id) . " Mobile No " . $mobile_no . " OTP for Login is " . $otp;
      } else if (!empty($result->dealer_office)) {
        $message = "Supervisor Name " . $result->name . " Dealer Name " . get_dealer_name($result->dealer_office) . " Mobile No " . $mobile_no . " OTP for Login is " . $otp;
      } else {
        $message = "Supervisor Name " . $result->name . " Dealer Name " . "-" . " Mobile No " . $mobile_no . " OTP for Login is " . $otp;
      }


      $resOtp = send_sms($mobile_no, $message);
      if ($resOtp == 1) {
        $data = array(
          'otp' => $otp,
          'otp_time' => $timestamp,
          'otp_flag' => 0,
        );
        DB::table('users')->where('mobile_no', '=', $mobile_no)->update($data);
        $return = array('name' => $result->name, 'result' => 'OTP sent in your mobile number', 'status_code' => 200);
        exit(json_encode($return));
      }
    } else if (!empty($result) && ($result->status == 0)) {
      $return = array('result' => 'Your account has been deactivated!', 'status_code' => 204);
      exit(json_encode($return));
    } else {
      $return = array('result' => 'Please fill valid credientials!', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // OTP match for login
  public function matchOTP(Request $decoded)
  {
    //$entityBody = file_get_contents('php://input');
    $timestamp = getCurrentTimestamp();
    //$decoded = json_decode($entityBody);
    $mobile_no = $decoded->mobile_no;
    $device_id = $decoded->device_id;
    $otpUser = $decoded->otp;
    $matchOtp = User::whereIn('role', [3, 4, 5])->where('status', 1)->where('mobile_no', $mobile_no)->where('otp', $otpUser)->first();
    if (@$matchOtp && !empty($matchOtp)) {
      $userOtp = $matchOtp->otp;
      $otpDatetime = $matchOtp->otp_time;
      $otpFlag = $matchOtp->otp_flag;
      $otpTimestamp = explode(' ', $otpDatetime);
      $currentTimestamp = explode(' ', $timestamp);
      $currentdate = $currentTimestamp[0];
      $otpdate = $otpTimestamp[0];
      if ($otpFlag == 1) {
        $return = array('result' => 'OTP already used, please try again', 'status_code' => 204);
        exit(json_encode($return));
      }
      if ($otpdate == $currentdate) {
        $datetime1 = strtotime($otpDatetime);
        $datetime2 = strtotime($timestamp);
        $interval  = abs($datetime2 - $datetime1);
        $minutes   = round($interval / 60);
        if ($minutes > 15) {
          $return = array('result' => 'OTP is expired, please try again', 'status_code' => 204);
          exit(json_encode($return));
        } else {
          $data = array(
            'otp_flag' => 1,
            'device_id' => $device_id,
          );
          User::where('role', 3)->where('mobile_no', '=', $mobile_no)->update($data);
          $result = array('user_id' => $matchOtp->id, 'username' => $matchOtp->name, 'dealer_id' => $matchOtp->dealer_id, 'role' => $matchOtp->role);
          $return = array('result' => $result, 'status_code' => 200);
          exit(json_encode($return));
        }
      } else {
        $return = array('result' => 'OTP is expired, please try again', 'status_code' => 204);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'OTP is not valid, please try again', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // Check login user
  public function checkUser($user_id, $device_id)
  {
    $result = DB::table('users')->where('role', 3)->where('status', 1)->where('id', $user_id)->where('device_id', $device_id)->get();
    if (count($result) > 0) {
      $return = array('result' => true, 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $return = array('result' => false, 'status_code' => 200);
      exit(json_encode($return));
    }
  }

  // listing of departments
  public function departments()
  {
    $result = DB::table('departments')->select('id', 'name')->where('status', 1)->get();
    $return = array('result' => $result, 'status_code' => 200);
    exit(json_encode($return));
  }

  //list of advisors as per dealer id
  public function getAdvisors($dealer_id)
  {
    if (@$dealer_id) {
      $result = DB::table('advisors as a')
        ->join('departments as d', 'd.id', '=', 'a.department')
        ->select('a.id', 'a.name', 'a.department', 'd.name as department_name')->where('a.dealer_id', $dealer_id)
        ->where('a.status', 1)
        ->get();

      $newresult = array();
      foreach ($result as $value) {
        $data['id'] = $value->id;
        $data['name'] = $value->name . "-" . $value->department_name;
        $newresult[] = $data;
      }

      if (count($result) > 0) {
        $return = array('result' => $newresult, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result = array();
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Something went wrong!', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  //list of models as per dealer id
  public function getModels($dealer_id)
  {
    if (@$dealer_id) {
      $template = DB::table('dealer_templates')->where('dealer_id', $dealer_id)->first();

      if (!empty($template)) {
        $models = DB::table('treatments')
          ->select('model_id')
          ->where('temp_id', $template->template_id)
          ->groupBy('model_id')
          ->get();
        if (!empty($models)) {
          $model = array();
          foreach ($models as $val) {
            $model[] = $val->model_id;
          }
          //dd($model);
          $result = DB::table('models')
            ->select('id', 'model_name as name')
            ->whereIn('id', $model)
            ->get();
          //dd($result);
          if (count($result) > 0) {
            $return = array('result' => $result, 'status_code' => 200);
            exit(json_encode($return));
          } else {
            $result = array();
            $return = array('result' => $result, 'status_code' => 200);
            exit(json_encode($return));
          }
        } else {
          $return = array('result' => array(), 'status_code' => 200);
          exit(json_encode($return));
        }
      } else {
        $return = array('result' => array(), 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Something went wrong!', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  //list of models as per dealer id
  public function getTreatments($dealer_id, $model_id)
  {
    if (@$model_id) {
      $result = DB::table('treatments')
        ->select('id', 'treatment', 'treatment_type', 'dealer_price', 'incentive', 'customer_price', 'labour_code')
        //->where('dealer_id',$dealer_id)
        ->where('model_id', $model_id)
        ->where('status', 1)
        ->get();

      if (count($result) > 0) {
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result = array();
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Something went wrong!', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // add new job
  public function addJob(Request $decoded)
  {
    // $str = str_replace('"', '', $decoded->treatments);
    // $data = explode(',', $str);

    // foreach ($data as $val) { 
    //   $value = DB::table('treatments')->where('id',$val)->first();
    //   if($value->treatment_type == 1){
    //     $hvt_value = $hvt_value + $value->customer_price;
    //     $i++;
    //   }
    //   $customer_price = $customer_price + $value->customer_price;
    //   $dealer_price = $dealer_price + $value->dealer_price;
    //   $incentive = $incentive + $value->incentive;
    //   $treat_id['id'] = $value->id;
    //   $treat_id['treatment'] = $value->treatment;
    //   $treat_id['treatment_type'] = $value->treatment_type;
    //   $treat_id['customer_price'] = $value->customer_price;
    //   $treat_id['dealer_price'] = $value->dealer_price;
    //   $treat_id['incentive'] = $value->incentive;
    //   $treat_id['labour_code'] = $value->labour_code;
    //   $treatment_data[] = $treat_id;  
    // }
    $treatment_id = array();
    $treatment_data = array();
    $i = $customer_price = $actual_price = $difference_price = $hvt_value = $dealer_price = $incentive = 0;
    foreach (json_decode($decoded['treatments']) as $key => $value) {
      $data1 = DB::table('treatments')->where('id', $value->id)->first();
      $data1->job_type = $value->job_type;
      $data1->actualPrice = $value->actualPrice;
      $data1->difference = $value->difference;
      $treatment_data[] = $data1;
      if ($data1->job_type == '5') {
        $actual_price = $actual_price + $data1->actualPrice;
        $difference_price = $difference_price + $data1->difference;
      } else {
        $actual_price = 0;
        $difference_price = 0;
      }
      $customer_price = $customer_price + $data1->customer_price;
      $dealer_price = $dealer_price + $data1->dealer_price;
      $incentive = $incentive + $data1->incentive;
      if ($data1->treatment_type == 1) {
        $i++;
        // $hvt_value = $hvt_value + $data1->customer_price;
        if ($data1->job_type == '5') {
          $hvt_value = $hvt_value + $data1->actualPrice;
        } else {
          $hvt_value = 0;
        }
      }
      $treat_id['id'] = $data1->id;
      $treatment_id[] = $treat_id;
    }

    $jobData['user_id'] = $decoded->user_id;
    $jobData['job_date'] = $decoded->job_date;
    $jobData['job_card_no'] = $decoded->job_card_no . '-M';
    $jobData['bill_no'] = $decoded->bill_no;
    $jobData['regn_no'] = trim($decoded->regn_no);
    $jobData['dealer_id'] = $decoded->dealer_id;
    $jobData['model_id'] = $decoded->model_id;
    $jobData['advisor_id'] = $decoded->advisor_id;
    $jobData['department_id'] = getDealerDepartment($decoded->advisor_id);
    $jobData['treatments'] = json_encode($treatment_data);
    $jobData['treatment_total'] = count($treatment_id);
    $jobData['hvt_total'] = $i;
    $jobData['hvt_value'] = $hvt_value;
    $jobData['vas_value'] = $customer_price;
    $jobData['vas_total'] = count($treatment_id);
    $jobData['remarks'] = $decoded->remarks;
    $jobData['customer_price'] = $customer_price;
    $jobData['actual_price'] = $actual_price;
    $jobData['difference_price'] = $difference_price;
    $jobData['dealer_price'] = $dealer_price;
    $jobData['incentive'] = $incentive;
    //$checkCardNo = DB::table('jobs')->where('job_card_no',$decoded->job_card_no)->where('delete_job',1)->get();
    // if(!empty($decoded->bill_no)){
    //   $checkBillNo = DB::table('jobs')->where('bill_no',$decoded->bill_no)->where('delete_job',1)->get();
    // }else{
    //   $checkBillNo = array();
    // }
    // if(!empty($decoded->regn_no)){
    // $checkRegnNo = DB::table('jobs')->where('regn_no',$decoded->regn_no)->where('delete_job',1)->where('job_date',$decoded->job_date)->get();
    // }else{
    //   $checkRegnNo = array();
    // }

    // $message = array();
    // if(count($checkCardNo) > 0)
    // {
    //   $message[] = 'Job card no';
    // }
    // if(count($checkBillNo) > 0)
    // {
    //   $message[] = 'Bill no';
    // }
    // if(count($checkRegnNo) > 0)
    // {
    //   $message[] = 'Regn no';
    // }
    // if(@$message)
    // {
    //   $return = array('result' => $message,'status_code'=>204);
    //         exit(json_encode($return));
    // }else{
    $addrecord = DB::table('jobs')->insert($jobData);
    $id = DB::getPdo()->lastInsertId();
    foreach ($treatment_data as $value) {
      $dataInsert = array(
        'job_id' => $id,
        'treatment_id' => $value->id,
      );
      DB::table('jobs_treatment')->insert($dataInsert);
    }
    if ($addrecord) {
      $return = array('result' => 'Added Successfully!', 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
    //}
  }

  // search job by regn no.
  public function searchJob($user_id, $regn_no)
  {
    if (@$user_id && @$regn_no) {
      $dealer_id = get_dealer_id($user_id);
      $result = DB::table('jobs')
        ->leftjoin('users as u', 'u.id', '=', 'jobs.dealer_id')
        ->join('models as m', 'm.id', '=', 'jobs.model_id')
        //->join('departments as d','d.id','=','jobs.department_id')
        ->join('advisors as a', 'a.id', '=', 'jobs.advisor_id')
        ->select('jobs.*', 'u.name as dealer', 'm.model_name as model', 'a.name as advisor')
        ->where('jobs.dealer_id', '=', $dealer_id)
        ->where('jobs.regn_no', 'LIKE', '%' . $regn_no)
        ->get();
      foreach ($result as $key => $value) {
        $result[$key]->treatments = json_decode($value->treatments);
      }
      if (count($result) > 0) {
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result = array();
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // list of jobs
  public function getJobs($user_id, $date)
  {
    if (@$user_id && @$date) {
      $dated = date('Y-m-d', strtotime($date));
      $dealer_id = get_dealer_id($user_id);
      $month = explode('-', date('Y-m'));
      $result = DB::table('jobs')
        ->join('users as u', 'u.id', '=', 'jobs.dealer_id')
        ->join('models as m', 'm.id', '=', 'jobs.model_id')
        //->join('departments as d','d.id','=','jobs.department_id')
        ->join('advisors as a', 'a.id', '=', 'jobs.advisor_id')
        ->select('jobs.*', 'u.name as dealer', 'm.model_name as model', 'a.name as advisor')
        // ->where('jobs.dealer_id','=',$dealer_id)
        ->where('jobs.user_id', '=', $user_id)
        ->whereMonth('job_date', $month[1])
        ->whereYear('job_date', $month[0])
        ->where('jobs.delete_job', 1)
        ->whereDate('jobs.job_date', '=', $dated)
        ->get();
      foreach ($result as $key => $value) {
        $result[$key]->treatments = json_decode($value->treatments);
      }
      if (count($result) > 0) {
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result = array();
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // edit existing job
  public function editJob(Request $decoded)
  {
    // $str = str_replace('"', '', $decoded->treatments);
    // $data = explode(',', $str);
    // foreach ($data as $val) {
    //   $value = DB::table('treatments')->where('id',$val)->first();
    //   if($value->treatment_type == 1){
    //     $hvt_value = $hvt_value + $value->customer_price;
    //     $i++;
    //   }
    //   $customer_price = $customer_price + $value->customer_price;
    //   $dealer_price = $dealer_price + $value->dealer_price;
    //   $incentive = $incentive + $value->incentive;
    //   $treat_id['id'] = $value->id;
    //   $treat_id['treatment'] = $value->treatment;
    //   $treat_id['treatment_type'] = $value->treatment_type;
    //   $treat_id['customer_price'] = $value->customer_price;
    //   $treat_id['dealer_price'] = $value->dealer_price;
    //   $treat_id['incentive'] = $value->incentive;
    //   $treat_id['labour_code'] = $value->labour_code;
    //   $treatment_data[] = $treat_id;
    // }
    $job_id = $decoded->job_id;
    $treatment_id = array();
    $treatment_data = array();
    $i = $customer_price = $actual_price = $difference_price = $hvt_value = $dealer_price = $incentive = 0;
    foreach (json_decode($decoded['treatments']) as $key => $value) {
      $data1 = DB::table('treatments')->where('id', $value->id)->first();
      $data1->job_type = $value->job_type;
      $data1->actualPrice = $value->actualPrice;
      $data1->difference = $value->difference;
      $treatment_data[] = $data1;
      if ($data1->job_type == '5') {
        $actual_price = $actual_price + $data1->actualPrice;
        $difference_price = $difference_price + $data1->difference;
      } else {
        $actual_price = 0;
        $difference_price = 0;
      }
      $customer_price = $customer_price + $data1->customer_price;
      $dealer_price = $dealer_price + $data1->dealer_price;
      $incentive = $incentive + $data1->incentive;
      if ($data1->treatment_type == 1) {
        $i++;
        // $hvt_value = $hvt_value + $data1->customer_price;
        if ($data1->job_type == '5') {
          $hvt_value = $hvt_value + $data1->actualPrice;
        } else {
          $hvt_value = 0;
        }
      }
      $treat_id['id'] = $data1->id;
      $treatment_id[] = $treat_id;
    }
    $jobData['user_id'] = $decoded->user_id;
    $jobData['job_date'] = $decoded->job_date;
    $jobData['job_card_no'] = $decoded->job_card_no;
    $jobData['bill_no'] = $decoded->bill_no;
    $jobData['regn_no'] = trim($decoded->regn_no);
    $jobData['dealer_id'] = $decoded->dealer_id;
    $jobData['model_id'] = $decoded->model_id;
    $jobData['advisor_id'] = $decoded->advisor_id;
    $jobData['department_id'] = getDealerDepartment($decoded->advisor_id);
    $jobData['treatments'] = json_encode($treatment_data);
    $jobData['treatment_total'] = count($treatment_id);
    $jobData['hvt_total'] = $i;
    $jobData['hvt_value'] = $hvt_value;
    $jobData['vas_value'] = $customer_price;
    $jobData['vas_total'] = count($treatment_id);
    $jobData['remarks'] = $decoded->remarks;
    $jobData['customer_price'] = $customer_price;
    $jobData['actual_price'] = $actual_price;
    $jobData['difference_price'] = $difference_price;
    $jobData['dealer_price'] = $dealer_price;
    $jobData['incentive'] = $incentive;
    //$checkCardNo = DB::table('jobs')->where('delete_job',1)->where('job_card_no',$decoded->job_card_no)->where('id','!=',$job_id)->get();
    // if(!empty($decoded->bill_no)){
    //   $checkBillNo = DB::table('jobs')->where('delete_job',1)->where('bill_no',$decoded->bill_no)->where('id','!=',$job_id)->get();
    // }else{
    //   $checkBillNo = array();
    // }
    // if(!empty($decoded->regn_no)){
    //   $checkRegnNo=DB::table('jobs')->where('delete_job',1)->where('regn_no',$decoded->regn_no)->where('id','!=',$job_id)->where('job_date',$decoded->job_date)->get();
    // }else{
    //   $checkRegnNo = array();
    // }
    // $message = array();
    // if(count($checkCardNo) > 0)
    // {
    //   $message[] = 'Job card no';
    // }
    // if(count($checkBillNo) > 0)
    // {
    //   $message[] = 'Bill no';
    // }
    // if(count($checkRegnNo) > 0)
    // {
    //   $message[] = 'Regn no';
    // }
    // if(@$message)
    // {
    //   $return = array('result' => $message,'status_code'=>204);
    //         exit(json_encode($return));
    // }else{
    $editrecord = DB::table('jobs')->where('id', $job_id)->update($jobData);
    DB::table('jobs_treatment')->where('job_id', $job_id)->delete();
    foreach ($treatment_data as $value) {
      $data1 = array(
        'job_id' => $job_id,
        'treatment_id' => $value->id,
      );
      DB::table('jobs_treatment')->insert($data1);
    }
    if ($editrecord) {
      $return = array('result' => 'Updated Successfully!', 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
    // }
  }

  // get images
  public function getImages()
  {
    $result = DB::table('gallery')
      ->select('gallery.*', DB::raw("CONCAT('" . str_replace("index.php", '', URL('/')) . "images/', path) as full_path"))
      ->where('type', '=', 'Image')
      ->where('status', '=', 1)
      ->get();

    if (count($result) > 0) {
      $return = array('result' => $result, 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $result = array();
      $return = array('result' => $result, 'status_code' => 200);
      exit(json_encode($return));
    }
  }

  // get videos
  public function getVideos()
  {
    $result = DB::table('gallery')
      ->select('gallery.*')
      ->where('type', '=', 'video')
      ->where('status', '=', 1)
      ->get();

    if (count($result) > 0) {
      $return = array('result' => $result, 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $result = array();
      $return = array('result' => $result, 'status_code' => 200);
      exit(json_encode($return));
    }
  }

  // home view
  public function getCalendar($user_id)
  {
    if (@$user_id) {
      $dealer_id = get_dealer_id($user_id);
      $mtd_total = $total = 0;
      $month = explode('-', date('Y-m'));
      $first_day = date('Y-m-01');
      $today = date('Y-m-d');
      $data = DB::table('jobs')
        ->select(DB::raw('SUM(customer_price) as customer_price, SUM(actual_price) as actual_price, SUM(difference_price) as difference_price, SUM(hvt_total) as hvt_total, SUM(hvt_value) as hvt_value, SUM(vas_total) as vas_total, SUM(vas_value) as vas_value, job_date'))
        // ->whereDate('job_date','>=',$first_day)
        // ->whereDate('job_date','<=',$today)
        ->groupBy('job_date')
        ->where('dealer_id', $dealer_id)
        ->whereMonth('job_date', $month[1])
        ->whereYear('job_date', $month[0])
        ->where('delete_job', 1)
        ->get();
      $result1 = array();
      $mtd_customer_price = $mtd_hvt = $mtd_hvt_value = $mtd_vas = $mtd_vas_value = 0;
      foreach ($data as $value) {
        $total = DB::table('jobs_by_date')
          ->select(DB::raw('SUM(total_jobs) as mtd_total'))
          ->whereDate('job_added_date', '>=', $first_day)
          ->whereDate('job_added_date', '<=', $value->job_date)
          ->where('dealer_id', $dealer_id)
          ->first();
        // dd($total);
        $mtd_customer_price = $mtd_customer_price + $value->customer_price;
        $mtd_hvt = $mtd_hvt + $value->hvt_total;
        $mtd_hvt_value = $mtd_hvt_value + $value->hvt_value;
        $mtd_vas = $mtd_vas + $value->vas_total;
        // $mtd_vas_value= $mtd_vas_value + $value->vas_value;
        $mtd_vas_value = $mtd_vas_value + $value->actual_price;
        $result = DB::table('jobs_by_date')
          ->select('jobs_by_date.total_jobs')
          ->whereDate('jobs_by_date.job_added_date', '=', $value->job_date)
          ->where('jobs_by_date.dealer_id', '=', $dealer_id)
          ->first();

        $res['total'] = (@$result->total_jobs) ? $result->total_jobs : '0';
        $res['mtd_total'] = (@$total->mtd_total) ? $total->mtd_total : '0';
        $res['customer_price'] = $value->customer_price;
        $res['mtd_customer_price'] = $mtd_customer_price;
        $res['hvt_total'] = $value->hvt_total;
        $res['mtd_hvt'] = $mtd_hvt;
        $res['hvt_value'] = $value->hvt_value;
        $res['hvt_percentage'] = hvt_in_percentage(@$value->hvt_value, @$value->vas_value);
        $res['mtd_hvt_value'] = $mtd_hvt_value;
        $res['mtd_hvt_percentage'] = hvt_in_percentage(@$mtd_hvt_value, @$mtd_vas_value);
        $res['vas_total'] = $value->vas_total;
        $res['mtd_vas'] = $mtd_vas;
        $res['vas_value'] = $value->vas_value;
        $res['mtd_vas_value'] = $mtd_vas_value;
        $res['job_date'] = $value->job_date;
        $result1[] = $res;
      }
      $is_login = getUserLogin(@$user_id);
      if (count($result1) > 0) {
        $return = array('result' => $result1, 'is_login' => $is_login, 'user_dealer' => $dealer_id, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result1 = array();
        $return = array('result' => $result1, 'is_login' => $is_login, 'user_dealer' => $dealer_id, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // home view
  public function getCalendarByMonth($user_id, $monthYear)
  {
    if (@$user_id && @$monthYear) {
      $dealer_id = get_dealer_id($user_id);
      $mtd_total = $total = 0;
      $explode_monthYear = explode('-', $monthYear);
      $month = $explode_monthYear[0];
      $year = $explode_monthYear[1];
      $first_day = date('Y-m-01');
      $today = date('Y-m-d');

      $data = DB::table('jobs')
        ->select(DB::raw('SUM(customer_price) as customer_price,  SUM(hvt_total) as hvt_total, SUM(hvt_value) as hvt_value,SUM(vas_total) as vas_total, SUM(vas_value) as vas_value, job_date'))
        ->whereMonth('job_date', $month)
        ->whereYear('job_date', $year)
        ->groupBy('job_date')
        ->where('dealer_id', $dealer_id)
        ->where('delete_job', 1)
        ->get();
      $result1 = array();
      $mtd_customer_price = $mtd_hvt = $mtd_hvt_value = $mtd_vas = $mtd_vas_value = 0;

      foreach ($data as $value) {
        $total = DB::table('jobs_by_date')
          ->select(DB::raw('SUM(total_jobs) as mtd_total'))
          ->whereDate('job_added_date', '>=', date('Y-m-01', strtotime($value->job_date)))
          ->whereDate('job_added_date', '<=', $value->job_date)
          ->where('dealer_id', $dealer_id)
          ->first();
        //dd($total);
        $mtd_customer_price = $mtd_customer_price + $value->customer_price;
        $mtd_hvt = $mtd_hvt + $value->hvt_total;
        $mtd_hvt_value = $mtd_hvt_value + $value->hvt_value;
        $mtd_vas = $mtd_vas + $value->vas_total;
        $mtd_vas_value = $mtd_vas_value + $value->vas_value;

        $result = DB::table('jobs_by_date')
          ->select('jobs_by_date.total_jobs')
          ->whereDate('jobs_by_date.job_added_date', '=', $value->job_date)
          ->where('jobs_by_date.dealer_id', '=', $dealer_id)
          ->first();

        $res['total'] = (@$result->total_jobs) ? $result->total_jobs : '0';
        $res['mtd_total'] = (@$total->mtd_total) ? $total->mtd_total : '0';
        $res['customer_price'] = $value->customer_price;
        $res['mtd_customer_price'] = $mtd_customer_price;
        $res['hvt_total'] = $value->hvt_total;
        $res['mtd_hvt'] = $mtd_hvt;
        $res['hvt_value'] = $value->hvt_value;
        $res['hvt_percentage'] = hvt_in_percentage(@$value->hvt_value, @$value->vas_value);
        $res['mtd_hvt_value'] = $mtd_hvt_value;
        $res['mtd_hvt_percentage'] = hvt_in_percentage(@$mtd_hvt_value, @$mtd_vas_value);
        $res['vas_total'] = $value->vas_total;
        $res['mtd_vas'] = $mtd_vas;
        $res['vas_value'] = $value->vas_value;
        $res['mtd_vas_value'] = $mtd_vas_value;
        $res['job_date'] = $value->job_date;
        $result1[] = $res;
      }

      if (count($result1) > 0) {
        $return = array('result' => $result1, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result1 = array();
        $return = array('result' => $result1, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  // Add service load
  public function addServices(Request $decoded)
  {
    // $entityBody = file_get_contents('php://input');
    // $decoded = json_decode($entityBody);
    $jobData['user_id'] = $decoded->user_id;
    $jobData['dealer_id'] = $decoded->dealer_id;
    $jobData['total_jobs'] = $decoded->total_jobs;
    $jobData['job_added_date'] = $decoded->job_added_date;
    $checkData = DB::table('jobs_by_date')->where('user_id', $decoded->user_id)->where('job_added_date', $decoded->job_added_date)->get();
    if (count($checkData) > 0) {
      $addrecord = DB::table('jobs_by_date')->where('user_id', $decoded->user_id)->where('job_added_date', $decoded->job_added_date)->update($jobData);
    } else {
      $addrecord = DB::table('jobs_by_date')->insert($jobData);
    }

    if ($addrecord) {
      $return = array('result' => 'Added Successfully!', 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $return = array('result' => 'Added Successfully!', 'status_code' => 200);
      exit(json_encode($return));
    }
  }

  // Web view for url
  public function getUrl()
  {
    $result = url('admin');
    $return = array('result' => $result, 'status_code' => 200);
    exit(json_encode($return));
  }

  // Get App Version
  public function getAppVersion()
  {
    $result = DB::table('app_version')->first();
    $return = array('result' => $result, 'status_code' => 200);
    exit(json_encode($return));
  }

  // Web view for Privacy Policy
  public function privacyPolicy()
  {
    $finalData = view('auto-solution-privacy-policy')->render();
    $final_data = (string)$finalData;
    $url = 'http://dev.01s.in/auto_solution/public/index.php/privacy_policy';
    return json_encode(array('result' => $url, 'status_code' => 200));
  }

  // Search history jobs
  public function searchHistoryJob($dealer_id, $regn_no)
  {
    if (@$dealer_id && @$regn_no) {
      $result = DB::table('history_jobs as h')
        ->join('users as u', 'u.id', '=', 'h.dealer_id')
        ->select('u.name as dealer_name', 'h.labour_code', 'h.job_card as job_card_no', 'h.job_date', 'h.bill_no', 'h.regn_no', 'h.advisor', 'h.model', 'h.treatment')
        ->where('h.dealer_id', '=', $dealer_id)
        ->where('h.regn_no', 'LIKE', '%' . $regn_no)
        ->get();

      if (count($result) > 0) {
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $result = array();
        $return = array('result' => $result, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }

  public function attendance(Request $decoded)
  {
    $checkjobreport = DB::table('jobs')->where(['user_id' => $decoded->user_id, 'job_date' => date('Y-m-d')])->get();
    $checkRO = DB::table('jobs_by_date')->where(['user_id' => $decoded->user_id, 'job_added_date' => date('Y-m-d')])->first();
    $checkDealer = get_users_dealer($decoded->user_id);
    // dd($checkDealer);
    $userDesignation = get_designation($decoded->user_id);
    if (!empty($decoded->user_id) && !empty($decoded->dealer_id) && !empty($decoded->date)) {
      $checkAttendance = DB::table('attendance')->where('user_id', $decoded->user_id)->where('dealer_id', $decoded->dealer_id)->where('date', $decoded->date)->first();
      $dealer_data = getLocationofDealer($decoded->dealer_id);
      if (!empty($checkAttendance)) {
        if (!empty($decoded->lat) && !empty($decoded->longi)) {
          if ($userDesignation == "3" || $userDesignation == "13" || $userDesignation == "23") {
            $updateData = array(
              'out_time' => getCurrentTime(),
              'out_lat' => $decoded->lat,
              'out_longi' => $decoded->longi,
              'attendance_status' => 2,
              'updated_at' => getCurrentTimestamp(),
            );
            DB::table('attendance')->where('id', $checkAttendance->id)->update($updateData);
            $return = array('result' => 'Thanks for check-out!', 'status_code' => 200);
          } else {
            if ($checkDealer == $decoded->dealer_id) {
              if (count($checkjobreport) > 0 && !empty($checkRO)) {
                $getDistance = calculateDistanceOfLatLng($decoded->lat, $decoded->longi, @$dealer_data->latitude, @$dealer_data->longitude, 'MT');
                if ($getDistance <= 100) {
                  $updateData = array(
                    'out_time' => getCurrentTime(),
                    'out_lat' => $decoded->lat,
                    'out_longi' => $decoded->longi,
                    'attendance_status' => 2,
                    'updated_at' => getCurrentTimestamp(),
                  );
                  DB::table('attendance')->where('id', $checkAttendance->id)->update($updateData);
                  $return = array('result' => 'Thanks for check-out!', 'status_code' => 200);
                } else {
                  $return = array('result' => 'Your location is not correct', 'status_code' => 204);
                }
              } else {
                $return = array('result' => 'Please Update Job Report.', 'status_code' => 204);
              }
            } else {
              $return = array('result' => 'Sorry, You cannot mark your attendance here', 'status_code' => 204);
            }
          }
        } else {
          $return = array('result' => 'Please fill Out time.', 'status_code' => 204);
        }
      } else {
        if (!empty($decoded->lat) && !empty($decoded->longi)) {
          if ($userDesignation == "3" || $userDesignation == "13" || $userDesignation == "23") {
            $insertData = array(
              'user_id' => $decoded->user_id,
              'dealer_id' => $decoded->dealer_id,
              'date' => $decoded->date,
              'in_time' => getCurrentTime(),
              'in_lat' => $decoded->lat,
              'in_longi' => $decoded->longi,
              'attendance_status' => 1,
              'created_at' => getCurrentTimestamp(),
            );
            DB::table('attendance')->insert($insertData);
            $return = array('result' => 'Thanks for check-in!', 'status_code' => 200);
          } else {
            $getDistance = calculateDistanceOfLatLng($decoded->lat, $decoded->longi, @$dealer_data->latitude, @$dealer_data->longitude, 'MT');
            if ($checkDealer == $decoded->dealer_id) {
              if ($getDistance <= 1000) {
                $insertData = array(
                  'user_id' => $decoded->user_id,
                  'dealer_id' => $decoded->dealer_id,
                  'date' => $decoded->date,
                  'in_time' => getCurrentTime(),
                  'in_lat' => $decoded->lat,
                  'in_longi' => $decoded->longi,
                  'attendance_status' => 1,
                  'created_at' => getCurrentTimestamp(),
                );
                DB::table('attendance')->insert($insertData);
                $return = array('result' => 'Thanks for check-in!', 'status_code' => 200);
              } else {
                $return = array('result' => 'Your location is not correct', 'status_code' => 204);
              }
            } else {
              $return = array('result' => 'Sorry, You cannot mark your attendance here', 'status_code' => 204);
            }
          }
        } else {
          $return = array('result' => 'Please fill In time.', 'status_code' => 204);
        }
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
    }
    exit(json_encode($return));
  }
  // check attendance
  public function checkAttendance(Request $request)
  {

    if (!empty($request->user_id)) {
      $checkAttendance = DB::table('attendance')->where('user_id', $request->user_id)->where('date', date('Y-m-d'))->first();
      $is_login = getUserLogin($request->user_id);
      $user_dealer = get_dealer_id($request->user_id);
      if (!empty($checkAttendance)) {
        $return = array('result' => 'out', 'is_login' => $is_login, 'user_dealer' => $user_dealer, 'status_code' => 200);
        exit(json_encode($return));
      } else {
        $return = array('result' => 'in', 'is_login' => $is_login, 'user_dealer' => $user_dealer, 'status_code' => 200);
        exit(json_encode($return));
      }
    } else {
      $return = array('result' => 'Please try again.', 'status_code' => 204);
      exit(json_encode($return));
    }
  }
  public function getDistance(Request $request)
  {
    $data = calculateDistanceOfLatLng($request->lat1, $request->lng1, $request->lat2, $request->lng2, 'mt');
    $return = array('result' => $data, 'status_code' => 200);
    exit(json_encode($return));
  }

  public function myProfile(Request $request)
  {
    if (!empty($request->user_id)) {
      $data = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->select('users.firm_id as firm', 'users.name as name', 'users.mobile_no as mobile', 'users.email as email', 'sd.department_id as department', 'sd.designation_id as designation', 'sd.emp_code as emp_code', 'sd.doj as doj')->where('users.id', $request->user_id)->first();
      $result = array(
        'firm' => get_firm_name($data->firm),
        'name' => $data->name,
        'mobile' => $data->mobile,
        'email' => $data->email,
        'department' => get_department_name($data->department),
        'designation' => get_designation_name($data->designation),
        'emp_code' => $data->emp_code,
        'doj' => $data->doj,
      );
      $return = array('result' => $result, 'status_code' => 200);
      exit(json_encode($return));
    } else {
      $return = array('result' => 'User does not exist', 'status_code' => 204);
      exit(json_encode($return));
    }
  }
}
