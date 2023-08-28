<?php

namespace App\Http\Controllers;

use App\Product;
use App\Unit;
use App\Utils\Util;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
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
        if (!auth()->user()->can('unit.view') && !auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $unit = Unit::where('business_id', $business_id)
                ->with(['base_unit'])
                ->select([
                    'actual_name', 'short_name', 'allow_decimal', 'id',
                    'base_unit_id', 'base_unit_multiplier',
                ]);

            return Datatables::of($unit)
                ->addColumn(
                    'action',
                    '@can("unit.update")
                    <button data-href="{{action(\'App\Http\Controllers\UnitController@edit\', [$id])}}" class="btn btn-xs f_edit_unit_button  edit_unit_button">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_248_3806)">
                    <path d="M10.4731 1.875L9.22312 3.125H3.125V11.875H11.875V5.77688L13.125 4.52688V12.5C13.125 12.6658 13.0592 12.8247 12.9419 12.9419C12.8247 13.0592 12.6658 13.125 12.5 13.125H2.5C2.33424 13.125 2.17527 13.0592 2.05806 12.9419C1.94085 12.8247 1.875 12.6658 1.875 12.5V2.5C1.875 2.33424 1.94085 2.17527 2.05806 2.05806C2.17527 1.94085 2.33424 1.875 2.5 1.875H10.4731ZM12.8031 1.3125L13.6875 2.1975L7.9425 7.9425L7.06 7.94438L7.05875 7.05875L12.8031 1.3125Z" fill="#0038FF"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_248_3806">
                    <rect width="15" height="15" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("unit.delete")
                        <button data-href="{{action(\'App\Http\Controllers\UnitController@destroy\', [$id])}}" class="btn btn-xs f_delete_unit_button delete_unit_button">
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
                ->editColumn('allow_decimal', function ($row) {
                    if ($row->allow_decimal) {
                        return __('messages.yes');
                    } else {
                        return __('messages.no');
                    }
                })
                ->editColumn('actual_name', function ($row) {
                    if (!empty($row->base_unit_id)) {
                        return  $row->actual_name . ' (' . (float) $row->base_unit_multiplier . $row->base_unit->short_name . ')';
                    }

                    return  $row->actual_name;
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('unit.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }

        $units = Unit::forDropdown($business_id);

        return view('unit.create')
            ->with(compact('quick_add', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['actual_name', 'short_name', 'allow_decimal']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');

            if ($request->has('define_base_unit')) {
                if (!empty($request->input('base_unit_id')) && !empty($request->input('base_unit_multiplier'))) {
                    $base_unit_multiplier = $this->commonUtil->num_uf($request->input('base_unit_multiplier'));
                    if ($base_unit_multiplier != 0) {
                        $input['base_unit_id'] = $request->input('base_unit_id');
                        $input['base_unit_multiplier'] = $base_unit_multiplier;
                    }
                }
            }

            $unit = Unit::create($input);
            $output = [
                'success' => true,
                'data' => $unit,
                'msg' => __('unit.added_success'),
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
        if (!auth()->user()->can('unit.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $unit = Unit::where('business_id', $business_id)->find($id);

            $units = Unit::forDropdown($business_id);

            return view('unit.edit')
                ->with(compact('unit', 'units'));
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
        if (!auth()->user()->can('unit.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['actual_name', 'short_name', 'allow_decimal']);
                $business_id = $request->session()->get('user.business_id');

                $unit = Unit::where('business_id', $business_id)->findOrFail($id);
                $unit->actual_name = $input['actual_name'];
                $unit->short_name = $input['short_name'];
                $unit->allow_decimal = $input['allow_decimal'];
                if ($request->has('define_base_unit')) {
                    if (!empty($request->input('base_unit_id')) && !empty($request->input('base_unit_multiplier'))) {
                        $base_unit_multiplier = $this->commonUtil->num_uf($request->input('base_unit_multiplier'));
                        if ($base_unit_multiplier != 0) {
                            $unit->base_unit_id = $request->input('base_unit_id');
                            $unit->base_unit_multiplier = $base_unit_multiplier;
                        }
                    }
                } else {
                    $unit->base_unit_id = null;
                    $unit->base_unit_multiplier = null;
                }

                $unit->save();

                $output = [
                    'success' => true,
                    'msg' => __('unit.updated_success'),
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
        if (!auth()->user()->can('unit.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $unit = Unit::where('business_id', $business_id)->findOrFail($id);

                //check if any product associated with the unit
                $exists = Product::where('unit_id', $unit->id)
                    ->exists();
                if (!$exists) {
                    $unit->delete();
                    $output = [
                        'success' => true,
                        'msg' => __('unit.deleted_success'),
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.unit_cannot_be_deleted'),
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => '__("messages.something_went_wrong")',
                ];
            }

            return $output;
        }
    }
}
