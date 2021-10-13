<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\User;
use Excel;

class attendanceController extends Controller
{
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

                    $month = ((@$_GET['selectMonth']) ? date('m', strtotime(@$_GET['selectMonth'])) : date('m'));
                    @$mydata1 = month_hours($id, $month);

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

        return view('admin/view_attendance')->with(['result' => $data, 'id' => $id]);
    }

    public function mark_attendance()
    {
        $dealers = User::where('role', 2)->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();

        if (!empty($_GET['dealer_id_hidden'])) {
        $employees = User::whereIn('role', [3, 4, 5])->select('id', 'name')->where('status', 1)->where('dealer_id',$_GET['dealer_id_hidden'])->orderBy('name', 'ASC')->get();          
         $empcodes = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->select('sd.user_id', 'sd.emp_code')->where('users.dealer_id',$_GET['dealer_id_hidden'])->where('users.status', 1)->where('sd.dol', '=', null)->orderBy('sd.emp_code', 'ASC')->get();   
    }else{
        $employees = User::whereIn('role', [3, 4, 5])->select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
         $empcodes = User::join('staff_detail as sd', 'sd.user_id', '=', 'users.id')->select('sd.user_id', 'sd.emp_code')->where('users.status', 1)->where('sd.dol', '=', null)->orderBy('sd.emp_code', 'ASC')->get();
    }

       

        return view('admin/markattendance')->with([
            'dealers' => $dealers,
            'employees' => $employees,
            'empcodes' => $empcodes,
        ]);
    }

    public function mark_attendance_post(Request $request)
    {
        $post = $request->all();

        if (!empty($post['employee']) && !empty($post['employeecode'])) {
            return back()->with('error', "You can't select both Employee and Employee Code together. Please Select only one.");
        }
        $data = array(
            'dealer_id' => $post['dealer'],
            'in_time' => $post['in_time'],
            'out_time' => $post['out_time'],
            'date' => $post['start_date'],
            'attendance_status' => 2,
            'created_at' => getCurrentTimestamp()
        );
        if (!empty($post['employeecode'])) {
            $data['user_id'] = $post['employeecode'];
        } else {
            $data['user_id'] = $post['employee'];
        }

        DB::table('attendance')->insert($data);
        return back()->with('success', 'Attendance Marked Successfully');
    }

    public function daily_attendance(Request $request,$dealer_id = null)
    {
        $total_present = 0;
        if (!empty($dealer_id)) {
            $employees =  DB::table('users')->where('dealer_id', $dealer_id)->orwhere('dealer_office', $dealer_id)->get();

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

            return view('admin/dailyattendance')->with('employees', $employees);
        } else {
             
            $firms = DB::table('firms')->get();
            $asms = DB::table('users')->select('id', 'name')->where(['role' => 5, 'status' => 1])->get();

            $result = DB::table('users')->whereIn('role', [3, 4])->where('dealer_id', '!=', null)
           ->where(function ($query) use ($request) {
           
            if (!empty($_GET['firm_id'])) {
           $query->where('firm_id',$_GET['firm_id']);
           }
           if (!empty($_GET['asm_id']) ) {
               $asm_id = $_GET['asm_id'];
              $query->whereRaw("find_in_set($asm_id,reporting_authority)");
           }
           })->groupBy('dealer_id')->select('dealer_id')->get()->toArray();

            $result1 = DB::table('users')->whereIn('role', [3, 4, 5])->where('dealer_office', '!=', null)
            // ->groupBy('dealer_office')->select('dealer_office')->get()->toArray();
            ->where(function ($query) use ($request) {

            if (!empty($_GET['firm_id'])) {
            $query->where('firm_id',$_GET['firm_id']);
            }

            if (!empty($_GET['asm_id']) ) {
            $asm_id = $_GET['asm_id'];
            $query->whereRaw("find_in_set($asm_id,reporting_authority)");
            }
            })->groupBy('dealer_office')->select('dealer_office')->get()->toArray();

            $result = array_merge($result, $result1);


            foreach ($result as $key => $value) {
            
                if (isset($result[$key]->dealer_id)) {
                    $employees =  DB::table('users')->whereIn('role', [3, 4])->where('dealer_id', $result[$key]->dealer_id)->get();
                    $result[$key]->dealer_name = get_name($result[$key]->dealer_id);
                }elseif (isset($result[$key]->dealer_office)) {
                    $employees =  DB::table('users')->whereIn('role', [3, 4, 5])->where('dealer_office', $result[$key]->dealer_office)->get();
                    $result[$key]->dealer_name = get_name($result[$key]->dealer_office);
                }


                if (!isset($result[$key]->dealer_id)) {
                    
                    $result[$key]->dealer_id = $result[$key]->dealer_office;
                }

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

                $result[$key]->total = $employees;
                $result[$key]->present = $total_present;
                $total_present = 0;
            }

            // dd($result);
            $dealer_name = array_map('strtolower', array_column($result, 'dealer_name'));
            $result1 = array_multisort($dealer_name, SORT_ASC, $result);

            return view('admin/dailyattendance')->with(['result'=>$result,'firms'=>$firms,'asms'=>$asms]);
        }
    }
}
