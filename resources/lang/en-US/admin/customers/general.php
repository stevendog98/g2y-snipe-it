<?php

return [
    'about_customers_title'           => 'About Customers',
    'about_customers_text'            => 'Customers are items tracked by serial number or customer tag.  They tend to be higher value items where identifying a specific item matters.',
    'archived'  				=> 'Archived',
    'customer'  					=> 'Customer',
    'bulk_checkout'             => 'Bulk Checkout',
    'bulk_checkin'              => 'Bulk Checkin',
    'checkin'  					=> 'Checkin customer',
    'checkout'  				=> 'Checkout customer',
    'clone'  					=> 'Clone customer',
    'deployable'  				=> 'Deployable',
    'deleted'  					=> 'This customer has been deleted.',
    'delete_confirm'            => 'Are you sure you want to delete this customer?',
    'edit'  					=> 'Edit customer',
    'model_deleted'  			=> 'This customers model has been deleted. You must restore the model before you can restore the customer.',
    'model_invalid'             => 'This model for this customer is invalid.',
    'model_invalid_fix'         => 'The customer must be updated use a valid customer model before attempting to check it in or out, or to audit it.',
    'requestable'               => 'Requestable',
    'requested'				    => 'Requested',
    'not_requestable'           => 'Not Requestable',
    'requestable_status_warning' => 'Do not change requestable status',
    'restore'  					=> 'Restore customer',
    'pending'  					=> 'Pending',
    'undeployable'  			=> 'Undeployable',
    'undeployable_tooltip'  	=> 'This customer has a status label that is undeployable and cannot be checked out at this time.',
    'view'  					=> 'View customer',
    'view_customer'             => 'View customer :name',
    'info'				        => 'Info',
    'email_assigned'            => 'Email List of All Assigned',
    'csv_error' => 'You have an error in your CSV file:',
    'import_text' => '<p>Upload a CSV that contains customer history. The customers and ticket MUST already exist in the system, or they will be skipped. Matching customers for history import happens against the customer tag. We will try to find a matching user based on the user\'s name you provide, and the criteria you select below. If you do not select any criteria below, it will simply try to match on the username format you configured in the <code>Admin &gt; General Settings</code>.</p><p>Fields included in the CSV must match the headers: <strong>Customer Tag, Name, Checkout Date, Checkin Date</strong>. Any additional fields will be ignored. </p><p>Checkin Date: blank or future checkin dates will checkout items to associated user.  Excluding the Checkin Date column will create a checkin date with todays date.</p>',
    'csv_import_match_f-l' => 'Try to match users by <strong>firstname.lastname</strong> (<code>jane.smith</code>) format',
    'csv_import_match_initial_last' => 'Try to match users by <strong>first initial last name</strong> (<code>jsmith</code>) format',
    'csv_import_match_first' => 'Try to match users by <strong>first name</strong> (<code>jane</code>) format',
    'csv_import_match_email' => 'Try to match users by <strong>email</strong> as username',
    'csv_import_match_username' => 'Try to match users by <strong>username</strong>',
    'error_messages' => 'Error messages:',
    'success_messages' => 'Success messages:',
    'alert_details' => 'Please see below for details.',
    'custom_export' => 'Custom Export',
    'mfg_warranty_lookup' => ':manufacturer Warranty Status Lookup',
    'user_department' => 'User Department',
    'print_assigned'    => 'Print All Assigned',0
];
