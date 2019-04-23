<?php

namespace App\Http\Controllers;

use App\CashForwarded;
use App\CashRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Utils\CashRegisterUtil;
use Illuminate\Support\Facades\DB;
use App\AccountTransaction;

class CashRegisterController extends Controller
{

    protected $cashRegisterUtil;

    /**
     * Constructor
     *
     * @param CashRegisterUtil $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->payment_types = ['cash' => 'Cash', 'card' => 'Card', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0) {
            return redirect()->action('SellPosController@create');
        }

        return view('cash_register.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    //create cash forwarded

    protected function cashForwarded(){

        $business_id = session()->get('user.business_id');


        $accounts=DB::table('accounts')
            ->where('business_id',session()->get('user.business_id'))
            ->orderBy('created_at', 'asc')->first();

        $accountTransection=AccountTransaction::where('account_id',$accounts->id)
            ->sum('amount');

        CashForwarded::create([

            'account_id'=>$accounts->id ,
            'business_id'=>$business_id,
            'cash_forwarded'=>$accountTransection
        ]);

        return $accounts;
    }

    //end cash forwarded

    public function store(Request $request)
    {
        try {
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');
            //create cash forwarded
            $this->cashForwarded();
            //end cash forwarded
            $register = CashRegister::create([
                        'business_id' => $business_id,
                        'user_id' => $user_id,
                        'status' => 'open'
                    ]);

            $register->cash_register_transactions()->create([
                            'amount' => $initial_amount,
//                            'amount' => $initial_amount,
                            'pay_method' => 'cash',
                            'type' => 'credit',
                            'transaction_type' => 'initial'
                        ]);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }

        return redirect()->action('SellPosController@create');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */

    protected function closingAmount(){

        $business_id = session()->get('user.business_id');
        $closing_amount=CashRegister::select('closing_amount')->where('business_id',$business_id)
            ->where('closing_amount', '>' ,0)
            ->latest()->first();

//        return $closing_amount;

        return empty($closing_amount->closing_amount)?'0':$closing_amount->closing_amount;

    }


    public function show($id)
    {


        $register_details =  $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        return view('cash_register.register_details')
                    ->with(compact('register_details', 'details'));
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */

    protected function expense(){

        $business_id = session()->get('user.business_id');
        $cashRegister=CashRegister::select('created_at')
            ->where('business_id',$business_id)
            ->latest()
            ->first();
        $expenses = DB::table('transactions')
            ->where('business_id',$business_id)
            ->where('type','expense')
            ->whereBetween('created_at', [$cashRegister->created_at, Carbon::now()->toDateTimeString()])
            ->sum('final_total');
        return $expenses;
    }

    protected function totalCredit(){

        $business_id = session()->get('user.business_id');
        $cashRegister=CashRegister::select('created_at')
            ->where('business_id',$business_id)
            ->latest()
            ->first();
        $credit = DB::table('transactions')
            ->where('business_id',$business_id)
            ->where('payment_status','due')
            ->whereBetween('created_at', [$cashRegister->created_at, Carbon::now()->toDateTimeString()])
            ->sum('final_total');
        return $credit;
    }


    public function getRegisterDetails()
    {

        $register_details =  $this->cashRegisterUtil->getRegisterDetails();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);

        //create closing amount
        $closing_amount=$this->closingAmount();
        //end closing amount

        // get cashForwarded
        $business_id = session()->get('user.business_id');
        $cashForward=CashForwarded::select('cash_forwarded')->where('business_id',$business_id)->latest()->first();
        // end cashForwarded
        //create expenses
        $expenses=$this->expense();
        // end expenses

        //create credit
        $credit=$this->totalCredit();
        // end credit

        return view('cash_register.register_details')
                ->with(compact('register_details', 'details','closing_amount','cashForward','expenses','credit'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister()
    {
        $register_details =  $this->cashRegisterUtil->getRegisterDetails();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);

//        $business_id = session()->get('user.business_id');
//        $closing_amount=CashRegister::select('closing_amount')->where('business_id',$business_id)
//            ->where('closing_amount', '>' ,0)
//            ->latest()->first();

        $closing_amount=$this->closingAmount();

        $business_id = session()->get('user.business_id');

        $cashForward=CashForwarded::select('cash_forwarded')->where('business_id',$business_id)->latest()->first();


        $expenses=$this->expense();

        //create credit
        $credit=$this->totalCredit();
        // end credit

        return view('cash_register.close_register_modal')
                    ->with(compact('register_details', 'details','closing_amount','cashForward','expenses','credit'));
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = ['success' => 0,
                                'msg' => 'Feature disabled in demo!!'
                            ];
                return redirect()->action('HomeController@index')->with('status', $output);
            }

            $input = $request->only(['closing_amount', 'total_card_slips', 'total_cheques',
                                    'closing_note']);

            //            $register_details =  $this->cashRegisterUtil->getRegisterDetails();

            $closing_amount=$this->closingAmount();
            $expenses=$this->expense();

            $input['closing_amount'] = ($this->cashRegisterUtil->num_uf($input['closing_amount'])+$closing_amount)-$expenses;
            $user_id = $request->session()->get('user.id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';

            CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->update($input);
            $output = ['success' => 1,
                            'msg' => __('cash_register.close_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->action('HomeController@index')->with('status', $output);


//        return response()->json($request->all());
    }
}
