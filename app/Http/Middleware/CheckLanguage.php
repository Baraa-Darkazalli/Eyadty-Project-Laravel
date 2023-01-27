<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CheckLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user->current_lang == 'ar') {
            app()->setLocale('ar');
        } else {
            app()->setLocale('en');
        }

        // //default langauge (Eglish)
        // app()->setLocale('en');

        // //arabic langauge
        // if(isset($request->lang)&& $request->lang=='ar')
        //     app()->setLocale('ar');
        // return $next($request);
        // if (session()->has('locale')) {
        //     app()->setLocale(session()->get('locale'));
        // }
        return $next($request);
    }
}
