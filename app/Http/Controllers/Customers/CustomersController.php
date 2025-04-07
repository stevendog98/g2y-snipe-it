<?php

namespace App\Http\Controllers\Customers;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Http\Requests\UploadFileRequest;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\User;
use App\View\Label;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TypeError;

/**
 * This class controls all actions related to customers for
 * the  Geeks 2 You Custom Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [S. Williams] [<swilliams@geeks2you.com>]
 */
class CustomersController extends Controller
{
    protected $qrCodeDimensions = ['height' => 3.5, 'width' => 3.5];
    protected $barCodeDimensions = ['height' => 2, 'width' => 22];

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the customers listing, which is generated in getDatatable.
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * 
     */
    public function index(Request $request) : View
    {
        $this->authorize('index', Customer::class);
        $company = Company::find($request->input('company_id'));

        return view('customers/index')->with('company', $company);
    }

    /**
     * Returns a view that presents a form to create a new customer.
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @since [v1.0]
     * @param Request $request
     * @internal param int $customer_id
     */
    public function create(Request $request) : View
    {
        $this->authorize('create', Customer::class);
        $view = view('customers/edit')
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('item', new Asset)
            ->with('statuslabel_types', Helper::statusTypeList());

        return $view;
    }

    /**
     * Validate and process new customer form data.
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @since [v1.0]
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize(Customer::class);

        // There are a lot more rules to add here but prevents
        // errors around `asset_tags` not being present below.
        $this->validate($request, ['asset_tags' => ['required', 'array']]);

        // Handle asset tags - there could be one, or potentially many.
        // This is only necessary on create, not update, since bulk editing is handled
        // differently
        $asset_tags = $request->input('asset_tags');

        $settings = Setting::getSettings();

        $successes = [];
        $failures = [];
        $customer = null;

        for ($a = 1; $a <= count($asset_tags); $a++) {
            $customer = new Customer();
            $customer->name = $request->input('name');

            

            if (($asset_tags) && (array_key_exists($a, $asset_tags))) {
                $customer->asset_tag = $asset_tags[$a];
            }

            $customer->company_id              = Company::getIdForCurrentUser($request->input('company_id'));
            $customer->customer_id             = $request->input('customer_id');
            $customer->notes                   = $request->input('notes');
            $customer->created_by              = auth()->id();
            $customer->status_id               = request('status_id');
            $customer->warranty_months         = request('warranty_months', null);
            $customer->rtd_location_id         = request('rtd_location_id', null);
            $customer->asset_tag               = $customer->customer_id;

            if (! empty($settings->audit_interval)) {
                $asset->next_audit_date = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
            }

            // Set location_id to rtd_location_id ONLY if the asset isn't being checked out
            if (!request('assigned_user') && !request('assigned_asset') && !request('assigned_location')) {
                $asset->location_id = $request->input('rtd_location_id', null);
            }

            // Create the image (if one was chosen.)
            if ($request->has('image')) {
                $asset = $request->handleImages($asset);
            }

            
            

            // Validate the customer before saving
            
            // This defaults permanently to location, as customers cant be checked out by snipe standards.
            if ($customer->isValid() && $customer->save()) {
                $target = Location::find(request('assigned_location'));
                $location = $target->id;

                if (isset($target)) {
                    $customer->checkOut($target, auth()->user(), date('Y-m-d H:i:s'), $request->input('expected_checkin', null), 'Checked out on customer creation', $request->get('name'), $location);
                }

                $successes[] = "<a href='" . route('customer.show', $customer) . "' style='color: white;'>" . e($customer->asset_tag) . "</a>";

            } else {
                $failures[] = join(",", $customer->getErrors()->all());
            }
        }

        session()->put(['redirect_option' => $request->get('redirect_option'), 'checkout_to_type' => $request->get('checkout_to_type')]);


        if ($successes) {
            if ($failures) {
                //some succeeded, some failed
                return redirect()->to(Helper::getRedirectOption($request, $customer->id, 'Customers')) //FIXME - not tested <<-- Left by SNIPE TEAM - Steven
                ->with('success-unescaped', trans_choice('admin/hardware/message.create.multi_success_linked', $successes, ['links' => join(", ", $successes)]))
                    ->with('warning', trans_choice('admin/hardware/message.create.partial_failure', $failures, ['failures' => join("; ", $failures)]));
            } else {
                if (count($successes) == 1) {
                    //the most common case, keeping it so we don't have to make every use of that translation string be trans_choice'ed
                    //and re-translated
                    return redirect()->to(Helper::getRedirectOption($request, $customer->id, 'Customers'))
                        ->with('success-unescaped', trans('admin/hardware/message.create.success_linked', ['link' => route('customers.show', $asset), 'id', 'tag' => e($customer->asset_tag)]));
                } else {
                    //multi-success
                    return redirect()->to(Helper::getRedirectOption($request, $customer->id, 'Customers'))
                        ->with('success-unescaped', trans_choice('admin/hardware/message.create.multi_success_linked', $successes, ['links' => join(", ", $successes)]));
                }
            }

        }

        return redirect()->back()->withInput()->withErrors($customer->getErrors());
    }


    /**
     * Returns a view that presents a form to edit an existing customer.
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Customer $customer) : View | RedirectResponse
    {
        $this->authorize($customer);
        return view('customer/edit')
            ->with('item', $asset)
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('statuslabel_types', Helper::statusTypeList());
    }


    /**
     * Returns a view that presents information about an customer for detail view.
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @param int $customerId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Customer $customer) : View | RedirectResponse
    {
        $this->authorize('view', $customer);
        $settings = Setting::getSettings();

        if (isset($customer)) {
            $audit_log = Actionlog::where('action_type', '=', 'audit')
                ->where('item_id', '=', $customer->id)
                ->where('item_type', '=', Customer::class)
                ->orderBy('created_at', 'DESC')->first();

            if ($customer->location) {
                $use_currency = $customer->location->currency;
            } else {
                if ($settings->default_currency != '') {
                    $use_currency = $settings->default_currency;
                } else {
                    $use_currency = trans('general.currency');
                }
            }

            $qr_code = (object) [
                'display' => $settings->qr_code == '1',
                'url' => route('qr_code/customer', $customer),
            ];

            return view('customer/view', compact('customer', 'qr_code', 'settings'))
                ->with('use_currency', $use_currency)->with('audit_log', $audit_log);
        }

        return redirect()->route('customer.index')->with('error', trans('admin/customers/message.does_not_exist'));
    }

    /**
     * Validate and process customer edit form.
     *
     * @param int $customerId
     * @since [v1.0]
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     */
    public function update(ImageUploadRequest $request, Customer $customer) : RedirectResponse
    {

        $this->authorize($customer);

        $customer->status_id = $request->input('status_id', null);
        $customer->warranty_months = $request->input('warranty_months', null);
        $customer->purchase_cost = $request->input('purchase_cost', null);
        $customer->purchase_date = $request->input('purchase_date', null);
        $customer->next_audit_date = $request->input('next_audit_date', null);
        
        $asset->rtd_location_id = $request->input('rtd_location_id', null);

        $status = Statuslabel::find($request->input('status_id'));

        

        if ($customer->assigned_to == '') {
            $customer->location_id = $request->input('rtd_location_id', null);
        }


        if ($request->filled('image_delete')) {
            try {
                unlink(public_path().'/uploads/customers/'.$customer->image);
                $customer->image = '';
            } catch (\Exception $e) {
                Log::info($e);
            }
        }

        // Update the customer data

        $customer->name = $request->input('name');
        $customer->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $customer->customer_id = $request->input('customer_id');
        $customer->order_number = $request->input('order_number');

        $asset_tags = $request->input('asset_tags');
        $customer->asset_tag = $request->input('asset_tags');

        if (is_array($request->input('asset_tags'))) {
            $customer->asset_tag = $asset_tags[1];
        }

        $customer->notes = $request->input('notes');

        $customer = $request->handleImages($customer);

        

        session()->put(['redirect_option' => $request->get('redirect_option'), 'checkout_to_type' => $request->get('checkout_to_type')]);

        if ($customer->save()) {
            return redirect()->to(Helper::getRedirectOption($request, $customer->id, 'Customers'))
                ->with('success', trans('admin/customers/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($customer->getErrors());
    }

    /**
     * Delete a given Customer (mark as deleted).
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @param int $customerId
     * @since [v1.0]
     */
    public function destroy(Request $request, $customerId) : RedirectResponse
    {
        // Check if the asset exists
        if (is_null($customer = Customer::find($customerId))) {
            // Redirect to the asset management page with error
            return redirect()->route('customer.index')->with('error', trans('admin/customers/message.does_not_exist'));
        }

        $this->authorize('delete', $customer);

        


        if ($asset->image) {
            try {
                Storage::disk('public')->delete('customers'.'/'.$customer->image);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', trans('admin/customers/message.delete.success'));
    }

    /**
     * Searches the customers table by asset tag, and redirects if it finds one
     *
     *  We can let it search by Asset Tag, as we set "asset tag 1" as the syncro ID
     *
     * @author [S. Williams] [<swilliams@geeks2you.com>]
     * @since [v3.0]
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getAssetByTag(Request $request, $tag=null) : RedirectResponse
    {
        $tag = $tag ? $tag : $request->get('assetTag');
        $topsearch = ($request->get('topsearch') == 'true');

        // Search for an exact and unique asset tag match
        $customers = Customer::where('asset_tag', '=', $tag);

        // If not a unique result, redirect to the index view
        if ($customers->count() != 1) {
            return redirect()->route('customers.index')
                ->with('search', $tag)
                ->with('warning', trans('admin/customers/message.does_not_exist_var', [ 'asset_tag' => $tag ]));
        }
        $customer = $customers->first();
        $this->authorize('view', $customer);

        return redirect()->route('customers.show', $customer->id)->with('topsearch', $topsearch);
    }


    /**
     * Return a QR code for the asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     */
    public function getQrCode(Customer $customer) : Response | BinaryFileResponse | string | bool
    {
        $settings = Setting::getSettings();

        if (($settings->qr_code == '1') && ($settings->label2_2d_type !== 'none')) {

            if ($customer) {
                $size = Helper::barcodeDimensions($settings->label2_2d_type);
                $qr_file = public_path().'/uploads/barcodes/qr-'.str_slug($customer->asset_tag).'-'.str_slug($customer->id).'.png';

                if (isset($customer->id, $customer->asset_tag)) {
                    if (file_exists($qr_file)) {
                        $header = ['Content-type' => 'image/png'];

                        return response()->file($qr_file, $header);
                    } else {
                        $barcode = new \Com\Tecnick\Barcode\Barcode();
                        $barcode_obj = $barcode->getBarcodeObj($settings->label2_2d_type, route('customers.show', $customer->id), $size['height'], $size['width'], 'black', [-2, -2, -2, -2]);
                        file_put_contents($qr_file, $barcode_obj->getPngData());

                        return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
                    }
                }
            }

            return 'That asset is invalid';
        }
        return false;
    }

    /**
     * Return a 2D barcode for the asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Response
     */
    public function getBarCode($assetId = null)
    {
        $settings = Setting::getSettings();
        if ($asset = Asset::withTrashed()->find($assetId)) {
            $barcode_file = public_path().'/uploads/barcodes/'.str_slug($settings->label2_1d_type).'-'.str_slug($asset->asset_tag).'.png';

            if (isset($asset->id, $asset->asset_tag)) {
                if (file_exists($barcode_file)) {
                    $header = ['Content-type' => 'image/png'];

                    return response()->file($barcode_file, $header);
                } else {
                    // Calculate barcode width in pixel based on label width (inch)
                    $barcode_width = ($settings->labels_width - $settings->labels_display_sgutter) * 200.000000000001;

                    $barcode = new \Com\Tecnick\Barcode\Barcode();
                    try {
                        $barcode_obj = $barcode->getBarcodeObj($settings->label2_1d_type, $asset->asset_tag, ($barcode_width < 300 ? $barcode_width : 300), 50);
                        file_put_contents($barcode_file, $barcode_obj->getPngData());

                        return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
                    } catch (\Exception|TypeError $e) {
                        Log::debug('The barcode format is invalid.');

                        return response(file_get_contents(public_path('uploads/barcodes/invalid_barcode.gif')))->header('Content-type', 'image/gif');
                    }
                }
            }
        }
        return null;
    }

    /**
     * Return a label for an individual asset.
     *
     * @author [L. Swartzendruber] [<logan.swartzendruber@gmail.com>
     * @param int $assetId
     * @return \Illuminate\Contracts\View\View
     */
    public function getLabel($assetId = null)
    {
        if (isset($assetId)) {
            $asset = Asset::find($assetId);
            $this->authorize('view', $asset);

            return (new Label())
                ->with('assets', collect([ $asset ]))
                ->with('settings', Setting::getSettings())
                ->with('template', request()->get('template'))
                ->with('offset', request()->get('offset'))
                ->with('bulkedit', false)
                ->with('count', 0);
        }
    }

    /**
     * Returns a view that presents a form to clone an asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function getClone(Asset $asset)
    {
        $this->authorize('create', $asset);
        $cloned = clone $asset;
        $cloned->id = null;
        $cloned->asset_tag = '';
        $cloned->serial = '';
        $cloned->assigned_to = '';
        $cloned->deleted_at = '';

        return view('hardware/edit')
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('statuslabel_types', Helper::statusTypeList())
            ->with('item', $cloned);
    }

    /**
     * Return history import view
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function getImportHistory()
    {
        $this->authorize('admin');

        return view('hardware/history');
    }

    /**
     * Import history
     *
     * This needs a LOT of love. It's done very inelegantly right now, and there are
     * a ton of optimizations that could (and should) be done.
     *
     * Updated to respect checkin dates:
     * No checkin column, assume all items are checked in (todays date)
     * Checkin date in the past, update history.
     * Checkin date in future or empty, check the item out to the user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.3]
     * @return \Illuminate\Contracts\View\View
     */
    public function postImportHistory(Request $request)
    {
        if (! $request->hasFile('user_import_csv')) {
            return back()->with('error', 'No file provided. Please select a file for import and try again. ');
        }

        if (! ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }
        $csv = Reader::createFromPath($request->file('user_import_csv'));
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();
        $isCheckinHeaderExplicit = in_array('checkin date', (array_map('strtolower', $header)));
        try {
            $results = $csv->getRecords();
        } catch (\Exception $e) {
            return back()->with('error', trans('general.error_in_import_file', ['error' => $e->getMessage()]));
        } 
        $item = [];
        $status = [];
        $status['error'] = [];
        $status['success'] = [];
        foreach ($results as $row) {
            if (is_array($row)) {
                $row = array_change_key_case($row, CASE_LOWER);
                $asset_tag = Helper::array_smart_fetch($row, 'asset tag');
                if (! array_key_exists($asset_tag, $item)) {
                    $item[$asset_tag] = [];
                }
                $batch_counter = count($item[$asset_tag]);
                $item[$asset_tag][$batch_counter]['checkout_date'] = Carbon::parse(Helper::array_smart_fetch($row, 'checkout date'))->format('Y-m-d H:i:s');

                if ($isCheckinHeaderExplicit) {
                    //checkin date not empty, assume past transaction or future checkin date (expected)
                    if (! empty(Helper::array_smart_fetch($row, 'checkin date'))) {
                        $item[$asset_tag][$batch_counter]['checkin_date'] = Carbon::parse(Helper::array_smart_fetch($row, 'checkin date'))->format('Y-m-d H:i:s');
                    } else {
                        $item[$asset_tag][$batch_counter]['checkin_date'] = '';
                    }
                } else {
                    //checkin header missing, assume data is unavailable and make checkin date explicit (now) so we don't encounter invalid state.
                    $item[$asset_tag][$batch_counter]['checkin_date'] = Carbon::parse(now())->format('Y-m-d H:i:s');
                }

                $item[$asset_tag][$batch_counter]['asset_tag'] = Helper::array_smart_fetch($row, 'asset tag');
                $item[$asset_tag][$batch_counter]['name'] = Helper::array_smart_fetch($row, 'name');
                $item[$asset_tag][$batch_counter]['email'] = Helper::array_smart_fetch($row, 'email');
                if ($asset = Asset::where('asset_tag', '=', $asset_tag)->first()) {
                    $item[$asset_tag][$batch_counter]['asset_id'] = $asset->id;
                    $base_username = User::generateFormattedNameFromFullName(Setting::getSettings()->username_format, $item[$asset_tag][$batch_counter]['name']);
                    $user = User::where('username', '=', $base_username['username']);
                    $user_query = ' on username '.$base_username['username'];
                    if ($request->input('match_firstnamelastname') == '1') {
                        $firstnamedotlastname = User::generateFormattedNameFromFullName('firstname.lastname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $firstnamedotlastname['username'];
                        $user->orWhere('username', '=', $firstnamedotlastname['username']);
                        $user_query .= ', or on username '.$firstnamedotlastname['username'];
                    }
                    if ($request->input('match_flastname') == '1') {
                        $flastname = User::generateFormattedNameFromFullName('filastname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $flastname['username'];
                        $user->orWhere('username', '=', $flastname['username']);
                        $user_query .= ', or on username '.$flastname['username'];
                    }
                    if ($request->input('match_firstname') == '1') {
                        $firstname = User::generateFormattedNameFromFullName('firstname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $firstname['username'];
                        $user->orWhere('username', '=', $firstname['username']);
                        $user_query .= ', or on username '.$firstname['username'];
                    }
                    if ($request->input('match_email') == '1') {
                        if ($item[$asset_tag][$batch_counter]['name'] == '') {
                            $item[$asset_tag][$batch_counter]['username'][] = $user_email = User::generateEmailFromFullName($item[$asset_tag][$batch_counter]['name']);
                            $user->orWhere('username', '=', $user_email);
                            $user_query .= ', or on username '.$user_email;
                        }
                    }
                    if ($request->input('match_username') == '1') {
                        // Added #8825: add explicit username lookup
                        $raw_username = $item[$asset_tag][$batch_counter]['name'];
                        $user->orWhere('username', '=', $raw_username);
                        $user_query .= ', or on username '.$raw_username;
                    }

                    // A matching user was found
                    if ($user = $user->first()) {
                        //$user is now matched user from db
                        $item[$asset_tag][$batch_counter]['user_id'] = $user->id;

                        Actionlog::firstOrCreate([
                            'item_id' => $asset->id,
                            'item_type' => Asset::class,
                            'created_by' =>  auth()->id(),
                            'note' => 'Checkout imported by '.auth()->user()->present()->fullName().' from history importer',
                            'target_id' => $item[$asset_tag][$batch_counter]['user_id'],
                            'target_type' => User::class,
                            'created_at' =>  $item[$asset_tag][$batch_counter]['checkout_date'],
                            'action_type'   => 'checkout',
                        ]);

                        $checkin_date = $item[$asset_tag][$batch_counter]['checkin_date'];

                        if ($isCheckinHeaderExplicit) {

                            // if checkin date header exists, assume that empty or future date is still checked out
                            // if checkin is before today's date, assume it's checked in and do not assign user ID, if checkin date is in the future or blank, this is the expected checkin date, items are checked out

                            if ((strtotime($checkin_date) > strtotime(Carbon::now())) || (empty($checkin_date)))
                            {
                                //only do this if item is checked out
                                $asset->assigned_to = $user->id;
                                $asset->assigned_type = User::class;
                            }
                        }

                        if (! empty($checkin_date)) {
                            //only make a checkin there is a valid checkin date or we created one on import.
                            Actionlog::firstOrCreate([
                                'item_id' => $item[$asset_tag][$batch_counter]['asset_id'],
                                'item_type' => Asset::class,
                                'created_by' => auth()->id(),
                                'note' => 'Checkin imported by '.auth()->user()->present()->fullName().' from history importer',
                                'target_id' => null,
                                'created_at' => $checkin_date,
                                'action_type' => 'checkin',
                            ]);
                        }

                        if ($asset->save()) {
                            $status['success'][]['asset'][$asset_tag]['msg'] = 'Asset successfully matched for '.Helper::array_smart_fetch($row, 'name').$user_query.' on '.$item[$asset_tag][$batch_counter]['checkout_date'];
                        } else {
                            $status['error'][]['asset'][$asset_tag]['msg'] = 'Asset and user was matched but could not be saved.';
                        }
                    } else {
                        $item[$asset_tag][$batch_counter]['user_id'] = null;
                        $status['error'][]['user'][Helper::array_smart_fetch($row, 'name')]['msg'] = 'User does not exist so no checkin log was created.';
                    }
                } else {
                    $item[$asset_tag][$batch_counter]['asset_id'] = null;
                    $status['error'][]['asset'][$asset_tag]['msg'] = 'Asset does not exist so no match was attempted.';
                }
            }
        }

        return view('hardware/history')->with('status', $status);
    }

    public function sortByName(array $recordA, array $recordB): int
    {
        return strcmp($recordB['Full Name'], $recordA['Full Name']);
    }

    /**
     * Restore a deleted asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function getRestore($assetId = null)
    {
        if ($asset = Asset::withTrashed()->find($assetId)) {
            $this->authorize('delete', $asset);

            if ($asset->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.asset')]));
            }

            if ($asset->restore()) {
                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_assets = Asset::onlyTrashed()->count();
                if ($deleted_assets > 0) {
                    return redirect()->back()->with('success', trans('admin/hardware/message.restore.success'));
                }
                return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.restore.success'));
            }

            // Check validation to make sure we're not restoring an asset with the same asset tag (or unique attribute) as an existing asset
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.asset'), 'error' => $asset->getErrors()->first()]));
        }

        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
    }

    public function quickScan()
    {
        $this->authorize('audit', Asset::class);
        $settings = Setting::getSettings();
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
        return view('hardware/quickscan')->with('next_audit_date', $dt);
    }

    public function quickScanCheckin()
    {
        $this->authorize('checkin', Asset::class);

        return view('hardware/quickscan-checkin')->with('statusLabel_list', Helper::statusLabelList());
    }

    public function audit(Asset $asset)
    {
        $settings = Setting::getSettings();
        $this->authorize('audit', Asset::class);
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
        return view('hardware/audit')->with('asset', $asset)->with('next_audit_date', $dt)->with('locations_list');
    }

    public function dueForAudit()
    {
        $this->authorize('audit', Asset::class);

        return view('hardware/audit-due');
    }

    public function dueForCheckin()
    {
        $this->authorize('checkin', Asset::class);

        return view('hardware/checkin-due');
    }


    public function auditStore(UploadFileRequest $request, Asset $asset)
    {
        $this->authorize('audit', Asset::class);

        $rules = [
            'location_id' => 'exists:locations,id|nullable|numeric',
            'next_audit_date' => 'date|nullable',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()->all()));
        }

        /**
         * Even though we do a save() further down, we don't want to log this as a "normal" asset update,
         * which would trigger the Asset Observer and would log an asset *update* log entry (because the
         * de-normed fields like next_audit_date on the asset itself will change on save()) *in addition* to
         * the audit log entry we're creating through this controller.
         *
         * To prevent this double-logging (one for update and one for audit), we skip the observer and bypass
         * that de-normed update log entry by using unsetEventDispatcher(), BUT invoking unsetEventDispatcher()
         * will bypass normal model-level validation that's usually handled at the observer )
         *
         * We handle validation on the save() by checking if the asset is valid via the ->isValid() method,
         * which manually invokes Watson Validating to make sure the asset's model is valid.
         *
         * @see \App\Observers\AssetObserver::updating()
         */
        $asset->unsetEventDispatcher();

        $asset->next_audit_date = $request->input('next_audit_date');
        $asset->last_audit_date = date('Y-m-d H:i:s');

        // Check to see if they checked the box to update the physical location,
        // not just note it in the audit notes
        if ($request->input('update_location') == '1') {
            $asset->location_id = $request->input('location_id');
        }
        

        /**
         * Invoke Watson Validating to check the asset itself and check to make sure it saved correctly.
         * We have to invoke this manually because of the unsetEventDispatcher() above.)
         */
        if ($asset->isValid() && $asset->save()) {

            $file_name = null;
            // Create the image (if one was chosen.)
            if ($request->hasFile('image')) {
                $file_name = $request->handleFile('private_uploads/audits/', 'audit-'.$asset->id, $request->file('image'));
            }

            $asset->logAudit($request->input('note'), $request->input('location_id'), $file_name);
            return redirect()->route('assets.audit.due')->with('success', trans('admin/hardware/message.audit.success'));
        }

        return redirect()->back()->withInput()->withErrors($asset->getErrors());
    }

    public function getRequestedIndex($user_id = null)
    {
        $this->authorize('index', Asset::class);
        $requestedItems = CheckoutRequest::with('user', 'requestedItem')->whereNull('canceled_at')->with('user', 'requestedItem');

        if ($user_id) {
            $requestedItems->where('user_id', $user_id)->get();
        }

        $requestedItems = $requestedItems->orderBy('created_at', 'desc')->get();

        return view('hardware/requested', compact('requestedItems'));
    }
}
