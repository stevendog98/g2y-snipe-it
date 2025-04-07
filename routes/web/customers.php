<?php

use App\Http\Controllers\customers;
use App\Http\Controllers\Customers\CustomersFilesController;
use Illuminate\Support\Facades\Route;

// Customer Management

Route::group(['prefix' => 'customers', 'middleware' => ['auth']], function () {

    

    

    Route::get(
        'export',
        [
            Customers\CustomersController::class, 
            'getExportCustomerCsv'
        ]
    )->name('customers.export');

    Route::post(
        '{customer_id}/restore',
        [
            Customers\CustomersController::class, 
            'getRestore'
        ]
    )->name('customers.restore.store');

    Route::get(
        '{customerId}/unsuspend',
        [
            Customers\CustomersController::class, 
            'getUnsuspend'
        ]
    )->name('unsuspend/customer');

    Route::post(
        '{customer}/upload',
        [
            Customers\CustomersFilesController::class, 
            'store'
        ]
    )->name('upload/customer')->withTrashed();

    Route::delete(
        '{customerId}/deletefile/{fileId}',
        [
            Customers\CustomersFilesController::class, 
            'destroy'
        ]
    )->name('customerfile.destroy');

    Route::get(
        '{customer}/showfile/{customerId}',
        [
            Customers\CustomersFilesController::class, 
            'show'
        ]
    )->name('show/customerfile')->withTrashed();


    Route::get(
        '{customerId}/print',
        [
            Customers\CustomersController::class, 
            'printInventory'
        ]
    )->name('customers.print');

});

Route::resource('customers', Customers\CustomersController::class, [
    'middleware' => ['auth']
])->withTrashed();
