<?php

use App\Http\Controllers\customers;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\Customers\CustomersFilesController;
use Illuminate\Support\Facades\Route;

// Customer Management

Route::group(['prefix' => 'customers', 'middleware' => ['auth']], function () {

    

    

    Route::get(
        'export',
        [
            CustomersController::class, 
            'getExportCustomerCsv'
        ]
    )->name('customers.export');

    Route::post(
        '{customer_id}/restore',
        [
            CustomersController::class, 
            'getRestore'
        ]
    )->name('customers.restore.store');

    Route::get(
        '{customerId}/unsuspend',
        [
            CustomersController::class, 
            'getUnsuspend'
        ]
    )->name('unsuspend/customer');

    Route::post(
        '{customer}/upload',
        [
            CustomersFilesController::class, 
            'store'
        ]
    )->name('upload/customer')->withTrashed();

    Route::delete(
        '{customerId}/deletefile/{fileId}',
        [
            CustomersFilesController::class, 
            'destroy'
        ]
    )->name('customerfile.destroy');

    Route::get(
        '{customer}/showfile/{customerId}',
        [
            CustomersFilesController::class, 
            'show'
        ]
    )->name('show/customerfile')->withTrashed();


    Route::get(
        '{customerId}/print',
        [
            CustomersController::class, 
            'printInventory'
        ]
    )->name('customers.print');

    Route::get('audit/due', [CustomersController::class, 'dueForAudit'])
        ->name('customers.audit.due')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('customers.index')
            ->push(trans_choice('general.audit_due_days', Setting::getSettings()->audit_warning_days, ['days' => Setting::getSettings()->audit_warning_days]), route('customers.audit.due'))
        );

    Route::get('audit/{customer}', [CustomersController::class, 'audit'])
        ->name('customers.audit.create')
        ->breadcrumbs(fn (Trail $trail, Customer $customer) =>
        $trail->parent('customers.show', $customer)
            ->push(trans('general.audit'))
        );

    Route::post('audit/{customer}',
        [CustomersController::class, 'auditStore']
    )->name('customers.audit.store');

});

Route::resource('customers', CustomersController::class, [
    'middleware' => ['auth']
])->withTrashed();
