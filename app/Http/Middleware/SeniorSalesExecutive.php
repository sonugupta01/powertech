<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use DB;
class SeniorSalesExecutive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $designation = DB::table('staff_detail')->where('user_id',Auth::id())->first();
        if (Auth::check() && $designation->designation_id == '23') {
            return $next($request)->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        }else{
            return redirect('admin/login');   
        }
    }
}
