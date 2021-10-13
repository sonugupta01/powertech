<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use DB;
class AuthenticateResource
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
        if ($request->route('id')) {
            $user = DB::table('emp_hierarchy')->where('authority_id',Auth::id())->where('user_id',$request->route('id'))->first();
            if (empty($user)) {
                return redirect()->back()->with('error','Invalid activity');
            }
        }
        return $next($request);
    }
}
