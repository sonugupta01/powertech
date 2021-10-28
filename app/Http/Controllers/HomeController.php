<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use Hash;
use DB;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\atten_report;
use Excel;
use Illuminate\Support\Facades\Log;
use DateTime;

class HomeController extends Controller
{
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        //$this->middleware('auth');
        
    }
    
    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        if (Auth::check() && Auth::user()->role == '1')
        {
            return redirect('/admin');
        }
        else
        {
            return view('home');
        }
    }
    
    // View login page
    public function login()
    {
        if (Auth::check())
        {
            return redirect('/admin');
        }
        else
        {
            // return view('auth.underconstruction');
            return view('auth.login');
        }
    }
    // Logout
    public function logout()
    {
        Auth::logout();
        Session::flash('success', 'You have been logged out');
        return redirect('/login');
    }
    
    // Check login authentication
    public function checklogin(Request $request)
    {
        if (Auth::attempt(array(
            'email' => $request->email,
            'password' => $request->password
            )))
            {
                $designation = DB::table('staff_detail')->where('user_id', Auth::id())
                ->first();
                if (Auth::user()->role == '1')
                {
                    return redirect('/admin');
                }
                else if (Auth::user()->role == '5')
                {
                    return redirect('/asm');
                }
                else if ($designation->designation_id == '13')
                {
                    return redirect('/rsm');
                }
                else if ($designation->designation_id == '23')
                {
                    return redirect('/sse');
                }
                else
                {
                    Auth::logout();
                    Session::flash('error', "Please fill valid email or password!");
                    return redirect('admin/login');
                }
            }
            else
            {
                Session::flash('error', 'Please fill valid email or password!');
                return redirect('admin/login');
            }
        }
        
        // view change password page
        public function changepassword()
        {
            return view('changepassword');
        }
        
        // update password
        public function updatePassword(Request $request)
        {
            $this->validate($request, ['current_password' => 'required', 'new_password' => 'required|min:6', 'confirm_password' => 'required|same:new_password', ], ['current_password.required' => 'Please enter current password', 'new_password.required' => 'Please enter new password', 'confirm_password.required' => 'Please enter confirm password', ]);
            
            if (!Hash::check($request->current_password, Auth::user()
            ->password))
            {
                return back()
                ->with('error', 'Please enter correct current password!');
            }
            else
            {
                $data = array(
                    'password' => Hash::make($request->new_password) ,
                );
                User::where('id', Auth::user()
                ->id)
                ->update($data);
                Session::flash('success', 'Your password changed successfully!');
                return redirect('changepassword');
            }
        }
        
        // Get District through state id in Ajax
        public function getDistrict(Request $request)
        {
            $post = $request->all();
            $state = $request->state;
            $districts = DB::table('districts')->where('state_id', $state)->orderBy('district_name', 'ASC')
            ->get();
            $districts = json_decode(json_encode($districts) , true);
            
            if (@$districts)
            {
                $res = '<option value="">Select District</option>';
                foreach ($districts as $district)
                {
                    $district_name = $district['district_name'];
                    $district_id = $district['district_id'];
                    $res .= "<option value='$district_id'>$district_name</option>";
                }
            }
            else
            {
                $treatment = $treat['treatment'];
                $id = $treat['id'];
                $res .= "<option value='$id'>$treatment</option>";
                
            }
            return $res;
        }
        
        // Get model through dealer id in Ajax
        public function getModels(Request $request)
        {
            $post = $request->all();
            $dealer = $request->dealer;
            if (!empty($dealer))
            {
                // $template = DB::table('dealer_templates')->where('dealer_id', $dealer)->first();
                // if (!empty($template)) {
                    // $models = DB::table('treatments')
                    //     ->select('model_id')
                    //     ->where('temp_id', $template->template_id)
                    //     ->groupBy('model_id')
                    //     ->get();
                    $models = DB::table('dealer_templates as dt')->join('treatments as t', 'dt.template_id', '=', 't.temp_id')
                    ->select('t.model_id')
                    ->groupBy('t.model_id')
                    ->where('dt.dealer_id', $dealer)->get();
                    
                    if (!empty($models))
                    {
                        $model = array();
                        foreach ($models as $val)
                        {
                            $model[] = $val->model_id;
                        }
                        $result = DB::table('models')->select('id', 'model_name')
                        ->whereIn('id', $model)->get();
                        
                        if (count($result) > 0)
                        {
                            $res = '<option value="">Select Model</option>';
                            foreach ($result as $model)
                            {
                                $model_name = $model->model_name;
                                $id = $model->id;
                                $res .= "<option value='$id'>$model_name</option>";
                            }
                        }
                        else
                        {
                            $res = "<option value=''>No Model found</option>";
                        }
                    }
                    else
                    {
                        $res = "<option value=''>No Model found</option>";
                    }
                    // } else {
                        //     $res = "<option value=''>No Model found</option>";
                        // }
                        
                    }
                    else
                    {
                        $res = "<option value=''>No Model found</option>";
                    }
                    return $res;
                }
                
                public function getJobId(Request $request)
                {
                    $current_date = getCurrentDate();
                    $res = array();
                    $res['status'] = 0;
                    $valid = DB::table('treatments')->where('id', $request->treatment_id)->first();
                    $time_period = $valid->time_period;
                    $time_unit = $valid->time_unit;
                    
                    if ($time_unit == 1) {
                        $res['total_days'] = $time_period * 365; 
                    } elseif ($time_unit == 2) {
                        $res['total_days'] = $time_period * 30;
                    } elseif ($time_unit == 3) {
                        $res['total_days'] = $time_period;
                    }
                    
                    $job_id = DB::table('jobs_treatment')->where('treatment_id', $request->treatment_id)->first();
                    if (!empty($job_id)) {
                        $data = DB::table('jobs')->where('id', $job_id->job_id)->get();
                        foreach ($data as $key => $value) {
                        //     $regn_no = $data->regn_no;
                        // $date_added = $data->date_added;
                        if ($value->regn_no == $request->regn_no) {
                            $diff = strtotime($current_date) - strtotime($date_added);
                            $days_spent = ceil(abs($diff / 86400));
                            if ($days_spent < $res['total_days']) {
                                $res['status'] == 1;
                            } else {
                                $res['status'] == 0;
                            }                 
                        } else {
                            $res['status'] == 0;
                        }
                        }
                    } else {
                        $res['status'] == 0;
                    }   
                    return $res;
                }
                
                // Get OEM templates through dealer id in Ajax
                public function getOEMtemplates(Request $request)
                {
                    $oem_id = $request->oem_id;
                    $templates = DB::table('treatment_templates')->where(['oem_id' => $oem_id])->get();
                    $templates = json_decode(json_encode($templates) , true);
                    if (@$templates)
                    {
                        $res = '<option value="">Select template</option>';
                        foreach ($templates as $template)
                        {
                            $name = ucwords($template['temp_name']);
                            $id = $template['id'];
                            $res .= "<option value='$id'>$name</option>";
                        }
                    }
                    else
                    {
                        $res = "<option value=''>No template found</option>";
                    }
                    return $res;
                }
                
                //get Models Of selected OEM
                public function getOemModels(Request $request)
                {
                    $oem_id = ($request->oem_id == '') ? 0 : $request->oem_id;
                    $template_id = ($request->template_id == '') ? 0 : $request->template_id;
                    $models = DB::table('models')->where(['oem_id' => $oem_id, 'template_id' => $template_id])->orderBy('id', 'ASC')
                    ->get();
                    $models = json_decode(json_encode($models) , true);
                    if (@$models)
                    {
                        $res = '<option value="">Select Model</option>';
                        foreach ($models as $model)
                        {
                            $model_name = $model['model_name'];
                            $model_id = $model['id'];
                            $res .= "<option value='$model_id'>$model_name</option>";
                        }
                    }
                    else
                    {
                        $res = "<option value=''>No Model Found</option>";
                    }
                    return $res;
                }
                
                // Get advisors through dealer id in Ajax
                public function getAdvisors(Request $request)
                {
                    $post = $request->all();
                    $dealer = $request->dealer;
                    $advisors = DB::table('advisors as a')->join('dealer_department as d', 'd.id', '=', 'a.department')
                    ->select('a.id', 'a.name', 'a.department', 'd.name as department_name')
                    ->where('a.dealer_id', $dealer)->where('a.status', 1)
                    ->get();
                    $advisors = json_decode(json_encode($advisors) , true);
                    if (@$advisors)
                    {
                        $res = '<option value="">Select Advisor</option>';
                        foreach ($advisors as $advisor)
                        {
                            $name = $advisor['name'] . '-' . $advisor['department_name'];
                            $id = $advisor['id'];
                            $res .= "<option value='$id'>$name</option>";
                        }
                    }
                    else
                    {
                        $res = "<option value=''>No Advisor found</option>";
                    }
                    return $res;
                }
                
                // Get Treatments through dealer id in Ajax
                public function getTreatments(Request $request)
                {
                    $post = $request->all();
                    $dealer = $request->dealer;
                    $model = $request->model;
                    $dealer_templates = DB::table('dealer_templates')->where('dealer_id', $dealer)->get(['template_id']);
                    $templates = array();
                    foreach ($dealer_templates as $key => $value) {
                        $templates[] = $value->template_id;
                    }
                    $treatments = DB::table('treatments')
                    ->select('id', 'treatment', 'treatment_type', 'dealer_price', 'incentive', 'customer_price', 'labour_code')
                    //->where('dealer_id',$dealer)
                    ->whereIn('temp_id', $templates)
                    ->where('model_id', $model)
                    ->where('status', 1)
                    ->get();
                    $treatments = json_decode(json_encode($treatments), true);
                    $res = array();
                    if (@$treatments) {
                        $res['treat_m'] = '<option value="">Select Treatment</option>';       
                        foreach ($treatments as $treat) {
                            $treatment = $treat['treatment'];
                            $id =  $treat['id'];
                            $res['treat_m'] = $res['treat_m']."<option value='$id'>$treatment</option>";
                        }
                    } else {
                        $res['treat_m'] = "<option value=''>No Treatment found</option>";
                        $res['gtp'] = '';
                        $res['gdp'] = '';
                        $res['discount'] = '';
                        $res['actualP'] = '';
                    }
                    return $res;
                }
                
                // Get Treatments through dealer id in Ajax
                public function getTreatmentPrice(Request $request)
                {
                    $post = $request->all();
                    $treatment_id = $request->treatment_id;
                    
                    if (!empty($treatment_id)) {
                        $res['gtp'] = DB::table('treatments')
                        ->select('customer_price')
                        ->where('id', $treatment_id)
                        ->where('status', 1)
                        ->first()->customer_price;
                        
                        $dealer_id = $request->dealer_id;
                        $res['share'] = DB::table('dealer_shares')->select('share_percentage')->where('dealer_id', $dealer_id)->orderBy('id', 'DESC')->first()->share_percentage;
                        
                        $res['calc'] = $res['gtp'] * $res['share']/100;
                        $res['gdp'] = round($res['gtp'] - $res['calc']);
                        
                    } else {
                        $res['gtp'] = '';
                        $res['gdp'] = '';
                    }
                    
                    return $res;
                }
                
                public function getDealerPrice(Request $request)
                {
                    $post = $request->all();
                    $dealer_id = $request->dealer_id;
                    $res = DB::table('dealer_shares')->select('share_percentage')->where('dealer_id', $dealer_id)
                    ->orderBy('id', 'DESC')->first()->share_percentage;
                    
                    return $res;
                }
                
                // Cron job for sending emails to dealers
                public function cronJob()
                {
                    $currentDate = date('Y-m-d');
                    $firstDate = date('Y-m-1');
                    $dealers = DB::table('users_email')->select('user_id', DB::raw('group_concat(email) as email'))
                    ->groupBy('user_id')
                    ->orderBy('user_id')
                    ->where('user_id', '!=', 58)
                    ->get();
                    // echo "<pre>";
                    // dd($dealers);
                    //die;
                    foreach ($dealers as $key => $value1)
                    {
                        $data = DB::table('jobs')->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(hvt_value) as hvt_customer_price,  SUM(incentive) as vas_incentive, advisor_id'))
                        ->where('dealer_id', '=', $value1->user_id)
                        ->whereDate('job_date', '>=', $firstDate)->whereDate('job_date', '<=', $currentDate)->where('delete_job', 1)
                        ->groupBy('advisor_id')
                        ->get();
                        $advisor = array();
                        $i = $mtd_total = 0;
                        
                        if (count($data) > 0)
                        {
                            foreach ($data as $value)
                            {
                                $today_data = DB::table('jobs')->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as today_vas_customer_price, SUM(hvt_value) as today_hvt_customer_price,  SUM(incentive) as today_vas_incentive, advisor_id'))
                                ->where('advisor_id', '=', $value->advisor_id)
                                ->whereDate('job_date', '=', $currentDate)->where('delete_job', 1)
                                ->groupBy('advisor_id')
                                ->first();
                                
                                $hvt_incentive = 0;
                                $d = explode(',', $value->job_id);
                                foreach ($d as $val)
                                {
                                    $treat = DB::table('jobs')->select('treatments')
                                    ->where('id', $val)->where('delete_job', 1)
                                    ->first();
                                    $dt = json_decode($treat->treatments);
                                    foreach ($dt as $val1)
                                    {
                                        if ($val1->treatment_type == 1)
                                        {
                                            $hvt_incentive = $hvt_incentive + $val1->incentive;
                                        }
                                    }
                                }
                                $advisor[$i]['advisor_id'] = $value->advisor_id;
                                $advisor[$i]['vas_customer_price'] = round($value->vas_customer_price);
                                $advisor[$i]['vas_incentive'] = round($value->vas_incentive);
                                $advisor[$i]['hvt_customer_price'] = round($value->hvt_customer_price);
                                $advisor[$i]['hvt_incentive'] = round($hvt_incentive);
                                if (!empty($today_data->advisor_id))
                                {
                                    $today_hvt_incentive = 0;
                                    $td1 = explode(',', $today_data->job_id);
                                    foreach ($td1 as $val)
                                    {
                                        $treat = DB::table('jobs')->select('treatments')
                                        ->where('id', $val)->where('delete_job', 1)
                                        ->first();
                                        $dt1 = json_decode($treat->treatments);
                                        foreach ($dt1 as $val1)
                                        {
                                            if ($val1->treatment_type == 1)
                                            {
                                                $today_hvt_incentive = $today_hvt_incentive + $val1->incentive;
                                            }
                                        }
                                    }
                                    $advisor[$i]['today_vas_customer_price'] = round($today_data->today_vas_customer_price);
                                    $advisor[$i]['today_vas_incentive'] = round($today_data->today_vas_incentive);
                                    $advisor[$i]['today_hvt_customer_price'] = round($today_data->today_hvt_customer_price);
                                    $advisor[$i]['today_hvt_incentive'] = round($today_hvt_incentive);
                                }
                                else
                                {
                                    $advisor[$i]['today_vas_customer_price'] = 0;
                                    $advisor[$i]['today_vas_incentive'] = 0;
                                    $advisor[$i]['today_hvt_customer_price'] = 0;
                                    $advisor[$i]['today_hvt_incentive'] = 0;
                                }
                                
                                @$total_service = DB::table('jobs_by_date')->select(DB::raw('SUM(total_jobs) as mtd_total'))
                                ->where('dealer_id', '=', $value1->user_id)
                                ->whereDate('job_added_date', '>=', $firstDate)->whereDate('job_added_date', '<=', $currentDate)->first();
                                
                                @$total_jobs = DB::table('jobs')->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                                ->where('dealer_id', '=', $value1->user_id)
                                ->whereDate('job_date', '>=', $firstDate)->whereDate('job_date', '<=', $currentDate)->where('delete_job', 1)
                                ->first();
                                
                                @$today_service = DB::table('jobs_by_date')->select(DB::raw('SUM(total_jobs) as total'))
                                ->where('dealer_id', '=', $value1->user_id)
                                ->whereDate('job_added_date', $currentDate)->first();
                                
                                @$today_jobs = DB::table('jobs')->select(DB::raw('SUM(vas_value) as vas_value,SUM(vas_total) as vas_total, SUM(hvt_value) as hvt_value,SUM(hvt_total) as hvt_total'))
                                ->where('dealer_id', '=', $value1->user_id)
                                ->whereDate('job_date', $currentDate)->where('delete_job', 1)
                                ->first();
                                
                                $total_job_array = array(
                                    'mtd_total' => @$total_service->mtd_total,
                                    'total' => @$today_service->total,
                                    'vas_value' => @$today_jobs->vas_value,
                                    'mtd_vas_value' => @$total_jobs->mtd_vas_value,
                                    'vas_total' => @$today_jobs->vas_total,
                                    'mtd_vas_total' => @$total_jobs->mtd_vas_total,
                                    'hvt_value' => @$today_jobs->hvt_value,
                                    'mtd_hvt_value' => @$total_jobs->mtd_hvt_value,
                                    'hvt_total' => @$today_jobs->hvt_total,
                                    'mtd_hvt_total' => @$total_jobs->mtd_hvt_total,
                                );
                                $i++;
                            }
                        }
                        else
                        {
                            $total_job_array = array(
                                'mtd_total' => '',
                                'total' => '',
                                'vas_value' => '',
                                'mtd_vas_value' => '',
                                'vas_total' => '',
                                'mtd_vas_total' => '',
                                'hvt_value' => '',
                                'mtd_hvt_value' => '',
                                'hvt_total' => '',
                                'mtd_hvt_total' => '',
                            );
                        }
                        
                        $email = explode(',', $value1->email);
                        //$email=['r.jain@ldh.01s.in'];
                        //$email=['varinder.kaur@ldh.01s.in'];
                        $subject = get_name($value1->user_id) . "- Daily Report on  " . date('d-M-Y') . ".";
                        $message = array(
                            'advisors' => $advisor,
                            'total_job_array' => @$total_job_array
                        );
                        Mail::send('cron_email', $message, function ($message) use ($email, $subject)
                        {
                            //$message->from('varinder.kaur@ldh.01s.in', 'Auto Solutions');
                            $message->from('vasmis@autosolutions.in', 'Auto Solutions');
                            //$message->to('varinder.kaur@ldh.01s.in')->subject($subject);
                            $message->to($email)->subject($subject);
                            //$message->bcc('varinder.kaur@ldh.01s.in');
                            $message->bcc('vasmis@autosolutions.in');
                        });
                        //die;
                        echo "<pre>";
                        print_r($email);
                        echo "<pre>";
                        print_r($advisor);
                        echo "<pre>";
                        print_r($total_job_array);
                    }
                }
                
                // Deactivate treatment by id
                public function treatment($id)
                {
                    $data = array(
                        'status' => 0,
                    );
                    $update = DB::table('treatments')->where('id', $id)->update($data);
                    if ($update > 0)
                    {
                        echo "Deactivate successfully";
                    }
                    else
                    {
                        echo "Id not found";
                    }
                }
                
                public function send_mail()
                {
                    sendgrid();
                    $arr = array(
                        'result' => sendgrid() ,
                        'status_code' => 204
                    );
                    return json_encode($arr);
                }
                
                public function send_mail_of_late_commer($id)
                {
                    // if (getCurrentTime() == "10:00:00") {
                        //     $id = 3;
                        // } elseif (getCurrentTime() == "16:00:00") {
                            //     $id = 2;
                            // } else {
                                //     exit("not a time ");
                                // }
                                $late1 = $late2 = $late3 = [];
                                $csv_name = '';
                                $b = [];
                                $interval = $id;
                                //   dd($data);
                                if ($interval == 3)
                                {
                                    $user = DB::table('users')->whereIn('users.role', [3, 4, 5])
                                    ->get();
                                    
                                    foreach ($user as $key => $value)
                                    {
                                        $value->user_id = $value->id;
                                        $check = DB::table('attendance')->whereDate('date', getCurrentDate())
                                        ->where('user_id', $value->id)
                                        ->first();
                                        if (empty($check))
                                        {
                                            $abcent[] = $value;
                                        }
                                    }
                                    $late1 = $abcent;
                                    $csv_name = "late_atten_3";
                                    // $b[] = $value;
                                    // $late1[] = array_merge($b, $abcent);
                                    
                                }
                                elseif ($interval == 2)
                                {
                                    $csv_name = "late_atten_2";
                                }
                                elseif ($interval == 1)
                                {
                                    $csv_name = "late_atten_1";
                                }
                                
                                // dd($abcent);
                                
                                
                                $data = DB::table('attendance')->whereDate('date', getCurrentDate())
                                ->join('timings', 'attendance.dealer_id', '=', 'timings.user_id')
                                ->select('attendance.*', 'timings.start_time', 'timings.relax_time')
                                ->get();
                                
                                foreach ($data as $key => $value)
                                {
                                    $relax_time = date("H:i:s", strtotime('+' . $value->relax_time . ' minutes', strtotime($value->start_time)));
                                    $late2_time = strtotime('+1 hour', strtotime($value->start_time));
                                    if ($interval == 1)
                                    {
                                        $csv_name = "late_atten_1";
                                        if ($value->in_time > $value->start_time && $value->in_time < $relax_time)
                                        {
                                            $late1[] = $value;
                                            // $b[] = [];
                                            
                                        }
                                    }
                                    elseif ($interval == 2)
                                    {
                                        $csv_name = "late_atten_2";
                                        if ($value->in_time > $relax_time && $value->in_time < $late2_time)
                                        {
                                            $late1[] = $value;
                                            // $b[] = [];
                                            
                                        }
                                    }
                                    elseif ($interval == 3)
                                    {
                                        $csv_name = "late_atten_3";
                                        if ($value->in_time > $late2_time)
                                        {
                                            $b[] = $value;
                                        }
                                    }
                                }
                                
                                if ($interval == 3)
                                {
                                    $late1 = array_merge($b, $abcent);
                                }
                                
                                // dd($late1);
                                $excel = Excel::create($csv_name, function ($excel) use ($late1)
                                {
                                    $excel->sheet('mySheet', function ($sheet) use ($late1)
                                    {
                                        $sheet->setCellValue('A1', 'Sr.no');
                                        $sheet->setCellValue('B1', 'Date');
                                        $sheet->setCellValue('C1', 'In Time');
                                        $sheet->setCellValue('D1', 'Out Time');
                                        $sheet->setCellValue('E1', 'User');
                                        $sheet->setCellValue('F1', 'Dealer');
                                        
                                        $i = 2;
                                        $loop = 1;
                                        foreach ($late1 as $key => $value)
                                        {
                                            // dd((string)$loop);
                                            $sheet->setCellValue('A' . $i, (string)$loop);
                                            $sheet->setCellValue('B' . $i, @$value->date);
                                            $sheet->setCellValue('C' . $i, @$value->in_time);
                                            $sheet->setCellValue('D' . $i, @$value->out_time);
                                            $sheet->setCellValue('E' . $i, get_name($value->user_id));
                                            $sheet->setCellValue('F' . $i, get_name($value->dealer_id));
                                            
                                            $i++;
                                            $loop++;
                                        }
                                    });
                                });
                                
                                $excel->store('csv', public_path('late_atten'));
                                $data['interval'] = $interval;
                                $data['csv_name'] = $csv_name;
                                if (Mail::to("hsingh@ldh.01s.in")->send(new atten_report($data)))
                                {
                                    Log::customlog("Message Sent" . $data);
                                }
                                return "Message Sent";
                            }
                        }