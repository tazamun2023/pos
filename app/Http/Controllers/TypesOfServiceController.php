<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\SellingPriceGroup;
use App\TypesOfService;
use App\Utils\Util;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TypesOfServiceController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  TaxUtil  $taxUtil
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $tax_rates = TypesOfService::where('business_id', $business_id)
                ->select('*');

            return Datatables::of($tax_rates)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\TypesOfServiceController@edit\', [$id])}}" class="btn btn-xs f_edit_unit_button btn-modal" data-container=".type_of_service_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    <button data-href="{{action(\'App\Http\Controllers\TypesOfServiceController@destroy\', [$id])}}" class="btn btn-xs f_delete_unit_button delete_type_of_service">
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
                    @lang("messages.delete")</button>'
                )
                ->editColumn('packing_charge', function ($row) {
                    $html = '<span class="display_currency" data-currency_symbol="false">' . $row->packing_charge . '</span>';

                    if ($row->packing_charge_type == 'percent') {
                        $html .= '%';
                    }

                    return $html;
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'packing_charge'])
                ->make(true);
        }

        return view('types_of_service.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        return view('types_of_service.create')
            ->with(compact('locations', 'price_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'name', 'description',
                'location_price_group', 'packing_charge_type',
                'packing_charge',
            ]);

            $input['business_id'] = $request->session()->get('user.business_id');
            $input['packing_charge'] = !empty($input['packing_charge']) ? $this->commonUtil->num_uf($input['packing_charge']) : 0;
            $input['enable_custom_fields'] = !empty($request->input('enable_custom_fields')) ? 1 : 0;

            TypesOfService::create($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
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
     * @param  \App\TypesOfService  $typesOfService
     * @return \Illuminate\Http\Response
     */
    public function show(TypesOfService $typesOfService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TypesOfService  $typesOfService
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $type_of_service = TypesOfService::where('business_id', $business_id)
            ->findOrFail($id);

        return view('types_of_service.edit')
            ->with(compact('locations', 'price_groups', 'type_of_service'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TypesOfService  $typesOfService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'name', 'description',
                'location_price_group', 'packing_charge_type',
                'packing_charge',
            ]);

            $business_id = $request->session()->get('user.business_id');
            $input['packing_charge'] = !empty($input['packing_charge']) ? $this->commonUtil->num_uf($input['packing_charge']) : 0;
            $input['enable_custom_fields'] = !empty($request->input('enable_custom_fields')) ? 1 : 0;
            $input['location_price_group'] = !empty($input['location_price_group']) ? json_encode($input['location_price_group']) : null;

            TypesOfService::where('business_id', $business_id)
                ->where('id', $id)
                ->update($input);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
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
     * Remove the specified resource from storage.
     *
     * @param  \App\TypesOfService  $typesOfService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('access_types_of_service')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                TypesOfService::where('business_id', $business_id)
                    ->where('id', $id)
                    ->delete();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
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
}
