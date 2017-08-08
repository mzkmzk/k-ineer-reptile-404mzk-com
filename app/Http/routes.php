<?php


Route::group(['prefix' => 'v2/Chart_Controller'],function(){
    Route::get('line','Chart_Controller@line');
    Route::get('network_chart','Chart_Controller@network_chart');
    Route::get('error_chart','Chart_Controller@error_chart');

});

Route::group(['prefix' => 'v2/Site_Controller'],function(){
    Route::get('authentication_site','Site_Controller@authentication_site');
});


