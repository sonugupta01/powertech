<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Session;
use Redirect;
use DB;
use Storage;
use File;
use Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class AsmController extends Controller
{
    public function dashboard(Request $request)
    {
        $download = $request->download;
        $selectMonth = $request->selectMonth;
        if (Auth::check() && Auth::user()->role == '5') {
            $user_id = Auth::id();
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($user_id, $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
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
                ->select(DB::raw('SUM(j.customer_price) as customer_price,  SUM(j.actual_price) as actual_price, SUM(j.difference_price) as difference_price, SUM(j.hvt_total) as hvt_total, SUM(j.hvt_value) as hvt_value,SUM(j.treatment_total) as vas_total, SUM(j.customer_price) as vas_value, j.job_date, j.foc_options'))
                ->whereMonth('j.job_date', $month)
                ->whereYear('j.job_date', $year)
                ->where('j.delete_job', 1)
                ->whereIn('j.dealer_id', $d_ids)
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
                ->whereIn('dealer_id', $d_ids)
                ->first();
            $result = json_decode(json_encode($result), true);
            if (!empty($selectMonth)) {
                $current = "'" . $selectMonth . "'";
                $currentM = $selectMonth;
            } else {
                $current = "'" . $currentMonth . "'";
                $currentM = $currentMonth;
            }

            return view('asm.dashboard', [
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
        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }
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
            ->select(DB::raw('SUM(j.customer_price) as customer_price,  SUM(j.actual_price) as actual_price, SUM(j.difference_price) as difference_price, SUM(j.hvt_total) as hvt_total, SUM(j.hvt_value) as hvt_value,SUM(j.treatment_total) as vas_total, SUM(j.customer_price) as vas_value, j.job_date, j.foc_options'))
            ->whereMonth('j.job_date', $month)
            ->whereYear('j.job_date', $year)
            ->where('j.delete_job', 1)
            // ->where('j.foc_options',5)
            ->whereIn('j.dealer_id', $d_ids)
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

    public function dealer_management(Request $request)
    {
        $search = $request->search;
        $ASM = Auth::id();
        $dealers = User::where('dealer_id', $ASM)
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    if (isset($search)) {
                        if (!empty(trim($search))) {
                            $query->orWhere('name', 'like', '%' . $search . '%');
                            $query->orWhere('email', 'like', '%' . $search . '%');
                            $query->orWhere('mobile_no', 'like', '%' . $search . '%');
                        }
                    }
                }
            })
            ->orderBy('name', 'ASC')->paginate(15);

        return view('asm.dealers', [
            'dealers' => $dealers->appends(Input::except('page')),
        ]);
    }

    // view add new Dealer page
    public function addDealer()
    {
        $states = DB::table('states')->get();
        $grouplist = DB::table('groups')->get();
        $oemlist = DB::table('oems')->get();
        return view('asm.addDealers', [
            'states' => $states,
            'grouplist' => $grouplist,
            'oemlist' => $oemlist,
        ]);
    }

    // save new Dealer
    public function insertDealer(Request $request)
    {
        $ASM = Auth::id();
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'address' => 'required|max:250',
                'state_id' => 'required',
                'city' => 'required',
                'district_id' => 'required',
                'mobile_no' => 'required|digits:10|unique:users,mobile_no',
                'email' => 'required|unique:users,email',
                //'group' => 'required',
                'OEM' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'email.required' => 'Please enter email',
                'address.required' => 'Please enter address',
                'city.required' => 'Please enter city',
                'district_id.required' => 'Please select district',
                'state_id.required' => 'Please select state',
                'mobile_no.required' => 'Please enter mobile no.',
                // 'group.required' => 'Please enter group.',
                'OEM.required' => 'Please enter OEM.',
                'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                'longitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
            ]
        );
        $data = array(
            'role' => 2,
            'name' => $post['name'],
            'email' => $post['email'],
            'address' => $post['address'],
            'longitude' => $post['longitude'],
            'latitude' => $post['latitude'],
            'mobile_no' => $post['mobile_no'],
            'state_id' => $post['state_id'],
            'district_id' => $post['district_id'],
            'city' => $post['city'],
            'group_id' => @$post['group'],
            'oem_id' => $post['OEM'],
            'dealer_id' => $ASM,
        );

        User::insert($data);

        Session::flash('success', 'Dealer added successfully!');
        return redirect('/asm/dealer_management');
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
            }
        } else {
            Session::flash('error', 'Something wrong!');
        }
        return redirect('/asm/dealer_management');
    }

    // view edit Dealer page
    public function editDealer($id)
    {
        $result = User::where('role', 2)->find($id);
        $states = DB::table('states')->get();
        $grouplist = DB::table('groups')->get();
        $oemlist = DB::table('oems')->get();
        $districts = DB::table('districts')->where('state_id', $result->state_id)->get();
        if (!empty($result)) {
            return view('asm.editDealers', [
                'result' => $result,
                'states' => $states,
                'districts' => $districts,
                'grouplist' => $grouplist,
                'oemlist' => $oemlist,
            ]);
        } else {
            Session::flash('error', 'No dealer found!');
            return redirect('/asm/dealer_management');
        }
    }

    // update existing Dealer
    public function updateDealer(Request $request)
    {
        $ASM = Auth::id();
        $post = $request->all();
        $this->validate(
            $request,
            [
                'name' => 'required',
                'address' => 'required|max:250',
                'state_id' => 'required',
                'city' => 'required',
                'district_id' => 'required',
                'mobile_no' => 'required|digits:10|unique:users,mobile_no,' . $request->id,
                'email' => 'required|unique:users,email,' . $request->id,
                //'group' => 'required',
                'OEM' => 'required',
            ],
            [
                'name.required' => 'Please enter name',
                'email.required' => 'Please enter email',
                'address.required' => 'Please enter address',
                'city.required' => 'Please enter city',
                'district_id.required' => 'Please select district',
                'state_id.required' => 'Please select state',
                'mobile_no.required' => 'Please enter mobile no.',
                // 'group.required' => 'Please enter group.',
                'OEM.required' => 'Please enter OEM.',
                'latitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
                'longitude' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$^/'],
            ]
        );
        $data = array(
            'role' => 2,
            'name' => $post['name'],
            'email' => $post['email'],
            'address' => $post['address'],
            'longitude' => $post['longitude'],
            'latitude' => $post['latitude'],
            'mobile_no' => $post['mobile_no'],
            'state_id' => $post['state_id'],
            'district_id' => $post['district_id'],
            'city' => $post['city'],
            'group_id' => @$post['group'],
            'oem_id' => $post['OEM'],
            'dealer_id' => $ASM,
        );
        User::where('id', $post['id'])->update($data);

        Session::flash('success', 'Dealer updated successfully!');
        return redirect('/asm/dealer_management');
    }



    // view Staff listing
    public function staff_management(Request $request)
    {
        $user_id = Auth::id();
        $search = $request->search;
        $des = $request->designation_id;
        $dealer_id = $request->dealer_id;
        $firm_id = $request->firm_id;
        $office_id = $request->office_id;
        $status = $request->status;

        $designations = DB::table('designations')->get();
        $dep_des = DB::table('staff_detail')->first();

        $dealers = array();
        if (!empty($firm_id)) {
            $results = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1, 'firm_id' => $firm_id])->orderBy('id', 'DESC')->get();
            foreach ($results as $key => $value) {
                $reporting_ids = explode(",", $value->reporting_authority);
                if (in_array($user_id, $reporting_ids)) {
                    $dealers[] = $results[$key];
                }
            }
            $firms = DB::table('firms')->get();
        } else {
            $results = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            foreach ($results as $key => $value) {
                $reporting_ids = explode(",", $value->reporting_authority);
                if (in_array($user_id, $reporting_ids)) {
                    $dealers[] = $results[$key];
                }
            }
        }
        $firms = DB::table('firms')->get();
        $offices = DB::table('users')->select('id', 'name')->where(['role' => 6, 'status' => 1])->get();

        $result = User::join('staff_detail', 'users.id', '=', 'staff_detail.user_id')
            // ->join('emp_hierarchy', 'emp_hierarchy.user_id', '=', 'users.id')
            ->select('*', 'users.id as user_id')
            ->whereIn('role', [3, 4])
            ->where('reporting_authority', $user_id)
            ->where(function ($query) use ($des, $search, $firm_id, $dealer_id, $office_id, $status) {
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
        return view('asm.staff', [
            'result' => $result->appends(Input::except('page')),
            'designations' => $designations,
            'des' => $des,
            'dealers' => $dealers,
            'dealer_id' => $dealer_id,
            'dep_des' => $dep_des,
            'firms' => $firms,
            'firm_id' => $firm_id,
            'offices' => $offices,
            'office_id' => $office_id,
            'status' => $status,
        ]);
    }

    public function downloadStaff(Request $request)
    {
        $user_id = Auth::id();
        $search = $request->search;
        $des = $request->designation_id;
        $dealer_id = $request->dealer_id;
        $firm_id = $request->firm_id;

        $designations = DB::table('designations')->get();
        $dep_des = DB::table('staff_detail')->first();
        $results = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        foreach ($results as $key => $value) {
            $reporting_ids = explode(",", $value->reporting_authority);
            if (in_array($user_id, $reporting_ids)) {
                $dealers[] = $results[$key];
            }
        }
        $firms = DB::table('firms')->get();

        $result = User::join('staff_detail', 'users.id', '=', 'staff_detail.user_id')
            // ->join('emp_hierarchy', 'emp_hierarchy.user_id', '=', 'users.id')
            ->select('*', 'users.id as user_id')
            ->whereIn('role', [3, 4])
            ->where('reporting_authority', $user_id)
            ->where(function ($query) use ($des, $search, $firm_id, $dealer_id) {
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
                if (!empty($dealer_id)) {
                    if (isset($dealer_id)) {
                        if (!empty(trim($dealer_id))) {
                            $query->Where(['dealer_id' => $dealer_id, 'status' => 1]);
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
            })
            ->orderBy('name', 'ASC')->paginate(20);

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
                    $i++;
                    $loop++;
                }
            });
        })->download('csv');
    }


    public function getDealerPermission($user_id, $del_id)
    {
        // $reporting_authority = get_asm($del_id);
        $checkDealer = DB::table('users')->where(['id' => $user_id, 'dealer_id' => $del_id])->first();
        if (!empty($checkDealer)) {
            $html = '';
        } else {
            $html = 'You are changing dealer. Are you sure ?';
        }
        return response()->json(['html' => $html]);
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
                $authority_id = Auth::id();
                $authority_name = get_name($authority_id);
                $authority_des_name = get_designation_by_userid($authority_id);
                $res = '<option value="">Select Reporting Authority</option>';
                // foreach ($authorities as $authority) {
                //     $authority_id = $authority;
                //     $authority_name = get_name($authority);
                //     $authority_des_name = get_designation_by_userid($authority);
                //     $res .= "<option value='$authority_id'>$authority_name - $authority_des_name</option>";
                // }
                $res .= "<option value='$authority_id'>$authority_name - $authority_des_name</option>";
            } else {
                $res = "<option value=''>No Reporting Authority found</option>";
            }
        } else {
            $res = "<option value=''>No Reporting Authority found</option>";
        }
        return $res;
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
        return redirect('/asm/staff_management');
    }

    // public function emp_hierarchy()
    // {
    //     $result = DB::table('staff_detail as sd')->join('users as u', 'u.id', '=', 'sd.user_id')->join('emp_hierarchy as eh', 'u.id', '=', 'eh.user_id')->where(['sd.designation_id'=>14, 'u.status'=>1])->paginate(20);
    //     return view('asm.emp_hierarchy',[
    //         'result' => $result->appends(Input::except('page')),
    //     ]);
    // }
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

    public function editEmpHierarchy($id)
    {
        $user_id = Auth::id();
        $result = DB::table('emp_hierarchy as eh')->join('users as u', 'u.id', '=', 'eh.user_id')->select('*', 'eh.id as id', 'u.id as user_id')->where('eh.user_id', $id)->first();
        if (empty($result)) {
            Session::flash('error', 'Please add required information first.');
            return redirect()->back();
        } else {
            $dep_des = DB::table('staff_detail')->where('user_id', $id)->first();
            // $dealers = DB::table('users')->where(['role'=>2, 'reporting_authority'=>$user_id, 'status'=>1])->select('id as dealer_id','name as dealer_name')->orderBy('name','ASC')->get();
            $results = DB::table('users')->select('id as dealer_id', 'name as dealer_name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            foreach ($results as $key => $value) {
                $reporting_ids = explode(",", $value->reporting_authority);
                if (in_array($user_id, $reporting_ids)) {
                    $dealers[] = $results[$key];
                }
            }
            $dealer_authorities = DB::table('users')
                ->where('id', @$result->dealer_id)
                ->select('reporting_authority')
                ->get();
            // $dealer_authorities = explode(",",@$dealer_authorities[0]->reporting_authority);
            $dealer_authorities = $user_id;
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
            return view('asm.editEmpHierarchy', [
                'result' => $result,
                'dealers' => $dealers,
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
                    'reporting_level' => 'required',
                    // 'fdate' => 'required',
                    // 'todate' => 'required',
                ],
                [
                    'reporting_level.required' => 'Please select Reporting Level',
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
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'dealer_id' => $d_id, 'authority_id' => $post['dealer_authid'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif ($post['del_id'] != $checkUser->dealer_id && $post['dealer_authid'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3, 'authority_id' => $post['dealer_authid']]);
            } elseif ($post['del_id'] != $checkUser->dealer_id && $post['dealer_authid'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 2, 'authority_id' => $post['dealer_authid']]);
            } else {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(['dealer_id' => $post['del_id'], 'authority_id' => $post['dealer_authid'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            }
            // DB::table('users')->where('id',$post['user_id'])->update(array('dealer_id'=>$post['del_id'],'reporting_authority'=>$post['dealer_authid']));
        } elseif (!empty($post['authority'])) {
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif (!empty($checkUser) && $post['authority'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3));
            } elseif (!empty($checkUser) && $post['authority'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['authority'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']));
            }
            // DB::table('users')->where('id',$post['user_id'])->update(array('dealer_id'=>null,'reporting_authority'=>$post['authority']));
        } else {
            if (empty($checkUser)) {
                DB::table('emp_hierarchy')->insert(['user_id' => $post['user_id'], 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']]);
            } elseif (!empty($checkUser) && $post['office'] != $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate'], 'status' => 3));
            } elseif (!empty($checkUser) && $post['office'] == $checkUser->authority_id) {
                DB::table('emp_hierarchy')->where('id', $post['id'])->update(array('dealer_id' => null, 'authority_id' => $post['office'], 'from_date' => $post['fdate'], 'to_date' => $post['todate']));
            }
            // DB::table('users')->where('id',$post['user_id'])->update(array('dealer_id'=>null,'reporting_authority'=>$post['office']));
        }
        return redirect('/asm/staff_management')->with('success', 'data updated succesfully');
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
        return redirect('/asm/emp_hierarchy');
    }

    // View targets list
    public function targets()
    {
        $user_id = Auth::id();
        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }
        $result = DB::table('target')->whereIn('dealer_id', $dealers)->paginate(10);
        foreach ($result as $key => $value) {
            $done_treatments = DB::table('jobs')->where('dealer_id', $value->dealer_id)->whereMonth('date_added', date('m', strtotime($value->month)))->whereYear('date_added', date('Y', strtotime($value->month)))->sum('treatment_total');
            $done_treatments_price = DB::table('jobs')->where('dealer_id', $value->dealer_id)->whereMonth('date_added', date('m', strtotime($value->month)))->whereYear('date_added', date('Y', strtotime($value->month)))->sum('customer_price');
            $result[$key]->done_treatments = $done_treatments;
            $result[$key]->done_treatments_price = $done_treatments_price;
        }

        return view('asm.targets', [
            'result' => $result,
        ]);
    }

    // View Target Listings 
    public function targetListing(Request $request, $dealer_id, $template_id, $target_id)
    {

        $templates = DB::table('treatment_templates')->get();
        $search = $request->month;
        $temp_id = $request->temp_id;

        $user_id = Auth::id();
        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }
        $result = DB::table('target_treatments')
            ->join('target', 'target.id', '=', 'target_treatments.target_id')
            ->select('target_treatments.*', 'target.*', 'target_treatments.id as id')
            ->whereIn('target.dealer_id', $dealers);
        if (!empty($search) && empty($temp_id)) {
            $result = $result->where(['target.month' => $search, 'target.dealer_id' => $dealer_id]);
            $search = $request->month;
        } elseif (empty($search) && !empty($temp_id)) {
            $result = $result->where(['target.template_id' => $temp_id, 'target.dealer_id' => $dealer_id]);
            $temp_id = $request->temp_id;
        } elseif (!empty($search) && !empty($temp_id)) {
            $result = $result->where(['target.month' => $search, 'target.template_id' => $temp_id, 'target.dealer_id' => $dealer_id]);
            $search = $request->month;
            $temp_id = $request->temp_id;
        } else {
            $result = $result->where('target_treatments.target_id', $target_id);
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
        return view('asm.targetListing', [
            'result' => $result,
            'dealer_id' => $dealer_id,
            'templates' => $templates,
            'template_id' => $template_id,
            'temp_id' => $temp_id,
            'search' => $search,
        ]);
    }

    // View Jobs listing
    public function jobs(Request $request)
    {

        $search = $request->search;
        $user_id = Auth::id();
        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }
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

        $result = DB::table('jobs as j')
            ->whereIn('dealer_id', $dealers)
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
        $result = $result->where(['delete_job' => 1])
            ->orderBy('j.job_date', 'DESC')
            ->paginate(10);
        $supervisors = DB::table('jobs_treatment as jt')
            ->join('jobs as j', 'j.id', '=', 'jt.job_id')
            ->join('users as u', 'u.id', '=', 'j.user_id')
            ->select('u.name', 'j.user_id as id')
            ->where(['jt.delete_job' => 1, 'j.user_id' => $user_id])
            ->groupBy('j.user_id')
            ->orderBy('u.name', 'ASC')
            ->get();

        if (request()->has('page')) {
            Session::put('job_url', url()->full());
        }
        return view('asm.jobs', [
            'result' => $result->appends(Input::except('page')),
            'supervisors' => $supervisors,
            'dealers' => $dealers,
            'regn_no' => @$regn_no,
            'job_type' => @$type,
            'oldSupervisor' => @$search,
        ]);
    }

    // view add job page
    public function addJob(Request $request)
    {
        $user_id = Auth::id();
        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }

        return view('asm.addJob', [
            'dealers' => $dealers,
        ]);
    }

    public function getdealerUsers(Request $request)
    {
        $dealer_id = $request->dealer;
        $users = DB::table('users')->where(['role' => 3, 'dealer_id' => $dealer_id, 'reporting_authority' => Auth::id()])->select('id', 'name')->get();
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

    // save new job
    public function insertJob(Request $request)
    {
        $post = $request->all();
        $actual_price = 0;
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
        $treatment_id = array();
        $treatment_data = array();
        $i = $hvt_value = 0;
        foreach ($post['treatment_id'] as $value) {
            $data1 = DB::table('treatments')->where('id', $value)->first();
            $data1->job_type = $post['job_type'][$key];
            $data1->actualPrice = $post['actualPrice'][$key];
            $data1->difference = $post['difference'][$key];
            $treatment_data[] = $data1;
            if ($data1->job_type == '5') {
                $actual_price = $actual_price + $data1->actualPrice;
                $difference_price = $difference_price + $data1->difference;
            } else {
                $actual_price = 0;
                $difference_price = 0;
            }
            $customer_price = $customer_price + $data1->customer_price;
            // $dealer_price = $dealer_price + $data1->dealer_price;
            // $incentive = $incentive + $data1->incentive;
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
        //     return redirect('/asm/addJob')->with('error',$error);
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
            'difference_price' => $difference_price,
            // 'dealer_price' => $dealer_price,
            // 'incentive' => $incentive,
            // 'foc_options' => $request->option,

        );
        $result = DB::table('jobs')->insert($data);
        $id = DB::getPdo()->lastInsertId();
        foreach ($treatment_id as $value) {
            $data = array(
                'job_id' => $id,
                'treatment_id' => $value['id'],
            );
            DB::table('jobs_treatment')->insert($data);
            // }
            Session::flash('success', 'Job added successfully!');
            return redirect('/asm/jobs');
        }
    }

    // view edit job page
    public function editJob($id)
    {
        $user_id = Auth::id();
        $result = DB::table('jobs')->where('id', $id)->first();
        $users = DB::table('users')->where(['role' => 3, 'dealer_id' => $result->dealer_id, 'reporting_authority' => $user_id])->select('id', 'name')->get();
        $selectTreatment = array();
        foreach (json_decode($result->treatments) as $value) {
            $select['id'] = $value->id;
            $select['treatment'] = $value->treatment;
            $select['customer_price'] = $value->customer_price;
            // $select['dealer_price'] = $value->dealer_price;
            // $select['incentive'] = $value->incentive;
            $select['job_type'] = @$value->job_type;
            $select['actualPrice'] = @$value->actualPrice;
            $select['difference'] = @$value->difference;
            $selectTreatment[] = $select;
        }
        $treatments = DB::table('treatments')->where('model_id', $result->model_id)->where('status', 1)->get();
        // $treatment=array();
        // foreach ($treatments as $value) {
        //     $tr['id']=$value->id;
        //     $tr['treatment']=$value->treatment;
        //     $treatment[]=$tr;
        // }
        //dd($treatments);
        $template = DB::table('dealer_templates')->where('dealer_id', $result->dealer_id)->first();
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
                $result_models = DB::table('models')
                    ->select('id', 'model_name')
                    ->whereIn('id', $model)
                    ->get();
            } else {
                $result_models = "";
            }
        } else {
            $result_models = "";
        }

        $advisors = DB::table('advisors')->select('id', 'name')->where('dealer_id', $result->dealer_id)->where('status', 1)->get();

        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }
        return view('asm.editJob', [
            'result' => $result,
            'dealers' => $dealers,
            'users' => $users,
            'treatments' => $treatments,
            'result_models' => $result_models,
            'advisors' => $advisors,
            'selectTreatment' => $selectTreatment,
        ]);
    }

    // update existing job
    public function updateJob(Request $request)
    {
        $post = $request->all();
        $job_id  =  $request->id;
        $actual_price = 0;
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
            // $data['dealer_price'] = $post['dealer'][$i];
            $data['customer_price'] = $post['customer'][$i];
            // $data['incentive'] = $post['incentive'][$i];
            $data['job_type'] = $post['job_type'][$i];
            $data['actualPrice'] = @$post['actualPrice'][$i];
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
                $difference_price = $difference_price + $value['difference'];
            } else {
                $actual_price = $actual_price + 0;
                $difference_price = $difference_price + 0;
            }
            $customer_price = $customer_price + $value['customer_price'];
            // $dealer_price = $dealer_price + $value['dealer_price'];
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
            // $treat_id['dealer_price'] = $value['dealer_price'];
            // $treat_id['incentive'] = $value['incentive'];
            $treat_id['job_type'] = $value['job_type'];
            $treat_id['actualPrice'] = $value['actualPrice'];
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
            'difference_price' => $difference_price,
            // 'dealer_price' => $dealer_price,
            // 'incentive' => $incentive,
            'last_updated' => getCurrentTimestamp(),
            // 'foc_options' => $request->option,
        );
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
        // if(Session::has('job_url')){
        //     return redirect(Session::get('job_url'));
        // }else{
        return redirect('/asm/jobs');
        // }       
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
                return redirect('/asm/jobs');
            }
        } else {
            Session::flash('error', 'Something wrong!');
            return redirect('/asm/jobs');
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
            return redirect('/asm/jobs');
        } else {
            Session::flash('error', 'Please select job!');
            return redirect('/asm/jobs');
        }
    }

    public function jobsTreatmentList(Request $request)
    {
        $search = $request->search;
        $user_id = Auth::id();

        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();
        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }
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
            ->where('j.delete_job', 1)
            ->whereIn('j.dealer_id', $dealers)
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
        return view('asm.jobsTreatmentList', [
            'result' => $jobs_treatments,
            'dealers' => $dealers,
            'regn_no' => @$regn_no,
            'job_type' => @$type,
            'search' => @$search,
        ]);
    }

    // Add Service load from dashboard
    public function addServiceLoad(Request $request)
    {
        $post = $request->all();

        $data = array(
            // 'user_id' => Auth::user()->id,
            'user_id' => $post['user_id'],
            'dealer_id' => $post['dealer_id'],
            'total_jobs' => $post['total_jobs'],
            'job_added_date' => $post['service_date'],
        );
        DB::table('jobs_by_date')->insert($data);
        Session::flash('success', 'RO added successfully!');
        return redirect('/asm/jobs');
    }

    // Attendance 
    public function attendance(Request $request)
    {
        $user_id = Auth::id();
        $search = $request->all();
        // $dealers = User::where('role',2)->select('id','name')->where('status',1)->orderBy('name','ASC')->get();
        $getDealers = DB::table('users')
            ->where(['role' => 2, 'status' => 1])
            ->select('id', 'reporting_authority')
            ->get();

        foreach ($getDealers as $key => $value) {
            $authorities = explode(",", $getDealers[$key]->reporting_authority);
            if (in_array($user_id, $authorities)) {
                $dealers[] = $getDealers[$key]->id;
            }
        }

        $getStaff = User::join('staff_detail', 'users.id', '=', 'staff_detail.user_id')
            // ->join('emp_hierarchy', 'emp_hierarchy.user_id', '=', 'users.id')
            ->select('*', 'users.id as user_id')
            ->whereIn('role', [3, 4])
            ->whereIn('dealer_id', $dealers)
            ->where('reporting_authority', $user_id)
            ->get();

        $staff = array();
        foreach ($getStaff as $k => $val) {
            $staff[] = $getStaff[$k]->user_id;
        }

        if (!empty($search['employee']) && !empty($search['employeecode'])) {
            return back()->with('error', "You can't select both Employee and Employee Code together. Please Select only one.");
        }


        if (!empty($_GET['dealer'])) {

            $employees = User::whereIn('id', $staff)->select('id', 'name')->where('status', 1)->where('dealer_id', $_GET['dealer'])->orderBy('name', 'ASC')->get();
            $employees_ids = User::whereIn('id', $staff)->select('id', 'name')->where('status', 1)->where('dealer_id', $_GET['dealer'])->orderBy('name', 'ASC')->pluck('id');
            $empcodes = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->select('sd.user_id', 'sd.emp_code')->where('users.dealer_id', $_GET['dealer'])->where('users.status', 1)->where('sd.dol', '=', null)->orderBy('sd.emp_code', 'ASC')->get();
        } else {

            $employees = User::whereIn('id', $staff)->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $employees_ids = User::whereIn('id', $staff)->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->pluck('id');
            $empcodes = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->whereIn('users.id', $staff)->select('sd.user_id', 'sd.emp_code')->where('users.status', 1)->where('sd.dol', '=', null)->orderBy('sd.emp_code', 'ASC')->get();
        }


        if (count($staff) > 0) {

            // $attendance = DB::table('attendance')
            //     ->whereIn('user_id', $staff)
            //     ->where(function ($query) use ($search) {
            //         if (!empty($search)) {
            //             if (isset($search['dealer'])) {
            //                 if (!empty(trim($search['dealer']))) {
            //                     $query->where('dealer_id', '=', $search['dealer']);
            //                 }
            //             }
            //             if (isset($search['start_date']) && isset($search['end_date'])) {
            //                 if (!empty(trim($search['start_date']))) {
            //                     $query->whereDate('date', '>=', $search['start_date']);
            //                     $query->whereDate('date', '<=', $search['end_date']);
            //                 }
            //             } elseif (isset($search['end_date'])) {
            //                 if (!empty(trim($search['end_date']))) {
            //                     $query->whereDate('date', '<=', $search['end_date']);
            //                 }
            //             } elseif (isset($search['start_date'])) {
            //                 if (!empty(trim($search['start_date']))) {
            //                     $query->whereDate('date', '>=', $search['start_date']);
            //                 }
            //             } else {
            //                 $query->whereDate('date', date("Y-m-d"));
            //             }
            //         } else {
            //             $query->whereDate('date', date("Y-m-d"));
            //         }
            //     })->orderBy('user_id', 'ASC')->orderBy('date', 'ASC')->get();

            //     if (@$search['download']) {
            //     return Excel::create('Attendance_' . date("d-M-Y"), function ($excel) use ($attendance, $search) {
            //         $excel->sheet('sheet', function ($sheet) use ($attendance, $search) {
            //             $result = array();
            //             foreach ($attendance as $key => $value) {
            //                 $data['S. No.'] = $key + 1;
            //                 $data['User'] = get_name($value->user_id);
            //                 $data['Emp Code'] = get_emp_code($value->user_id);
            //                 $data['Dealer'] = get_name($value->dealer_id);
            //                 @$mydata1 =
            //                     month_hours_date($value->user_id, $search['start_date'], $search['end_date']);

            //                 $data['Total Hours This Month'] = @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes';

            //                 $result[] = $data;
            //             }
            //             $sheet->fromArray($result);
            //         });
            //     })->export('xlsx');
            // }

            if (isset($search['dealer']) && !empty(isset($search['dealer']))) {
                if (!empty($search['employee']) || !empty($search['employeecode'])) {
                    $attendance = DB::table('attendance')
                        ->whereIn('user_id', $staff)
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
                                        $query->whereDate('date', '=', $search['end_date']);
                                    }
                                } elseif (isset($search['start_date'])) {
                                    if (!empty(trim($search['start_date']))) {
                                        $query->whereDate('date', '=', $search['start_date']);
                                    }
                                } else {
                                    $query->whereDate('date', date("Y-m-d"));
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        })->orderBy('date', 'ASC')->get();


                    $dates = DB::table('attendance')
                        ->whereIn('user_id', $staff)
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
                        ->whereIn('user_id', $staff)
                        ->where(function ($query) use ($search, $employees_ids) {
                            if (!empty($search)) {
                                if (isset($search['dealer'])) {
                                    if (!empty(trim($search['dealer']))) {
                                        $query->where('dealer_id', '=', $search['dealer']);
                                        $query->whereIn('user_id', $employees_ids);
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
                                        // $query->whereIn('user_id',$employees_ids);
                                    }
                                }
                                if (isset($search['start_date']) && isset($search['end_date']) && !empty($search['start_date']) && !empty($search['end_date'])) {
                                    if (!empty(trim($search['start_date']))) {
                                        $query->whereDate('date', '>=', $search['start_date']);
                                        $query->whereDate('date', '<=', $search['end_date']);
                                    }
                                } elseif (isset($search['end_date']) && !empty($search['end_date'])) {
                                    if (!empty(trim($search['end_date']))) {
                                        $query->whereDate('date', '=', $search['end_date']);
                                    }
                                } elseif (isset($search['start_date']) && !empty($search['start_date'])) {
                                    if (!empty(trim($search['start_date']))) {
                                        $query->whereDate('date', '=', $search['start_date']);
                                    }
                                } else {
                                    $query->whereDate('date', date("Y-m-d"));
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        })->groupBy('user_id')->get();

                    $view = 0;
                }
            } elseif (isset($search['employee']) || isset($search['employeecode'])) {
                $attendance = DB::table('attendance')
                    ->whereIn('user_id', $staff)
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
                            if (isset($search['start_date']) && isset($search['end_date']) && !empty($search['start_date']) && !empty($search['end_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['end_date'])) {
                                if (!empty(trim($search['end_date']))) {
                                    $query->whereDate('date', '=', $search['end_date']);
                                }
                            } elseif (isset($search['start_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '=', $search['start_date']);
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                            }
                        } else {
                            $query->whereDate('date', date("Y-m-d"));
                        }
                    })->orderBy('date', 'ASC')->get();


                $dates = DB::table('attendance')
                    ->whereIn('user_id', $staff)
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

                $attendance1 = [];
                foreach ($dates as $key => $value) {
                    $id = searchForDate($value->date, $attendance);
                    $attendance1[] =  $id;
                }
                $attendance = $attendance1;
                $view = 2;
            } else {

                $attendance = DB::table('attendance')
                    ->whereIn('user_id', $staff)
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
                            if (isset($search['start_date']) && isset($search['end_date']) && !empty($search['start_date']) && !empty($search['end_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '>=', $search['start_date']);
                                    $query->whereDate('date', '<=', $search['end_date']);
                                }
                            } elseif (isset($search['end_date']) && !empty($search['end_date'])) {
                                if (!empty(trim($search['end_date']))) {
                                    $query->whereDate('date', '=', $search['end_date']);
                                }
                            } elseif (isset($search['start_date']) && !empty($search['start_date'])) {
                                if (!empty(trim($search['start_date']))) {
                                    $query->whereDate('date', '=', $search['start_date']);
                                }
                            } else {
                                $query->whereDate('date', date("Y-m-d"));
                                // $query->whereMonth('date', date("m"));
                            }
                        } else {
                            // $query->whereMonth('date', date("m"));
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

                                        $absent = strtok(absent_days_in_range($value->user_id, $_GET['start_date'], $_GET['end_date']), '-');
                                        $three = substr(absent_days_in_range($value->user_id, $_GET['start_date'], $_GET['end_date']), strrpos(absent_days_in_range($value->user_id, $_GET['start_date'], $_GET['end_date']), '-') + 1);



                                        // $data['Absent Days'] = absent_days_in_range($value->user_id, $search['start_date'], $search['end_date']) ? absent_days_in_range($value->user_id, $search['start_date'], $search['end_date']) : "0";
                                        $data['Absent Days'] = $three + $absent . " days" . "(" . $absent . " Absent + " . $three . " Less Than 3 Hours " . ")";

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
        } else {
            $attendance = [];
        }

        return view('asm.attendance', [
            'result' => $attendance,
            'dealers' => $dealers,
            'employees' => $employees,
            'empcodes' => $empcodes,
            'dealer' => @$search['dealer'],
            'emp' => @$search['employee'],
            'empcode' => @$search['employeecode'],
            'start_date' => @$search['start_date'],
            'end_date' => @$search['end_date'],
            'view'  =>  $view,
            'ids' => $employees_ids
        ]);
    }


    // View Daily report by dealer and advisor
    public function dailyReport(Request $request)
    {
        $search = $request->all();
        $user_id = Auth::id();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['report_type'])) {
            $type = $search['report_type'];
        } else {
            $type = 'dealer';
        }
        if (!empty($search['dealer'])) {
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($user_id, $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
            $oems = User::where('status', 1)->where('id', $search['dealer'])->select('oem_id')->groupBy('oem_id')->get();
            $groups = User::where('status', 1)->where('id', $search['dealer'])->select('group_id')->groupBy('group_id')->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->where('status', 1)->where('dealer_id', $search['dealer'])->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        } else {
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($user_id, $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    $d_ids[] = $dealer_ids[$i]->id;
                }
            }
            $oems = DB::table('oems')->where('status', 1)->get();
            $groups = DB::table('groups')->where('status', 1)->get();
            $allAdvisors = DB::table('advisors')->select('*', 'id as advisor_id')->whereIn('dealer_id', $d_ids)->where('status', 1)->orderBy('dealer_id', 'ASC')->get();
            $departments = DB::table('dealer_department')->where('status', 1)->get();
        }
        /************************************ Dealer Wise Report Start *************************/
        $result = DB::table('jobs as j')
            ->select('j.*')
            ->where(function ($query) use ($search, $d_ids) {
                if (!empty($search)) {
                    // if(empty(trim($search['dealer']))){
                    //     $query->whereIn('j.dealer_id',$d_ids);
                    // }
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
            ->whereIn('j.dealer_id', $d_ids)
            ->orderBy('j.job_date', 'ASC')
            ->get();
        $array = array();
        $result1 = array();
        $total_incentive = 0;
        foreach ($result as $key => $value) {
            $array['dealer_id'] = $value->dealer_id;
            $array['job_date'] = $value->job_date;
            $array['job_card_no'] = $value->job_card_no;
            $array['bill_no'] = $value->bill_no;
            $array['regn_no'] = $value->regn_no;
            $array['advisor_id'] = $value->advisor_id;
            $array['model_id'] = $value->model_id;
            $array['remarks'] = $value->remarks;
            $array['foc_options'] = $value->foc_options;
            $total_incentive = $total_incentive + $value->incentive;
            $decoded = json_decode($value->treatments);

            foreach ($decoded as $val) {
                $array['labour_code'] = $val->labour_code;
                $array['job_type'] = @$val->job_type;
                $array['treatment_name'] = $val->treatment;
                $array['customer_price'] = $val->customer_price;
                $array['actual_price'] = @$val->actualPrice;
                $array['difference_price'] = @$val->difference;
                $array['dealer_price'] = @$val->dealer_price;
                $array['incentive'] = $val->incentive;

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
        /************************************ Dealer Wise Report End *************************/

        /************************************ Advisor Wise Report Start *************************/
        $data = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as vas_customer_price, SUM(actual_price) as vas_actual_price, SUM(difference_price) as vas_difference, SUM(hvt_value) as hvt_customer_price,SUM(hvt_value) as hvt_actual_price, SUM(incentive) as vas_incentive, advisor_id, job_date'))
            ->whereIn('dealer_id', $d_ids)
            ->where(function ($query) use ($search) {
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
                // $advisor['vas_customer_price'] = $value->vas_customer_price;
                // $advisor['vas_incentive'] = $value->vas_incentive;
                $advisor['vas_customer_price'] = $customer_price;
                $advisor['vas_incentive'] = $incentive;
                $advisor['vas_actual_price'] = $value->vas_actual_price;
                $advisor['vas_difference'] = $value->vas_difference;
                $advisor['hvt_customer_price'] = $value->hvt_customer_price;
                $advisor['hvt_actual_price'] = $value->hvt_actual_price;
                $advisor['hvt_incentive'] = $hvt_incentive;
                $advisors[] = $advisor;
                @$total_service = DB::table('jobs_by_date')
                    ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                    ->whereIn('dealer_id', $d_ids)
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

                //dd($total_service);

                @$total_jobs = DB::table('jobs')
                    ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(actual_price) as mtd_actual_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                    // ->where('foc_options',5)
                    ->whereIn('dealer_id', $d_ids)
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

        // brand list start
        $brands = DB::table("product_brands")->where('status', 1)->get();
        // brand list end
        // dd($result4);
        if (!empty(request()->brand)) {


            // filter by brand id 
            $a =  array_filter($result1, fn ($value) => in_array(request()->brand, $value['brands']));

            $result1 = $a;
        }


        Session::put('oldReport', $type);
        return view('asm.dailyReport', [
            'result' => $result1,
            'brands' => $brands,
            'total_incentive' => $total_incentive,
            'advisors' => $advisors,
            'allAdvisors' => $allAdvisors,
            'oldAdvisor' => @$search['advisor'],
            'total_job_array' => @$total_job_array,
            'dealers' => $dealers,
            'oems' => $oems,
            'groups' => $groups,
            'oldFromDate' => @$search['from'],
            'oldToDate' => @$search['to'],
            'oldDealer' => @$search['dealer'],
            'oldSelectMonth' => @$search['month1'],
            'oldReport' => @$type,
            'oldOem' => @$search['oem'],
            'oldGroup' => @$search['group'],
            'departments' => $departments,
            'oldDepartment' => @$search['department'],
        ]);
    }


    // Download Report
    public function downloadReport(Request $request)
    {
        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }
        $search = $request->all();
        if ($search['report'] == 'dealer') {
            $result = DB::table('jobs as j')
                ->select('j.*')
                // ->where('foc_options',5)
                ->where(function ($query) use ($search, $d_ids) {
                    if (!empty($search)) {
                        if (empty(trim($search['dealer1']))) {
                            $query->whereIn('j.dealer_id', $d_ids);
                        }
                        if (isset($search['dealer1'])) {
                            if (!empty(trim($search['dealer1']))) {
                                $query->where('j.dealer_id', '=', $search['dealer1']);
                            }
                        }
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
                ->whereIn('j.dealer_id', $d_ids)
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
                            if (@$val->job_type == 5) {
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
                ->whereIn('dealer_id', $d_ids)
                ->groupBy('advisor_id')
                ->get();
            $advisors = array();
            $i = $mtd_total = 0;
            $total_job_array = array();
            if (count($data) > 0) {
                foreach ($data as $value) {
                    $hvt_incentive = 0;
                    $decoded_jobs = explode(',', $value->job_id);
                    foreach ($decoded_jobs as $key => $val) {
                        $customer_price = $incentive = 0;
                        $treat = DB::table('jobs')->select('treatments')->where('id', $val)->first();
                        $decoded_treatments = json_decode($treat->treatments);
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
                    $advisor['advisor_id'] = $value->advisor_id;
                    // $advisor['vas_customer_price'] = round($value->vas_customer_price);
                    // $advisor['vas_incentive'] = round($value->vas_incentive);
                    $advisor['vas_customer_price'] = round($customer_price);
                    $advisor['vas_incentive'] = round($incentive);
                    $advisor['vas_actual_price'] = round($value->vas_actual_price);
                    $advisor['vas_difference'] = round($value->vas_difference);
                    $advisor['hvt_customer_price'] = round($value->hvt_customer_price);
                    $advisor['hvt_actual_price'] = round($value->hvt_actual_price);
                    $advisor['hvt_incentive'] = round($hvt_incentive);
                    $advisors[] = $advisor;
                    @$total_service = DB::table('jobs_by_date')
                        ->select(DB::raw('SUM(total_jobs) as mtd_total'))
                        ->whereIn('dealer_id', $d_ids)
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
                        ->whereIn('dealer_id', $d_ids)
                        ->select(DB::raw('SUM(vas_value) as mtd_vas_value,SUM(vas_total) as mtd_vas_total, SUM(hvt_value) as mtd_hvt_value,SUM(hvt_total) as mtd_hvt_total'))
                        ->where(function ($query) use ($search, $first_day, $today, $value) {
                            if (!empty($search)) {
                                if (isset($search['dealer2'])) {
                                    if (!empty(trim($search['dealer2']))) {
                                        $query->where('dealer_id', '=', $search['dealer2']);
                                    }
                                }
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

                    @$total_job_array = array(
                        'mtd_total' => round(@$total_service->mtd_total),
                        'mtd_vas_value' => round(@$total_jobs->mtd_vas_value),
                        'mtd_actual_value' => round(@$total_jobs->mtd_actual_value),
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
                    $sheet->setCellValue('I2', @$total_job_array['mtd_total']);
                    $sheet->setCellValue('H3', 'VAS');
                    $sheet->mergeCells("H3:I3");
                    $sheet->setCellValue('H4', 'No of Trmt');
                    $sheet->setCellValue('I4', @$total_job_array['mtd_vas_total']);
                    $sheet->setCellValue('H5', 'Amount');
                    // $sheet->setCellValue('I5',$total_job_array['mtd_vas_value']);
                    $sheet->setCellValue('I5', @$total_job_array['mtd_actual_value']);
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

    // View Mis report
    public function misReport(Request $request)
    {
        $search = $request->all();
        $user_id = Auth::id();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');

        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }

        /************************************ MIS Report Start *************************/
        // $users = DB::table('users')
        //           ->select('id')
        //           ->where('users.role',2)
        //           ->orderBy('users.name','ASC')
        //           ->get();

        $oems = User::where('status', 1)->whereIn('id', $d_ids)->select('oem_id')->groupBy('oem_id')->get();

        $groups = User::where('status', 1)->whereIn('id', $d_ids)->select('group_id')->groupBy('group_id')->get();

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

        foreach ($mist as $key => $value2) {

            $total = DB::table('jobs_by_date')
                ->select(DB::raw('SUM(total_jobs) as total_jobs,dealer_id'))
                ->where('dealer_id', $value2['dealer_id'])
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

        return view('asm.misReport', [
            'mis' => $mist,
            'oems' => $oems,
            'groups' => $groups,
            'oldFromDate1' => @$search['from1'],
            'oldToDate1' => @$search['to1'],
            'oldMonth' => @$search['month'],
        ]);
    }

    // Download MIS
    public function downloadMIS(Request $request)
    {
        $search = $request->all();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');

        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
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
                ->first();
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
                    $array['Service_Load'] = $val['service_load'];
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


    // View DCF report
    public function dcfReport(Request $request)
    {
        $search = $request->all();
        $user_id = Auth::id();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');
        if (!empty($search['report_type'])) {
            $type = $search['report_type'];
        } else {
            $type = 'dealer';
        }

        /************************************ DCF Report Start *******************************/
        if (@$search['selectMonth']) {
            $monthYear = explode('-', $search['selectMonth']);
            $year = $monthYear[0];
            $month =  $monthYear[1];
            $model = array();
            $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
            $dealers = array();
            $d_ids = array();
            foreach ($dealer_ids as $i => $j) {
                $report_ids = explode(",", $j->reporting_authority);
                if (in_array($user_id, $report_ids)) {
                    $dealers[] = $dealer_ids[$i];
                    // $d_ids[]=$dealer_ids[$i]->id;
                }
            }
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
                                    // ->where('foc_options',5)
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
        return view('asm.dcf_report', [
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

    // view Consumption Report 
    public function consumptionReport(Request $request)
    {
        $search = $request->all();
        if (@$search['selectMonth']) {
            $month = explode('-', $search['selectMonth']);
        } else {
            $month = explode('-', date('Y-m'));
        }
        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }
        // $dealers = User::where('role',2)->select('id','name')->get();
        $result = DB::table('jobs')
            ->whereIn('dealer_id', $d_ids)
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
        return view('asm.consumptionReport', [
            'treatments' => @$treatments,
            'dealers' => $dealers,
            'oldDealer' => @$search['dealer'],
            'oldMonth' => @$search['selectMonth'],
            'products' => array_values(@$newarray),
        ]);
        // echo "<pre>";
        // print_r(array_values($newarray));
    }

    public function downloadPerformanceSheet(Request $request)
    {
        $search = $request->all();
        $current = date('Y-m');
        if (@$search['selectMonth1']) {
            $monthYear = explode('-', $search['selectMonth1']);
        } else {
            $monthYear = explode('-', $current);
        }
        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }
        $advisors = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as customer_price, SUM(incentive) as incentive, SUM(dealer_price) as dealer_price,SUM(hvt_value) as hvt_value,SUM(hvt_total) as hvt_total, advisor_id,dealer_id, job_date'))
            ->whereIn('dealer_id', $d_ids)
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
                $sheet->fromArray($result);
            });
        })->export('xlsx');
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
        $user_id = Auth::id();
        $dealer_ids = DB::table('users')->select('id', 'name', 'reporting_authority')->where(['role' => 2, 'status' => 1])->orderBy('id', 'DESC')->get();
        $dealers = array();
        $d_ids = array();
        foreach ($dealer_ids as $i => $j) {
            $report_ids = explode(",", $j->reporting_authority);
            if (in_array($user_id, $report_ids)) {
                $dealers[] = $dealer_ids[$i];
                $d_ids[] = $dealer_ids[$i]->id;
            }
        }
        // $dealers = User::where('role',2)->select('id','name')->orderBy('name','ASC')->get();
        $advisors = DB::table('jobs')
            ->select(DB::raw('group_concat(id) as job_id, SUM(customer_price) as customer_price, SUM(incentive) as incentive, SUM(dealer_price) as dealer_price,SUM(hvt_value) as hvt_value, advisor_id,dealer_id,job_date'))
            ->whereIn('dealer_id', $d_ids)
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
        return view('asm.performance_reports', [
            'dealers' => $dealers,
            'advisors' => $advisors,
            'oldDealer' => @$search['dealer'],
            'oldMonth' => @$search['selectMonth'],
        ]);
    }

    public function view_attendance($id)
    {
        $list = array();

        if (isset($_GET['selectMonth']) && !empty($_GET['selectMonth'])) {
            $month = date('m', strtotime($_GET['selectMonth']));
            $year = date('Y', strtotime($_GET['selectMonth']));;
            $day = 31;
        } else {
            $month = date('m');
            $year = date('Y');
            $day = date('d');
        }

        for ($d = 1; $d <= $day; $d++) {
            $time = mktime(12, 0, 0, $month, $d, $year);
            if (date('m', $time) == $month)
                $list[] = date('Y-m-d', $time);
        }

        foreach ($list as $key => $value) {

            $result = DB::table('attendance')->where('user_id', $id)->whereDate('date', $value)->get();
            if ($result->count() == 0) {
                $nulldata = new \stdClass();
                // $result->date = $list[$key];
                $nulldata->date = $list[$key];
                $result[] = $nulldata;
            }
            $data[] = $result;
        }

        if (isset($_GET['download']) && ($_GET['download'] == 1)) {
            return Excel::create('MonthlyAttendanceReport_' . date("d-M-Y"), function ($excel) use ($data, $id) {
                $excel->sheet('sheet', function ($sheet) use ($data, $id) {
                    $result = array();
                    $file = array();
                    $notmarked = 0;
                    $half = 0;
                    $full = 0;
                    $i = 2;
                    $three = 0;

                    foreach ($data as $key1 => $value1) {

                        $row = count($data[$key1]);
                        foreach ($data[$key1] as $key => $value) {
                            $file['Date'] = date('d M, Y', strtotime($data[$key1][0]->date));
                            if ($row > 1) {
                                if ($key == 0) {
                                    $end = $i + $row - 1;
                                    $sheet->mergeCells('A' . $i . ':' . 'A' . $end);
                                    $sheet->mergeCells('G' . $i . ':' . 'G' . $end);
                                    $sheet->cells('A' . $i, function ($cells) {
                                        $cells->setValignment('center');
                                    });
                                    $sheet->cells('G' . $i, function ($cells) {
                                        $cells->setValignment('center');
                                    });
                                }
                            }

                            if (isset($value->user_id)) {
                                $myid = $value->user_id;
                                $file['In Time'] = $value->in_time;
                                $file['Out Time'] = $value->out_time;
                                $file['User'] = get_name($value->user_id);
                                $file['Dealer'] = get_name($value->dealer_id);
                                $timeFirst = strtotime($value->in_time);
                                $timeSecond = strtotime($value->out_time);
                                if (!empty($timeFirst) && !empty($timeSecond)) {
                                    @$differenceInSeconds = secToHR($timeSecond - $timeFirst);
                                    $file['This Dealer Hours'] =  @$differenceInSeconds['hours'] . ' hours ' . @$differenceInSeconds['minutes'] . ' minutes';
                                } else {
                                    $notmarked += 1;
                                    $file['This Dealer Hours'] = 'Attendance Not Marked';
                                }


                                if (!empty($timeFirst) && !empty($timeSecond)) {
                                    @$data_total_hours = day_hours($value->user_id, $value->date);
                                    $file['Total Hours Today'] = @$data_total_hours['hours'] . ' hours ' .
                                        @$data_total_hours['minutes'] . ' minutes';

                                    if ((@$data_total_hours['hours'] < 6 && (@$data_total_hours['hours'] >= 3))) {
                                        $half += 1;
                                    } elseif (@$data_total_hours['hours'] > 6) {
                                        $full += 1;
                                    } elseif (@$data_total_hours['hours'] < 3) {
                                        $three += 1;
                                    }
                                } else {
                                    $file['Total Hours Today'] = 'Attendance Not Marked';
                                }

                                $range = 'A' . $i . ':' . 'G' . $i;
                                $sheet->setBorder($range);
                                $sheet->cells($range, function ($cells) {
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });


                                $i = $i + 1;
                            } else {
                                $range = 'A' . $i . ':' . 'G' . $i;
                                $sheet->setBorder($range);
                                $sheet->cells($range, function ($cells) {
                                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                });

                                $sheet->cells('B' . $i . ':' . 'G' . $i, function ($cells) {
                                    $cells->setBackground('#dd4b39');
                                });

                                $sheet->mergeCells('B' . $i . ':' . 'G' . $i);
                                $sheet->cell('B' . $i . ':' . 'G' . $i, function ($cell) {
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

                    $sheet->setCellValue('A' . $i, 'Total Hours This Month :');
                    $sheet->mergeCells('A' . $i . ':' . 'E' . $i);
                    $sheet->setBorder('A' . $i . ':' . 'E' . $i);
                    $sheet->cell('A' . $i . ':' . 'E' . $i, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    @$mydata1 = month_hours($myid);
                    $month_hours =  @$mydata1['hours'] . ' hours ' . @$mydata1['minutes'] . ' minutes';
                    $sheet->setCellValue('F' . $i, $month_hours);
                    $sheet->mergeCells('F' . $i . ':' . 'G' . $i);
                    $sheet->setBorder('F' . $i . ':' . 'G' . $i);
                    $sheet->cell('F' . $i . ':' . 'G' . $i, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $i1 = $i + 1;
                    $sheet->setCellValue('A' . $i1, 'Total Full Days');
                    $sheet->mergeCells('A' . $i1 . ':' . 'E' . $i1);
                    $sheet->setBorder('A' . $i1 . ':' . 'E' . $i1);
                    $sheet->cell('A' . $i1 . ':' . 'E' . $i1, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('F' . $i1, $full);
                    $sheet->mergeCells('F' . $i1 . ':' . 'G' . $i1);
                    $sheet->setBorder('F' . $i1 . ':' . 'G' . $i1);
                    $sheet->cell('F' . $i1 . ':' . 'G' . $i1, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $i2 = $i + 2;
                    $sheet->setCellValue('A' . $i2, 'Total Half Days :');
                    $sheet->mergeCells('A' . $i2 . ':' . 'E' . $i2);
                    $sheet->setBorder('A' . $i2 . ':' . 'E' . $i2);
                    $sheet->cell('A' . $i2 . ':' . 'E' . $i2, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('F' . $i2, $half);
                    $sheet->mergeCells('F' . $i2 . ':' . 'G' . $i2);
                    $sheet->setBorder('F' . $i2 . ':' . 'G' . $i2);
                    $sheet->cell('F' . $i2 . ':' . 'G' . $i2, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $i3 = $i + 3;
                    $sheet->setCellValue('A' . $i3, 'Working Hours Less Than 3 :');
                    $sheet->mergeCells('A' . $i3 . ':' . 'E' . $i3);
                    $sheet->setBorder('A' . $i3 . ':' . 'E' . $i3);
                    $sheet->cell('A' . $i3 . ':' . 'E' . $i3, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('F' . $i3, $three);
                    $sheet->mergeCells('F' . $i3 . ':' . 'G' . $i3);
                    $sheet->setBorder('F' . $i3 . ':' . 'G' . $i3);
                    $sheet->cell('F' . $i3 . ':' . 'G' . $i3, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $i4 = $i + 4;
                    $sheet->setCellValue('A' . $i4, 'Days Attendance Not Marked :');
                    $sheet->mergeCells('A' . $i4 . ':' . 'E' . $i4);
                    $sheet->setBorder('A' . $i4 . ':' . 'E' . $i4);
                    $sheet->cell('A' . $i4 . ':' . 'E' . $i4, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->setCellValue('F' . $i4, @$notmarked);
                    $sheet->mergeCells('F' . $i4 . ':' . 'G' . $i4);
                    $sheet->setBorder('F' . $i4 . ':' . 'G' . $i4);
                    $sheet->cell('F' . $i4 . ':' . 'G' . $i4, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });


                    $sheet->setCellValue('H' . 1, 'Name :');
                    $sheet->mergeCells('H' . 1 . ':' . 'J' . 1);
                    $sheet->setBorder('H' . 1 . ':' . 'J' . 1);
                    $sheet->cell('H' . 1 . ':' . 'J' . 1, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('K' . 1, get_name($id));
                    $sheet->mergeCells('K' . 1 . ':' . 'M' . 1);
                    $sheet->setBorder('K' . 1 . ':' . 'M' . 1);
                    $sheet->cell('K' . 1 . ':' . 'M' . 1, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('H' . 2, 'Employee Code : ');
                    $sheet->mergeCells('H' . 2 . ':' . 'J' . 2);
                    $sheet->setBorder('H' . 2 . ':' . 'J' . 2);
                    $sheet->cell('H' . 2 . ':' . 'J' . 2, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('K' . 2, get_emp_code($id));
                    $sheet->mergeCells('K' . 2 . ':' . 'M' . 2);
                    $sheet->setBorder('K' . 2 . ':' . 'M' . 2);
                    $sheet->cell('K' . 2 . ':' . 'M' . 2, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('H' . 3, 'Current Dealer : ');
                    $sheet->mergeCells('H' . 3 . ':' . 'J' . 3);
                    $sheet->setBorder('H' . 3 . ':' . 'J' . 3);
                    $sheet->cell('H' . 3 . ':' . 'J' . 3, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->setCellValue('K' . 3, get_dealer_name_by_id($id));
                    $sheet->mergeCells('K' . 3 . ':' . 'M' . 3);
                    $sheet->setBorder('K' . 3 . ':' . 'M' . 3);
                    $sheet->cell('K' . 3 . ':' . 'M' . 3, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $range = 'A1:G1';
                    $sheet->setBorder($range);
                    $sheet->cells($range, function ($cells) {
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        $cells->setFontWeight('bold');
                    });

                    $sheet->fromArray($result);
                });
            })->export('xlsx');
        }

        // $result = DB::table('attendance')->where('user_id', $id)->whereMonth('date', date('m'))->get();
        return view('admin/view_attendance')->with(['result' => $data, 'id' => $id]);
    }

    public function daily_attendance($dealer_id = null)
    {
        $total_present = 0;
        if (!empty($dealer_id)) {
            $employees =  DB::table('users')->where('dealer_id', $dealer_id)->orwhere('dealer_office', $dealer_id)->get();
            if (count($employees) == 0) {
                return back()->with('error', 'No Data Found');
            }
            foreach ($employees as $key => $value) {

                if (isset($_GET['selectDate']) && !empty($_GET['selectDate'])) {
                    $employees_present =  DB::table('attendance')->where('user_id', $value->id)->where('date', $_GET['selectDate'])->first();
                } else {
                    $employees_present =  DB::table('attendance')->where('user_id', $value->id)->where('date', date('Y-m-d'))->first();
                }

                if (!empty($employees_present)) {
                    $employees[$key]->present_status = "Present";
                } else {
                    $employees[$key]->present_status = "Absent";
                }
            }
            return view('asm/dailyattendance')->with('employees', $employees);
        } else {
            $firms = DB::table('firms')->get();
            $user_id = Auth::id();
            $getDealers = DB::table('users')
                ->where(['role' => 2, 'status' => 1])
                ->where(function ($query) {
                    if (!empty($_GET['firm_id'])) {
                        $query->where('firm_id', $_GET['firm_id']);
                    }
                })->select('id', 'reporting_authority')
                ->orderBy('name', 'asc')
                ->get();

            foreach ($getDealers as $key => $value) {
                $authorities = explode(",", $getDealers[$key]->reporting_authority);
                if (in_array($user_id, $authorities)) {
                    $dealers[] = $getDealers[$key]->id;
                }
            }
            $result = $dealers;

            foreach ($result as $key => $value) {
                $employees =  DB::table('users')->whereIn('role', [3, 4])->where('dealer_id', $result[$key])->get();
                foreach ($employees as $key1 => $value1) {
                    if (isset($_GET['selectDate']) && !empty($_GET['selectDate'])) {
                        $employees_present =  DB::table('attendance')->where('user_id', $employees[$key1]->id)->where('date', $_GET['selectDate'])->first();
                    } else {
                        $employees_present =  DB::table('attendance')->where('user_id', $employees[$key1]->id)->where('date', date('Y-m-d'))->first();
                    }

                    if (!empty($employees_present)) {
                        $total_present += 1;
                    }
                }
                $result[$key] = new \stdClass();
                $result[$key]->dealer_id = $value;
                $result[$key]->total = $employees;
                $result[$key]->present = $total_present;
                $total_present = 0;
            }
        }

        return view('asm/dailyattendance')->with('result', $result)->with('firms', $firms);
    }
}
