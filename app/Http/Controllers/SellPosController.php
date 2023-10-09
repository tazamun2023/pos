<?php
/* LICENSE: This source file belongs to The Web Fosters. The customer
 * is provided a licence to use it.
 * Permission is hereby granted, to any person obtaining the licence of this
 * software and associated documentation files (the "Software"), to use the
 * Software for personal or business purpose ONLY. The Software cannot be
 * copied, published, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. THE AUTHOR CAN FIX
 * ISSUES ON INTIMATION. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     The Web Fosters <thewebfosters@gmail.com>
 * @owner      The Web Fosters <thewebfosters@gmail.com>
 * @copyright  2018 The Web Fosters
 * @license    As attached in zip file.
 */

namespace App\Http\Controllers;

use App\Account;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Warranty;
use App\ZatkaInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Facades\DataTables;

class SellPosController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('sell.view') && ! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (! empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');

        //Service staff filter
        $service_staffs = null;
        if ($is_service_staff_enabled) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('sale_pos.index')->with(compact('business_locations', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || auth()->user()->can('sell.create') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('repair.create')))) {
            abort(403, 'Unauthorized action.');
        }

        //Check if subscribed or not, then check for users quota
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\HomeController::class, 'index']));
        } elseif (! $this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellPosController::class, 'index']));
        }

        //like:repair
        $sub_type = request()->get('sub_type');

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create'], ['sub_type' => $sub_type]);
        }

        $register_details = $this->cashRegisterUtil->getCurrentCashRegister(auth()->user()->id);

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $payment_lines[] = $this->dummyPaymentLine;

        $default_location = ! empty($register_details->location_id) ? BusinessLocation::findOrFail($register_details->location_id) : null;

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        //set first location as default locaton
        if (empty($default_location)) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
                break;
            }
        }

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Shortcuts
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = ! empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $default_datetime = $this->businessUtil->format_date('now', true);

        $featured_products = ! empty($default_location) ? $default_location->getFeaturedProducts() : [];

        //pos screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type, 'job_sheet_id' => request()->get('job_sheet_id')]);
        $invoice_layouts = InvoiceLayout::forDropdown($business_id);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        return view('sale_pos.create')
            ->with(compact(
                'edit_discount',
                'edit_price',
                'business_locations',
                'bl_attributes',
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'payment_lines',
                'default_location',
                'shortcuts',
                'commission_agent',
                'categories',
                'brands',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'accounts',
                'price_groups',
                'types_of_service',
                'default_price_group_id',
                'shipping_statuses',
                'default_datetime',
                'featured_products',
                'sub_type',
                'pos_module_data',
                'invoice_schemes',
                'default_invoice_schemes',
                'invoice_layouts',
                'users'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('sell.create') && ! auth()->user()->can('direct_sell.access') && ! auth()->user()->can('so.create')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = false;
        if (! empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if (! $is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        try {
            $input = $request->except('_token');

            $input['is_quotation'] = 0;
            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
            }

            //Add change return
            $change_return = $this->dummyPaymentLine;
            if (! empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            //Check Customer credit limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = ['success' => 0,
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount]),
                ];
                if (! $is_direct_sale) {
                    return $output;
                } else {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'index'])
                        ->with('status', $output);
                }
            }

            if (! empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not, then check for users quota
                if (! $this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (! $this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellPosController::class, 'index']));
                }

                $user_id = $request->session()->get('user.id');

                $discount = ['discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount'],
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                DB::beginTransaction();

                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = \Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                //Set commission agent
                $input['commission_agent'] = ! empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;

                //If default price group for the location exists
                $price_group_id = $price_group_id == 0 && $request->has('default_price_group') ? $request->input('default_price_group') : $price_group_id;

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend'] ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = ! empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Generate reference number
                if (! empty($input['is_recurring'])) {
                    //Update reference count
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                    $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
                }

                if (! empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                //Types of service
                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = ! empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = ! empty($request->input('packing_charge')) ?
                    $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = ! empty($request->input('service_custom_field_1')) ?
                    $request->input('service_custom_field_1') : null;
                    $input['service_custom_field_2'] = ! empty($request->input('service_custom_field_2')) ?
                    $request->input('service_custom_field_2') : null;
                    $input['service_custom_field_3'] = ! empty($request->input('service_custom_field_3')) ?
                    $request->input('service_custom_field_3') : null;
                    $input['service_custom_field_4'] = ! empty($request->input('service_custom_field_4')) ?
                    $request->input('service_custom_field_4') : null;
                    $input['service_custom_field_5'] = ! empty($request->input('service_custom_field_5')) ?
                    $request->input('service_custom_field_5') : null;
                    $input['service_custom_field_6'] = ! empty($request->input('service_custom_field_6')) ?
                    $request->input('service_custom_field_6') : null;
                }

                if ($request->input('additional_expense_value_1') != '') {
                    $input['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                    $input['additional_expense_value_1'] = $request->input('additional_expense_value_1');
                }

                if ($request->input('additional_expense_value_2') != '') {
                    $input['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                    $input['additional_expense_value_2'] = $request->input('additional_expense_value_2');
                }

                if ($request->input('additional_expense_value_3') != '') {
                    $input['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                    $input['additional_expense_value_3'] = $request->input('additional_expense_value_3');
                }

                if ($request->input('additional_expense_value_4') != '') {
                    $input['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                    $input['additional_expense_value_4'] = $request->input('additional_expense_value_4');
                }

                $input['selling_price_group_id'] = $price_group_id;

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = request()->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = request()->get('res_waiter_id');
                }

                //upload document
                $input['document'] = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');

                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

                //Upload Shipping documents
                Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

                $change_return['amount'] = $input['change_return'] ?? 0;
                $change_return['is_return'] = 1;

                $input['payment'][] = $change_return;

                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                if (! $transaction->is_suspend && ! empty($input['payment']) && ! $is_credit_sale) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                //Check for final and do some processing.
                if ($input['status'] == 'final') {
                    if (! $is_direct_sale) {
                        //set service staff timer
                        foreach ($input['products'] as $product_line) {
                            if (! empty($product_line['res_service_staff_id'])) {
                                $product = Product::find($product_line['product_id']);

                                if (! empty($product->preparation_time_in_minutes)) {
                                    $service_staff = User::find($product_line['res_service_staff_id']);

                                    $base_time = \Carbon::parse($transaction->transaction_date);

                                    //if already assigned set base time as available_at
                                    if (! empty($service_staff->available_at) && \Carbon::parse($service_staff->available_at)->gt(\Carbon::now())) {
                                        $base_time = \Carbon::parse($service_staff->available_at);
                                    }

                                    $total_minutes = $product->preparation_time_in_minutes * $this->transactionUtil->num_uf($product_line['quantity']);

                                    $service_staff->available_at = $base_time->addMinutes($total_minutes);
                                    $service_staff->save();
                                }
                            }
                        }
                    }
                    //update product stock
                    foreach ($input['products'] as $product) {
                        $decrease_qty = $this->productUtil
                                    ->num_uf($product['quantity']);
                        if (! empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        if ($product['enable_stock']) {
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $decrease_qty
                            );
                        }

                        if ($product['product_type'] == 'combo') {
                            //Decrease quantity of combo as well.
                            $this->productUtil
                                ->decreaseProductQuantityCombo(
                                    $product['combo'],
                                    $input['location_id']
                                );
                        }
                    }

                    //Add payments to Cash Register
                    if (! $is_direct_sale && ! $transaction->is_suspend && ! empty($input['payment']) && ! $is_credit_sale) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }

                    //Update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                    $transaction->payment_status = $payment_status;

                    if ($request->session()->get('business.enable_rp') == 1) {
                        $redeemed = ! empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $redeemed);
                    }

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                    $business = ['id' => $business_id,
                        'accounting_method' => $request->session()->get('business.accounting_method'),
                        'location_id' => $input['location_id'],
                        'pos_settings' => $pos_settings,
                    ];
                    $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    //Auto send notification
                    $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                if (! empty($transaction->sales_order_ids)) {
                    $this->transactionUtil->updateSalesOrderStatus($transaction->sales_order_ids);
                }

                $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                $this->transactionUtil->activityLog($transaction, 'added');

                DB::commit();

                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);

                    return redirect()->to($url.'?print_on_load=true');
                }

                $msg = trans('sale.pos_sale_added');
                $receipt = '';
                $invoice_layout_id = $request->input('invoice_layout_id');
                $print_invoice = false;
                if (! $is_direct_sale) {
                    if ($input['status'] == 'draft') {
                        $msg = trans('sale.draft_added');

                        if ($input['is_quotation'] == 1) {
                            $msg = trans('lang_v1.quotation_added');
                            $print_invoice = true;
                        }
                    } elseif ($input['status'] == 'final') {
                        $print_invoice = true;
                    }
                }

                if ($transaction->is_suspend == 1 && empty($pos_settings['print_on_suspend'])) {
                    $print_invoice = false;
                }

                if (! auth()->user()->can('print_invoice')) {
                    $print_invoice = false;
                }

                if ($print_invoice) {
//                    dd($request->contact_id);
                    $contacts = Contact::where('id', $request->contact_id)->first();
                    $number = $contacts->mobile;
                    $is_valid_num = validate_mobile($number);;
                    $is_admin = $this->isAdmin();
                    if ($request->contact_id != 1 && $is_valid_num && $is_admin){
//                        $transaction = Transaction::where('business_id', $business_id)
//                            ->findorfail($transaction->id);
                        $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
//                        $new_url = is_short_url($url);
                        $body = $contacts->name.' '.'فاتورتك جاهزة. على الرابط'.' '.$url;
//                        dd($number);
                        OurSMS($number, $body);
                    }

                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

                if (! empty($whatsapp_link)) {
                    $output['whatsapp_link'] = $whatsapp_link;
                }
            } else {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $msg = trans('messages.something_went_wrong');

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }
            if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                'msg' => $msg,
            ];
        }

        if (! $is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getQuotations'])
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getDrafts'])
                        ->with('status', $output);
                }
            } elseif ($input['status'] == 'quotation') {
                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'getQuotations'])
                    ->with('status', $output);
            } elseif (isset($input['type']) && $input['type'] == 'sales_order') {
                return redirect()
                    ->action([\App\Http\Controllers\SalesOrderController::class, 'index'])
                    ->with('status', $output);
            } else {
                if (! empty($input['sub_type']) && $input['sub_type'] == 'repair') {
                    $redirect_url = $input['print_label'] == 1 ? action([\Modules\Repair\Http\Controllers\RepairController::class, 'printLabel'], [$transaction->id]) : action([\Modules\Repair\Http\Controllers\RepairController::class, 'index']);

                    return redirect($redirect_url)
                        ->with('status', $output);
                }

                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'index'])
                    ->with('status', $output);
            }
        }
    }

    function sendSMS($mobileNumber, $messageContent)
    {
//        info('sms'. $mobileNumber .' '. $messageContent);
        $user = 'eis1980';
        $password = 'Mhamcloud@1980';
        $sendername = 'Mhamcloud';
        $text = urlencode( $messageContent);
        $to = $mobileNumber;
// auth call
        $url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=full";

//لارجاع القيمه json
//$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";
// لارجاع القيمه xml
//$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=xml";
// لارجاع القيمه string
//$url = "http://www.oursms.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E";
// Call API and get return message
//fopen($url,"r");
        $ret = file_get_contents($url);
        echo nl2br($ret);
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param  string  $printer_type = null
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true,
        $invoice_layout_id = null,
        $is_delivery_note = false
    ) {
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        $invoice_layout_id = ! empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
//dd($receipt_details);
        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details'))->render();

            return $output;
        }

        if ($is_delivery_note) {
            $output['html_content'] = view('sale_pos.receipts.delivery_note', compact('receipt_details'))->render();

            return $output;
        }

        $output['print_title'] = $receipt_details->invoice_no;
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = ! empty($receipt_details->design) ? 'sale_pos.receipts.'.$receipt_details->design : 'sale_pos.receipts.classic';

            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }
//dd($receipt_details->design);

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
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || auth()->user()->can('sell.update')
        || auth()->user()->can('edit_pos_payment')
         || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') &&
         auth()->user()->can('repair.update')))) {
            abort(403, 'Unauthorized action.');
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (! $this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]), ]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                'msg' => __('lang_v1.return_exist'), ]);
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->with(['price_group', 'types_of_service'])
                            ->findorfail($id);

        $location_id = $transaction->location_id;
        $business_location = BusinessLocation::find($location_id);
        $payment_types = $this->productUtil->payment_types($business_location, true);
        $location_printer_type = $business_location->receipt_printer_type;
        $sell_details = TransactionSellLine::join(
                            'products AS p',
                            'transaction_sell_lines.product_id',
                            '=',
                            'p.id'
                        )
                        ->join(
                            'variations AS variations',
                            'transaction_sell_lines.variation_id',
                            '=',
                            'variations.id'
                        )
                        ->join(
                            'product_variations AS pv',
                            'variations.product_variation_id',
                            '=',
                            'pv.id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
                        ->where('transaction_sell_lines.transaction_id', $id)
                        ->with(['warranties'])
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'p.name as product_actual_name',
                            'p.type as product_type',
                            'pv.name as product_variation_name',
                            'pv.is_dummy as is_dummy',
                            'variations.name as variation_name',
                            'variations.sub_sku',
                            'p.barcode_type',
                            'p.enable_sr_no',
                            'variations.id as variation_id',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'u.short_name as second_unit',
                            'transaction_sell_lines.secondary_unit_quantity',
                            'transaction_sell_lines.tax_id as tax_id',
                            'transaction_sell_lines.item_tax as item_tax',
                            'transaction_sell_lines.unit_price as default_sell_price',
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.id',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.parent_sell_line_id',
                            'transaction_sell_lines.lot_no_line_id',
                            'transaction_sell_lines.line_discount_type',
                            'transaction_sell_lines.line_discount_amount',
                            'transaction_sell_lines.res_service_staff_id',
                            'units.id as unit_id',
                            'transaction_sell_lines.sub_unit_id',

                            //qty_available not added when negative to avoid max quanity getting decreased in edit and showing error in max quantity validation
                            DB::raw('IF(vld.qty_available > 0, vld.qty_available + transaction_sell_lines.quantity, transaction_sell_lines.quantity) AS qty_available')
                        )
                        ->get();
        if (! empty($sell_details)) {
            foreach ($sell_details as $key => $value) {

                //If modifier or combo sell line then unset
                if (! empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (! empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (! empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null,
                            ];
                        }
                        $sell_details[$key]->qty_available =
                        $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
            }
        }

        $featured_products = $business_location->getFeaturedProducts();

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && ! empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }
        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $warranties = $this->__getwarranties();
        $sub_type = request()->get('sub_type');

        //pos screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $invoice_layouts = InvoiceLayout::forDropdown($business_id);

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
        $only_payment = request()->segment(2) == 'payment';

        return view('sale_pos.edit')
            ->with(compact('business_details', 'taxes', 'payment_types', 'walk_in_customer',
            'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts',
            'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups',
            'brands', 'accounts', 'waiters', 'redeem_details', 'edit_price', 'edit_discount',
            'shipping_statuses', 'warranties', 'sub_type', 'pos_module_data', 'invoice_schemes',
            'default_invoice_schemes', 'invoice_layouts', 'featured_products', 'customer_due',
            'users', 'only_payment'));
    }

    /**
     * Update the specified resource in storage.
     * TODO: Add edit log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('sell.update') && ! auth()->user()->can('direct_sell.access') &&
        ! auth()->user()->can('so.update') && ! auth()->user()->can('edit_pos_payment')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->except('_token');

            //status is send as quotation from edit sales screen.
            $input['is_quotation'] = 0;
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
                $input['is_quotation'] = 0;
            } else {
                $input['sub_status'] = null;
                $input['is_quotation'] = 0;
            }

            $is_direct_sale = false;
            if (! empty($input['products'])) {
                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                $status_before = $transaction_before->status;
                $rp_earned_before = $transaction_before->rp_earned;
                $rp_redeemed_before = $transaction_before->rp_redeemed;

                if ($transaction_before->is_direct_sale == 1) {
                    $is_direct_sale = true;
                }

                $sales_order_ids = $transaction_before->sales_order_ids ?? [];

                //Add change return
                $change_return = $this->dummyPaymentLine;
                if (! empty($input['payment']['change_return'])) {
                    $change_return = $input['payment']['change_return'];
                    unset($input['payment']['change_return']);
                }

                //Check Customer credit limit
                $is_credit_limit_exeeded = $transaction_before->type == 'sell' ? $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id) : false;

                if ($is_credit_limit_exeeded !== false) {
                    $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                    $output = ['success' => 0,
                        'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount]),
                    ];
                    if (! $is_direct_sale) {
                        return $output;
                    } else {
                        return redirect()
                            ->action([\App\Http\Controllers\SellController::class, 'index'])
                            ->with('status', $output);
                    }
                }

                //Check if there is a open register, if no then redirect to Create Register screen.
                if (! $is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                    return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
                }

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = ['discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount'],
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                if (! empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }

                $input['commission_agent'] = ! empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend'] ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = ! empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                if ($status_before == 'draft' && ! empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                //Types of service
                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = ! empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = ! empty($request->input('packing_charge')) ?
                    $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = ! empty($request->input('service_custom_field_1')) ?
                    $request->input('service_custom_field_1') : null;
                    $input['service_custom_field_2'] = ! empty($request->input('service_custom_field_2')) ?
                    $request->input('service_custom_field_2') : null;
                    $input['service_custom_field_3'] = ! empty($request->input('service_custom_field_3')) ?
                    $request->input('service_custom_field_3') : null;
                    $input['service_custom_field_4'] = ! empty($request->input('service_custom_field_4')) ?
                    $request->input('service_custom_field_4') : null;
                    $input['service_custom_field_5'] = ! empty($request->input('service_custom_field_5')) ?
                    $request->input('service_custom_field_5') : null;
                    $input['service_custom_field_6'] = ! empty($request->input('service_custom_field_6')) ?
                    $request->input('service_custom_field_6') : null;
                }

                $input['selling_price_group_id'] = $price_group_id;

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = request()->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = request()->get('res_waiter_id');
                }

                //upload document
                $document_name = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');
                if (! empty($document_name)) {
                    $input['document'] = $document_name;
                }

                if ($request->input('additional_expense_value_1') != '') {
                    $input['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                    $input['additional_expense_value_1'] = $request->input('additional_expense_value_1');
                }

                if ($request->input('additional_expense_value_2') != '') {
                    $input['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                    $input['additional_expense_value_2'] = $request->input('additional_expense_value_2');
                }

                if ($request->input('additional_expense_value_3') != '') {
                    $input['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                    $input['additional_expense_value_3'] = $request->input('additional_expense_value_3');
                }

                if ($request->input('additional_expense_value_4') != '') {
                    $input['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                    $input['additional_expense_value_4'] = $request->input('additional_expense_value_4');
                }
                $only_payment = ! $is_direct_sale && ! auth()->user()->can('sell.update') && auth()->user()->can('edit_pos_payment');

                //if edit pos not allowed and only edit payment allowed
                if ($only_payment) {
                    DB::beginTransaction();
                    $this->onlyUpdatePayment($transaction_before, $input);
                    DB::commit();

                    $can_print_invoice = auth()->user()->can('print_invoice');
                    $invoice_layout_id = $request->input('invoice_layout_id');

                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction_before->id, null, false, true, $invoice_layout_id);
                    $msg = trans('purchase.payment_updated_success');

                    $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

                    return $output;
                }

                //Begin transaction
                DB::beginTransaction();

                $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

                //update service staff timer
                if (! $is_direct_sale && $transaction->status == 'final') {
                    foreach ($input['products'] as $product_line) {
                        if (! empty($product_line['res_service_staff_id'])) {
                            $product = Product::find($product_line['product_id']);

                            if (! empty($product->preparation_time_in_minutes)) {
                                //if quantity not increase skip line
                                $quantity = $this->transactionUtil->num_uf($product_line['quantity']);
                                if (! empty($product_line['transaction_sell_lines_id'])) {
                                    $sl = TransactionSellLine::find($product_line['transaction_sell_lines_id']);

                                    if ($sl->quantity >= $quantity && $sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                        continue;
                                    }

                                    //if same service staff assigned quantity is only increased quantity
                                    if ($sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                        $quantity = $quantity - $sl->quantity;
                                    }
                                }

                                $service_staff = User::find($product_line['res_service_staff_id']);

                                $base_time = \Carbon::parse($transaction->transaction_date);
                                //is transaction date is past take base time as now
                                if ($base_time->lt(\Carbon::now())) {
                                    $base_time = \Carbon::now();
                                }

                                //if already assigned set base time as available_at
                                if (! empty($service_staff->available_at) && \Carbon::parse($service_staff->available_at)->gt(\Carbon::now())) {
                                    $base_time = \Carbon::parse($service_staff->available_at);
                                }

                                $total_minutes = $product->preparation_time_in_minutes * $quantity;

                                $service_staff->available_at = $base_time->addMinutes($total_minutes);
                                $service_staff->save();
                            }
                        }
                    }
                }

                //Update Sell lines
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);

                //Update update lines
                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                $new_sales_order_ids = $transaction->sales_order_ids ?? [];
                $sales_order_ids = array_unique(array_merge($sales_order_ids, $new_sales_order_ids));

                if (! empty($sales_order_ids)) {
                    $this->transactionUtil->updateSalesOrderStatus($sales_order_ids);
                }

                if (! $transaction->is_suspend && ! $is_credit_sale) {
                    //Add change return
                    $change_return['amount'] = $input['change_return'] ?? 0;
                    $change_return['is_return'] = 1;
                    if (! empty($input['change_return_id'])) {
                        $change_return['payment_id'] = $input['change_return_id'];
                    }
                    $input['payment'][] = $change_return;

                    if (! $is_direct_sale || auth()->user()->can('sell.payments')) {
                        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);

                        //Update cash register
                        if (! $is_direct_sale) {
                            $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                        }
                    }
                }

                if ($request->session()->get('business.enable_rp') == 1) {
                    $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
                }

                Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

                if ($transaction->type == 'sell') {

                    //Update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                    $transaction->payment_status = $payment_status;

                    //Update product stock
                    $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                    $business = ['id' => $business_id,
                        'accounting_method' => $request->session()->get('business.accounting_method'),
                        'location_id' => $input['location_id'],
                        'pos_settings' => $pos_settings,
                    ];
                    $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

                    //Auto send notification
                    $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                $log_properties = [];
                if (isset($input['repair_completed_on'])) {
                    $completed_on = ! empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                    if ($transaction->repair_completed_on != $completed_on) {
                        $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                        $log_properties['completed_on_to'] = $completed_on;
                    }
                }

                $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

                DB::commit();

                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

                    return redirect()->to($url.'?print_on_load=true');
                }

                $msg = __('lang_v1.updated_success');
                $receipt = '';
                $can_print_invoice = auth()->user()->can('print_invoice');
                $invoice_layout_id = $request->input('invoice_layout_id');

                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans('sale.draft_added');
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans('lang_v1.quotation_updated');
                    if (! $is_direct_sale && $can_print_invoice) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans('sale.pos_sale_updated');
                    if (! $is_direct_sale && $can_print_invoice) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

                if (! empty($whatsapp_link)) {
                    $output['whatsapp_link'] = $whatsapp_link;
                }
            } else {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if (! $is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getQuotations'])
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getDrafts'])
                        ->with('status', $output);
                }
            } else {
                if (! empty($transaction->sub_type) && $transaction->sub_type == 'repair') {
                    return redirect()
                        ->action([\Modules\Repair\Http\Controllers\RepairController::class, 'index'])
                        ->with('status', $output);
                }

                if ($transaction->type == 'sales_order') {
                    return redirect()
                    ->action([\App\Http\Controllers\SalesOrderController::class, 'index'])
                    ->with('status', $output);
                }

                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'index'])
                    ->with('status', $output);
            }
        }
    }

    /**
     * Function to add/edit payments for a pos sale
     */
    private function onlyUpdatePayment($transaction, $input)
    {
        //Add change return
        $change_return = $this->dummyPaymentLine;
        if (! empty($input['payment']['change_return'])) {
            $change_return = $input['payment']['change_return'];
            unset($input['payment']['change_return']);
        }

        //Add change return
        $change_return['amount'] = $input['change_return'] ?? 0;
        $change_return['is_return'] = 1;
        if (! empty($input['change_return_id'])) {
            $change_return['payment_id'] = $input['change_return_id'];
        }
        $input['payment'][] = $change_return;
        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
        $this->cashRegisterUtil->updateSellPayments($transaction->status, $transaction, $input['payment']);

        //Update payment status
        $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        $transaction_before = $transaction;
        $transaction->payment_status = $payment_status;

        if ($payment_status == 'paid') {
            $transaction->is_suspend = 0;
            $transaction->save();
        }

        $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('sell.delete') && ! auth()->user()->can('direct_sell.delete') && ! auth()->user()->can('so.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                //Begin transaction
                DB::beginTransaction();

                $output = $this->transactionUtil->deleteSale($business_id, $id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans('messages.something_went_wrong');
            }

            return $output;
        }
    }

    public function getSalesOrderLines()
    {
        $business_id = request()->session()->get('user.business_id');
        $sales_order_id = request()->input('sales_order_id');
        $row_count = request()->get('product_row');
        $row_count = $row_count + 1;

        $sales_order = Transaction::where('business_id', $business_id)
                                ->where('type', 'sales_order')
                                ->with(['sell_lines'])
                                ->find($sales_order_id);

        $html = '<table>';

        if (! empty($sales_order)) {
            foreach ($sales_order->sell_lines as $sell_line) {
                $quantity = $sell_line->quantity - $sell_line->so_quantity_invoiced;
                $sell_line->qty_available = $quantity;
                $sell_line->formatted_qty_available = $this->transactionUtil->num_f($quantity);
                $sell_line_row = $this->getSellLineRow($sell_line->variation_id, $sales_order->location_id, $quantity, $row_count, true, $sell_line);
                $html .= $sell_line_row['html_content'];
                $row_count++;
            }
        }
        $html .= '</table>';

        return [
            'html' => $html,
            'sales_order' => $sales_order,
        ];
    }

    private function getSellLineRow($variation_id, $location_id, $quantity, $row_count, $is_direct_sell, $so_line = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $business_details = $this->businessUtil->getDetails($business_id);
        //Check for weighing scale barcode
        $weighing_barcode = request()->get('weighing_scale_barcode');

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $check_qty = ! empty($pos_settings['allow_overselling']) ? false : true;

        $is_sales_order = request()->has('is_sales_order') && request()->input('is_sales_order') == 'true' ? true : false;
        $is_draft = request()->has('is_draft') && request()->input('is_draft') == 'true' ? true : false;

        if ($is_sales_order || ! empty($so_line) || $is_draft) {
            $check_qty = false;
        }

        if (request()->input('disable_qty_alert') === 'true') {
            $pos_settings['allow_overselling'] = true;
        }

        $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);

        if (! isset($product->quantity_ordered)) {
            $product->quantity_ordered = $quantity;
        }

        $product->secondary_unit_quantity = ! isset($product->secondary_unit_quantity) ? 0 : $product->secondary_unit_quantity;

        $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);

        //Get customer group and change the price accordingly
        $customer_id = request()->get('customer_id', null);
        $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
        $percent = (empty($cg) || empty($cg->amount) || $cg->price_calculation_type != 'percentage') ? 0 : $cg->amount;
        $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
        $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

        $enabled_modules = $this->transactionUtil->allModulesEnabled();

        //Get lot number dropdown if enabled
        $lot_numbers = [];
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
            foreach ($lot_number_obj as $lot_number) {
                $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                $lot_numbers[] = $lot_number;
            }
        }
        $product->lot_numbers = $lot_numbers;

        $purchase_line_id = request()->get('purchase_line_id');

        $price_group = request()->input('price_group');
        if (! empty($price_group)) {
            $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

            if (! empty($variation_group_prices['price_inc_tax'])) {
                $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                $product->default_sell_price = $variation_group_prices['price_exc_tax'];
            }
        }

        $warranties = $this->__getwarranties();

        $output['success'] = true;
        $output['enable_sr_no'] = $product->enable_sr_no;

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && ! empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
        }

        $last_sell_line = null;
        if ($is_direct_sell) {
            $last_sell_line = $this->getLastSellLineForCustomer($variation_id, $customer_id, $location_id);
        }

        if (request()->get('type') == 'sell-return') {
            $output['html_content'] = view('sell_return.partials.product_row')
                        ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units'))
                        ->render();
        } else {
            $is_cg = ! empty($cg->id) ? true : false;

            $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $price_group, $variation_id);

            if ($is_direct_sell) {
                $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
            } else {
                $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
            }

            $output['html_content'] = view('sale_pos.product_row')
                        ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'purchase_line_id', 'warranties', 'quantity', 'is_direct_sell', 'so_line', 'is_sales_order', 'last_sell_line'))
                        ->render();
        }

        return $output;
    }

    /**
     * Finds last sell line of a variation for the customer for a location
     */
    private function getLastSellLineForCustomer($variation_id, $customer_id, $location_id)
    {
        $sell_line = TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                            ->where('t.location_id', $location_id)
                            ->where('t.contact_id', $customer_id)
                            ->where('t.type', 'sell')
                            ->where('t.status', 'final')
                            ->where('transaction_sell_lines.variation_id', $variation_id)
                            ->orderBy('t.transaction_date', 'desc')
                            ->select('transaction_sell_lines.*')
                            ->first();

        return $sell_line;
    }

    /**
     * Returns the HTML row for a product in POS
     *
     * @param  int  $variation_id
     * @param  int  $location_id
     * @return \Illuminate\Http\Response
     */
    public function getProductRow($variation_id, $location_id)
    {
        $output = [];

        try {
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $quantity = request()->get('quantity', 1);
            $weighing_barcode = request()->get('weighing_scale_barcode', null);

            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }

            if ($variation_id == 'null' && ! empty($weighing_barcode)) {
                $product_details = $this->__parseWeighingBarcode($weighing_barcode);
                if ($product_details['success']) {
                    $variation_id = $product_details['variation_id'];
                    $quantity = $product_details['qty'];
                } else {
                    $output['success'] = false;
                    $output['msg'] = $product_details['msg'];

                    return $output;
                }
            }

            $output = $this->getSellLineRow($variation_id, $location_id, $quantity, $row_count, $is_direct_sell);

            if ($this->transactionUtil->isModuleEnabled('modifiers') && ! $is_direct_sell) {
                $variation = Variation::find($variation_id);
                $business_id = request()->session()->get('user.business_id');
                $this_product = Product::where('business_id', $business_id)
                                        ->with(['modifier_sets'])
                                        ->find($variation->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] = view('restaurant.product_modifier_set.modifier_for_product')
                    ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output['success'] = false;
            $output['msg'] = __('lang_v1.item_out_of_stock');
        }

        return $output;
    }

    /**
     * Returns the HTML row for a payment in POS
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $row_index = $request->input('row_index');
        $location_id = $request->input('location_id');
        $removable = true;
        $payment_types = $this->productUtil->payment_types($location_id, true);

        $payment_line = $this->dummyPaymentLine;

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('sale_pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    /**
     * Returns recent transactions
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');

        $register = $this->cashRegisterUtil->getCurrentCashRegister($user_id);

        $query = Transaction::with('zatka_info')->where('business_id', $business_id)
                        ->where('transactions.created_by', $user_id)
                        ->where('transactions.type', 'sell')
                        ->where('is_direct_sale', 0);

        if ($transaction_status == 'final') {
            //Commented as credit sales not showing
            // if (!empty($register->id)) {
            //     $query->leftjoin('cash_register_transactions as crt', 'transactions.id', '=', 'crt.transaction_id')
            //     ->where('crt.cash_register_id', $register->id);
            // }
        }

        if ($transaction_status == 'quotation') {
            $query->where('transactions.status', 'draft')
                ->where('sub_status', 'quotation');
        } elseif ($transaction_status == 'draft') {
            $query->where('transactions.status', 'draft')
                ->whereNull('sub_status');
        } else {
            $query->where('transactions.status', $transaction_status);
        }

        $transaction_sub_type = $request->get('transaction_sub_type');
        if (! empty($transaction_sub_type)) {
            $query->where('transactions.sub_type', $transaction_sub_type);
        } else {
            $query->where('transactions.sub_type', null);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')
                            ->groupBy('transactions.id')
                            ->select('transactions.*')
                            ->with(['contact', 'table'])
                            ->limit(10)
                            ->get();

        return view('sale_pos.partials.recent_transactions')
            ->with(compact('transactions', 'transaction_sub_type'));
    }

    public function isAdmin()
    {
        $administrator_list = config('constants.administrator_usernames');
        $is_admin = false;
        $is_user = auth()->user()->username;
        if (in_array($is_user, explode(',', $administrator_list))) {
            $is_admin = true;
        }
        return $is_admin;
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];

                $business_id = $request->session()->get('user.business_id');


                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->with(['location'])
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (! empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                $is_package_slip = ! empty($request->input('package_slip')) ? true : false;
                $is_delivery_note = ! empty($request->input('delivery_note')) ? true : false;

                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, $is_package_slip, false, $invoice_layout_id, $is_delivery_note);

                if (! empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }




    function SendInvoiceToZatka($trx_id){
        $trx_id = Crypt::decrypt($trx_id);
        $transaction = Transaction::find($trx_id);
//        dd($transaction);
        $location_details = BusinessLocation::find($transaction->location_id);
        $invoice_layout_id = ! empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($transaction->business_id, $invoice_layout_id);
        $business_details = $this->businessUtil->getDetails($transaction->business_id);

        $printer_type = null;
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction->id, $transaction->location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
//        dd($receipt_details);
        $invoice_xml =  view('invoice', compact('transaction', 'receipt_details'));


        $header = [
            'accept' => 'application/json',
            'accept-language' => 'en',
            'Clearance-Status' => '0',
            'Accept-Version' => 'V2',
            'Authorization' => 'Basic VFVsSlJERkVRME5CTTIxblFYZEpRa0ZuU1ZSaWQwRkJaVE5WUVZsV1ZUTTBTUzhyTlZGQlFrRkJRamRrVkVGTFFtZG5jV2hyYWs5UVVWRkVRV3BDYWsxU1ZYZEZkMWxMUTFwSmJXbGFVSGxNUjFGQ1IxSlpSbUpIT1dwWlYzZDRSWHBCVWtKbmIwcHJhV0ZLYXk5SmMxcEJSVnBHWjA1dVlqTlplRVo2UVZaQ1oyOUthMmxoU21zdlNYTmFRVVZhUm1ka2JHVklVbTVaV0hBd1RWSjNkMGRuV1VSV1VWRkVSWGhPVlZVeGNFWlRWVFZYVkRCc1JGSlRNVlJrVjBwRVVWTXdlRTFDTkZoRVZFbDVUVVJaZUUxcVJUTk9SRUV4VFd4dldFUlVTVEJOUkZsNFRWUkZNMDVFUVRGTmJHOTNVMVJGVEUxQmEwZEJNVlZGUW1oTlExVXdSWGhFYWtGTlFtZE9Wa0pCYjFSQ1YwWnVZVmQ0YkUxU1dYZEdRVmxFVmxGUlRFVjNNVzlaV0d4b1NVaHNhRm95YUhSaU0xWjVUVkpKZDBWQldVUldVVkZFUlhkcmVFMXFZM1ZOUXpSM1RHcEZkMVpxUVZGQ1oyTnhhR3RxVDFCUlNVSkNaMVZ5WjFGUlFVTm5Ua05CUVZSVVFVczViSEpVVm10dk9YSnJjVFphV1dOak9VaEVVbHBRTkdJNVV6UjZRVFJMYlRkWldFb3JjMjVVVm1oTWEzcFZNRWh6YlZOWU9WVnVPR3BFYUZKVVQwaEVTMkZtZERoREwzVjFWVms1TXpSMmRVMU9ielJKUTBwNlEwTkJhVTEzWjFsblIwRXhWV1JGVVZOQ1owUkNLM0JJZDNkbGFrVmlUVUpyUjBFeFZVVkNRWGRUVFZNeGIxbFliR2htUkVsMFRXcE5NR1pFVFhSTlZFVjVUVkk0ZDBoUldVdERXa2x0YVZwUWVVeEhVVUpCVVhkUVRYcEJkMDFFWXpGT1ZHYzBUbnBCZDAxRVFYcE5VVEIzUTNkWlJGWlJVVTFFUVZGNFRWUkJkMDFTUlhkRWQxbEVWbEZSWVVSQmFHRlpXRkpxV1ZOQmVFMXFSVmxOUWxsSFFURlZSVVIzZDFCU2JUbDJXa05DUTJSWVRucGhWelZzWXpOTmVrMUNNRWRCTVZWa1JHZFJWMEpDVTJkdFNWZEVObUpRWm1KaVMydHRWSGRQU2xKWWRrbGlTRGxJYWtGbVFtZE9Wa2hUVFVWSFJFRlhaMEpTTWxsSmVqZENjVU56V2pGak1XNWpLMkZ5UzJOeWJWUlhNVXg2UWs5Q1owNVdTRkk0UlZKNlFrWk5SVTluVVdGQkwyaHFNVzlrU0ZKM1QyazRkbVJJVGpCWk0wcHpURzV3YUdSSFRtaE1iV1IyWkdrMWVsbFRPVVJhV0Vvd1VsYzFlV0l5ZUhOTU1WSlVWMnRXU2xSc1dsQlRWVTVHVEZaT01WbHJUa0pNVkVWMVdUTktjMDFKUjNSQ1oyZHlRbWRGUmtKUlkwSkJVVk5DYjBSRFFtNVVRblZDWjJkeVFtZEZSa0pSWTNkQldWcHBZVWhTTUdORWIzWk1NMUo2WkVkT2VXSkROVFpaV0ZKcVdWTTFibUl6V1hWak1rVjJVVEpXZVdSRlZuVmpiVGx6WWtNNVZWVXhjRVpoVnpVeVlqSnNhbHBXVGtSUlZFVjFXbGhvTUZveVJqWmtRelZ1WWpOWmRXSkhPV3BaVjNobVZrWk9ZVkpWYkU5V2F6bEtVVEJWZEZVelZtbFJNRVYwVFZObmVFdFROV3BqYmxGM1MzZFpTVXQzV1VKQ1VWVklUVUZIUjBneWFEQmtTRUUyVEhrNU1HTXpVbXBqYlhkMVpXMUdNRmt5UlhWYU1qa3lURzVPYUV3eU9XcGpNMEYzUkdkWlJGWlNNRkJCVVVndlFrRlJSRUZuWlVGTlFqQkhRVEZWWkVwUlVWZE5RbEZIUTBOelIwRlJWVVpDZDAxRFFtZG5ja0puUlVaQ1VXTkVRWHBCYmtKbmEzSkNaMFZGUVZsSk0wWlJiMFZIYWtGWlRVRnZSME5EYzBkQlVWVkdRbmROUTAxQmIwZERRM05IUVZGVlJrSjNUVVJOUVc5SFEwTnhSMU5OTkRsQ1FVMURRVEJyUVUxRldVTkpVVU5XZDBSTlkzRTJVRThyVFdOdGMwSllWWG92ZGpGSFpHaEhjRGR5Y1ZOaE1rRjRWRXRUZGpnek9FbEJTV2hCVDBKT1JFSjBPU3N6UkZOc2FXcHZWbVo0ZW5Ka1JHZzFNamhYUXpNM2MyMUZaRzlIVjFaeVUzQkhNUT09OlhsajE1THlNQ2dTQzY2T2JuRU8vcVZQZmhTYnMza0RUalduR2hlWWhmU3M9',
            'Content-Type' => 'application/json',
        ];


        $encoded_invoice = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPEludm9pY2UgeG1sbnM9InVybjpvYXNpczpuYW1lczpzcGVjaWZpY2F0aW9uOnVibDpzY2hlbWE6eHNkOkludm9pY2UtMiIgeG1sbnM6Y2FjPSJ1cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2NoZW1hOnhzZDpDb21tb25BZ2dyZWdhdGVDb21wb25lbnRzLTIiIHhtbG5zOmNiYz0idXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNjaGVtYTp4c2Q6Q29tbW9uQmFzaWNDb21wb25lbnRzLTIiIHhtbG5zOmV4dD0idXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNjaGVtYTp4c2Q6Q29tbW9uRXh0ZW5zaW9uQ29tcG9uZW50cy0yIj48ZXh0OlVCTEV4dGVuc2lvbnM+CiAgICA8ZXh0OlVCTEV4dGVuc2lvbj4KICAgICAgICA8ZXh0OkV4dGVuc2lvblVSST51cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6ZHNpZzplbnZlbG9wZWQ6eGFkZXM8L2V4dDpFeHRlbnNpb25VUkk+CiAgICAgICAgPGV4dDpFeHRlbnNpb25Db250ZW50PgogICAgICAgICAgICA8IS0tIFBsZWFzZSBub3RlIHRoYXQgdGhlIHNpZ25hdHVyZSB2YWx1ZXMgYXJlIHNhbXBsZSB2YWx1ZXMgb25seSAtLT4KICAgICAgICAgICAgPHNpZzpVQkxEb2N1bWVudFNpZ25hdHVyZXMgeG1sbnM6c2lnPSJ1cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2NoZW1hOnhzZDpDb21tb25TaWduYXR1cmVDb21wb25lbnRzLTIiIHhtbG5zOnNhYz0idXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNjaGVtYTp4c2Q6U2lnbmF0dXJlQWdncmVnYXRlQ29tcG9uZW50cy0yIiB4bWxuczpzYmM9InVybjpvYXNpczpuYW1lczpzcGVjaWZpY2F0aW9uOnVibDpzY2hlbWE6eHNkOlNpZ25hdHVyZUJhc2ljQ29tcG9uZW50cy0yIj4KICAgICAgICAgICAgICAgIDxzYWM6U2lnbmF0dXJlSW5mb3JtYXRpb24+CiAgICAgICAgICAgICAgICAgICAgPGNiYzpJRD51cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2lnbmF0dXJlOjE8L2NiYzpJRD4KICAgICAgICAgICAgICAgICAgICA8c2JjOlJlZmVyZW5jZWRTaWduYXR1cmVJRD51cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2lnbmF0dXJlOkludm9pY2U8L3NiYzpSZWZlcmVuY2VkU2lnbmF0dXJlSUQ+CiAgICAgICAgICAgICAgICAgICAgPGRzOlNpZ25hdHVyZSB4bWxuczpkcz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyIgSWQ9InNpZ25hdHVyZSI+CiAgICAgICAgICAgICAgICAgICAgICAgIDxkczpTaWduZWRJbmZvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkNhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDA2LzEyL3htbC1jMTRuMTEiLz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNlY2RzYS1zaGEyNTYiLz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpSZWZlcmVuY2UgSWQ9Imludm9pY2VTaWduZWREYXRhIiBVUkk9IiI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlRyYW5zZm9ybXM+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy9UUi8xOTk5L1JFQy14cGF0aC0xOTk5MTExNiI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WFBhdGg+bm90KC8vYW5jZXN0b3Itb3Itc2VsZjo6ZXh0OlVCTEV4dGVuc2lvbnMpPC9kczpYUGF0aD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kczpUcmFuc2Zvcm0+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy9UUi8xOTk5L1JFQy14cGF0aC0xOTk5MTExNiI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WFBhdGg+bm90KC8vYW5jZXN0b3Itb3Itc2VsZjo6Y2FjOlNpZ25hdHVyZSk8L2RzOlhQYXRoPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlRyYW5zZm9ybT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnL1RSLzE5OTkvUkVDLXhwYXRoLTE5OTkxMTE2Ij4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpYUGF0aD5ub3QoLy9hbmNlc3Rvci1vci1zZWxmOjpjYWM6QWRkaXRpb25hbERvY3VtZW50UmVmZXJlbmNlW2NiYzpJRD0nUVInXSk8L2RzOlhQYXRoPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlRyYW5zZm9ybT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDYvMTIveG1sLWMxNG4xMSIvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6VHJhbnNmb3Jtcz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6RGlnZXN0TWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjc2hhMjU2Ii8+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdFZhbHVlPlBFeDhiTkZjRU1FcEh6VVZ2UW50UUk2b3Q4ZUZxVFQvbDU5YitIMUhxWDQ9PC9kczpEaWdlc3RWYWx1ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6UmVmZXJlbmNlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlJlZmVyZW5jZSBUeXBlPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjU2lnbmF0dXJlUHJvcGVydGllcyIgVVJJPSIjeGFkZXNTaWduZWRQcm9wZXJ0aWVzIj4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6RGlnZXN0TWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjc2hhMjU2Ii8+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdFZhbHVlPlpERXlNRFV5T0RKall6azRNR1ZpTlRKaE5tWXpNR0l5WlRneE9EaGtZMkpsT1dFek5tUmlNVEZsWlRWaE1EQXhOams1T1RSa1lUZzNPRGhsWTJaaU13PT08L2RzOkRpZ2VzdFZhbHVlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kczpSZWZlcmVuY2U+CiAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6U2lnbmVkSW5mbz4KICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlNpZ25hdHVyZVZhbHVlPk1FVUNJUUM5MGZGWU9xVGltSHZZUDFmOWJiVDVzdEFmUjhiSTJmQUFGQXpZQXZNQ1BRSWdjR3BHaE1Tb2N4ZndkdmNTVzFCMTUyM2c1bkQ4YkNlOFNDV05lY3Q1cktNPTwvZHM6U2lnbmF0dXJlVmFsdWU+CiAgICAgICAgICAgICAgICAgICAgICAgIDxkczpLZXlJbmZvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlg1MDlEYXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRDZUQ0NBNUNnQXdJQkFnSVRid0FBZjh0ZW02am5ncjE2RHdBQkFBQi95ekFLQmdncWhrak9QUVFEQWpCak1SVXdFd1lLQ1pJbWlaUHlMR1FCR1JZRmJHOWpZV3d4RXpBUkJnb0praWFKay9Jc1pBRVpGZ05uYjNZeEZ6QVZCZ29Ka2lhSmsvSXNaQUVaRmdkbGVIUm5ZWHAwTVJ3d0dnWURWUVFERXhOVVUxcEZTVTVXVDBsRFJTMVRkV0pEUVMweE1CNFhEVEl5TURreE5ERXpNall3TkZvWERUSTBNRGt4TXpFek1qWXdORm93VGpFTE1Ba0dBMVVFQmhNQ1UwRXhFekFSQmdOVkJBb1RDak14TVRFeE1URXhNVEV4RERBS0JnTlZCQXNUQTFSVFZERWNNQm9HQTFVRUF4TVRWRk5VTFRNeE1URXhNVEV4TVRFd01URXhNekJXTUJBR0J5cUdTTTQ5QWdFR0JTdUJCQUFLQTBJQUJHR0RES0RtaFdBSVREdjdMWHFMWDJjbXI2K3FkZFVrcGNMQ3ZXczVyQzJPMjlXL2hTNGFqQUs0UWRuYWh5bTZNYWlqWDc1Q2czajRhYW83b3VZWEo5R2pnZ0k1TUlJQ05UQ0JtZ1lEVlIwUkJJR1NNSUdQcElHTU1JR0pNVHN3T1FZRFZRUUVEREl4TFZSVFZId3lMVlJUVkh3ekxXRTROalppTVRReUxXRmpPV010TkRJME1TMWlaamhsTFRkbU56ZzNZVEkyTW1ObE1qRWZNQjBHQ2dtU0pvbVQ4aXhrQVFFTUR6TXhNVEV4TVRFeE1URXdNVEV4TXpFTk1Bc0dBMVVFREF3RU1URXdNREVNTUFvR0ExVUVHZ3dEVkZOVU1Rd3dDZ1lEVlFRUERBTlVVMVF3SFFZRFZSME9CQllFRkR1V1lsT3pXcEZOM25vMVd0eU5rdFFkckE4Sk1COEdBMVVkSXdRWU1CYUFGSFpnalBzR29LeG5WeldkejVxc3B5dVpOYlV2TUU0R0ExVWRId1JITUVVd1E2QkJvRCtHUFdoMGRIQTZMeTkwYzNSamNtd3VlbUYwWTJFdVoyOTJMbk5oTDBObGNuUkZibkp2Ykd3dlZGTmFSVWxPVms5SlEwVXRVM1ZpUTBFdE1TNWpjbXd3Z2EwR0NDc0dBUVVGQndFQkJJR2dNSUdkTUc0R0NDc0dBUVVGQnpBQmhtSm9kSFJ3T2k4dmRITjBZM0pzTG5waGRHTmhMbWR2ZGk1ellTOURaWEowUlc1eWIyeHNMMVJUV2tWcGJuWnZhV05sVTBOQk1TNWxlSFJuWVhwMExtZHZkaTVzYjJOaGJGOVVVMXBGU1U1V1QwbERSUzFUZFdKRFFTMHhLREVwTG1OeWREQXJCZ2dyQmdFRkJRY3dBWVlmYUhSMGNEb3ZMM1J6ZEdOeWJDNTZZWFJqWVM1bmIzWXVjMkV2YjJOemNEQU9CZ05WSFE4QkFmOEVCQU1DQjRBd0hRWURWUjBsQkJZd0ZBWUlLd1lCQlFVSEF3SUdDQ3NHQVFVRkJ3TURNQ2NHQ1NzR0FRUUJnamNWQ2dRYU1CZ3dDZ1lJS3dZQkJRVUhBd0l3Q2dZSUt3WUJCUVVIQXdNd0NnWUlLb1pJemowRUF3SURSd0F3UkFJZ09nak5QSlcwMTdsc0lpam1WUVZrUDdHekZPMktRS2Q5R0hhdWtMZ0lXRnNDSUZKRjl1d0toVE14RGpXYk4rMWF3c25GSTdSTEJSeEEvNmhaK0Yxd3RhcVU8L2RzOlg1MDlDZXJ0aWZpY2F0ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6WDUwOURhdGE+CiAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6S2V5SW5mbz4KICAgICAgICAgICAgICAgICAgICAgICAgPGRzOk9iamVjdD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpRdWFsaWZ5aW5nUHJvcGVydGllcyB4bWxuczp4YWRlcz0iaHR0cDovL3VyaS5ldHNpLm9yZy8wMTkwMy92MS4zLjIjIiBUYXJnZXQ9InNpZ25hdHVyZSI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHhhZGVzOlNpZ25lZFByb3BlcnRpZXMgSWQ9InhhZGVzU2lnbmVkUHJvcGVydGllcyI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpTaWduZWRTaWduYXR1cmVQcm9wZXJ0aWVzPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHhhZGVzOlNpZ25pbmdUaW1lPjIwMjMtMDEtMTFUMTM6MDg6MTBaPC94YWRlczpTaWduaW5nVGltZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpTaWduaW5nQ2VydGlmaWNhdGU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHhhZGVzOkNlcnQ+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpDZXJ0RGlnZXN0PgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTI1NiIvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdFZhbHVlPllUSmtNMkpoWVRjd1pUQmhaVEF4T0dZd09ETXlOelkzTlRka1pETTNZemhqWTJJeE9USXlaRFpoTTJSbFpHSmlNR1kwTkRVelpXSmhZV0k0TURobVlnPT08L2RzOkRpZ2VzdFZhbHVlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOkNlcnREaWdlc3Q+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpJc3N1ZXJTZXJpYWw+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WDUwOUlzc3Vlck5hbWU+Q049VFNaRUlOVk9JQ0UtU3ViQ0EtMSwgREM9ZXh0Z2F6dCwgREM9Z292LCBEQz1sb2NhbDwvZHM6WDUwOUlzc3Vlck5hbWU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WDUwOVNlcmlhbE51bWJlcj4yNDc1MzgyODg2OTA0ODA5Nzc0ODE4NjQ0NDgwODIwOTM2MDUwMjA4NzAyNDExPC9kczpYNTA5U2VyaWFsTnVtYmVyPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOklzc3VlclNlcmlhbD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOkNlcnQ+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOlNpZ25pbmdDZXJ0aWZpY2F0ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC94YWRlczpTaWduZWRTaWduYXR1cmVQcm9wZXJ0aWVzPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwveGFkZXM6U2lnbmVkUHJvcGVydGllcz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwveGFkZXM6UXVhbGlmeWluZ1Byb3BlcnRpZXM+CiAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6T2JqZWN0PgogICAgICAgICAgICAgICAgICAgIDwvZHM6U2lnbmF0dXJlPgogICAgICAgICAgICAgICAgPC9zYWM6U2lnbmF0dXJlSW5mb3JtYXRpb24+CiAgICAgICAgICAgIDwvc2lnOlVCTERvY3VtZW50U2lnbmF0dXJlcz4KICAgICAgICA8L2V4dDpFeHRlbnNpb25Db250ZW50PgogICAgPC9leHQ6VUJMRXh0ZW5zaW9uPgo8L2V4dDpVQkxFeHRlbnNpb25zPgogICAgCiAgICA8Y2JjOlByb2ZpbGVJRD5yZXBvcnRpbmc6MS4wPC9jYmM6UHJvZmlsZUlEPgogICAgPGNiYzpJRD5TTUUwMDA2MjwvY2JjOklEPgogICAgPGNiYzpVVUlEPjE2ZTc4NDY5LTY0YWYtNDA2ZC05Y2ZkLTg5NWU3MjQxOThmMDwvY2JjOlVVSUQ+CiAgICA8Y2JjOklzc3VlRGF0ZT4yMDIyLTAzLTEzPC9jYmM6SXNzdWVEYXRlPgogICAgPGNiYzpJc3N1ZVRpbWU+MTQ6NDA6NDA8L2NiYzpJc3N1ZVRpbWU+CiAgICA8Y2JjOkludm9pY2VUeXBlQ29kZSBuYW1lPSIwMTExMDEwIj4zODg8L2NiYzpJbnZvaWNlVHlwZUNvZGU+CiAgICA8Y2JjOkRvY3VtZW50Q3VycmVuY3lDb2RlPlNBUjwvY2JjOkRvY3VtZW50Q3VycmVuY3lDb2RlPgogICAgPGNiYzpUYXhDdXJyZW5jeUNvZGU+U0FSPC9jYmM6VGF4Q3VycmVuY3lDb2RlPgogICAgPGNhYzpBZGRpdGlvbmFsRG9jdW1lbnRSZWZlcmVuY2U+CiAgICAgICAgPGNiYzpJRD5JQ1Y8L2NiYzpJRD4KICAgICAgICA8Y2JjOlVVSUQ+NjI8L2NiYzpVVUlEPgogICAgPC9jYWM6QWRkaXRpb25hbERvY3VtZW50UmVmZXJlbmNlPgogICAgPGNhYzpBZGRpdGlvbmFsRG9jdW1lbnRSZWZlcmVuY2U+CiAgICAgICAgPGNiYzpJRD5QSUg8L2NiYzpJRD4KICAgICAgICA8Y2FjOkF0dGFjaG1lbnQ+CiAgICAgICAgICAgIDxjYmM6RW1iZWRkZWREb2N1bWVudEJpbmFyeU9iamVjdCBtaW1lQ29kZT0idGV4dC9wbGFpbiI+TldabFkyVmlOalptWm1NNE5tWXpPR1E1TlRJM09EWmpObVEyT1Raak56bGpNbVJpWXpJek9XUmtOR1U1TVdJME5qY3lPV1EzTTJFeU4yWmlOVGRsT1E9PTwvY2JjOkVtYmVkZGVkRG9jdW1lbnRCaW5hcnlPYmplY3Q+CiAgICAgICAgPC9jYWM6QXR0YWNobWVudD4KICAgIDwvY2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZT4KICAgIAogICAgPGNhYzpBZGRpdGlvbmFsRG9jdW1lbnRSZWZlcmVuY2U+CiAgICAgICAgPGNiYzpJRD5RUjwvY2JjOklEPgogICAgICAgIDxjYWM6QXR0YWNobWVudD4KICAgICAgICAgICAgPGNiYzpFbWJlZGRlZERvY3VtZW50QmluYXJ5T2JqZWN0IG1pbWVDb2RlPSJ0ZXh0L3BsYWluIj5BUmRCYUcxbFpDQk5iMmhoYldWa0lFRk1JRUZvYldGa2VRSVBNekF3TURjMU5UZzROekF3TURBekF4UXlNREl5TFRBekxURXpWREUwT2pRd09qUXdXZ1FITVRFeE1DNDVNQVVGTVRRMExqa0dMRkJGZURoaVRrWmpSVTFGY0VoNlZWWjJVVzUwVVVrMmIzUTRaVVp4VkZRdmJEVTVZaXRJTVVoeFdEUTlCMkJOUlZWRFNWRkRPVEJtUmxsUGNWUnBiVWgyV1ZBeFpqbGlZbFExYzNSQlpsSTRZa2t5WmtGQlJrRjZXVUYyVFVOUVVVbG5ZMGR3UjJoTlUyOWplR1ozWkhaalUxY3hRakUxTWpObk5XNUVPR0pEWlRoVFExZE9aV04wTlhKTFRUMElXREJXTUJBR0J5cUdTTTQ5QWdFR0JTdUJCQUFLQTBJQUJHR0RES0RtaFdBSVREdjdMWHFMWDJjbXI2K3FkZFVrcGNMQ3ZXczVyQzJPMjlXL2hTNGFqQUs0UWRuYWh5bTZNYWlqWDc1Q2czajRhYW83b3VZWEo5RT08L2NiYzpFbWJlZGRlZERvY3VtZW50QmluYXJ5T2JqZWN0PgogICAgICAgIDwvY2FjOkF0dGFjaG1lbnQ+CjwvY2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZT48Y2FjOlNpZ25hdHVyZT4KICAgICAgPGNiYzpJRD51cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2lnbmF0dXJlOkludm9pY2U8L2NiYzpJRD4KICAgICAgPGNiYzpTaWduYXR1cmVNZXRob2Q+dXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOmRzaWc6ZW52ZWxvcGVkOnhhZGVzPC9jYmM6U2lnbmF0dXJlTWV0aG9kPgo8L2NhYzpTaWduYXR1cmU+PGNhYzpBY2NvdW50aW5nU3VwcGxpZXJQYXJ0eT4KICAgICAgICA8Y2FjOlBhcnR5PgogICAgICAgICAgICA8Y2FjOlBhcnR5SWRlbnRpZmljYXRpb24+CiAgICAgICAgICAgICAgICA8Y2JjOklEIHNjaGVtZUlEPSJDUk4iPjQ1NDYzNDY0NTY0NTY1NDwvY2JjOklEPgogICAgICAgICAgICA8L2NhYzpQYXJ0eUlkZW50aWZpY2F0aW9uPgogICAgICAgICAgICA8Y2FjOlBvc3RhbEFkZHJlc3M+CiAgICAgICAgICAgICAgICA8Y2JjOlN0cmVldE5hbWU+dGVzdDwvY2JjOlN0cmVldE5hbWU+CiAgICAgICAgICAgICAgICA8Y2JjOkJ1aWxkaW5nTnVtYmVyPjM0NTQ8L2NiYzpCdWlsZGluZ051bWJlcj4KICAgICAgICAgICAgICAgIDxjYmM6UGxvdElkZW50aWZpY2F0aW9uPjEyMzQ8L2NiYzpQbG90SWRlbnRpZmljYXRpb24+CiAgICAgICAgICAgICAgICA8Y2JjOkNpdHlTdWJkaXZpc2lvbk5hbWU+dGVzdDwvY2JjOkNpdHlTdWJkaXZpc2lvbk5hbWU+CiAgICAgICAgICAgICAgICA8Y2JjOkNpdHlOYW1lPlJpeWFkaDwvY2JjOkNpdHlOYW1lPgogICAgICAgICAgICAgICAgPGNiYzpQb3N0YWxab25lPjEyMzQ1PC9jYmM6UG9zdGFsWm9uZT4KICAgICAgICAgICAgICAgIDxjYmM6Q291bnRyeVN1YmVudGl0eT50ZXN0PC9jYmM6Q291bnRyeVN1YmVudGl0eT4KICAgICAgICAgICAgICAgIDxjYWM6Q291bnRyeT4KICAgICAgICAgICAgICAgICAgICA8Y2JjOklkZW50aWZpY2F0aW9uQ29kZT5TQTwvY2JjOklkZW50aWZpY2F0aW9uQ29kZT4KICAgICAgICAgICAgICAgIDwvY2FjOkNvdW50cnk+CiAgICAgICAgICAgIDwvY2FjOlBvc3RhbEFkZHJlc3M+CiAgICAgICAgICAgIDxjYWM6UGFydHlUYXhTY2hlbWU+CiAgICAgICAgICAgICAgICA8Y2JjOkNvbXBhbnlJRD4zMDAwNzU1ODg3MDAwMDM8L2NiYzpDb21wYW55SUQ+CiAgICAgICAgICAgICAgICA8Y2FjOlRheFNjaGVtZT4KICAgICAgICAgICAgICAgICAgICA8Y2JjOklEPlZBVDwvY2JjOklEPgogICAgICAgICAgICAgICAgPC9jYWM6VGF4U2NoZW1lPgogICAgICAgICAgICA8L2NhYzpQYXJ0eVRheFNjaGVtZT4KICAgICAgICAgICAgPGNhYzpQYXJ0eUxlZ2FsRW50aXR5PgogICAgICAgICAgICAgICAgPGNiYzpSZWdpc3RyYXRpb25OYW1lPkFobWVkIE1vaGFtZWQgQUwgQWhtYWR5PC9jYmM6UmVnaXN0cmF0aW9uTmFtZT4KICAgICAgICAgICAgPC9jYWM6UGFydHlMZWdhbEVudGl0eT4KICAgICAgICA8L2NhYzpQYXJ0eT4KICAgIDwvY2FjOkFjY291bnRpbmdTdXBwbGllclBhcnR5PgogICAgPGNhYzpBY2NvdW50aW5nQ3VzdG9tZXJQYXJ0eT4KICAgICAgICA8Y2FjOlBhcnR5PgogICAgICAgICAgICA8Y2FjOlBhcnR5SWRlbnRpZmljYXRpb24+CiAgICAgICAgICAgICAgICA8Y2JjOklEIHNjaGVtZUlEPSJOQVQiPjIzNDU8L2NiYzpJRD4KICAgICAgICAgICAgPC9jYWM6UGFydHlJZGVudGlmaWNhdGlvbj4KICAgICAgICAgICAgPGNhYzpQb3N0YWxBZGRyZXNzPgogICAgICAgICAgICAgICAgPGNiYzpTdHJlZXROYW1lPmJhYW91bjwvY2JjOlN0cmVldE5hbWU+CiAgICAgICAgICAgICAgICA8Y2JjOkFkZGl0aW9uYWxTdHJlZXROYW1lPnNkc2Q8L2NiYzpBZGRpdGlvbmFsU3RyZWV0TmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6QnVpbGRpbmdOdW1iZXI+MzM1MzwvY2JjOkJ1aWxkaW5nTnVtYmVyPgogICAgICAgICAgICAgICAgPGNiYzpQbG90SWRlbnRpZmljYXRpb24+MzQzNDwvY2JjOlBsb3RJZGVudGlmaWNhdGlvbj4KICAgICAgICAgICAgICAgIDxjYmM6Q2l0eVN1YmRpdmlzaW9uTmFtZT5mZ2ZmPC9jYmM6Q2l0eVN1YmRpdmlzaW9uTmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6Q2l0eU5hbWU+RGh1cm1hPC9jYmM6Q2l0eU5hbWU+CiAgICAgICAgICAgICAgICA8Y2JjOlBvc3RhbFpvbmU+MzQ1MzQ8L2NiYzpQb3N0YWxab25lPgogICAgICAgICAgICAgICAgPGNiYzpDb3VudHJ5U3ViZW50aXR5PnVsaGs8L2NiYzpDb3VudHJ5U3ViZW50aXR5PgogICAgICAgICAgICAgICAgPGNhYzpDb3VudHJ5PgogICAgICAgICAgICAgICAgICAgIDxjYmM6SWRlbnRpZmljYXRpb25Db2RlPlNBPC9jYmM6SWRlbnRpZmljYXRpb25Db2RlPgogICAgICAgICAgICAgICAgPC9jYWM6Q291bnRyeT4KICAgICAgICAgICAgPC9jYWM6UG9zdGFsQWRkcmVzcz4KICAgICAgICAgICAgPGNhYzpQYXJ0eVRheFNjaGVtZT4KICAgICAgICAgICAgICAgIDxjYWM6VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgICAgIDxjYmM6SUQ+VkFUPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8L2NhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgIDwvY2FjOlBhcnR5VGF4U2NoZW1lPgogICAgICAgICAgICA8Y2FjOlBhcnR5TGVnYWxFbnRpdHk+CiAgICAgICAgICAgICAgICA8Y2JjOlJlZ2lzdHJhdGlvbk5hbWU+c2RzYTwvY2JjOlJlZ2lzdHJhdGlvbk5hbWU+CiAgICAgICAgICAgIDwvY2FjOlBhcnR5TGVnYWxFbnRpdHk+CiAgICAgICAgPC9jYWM6UGFydHk+CiAgICA8L2NhYzpBY2NvdW50aW5nQ3VzdG9tZXJQYXJ0eT4KICAgIDxjYWM6RGVsaXZlcnk+CiAgICAgICAgPGNiYzpBY3R1YWxEZWxpdmVyeURhdGU+MjAyMi0wMy0xMzwvY2JjOkFjdHVhbERlbGl2ZXJ5RGF0ZT4KICAgICAgICA8Y2JjOkxhdGVzdERlbGl2ZXJ5RGF0ZT4yMDIyLTAzLTE1PC9jYmM6TGF0ZXN0RGVsaXZlcnlEYXRlPgogICAgPC9jYWM6RGVsaXZlcnk+CiAgICA8Y2FjOlBheW1lbnRNZWFucz4KICAgICAgICA8Y2JjOlBheW1lbnRNZWFuc0NvZGU+MTA8L2NiYzpQYXltZW50TWVhbnNDb2RlPgogICAgPC9jYWM6UGF5bWVudE1lYW5zPgogICAgPGNhYzpBbGxvd2FuY2VDaGFyZ2U+CiAgICAgICAgPGNiYzpJRD4xPC9jYmM6SUQ+CiAgICAgICAgPGNiYzpDaGFyZ2VJbmRpY2F0b3I+ZmFsc2U8L2NiYzpDaGFyZ2VJbmRpY2F0b3I+CiAgICAgICAgPGNiYzpBbGxvd2FuY2VDaGFyZ2VSZWFzb24+ZGlzY291bnQ8L2NiYzpBbGxvd2FuY2VDaGFyZ2VSZWFzb24+CiAgICAgICAgPGNiYzpBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4yPC9jYmM6QW1vdW50PgogICAgICAgIDxjYWM6VGF4Q2F0ZWdvcnk+CiAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lQWdlbmN5SUQ9IjYiIHNjaGVtZUlEPSJVTi9FQ0UgNTMwNSI+UzwvY2JjOklEPgogICAgICAgICAgICA8Y2JjOlBlcmNlbnQ+MTU8L2NiYzpQZXJjZW50PgogICAgICAgICAgICA8Y2FjOlRheFNjaGVtZT4KICAgICAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lQWdlbmN5SUQ9IjYiIHNjaGVtZUlEPSJVTi9FQ0UgNTE1MyI+VkFUPC9jYmM6SUQ+CiAgICAgICAgICAgIDwvY2FjOlRheFNjaGVtZT4KICAgICAgICA8L2NhYzpUYXhDYXRlZ29yeT4KICAgIDwvY2FjOkFsbG93YW5jZUNoYXJnZT4KICAgIDxjYWM6VGF4VG90YWw+CiAgICAgICAgPGNiYzpUYXhBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4xNDQuOTwvY2JjOlRheEFtb3VudD4KICAgICAgICA8Y2FjOlRheFN1YnRvdGFsPgogICAgICAgICAgICA8Y2JjOlRheGFibGVBbW91bnQgY3VycmVuY3lJRD0iU0FSIj45NjYuMDA8L2NiYzpUYXhhYmxlQW1vdW50PgogICAgICAgICAgICA8Y2JjOlRheEFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjE0NC45MDwvY2JjOlRheEFtb3VudD4KICAgICAgICAgICAgPGNhYzpUYXhDYXRlZ29yeT4KICAgICAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lQWdlbmN5SUQ9IjYiIHNjaGVtZUlEPSJVTi9FQ0UgNTMwNSI+UzwvY2JjOklEPgogICAgICAgICAgICAgICAgPGNiYzpQZXJjZW50PjE1LjAwPC9jYmM6UGVyY2VudD4KICAgICAgICAgICAgICAgIDxjYWM6VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lQWdlbmN5SUQ9IjYiIHNjaGVtZUlEPSJVTi9FQ0UgNTE1MyI+VkFUPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8L2NhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgIDwvY2FjOlRheENhdGVnb3J5PgogICAgICAgIDwvY2FjOlRheFN1YnRvdGFsPgogICAgPC9jYWM6VGF4VG90YWw+CiAgICA8Y2FjOlRheFRvdGFsPgogICAgICAgIDxjYmM6VGF4QW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTQ0Ljk8L2NiYzpUYXhBbW91bnQ+CgogICAgPC9jYWM6VGF4VG90YWw+CiAgICA8Y2FjOkxlZ2FsTW9uZXRhcnlUb3RhbD4KICAgICAgICA8Y2JjOkxpbmVFeHRlbnNpb25BbW91bnQgY3VycmVuY3lJRD0iU0FSIj45NjguMDA8L2NiYzpMaW5lRXh0ZW5zaW9uQW1vdW50PgogICAgICAgIDxjYmM6VGF4RXhjbHVzaXZlQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+OTY2LjAwPC9jYmM6VGF4RXhjbHVzaXZlQW1vdW50PgogICAgICAgIDxjYmM6VGF4SW5jbHVzaXZlQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTExMC45MDwvY2JjOlRheEluY2x1c2l2ZUFtb3VudD4KICAgICAgICA8Y2JjOkFsbG93YW5jZVRvdGFsQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+Mi4wMDwvY2JjOkFsbG93YW5jZVRvdGFsQW1vdW50PgogICAgICAgIDxjYmM6UHJlcGFpZEFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjAuMDA8L2NiYzpQcmVwYWlkQW1vdW50PgogICAgICAgIDxjYmM6UGF5YWJsZUFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjExMTAuOTA8L2NiYzpQYXlhYmxlQW1vdW50PgogICAgPC9jYWM6TGVnYWxNb25ldGFyeVRvdGFsPgogICAgPGNhYzpJbnZvaWNlTGluZT4KICAgICAgICA8Y2JjOklEPjE8L2NiYzpJRD4KICAgICAgICA8Y2JjOkludm9pY2VkUXVhbnRpdHkgdW5pdENvZGU9IlBDRSI+NDQuMDAwMDAwPC9jYmM6SW52b2ljZWRRdWFudGl0eT4KICAgICAgICA8Y2JjOkxpbmVFeHRlbnNpb25BbW91bnQgY3VycmVuY3lJRD0iU0FSIj45NjguMDA8L2NiYzpMaW5lRXh0ZW5zaW9uQW1vdW50PgogICAgICAgIDxjYWM6VGF4VG90YWw+CiAgICAgICAgICAgIDxjYmM6VGF4QW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTQ1LjIwPC9jYmM6VGF4QW1vdW50PgogICAgICAgICAgICA8Y2JjOlJvdW5kaW5nQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTExMy4yMDwvY2JjOlJvdW5kaW5nQW1vdW50PgoKICAgICAgICA8L2NhYzpUYXhUb3RhbD4KICAgICAgICA8Y2FjOkl0ZW0+CiAgICAgICAgICAgIDxjYmM6TmFtZT5kc2Q8L2NiYzpOYW1lPgogICAgICAgICAgICA8Y2FjOkNsYXNzaWZpZWRUYXhDYXRlZ29yeT4KICAgICAgICAgICAgICAgIDxjYmM6SUQ+UzwvY2JjOklEPgogICAgICAgICAgICAgICAgPGNiYzpQZXJjZW50PjE1LjAwPC9jYmM6UGVyY2VudD4KICAgICAgICAgICAgICAgIDxjYWM6VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgICAgIDxjYmM6SUQ+VkFUPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8L2NhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgIDwvY2FjOkNsYXNzaWZpZWRUYXhDYXRlZ29yeT4KICAgICAgICA8L2NhYzpJdGVtPgogICAgICAgIDxjYWM6UHJpY2U+CiAgICAgICAgICAgIDxjYmM6UHJpY2VBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4yMi4wMDwvY2JjOlByaWNlQW1vdW50PgogICAgICAgICAgICA8Y2FjOkFsbG93YW5jZUNoYXJnZT4KICAgICAgICAgICAgICAgIDxjYmM6Q2hhcmdlSW5kaWNhdG9yPmZhbHNlPC9jYmM6Q2hhcmdlSW5kaWNhdG9yPgogICAgICAgICAgICAgICAgPGNiYzpBbGxvd2FuY2VDaGFyZ2VSZWFzb24+ZGlzY291bnQ8L2NiYzpBbGxvd2FuY2VDaGFyZ2VSZWFzb24+CiAgICAgICAgICAgICAgICA8Y2JjOkFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjIuMDA8L2NiYzpBbW91bnQ+CiAgICAgICAgICAgIDwvY2FjOkFsbG93YW5jZUNoYXJnZT4KICAgICAgICA8L2NhYzpQcmljZT4KICAgIDwvY2FjOkludm9pY2VMaW5lPgo8L0ludm9pY2U+";
        $error_invoice = "CiAgICAgICAgPGV4dDpFeHRlbnNpb25VUkk+dXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOmRzaWc6ZW52ZWxvcGVkOnhhZGVzPC9leHQ6RXh0ZW5zaW9uVVJJPgogICAgICAgIDxleHQ6RXh0ZW5zaW9uQ29udGVudD4KICAgICAgICAgICAgPCEtLSBQbGVhc2Ugbm90ZSB0aGF0IHRoZSBzaWduYXR1cmUgdmFsdWVzIGFyZSBzYW1wbGUgdmFsdWVzIG9ubHkgLS0+CiAgICAgICAgICAgIDxzaWc6VUJMRG9jdW1lbnRTaWduYXR1cmVzIHhtbG5zOnNpZz0idXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNjaGVtYTp4c2Q6Q29tbW9uU2lnbmF0dXJlQ29tcG9uZW50cy0yIiB4bWxuczpzYWM9InVybjpvYXNpczpuYW1lczpzcGVjaWZpY2F0aW9uOnVibDpzY2hlbWE6eHNkOlNpZ25hdHVyZUFnZ3JlZ2F0ZUNvbXBvbmVudHMtMiIgeG1sbnM6c2JjPSJ1cm46b2FzaXM6bmFtZXM6c3BlY2lmaWNhdGlvbjp1Ymw6c2NoZW1hOnhzZDpTaWduYXR1cmVCYXNpY0NvbXBvbmVudHMtMiI+CiAgICAgICAgICAgICAgICA8c2FjOlNpZ25hdHVyZUluZm9ybWF0aW9uPgogICAgICAgICAgICAgICAgICAgIDxjYmM6SUQ+dXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNpZ25hdHVyZToxPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICAgICAgPHNiYzpSZWZlcmVuY2VkU2lnbmF0dXJlSUQ+dXJuOm9hc2lzOm5hbWVzOnNwZWNpZmljYXRpb246dWJsOnNpZ25hdHVyZTpJbnZvaWNlPC9zYmM6UmVmZXJlbmNlZFNpZ25hdHVyZUlEPgogICAgICAgICAgICAgICAgICAgIDxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiIElkPSJzaWduYXR1cmUiPgogICAgICAgICAgICAgICAgICAgICAgICA8ZHM6U2lnbmVkSW5mbz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwNi8xMi94bWwtYzE0bjExIi8+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxkc2lnLW1vcmUjZWNkc2Etc2hhMjU2Ii8+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6UmVmZXJlbmNlIElkPSJpbnZvaWNlU2lnbmVkRGF0YSIgVVJJPSIiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpUcmFuc2Zvcm1zPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvVFIvMTk5OS9SRUMteHBhdGgtMTk5OTExMTYiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlhQYXRoPm5vdCgvL2FuY2VzdG9yLW9yLXNlbGY6OmV4dDpVQkxFeHRlbnNpb25zKTwvZHM6WFBhdGg+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6VHJhbnNmb3JtPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvVFIvMTk5OS9SRUMteHBhdGgtMTk5OTExMTYiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlhQYXRoPm5vdCgvL2FuY2VzdG9yLW9yLXNlbGY6OmNhYzpTaWduYXR1cmUpPC9kczpYUGF0aD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kczpUcmFuc2Zvcm0+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy9UUi8xOTk5L1JFQy14cGF0aC0xOTk5MTExNiI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WFBhdGg+bm90KC8vYW5jZXN0b3Itb3Itc2VsZjo6Y2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZVtjYmM6SUQ9J1FSJ10pPC9kczpYUGF0aD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kczpUcmFuc2Zvcm0+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDA2LzEyL3htbC1jMTRuMTEiLz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlRyYW5zZm9ybXM+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTI1NiIvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpEaWdlc3RWYWx1ZT5QRXg4Yk5GY0VNRXBIelVWdlFudFFJNm90OGVGcVRUL2w1OWIrSDFIcVg0PTwvZHM6RGlnZXN0VmFsdWU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlJlZmVyZW5jZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpSZWZlcmVuY2UgVHlwZT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI1NpZ25hdHVyZVByb3BlcnRpZXMiIFVSST0iI3hhZGVzU2lnbmVkUHJvcGVydGllcyI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTI1NiIvPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpEaWdlc3RWYWx1ZT5aREV5TURVeU9ESmpZems0TUdWaU5USmhObVl6TUdJeVpUZ3hPRGhrWTJKbE9XRXpObVJpTVRGbFpUVmhNREF4TmprNU9UUmtZVGczT0RobFkyWmlNdz09PC9kczpEaWdlc3RWYWx1ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZHM6UmVmZXJlbmNlPgogICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlNpZ25lZEluZm8+CiAgICAgICAgICAgICAgICAgICAgICAgIDxkczpTaWduYXR1cmVWYWx1ZT5NRVVDSVFDOTBmRllPcVRpbUh2WVAxZjliYlQ1c3RBZlI4YkkyZkFBRkF6WUF2TUNQUUlnY0dwR2hNU29jeGZ3ZHZjU1cxQjE1MjNnNW5EOGJDZThTQ1dOZWN0NXJLTT08L2RzOlNpZ25hdHVyZVZhbHVlPgogICAgICAgICAgICAgICAgICAgICAgICA8ZHM6S2V5SW5mbz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpYNTA5RGF0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZHM6WDUwOUNlcnRpZmljYXRlPk1JSUQ2VENDQTVDZ0F3SUJBZ0lUYndBQWY4dGVtNmpuZ3IxNkR3QUJBQUIveXpBS0JnZ3Foa2pPUFFRREFqQmpNUlV3RXdZS0NaSW1pWlB5TEdRQkdSWUZiRzlqWVd3eEV6QVJCZ29Ka2lhSmsvSXNaQUVaRmdObmIzWXhGekFWQmdvSmtpYUprL0lzWkFFWkZnZGxlSFJuWVhwME1Sd3dHZ1lEVlFRREV4TlVVMXBGU1U1V1QwbERSUzFUZFdKRFFTMHhNQjRYRFRJeU1Ea3hOREV6TWpZd05Gb1hEVEkwTURreE16RXpNall3TkZvd1RqRUxNQWtHQTFVRUJoTUNVMEV4RXpBUkJnTlZCQW9UQ2pNeE1URXhNVEV4TVRFeEREQUtCZ05WQkFzVEExUlRWREVjTUJvR0ExVUVBeE1UVkZOVUxUTXhNVEV4TVRFeE1URXdNVEV4TXpCV01CQUdCeXFHU000OUFnRUdCU3VCQkFBS0EwSUFCR0dEREtEbWhXQUlURHY3TFhxTFgyY21yNitxZGRVa3BjTEN2V3M1ckMyTzI5Vy9oUzRhakFLNFFkbmFoeW02TWFpalg3NUNnM2o0YWFvN291WVhKOUdqZ2dJNU1JSUNOVENCbWdZRFZSMFJCSUdTTUlHUHBJR01NSUdKTVRzd09RWURWUVFFRERJeExWUlRWSHd5TFZSVFZId3pMV0U0TmpaaU1UUXlMV0ZqT1dNdE5ESTBNUzFpWmpobExUZG1OemczWVRJMk1tTmxNakVmTUIwR0NnbVNKb21UOGl4a0FRRU1Eek14TVRFeE1URXhNVEV3TVRFeE16RU5NQXNHQTFVRURBd0VNVEV3TURFTU1Bb0dBMVVFR2d3RFZGTlVNUXd3Q2dZRFZRUVBEQU5VVTFRd0hRWURWUjBPQkJZRUZEdVdZbE96V3BGTjNubzFXdHlOa3RRZHJBOEpNQjhHQTFVZEl3UVlNQmFBRkhaZ2pQc0dvS3huVnpXZHo1cXNweXVaTmJVdk1FNEdBMVVkSHdSSE1FVXdRNkJCb0QrR1BXaDBkSEE2THk5MGMzUmpjbXd1ZW1GMFkyRXVaMjkyTG5OaEwwTmxjblJGYm5KdmJHd3ZWRk5hUlVsT1ZrOUpRMFV0VTNWaVEwRXRNUzVqY213d2dhMEdDQ3NHQVFVRkJ3RUJCSUdnTUlHZE1HNEdDQ3NHQVFVRkJ6QUJobUpvZEhSd09pOHZkSE4wWTNKc0xucGhkR05oTG1kdmRpNXpZUzlEWlhKMFJXNXliMnhzTDFSVFdrVnBiblp2YVdObFUwTkJNUzVsZUhSbllYcDBMbWR2ZGk1c2IyTmhiRjlVVTFwRlNVNVdUMGxEUlMxVGRXSkRRUzB4S0RFcExtTnlkREFyQmdnckJnRUZCUWN3QVlZZmFIUjBjRG92TDNSemRHTnliQzU2WVhSallTNW5iM1l1YzJFdmIyTnpjREFPQmdOVkhROEJBZjhFQkFNQ0I0QXdIUVlEVlIwbEJCWXdGQVlJS3dZQkJRVUhBd0lHQ0NzR0FRVUZCd01ETUNjR0NTc0dBUVFCZ2pjVkNnUWFNQmd3Q2dZSUt3WUJCUVVIQXdJd0NnWUlLd1lCQlFVSEF3TXdDZ1lJS29aSXpqMEVBd0lEUndBd1JBSWdPZ2pOUEpXMDE3bHNJaWptVlFWa1A3R3pGTzJLUUtkOUdIYXVrTGdJV0ZzQ0lGSkY5dXdLaFRNeERqV2JOKzFhd3NuRkk3UkxCUnhBLzZoWitGMXd0YXFVPC9kczpYNTA5Q2VydGlmaWNhdGU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2RzOlg1MDlEYXRhPgogICAgICAgICAgICAgICAgICAgICAgICA8L2RzOktleUluZm8+CiAgICAgICAgICAgICAgICAgICAgICAgIDxkczpPYmplY3Q+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8eGFkZXM6UXVhbGlmeWluZ1Byb3BlcnRpZXMgeG1sbnM6eGFkZXM9Imh0dHA6Ly91cmkuZXRzaS5vcmcvMDE5MDMvdjEuMy4yIyIgVGFyZ2V0PSJzaWduYXR1cmUiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpTaWduZWRQcm9wZXJ0aWVzIElkPSJ4YWRlc1NpZ25lZFByb3BlcnRpZXMiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8eGFkZXM6U2lnbmVkU2lnbmF0dXJlUHJvcGVydGllcz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpTaWduaW5nVGltZT4yMDIzLTAxLTExVDEzOjA4OjEwWjwveGFkZXM6U2lnbmluZ1RpbWU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8eGFkZXM6U2lnbmluZ0NlcnRpZmljYXRlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx4YWRlczpDZXJ0PgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8eGFkZXM6Q2VydERpZ2VzdD4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNzaGEyNTYiLz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkczpEaWdlc3RWYWx1ZT5ZVEprTTJKaFlUY3daVEJoWlRBeE9HWXdPRE15TnpZM05UZGtaRE0zWXpoalkySXhPVEl5WkRaaE0yUmxaR0ppTUdZME5EVXpaV0poWVdJNE1EaG1ZZz09PC9kczpEaWdlc3RWYWx1ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC94YWRlczpDZXJ0RGlnZXN0PgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8eGFkZXM6SXNzdWVyU2VyaWFsPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlg1MDlJc3N1ZXJOYW1lPkNOPVRTWkVJTlZPSUNFLVN1YkNBLTEsIERDPWV4dGdhenQsIERDPWdvdiwgREM9bG9jYWw8L2RzOlg1MDlJc3N1ZXJOYW1lPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRzOlg1MDlTZXJpYWxOdW1iZXI+MjQ3NTM4Mjg4NjkwNDgwOTc3NDgxODY0NDQ4MDgyMDkzNjA1MDIwODcwMjQxMTwvZHM6WDUwOVNlcmlhbE51bWJlcj4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC94YWRlczpJc3N1ZXJTZXJpYWw+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC94YWRlczpDZXJ0PgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC94YWRlczpTaWduaW5nQ2VydGlmaWNhdGU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwveGFkZXM6U2lnbmVkU2lnbmF0dXJlUHJvcGVydGllcz4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOlNpZ25lZFByb3BlcnRpZXM+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3hhZGVzOlF1YWxpZnlpbmdQcm9wZXJ0aWVzPgogICAgICAgICAgICAgICAgICAgICAgICA8L2RzOk9iamVjdD4KICAgICAgICAgICAgICAgICAgICA8L2RzOlNpZ25hdHVyZT4KICAgICAgICAgICAgICAgIDwvc2FjOlNpZ25hdHVyZUluZm9ybWF0aW9uPgogICAgICAgICAgICA8L3NpZzpVQkxEb2N1bWVudFNpZ25hdHVyZXM+CiAgICAgICAgPC9leHQ6RXh0ZW5zaW9uQ29udGVudD4KICAgIDwvZXh0OlVCTEV4dGVuc2lvbj4KPC9leHQ6VUJMRXh0ZW5zaW9ucz4KCiAgICA8Y2JjOlByb2ZpbGVJRD5yZXBvcnRpbmc6MS4wPC9jYmM6UHJvZmlsZUlEPgogICAgPGNiYzpJRD5TTUUwMDA2MjwvY2JjOklEPgogICAgPGNiYzpVVUlEPjE2ZTc4NDY5LTY0YWYtNDA2ZC05Y2ZkLTg5NWU3MjQxOThmMDwvY2JjOlVVSUQ+CiAgICA8Y2JjOklzc3VlRGF0ZT4yMDIyLTAzLTEzPC9jYmM6SXNzdWVEYXRlPgogICAgPGNiYzpJc3N1ZVRpbWU+MTQ6NDA6NDA8L2NiYzpJc3N1ZVRpbWU+CiAgICA8Y2JjOkludm9pY2VUeXBlQ29kZSBuYW1lPSIwMTExMDEwIj4zODg8L2NiYzpJbnZvaWNlVHlwZUNvZGU+CiAgICA8Y2JjOkRvY3VtZW50Q3VycmVuY3lDb2RlPlNBUjwvY2JjOkRvY3VtZW50Q3VycmVuY3lDb2RlPgogICAgPGNiYzpUYXhDdXJyZW5jeUNvZGU+U0FSPC9jYmM6VGF4Q3VycmVuY3lDb2RlPgogICAgPGNhYzpBZGRpdGlvbmFsRG9jdW1lbnRSZWZlcmVuY2U+CiAgICAgICAgPGNiYzpJRD5JQ1Y8L2NiYzpJRD4KICAgICAgICA8Y2JjOlVVSUQ+NjI8L2NiYzpVVUlEPgogICAgPC9jYWM6QWRkaXRpb25hbERvY3VtZW50UmVmZXJlbmNlPgogICAgPGNhYzpBZGRpdGlvbmFsRG9jdW1lbnRSZWZlcmVuY2U+CiAgICAgICAgPGNiYzpJRD5QSUg8L2NiYzpJRD4KICAgICAgICA8Y2FjOkF0dGFjaG1lbnQ+CiAgICAgICAgICAgIDxjYmM6RW1iZWRkZWREb2N1bWVudEJpbmFyeU9iamVjdCBtaW1lQ29kZT0idGV4dC9wbGFpbiI+TldabFkyVmlOalptWm1NNE5tWXpPR1E1TlRJM09EWmpObVEyT1Raak56bGpNbVJpWXpJek9XUmtOR1U1TVdJME5qY3lPV1EzTTJFeU4yWmlOVGRsT1E9PTwvY2JjOkVtYmVkZGVkRG9jdW1lbnRCaW5hcnlPYmplY3Q+CiAgICAgICAgPC9jYWM6QXR0YWNobWVudD4KICAgIDwvY2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZT4KCiAgICA8Y2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZT4KICAgICAgICA8Y2JjOklEPlFSPC9jYmM6SUQ+CiAgICAgICAgPGNhYzpBdHRhY2htZW50PgogICAgICAgICAgICA8Y2JjOkVtYmVkZGVkRG9jdW1lbnRCaW5hcnlPYmplY3QgbWltZUNvZGU9InRleHQvcGxhaW4iPkFSZEJhRzFsWkNCTmIyaGhiV1ZrSUVGTUlFRm9iV0ZrZVFJUE16QXdNRGMxTlRnNE56QXdNREF6QXhReU1ESXlMVEF6TFRFelZERTBPalF3T2pRd1dnUUhNVEV4TUM0NU1BVUZNVFEwTGprR0xGQkZlRGhpVGtaalJVMUZjRWg2VlZaMlVXNTBVVWsyYjNRNFpVWnhWRlF2YkRVNVlpdElNVWh4V0RROUIyQk5SVlZEU1ZGRE9UQm1SbGxQY1ZScGJVaDJXVkF4WmpsaVlsUTFjM1JCWmxJNFlra3laa0ZCUmtGNldVRjJUVU5RVVVsblkwZHdSMmhOVTI5amVHWjNaSFpqVTFjeFFqRTFNak5uTlc1RU9HSkRaVGhUUTFkT1pXTjBOWEpMVFQwSVdEQldNQkFHQnlxR1NNNDlBZ0VHQlN1QkJBQUtBMElBQkdHRERLRG1oV0FJVER2N0xYcUxYMmNtcjYrcWRkVWtwY0xDdldzNXJDMk8yOVcvaFM0YWpBSzRRZG5haHltNk1haWpYNzVDZzNqNGFhbzdvdVlYSjlFPTwvY2JjOkVtYmVkZGVkRG9jdW1lbnRCaW5hcnlPYmplY3Q+CiAgICAgICAgPC9jYWM6QXR0YWNobWVudD4KICAgIDwvY2FjOkFkZGl0aW9uYWxEb2N1bWVudFJlZmVyZW5jZT48Y2FjOlNpZ25hdHVyZT4KICAgICAgICA8Y2JjOklEPnVybjpvYXNpczpuYW1lczpzcGVjaWZpY2F0aW9uOnVibDpzaWduYXR1cmU6SW52b2ljZTwvY2JjOklEPgogICAgICAgIDxjYmM6U2lnbmF0dXJlTWV0aG9kPnVybjpvYXNpczpuYW1lczpzcGVjaWZpY2F0aW9uOnVibDpkc2lnOmVudmVsb3BlZDp4YWRlczwvY2JjOlNpZ25hdHVyZU1ldGhvZD4KICAgIDwvY2FjOlNpZ25hdHVyZT48Y2FjOkFjY291bnRpbmdTdXBwbGllclBhcnR5PgogICAgICAgIDxjYWM6UGFydHk+CiAgICAgICAgICAgIDxjYWM6UGFydHlJZGVudGlmaWNhdGlvbj4KICAgICAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lSUQ9IkNSTiI+NDU0NjM0NjQ1NjQ1NjU0PC9jYmM6SUQ+CiAgICAgICAgICAgIDwvY2FjOlBhcnR5SWRlbnRpZmljYXRpb24+CiAgICAgICAgICAgIDxjYWM6UG9zdGFsQWRkcmVzcz4KICAgICAgICAgICAgICAgIDxjYmM6U3RyZWV0TmFtZT50ZXN0PC9jYmM6U3RyZWV0TmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6QnVpbGRpbmdOdW1iZXI+MzQ1NDwvY2JjOkJ1aWxkaW5nTnVtYmVyPgogICAgICAgICAgICAgICAgPGNiYzpQbG90SWRlbnRpZmljYXRpb24+MTIzNDwvY2JjOlBsb3RJZGVudGlmaWNhdGlvbj4KICAgICAgICAgICAgICAgIDxjYmM6Q2l0eVN1YmRpdmlzaW9uTmFtZT50ZXN0PC9jYmM6Q2l0eVN1YmRpdmlzaW9uTmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6Q2l0eU5hbWU+Uml5YWRoPC9jYmM6Q2l0eU5hbWU+CiAgICAgICAgICAgICAgICA8Y2JjOlBvc3RhbFpvbmU+MTIzNDU8L2NiYzpQb3N0YWxab25lPgogICAgICAgICAgICAgICAgPGNiYzpDb3VudHJ5U3ViZW50aXR5PnRlc3Q8L2NiYzpDb3VudHJ5U3ViZW50aXR5PgogICAgICAgICAgICAgICAgPGNhYzpDb3VudHJ5PgogICAgICAgICAgICAgICAgICAgIDxjYmM6SWRlbnRpZmljYXRpb25Db2RlPlNBPC9jYmM6SWRlbnRpZmljYXRpb25Db2RlPgogICAgICAgICAgICAgICAgPC9jYWM6Q291bnRyeT4KICAgICAgICAgICAgPC9jYWM6UG9zdGFsQWRkcmVzcz4KICAgICAgICAgICAgPGNhYzpQYXJ0eVRheFNjaGVtZT4KICAgICAgICAgICAgICAgIDxjYmM6Q29tcGFueUlEPjMwMDA3NTU4ODcwMDAwMzwvY2JjOkNvbXBhbnlJRD4KICAgICAgICAgICAgICAgIDxjYWM6VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgICAgIDxjYmM6SUQ+VkFUPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8L2NhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgIDwvY2FjOlBhcnR5VGF4U2NoZW1lPgogICAgICAgICAgICA8Y2FjOlBhcnR5TGVnYWxFbnRpdHk+CiAgICAgICAgICAgICAgICA8Y2JjOlJlZ2lzdHJhdGlvbk5hbWU+QWhtZWQgTW9oYW1lZCBBTCBBaG1hZHk8L2NiYzpSZWdpc3RyYXRpb25OYW1lPgogICAgICAgICAgICA8L2NhYzpQYXJ0eUxlZ2FsRW50aXR5PgogICAgICAgIDwvY2FjOlBhcnR5PgogICAgPC9jYWM6QWNjb3VudGluZ1N1cHBsaWVyUGFydHk+CiAgICA8Y2FjOkFjY291bnRpbmdDdXN0b21lclBhcnR5PgogICAgICAgIDxjYWM6UGFydHk+CiAgICAgICAgICAgIDxjYWM6UGFydHlJZGVudGlmaWNhdGlvbj4KICAgICAgICAgICAgICAgIDxjYmM6SUQgc2NoZW1lSUQ9Ik5BVCI+MjM0NTwvY2JjOklEPgogICAgICAgICAgICA8L2NhYzpQYXJ0eUlkZW50aWZpY2F0aW9uPgogICAgICAgICAgICA8Y2FjOlBvc3RhbEFkZHJlc3M+CiAgICAgICAgICAgICAgICA8Y2JjOlN0cmVldE5hbWU+YmFhb3VuPC9jYmM6U3RyZWV0TmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6QWRkaXRpb25hbFN0cmVldE5hbWU+c2RzZDwvY2JjOkFkZGl0aW9uYWxTdHJlZXROYW1lPgogICAgICAgICAgICAgICAgPGNiYzpCdWlsZGluZ051bWJlcj4zMzUzPC9jYmM6QnVpbGRpbmdOdW1iZXI+CiAgICAgICAgICAgICAgICA8Y2JjOlBsb3RJZGVudGlmaWNhdGlvbj4zNDM0PC9jYmM6UGxvdElkZW50aWZpY2F0aW9uPgogICAgICAgICAgICAgICAgPGNiYzpDaXR5U3ViZGl2aXNpb25OYW1lPmZnZmY8L2NiYzpDaXR5U3ViZGl2aXNpb25OYW1lPgogICAgICAgICAgICAgICAgPGNiYzpDaXR5TmFtZT5EaHVybWE8L2NiYzpDaXR5TmFtZT4KICAgICAgICAgICAgICAgIDxjYmM6UG9zdGFsWm9uZT4zNDUzNDwvY2JjOlBvc3RhbFpvbmU+CiAgICAgICAgICAgICAgICA8Y2JjOkNvdW50cnlTdWJlbnRpdHk+dWxoazwvY2JjOkNvdW50cnlTdWJlbnRpdHk+CiAgICAgICAgICAgICAgICA8Y2FjOkNvdW50cnk+CiAgICAgICAgICAgICAgICAgICAgPGNiYzpJZGVudGlmaWNhdGlvbkNvZGU+U0E8L2NiYzpJZGVudGlmaWNhdGlvbkNvZGU+CiAgICAgICAgICAgICAgICA8L2NhYzpDb3VudHJ5PgogICAgICAgICAgICA8L2NhYzpQb3N0YWxBZGRyZXNzPgogICAgICAgICAgICA8Y2FjOlBhcnR5VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgPGNhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgICAgICAgICAgPGNiYzpJRD5WQVQ8L2NiYzpJRD4KICAgICAgICAgICAgICAgIDwvY2FjOlRheFNjaGVtZT4KICAgICAgICAgICAgPC9jYWM6UGFydHlUYXhTY2hlbWU+CiAgICAgICAgICAgIDxjYWM6UGFydHlMZWdhbEVudGl0eT4KICAgICAgICAgICAgICAgIDxjYmM6UmVnaXN0cmF0aW9uTmFtZT5zZHNhPC9jYmM6UmVnaXN0cmF0aW9uTmFtZT4KICAgICAgICAgICAgPC9jYWM6UGFydHlMZWdhbEVudGl0eT4KICAgICAgICA8L2NhYzpQYXJ0eT4KICAgIDwvY2FjOkFjY291bnRpbmdDdXN0b21lclBhcnR5PgogICAgPGNhYzpEZWxpdmVyeT4KICAgICAgICA8Y2JjOkFjdHVhbERlbGl2ZXJ5RGF0ZT4yMDIyLTAzLTEzPC9jYmM6QWN0dWFsRGVsaXZlcnlEYXRlPgogICAgICAgIDxjYmM6TGF0ZXN0RGVsaXZlcnlEYXRlPjIwMjItMDMtMTU8L2NiYzpMYXRlc3REZWxpdmVyeURhdGU+CiAgICA8L2NhYzpEZWxpdmVyeT4KICAgIDxjYWM6UGF5bWVudE1lYW5zPgogICAgICAgIDxjYmM6UGF5bWVudE1lYW5zQ29kZT4xMDwvY2JjOlBheW1lbnRNZWFuc0NvZGU+CiAgICA8L2NhYzpQYXltZW50TWVhbnM+CiAgICA8Y2FjOkFsbG93YW5jZUNoYXJnZT4KICAgICAgICA8Y2JjOklEPjE8L2NiYzpJRD4KICAgICAgICA8Y2JjOkNoYXJnZUluZGljYXRvcj5mYWxzZTwvY2JjOkNoYXJnZUluZGljYXRvcj4KICAgICAgICA8Y2JjOkFsbG93YW5jZUNoYXJnZVJlYXNvbj5kaXNjb3VudDwvY2JjOkFsbG93YW5jZUNoYXJnZVJlYXNvbj4KICAgICAgICA8Y2JjOkFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjI8L2NiYzpBbW91bnQ+CiAgICAgICAgPGNhYzpUYXhDYXRlZ29yeT4KICAgICAgICAgICAgPGNiYzpJRCBzY2hlbWVBZ2VuY3lJRD0iNiIgc2NoZW1lSUQ9IlVOL0VDRSA1MzA1Ij5TPC9jYmM6SUQ+CiAgICAgICAgICAgIDxjYmM6UGVyY2VudD4xNTwvY2JjOlBlcmNlbnQ+CiAgICAgICAgICAgIDxjYWM6VGF4U2NoZW1lPgogICAgICAgICAgICAgICAgPGNiYzpJRCBzY2hlbWVBZ2VuY3lJRD0iNiIgc2NoZW1lSUQ9IlVOL0VDRSA1MTUzIj5WQVQ8L2NiYzpJRD4KICAgICAgICAgICAgPC9jYWM6VGF4U2NoZW1lPgogICAgICAgIDwvY2FjOlRheENhdGVnb3J5PgogICAgPC9jYWM6QWxsb3dhbmNlQ2hhcmdlPgogICAgPGNhYzpUYXhUb3RhbD4KICAgICAgICA8Y2JjOlRheEFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjE0NC45PC9jYmM6VGF4QW1vdW50PgogICAgICAgIDxjYWM6VGF4U3VidG90YWw+CiAgICAgICAgICAgIDxjYmM6VGF4YWJsZUFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjk2Ni4wMDwvY2JjOlRheGFibGVBbW91bnQ+CiAgICAgICAgICAgIDxjYmM6VGF4QW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTQ0LjkwPC9jYmM6VGF4QW1vdW50PgogICAgICAgICAgICA8Y2FjOlRheENhdGVnb3J5PgogICAgICAgICAgICAgICAgPGNiYzpJRCBzY2hlbWVBZ2VuY3lJRD0iNiIgc2NoZW1lSUQ9IlVOL0VDRSA1MzA1Ij5TPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8Y2JjOlBlcmNlbnQ+MTUuMDA8L2NiYzpQZXJjZW50PgogICAgICAgICAgICAgICAgPGNhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgICAgICAgICAgPGNiYzpJRCBzY2hlbWVBZ2VuY3lJRD0iNiIgc2NoZW1lSUQ9IlVOL0VDRSA1MTUzIj5WQVQ8L2NiYzpJRD4KICAgICAgICAgICAgICAgIDwvY2FjOlRheFNjaGVtZT4KICAgICAgICAgICAgPC9jYWM6VGF4Q2F0ZWdvcnk+CiAgICAgICAgPC9jYWM6VGF4U3VidG90YWw+CiAgICA8L2NhYzpUYXhUb3RhbD4KICAgIDxjYWM6VGF4VG90YWw+CiAgICAgICAgPGNiYzpUYXhBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4xNDQuOTwvY2JjOlRheEFtb3VudD4KCiAgICA8L2NhYzpUYXhUb3RhbD4KICAgIDxjYWM6TGVnYWxNb25ldGFyeVRvdGFsPgogICAgICAgIDxjYmM6TGluZUV4dGVuc2lvbkFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjk2OC4wMDwvY2JjOkxpbmVFeHRlbnNpb25BbW91bnQ+CiAgICAgICAgPGNiYzpUYXhFeGNsdXNpdmVBbW91bnQgY3VycmVuY3lJRD0iU0FSIj45NjYuMDA8L2NiYzpUYXhFeGNsdXNpdmVBbW91bnQ+CiAgICAgICAgPGNiYzpUYXhJbmNsdXNpdmVBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4xMTEwLjkwPC9jYmM6VGF4SW5jbHVzaXZlQW1vdW50PgogICAgICAgIDxjYmM6QWxsb3dhbmNlVG90YWxBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4yLjAwPC9jYmM6QWxsb3dhbmNlVG90YWxBbW91bnQ+CiAgICAgICAgPGNiYzpQcmVwYWlkQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MC4wMDwvY2JjOlByZXBhaWRBbW91bnQ+CiAgICAgICAgPGNiYzpQYXlhYmxlQW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+MTExMC45MDwvY2JjOlBheWFibGVBbW91bnQ+CiAgICA8L2NhYzpMZWdhbE1vbmV0YXJ5VG90YWw+CiAgICA8Y2FjOkludm9pY2VMaW5lPgogICAgICAgIDxjYmM6SUQ+MTwvY2JjOklEPgogICAgICAgIDxjYmM6SW52b2ljZWRRdWFudGl0eSB1bml0Q29kZT0iUENFIj40NC4wMDAwMDA8L2NiYzpJbnZvaWNlZFF1YW50aXR5PgogICAgICAgIDxjYmM6TGluZUV4dGVuc2lvbkFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjk2OC4wMDwvY2JjOkxpbmVFeHRlbnNpb25BbW91bnQ+CiAgICAgICAgPGNhYzpUYXhUb3RhbD4KICAgICAgICAgICAgPGNiYzpUYXhBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4xNDUuMjA8L2NiYzpUYXhBbW91bnQ+CiAgICAgICAgICAgIDxjYmM6Um91bmRpbmdBbW91bnQgY3VycmVuY3lJRD0iU0FSIj4xMTEzLjIwPC9jYmM6Um91bmRpbmdBbW91bnQ+CgogICAgICAgIDwvY2FjOlRheFRvdGFsPgogICAgICAgIDxjYWM6SXRlbT4KICAgICAgICAgICAgPGNiYzpOYW1lPmRzZDwvY2JjOk5hbWU+CiAgICAgICAgICAgIDxjYWM6Q2xhc3NpZmllZFRheENhdGVnb3J5PgogICAgICAgICAgICAgICAgPGNiYzpJRD5TPC9jYmM6SUQ+CiAgICAgICAgICAgICAgICA8Y2JjOlBlcmNlbnQ+MTUuMDA8L2NiYzpQZXJjZW50PgogICAgICAgICAgICAgICAgPGNhYzpUYXhTY2hlbWU+CiAgICAgICAgICAgICAgICAgICAgPGNiYzpJRD5WQVQ8L2NiYzpJRD4KICAgICAgICAgICAgICAgIDwvY2FjOlRheFNjaGVtZT4KICAgICAgICAgICAgPC9jYWM6Q2xhc3NpZmllZFRheENhdGVnb3J5PgogICAgICAgIDwvY2FjOkl0ZW0+CiAgICAgICAgPGNhYzpQcmljZT4KICAgICAgICAgICAgPGNiYzpQcmljZUFtb3VudCBjdXJyZW5jeUlEPSJTQVIiPjIyLjAwPC9jYmM6UHJpY2VBbW91bnQ+CiAgICAgICAgICAgIDxjYWM6QWxsb3dhbmNlQ2hhcmdlPgogICAgICAgICAgICAgICAgPGNiYzpDaGFyZ2VJbmRpY2F0b3I+ZmFsc2U8L2NiYzpDaGFyZ2VJbmRpY2F0b3I+CiAgICAgICAgICAgICAgICA8Y2JjOkFsbG93YW5jZUNoYXJnZVJlYXNvbj5kaXNjb3VudDwvY2JjOkFsbG93YW5jZUNoYXJnZVJlYXNvbj4KICAgICAgICAgICAgICAgIDxjYmM6QW1vdW50IGN1cnJlbmN5SUQ9IlNBUiI+Mi4wMDwvY2JjOkFtb3VudD4KICAgICAgICAgICAgPC9jYWM6QWxsb3dhbmNlQ2hhcmdlPgogICAgICAgIDwvY2FjOlByaWNlPgogICAgPC9jYWM6SW52b2ljZUxpbmU+CjwvSW52b2ljZT4=";
        $body = [
          "invoiceHash" => "PEx8bNFcEMEpHzUVvQntQI6ot8eFqTT/l59b+H1HqX4=",
          "uuid" => "16e78469-64af-406d-9cfd-895e724198f0",
          "invoice" => base64_encode($invoice_xml)
        ];

        $url = "https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/invoices/reporting/single";

        $response = Http::withHeaders($header)->post($url, $body);
//        if ($response->status()==200){
            ZatkaInfo::create([
                'trx_id' => $trx_id,
                'info' => 'nothing to store',
                'status_code' => $response->status()
            ]);
//        }

        return response($response->json(), $response->status());

    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');
            $business = $request->session()->get('business');
            $pos_settings = empty($business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business->pos_settings, true);

            $products = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (! empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                        }
                    }
                )
                        ->where('p.business_id', $business_id)
                        ->where('p.type', '!=', 'modifier')
                        ->where('p.is_inactive', 0)
                        ->where('p.not_for_selling', 0)
                        //Hide products not available in the selected location
                        ->where(function ($q) use ($location_id) {
                            $q->where('pl.location_id', $location_id);
                        });

            //Include search
            if (! empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('p.name', 'like', '%'.$term.'%');
                    $query->orWhere('sku', 'like', '%'.$term.'%');
                    $query->orWhere('sub_sku', 'like', '%'.$term.'%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }

            if (! empty($category_id) && ($category_id != 'all')) {
                $products->where(function ($query) use ($category_id) {
                    $query->where('p.category_id', $category_id);
                    $query->orWhere('p.sub_category_id', $category_id);
                });
            }
            if (! empty($brand_id) && ($brand_id != 'all')) {
                $products->where('p.brand_id', $brand_id);
            }

            if (! empty($request->get('is_enabled_stock'))) {
                $is_enabled_stock = 0;
                if ($request->get('is_enabled_stock') == 'product') {
                    $is_enabled_stock = 1;
                }

                $products->where('p.enable_stock', $is_enabled_stock);
            }

            if (! empty($request->get('repair_model_id'))) {
                $products->where('p.repair_model_id', $request->get('repair_model_id'));
            }

            $products = $products->select(
                'p.id as product_id',
                'p.name',
                'p.type',
                'p.enable_stock',
                'p.image as product_image',
                'variations.id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku'
            )
            ->with(['media', 'group_prices'])
            ->orderBy('p.name', 'asc')
            ->paginate(50);

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.'.$key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $show_prices = ! empty($pos_settings['show_pricing_on_product_sugesstion']);

            return view('sale_pos.partials.product_list')
                    ->with(compact('products', 'allowed_group_prices', 'show_prices'));
        }
    }

    /**
     * Shows invoice url.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showInvoiceUrl($id)
    {
        // if (!auth()->user()->can('sell.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            return view('sale_pos.partials.invoice_url_modal')
                    ->with(compact('transaction', 'url'));
        }
    }

    /**
     * Shows invoice to guest user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function showInvoice($token)
    {
        $transaction = Transaction::where('invoice_token', $token)->with(['business', 'location'])->first();

        if (! empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', false, false, $invoice_layout_id);
            $pos_settings = empty($transaction->business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($transaction->business->pos_settings, true);
            $payment_link = '';
            if (! empty($pos_settings['enable_payment_link']) && $transaction->payment_status != 'paid') {
                $payment_link = $this->transactionUtil->getInvoicePaymentLink($transaction->id, $transaction->business_id);
            }

            $title = $transaction->business->name.' | '.$transaction->invoice_no;

            return view('sale_pos.partials.show_invoice')
                    ->with(compact('receipt', 'title', 'payment_link'));
        } else {
            exit(__('messages.something_went_wrong'));
        }
    }

    /**
     * Allows payment for the invoice by guest user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function invoicePayment($token)
    {
        $transaction = Transaction::where('invoice_token', $token)->with(['business', 'contact', 'location'])->first();
        $business = $transaction->business;
        $business_details = $this->businessUtil->getDetails($business->id);
        $pos_settings = empty($business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business->pos_settings, true);

        if (! empty($transaction) && $transaction->status == 'final' && ! empty($pos_settings['enable_payment_link'])) {
            $title = $transaction->business->name.' | '.$transaction->invoice_no;
            $paid_amount = $this->transactionUtil->getTotalPaid($transaction->id);
            $total_payable = $transaction->final_total - $paid_amount;

            $total_payable_formatted = $this->transactionUtil->num_f($total_payable, true, $business_details);
            $date_formatted = $this->transactionUtil->format_date($transaction->transaction_date, true, $business_details);
            $total_amount = $this->transactionUtil->num_f($transaction->final_total, true, $business_details);
            $total_paid = $this->transactionUtil->num_f($paid_amount, true, $business_details);

            return view('sale_pos.partials.guest_payment_form')
                    ->with(compact('transaction', 'title', 'pos_settings', 'total_payable', 'total_payable_formatted', 'date_formatted', 'total_amount', 'total_paid', 'business_details'));
        } else {
            exit(__('messages.something_went_wrong'));
        }
    }

    public function pay_razorpay($transaction, $total_payable, $request)
    {
        $pos_settings = empty($transaction->business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($transaction->business->pos_settings, true);

        $razorpay_payment_id = $request->razorpay_payment_id;
        $razorpay_api = new Api($pos_settings['razor_pay_key_id'], $pos_settings['razor_pay_key_secret']);
        $payment = $razorpay_api->payment->fetch($razorpay_payment_id)->capture(['amount' => $total_payable * 100]); // Captures a payment

        if (empty($payment->error_code)) {
            return $payment->id;
        } else {
            $error_description = $payment->error_description;

            \Log::emergency($payment->error_description);
            throw new \Exception($error_description);
        }
    }

    public function pay_stripe($transaction, $total_payable, $request)
    {
        $pos_settings = empty($transaction->business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($transaction->business->pos_settings, true);

        Stripe::setApiKey($pos_settings['stripe_secret_key']);

        $metadata = ['stripe_email' => $request->stripeEmail];

        $business_details = $this->businessUtil->getDetails($transaction->business->id);

        $charge = Charge::create([
            'amount' => $total_payable * 100,
            'currency' => strtolower($business_details->currency_code),
            'source' => $request->stripeToken,
            'metadata' => $metadata,
        ]);

        return $charge->id;
    }

    public function confirmPayment($id, Request $request)
    {
        try {
            $transaction = Transaction::with(['business'])->find($id);

            $transaction_before = $transaction->replicate();

            $payment_link = $this->transactionUtil->getInvoicePaymentLink($transaction->id, $transaction->business_id);

            $paid_amount = $this->transactionUtil->getTotalPaid($transaction->id);
            $total_payable = $transaction->final_total - $paid_amount;

            $pay_function = 'pay_'.$request->gateway;

            $payment_id = $this->$pay_function($transaction, $total_payable, $request);

            if (! empty($payment_id)) {
                DB::beginTransaction();
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('sell_payment', $transaction->business_id);
                $payment_ref_no = $this->transactionUtil->generateReferenceNumber('sell_payment', $ref_count, $transaction->business_id);

                $data = [
                    'paid_on' => \Carbon::now()->toDateTimeString(),
                    'transaction_id' => $transaction->id,
                    'amount' => $total_payable,
                    'payment_for' => $transaction->contact_id,
                    'method' => 'cash',
                    'note' => $payment_id,
                    'paid_through_link' => 1,
                    'gateway' => $request->gateway,
                    'business_id' => $transaction->business_id,
                    'payment_ref_no' => $payment_ref_no,
                ];

                $tp = TransactionPayment::create($data);

                $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                $transaction->payment_status = $payment_status;

                $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);
                DB::commit();

                $output = [
                    'success' => 1,
                    'msg' => __('purchase.payment_added_success'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect($payment_link)->with('status', $output);
    }

    /**
     * Display a listing of the recurring invoices.
     *
     * @return \Illuminate\Http\Response
     */
    public function listSubscriptions()
    {
        if (! auth()->user()->can('sell.view') && ! auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_recurring', 1)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.subscription_no',
                    'bl.name as business_location',
                    'transactions.recur_parent_id',
                    'transactions.recur_stopped_on',
                    'transactions.is_recurring',
                    'transactions.recur_interval',
                    'transactions.recur_interval_type',
                    'transactions.recur_repetitions',
                    'transactions.subscription_repeat_on'
                )->with(['subscription_invoices']);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }
            if (! empty(request()->contact_id)) {
                $sells->where('transactions.contact_id', request()->contact_id);
            }
            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '';

                        if ($row->is_recurring == 1 && auth()->user()->can('sell.update')) {
                            $link_text = ! empty($row->recur_stopped_on) ? __('lang_v1.start_subscription') : __('lang_v1.stop_subscription');
                            $link_class = ! empty($row->recur_stopped_on) ? 'btn-success' : 'btn-danger';

                            $html .= '<a href="'.action([\App\Http\Controllers\SellPosController::class, 'toggleRecurringInvoices'], [$row->id]).'" class="toggle_recurring_invoice btn btn-xs '.$link_class.'"><i class="fa fa-power-off"></i> '.$link_text.'</a>';

                            if ($row->is_direct_sale == 0) {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="'.action([\App\Http\Controllers\SellPosController::class, 'edit'], [$row->id]).'"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                            } else {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="'.action([\App\Http\Controllers\SellController::class, 'edit'], [$row->id]).'"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                            }

                            if (auth()->user()->can('direct_sell.delete') || auth()->user()->can('sell.delete')) {
                                $html .= '&nbsp;<a href="'.action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$row->id]).'" class="delete-sale btn btn-xs btn-danger"><i class="fas fa-trash"></i> '.__('messages.delete').'</a>';
                            }
                        }

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('recur_interval', function ($row) {
                    $type = $row->recur_interval == 1 ? Str::singular(__('lang_v1.'.$row->recur_interval_type)) : __('lang_v1.'.$row->recur_interval_type);
                    $recur_interval = $row->recur_interval.$type;

                    if ($row->recur_interval_type == 'months' && ! empty($row->subscription_repeat_on)) {
                        $recur_interval .= '<br><small class="text-muted">'.
                        __('lang_v1.repeat_on').': '.str_ordinal($row->subscription_repeat_on);
                    }

                    return $recur_interval;
                })
                ->editColumn('recur_repetitions', function ($row) {
                    return ! empty($row->recur_repetitions) ? $row->recur_repetitions : '-';
                })
                ->addColumn('subscription_invoices', function ($row) {
                    $invoices = [];
                    if (! empty($row->subscription_invoices)) {
                        $invoices = $row->subscription_invoices->pluck('invoice_no')->toArray();
                    }

                    $html = '';
                    $count = 0;
                    if (! empty($invoices)) {
                        $imploded_invoices = '<span class="label bg-info">'.implode('</span>, <span class="label bg-info">', $invoices).'</span>';
                        $count = count($invoices);
                        $html .= '<small>'.$imploded_invoices.'</small>';
                    }
                    if ($count > 0) {
                        $html .= '<br><small class="text-muted">'.
                    __('sale.total').': '.$count.'</small>';
                    }

                    return $html;
                })
                ->addColumn('last_generated', function ($row) {
                    if (! empty($row->subscription_invoices)) {
                        $last_generated_date = $row->subscription_invoices->max('created_at');
                    }

                    return ! empty($last_generated_date) ? $last_generated_date->diffForHumans() : '';
                })
                ->addColumn('upcoming_invoice', function ($row) {
                    if (empty($row->recur_stopped_on)) {
                        $last_generated = ! empty(count($row->subscription_invoices)) ? \Carbon::parse($row->subscription_invoices->max('transaction_date')) : \Carbon::parse($row->transaction_date);
                        $last_generated_string = $last_generated->format('Y-m-d');
                        $last_generated = \Carbon::parse($last_generated_string);

                        if ($row->recur_interval_type == 'days') {
                            $upcoming_invoice = $last_generated->addDays($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'months') {
                            if (! empty($row->subscription_repeat_on)) {
                                $last_generated_string = $last_generated->format('Y-m');
                                $last_generated = \Carbon::parse($last_generated_string.'-'.$row->subscription_repeat_on);
                            }

                            $upcoming_invoice = $last_generated->addMonths($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'years') {
                            $upcoming_invoice = $last_generated->addYears($row->recur_interval);
                        }
                    }

                    return ! empty($upcoming_invoice) ? $this->transactionUtil->format_date($upcoming_invoice) : '';
                })
                ->rawColumns(['action', 'subscription_invoices', 'recur_interval'])
                ->make(true);

            return $datatable;
        }

        return view('sale_pos.subscriptions');
    }

    /**
     * Starts or stops a recurring invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleRecurringInvoices($id)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->where('is_recurring', 1)
                            ->findorfail($id);

            if (empty($transaction->recur_stopped_on)) {
                $transaction->recur_stopped_on = \Carbon::now();
            } else {
                $transaction->recur_stopped_on = null;
            }
            $transaction->save();

            $output = ['success' => 1,
                'msg' => trans('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function getRewardDetails(Request $request)
    {
        if ($request->session()->get('business.enable_rp') != 1) {
            return '';
        }

        $business_id = request()->session()->get('user.business_id');

        $customer_id = $request->input('customer_id');

        $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $customer_id);

        return json_encode($redeem_details);
    }

    public function placeOrdersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');
            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business_id = $api_settings->business_id;
            $location_id = $api_settings->location_id;

            $input = $request->only(['products', 'customer_id', 'addresses']);

            //check if all stocks are available
            $variation_ids = [];
            foreach ($input['products'] as $product_data) {
                $variation_ids[] = $product_data['variation_id'];
            }

            $variations_details = $this->getVariationsDetails($business_id, $location_id, $variation_ids);
            $is_valid = true;
            $error_messages = [];
            $sell_lines = [];
            $final_total = 0;
            foreach ($variations_details as $variation_details) {
                if ($variation_details->product->enable_stock == 1) {
                    if (empty($variation_details->variation_location_details[0]) || $variation_details->variation_location_details[0]->qty_available < $input['products'][$variation_details->id]['quantity']) {
                        $is_valid = false;
                        $error_messages[] = 'Only '.$variation_details->variation_location_details[0]->qty_available.' '.$variation_details->product->unit->short_name.' of '.$input['products'][$variation_details->id]['product_name'].' available';
                    }
                }

                //Create product line array
                $sell_lines[] = [
                    'product_id' => $variation_details->product->id,
                    'unit_price_before_discount' => $variation_details->unit_price_inc_tax,
                    'unit_price' => $variation_details->unit_price_inc_tax,
                    'unit_price_inc_tax' => $variation_details->unit_price_inc_tax,
                    'variation_id' => $variation_details->id,
                    'quantity' => $input['products'][$variation_details->id]['quantity'],
                    'item_tax' => 0,
                    'enable_stock' => $variation_details->product->enable_stock,
                    'tax_id' => null,
                ];

                $final_total += ($input['products'][$variation_details->id]['quantity'] * $variation_details->unit_price_inc_tax);
            }

            if (! $is_valid) {
                return $this->respond([
                    'success' => false,
                    'error_messages' => $error_messages,
                ]);
            }

            $business = Business::find($business_id);
            $user_id = $business->owner_id;

            $business_data = [
                'id' => $business_id,
                'accounting_method' => $business->accounting_method,
                'location_id' => $location_id,
            ];

            $customer = Contact::where('business_id', $business_id)
                            ->whereIn('type', ['customer', 'both'])
                            ->find($input['customer_id']);

            $order_data = [
                'business_id' => $business_id,
                'location_id' => $location_id,
                'contact_id' => $input['customer_id'],
                'final_total' => $final_total,
                'created_by' => $user_id,
                'status' => 'final',
                'payment_status' => 'due',
                'additional_notes' => '',
                'transaction_date' => \Carbon::now(),
                'customer_group_id' => $customer->customer_group_id,
                'tax_rate_id' => null,
                'sale_note' => null,
                'commission_agent' => null,
                'order_addresses' => json_encode($input['addresses']),
                'products' => $sell_lines,
                'is_created_from_api' => 1,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
            ];

            $invoice_total = [
                'total_before_tax' => $final_total,
                'tax' => 0,
            ];

            DB::beginTransaction();

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $order_data, $invoice_total, $user_id, false);

            //Create sell lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $order_data['products'], $order_data['location_id'], false, null, [], false);

            //update product stock
            foreach ($order_data['products'] as $product) {
                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $order_data['location_id'],
                        $product['quantity']
                    );
                }
            }

            $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            //Auto send notification
            $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);

            DB::commit();

            $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction->id);

            $output = [
                'success' => 1,
                'transaction' => $transaction,
                'receipt' => $receipt,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $msg = trans('messages.something_went_wrong');

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                'error_messages' => [$msg],
            ];
        }

        return $this->respond($output);
    }

    private function getVariationsDetails($business_id, $location_id, $variation_ids)
    {
        $variation_details = Variation::whereIn('id', $variation_ids)
                            ->with([
                                'product' => function ($q) use ($business_id) {
                                    $q->where('business_id', $business_id);
                                },
                                'product.unit',
                                'variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                },
                            ])->get();

        return $variation_details;
    }

    public function getTypesOfServiceDetails(Request $request)
    {
        $location_id = $request->input('location_id');
        $types_of_service_id = $request->input('types_of_service_id');

        $business_id = $request->session()->get('user.business_id');

        $types_of_service = TypesOfService::where('business_id', $business_id)
                                        ->where('id', $types_of_service_id)
                                        ->first();

        $price_group_id = ! empty($types_of_service->location_price_group[$location_id])
                ? $types_of_service->location_price_group[$location_id] : '';
        $price_group_name = '';

        if (! empty($price_group_id)) {
            $price_group = SellingPriceGroup::find($price_group_id);
            $price_group_name = $price_group->name;
        }

        $modal_html = view('types_of_service.pos_form_modal')
                    ->with(compact('types_of_service'))->render();

        return $this->respond([
            'price_group_id' => $price_group_id,
            'packing_charge' => $types_of_service->packing_charge,
            'packing_charge_type' => $types_of_service->packing_charge_type,
            'modal_html' => $modal_html,
            'price_group_name' => $price_group_name,
        ]);
    }

    private function __getwarranties()
    {
        $business_id = session()->get('user.business_id');
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = ! empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        return $warranties;
    }

    /**
     * Parse the weighing barcode.
     *
     * @return array
     */
    private function __parseWeighingBarcode($scale_barcode)
    {
        $business_id = session()->get('user.business_id');

        $scale_setting = session()->get('business.weighing_scale_setting');

        $error_msg = trans('messages.something_went_wrong');

        //Check for prefix.
        if ((strlen($scale_setting['label_prefix']) == 0) || Str::startsWith($scale_barcode, $scale_setting['label_prefix'])) {
            $scale_barcode = substr($scale_barcode, strlen($scale_setting['label_prefix']));

            //Get product sku, trim left side 0
            $sku = ltrim(substr($scale_barcode, 0, $scale_setting['product_sku_length'] + 1), '0');

            //Get quantity integer
            $qty_int = substr($scale_barcode, $scale_setting['product_sku_length'] + 1, $scale_setting['qty_length'] + 1);

            //Get quantity decimal
            $qty_decimal = '0.'.substr($scale_barcode, $scale_setting['product_sku_length'] + $scale_setting['qty_length'] + 2, $scale_setting['qty_length_decimal'] + 1);

            $qty = (float) $qty_int + (float) $qty_decimal;

            //Find the variation id
            $result = $this->productUtil->filterProduct($business_id, $sku, null, false, null, [], ['sub_sku'], false, 'exact')->first();

            if (! empty($result)) {
                return ['variation_id' => $result->variation_id,
                    'qty' => $qty,
                    'success' => true,
                ];
            } else {
                $error_msg = trans('lang_v1.sku_not_match', ['sku' => $sku]);
            }
        } else {
            $error_msg = trans('lang_v1.prefix_did_not_match');
        }

        return [
            'success' => false,
            'msg' => $error_msg,
        ];
    }

    public function getFeaturedProducts($id)
    {
        $location = BusinessLocation::findOrFail($id);
        $featured_products = $location->getFeaturedProducts();

        if (! empty($featured_products)) {
            return view('sale_pos.partials.featured_products')->with(compact('featured_products'));
        } else {
            return '';
        }
    }

    /**
     * Converts drafts and quotations to invoice
     */
    public function convertToInvoice($id)
    {
        if (! auth()->user()->can('sell.create') && ! auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::with(['sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'contact', ])
                            ->where('business_id', $business_id)
                            ->where('status', 'draft')
                            ->findOrFail($id);

            $transaction_before = $transaction->replicate();
            $is_direct_sale = $transaction->is_direct_sale;
            //Check Customer credit limit
            $data = [
                'final_total' => $transaction->final_total,
                'contact_id' => $transaction->contact_id,
                'status' => 'final',
            ];
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($data, $id);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = ['success' => 0,
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount]),
                ];

                return redirect()
                        ->back()
                        ->with('status', $output);
            }

            DB::beginTransaction();
            //Check if there is a open register, if no then redirect to Create Register screen.
            if (! $is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
            }

            $invoice_no = $this->transactionUtil->getInvoiceNumber($business_id, 'final', $transaction->location_id);

            $transaction->invoice_no = $invoice_no;
            $transaction->transaction_date = \Carbon::now();
            $transaction->status = 'final';
            $transaction->sub_status = null;
            $transaction->is_quotation = 0;
            $transaction->save();

            //update product stock
            foreach ($transaction->sell_lines as $sell_line) {
                $decrease_qty = $sell_line->quantity;

                if ($sell_line->product->enable_stock == 1) {
                    $this->productUtil->decreaseProductQuantity(
                        $sell_line->product_id,
                        $sell_line->variation_id,
                        $transaction->location_id,
                        $decrease_qty
                    );
                }

                if ($sell_line->product->type == 'combo') {
                    //Decrease quantity of combo as well.
                    $combo_variations = $sell_line->variations->combo_variations;

                    foreach ($combo_variations as $key => $value) {
                        $base_unit_multiplier = 1;

                        if (! empty($value['unit_id'])) {
                            $unit = Unit::find($value['unit_id']);
                            $base_unit_multiplier = ! empty($unit->base_unit_multiplier) ? $unit->base_unit_multiplier : $base_unit_multiplier;
                        }

                        $combo_variations[$key]['product_id'] = $sell_line->product_id;
                        $combo_variations[$key]['product_id'] = $sell_line->product_id;
                        $combo_variations[$key]['quantity'] = $value['quantity'] * $decrease_qty * $base_unit_multiplier;
                    }
                    $this->productUtil
                        ->decreaseProductQuantityCombo(
                            $combo_variations,
                            $transaction->location_id
                        );
                }
            }

            //Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $business = ['id' => $business_id,
                'accounting_method' => request()->session()->get('business.accounting_method'),
                'location_id' => $transaction->location_id,
                'pos_settings' => $pos_settings,
            ];

            try {
                $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $msg = trans('messages.something_went_wrong');

                if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                    $msg = $e->getMessage();
                }

                if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                    $msg = $e->getMessage();
                }

                $output = ['success' => 0,
                    'msg' => $msg,
                ];

                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'index'])
                    ->with('status', $output);
            }

            //Auto send notification
            $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_invoice_successfully', ['invoice_no' => $transaction->invoice_no])];
        } catch (Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $msg = trans('messages.something_went_wrong');

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                'msg' => $msg,
            ];
        }

        return redirect()
                ->action([\App\Http\Controllers\SellController::class, 'index'])
                ->with('status', $output);
    }

    /**
     * Converts drafts and quotations to invoice
     */
    public function convertToProforma($id)
    {
        if (! auth()->user()->can('sell.create') && ! auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                            ->where('status', 'draft')
                            ->findOrFail($id);

            $transaction_before = $transaction->replicate();

            $transaction->sub_status = 'proforma';
            $transaction->save();

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_proforma_successfully')];
        } catch (Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => trans('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Copy quotation
     *
     */
    public function copyQuotation($id)
    {
        if (!auth()->user()->can('quotation.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                            ->where('sub_status', 'quotation')
                            ->findOrFail($id);

            DB::beginTransaction();
            $quotation = $transaction->replicate();

            $quotation->transaction_date = \Carbon::now()->format('Y-m-d H:i:s');
            $quotation->invoice_no = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', 
            $transaction->location_id);
            $quotation->save();

            $sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)->get();
            $new_sell_lines = [];
            foreach($sell_lines as $sell_line) {
                $sl = $sell_line->replicate();
                $sl->transaction_id = $quotation->id;

                $new_sell_lines[] = $sl;
            }

            $quotation->sell_lines()->saveMany($new_sell_lines);

            DB::commit();
                        
            $output = ['success' => 1, 'msg' => __('lang_v1.converted_to_proforma_successfully')];

        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        return redirect()->action([\App\Http\Controllers\SellController::class, 'getQuotations']);
    }

     /**
     * download pdf for given transaction
     */
    public function downloadPdf($id)
    {
        if (! (config('constants.enable_download_pdf') && auth()->user()->can('print_invoice'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $receipt_contents = $this->transactionUtil->getPdfContentsForGivenTransaction($business_id, $id);
        $receipt_details = $receipt_contents['receipt_details'];
        $location_details = $receipt_contents['location_details'];
        $is_email_attachment = false;

        $blade_file = 'download_pdf';
        if (! empty($receipt_details->is_export)) {
            $blade_file = 'download_export_pdf';
        }

        //Generate pdf
        $body = view('sale_pos.receipts.'.$blade_file)
                    ->with(compact('receipt_details', 'location_details', 'is_email_attachment'))
                    ->render();

        $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'format' => 'A4',
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetWatermarkText($receipt_details->business_name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle('INVOICE-'.$receipt_details->invoice_no.'.pdf');
        $mpdf->WriteHTML($body);
        $mpdf->Output('INVOICE-'.$receipt_details->invoice_no.'.pdf', 'I');
    }

    /**
     * download pdf for given quotation
     */
    public function downloadQuotationPdf($id)
    {
        if (! (config('constants.enable_download_pdf'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $sub_status = ! empty(request()->input('sub_status')) ? request()->input('sub_status') : '';
        $receipt_contents = $this->transactionUtil->getPdfContentsForGivenTransaction($business_id, $id);
        $receipt_details = $receipt_contents['receipt_details'];
        $location_details = $receipt_contents['location_details'];

        //Generate pdf
        $body = view('sale_pos.receipts.download_quotation_pdf')
                    ->with(compact('receipt_details', 'location_details', 'sub_status'))
                    ->render();
        $pdf_name = (! empty($sub_status) && $sub_status == 'proforma') ? __('lang_v1.proforma_invoice') : 'QUOTATION';
        $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'format' => 'A4',
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetWatermarkText($receipt_details->business_name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle($pdf_name.'-'.$receipt_details->invoice_no.'.pdf');
        $mpdf->WriteHTML($body);
        $mpdf->Output($pdf_name.'-'.$receipt_details->invoice_no.'.pdf', 'I');
    }

    /**
     * download pdf for given shipment
     */
    public function downloadPackingListPdf($id)
    {
        if (! (config('constants.enable_download_pdf'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $receipt_contents = $this->transactionUtil->getPdfContentsForGivenTransaction($business_id, $id);
        $receipt_details = $receipt_contents['receipt_details'];
        $location_details = $receipt_contents['location_details'];

        //Generate pdf
        $body = view('sale_pos.receipts.download_packing_list_pdf')
                    ->with(compact('receipt_details', 'location_details'))
                    ->render();

        $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'format' => 'A4',
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetWatermarkText($receipt_details->business_name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle('PACKINGSLIP-'.$receipt_details->invoice_no.'.pdf');
        $mpdf->WriteHTML($body);
        $mpdf->Output('PACKINGSLIP-'.$receipt_details->invoice_no.'.pdf', 'I');
    }

    public function showServiceStaffAvailibility()
    {
        $location_id = request()->input('location_id');
        $business_id = request()->session()->get('user.business_id');

        $service_staffs = $this->productUtil->getServiceStaff($business_id, $location_id);

        return view('sale_pos.partials.service_staff_availability_modal')
                    ->with(compact('service_staffs'));
    }

    public function pauseResumeServiceStaffTimer($user_id)
    {
        $service_staff = User::find($user_id);
        if (empty($service_staff->paused_at)) {
            $service_staff->paused_at = \Carbon::now();
        } else {
            //add diff to available_at
            $mins = \Carbon::now()->diffInMinutes(\Carbon::parse($service_staff->paused_at));
            $service_staff->available_at = \Carbon::parse($service_staff->available_at)->addMinutes($mins);
            $service_staff->paused_at = null;
        }

        $service_staff->save();

        return ['paused_at' => $service_staff->paused_at];
    }

    public function markAsAvailable($user_id)
    {
        $service_staff = User::where('id', $user_id)
                            ->update(['paused_at' => null, 'available_at' => null]);

        return ['success' => true];
    }
}
