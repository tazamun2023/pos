<?php

namespace App\Http\Controllers;

use App\GroupSubTax;
use App\TaxRate;
use App\Utils\TaxUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxRateController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $taxUtil;

    /**
     * Constructor
     *
     * @param  TaxUtil  $taxUtil
     * @return void
     */
    public function __construct(TaxUtil $taxUtil)
    {
        $this->taxUtil = $taxUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('tax_rate.view') && !auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $tax_rates = TaxRate::where('business_id', $business_id)
                ->where('is_tax_group', '0')
                ->select(['name', 'amount', 'id', 'for_tax_group']);

            return Datatables::of($tax_rates)
                ->addColumn(
                    'action',
                    '@can("tax_rate.update")
                    <button data-href="{{action(\'App\Http\Controllers\TaxRateController@edit\', [$id])}}" class="btn btn-xs f_edit_unit_button edit_tax_rate_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    
                    @endcan
                    @can("tax_rate.delete")
                        <button data-href="{{action(\'App\Http\Controllers\TaxRateController@destroy\', [$id])}}" class="btn btn-xs f_delete_unit_button delete_tax_rate_button">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_248_3811)">
                            <path d="M10.625 3.75H13.75V5H12.5V13.125C12.5 13.2908 12.4342 13.4497 12.3169 13.5669C12.1997 13.6842 12.0408 13.75 11.875 13.75H3.125C2.95924 13.75 2.80027 13.6842 2.68306 13.5669C2.56585 13.4497 2.5 13.2908 2.5 13.125V5H1.25V3.75H4.375V1.875C4.375 1.70924 4.44085 1.55027 4.55806 1.43306C4.67527 1.31585 4.83424 1.25 5 1.25H10C10.1658 1.25 10.3247 1.31585 10.4419 1.43306C10.5592 1.55027 10.625 1.70924 10.625 1.875V3.75ZM11.25 5H3.75V12.5H11.25V5ZM5.625 6.875H6.875V10.625H5.625V6.875ZM8.125 6.875H9.375V10.625H8.125V6.875ZM5.625 2.5V3.75H9.375V2.5H5.625Z" fill="#EF1463"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_248_3811">
                            <rect width="15" height="15" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>
                        @lang("messages.delete")</button>
                    @endcan'
                )
                ->editColumn('name', '@if($for_tax_group == 1) {{$name}} <small>(@lang("lang_v1.for_tax_group_only"))</small> @else {{$name}} @endif')
                ->editColumn('amount', '{{@num_format($amount)}}')
                ->removeColumn('for_tax_group')
                ->removeColumn('id')
                ->rawColumns([0, 2])
                ->make(false);
        }

        return view('tax_rate.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('tax_rate.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'amount']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            $input['amount'] = $this->taxUtil->num_uf($input['amount']);
            $input['for_tax_group'] = !empty($request->for_tax_group) ? 1 : 0;

            $tax_rate = TaxRate::create($input);
            $output = [
                'success' => true,
                'data' => $tax_rate,
                'msg' => __('tax_rate.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('tax_rate.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $tax_rate = TaxRate::where('business_id', $business_id)->find($id);

            return view('tax_rate.edit')
                ->with(compact('tax_rate'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('tax_rate.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'amount']);
                $business_id = $request->session()->get('user.business_id');

                $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                $tax_rate->name = $input['name'];
                $tax_rate->amount = $this->taxUtil->num_uf($input['amount']);
                $tax_rate->for_tax_group = !empty($request->for_tax_group) ? 1 : 0;
                $tax_rate->save();

                //update group tax amount
                $group_taxes = GroupSubTax::where('tax_id', $id)
                    ->get();

                foreach ($group_taxes as $group_tax) {
                    $this->taxUtil->updateGroupTaxAmount($group_tax->group_tax_id);
                }

                $output = [
                    'success' => true,
                    'msg' => __('tax_rate.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('tax_rate.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                //update group tax amount
                $group_taxes = GroupSubTax::where('tax_id', $id)
                    ->get();
                if ($group_taxes->isEmpty()) {
                    $business_id = request()->user()->business_id;

                    $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                    $tax_rate->delete();

                    $output = [
                        'success' => true,
                        'msg' => __('tax_rate.deleted_success'),
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __('tax_rate.can_not_be_deleted'),
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
}
