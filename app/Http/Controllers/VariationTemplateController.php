<?php

namespace App\Http\Controllers;

use App\ProductVariation;
use App\Variation;
use App\VariationTemplate;
use App\VariationValueTemplate;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VariationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $variations = VariationTemplate::where('business_id', $business_id)
                ->with(['values'])
                ->select('id', 'name', DB::raw('(SELECT COUNT(id) FROM product_variations WHERE product_variations.variation_template_id=variation_templates.id) as total_pv'));

            return Datatables::of($variations)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\VariationTemplateController@edit\', [$id])}}" class="btn btn-xs f_edit_unit_button edit_variation_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        @if(empty($total_pv))
                        <button data-href="{{action(\'App\Http\Controllers\VariationTemplateController@destroy\', [$id])}}" class="btn btn-xs f_delete_unit_button delete_customer_group_button">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_248_3811)">
                            <path d="M10.625 3.75H13.75V5H12.5V13.125C12.5 13.2908 12.4342 13.4497 12.3169 13.5669C12.1997 13.6842 12.0408 13.75 11.875 13.75H3.125C2.95924 13.75 2.80027 13.6842 2.68306 13.5669C2.56585 13.4497 2.5 13.2908 2.5 13.125V5H1.25V3.75H4.375V1.875C4.375 1.70924 4.44085 1.55027 4.55806 1.43306C4.67527 1.31585 4.83424 1.25 5 1.25H10C10.1658 1.25 10.3247 1.31585 10.4419 1.43306C10.5592 1.55027 10.625 1.70924 10.625 1.875V3.75ZM11.25 5H3.75V12.5H11.25V5ZM5.625 6.875H6.875V10.625H5.625V6.875ZM8.125 6.875H9.375V10.625H8.125V6.875ZM5.625 2.5V3.75H9.375V2.5H5.625Z" fill="#EF1463"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_248_3811">
                            <rect width="15" height="15" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>  @lang("messages.delete")</button>
                        @endif'
                )
                ->editColumn('values', function ($data) {
                    $values_arr = [];
                    foreach ($data->values as $attr) {
                        $values_arr[] = $attr->name;
                    }

                    return implode(', ', $values_arr);
                })
                ->removeColumn('id')
                ->removeColumn('total_pv')
                ->rawColumns([2])
                ->make(false);
        }

        return view('variation.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('variation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->only(['name']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $variation = VariationTemplate::create($input);

            //craete variation values
            if (!empty($request->input('variation_values'))) {
                $values = $request->input('variation_values');
                $data = [];
                foreach ($values as $value) {
                    if (!empty($value)) {
                        $data[] = ['name' => $value];
                    }
                }
                $variation->values()->createMany($data);
            }

            $output = [
                'success' => true,
                'data' => $variation,
                'msg' => 'Variation added succesfully',
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'Something went wrong, please try again',
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\VariationTemplate  $variationTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(VariationTemplate $variationTemplate)
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
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $variation = VariationTemplate::where('business_id', $business_id)
                ->with(['values'])->find($id);

            return view('variation.edit')
                ->with(compact('variation'));
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
        if (request()->ajax()) {
            try {
                $input = $request->only(['name']);
                $business_id = $request->session()->get('user.business_id');

                $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);

                if ($variation->name != $input['name']) {
                    $variation->name = $input['name'];
                    $variation->save();

                    ProductVariation::where('variation_template_id', $variation->id)
                        ->update(['name' => $variation->name]);
                }

                //update variation
                $data = [];
                if (!empty($request->input('edit_variation_values'))) {
                    $values = $request->input('edit_variation_values');
                    foreach ($values as $key => $value) {
                        if (!empty($value)) {
                            $variation_val = VariationValueTemplate::find($key);

                            if ($variation_val->name != $value) {
                                $variation_val->name = $value;
                                $data[] = $variation_val;
                                Variation::where('variation_value_id', $key)
                                    ->update(['name' => $value]);
                            }
                        }
                    }
                    $variation->values()->saveMany($data);
                }
                if (!empty($request->input('variation_values'))) {
                    $values = $request->input('variation_values');
                    foreach ($values as $value) {
                        if (!empty($value)) {
                            $data[] = new VariationValueTemplate(['name' => $value]);
                        }
                    }
                }
                $variation->values()->saveMany($data);

                $output = [
                    'success' => true,
                    'msg' => 'Variation updated succesfully',
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => 'Something went wrong, please try again',
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
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);
                $variation->delete();

                $output = [
                    'success' => true,
                    'msg' => 'Category deleted succesfully',
                ];
            } catch (\Eexception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => 'Something went wrong, please try again',
                ];
            }

            return $output;
        }
    }
}
