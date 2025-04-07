<?php

return [

    'undeployable' 		 => '<strong>Warning: </strong> This customer has been marked as currently undeployable. If this status has changed, please update the customer status.',
    'does_not_exist' 	 => 'Customer does not exist.',
    'does_not_exist_var' => 'Customer with tag :Customer_tag not found.',
    'no_tag' 	         => 'No Customer tag provided.',
    'does_not_exist_or_not_requestable' => 'That Customer does not exist or is not requestable.',
    'customer_deleted_warning'      => 'This customer has been deleted. You will have to restore this customer to edit them or assign them new tickets.',
    'assoc_customers'	 	 => 'This Customer is currently checked out to a user and cannot be deleted. Please check the Customer in first, and then try deleting again. ',
    'warning_audit_date_mismatch' 	=> 'This Customer\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_customers_selected' => 'No Customers selected.',
    'customer_has_no_email'         => 'This customer does not have an email address in their profile.',
    'customer_has_no_assets_assigned' => 'No assets currently assigned to customer.',

    'create' => [
        'error'   		=> 'Customer was not created, please try again. :(',
        'success' 		=> 'Customer created successfully. :)',
        'success_linked' => 'Customer with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Customer with tag :links was created successfully.|:count Customers were created succesfully. :links.',
        'partial_failure' => 'An customer was unable to be created. Reason: :failures|:count Customers were unable to be created. Reasons: :failures',
    ],

    'update' => [
        'error'   			=> 'Customer was not updated, please try again',
        'success' 			=> 'Customer updated successfully.',
        'encrypted_warning' => 'Customer updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'No fields were selected, so nothing was updated.',
        'no_Customers_selected'  =>  'No customers were selected, so nothing was updated.',
        'customers_do_not_exist_or_are_invalid' => 'Selected customers cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Customer was not restored, please try again',
        'success' 		=> 'Customer restored successfully.',
        'bulk_success' 		=> 'Customer restored successfully.',
        'nothing_updated'   => 'No customers were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Customer audit unsuccessful: :error ',
        'success' 		=> 'Customer audit successfully logged.',
    ],


    'deletefile' => [
        'error'   => 'File not deleted. Please try again.',
        'success' => 'File successfully deleted.',
    ],

    'upload' => [
        'error'   => 'File(s) not uploaded. Please try again.',
        'success' => 'File(s) successfully uploaded.',
        'nofiles' => 'You did not select any files for upload, or the file you are trying to upload is too large',
        'invalidfiles' => 'One or more of your files is too large or is a filetype that is not allowed. Allowed filetypes are png, gif, jpg, doc, docx, pdf, and txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Some items did not import correctly.',
        'errorDetail'           => 'The following Items were not imported because of errors.',
        'success'               => 'Your file has been imported',
        'file_delete_success'   => 'Your file has been been successfully deleted',
        'file_delete_error'      => 'The file was unable to be deleted',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Are you sure you wish to delete this Customer?',
        'error'   		=> 'There was an issue deleting the Customer. Please try again.',
        'assigned_to_error' => '{1}customer Tag: :customer_tag is currently checked out. Check in this device before deletion.|[2,*]Customer Tags: :customer_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'No customers were selected, so nothing was deleted.',
        'success' 		=> 'The customer was deleted successfully.',
    ],

    'checkout' => [
        'error'   		=> 'Customer was not checked out, please try again',
        'success' 		=> 'Customer checked out successfully.',
        'user_does_not_exist' => 'That customer is invalid. Please try again.',
        'not_available' => 'That customer is not available for checkout!',
        'no_customers_selected' => 'You must select at least one customer from the list',
    ],

    'multi-checkout' => [
        'error'   => 'Customer was not checked out, please try again|Customers were not checked out, please try again',
        'success' => 'Customer checked out successfully.|Customers checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Customer was not checked in, please try again',
        'success' 		=> 'Customer checked in successfully.',
        'customer_does_not_exist' => 'That customer is invalid. Please try again.',
        'already_checked_in'  => 'That customer is already checked in.',

    ],

    'requests' => [
        'error'   		=> 'Customer was not requested, please try again',
        'success' 		=> 'Customer requested successfully.',
        'canceled'      => 'Checkout request successfully canceled',
    ],

];
