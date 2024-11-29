<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Circle;
use App\User;
use App\Model\Report;
use App\Model\Aepsreport;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Aepsfundrequest;
use App\Model\Fingagent;
use Illuminate\Support\Facades\Validator; // Add this line
use DB;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        $this->middleware('auth');
    }
    
     
    public function statsByDate(Request $request)
{
    $data = [];
    if(!\Myhelper::hasRole('admin')) {
        return redirect()->back()->with('error', 'Unauthorised access');
    }
    
    if ($request->has('from_date')) {
        
        switch($request->service){
            case 'matm':
                $data['data'] = DB::table('aepsreports')
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('product', $request->service)
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
                
            case 'cw':
                $data['data'] = DB::table('aepsreports')
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('aepstype', 'CW')
                ->where('product', 'aeps')
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
                
            case 'm':
                $data['data'] = DB::table('aepsreports')
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('aepstype', 'M')
                ->where('product', 'aeps')
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
                
            case 'cd':
                $data['data'] = DB::table('aepsreports')
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('aepstype', 'CD')
                ->where('product', 'cashdeposit')
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
                
            case 'mfund':
                $data['data'] = DB::table('aepsfundrequests')
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('type', 'bank')
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
                
            case 'wtb':
                $data['data'] = DB::table('reports')
                ->where('rtype', 'main')->where('product', "wt")
                ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->whereBetween('created_at', [$request->from_date, $request->to_date])
                ->groupBy('status')
                ->get();
                break;
        }
        
        
        $data['datashow'] = 'view';
        $data['from_date'] = $request->from_date;
        $data['to_date'] = $request->to_date;
        $data['service'] = $request->service;
    } else {
        $data['datashow'] = 'no';
    }
    
    return view('statement.statsByDate')->with(['data' => $data]);
}


    public function deletedataview()
    {
        return view('statement.delete_data');
    }



    public function deleteData(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $tableName = $request->input('table_name');

        // Validate the inputs to ensure they are in the correct format
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'table_name' => 'required|in:apilogs,microlog,aepsreports' // Replace 'table1', 'table2', 'table3' with the actual table names
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Call the deleteDataFromTable function passing the table name and date range
        try {
            $result = $this->deleteDataFromTable($tableName, $dateFrom, $dateTo);
            return response()->json(['message' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete data'], 500);
        }
    }

    private function deleteDataFromTable($tableName, $dateFrom, $dateTo)
    {
        // Use the DB facade to run the delete query directly
        $deletedRows = DB::table($tableName)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->delete();

        return "Deleted $deletedRows rows from $tableName table.";
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function getbalance()
    {
        return true;
    }

    public function statistics()
    {
        try {
            $parentData = session('parentData', \Myhelper::getParents(\Auth::id()));
    
            $data['state'] = DB::table('circles')->get();
            
            $roles = ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'other'];
            
            foreach ($roles as $role) {
                $query = DB::table('users')
                    ->whereIn('id', $parentData)
                    ->where('kyc', 'verified');
                
                if ($role !== 'other') {
                    $query->whereIn('role_id', function ($q) use ($role) {
                        $q->select('id')
                            ->from('roles')
                            ->where('slug', $role);
                    });
                } else {
                    $query->whereNotIn('role_id', function ($q) {
                        $q->select('id')
                            ->from('roles')
                            ->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'admin']);
                    });
                }
    
                $data[$role] = $query->count();
            }
    
            $product = [
                'recharge',
                'billpayment',
                'cashdeposit',
                'insurance',
                'utipancard',
                'money',
                'aeps'
            ];
    
            $slot = ['today', 'month', 'lastmonth'];
    
            $statuscount = [
                'success' => ['success'],
                'pending' => ['pending'],
                'failed' => ['failed', 'reversed']
            ];
    
            foreach ($product as $value) {
                foreach ($slot as $slots) {
                    $query = ($value === 'aeps' || $value === 'cashdeposit') ? DB::table('aepsreports') : DB::table('reports');
                    $query = $query->whereIn('user_id', $parentData);
    
                    switch ($value) {
                        case 'recharge':
                            $query->where('product', 'recharge');
                            break;
                        case 'billpayment':
                            $query->where('product', 'billpay');
                            break;
                        case 'utipancard':
                            $query->where('product', 'utipancard');
                            break;
                        case 'money':
                            $query->where('product', 'dmt');
                            break;
                        case 'cashdeposit':
                            $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', 'CD');
                            break;
                        case 'aeps':
                            $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', '!=', 'CD');
                            break;
                    }
    
                    if ($slots === 'today') {
                        $query->whereDate('created_at', now()->format('Y-m-d'));
                    } elseif ($slots === 'month') {
                        $query->whereYear('created_at', now()->format('Y'))->whereMonth('created_at', now()->format('m'));
                    } elseif ($slots === 'lastmonth') {
                        $lastMonth = now()->subMonth();
                        $query->whereYear('created_at', $lastMonth->format('Y'))->whereMonth('created_at', $lastMonth->format('m'));
                    }
    
                    $data[$value][$slots] = $query->where('status', 'success')->sum('amount');
                }
    
                foreach ($statuscount as $keys => $values) {
                    $query = ($value === 'aeps' || $value === 'cashdeposit') ? DB::table('aepsreports') : DB::table('reports');
                    $query = $query->whereIn('user_id', $parentData);
    
                    switch ($value) {
                        case 'recharge':
                            $query->where('product', 'recharge');
                            break;
                        case 'billpayment':
                            $query->where('product', 'billpay');
                            break;
                        case 'utipancard':
                            $query->where('product', 'utipancard');
                            break;
                        case 'money':
                            $query->where('product', 'dmt');
                            break;
                        case 'cashdeposit':
                            $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', 'CD');
                            break;
                        case 'aeps':
                            $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', '!=', 'CD');
                            break;
                    }
    
                    $data[$value][$keys] = $query->whereIn('status', $values)->whereDate('created_at', now()->format('Y-m-d'))->count();
                }
            }
    
            return view('home')->with($data);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            print_r($e->getMessage());
        }
    }
    


    public function statistics_old()
{
    try {
        $parentData = session('parentData') ?? \Myhelper::getParents(\Auth::id());
        if (!$parentData) {
            session(['parentData' => $parentData]);
        }

        $data['state'] = Circle::all();
        $roles = ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'other'];

        foreach ($roles as $role) {
            $query = User::whereIn('id', $parentData)->whereIn('kyc', ['verified']);
            if ($role !== 'other') {
                $query->whereHas('role', function ($q) use ($role) {
                    $q->where('slug', $role);
                });
            } else {
                $query->whereDoesntHave('role', function ($q) use ($roles) {
                    $q->whereIn('slug', $roles);
                });
            }
            $data[$role] = $query->count();
        }

        $product = ['recharge', 'billpayment', 'cashdeposit', 'insurance', 'utipancard', 'money', 'aeps'];
        $slot = ['today', 'month', 'lastmonth'];
        $statuscount = ['success' => ['success'], 'pending' => ['pending'], 'failed' => ['failed', 'reversed']];

        foreach ($product as $value) {
            foreach ($slot as $slots) {
                $query = ($value == 'aeps' || $value == 'cashdeposit') ? Aepsreport::whereIn('user_id', $parentData) : Report::whereIn('user_id', $parentData);

                switch ($value) {
                    case 'cashdeposit':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', 'CD');
                        break;
                    case 'aeps':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', '!=', 'CD');
                        break;
                    default:
                        $query->where('product', $value == 'money' ? 'dmt' : $value);
                        break;
                }

                if ($slots == "today") {
                    $query->whereDate('created_at', date('Y-m-d'));
                } elseif ($slots == "month") {
                    $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
                } elseif ($slots == "lastmonth") {
                    $query->whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'));
                }

                foreach ($statuscount as $keys => $values) {
                    $data[$value][$slots][$keys] = $query->whereIn('status', $values)->sum('amount');
                }
            }
        }

        return view('home')->with($data);
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return view('error_page');
    }
}


    public function fetchUpdatedData()
    {
        $currentDate = now()->format('Y-m-d');
        $parentData = session('parentData', \Myhelper::getParents(\Auth::id()));

        // Fetch updated data based on your logic
        $aepstypeData = DB::table('aepsreports')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%H:00') as time"),
                'product',
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereIn('user_id', $parentData)
            ->where('status', 'success')
            ->whereDate('created_at', $currentDate)
            ->groupBy('time', 'product')
            ->orderBy('time', 'asc')
            ->get();
            

        // Process the data to match the expected format for the chart
        $labels = [];
        $datasets = [];
        $aepstypes = [];

          // Process the data similar to how you did in the JavaScript section
          $aepstypeData->each(function ($item) use (&$labels, &$aepstypes, &$datasets) {
            if (!in_array($item->aepstype, $aepstypes)) {
                $aepstypes[] = $item->aepstype;
            }

            if (!in_array($item->time, $labels)) {
                $labels[] = $item->time;
            }
        });

        // Define an array of colors for datasets (you can add more colors)
        $datasetColors = [
            'rgba(255, 99, 132, 0.6)',  // Red
            'rgba(54, 162, 235, 0.6)', // Blue
            'rgba(255, 206, 86, 0.6)', // Yellow
            'rgba(75, 192, 192, 0.6)', // Teal
            'rgba(153, 102, 255, 0.6)' // Purple
        ];

        foreach ($aepstypes as $index => $aepstype) {
            $data = [];

            foreach ($labels as $time) {
                $foundItem = $aepstypeData->first(function ($item) use ($aepstype, $time) {
                    return $item->aepstype === $aepstype && $item->time === $time;
                });

                $data[] = $foundItem ? $foundItem->total_amount : 0;
            }

            $datasets[] = [
                'label' => $aepstype,
                'data' => $data,
                'borderColor' => $datasetColors[$index % count($datasetColors)], // Assign colors based on order
                'fill' => false,
            ];
        }

        $response = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];

        return response()->json($response);
    
    }

    public function fetchTotalAmount()
    {
        $currentDate = now()->format('Y-m-d');
        $parentData = session('parentData', \Myhelper::getParents(\Auth::id()));
    
        $totalAmountsData = DB::table('aepsreports')
    ->whereIn('user_id', $parentData)
    ->where('status', 'success')
    ->where('transtype', 'transaction')
    ->whereDate('created_at', $currentDate)
    ->groupBy('product', 'aepstype') // Group by both 'product' and 'aepstype'
    ->select(
        'product', 
        'aepstype',
        DB::raw('SUM(amount) as total_amount'), 
        DB::raw('COUNT(*) as count')
    )
    ->get();
            
        
    
        $aepstypes = [];
        $totalAmounts = [];
        $totalCounts = [];
        $colors = ['rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)', 'rgba(75, 192, 192, 0.6)', 'rgba(153, 102, 255, 0.6)'];
    
        foreach ($totalAmountsData as $key => $data) {
            if($data->product == 'aeps'){
                $aepstypes[] = $data->aepstype;
            }else{
                $aepstypes[] = $data->product;
            }
            
            $totalAmounts[] = $data->total_amount;
            $totalCounts[] = $data->count;
            $colors[] = $colors[$key % count($colors)]; // Reuse colors for more segments
        }
    
        $response = [
            'aepstypes' => $aepstypes,
            'totalAmounts' => $totalAmounts,
            'totalCounts' => $totalCounts,
            'colors' => $colors,
        ];
    
        return response()->json($response);
    }
    
    



    public function index()
    {
        if (!session('parentData')) {
            session(['parentData' => \Myhelper::getParents(\Auth::id())]);
        }
        if (!\Myhelper::handleFingAeps()) {
            $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();

            $data['agent'] = Fingagent::where('user_id', \Auth::id())->first();
            $data['aepsbanks'] = \DB::table('fingaepsbanks')->orderBy('bankName', 'ASC')->get();

            $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
            $data['state'] = \DB::table('fingstate')->get();
            $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
            //  dd($data); exit;
            return view('service.fingaeps')->with($data);
        }

        


        return view('dash');
    }

    public function isactive()
    {

        $isactive = date('Y-m-d H:i:s');
        $userid = \Auth::id();

        $affected = DB::table('users')
            ->where('id', $userid)
            ->update(['isactive' => $isactive]);
        $data['status'] = true;
        return response()->json($data);
    }

    public function currentlocation(request $post)
    {
        $lat = $post->x;
        $long = $post->y;
        $userid = \Auth::id();

        $affected = DB::table('users')
            ->where('id', $userid)
            ->update(['lat' => $lat, 'long' => $long]);
        $data['status'] = true;
        return response()->json($data);
    }

    public function getmysendip()
    {
        $url = "http://securepayments.net.in/api/getip";
        $result = \Myhelper::curl($url, "GET", "", [], "no");
        dd($result);
    }

    public function setpermissions()
    {
        $users = User::whereHas('role', function ($q) {
            $q->where('slug', '!=', 'admin');
        })->get();

        foreach ($users as $user) {
            $inserts = [];
            $insert = [];
            $permissions = \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $user->role_id)->get();

            if (sizeof($permissions) > 0) {
                \DB::table('user_permissions')->where('user_id', $user->id)->delete();
                foreach ($permissions as $permission) {
                    $insert = array('user_id' => $user->id, 'permission_id' => $permission->permission_id);
                    $inserts[] = $insert;
                }
                \DB::table('user_permissions')->insert($inserts);
            }
        }
    }

    public function setscheme()
    {
        $users = User::whereHas('role', function ($q) {
            $q->where('slug', '!=', 'admin');
        })->get();

        foreach ($users as $user) {
            $inserts = [];
            $insert = [];
            $scheme = \DB::table('default_permissions')->where('type', 'scheme')->where('role_id', $user->role_id)->first();
            if ($scheme) {
                User::where('id', $user->id)->update(['scheme_id' => $scheme->permission_id]);
            }
        }
    }

    public function mydata()
    {
        //return true;
        $api = Api::where('code', 'recharge1')->first();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        do {
            $apibalance = 0;
            /*$url = "http://securepayments.net.in/api/getbal/".$api->username;
            $result = \Myhelper::curl($url, "GET", "", [], "no");
            if(!$result['error'] && $result['response'] != ''){
                $response = json_decode($result['response']);
                if(isset($response->balance)){
                    $apibalance = round($response->balance, 2);
                }
            }*/
            $fundrequest = \App\Model\Fundreport::where('credited_by', \Auth::id())->where('status', 'pending')->count();
            $aepsfundrequest = \App\Model\Aepsfundrequest::where('status', 'pending')->where('pay_type', 'manual')->count();
            $aepspayoutrequest = \App\Model\Aepsfundrequest::where('status', 'pending')->where('pay_type', 'payout')->count();
            $downlinebalance = \App\User::whereIn('id', array_diff(session('parentData'), array(\Auth::id())))->sum('mainwallet');
            echo "data: {\"apibalance\" : {$apibalance}, \"downlinebalance\" : {$downlinebalance},\"fundrequest\" : {$fundrequest},\"aepsfundrequest\" : {$aepsfundrequest},\"aepspayoutrequest\" : {$aepspayoutrequest}}\n\n";
            sleep(10);
            @ob_flush();
            flush();
        } while (true);
    }
}
