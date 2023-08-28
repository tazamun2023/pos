<?php

namespace App\Http\Controllers;

use App\Warranty;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarrantyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $warranties = Warranty::where('business_id', $business_id)
                         ->select(['id', 'name', 'description', 'duration', 'duration_type']);

            return Datatables::of($warranties)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\WarrantyController@edit\', [$id])}}" class="btn btn-xs f_edit_unit_button btn-modal" data-container=".view_modal">
                     <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_248_3806)">
                    <path d="M10.4731 1.875L9.22312 3.125H3.125V11.875H11.875V5.77688L13.125 4.52688V12.5C13.125 12.6658 13.0592 12.8247 12.9419 12.9419C12.8247 13.0592 12.6658 13.125 12.5 13.125H2.5C2.33424 13.125 2.17527 13.0592 2.05806 12.9419C1.94085 12.8247 1.875 12.6658 1.875 12.5V2.5C1.875 2.33424 1.94085 2.17527 2.05806 2.05806C2.17527 1.94085 2.33424 1.875 2.5 1.875H10.4731ZM12.8031 1.3125L13.6875 2.1975L7.9425 7.9425L7.06 7.94438L7.05875 7.05875L12.8031 1.3125Z" fill="#0038FF"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_248_3806">
                    <rect width="15" height="15" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>@lang("messages.edit")</button>'
                 )
                 ->removeColumn('id')
                 ->editColumn('duration', function ($row) {
                     return $row->duration.' '.__('lang_v1.'.$row->duration_type);
                 })
                 ->rawColumns(['action'])
                 ->make(true);
        }

        return view('warranties.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('warranties.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $input = $request->only(['name', 'description', 'duration', 'duration_type']);
            $input['business_id'] = $business_id;

            $status = Warranty::create($input);

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function show(Warranty $warranty)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $warranty = Warranty::where('business_id', $business_id)->find($id);

            return view('warranties.edit')
                ->with(compact('warranty'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description', 'duration', 'duration_type']);

                $warranty = Warranty::where('business_id', $business_id)->findOrFail($id);

                $warranty->update($input);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Warranty  $warranty
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warranty $warranty)
    {
        //
    }
}
