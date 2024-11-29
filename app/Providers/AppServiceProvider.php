<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Schema::defaultStringLength(191);
        // dd($_SERVER['HTTP_HOST']);

        try {
            view()->composer('*', function ($view) {
                $mydata['sessionOut'] = \App\Model\PortalSetting::where('code', 'sessionout')->first()->value;
                $mydata['complaintsubject'] = \App\Model\Complaintsubject::get();
                $mydata['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
                $mydata['topheadcolor'] = \App\Model\PortalSetting::where('code', "topheadcolor")->first();
                $mydata['sidebarlightcolor'] = \App\Model\PortalSetting::where('code', "sidebarlightcolor")->first();
                $mydata['sidebardarkcolor'] = \App\Model\PortalSetting::where('code', "sidebardarkcolor")->first();
                $mydata['sidebariconcolor'] = \App\Model\PortalSetting::where('code', "sidebariconcolor")->first();
                $mydata['sidebarchildhrefcolor'] = \App\Model\PortalSetting::where('code', "sidebarchildhrefcolor")->first();
                $mydata['settlementcharge'] = \App\Model\PortalSetting::where('code', "settlementcharge")->first();

                if ($mydata['company']) {
                    $news = \App\Model\Companydata::where('company_id', $mydata['company']->id)->first();
                    $mydata['supportnumber'] = isset($news->number) ? $news->number : "";
                    $mydata['supportemail']  = isset($news->email) ? $news->email : "";
                } else {
                    $mydata['supportnumber'] = "";
                    $mydata['supportemail']  = "";
                }
                if (\Auth::check()) {
                    if ($mydata['company'] && $news) {
                        if (\Auth::user()->role->slug == 'whitelable' && $news->wnews != NULL) {
                            $mydata['news'] = $news->wnews;
                        } else if (\Auth::user()->role->slug == 'md' && $news->mdnews != NULL) {
                            $mydata['news'] = $news->mdnews;
                        } else if (\Auth::user()->role->slug == 'distributor' && $news->dnews != NULL) {
                            $mydata['news'] = $news->dnews;
                        } else if (\Auth::user()->role->slug == 'retailer' && $news->rnews != NULL) {
                            $mydata['news'] = $news->rnews;
                        } else {
                            $mydata['news'] = $news->news;
                        }

                        $mydata['notice'] = $news->notice;
                        $mydata['billnotice'] = $news->billnotice;
                    } else {
                        $mydata['news'] = "";
                        $mydata['notice'] = "";
                        $mydata['billnotice'] = "";
                    }
                    $mydata['downlinebalance'] = \App\User::whereIn('id', array_diff(session('parentData'), array(\Auth::id())))->sum('mainwallet');
                }
                $view->with('mydata', $mydata);
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
