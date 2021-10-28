<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use App\User;
use App\Contact;
use Session;
use Redirect;
use DB;
use Storage;
use File;
use Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use DateTime;
use PDF;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }
    // Admin Index
    public function index()
    {
        if (Auth::check() && Auth::user()->role == '1') {
            $result = DB::table('jobs')
                ->select(DB::raw('SUM(treatment_total) as total,SUM(customer_price) as customer_price,  SUM(hvt_total) as hvt, job_date'))
                ->where('delete_job', 1)
                ->groupBy('job_date')
                ->get();
            return view('admin.dashboard', [
                'result' => $result,
            ]);
        } else {
            return redirect('admin/login');
        }
    }
    public function dashboard(Request $request)
    {
        $download = $request->download;
        $selectMonth = $request->selectMonth;
        if (Auth::check() && Auth::user()->role == '1') {
            if (!empty($selectMonth)) {
                $exp = explode('-', $selectMonth);
                $month = $exp[1];
                $year = $exp[0];
            } else {
                $currentMonthYear = explode('-', date('Y-m'));
                $month = $currentMonthYear[1];
                $year = $currentMonthYear[0];
            }
            $first_day = date('Y-m-01');
            $currentMonth = date('Y-m');
            $today = date('Y-m-d');
            $result = DB::table('jobs as j')
                ->select(DB::raw('SUM(j.customer_price) as customer_price, SUM(j.actual_price) as actual_price, SUM(j.difference_price) as difference_price, SUM(j.hvt_total) as hvt_total, SUM(j.hvt_value) as hvt_value,SUM(j.treatment_total) as vas_total, SUM(j.customer_price) as vas_value, j.job_date, j.foc_options'))
                ->whereMonth('j.job_date', $month)
                ->whereYear('j.job_date', $year)
                ->where('j.delete_job', 1)
                // ->where('j.foc_options',5)
                ->groupBy('j.job_date')
                ->get();

            $i = 0;
            foreach ($result as $key => $value) {
                $total = DB::table('jobs_by_date')->select(DB::raw('SUM(total_jobs) as total_jobs'))->where('job_added_date', $value->job_date)->first();
                if (!empty($total->total_jobs)) {
                    $result[$i]->total = $total->total_jobs;
                } else {
                    $result[$i]->total = 0;
                }
                $i++;
            }
            $total = DB::table('jobs_by_date')
                ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                ->whereMonth('job_added_date', $month)
                ->whereYear('job_added_date', $year)
                ->first();
            $result = json_decode(json_encode($result), true);

            $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->get();
            if (!empty($selectMonth)) {
                $current = "'" . $selectMonth . "'";
                $currentM = $selectMonth;
            } else {
                $current = "'" . $currentMonth . "'";
                $currentM = $currentMonth;
            }
            //dd($current);
            return view('admin.dashboard', [
                'dealers' => $dealers,
                'current' => $current,
                'currentM' => $currentM,
                'result' => $result,
                'total' => $total->mtd_total,
                'oldMonth' => @$selectMonth,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }
    public function downloadDashboard(Request $request)
    {
        $selectMonth = $request->selectMonth1;
        if (!empty($selectMonth)) {
            $exp = explode('-', $selectMonth);
            $month = $exp[1];
            $year = $exp[0];
        } else {
            $currentMonthYear = explode('-', date('Y-m'));
            $month = $currentMonthYear[1];
            $year = $currentMonthYear[0];
        }
        $first_day = date('Y-m-01');
        $currentMonth = date('Y-m');
        $today = date('Y-m-d');
        $result = DB::table('jobs as j')
            ->select(DB::raw('SUM(j.customer_price) as customer_price, SUM(j.actual_price) as actual_price, SUM(j.difference_price) as difference_price, SUM(j.hvt_total) as hvt_total, SUM(j.hvt_value) as hvt_value,SUM(j.treatment_total) as vas_total, SUM(j.customer_price) as vas_value, j.job_date, j.foc_options'))
            ->whereMonth('j.job_date', $month)
            ->whereYear('j.job_date', $year)
            ->where('j.delete_job', 1)
            // ->where('j.foc_options',5)
            ->groupBy('j.job_date')
            ->get();
        $i = 0;
        foreach ($result as $key => $value) {
            $total = DB::table('jobs_by_date')->select(DB::raw('SUM(total_jobs) as total_jobs'))->where('job_added_date', $value->job_date)->first();
            if (!empty($total->total_jobs)) {
                $result[$i]->total = $total->total_jobs;
            } else {
                $result[$i]->total = 0;
            }
            $i++;
        }
        $total = DB::table('jobs_by_date')
            ->select(DB::raw('SUM(total_jobs) as mtd_total'))
            ->whereMonth('job_added_date', $month)
            ->whereYear('job_added_date', $year)
            ->first();
        $result = json_decode(json_encode($result), true);
        /************************************ Download Dashboard *************************/
        $vas_total = $vas_value = $hvt_total = $hvt_value = 0;
        foreach ($result as $value) {
            $vas_total = $vas_total + $value['vas_total'];
            // $vas_value = $vas_value + $value['vas_value'];
            $vas_value = $vas_value + $value['actual_price'];
            $hvt_total = $hvt_total + $value['hvt_total'];
            $hvt_value = $hvt_value + $value['hvt_value'];
        }
        $mtd_total = $total->mtd_total;
        return Excel::create('Dashboard_' . date("d/m/Y"), function ($excel) use ($mtd_total, $vas_total, $hvt_total, $vas_value, $hvt_value) {
            $excel->sheet('sheet', function ($sheet) use ($mtd_total, $vas_total, $hvt_total, $vas_value, $hvt_value) {
                $sheet->setBorder('B1:C10');
                $sheet->cells('B1:C1', function ($cells) {
                    $cells->setBackground('#FFFFFF');
                });
                $sheet->cells('B2:C2', function ($cells) {
                    $cells->setBackground('#00a65a');
                });
                $sheet->cells('B3:C3', function ($cells) {
                    $cells->setBackground('#dd4b39');
                });
                $sheet->cells('B4:C4', function ($cells) {
                    $cells->setBackground('#00a65a');
                });
                $sheet->cells('B5:C5', function ($cells) {
                    $cells->setBackground('#00a65a');
                });
                $sheet->cells('B6:C6', function ($cells) {
                    $cells->setBackground('#FFFF00');
                });
                $sheet->cells('B7:C7', function ($cells) {
                    $cells->setBackground('#dd4b39');
                });
                $sheet->cells('B8:C8', function ($cells) {
                    $cells->setBackground('#00a65a');
                });
                $sheet->cells('B9:C9', function ($cells) {
                    $cells->setBackground('#00a65a');
                });
                $sheet->cells('B10:C10', function ($cells) {
                    $cells->setBackground('#FFFF00');
                });
                $sheet->setCellValue('B1', 'Monthly Treatments till Date');
                $sheet->mergeCells("B1:C1");
                $sheet->setCellValue('B2', 'RO');
                $sheet->setCellValue('C2', $mtd_total);
                $sheet->setCellValue('B3', 'VAS');
                $sheet->mergeCells("B3:C3");
                $sheet->setCellValue('B4', 'No of Trmt');
                $sheet->setCellValue('C4', $vas_total);
                $sheet->setCellValue('B5', 'Amount');
                $sheet->setCellValue('C5', $vas_value);
                $sheet->setCellValue('B6', 'Value Per Treatment');
                $sheet->setCellValue('C6', vas_in_percentage(@$vas_value, @$vas_total));
                $sheet->setCellValue('B7', 'HVT');
                $sheet->mergeCells("B7:C7");
                $sheet->setCellValue('B8', 'No of Trmt');
                $sheet->setCellValue('C8', $hvt_total);
                $sheet->setCellValue('B9', 'Amount');
                $sheet->setCellValue('C9', $hvt_value);
                $sheet->setCellValue('B10', 'HVT %');
                $sheet->setCellValue('C10', hvt_in_percentage(@$hvt_value, @$vas_value));
            });
        })->export('xlsx');
    }
    // Add Service load from dashboard
    public function addServiceLoad(Request $request)
    {
        $post = $request->all();
        $data = array(
            'user_id' => Auth::user()->id,
            'dealer_id' => $post['dealer_id'],
            'total_jobs' => $post['total_jobs'],
            'job_added_date' => $post['service_date'],
        );
        DB::table('jobs_by_date')->insert($data);
        Session::flash('success', 'RO added successfully!');
        return redirect('/admin/jobs');
    }
    // view Dealer listing
    public function dealer_management(Request $request)
    {
        $search = $request->search;
        $type = $request->type;
        $firm_id = $request->firm_id;
        $asm_id = $request->asm_id;
        $status = $request->status;

        $firms = DB::table('firms')->get();
        if (!empty($firm_id)) {
            $asms = DB::table('users')->where(['firm_id' => $firm_id, 'role' => 5, 'status' => 1])->select('id', 'name')->get();
        } else {
            $asms = DB::table('users')->where(['role' => 5, 'status' => 1])->select('id', 'name')->get();
        }

        $result = User::whereIn('role', [2, 6])
            ->where(function ($query) use ($search, $type, $firm_id, $status, $asm_id) {
                if (!empty($firm_id)) {
                    if (isset($firm_id)) {
                        if (!empty(trim($firm_id))) {
                            $query->Where('firm_id', $firm_id);
                        }
                    }
                }
                if (!empty($asm_id)) {
                    if (isset($asm_id)) {
                        if (!empty(trim($asm_id))) {
                            $query->whereRaw("find_in_set($asm_id,reporting_authority)");
                            // $query->Where('reporting_authority', $asm_id);
                        }
                    }
                }
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                            $query->orWhere('email', 'like', '%' . $search . '%');
                            $query->orWhere('mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
                if (!empty($type)) {
                    if (isset($type)) {
                        if (!empty(trim($type))) {
                            $query->Where('type', $type);
                        }
                    }
                }
                if (!empty($status)) {
                    if ($status == 'activated') {
                        $query->Where('status', 1);
                    } elseif ($status == 'deactivated') {
                        $query->Where('status', 0);
                    }
                }
            })
            ->orderBy('name', 'ASC')->paginate(20);
        // dd($result);
        return view('admin.dealers', [
            'result' => $result->appends(Input::except('page')),
            'search' => $search,
            'type' => $type,
            'firms' => $firms,
            'firm_id' => $firm_id,
            'status' => $status,
            'asms' => $asms,
            'asm_id' => $asm_id,
        ]);
    }

    public function downloadDealers(Request $request)
    {
        $search = $request->search;
        $type = $request->type;
        $firm_id = $request->firm_id;
        $asm_id = $request->asm_id;
        $status = $request->status;

        $firms = DB::table('firms')->get();
        $result = User::whereIn('role', [2, 6])
            ->where(function ($query) use ($search, $type, $firm_id, $status, $asm_id) {
                if (!empty($firm_id)) {
                    if (isset($firm_id)) {
                        if (!empty(trim($firm_id))) {
                            $query->Where('firm_id', $firm_id);
                        }
                    }
                }
                if (!empty($asm_id)) {
                    if (isset($asm_id)) {
                        if (!empty(trim($asm_id))) {
                            $query->whereRaw("find_in_set($asm_id,reporting_authority)");
                        }
                    }
                }
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                            $query->orWhere('email', 'like', '%' . $search . '%');
                            $query->orWhere('mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
                if (!empty($type)) {
                    if (isset($type)) {
                        if (!empty(trim($type))) {
                            $query->Where('type', $type);
                        }
                    }
                }
                if (!empty($status)) {
                    if ($status == 'activated') {
                        $query->Where('status', 1);
                    } elseif ($status == 'deactivated') {
                        $query->Where('status', 0);
                    }
                }
            })
            ->orderBy('name', 'ASC')->get();

        return Excel::create('Dealers ' . date("d M,Y"), function ($excel) use ($result) {
            $excel->sheet('mySheet', function ($sheet) use ($result) {
                $sheet->setCellValue('A1', 'Firm');
                $sheet->setCellValue('B1', 'Center Code');
                $sheet->setCellValue('C1', 'Name');
                $sheet->setCellValue('D1', 'Mobile No.');
                $sheet->setCellValue('E1', 'Address');
                $i = 2;
                $loop = 1;
                foreach ($result as $key => $value) {
                    $sheet->setCellValue('A' . $i, get_firm_short_code(@$value->firm_id));
                    $sheet->setCellValue('B' . $i, $value->center_code);
                    $sheet->setCellValue('C' . $i, $value->name);
                    $sheet->setCellValue('D' . $i, $value->mobile_no);
                    $sheet->setCellValue('E' . $i, $value->address);
                    $i++;
                    $loop++;
                }
            });
        })->download('csv');
    }

    // view add new Dealer page
    public function addDealer(Request $request)
    {
        $states = DB::table('states')->get();
        $grouplist = DB::table('groups')->get();
        $oemlist = DB::table('oems')->get();
        // $ASMlist = DB::table('users')->where(['role'=>5,'status'=>1])->get();
        $authorities = DB::table('users as u')
            ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
            ->whereIn('sd.designation_id', [3, 13, 23])
            ->orderBy('sd.designation_id', 'ASC')
            ->select('u.id as uid', 'u.name as uname', 'sd.designation_id as des_id')
            ->get();
        $firms = DB::table('firms')->get();
        return view('admin.addDealer', [
            'states' => $states,
            'grouplist' => $grouplist,
            'oemlist' => $oemlist,
            // 'ASMlist' => $ASMlist,
            'authorities' => $authorities,
            'firms' => $firms,
        ]);
    }

    //get ASM/RSM/SSE Of selected firm
    public function getAuthorities(Request $request)
    {
        $firm_id = $request->firm_id;
        $authorities = DB::table('users as u')
            ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
            ->whereIn('sd.designation_id', [3, 13, 23])
            ->where('u.firm_id', $firm_id)
            ->orderBy('sd.designation_id', 'ASC')
            ->select('u.id as uid', 'u.firm_id as firm_id', 'u.name as uname', 'sd.designation_id as des_id')
            ->get();
        if (@$authorities) {
            $res = '<option value="">Select</option>';
            foreach ($authorities as $authority) {
                $authority_name = $authority->uname;
                $authority_id = $authority->uid;
                $authority_des_name = get_designation_by_userid($authority_id);
                $res .= "<option value='$authority_id'>$authority_name - $authority_des_name</option>";
            }
        } else {
            $res = "<option value=''>No Authority found</option>";
        }
        return $res;
    }

    public function getlatlong(Request $request)
    {
        $url = "http://dev.01s.in/location?address=" . $request->address;

        $address = str_replace(" ", "+", $url);

        $data = file_get_contents($address);
        // $data = json_decode($data);
        return $data;
    }
    // save new Dealer
    public function insertDealer(Request $request)
    {
        $post = $request->all();
        $emails = explode(',', $post['email']);

        if ($post['type'] == 'office') {
            $this->validate(
                $request,
                [
                    'type' => 'required',
                    'firm_id' => 'required',
                    'center_code' => 'required',
                    'name' => 'required',
                    'address' => 'required|max:250',
                    'state_id' => 'required',
                    'city' => 'required',
                    'district_id' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    // 'mobile_no' => 'required|digits:10',
                    'email' => 'required|email',
                    'latitude' => 'required',
                    'longitude' => 'required',
                ],
                [
                    'type.required' => 'Please select Type.',
                    'firm_id.required' => 'Please select firm.',
                    'center_code.required' => 'Please enter Center Code.',
                    'name.required' => 'Please enter name.',
                    'email.required' => 'Please enter email.',
                    // 'mobile_no.required' => 'Please enter mobile no.',
                    'address.required' => 'Please enter address.',
                    'city.required' => 'Please enter city.',
                    'district_id.required' => 'Please select district.',
                    'state_id.required' => 'Please select state.',
                    'start_time.required' => 'Please select start time',
                    'end_time.required' => 'Please select end time',
                    'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                    'longitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'type' => 'required',
                    'firm_id' => 'required',
                    'center_code' => 'required',
                    'authority_id' => 'required',
                    'name' => 'required',
                    'share_percentage' => 'required',
                    'address' => 'required|max:250',
                    'state_id' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'city' => 'required',
                    'district_id' => 'required',
                    'mobile_no' => 'required|digits:10',
                    'email' => 'required|email',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    //'group' => 'required',
                    'OEM' => 'required',
                ],
                [
                    'type.required' => 'Please select type.',
                    'firm_id.required' => 'Please select firm.',
                    'center_code.required' => 'Please enter Center Code.',
                    'authority_id.required' => 'Please enter.',
                    'name.required' => 'Please enter name.',
                    'share_percentage.required' => 'Please enter shares from this dealer',
                    'email.required' => 'Please enter email.',
                    'mobile_no.required' => 'Please enter mobile no.',
                    'address.required' => 'Please enter address.',
                    'city.required' => 'Please enter city.',
                    'district_id.required' => 'Please select district.',
                    'start_time.required' => 'Please select start time',
                    'end_time.required' => 'Please select end time',
                    'state_id.required' => 'Please select state.',
                    // 'group.required' => 'Please enter group.',
                    'OEM.required' => 'Please enter OEM.',
                    'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                    'longitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                ]
            );
        }


        if ($post['type'] == 'office') {
            $data = array(
                'firm_id' => $post['firm_id'],
                'role' => 6,
                'center_code' => $post['center_code'],
                'name' => $post['name'],
                'mobile_no' => $post['mobile_no'],
                'password' => Hash::make(123456),
                'state_id' => $post['state_id'],
                'district_id' => $post['district_id'],
                'city' => $post['city'],
                'address' => $post['address'],
                'longitude' => $post['longitude'],
                'latitude' => $post['latitude'],
                'type' => 'office',
                'created_at' => date('Y-m-d h:i:s'),
            );
        } else {
            $data = array(
                'firm_id' => $post['firm_id'],
                'role' => 2,
                'center_code' => $post['center_code'],
                'reporting_authority' => implode(',', $post['authority_id']),
                'name' => $post['name'],
                'mobile_no' => $post['mobile_no'],
                'password' => Hash::make(123456),
                'state_id' => $post['state_id'],
                'district_id' => $post['district_id'],
                'city' => $post['city'],
                'address' => $post['address'],
                'longitude' => $post['longitude'],
                'latitude' => $post['latitude'],
                'group_id' => @$post['group'],
                'oem_id' => $post['OEM'],
                'type' => 'dealer',
                'created_at' => date('Y-m-d h:i:s'),
            );
        }

        $userId = User::insertGetId($data);
        if (!empty($post['share_percentage'])) {
            DB::table('dealer_shares')->insert(['dealer_id' => $userId, 'share_percentage' => $post['share_percentage'], 'created_at' => getCurrentTimestamp()]);
        }

        $time_diff = abs((strtotime($post['end_time']) - strtotime($post['start_time'])) / 3600);

        DB::table('timings')->insert([
            'user_id' => $userId,
            'start_time' => $post['start_time'],
            'end_time' => $post['end_time'],
            'total_time' => $time_diff
        ]);

        if ($post['cname'][0] != null && $post['cemail'][0] != null) {
            foreach ($post['cname'] as $k => $value) {
                Contact::insert(['user_id' => $userId, 'name' => $value, 'email' => $post['cemail'][$k], 'mobile' => $post['cmobile'][$k], 'designation' => $post['cdesignation'][$k]]);
            }
        }

        //$id = DB::getPdo()->lastInsertId();
        $filename = str_replace(" ", "_", $post['name']) . time();
        $filename = $filename . '.png';
        foreach ($emails as $value) {
            $data = array(
                'user_id' => $userId,
                'email' => trim($value),
            );
            DB::table('users_email')->insertGetId($data);
            \QrCode::backgroundColor(255, 255, 0)->color(255, 0, 127)
                ->format('png')->size('700')
                ->generate($userId, public_path('images/' . $filename));
            User::where('id', $userId)->update(array(
                'qrcode' => $filename
            ));
        }
        Session::flash('success', 'Dealer added successfully!');
        return redirect('/admin/dealer_management');
    }
    // view edit Dealer page
    public function editDealer($id)
    {
        $result = User::whereIn('role', [2, 6])->find($id);
        $timing = DB::table('timings')->where('user_id', $id)->first();
        $authority_ids = explode(",", $result->reporting_authority);
        $states = DB::table('states')->get();
        $grouplist = DB::table('groups')->get();
        $oemlist = DB::table('oems')->get();
        // $ASMlist = DB::table('users')->where(['role'=>5,'status'=>1])->get();
        $authorities = DB::table('users as u')
            ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
            ->whereIn('sd.designation_id', [3, 13, 23])
            ->where('u.firm_id', $result->firm_id)
            ->orderBy('sd.designation_id', 'ASC')
            ->select('u.id as uid', 'u.name as uname', 'sd.designation_id as des_id')
            ->get();
        $districts = DB::table('districts')->where('state_id', $result->state_id)->get();
        $contacts = DB::table('contacts')->where('user_id', $id)->get();
        $firms = DB::table('firms')->get();
        $share_percentage = DB::table('dealer_shares')->where('dealer_id', $id)->orderBy('created_at', 'DESC')->first();
        if (!empty($result)) {
            return view('admin.editDealer', [
                'result' => $result,
                'states' => $states,
                'districts' => $districts,
                'grouplist' => $grouplist,
                'oemlist' => $oemlist,
                // 'ASMlist' => $ASMlist,
                'authorities' => $authorities,
                'authority_ids' => $authority_ids,
                'contacts' => $contacts,
                'firms' => $firms,
                'timings' => $timing,
                'share_percentage' => $share_percentage
            ]);
        } else {
            Session::flash('error', 'No dealer found!');
            return redirect('/admin/dealer_management');
        }
    }

    public function downloadDealerInfo($id)
    {
        $result = User::whereIn('role', [2, 6])->find($id);
        $timing = DB::table('timings')->where('user_id', $id)->first();
        $contacts = DB::table('contacts')->where('user_id', $id)->get();
        $today = date("Y-m-d");

        $pdf = PDF::loadView('admin.downloadDealerInfo', compact('result', 'timing', 'today', 'contacts'));
        return $pdf->download('Dealer.pdf');
    }

    // update existing Dealer
    public function updateDealer(Request $request)
    {
        $post = $request->all();
        $emails = explode(',', $post['email']);

        if ($post['type'] == 'office') {
            $this->validate(
                $request,
                [
                    'type' => 'required',
                    'firm_id' => 'required',
                    'center_code' => 'required',
                    'name' => 'required',
                    'address' => 'required|max:250',
                    'state_id' => 'required',
                    'city' => 'required',
                    'district_id' => 'required',
                    'mobile_no' => 'required|digits:10',
                    'email' => 'required|email',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                ],
                [
                    'type.required' => 'Please select type.',
                    'firm_id.required' => 'Please select firm.',
                    'center_code.required' => 'Please enter Center Code.',
                    'name.required' => 'Please enter name.',
                    'email.required' => 'Please enter email.',
                    'mobile_no.required' => 'Please enter mobile no.',
                    'address.required' => 'Please enter address.',
                    'city.required' => 'Please enter city.',
                    'district_id.required' => 'Please select district.',
                    'state_id.required' => 'Please select state.',
                    'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                    'longitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'type' => 'required',
                    'firm_id' => 'required',
                    'center_code' => 'required',
                    'authority_id' => 'required',
                    'name' => 'required',
                    'share_percentage' => 'required',
                    'email' => 'required|email',
                    'mobile_no' => 'required|digits:10',
                    'address' => 'required|max:250',
                    'state_id' => 'required',
                    'city' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'district_id' => 'required',
                    //'group' => 'required',
                    'OEM' => 'required',
                    'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                    'longitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                ],
                [
                    'type.required' => 'Please select type',
                    'firm_id.required' => 'Please select firm',
                    'center_code.required' => 'Please enter Center Code',
                    'authority_id.required' => 'Please select',
                    'name.required' => 'Please enter name',
                    'share_percentage.required' => 'Please enter shares from this dealer',
                    'email.required' => 'Please enter email',
                    'mobile_no.required' => 'Please enter mobile no.',
                    'address.required' => 'Please enter address',
                    'city.required' => 'Please enter city',
                    'latitude.required' => 'Please enter latitude',
                    'longitude.required' => 'Please enter longitude',
                    'district_id.required' => 'Please select district',
                    'state_id.required' => 'Please select state',
                    //'group.required' => 'Please select group',
                    'OEM.required' => 'Please select OEM',
                ]
            );
        }

        if ($post['type'] == 'office') {
            $data = array(
                'firm_id' => $post['firm_id'],
                'role' => 6,
                'center_code' => $post['center_code'],
                'dealer_id' => null,
                'reporting_authority' => null,
                'name' => $post['name'],
                'mobile_no' => $post['mobile_no'],
                'password' => Hash::make(123456),
                'state_id' => $post['state_id'],
                'district_id' => $post['district_id'],
                'city' => $post['city'],
                'address' => $post['address'],
                'longitude' => $post['longitude'],
                'latitude' => $post['latitude'],
                'type' => 'office',
                'updated_at' => date('Y-m-d h:i:s'),
            );
        } else {
            $data = array(
                'firm_id' => $post['firm_id'],
                'role' => 2,
                'center_code' => $post['center_code'],
                'dealer_id' => null,
                'reporting_authority' => implode(',', $post['authority_id']),
                'name' => $post['name'],
                'mobile_no' => $post['mobile_no'],
                'password' => Hash::make(123456),
                'state_id' => $post['state_id'],
                'district_id' => $post['district_id'],
                'city' => $post['city'],
                'address' => $post['address'],
                'longitude' => $post['longitude'],
                'latitude' => $post['latitude'],
                'group_id' => @$post['group'],
                'oem_id' => $post['OEM'],
                'type' => 'dealer',
                'updated_at' => date('Y-m-d h:i:s'),
            );

            DB::table('dealer_shares')->insert(['dealer_id' => $post['id'], 'share_percentage' => $post['share_percentage'], 'created_at' => getCurrentTimestamp()]);
        }

        // $data = array(
        //     'center_code' => $post['center_code'],
        //     'name' => $post['name'], 
        //     'address' => $post['address'], 
        //     'mobile_no' => $post['mobile_no'], 
        //     'state_id' => $post['state_id'], 
        //     'district_id' => $post['district_id'], 
        //     'city' => $post['city'], 
        //     'longitude' => $post['longitude'],
        //     'latitude' => $post['latitude'],
        //     'group_id' => @$post['group'],
        //     'oem_id' => $post['OEM'],
        //     // 'dealer_id' => $post['ASM'],
        //     // 'updated_at' => date('Y-m-d h:i:s'),
        // );
        User::where('id', $post['id'])->update($data);

        $datetime1 = new DateTime($post['start_time']);
        $datetime2 = new DateTime($post['end_time']);
        $interval = $datetime1->diff($datetime2);

        $old_timing = DB::table('timings')->where('user_id', $post['id'])->first();

        if (!empty($old_timing)) {
            DB::table('timings')->where('user_id', $post['id'])->update([
                'start_time' => $post['start_time'],
                'end_time' => $post['end_time'],
                'hour_diff' => $interval->format('%h'),
                'minute_diff' => $interval->format('%i')
            ]);
        } else {
            DB::table('timings')->insert([
                'user_id' => $post['id'],
                'start_time' => $post['start_time'],
                'end_time' => $post['end_time'],
                'hour_diff' => $interval->format('%h'),
                'minute_diff' => $interval->format('%i')
            ]);
        }

        DB::table('users_email')->where('user_id', $post['id'])->delete();

        if (@$post['cname'][0] != null && @$post['cemail'][0] != null) {
            foreach ($post['cname'] as $k => $value) {
                if (!empty($post['contact_id'][$k])) {
                    Contact::where('id', $post['contact_id'][$k])->update(['user_id' => $post['id'], 'name' => $value, 'email' => $post['cemail'][$k], 'mobile' => $post['cmobile'][$k], 'designation' => $post['cdesignation'][$k]]);
                } else {
                    Contact::insert(['user_id' => $post['id'], 'name' => $value, 'email' => $post['cemail'][$k], 'mobile' => $post['cmobile'][$k], 'designation' => $post['cdesignation'][$k]]);
                }
            }
        }
        foreach ($emails as $value) {
            $data = array(
                'user_id' => $post['id'],
                'email' => trim($value),
            );
            DB::table('users_email')->insert($data);
        }
        Session::flash('success', 'Dealer updated successfully!');
        return redirect('/admin/dealer_management');
    }
    //Change Dealer status or delete
    public function statusDealer($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                User::where('id', $id)->update($udata);
                Session::flash('success', 'Dealer deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                User::where('id', $id)->update($udata);
                Session::flash('success', 'Dealer activated successfully!');
            } else if ($status == "delete") {
                User::where('id', $id)->delete();
                DB::table('users_email')->where('user_id', $id)->delete();
                Session::flash('success', 'Dealer deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/dealer_management');
    }

    // View dealer's Sales Executives
    public function dealers_SalesExecutivesListing(Request $request, $dealer_id)
    {
        $search = $request->search;
        $result = DB::table('emp_hierarchy')
            ->join('staff_detail', 'staff_detail.user_id', '=', 'emp_hierarchy.user_id')
            ->join('users', 'users.id', '=', 'emp_hierarchy.user_id')
            ->where(['emp_hierarchy.dealer_id' => $dealer_id, 'staff_detail.designation_id' => 14])
            ->where(function ($query) {
                $query->where('emp_hierarchy.status', 1);
                $query->orWhere('emp_hierarchy.status', 2);
                $query->orWhere('emp_hierarchy.status', 3);
            })
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('users.name', 'like', '%' . $search . '%');
                            $query->orWhere('users.mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->paginate(10);
        return view('admin.dealers_SalesExecutivesListing', [
            'result' => $result->appends(Input::except('page')),
            'dealer_id' => $dealer_id,
        ]);
    }

    // View dealer's ASM
    public function dealers_ASM($dealer_id)
    {
        $dealer = DB::table('users')->where('id', $dealer_id)->first();
        // $asm_data = DB::table('users')->where('id',$dealer->reporting_authority)->first();
        $dealer_authorities = DB::table('users')
            ->where('id', @$dealer_id)
            ->select('reporting_authority')
            ->first();
        $dealer_authorities = explode(",", @$dealer_authorities->reporting_authority);
        $authorities = DB::table('users')->whereIn('id', $dealer_authorities)->get();
        // dd($dealer_authorities); 
        return view('admin.dealers_ASM', [
            'authorities' => $authorities,
            'dealer_id' => $dealer_id,
        ]);
    }

    public function dealerPercentageHistory($dealer_id)
    {
        $percentages = DB::table('dealer_shares')->where('dealer_id', $dealer_id)->orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.dealerPercentageHistory', compact('percentages', 'dealer_id'));
    }

    public function addDealerPercentage($dealer_id)
    {
        return view('admin.addDealerPercentage', [
            'dealer_id' => $dealer_id,
        ]);
    }

    public function insertDealerPercentage(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'share_percentage' => 'required'
            ],
            [
                'share_percentage.required' => 'Please enter admin share',
            ]
        );
        $data = array(
            'dealer_id' => $post['dealer_id'],
            'share_percentage' => $post['share_percentage'],
            'created_at' => getCurrentTimestamp(),
        );
        DB::table('dealer_shares')->insert($data);
        Session::flash('success', 'Percentage added successfully!');
        return redirect('/admin/dealer_percentage_history/' . $post['dealer_id']);
    }

    // view Contacts listing
    public function Contacts(Request $request, $id)
    {
        //dd($id);
        $search = $request->search;
        $result = DB::table('contacts')
            //  ->join('contacts','contacts.id','=',$id)
            //  ->select('contacts.*','contacts.name as contact_name')
            //  ->where(function($query) use ($search){
            //     if(!empty($search)){
            //         if(isset($search)){
            //             if(!empty(trim($search))){
            //                 $query->orWhere('contacts.name','like','%'.$search.'%');               
            //                 $query->orWhere('contacts.email','like','%'.$search.'%');  
            //                 $query->orWhere('contacts.mobile','like','%'.$search.'%');
            //                 $query->orWhere('contacts.designation','like','%'.$search.'%');  
            //             }
            //         }
            //     }
            // })
            ->where('user_id', $id)->paginate(10);
        //->orderBy('contacts.status','DESC')->paginate(20);
        return view('admin.contacts', [
            'result' => $result->appends(Input::except('page')),
            'dealer_id' => $id,
        ]);
    }

    // view add new Contact page
    public function addContact($dealer_id)
    {
        return view('admin.addContact', [
            'dealer_id' => $dealer_id,
        ]);
    }

    // save new Contact
    public function insertContact(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'email' => 'required|unique:contacts,email',
                'mobile' => 'required|digits:10|unique:contacts,mobile',
                'designation' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'email.required' => 'Please enter email',
                'mobile.required' => 'Please enter mobile no.',
                'designation.required' => 'Please enter designation',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'user_id' => $post['dealer_id'],
            'mobile' => $post['mobile'],
            'email' => $post['email'],
            'designation' => $post['designation'],
        );
        DB::table('contacts')->insert($data);
        Session::flash('success', 'Contact added successfully!');
        return redirect('/admin/contacts/' . $post['dealer_id']);
    }

    // view edit Contact page
    public function editContact($dealer_id, $id)
    {
        $result = DB::table('contacts')->where('id', $id)->first();
        return view('admin.editContact', [
            'dealer_id' => $dealer_id,
            'result' => $result,
        ]);
    }
    // update existing Contact
    public function updateContact(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'email' => 'required|unique:contacts,email,' . $request->contact_id,
                'mobile' => 'required|digits:10|unique:contacts,mobile,' . $request->contact_id,
                'designation' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'email.required' => 'Please enter email',
                'mobile.required' => 'Please enter mobile no.',
                'designation.required' => 'Please enter designation',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'user_id' => $post['dealer_id'],
            'mobile' => $post['mobile'],
            'email' => $post['email'],
            'designation' => $post['designation'],
        );
        DB::table('contacts')->where('id', $post['contact_id'])->update($data);
        Session::flash('success', 'Contact updated successfully!');
        return redirect('/admin/contacts/' . $post['dealer_id']);
    }

    //Change Contact status or delete
    public function statusContact($status, $dealer_id, $id)
    {

        if (@$status) {
            if ($status == "delete") {
                DB::table('contacts')->where('id', $id)->delete();
                Session::flash('success', 'Contact deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/contacts/' . $dealer_id);
    }

    // view Staff listing
    public function staff_management(Request $request)
    {
        $search = $request->search;
        $des = $request->designation_id;
        $asm_id = $request->asm_id;
        $dealer_id = $request->dealer_id;
        $office_id = $request->office_id;
        $firm_id = $request->firm_id;
        $status = $request->status;

        $designations = DB::table('designations')->get();
        $dep_des = DB::table('staff_detail')->first();

        if (!empty($asm_id) && !empty($firm_id)) {
            $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1, 'firm_id' => $firm_id])
                ->get();

            $results1 = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1, 'firm_id' => $firm_id])
                ->get();

            $dealers = array();
            foreach ($results1 as $key => $value) {
                $reporting_ids = explode(",", $value->reporting_authority);
                if (in_array($asm_id, $reporting_ids)) {
                    $dealers[] = $results1[$key];
                }
            }

            $firms = DB::table('firms')->get();
        } elseif (!empty($firm_id)) {
            // dd("firm");
            $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1, 'firm_id' => $firm_id])
                ->get();

            $dealers = DB::table('users')->select('id', 'name')->where(['role' => 2, 'status' => 1, 'firm_id' => $firm_id])->get();

            $firms = DB::table('firms')->get();
        } elseif (!empty($asm_id)) {
            // dd("asm");
            $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1])->get();

            $firms = DB::table('firms')->get();

            $results1 = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])
                ->get();

            $dealers = array();
            foreach ($results1 as $key => $value) {
                $reporting_ids = explode(",", $value->reporting_authority);
                if (in_array($asm_id, $reporting_ids)) {
                    $dealers[] = $results1[$key];
                }
            }
        } else {
            // dd("fd");
            $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1])->get();
            $dealers = DB::table('users')->select('id', 'name')->where(['role' => 2, 'status' => 1])->get();
            $firms = DB::table('firms')->get();
        }

        // else {
        //     $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1])->get();
        // }

        // if (!empty($asm_id)) {
        //     $results1 = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])
        //         ->get();

        //     $dealers = array();
        //     foreach ($results1 as $key => $value) {
        //         $reporting_ids = explode(",", $value->reporting_authority);
        //         if (in_array($asm_id, $reporting_ids)) {
        //             $dealers[] = $results1[$key];
        //         }
        //     }
        // } else {
        //     $dealers = DB::table('users')->select('id', 'name')->where(['role' => 2, 'status' => 1])->get();
        // }

        $offices = DB::table('users')->select('id', 'name')->where(['role' => 6, 'status' => 1])->get();
        // $firms = DB::table('firms')->get();

        $result = User::join('staff_detail', 'users.id', '=', 'staff_detail.user_id')
            // ->join('emp_hierarchy', 'emp_hierarchy.user_id', '=', 'users.id')
            ->select('*', 'users.id as user_id')
            ->whereIn('role', [3, 4, 5])
            ->where(function ($query) use ($des, $search, $asm_id, $firm_id, $dealer_id, $office_id, $status) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                            $query->orWhere('mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
                if (!empty($firm_id)) {
                    if (isset($firm_id)) {
                        if (!empty(trim($firm_id))) {
                            $query->Where(['firm_id' => $firm_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($asm_id)) {
                    if (isset($asm_id)) {
                        if (!empty(trim($asm_id))) {
                            $query->Where(['reporting_authority' => $asm_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($dealer_id)) {
                    if (isset($dealer_id)) {
                        if (!empty(trim($dealer_id))) {
                            $query->Where(['dealer_id' => $dealer_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($office_id)) {
                    if (isset($office_id)) {
                        if (!empty(trim($office_id))) {
                            $query->Where(['dealer_office' => $office_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($des)) {
                    if (isset($des)) {
                        if (!empty(trim($des))) {
                            $query->Where(['designation_id' => $des, 'status' => 1]);
                        }
                    }
                }
                if (!empty($status)) {
                    if ($status == 'activated') {
                        $query->Where('status', 1);
                    } elseif ($status == 'deactivated') {
                        $query->Where('status', 0);
                    }
                }
            })
            ->orderBy('name', 'ASC')->paginate(20);
        // dd($result);
        return view('admin.staff', [
            'result' => $result->appends(Input::except('page')),
            'designations' => $designations,
            'des' => $des,
            'asms' => $asms,
            'dealers' => $dealers,
            'offices' => $offices,
            'asm_id' => $asm_id,
            'dealer_id' => $dealer_id,
            'office_id' => $office_id,
            'dep_des' => $dep_des,
            'firms' => $firms,
            'firm_id' => $firm_id,
            'status' => $status,
        ]);
    }

    public function downloadStaff(Request $request)
    {
        $search = $request->search;
        $des = $request->designation_id;
        $asm_id = $request->asm_id;
        $dealer_id = $request->dealer_id;
        $office_id = $request->office_id;
        $firm_id = $request->firm_id;
        $status = $request->status;

        $result = User::join('staff_detail', 'users.id', '=', 'staff_detail.user_id')
            // ->join('emp_hierarchy', 'emp_hierarchy.user_id', '=', 'users.id')
            ->select('*', 'users.id as user_id')
            ->whereIn('role', [3, 4, 5])
            ->where(function ($query) use ($des, $search, $asm_id, $firm_id, $dealer_id, $office_id, $status) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                            $query->orWhere('mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
                if (!empty($firm_id)) {
                    if (isset($firm_id)) {
                        if (!empty(trim($firm_id))) {
                            $query->Where(['firm_id' => $firm_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($asm_id)) {
                    if (isset($asm_id)) {
                        if (!empty(trim($asm_id))) {
                            $query->Where(['reporting_authority' => $asm_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($dealer_id)) {
                    if (isset($dealer_id)) {
                        if (!empty(trim($dealer_id))) {
                            $query->Where(['dealer_id' => $dealer_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($office_id)) {
                    if (isset($office_id)) {
                        if (!empty(trim($office_id))) {
                            $query->Where(['reporting_authority' => $office_id, 'status' => 1]);
                        }
                    }
                }
                if (!empty($des)) {
                    if (isset($des)) {
                        if (!empty(trim($des))) {
                            $query->Where(['designation_id' => $des, 'status' => 1]);
                        }
                    }
                }
                if (!empty($status)) {
                    if ($status == 'activated') {
                        $query->Where('status', 1);
                    } elseif ($status == 'deactivated') {
                        $query->Where('status', 0);
                    }
                }
            })
            ->orderBy('name', 'ASC')->get();

        return Excel::create('Staff ' . date("d M,Y"), function ($excel) use ($result) {
            $excel->sheet('mySheet', function ($sheet) use ($result) {
                $sheet->setCellValue('A1', 'Firm');
                $sheet->setCellValue('B1', 'Emp. Code');
                $sheet->setCellValue('C1', 'Name');
                $sheet->setCellValue('D1', 'Mobile No.');
                $sheet->setCellValue('E1', 'Dealer Name');
                $sheet->setCellValue('F1', 'Reporting Authority');
                $sheet->setCellValue('G1', 'Department');
                $sheet->setCellValue('H1', 'Designation');
                $sheet->setCellValue('I1', 'Access Right');
                $sheet->setCellValue('J1', 'Date Of Joining');
                $sheet->setCellValue('K1', 'Date Of Leaving');
                $sheet->setCellValue('L1', 'Salary');
                $i = 2;
                $loop = 1;
                foreach ($result as $key => $value) {
                    if ($value->role == "5") {
                        $access_rights = "ASM";
                    } elseif ($value->role == "3") {
                        $access_rights = "All";
                    } else {
                        $access_rights = "Attendance Only";
                    }

                    if (!empty($value->doj)) {
                        $doj = date("Y-m-d", strtotime($value->doj));
                    } else {
                        $doj = '';
                    }
                    if (!empty($value->dol)) {
                        $dol = date("Y-m-d", strtotime($value->dol));
                    } else {
                        $dol = '';
                    }

                    $sheet->setCellValue('A' . $i, get_firm_short_code(@$value->firm_id));
                    $sheet->setCellValue('B' . $i, $value->emp_code);
                    $sheet->setCellValue('C' . $i, $value->name);
                    $sheet->setCellValue('D' . $i, $value->mobile_no);
                    $sheet->setCellValue('E' . $i, get_dealer_name(@$value->dealer_id));
                    $sheet->setCellValue('F' . $i, get_reporting_authority_name(@$value->reporting_authority));
                    $sheet->setCellValue('G' . $i, get_department_name($value->department_id));
                    $sheet->setCellValue('H' . $i, get_designation_name($value->designation_id));
                    $sheet->setCellValue('I' . $i, $access_rights);
                    $sheet->setCellValue('J' . $i, $doj);
                    $sheet->setCellValue('K' . $i, $dol);
                    $sheet->setCellValue('L' . $i, @getsalarybyid($value->user_id));
                    $i++;
                    $loop++;
                }
            });
        })->download('csv');
    }
    // view add new Staff page
    public function addStaff()
    {
        $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->where('status', 1)->orderBy('name', 'ASC')->get();
        $department = DB::table('departments')->select('id as depart_id', 'name as depart_name')->where('status', 1)->orderBy('name', 'ASC')->get();
        $firms = DB::table('firms')->get();
        $levels = DB::table('designation_levels')->get();
        $offices = DB::table('users')->select('id as office_id', 'name as office_name')->where(['role' => 6, 'status' => 1])->get();
        $groups = DB::table('groups')->where('status', 1)->get();
        return view('admin.addStaff', [
            'dealers' => $dealers,
            'department' => $department,
            'firms' => $firms,
            'offices' => $offices,
            'levels' => $levels,
            'groups' => $groups
        ]);
    }

    // Get Department through level id in Ajax
    public function getdepartmentbylevel(Request $request)
    {
        $level = $request->level;
        $departments = DB::table('designations')->where('level', $level)->orderBy('level', 'ASC')->groupBy('department_id')->get(['department_id']);
        // $departments = json_decode(json_encode($departments), true);
        if (@$departments) {
            $res = '<option value="">Select Departments</option>';
            foreach ($departments as $department) {
                $department_name = get_department_name($department->department_id);
                $department_id = $department->department_id;
                $res .= "<option value='$department_id'>$department_name</option>";
            }
        } else {
            $res = "<option value=''>No department found</option>";
        }
        return $res;
    }

    // Get Designation through level id in Ajax
    public function getdesbylevel(Request $request)
    {
        $level = $request->level;
        $designations = DB::table('designations')->where('level', $level)->orderBy('level', 'ASC')->get();
        $designations = json_decode(json_encode($designations), true);
        if (@$designations) {
            $res = '<option value="">Select Designation</option>';
            foreach ($designations as $designation) {
                $designation_name = $designation["designation"];
                $designation_id = $designation["id"];
                $res .= "<option value='$designation_id'>$designation_name</option>";
            }
        } else {
            $res = "<option value=''>No Designation found</option>";
        }
        return $res;
    }

    // Get Reporting Level through Designation level id in Ajax
    public function getreportinglevel(Request $request)
    {
        $level = $request->level;
        $reportinglevels = DB::table('designation_levels')->where('id', '<', $level)->get();
        $reportinglevels = json_decode(json_encode($reportinglevels), true);
        if (@$reportinglevels) {
            $res = '<option value="">Select Reporting Level</option>';
            foreach ($reportinglevels as $reportinglevel) {
                $reportinglevel_name = $reportinglevel["level"];
                $reportinglevel_id = $reportinglevel["id"];
                $res .= "<option value='$reportinglevel_id'>$reportinglevel_name</option>";
            }
        } else {
            $res = "<option value=''>No Reporting Level found</option>";
        }
        return $res;
    }

    public function getreportingauthority(Request $request)
    {
        $user_id = $request->user_id;
        $reportinglevel = $request->reportinglevel;
        $authority_id = $request->auth_id;
        $reporting_authorities = DB::table('users as u')
            ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
            ->join('designations as d', 'sd.designation_id', '=', 'd.id')
            ->where('d.level', $reportinglevel)
            ->where('u.id', '!=', $user_id)
            ->select('u.id as uid', 'u.name as uname', 'sd.designation_id as des_id')
            ->get();
        $reporting_authorities = json_decode(json_encode($reporting_authorities), true);
        if (@$reporting_authorities) {
            $res = '<option value="">Select Reporting Authority</option>';
            foreach ($reporting_authorities as $authority) {
                $authority_id = $authority["uid"];
                $authority_name = $authority["uname"];
                $authority_des_name = get_designation_by_userid($authority["uid"]);
                $res .= "<option value='$authority_id'>$authority_name - $authority_des_name</option>";
            }
        } else {
            $res = "<option value=''>No Reporting Authority found</option>";
        }
        return $res;
    }

    public function getdealerauthority(Request $request)
    {
        $user_id = $request->user_id;
        $del_id = $request->del_id;
        $result = DB::table('users')
            ->where('id', $del_id)
            ->where('reporting_authority', '!=', $user_id)
            ->select('reporting_authority')
            ->first();
        if (!empty($result)) {
            $authorities = explode(",", @$result->reporting_authority);
            if (($key = array_search($user_id, $authorities)) !== false) {
                unset($authorities[$key]);
            }
            $authorities = json_decode(json_encode($authorities), true);
            if (!empty($authorities)) {
                $res = '<option value="">Select Reporting Authority</option>';
                foreach ($authorities as $authority) {
                    $authority_id = $authority;
                    $authority_name = get_name($authority);
                    $authority_des_name = get_designation_by_userid($authority);
                    $res .= "<option value='$authority_id'>$authority_name - $authority_des_name</option>";
                }
            } else {
                $res = "<option value=''>No Reporting Authority found</option>";
            }
        } else {
            $res = "<option value=''>No Reporting Authority found</option>";
        }
        return $res;
    }

    public function getBydesignation(Request $request)
    {
        $des_id = $request->des_id;
        $authority_id = $request->auth_id;
        if (!$des_id) {
            $res = '<option value="">Select Reporting Authority</option>';
        } else {
            $res = '';
            $reporting_authorities = DB::table('users as u')->join('staff_detail as sd', 'sd.user_id', '=', 'u.id');
            if ($des_id == 14 || $des_id == 15 || $des_id == 16 || $des_id == 17 || $des_id == 19 || $des_id == 21) {
                $reporting_authorities = $reporting_authorities->select('u.id', 'u.name', 'sd.user_id')->whereIn('sd.designation_id', [3, 13, 23])->where('u.status', 1)->get();
            } elseif ($des_id == 3 || $des_id == 13 || $des_id == 23) {
                $reporting_authorities = $reporting_authorities->select('u.id', 'u.name', 'sd.user_id')->where(['sd.designation_id' => 24, 'u.status' => 1])->get();
            } elseif ($des_id == 9) {
                $reporting_authorities = $reporting_authorities->select('u.id', 'u.name', 'sd.user_id')->where(['sd.designation_id' => 10, 'u.status' => 1])->get();
            } elseif ($des_id == 10) {
                $reporting_authorities = $reporting_authorities->select('u.id', 'u.name', 'sd.user_id')->where(['sd.designation_id' => 11, 'u.status' => 1])->get();
            }

            if (!empty($authority_id)) {
                $res .= '<option value="">Select Reporting Authority</option>';
                foreach ($reporting_authorities as $authority) {
                    if ($authority->id == $authority_id) {
                        $res .= '<option value="' . $authority->id . '" selected>' . $authority->name . ' - ' .  get_designation_by_userid($authority->id) . '</option>';
                    } else {
                        $res .= '<option value="' . $authority->id . '">' . $authority->name . ' - ' .  get_designation_by_userid($authority->id) . '</option>';
                    }
                }
            } else {
                $res .= '<option value="">Select Reporting Authority</option>';
                foreach ($reporting_authorities as $authority) {
                    $res .= '<option value="' . $authority->id . '">' . $authority->name . ' - ' .  get_designation_by_userid($authority->id) . '</option>';
                }
            }
        }
        return response()->json(['html' => $res]);
    }
    // save new staff
    public function insertStaff(Request $request)
    {
        $post = $request->all();
        // dd($post);
        if ($post['designation'] == 3) {
            $role = 5;
        } else if (!empty($post['dealer_office'])) {
            $role = 4;
        } else {
            $role = $post['access_rights'];
        }
        $this->validate(
            $request,
            [
                'firm_id' => 'required',
                'depart_id' => 'required',
                'level' => 'required',
                'designation' => 'required',
                'name' => 'required',
                'email' => 'nullable|unique:users,email',
                // 'password' => 'required',
                'salary' => 'required',
                'mobile_no' => 'required|digits:10|unique:users,mobile_no',
                'emp_code' => 'required',
                'doj' => 'required',
                // 'user_group' => 'required',
                // 'dealer_id' => 'required',
                // 'office' => 'required',
            ],
            [
                'firm_id.required' => 'Please select firm',
                'depart_id.required' => 'Please select department',
                'level.required' => 'Please select designation level',
                'designation.required' => 'Please select designation',
                'name.required' => 'Please enter name',
                // 'email.required' => 'Please enter email',
                // 'password.required' => 'Please enter password',
                'salary.required' => 'Please enter salary',
                'mobile_no.required' => 'Please enter mobile no.',
                'emp_code.required' => 'Please enter employee code.',
                'doj.required' => 'Please enter date Of joining',
                // 'user_group.required' => 'Please select user group',
            ]
        );
        $data = array(
            'firm_id' => $post['firm_id'],
            'role' => $role,
            'name' => $post['name'],
            'email' => $post['email'],
            'mobile_no' => $post['mobile_no'],
            'alt_mobile_no' => $post['alt_mobile_no'],
            'password' => Hash::make($post['password']),
            // 'group_id' => $post['user_group'],
            'created_at' => date('Y-m-d h:i:s'),
        );
        $user_id = User::insertGetId($data);
        if (getUserRole($user_id) == '3') {
            User::where('id', $user_id)->update(array('is_login' => '3'));
        } else {
            User::where('id', $user_id)->update(array('is_login' => '4'));
        }
        $salary = DB::table('employee_salary')->insert(['user_id' => $user_id, 'emp_salary' => $post['salary'], 'created_at' => date('Y-m-d h:i:s')]);
        DB::table('staff_detail')->insert(array(
            'user_id' => $user_id,
            'department_id' => $post['depart_id'],
            'designation_id' => $post['designation'],
            'emp_code' => $post['emp_code'],
            'doj' => $post['doj']
        ));
        if (!empty($post['authority'])) {
            User::where('id', $user_id)->update(array('reporting_authority' => $post['authority']));
            DB::table('emp_hierarchy')->insert(array('user_id' => $user_id, 'authority_id' => $post['authority'], 'status' => 1));
        } elseif (!empty($post['dealer_id'])) {
            $reporting_authority = $post['dealer_authid'];
            User::where('id', $user_id)->update(array('dealer_id' => $post['dealer_id'], 'reporting_authority' => $reporting_authority));
            DB::table('emp_hierarchy')->insert(array('user_id' => $user_id, 'dealer_id' => $post['dealer_id'], 'authority_id' => $reporting_authority, 'status' => 1));
        }
        if (!empty($post['dealer_office'])) {
            User::where('id', $user_id)->update(array('dealer_office' => $post['dealer_office']));
        }
        Session::flash('success', 'Staff member added successfully!');
        return redirect('/admin/staff_management');
    }
    // view edit Staff page
    public function editStaff($id)
    {
        Session::put('prevUrl', \URL::previous());
        $result = User::whereIn('role', [3, 4, 5])->find($id);
        $dep_des = DB::table('staff_detail')->where('user_id', $id)->first();
        $authority_id = DB::table('emp_hierarchy')->where('user_id', $id)->first();
        $departments = DB::table('departments')->select('id as depart_id', 'name as depart_name')->where('status', 1)->orderBy('name', 'ASC')->get();
        $firms = DB::table('firms')->get();
        $levels = DB::table('designation_levels')->get();
        $designations = DB::table('designations')->where('level', getlevelbydesignation(@$dep_des->designation_id))->orderBy('designation', 'ASC')->get();
        if (empty($dep_des)) {
            $reportinglevels = $levels;
        } else {
            $reportinglevels = DB::table('designation_levels')->where('id', '<', getlevelbydesignation($dep_des->designation_id))->get();
        }
        $reporting_authorities = DB::table('users as u')
            ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
            ->join('designations as d', 'sd.designation_id', '=', 'd.id')
            ->where('d.level', getlevelbydesignation(get_designation($result->reporting_authority)))
            ->select('u.id as uid', 'u.name as uname', 'sd.designation_id as des_id')
            ->get();
        $dealer_authorities = DB::table('users')
            ->where('id', @$result->dealer_id)
            ->select('reporting_authority')
            ->first();
        $dealer_authorities = explode(",", @$dealer_authorities->reporting_authority);
        $emp_salary = DB::table('employee_salary')->where('user_id', $id)->first();
        $dealers = DB::table('users')->where('role', 2)->where('status', 1)->select('id as dealer_id', 'name as dealer_name')->orderBy('name', 'ASC')->get();
        $offices = DB::table('users')->select('id as office_id', 'name as office_name')->where(['role' => 6, 'status' => 1])->get();
        $groups = DB::table('groups')->where('status', 1)->get();
        if (!empty($result)) {
            return view('admin.editStaff', [
                'result' => $result,
                'dep_des' => $dep_des,
                'authority_id' => $authority_id,
                'departments' => $departments,
                'firms' => $firms,
                'levels' => $levels,
                'emp_salary' => $emp_salary,
                'dealers' => $dealers,
                'designations' => $designations,
                'reportinglevels' => $reportinglevels,
                'reporting_authorities' => $reporting_authorities,
                'dealer_authorities' => $dealer_authorities,
                'offices' => $offices,
                'groups' => $groups
            ]);
        } else {
            Session::flash('error', 'No staff member found!');
            return redirect('/admin/staff_management');
        }
    }

    public function getDealerPermission($user_id, $del_id)
    {
        // $reporting_authority = get_asm($del_id);
        $checkAuthority = DB::table('users')->where(['id' => $user_id, 'dealer_id' => $del_id])->first();
        if (!empty($checkAuthority)) {
            $html = '';
        } else {
            $html = 'You are changing Dealer. Are you sure ?';
        }
        return response()->json(['html' => $html]);
    }

    public function getreportingpermission($user_id, $del_id, $del_authid)
    {
        // $reporting_authority = get_asm($del_id);
        $checkAuthority = DB::table('users')->where(['id' => $user_id, 'dealer_id' => $del_id, 'reporting_authority' => $del_authid])->first();
        if (!empty($checkAuthority)) {
            $html = '';
        } else {
            $html = 'You are changing Reporting Authority. Are you sure ?';
        }
        return response()->json(['html' => $html]);
    }

    public function getauthority($user_id, $authority_id)
    {
        $checkAuthority = DB::table('users')->where(['id' => $user_id, 'reporting_authority' => $authority_id])->first();
        if (!empty($checkAuthority)) {
            $html = '';
        } else {
            $html = 'You are changing reporting authority. Are you sure ?';
        }
        return response()->json(['html' => $html]);
    }

    // update existing Staff member
    public function updateStaff(Request $request)
    {
        $post = $request->all();
        if ($post['designation'] == 3) {
            $role = 5;
        } else if (!empty($post['dealer_office'])) {
            $role = 4;
        } else {
            $role = $post['access_rights'];
        }
        $this->validate(
            $request,
            [
                'firm_id' => 'required',
                'depart_id' => 'required',
                'level' => 'required',
                'designation' => 'required',
                'emp_code' => 'required',
                'name' => 'required',
                'email' => 'nullable|unique:users,email,' . $request->id,
                'salary' => 'required',
                'mobile_no' => 'required|digits:10|unique:users,mobile_no,' . $request->id,
                'doj' => 'required',
                // 'user_group' => 'required',
                // 'dealer_id' => 'required',
                // 'office' => 'required',
            ],
            [
                'firm_id.required' => 'Please select firm',
                'depart_id.required' => 'Please select department',
                'level.required' => 'Please select designation level',
                'designation.required' => 'Please select designation',
                'emp_code.required' => 'Please enter employee code.',
                'name.required' => 'Please enter name',
                // 'email.required' => 'Please enter email',
                'salary.required' => 'Please enter salary',
                'mobile_no.required' => 'Please enter mobile no.',
                'doj.required' => 'Please enter date Of joining.',
                // 'user_group.required' => 'Please select user group',
                // 'dealer_id.required' => 'Please select dealer',
                // 'office.required' => 'Please select office',
            ]
        );

        if (!empty($post['authority'])) {
            if (!empty($post['dealer_id']) && !empty($post['reporting_level'])) {
                $data = array(
                    'firm_id' => $post['firm_id'],
                    'role' => $role,
                    'dealer_id' => null,
                    'reporting_authority' => $post['authority'],
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'mobile_no' => $post['mobile_no'],
                    'alt_mobile_no' => $post['alt_mobile_no'],
                    // 'group_id' => $post['user_group'],
                    'updated_at' => date('Y-m-d h:i:s'),
                );
            } else {
                $data = array(
                    'firm_id' => $post['firm_id'],
                    'role' => $role,
                    'dealer_id' => null,
                    'reporting_authority' => $post['authority'],
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'mobile_no' => $post['mobile_no'],
                    'alt_mobile_no' => $post['alt_mobile_no'],
                    // 'group_id' => $post['user_group'],
                    'updated_at' => date('Y-m-d h:i:s'),
                );
            }
        } else if (!empty($post['dealer_id'])) {
            if (empty($post['dealer_authid'])) {
                Session::flash('error', 'The chosen dealer does not have any reporting authority. Please add it.');
                return redirect('admin/editStaffMember/' . $post['id']);
            } else {
                $data = array(
                    'firm_id' => $post['firm_id'],
                    'role' => $role,
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'dealer_id' => $post['dealer_id'],
                    'reporting_authority' => $post['dealer_authid'],
                    'mobile_no' => $post['mobile_no'],
                    'alt_mobile_no' => $post['alt_mobile_no'],
                    // 'group_id' => $post['user_group'],
                    'updated_at' => date('Y-m-d h:i:s'),
                );
            }
        } else {
            $data = array(
                'firm_id' => $post['firm_id'],
                'role' => $role,
                'dealer_id' => null,
                'reporting_authority' => null,
                'name' => $post['name'],
                'email' => $post['email'],
                'mobile_no' => $post['mobile_no'],
                'alt_mobile_no' => $post['alt_mobile_no'],
                // 'group_id' => $post['user_group'],
                'updated_at' => date('Y-m-d h:i:s'),
            );
        }
        // elseif (!empty($post['office'])) {
        //     $data = array(
        //         'firm_id' => $post['firm_id'],
        //         'role' => $role,
        //         'name' => $post['name'], 
        //         'email' => $post['email'],
        //         'dealer_id'=> null,
        //         'reporting_authority' => $post['office'], 
        //         'mobile_no' => $post['mobile_no'],
        //         'group_id' => $post['user_group'],
        //         'updated_at' => date('Y-m-d h:i:s'),
        //     );
        // } 


        $check = DB::table('staff_detail')->where('user_id', $post['id'])->select('designation_id')->first();
        $checkUser = DB::table('emp_hierarchy')->where('user_id', $post['id'])->select('dealer_id', 'authority_id')->first();
        $checkAuthority = DB::table('emp_hierarchy')->where('user_id', $post['id'])->where('authority_id', @$post['authority'])->first();
        if ($post['designation'] != @$check->designation_id || empty($check)) {
            if (!empty($post['dealer_id'])) {
                if (empty($post['dealer_authid'])) {
                    Session::flash('error', 'The chosen dealer does not have any reporting authority. Please add it.');
                    return redirect('admin/editStaffMember/' . $post['id']);
                } elseif (!empty($post['dealer_id']) && !empty($post['authority'])) {
                    $newdata = array(
                        'firm_id' => $post['firm_id'],
                        'role' => $role,
                        'dealer_id' => null,
                        'reporting_authority' => $post['authority'],
                        'name' => $post['name'],
                        'email' => $post['email'],
                        'mobile_no' => $post['mobile_no'],
                        'alt_mobile_no' => $post['alt_mobile_no'],
                        // 'group_id' => $post['user_group'],
                        'updated_at' => date('Y-m-d h:i:s'),
                    );
                } elseif (!empty($post['dealer_id']) && !empty($post['authority']) && $post['authority'] != $post['dealer_authid']) {
                    $newdata = array(
                        'firm_id' => $post['firm_id'],
                        'role' => $role,
                        'dealer_id' => $post['dealer_id'],
                        'reporting_authority' => $post['dealer_authid'],
                        'name' => $post['name'],
                        'email' => $post['email'],
                        'mobile_no' => $post['mobile_no'],
                        'alt_mobile_no' => $post['alt_mobile_no'],
                        // 'group_id' => $post['user_group'],
                        'updated_at' => date('Y-m-d h:i:s'),
                    );
                } else {
                    $newdata = array(
                        'firm_id' => $post['firm_id'],
                        'role' => $role,
                        'dealer_id' => $post['dealer_id'],
                        'reporting_authority' => $post['dealer_authid'],
                        'name' => $post['name'],
                        'email' => $post['email'],
                        'mobile_no' => $post['mobile_no'],
                        'alt_mobile_no' => $post['alt_mobile_no'],
                        // 'group_id' => $post['user_group'],
                        'updated_at' => date('Y-m-d h:i:s'),
                    );
                }
            }
            // elseif (!empty($post['office'])) {
            //     $newdata = array(
            //         'firm_id' => $post['firm_id'],
            //         'role' => $role,
            //         'dealer_id'=> null,
            //         'reporting_authority' => $post['office'],
            //         'name' => $post['name'],
            //         'email' => $post['email'],
            //         'mobile_no' => $post['mobile_no'],
            //         'group_id' => $post['user_group'],  
            //         'updated_at' => date('Y-m-d h:i:s'),  
            //     );
            // } 
            else if (!empty($post['authority']) && getlevelbydesignation($post['designation']) == '1') {
                $newdata = array(
                    'firm_id' => $post['firm_id'],
                    'role' => $role,
                    'dealer_id' => null,
                    'reporting_authority' => null,
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'mobile_no' => $post['mobile_no'],
                    'alt_mobile_no' => $post['alt_mobile_no'],
                    // 'group_id' => $post['user_group'],
                    'updated_at' => date('Y-m-d h:i:s'),
                );
            } else {
                $newdata = array(
                    'firm_id' => $post['firm_id'],
                    'role' => $role,
                    'dealer_id' => null,
                    'reporting_authority' => $post['authority'],
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'mobile_no' => $post['mobile_no'],
                    'alt_mobile_no' => $post['alt_mobile_no'],
                    // 'group_id' => $post['user_group'],
                    'updated_at' => date('Y-m-d h:i:s'),
                );
            }
            if (!empty($post['password'])) {
                User::where('id', $post['id'])->update(['password' => Hash::make($post['password'])]);
            }
            $checkSalary = DB::table('employee_salary')->where('user_id', $post['id'])->first();
            if (empty($checkSalary)) {
                User::where('id', $post['id'])->update($newdata);
                $salary = DB::table('employee_salary')->insert(['user_id' => $post['id'], 'emp_salary' => $post['salary'], 'created_at' => date('Y-m-d h:i:s')]);
            } else {
                User::where('id', $post['id'])->update($newdata);
                $salary = DB::table('employee_salary')->where('user_id', $post['id'])->update(['emp_salary' => $post['salary'], 'updated_at' => date('Y-m-d h:i:s')]);
            }

            if (!empty($check)) {
                $staff = DB::table('staff_detail')->where('user_id', $post['id'])->update(array('department_id' => $post['depart_id'], 'designation_id' => $post['designation'], 'emp_code' => $post['emp_code'], 'doj' => $post['doj']));
            } else {
                $staff = DB::table('staff_detail')->insert(array('user_id' => $post['id'], 'department_id' => $post['depart_id'], 'designation_id' => $post['designation'], 'emp_code' => $post['emp_code'], 'doj' => $post['doj']));
            }
        } else {
            if (!empty($post['password'])) {
                User::where('id', $post['id'])->update(['password' => Hash::make($post['password'])]);
            }
            $staff = DB::table('staff_detail')->where('user_id', $post['id'])->update(array('department_id' => $post['depart_id'], 'designation_id' => $post['designation'], 'emp_code' => $post['emp_code'], 'doj' => $post['doj']));
            $checkSalary = DB::table('employee_salary')->where('user_id', $post['id'])->first();
            if (empty($checkSalary)) {
                User::where('id', $post['id'])->update($data);
                $salary = DB::table('employee_salary')->insert(['user_id' => $post['id'], 'emp_salary' => $post['salary'], 'created_at' => date('Y-m-d h:i:s')]);
            } else {
                User::where('id', $post['id'])->update($data);
                $salary = DB::table('employee_salary')->where('user_id', $post['id'])->update(['emp_salary' => $post['salary'], 'updated_at' => date('Y-m-d h:i:s')]);
            }
        }
        if (!empty($post['dealer_office'])) {
            User::where('id', $post['id'])->update(array('dealer_office' => $post['dealer_office']));
        } else {
            User::where('id', $post['id'])->update(array('dealer_office' => null));
        }

        if (!empty($post['dol'])) {
            DB::table('staff_detail')->where('user_id', $post['id'])->update(['dol' => $post['dol']]);
        }

        if (getUserRole($post['id']) == '3') {
            User::where('id', $post['id'])->update(['is_login' => '3']);
        } elseif (getUserRole($post['id']) == '4') {
            User::where('id', $post['id'])->update(['is_login' => '4']);
        }

        if (empty($post['hidden_authority_id']) && $post['designation'] != @$check->designation_id && !empty($post['authority'])) {
            if (!empty($checkUser)) {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'status' => 3));
            } else {
                DB::table('emp_hierarchy')->insert(array('user_id' => $post['id'], 'authority_id' => $post['authority'], 'status' => 3));
            }
        } elseif (!empty($post['hidden_authority_id']) && $post['designation'] != @$check->designation_id && !empty($post['authority'])) {
            if (!empty($checkUser)) {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'status' => 3));
            } else {
                DB::table('emp_hierarchy')->insert(array('user_id' => $post['id'], 'authority_id' => $post['authority'], 'status' => 3));
            }
        } elseif (!empty($post['authority']) && empty($checkAuthority)) {
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['id'], 'dealer_id' => null, 'authority_id' => $post['authority'], 'status' => 3]);
            } else {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('authority_id' => $post['authority'], 'status' => 2));
            }
        } elseif (!empty($post['dealer_id'])) {
            if (empty($checkUser)) {
                if (!empty($post['office'])) {
                    DB::table('emp_hierarchy')->insert(['user_id' => $post['id'], 'dealer_id' => null, 'authority_id' => $post['office']]);
                } elseif (!empty($post['authority'])) {
                    DB::table('emp_hierarchy')->insert(['user_id' => $post['id'], 'dealer_id' => null, 'authority_id' => $post['authority']]);
                } else {
                    DB::table('emp_hierarchy')->insert(['user_id' => $post['id'], 'dealer_id' => $post['dealer_id'], 'authority_id' => $post['dealer_authid']]);
                }
            } elseif ($post['dealer_id'] != @$checkUser->dealer_id && $post['dealer_authid'] != @$checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => $post['dealer_id'], 'authority_id' => $post['dealer_authid'], 'status' => 3));
            } elseif ($post['dealer_id'] != @$checkUser->dealer_id && $post['dealer_authid'] == @$checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => $post['dealer_id'], 'status' => 3));
            } elseif ($post['dealer_id'] == @$checkUser->dealer_id && $post['dealer_authid'] != @$checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => $post['dealer_id'], 'authority_id' => $post['dealer_authid'], 'status' => 2));
            } else {
                DB::table('emp_hierarchy')->where('user_id', $post['id'])->update(array('dealer_id' => $post['dealer_id'], 'authority_id' => $post['dealer_authid']));
            }
        }
        // elseif (!empty($post['office'])) {
        //     if (empty($checkUser)) {
        //         DB::table('emp_hierarchy')->insert(['user_id'=>$post['id'], 'authority_id'=>$post['office']]);
        //     } elseif (!empty($checkUser) && $post['office'] != $checkUser->authority_id) {
        //         DB::table('emp_hierarchy')->where('user_id',$post['id'])->update(array('dealer_id' =>null, 'authority_id'=>$post['office'], 'status'=>3));
        //     } elseif (!empty($checkUser) && $post['office'] == $checkUser->authority_id) {
        //         DB::table('emp_hierarchy')->where('user_id',$post['id'])->update(array('dealer_id'=>null,'authority_id' => $post['office']));
        //     }
        // }
        Session::flash('success', 'Staff member updated successfully!');
        // return redirect('/admin/staff_management');
        return redirect(Session::get('prevUrl'));
    }

    //Change Staff status or delete
    public function statusStaff($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                User::where('id', $id)->update($udata);
                Session::flash('success', 'Staff member deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                User::where('id', $id)->update($udata);
                Session::flash('success', 'Staff member activated successfully!');
            } else if ($status == "delete") {
                User::where('id', $id)->delete();
                Session::flash('success', 'Staff member deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/staff_management');
    }

    // public function emp_hierarchy()
    // {
    //     $result = DB::table('staff_detail as sd')->join('users as u', 'u.id', '=', 'sd.user_id')->join('emp_hierarchy as eh', 'u.id', '=', 'eh.user_id')->where(['sd.designation_id'=>14, 'u.status'=>1])->paginate(20);
    //     return view('admin.emp_hierarchy',[
    //         'result' => $result->appends(Input::except('page')),
    //     ]);
    // }

    public function editEmpHierarchy($id)
    {
        $result = DB::table('emp_hierarchy as eh')->join('users as u', 'u.id', '=', 'eh.user_id')->select('*', 'eh.id as id', 'u.id as user_id')->where('eh.user_id', $id)->first();

        if (empty($result)) {
            Session::flash('error', 'Please add required information first.');
            return redirect()->back();
        } else {
            // $result = DB::table('users')->where('id',$id)->first();
            // dd($result);
            $dep_des = DB::table('staff_detail')->where('user_id', $id)->first();

            $all_asm = User::where('role', 5)->select('id as asm_id', 'name as asm_name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $dealers = User::where('role', 2)->where('status', 1)->select('id as dealer_id', 'name as dealer_name')->orderBy('name', 'ASC')->get();
            $offices = DB::table('users')->select('id as office_id', 'name as office_name')->where(['role' => 6, 'status' => 1])->get();
            $reporting_authorities = DB::table('users as u')->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')->where('sd.designation_id', get_designation(@$result->reporting_authority))->where('u.status', 1)->get();
            $levels = DB::table('designation_levels')->get();
            if (empty($dep_des)) {
                $reportinglevels = $levels;
            } else {
                $reportinglevels = DB::table('designation_levels')->where('id', '<', getlevelbydesignation($dep_des->designation_id))->get();
            }
            $reporting_authorities = DB::table('users as u')
                ->join('staff_detail as sd', 'sd.user_id', '=', 'u.id')
                ->join('designations as d', 'sd.designation_id', '=', 'd.id')
                ->where('d.level', getlevelbydesignation(get_designation($result->reporting_authority)))
                ->select('u.id as uid', 'u.name as uname', 'sd.designation_id as des_id')
                ->get();
            $dealer_authorities = DB::table('users')
                ->where('id', @$result->dealer_id)
                ->select('reporting_authority')
                ->first();
            $dealer_authorities = explode(",", @$dealer_authorities->reporting_authority);
            return view('admin.editEmpHierarchy', [
                'result' => $result,
                'all_asm' => $all_asm,
                'dealers' => $dealers,
                'offices' => $offices,
                'dep_des' => $dep_des,
                'levels' => $levels,
                'reportinglevels' => $reportinglevels,
                'reporting_authorities' => $reporting_authorities,
                'dealer_authorities' => $dealer_authorities,
            ]);
        }
    }

    public function updateEmpHierarchy(Request $request)
    {
        $post = $request->all();
        if (!empty($post['del_id'])) {
            $this->validate(
                $request,
                [
                    'del_id' => 'required',
                    // 'fdate' => 'required',
                    // 'todate' => 'required',
                ],
                [
                    'del_id.required' => 'Please select dealer',
                    // 'fdate.required' => 'Please select date.',
                    // 'todate.required' => 'Please select.',
                ]
            );
        } elseif (!empty($post['authority'])) {
            $this->validate(
                $request,
                [
                    'authority' => 'required',
                    // 'fdate' => 'required',
                    // 'todate' => 'required',
                ],
                [
                    'authority.required' => 'Please select authority',
                    // 'fdate.required' => 'Please select date.',
                    // 'todate.required' => 'Please select.',
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'office' => 'required',
                    // 'fdate' => 'required',
                    // 'todate' => 'required',
                ],
                [
                    'office.required' => 'Please select office',
                    // 'fdate.required' => 'Please select date.',
                    // 'todate.required' => 'Please select.',
                ]
            );
        }

        $checkUser = DB::table('emp_hierarchy')->where('id', $post['id'])->select('dealer_id', 'authority_id')->first();

        if (!empty($post['del_id'])) {
            $d_id = $post['del_id'];
            // $asm_id = get_asm($d_id);

            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'dealer_id' => $d_id, 'authority_id' => $post['dealer_authid'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif ($post['del_id'] != $checkUser->dealer_id && $post['dealer_authid'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3, 'authority_id' => $post['dealer_authid']]);
            } elseif ($post['del_id'] != $checkUser->dealer_id && $post['dealer_authid'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 2, 'authority_id' => $post['dealer_authid']]);
            } else {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'authority_id' => $post['dealer_authid'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            }
            // DB::table('users')->where('id', $post['user_id'])->update(array('dealer_id' => $post['del_id'], 'reporting_authority' => $post['dealer_authid']));
        } elseif (!empty($post['authority'])) {
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif (!empty($checkUser) && $post['authority'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3));
            } elseif (!empty($checkUser) && $post['authority'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']));
            }
            // DB::table('users')->where('id', $post['user_id'])->update(array('dealer_id' => null, 'reporting_authority' => $post['authority']));
        } else {
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif (!empty($checkUser) && $post['office'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3));
            } elseif (!empty($checkUser) && $post['office'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']));
            }
            // DB::table('users')->where('id', $post['user_id'])->update(array('dealer_id' => null, 'reporting_authority' => $post['office']));
        }
        return redirect('/admin/staff_management')->with('success', 'data updated succesfully');
    }


    public function statusEmpHierarchy($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('emp_hierarchy')->where('id', $id)->update($udata);
                Session::flash('success', 'Member deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('emp_hierarchy')->where('id', $id)->update($udata);
                Session::flash('success', 'Member activated successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/emp_hierarchy');
    }

    // view Advisors listing
    public function advisors(Request $request, $id)
    {
        $search = $request->search;
        $result = DB::table('advisors')
            ->leftjoin('dealer_department', 'dealer_department.id', '=', 'department')
            ->select('advisors.*', 'dealer_department.name as department_name')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('advisors.name', 'like', '%' . $search . '%');
                            $query->orWhere('advisors.pan_no', 'like', '%' . $search . '%');
                            $query->orWhere('advisors.mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->where('advisors.dealer_id', $id)
            ->orderBy('advisors.status', 'DESC')->paginate(20);
        // dd($result[0]->id);
        return view('admin.advisors', [
            'result' => $result->appends(Input::except('page')),
            'dealer_id' => $id,
            'advisor_id' => @$result[0]->id,
        ]);
    }

    public function addAdvisorIncentive(Request $request, $dealer_id)
    {
        $request->validate([
            'incentive' => 'required'
        ]);

        $advisors_id = DB::table('advisors')->where('dealer_id', $dealer_id)->get();
        foreach ($advisors_id as $key => $value) {
            $check = DB::table('advisor_shares')->where('dealer_id', $dealer_id)->where('advisor_id', $value->id)->first();
            if (!empty($check)) {
                DB::table('advisor_shares')->where('dealer_id', $dealer_id)->where('advisor_id', $value->id)->update([
                    'dealer_id' => $dealer_id,
                    'advisor_id' => $value->id,
                    'advisor_share' => $request->incentive,
                    'created_at' => getCurrentTimestamp(),
                ]);
            } else {
                DB::table('advisor_shares')->insert([
                    'dealer_id' => $dealer_id,
                    'advisor_id' => $value->id,
                    'advisor_share' => $request->incentive,
                    'created_at' => getCurrentTimestamp(),
                ]);
            }
        }

        Session::flash('success', 'Incentive added successfully!');
        return redirect('/admin/advisors/' . $dealer_id);
    }
    // view add new advisor page
    public function addAdvisor($dealer_id)
    {
        $departments = DB::table('dealer_department')->get();
        return view('admin.addAdvisor', [
            'dealer_id' => $dealer_id,
            'departments' => $departments,
        ]);
    }
    // save new Advisor
    public function insertAdvisor(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                // 'pan_no' => 'required|min:10|max:10|regex:/[A-Za-z]{5}\d{4}[A-Za-z]{1}/|unique:advisors,pan_no',
                'mobile_no' => 'nullable|digits:10|unique:advisors,mobile_no',
                'department' => 'required',
                // 'advisor_share' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                //'pan_no.required' => 'Please enter PAN no.',   
                // 'mobile_no.required' => 'Please enter mobile no.',
                'department.required' => 'Please select department',
                // 'advisor_share.required' => 'Please enter advisor share',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'dealer_id' => $post['dealer_id'],
            'mobile_no' => $post['mobile_no'],
            'pan_no' => $post['pan_no'],
            'department' => $post['department'],
        );
        $advisor_id = DB::table('advisors')->insertGetId($data);
        // if (!empty($post['advisor_share'])) {
        //     DB::table('advisor_shares')->insert(['dealer_id' => $post['dealer_id'], 'advisor_id' => $advisor_id, 'created_at' => getCurrentTimestamp()]);
        // }
        Session::flash('success', 'Advisor added successfully!');
        return redirect('/admin/advisors/' . $post['dealer_id']);
    }
    // view edit advisor page
    public function editAdvisor($dealer_id, $id)
    {
        $result = DB::table('advisors')->where('id', $id)->first();
        $departments = DB::table('dealer_department')->get();
        return view('admin.editAdvisor', [
            'dealer_id' => $dealer_id,
            'departments' => $departments,
            'result' => $result,
        ]);
    }
    // update existing Advisor
    public function updateAdvisor(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                // 'pan_no' => 'required|min:10|max:10|regex:/[A-Za-z]{5}\d{4}[A-Za-z]{1}/|unique:advisors,pan_no',
                'mobile_no' => 'nullable|digits:10|unique:advisors,mobile_no,' . $post['id'],
                'department' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                //'pan_no.required' => 'Please enter PAN no.',   
                // 'mobile_no.required' => 'Please enter mobile no.',
                'department.required' => 'Please select department',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'mobile_no' => $post['mobile_no'],
            'pan_no' => $post['pan_no'],
            'department' => $post['department'],
        );
        DB::table('advisors')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Advisor updated successfully!');
        return redirect('/admin/advisors/' . $post['dealer_id']);
    }
    //Change Advisor status or delete
    public function statusAdvisor($status, $dealer_id, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('advisors')->where('id', $id)->update($udata);
                Session::flash('success', 'Advisor deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('advisors')->where('id', $id)->update($udata);
                Session::flash('success', 'Advisor activated successfully!');
            } else if ($status == "delete") {
                DB::table('advisors')->where('id', $id)->delete();
                Session::flash('success', 'Advisor deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/advisors/' . $dealer_id);
    }

    public function advisorPercentageHistory($dealer_id, $advisor_id)
    {
        $percentages = DB::table('advisor_shares')->where(['dealer_id' => $dealer_id, 'advisor_id' => $advisor_id])->orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.advisorPercentageHistory', compact('percentages', 'dealer_id', 'advisor_id'));
    }

    public function addAdvisorPercentage($dealer_id, $advisor_id)
    {
        return view('admin.addAdvisorPercentage', [
            'dealer_id' => $dealer_id,
            'advisor_id' => $advisor_id,
        ]);
    }

    public function insertAdvisorPercentage(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'advisor_share' => 'required'
            ],
            [
                'advisor_share.required' => 'Please enter advisor share',
            ]
        );
        $data = array(
            'dealer_id' => $post['dealer_id'],
            'advisor_id' => $post['advisor_id'],
            'advisor_share' => $post['advisor_share'],
            'created_at' => getCurrentTimestamp(),
        );
        DB::table('advisor_shares')->insert($data);
        Session::flash('success', 'Percentage added successfully!');
        return redirect('/admin/advisor_percentage_history/' . $post['dealer_id'] . '/' . $post['advisor_id']);
    }

    // view Department listing
    public function department(Request $request)
    {
        $search = $request->search;
        $result = DB::table('departments')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->where('name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->orderBy('name', 'ASC')
            ->paginate(20);
        return view('admin.department', [
            'result' => $result->appends(Input::except('page')),
        ]);
    }
    // view add new department page
    public function addDepartment()
    {
        return view('admin.addDepartment');
    }
    // save new Department
    public function insertDepartment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required|unique:departments,name',
            ],
            [
                'name.required' => 'Please enter name',
            ]
        );
        $data = array(
            'name' => $post['name'],
        );
        DB::table('departments')->insert($data);
        Session::flash('success', 'Department added successfully!');
        return redirect('/admin/department');
    }
    // view edit Department page
    public function editDepartment($id)
    {
        $result = DB::table('departments')->where('id', $id)->first();
        if (!empty($result)) {
            return view('admin.editDepartment', [
                'result' => $result,
            ]);
        } else {
            Session::flash('error', 'No Department member found!');
            return redirect('/admin/department');
        }
    }
    // update existing Department
    public function updateDepartment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required|unique:departments,name,' . $post['id'],
            ],
            [
                'name.required' => 'Please enter name',
            ]
        );
        $data = array(
            'name' => $post['name'],
        );
        DB::table('departments')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Department updated successfully!');
        return redirect('/admin/department');
    }

    // view Dealer Department listing
    public function dealerDepartments(Request $request)
    {
        $search = $request->search;
        $result = DB::table('dealer_department')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->where('name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->orderBy('name', 'ASC')
            ->paginate(20);
        return view('admin.dealerDepartments', [
            'result' => $result->appends(Input::except('page')),
        ]);
    }
    // view add new Dealer department page
    public function addDealerDepartment()
    {
        return view('admin.addDealerDepartment');
    }
    // save new Dealer Department
    public function insertDealerDepartment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required|unique:departments,name',
            ],
            [
                'name.required' => 'Please enter name',
            ]
        );
        $data = array(
            'name' => $post['name'],
        );
        DB::table('dealer_department')->insert($data);
        Session::flash('success', 'Department added successfully!');
        return redirect('/admin/dealer_departments');
    }
    // view edit Dealer Department page
    public function editDealerDepartment($id)
    {
        $result = DB::table('dealer_department')->where('id', $id)->first();
        if (!empty($result)) {
            return view('admin.editDealerDepartment', [
                'result' => $result,
            ]);
        } else {
            Session::flash('error', 'No Department member found!');
            return redirect('/admin/dealer_departments');
        }
    }
    // update existing Dealer Department
    public function updateDealerDepartment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required|unique:departments,name,' . $post['id'],
            ],
            [
                'name.required' => 'Please enter name',
            ]
        );
        $data = array(
            'name' => $post['name'],
        );
        DB::table('dealer_department')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Department updated successfully!');
        return redirect('/admin/dealer_departments');
    }
    //view designation listing
    public function designation(Request $request)
    {
        $designationlist = DB::table('designations')->orderBy('id', 'ASC')->paginate(20);;
        return view('admin.designation', ['designationlist' => $designationlist]);
    }
    // view add new designation page
    public function addDesignation()
    {
        $levels = DB::table('designation_levels')->get();
        // $departments = DB::table('departments')->where('status',1)->orderBy('name', 'ASC')->get();
        return view('admin.addDesignation', compact('levels'));
    }
    // save new Department
    public function insertDesignation(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'level' => 'required',
                // 'department_id' => 'required',
                'designation' => 'required|unique:designations,designation',
            ],
            [
                'level.required' => 'Please select designation level',
                // 'department_id.required' => 'Please select department',
                'designation.required' => 'Please enter designation',
            ]
        );
        $data = array(
            'level' => $post['level'],
            // 'department_id' => $post['department_id'],
            'designation' => $post['designation'],
        );
        DB::table('designations')->insert($data);
        Session::flash('success', 'Designation added successfully!');
        return redirect('/admin/designation');
    }
    // view edit Designation page
    public function editDesignation($id)
    {
        $levels = DB::table('designation_levels')->get();
        $designationEdit = DB::table('designations')->where('id', $id)->first();
        // $departments = DB::table('departments')->where('status',1)->orderBy('name', 'ASC')->get();
        if (!empty($designationEdit)) {
            return view('admin.editDesignation', [
                'designationEdit' => $designationEdit,
                'levels' => $levels,
                // 'departments' => $departments,
            ]);
        } else {
            Session::flash('error', 'No Designation found!');
            return redirect('/admin/designation');
        }
    }
    // update designation
    public function updateDesignation(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'level' => 'required',
                // 'department_id' => 'required',
                'designation' => 'required|unique:designations,designation,' . $post['id'],
            ],
            [
                'level.required' => 'Please select designation level',
                // 'department_id.required' => 'Please select department',
                'designation.required' => 'Please enter designation',
            ]
        );
        $data = array(
            'level' => $post['level'],
            // 'department_id' => $post['department_id'],
            'designation' => $post['designation'],
        );
        DB::table('designations')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Designation updated successfully!');
        return redirect('/admin/designation');
    }

    //view designation level listing
    public function level(Request $request)
    {
        $levels = DB::table('designation_levels')->orderBy('id', 'ASC')->paginate(10);;
        return view('admin.levels', ['levels' => $levels]);
    }
    // view add new Level page
    public function addLevel()
    {
        return view('admin.addLevel');
    }
    // save new Level
    public function insertLevel(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'level' => 'required|unique:designation_levels,level',
            ],
            [
                'level.required' => 'Please enter level',
            ]
        );
        $data = array(
            'level' => $post['level'],
            'created_at' => date('Y-m-d h:i:s'),
        );
        DB::table('designation_levels')->insert($data);
        Session::flash('success', 'Level added successfully!');
        return redirect('/admin/level');
    }
    // view edit Level page
    public function editLevel($id)
    {
        $levelEdit = DB::table('designation_levels')->where('id', $id)->first();
        if (!empty($levelEdit)) {
            return view('admin.editLevel', [
                'levelEdit' => $levelEdit,
            ]);
        } else {
            Session::flash('error', 'No Level found!');
            return redirect('/admin/level');
        }
    }
    // update Level
    public function updateLevel(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'level' => 'required|unique:designation_levels,level,' . $post['id'],
            ],
            [
                'level.required' => 'Please enter Level',
            ]
        );
        $data = array(
            'level' => $post['level'],
            'updated_at' => date('Y-m-d h:i:s'),
        );
        DB::table('designation_levels')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Level updated successfully!');
        return redirect('/admin/level');
    }

    //view groups listing
    public function groups(Request $request)
    {
        $groupslist = DB::table('groups')->orderBy('id', 'ASC')->paginate(20);;
        return view('admin.groups', ['groupslist' => $groupslist]);
    }
    // view add new group page
    public function addGroup()
    {
        return view('admin.addGroup');
    }
    // save new Group
    public function insertGroup(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'group' => 'required|unique:groups,group_name',
            ],
            [
                'group.required' => 'Please enter group name',
            ]
        );
        $data = array(
            'group_name' => $post['group'],
        );
        DB::table('groups')->insert($data);
        Session::flash('success', 'Group added successfully!');
        return redirect('/admin/groups');
    }
    // view edit Group page
    public function editGroup($id)
    {
        $groupEdit = DB::table('groups')->where('id', $id)->first();
        if (!empty($groupEdit)) {
            return view('admin.editGroup', [
                'groupEdit' => $groupEdit,
            ]);
        } else {
            Session::flash('error', 'No Group found!');
            return redirect('/admin/groups');
        }
    }
    // update group
    public function updateGroup(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'group' => 'required|unique:groups,group_name,' . $post['id'],
            ],
            [
                'group.required' => 'Please enter designation',
            ]
        );
        $data = array(
            'group_name' => $post['group'],
        );
        DB::table('groups')->where('id', $post['id'])->update($data);
        Session::flash('success', 'group updated successfully!');
        return redirect('/admin/groups');
    }
    //Change Group status or delete
    public function statusGroup($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                DB::table('groups')->where('id', $id)->update($udata);
                Session::flash('success', 'Group deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                DB::table('groups')->where('id', $id)->update($udata);
                Session::flash('success', 'Group activated successfully!');
            } else if ($status == "delete") {
                DB::table('groups')->where('id', $id)->delete();
                Session::flash('success', 'Group deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/groups');
    }

    //view oems listing
    public function oems(Request $request)
    {
        $oemlist = DB::table('oems')->orderBy('id', 'ASC')->paginate(20);
        return view('admin.oems', ['oemlist' => $oemlist]);
    }
    // view add new oem page
    public function addOEM()
    {
        return view('admin.addOEM');
    }
    // save new OEM
    public function insertOEM(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'OEM' => 'required|unique:oems,oem',
            ],
            [
                'OEM.required' => 'Please enter OEM',
            ]
        );
        $data = array(
            'oem' => $post['OEM'],
            'date_added' => getCurrentTimestamp(),
        );
        DB::table('oems')->insert($data);
        Session::flash('success', 'OEM added successfully!');
        return redirect('/admin/oems');
    }

    // view edit Group page
    public function editOEM($id)
    {
        $oemEdit = DB::table('oems')->where('id', $id)->first();
        if (!empty($oemEdit)) {
            return view('admin.editOEM', [
                'oemEdit' => $oemEdit,
            ]);
        } else {
            Session::flash('error', 'No OEM found!');
            return redirect('/admin/oems');
        }
    }
    // update Oem
    public function updateOEM(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'OEM' => 'required|unique:oems,oem,' . $post['id'],
            ],
            [
                'OEM.required' => 'Please enter OEM',
            ]
        );
        $data = array(
            'oem' => $post['OEM'],
            'updated_at' => getCurrentTimestamp(),
        );
        DB::table('oems')->where('id', $post['id'])->update($data);
        Session::flash('success', 'OEM updated successfully!');
        return redirect('/admin/oems');
    }

    //Change OEM status or delete
    public function statusOEM($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                DB::table('oems')->where('id', $id)->update($udata);
                Session::flash('success', 'OEM deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                $udata['updated_at'] = date('Y-m-d h:i:s');
                DB::table('oems')->where('id', $id)->update($udata);
                Session::flash('success', 'OEM activated successfully!');
            } else if ($status == "delete") {
                DB::table('oems')->where('id', $id)->delete();
                Session::flash('success', 'Staff member deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/oems');
    }
    // view Models listing
    public function models(Request $request) //,$id
    {
        $search = $request->search;
        $result = DB::table('models')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->where('model_name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            //->where('dealer_id',$id)
            ->orderBy('model_name', 'ASC')->paginate(20);
        return view('admin.models', [
            'result' => $result->appends(Input::except('page')),
            //'dealer_id' => $id,
        ]);
    }


    // view add new model page
    public function addModel() //$dealer_id
    {
        $oemlist = DB::table('oems')->where('status', 1)->orderBy('id', 'ASC')->get();
        return view('admin.addModel', [
            'oemlist' => $oemlist,
            //'dealer_id' => $dealer_id,
        ]);
    }
    // save new Model
    public function insertModel(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'oem_id' => 'required',
                'template_id' => 'required',
                'name' => 'required',
                'size' => 'required',
            ],
            [
                'oem_id.required' => 'Please select OEM',
                'template_id.required' => 'Please select template',
                'name.required' => 'Please enter name',
                'size.required' => 'Please select size',
            ]
        );
        $data = array(
            'oem_id' => $post['oem_id'],
            'template_id' => $post['template_id'],
            'model_name' => $post['name'],
            'model_size' => $post['size'],
            'date_added' => getCurrentTimestamp(),
            //'dealer_id' => $post['dealer_id'],
        );
        DB::table('models')->insert($data);
        Session::flash('success', 'Model added successfully!');
        return redirect('/admin/models/');
    }
    // view edit Model page
    public function editModel($id)
    {
        $result = DB::table('models')->where('id', $id)->first();
        $templates = DB::table('treatment_templates')->where('oem_id',  $result->oem_id)->get();
        $oemlist = DB::table('oems')->where('status', 1)->orderBy('id', 'ASC')->get();
        return view('admin.editModel', [
            'result' => $result,
            'oemlist' => $oemlist,
            'templates' => $templates,
        ]);
    }
    // update existing Model
    public function updateModel(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'oem_id' => 'required',
                'template_id' => 'required',
                'name' => 'required',
                'size' => 'required',
            ],
            [
                'oem_id.required' => 'Please select OEM',
                'template_id.required' => 'Please select Template',
                'name.required' => 'Please enter name',
                'size.required' => 'Please select size',
            ]
        );
        $data = array(
            'oem_id' => $post['oem_id'],
            'template_id' => $post['template_id'],
            'model_name' => $post['name'],
            'model_size' => $post['size'],
        );
        DB::table('models')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Model updated successfully!');
        return redirect('/admin/models');
    }

    //Change Model status or delete
    public function statusModel($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('models')->where('id', $id)->update($udata);
                Session::flash('success', 'Model deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('models')->where('id', $id)->update($udata);
                Session::flash('success', 'Model activated successfully!');
            } else if ($status == "delete") {
                DB::table('models')->where('id', $id)->delete();
                Session::flash('success', 'Model deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/models/');
    }


    // View treatments list
    public function treatments(Request $request)
    {
        $post = $request->all();
        $models = DB::table('models')->get();
        $templates = DB::table('treatment_templates')->get();
        $type = 2;
        if (@$post['search'] == 'heavy' || @$post['search'] == 'Heavy') {
            $type = 1;
        } elseif (@$post['search'] == 'normal' || @$post['search'] == 'Normal') {
            $type = 0;
        }
        $result = DB::table('treatments')
            // ->join('users as u','u.id','=','treatments.dealer_id')
            ->join('models as m', 'm.id', '=', 'treatments.model_id')
            ->join('treatment_templates as tt', 'tt.id', '=', 'treatments.temp_id')
            ->select('treatments.*', 'm.model_name', 'm.model_size');
        // if(!empty($post['template_id'])){
        //      $result = $result->where('treatments.temp_id','=',$post['template_id']);
        // }
        $result = $result->where(function ($query) use ($post, $type) {
            //    if(!empty($post['dealer'])){
            //        if(isset($post['dealer'])){
            //            if(!empty(trim($post['dealer']))){
            //                $query->where('treatments.dealer_id','=',$post['dealer']);                
            //            }
            //        }if(!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')){
            //           $query->where('treatments.treatment_type','=',$type);
            //       }
            //       if(!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')){
            //        $query->where('treatments.treatment','LIKE','%'.@$post['search'].'%');
            //    }
            // }
            if (!empty($post['model'])) {
                if (isset($post['model'])) {
                    if (!empty(trim($post['model']))) {
                        $query->where('treatments.model_id', '=', $post['model']);
                    }
                }
                if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                    $query->where('treatments.treatment_type', '=', $type);
                }
                if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                    $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                }
            }
            if (!empty($post['template'])) {
                if (isset($post['template'])) {
                    if (!empty(trim($post['template']))) {
                        $query->where('treatments.temp_id', '=', $post['template']);
                    }
                }
                if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                    $query->where('treatments.treatment_type', '=', $type);
                }
                if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                    $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                }
            }
            if (!empty($post['search'])) {
                if (isset($post['search'])) {
                    if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                        $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                    }
                    if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                        $query->where('treatments.treatment_type', '=', $type);
                    }
                }
            }
            if (!empty($post['treatment_option'])) {
                if (isset($post['treatment_option'])) {
                    if (!empty(trim($post['treatment_option']))) {
                        $query->where('treatments.treatment_option', '=', $post['treatment_option']);
                    }
                }
                if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                    $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                }
                if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                    $query->where('treatments.treatment_type', '=', $type);
                }
            }
        });
        $result = $result->orderBy('treatments.treatment', 'ASC')->paginate(50);

        // dd($result);
        // $dealers = DB::table('treatments')
        // ->join('users as u','u.id','=','treatments.dealer_id')
        // ->select('treatments.dealer_id','u.name')
        // ->groupBy('treatments.dealer_id')
        // ->orderBy('u.name','ASC')
        // ->get();
        // if(!empty($post['model'])){
        //  $models = DB::table('treatments')
        //  ->join('models as m','m.id','=','treatments.model_id')
        //  ->select('treatments.model_id','m.model_name')
        //  ->groupBy('treatments.model_id')
        //  ->orderBy('m.model_name','ASC')
        //  //->where('treatments.dealer_id','=',$post['dealer'])
        //  ->get();   
        //  }else{
        //      $models = array();
        //  }
        return view('admin.treatments', [
            'result' => $result->appends(Input::except('page')),
            //'dealers' => $dealers,
            'models' => $models,
            'templates' => $templates,
            //'oldDealer' => @$post['dealer'],
            'oldModel' => @$post['model'],
            'oldTemplate' => @$post['template'],
            'oldSearch' => @$post['search'],
            'oldOption' => @$post['treatment_option'],
        ]);
    }

    // view add new Treatment page
    public function addTreatment()
    {
        // $products = DB::table('products')->where('status',1)->get();
        $templates = DB::table('treatment_templates')->get();
        $oemlist = DB::table('oems')->where('status', 1)->orderBy('id', 'ASC')->get();
        // $models = DB::table('models')->get();
        // $dealers = User::where('role',2)
        // ->select('id as dealer_id','name as dealer_name')
        // ->where('status',1)
        // ->orderBy('name',"ASC")
        // ->get();
        return view('admin.addTreatment', [
            'oemlist' => $oemlist,
            'templates' => $templates,
        ]);
    }

    // save new Treatment
    public function insertTreatment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'oem_id' => 'required',
                'tempId' => 'required',
                'model_id' => 'required',
                'treatment' => 'required',
                'treatment_type' => 'required',
                'labour_code' => 'required',
                // 'treatment_option' => 'required',
            ],
            [
                'oem_id.required' => 'Please select OEM',
                'tempId.required' => 'Please select Template',
                'model_id.required' => 'Please select model',
                'treatment.required' => 'Please enter treatment',
                'treatment_type.required' => 'Please select type of treatment',
                'labour_code.required' => 'Please enter labour code',
                // 'treatment_option.required' => 'Please select option of treatment',
            ]
        );

        $checkData = DB::table('treatments')
            ->where('oem_id', $post['oem_id'])
            ->where('temp_id', $post['tempId'])
            ->where('model_id', $post['model_id'])
            ->where('treatment', $post['treatment'])
            ->where('treatment_type', $post['treatment_type'])
            ->where('labour_code', $post['labour_code'])
            ->get();
        if (count($checkData) > 0) {
            Session::flash('error', 'The treatment is already added for this model! Please select another model or enter different treatment.');
            return redirect('/admin/addTreatment');
        } else {
            $data = array(
                'temp_id'  => $post['tempId'],
                'oem_id' => $post['oem_id'],
                'model_id' => $post['model_id'],
                'treatment' => $post['treatment'],
                'treatment_type' => $post['treatment_type'],
                'labour_code' => $post['labour_code'],
                'treatment_option' => @$post['treatment_option'],
                'time_period' => @$post['time_period'],
                'time_unit' => @$post['time_period_unit'],
            );
            $treatment_id = DB::table('treatments')->insertGetId($data);
            Session::flash('success', 'Treatment added successfully!');
            // return redirect('/admin/treatments');
            return redirect('/admin/treatmentProducts/' . $treatment_id);
        }
    }

    public function updateTreatmentPrice(Request $request, $treatment_id)
    {
        $post = $request->all();
        $product_price = DB::table('products_treatments')->where('tre_id', $treatment_id)->sum('price');
        if (!empty($post)) {
            $this->validate(
                $request,
                [
                    // 'dealer_price' => 'required|numeric',
                    'customer_price' => 'required|numeric',
                    // 'incentive' => 'required|numeric',
                ],
                [
                    // 'dealer_price.required' => 'Please enter dealer price',
                    'customer_price.required' => 'Please enter customer price',
                    // 'incentive.required' => 'Please enter incentive',
                ]
            );
            $data = array(
                'customer_price' => $post['customer_price'],
                // 'dealer_price' => $post['dealer_price'],
                // 'incentive' => $post['incentive'],
            );
            DB::table('treatments')->where('id', $post['treatment_id'])->update($data);
            Session::flash('success', 'Treatment Price Updated successfully!');
            return redirect('/admin/treatments');
        } else {
            $result = DB::table('treatments')->where('id', $treatment_id)->first();
            // dd($check);
            return view('admin.addTreatmentPrice', [
                'treatment_id' => $treatment_id,
                'result' => $result,
                'product_price' => $product_price,
            ]);
        }
    }

    // view edit treatment page
    public function editTreatment($id)
    {
        $result = DB::table('treatments')->where('id', $id)->first();
        $templates = DB::table('treatment_templates')->get();
        $oemlist = DB::table('oems')->where('status', 1)->orderBy('id', 'ASC')->get();
        $oem_id = ($result->oem_id == '') ? 0 : $result->oem_id;
        $template_id = ($result->temp_id == '') ? 0 : $result->temp_id;
        $models = DB::table('models')->where(['oem_id' => $oem_id, 'template_id' => $template_id])->get();

        // $dealers = User::where('role',2)
        // ->select('id as dealer_id','name as dealer_name')
        // ->where('status',1)
        // ->orderBy('name',"ASC")
        // ->get();
        return view('admin.editTreatment', [
            'result' => $result,
            'oemlist' => $oemlist,
            'models' => $models,
            'templates' => $templates,
        ]);
    }

    // update existing Treatment
    public function updateTreatment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'tempId' => 'required',
                'oem_id' => 'required',
                'model_id' => 'required',
                'treatment' => 'required',
                'treatment_type' => 'required',
                'labour_code' => 'required',
            ],
            [
                'tempId.required' => 'Please select Template',
                'oem_id.required' => 'Please select OEM',
                'model_id.required' => 'Please select model',
                'treatment.required' => 'Please enter treatment',
                'treatment_type.required' => 'Please select type of treatment',
                'labour_code.required' => 'Please enter labour code',
            ]
        );
        $checkData = DB::table('treatments')
            ->where('temp_id', $post['tempId'])
            ->where('oem_id', $post['oem_id'])
            ->where('model_id', $post['model_id'])
            ->where('treatment', $post['treatment'])
            ->where('treatment_type', $post['treatment_type'])
            ->where('labour_code', $post['labour_code'])
            ->where('id', '!=', $post['id'])
            ->get();
        if (!empty($post['treatment_option'])) {
            $post['time_period'] = $post['time_period'];
            $post['time_period_unit'] = $post['time_period_unit'];
        } else {
            $post['time_period'] = NULL;
            $post['time_period_unit'] = NULL;
        }

        if (count($checkData) > 0) {
            Session::flash('error', 'The treatment is already added for this model! Please select another model or enter different treatment.');
            return redirect('/admin/editTreatment/' . $post['id']);
        } else {
            $data = array(
                'temp_id'  => $post['tempId'],
                'oem_id' => $post['oem_id'],
                'model_id' => $post['model_id'],
                'treatment' => $post['treatment'],
                'treatment_type' => $post['treatment_type'],
                'labour_code' => $post['labour_code'],
                'treatment_option' => $post['treatment_option'],
                'time_period' => @$post['time_period'],
                'time_unit' => @$post['time_period_unit']
            );
            DB::table('treatments')->where('id', $post['id'])->update($data);
            Session::flash('success', 'Treatment updated successfully! Please add Products for the Treatment.');
            return redirect('/admin/treatmentProducts/' . $post['id']);
        }
    }

    //Change Treatment status or delete
    public function statusTreatment($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('treatments')->where('id', $id)->update($udata);
                Session::flash('success', 'Treatment deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('treatments')->where('id', $id)->update($udata);
                Session::flash('success', 'Treatment activated successfully!');
            } else if ($status == "delete") {
                $udata['status'] = 0;
                DB::table('treatments')->where('id', $id)->update($udata);
                Session::flash('success', 'Treatment deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/treatments');
    }
    // View Upload treatment page
    public function uploadTreatment()
    {
        // $dealers = User::where('role',2)
        // ->select('id as dealer_id','name as dealer_name')
        // ->orderBy('name',"ASC")
        // ->get();
        $templates = DB::table('treatment_templates')->get();
        $models = DB::table('models')->get();
        return view('admin.uploadTreatment', [
            //'dealers'=>$dealers,
            'models' => $models,
            'templates' => $templates,
        ]);
    }

    // Import treatments through excel
    public function importTreatment(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                //'dealer_id' => 'required',
                'tempId' => 'required',
                'model_id' => 'required',
                'csv' => 'required|mimes:xlsx',
            ],
            [
                //'dealer_id.required' => 'Please select dealer',
                'tempId.required' => 'Please select Template',
                'model_id.required' => 'Please select model',
                'csv.required' => 'Please select model',
                'csv.mimes' => 'File must be type of xlsx',
            ]
        );
        if (Input::hasFile('csv')) {
            $path = Input::file('csv')->getRealPath();
            $data = Excel::load($path, function ($reader) {
            })->get();
            if (!empty($data) && $data->count()) {
                foreach ($data as $key => $value) {
                    if ($value->treatment_type == '0.0') {
                        $treatment_type = 0;
                    } elseif ($value->treatment_type == '1.0') {
                        $treatment_type = 1;
                    }
                    $insert[] = [
                        //'dealer_id' => $post['dealer_id'],
                        'temp_id' => $post['tempId'],
                        'model_id' => $post['model_id'],
                        'treatment' => $value->treatment,
                        'treatment_type' => $treatment_type,
                        'labour_code' => $value->labour_code,
                        'customer_price' => $value->customer_price,
                        'dealer_price' => $value->dealer_price,
                        'incentive' => $value->incentive
                    ];
                    $data1['status'] = 0;
                    $delete = DB::table('treatments')
                        //->where('dealer_id',$post['dealer_id'])
                        ->where('model_id', $post['model_id'])
                        ->where('treatment', $value->treatment)
                        ->where('treatment_type', $treatment_type)
                        ->where('labour_code', $value->labour_code)
                        ->update($data1);
                }
                DB::table('treatments')->insert($insert);
                Session::flash('success', 'File uploaded successfully!');
                return redirect('/admin/treatments');
            } else {
                Session::flash('error', 'File not uploaded!');
                return redirect('/admin/uploadTreatment');
            }
        }
        return back();
    }

    // Download Treatments
    public function downloadTreatment(Request $request)
    {
        $post = $request->all();
        $type = 2;
        if (@$post['search'] == 'heavy' || @$post['search'] == 'Heavy') {
            $type = 1;
        } elseif (@$post['search'] == 'normal' || @$post['search'] == 'Normal') {
            $type = 0;
        }
        $result = DB::table('treatments')
            //->join('users as u','u.id','=','treatments.dealer_id')
            ->join('models as m', 'm.id', '=', 'treatments.model_id')
            ->select('treatments.*', 'm.model_name', 'm.model_size')
            ->where(function ($query) use ($post, $type) {
                // if(!empty($post['dealer'])){
                //     if(isset($post['dealer'])){
                //         if(!empty(trim($post['dealer']))){
                //             $query->where('treatments.dealer_id','=',$post['dealer']);                
                //         }
                //     }if(!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')){
                //         $query->where('treatments.treatment_type','=',$type);
                //     }
                //     if(!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')){
                //         $query->where('treatments.treatment','LIKE','%'.@$post['search'].'%');
                //     }
                // }
                if (!empty($post['template'])) {
                    if (isset($post['template'])) {
                        if (!empty(trim($post['template']))) {
                            $query->where('treatments.temp_id', '=', $post['template']);
                        }
                    }
                    if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                        $query->where('treatments.treatment_type', '=', $type);
                    }
                    if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                        $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                    }
                }
                if (!empty($post['model'])) {
                    if (isset($post['model'])) {
                        if (!empty(trim($post['model']))) {
                            $query->where('treatments.model_id', '=', $post['model']);
                        }
                    }
                    if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                        $query->where('treatments.treatment_type', '=', $type);
                    }
                    if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                        $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                    }
                }
                if (!empty($post['search'])) {
                    if (isset($post['search'])) {
                        if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                            $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                        }
                        if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                            $query->where('treatments.treatment_type', '=', $type);
                        }
                    }
                }
                if (!empty($post['treatment_option'])) {
                    if (isset($post['treatment_option'])) {
                        if (!empty(trim($post['treatment_option']))) {
                            $query->where('treatments.treatment_option', '=', $post['treatment_option']);
                        }
                    }
                    if (!empty(@$post['search'] != 'heavy' && @$post['search'] != 'heavy' && @$post['search'] != 'normal' && @$post['search'] != 'Normal')) {
                        $query->where('treatments.treatment', 'LIKE', '%' . @$post['search'] . '%');
                    }
                    if (!empty(@$post['search'] == 'heavy' || @$post['search'] == 'heavy' || @$post['search'] == 'normal' || @$post['search'] == 'Normal')) {
                        $query->where('treatments.treatment_type', '=', $type);
                    }
                }
            })
            ->orderBy('treatments.treatment', 'ASC')
            ->get();
        if (!empty($post['model'])) {
            $reportName = get_model_name($post['model']);
        } else {
            $reportName = 'All';
        }
        return Excel::create($reportName . '-Treatments-' . date("d-M-Y"), function ($excel) use ($result) {
            $final = array();
            foreach ($result as $key => $value) {
                if ($value->model_size == 1) {
                    $model = 'Large- ' . $value->model_name;
                } elseif ($value->model_size == 2) {
                    $model = 'Medium- ' . $value->model_name;
                } elseif ($value->model_size == 3) {
                    $model = 'Small- ' . $value->model_name;
                }
                if ($value->treatment_type == 0) {
                    $treatment_type = 'Normal';
                } elseif ($value->treatment_type == 1) {
                    $treatment_type = 'Heavy';
                }
                //$data['Dealer'] = get_name($value->dealer_id);
                $data['Treatment'] = $value->treatment;
                $data['Size- Model'] = $model;
                $data['Treatment_Type'] = $treatment_type;
                $data['Labour_Code'] = $value->labour_code;
                $data['Customer_Price'] = $value->customer_price;
                $data['Dealer_Price'] = $value->dealer_price;
                $data['Incentive'] = $value->incentive;
                $final[] = $data;
            }
            $excel->sheet('sheet', function ($sheet) use ($final) {
                $sheet->fromArray($final);
            });
        })->export('xlsx');
    }

    // view Treatment Products listing
    public function treatmentProducts(Request $request, $id)
    {
        //dd($id);
        $search = $request->search;
        $result = DB::table('products_treatments')
            ->join('products', 'products.id', '=', 'products_treatments.pro_id')
            ->where('products_treatments.tre_id', $id)
            ->select('products_treatments.*')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('products.name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->paginate(10);
        return view('admin.treatmentProducts', [
            'result' => $result->appends(Input::except('page')),
            'treatment_id' => $id,
        ]);
    }

    // view add new Treatment Product page
    public function addTreatmentProduct($treatment_id)
    {
        $products = DB::table('products')->get();
        return view('admin.addTreatmentProduct', [
            'treatment_id' => $treatment_id,
            'products' => $products,
        ]);
    }

    // save new Treatment Product
    public function insertTreatmentProduct(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'product_id' => 'required',
                'quantity' => 'required|numeric',
                // 'uom' => 'required',
                'price' => 'required',
            ],
            [
                'product_id.required' => 'Please enter name',
                'quantity.required' => 'Please enter quantity',
                // 'uom.required' => 'Please select unit of measurement',
                'price.required' => 'Please enter price',
            ]
        );
        $data = array(
            'pro_id' => $post['product_id'],
            'tre_id' => $post['treatment_id'],
            'quantity' => $post['quantity'],
            'uom' => $post['pro_uom'],
            'price' => $post['price'],
        );
        DB::table('products_treatments')->insert($data);
        Session::flash('success', 'Product added successfully!');
        return redirect('/admin/treatmentProducts/' . $post['treatment_id']);
    }

    // view edit Treatment Product page
    public function editTreatmentProduct($treatment_id, $id)
    {
        $products = DB::table('products')->get();
        $result = DB::table('products_treatments')->where('id', $id)->first();
        if (!empty($result)) {
            return view('admin.editTreatmentProduct', [
                'result' => $result,
                'treatment_id' => $treatment_id,
                'products' => $products,
            ]);
        } else {
            Session::flash('error', 'No product found!');
            return redirect('/admin/treatmentProducts/' . $post['treatment_id']);
        }
    }

    // update existing Treatment Product
    public function updateTreatmentProduct(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'product_id' => 'required',
                'quantity' => 'required|numeric',
                // 'uom' => 'required',
                'price' => 'required',
            ],
            [
                'product_id.required' => 'Please enter name',
                'quantity.required' => 'Please enter quantity',
                // 'uom.required' => 'Please select unit of measurement',
                'price.required' => 'Please enter price',
            ]
        );
        $data = array(
            'pro_id' => $post['product_id'],
            'tre_id' => $post['treatment_id'],
            'quantity' => $post['quantity'],
            'uom' => $post['pro_uom'],
            'price' => $post['price'],
        );
        DB::table('products_treatments')->where('id', $post['tp_id'])->update($data);
        Session::flash('success', 'Product updated successfully!');
        return redirect('/admin/treatmentProducts/' . $post['treatment_id']);
    }

    //Change Treatment Product status or delete
    public function statusTreatmentProduct($status, $treatment_id, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('products_treatments')->where('id', $id)->update($udata);
                Session::flash('success', 'Product deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('products_treatments')->where('id', $id)->update($udata);
                Session::flash('success', 'Product activated successfully!');
            } else if ($status == "delete") {
                DB::table('products_treatments')->where('id', $id)->delete();
                Session::flash('success', 'Product deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/treatmentProducts/' . $treatment_id);
    }

    // get Product Data
    public function getProductData(Request $request)
    {
        $data = array();
        $result = DB::table('products')->where('id', $request->p_id)->first();
        $quantity = $request->qty;
        $pro_qty = $result->quantity;
        $pro_price = $result->price;
        $pByq = $quantity / $pro_qty * $pro_price;

        $data['uom'] = $result->uom;
        $data['finalPrice'] = round($pByq, 2);

        return Response($data);
    }

    // view Treatments Templates listing
    public function treatmentTemplates(Request $request)
    {
        $search = $request->search;
        $result = DB::table('treatment_templates')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->Where('temp_name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->paginate(10);
        return view('admin.treatTemps', [
            'result' => $result->appends(Input::except('page')),
        ]);
    }

    // view add new Treatments Templates page
    public function addTreatmentTemplate()
    {
        $oems = DB::table('oems')->where('status', 1)->get();
        return view('admin.addTreatTemp', compact('oems'));
    }

    // save new Treatments Templates
    public function insertTreatmentTemp(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'oem_id' => 'required',
                'temp_name' => 'required|unique:treatment_templates,temp_name',
                'temp_description' => 'required|unique:treatment_templates,temp_description',
            ],
            [
                'oem_id.required' => 'Please select OEM',
                'temp_name.required' => 'Please enter Template Name',
                'temp_description.required' => 'Please enter Template Description',
            ]
        );
        $data = array(
            'oem_id' => $post['oem_id'],
            'temp_name' => $post['temp_name'],
            'temp_description' => $post['temp_description'],
            'created_at' => getCurrentTimestamp(),
        );
        DB::table('treatment_templates')->insert($data);
        Session::flash('success', 'Treatment Template added successfully!');
        return redirect('/admin/treatmentTemplates');
    }

    // view edit Treatments Templates page
    public function editTreatmentTemplate($id)
    {
        $oems = DB::table('oems')->where('status', 1)->get();
        $result = DB::table('treatment_templates')->where('id', $id)->first();
        return view('admin.editTreatTemp', [
            'result' => $result,
            'oems' => $oems,
        ]);
    }

    // update existing Treatments Templates
    public function updateTreatmentTemp(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'oem_id' => 'required',
                'temp_name' => 'required',
                'temp_description' => 'required',
            ],
            [
                'oem_id.required' => 'Please select oem',
                'temp_name.required' => 'Please enter Template Name',
                'temp_description.required' => 'Please enter Template Description',
            ]
        );
        $data = array(
            'oem_id' => $post['oem_id'],
            'temp_name' => $post['temp_name'],
            'temp_description' => $post['temp_description'],
            'updated_at' => getCurrentTimestamp(),
        );
        DB::table('treatment_templates')->where('id', $post['tempId'])->update($data);
        Session::flash('success', 'Treatment Template updated successfully!');
        return redirect('/admin/treatmentTemplates');
    }

    //Change Treatments Templates status or delete
    public function statusTreatTemp($status, $id)
    {
        $treatmentIds = DB::table('treatments')->where('temp_id', $id)->select('id')->get();
        foreach ($treatmentIds as $value) {
            $tre_ids[] = $value->id;
        }
        if (@$status) {
            if ($status == "delete") {
                if (!empty($tre_ids)) {
                    DB::table('products_treatments')->whereIn('tre_id', $tre_ids)->delete();
                    DB::table('treatments')->whereIn('id', $tre_ids)->delete();
                }
                DB::table('treatment_templates')->where('id', $id)->delete();
                Session::flash('success', 'Treatment Template deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/treatmentTemplates');
    }

    // view Add Duplicate Treatment Template Page 
    public function addDuplicateTemplate($temp_id)
    {
        $templateData = DB::table('treatment_templates')->where('id', $temp_id)->first();
        return view('admin.addDuplicateTemplate', [
            'templateData' => $templateData,
            'temp_id' => $temp_id,
        ]);
    }

    // Insert Duplicate Treatment Template Page
    public function insertDuplicateTemplate(Request $request)
    {
        $post = $request->all();
        $treatmentData = DB::table('treatments')->where('temp_id', $post['tempId'])->get();

        $this->validate(
            $request,
            [
                'temp_name' => 'required|unique:treatment_templates,temp_name',
                'temp_description' => 'required|unique:treatment_templates,temp_description',
            ],
            [
                'temp_name.required' => 'Please enter Template Name',
                'temp_description.required' => 'Please enter Template Description',
            ]
        );
        $data = array(
            'temp_name' => $post['temp_name'],
            'temp_description' => $post['temp_description'],
        );

        $duplicateId = DB::table('treatment_templates')->insertGetId($data);

        foreach ($treatmentData as $val) {

            $treatment = array(
                'temp_id' => $duplicateId,
                'model_id' => $val->model_id,
                'treatment' => $val->treatment,
                'treatment_type' => $val->treatment_type,
                'labour_code' => $val->labour_code,
                'customer_price' => $val->customer_price,
                'dealer_price' => $val->dealer_price,
                'incentive' => $val->incentive,
            );
            $treatment_id = DB::table('treatments')->insertGetId($treatment);
            $treatmentProductsData = DB::table('products_treatments')->where('tre_id', $val->id)->get();
            foreach ($treatmentProductsData as $treatmentProduct) {
                $product = array(
                    'pro_id' => $treatmentProduct->pro_id,
                    'tre_id' => $treatment_id,
                    'quantity' => $treatmentProduct->quantity,
                    'uom' => $treatmentProduct->uom,
                    'price' => $treatmentProduct->price,
                );
                DB::table('products_treatments')->insert($product);
            }
        }
        Session::flash('success', 'Treatment Template Duplicated Successfully!');
        return redirect('/admin/treatments?template=' . $duplicateId);
    }

    // view Add Duplicate Treatment's Price Page 
    public function addPercentagePrice($template_id)
    {
        return view('admin.addPercentagePrice', [
            'template_id' => $template_id,
        ]);
    }

    //Change price for selected template's treatments
    public function updatePercentagePrice(Request $request)
    {
        $post = $request->all();
        $treatments = DB::table('treatments')->where('temp_id', $post['template_id'])->select('id', 'dealer_price')->get();
        // dd($treatments);
        foreach ($treatments as $value) {
            if ($post['change'] == 'increase') {
                $increase = $post['amount'];
                $increase_amount = $value->dealer_price * $increase / 100;
                $updated_dealer_price = $value->dealer_price + $increase_amount;
            } else {
                $decrease = $post['amount'];
                $decrease_amount = $value->dealer_price * $decrease / 100;
                $updated_dealer_price = $value->dealer_price - $decrease_amount;
            }
            DB::table('treatments')->where('id', $value->id)->update(array('dealer_price' => $updated_dealer_price));
        }

        return redirect('admin/treatmentTemplates')->with('success', 'Percentage Price Updated Successfully');
    }

    // View Treatments of Particular Templates
    public function getTreatmentsList($id)
    {
        $treatments = DB::table('treatments')->where('temp_id', $id)->get();
        // dd($treatments);
        return view('admin.tempTreatlisting', [
            'treatments' => $treatments,
            'temp_id' => $id,
        ]);
    }


    // View Gallery
    public function gallery()
    {
        $images = DB::table('gallery')->where('type', '=', 'Image')->count();
        $videos = DB::table('gallery')->where('type', '=', 'Video')->count();
        return view('admin.gallery', [
            'images' => $images,
            'videos' => $videos,
        ]);
    }
    // View Images
    public function images()
    {
        $result = DB::table('gallery')->where('type', '=', 'Image')->paginate(20);
        return view('admin.images', [
            'result' => $result,
        ]);
    }
    // view add image page
    public function addImage()
    {
        return view('admin.addImage');
    }
    // save new image
    public function insertImage(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'title' => 'required|max:150',
                'image' => 'required|image',
            ],
            [
                'title.required' => 'Please enter title',
                'image.required' => 'Please upload image',
            ]
        );
        $image = Input::file('image');
        if (@$image) {
            $destination_path = 'images/';
            $Orname = $image->getClientOriginalName();
            $filename = trim(time() . '_' . $Orname);
            $image->move($destination_path, $filename);
        }
        $data = array(
            'title' => $request->title,
            'type' => 'Image',
            'path' => $filename,
        );
        $result = DB::table('gallery')->insert($data);
        Session::flash('success', 'Image added successfully!');
        return redirect('/admin/images');
    }
    // view edit image page
    public function editImage($id)
    {
        $result = DB::table('gallery')->where('id', $id)->first();
        return view('admin.editImage', [
            'result' => $result,
        ]);
    }
    // update existing image
    public function updateImage(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'title' => 'required|max:150',
                'image' => 'image',
            ],
            [
                'title.required' => 'Please enter title',
            ]
        );
        if (@$post['image']) {
            $image = Input::file('image');
            if (@$image) {
                $destination_path = 'images/';
                $Orname = $image->getClientOriginalName();
                $filename = trim(time() . '_' . $Orname);
                $image->move($destination_path, $filename);
            }
            $data = array(
                'title' => $request->title,
                'path' => $filename,
            );
        } else {
            $data = array(
                'title' => $request->title,
            );
        }
        $result = DB::table('gallery')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Image updated successfully!');
        return redirect('/admin/images');
    }
    //Change Image status or delete
    public function statusImage($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('gallery')->where('id', $id)->update($udata);
                Session::flash('success', 'Image deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('gallery')->where('id', $id)->update($udata);
                Session::flash('success', 'Image activated successfully!');
            } else if ($status == "delete") {
                DB::table('gallery')->where('id', $id)->delete();
                Session::flash('success', 'Image deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/images');
    }
    // View videos
    public function videos()
    {
        $result = DB::table('gallery')->where('type', '=', 'video')->paginate(20);
        return view('admin.videos', [
            'result' => $result,
        ]);
    }
    // view add video page
    public function addVideo()
    {
        return view('admin.addVideo');
    }
    // save new video link
    public function insertVideo(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'title' => 'required|max:150',
                'path' => 'required|url',
            ],
            [
                'title.required' => 'Please enter title',
                'path.required' => 'Please enter path',
            ]
        );
        $data = array(
            'title' => $request->title,
            'type' => 'video',
            'path' => $request->path,
        );
        $result = DB::table('gallery')->insert($data);
        Session::flash('success', 'Video added successfully!');
        return redirect('/admin/videos');
    }
    // view edit video page
    public function editVideo($id)
    {
        $result = DB::table('gallery')->where('id', $id)->first();
        return view('admin.editVideo', [
            'result' => $result,
        ]);
    }
    // update exit video url
    public function updateVideo(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'title' => 'required|max:150',
                'path' => 'required|url',
            ],
            [
                'title.required' => 'Please enter title',
                'path.required' => 'Please enter path',
            ]
        );
        $data = array(
            'title' => $request->title,
            'path' => $request->path,
        );
        $result = DB::table('gallery')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Video updated successfully!');
        return redirect('/admin/videos');
    }
    //Change Video status or delete
    public function statusVideo($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('gallery')->where('id', $id)->update($udata);
                Session::flash('success', 'Video deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('gallery')->where('id', $id)->update($udata);
                Session::flash('success', 'Video activated successfully!');
            } else if ($status == "delete") {
                DB::table('gallery')->where('id', $id)->delete();
                Session::flash('success', 'Video deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/videos');
    }
    // View Daily report by dealer and advisor, Mis report, DCF report
    public function reports(Request $request)
    {
        $search = $request->all();

        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['report_type'])) {
            $type = $search['report_type'];
        } else {
            $type = 'dealer';
        }

        $dealers = User::where('role', 2)->where('id', '!=', 58)->select('id', 'name')->orderBy('name', 'ASC')->get();
        /************************************ Dealer Wise Report Start *************************/
        $result = DB::table('jobs as j')->join('users', 'users.id', '=', 'j.user_id')
            ->select('j.*')

            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('j.dealer_id', '=', $search['dealer']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                            $query->whereDate('j.job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('j.job_date', $exp[1]);
                            $query->whereYear('j.job_date', $exp[0]);
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                } else {
                    $query->where('j.job_date', '=', date("Y-m-d"));
                }
            })
            ->where('delete_job', 1)
            ->where('foc', 0)
            ->get();
        // dd($result);                      
        $array = array();
        $result1 = array();
        $total_incentive = 0;
        foreach ($result as $key => $value) {
            $array['job_date'] = $value->job_date;
            $array['job_card_no'] = $value->job_card_no;
            $array['bill_no'] = $value->bill_no;
            $array['regn_no'] = $value->regn_no;
            $array['advisor_id'] = $value->advisor_id;
            $array['model_id'] = $value->model_id;
            $array['remarks'] = $value->remarks;
            $total_incentive = $total_incentive + $value->incentive;
            $decoded = json_decode($value->treatments);
            foreach ($decoded as $val) {
                $array['labour_code'] = $val->labour_code;
                $array['treatment_name'] = $val->treatment;
                $array['customer_price'] = $val->customer_price;
                $array['dealer_price'] = @$val->dealer_price;
                $array['incentive'] = $val->incentive;
                $result1[] = $array;
            }
        }
        /************************************ Dealer Wise Report End *************************/
        /************************************ MIS Report Start *************************/
        $users = DB::table('users')
            ->select('id')
            ->where(function ($query) use ($search) {
                if (isset($search['oem']) && isset($search['group'])) {
                    $query->where('group_id', $search['group']);
                    $query->where('oem_id', $search['oem']);
                } elseif (isset($search['oem'])) {
                    $query->where('oem_id', $search['oem']);
                } elseif (isset($search['group'])) {
                    $query->where('group_id', $search['group']);
                }
            })
            ->where('users.role', 2)
            ->orderBy('users.name', 'ASC')
            ->get();
        $oems = DB::table('oems')->where('status', 1)->get();
        $groups = DB::table('groups')->where('status', 1)->get();
        $mist = array();
        //dd($users);
        foreach ($users as $key => $value) {
            $mis = DB::table('jobs')
                ->select(DB::raw('SUM(jobs.treatment_total) as mtd_total,SUM(jobs.customer_price) as customer_price,SUM(jobs.hvt_total) as hvt_total, SUM(jobs.dealer_price) as dealer_price, SUM(jobs.incentive) as incentive,SUM(jobs.hvt_total) as mtd_hvt, SUM(jobs.hvt_value) as mtd_hvt_value,SUM(jobs.vas_total) as mtd_vas, SUM(jobs.vas_value) as mtd_vas_value, jobs.dealer_id'))
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from1']) && isset($search['to1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('jobs.job_date', '>=', $search['from1']);
                            $query->whereDate('jobs.job_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['to1'])) {
                        if (!empty(trim($search['to1']))) {
                            $query->whereDate('jobs.job_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['from1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('jobs.job_date', '>=', $search['from1']);
                        }
                    } elseif (!empty($search['month'])) {
                        $exp = explode('-', $search['month']);
                        $query->whereMonth('jobs.job_date', $exp[1]);
                        $query->whereYear('jobs.job_date', $exp[0]);
                    } else {
                        $query->whereDate('jobs.job_date', '>=', $first_day);
                        $query->whereDate('jobs.job_date', '<=', $today);
                    }
                })
                ->where('jobs.dealer_id', $value->id)
                ->where('jobs.delete_job', 1)
                ->where('jobs.foc', 0)
                ->groupBy('jobs.dealer_id')
                ->first();
            if (!empty($mis)) {
                $array['mtd_total'] = $mis->mtd_total;
                $array['customer_price'] = $mis->customer_price;
                $array['hvt_total'] = $mis->hvt_total;
                $array['dealer_price'] = $mis->dealer_price;
                $array['incentive'] = $mis->incentive;
                $array['mtd_hvt'] = $mis->mtd_hvt;
                $array['mtd_hvt_value'] = $mis->mtd_hvt_value;
                $array['mtd_vas'] = $mis->mtd_vas;
                $array['mtd_vas_value'] = $mis->mtd_vas_value;
                $array['dealer_id'] = $mis->dealer_id;
            } else {
                $array['mtd_total'] = 0;
                $array['customer_price'] = 0;
                $array['hvt_total'] = 0;
                $array['dealer_price'] = 0;
                $array['incentive'] = 0;
                $array['mtd_hvt'] = 0;
                $array['mtd_hvt_value'] = 0;
                $array['mtd_vas'] = 0;
                $array['mtd_vas_value'] = 0;
                $array['dealer_id'] = $value->id;
            }
            $mist[] = $array;
        }
        $i = 0;
        foreach ($mist as $key => $value) {
            $total = DB::table('jobs_by_date')
                ->select(DB::raw('SUM(total_jobs) as total_jobs,dealer_id'))
                ->where('dealer_id', $value['dealer_id'])
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from1']) && isset($search['to1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('job_added_date', '>=', $search['from1']);
                            $query->whereDate('job_added_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['to1'])) {
                        if (!empty(trim($search['to1']))) {
                            $query->whereDate('job_added_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['from1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('job_added_date', '>=', $search['from1']);
                        }
                    } elseif (!empty($search['month'])) {
                        $exp = explode('-', $search['month']);
                        $query->whereMonth('job_added_date', $exp[1]);
                        $query->whereYear('job_added_date', $exp[0]);
                    } else {
                        $query->whereDate('job_added_date', '>=', $first_day);
                        $query->whereDate('job_added_date', '<=', $today);
                    }
                })
                ->groupBy('dealer_id')
                ->first();
            if (!empty($total->total_jobs)) {
                $mist[$i]['service_load'] = $total->total_jobs;
            } else {
                $mist[$i]['service_load'] = 0;
            }
            $i++;
        }
        /************************************ MIS Report End *************************/
        /************************************ Advisor Wise Report Start *************************/
        $data = DB::table('jobs')
            ->where('foc', 0)
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(hvt_value) as hvt_customer_price,  SUM(incentive) as vas_incentive, advisor_id, job_date'))
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('dealer_id', '=', $search['dealer']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('job_date', '>=', $search['from']);
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('job_date', $exp[1]);
                            $query->whereYear('job_date', $exp[0]);
                        }
                    }
                }
            })
            ->where('delete_job', 1)
            ->groupBy('advisor_id')
            ->get();

        $advisors = array();
        $i = $mtd_total = 0;
        if (count($data) > 0) {
            foreach ($data as $value) {
                $hvt_incentive = 0;
                $decoded_jobs = explode(',', $value->job_id);
                foreach ($decoded_jobs as $key => $val) {
                    $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                    $decoded_treatments = json_decode(@$treat->treatments);
                    if (!empty($decoded_treatments)) {
                        foreach ($decoded_treatments as $key => $val1) {
                            if ($val1->treatment_type == 1) {
                                $hvt_incentive = $hvt_incentive + $val1->incentive;
                            }
                        }
                    }
                }
                $advisor['advisor_id'] = $value->advisor_id;
                $advisor['vas_customer_price'] = $value->vas_customer_price;
                $advisor['vas_incentive'] = $value->vas_incentive;
                $advisor['hvt_customer_price'] = $value->hvt_customer_price;
                $advisor['hvt_incentive'] = $hvt_incentive;
                $advisors[] = $advisor;
                @$total_service = DB::table('jobs_by_date')
                    ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                    ->where(function ($query) use ($search, $first_day, $today, $value) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['from']) && isset($search['to'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_added_date', '>=', $search['from']);
                                    $query->whereDate('job_added_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['to'])) {
                                if (!empty(trim($search['to']))) {
                                    $query->whereDate('job_added_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['from'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_added_date', '>=', $search['from']);
                                }
                            } elseif (isset($search['month1'])) {
                                if (!empty(trim($search['month1']))) {
                                    $exp = explode('-', $search['month1']);
                                    $query->whereMonth('job_added_date', $exp[1]);
                                    $query->whereYear('job_added_date', $exp[0]);
                                }
                            }
                        } else {
                            $query->whereDate('job_added_date', '=', @$value->job_date);
                        }
                    })
                    ->first();
                @$total_jobs = DB::table('jobs')
                    ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                    ->where(function ($query) use ($search, $first_day, $today, $value) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['from']) && isset($search['to'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_date', '>=', $search['from']);
                                    $query->whereDate('job_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['to'])) {
                                if (!empty(trim($search['to']))) {
                                    $query->whereDate('job_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['from'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_date', '>=', $search['from']);
                                }
                            } elseif (isset($search['month1'])) {
                                if (!empty(trim($search['month1']))) {
                                    $exp = explode('-', $search['month1']);
                                    $query->whereMonth('job_date', $exp[1]);
                                    $query->whereYear('job_date', $exp[0]);
                                }
                            }
                        } else {
                            $query->whereDate('job_date', '=', @$value->job_date);
                        }
                    })
                    ->where('delete_job', 1)
                    ->first();
                $total_job_array = array(
                    'mtd_total' => @$total_service->mtd_total,
                    'mtd_vas_value' => @$total_jobs->mtd_vas_value,
                    'mtd_vas_total' => @$total_jobs->mtd_vas_total,
                    'mtd_hvt_value' => @$total_jobs->mtd_hvt_value,
                    'mtd_hvt_total' => @$total_jobs->mtd_hvt_total,
                );
                $i++;
            }
        }
        /************************************ Advisor Wise Report End *************************/
        /************************************ DCF Report Start *******************************/
        if (@$search['selectMonth']) {
            $monthYear = explode('-', $search['selectMonth']);
            $year = $monthYear[0];
            $month =  $monthYear[1];
            $model = array();
            $dealers = DB::table('users')->where('role', 2)->where('id', '!=', 58)->orderBy('name', 'ASC')->get();
            return Excel::create('DCF_' . date("d-M-Y"), function ($excel) use ($dealers, $search, $month, $year) {
                foreach ($dealers as $dealerValue) {
                    $excel->sheet(get_name($dealerValue->id), function ($sheet) use ($dealerValue, $search, $month, $year) {
                        $getModels = DB::table('models as m')
                            ->select(DB::raw('group_concat(m.id) as model_id, m.model_size'))
                            ->where('m.dealer_id', $dealerValue->id)
                            ->groupBy('m.model_size')
                            ->orderBy('m.model_size', 'ASC')
                            ->get();
                        foreach ($getModels as $value) {
                            $model_id = explode(',', $value->model_id);
                            $jobs = DB::table('jobs')
                                ->select('id', 'model_id', 'treatments')
                                ->where('dealer_id', $dealerValue->id)
                                ->whereIn('model_id', $model_id)
                                ->whereMonth('job_date', $month)
                                ->whereYear('job_date', $year)
                                ->where('delete_job', 1)
                                ->get();
                            $getTreatments = DB::table('treatments')
                                ->select('id', 'treatment', 'labour_code')
                                ->where('dealer_id', $dealerValue->id)
                                ->whereIn('model_id', $model_id)
                                ->orderBy('treatment', 'ASC')
                                ->get();
                            $tcounts = array();
                            $i = 0;
                            foreach ($getTreatments as $key => $tvalue) {
                                $tcounts[$i]['id'] = $tvalue->id;
                                $tcounts[$i]['treatment'] = $tvalue->treatment;
                                $tcounts[$i]['labour_code'] = $tvalue->labour_code;
                                $tcounts[$i]['total'] = 0;
                                $tcounts[$i]['customer_price'] = 0;
                                foreach ($jobs as $jvalue) {
                                    if (@$jvalue->treatments) {
                                        $t = json_decode($jvalue->treatments);
                                        if (@$t) {
                                            foreach ($t as $jtvalue) {
                                                if ($jtvalue->id == $tvalue->id) {
                                                    $tcounts[$i]['total']++;
                                                    $tcounts[$i]['customer_price'] = $tcounts[$i]['customer_price'] + $jtvalue->customer_price;
                                                }
                                            }
                                        }
                                    }
                                }
                                $i++;
                            }
                            $final_treatments = array();
                            $tnames = array();
                            $j = 0;
                            foreach ($tcounts as $valuet) {
                                if (in_array($valuet['treatment'], $tnames)) {
                                    $a = array_search($valuet['treatment'], $tnames);
                                    $final_treatments[$a]['total'] = $final_treatments[$a]['total'] + $valuet['total'];
                                    $final_treatments[$a]['customer_price'] = $final_treatments[$a]['customer_price'] + $valuet['customer_price'];
                                } else {
                                    $tnames[$j] = $valuet['treatment'];
                                    $final_treatments[$j]['labour_code'] = $valuet['labour_code'];
                                    $final_treatments[$j]['treatment'] = $valuet['treatment'];
                                    $final_treatments[$j]['total'] = $valuet['total'];
                                    $final_treatments[$j]['customer_price'] = $valuet['customer_price'];
                                    $j++;
                                }
                            }
                            $sheet->setCellValue('A1', 'Large');
                            $sheet->setCellValue('B2', 'Treatment');
                            $sheet->setCellValue('B3', 'Labour_Code');
                            $sheet->setCellValue('B4', 'Number');
                            $sheet->setCellValue('B5', 'Value');
                            $sheet->setCellValue('A6', 'Medium');
                            $sheet->setCellValue('B7', 'Treatment');
                            $sheet->setCellValue('B8', 'Labour_Code');
                            $sheet->setCellValue('B9', 'Number');
                            $sheet->setCellValue('B10', 'Value');
                            $sheet->setCellValue('A11', 'Small');
                            $sheet->setCellValue('B12', 'Treatment');
                            $sheet->setCellValue('B13', 'Labour_Code');
                            $sheet->setCellValue('B14', 'Number');
                            $sheet->setCellValue('B15', 'Value');
                            if ($value->model_size == 1) {
                                $i = 2;
                                $in = 'C';
                                foreach ($final_treatments as  $value1) {
                                    $sheet->setCellValue($in . $i, $value1['treatment']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value1['labour_code']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value1['total']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value1['customer_price']);
                                    $i = 2;
                                    $in++;
                                }
                            }
                            if ($value->model_size == 2) {
                                $i = 7;
                                $in = 'C';
                                foreach ($final_treatments as  $value2) {
                                    $sheet->setCellValue($in . $i, $value2['treatment']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value2['labour_code']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value2['total']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value2['customer_price']);
                                    $i = 7;
                                    $in++;
                                }
                            }
                            if ($value->model_size == 3) {
                                $i = 12;
                                $in = 'C';
                                foreach ($final_treatments as $value3) {
                                    $sheet->setCellValue($in . $i, $value3['treatment']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value3['labour_code']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value3['total']);
                                    $i++;
                                    $sheet->setCellValue($in . $i, $value3['customer_price']);
                                    $i = 12;
                                    $in++;
                                }
                            }
                        }
                    });
                }
            })->export('xlsx');
        }
        /************************************ DCF Report End *******************************/
        Session::put('oldReport', $type);
        return view('admin.reports', [
            'result' => $result1,
            'total_incentive' => $total_incentive,
            'advisors' => $advisors,
            'total_job_array' => @$total_job_array,
            'mis' => $mist,
            'dealers' => $dealers,
            'oems' => $oems,
            'groups' => $groups,
            'oldFromDate' => @$search['from'],
            'oldToDate' => @$search['to'],
            'oldFromDate1' => @$search['from1'],
            'oldToDate1' => @$search['to1'],
            'oldDealer' => @$search['dealer'],
            'oldDealers' => @$search['dealers'],
            'oldMonth' => @$search['month'],
            'oldSelectMonth' => @$search['month1'],
            'oldReport' => @$type,
            'tabName' => @$search['tabName'],
            'oldOem' => @$search['oem'],
            'oldGroup' => @$search['group'],
        ]);
    }

    //get asm list by firm id
    public function getByfirm($id)
    {
        if (!$id) {
            $html = '<option value="">Select ASM</option>';
        } else {
            $html = '';
            $asms = DB::table('users')->select('id', 'name')->where(['firm_id' => $id, 'role' => 5, 'status' => 1])->get();
            $html .= '<option value="">Select ASM</option>';
            foreach ($asms as $asm) {
                $html .= '<option value="' . $asm->id . '">' . $asm->name . '</option>';
            }
        }
        return response()->json(['html' => $html]);
    }

    // Get asm through firm id in Ajax
    // public function getByfirm(Request $request)
    // {
    //     dd($request->firm_id);
    //     $firm_id = $request->firm_id;
    //     $asms = DB::table('users')->select('id','name')->where(['firm_id'=>$firm_id, 'role'=>5, 'status'=>1])->get();
    //     $asms = json_decode(json_encode($asms),true);

    //     if(@$asms){
    //         $html = '<option value="">Select ASM</option>';
    //         foreach ($asms as $asm) {
    //             $asm_id = $asm['id'];
    //             $asm_name = $asm['name'];
    //             $html .= "<option value='$asm_id'>$asm_name</option>";
    //         }
    //     }else{
    //         $html = "<option value=''>Select ASM</option>";
    //     }
    //     return $html;
    // }

    //get dealers list by firm id
    public function getDealersByfirm($id)
    {
        if (!$id) {
            $html = '<option value="">Select Dealer</option>';
        } else {
            $html = '';
            $dealers = DB::table('users')->select('id', 'name')->where(['firm_id' => $id, 'role' => 2, 'status' => 1])->get();
            $html .= '<option value="">Select Dealer</option>';
            foreach ($dealers as $dealer) {
                $html .= '<option value="' . $dealer->id . '">' . $dealer->name . '</option>';
            }
        }
        return response()->json(['html' => $html]);
    }

    //get asm list by firm id
    public function getAsmByfirm($firm_id)
    {
        $Asms = DB::table('users')->select('id', 'name')->where(['firm_id' => $firm_id, 'role' => 5, 'status' => 1])->get();
        $Asms = json_decode(json_encode($Asms), true);
        if (@$Asms) {
            $html = '<option value="">Select ASM</option>';
            foreach ($Asms as $asm) {
                $asm_name = $asm["name"];
                $asm_id = $asm["id"];
                $html .= "<option value='$asm_id'>$asm_name</option>";
            }
        } else {
            $html = "<option value=''>No ASM found</option>";
        }
        return response()->json(['html' => $html]);
    }

    // View Daily report by dealer and advisor
    public function dailyReport(Request $request)
    {
        $search = $request->all();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['report_type'])) {
            $type = $search['report_type'];
        } else {
            $type = 'dealer';
        }

        if (!empty($search['firm']) && empty($search['asm']) && empty($search['dealer'])) {
            $dealers = User::where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = array();
            foreach ($dealers as $k => $v) {
                $d_ids[] = $dealers[$k]->id;
            }
            $oems = User::where('status', 1)->where('oem_id', '!=', null)->whereIn('id', $d_ids)->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->where('group_id', '!=', null)->whereIn('id', $d_ids)->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->whereIn('dealer_id', $d_ids)->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else if (!empty($search['firm']) && !empty($search['asm']) && empty($search['dealer'])) {
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($search['asm'], $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
            $oems = User::where('status', 1)->whereIn('id', $d_ids)->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->whereIn('id', $d_ids)->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->whereIn('dealer_id', $d_ids)->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else if (!empty($search['firm']) && !empty($search['asm']) && !empty($search['dealer'])) {
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($search['asm'], $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
            $oems = User::where('status', 1)->whereIn('id', $d_ids)->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->whereIn('id', $d_ids)->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->where('dealer_id', $search['dealer'])->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else if (!empty($search['firm']) && !empty($search['dealer']) && empty($search['asm'])) {
            $dealers = User::where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = array();
            foreach ($dealers as $k => $v) {
                $d_ids[] = $dealers[$k]->id;
            }
            $oems = User::where('status', 1)->where('id', $search['dealer'])->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->where('id', $search['dealer'])->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->where('dealer_id', $search['dealer'])->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else if (empty($search['firm']) && !empty($search['dealer']) && empty($search['asm'])) {
            $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = array();
            $oems = User::where('status', 1)->where('id', $search['dealer'])->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->where('id', $search['dealer'])->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->where('dealer_id', $search['dealer'])->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else {
            $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = [];
            $oems = DB::table('oems')->select('id as oem_id')->where('status', 1)->get();
            $groups = DB::table('groups')->select('id as group_id')->where('status', 1)->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        }

        $firms = DB::table('firms')->get();
        $asms = DB::table('users')->where(["firm_id" => @$search['firm'], "role" => 5, 'status' => 1])->get();
        /************************************ Firm Wise Report Start *************************/
        $firmResult = DB::table('jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search, $d_ids) {
                if (!empty($search)) {
                    if (isset($search['firm'])) {
                        if (!empty(trim($search['firm']))) {
                            $query->whereIn('j.dealer_id', $d_ids);
                        }
                    }
                    if (isset($search['asm'])) {
                        if (!empty(trim($search['asm']))) {
                            $query->whereIn('j.dealer_id', $d_ids);
                        }
                    }
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('j.dealer_id', '=', $search['dealer']);
                        }
                    }
                    // if(isset($search['advisor'])){
                    //     if(!empty(trim($search['advisor']))){
                    //         $query->where('j.advisor_id','=',$search['advisor']);
                    //     }
                    // }
                    if (isset($search['department'])) {
                        if (!empty(trim($search['department']))) {
                            $query->where('j.department_id', '=', $search['department']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                            $query->whereDate('j.job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('j.job_date', $exp[1]);
                            $query->whereYear('j.job_date', $exp[0]);
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                } else {
                    $query->whereDate('j.job_date', '=', date("Y-m-d"));
                }
            })
            ->where('delete_job', 1)
            ->orderBy('j.job_date', 'ASC')
            ->get();

        $array4 = array();
        $result4 = array();
        $total_incentive = 0;

        foreach ($firmResult as $k => $v1) {
            $array4['job_date'] = $v1->job_date;
            $array4['dealer_id'] = $v1->dealer_id;
            $array4['job_card_no'] = $v1->job_card_no;
            $array4['bill_no'] = $v1->bill_no;
            $array4['regn_no'] = $v1->regn_no;
            $array4['advisor_id'] = $v1->advisor_id;
            $array4['model_id'] = $v1->model_id;
            $array4['remarks'] = $v1->remarks;
            // $array4['foc_options'] = $v1->foc_options;
            if (!empty($v1->incentive)) {
                $v1->incentive = $v1->incentive;
            } else {
                $v1->incentive = 0;
            }
            $total_incentive = $total_incentive + @$v1->incentive;
            $trtmnts = json_decode($v1->treatments);

            foreach ($trtmnts as $v2) {
                $array4['labour_code'] = $v2->labour_code;
                $array4['job_type'] = @$v2->job_type;
                $array4['treatment_name'] = $v2->treatment;
                $array4['customer_price'] = $v2->customer_price;
                $array4['actual_price'] = @$v2->actualPrice;
                $array4['difference_price'] = @$v2->difference;
                $array4['dealer_price'] = @$v2->dealer_price;
                $array4['incentive'] = @$v2->incentive;
                $array4['treatment_id'] = @$v2->id;

                // find all brands by treatment id 
                $treatment_products = DB::table("products_treatments")
                    ->where('products_treatments.tre_id', @$v2->id)
                    ->join('products', 'products.id', '=', 'products_treatments.pro_id')
                    ->select('products.brand_id')
                    ->groupBy('products.brand_id')->get();


                $brands = [];
                foreach ($treatment_products as $key => $value) {
                    $brands[] = $value->brand_id;
                }
                $array4['brands'] = $brands;
                $result4[] = $array4;
            }
        }
        // dd($result4);
        /************************************ Firm Wise Report End *************************/
        /************************************ ASM Wise Report Start *************************/
        $AsmResult = DB::table('jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search, $d_ids) {
                if (!empty($search)) {
                    if (isset($search['asm'])) {
                        if (!empty(trim($search['asm']))) {
                            $query->whereIn('j.dealer_id', $d_ids);
                        }
                    }
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('j.dealer_id', '=', $search['dealer']);
                        }
                    }
                    // if(isset($search['advisor'])){
                    //     if(!empty(trim($search['advisor']))){
                    //         $query->where('j.advisor_id','=',$search['advisor']);
                    //     }
                    // }
                    if (isset($search['department'])) {
                        if (!empty(trim($search['department']))) {
                            $query->where('j.department_id', '=', $search['department']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                            $query->whereDate('j.job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('j.job_date', $exp[1]);
                            $query->whereYear('j.job_date', $exp[0]);
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                } else {
                    $query->whereDate('j.job_date', '=', date("Y-m-d"));
                }
            })
            ->where('delete_job', 1)
            ->orderBy('j.job_date', 'ASC')
            ->get();

        $array3 = array();
        $result3 = array();
        $total_incentive = 0;
        foreach ($AsmResult as $k => $v) {
            $array3['job_date'] = $v->job_date;
            $array3['dealer_id'] = $v->dealer_id;
            $array3['job_card_no'] = $v->job_card_no;
            $array3['bill_no'] = $v->bill_no;
            $array3['regn_no'] = $v->regn_no;
            $array3['advisor_id'] = $v->advisor_id;
            $array3['model_id'] = $v->model_id;
            $array3['remarks'] = $v->remarks;
            // $array3['foc_options'] = $v->foc_options;
            if (!empty($v->incentive)) {
                $v->incentive = $v->incentive;
            } else {
                $v->incentive = 0;
            }
            $total_incentive = $total_incentive + @$v->incentive;
            $treatments = json_decode($v->treatments);

            foreach ($treatments as $v1) {
                $array3['labour_code'] = $v1->labour_code;
                $array3['job_type'] = @$v1->job_type;
                $array3['treatment_name'] = $v1->treatment;
                $array3['customer_price'] = $v1->customer_price;
                $array3['actual_price'] = @$v1->actualPrice;
                $array3['difference_price'] = @$v1->difference;
                $array3['dealer_price'] = @$v1->dealer_price;
                $array3['incentive'] = @$v1->incentive;

                $array3['treatment_id'] = @$v1->id;

                // find all brands by treatment id 
                $treatment_products = DB::table("products_treatments")
                    ->where('products_treatments.tre_id', @$v1->id)
                    ->join('products', 'products.id', '=', 'products_treatments.pro_id')
                    ->select('products.brand_id')
                    ->groupBy('products.brand_id')->get();


                $brands = [];
                foreach ($treatment_products as $key => $value) {
                    $brands[] = $value->brand_id;
                }
                $array3['brands'] = $brands;
                $result3[] = $array3;
            }
        }
        /************************************ ASM Wise Report End *************************/
        /************************************ Dealer Wise Report Start *************************/
        $result = DB::table('jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search, $d_ids) {
                if (!empty($search)) {
                    if (isset($search['firm'])) {
                        if (!empty(trim($search['firm']))) {
                            $query->whereIn('j.dealer_id', $d_ids);
                        }
                    }
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('j.dealer_id', '=', $search['dealer']);
                        }
                    }
                    // if(isset($search['advisor'])){
                    //     if(!empty(trim($search['advisor']))){
                    //         $query->where('j.advisor_id','=',$search['advisor']);
                    //     }
                    // }
                    if (isset($search['department'])) {
                        if (!empty(trim($search['department']))) {
                            $query->where('j.department_id', '=', $search['department']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                            $query->whereDate('j.job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('j.job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('j.job_date', $exp[1]);
                            $query->whereYear('j.job_date', $exp[0]);
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                } else {
                    $query->whereDate('j.job_date', '=', date("Y-m-d"));
                }
            })
            ->where('delete_job', 1)
            ->orderBy('j.job_date', 'ASC')
            ->get();
        $array = array();
        $result1 = array();
        $total_incentive = 0;
        foreach ($result as $key => $value) {
            $array['job_date'] = $value->job_date;
            $array['job_card_no'] = $value->job_card_no;
            $array['bill_no'] = $value->bill_no;
            $array['regn_no'] = $value->regn_no;
            $array['advisor_id'] = $value->advisor_id;
            $array['model_id'] = $value->model_id;
            $array['remarks'] = $value->remarks;
            // $array['foc_options'] = $value->foc_options;
            if (!empty($value->incentive)) {
                $value->incentive = $value->incentive;
            } else {
                $value->incentive = 0;
            }
            $total_incentive = $total_incentive + @$value->incentive;
            $decoded = json_decode($value->treatments);

            foreach ($decoded as $val) {
                $array['labour_code'] = $val->labour_code;
                $array['job_type'] = @$val->job_type;
                $array['treatment_name'] = $val->treatment;
                $array['customer_price'] = $val->customer_price;
                $array['actual_price'] = @$val->actualPrice;
                $array['difference_price'] = @$val->difference;
                $array['dealer_price'] = @$val->dealer_price;
                $array['incentive'] = @$val->incentive;

                $array['treatment_id'] = @$val->id;

                // find all brands by treatment id 
                $treatment_products = DB::table("products_treatments")
                    ->where('products_treatments.tre_id', @$val->id)
                    ->join('products', 'products.id', '=', 'products_treatments.pro_id')
                    ->select('products.brand_id')
                    ->groupBy('products.brand_id')->get();


                $brands = [];
                foreach ($treatment_products as $key => $value) {
                    $brands[] = $value->brand_id;
                }
                $array['brands'] = $brands;
                $result1[] = $array;
            }
        }
        /************************************ Dealer Wise Report End ****************************/

        /************************************ Advisor Wise Report Start *************************/
        $data = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(actual_price) as vas_actual_price, SUM(difference_price) as vas_difference, SUM(hvt_value) as hvt_customer_price,SUM(hvt_value) as hvt_actual_price,  SUM(incentive) as vas_incentive, advisor_id, job_date'))
            ->where(function ($query) use ($search, $d_ids) {
                if (!empty($search)) {
                    if (isset($search['firm'])) {
                        if (!empty(trim($search['firm']))) {
                            $query->whereIn('dealer_id', $d_ids);
                        }
                    }
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('dealer_id', '=', $search['dealer']);
                        }
                    }
                    // if(isset($search['advisor'])){
                    //     if(!empty(trim($search['advisor']))){
                    //         $query->where('advisor_id','=',$search['advisor']);
                    //     }
                    // }
                    if (isset($search['department'])) {
                        if (!empty(trim($search['department']))) {
                            $query->where('department_id', '=', $search['department']);
                        }
                    }
                    if (isset($search['from']) && isset($search['to'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('job_date', '>=', $search['from']);
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['to'])) {
                        if (!empty(trim($search['to']))) {
                            $query->whereDate('job_date', '<=', $search['to']);
                        }
                    } elseif (isset($search['from'])) {
                        if (!empty(trim($search['from']))) {
                            $query->whereDate('job_date', '>=', $search['from']);
                        }
                    } elseif (isset($search['month1'])) {
                        if (!empty(trim($search['month1']))) {
                            $exp = explode('-', $search['month1']);
                            $query->whereMonth('job_date', $exp[1]);
                            $query->whereYear('job_date', $exp[0]);
                        }
                    } else {
                        $query->whereDate('job_date', '=', date("Y-m-d"));
                    }
                } else {
                    $query->whereDate('job_date', '=', date("Y-m-d"));
                }
            })
            ->where('delete_job', 1)
            ->groupBy('advisor_id')
            ->get();

        $advisors = array();
        $i = $mtd_total = 0;
        if (count($data) > 0) {
            foreach ($data as $value) {
                $hvt_incentive = 0;
                $decoded_jobs = explode(',', $value->job_id);
                foreach ($decoded_jobs as $key => $val) {
                    $customer_price = $incentive = 0;
                    $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                    $decoded_treatments = json_decode(@$treat->treatments);
                    if (!empty($decoded_treatments)) {
                        foreach ($decoded_treatments as $key => $val1) {
                            if (@$val1->job_type == 5) {
                                $customer_price = $customer_price + $val1->customer_price;
                                if (!empty($val1->incentive)) {
                                    $val1->incentive = $val1->incentive;
                                } else {
                                    $val1->incentive = 0;
                                }
                                $incentive = $incentive + $val1->incentive;
                            } else {
                                $customer_price = $customer_price + 0;
                                $incentive = $incentive + 0;
                            }
                            if ($val1->treatment_type == 1) {
                                $hvt_incentive = $hvt_incentive + $val1->incentive;
                            }
                        }
                    }
                }

                $advisor['advisor_id'] = $value->advisor_id;
                $advisor['vas_customer_price'] = $value->vas_customer_price;
                // $advisor['vas_incentive'] = $value->vas_incentive;
                // $advisor['vas_customer_price'] = $customer_price;
                $advisor['vas_incentive'] = $incentive;
                $advisor['vas_actual_price'] = $value->vas_actual_price;
                $advisor['vas_difference'] = $value->vas_difference;
                $advisor['hvt_customer_price'] = $value->hvt_customer_price;
                $advisor['hvt_actual_price'] = $value->hvt_actual_price;
                $advisor['hvt_incentive'] = @$hvt_incentive;
                $advisors[] = $advisor;

                @$total_service = DB::table('jobs_by_date')
                    ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                    ->where(function ($query) use ($search, $first_day, $today, $value) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['from']) && isset($search['to'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_added_date', '>=', $search['from']);
                                    $query->whereDate('job_added_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['to'])) {
                                if (!empty(trim($search['to']))) {
                                    $query->whereDate('job_added_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['from'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_added_date', '>=', $search['from']);
                                }
                            } elseif (isset($search['month1'])) {
                                if (!empty(trim($search['month1']))) {
                                    $exp = explode('-', $search['month1']);
                                    $query->whereMonth('job_added_date', $exp[1]);
                                    $query->whereYear('job_added_date', $exp[0]);
                                }
                            }
                        } else {
                            $query->whereDate('job_added_date', '=', @$value->job_date);
                        }
                    })
                    ->first();

                // dd($total_service);

                @$total_jobs = DB::table('jobs')
                    ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(actual_price) as mtd_actual_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                    //  ->where('foc_options',5)
                    ->where(function ($query) use ($search, $first_day, $today, $value) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            // if(isset($search['advisor'])){
                            //     if(!empty(trim($search['advisor']))){
                            //         $query->where('advisor_id','=',$search['advisor']);
                            //     }
                            // }
                            if (isset($search['department'])) {
                                if (!empty(trim($search['department']))) {
                                    $query->where('department_id', '=', $search['department']);
                                }
                            }
                            if (isset($search['from']) && isset($search['to'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_date', '>=', $search['from']);
                                    $query->whereDate('job_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['to'])) {
                                if (!empty(trim($search['to']))) {
                                    $query->whereDate('job_date', '<=', $search['to']);
                                }
                            } elseif (isset($search['from'])) {
                                if (!empty(trim($search['from']))) {
                                    $query->whereDate('job_date', '>=', $search['from']);
                                }
                            } elseif (isset($search['month1'])) {
                                if (!empty(trim($search['month1']))) {
                                    $exp = explode('-', $search['month1']);
                                    $query->whereMonth('job_date', $exp[1]);
                                    $query->whereYear('job_date', $exp[0]);
                                }
                            }
                        } else {
                            $query->whereDate('job_date', '=', @$value->job_date);
                        }
                    })
                    ->where('delete_job', 1)
                    ->first();

                $total_job_array = array(
                    'mtd_total' => @$total_service->mtd_total,
                    'mtd_vas_value' => @$total_jobs->mtd_vas_value,
                    'mtd_actual_value' => @$total_jobs->mtd_actual_value,
                    'mtd_vas_total' => @$total_jobs->mtd_vas_total,
                    'mtd_hvt_value' => @$total_jobs->mtd_hvt_value,
                    'mtd_hvt_total' => @$total_jobs->mtd_hvt_total,
                );
                $i++;
            }
        }
        /************************************ Advisor Wise Report End *************************/

        Session::put('oldReport', $type);

        // brand list start
        $brands = DB::table("product_brands")->where('status', 1)->get();
        // brand list end
        // dd($result4);
        if (!empty(request()->brand)) {


            // filter by brand id 
            $a =  array_filter($result1, function ($value) {
                return in_array(request()->brand, $value['brands']);
            });
            $b =  array_filter($result3, function ($value) {
                return in_array(request()->brand, $value['brands']);
            });
            $c =  array_filter($result4, function ($value) {
                return in_array(request()->brand, $value['brands']);
            });

            $result1 = $a;
            $result3 = $b;
            $result4 = $c;
        }

        // dd($result4);
        return view('admin.dailyReport', [
            'result' => $result1,
            'result3' => $result3,
            'result4' => $result4,
            'brands' => $brands,
            'total_incentive' => $total_incentive,
            'advisors' => $advisors,
            'allAdvisors' => $allAdvisors,
            'oldAdvisor' => @$search['advisor'],
            'total_job_array' => @$total_job_array,
            'dealers' => $dealers,
            'firms' => $firms,
            'oldFirm' => @$search['firm'],
            'asms' => $asms,
            'oldAsm' => @$search['asm'],
            'oems' => $oems,
            'oldOem' => @$search['oem'],
            'groups' => $groups,
            'oldGroup' => @$search['group'],
            'oldFromDate' => @$search['from'],
            'oldToDate' => @$search['to'],
            'oldDealer' => @$search['dealer'],
            'oldSelectMonth' => @$search['month1'],
            'oldReport' => @$type,
            'departments' => $departments,
            'oldDepartment' => @$search['department'],
        ]);
    }

    // array filter barand daily repot 
    // public function brandFilter()
    // {
    //     return in_array(request()->brand, $value['brands']);
    // }

    // View Mis report
    public function misReport(Request $request)
    {
        $search = $request->all();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');

        if (!empty($search['firm']) && empty($search['asm'])) {
            $dealers = User::where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = array();
            foreach ($dealers as $k => $v) {
                $d_ids[] = $dealers[$k]->id;
            }
        } else if (!empty($search['firm']) && !empty($search['asm'])) {
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($search['asm'], $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
        } else {
            $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = [];
        }
        /************************************ MIS Report Start *************************/
        // $users = DB::table('users')
        //           ->select('id')
        //           ->where(['users.role'=>2, 'status'=>1])
        //           ->orderBy('users.name','ASC')
        //           ->get();

        $firms = DB::table('firms')->get();
        $oems = DB::table('oems')->where('status', 1)->get();
        $groups = DB::table('groups')->where('status', 1)->get();
        $asms = DB::table('users')->where(["firm_id" => @$search['firm'], "role" => 5, 'status' => 1])->get();
        $mist = array();

        foreach ($dealers as $key => $value) {
            $mis = DB::table('jobs')
                // ->select(DB::raw('jobs.id as job_id,SUM(jobs.treatment_total) as mtd_total,SUM(jobs.customer_price) as customer_price,SUM(jobs.actual_price) as actual_price,SUM(jobs.hvt_total) as hvt_total, SUM(jobs.dealer_price) as dealer_price, SUM(jobs.incentive) as incentive,SUM(jobs.hvt_total) as mtd_hvt, SUM(jobs.hvt_value) as mtd_hvt_value,SUM(jobs.vas_total) as mtd_vas, SUM(jobs.vas_value) as mtd_vas_value, jobs.dealer_id, jobs.foc_options,jobs.treatments'))
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from1']) && isset($search['to1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('jobs.job_date', '>=', $search['from1']);
                            $query->whereDate('jobs.job_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['to1'])) {
                        if (!empty(trim($search['to1']))) {
                            $query->whereDate('jobs.job_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['from1'])) {
                        if (!empty(trim($search['from1']))) {

                            $query->whereDate('jobs.job_date', '>=', $search['from1']);
                        }
                    } elseif (!empty($search['month'])) {
                        $exp = explode('-', $search['month']);
                        $query->whereMonth('jobs.job_date', $exp[1]);
                        $query->whereYear('jobs.job_date', $exp[0]);
                    } else {
                        $query->whereDate('jobs.job_date', '>=', $first_day);
                        $query->whereDate('jobs.job_date', '<=', $today);
                    }
                })
                ->where('jobs.dealer_id', $value->id)
                ->where('jobs.delete_job', 1)
                // ->where('jobs.foc_options',5)
                // ->groupBy('jobs.dealer_id')
                ->get();

            $treatment_total = $hvt_incentive = $customer_price = $actual_price = $incentive = $hvt_total = $hvt_value = $vas_total = $vas_value = $dealer_price = 0;
            $array = array();
            if (count($mis) == 0) {
                $data = new \stdClass();
                $data->dealer_id = $value->id;
                $data->treatment_total = 0;
                $data->id = 0;
                $data->customer_price = 0;
                $data->actual_price = 0;
                $data->dealer_price = 0;
                $data->incentive = 0;
                $data->hvt_total = 0;
                $data->mtd_hvt = 0;
                $data->hvt_value = 0;
                $data->vas_total = 0;
                $data->vas_value = 0;
                $data->hvt_incentive = 0;
                $mis[] = $data;
            }
            foreach ($mis as $key1 => $value1) {
                $treatment_total += $value1->treatment_total;
                if (!empty($value1->incentive) || !empty($value1->dealer_price)) {
                    $value1->incentive = $value1->incentive;
                    $value1->dealer_price = $value1->dealer_price;
                } else {
                    $value1->incentive = 0;
                    $value1->dealer_price = 0;
                }
                $incentive       += $value1->incentive;
                $actual_price    += (int)$value1->actual_price;
                $hvt_total       += $value1->hvt_total;
                $hvt_value       += $value1->hvt_value;
                $vas_total       += $value1->vas_total;
                $vas_value       += $value1->vas_value;
                $dealer_price    += $value1->dealer_price;

                if ($value1->id != 0) {
                    $decoded_jobs = explode(',', $value1->id);
                    foreach ($decoded_jobs as $key => $val) {
                        $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                        $decoded_treatments = json_decode(@$treat->treatments);
                        if (!empty($decoded_treatments)) {
                            foreach ($decoded_treatments as $key => $val1) {
                                if (@$val1->job_type == 5) {
                                    $customer_price = $customer_price + $val1->customer_price;
                                    // $incentive = $incentive + $val1->incentive;
                                } else {
                                    $customer_price = $customer_price + 0;
                                    // $incentive = $incentive + 0;
                                }
                                if ($val1->treatment_type == 1) {
                                    $hvt_incentive = $hvt_incentive + $val1->incentive;
                                }
                            }
                        }
                    }
                }
                $array['mtd_total'] = $treatment_total;
                $array['customer_price'] = $customer_price;
                $array['actual_price'] = $actual_price;
                $array['dealer_price'] = $dealer_price;
                $array['incentive'] = $incentive;
                $array['hvt_total'] = $hvt_total;
                $array['mtd_hvt'] = $hvt_total;
                $array['mtd_hvt_value'] = $hvt_value;
                $array['mtd_vas'] = $vas_total;
                $array['mtd_vas_value'] = $vas_value;
                $array['hvt_incentive'] = $hvt_incentive;
                $array['dealer_id'] = @$value1->dealer_id;
            }
            $mist[] = $array;
        }
        foreach ($mist as $key => $value2) {
            $total = DB::table('jobs_by_date')
                ->select(DB::raw('SUM(total_jobs) as total_jobs,dealer_id'))
                ->where('dealer_id', @$value2['dealer_id'])
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from1']) && isset($search['to1'])) {
                        if (!empty(trim($search['from1']))) {
                            $query->whereDate('job_added_date', '>=', $search['from1']);
                            $query->whereDate('job_added_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['to1'])) {
                        if (!empty(trim($search['to1']))) {
                            $query->whereDate('job_added_date', '<=', $search['to1']);
                        }
                    } elseif (isset($search['from1'])) {
                        if (!empty(trim($search['from1']))) {

                            $query->whereDate('job_added_date', '>=', $search['from1']);
                        }
                    } elseif (!empty($search['month'])) {
                        $exp = explode('-', $search['month']);
                        $query->whereMonth('job_added_date', $exp[1]);
                        $query->whereYear('job_added_date', $exp[0]);
                    } else {
                        $query->whereDate('job_added_date', '>=', $first_day);
                        $query->whereDate('job_added_date', '<=', $today);
                    }
                })
                ->groupBy('dealer_id')
                ->first();
            if (!empty($total->total_jobs)) {
                $mist[$key]['service_load'] = $total->total_jobs;
            } else {
                $mist[$key]['service_load'] = 0;
            }
        }
        /************************************ MIS Report End *************************/
        return view('admin.misReport', [
            'mis' => $mist,
            'firms' => $firms,
            'oldFirm' => @$search['firm'],
            'asms' => $asms,
            'oldAsm' => @$search['asm'],
            'oems' => $oems,
            'groups' => $groups,
            'oldFromDate1' => @$search['from1'],
            'oldToDate1' => @$search['to1'],
            'oldMonth' => @$search['month'],
        ]);
    }

    // View DCF report
    public function dcfReport(Request $request)
    {

        $search = $request->all();
        //dd($search);
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['report_type'])) {
            $type = $search['report_type'];
        } else {
            $type = 'dealer';
        }
        $dealers = User::where('role', 2)->where('id', '!=', 58)->select('id', 'name')->orderBy('name', 'ASC')->get();

        /************************************ DCF Report Start *******************************/
        if (@$search['selectMonth']) {
            $monthYear = explode('-', $search['selectMonth']);
            $year = $monthYear[0];
            $month =  $monthYear[1];
            $model = array();
            $dealers = DB::table('users')->where('role', 2)->where('id', '!=', 58)->orderBy('name', 'ASC')->get();
            if (!empty($dealers) && @count($dealers) > 0) {
                return Excel::create('DCF_' . date("d-M-Y"), function ($excel) use ($dealers, $search, $month, $year) {
                    foreach ($dealers as $dealerValue) {
                        $excel->sheet(get_name($dealerValue->id), function ($sheet) use ($dealerValue, $search, $month, $year) {
                            $getModels = DB::table('models as m')
                                ->select(DB::raw('group_concat(m.id) as model_id, m.model_size'))
                                // ->where('m.dealer_id',$dealerValue->id)
                                ->groupBy('m.model_size')
                                ->orderBy('m.model_size', 'ASC')
                                ->get();
                            foreach ($getModels as $value) {
                                $model_id = explode(',', $value->model_id);
                                $jobs = DB::table('jobs')
                                    ->select('id', 'model_id', 'treatments')
                                    ->where('dealer_id', $dealerValue->id)
                                    ->whereIn('model_id', $model_id)
                                    ->whereMonth('job_date', $month)
                                    ->whereYear('job_date', $year)
                                    ->where('delete_job', 1)
                                    ->get();

                                $getTreatments = DB::table('treatments')
                                    ->select('id', 'treatment', 'labour_code')
                                    // ->where('dealer_id',$dealerValue->id)
                                    ->whereIn('model_id', $model_id)
                                    ->orderBy('treatment', 'ASC')
                                    ->get();

                                $tcounts = array();
                                $i = 0;
                                foreach ($getTreatments as $key => $tvalue) {
                                    $tcounts[$i]['id'] = $tvalue->id;
                                    $tcounts[$i]['treatment'] = $tvalue->treatment;
                                    $tcounts[$i]['labour_code'] = $tvalue->labour_code;
                                    $tcounts[$i]['total'] = 0;
                                    $tcounts[$i]['customer_price'] = 0;
                                    foreach ($jobs as $jvalue) {
                                        if (@$jvalue->treatments) {
                                            $t = json_decode($jvalue->treatments);
                                            if (@$t) {
                                                foreach ($t as $jtvalue) {
                                                    if ($jtvalue->id == $tvalue->id) {
                                                        $tcounts[$i]['total']++;
                                                        $tcounts[$i]['customer_price'] = $tcounts[$i]['customer_price'] + $jtvalue->customer_price;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $i++;
                                }
                                $final_treatments = array();
                                $tnames = array();
                                $j = 0;
                                foreach ($tcounts as $valuet) {
                                    if (in_array($valuet['treatment'], $tnames)) {
                                        $a = array_search($valuet['treatment'], $tnames);
                                        $final_treatments[$a]['total'] = $final_treatments[$a]['total'] + $valuet['total'];
                                        $final_treatments[$a]['customer_price'] = $final_treatments[$a]['customer_price'] + $valuet['customer_price'];
                                    } else {
                                        $tnames[$j] = $valuet['treatment'];
                                        $final_treatments[$j]['labour_code'] = $valuet['labour_code'];
                                        $final_treatments[$j]['treatment'] = $valuet['treatment'];
                                        $final_treatments[$j]['total'] = $valuet['total'];
                                        $final_treatments[$j]['customer_price'] = $valuet['customer_price'];
                                        $j++;
                                    }
                                }
                                $sheet->setCellValue('A1', 'Small');
                                $sheet->mergeCells("A1:D1");
                                $sheet->setCellValue('A2', 'Treatment');
                                $sheet->setCellValue('B2', 'Labour_Code');
                                $sheet->setCellValue('C2', 'Number');
                                $sheet->setCellValue('D2', 'Value');
                                $sheet->setCellValue('E1', 'Medium');
                                $sheet->mergeCells("E1:H1");
                                $sheet->setCellValue('E2', 'Treatment');
                                $sheet->setCellValue('F2', 'Labour_Code');
                                $sheet->setCellValue('G2', 'Number');
                                $sheet->setCellValue('H2', 'Value');
                                $sheet->setCellValue('I1', 'Large');
                                $sheet->mergeCells("I1:L1");
                                $sheet->setCellValue('I2', 'Treatment');
                                $sheet->setCellValue('J2', 'Labour_Code');
                                $sheet->setCellValue('K2', 'Number');
                                $sheet->setCellValue('L2', 'Value');
                                // $sheet->setCellValue('M1','Total');
                                // $sheet->mergeCells("M1:N2");
                                if ($value->model_size == 3) {
                                    $i = 3;
                                    $in = 'A';
                                    $no = 0;
                                    $val = 0;
                                    foreach ($final_treatments as  $value1) {
                                        $sheet->setCellValue($in . $i, $value1['treatment']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value1['labour_code']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value1['total']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value1['customer_price']);
                                        $no = $no + $value1['total'];
                                        $val = $val + $value1['customer_price'];
                                        $i++;
                                        $in = 'A';
                                    }
                                    $sheet->setCellValue('A' . $i, 'Total');
                                    $sheet->setCellValue('C' . $i, $no);
                                    $sheet->setCellValue('D' . $i, $val);
                                }
                                if ($value->model_size == 2) {
                                    $i = 3;
                                    $in = 'E';
                                    $no = 0;
                                    $val = 0;
                                    foreach ($final_treatments as  $value2) {
                                        $sheet->setCellValue($in . $i, $value2['treatment']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value2['labour_code']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value2['total']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value2['customer_price']);
                                        $no = $no + $value2['total'];
                                        $val = $val + $value2['customer_price'];
                                        $i++;
                                        $in = 'E';
                                    }
                                    $sheet->setCellValue('G' . $i, $no);
                                    $sheet->setCellValue('H' . $i, $val);
                                }
                                if ($value->model_size == 1) {
                                    $i = 3;
                                    $in = 'I';
                                    $no = 0;
                                    $val = 0;
                                    foreach ($final_treatments as $value3) {
                                        $sheet->setCellValue($in . $i, $value3['treatment']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value3['labour_code']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value3['total']);
                                        $in++;
                                        $sheet->setCellValue($in . $i, $value3['customer_price']);
                                        $no = $no + $value3['total'];
                                        $val = $val + $value3['customer_price'];
                                        $i++;
                                        $in = 'I';
                                    }
                                    $sheet->setCellValue('K' . $i, $no);
                                    $sheet->setCellValue('L' . $i, $val);
                                }
                            }
                        });
                    }
                })->export('xlsx');
            } else {
                Session::flash('error', 'Data Not Available.');
                return redirect()->back();
            }
        }

        /************************************ DCF Report End *******************************/


        Session::put('oldReport', $type);
        return view('admin.dcf_report', [
            //'result' => $result1,
            //'total_incentive' => $total_incentive,
            //'advisors' => $advisors,
            'total_job_array' => @$total_job_array,
            // 'mis' => $mist,
            //'dealers' => $dealers,
            'oldFromDate' => @$search['from'],
            'oldToDate' => @$search['to'],
            'oldFromDate1' => @$search['from1'],
            'oldToDate1' => @$search['to1'],
            'oldDealer' => @$search['dealer'],
            'oldDealers' => @$search['dealers'],
            'oldMonth' => @$search['month'],
            'oldSelectMonth' => @$search['month1'],
            'oldReport' => @$type,
            'tabName' => @$search['tabName'],
        ]);
    }


    // Download Report
    public function downloadReport(Request $request)
    {
        $search = $request->all();
        if ($search['report'] == 'dealer') {
            $result = DB::table('jobs as j')
                ->select('j.*')
                // ->where('j.foc_options',5)
                ->where(function ($query) use ($search) {
                    if (!empty($search)) {
                        if (isset($search['dealer1'])) {
                            if (!empty(trim($search['dealer1']))) {
                                $query->where('j.dealer_id', '=', $search['dealer1']);
                            }
                        }
                        // if(isset($search['advisor1'])){
                        //     if(!empty(trim($search['advisor1']))){
                        //         $query->where('j.advisor_id','=',$search['advisor1']);  
                        //     }
                        // }
                        if (isset($search['department1'])) {
                            if (!empty(trim($search['department1']))) {
                                $query->where('j.department_id', '=', $search['department1']);
                            }
                        }
                        if (isset($search['from1']) && isset($search['to1'])) {
                            if (!empty(trim($search['from1'])) && !empty(trim($search['to1']))) {
                                $query->whereDate('j.job_date', '>=', $search['from1']);
                                $query->whereDate('j.job_date', '<=', $search['to1']);
                            }
                        } elseif (isset($search['to1'])) {
                            if (!empty(trim($search['to1']))) {
                                $query->whereDate('job_date', '<=', $search['to1']);
                            }
                        } elseif (isset($search['from1'])) {
                            if (!empty(trim($search['from1']))) {
                                $query->whereDate('j.job_date', '>=', $search['from1']);
                            }
                        } elseif (isset($search['month3'])) {
                            if (!empty(trim($search['month3']))) {
                                $exp = explode('-', $search['month3']);
                                $query->whereMonth('j.job_date', $exp[1]);
                                $query->whereYear('j.job_date', $exp[0]);
                            }
                        } else {
                            $query->whereDate('j.job_date', '=', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                })
                ->where('j.delete_job', 1)
                ->orderBy('j.job_date', 'ASC')
                ->get();

            $array = array();
            $result1 = array();
            $customer_price = $actual_price = $difference_price = $dealer_price = $incentive = 0;
            return Excel::create('Dealer_' . date("d-M-Y"), function ($excel) use ($result, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                $excel->sheet('sheet', function ($sheet) use ($result, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                    foreach ($result as $key => $value) {
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            if ($val->job_type == 5) {
                                $customer_price = $customer_price + round($val->customer_price);
                                $dealer_price = $dealer_price + round(@$val->dealer_price);
                                $incentive = $incentive + round(@$val->incentive);
                                $actual_price = $actual_price + round(@$val->actualPrice);
                                $difference_price = $difference_price + round(@$val->difference);
                            }
                        }
                    }
                    $sheet->setBorder('P1:T2');
                    $sheet->cells('P1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('Q1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('R1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('S1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('T1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->setCellValue('P1', 'Customer_Price');
                    $sheet->setCellValue('P2', $customer_price);
                    $sheet->setCellValue('Q1', 'Dealer_Price');
                    $sheet->setCellValue('Q2', $dealer_price);
                    $sheet->setCellValue('R1', 'Incentive');
                    $sheet->setCellValue('R2', $incentive);
                    $sheet->setCellValue('S1', 'Actual_Price');
                    $sheet->setCellValue('S2', $actual_price);
                    $sheet->setCellValue('T1', 'Difference');
                    $sheet->setCellValue('T2', $difference_price);
                    foreach ($result as $key => $value) {
                        $array['Job_Date'] = date("d-M-Y", strtotime($value->job_date));
                        $array['Job_Card_No'] = $value->job_card_no;
                        $array['Bill_No'] = $value->bill_no;
                        $array['Regn_No'] = $value->regn_no;
                        $array['Advisor'] = get_advisor_name($value->advisor_id);
                        $array['Model'] = get_model_name($value->model_id);
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            $array['Labour_Code'] = $val->labour_code;
                            $array['Treatment'] = $val->treatment;
                            $array['Customer_Price'] = round($val->customer_price);
                            $array['Dealer_Price'] = round(@$val->dealer_price);
                            $array['Incentive'] = round(@$val->incentive);
                            $array['Actual_Price'] = round(@$val->actualPrice);
                            $array['Difference_Price'] = round(@$val->difference);
                            $array['Remark'] = $value->remarks;
                            $result1[] = $array;
                        }
                    }
                    $sheet->fromArray(@$result1);
                });
            })->export('xlsx');
            /************************************ Download Dealer Wise Report *************************/
        } elseif ($search['report'] == 'firm') {
            if (!empty($search['firm_id']) && empty($search['firm_asm_id'])) {
                $dealers = User::where(['role' => 2, 'firm_id' => $search['firm_id'], 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
                $d_ids = array();
                foreach ($dealers as $k => $v) {
                    $d_ids[] = $dealers[$k]->id;
                }
            } elseif (!empty($search['firm_id']) && !empty($search['firm_asm_id'])) {
                $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
                $dealers = array();
                $d_ids = array();
                foreach ($dealer_ids as $i => $j) {
                    $report_ids = explode(",", $j->reporting_authority);
                    if (in_array($search['firm_asm_id'], $report_ids)) {
                        $dealers[] = $dealer_ids[$i];
                        $d_ids[] = $dealer_ids[$i]->id;
                    }
                }
            } else {
                $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
                $d_ids = [];
            }

            $FirmResult = DB::table('jobs as j')
                ->select('j.*')
                // ->where('j.foc_options',5)
                ->where(function ($query) use ($search, $d_ids) {
                    if (!empty($search)) {
                        if (isset($search['firm_id'])) {
                            if (!empty(trim($search['firm_id']))) {
                                $query->whereIn('j.dealer_id', $d_ids);
                            }
                        }
                        if (isset($search['firm_dealer'])) {
                            if (!empty(trim($search['firm_dealer']))) {
                                $query->where('j.dealer_id', '=', $search['firm_dealer']);
                            }
                        }
                        // if(isset($search['firm_advisor'])){
                        //     if(!empty(trim($search['firm_advisor']))){
                        //         $query->where('j.advisor_id','=',$search['firm_advisor']);              
                        //     }
                        // }
                        if (isset($search['firm_department'])) {
                            if (!empty(trim($search['firm_department']))) {
                                $query->where('j.department_id', '=', $search['firm_department']);
                            }
                        }
                        if (isset($search['firm_from']) && isset($search['firm_to'])) {
                            if (!empty(trim($search['firm_from'])) && !empty(trim($search['firm_to']))) {
                                $query->whereDate('j.job_date', '>=', $search['firm_from']);
                                $query->whereDate('j.job_date', '<=', $search['firm_to']);
                            }
                        } elseif (isset($search['firm_to'])) {
                            if (!empty(trim($search['firm_to']))) {
                                $query->whereDate('job_date', '<=', $search['firm_to']);
                            }
                        } elseif (isset($search['firm_from'])) {
                            if (!empty(trim($search['firm_from']))) {
                                $query->whereDate('j.job_date', '>=', $search['firm_from']);
                            }
                        } elseif (isset($search['firm_month'])) {
                            if (!empty(trim($search['firm_month']))) {
                                $exp = explode('-', $search['firm_month']);
                                $query->whereMonth('j.job_date', $exp[1]);
                                $query->whereYear('j.job_date', $exp[0]);
                            }
                        } else {
                            $query->whereDate('j.job_date', '=', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                })
                ->where('j.delete_job', 1)
                ->orderBy('j.job_date', 'ASC')
                ->get();

            $array = array();
            $result2 = array();
            $customer_price = $actual_price = $difference_price = $dealer_price = $incentive = 0;
            return Excel::create('Firm_' . date("d-M-Y"), function ($excel) use ($FirmResult, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                $excel->sheet('sheet', function ($sheet) use ($FirmResult, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                    foreach ($FirmResult as $key => $value) {
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            if ($val->job_type == 5) {
                                $customer_price = $customer_price + round($val->customer_price);
                                $dealer_price = $dealer_price + round($val->dealer_price);
                                $incentive = $incentive + round($val->incentive);
                                $actual_price = $actual_price + round(@$val->actualPrice);
                                $difference_price = $difference_price + round(@$val->difference);
                            }
                        }
                    }
                    $sheet->setBorder('P1:T2');
                    $sheet->cells('P1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('Q1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('R1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('S1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('T1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->setCellValue('P1', 'Customer_Price');
                    $sheet->setCellValue('P2', $customer_price);
                    $sheet->setCellValue('Q1', 'Dealer_Price');
                    $sheet->setCellValue('Q2', $dealer_price);
                    $sheet->setCellValue('R1', 'Incentive');
                    $sheet->setCellValue('R2', $incentive);
                    $sheet->setCellValue('S1', 'Actual_Price');
                    $sheet->setCellValue('S2', $actual_price);
                    $sheet->setCellValue('T1', 'Difference');
                    $sheet->setCellValue('T2', $difference_price);
                    foreach ($FirmResult as $key => $value) {
                        $array['Job_Date'] = date("d-M-Y", strtotime($value->job_date));
                        $array['Dealer_Name'] = get_dealer_name($value->dealer_id);
                        $array['Job_Card_No'] = $value->job_card_no;
                        $array['Bill_No'] = $value->bill_no;
                        $array['Regn_No'] = $value->regn_no;
                        $array['Advisor'] = get_advisor_name($value->advisor_id);
                        $array['Model'] = get_model_name($value->model_id);
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            $array['Labour_Code'] = $val->labour_code;
                            $array['Treatment'] = $val->treatment;
                            $array['Customer_Price'] = round($val->customer_price);
                            $array['Dealer_Price'] = round($val->dealer_price);
                            $array['Incentive'] = round($val->incentive);
                            $array['Actual_Price'] = round(@$val->actualPrice);
                            $array['Difference_Price'] = round(@$val->difference);
                            $array['Remark'] = $value->remarks;
                            $result2[] = $array;
                        }
                    }
                    $sheet->fromArray(@$result2);
                });
            })->export('xlsx');
            /************************************ Download Firm Wise Report *************************/
        } elseif ($search['report'] == 'asm') {
            if (!empty($search['asm_id'])) {
                $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
                $dealers = array();
                $d_ids = array();
                foreach ($dealer_ids as $i => $j) {
                    $report_ids = explode(",", $j->reporting_authority);
                    if (in_array($search['asm_id'], $report_ids)) {
                        $dealers[] = $dealer_ids[$i];
                        $d_ids[] = $dealer_ids[$i]->id;
                    }
                }
            } else {
                $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
                $d_ids = [];
            }
            $AsmResult = DB::table('jobs as j')
                ->select('j.*')
                // ->where('j.foc_options',5)
                ->where(function ($query) use ($search, $d_ids) {
                    if (!empty($search)) {
                        if (isset($search['asm_id'])) {
                            if (!empty(trim($search['asm_id']))) {
                                $query->whereIn('j.dealer_id', $d_ids);
                            }
                        }
                        if (isset($search['asm_dealer'])) {
                            if (!empty(trim($search['asm_dealer']))) {
                                $query->where('j.dealer_id', '=', $search['asm_dealer']);
                            }
                        }
                        // if(isset($search['asm_advisor'])){
                        //     if(!empty(trim($search['asm_advisor']))){
                        //         $query->where('j.advisor_id','=',$search['asm_advisor']);              
                        //     }
                        // }
                        if (isset($search['asm_department'])) {
                            if (!empty(trim($search['asm_department']))) {
                                $query->where('j.department_id', '=', $search['asm_department']);
                            }
                        }
                        if (isset($search['asm_from']) && isset($search['asm_to'])) {
                            if (!empty(trim($search['asm_from'])) && !empty(trim($search['asm_to']))) {
                                $query->whereDate('j.job_date', '>=', $search['asm_from']);
                                $query->whereDate('j.job_date', '<=', $search['asm_to']);
                            }
                        } elseif (isset($search['asm_to'])) {
                            if (!empty(trim($search['asm_to']))) {
                                $query->whereDate('job_date', '<=', $search['asm_to']);
                            }
                        } elseif (isset($search['asm_from'])) {
                            if (!empty(trim($search['asm_from']))) {
                                $query->whereDate('j.job_date', '>=', $search['asm_from']);
                            }
                        } elseif (isset($search['asm_month'])) {
                            if (!empty(trim($search['asm_month']))) {
                                $exp = explode('-', $search['asm_month']);
                                $query->whereMonth('j.job_date', $exp[1]);
                                $query->whereYear('j.job_date', $exp[0]);
                            }
                        } else {
                            $query->whereDate('j.job_date', '=', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('j.job_date', '=', date("Y-m-d"));
                    }
                })
                ->where('j.delete_job', 1)
                ->orderBy('j.job_date', 'ASC')
                ->get();
            $array = array();
            $result3 = array();
            $customer_price = $actual_price = $difference_price = $dealer_price = $incentive = 0;
            return Excel::create('ASM_' . date("d-M-Y"), function ($excel) use ($AsmResult, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                $excel->sheet('sheet', function ($sheet) use ($AsmResult, $customer_price, $dealer_price, $incentive, $actual_price, $difference_price) {
                    foreach ($AsmResult as $key => $value) {
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            if ($val->job_type == 5) {
                                $customer_price = $customer_price + round($val->customer_price);
                                $dealer_price = $dealer_price + round($val->dealer_price);
                                $incentive = $incentive + round($val->incentive);
                                $actual_price = $actual_price + round(@$val->actualPrice);
                                $difference_price = $difference_price + round(@$val->difference);
                            }
                        }
                    }
                    $sheet->setBorder('P1:T2');
                    $sheet->cells('P1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('Q1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('R1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('S1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->cells('T1', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->setCellValue('P1', 'Customer_Price');
                    $sheet->setCellValue('P2', $customer_price);
                    $sheet->setCellValue('Q1', 'Dealer_Price');
                    $sheet->setCellValue('Q2', $dealer_price);
                    $sheet->setCellValue('R1', 'Incentive');
                    $sheet->setCellValue('R2', $incentive);
                    $sheet->setCellValue('S1', 'Actual_Price');
                    $sheet->setCellValue('S2', $actual_price);
                    $sheet->setCellValue('T1', 'Difference');
                    $sheet->setCellValue('T2', $difference_price);
                    foreach ($AsmResult as $key => $value) {
                        $array['Job_Date'] = date("d-M-Y", strtotime($value->job_date));
                        $array['Dealer_Name'] = get_dealer_name($value->dealer_id);
                        $array['Job_Card_No'] = $value->job_card_no;
                        $array['Bill_No'] = $value->bill_no;
                        $array['Regn_No'] = $value->regn_no;
                        $array['Advisor'] = get_advisor_name($value->advisor_id);
                        $array['Model'] = get_model_name($value->model_id);
                        $decoded = json_decode($value->treatments);
                        foreach ($decoded as $val) {
                            $array['Labour_Code'] = $val->labour_code;
                            $array['Treatment'] = $val->treatment;
                            $array['Customer_Price'] = round($val->customer_price);
                            $array['Dealer_Price'] = round($val->dealer_price);
                            $array['Incentive'] = round($val->incentive);
                            $array['Actual_Price'] = round(@$val->actualPrice);
                            $array['Difference_Price'] = round(@$val->difference);
                            $array['Remark'] = $value->remarks;
                            $result3[] = $array;
                        }
                    }
                    $sheet->fromArray(@$result3);
                });
            })->export('xlsx');
            /************************************ Download Dealer Wise Report *************************/
        } elseif ($search['report'] == 'advisor') {
            /************************************ Download Advisor Wise Report ***********************/
            $data = DB::table('jobs')
                // ->where('foc_options',5)
                ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(actual_price) as vas_actual_price, SUM(difference_price) as vas_difference, SUM(hvt_value) as hvt_customer_price,SUM(hvt_value) as hvt_actual_price,  SUM(incentive) as vas_incentive, advisor_id, job_date'))
                ->where(function ($query) use ($search) {
                    if (!empty($search)) {
                        if (isset($search['dealer2'])) {
                            if (!empty(trim($search['dealer2']))) {
                                $query->where('dealer_id', '=', $search['dealer2']);
                            }
                        }
                        // if(isset($search['advisor2'])){
                        //     if(!empty(trim($search['advisor2']))){
                        //        $query->where('advisor_id','=',$search['advisor2']);              
                        //     }
                        // }
                        if (isset($search['department2'])) {
                            if (!empty(trim($search['department2']))) {
                                $query->where('department_id', '=', $search['department2']);
                            }
                        }
                        if (isset($search['from2']) && isset($search['to2'])) {
                            if (!empty(trim($search['from2']))) {
                                $query->whereDate('job_date', '>=', $search['from2']);
                                $query->whereDate('job_date', '<=', $search['to2']);
                            }
                        } elseif (isset($search['to2'])) {
                            if (!empty(trim($search['to2']))) {
                                $query->whereDate('job_date', '<=', $search['to2']);
                            }
                        } elseif (isset($search['from2'])) {
                            if (!empty(trim($search['from2']))) {
                                $query->whereDate('job_date', '>=', $search['from2']);
                            }
                        } elseif (isset($search['month4'])) {
                            if (!empty(trim($search['month4']))) {
                                $exp = explode('-', $search['month4']);
                                $query->whereMonth('job_date', $exp[1]);
                                $query->whereYear('job_date', $exp[0]);
                            }
                        }
                    }
                })
                ->where('delete_job', 1)
                ->groupBy('advisor_id')
                ->get();
            $advisors = array();
            $i = $mtd_total = 0;
            if (count($data) > 0) {
                foreach ($data as $value) {
                    $hvt_incentive = 0;
                    $decoded_jobs = explode(',', $value->job_id);
                    foreach ($decoded_jobs as $key => $val) {
                        $customer_price = $incentive = 0;
                        $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                        $decoded_treatments = json_decode($treat->treatments);
                        foreach ($decoded_treatments as $key => $val1) {
                            if ($val1->job_type == 5) {
                                $customer_price = $customer_price + $val1->customer_price;
                                if (!empty($val1->incentive)) {
                                    $val1->incentive = $val1->incentive;
                                } else {
                                    $val1->incentive = 0;
                                }
                                $incentive = $incentive + $val1->incentive;
                            } else {
                                $customer_price = $customer_price + 0;
                                $incentive = $incentive + 0;
                            }
                            if ($val1->treatment_type == 1) {
                                $hvt_incentive = $hvt_incentive + $val1->incentive;
                            }
                        }
                    }
                    $advisor['advisor_id'] = $value->advisor_id;
                    $advisor['vas_customer_price'] = round($value->vas_customer_price);
                    // $advisor['vas_incentive'] = $value->vas_incentive;
                    // $advisor['vas_customer_price'] = round($customer_price);
                    $advisor['vas_incentive'] = round($incentive);
                    $advisor['vas_actual_price'] = round($value->vas_actual_price);
                    $advisor['vas_difference'] = round($value->vas_difference);
                    $advisor['hvt_customer_price'] = round($value->hvt_customer_price);
                    $advisor['hvt_actual_price'] = round($value->hvt_actual_price);
                    $advisor['hvt_incentive'] = round($hvt_incentive);
                    $advisors[] = $advisor;
                    @$total_service = DB::table('jobs_by_date')
                        ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                        ->where(function ($query) use ($search, $first_day, $today, $value) {
                            if (!empty($search)) {
                                if (isset($search['dealer2'])) {
                                    if (!empty(trim($search['dealer2']))) {
                                        $query->where('dealer_id', '=', $search['dealer2']);
                                    }
                                }
                                if (isset($search['from2']) && isset($search['to2'])) {
                                    if (!empty(trim($search['from2']))) {
                                        $query->whereDate('job_added_date', '>=', $search['from2']);
                                        $query->whereDate('job_added_date', '<=', $search['to2']);
                                    }
                                } elseif (isset($search['to2'])) {
                                    if (!empty(trim($search['to2']))) {
                                        $query->whereDate('job_added_date', '<=', $search['to2']);
                                    }
                                } elseif (isset($search['from2'])) {
                                    if (!empty(trim($search['from2']))) {
                                        $query->whereDate('job_added_date', '>=', $search['from2']);
                                    }
                                } elseif (isset($search['month4'])) {
                                    if (!empty(trim($search['month4']))) {
                                        $exp = explode('-', $search['month4']);
                                        $query->whereMonth('job_added_date', $exp[1]);
                                        $query->whereYear('job_added_date', $exp[0]);
                                    }
                                } else {
                                    //$query->whereDate('job_added_date','=',@$value->job_date);
                                }
                            } else {
                                $query->whereDate('job_added_date', '=', @$value->job_date);
                            }
                        })
                        ->first();
                    @$total_jobs = DB::table('jobs')
                        // ->where('foc_options',5)
                        ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(actual_price) as mtd_actual_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                        ->where(function ($query) use ($search, $first_day, $today, $value) {
                            if (!empty($search)) {
                                if (isset($search['dealer2'])) {
                                    if (!empty(trim($search['dealer2']))) {
                                        $query->where('dealer_id', '=', $search['dealer2']);
                                    }
                                }
                                // if(isset($search['advisor2'])){
                                //     if(!empty(trim($search['advisor2']))){
                                //         $query->where('advisor_id','=',$search['advisor2']);              
                                //     }
                                // }
                                if (isset($search['department2'])) {
                                    if (!empty(trim($search['department2']))) {
                                        $query->where('department_id', '=', $search['department2']);
                                    }
                                }
                                if (isset($search['from2']) && isset($search['to2'])) {
                                    if (!empty(trim($search['from2']))) {
                                        $query->whereDate('job_date', '>=', $search['from2']);
                                        $query->whereDate('job_date', '<=', $search['to2']);
                                    }
                                } elseif (isset($search['to2'])) {
                                    if (!empty(trim($search['to2']))) {
                                        $query->whereDate('job_date', '<=', $search['to2']);
                                    }
                                } elseif (isset($search['from2'])) {
                                    if (!empty(trim($search['from2']))) {
                                        $query->whereDate('job_date', '>=', $search['from2']);
                                    }
                                } elseif (isset($search['month4'])) {
                                    if (!empty(trim($search['month4']))) {
                                        $exp = explode('-', $search['month4']);
                                        $query->whereMonth('job_date', $exp[1]);
                                        $query->whereYear('job_date', $exp[0]);
                                    }
                                } else {
                                    //$query->whereDate('job_date','=',@$value->job_date);
                                }
                            } else {
                                $query->whereDate('job_date', '=', @$value->job_date);
                            }
                        })
                        ->where('delete_job', 1)
                        ->first();
                    $total_job_array = array(
                        'mtd_total' => round(@$total_service->mtd_total),
                        'mtd_vas_value' => round(@$total_jobs->mtd_vas_value),
                        'mtd_actual_value' => @$total_jobs->mtd_actual_value,
                        'mtd_vas_total' => round(@$total_jobs->mtd_vas_total),
                        'mtd_hvt_value' => round(@$total_jobs->mtd_hvt_value),
                        'mtd_hvt_total' => round(@$total_jobs->mtd_hvt_total),
                    );
                    $i++;
                }
            }
            $finalAdvisor = array();
            $array = array();
            foreach ($advisors as $value) {
                $array['Advisor'] = get_advisor_name($value['advisor_id']);
                $array['Vas_Customer_Price'] = round($value['vas_customer_price']);
                $array['Vas_Actual_Price'] = round($value['vas_actual_price']);
                $array['Vas_incentive'] = round($value['vas_incentive']);
                $array['HVT_Customer_Price'] = round($value['hvt_customer_price']);
                $array['HVT_Actual_Price'] = round($value['hvt_actual_price']);
                $array['HVT_Incentive'] = round($value['hvt_incentive']);
                $finalAdvisor[] = $array;
            }
            return Excel::create('Advisor_' . date("d-M-Y"), function ($excel) use ($finalAdvisor, $total_job_array) {
                $excel->sheet('sheet', function ($sheet) use ($finalAdvisor, $total_job_array) {
                    $sheet->setBorder('H1:I10');
                    $sheet->setCellValue('H1', 'Monthly Treatments till Date');
                    $sheet->mergeCells("H1:I1");
                    $sheet->setCellValue('H2', 'RO');
                    $sheet->setCellValue('I2', $total_job_array['mtd_total']);
                    $sheet->setCellValue('H3', 'VAS');
                    $sheet->mergeCells("H3:I3");
                    $sheet->setCellValue('H4', 'No of Trmt');
                    $sheet->setCellValue('I4', $total_job_array['mtd_vas_total']);
                    $sheet->setCellValue('H5', 'Amount');
                    // $sheet->setCellValue('I5',$total_job_array['mtd_vas_value']);
                    $sheet->setCellValue('I5', $total_job_array['mtd_actual_value']);
                    $sheet->setCellValue('H6', 'Value Per Treatment');
                    $sheet->setCellValue('I6', vas_in_percentage(@$total_job_array['mtd_actual_value'], @$total_job_array['mtd_vas_total']));
                    $sheet->setCellValue('H7', 'HVT');
                    $sheet->mergeCells("H7:I7");
                    $sheet->setCellValue('H8', 'No of Trmt');
                    $sheet->setCellValue('I8', $total_job_array['mtd_hvt_total']);
                    $sheet->setCellValue('H9', 'Amount');
                    $sheet->setCellValue('I9', $total_job_array['mtd_hvt_value']);
                    $sheet->setCellValue('H10', 'HVT %');
                    $sheet->setCellValue('I10', hvt_in_percentage(@$total_job_array['mtd_hvt_value'], @$total_job_array['mtd_vas_value']));
                    $sheet->fromArray(@$finalAdvisor);
                });
            })->export('xlsx');
        }
    }
    // Download MIS
    public function downloadMIS(Request $request)
    {
        $search = $request->all();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['firm']) && empty($search['asm'])) {
            $dealers = User::where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = array();
            foreach ($dealers as $k => $v) {
                $d_ids[] = $dealers[$k]->id;
            }
        } else if (!empty($search['firm']) && !empty($search['asm'])) {
            // $dealers = User::where(['role'=>2, 'reporting_authority'=>$search['asm'],'status'=>1])->select('id','name')->orderBy('name','ASC')->get();
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'firm_id' => $search['firm'], 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($search['asm'], $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
        } else {
            $dealers = User::where('role', 2)->where('status', 1)->select('id', 'name')->orderBy('name', 'ASC')->get();
            $d_ids = [];
        }
        // $users = DB::table('users')
        // ->select('id')
        // ->where('users.role',2)
        // ->orderBy('users.name','ASC')
        // ->get();
        $mist = array();
        foreach ($dealers as $key => $value) {
            $mis = DB::table('jobs')
                // ->select(DB::raw('jobs.id as job_id,SUM(jobs.treatment_total) as mtd_total,SUM(jobs.customer_price) as customer_price,SUM(jobs.actual_price) as actual_price,SUM(jobs.hvt_total) as hvt_total, SUM(jobs.dealer_price) as dealer_price, SUM(jobs.incentive) as incentive,SUM(jobs.hvt_total) as mtd_hvt, SUM(jobs.hvt_value) as mtd_hvt_value,SUM(jobs.vas_total) as mtd_vas, SUM(jobs.vas_value) as mtd_vas_value, jobs.dealer_id, jobs.foc_options,jobs.treatments'))
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from12']) && isset($search['to12'])) {
                        if (!empty(trim($search['from12']))) {
                            $query->whereDate('jobs.job_date', '>=', $search['from12']);
                            $query->whereDate('jobs.job_date', '<=', $search['to12']);
                        }
                    } elseif (isset($search['to12'])) {
                        if (!empty(trim($search['to12']))) {
                            $query->whereDate('jobs.job_date', '<=', $search['to12']);
                        }
                    } elseif (isset($search['from12'])) {
                        if (!empty(trim($search['from12']))) {
                            $query->whereDate('jobs.job_date', '>=', $search['from12']);
                        }
                    } elseif (!empty($search['selectMonth2'])) {
                        $exp = explode('-', $search['selectMonth2']);
                        $query->whereMonth('jobs.job_date', $exp[1]);
                        $query->whereYear('jobs.job_date', $exp[0]);
                    } else {
                        $query->whereDate('jobs.job_date', '>=', $first_day);
                        $query->whereDate('jobs.job_date', '<=', $today);
                    }
                })
                ->where('jobs.dealer_id', $value->id)
                ->where('jobs.delete_job', 1)
                // ->where('jobs.foc_options',5)
                // ->groupBy('jobs.dealer_id')
                ->get();
            $treatment_total = $hvt_incentive = $customer_price = $actual_price = $incentive = $hvt_total = $hvt_value = $vas_total = $vas_value = $dealer_price = 0;
            $array = array();
            if (count($mis) == 0) {
                $data = new \stdClass();
                $data->dealer_id = $value->id;
                $data->treatment_total = 0;
                $data->id = 0;
                $data->customer_price = 0;
                $data->actual_price = 0;
                $data->dealer_price = 0;
                $data->incentive = 0;
                $data->hvt_total = 0;
                $data->mtd_hvt = 0;
                $data->hvt_value = 0;
                $data->vas_total = 0;
                $data->vas_value = 0;
                $data->hvt_incentive = 0;
                $mis[] = $data;
            }
            foreach ($mis as $key1 => $value1) {
                $treatment_total += $value1->treatment_total;
                $incentive       += $value1->incentive;
                $actual_price    += (int)$value1->actual_price;
                $hvt_total       += $value1->hvt_total;
                $hvt_value       += $value1->hvt_value;
                $vas_total       += $value1->vas_total;
                $vas_value       += $value1->vas_value;
                $dealer_price    += $value1->dealer_price;

                if ($value1->id != 0) {
                    $decoded_jobs = explode(',', $value1->id);
                    foreach ($decoded_jobs as $key => $val) {
                        $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                        $decoded_treatments = json_decode(@$treat->treatments);
                        if (!empty($decoded_treatments)) {
                            foreach ($decoded_treatments as $key => $val1) {
                                if (@$val1->job_type == 5) {
                                    $customer_price = $customer_price + $val1->customer_price;
                                    $incentive = $incentive + $val1->incentive;
                                } else {
                                    $customer_price = $customer_price + 0;
                                    $incentive = $incentive + 0;
                                }
                                if ($val1->treatment_type == 1) {
                                    $hvt_incentive = $hvt_incentive + $val1->incentive;
                                }
                            }
                        }
                    }
                }
                $array['mtd_total'] = $treatment_total;
                $array['customer_price'] = $customer_price;
                $array['actual_price'] = $actual_price;
                $array['dealer_price'] = $dealer_price;
                $array['incentive'] = $incentive;
                $array['hvt_total'] = $hvt_total;
                $array['mtd_hvt'] = $hvt_total;
                $array['mtd_hvt_value'] = $hvt_value;
                $array['mtd_vas'] = $vas_total;
                $array['mtd_vas_value'] = $vas_value;
                $array['hvt_incentive'] = $hvt_incentive;
                $array['dealer_id'] = @$value1->dealer_id;
            }
            $mist[] = $array;
        }
        foreach ($mist as $key => $value) {
            $total = DB::table('jobs_by_date')
                ->select(DB::raw('SUM(total_jobs) as total_jobs,dealer_id'))
                ->where('dealer_id', $value['dealer_id'])
                ->where(function ($query) use ($search, $first_day, $today) {
                    if (isset($search['from12']) && isset($search['to12'])) {
                        if (!empty(trim($search['from12']))) {
                            $query->whereDate('job_added_date', '>=', $search['from12']);
                            $query->whereDate('job_added_date', '<=', $search['to12']);
                        }
                    } elseif (isset($search['to12'])) {
                        if (!empty(trim($search['to12']))) {
                            $query->whereDate('job_added_date', '<=', $search['to12']);
                        }
                    } elseif (isset($search['from12'])) {
                        if (!empty(trim($search['from12']))) {
                            $query->whereDate('job_added_date', '>=', $search['from12']);
                        }
                    } elseif (!empty($search['selectMonth2'])) {
                        $exp = explode('-', $search['selectMonth2']);
                        $query->whereMonth('job_added_date', $exp[1]);
                        $query->whereYear('job_added_date', $exp[0]);
                    } else {
                        $query->whereDate('job_added_date', '>=', $first_day);
                        $query->whereDate('job_added_date', '<=', $today);
                    }
                })
                ->groupBy('dealer_id')
                ->first();
            if (!empty($total->total_jobs)) {
                $mist[$key]['service_load'] = (int)$total->total_jobs;
            } else {
                $mist[$key]['service_load'] = 0;
            }
        }
        /********************************* Download MIS Report Start *******************************/
        return Excel::create('MIS_' . date("d-M-Y"), function ($excel) use ($mist) {
            $excel->sheet('sheet', function ($sheet) use ($mist) {
                $arr = array();
                $cp = $dp = $in = $hvt = $mtd_hvt = $service = 0;
                foreach ($mist as $val1) {
                    $cp = $cp + $val1['customer_price'];
                    $dp = $dp + $val1['dealer_price'];
                    $in = $in + $val1['incentive'];
                    $hvt = $hvt + $val1['hvt_total'];
                    $mtd_hvt = $mtd_hvt + $val1['mtd_hvt_value'];
                    $service = $service + $val1['service_load'];
                }
                $array['CDC'] = 'Business Total';
                $array['Cust_Bill'] = round($cp);
                $array['Vendor'] = round($dp);
                $array['Incentive'] = round($in);
                $array['MTD_HVT'] = round($hvt);
                $array['HVT_Value'] = round($mtd_hvt);
                $array['HVT_%'] = hvt_in_percentage($mtd_hvt, $cp);
                $array['RO'] = round($service);
                $arr[] = $array;
                foreach ($mist as $val) {
                    $array['CDC'] = get_name($val['dealer_id']);
                    $array['Cust_Bill'] = round($val['customer_price']);
                    $array['Vendor'] = round($val['dealer_price']);
                    $array['Incentive'] = round($val['incentive']);
                    $array['MTD_HVT'] = round($val['hvt_total']);
                    $array['HVT_Value'] = round($val['mtd_hvt_value']);
                    $array['HVT_%'] = hvt_in_percentage($val['mtd_hvt_value'], $val['customer_price']);
                    $array['RO'] = $val['service_load'];
                    $arr[] = $array;
                }
                $count = count($arr) + 1;
                $sheet->setBorder('A3:H' . $count);
                $sheet->cells('A3:A' . $count, function ($cells) {
                    $cells->setBackground('#FFFF00');
                });
                $sheet->cells('B3:B' . $count, function ($cells) {
                    $cells->setBackground('#B6DDE8');
                });
                $sheet->cells('C3:C' . $count, function ($cells) {
                    $cells->setBackground('#F7FED0');
                });
                $sheet->cells('D3:D' . $count, function ($cells) {
                    $cells->setBackground('#FFFF00');
                });
                $sheet->cells('E3:E' . $count, function ($cells) {
                    $cells->setBackground('#F2DDDC');
                });
                $sheet->cells('F3:F' . $count, function ($cells) {
                    $cells->setBackground('#F2DDDC');
                });
                $sheet->cells('G3:G' . $count, function ($cells) {
                    $cells->setBackground('#FFFF00');
                });
                $sheet->cells('H3:H' . $count, function ($cells) {
                    $cells->setBackground('#B6DDE8');
                });
                $sheet->fromArray(@$arr);
            });
        })->export('xlsx');
        /********************************* Download MIS Report End *******************************/
    }
    // View performance report
    public function performance_reports(Request $request)
    {
        $search = $request->all();
        $current = date('Y-m');
        if (@$search['selectMonth']) {
            $monthYear = explode('-', $search['selectMonth']);
        } else {
            $monthYear = explode('-', $current);
        }
        //dd($monthYear);
        $dealers = User::where(['role' => 2, 'status' => 1])->select('id', 'name')->orderBy('name', 'ASC')->get();
        $advisors = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as customer_price, SUM(incentive) as incentive, SUM(dealer_price) as dealer_price,SUM(hvt_value) as hvt_value, advisor_id,dealer_id,job_date'))
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('dealer_id', '=', $search['dealer']);
                        }
                    }
                }
            })
            ->whereMonth('job_date', '=', $monthYear[1])
            ->whereYear('job_date', '=', $monthYear[0])
            ->where('delete_job', 1)
            ->where('foc', 0)
            ->groupBy('advisor_id')
            ->get();
        //dd($advisors);
        return view('admin.performance_reports', [
            'dealers' => $dealers,
            'advisors' => $advisors,
            'oldDealer' => @$search['dealer'],
            'oldMonth' => @$search['selectMonth'],
        ]);
    }
    // Download performance report
    public function downloadPerformanceSheet(Request $request)
    {
        $search = $request->all();
        $current = date('Y-m');
        if (@$search['selectMonth1']) {
            $monthYear = explode('-', $search['selectMonth1']);
        } else {
            $monthYear = explode('-', $current);
        }
        $advisors = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as customer_price, SUM(incentive) as incentive, SUM(dealer_price) as dealer_price,SUM(hvt_value) as hvt_value,SUM(hvt_total) as hvt_total, advisor_id,dealer_id, job_date'))
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search['dealer1'])) {
                        if (!empty(trim($search['dealer1']))) {
                            $query->where('dealer_id', '=', $search['dealer1']);
                        }
                    }
                }
            })
            ->whereMonth('job_date', '=', $monthYear[1])
            ->whereYear('job_date', '=', $monthYear[0])
            ->where('delete_job', 1)
            ->groupBy('advisor_id')
            ->get();
        if (@$search['dealer1']) {
            $sheetName = get_name($search['dealer1']) . '_';
        } else {
            $sheetName = 'All_Dealers_';
        }
        return Excel::create($sheetName . 'Performance_Report_' . date("M-Y"), function ($excel) use ($advisors) {
            $result = array();
            foreach ($advisors as $key => $value) {
                $arr['Dealer'] = get_name($value->dealer_id);
                $arr['Advisor'] = get_advisor_name($value->advisor_id);
                $arr['PAN_Card'] = get_pan_no($value->advisor_id);
                $arr['Customer_Price'] = round($value->customer_price);
                $arr['Incentive'] = round($value->incentive);
                $arr['HVT_Value'] = round($value->hvt_value);
                $arr['HVT_Number'] = (int)$value->hvt_total;
                $result[] = $arr;
            }
            //dd($result);
            $excel->sheet('sheet', function ($sheet) use ($result) {
                $sheet->fromArray(@$result);
            });
        })->export('xlsx');
    }
    // Download Particular Advisor's Performance Report
    public function downloadAdvisor($id, $dealer_id, $month)
    {
        $monthYear = explode('-', $month);
        $getModels = DB::table('models as m')
            ->select(DB::raw('group_concat(m.id) as model_id, m.model_size'))
            // ->where('m.dealer_id',$dealer_id)
            ->groupBy('m.model_size')
            ->orderBy('m.model_size', 'ASC')->get();
        return Excel::create(get_name($dealer_id) . '_(' . get_advisor_name($id) . ')_' . date("M-Y"), function ($excel) use ($getModels, $id, $dealer_id, $monthYear) {
            $excel->sheet('sheet', function ($sheet) use ($getModels, $id, $dealer_id, $monthYear) {
                foreach ($getModels as $value) {
                    $model_id = explode(',', $value->model_id);
                    // if($value->model_size == 1){
                    //     $sheetName = 'Large';
                    // }elseif($value->model_size == 2){
                    //     $sheetName = 'Medium';
                    // }elseif($value->model_size == 3){
                    //     $sheetName = 'Small';
                    // }
                    $advisors = DB::table('jobs')
                        ->select('model_id', 'treatments')
                        ->where('dealer_id', '=', $dealer_id)
                        ->whereIn('model_id', $model_id)
                        ->whereMonth('job_date', '=', $monthYear[1])
                        ->whereYear('job_date', '=', $monthYear[0])
                        ->where('advisor_id', $id)
                        ->where('delete_job', 1)
                        ->get();
                    //dd($advisors);
                    $getTreatments = DB::table('treatments')
                        ->select('id', 'treatment')
                        // ->where('dealer_id',$dealer_id)
                        ->whereIn('model_id', $model_id)
                        // ->where('treatment_type',1)
                        ->orderBy('treatment', 'ASC')
                        ->get();
                    $tcounts = array();
                    $i = 0;
                    foreach ($getTreatments as $key => $tvalue) {
                        $tcounts[$i]['id'] = $tvalue->id;
                        $tcounts[$i]['treatment'] = $tvalue->treatment;
                        $tcounts[$i]['total'] = 0;
                        $tcounts[$i]['customer_price'] = 0;
                        foreach ($advisors as $jvalue) {
                            if (@$jvalue->treatments) {
                                $t = json_decode($jvalue->treatments);
                                if (@$t) {
                                    foreach ($t as $jtvalue) {
                                        if ($jtvalue->id == $tvalue->id) {
                                            // if($jtvalue->treatment_type=='1'){
                                            $tcounts[$i]['total']++;
                                            $tcounts[$i]['customer_price'] = $tcounts[$i]['customer_price'] + $jtvalue->customer_price;
                                            // }
                                        }
                                    }
                                }
                            }
                        }
                        $i++;
                    }
                    $final_treatments = array();
                    $tnames = array();
                    $j = 0;
                    foreach ($tcounts as $valuet) {
                        if (in_array($valuet['treatment'], $tnames)) {
                            $a = array_search($valuet['treatment'], $tnames);
                            $final_treatments[$a]['total'] = $final_treatments[$a]['total'] + $valuet['total'];
                            $final_treatments[$a]['customer_price'] = $final_treatments[$a]['customer_price'] + $valuet['customer_price'];
                        } else {
                            $tnames[$j] = $valuet['treatment'];
                            $final_treatments[$j]['treatment'] = $valuet['treatment'];
                            $final_treatments[$j]['total'] = $valuet['total'];
                            $final_treatments[$j]['customer_price'] = $valuet['customer_price'];
                            $j++;
                        }
                    }
                    //dd($final_treatments);
                    $sheet->cells('A1:K2', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->setBorder('A1:K2');
                    $sheet->setCellValue('A1', 'Large');
                    $sheet->mergeCells("A1:C1");
                    $sheet->setCellValue('A2', 'Treatment');
                    $sheet->setCellValue('B2', 'Number');
                    $sheet->setCellValue('C2', 'Value');
                    $sheet->setCellValue('E1', 'Medium');
                    $sheet->mergeCells("E1:G1");
                    $sheet->setCellValue('E2', 'Treatment');
                    $sheet->setCellValue('F2', 'Number');
                    $sheet->setCellValue('G2', 'Value');
                    $sheet->setCellValue('I1', 'Small');
                    $sheet->mergeCells("I1:K1");
                    $sheet->setCellValue('I2', 'Treatment');
                    $sheet->setCellValue('J2', 'Number');
                    $sheet->setCellValue('K2', 'Value');
                    $sheet->setBorder('M2:N4');
                    $sheet->cells('M2:N4', function ($cells) {
                        $cells->setBackground('#FFFF00');
                    });
                    $sheet->setCellValue('M2', 'Advisor');
                    $sheet->setCellValue('N2', get_advisor_name($id));
                    $sheet->setCellValue('M3', 'Pan Card');
                    $sheet->setCellValue('N3', get_pan_no($id));
                    $sheet->setCellValue('M4', 'Dealer');
                    $sheet->setCellValue('N4', get_name($dealer_id));
                    foreach ($final_treatments as $value1) {
                        $tot = count($final_treatments) + 3;
                        if ($value->model_size == 1) {
                            $i = 3;
                            $model1_total = $model1_value = 0;
                            foreach ($final_treatments as  $value1) {
                                $sheet->setCellValue('A' . $i, $value1['treatment']);
                                $sheet->setCellValue('B' . $i, $value1['total']);
                                $sheet->setCellValue('C' . $i, $value1['customer_price']);
                                $i++;
                                $sheet->setCellValue('A28', 'Total');
                                $model1_total = $model1_total + $value1['total'];
                                $model1_value = $model1_value + $value1['customer_price'];
                            }
                        }
                        if ($value->model_size == 2) {
                            $i = 3;
                            $model2_total = $model2_value = 0;
                            foreach ($final_treatments as  $value2) {
                                $sheet->setCellValue('E' . $i, $value2['treatment']);
                                $sheet->setCellValue('F' . $i, $value2['total']);
                                $sheet->setCellValue('G' . $i, $value2['customer_price']);
                                $i++;
                                $model2_total = $model2_total + $value2['total'];
                                $model2_value = $model2_value + $value2['customer_price'];
                            }
                        }
                        if ($value->model_size == 3) {
                            $i = 3;
                            $model3_total = $model3_value = 0;
                            foreach ($final_treatments as $value3) {
                                $sheet->setCellValue('I' . $i, $value3['treatment']);
                                $sheet->setCellValue('J' . $i, $value3['total']);
                                $sheet->setCellValue('K' . $i, $value3['customer_price']);
                                $i++;
                                $model3_total = $model3_total + $value3['total'];
                                $model3_value = $model3_value + $value3['customer_price'];
                            }
                        }
                        $sheet->setCellValue('B28', @$model1_total);
                        $sheet->setCellValue('C28', @$model1_value);
                        $sheet->setCellValue('F28', @$model2_total);
                        $sheet->setCellValue('G28', @$model2_value);
                        $sheet->setCellValue('J28', @$model3_total);
                        $sheet->setCellValue('K28', @$model3_value);
                        $sheet->cells('A28:K28', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->setBorder('A28:K28');
                        //$final[]=$array;
                    }
                    //$sheet->fromArray($final);
                }
            });
        })->export('xlsx');
    }
    // Download All Advisor's Performance Report
    public function downloadAllAdvisor(Request $request)
    {
        if (!empty($request->selectMonth2)) {
            $month = $request->selectMonth2;
        } else {
            $month = date('Y-m');
        }
        $dealer_id = $request->dealer2;
        $monthYear = explode('-', $month);
        $advisors = DB::table('advisors')->where('dealer_id', $dealer_id)->get();
        //dd($advisors);
        $getModels = DB::table('models as m')
            ->select(DB::raw('group_concat(m.id) as model_id, m.model_size'))
            // ->where('m.dealer_id',$dealer_id)
            ->groupBy('m.model_size')
            ->orderBy('m.model_size', 'ASC')->get();
        return Excel::create(get_name($dealer_id) . '_' . date("M-Y"), function ($excel) use ($getModels, $dealer_id, $monthYear, $advisors) {
            foreach ($advisors as $advisor) {
                $excel->sheet($advisor->name, function ($sheet) use ($getModels, $dealer_id, $monthYear, $advisor) {
                    foreach ($getModels as $value) {
                        $model_id = explode(',', $value->model_id);
                        $advisors = DB::table('jobs')
                            ->select('model_id', 'treatments')
                            ->where('dealer_id', '=', $dealer_id)
                            ->whereIn('model_id', $model_id)
                            ->whereMonth('job_date', '=', $monthYear[1])
                            ->whereYear('job_date', '=', $monthYear[0])
                            ->where('advisor_id', $advisor->id)
                            ->where('delete_job', 1)
                            ->get();
                        //dd($advisors);
                        $getTreatments = DB::table('treatments')
                            ->select('id', 'treatment')
                            // ->where('dealer_id',$dealer_id)
                            ->whereIn('model_id', $model_id)
                            // ->where('treatment_type',1)
                            ->orderBy('treatment', 'ASC')
                            ->get();
                        $tcounts = array();
                        $i = 0;
                        foreach ($getTreatments as $key => $tvalue) {
                            $tcounts[$i]['id'] = $tvalue->id;
                            $tcounts[$i]['treatment'] = $tvalue->treatment;
                            $tcounts[$i]['total'] = 0;
                            $tcounts[$i]['customer_price'] = 0;
                            foreach ($advisors as $jvalue) {
                                if (@$jvalue->treatments) {
                                    $t = json_decode($jvalue->treatments);
                                    if (@$t) {
                                        foreach ($t as $jtvalue) {
                                            if ($jtvalue->id == $tvalue->id) {
                                                // if($jtvalue->treatment_type=='1'){
                                                $tcounts[$i]['total']++;
                                                $tcounts[$i]['customer_price'] = $tcounts[$i]['customer_price'] + $jtvalue->customer_price;
                                                // }
                                            }
                                        }
                                    }
                                }
                            }
                            $i++;
                        }
                        $final_treatments = array();
                        $tnames = array();
                        $j = 0;
                        foreach ($tcounts as $valuet) {
                            if (in_array($valuet['treatment'], $tnames)) {
                                $a = array_search($valuet['treatment'], $tnames);
                                $final_treatments[$a]['total'] = $final_treatments[$a]['total'] + $valuet['total'];
                                $final_treatments[$a]['customer_price'] = $final_treatments[$a]['customer_price'] + $valuet['customer_price'];
                            } else {
                                $tnames[$j] = $valuet['treatment'];
                                $final_treatments[$j]['treatment'] = $valuet['treatment'];
                                $final_treatments[$j]['total'] = $valuet['total'];
                                $final_treatments[$j]['customer_price'] = $valuet['customer_price'];
                                $j++;
                            }
                        }
                        //dd($final_treatments);
                        $sheet->cells('A1:K2', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->setBorder('A1:K2');
                        $sheet->setCellValue('A1', 'Large');
                        $sheet->mergeCells("A1:C1");
                        $sheet->setCellValue('A2', 'Treatment');
                        $sheet->setCellValue('B2', 'Number');
                        $sheet->setCellValue('C2', 'Value');
                        $sheet->setCellValue('E1', 'Medium');
                        $sheet->mergeCells("E1:G1");
                        $sheet->setCellValue('E2', 'Treatment');
                        $sheet->setCellValue('F2', 'Number');
                        $sheet->setCellValue('G2', 'Value');
                        $sheet->setCellValue('I1', 'Small');
                        $sheet->mergeCells("I1:K1");
                        $sheet->setCellValue('I2', 'Treatment');
                        $sheet->setCellValue('J2', 'Number');
                        $sheet->setCellValue('K2', 'Value');
                        $sheet->setBorder('M2:N4');
                        $sheet->cells('M2:N4', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->setCellValue('M2', 'Advisor');
                        $sheet->setCellValue('N2', get_advisor_name($advisor->id));
                        $sheet->setCellValue('M3', 'Pan Card');
                        $sheet->setCellValue('N3', get_pan_no($advisor->id));
                        $sheet->setCellValue('M4', 'Dealer');
                        $sheet->setCellValue('N4', get_name($dealer_id));
                        foreach ($final_treatments as $value1) {
                            $tot = count($final_treatments) + 3;
                            if ($value->model_size == 1) {
                                $i = 3;
                                $model1_total = $model1_value = 0;
                                foreach ($final_treatments as  $value1) {
                                    $sheet->setCellValue('A' . $i, $value1['treatment']);
                                    $sheet->setCellValue('B' . $i, $value1['total']);
                                    $sheet->setCellValue('C' . $i, $value1['customer_price']);
                                    $i++;
                                    $sheet->setCellValue('A28', 'Total');
                                    $model1_total = $model1_total + $value1['total'];
                                    $model1_value = $model1_value + $value1['customer_price'];
                                }
                            }
                            if ($value->model_size == 2) {
                                $i = 3;
                                $model2_total = $model2_value = 0;
                                foreach ($final_treatments as  $value2) {
                                    $sheet->setCellValue('E' . $i, $value2['treatment']);
                                    $sheet->setCellValue('F' . $i, $value2['total']);
                                    $sheet->setCellValue('G' . $i, $value2['customer_price']);
                                    $i++;
                                    $model2_total = $model2_total + $value2['total'];
                                    $model2_value = $model2_value + $value2['customer_price'];
                                }
                            }
                            if ($value->model_size == 3) {
                                $i = 3;
                                $model3_total = $model3_value = 0;
                                foreach ($final_treatments as $value3) {
                                    $sheet->setCellValue('I' . $i, $value3['treatment']);
                                    $sheet->setCellValue('J' . $i, $value3['total']);
                                    $sheet->setCellValue('K' . $i, $value3['customer_price']);
                                    $i++;
                                    $model3_total = $model3_total + $value3['total'];
                                    $model3_value = $model3_value + $value3['customer_price'];
                                }
                            }
                            $sheet->setCellValue('B28', @$model1_total);
                            $sheet->setCellValue('C28', @$model1_value);
                            $sheet->setCellValue('F28', @$model2_total);
                            $sheet->setCellValue('G28', @$model2_value);
                            $sheet->setCellValue('J28', @$model3_total);
                            $sheet->setCellValue('K28', @$model3_value);
                            $sheet->cells('A28:K28', function ($cells) {
                                $cells->setBackground('#FFFF00');
                            });
                            $sheet->setBorder('A28:K28');
                            //$final[]=$array;
                        }
                        //$sheet->fromArray($final);
                    }
                });
            }
        })->export('xlsx');
    }
    // View services in staff management module
    public function viewServices($id)
    {
        $result = DB::table('jobs_by_date')
            ->select('job_added_date as job_date', 'total_jobs as total')
            ->groupBy('job_added_date')
            ->where('user_id', $id)
            ->get();
        return view('admin.viewServices', [
            'result' => $result,
            'id' => $id,
        ]);
    }
    // View Jobs listing
    public function jobs(Request $request)
    {
        $search = $request->search;
        $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->get();
        $regn_no = $request->regn_no;
        if (@$request->job_type) {
            if ($request->job_type == 1) {
                $job_type = 1;
                $type = $request->job_type;
            } elseif ($request->job_type == 2) {
                $job_type = 2;
                $type = $request->job_type;
            } elseif ($request->job_type == 3) {
                $job_type = 3;
                $type = $request->job_type;
            } elseif ($request->job_type == 4) {
                $job_type = 4;
                $type = $request->job_type;
            } elseif ($request->job_type == 5) {
                $job_type = 5;
                $type = $request->job_type;
            }
        }

        //dd($job_type);
        $result = DB::table('jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->where('j.user_id', '=', $search);
                        }
                    }
                }
            })
            ->where(function ($query) use ($regn_no) {
                if (!empty($regn_no)) {
                    if (isset($regn_no)) {
                        if (!empty(trim($regn_no))) {
                            $query->where('j.regn_no', 'LIKE', '%' . $regn_no);
                        }
                    }
                }
            });
        if ($request->job_type == 1) {
            $result = $result->where(function ($query) use ($job_type) {
                $query->where('j.foc_options', 1);
            });
        } elseif ($request->job_type == 2) {
            $result = $result->where(function ($query) use ($job_type) {
                $query->where('j.foc_options', 2);
            });
        } elseif ($request->job_type == 3) {
            $result = $result->where(function ($query) use ($job_type) {
                $query->where('j.foc_options', 3);
            });
        } elseif ($request->job_type == 4) {
            $result = $result->where(function ($query) use ($job_type) {
                $query->where('j.foc_options', 4);
            });
        } elseif ($request->job_type == 5) {
            $result = $result->where(function ($query) use ($job_type) {
                $query->where('j.foc_options', 5);
            });
        }
        $result = $result->where('delete_job', 1)
            ->orderBy('j.job_date', 'DESC')
            ->paginate(20);
        $supervisors = DB::table('jobs_treatment as jt')
            ->join('jobs as j', 'j.id', '=', 'jt.job_id')
            ->join('users as u', 'u.id', '=', 'j.user_id')
            ->select('u.name', 'j.user_id as id')
            ->where('jt.delete_job', 1)
            ->groupBy('j.user_id')
            ->orderBy('u.name', 'ASC')
            ->get();
        if (request()->has('page')) {
            Session::put('job_url', url()->full());
        }
        return view('admin.jobs', [
            'result' => $result->appends(Input::except('page')),
            'supervisors' => $supervisors,
            'dealers' => $dealers,
            'regn_no' => @$regn_no,
            'job_type' => @$type,
            'oldSupervisor' => @$search,
        ]);
    }

    public function getdealerUsers(Request $request)
    {
        $dealer_id = $request->dealer;
        $users = DB::table('users')->where(['role' => 3, 'dealer_id' => $dealer_id])->where('dealer_id', '!=', '')->where('reporting_authority', '!=', '')->select('id', 'name')->get();
        if (count($users) > 0) {
            $res = '<option value="">Select User</option>';
            foreach ($users as $user) {
                $user_id = $user->id;
                $user_name = $user->name;
                $user_des_name = get_designation_by_userid($user->id);
                $res .= "<option value='$user_id'>$user_name - $user_des_name</option>";
            }
        } else {
            $res = "<option value=''>No User found</option>";
        }
        return $res;
    }

    // view add job page
    public function addJob()
    {
        $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->where('status', 1)->orderBy('name')->get();
        return view('admin.addJob', [
            'dealers' => $dealers,
        ]);
    }
    // save new job
    public function insertJob(Request $request)
    {
        $post = $request->all();
        $actual_price = 0;
        // $discount_price = 0;
        $difference_price = 0;
        $customer_price = 0;
        $dealer_price = 0;
        $incentive = 0;
        $this->validate(
            $request,
            [
                'dealer_id' => 'required',
                'treatment_id' => 'required',
                'advisor_id' => 'required',
                'model_id' => 'required',
                'job_date' => 'required|date',
                'job_card_no' => 'required',
                'bill_no' => 'required',
                'regn_no' => 'required',
                'dealer_price' => 'required',
            ],
            [
                'model_id.required' => 'Please select model',
                'advisor_id.required' => 'Please select advisor',
                'treatment_id.required' => 'Please select treatment',
                'dealer_id.required' => 'Please select dealer',
                'job_date.required' => 'Please select job date',
                'job_card_no.required' => 'Please enter job card no',
                'bill_no.required' => 'Please enter bill no',
                'regn_no.required' => 'Please enter registration no',
                'dealer_price.required' => 'Please enter dealer price',
            ]
        );
        $treatment_id = array();
        $treatment_data = array();
        $i = $hvt_value = 0;
        foreach ($post['treatment_id'] as $key => $value) {
            $data1 = DB::table('treatments')->where('id', $value)->first();
            $data1->job_type = $post['job_type'][$key];
            $data1->actualPrice = $post['actualPrice'][$key];
            // $data1->discountPrice = $post['discountPrice'][$key];
            $data1->difference = $post['difference'][$key];
            $data1->dealer_price = $post['dealer_price'][$key];
            $treatment_data[] = $data1;
            if ($data1->job_type == '5') {
                $actual_price = $actual_price + $data1->actualPrice;
                // $discount_price = $discount_price + $data1->discountPrice;
                $difference_price = $difference_price + $data1->difference;
            } else {
                $actual_price = 0;
                // $discount_price = 0;
                $difference_price = 0;
            }
            $customer_price = $customer_price + $data1->customer_price;
            $dealer_price = $dealer_price + $data1->dealer_price;
            // $incentive = $incentive + $data1->incentive;
            if ($data1->treatment_type == 1) {
                $i++;
                // $hvt_value = $hvt_value + $data1->customer_price;
                if ($data1->job_type == '5') {
                    $hvt_value = $hvt_value + $data1->actualPrice;
                    // $hvt_value = $hvt_value + $data1->difference;
                } else {
                    $hvt_value = 0;
                }
            }
            $treat_id['id'] = $data1->id;
            $treatment_id[] = $treat_id;
        }

        $job_card_no = $request->job_card_no . '-W';
        // $checkCardNo = DB::table('jobs')
        //      ->where('job_card_no',$request->job_card_no)
        //      ->where('dealer_id',$request->dealer_id)
        //                     ->where('delete_job',1)
        //      ->get();
        // if(!empty($request->bill_no)){
        //   $checkBillNo = DB::table('jobs')->where('delete_job',1)->where('bill_no',$request->bill_no)->get();
        // }else{
        //   $checkBillNo = array();
        // }
        // if(!empty($request->regn_no)){
        //    $checkRegnNo = DB::table('jobs')->where('delete_job',1)->where('regn_no',$request->regn_no)->where('job_date',$request->job_date)->get();
        // }else{
        //   $checkRegnNo = array();
        // }
        // $error = array();
        // if(count($checkCardNo) > 0)
        // {
        // $error[] = 'This Job card no. has already added.';
        // }
        // if(count($checkBillNo) > 0)
        // {
        // $error[] = 'This Bill no. has already added.';
        // }
        // if(count($checkRegnNo) > 0)
        // {
        // $error[] = 'This Regn no. has already added.';
        // }
        // if(count($error) > 0)
        // {                  
        //    Session::flash('addComErrmsg', $error);
        //    Session::flash('alert-class', 'alert-danger');
        //     return redirect('/admin/addJob')->with('error',$error);
        // }else{
        $data = array(
            // 'user_id' => Auth::user()->id,
            'user_id' => $request->user_id,
            'job_date' => $request->job_date,
            'job_card_no' => $job_card_no,
            'bill_no' => $request->bill_no,
            'regn_no' => trim($request->regn_no),
            'remarks' => $request->remarks,
            'dealer_id' => $request->dealer_id,
            'model_id' => $request->model_id,
            'advisor_id' => $request->advisor_id,
            'department_id' => getDealerDepartment($request->advisor_id),
            'remarks' => $request->remark,
            'treatments' => json_encode($treatment_data),
            'treatment_total' => count($request->treatment_id),
            'hvt_total' => $i,
            'hvt_value' => $hvt_value,
            'vas_total' => count($request->treatment_id),
            'vas_value' => $customer_price,
            'customer_price' => $customer_price,
            'actual_price' => $actual_price,
            // 'discount_price' => $discount_price,
            'difference_price' => $difference_price,
            'dealer_price' => $dealer_price,
            // 'incentive' => $incentive,
            'date_added' => getCurrentTimestamp(),
            // 'foc_options' => $request->option,

        );
        // if (@$request->option) {
        //     $data['foc_options'] = $request->option;
        // }else{
        //    $data['foc_options'] = 5; 
        // }
        $result = DB::table('jobs')->insert($data);
        $id = DB::getPdo()->lastInsertId();
        foreach ($treatment_id as $value) {
            $data = array(
                'job_id' => $id,
                'treatment_id' => $value['id'],
            );
            DB::table('jobs_treatment')->insert($data);
        }
        Session::flash('success', 'Job added successfully!');
        return redirect('/admin/jobs');
    }
    // view edit job page
    public function editJob($id)
    {
        $result = DB::table('jobs')->where('id', $id)->first();
        $users = DB::table('users')->where(['role' => 3, 'dealer_id' => $result->dealer_id])->where('reporting_authority', '!=', '')->select('id', 'name')->get();
        $selectTreatment = array();
        foreach (json_decode($result->treatments) as $value) {
            $select['id'] = $value->id;
            $select['treatment'] = $value->treatment;
            $select['customer_price'] = $value->customer_price;
            $select['dealer_price'] = $value->dealer_price;
            // $select['incentive'] = $value->incentive;
            $select['job_type'] = @$value->job_type;
            $select['actualPrice'] = @$value->actualPrice;
            // $select['discountPrice'] = @$value->discountPrice;
            $select['difference'] = @$value->difference;
            $selectTreatment[] = $select;
        }

        $treatments = DB::table('treatments')
            ->where('model_id', $result->model_id)
            //->where('dealer_id',$result->dealer_id)
            ->where('status', 1)
            ->get();
        // $treatment=array();
        // foreach ($treatments as $value) {
        //     $tr['id']=$value->id;
        //     $tr['treatment']=$value->treatment;
        //     $treatment[]=$tr;
        // }
        // dd($treatments);
        $dealer_templates = DB::table('dealer_templates')->where('dealer_id', $result->dealer_id)->get(['template_id']);
        $templates = array();
        foreach ($dealer_templates as $key => $value) {
            $templates[] = $value->template_id;
        }

        if (count($templates) > 0) {
            $models = DB::table('dealer_templates as dt')
                ->join('treatments as t', 'dt.template_id', '=', 't.temp_id')
                ->select('t.model_id')
                ->groupBy('t.model_id')
                ->whereIn('dt.template_id', $templates)
                ->where('dt.dealer_id', $result->dealer_id)
                ->get();

            // $models = DB::table('treatments')
            //     ->select('model_id')
            //     ->where('temp_id', $template->template_id)
            //     ->groupBy('model_id')
            //     ->get();

            if (!empty($models)) {
                $model = array();
                foreach ($models as $val) {
                    $model[] = $val->model_id;
                }

                $result_models = DB::table('models')
                    ->select('id', 'model_name')
                    // ->whereIn('id', $model)
                    ->where('id', $result->model_id)
                    ->get();
            } else {
                $result_models = "";
            }
        } else {
            $result_models = "";
        }
        // $models = DB::table('models')->select('id','model_name')->where('dealer_id',$result->dealer_id)->get();
        // dd($models);
        $advisors = DB::table('advisors')->select('id', 'name')->where('dealer_id', $result->dealer_id)->where('status', 1)->get();
        $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->where(['id' => $result->dealer_id, 'status' => 1])->orderBy('name')->get();
        return view('admin.editJob', [
            'result' => $result,
            'users' => $users,
            'dealers' => $dealers,
            'treatments' => $treatments,
            'result_models' => $result_models,
            'advisors' => $advisors,
            'selectTreatment' => $selectTreatment,
        ]);
    }
    // update existing job
    public function updateJob(Request $request)
    {
        //dd(Request::server('HTTP_REFERER'));
        $post = $request->all();
        // dd($post['treatment_id']);
        $job_id  =  $request->id;
        $actual_price = 0;
        // $discount_price = 0;
        $difference_price = 0;
        $customer_price = 0;
        $dealer_price = 0;
        $incentive = 0;
        $this->validate(
            $request,
            [
                'dealer_id' => 'required',
                'treatment_id' => 'required',
                'advisor_id' => 'required',
                'model_id' => 'required',
                'job_date' => 'required|date',
                'job_card_no' => 'required',
                'bill_no' => 'required',
                'regn_no' => 'required',
            ],
            [
                'model_id.required' => 'Please select model',
                'advisor_id.required' => 'Please select advisor',
                'treatment_id.required' => 'Please select treatment',
                'dealer_id.required' => 'Please select dealer',
                'job_date.required' => 'Please select job date',
                'job_card_no.required' => 'Please enter job card no',
                'bill_no.required' => 'Please enter bill no',
                'regn_no.required' => 'Please enter registration no',
            ]
        );

        $selectedTreatments = array();
        for ($i = 0; $i < count($post['treatment_id']); $i++) {
            $data['id'] = $post['treatment_id'][$i];
            $data['dealer_price'] = $post['dealer_price'][$i];
            $data['customer_price'] = $post['customer'][$i];
            // $data['incentive'] = $post['incentive'][$i];
            $data['job_type'] = $post['job_type'][$i];
            $data['actualPrice'] = @$post['actualPrice'][$i];
            // $data['discountPrice'] = @$post['discountPrice'][$i];
            $data['difference'] = $post['difference'][$i];
            $selectedTreatments[] = $data;
        }

        $treatment_id = array();
        $treatment_data = array();
        $i = $hvt_value = 0;
        foreach ($selectedTreatments as $value) {
            $data1 = DB::table('treatments')->where('id', $value['id'])->first();
            if ($value['job_type'] == 5) {
                $actual_price = $actual_price + $value['actualPrice'];
                // $discount_price = $discount_price + $value['discountPrice'];
                $difference_price = $difference_price + $value['difference'];
            } else {
                $actual_price = $actual_price + 0;
                // $discount_price = $discount_price + 0;
                $difference_price = $difference_price + 0;
            }
            $customer_price = $customer_price + $value['customer_price'];
            $dealer_price = $dealer_price + $value['dealer_price'];
            // $incentive = $incentive + $value['incentive'];
            if ($data1->treatment_type == 1) {
                $i++;
                // $hvt_value = $hvt_value + $value['customer_price'];
                if ($value['job_type'] == 5) {
                    $hvt_value = $hvt_value + $value['actualPrice'];
                } else {
                    $hvt_value = 0;
                }
            }
            $treat_id['id'] = $value['id'];
            $treat_id['treatment'] = $data1->treatment;
            $treat_id['treatment_type'] = $data1->treatment_type;
            $treat_id['customer_price'] = $value['customer_price'];
            $treat_id['dealer_price'] = $value['dealer_price'];
            // $treat_id['incentive'] = $value['incentive'];
            $treat_id['job_type'] = $value['job_type'];
            $treat_id['actualPrice'] = $value['actualPrice'];
            // $treat_id['discountPrice'] = $value['discountPrice'];
            $treat_id['difference'] = $value['difference'];
            $treat_id['labour_code'] = $data1->labour_code;
            $treatment_data[] = $treat_id;
            $treatment_id[] = $treat_id['id'];
        }

        // $checkCardNo = DB::table('jobs')->where('job_card_no',$request->job_card_no)->where('dealer_id',$request->dealer_id)->where('id','!=',$job_id)->where('delete_job',1)->get();
        // if(!empty($request->bill_no)){
        //           $checkBillNo = DB::table('jobs')->where('delete_job',1)->where('bill_no',$request->bill_no)->where('id','!=',$job_id)->get();
        //         }else{
        //           $checkBillNo = array();
        //         }
        //         if(!empty($request->regn_no)){
        //            $checkRegnNo = DB::table('jobs')->where('delete_job',1)->where('regn_no',$request->regn_no)->where('id','!=',$job_id)->where('job_date',$request->job_date)->get();
        //         }else{
        //           $checkRegnNo = array();
        //         }
        // $error = array();
        // if(count($checkCardNo) > 0)
        // {
        // $error[] = 'This Job card no. has already added.';
        // }
        // if(count($checkBillNo)>0)
        // {
        // $error[] = 'This Bill no. has already added.';
        // }
        // if(count($checkRegnNo)>0)
        // {
        // $error[] = 'This Regn no. has already added.';
        // }
        // if(count($error) > 0)
        // {                  
        //          Session::flash('addComErrmsg', $error);
        //          Session::flash('alert-class', 'alert-danger');
        //        return back()->with('error',$error);
        // }else{
        $data = array(
            'user_id' => $request->user_id,
            'job_date' => $request->job_date,
            'job_card_no' => $request->job_card_no,
            'bill_no' => $request->bill_no,
            'regn_no' => trim($request->regn_no),
            'remarks' => $request->remarks,
            'dealer_id' => $request->dealer_id,
            'model_id' => $request->model_id,
            'advisor_id' => $request->advisor_id,
            'department_id' => getDealerDepartment($request->advisor_id),
            'remarks' => $request->remark,
            'treatments' => json_encode($treatment_data),
            'treatment_total' => count($treatment_id),
            'hvt_total' => $i,
            'hvt_value' => $hvt_value,
            'vas_total' => count($treatment_id),
            'vas_value' => $customer_price,
            'customer_price' => $customer_price,
            'actual_price' => $actual_price,
            // 'discount_price' => $discount_price,
            'difference_price' => $difference_price,
            'dealer_price' => $dealer_price,
            // 'incentive' => $incentive,
            'last_updated' => getCurrentTimestamp(),
            // 'foc_options' => $request->option,
        );
        // if (@$request->foc) {
        //     $data['foc'] = 1;
        // } else {
        //     $data['foc'] = 0;
        // }
        $result = DB::table('jobs')->where('id', $job_id)->update($data);
        DB::table('jobs_treatment')->where('job_id', $job_id)->delete();
        foreach ($treatment_id as $value) {
            $data = array(
                'job_id' => $job_id,
                'treatment_id' => $value,
            );
            DB::table('jobs_treatment')->insert($data);
        }
        Session::flash('success', 'Job Updated successfully!');
        if (Session::has('job_url')) {
            return redirect(Session::get('job_url'));
        } else {
            return redirect('/admin/jobs');
        }
        //}
    }
    //Job delete
    public function statusJob($status, $job_id)
    {
        if (@$status) {
            if ($status == "delete") {
                $udata['delete_job'] = 0;
                $udata['delete_at'] = getCurrentTimestamp();
                DB::table('jobs')->where('id', $job_id)->update($udata);
                DB::table('jobs_treatment')->where('job_id', $job_id)->update($udata);
                Session::flash('success', 'Job deleted successfully!');
                return redirect('/admin/jobs');
            }
        } else {
            Session::flash('error', 'Something wrong!');
            return redirect('/admin/jobs');
        }
    }

    // Delete multiple jobs
    public function deleteJobs(Request $request)
    {
        $post = $request->all();
        if (!empty($post['selectedId'])) {
            $ids = explode(',', $post['selectedId']);
            foreach ($ids as $key => $value) {
                $udata['delete_job'] = 0;
                $udata['delete_at'] = getCurrentTimestamp();
                DB::table('jobs')->where('id', $value)->update($udata);
                DB::table('jobs_treatment')->where('job_id', $value)->update($udata);
            }
            Session::flash('success', 'Jobs deleted successfully!');
            return redirect('/admin/jobs');
        } else {
            Session::flash('error', 'Please select job!');
            return redirect('/admin/jobs');
        }
    }

    public function jobsTreatmentList(Request $request)
    {
        $search = $request->search;
        $dealers = User::where('role', 2)->select('id as dealer_id', 'name as dealer_name')->where('status', 1)->orderBy('name')->get();
        if (@$request->job_type) {
            if ($request->job_type == 1) {
                $job_type = 1;
                $type = $request->job_type;
            } elseif ($request->job_type == 2) {
                $job_type = 2;
                $type = $request->job_type;
            } elseif ($request->job_type == 3) {
                $job_type = 3;
                $type = $request->job_type;
            } elseif ($request->job_type == 4) {
                $job_type = 4;
                $type = $request->job_type;
            } elseif ($request->job_type == 5) {
                $job_type = 5;
                $type = $request->job_type;
            }
        }

        $result = DB::table('jobs as j')
            ->select('j.*', 'j.id as job_id')
            ->where('delete_job', 1)
            ->orderBy('j.job_date', 'DESC')
            ->get();

        $jobs_treatments = array();
        foreach ($result as $k => $v) {
            foreach ($data = json_decode($v->treatments) as $key => $value) {
                $data[$key]->job_id = $v->job_id;
                $data[$key]->dealer_id = $v->dealer_id;
                $data[$key]->job_date = $v->job_date;
                $data[$key]->job_card_no = $v->job_card_no;
                $data[$key]->regn_no = $v->regn_no;
                $data[$key]->bill_no = $v->bill_no;
                $jobs_treatments[] = $value;
            }
        }
        $data = array();
        if (!empty($search) && !empty($request->job_type)) {
            foreach ($jobs_treatments as $key => $value) {
                if ($search == @$value->job_card_no && $request->job_type == @$value->job_type) {
                    $data[] = $value;
                } elseif ($search == @$value->bill_no && $request->job_type == @$value->job_type) {
                    $data[] = $value;
                } elseif ($search == @$value->regn_no && $request->job_type == @$value->job_type) {
                    $data[] = $value;
                }
            }
            $jobs_treatments = $data;
        } elseif (!empty($search) && empty($request->job_type)) {
            foreach ($jobs_treatments as $key => $value) {
                if ($search == @$value->job_card_no) {
                    $data[] = $value;
                } elseif ($search == @$value->bill_no) {
                    $data[] = $value;
                } elseif ($search == @$value->regn_no) {
                    $data[] = $value;
                }
            }
            $jobs_treatments = $data;
        } elseif (empty($search) && !empty($request->job_type)) {
            foreach ($jobs_treatments as $key => $value) {
                if ($request->job_type == @$value->job_type) {
                    $data[] = $value;
                }
            }
            $jobs_treatments = $data;
        }
        // $jobs_treatments = $this->paginate($jobs_treatments);
        if (request()->has('page')) {
            Session::put('job_url', url()->full());
        }
        return view('admin.jobsTreatmentList', [
            'result' => $jobs_treatments,
            'dealers' => $dealers,
            'regn_no' => @$regn_no,
            'job_type' => @$type,
            'search' => @$search,
        ]);
    }

    // pagination 
    // public function paginate($items, $perPage = 20, $page = null, $options = [])
    // {
    //     $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    //     $items = $items instanceof Collection ? $items : Collection::make($items);
    //     return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    // }

    // View history jobs
    public function history_jobs(Request $request)
    {
        // dd($request);
        $search = $request->search;
        $regn_no = $request->regn_no;
        $result = DB::table('history_jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->where('j.dealer_id', '=', $search);
                        }
                    }
                }
            })
            ->where(function ($query) use ($regn_no) {
                if (!empty($regn_no)) {
                    if (isset($regn_no)) {
                        if (!empty(trim($regn_no))) {
                            $query->where('j.regn_no', '=', $regn_no);
                        }
                    }
                }
            })
            ->orderBy('j.job_date', 'DESC')
            ->paginate(20);
        $dealers = DB::table('users')
            ->select('name', 'id')
            ->orderBy('name', 'ASC')
            ->where('role', 2)
            ->get();
        return view('admin.history_jobs', [
            'result' => $result,
            'dealers' => $dealers,
            'oldSupervisor' => @$search,
        ]);
    }
    // View upload history jobs page
    public function uploadJobHistory()
    {
        $dealers = DB::table('users')
            ->select('name as dealer_name', 'id as dealer_id')
            ->orderBy('name', 'ASC')
            ->where('role', 2)
            ->get();
        return view('admin.uploadJob', [
            'dealers' => $dealers,
        ]);
    }
    // Import history jobs from xlsx
    public function importJobsHistory(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'dealer_id' => 'required',
                'csv' => 'required|mimes:xlsx',
            ],
            [
                'dealer_id.required' => 'Please select dealer',
                'csv.required' => 'Please select model',
                'csv.mimes' => 'File must be type of xlsx',
            ]
        );
        if (Input::hasFile('csv')) {
            $path = Input::file('csv')->getRealPath();
            $data = Excel::load($path, function ($reader) {
            })->get();
            $data = json_decode(json_encode($data), true);
            //echo "<pre>"; print_r($data); die;
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    if (!empty($value['job_card'])) {
                        $insert[] = [
                            'dealer_id' => $post['dealer_id'],
                            'job_date' => date('Y-m-d', strtotime($value['job_date'])),
                            'job_card' => $value['job_card'],
                            'bill_no' => $value['bill_no'],
                            'regn_no' => $value['regn_no'],
                            'advisor' => $value['advisor'],
                            'model' => $value['model'],
                            'labour_code' => $value['labour_code'],
                            'treatment' => $value['treatment']
                        ];
                    }
                }
                //dd($insert);
                DB::table('history_jobs')->insert($insert);
                Session::flash('success', 'File uploaded successfully!');
                return redirect('/admin/history_jobs');
            } else {
                Session::flash('error', 'File not uploaded!');
                return redirect('/admin/uploadJobHistory');
            }
        }
        return back();
    }
    // Download dealer wise report for all dealers
    public function downloadAllDealerReport(Request $request)
    {
        $post = $request->all();
        $exp = explode('-', $post['getMonth']);
        $dealers = User::where('role', 2)
            ->where('id', '!=', 58)
            ->orderBy('name', 'ASC')
            ->get();
        if ($post['getReportType'] == 'dealer') {
            return Excel::create('Dealer_' . $post['getMonth'], function ($excel) use ($dealers, $exp) {
                foreach ($dealers as $dealer) {
                    $result = DB::table('jobs as j')
                        ->select('j.*')
                        ->whereMonth('j.job_date', $exp[1])
                        ->whereYear('j.job_date', $exp[0])
                        ->where('j.dealer_id', $dealer->id)
                        ->where('j.delete_job', 1)
                        ->orderBy('j.job_date', 'ASC')
                        ->get();
                    $array = array();
                    $result1 = array();
                    $customer_price = $dealer_price = $incentive = 0;
                    $excel->sheet(get_name($dealer->id), function ($sheet) use ($result, $customer_price, $dealer_price, $incentive) {
                        foreach ($result as $key => $value) {
                            $decoded = json_decode($value->treatments);
                            foreach ($decoded as $val) {
                                $customer_price = $customer_price + round($val->customer_price);
                                $dealer_price = $dealer_price + round($val->dealer_price);
                                $incentive = $incentive + round($val->incentive);
                            }
                        }
                        $sheet->setBorder('N1:P2');
                        $sheet->cells('N1', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->cells('O1', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->cells('P1', function ($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->setCellValue('N1', 'Customer_Price');
                        $sheet->setCellValue('N2', $customer_price);
                        $sheet->setCellValue('O1', 'Dealer_Price');
                        $sheet->setCellValue('O2', $dealer_price);
                        $sheet->setCellValue('P1', 'Incentive');
                        $sheet->setCellValue('P2', $incentive);
                        foreach ($result as $key => $value) {
                            $array['Job_Date'] = date("d-M-Y", strtotime($value->job_date));
                            $array['Job_Card_No'] = $value->job_card_no;
                            $array['Bill_No'] = $value->bill_no;
                            $array['Regn_No'] = $value->regn_no;
                            $array['Advisor'] = get_advisor_name($value->advisor_id);
                            $array['Model'] = get_model_name($value->model_id);
                            $decoded = json_decode($value->treatments);
                            foreach ($decoded as $val) {
                                $array['Labour_Code'] = $val->labour_code;
                                $array['Treatment'] = $val->treatment;
                                $array['Customer_Price'] = round($val->customer_price);
                                $array['Dealer_Price'] = round($val->dealer_price);
                                $array['Incentive'] = round($val->incentive);
                                $array['Remark'] = $value->remarks;
                                $result1[] = $array;
                            }
                        }
                        $sheet->fromArray(@$result1);
                    });
                }
            })->export('xlsx');
        } elseif ($post['getReportType'] == 'advisor') {
            return Excel::create('Advisor_' . $post['getMonth'], function ($excel) use ($dealers, $exp) {
                foreach ($dealers as $dealer) {
                    $data = DB::table('jobs')
                        ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(hvt_value) as hvt_customer_price,  SUM(incentive) as vas_incentive, advisor_id, job_date'))
                        ->whereMonth('job_date', $exp[1])
                        ->whereYear('job_date', $exp[0])
                        ->where('dealer_id', $dealer->id)
                        ->where('delete_job', 1)
                        ->groupBy('advisor_id')
                        ->get();
                    $advisors = array();
                    $i = $mtd_total = 0;
                    if (count($data) > 0) {
                        foreach ($data as $value) {
                            $hvt_incentive = 0;
                            $decoded_jobs = explode(',', $value->job_id);
                            foreach ($decoded_jobs as $key => $val) {
                                $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                                $decoded_treatments = json_decode($treat->treatments);
                                foreach ($decoded_treatments as $key => $val1) {
                                    if ($val1->treatment_type == 1) {
                                        $hvt_incentive = $hvt_incentive + $val1->incentive;
                                    }
                                }
                            }
                            $advisor['advisor_id'] = $value->advisor_id;
                            $advisor['vas_customer_price'] = round($value->vas_customer_price);
                            $advisor['vas_incentive'] = round($value->vas_incentive);
                            $advisor['hvt_customer_price'] = round($value->hvt_customer_price);
                            $advisor['hvt_incentive'] = round($hvt_incentive);
                            $advisors[] = $advisor;
                            @$total_service = DB::table('jobs_by_date')
                                ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                                ->whereMonth('job_added_date', $exp[1])
                                ->whereYear('job_added_date', $exp[0])
                                ->where('dealer_id', $dealer->id)
                                ->first();
                            @$total_jobs = DB::table('jobs')
                                ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                                ->whereMonth('job_date', $exp[1])
                                ->whereYear('job_date', $exp[0])
                                ->where('dealer_id', $dealer->id)
                                ->where('delete_job', 1)
                                ->first();
                            $total_job_array = array(
                                'mtd_total' => round(@$total_service->mtd_total),
                                'mtd_vas_value' => round(@$total_jobs->mtd_vas_value),
                                'mtd_vas_total' => round(@$total_jobs->mtd_vas_total),
                                'mtd_hvt_value' => round(@$total_jobs->mtd_hvt_value),
                                'mtd_hvt_total' => round(@$total_jobs->mtd_hvt_total),
                            );
                            $i++;
                        }
                    }
                    $finalAdvisor = array();
                    $array = array();
                    foreach ($advisors as $value) {
                        $array['Advisor'] = get_advisor_name($value['advisor_id']);
                        $array['Vas_Customer_Price'] = round($value['vas_customer_price']);
                        $array['Vas_incentive'] = round($value['vas_incentive']);
                        $array['HVT_Customer_Price'] = round($value['hvt_customer_price']);
                        $array['HVT_Incentive'] = round($value['hvt_incentive']);
                        $finalAdvisor[] = $array;
                    }
                    $excel->sheet(get_name($dealer->id), function ($sheet) use ($finalAdvisor, $total_job_array) {
                        $sheet->setBorder('G1:H10');
                        $sheet->setCellValue('G1', 'Monthly Treatments till Date');
                        $sheet->mergeCells("G1:H1");
                        $sheet->setCellValue('G2', 'RO');
                        $sheet->setCellValue('H2', $total_job_array['mtd_total']);
                        $sheet->setCellValue('G3', 'VAS');
                        $sheet->mergeCells("G3:H3");
                        $sheet->setCellValue('G4', 'No of Trmt');
                        $sheet->setCellValue('H4', $total_job_array['mtd_vas_total']);
                        $sheet->setCellValue('G5', 'Amount');
                        $sheet->setCellValue('H5', $total_job_array['mtd_vas_value']);
                        $sheet->setCellValue('G6', 'Value Per Treatment');
                        $sheet->setCellValue('H6', vas_in_percentage(@$total_job_array['mtd_vas_value'], @$total_job_array['mtd_vas_total']));
                        $sheet->setCellValue('G7', 'HVT');
                        $sheet->mergeCells("G7:H7");
                        $sheet->setCellValue('G8', 'No of Trmt');
                        $sheet->setCellValue('H8', $total_job_array['mtd_hvt_total']);
                        $sheet->setCellValue('G9', 'Amount');
                        $sheet->setCellValue('H9', $total_job_array['mtd_hvt_value']);
                        $sheet->setCellValue('G10', 'HVT %');
                        $sheet->setCellValue('H10', hvt_in_percentage(@$total_job_array['mtd_hvt_value'], @$total_job_array['mtd_vas_value']));
                        $sheet->fromArray($finalAdvisor);
                    });
                }
            })->export('xlsx');
        } else {
            return redirect('/admin/reports');
        }
    }
    // List of product brands
    public function productBrands(Request $request)
    {
        $search = $request->search;
        $brands = DB::table('product_brands')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('brand_name', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->paginate(20);
        return view('admin.productBrands', [
            'result' => $brands,
        ]);
    }

    // view add new Product Brand page
    public function addProductBrand()
    {
        return view('admin.addProductBrand');
    }
    // save new Product Brand
    public function insertProductBrand(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'brand_name' => 'required|unique:product_brands,brand_name',
            ],
            [
                'brand_name.required' => 'Please enter brnad name'
            ]
        );
        $data = array(
            'brand_name' => $post['brand_name'],
            'created_at' => getCurrentTimestamp()
        );
        DB::table('product_brands')->insert($data);
        Session::flash('success', 'Product brand added successfully!');
        return redirect('/admin/product_brands');
    }
    // view edit Product page
    public function editProductBrand($id)
    {
        $result = DB::table('product_brands')->where('id', $id)->first();
        if (!empty($result)) {
            return view('admin.editProductBrand', [
                'result' => $result
            ]);
        } else {
            Session::flash('error', 'No product brand found!');
            return redirect('/admin/product_brands');
        }
    }

    // update existing Product
    public function updateProductBrand(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'brand_name' => 'required|unique:product_brands,brand_name,' . $post['id'],
            ],
            [
                'name.required' => 'Please enter brand name'
            ]
        );
        $data = array(
            'brand_name' => $post['brand_name'],
            'updated_at' => getCurrentTimestamp()
        );
        DB::table('product_brands')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Product brand updated successfully!');
        return redirect('/admin/product_brands');
    }
    //Change Product status or delete
    public function statusProductBrand($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('product_brands')->where('id', $id)->update($udata);
                Session::flash('success', 'Product brand deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('product_brands')->where('id', $id)->update($udata);
                Session::flash('success', 'Product brand activated successfully!');
            } else if ($status == "delete") {
                DB::table('product_brands')->where('id', $id)->delete();
                Session::flash('success', 'Product brand deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/product_brands');
    }

    // List of products
    public function products(Request $request)
    {
        $search = $request->search;
        $brand_id = $request->brand_id;
        $brands = DB::table('product_brands')->where('status', 1)->get();
        $products = DB::table('products')
            ->where(function ($query) use ($search, $brand_id) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                        }
                    }
                }
                if (!empty($brand_id)) {
                    if (isset($brand_id)) {
                        if (!empty(trim($brand_id))) {
                            $query->Where('brand_id', $brand_id);
                        }
                    }
                }
            })
            ->paginate(20);
        return view('admin.products', [
            'result' => $products,
            'brands' => $brands,
            'search' => $search,
            'brand_id' => $brand_id,
        ]);
    }

    // view add new Product page
    public function addProduct()
    {
        $brands = DB::table('product_brands')->where('status', 1)->get();
        return view('admin.addProduct', compact('brands'));
    }
    // save new Product
    public function insertProduct(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'quantity' => 'required|numeric',
                'uom' => 'required',
                'price' => 'required',
                'brand_id' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'quantity.required' => 'Please enter quantity',
                'uom.required' => 'Please select unit of measurement',
                'price.required' => 'Please enter price',
                'brand_id.required' => 'Please select brand',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'quantity' => $post['quantity'],
            'uom' => $post['uom'],
            'price' => $post['price'],
            'brand_id' => $post['brand_id'],
        );
        DB::table('products')->insert($data);
        Session::flash('success', 'Product added successfully!');
        return redirect('/admin/products');
    }
    // view edit Product page
    public function editProduct($id)
    {
        $result = DB::table('products')->where('id', $id)->first();
        $brands = DB::table('product_brands')->where('status', 1)->get();
        if (!empty($result)) {
            return view('admin.editProduct', [
                'result' => $result,
                'brands' => $brands
            ]);
        } else {
            Session::flash('error', 'No product found!');
            return redirect('/admin/products');
        }
    }

    // update existing Product
    public function updateProduct(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'quantity' => 'required|numeric',
                'uom' => 'required',
                'price' => 'required',
                'brand_id' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'quantity.required' => 'Please enter quantity',
                'uom.required' => 'Please select unit of measurement',
                'price.required' => 'Please enter price',
                'brand_id.required' => 'Please select brand',
            ]
        );
        $data = array(
            'name' => $post['name'],
            'quantity' => $post['quantity'],
            'uom' => $post['uom'],
            'price' => $post['price'],
            'brand_id' => $post['brand_id'],
        );
        DB::table('products')->where('id', $post['id'])->update($data);
        Session::flash('success', 'Product updated successfully!');
        return redirect('/admin/products');
    }
    //Change Product status or delete
    public function statusProduct($status, $id)
    {
        if (@$status) {
            if ($status == "deactivate") {
                $udata['status'] = 0;
                DB::table('products')->where('id', $id)->update($udata);
                Session::flash('success', 'Product deactivated successfully!');
            } else if ($status == "activate") {
                $udata['status'] = 1;
                DB::table('products')->where('id', $id)->update($udata);
                Session::flash('success', 'Product activated successfully!');
            } else if ($status == "delete") {
                DB::table('products')->where('id', $id)->delete();
                Session::flash('success', 'Product deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/products');
    }

    // Attendance 
    public function attendance(Request $request)
    {

        // dd($request->all());
        $search = $request->all();
        if (!empty($search['employee']) && !empty($search['employeecode'])) {
            return back()->with('error', "You can't select both Employee and Employee Code together. Please Select only one.");
        }

        $dealers = User::where('role', 2)->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
        $employees = User::whereIn('role', [3, 4, 5])->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
        $empcodes = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->select('sd.user_id', 'sd.emp_code')->where('users.status', 1)->where('sd.dol', '=', null)->orderBy('sd.emp_code', 'ASC')->get();

        if (isset($search['dealer']) && !empty(isset($search['dealer']))) {
            if (isset($search['employee']) || isset($search['employeecode'])) {
                $attendance = DB::table('attendance')
                    ->where(function ($query) use ($search) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['employee'])) {
                                if (!empty(trim($search['employee']))) {
                                    $query->where('user_id', '=', $search['employee']);
                                }
                            }
                            if (isset($search['employeecode'])) {
                                if (!empty(trim($search['employeecode']))) {
                                    $query->where('user_id', '=', $search['employeecode']);
                                }
                            }
                            if (isset($search['start_date']) && isset($search['end_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['end_date'])) {
                                if (!empty(trim($search['end_date']))) {
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['start_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    })->orderBy('date', 'ASC')->get();


                $dates = DB::table('attendance')
                    ->where(function ($query) use ($search) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['employee'])) {
                                if (!empty(trim($search['employee']))) {
                                    $query->where('user_id', '=', $search['employee']);
                                }
                            }
                            if (isset($search['employeecode'])) {
                                if (!empty(trim($search['employeecode']))) {
                                    $query->where('user_id', '=', $search['employeecode']);
                                }
                            }
                            if (isset($search['start_date']) && isset($search['end_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['end_date'])) {
                                if (!empty(trim($search['end_date']))) {
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['start_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    })->groupBy('date')->get();

                $attendance1 = array();
                foreach ($dates as $key => $value) {
                    $id = searchForDate($value->date, $attendance);
                    $attendance1[] =  $id;
                }
                $attendance = $attendance1;

                $view = 2;
            } else {
                $attendance = DB::table('attendance')
                    ->where(function ($query) use ($search) {
                        if (!empty($search)) {
                            if (isset($search['dealer'])) {
                                if (!empty(trim($search['dealer']))) {
                                    $query->where('dealer_id', '=', $search['dealer']);
                                }
                            }
                            if (isset($search['employee'])) {
                                if (!empty(trim($search['employee']))) {
                                    $query->where('user_id', '=', $search['employee']);
                                }
                            }
                            if (isset($search['employeecode'])) {
                                if (!empty(trim($search['employeecode']))) {
                                    $query->where('user_id', '=', $search['employeecode']);
                                }
                            }
                            if (isset($search['start_date']) && isset($search['end_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['end_date'])) {
                                if (!empty(trim($search['end_date']))) {
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['start_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    })->groupBy('user_id')->get();

                $view = 1;
            }
        } elseif (isset($search['employee']) || isset($search['employeecode'])) {
            $attendance = DB::table('attendance')
                ->where(function ($query) use ($search) {
                    if (!empty($search)) {
                        if (isset($search['dealer'])) {
                            if (!empty(trim($search['dealer']))) {
                                $query->where('dealer_id', '=', $search['dealer']);
                            }
                        }
                        if (isset($search['employee'])) {
                            if (!empty(trim($search['employee']))) {
                                $query->where('user_id', '=', $search['employee']);
                            }
                        }
                        if (isset($search['employeecode'])) {
                            if (!empty(trim($search['employeecode']))) {
                                $query->where('user_id', '=', $search['employeecode']);
                            }
                        }
                        if (isset($search['start_date']) && isset($search['end_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['end_date'])) {
                            if (!empty(trim($search['end_date']))) {
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['start_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('date', date("Y-m-d"));
                    }
                })->orderBy('date', 'ASC')->get();


            $dates = DB::table('attendance')
                ->where(function ($query) use ($search) {
                    if (!empty($search)) {
                        if (isset($search['dealer'])) {
                            if (!empty(trim($search['dealer']))) {
                                $query->where('dealer_id', '=', $search['dealer']);
                            }
                        }
                        if (isset($search['employee'])) {
                            if (!empty(trim($search['employee']))) {
                                $query->where('user_id', '=', $search['employee']);
                            }
                        }
                        if (isset($search['employeecode'])) {
                            if (!empty(trim($search['employeecode']))) {
                                $query->where('user_id', '=', $search['employeecode']);
                            }
                        }
                        if (isset($search['start_date']) && isset($search['end_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['end_date'])) {
                            if (!empty(trim($search['end_date']))) {
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['start_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('date', date("Y-m-d"));
                    }
                })->groupBy('date')->get();


            foreach ($dates as $key => $value) {
                $id = searchForDate($value->date, $attendance);
                $attendance1[] =  $id;
            }
            $attendance = $attendance1;
            $view = 2;
        } else {

            $attendance = DB::table('attendance')
                ->where(function ($query) use ($search) {
                    if (!empty($search)) {
                        if (isset($search['dealer'])) {
                            if (!empty(trim($search['dealer']))) {
                                $query->where('dealer_id', '=', $search['dealer']);
                            }
                        }
                        if (isset($search['employee'])) {
                            if (!empty(trim($search['employee']))) {
                                $query->where('user_id', '=', $search['employee']);
                            }
                        }
                        if (isset($search['employeecode'])) {
                            if (!empty(trim($search['employeecode']))) {
                                $query->where('user_id', '=', $search['employeecode']);
                            }
                        }
                        if (isset($search['start_date']) && isset($search['end_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['end_date'])) {
                            if (!empty(trim($search['end_date']))) {
                                $query->whereDate('date', '<=', $search['end_date']);
                            }
                        } elseif (isset($search['start_date'])) {
                            if (!empty(trim($search['start_date']))) {
                                $query->whereDate('date', '>=', $search['start_date']);
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    } else {
                        $query->whereDate('date', date("Y-m-d"));
                    }
                })->groupBy('user_id')->orderBy('date', 'ASC')->get();

            $view = 0;
        }


        if (@$search['download']) {
            return Excel::create('Attendance_' . date("d-M-Y"), function ($excel) use ($attendance, $view, $search) {
                $excel->sheet('sheet', function ($sheet) use ($attendance, $view, $search) {
                    $result = array();
                    if ($view == 0) {

                        if (isset($search['end_date']) && isset($search['start_date']) && !empty($search['start_date']) && !empty($search['end_date'])) {

                            if ($search['start_date'] != $search['end_date']) {
                                foreach ($attendance as $key => $value) {
                                    $data['S. No.'] = $key + 1;
                                    $data['Emp Name'] = get_name($value->user_id);
                                    $data['Emp Code'] = get_emp_code($value->user_id);
                                    $data['Dealer'] = get_name($value->user_id);

                                    $total_hours = total_hours_range($value->user_id, $_GET['start_date'], $_GET['end_date']);
                                    $data['Total Hours'] = $total_hours['hours'] . 'hours ' . $total_hours['minutes'] . 'minutes ';

                                    $data['Full Days'] = full_days_in_range($value->user_id, $search['start_date'], $search['end_date']) ? full_days_in_range($value->user_id, $search['start_date'], $search['end_date']) : "0";

                                    $data['Half Days'] =
                                        half_days_in_range($value->user_id, $search['start_date'], $search['end_date'])
                                        ?
                                        half_days_in_range($value->user_id, $search['start_date'], $search['end_date'])
                                        : "0";

                                    $data['Absent Days'] = absent_days_in_range($value->user_id, $search['start_date'], $search['end_date']) ? absent_days_in_range($value->user_id, $search['start_date'], $search['end_date']) : "0";

                                    $data['Attendance Not Marked'] = notmarked_days_in_range($value->user_id, $search['start_date'], $search['end_date']) ? notmarked_days_in_range($value->user_id, $search['start_date'], $search['end_date']) : "0";

                                    $data['Sales Report Not Filled'] = salesreportnotfilled_days_in_range($value->user_id, $_GET['start_date'], $_GET['end_date']) ? salesreportnotfilled_days_in_range($value->user_id, $_GET['start_date'], $_GET['end_date']) : "0";

                                    $result[] = $data;
                                }
                            } else {
                                foreach ($attendance as $key => $value) {
                                    $data['Date'] = date('d M, Y', strtotime($value->date));
                                    $data['In Time'] = @$value->in_time;
                                    $data['Out Time'] = @$value->out_time;
                                    $data['Staff Member'] = get_name($value->user_id);
                                    $data['Dealer'] = get_name($value->dealer_id);
                                    $timeFirst = strtotime($value->in_time);
                                    $timeSecond = strtotime($value->out_time);
                                    if (!empty($value->in_time) && !empty($value->out_time)) {
                                        @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                        $data['This Dealer Hours'] = @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes';
                                    } else {
                                        $data['This Dealer Hours'] = '0 hours ' . '0 minutes';
                                    }
                                    @$data_total_hours = day_hours($value->user_id, $value->date);
                                    $data['Total Hours Today'] = @$data_total_hours['hours'] . ' hours ' .
                                        @$data_total_hours['minutes'] . ' minutes';

                                    $month = ((@$_GET['selectMonth']) ? date('m', strtotime(@$_GET['selectMonth'])) : date('m'));

                                    @$mydata1 = month_hours_date($value->user_id, $search['start_date'], $search['end_date']);

                                    // $data['Total Hours This Month'] = @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes';

                                    $result[] = $data;
                                }
                            }
                        } else {

                            foreach ($attendance as $key => $value) {
                                $data['Date'] = date('d M, Y', strtotime($value->date));
                                $data['In Time'] = @$value->in_time;
                                $data['Out Time'] = @$value->out_time;
                                $data['Staff Member'] = get_name($value->user_id);
                                $data['Dealer'] = get_name($value->dealer_id);
                                $timeFirst = strtotime($value->in_time);
                                $timeSecond = strtotime($value->out_time);
                                if (!empty($value->in_time) && !empty($value->out_time)) {
                                    @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                    $data['This Dealer Hours'] = @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes';
                                } else {
                                    $data['This Dealer Hours'] = '0 hours ' . '0 minutes';
                                }
                                @$data_total_hours = day_hours($value->user_id, $value->date);
                                $data['Total Hours Today'] = @$data_total_hours['hours'] . ' hours ' .
                                    @$data_total_hours['minutes'] . ' minutes';

                                $month = ((@$_GET['selectMonth']) ? date('m', strtotime(@$_GET['selectMonth'])) : date('m'));

                                @$mydata1 = month_hours_date($value->user_id, $search['start_date'], $search['end_date']);

                                // $data['Total Hours This Month'] = @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes';

                                $result[] = $data;
                            }
                        }
                    } elseif ($view == 1) {
                        foreach ($attendance as $key => $value) {
                            $data['S. No.'] = $key + 1;
                            $data['Staff Member'] = get_name($value->user_id);
                            $data['Emp Code'] = get_emp_code($value->user_id);
                            $data['Dealer'] = get_name($value->dealer_id);
                            $timeFirst = strtotime($value->in_time);
                            $timeSecond = strtotime($value->out_time);

                            $month = ((@$_GET['selectMonth']) ? date('m', strtotime(@$_GET['selectMonth'])) : date('m'));

                            @$mydata1 =
                                month_hours_date($value->user_id, $search['start_date'], $search['end_date']);

                            $data['Total Hours This Month'] = @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes';

                            $result[] = $data;
                        }
                    } elseif ($view == 2) {
                        $result = array();
                        $file = array();

                        $i = 2;
                        $three = 0;
                        foreach ($data = $attendance as $key1 => $value1) {

                            $row = count($data[$key1]);
                            foreach ($data[$key1] as $key => $value) {
                                $file['Date'] = date('d M, Y', strtotime($data[$key1][0]->date));
                                if ($row > 1) {
                                    if ($key == 0) {
                                        $end = $i + $row - 1;
                                        $sheet->mergeCells('A' . $i . ':' . 'A' . $end);
                                        $sheet->mergeCells('H' . $i . ':' . 'H' . $end);
                                        $sheet->cells('A' . $i, function ($cells) {
                                            $cells->setValignment('center');
                                        });
                                        $sheet->cells('H' . $i, function ($cells) {
                                            $cells->setValignment('center');
                                        });
                                    }
                                }

                                if (isset($value->user_id)) {
                                    $myid = $value->user_id;
                                    $file['In Time'] = $value->in_time;
                                    $file['Out Time'] = $value->out_time;
                                    $file['User'] = get_name($value->user_id);
                                    $file['Employee Code'] = get_emp_code($value->user_id);
                                    $file['Dealer'] = get_name($value->dealer_id);
                                    $timeFirst = strtotime($value->in_time);
                                    $timeSecond = strtotime($value->out_time);
                                    if (!empty($timeFirst) && !empty($timeSecond)) {
                                        @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                        $file['This Dealer Hours'] =  @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes';
                                    } else {
                                        $file['This Dealer Hours'] = 'Attendance Not Marked';
                                    }


                                    if (!empty($timeFirst) && !empty($timeSecond)) {
                                        @$data_total_hours = day_hours($value->user_id, $value->date);
                                        $file['Total Hours Today'] = @$data_total_hours['hours'] . ' hours ' .
                                            @$data_total_hours['minutes'] . ' minutes';
                                    } else {
                                        $file['Total Hours Today'] = 'Attendance Not Marked';
                                    }

                                    $range = 'A' . $i . ':' . 'H' . $i;
                                    $sheet->setBorder($range);
                                    $sheet->cells($range, function ($cells) {
                                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                    });


                                    $i = $i + 1;
                                } else {
                                    $range = 'A' . $i . ':' . 'H' . $i;
                                    $sheet->setBorder($range);
                                    $sheet->cells($range, function ($cells) {
                                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                    });

                                    $sheet->cells('B' . $i . ':' . 'H' . $i, function ($cells) {
                                        $cells->setBackground('#dd4b39');
                                    });

                                    $sheet->mergeCells('B' . $i . ':' . 'H' . $i);
                                    $sheet->cell('B' . $i . ':' . 'H' . $i, function ($cell) {
                                        $cell->setAlignment('center');
                                    });
                                    $i = $i + 1;

                                    $file['In Time'] = 'Absent';
                                    $file['Out Time'] = 'Absent';
                                    $file['User'] = 'Absent';
                                    $file['Dealer'] = 'Absent';
                                    $file['This Dealer Hours'] = 'Absent';
                                    $file['Total Hours Today'] = 'Absent';
                                }
                                $result[] = $file;
                            }
                        }
                    }


                    $sheet->fromArray($result);
                });
            })->export('xlsx');
        }

        return view('admin.attendance', [
            'result' => $attendance,
            'dealers' => $dealers,
            'employees' => $employees,
            'empcodes' => $empcodes,
            'dealer' => @$search['dealer'],
            'emp' => @$search['employee'],
            'empcode' => @$search['employeecode'],
            'start_date' => @$search['start_date'],
            'end_date' => @$search['end_date'],
            'view'  =>  $view
        ]);
    }

    // View ASM list
    // public function asm(Request $request)
    // {
    //     $data = User::where('role',5)->paginate(10);
    //     return view('admin.asm',[
    //         'result' => $data->appends(Input::except('page')),
    //     ]);
    // }

    // view add new ASM page
    // public function addASM()
    // {
    //     return view('admin.addASM');
    // }

    // save new ASM
    // public function insertASM(Request $request)
    // {

    //     $post = $request->all();
    //     $check = session()->get('page');
    //     $this->validate(
    //         $request, [ 
    //             'name' => 'required',
    //             'mobile_no' => 'required|digits:10|unique:users,mobile_no',
    //             'email' => 'required|unique:users,email',
    //             'password' => 'required',
    //         ],
    //         [
    //             'name.required' => 'Please enter name',
    //             'email.required' => 'Please enter email',
    //             'mobile_no.required' => 'Please enter mobile no.',
    //             'password.required' => 'Please enter password.',
    //         ]
    //     );
    //     $data = array(
    //         'role' => 5,
    //         'name' => $post['name'],
    //         'email'=> $post['email'],
    //         'mobile_no' => $post['mobile_no'],
    //         'password' => Hash::make($post['password']),  
    //     );
    //     User::insert($data);
    //     Session::flash('success', 'ASM added successfully!');
    //     if(!empty($check)){
    //         return redirect('/admin/asm?page='.$check);
    //     } else{
    //         return redirect('/admin/asm');
    //     }
    // }

    //Change ASM status or delete
    // public function statusASM($status,$id)
    // {
    //     $check = session()->get('page');
    //     if(@$status){
    //         if($status == "deactivate"){               
    //             $udata['status'] = 0;
    //             User::where('id',$id)->update($udata);
    //             Session::flash('success', 'ASM deactivated successfully!');
    //         }else if($status == "activate"){                
    //             $udata['status'] = 1;
    //             User::where('id',$id)->update($udata);
    //             Session::flash('success', 'ASM activated successfully!');
    //         }else if($status == "delete"){
    //             User::where('id', $id)->delete();
    //             Session::flash('success', 'ASM deleted successfully!');
    //         }
    //     }else{
    //         Session::flash('error', 'Something wrong!');
    //     }
    //     if(!empty($check)){
    //         return redirect('/admin/asm?page='.$check);
    //     } else{
    //         return redirect('/admin/asm');
    //     }
    // }

    // view edit ASM page
    // public function editASM($id)
    // {
    //     $user = User::where(['id'=>$id, 'role'=>5])->first();
    //     if(!empty($user))
    //     {
    //         return view('admin.editASM',[
    //             'user' => $user,
    //         ]);
    //     }else{
    //         Session::flash('error', 'No ASM found!');
    //         return redirect('/admin/editASM');
    //     }  
    // }

    // update existing ASM
    // public function updateASM(Request $request)
    // {
    //     $check = session()->get('page');
    //     $post = $request->all();
    //     $this->validate(
    //         $request, [ 
    //             'name' => 'required',
    //             'mobile_no' => 'required|digits:10|unique:users,mobile_no,'. $post['asm_id'],
    //             'email' => 'required|unique:users,email,'. $post['asm_id'],
    //             'password' => 'required',
    //         ],
    //         [
    //             'name.required' => 'Please enter name',
    //             'email.required' => 'Please enter email',
    //             'mobile_no.required' => 'Please enter mobile no.',
    //             'password.required' => 'Please enter password.',
    //         ]
    //     );
    //     $data = array(
    //         'role' => 5,
    //         'name' => $post['name'],
    //         'email'=> $post['email'],
    //         'mobile_no' => $post['mobile_no'],
    //         'password' => Hash::make($post['password']),  
    //     );
    //     User::where('id',$post['asm_id'])->update($data);
    //     Session::flash('success', 'ASM updated successfully!');
    //     if(!empty($check)){
    //         return redirect('/admin/asm?page='.$check);
    //     } else{
    //         return redirect('/admin/asm');
    //     }
    // }

    // view ASM's Sales Executive listing
    // public function asm_SalesExecutiveListing(Request $request, $asm_id)
    // {
    //     $search = $request->search;
    //     $result = DB::table('emp_hierarchy')->join('staff_detail', 'staff_detail.user_id', '=', 'emp_hierarchy.user_id')->join('users', 'users.id', '=', 'emp_hierarchy.user_id')->where(['emp_hierarchy.asm_id'=>$asm_id, 'staff_detail.designation_id'=>14])
    //         ->where(function($query){
    //             $query->where('emp_hierarchy.status',1);
    //             $query->orWhere('emp_hierarchy.status',2);
    //         })
    //         ->where(function($query) use ($search){
    //             if(!empty($search)){
    //                 if(isset($search)){
    //                     if(!empty(trim($search))){
    //                        $query->orWhere('users.name','like','%'.$search.'%');
    //                        $query->orWhere('users.mobile_no','like','%'.$search.'%');  
    //                     }
    //                 }
    //             }
    //         })
    //         ->paginate(10);
    //     return view('admin.asm_SalesExecutiveListing',[
    //         'result' => $result->appends(Input::except('page')),
    //         'asm_id' => $asm_id,
    //     ]);
    // }

    public function relax_attendance()
    {
        // $data = DB::table('users')->whereIn('role', [2, 6])->get();
        // foreach ($data as $key => $value) {
        //     DB::table('timings')->insert([
        //         'user_id' => $value->id,
        //         'start_time' => date("H:i:s", strtotime("07:00:00")),
        //         'end_time' => date("H:i:s", strtotime("19:00:00")),
        //         'hour_diff' => "1",
        //         'minute_diff' => "1",
        //     ]);
        // }
        // dd("ghfjh");
        return view('admin.addrelaxation');
    }
    public function addrelaxation(Request $request)
    {

        $request->validate([
            'relax_time' => "required",
        ]);
        // dd($request->relax_time);
        DB::table('timings')->update(['relax_time' => $request->relax_time]);
        return back()->with('success', 'Relaxation Time Updated for all Dealers');
    }

    public function late_attendance()
    {

        // $dealers = DB::table('users')->whereIn('role', [2, 6])->get();
        // // $data = array();

        // // foreach ($dealers as $key => $value) {

        // $relax_time =   DB::table('timings')->first();
        // // dd($relax_time->relax_time);
        // $data = DB::table('attendance')
        //     ->whereDate('date', getCurrentDate())
        //     // ->where('dealer_id', $value->id)
        //     // ->whereTime('in_time', '>=', $relax_time->start_time)
        //     // ->whereTime('in_time', '<', date("H:i:s", strtotime('+' . $relax_time->relax_time . ' minutes', strtotime($relax_time->start_time))))
        //     ->get();
        // // }



        // $atten = DB::table('attendance')->whereDate('date', getCurrentDate())->get();
        // foreach ($atten as $key => $value) {
        //     $late1 = 0;
        //     $timming = DB::table('timings')->where('user_id', $value->dealer_id)->first();
        //     // dd($timming);
        //     $late1 = DB::table('attendance')
        //         ->whereDate('date', getCurrentDate())
        //         ->where('dealer_id', $value->id)
        //         ->whereTime('in_time', '>', $timming->start_time)
        //         ->whereTime('in_time', '<', date("H:i:s", strtotime('+' . $timming->relax_time . ' minutes', strtotime($timming->start_time))))
        //         ->get();
        // }
        // dd($late1);


        // $timming = DB::table('timings')->first();
        // $late1 = DB::table('attendance')
        //     ->whereDate('date', getCurrentDate())
        //     // ->where('dealer_id', $value->id)
        //     ->whereTime('in_time', '>', $timming->start_time)
        //     ->whereTime('in_time', '<', date("H:i:s", strtotime('+' . $timming->relax_time . ' minutes', strtotime($timming->start_time))))
        //     ->get();
        // $late2 = DB::table('attendance')
        //     ->whereDate('date', getCurrentDate())
        //     // ->where('dealer_id', $value->id)
        //     ->whereTime('in_time', '>', date("H:i:s", strtotime('+' . $timming->relax_time . ' minutes', strtotime($timming->start_time))))
        //     ->whereTime('in_time', '>', strtotime('+1 hour +30 minutes', strtotime($timming->start_time)))
        //     ->get();
        // dd($late1, $late2);
        $interval = request()->interval;
        $data = DB::table('attendance')
            ->whereDate('date', getCurrentDate())
            ->join('timings', 'attendance.dealer_id', '=', 'timings.user_id')
            ->select('attendance.*', 'timings.start_time', 'timings.relax_time')
            ->get();
        $late1 = $late2 = $late3 = [];
        foreach ($data as $key => $value) {
            $relax_time = date("H:i:s", strtotime('+' . $value->relax_time . ' minutes', strtotime($value->start_time)));
            $late2_time = strtotime('+1 hour +30 minutes', strtotime($value->start_time));
            if ($interval == 1) {
                if ($value->in_time < $relax_time) {
                    $late1[] = $value;
                }
            }
            // elseif ($interval == 2) {
            //     if ($value->in_time > $relax_time && $value->in_time < $late2_time) {
            //         $late1[] = $value;
            //     }
            // } 

            // elseif ($interval == 3) {
            //     if ($value->in_time > $late2_time) {
            //         $late1[] = $value;
            //     }
            // }


            elseif ($interval == 2) {
                if ($value->in_time > $relax_time) {
                    $late1[] = $value;
                }
            }
        }

        // dd($late1, $late2, $late3);


        $onlycontent = 0;
        return view('admin.late_attendance')->with('late1', $late1)->with('onlycontent', $onlycontent);
    }


    // View targets list
    public function targets()
    {
        $result = DB::table('target')->paginate(10);
        foreach ($result as $key => $value) {
            $done_treatments = DB::table('jobs')->where('dealer_id', $value->dealer_id)->whereMonth('date_added', date('m', strtotime($value->month)))->whereYear('date_added', date('Y', strtotime($value->month)))->sum('treatment_total');
            $done_treatments_price = DB::table('jobs')->where('dealer_id', $value->dealer_id)->whereMonth('date_added', date('m', strtotime($value->month)))->whereYear('date_added', date('Y', strtotime($value->month)))->sum('customer_price');
            $result[$key]->done_treatments = $done_treatments;
            $result[$key]->done_treatments_price = $done_treatments_price;
        }

        return view('admin.targets', [
            'result' => $result,
        ]);
    }

    // view add new target page
    public function addTarget()
    {
        $dealers = User::where(['role' => 2, 'status' => 1])->select('id as dealer_id', 'name as dealer_name')->orderBy('name', 'ASC')->get();
        return view('admin.addTarget', [
            'dealers' => $dealers,
        ]);
    }


    // get Templates on the basis of Dealer
    // public function getByDealer(Request $request)
    // {
    //     if (!$request->d_id) {
    //         $html = '<option value="">Select Template</option>';
    //     }
    //     else {
    //         $html = '';
    //         $templates = DB::table('dealer_templates')->where('dealer_id', $request->d_id)->get();
    //         $html .='<option value="">Select Template</option>'; 
    //         foreach ($templates as $template) {
    //             $html .='<option value="'.$template->template_id.'">'.get_template_name($template->template_id).'</option>';       
    //         }
    //     }
    //     return response()->json(['html' => $html]);
    // }

    // get template on the basis of Dealer
    public function getDealerTemplates(Request $request)
    {
        $templates = DB::table('dealer_templates')->join('treatment_templates', 'treatment_templates.id', '=', 'dealer_templates.template_id')->where('dealer_id', $request->dealerData)->select('dealer_templates.*', 'treatment_templates.temp_name as tempName', 'treatment_templates.id as tempId')->get();

        return Response($templates);
    }

    // view add new template target page
    public function addTempTarget($dealer_id, $temp_id)
    {
        $treatments = DB::table('treatments')->where('temp_id', $temp_id)->get();
        return view("admin.addTempTarget", compact('treatments', 'dealer_id', 'temp_id'));
    }

    // save new target
    public function insertTempTarget(Request $request)
    {
        // dd($request->all());
        $post = $request->all();

        $this->validate(
            $request,
            [
                'month' => 'required',
            ],
            [
                'month.required' => 'Please enter Target',
            ]
        );
        $data1 = array(
            'dealer_id' => $post['dealer_id'],
            'template_id' => $post['template_id'],
            'total_treatments' => $post['totalTargetNum'],
            'total_treatments_price' => $post['grdtot'],
            'month' => $post['month'],
        );

        $targetId = DB::table('target')->insertGetId($data1);

        $data2 = array(
            'target_id' => $targetId,
            'treatment_id' => $post['treatment_id'],
            'target_num' => $post['targetNum'],
            'customer_price' => $post['customer_price'],
            'total_target' => $post['total_target'],
        );

        if (@$post['targetNum'][0] != null && @$post['total_target'][0] != null) {
            foreach ($post['targetNum'] as $k => $value) {
                DB::table('target_treatments')->insert(['target_id' => $targetId, 'treatment_id' => $post['treatment_id'][$k], 'target_num' => $value, 'customer_price' => $post['customer_price'][$k], 'total_target' => $post['total_target'][$k]]);
            }
        }

        return redirect('/admin/targets');
    }

    // view edit template target page
    public function editTempTarget($dealer_id, $template_id, $target_id)
    {
        Session::put('prevUrl', \URL::previous());
        $target = DB::table('target_treatments')->where('id', $target_id)->first();
        // dd($target);
        if (!empty($target)) {
            return view('admin.editTempTarget', [
                'target' => $target,
                'dealer_id' => $dealer_id,
                'template_id' => $template_id,
                'target_id' => $target_id,
            ]);
        } else {
            Session::flash('error', 'No target found!');
            return redirect('/admin/targets');
        }
    }

    // update template target
    public function updateTempTarget(Request $request)
    {
        $post = $request->all();

        $data = array(
            // 'target_id' => $post['target_id'],
            'treatment_id' => $post['treatment_id'],
            'target_num' => $post['targetNum'],
            'customer_price' => $post['customer_price'],
            'total_target' => $post['total_target'],
        );
        DB::table('target_treatments')->where('id', $post['t_id'])->update($data);

        $update_target = DB::table('target_treatments')->where('target_id', $post['target_id'])->get();

        $totalTreatments = 0;
        $totalTreatmentsPrice = 0;
        foreach ($update_target as $value) {
            $totalTreatments += $value->target_num;
            $totalTreatmentsPrice += $value->total_target;
        }

        DB::table('target')->where('id', $post['target_id'])->update(array('total_treatments' => $totalTreatments, 'total_treatments_price' => $totalTreatmentsPrice));

        return redirect(Session::get('prevUrl'));
    }

    public function getTargetid(Request $request)
    {
        $month = $request->month;
        $dealer_id = $request->dealer_id;
        $template_id = $request->template_id;
        return $res = $template_id;
    }

    // View Target Listings 
    public function targetListing(Request $request, $target_id)
    {
        $targetData = DB::table('target')->where(['id' => $target_id])->first();
        $dealer_id = $targetData->dealer_id;
        $template_id = $targetData->template_id;
        $target_month = $targetData->month;
        // $templates = DB::table('treatment_templates')->get();
        $search = $request->month;
        // $temp_id = $request->temp_id;

        $result = DB::table('target_treatments')
            ->join('target', 'target.id', '=', 'target_treatments.target_id')
            ->select('target_treatments.*', 'target.*', 'target_treatments.id as id');
        if (!empty($search) && empty($template_id)) {
            $result = $result->where(['target.month' => $search, 'target.dealer_id' => $dealer_id]);
            $search = $request->month;
        }
        // elseif (empty($search) && !empty($temp_id)) {
        //     $result = $result->where(['target.template_id' => $template_id, 'target.dealer_id' => $dealer_id]);
        //     $temp_id = $template_id;
        // } 
        elseif (!empty($search) && !empty($template_id)) {
            $result = $result->where(['target.month' => $search, 'target.template_id' => $template_id, 'target.dealer_id' => $dealer_id]);
            $search = $request->month;
            $temp_id = $template_id;
        } else {
            $result = $result->where('target_id', $target_id);
        }
        $result = $result->paginate(20);

        foreach ($result as $key => $value) {
            $done = DB::table('jobs')->where('dealer_id', $value->dealer_id)->whereMonth('date_added', date('m', strtotime($value->month)))->whereYear('date_added', date('Y', strtotime($value->month)))->select('treatments')->get();
            foreach ($done as $k => $val) {
                $data = json_decode($val->treatments);
                foreach ($data as $k1 => $val1) {
                    if ($result[$key]->treatment_id == $val1->id) {
                        $result[$key]->countdone[] = $val1->id;
                    }
                }
            }
        }

        return view('admin.targetListing', [
            'result' => $result,
            'dealer_id' => $dealer_id,
            // 'templates' => $templates,
            'template_id' => $template_id,
            // 'temp_id' => $temp_id,
            'search' => $search,
            'currentMonth' => $target_month,
        ]);
    }

    // get Target by Month or Template
    public function getTarget(Request $request)
    {
        $result = DB::table('target_treatments')->join('target', 'target.id', '=', 'target_treatments.target_id')->where(['template_id' => $request->template_id, 'dealer_id' => $request->dealer_id])->get();
        return Response($result);
    }


    // get treatments on the basis of Model
    public function getModelTreatments(Request $request)
    {
        // dd($request->modelData);
        $output = "";
        // $treatments = DB::table('treatments')->where(function ($query) use ($request){
        //                 if (!empty($request->modelData)) {
        //                     $query->Where('model_id', $request->modelData);
        //                 }
        //             })->get();
        if (!$request->modelData) {
            $output .= " ";
        } else {
            $treatments = DB::table('treatments')->where('model_id', $request->modelData)->get();
            if (!empty($treatments) && count($treatments) > 0) {

                $output .= '<table class="table table-bordered table-hover" id="treatmentsTbl">' .
                    '<thead><tr><td><b>Name</b></td><td><b>No. Of Treatments</b></td><td><b>Customer Price</b></td><td><b>Total Target</b></td></tr></thead><tbody>';
                foreach ($treatments as $key => $treatment) {
                    $output .= '<tr>' .
                        '<td class="col-sm-3"><input type="hidden" name="treatment_id[]" value="' . $treatment->id . '">' . $treatment->treatment . '</td>' .
                        '<td class="col-sm-3"><input type="text" name="targetNum[]" id="targetNum' . $treatment->id . '" class="targetNum" value=""/></td>' .
                        '<td class="col-sm-3"><input type="text" class="dealer_price" id="dealer_price' . $treatment->id . '" name="customer_price[]" value="' . $treatment->customer_price . '" readonly></td>' .
                        '<td class="col-sm-3"><input type="text" name="total_target[]" class="total_target" id="total_target' . $treatment->id . '" value="0" readonly/></td>' .
                        '</tr>';
                }
                $output .= '</tbody>';
                $output .= '<tfoot><td style="text-align:right;">Total = </td><td><input type="text" class="totalTargetNum" name="totalTargetNum" value="" readonly></td><td style="text-align:right;">Total = </td><td><input type="text" class="grdtot" name="grdtot" value="" readonly></td></tfoot></table>';
                $output .= '<script type="text/javascript">
            var $tblrows = $("#treatmentsTbl tbody tr");
            $tblrows.each(function (index) {

                var $tblrow = $(this);

                $tblrow.find(".targetNum").on("keyup", function () {

                    var numbers = $tblrow.find("[name*=targetNum]").val();
                    var price = $tblrow.find("[name*=customer_price]").val();

                    var sum = 0;
                    $(".targetNum").each(function() {

                        var value = $(this).val();
                        if(!isNaN(value) && value.length != 0) {
                            sum += parseFloat(value);
                        }
                        });

                        $(".totalTargetNum").val(sum);

                        var subTotal = parseInt(numbers,10) * parseFloat(price);

                        if (!isNaN(numbers)) {

                            $tblrow.find(".total_target").val(isNaN(subTotal.toFixed(2))? 0 : subTotal.toFixed(2));
                            var grandTotal = 0;

                            $(".total_target").each(function () {

                                var stval = parseFloat($(this).val());
                                grandTotal += isNaN(stval) ? 0 : stval;
                                });

                                $(".grdtot").val(grandTotal.toFixed(2));
                            } 

                            });

                            });
                            </script>';
            } else {

                $output .= '';
            }
        }
        return Response($output);
        //return $treatments;
    }



    // // view edit target page
    // public function editTarget($id)
    // {
    //     $result = DB::table('target')->where('id',$id)->first();
    //     $result2 = DB::table('target_treatments')->where('target_id',$id)->get();

    //     $dealers = User::where('role',2)->where('status',1)->select('id as dealer_id','name as dealer_name')->orderBy('name','ASC')->get();
    //     $models = DB::table('models')->orderBy('model_name','ASC')->get();
    //    // dd($result);
    //     return view('admin.editTarget',[
    //         'result' => $result,
    //         'result2' => $result2,
    //         'dealers' => $dealers,
    //         'models' => $models,
    //     ]);
    // }

    // view Templates listing
    public function dealerTemplates(Request $request, $id)
    {
        $result = DB::table('dealer_templates')->where('dealer_id', $id)->paginate(10);

        return view('admin.dealerTemplates', [
            'result' => $result->appends(Input::except('page')),
            'dealer_id' => $id,
        ]);
    }

    // view add new Template page
    public function addDealerTemplate($dealer_id)
    {
        $templates = DB::table('treatment_templates')->get();
        // dd($templates);
        return view('admin.addDealerTemplate', [
            'dealer_id' => $dealer_id,
            'templates' => $templates,
        ]);
    }

    // save new Template
    public function insertDealerTemplate(Request $request)
    {
        $post = $request->all();
        $this->validate(
            $request,
            [
                'dealer_id' => 'required',
                'template_id' => 'required',
            ],
            [
                'dealer_id.required' => 'Please enter Dealer',
                'template_id.required' => 'Please Select Template',
            ]
        );
        $data = array(
            'dealer_id' => $post['dealer_id'],
            'template_id' => $post['template_id'],
            'date_added' => getCurrentTimestamp()
        );

        $check = DB::table('dealer_templates')->where(['dealer_id' => $post['dealer_id'], 'template_id' => $post['template_id']])->first();

        if (!empty($check)) {
            Session::flash('error', 'Template Already Exist!');
        } else {
            DB::table('dealer_templates')->insert($data);
            Session::flash('success', 'Template added successfully!');
        }
        return redirect('/admin/dealerTemplates/' . $post['dealer_id']);
    }

    // view edit Template page
    public function editDealerTemplate($dealer_id, $id)
    {
        $result = DB::table('dealer_templates')->where('id', $id)->first();
        $templates = DB::table('treatment_templates')->get();
        return view('admin.editDealerTemplate', [
            'dealer_id' => $dealer_id,
            'result' => $result,
            'templates' => $templates,
        ]);
    }
    // update existing Template
    public function updateDealerTemplate(Request $request)
    {
        $post = $request->all();

        $this->validate(
            $request,
            [
                'dealer_id' => 'required',
                'template_id' =>  'required',
            ],
            [
                'dealer_id.required' => 'Please enter Dealer',
                'template_id.required' => 'Please Select Template',
            ]
        );

        $data = array(
            'dealer_id' => $post['dealer_id'],
            'template_id' => $post['template_id'],
            'date_added' => getCurrentTimestamp()
        );
        $check = DB::table('dealer_templates')->where(['dealer_id' => $post['dealer_id'], 'template_id' => $post['template_id']])->first();
        if (!empty($check)) {
            Session::flash('error', 'Template Already Exist!');
            return redirect()->back();
        } else {
            DB::table('dealer_templates')->where('id', $post['temp_id'])->update($data);
            Session::flash('success', 'Template added successfully!');
        }
        return redirect('/admin/dealerTemplates/' . $post['dealer_id']);
    }

    //Change Template status or delete
    public function statusDealerTemplate($status, $dealer_id, $id)
    {

        if (@$status) {
            if ($status == "delete") {
                DB::table('dealer_templates')->where('id', $id)->delete();
                Session::flash('success', 'Template deleted successfully!');
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/admin/dealerTemplates/' . $dealer_id);
    }

    public function dealerProducts(Request $request, $dealer_id)
    {
        $month = $request->month;
        if (!empty($month)) {
            $selectedMonth = explode('-', $month);
            $month = $selectedMonth[1];
            $year = $selectedMonth[0];
        } else {
            $currentMonthYear = explode('-', date('Y-m'));
            $month = $currentMonthYear[1];
            $year = $currentMonthYear[0];
        }

        $products = DB::table('dealer_templates as dt')->where(['dt.dealer_id' => $dealer_id])
            ->join('treatments as t', 'dt.template_id', '=', 't.temp_id')
            ->join('products_treatments as pt', 't.id', '=', 'pt.tre_id')
            ->select('pt.pro_id')
            ->groupBy('pt.pro_id')
            ->get();

        $treatmentConsumptionOfProduct = DB::table('jobs as j')
            ->join('jobs_treatment as jt', 'jt.job_id', '=', 'j.id')
            ->join('products_treatments as pt', 'pt.tre_id', '=', 'jt.treatment_id')
            ->where(['j.dealer_id' => $dealer_id])
            ->whereMonth('j.job_date', $month)
            ->whereYear('j.job_date', $year)
            ->get(['pt.id', 'pt.tre_id', 'pt.pro_id', 'pt.quantity', 'pt.uom', 'pt.price', 'pt.status', 'pt.created_at']);

        $result = array();
        foreach ($treatmentConsumptionOfProduct as $k => $v) {
            $id = $v->pro_id;
            $result[$id]['quantity'][] = $v->quantity;
            $result[$id]['price'][] = $v->price;
            $result[$id]['uom'] = $v->uom;
        }
        $consumeData = array();
        foreach ($result as $i => $j) {
            $consumeData[] = array('id' => $i, 'quanity' => array_sum($j['quantity']), 'price' => array_sum($j['price']), 'uom' => $j['uom']);
        }
        $productDetail = array();
        foreach ($products as $key => $value) {
            $detail = new \stdClass();
            $detail->id = $value->pro_id;
            $detail->pro_name = get_product_name($value->pro_id);
            $detail->pro_unit = get_product_unit($value->pro_id);
            $getStock = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'product_id' => $value->pro_id, 'uom' => get_product_unit($value->pro_id)])
                ->orderBy('updated_at', 'DESC')
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', $year)
                ->first();
            if (!empty($getStock)) {
                $detail->minimum_stock = $getStock->minimum_stock;
                $detail->stock_in_hand = $getStock->stock_in_hand;
                $detail->updated_at = $getStock->updated_at;
            } else {
                $detail->minimum_stock = '';
                $detail->stock_in_hand = '';
                $detail->updated_at = '';
            }
            $detail->unit_name = get_unit_name(get_product_unit($value->pro_id));
            foreach ($consumeData as $key1 => $value1) {
                if ($value1['id'] == $detail->id  && $value1['uom'] == $detail->pro_unit) {
                    $detail->consumedQuantity = (string)$value1['quanity'];
                    $detail->totalPrice = (string)$value1['price'];
                }
            }
            $productDetail[] = $detail;
        }

        foreach ($productDetail as $key3 => $value3) {
            if (!isset($value3->consumedQuantity)) {
                $productDetail[$key3]->consumedQuantity = '';
                $productDetail[$key3]->totalPrice = '';
            }
        }
        // dd($productDetail);
        $selectedDate = $year . '-' . $month;
        return view('admin.dealerProductInventory', compact('dealer_id', 'productDetail', 'selectedDate'));
    }

    public function dealerProductInventory($dealer_id, $product_id)
    {
        Session::put('prevUrl', \URL::previous());
        $selectedMonth = Session::get('selectedMonth');
        if (!empty($selectedMonth)) {
            $date = explode('-', $selectedMonth);
            $month = $date[1];
            $year = $date[0];
        } else {
            $currentMonthYear = explode('-', date('Y-m'));
            $month = $currentMonthYear[1];
            $year = $currentMonthYear[0];
        }
        for ($i = 1; $i <=  date('t'); $i++) {
            $dates[] = date('Y') . "-" . date('m') . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        $lastThreeDays = array_slice($dates, -3, 3, true);
        // if (in_array(date('Y-m-d'), $lastThreeDays)) {
        //     dd("yes");
        // } else {
        //     dd("no");
        // }

        $minimum_stock = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'product_id' => $product_id])
            ->orderBy('updated_at', 'DESC')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->first();

        $updateHistory = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'product_id' => $product_id])->orderBy('updated_at', 'DESC')->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)->get();

        return view('admin.updateDealerProductInventory', compact('dealer_id', 'product_id', 'minimum_stock', 'updateHistory'));
    }

    public function updateDealerProductInventory(Request $request)
    {
        $post = $request->all();
        $selectedMonth = $post['selectedMonth'];
        $data = array(
            'dealer_id' => $post['dealer_id'],
            'product_id' => $post['product_id'],
            'minimum_stock' => $post['minimum_stock'],
            'stock_in_hand' => $post['stock_in_hand'],
            'uom' => $post['pro_unit'],
            'updated_at' => getCurrentTimestamp()
        );

        $check = DB::table('dealer_product_inventory')->where(['dealer_id' => $post['dealer_id'], 'product_id' => $post['product_id'], 'uom' => $post['pro_unit']])->first();

        $checkMonth = DB::table('dealer_product_inventory')->where(['dealer_id' => $post['dealer_id'], 'product_id' => $post['product_id']])->whereMonth('updated_at', date('m'))->first();

        if ($selectedMonth == date('Y-m') || empty($selectedMonth)) {
            if ($post['stock_in_hand'] >= $post['minimum_stock']) {
                return redirect()->back()->with('error', "Stock in hand should be less then Minimum Stock");
            } else {
                // if (!empty($check) && !empty($checkMonth)) {
                //     DB::table('dealer_product_inventory')->where('id',$post['inventory_id'])->update($data);
                // } else {
                DB::table('dealer_product_inventory')->insert($data);
                // }
            }
        } else {
            return redirect()->back()->with('error', "You can update only current month inventory");
        }
        return redirect(Session::get('prevUrl'))->with('success', "Inventory updated successfully");
    }

    public function downloadProductInventory(Request $request, $dealer_id)
    {
        // dd($dealer_id, $request->all());
        $month = $request->month;
        if (!empty($month)) {
            $selectedMonth = explode('-', $month);
            $month = $selectedMonth[1];
            $year = $selectedMonth[0];
        } else {
            $currentMonthYear = explode('-', date('Y-m'));
            $month = $currentMonthYear[1];
            $year = $currentMonthYear[0];
        }

        $products = DB::table('dealer_templates as dt')->where(['dt.dealer_id' => $dealer_id])
            ->join('treatments as t', 'dt.template_id', '=', 't.temp_id')
            ->join('products_treatments as pt', 't.id', '=', 'pt.tre_id')
            ->select('pt.pro_id')
            ->groupBy('pt.pro_id')
            ->get();

        $treatmentConsumptionOfProduct = DB::table('jobs as j')
            ->join('jobs_treatment as jt', 'jt.job_id', '=', 'j.id')
            ->join('products_treatments as pt', 'pt.tre_id', '=', 'jt.treatment_id')
            ->where(['j.dealer_id' => $dealer_id])
            ->whereMonth('j.job_date', $month)
            ->whereYear('j.job_date', $year)
            ->get(['pt.id', 'pt.tre_id', 'pt.pro_id', 'pt.quantity', 'pt.uom', 'pt.price', 'pt.status', 'pt.created_at']);

        $result = array();
        foreach ($treatmentConsumptionOfProduct as $k => $v) {
            $id = $v->pro_id;
            $result[$id]['quantity'][] = $v->quantity;
            $result[$id]['price'][] = $v->price;
            $result[$id]['uom'] = $v->uom;
        }
        $consumeData = array();
        foreach ($result as $i => $j) {
            $consumeData[] = array('id' => $i, 'quanity' => array_sum($j['quantity']), 'price' => array_sum($j['price']), 'uom' => $j['uom']);
        }
        $productDetail = array();
        foreach ($products as $key => $value) {
            $detail = new \stdClass();
            $detail->id = $value->pro_id;
            $detail->pro_name = get_product_name($value->pro_id);
            $detail->pro_unit = get_product_unit($value->pro_id);
            $getStock = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'product_id' => $value->pro_id, 'uom' => get_product_unit($value->pro_id)])
                ->orderBy('updated_at', 'DESC')
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', $year)
                ->first();
            if (!empty($getStock)) {
                $detail->minimum_stock = $getStock->minimum_stock;
                $detail->stock_in_hand = $getStock->stock_in_hand;
                $detail->updated_at = $getStock->updated_at;
            } else {
                $detail->minimum_stock = '';
                $detail->stock_in_hand = '';
                $detail->updated_at = '';
            }
            $detail->unit_name = get_unit_name(get_product_unit($value->pro_id));
            foreach ($consumeData as $key1 => $value1) {
                if ($value1['id'] == $detail->id  && $value1['uom'] == $detail->pro_unit) {
                    $detail->consumedQuantity = (string)$value1['quanity'];
                    $detail->totalPrice = (string)$value1['price'];
                }
            }
            $productDetail[] = $detail;
        }

        foreach ($productDetail as $key3 => $value3) {
            if (!isset($value3->consumedQuantity)) {
                $productDetail[$key3]->consumedQuantity = '';
                $productDetail[$key3]->totalPrice = '';
            }
        }

        return Excel::create('Dealers ' . date("d M,Y"), function ($excel) use ($productDetail) {
            $excel->sheet('mySheet', function ($sheet) use ($productDetail) {
                $sheet->setCellValue('A1', 'Product Name');
                $sheet->setCellValue('B1', 'Minimum Stock');
                $sheet->setCellValue('C1', 'Treatmentwise Consumption');
                $sheet->setCellValue('D1', 'Expected Stock');
                $sheet->setCellValue('E1', 'Stock in Hand');
                $sheet->setCellValue('F1', 'Last Updated');
                $i = 2;
                $loop = 1;
                foreach ($productDetail as $key => $value) {
                    if (!empty($value->minimum_stock) || $value->minimum_stock != 0) {
                        $minimum_stock = (int)$value->minimum_stock . ' ' . $value->unit_name;
                    } else {
                        $minimum_stock = '';
                    }

                    if (!empty($value->consumedQuantity)) {
                        $consumedQuantity = (int)$value->consumedQuantity . ' ' . $value->unit_name;
                    } else {
                        $consumedQuantity = '';
                    }

                    if (!empty($minimum_stock)) {
                        $expectedStock = ((float)$value->minimum_stock - (float)$consumedQuantity) . ' ' . $value->unit_name;
                    } else {
                        $expectedStock = '';
                    }

                    if (!empty($value->unit_name)) {
                        $stock_in_hand = (int)$value->stock_in_hand . ' ' . $value->unit_name;
                    } else {
                        $stock_in_hand = '';
                    }

                    $sheet->setCellValue('A' . $i, $value->pro_name);
                    $sheet->setCellValue('B' . $i, $minimum_stock);
                    $sheet->setCellValue('C' . $i, $consumedQuantity);
                    $sheet->setCellValue('D' . $i, $expectedStock);
                    $sheet->setCellValue('E' . $i, $value->stock_in_hand);
                    $sheet->setCellValue('F' . $i, $value->updated_at);
                    $i++;
                    $loop++;
                }
            });
        })->download('csv');
    }

    public function dealerTemplatesProducts($dealer_id, $id)
    {
        $productPrice = 0;
        $consumptionQuantity = 0;
        $template_id = DB::table('dealer_templates')->where(['id' => $id, 'dealer_id' => $dealer_id])->first()->template_id;
        $getTreatments = DB::table('treatments')->where('temp_id', $template_id)->get();
        $products = array();
        foreach ($getTreatments as $key => $value) {
            $productData = DB::table('products_treatments')->where(['tre_id' => $value->id])->get();
            $productAllData = array();
            foreach ($productData as $key1 => $value1) {
                $products[] = $value1->pro_id;
            }
        }
        $products = array_unique($products);
        $productDetail = array();
        foreach ($products as $key2 => $value2) {
            $detail = new \stdClass();
            $detail->id = $value2;
            $detail->pro_name = get_product_name($value2);
            $detail->pro_unit = get_product_unit($value2);
            $getStock = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'product_id' => $value2, 'template_id' => $template_id, 'uom' => get_product_unit($value2)])->first();
            if (!empty($getStock)) {
                $detail->stock = $getStock->minimum_stock;
            } else {
                $detail->stock = '';
            }
            $detail->unit_name = get_unit_name(get_product_unit($value2));
            $productDetail[] = $detail;
        }
        return view('admin.minimunInventoryLevel', compact('template_id', 'dealer_id', 'productDetail'));
    }

    public function set_min_level($dealer_id, $temp_id, $pro_id)
    {
        Session::put('prevUrl', \URL::previous());
        for ($i = 1; $i <=  date('t'); $i++) {
            $dates[] = date('Y') . "-" . date('m') . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        $lastThreeDays = array_slice($dates, -3, 3, true);
        // if (in_array(date('Y-m-d'), $lastThreeDays)) {
        //     dd("yes");
        // } else {
        //     dd("no");
        // }
        $primary_id = DB::table('dealer_templates')->where(['dealer_id' => $dealer_id, 'template_id' => $temp_id])->first()->id;
        $minimum_stock = DB::table('dealer_product_inventory')->where(['dealer_id' => $dealer_id, 'template_id' => $temp_id, 'product_id' => $pro_id])->first();
        return view('admin.updateProductInventory', compact('primary_id', 'dealer_id', 'temp_id', 'pro_id', 'minimum_stock'));
    }

    public function updateProductInventory(Request $request)
    {
        $post = $request->all();

        $data = array(
            'template_id' => $post['template_id'],
            'dealer_id' => $post['dealer_id'],
            'product_id' => $post['product_id'],
            'minimum_stock' => $post['minimum_stock'],
            'uom' => $post['pro_unit'],
            'updated_at' => getCurrentTimestamp()
        );

        $checkMonth = DB::table('dealer_product_inventory')->where(['dealer_id' => $post['dealer_id'], 'template_id' => $post['template_id'], 'product_id' => $post['product_id']])->whereMonth('updated_at', date('m'))->first();
        if (!empty($checkMonth)) {
            $check = DB::table('dealer_product_inventory')->where(['dealer_id' => $post['dealer_id'], 'template_id' => $post['template_id'], 'product_id' => $post['product_id'], 'uom' => $post['pro_unit']])->first();
            if (!empty($check)) {
                DB::table('dealer_product_inventory')->where('id', $check->id)->update($data);
            } else {
                DB::table('dealer_product_inventory')->insert($data);
            }
        } else {
            return redirect()->back()->with('error', "Cannot change previous month inventory");
        }
        return redirect(Session::get('prevUrl'))->with('success', "Inventory updated successfully");
    }

    public function consumptionReport(Request $request)
    {
        $search = $request->all();
        if (@$search['selectMonth']) {
            $month = explode('-', $search['selectMonth']);
        } else {
            $month = explode('-', date('Y-m'));
        }
        $dealers = User::where(['role' => 2, 'status' => 1])->select('id', 'name')->get();
        $result = DB::table('jobs')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search['dealer'])) {
                        if (!empty(trim($search['dealer']))) {
                            $query->where('dealer_id', '=', $search['dealer']);
                        }
                    }
                }
            })
            ->whereMonth('job_date', $month[1])
            ->whereYear('job_date', $month[0])
            ->where('delete_job', 1)
            ->get();
        $duplicate = array();
        $treatment_data = array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $treatments = json_decode($value->treatments);
                $treatment_data[] = $treatments[0];
            }
            $duplicate = array_count_values(array_column(@$treatment_data, 'id'));
            $first_array = array();
            foreach ($duplicate as $k => $val) {
                $treatment_value = 0;
                foreach ($treatment_data as $tre_k => $tre_value) {

                    if ($k == $tre_value->id) {
                        $treatment_value = $treatment_value + $tre_value->customer_price;
                        $res['id'] = $tre_value->id;
                        $res['treatment'] = $tre_value->treatment;
                        $res['treatment_type'] = $tre_value->treatment_type;
                        $res['count'] = $val;
                    }
                    $res['total_price'] = $treatment_value;
                }
                $first_array[] = $res;
            }

            foreach ($first_array as $key => $value) {
                $products = DB::table('products_treatments as pt')
                    ->join('products as p', 'p.id', 'pt.pro_id')
                    ->select('pt.pro_id', 'pt.tre_id', 'pt.uom', 'p.name', DB::raw('SUM(pt.quantity) as quantity, SUM(pt.price) as price, count(pt.pro_id) as total_pro_id'))
                    ->where('pt.tre_id', $value['id'])
                    ->groupBy('pt.pro_id')
                    ->get();

                foreach ($products as $k => $val) {
                    $products[$k]->total_quantity = $val->quantity * $value['count'];
                    $products[$k]->total_price = $val->price * $value['count'];
                }
                $first_array[$key]['products'] = $products;
            }
            $treatments = array();
            foreach ($first_array as $key => $value) {
                // print_r($value);
                // die;
                $treatments[$key]['id'] = $value['id'];
                $treatments[$key]['treatment'] = $value['treatment'];
                $treatments[$key]['treatment_type'] = $value['treatment_type'];
                $treatments[$key]['count'] = $value['count'];
                $treatments[$key]['total_price'] = $value['total_price'];
                if (!empty($value['products'])) {
                    foreach ($value['products'] as $k => $val) {
                        $pro['pro_id'] = $val->pro_id;
                        $pro['tre_id'] = $val->tre_id;
                        $pro['uom'] = $val->uom;
                        $pro['name'] = $val->name;
                        $pro['quantity'] = $val->quantity;
                        $pro['price'] = $val->price;
                        $pro['total_pro_id'] = $val->total_pro_id;
                        $pro['total_quantitys'] = $val->total_quantity;
                        $pro['total_prices'] = $val->total_price;
                        $treatments[$key]['products'][$k] = $pro;
                    }
                }
                //$treatments[$key]['treatment'] = $value['treatment'];
            }
            //print_r($duplicate);
            // echo "<pre>";
            // print_r($treatments);
            // die;
            //  print_r($first_array);
            //$treatments = $first_array;
            //dd($treatments);
            $newarray = array();
            foreach ($first_array as $row) {
                foreach ($row['products'] as $key => $val) {
                    if (!isset($newarray[$val->pro_id])) {
                        $newarray[$val->pro_id] = $val;
                        $newarray[$val->pro_id]->count = 1;
                        continue;
                    }
                    $newarray[$val->pro_id]->total_quantity += $val->total_quantity;
                    $newarray[$val->pro_id]->total_price += $val->total_price;
                    $newarray[$val->pro_id]->count++;
                }
            }
        }
        //dd($first_array, $treatments);
        return view('admin.consumptionReport', [
            'treatments' => @$treatments,
            'dealers' => $dealers,
            'oldDealer' => @$search['dealer'],
            'oldMonth' => @$search['selectMonth'],
            'products' => array_values(@$newarray),
        ]);
        // echo "<pre>";
        // print_r(array_values($newarray));
    }
}
