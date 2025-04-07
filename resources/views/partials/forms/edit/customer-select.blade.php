<div id="assigned_customer" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="customers" data-placeholder="{{ trans('general.select_customer') }}" name="{{ $fieldname }}" style="width: 100%" id="assigned_customer_select" aria-label="{{ $fieldname }}"{{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}>
            @if ($customer_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $customer_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Customer::find($customer_id)) ? \App\Models\Customer::find($customer_id)->present()->fullName : '' }}
                </option>
            @else
                <option value=""  role="option">{{ trans('general.select_customer') }}</option>
            @endif
        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Customer::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.show', 'customer') }}' data-toggle="modal"  data-target="#createModal" data-select='assigned_customer_select' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>
