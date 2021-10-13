<!DOCTYPE html>
<html>
    <head>
        <title>Auto Solutions</title>
        <style type="text/css">
            body{font-family: Helvetica,sans-Serif;color: #77798c;font-size: 14px;}
        </style>
    </head>
    <style type="text/css">
        table, th {
        font-size: 12px;
        }
    </style>
    <body>
        <table cellspacing="0" cellpadding="0" width="100%" style="padding: 0;">
            <tbody>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" width="600px" style="margin: 0px auto 0px; text-align: center; box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1); border-radius: 4px; background-image: url('{{ asset('bg11.png') }}');     background-size: cover; background-position: center center; padding:40px;" >
                            <tbody>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" width="100%" style="margin: 0 auto; text-align: center; box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1); border-radius: 4px;" bgcolor="#fff" >
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <table width="100%" style="background-image: url('{{ asset('bg-img1.png') }}');background-size: cover; background-position: center center; padding: 120px;border-radius: 4px 4px 0 0;
                                                            ">
                                                            <tbody>
                                                                <tr>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <p style="text-align: left; padding: 10px 0 0 20px;"><b>Dear Sir,</b><br><br>
                                                <b>Greetings from 3M Car Care !!!</b><br><br>
                                                <b>The VAS MIS for the date is tabulated below:</b><br></p> 
                                                <tr>
                                                    <td style="padding:15px 15px  0 15px;">
                                                        <table width="100%" border="1" style="margin:0 auto; text-align: left; border-collapse: collapse;"  cellpadding="5">
                                                            <tbody>
                                                                <tr style="text-align: center;">
                                                                    <th colspan="3" >MIS</th>
                                                                </tr>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Today</th>
                                                                    <th>MTD</th>
                                                                </tr>
                                                                <tr>
                                                                    <td>Service Load</td>
                                                                    <td>{{@$total_job_array['total']}}</td>
                                                                    <td>{{@$total_job_array['mtd_total']}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3"><b>VAS</b></td>
                                                                    <!-- <td></td> -->
                                                                </tr>
                                                                <tr>
                                                                    <td>No of Trmt</td>
                                                                    <td>{{@$total_job_array['vas_total']}}</td>
                                                                    <td>{{@$total_job_array['mtd_vas_total']}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Amount</td>
                                                                    <td>{{@$total_job_array['vas_value']}}</td>
                                                                    <td>{{@$total_job_array['mtd_vas_value']}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Value Per Treatment</td>
                                                                    <th>{{vas_in_percentage(@$total_job_array['vas_value'],@$total_job_array['vas_total'])}}</th>
                                                                    <th>{{vas_in_percentage(@$total_job_array['mtd_vas_value'],@$total_job_array['mtd_vas_total'])}}</th>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3"><b>HVT</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>No of Trmt</td>
                                                                    <td>{{@$total_job_array['hvt_total']}}</td>
                                                                    <td>{{@$total_job_array['mtd_hvt_total']}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Amount</td>
                                                                    <td>{{@$total_job_array['hvt_value']}}</td>
                                                                    <td>{{@$total_job_array['mtd_hvt_value']}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>HVT %</td>
                                                                    <th>{{hvt_in_percentage(@$total_job_array['hvt_value'],@$total_job_array['vas_value'])}}%</th>
                                                                    <th>{{hvt_in_percentage(@$total_job_array['mtd_hvt_value'],@$total_job_array['mtd_vas_value'])}}%</th>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 15px;">
                                                        <h3 style="text-transform: uppercase; text-align: center; font-size: 14px;">Advisor Wise Performance</h3>
                                                        <table width="100%" border="1" style="margin:0 auto; text-align: left; border-collapse: collapse;"  cellpadding="5">

                                                            <tbody>
                                                               
                                                                <!-- <tr style="text-align: center;">
                                                                    <th width="100px">Advisor Name</th>
                                                                    <th colspan="4" >MTD</th>
                                                                </tr> -->
                                                                <tr>
                                                                    <th width="100px">Advisor Name</th>
                                                                    <td colspan="4" style="text-align: center;"><b>VAS</b></td>
                                                                    <td  colspan="4" style="text-align: center;"><b>HVT</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="2"><b>Cust Billing</b></td>
                                                                    <td colspan="2"><b>Incentive</b></td>
                                                                    <td colspan="2"><b>Cust Billing</b></td>
                                                                    <td colspan="2"><b>Incentive</b></td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td style="background-color: yellow;"><b>Today</b></td>
                                                                    <td><b>MTD</b></td>
                                                                    <td colspan="2"><b>MTD</b></td>
                                                                    <td colspan="2"><b>MTD</b></td>
                                                                    <td colspan="2"><b>MTD</b></td>
                                                                </tr>
                                                                @foreach($advisors as $val)
                                                                <tr>
                                                                    <td>{{get_advisor_name($val['advisor_id'])}}</td>
                                                                    <td style="background-color: yellow;">{{round($val['today_vas_customer_price'])}}</td>
                                                                    <td>{{round($val['vas_customer_price'])}}</td>
                                                                    <td colspan="2">{{round($val['vas_incentive'])}}</td>
                                                                    <td colspan="2">{{round($val['hvt_customer_price'])}}</td>
                                                                    <td colspan="2">{{round($val['hvt_incentive'])}}</td>
                                                                </tr>
                                                                @endforeach
                                                                
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                               <tr>
                                                   <td>
                                                        <p style="text-align: left; padding: 10px 0 0 20px; font-size: 12px; color:#333">
                                                        <b>We sincerely Thank You and the team for all the support.</b>
                                                        </p>
                                                   </td>
                                               </tr>
                                                <tr>

                                                    <td>
                                                        <p style="text-align: left; padding: 10px 0 0 20px; font-size: 12px; color:#333">
                                                         
                                                            <b>Thanking You<br>
                                                                Warm Regards<br>
                                                                Abhay Nanda<br>
                                                                Business Head<br>
                                                                Mobile: 9888247515<br>
                                                                Email: info@autosolutions.in</b>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <table width="100%" style="border-top:1px solid #ddd; margin-top:10px;border-radius:0 0 4px 4px;">
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <p style="line-height:20px;">Copyright &copy; 2018 Auto Solutions.<br> All rights reserved. </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
    </style>

