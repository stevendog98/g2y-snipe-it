@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/customers/general.view_customer', ['name' => $customer->present()->fullName()]) }}
@parent
@stop

{{-- Page content --}}
@section('content')



<div class="row">

    @if ($customer->deleted_at!='')
        <div class="col-md-12">
            <div class="callout callout-warning">
                <x-icon type="warning" />
                {{ trans('admin/customers/message.customer_deleted_warning') }}
            </div>
        </div>
    @endif

  <div class="col-md-12">




    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs hidden-print">

        <li class="active">
          <a href="#details" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="info-circle" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('admin/customers/general.info') }}</span>
          </a>
        </li>

        <li>
          <a href="#asset" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="assets" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.assets') }}
              {!! ($customer->assets()->AssetsForShow()->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($customer->assets()->AssetsForShow()->withoutTrashed()->count()).'</badge>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#licenses" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="licenses" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.licenses') }}
              {!! ($customer->licenses->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($customer->licenses->count()).'</badge>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#accessories" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="accessories" class="fa-2x" />
            </span> 
            <span class="hidden-xs hidden-sm">{{ trans('general.accessories') }}
              {!! ($customer->accessories->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($customer->accessories->count()).'</badge>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#consumables" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="consumables" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.consumables') }}
              {!! ($customer->consumables->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($customer->consumables->count()).'</badge>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#files" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="files" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
              {!! ($customer->uploads->count() > 0 ) ? '<badge class="badge badge-secondary">'.number_format($customer->uploads->count()).'</badge>' : '' !!}
            </span>
          </a>
        </li>

        <li>
          <a href="#history" data-toggle="tab">
            <span class="hidden-lg hidden-md">
                <x-icon type="history" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('general.history') }}</span>
          </a>
        </li>



      @can('update', $customer)
          <li class="pull-right">
              <a href="#" data-toggle="modal" data-target="#uploadFileModal">
              <span class="hidden-xs"><x-icon type="paperclip" /></span>
              <span class="hidden-lg hidden-md hidden-xl"><x-icon type="paperclip" class="fa-2x" /></span>
              <span class="hidden-xs hidden-sm">{{ trans('button.upload') }}</span>
              </a>
          </li>
        @endcan
      </ul>

      <div class="tab-content">
        <div class="tab-pane active" id="details">
          <div class="row">

        <div class="info-stack-container">
            <!-- Start button column -->
            <div class="col-md-3 col-xs-12 col-sm-push-9 info-stack">

              @can('update', $customer)
                <div class="col-md-12">
                  <a href="{{ ($customer->deleted_at=='') ? route('customers.edit', $customer->id) : '#' }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social hidden-print{{ ($customer->deleted_at!='') ? ' disabled' : '' }}">
                      <x-icon type="edit" />
                      {{ trans('admin/customers/general.edit') }}
                  </a>
                </div>
              @endcan

                @can('view', $customer)
                <div class="col-md-12" style="padding-top: 5px;">
                @if($customer->allAssignedCount() != '0') 
                  <a href="{{ route('customers.print', $customer->id) }}" style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print" target="_blank" rel="noopener">
                      <x-icon type="print" />
                      {{ trans('admin/customers/general.print_assigned') }}
                  </a>
                  @else
                  <button style="width: 100%;" class="btn btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/users/message.user_has_no_assets_assigned') }}">
                      <x-icon type="print" />
                      {{ trans('admin/customers/general.print_assigned') }}</button>
                @endif
                </div>
                @endcan

                @can('view', $customer)
                  <div class="col-md-12" style="padding-top: 5px;">
                  @if(!empty($customer->email) && ($customer->allAssignedCount() != '0'))
                    <form action="{{ route('users.email',['userId'=> $customer->id]) }}" method="POST">
                      {{ csrf_field() }}
                      <button class="btn-block btn btn-sm btn-primary btn-social hidden-print" rel="noopener">
                          <x-icon type="email" />
                          {{ trans('admin/customers/general.email_assigned') }}
                      </button>
                    </form>
                  @elseif(!empty($customer->email) && ($customer->allAssignedCount() == '0'))
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/customer/message.customer_has_no_assets_assigned') }}">
                          <x-icon type="email" />
                          {{ trans('admin/customers/general.email_assigned') }}
                      </button>
                  @else
                      <button class="btn btn-block btn-sm btn-primary btn-social hidden-print" rel="noopener" disabled title="{{ trans('admin/customers/message.customer_has_no_email') }}">
                          <x-icon type="email" />
                          {{ trans('admin/customers/general.email_assigned') }}
                      </button>
                  @endif
                  </div>
                @endcan


            @can('delete', $customer)
                  @if ($customer->deleted_at=='')
                    <div class="col-md-12" style="padding-top: 30px;">
                        @if ($customer->isDeletable())
                            <a href="#" class="btn-block delete-asset btn btn-sm btn-danger btn-social hidden-print" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $customer->present()->fullName]) }}" data-target="#dataConfirmModal">
                                <x-icon type="delete" />
                                {{ trans('button.delete')}}
                            </a>
                            @else
                            <button class="btn-block btn btn-sm btn-danger btn-social hidden-print disabled">
                                <x-icon type="delete" />
                                {{ trans('button.delete')}}
                            </button>
                        @endif
                    </div>
                    <div class="col-md-12" style="padding-top: 5px;">
                      <form action="{{ route('users/bulkedit') }}" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="bulk_actions" value="delete" />

                        <input type="hidden" name="ids[{{ $customer->id }}]" value="{{ $customer->id }}" />
                        <button class="btn btn-block btn-sm btn-danger btn-social hidden-print">
                            <x-icon type="checkin-and-delete" />
                            {{ trans('button.checkin_and_delete') }}
                        </button>
                      </form>
                    </div>
                  @else
                    <div class="col-md-12" style="padding-top: 5px;">
                        <form method="POST" action="{{ route('customers.restore.store', $customer->id) }}">
                            @csrf
                            <button class="btn btn-block btn-sm btn-warning btn-social hidden-print">
                                <x-icon type="restore" />
                                {{ trans('button.restore') }}
                            </button>
                        </form>
                    </div>
                  @endif
                @endcan
                <br><br>
            </div>
 
            <!-- End button column -->
          
            <div class="col-md-9 col-xs-12 col-sm-pull-3 info-stack">

               <div class="row-new-striped">
                
                  <div class="row">
                    <!-- name -->
    
                      <div class="col-md-3">
                        {{ trans('admin/customers/table.name') }}
                      </div>
                      <div class="col-md-9">
                        {{ $customer->present()->fullName() }}
                      </div>

                  </div>

               

                   <!-- company -->
                    @if (!is_null($customer->company))
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('general.company') }}
                      </div>
                      <div class="col-md-9">
                          @can('view', 'App\Models\Company')
                            <a href="{{ route('companies.show', $customer->company->id) }}">
                                {{ $customer->company->name }}
                            </a>
                              @else
                              {{ $customer->company->name }}
                            @endcan
                      </div>

                    </div>
                   
                    @endif

                    <!-- address -->
                    @if (($customer->address) || ($customer->city) || ($customer->state) || ($customer->country))
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.address') }}
                      </div>
                      <div class="col-md-9">
                      
                          @if ($customer->address)
                          {{ $customer->address }} <br>
                          @endif
                          @if ($customer->city)
                            {{ $customer->city }}
                          @endif
                          @if ($customer->state)
                            {{ $customer->state }}
                          @endif
                          @if ($customer->country)
                            {{ $customer->country }}
                          @endif
                          @if ($customer->zip)
                              {{ $customer->zip }}
                          @endif

                      </div>
                    </div>
                    @endif


                     <!-- groups -->
                     <div class="row">
                        <div class="col-md-3">
                          {{ trans('general.groups') }}
                        </div>
                        <div class="col-md-9">
                          @if ($customer->groups->count() > 0)
                            @foreach ($customer->groups as $group)

                              @can('superadmin')
                                  <a href="{{ route('groups.show', $group->id) }}" class="label label-default">{{ $group->name }}</a>
                              @else
                              {{ $group->name }}
                              @endcan

                            @endforeach
                          @else
                              --
                          @endif
                        </div>
                      </div>

                   <!-- start date -->
                   @if ($customer->start_date)
                       <div class="row">
                           <div class="col-md-3">
                               {{ trans('general.start_date') }}
                           </div>
                           <div class="col-md-9">
                               {{ \App\Helpers\Helper::getFormattedDateObject($customer->start_date, 'date', false) }}
                           </div>
                       </div>
                   @endif

                   <!-- end date -->
                   @if ($customer->end_date)
                       <div class="row">
                           <div class="col-md-3">
                               {{ trans('general.end_date') }}
                           </div>
                           <div class="col-md-9">
                               {{ \App\Helpers\Helper::getFormattedDateObject($customer->end_date, 'date', false) }}
                           </div>
                       </div>
                   @endif

                    @if ($customer->jobtitle)
                     <!-- jobtitle -->
                     <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.job') }}
                        </div>
                        <div class="col-md-9">
                          {{ $customer->jobtitle }}
                        </div>

                      </div>
                    @endif

                    @if ($customer->employee_num)
                      <!-- employee_num -->
                      <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.employee_num') }}
                        </div>
                        <div class="col-md-9">
                          {{ $customer->employee_num }}
                        </div>
                        
                      </div>
                    @endif

                    @if ($customer->manager)
                      <!-- manager -->
                      <div class="row">

                        <div class="col-md-3">
                          {{ trans('admin/users/table.manager') }}
                        </div>
                        <div class="col-md-9">
                          <a href="{{ route('users.show', $customer->manager->id) }}">
                            {{ $customer->manager->getFullNameAttribute() }}
                          </a>
                        </div>

                      </div>

                    @endif

                    
                    @if ($customer->email)
                    <!-- email -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.email') }}
                      </div>
                      <div class="col-md-9">
                        <a href="mailto:{{ $customer->email }}" data-tooltip="true" title="{{ trans('general.send_email') }}">
                            <x-icon type="email" />
                            {{ $customer->email }}</a>
                      </div>
                    </div>
                    @endif

                    @if ($customer->website)
                     <!-- website -->
                     <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.website') }}
                      </div>
                      <div class="col-md-9">
                          <a href="{{ $customer->website }}" target="_blank"><x-icon type="external-link" /> {{ $customer->website }}</a>
                      </div>
                    </div>
                    @endif

                    @if ($customer->phone)
                      <!-- phone -->
                      <div class="row">
                        <div class="col-md-3">
                          {{ trans('admin/users/table.phone') }}
                        </div>
                        <div class="col-md-9">
                          <a href="tel:{{ $customer->phone }}" data-tooltip="true" title="{{ trans('general.call') }}">
                              <x-icon type="phone" />
                              {{ $customer->phone }}</a>
                        </div>
                      </div>
                    @endif

                    @if ($customer->userloc)
                     <!-- location -->
                     <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/users/table.location') }}
                      </div>
                      <div class="col-md-9">
                        {{ link_to_route('locations.show', $customer->userloc->name, [$customer->userloc->id]) }}
                      </div>
                    </div>
                    @endif

                    <!-- last login -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.last_login') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($customer->last_login, 'datetime', false) }}
                      </div>
                    </div>


                    @if ($customer->department)
                    <!-- empty -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.department') }}
                      </div>
                      <div class="col-md-9">
                        <a href="{{ route('departments.show', $customer->department) }}">
                          {{ $customer->department->name }}
                        </a>
                      </div>
                    </div>
                    @endif

                    @if ($customer->created_at)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.created_at') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($customer->created_at, 'datetime')['formatted']}}

                          @if ($customer->createdBy)
                              -
                              @if ($customer->createdBy->deleted_at=='')
                                  <a href="{{ route('users.show', ['user' => $customer->created_by]) }}">{{ $customer->createdBy->present()->fullName }}</a>
                              @else
                                  <del>{{ $customer->createdBy->present()->fullName }}</del>
                              @endif


                          @endif
                      </div>
                    </div>
                    @endif

                   <!-- auto assign license -->
                   <div class="row">
                       <div class="col-md-3">
                           {{ trans('general.autoassign_licenses') }}
                       </div>
                       <div class="col-md-9">
                           @if ($customer->autoassign_licenses == '1')
                               <x-icon type="checkmark" class="fa-fw text-success" />
                               {{ trans('general.yes') }}
                           @else
                               <x-icon type="x" class="fa-fw text-danger" />
                               {{ trans('general.no') }}
                           @endif
                       </div>
                   </div>

                    @if ($customer->notes)
                     <!-- empty -->
                     <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/customers/table.notes') }}
                      </div>
                      <div class="col-md-9">
                          {!! nl2br(Helper::parseEscapedMarkedownInline($customer->notes)) !!}
                      </div>

                    </div>
                    @endif
                   @if($customer->getCustomerTotalCost()->total_customer_cost > 0)
                   <div class="row">
                       <div class="col-md-3">
                           {{ trans('admin/customers/table.total_assets_cost') }}
                       </div>
                       <div class="col-md-9">
                           {{Helper::formatCurrencyOutput($customer->getCustomerTotalCost()->total_customer_cost)}}

                           <a id="optional_info" class="text-primary">
                               <x-icon type="caret-right" id="optional_info_icon" />
                               <strong>{{ trans('admin/hardware/form.optional_infos') }}</strong>
                           </a>
                       </div>
                           <div id="optional_details" class="col-md-12" style="display:none">
                               <div class="col-md-3" style="border-top:none;"></div>
                               <div class="col-md-9" style="border-top:none;">
                               {{trans('general.assets').': '. Helper::formatCurrencyOutput($customer->getCustomerTotalCost()->asset_cost)}}<br>
                               {{trans('general.licenses').': '. Helper::formatCurrencyOutput($customer->getCustomerTotalCost()->license_cost)}}<br>
                               {{trans('general.accessories').': '.Helper::formatCurrencyOutput($customer->getCustomerTotalCost()->accessory_cost)}}<br>
                               </div>
                           </div>
                   </div><!--/.row-->
                   @endif
                  </div> <!--/end striped container-->
                </div> <!-- end col-md-9 -->
             </div><!-- end info-stack-container-->
          </div> <!--/.row-->
        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="asset">
          <!-- checked out assets table -->

            @include('partials.asset-bulk-actions')

            <div class="table table-responsive">

            <table
                    data-click-to-select="true"
                    data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                    data-cookie-id-table="customerAssetsListingTable"
                    data-pagination="true"
                    data-id-table="customerAssetsListingTable"
                    data-search="true"
                    data-side-pagination="server"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-footer="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    data-sort-name="name"
                    data-toolbar="#assetsBulkEditToolbar"
                    data-bulk-button-id="#bulkAssetEditButton"
                    data-bulk-form-id="#assetsBulkForm"
                    id="customerAssetsListingTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.assets.index',['assigned_to' => e($customer->id), 'assigned_type' => 'App\Models\Customer']) }}"
                    data-export-options='{
                "fileName": "export-{{ str_slug($customer->present()->fullName()) }}-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
            </table>
          </div>
        </div><!-- /asset -->

        <div class="tab-pane" id="licenses">


          <div class="table-responsive">
            <table
                    data-cookie-id-table="customerLicenseTable"
                    data-id-table="customerLicenseTable"
                    id="customerLicenseTable"
                    data-search="true"
                    data-pagination="true"
                    data-side-pagination="client"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-footer="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-license-{{ str_slug($customer->fullName()) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>

              <thead>
                <tr>
                  <th>{{ trans('general.name') }}</th>
                  <th>{{ trans('admin/licenses/form.license_key') }}</th>
                  <th data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  <th>{{ trans('admin/licenses/form.purchase_order') }}</th>
                  <th>{{ trans('general.order_number') }}</th>
                  <th class="col-md-1 hidden-print">{{ trans('general.action') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($customer->licenses as $license)
                <tr>
                  <td class="col-md-4">
                    {!! $license->present()->nameUrl() !!}
                  </td>
                  <td class="col-md-4">
                    @can('viewKeys', $license)
                          <code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-{{ $license->id }}" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-{{ $license->id }}">{{ $license->serial }}</span></span></code>
                    @else
                      ------------
                    @endcan
                  </td>
                  <td class="col-md-2">
                    {{ Helper::formatCurrencyOutput($license->purchase_cost) }}
                  </td>
                  <td>
                    {{ $license->purchase_order }}
                  </td>
                  <td>
                    {{ $license->order_number }}
                  </td>
                  <td class="hidden-print col-md-2">
                    @can('update', $license)
                      <a href="{{ route('licenses.checkin', $license->pivot->id, ['backto'=>'customer']) }}" class="btn btn-primary btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                     @endcan
                  </td>
                </tr>
                @endforeach
              </tbody>
          </table>
          </div>
        </div><!-- /licenses-tab -->

        <div class="tab-pane" id="accessories">
          <div class="table-responsive">
            <table
                    data-cookie-id-table="customerAccessoryTable"
                    data-id-table="customerAccessoryTable"
                    id="customerAccessoryTable"
                    data-search="true"
                    data-pagination="true"
                    data-side-pagination="client"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-footer="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-accessory-{{ str_slug($customer->fullName()) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
              <thead>
                <tr>
                    <th class="col-md-1">{{ trans('general.id') }}</th>
                    <th class="col-md-4">{{ trans('general.name') }}</th>
                    <th class-="col-md-5" data-fieldname="note">{{ trans('general.notes') }}</th>
                    <th class="col-md-1" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                    <th class="col-md-1 hidden-print">{{ trans('general.action') }}</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($customer->accessories as $accessory)
                  <tr>
                      <td>{{ $accessory->pivot->id }}</td>
                      <td>{!!$accessory->present()->nameUrl()!!}</td>
                      <td>{!! $accessory->pivot->note !!}</td>
                      <td>
                      {!! Helper::formatCurrencyOutput($accessory->purchase_cost) !!}
                      </td>
                    <td class="hidden-print">
                      @can('checkin', $accessory)
                        <a href="{{ route('accessories.checkin.show', array('accessoryID'=> $accessory->pivot->id, 'backto'=>'user')) }}" class="btn btn-primary btn-sm hidden-print">{{ trans('general.checkin') }}</a>
                      @endcan
                    </td>
                  </tr>
                  @endforeach
              </tbody>
            </table>
          </div>
        </div><!-- /accessories-tab -->

        <div class="tab-pane" id="consumables">
          <div class="table-responsive">
            <table
                    data-cookie-id-table="customerConsumableTable"
                    data-id-table="customerConsumableTable"
                    id="customerConsumableTable"
                    data-search="true"
                    data-pagination="true"
                    data-side-pagination="client"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-footer="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    data-sort-name="name"
                    class="table table-striped snipe-table table-hover"
                    data-export-options='{
                    "fileName": "export-consumable-{{ str_slug($customer->fullName()) }}-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","delete","download","icon"]
                    }'>
              <thead>
                <tr>
                  <th class="col-md-3">{{ trans('general.name') }}</th>
                  <th class="col-md-2" data-footer-formatter="sumFormatter" data-fieldname="purchase_cost">{{ trans('general.purchase_cost') }}</th>
                  <th class="col-md-2">{{ trans('general.date') }}</th>
                    <th class="col-md-5">{{ trans('general.notes') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($customer->consumables as $consumable)
                <tr>
                  <td>{!! $consumable->present()->nameUrl() !!}</td>
                  <td>
                    {!! Helper::formatCurrencyOutput($consumable->purchase_cost) !!}
                  </td>
                  <td>{{ Helper::getFormattedDateObject($consumable->pivot->created_at, 'datetime',  false) }}</td>
                  <td>{{ $consumable->pivot->note }}</td>
                </tr>
                @endforeach
              </tbody>
          </table>
          </div>
        </div><!-- /consumables-tab -->

        <div class="tab-pane" id="files">
          <div class="row">

            <div class="col-md-12 col-sm-12">
                <x-filestable
                        filepath="private_uploads/customers/"
                        showfile_routename="show/customerfile"
                        deletefile_routename="customerfile.destroy"
                        :object="$customer" />
            </div>
          </div> <!--/ROW-->
        </div><!--/FILES-->

        <div class="tab-pane" id="history">
          <div class="table-responsive">


            <table
                    data-click-to-select="true"
                    data-cookie-id-table="customersHistoryTable"
                    data-pagination="true"
                    data-id-table="customersHistoryTable"
                    data-search="true"
                    data-side-pagination="server"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-refresh="true"
                    data-sort-order="desc"
                    id="customersHistoryTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.activity.index', ['target_id' => $customer->id, 'target_type' => 'customer']) }}"
                    data-export-options='{
                "fileName": "export-{{ str_slug($customer->present()->fullName ) }}-history-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              <thead>
              <tr>
                <th data-field="icon" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter">Icon</th>
                <th data-field="created_at" data-formatter="dateDisplayFormatter" data-sortable="true">{{ trans('general.date') }}</th>
                  <th data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                  <th data-field="action_type">{{ trans('general.action') }}</th>
                  <th data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                  <th data-field="note">{{ trans('general.notes') }}</th>
                  @if  ($snipeSettings->require_accept_signature=='1')
                      <th data-field="signature_file" data-visible="false"  data-formatter="imageFormatter">{{ trans('general.signature') }}</th>
                  @endif
                  <th data-field="item.serial" data-visible="false">{{ trans('admin/hardware/table.serial') }}</th>
                  <th data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.admin') }}</th>
                  <th data-field="remote_ip" data-visible="false" data-sortable="true">{{ trans('admin/settings/general.login_ip') }}</th>
                  <th data-field="user_agent" data-visible="false" data-sortable="true">{{ trans('admin/settings/general.login_user_agent') }}</th>
                  <th data-field="action_source" data-visible="false" data-sortable="true">{{ trans('general.action_source') }}</th>

              </tr>
              </thead>
            </table>

          </div>
        </div><!-- /.tab-pane -->

        <div class="tab-pane" id="managed-locations">

            @include('partials.locations-bulk-actions')


            <table
                    data-columns="{{ \App\Presenters\LocationPresenter::dataTableLayout() }}"
                    data-cookie-id-table="locationTable"
                    data-click-to-select="true"
                    data-pagination="true"
                    data-id-table="locationTable"
                    data-toolbar="#locationsBulkEditToolbar"
                    data-bulk-button-id="#bulkLocationsEditButton"
                    data-bulk-form-id="#locationsBulkForm"
                    data-search="true"
                    data-side-pagination="server"
                    data-show-columns="true"
                    data-show-fullscreen="true"
                    data-show-export="true"
                    data-show-refresh="true"
                    data-sort-order="asc"
                    id="locationTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.locations.index', ['manager_id' => $customer->id]) }}"
                    data-export-options='{
              "fileName": "export-locations-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>

          </div>
        </div><!-- /consumables-tab -->
      </div><!-- /.tab-content -->
    </div><!-- nav-tabs-custom -->
  </div>

  @can('update', \App\Models\Customer::class)
    @include ('modals.upload-file', ['item_type' => 'customer', 'item_id' => $customer->id])
  @endcan



  @stop

@section('moar_scripts')
  @include ('partials.bootstrap-table', ['simple_view' => true])
<script nonce="{{ csrf_token() }}">
$(function () {

$('#dataConfirmModal').on('show.bs.modal', function (event) {
    var content = $(event.relatedTarget).data('content');
    var title = $(event.relatedTarget).data('title');
    $(this).find(".modal-body").text(content);
    $(this).find(".modal-header").text(title);
 });


  $("#two_factor_reset").click(function(){
    $("#two_factor_resetrow").removeClass('success');
    $("#two_factor_resetrow").removeClass('danger');
    $("#two_factor_resetstatus").html('');
    $("#two_factor_reseticon").html('<x-icon type="spinner" />');
    $.ajax({
      url: '{{ route('api.users.two_factor_reset', ['id'=> $customer->id]) }}',
      type: 'POST',
      data: {},
      headers: {
        "X-Requested-With": 'XMLHttpRequest',
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
      },
      dataType: 'json',

      success: function (data) {
        $("#two_factor_reset_toggle").html('').html('<span class="text-danger"><x-icon type="x" /> {{ trans('general.no') }}</span>');
        $("#two_factor_reseticon").html('');
        $("#two_factor_resetstatus").html('<span class="text-success"><x-icon type="checkmark" class="fa-2x" /> ' + data.message + '</span>');

      },

      error: function (data) {
        $("#two_factor_reseticon").html('');
        $("#two_factor_reseticon").html('<x-icon type="warning" class="text-danger" />');
        $('#two_factor_resetstatus').text(data.message);
      }


    });
  });


    // binds to onchange event of your input field
    var uploadedFileSize = 0;
    $('#fileupload').bind('change', function() {
      uploadedFileSize = this.files[0].size;
      $('#progress-container').css('visibility', 'visible');
    });

    $('#fileupload').fileupload({
        //maxChunkSize: 100000,
        dataType: 'json',
        formData:{
        _token:'{{ csrf_token() }}',
        notes: $('#notes').val(),
        },

        progress: function (e, data) {
            //var overallProgress = $('#fileupload').fileupload('progress');
            //var activeUploads = $('#fileupload').fileupload('active');
            var progress = parseInt((data.loaded / uploadedFileSize) * 100, 10);
            $('.progress-bar').addClass('progress-bar-warning').css('width',progress + '%');
            $('#progress-bar-text').html(progress + '%');
            //console.dir(overallProgress);
        },

        done: function (e, data) {
            console.dir(data);
            // We use this instead of the fail option, since our API
            // returns a 200 OK status which always shows as "success"

            if (data && data.jqXHR && data.jqXHR.responseJSON && data.jqXHR.responseJSON.status === "error") {
                var errorMessage = data.jqXHR.responseJSON.messages["file.0"];
                $('#progress-bar-text').html(errorMessage[0]);
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-danger').css('width','100%');
                $('.progress-checkmark').fadeIn('fast').html('<x-icon type="xt" class="fa-3x text-danger" />');
            } else {
                $('.progress-bar').removeClass('progress-bar-warning').addClass('progress-bar-success').css('width','100%');
                $('.progress-checkmark').fadeIn('fast');
                $('#progress-container').delay(950).css('visibility', 'visible');
                $('.progress-bar-text').html('Finished!');
                $('.progress-checkmark').fadeIn('fast').html('<x-icon type="checkmark" class="fa-3x text-success" />');
                $.each(data.result, function (index, file) {
                    $('<tr><td>' + file.note + '</td><td>' + file.filename + '</td></tr>').prependTo("#files-table > tbody");
                });
            }
            $('#progress').removeClass('active');


        }
    });
    $("#optional_info").on("click",function(){
        $('#optional_details').fadeToggle(100);
        $('#optional_info_icon').toggleClass('fa-caret-right fa-caret-down');
        var optional_info_open = $('#optional_info_icon').hasClass('fa-caret-down');
        document.cookie = "optional_info_open="+optional_info_open+'; path=/';
    });
});
</script>


@stop
