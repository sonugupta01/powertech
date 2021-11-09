<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'HomeController@index'); //home page
Route::get('/cronJob', 'HomeController@cronJob'); //home page
Route::get('/sendmail', 'HomeController@sendmail'); //testing function
Route::get('/admin/login', 'HomeController@login'); //admin login
Route::get('/send_mail_of_late_commer/{id}', 'HomeController@send_mail_of_late_commer');
Route::get('/login', function () {
  return redirect('admin/login'); //redirect to admin login url
});
Route::get('/treatment/{id}', 'HomeController@treatment');
Route::get('/privacy_policy', function () {
  return view('auto-solution-privacy-policy'); //redirect to admin login url
});
Route::post('/checklogin', 'HomeController@checklogin'); //check user authentication
Route::get('/logout', 'HomeController@logout'); //logout
Route::get('/changepassword', 'HomeController@changepassword')->middleware('auth'); //change password
Route::post('/updatePassword', 'HomeController@updatePassword')->middleware('auth'); //update password
Route::post('/getDistrict', 'HomeController@getDistrict'); //get districts through ajax
Route::post('/getModels', 'HomeController@getModels'); //get models through ajax
Route::post('/getOEMtemplates', 'HomeController@getOEMtemplates'); //get models through ajax
Route::post('/getOemModels', 'HomeController@getOemModels'); //get OEM models through ajax
Route::post('/getAdvisors', 'HomeController@getAdvisors'); //get advisors through ajax
Route::post('/getTreatments', 'HomeController@getTreatments'); //get treatments through ajax
Route::post('/getTreatmentPrice', 'HomeController@getTreatmentPrice'); //get treatment price through ajax
Route::post('/getDealerPrice', 'HomeController@getDealerPrice'); //get Dealer price through ajax
Route::post('/getJobId', 'HomeController@getJobId'); //get Job id through ajax
Auth::routes();
/* Admin Panel */
Route::group(['prefix' => 'admin', 'as' => 'admin::', 'middleware' => ['web', 'admin']], function () {
  //Route::get('/','AdminController@index');
  Route::get('/', 'AdminController@dashboard');
  Route::get('/downloadDashboard', 'AdminController@downloadDashboard');
  Route::post('/addServiceLoad', 'AdminController@addServiceLoad');
  // Dealer Module
  Route::get('/dealer_management', 'AdminController@dealer_management');
  Route::get('/addDealer', 'AdminController@addDealer');
  Route::post('/getAuthorities', 'AdminController@getAuthorities');
  Route::post('/insertDealer', 'AdminController@insertDealer');
  Route::post('/getlatlong', 'AdminController@getlatlong');
  Route::get('/editDealer/{id}', 'AdminController@editDealer');
  Route::post('/updateDealer', 'AdminController@updateDealer');
  Route::get('/statusDealer/{status}/{id}', 'AdminController@statusDealer');
  Route::get('/dealers_SalesExecutivesListing/{dealer_id}', 'AdminController@dealers_SalesExecutivesListing');
  Route::get('/dealers_ASM/{dealer_id}', 'AdminController@dealers_ASM');
  Route::get('/downloadDealers', 'AdminController@downloadDealers');
  Route::get('/downloadDealerInfo/{dealer_id}', 'AdminController@downloadDealerInfo');

  // Contact module
  Route::get('/contacts/{id}', 'AdminController@contacts');
  Route::get('/addContact/{dealer_id}', 'AdminController@addContact');
  Route::post('/insertContact', 'AdminController@insertContact');
  Route::get('/editContact/{dealer_id}/{id}', 'AdminController@editContact');
  Route::post('/updateContact', 'AdminController@updateContact');
  Route::get('/statusContact/{status}/{dealer_id}/{id}', 'AdminController@statusContact');

  // Advisor module
  Route::get('/advisors/{id}', 'AdminController@advisors');
  Route::post('/addAdvisorIncentive/{dealer_id}', 'AdminController@addAdvisorIncentive');
  Route::get('/addAdvisor/{dealer_id}', 'AdminController@addAdvisor');
  Route::post('/insertAdvisor', 'AdminController@insertAdvisor');
  Route::get('/editAdvisor/{dealer_id}/{id}', 'AdminController@editAdvisor');
  Route::post('/updateAdvisor', 'AdminController@updateAdvisor');
  Route::get('/statusAdvisor/{status}/{dealer_id}/{id}', 'AdminController@statusAdvisor');

  // Dealer share module
  Route::get('/dealer_percentage_history/{dealer_id}', 'AdminController@dealerPercentageHistory');
  Route::get('/addDealerPercentage/{dealer_id}', 'AdminController@addDealerPercentage');
  Route::post('/insertDealerPercentage', 'AdminController@insertDealerPercentage');

  // Dealer advisor share module
  Route::get('/advisor_percentage_history/{dealer_id}/{advisor_id}', 'AdminController@advisorPercentageHistory');
  Route::get('/addAdvisorPercentage/{dealer_id}/{advisor_id}', 'AdminController@addAdvisorPercentage');
  Route::post('/insertAdvisorPercentage', 'AdminController@insertAdvisorPercentage');

  // Template module
  Route::get('/dealerTemplates/{id}', 'AdminController@dealerTemplates');
  Route::get('/addDealerTemplate/{dealer_id}', 'AdminController@addDealerTemplate');
  Route::post('/insertDealerTemplate', 'AdminController@insertDealerTemplate');
  Route::get('/editDealerTemplate/{dealer_id}/{id}', 'AdminController@editDealerTemplate');
  Route::post('/updateDealerTemplate', 'AdminController@updateDealerTemplate');
  Route::get('/statusDealerTemplate/{status}/{dealer_id}/{id}', 'AdminController@statusDealerTemplate');
  Route::get('/dealerProducts/{dealer_id}', 'AdminController@dealerProducts');
  Route::get('/dealerProductInventory/{dealer_id}/{product_id}', 'AdminController@dealerProductInventory');
  Route::get('/downloadProductInventory/{dealer_id}', 'AdminController@downloadProductInventory');
  Route::post('/updateDealerProductInventory', 'AdminController@updateDealerProductInventory');
  Route::get('/dealerTemplatesProducts/{dealer_id}/{id}', 'AdminController@dealerTemplatesProducts');
  Route::get('/set_min_level/{dealer_id}/{temp_id}/{pro_id}', 'AdminController@set_min_level');
  Route::post('/updateProductInventory', 'AdminController@updateProductInventory');


  // Model module
  // Route::get('/models/{id}','AdminController@models');
  // Route::get('/addModel/{dealer_id}', 'AdminController@addModel');
  // Route::post('/insertModel', 'AdminController@insertModel');
  // Route::get('/editModel/{dealer_id}/{id}', 'AdminController@editModel');
  // Route::post('/updateModel', 'AdminController@updateModel');
  // Route::get('/statusModel/{status}/{dealer_id}/{id}', 'AdminController@statusModel');
  // Staff Module
  Route::get('/staff_management', 'AdminController@staff_management');
  Route::get('/addStaffMember', 'AdminController@addStaff');
  Route::post('getdepartmentbylevel', 'AdminController@getdepartmentbylevel');
  Route::post('getdesbylevel', 'AdminController@getdesbylevel');
  Route::post('getreportinglevel', 'AdminController@getreportinglevel');
  Route::get('getBydesignation/{id}/{auth_id?}', 'AdminController@getBydesignation');
  Route::post('/insertStaff', 'AdminController@insertStaff');
  Route::get('/editStaffMember/{id}', 'AdminController@editStaff');
  Route::post('/getreportingauthority/', 'AdminController@getreportingauthority');
  Route::post('/getdealerauthority/', 'AdminController@getdealerauthority');
  // Route::get('/getreportingauthority/{user_id}/{del_id}/', 'AdminController@getreportingauthority');
  Route::get('/getauthority/{user_id}/{authority_id}/', 'AdminController@getauthority');
  Route::get('/getDealerPermission/{user_id}/{dealer_id}/', 'AdminController@getDealerPermission');
  Route::get('/getreportingpermission/{user_id}/{dealer_id}/{del_authid}', 'AdminController@getreportingpermission');
  Route::post('/updateStaff', 'AdminController@updateStaff');
  Route::get('/statusStaff/{status}/{id}', 'AdminController@statusStaff');
  Route::get('/downloadStaff', 'AdminController@downloadStaff');
  Route::post('/ajax_office_user', 'AdminController@ajax_office_user');

  // Department Module
  Route::get('/department', 'AdminController@department');
  Route::get('/addDepartment', 'AdminController@addDepartment');
  Route::post('/insertDepartment', 'AdminController@insertDepartment');
  Route::get('/editDepartment/{id}', 'AdminController@editDepartment');
  Route::post('/updateDepartment', 'AdminController@updateDepartment');

  // Dealer Department Module
  Route::get('/dealer_departments', 'AdminController@dealerDepartments');
  Route::get('/addDealerDepartment', 'AdminController@addDealerDepartment');
  Route::post('/insertDealerDepartment', 'AdminController@insertDealerDepartment');
  Route::get('/editDealerDepartment/{id}', 'AdminController@editDealerDepartment');
  Route::post('/updateDealerDepartment', 'AdminController@updateDealerDepartment');

  //Designation Module
  Route::get('/designation', 'AdminController@designation');
  Route::get('/addDesignation', 'AdminController@addDesignation');
  Route::post('/insertDesignation', 'AdminController@insertDesignation');
  Route::get('/editDesignation/{id}', 'AdminController@editDesignation');
  Route::post('/updateDesignation', 'AdminController@updateDesignation');

  //Designation Level Module
  Route::get('/level', 'AdminController@level');
  Route::get('/addLevel', 'AdminController@addLevel');
  Route::post('/insertLevel', 'AdminController@insertLevel');
  Route::get('/editLevel/{id}', 'AdminController@editLevel');
  Route::post('/updateLevel', 'AdminController@updateLevel');

  //Groups Module
  Route::get('/groups', 'AdminController@groups');
  Route::get('/addGroup', 'AdminController@addGroup');
  Route::post('/insertGroup', 'AdminController@insertGroup');
  Route::get('/editGroup/{id}', 'AdminController@editGroup');
  Route::post('/updateGroup', 'AdminController@updateGroup');
  Route::get('/statusGroup/{status}/{id}', 'AdminController@statusGroup');

  //OEM Module
  Route::get('/oems', 'AdminController@oems');
  Route::get('/addOEM', 'AdminController@addOEM');
  Route::post('/insertOEM', 'AdminController@insertOEM');
  Route::get('/editOEM/{id}', 'AdminController@editOEM');
  Route::post('/updateOEM', 'AdminController@updateOEM');
  Route::get('/statusOEM/{status}/{id}', 'AdminController@statusOEM');

  // Model module
  Route::get('/models/', 'AdminController@models');
  Route::get('/addModel/', 'AdminController@addModel');
  Route::post('/insertModel', 'AdminController@insertModel');
  Route::get('/editModel/{id}', 'AdminController@editModel');
  Route::post('/updateModel', 'AdminController@updateModel');
  Route::get('/statusModel/{status}/{id}', 'AdminController@statusModel');

  // Treatment Module
  Route::get('/treatments', 'AdminController@treatments');
  // Route::get('/getTreatments/{id}','AdminController@getTreatments');
  Route::get('/addTreatment', 'AdminController@addTreatment');
  Route::post('/insertTreatment', 'AdminController@insertTreatment');
  Route::get('/editTreatment/{id}', 'AdminController@editTreatment');
  Route::get('/updateTreatmentPrice/{treatment_id}', 'AdminController@updateTreatmentPrice');
  Route::post('/updateTreatmentPrice/{treatment_id}', 'AdminController@updateTreatmentPrice');
  Route::post('/updateTreatment', 'AdminController@updateTreatment');
  Route::get('/statusTreatment/{status}/{id}', 'AdminController@statusTreatment');
  Route::get('/uploadTreatment', 'AdminController@uploadTreatment');
  Route::post('/importTreatment', 'AdminController@importTreatment');
  Route::get('/downloadTreatment', 'AdminController@downloadTreatment');

  // Treatment Product module
  Route::get('/treatmentProducts/{id}', 'AdminController@treatmentProducts');
  Route::get('/addTreatmentProduct/{treatment_id}', 'AdminController@addTreatmentProduct');
  Route::post('/insertTreatmentProduct', 'AdminController@insertTreatmentProduct');
  Route::get('/editTreatmentProduct/{treatment_id}/{id}', 'AdminController@editTreatmentProduct');
  Route::post('/updateTreatmentProduct', 'AdminController@updateTreatmentProduct');
  Route::get('/statusTreatmentProduct/{status}/{treatment_id}/{id}', 'AdminController@statusTreatmentProduct');
  Route::get('/getProductData', 'AdminController@getProductData');

  // Treatment Template module
  Route::get('/treatmentTemplates', 'AdminController@treatmentTemplates');
  Route::get('/addTreatmentTemplate', 'AdminController@addTreatmentTemplate');
  Route::post('/insertTreatmentTemp', 'AdminController@insertTreatmentTemp');
  Route::get('/editTreatmentTemplate/{id}', 'AdminController@editTreatmentTemplate');
  Route::post('/updateTreatmentTemp', 'AdminController@updateTreatmentTemp');
  Route::get('/statusTreatTemp/{status}/{id}', 'AdminController@statusTreatTemp');
  Route::get('/addDuplicateTemplate/{temp_id}', 'AdminController@addDuplicateTemplate');
  Route::post('/insertDuplicateTemplate', 'AdminController@insertDuplicateTemplate');
  Route::get('/addPercentagePrice/{temp_id}', 'AdminController@addPercentagePrice');
  Route::post('/updatePercentagePrice', 'AdminController@updatePercentagePrice');

  // View Treatments of Particular Templates
  Route::get('/getTreatmentList/{id}', 'AdminController@getTreatmentsList');

  // History Jobs
  Route::get('/history_jobs', 'AdminController@history_jobs');
  Route::get('/uploadJobHistory', 'AdminController@uploadJobHistory');
  Route::post('/importJobsHistory', 'AdminController@importJobsHistory');

  // Gallery Module
  Route::get('/gallery', 'AdminController@gallery');
  Route::get('/images', 'AdminController@images');
  Route::get('/addImage', 'AdminController@addImage');
  Route::post('/insertImage', 'AdminController@insertImage');
  Route::get('/editImage/{id}', 'AdminController@editImage');
  Route::post('/updateImage', 'AdminController@updateImage');
  Route::get('/statusImage/{status}/{id}', 'AdminController@statusImage');
  Route::get('/videos', 'AdminController@videos');
  Route::get('/addVideo', 'AdminController@addVideo');
  Route::post('/insertVideo', 'AdminController@insertVideo');
  Route::get('/editVideo/{id}', 'AdminController@editVideo');
  Route::post('/updateVideo', 'AdminController@updateVideo');
  Route::get('/statusVideo/{status}/{id}', 'AdminController@statusVideo');

  // Report Module
  Route::get('/reports', 'AdminController@reports');
  Route::get('/daily_report', 'AdminController@dailyReport');
  Route::get('/mis_report', 'AdminController@misReport');
  Route::get('/mom_report', 'AdminController@momReport');
  Route::get('/percentage_business_report', 'AdminController@percentageBusinessReport');
  Route::get('/undone_treatments_report', 'AdminController@undoneTreatmentsReport');
  Route::get('/job_types_report', 'AdminController@jobTypesReport');
  Route::post('/getByfirm', 'AdminController@getByfirm');
  Route::get('getDealersByfirm/{id}', 'AdminController@getDealersByfirm');
  Route::get('getAsmByfirm/{firm_id}', 'AdminController@getAsmByfirm');
  Route::get('/dcf_report', 'AdminController@dcfReport');
  Route::get('/downloadReport', 'AdminController@downloadReport');
  Route::get('/downloadMIS', 'AdminController@downloadMIS');
  Route::get('/performance_reports', 'AdminController@performance_reports');
  Route::get('/downloadAdvisor/{id}/{dealer_id}/{month}', 'AdminController@downloadAdvisor');
  Route::get('/downloadPerformanceSheet', 'AdminController@downloadPerformanceSheet');
  Route::get('/downloadAllAdvisor', 'AdminController@downloadAllAdvisor');
  Route::get('/downloadAllDealerReport', 'AdminController@downloadAllDealerReport');
  //Route::get('/viewServices/{id}','AdminController@viewServices');

  // Job Module
  Route::get('/jobs', 'AdminController@jobs');
  Route::get('/addJob', 'AdminController@addJob');
  Route::post('/getdealerUsers', 'AdminController@getdealerUsers');
  Route::post('/insertJob', 'AdminController@insertJob');
  Route::get('/editJob/{id}', 'AdminController@editJob');
  Route::post('/updateJob', 'AdminController@updateJob');
  Route::get('/statusJob/{status}/{job_id}', 'AdminController@statusJob');
  Route::get('/deleteJobs', 'AdminController@deleteJobs');

  // Jobs Treatment Module
  Route::get('/jobs_treatment_list', 'AdminController@jobsTreatmentList');

  // Product Module
  Route::get('/products', 'AdminController@products');
  Route::get('/addProduct', 'AdminController@addProduct');
  Route::post('/insertProduct', 'AdminController@insertProduct');
  Route::get('/editProduct/{id}', 'AdminController@editProduct');
  Route::post('/updateProduct', 'AdminController@updateProduct');
  Route::get('/statusProduct/{status}/{id}', 'AdminController@statusProduct');

  // Attendance
  Route::get('/attendance', 'AdminController@attendance');
  Route::get('/view_attendance/{id}', 'attendanceController@view_attendance');
  Route::get('/mark_attendance/', 'attendanceController@mark_attendance');
  Route::get('/daily_attendance/{dealer_id?}', 'attendanceController@daily_attendance');
  Route::post('/markattendance/', 'attendanceController@mark_attendance_post');

  Route::get('/relax_attendance', 'AdminController@relax_attendance');
  Route::get('/late_attendance', 'AdminController@late_attendance');
  Route::post('/addrelaxation', 'AdminController@addrelaxation');

  // // ASM Module
  // Route::get('/asm','AdminController@asm');
  // Route::get('/addASM', 'AdminController@addASM');
  // Route::post('/insertASM', 'AdminController@insertASM');
  // Route::get('/editASM/{id}', 'AdminController@editASM');
  // Route::post('/updateASM', 'AdminController@updateASM');
  // Route::get('/statusASM/{status}/{id}', 'AdminController@statusASM');
  // Route::get('asm_SalesExecutiveListing/{asm_id}', 'AdminController@asm_SalesExecutiveListing');

  // // Employee Hierarchy Module
  // Route::get('/emp_hierarchy','AdminController@emp_hierarchy');
  Route::get('/editEmpHierarchy/{id}', 'AdminController@editEmpHierarchy');
  Route::post('/updateEmpHierarchy', 'AdminController@updateEmpHierarchy');
  Route::get('/statusEmpHierarchy/{status}/{id}', 'AdminController@statusEmpHierarchy');

  // Target Module
  Route::get('targets', 'AdminController@targets');
  Route::get('addTarget', 'AdminController@addTarget');
  Route::get('addTempTarget/{dealer_id}/{temp_id}', 'AdminController@addTempTarget');
  Route::get('getDealerTemplates', 'AdminController@getDealerTemplates');
  Route::post('insertTempTarget', 'AdminController@insertTempTarget');
  Route::get('targetListing/{target_id}', 'AdminController@targetListing');
  Route::post('getTargetid', 'AdminController@getTargetid');
  Route::get('editTempTarget/{dealer_id}/{temp_id}/{target_id}', 'AdminController@editTempTarget');
  Route::post('updateTempTarget', 'AdminController@updateTempTarget');
  // Route::get('getModelTreatments', 'AdminController@getModelTreatments');
  // Route::post('updateTarget', 'AdminController@updateTarget');
  Route::get('/consumption_report', 'AdminController@consumptionReport');

  // Product Brand module
  Route::get('/product_brands', 'AdminController@productBrands');
  Route::get('/addProductBrand', 'AdminController@addProductBrand');
  Route::post('/insertProductBrand', 'AdminController@insertProductBrand');
  Route::get('/editProductBrand/{id}', 'AdminController@editProductBrand');
  Route::post('/updateProductBrand', 'AdminController@updateProductBrand');
  Route::get('/statusProductBrand/{status}/{id}', 'AdminController@statusProductBrand');
});

Route::group(['prefix' => 'asm', 'as' => 'asm::', 'middleware' => ['web', 'asm']], function () {
  Route::get('/', 'AsmController@dashboard');
  Route::get('/downloadDashboard', 'AsmController@downloadDashboard');

  // Dealer Module
  Route::get('/addDealer', 'AsmController@addDealer');
  Route::post('/insertDealer', 'AsmController@insertDealer');
  Route::get('/dealer_management', 'AsmController@dealer_management');
  Route::get('/statusDealer/{status}/{id}', 'AsmController@statusDealer');
  Route::get('/editDealer/{id}', 'AsmController@editDealer');
  Route::post('/updateDealer', 'AsmController@updateDealer');
  Route::get('/dealerProducts/{dealer_id}', 'AsmController@dealerProducts');
  Route::get('/dealerProductInventory/{dealer_id}/{product_id}', 'AsmController@dealerProductInventory');
  Route::get('/downloadProductInventory/{dealer_id}', 'AsmController@downloadProductInventory');
  Route::post('/updateDealerProductInventory', 'AsmController@updateDealerProductInventory');

  // Staff Module
  Route::get('/staff_management', 'AsmController@staff_management');
  // Route::get('/addStaffMember', 'AsmController@addStaff');
  // Route::get('getBydesignation/{id}/{auth_id?}', 'AsmController@getBydesignation');
  // Route::post('/insertStaff', 'AsmController@insertStaff');
  // Route::get('/editStaffMember/{id}', 'AsmController@editStaff');
  Route::get('/getDealerPermission/{user_id}/{del_id}', 'AsmController@getDealerPermission');
  Route::post('/getdealerauthority/', 'AsmController@getdealerauthority');
  Route::get('/getreportingpermission/{user_id}/{dealer_id}/{del_authid}', 'AsmController@getreportingpermission');
  // Route::post('/updateStaff', 'AsmController@updateStaff');
  Route::get('/statusStaff/{status}/{id}', 'AsmController@statusStaff');
  Route::get('/downloadStaff', 'AsmController@downloadStaff');
  Route::get('/editEmpHierarchy/{id}', 'AsmController@editEmpHierarchy')->middleware('authenticate');
  Route::get('/getauthority/{user_id}/{authority_id}/', 'AsmController@getauthority');
  Route::post('/getreportingauthority', 'AsmController@getreportingauthority');
  Route::post('/updateEmpHierarchy', 'AsmController@updateEmpHierarchy');
  Route::get('/statusEmpHierarchy/{status}/{id}', 'AsmController@statusEmpHierarchy');

  // Target Module
  Route::get('targets', 'AsmController@targets');
  Route::get('targetListing/{dealer_id}/{temp_id}/{target_id}', 'AsmController@targetListing');

  // Job Module
  Route::get('/jobs', 'AsmController@jobs');
  Route::get('/addJob', 'AsmController@addJob');
  Route::post('/getdealerUsers', 'AsmController@getdealerUsers');
  Route::post('/insertJob', 'AsmController@insertJob');
  Route::get('/editJob/{id}', 'AsmController@editJob');
  Route::post('/updateJob', 'AsmController@updateJob');
  Route::get('/statusJob/{status}/{job_id}', 'AsmController@statusJob');
  Route::get('/deleteJobs', 'AsmController@deleteJobs');
  Route::post('/addServiceLoad', 'AsmController@addServiceLoad');

  // Jobs Treatment Module
  Route::get('/jobs_treatment_list', 'AsmController@jobsTreatmentList');

  // Attendance
  Route::get('/attendance', 'AsmController@attendance');
  Route::get('/view_attendance/{id}', 'AsmController@view_attendance');
  Route::get('/mark_attendance/', 'AsmController@mark_attendance');
  Route::get('/daily_attendance/{dealer_id?}', 'AsmController@daily_attendance');
  Route::post('/markattendance/', 'AsmController@mark_attendance_post');


  // Report Module
  Route::get('/reports', 'AsmController@reports');
  Route::get('/daily_report', 'AsmController@dailyReport');
  Route::get('/mis_report', 'AsmController@misReport');
  Route::get('/dcf_report', 'AsmController@dcfReport');
  Route::get('/downloadReport', 'AsmController@downloadReport');
  Route::get('/downloadMIS', 'AsmController@downloadMIS');
  Route::get('/performance_reports', 'AsmController@performance_reports');
  Route::get('/downloadAdvisor/{id}/{dealer_id}/{month}', 'AsmController@downloadAdvisor');
  Route::get('/downloadPerformanceSheet', 'AsmController@downloadPerformanceSheet');
  Route::get('/downloadAllAdvisor', 'AsmController@downloadAllAdvisor');
  Route::get('/downloadAllDealerReport', 'AsmController@downloadAllDealerReport');
  Route::get('/consumption_report', 'AsmController@consumptionReport');
  //Route::get('/viewServices/{id}','AdminController@viewServices');
});
Route::group(['prefix' => 'rsm', 'as' => 'rsm::', 'middleware' => ['web', 'rsm']], function () {
  Route::get('/', 'RsmController@dashboard');
  Route::get('/downloadDashboard', 'RsmController@downloadDashboard');
  // Dealer Module
  Route::get('/addDealer', 'RsmController@addDealer');
  Route::post('/insertDealer', 'RsmController@insertDealer');
  Route::get('/dealer_management', 'RsmController@dealer_management');
  Route::get('/statusDealer/{status}/{id}', 'RsmController@statusDealer');
  Route::get('/editDealer/{id}', 'RsmController@editDealer');
  Route::post('/updateDealer', 'RsmController@updateDealer');
  // Staff Module
  Route::get('/staff_management', 'RsmController@staff_management');
  // Route::get('/addStaffMember', 'RsmController@addStaff');
  // Route::get('getBydesignation/{id}/{auth_id?}', 'RsmController@getBydesignation');
  // Route::post('/insertStaff', 'RsmController@insertStaff');
  // Route::get('/editStaffMember/{id}', 'RsmController@editStaff');
  Route::get('/getDealerPermission/{user_id}/{del_id}', 'RsmController@getDealerPermission');
  Route::post('/getdealerauthority/', 'RsmController@getdealerauthority');
  Route::get('/getreportingpermission/{user_id}/{dealer_id}/{del_authid}', 'RsmController@getreportingpermission');
  // Route::post('/updateStaff', 'RsmController@updateStaff');
  Route::get('/statusStaff/{status}/{id}', 'RsmController@statusStaff');
  Route::get('/downloadStaff', 'RsmController@downloadStaff');
  Route::get('/editEmpHierarchy/{id}', 'RsmController@editEmpHierarchy')->middleware('authenticate');
  Route::get('/getauthority/{user_id}/{authority_id}/', 'RsmController@getauthority');
  Route::post('/getreportingauthority/', 'RsmController@getreportingauthority');
  Route::post('/updateEmpHierarchy', 'RsmController@updateEmpHierarchy');
  Route::get('/statusEmpHierarchy/{status}/{id}', 'RsmController@statusEmpHierarchy');
  // Target Module
  Route::get('targets', 'RsmController@targets');
  Route::get('targetListing/{dealer_id}/{temp_id}/{target_id}', 'RsmController@targetListing');
  // Job Module
  Route::get('/jobs', 'RsmController@jobs');
  Route::get('/addJob', 'RsmController@addJob');
  Route::post('/insertJob', 'RsmController@insertJob');
  Route::get('/editJob/{id}', 'RsmController@editJob');
  Route::post('/updateJob', 'RsmController@updateJob');
  Route::get('/statusJob/{status}/{job_id}', 'RsmController@statusJob');
  Route::get('/deleteJobs', 'RsmController@deleteJobs');
  Route::post('/addServiceLoad', 'RsmController@addServiceLoad');

  // Jobs Treatment Module
  Route::get('/jobs_treatment_list', 'RsmController@jobsTreatmentList');

  // Attendance
  Route::get('/attendance', 'RsmController@attendance');
  Route::get('/view_attendance/{id}', 'RsmController@view_attendance');
  Route::get('/mark_attendance/', 'RsmController@mark_attendance');
  Route::get('/daily_attendance/{dealer_id?}', 'RsmController@daily_attendance');
  Route::post('/markattendance/', 'Rsm@mark_attendance_post');

  // Report Module
  Route::get('/reports', 'RsmController@reports');
  Route::get('/daily_report', 'RsmController@dailyReport');
  Route::get('/mis_report', 'RsmController@misReport');
  Route::get('/dcf_report', 'RsmController@dcfReport');
  Route::get('/downloadReport', 'RsmController@downloadReport');
  Route::get('/downloadMIS', 'RsmController@downloadMIS');
  Route::get('/performance_reports', 'RsmController@performance_reports');
  Route::get('/downloadAdvisor/{id}/{dealer_id}/{month}', 'RsmController@downloadAdvisor');
  Route::get('/downloadPerformanceSheet', 'RsmController@downloadPerformanceSheet');
  Route::get('/downloadAllAdvisor', 'RsmController@downloadAllAdvisor');
  Route::get('/downloadAllDealerReport', 'RsmController@downloadAllDealerReport');
  Route::get('/consumption_report', 'RsmController@consumptionReport');
  //Route::get('/viewServices/{id}','AdminController@viewServices');
});
Route::group(['prefix' => 'sse', 'as' => 'sse::', 'middleware' => ['web', 'sse']], function () {
  Route::get('/', 'SseController@dashboard');
  Route::get('/downloadDashboard', 'SseController@downloadDashboard');
  // Dealer Module
  Route::get('/addDealer', 'SseController@addDealer');
  Route::post('/insertDealer', 'SseController@insertDealer');
  Route::get('/dealer_management', 'SseController@dealer_management');
  Route::get('/statusDealer/{status}/{id}', 'SseController@statusDealer');
  Route::get('/editDealer/{id}', 'SseController@editDealer');
  Route::post('/updateDealer', 'SseController@updateDealer');
  // Staff Module
  Route::get('/staff_management', 'SseController@staff_management');
  // Route::get('/addStaffMember', 'SseController@addStaff');
  // Route::get('getBydesignation/{id}/{auth_id?}', 'SseController@getBydesignation');
  // Route::post('/insertStaff', 'SseController@insertStaff');
  // Route::get('/editStaffMember/{id}', 'SseController@editStaff');
  Route::get('/getDealerPermission/{user_id}/{del_id}', 'SseController@getDealerPermission');
  Route::post('/getdealerauthority/', 'SseController@getdealerauthority');
  Route::get('/getreportingpermission/{user_id}/{dealer_id}/{del_authid}', 'SseController@getreportingpermission');
  // Route::post('/updateStaff', 'SseController@updateStaff');
  Route::get('/statusStaff/{status}/{id}', 'SseController@statusStaff');
  Route::get('/downloadStaff', 'SseController@downloadStaff');
  Route::get('/editEmpHierarchy/{id}', 'SseController@editEmpHierarchy')->middleware('authenticate');
  Route::get('/getauthority/{user_id}/{authority_id}/', 'SseController@getauthority');
  Route::post('/getreportingauthority/', 'SseController@getreportingauthority');
  Route::post('/updateEmpHierarchy', 'SseController@updateEmpHierarchy');
  Route::get('/statusEmpHierarchy/{status}/{id}', 'SseController@statusEmpHierarchy');
  // Target Module
  Route::get('targets', 'SseController@targets');
  Route::get('targetListing/{dealer_id}/{temp_id}/{target_id}', 'SseController@targetListing');
  // Job Module
  Route::get('/jobs', 'SseController@jobs');
  Route::get('/addJob', 'SseController@addJob');
  Route::post('/insertJob', 'SseController@insertJob');
  Route::get('/editJob/{id}', 'SseController@editJob');
  Route::post('/updateJob', 'SseController@updateJob');
  Route::get('/statusJob/{status}/{job_id}', 'SseController@statusJob');
  Route::get('/deleteJobs', 'SseController@deleteJobs');
  Route::post('/addServiceLoad', 'SseController@addServiceLoad');

  // Jobs Treatment Module
  Route::get('/jobs_treatment_list', 'SseController@jobsTreatmentList');

  // Attendance
  Route::get('/attendance', 'SseController@attendance');
  Route::get('/view_attendance/{id}', 'SseController@view_attendance');
  Route::get('/daily_attendance/{dealer_id?}', 'SseController@daily_attendance');

  // Report Module 
  Route::get('/reports', 'SseController@reports');
  Route::get('/daily_report', 'SseController@dailyReport');
  Route::get('/mis_report', 'SseController@misReport');
  Route::get('/dcf_report', 'SseController@dcfReport');
  Route::get('/downloadReport', 'SseController@downloadReport');
  Route::get('/downloadMIS', 'SseController@downloadMIS');
  Route::get('/performance_reports', 'SseController@performance_reports');
  Route::get('/downloadAdvisor/{id}/{dealer_id}/{month}', 'SseController@downloadAdvisor');
  Route::get('/downloadPerformanceSheet', 'SseController@downloadPerformanceSheet');
  Route::get('/downloadAllAdvisor', 'SseController@downloadAllAdvisor');
  Route::get('/downloadAllDealerReport', 'SseController@downloadAllDealerReport');
  Route::get('/consumption_report', 'SseController@consumptionReport');
  //Route::get('/viewServices/{id}','AdminController@viewServices');
});
