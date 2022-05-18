<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
date_default_timezone_set('Asia/Kolkata');
$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'vendor/v-1-0-0/'], function () use ($router) {
    $router->post('login', ['uses' => 'V_1_0_0\Authentications\Controllers\AuthenticationController@login']);
    $router->post('test-notification', ['uses' => 'V_1_0_0\Notifications\Controllers\NotificationController@testNotification']);
    $router->post('add-vendor', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\VendorController@addVendor']);
    $router->post('update-vendor', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\VendorController@updateVendor']);
    $router->post('update-vendor-timing', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\VendorController@updateVendorTiming']);
    $router->post('update-vendor-status', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\VendorController@updateVendorStatus']);
    /* Migration */
    $router->post('import-vendor', ['uses' => 'V_1_0_0\Migration\Controllers\ImportVendor@importVendor']);
    $router->post('import-vendor-timings', ['uses' => 'V_1_0_0\Migration\Controllers\ImportVendorTiming@ImportVendorTiming']);

    /* Now this api - working on vendor app/panel and monolithic. So Temp i have hided the token (Mono to Micro) -- start */
    $router->post('micro-accept-order', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\Orders\AcceptOrderController@accpetOrder']);
    $router->post('micro-ready-for-dispatch', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\Orders\ReadyforDispatchcontroller@readyforDispatch']);
    $router->post('micro-reject-order', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\Orders\RejectOrderController@rejectOrder']);
    $router->post('micro-store-notification', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\Orders\NotificationController@storeNotification']);
    $router->post('micro-update-menu-item-available-option', ['uses' => 'V_1_0_0\MenuItems\Controllers\MenuItemController@menuItemAvailable']);
    /* Now this api - working on vendor app/panel and monolithic. So Temp i have hided the token (Mono to Micro) -- end */

    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->post('logout', ['uses' => 'V_1_0_0\Authentications\Controllers\AuthenticationController@logout']);
		$router->post('accept-order', ['uses' => 'V_1_0_0\Orders\Controllers\AcceptOrderController@accpetOrder']);
		$router->post('ready-for-dispatch', ['uses' => 'V_1_0_0\Orders\Controllers\ReadyforDispatchcontroller@readyforDispatch']);
		$router->post('reject-order', ['uses' => 'V_1_0_0\Orders\Controllers\RejectOrderController@rejectOrder']);
        $router->post('new-order-list', ['uses' => 'V_1_0_0\Orders\Controllers\NewOrderController@newOrderList']);
        $router->post('past-order-list', ['uses' => 'V_1_0_0\Orders\Controllers\PastOrderController@pastOrderList']);
        $router->post('processing-order-list', ['uses' => 'V_1_0_0\Orders\Controllers\ProcessingOrderController@processingOrdeList']);
        $router->post('order-item', ['uses' => 'V_1_0_0\Orders\Controllers\OrderItemController@orderItems']);
        $router->post('order-detail', ['uses' => 'V_1_0_0\Orders\Controllers\OrderDetailController@orderDetail']);
        $router->post('ready-for-dispatch-order-list', ['uses' => 'V_1_0_0\Orders\Controllers\ReadyforDispatchcontroller@readyforDispatchlist']);
        $router->post('open-status-detail', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@openStatusDetail']);
        $router->post('update-open-status', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@updateOpenStatus']);
        $router->post('feedbackList', ['uses' => 'V_1_0_0\FeedBacks\Controllers\FeedBackController@FeedBackList']);
        $router->post('orderList', ['uses' => 'V_1_0_0\Orders\Controllers\OrderListController@orderList']);
        $router->post('changePassword', ['uses' => 'V_1_0_0\Authentications\Controllers\AuthenticationController@Change_password']);
        $router->post('updateProfile', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@UpdateProfile']);
        $router->post('vendor-primary-tag-list', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@vendorPrimaryTagList']);
        $router->post('vendorDetails', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@VendorDetails']);
        $router->post('restuarantCategory', ['uses' => 'V_1_0_0\Profile\Controllers\VendorController@RestuarantCategory']);
        $router->post('check-token', ['uses' => 'V_1_0_0\Authentications\Controllers\AuthenticationController@checkAuthToken']);
        $router->post('timingList', ['uses' => 'V_1_0_0\VendorTimings\Controllers\VendorTimingController@TimingList']);
        $router->post('timingDetails', ['uses' => 'V_1_0_0\VendorTimings\Controllers\VendorTimingController@TimingDetails']);
        $router->post('saveTiming', ['uses' => 'V_1_0_0\VendorTimings\Controllers\VendorTimingController@AddTimings']);
        $router->post('updateTiming', ['uses' => 'V_1_0_0\VendorTimings\Controllers\VendorTimingController@UpdateTimings']);
        $router->post('refresh_cache', ['uses' => 'V_1_0_0\InsertUpdate\Controllers\VendorController@refresh_cache']);
        $router->post('notification-list', ['uses' => 'V_1_0_0\Notifications\Controllers\NotificationController@notificationList']);
        
        /* Orders Management */
        $router->group(['prefix' => 'order', 'namespace' => 'V_1_0_0\VendorOrder'], function () use ($router) {
            $router->post('list', ['uses' => 'Controllers\OrderController@orderList']);
            $router->post('detail', ['uses' => 'Controllers\OrderController@orderDetail']);
            $router->post('item', ['uses' => 'Controllers\OrderController@orderItems']);
            $router->post('accept-order', ['uses' => 'Controllers\OrderController@accpetOrder']);
            $router->post('reject-order', ['uses' => 'Controllers\OrderController@rejectOrder']);
            $router->post('ready-for-dispatch', ['uses' => 'Controllers\OrderController@readyforDispatch']);
            $router->post('status', ['uses' => 'Controllers\OrderController@OrderStatus']);
            $router->post('allOrderStatus', ['uses' => 'Controllers\OrderController@AllOrderStatus']);
            $router->post('revenue', ['uses' => 'Controllers\OrderController@Revenue']);
            $router->post('revenueList', ['uses' => 'Controllers\OrderController@RevenueList']);
        });

        /* Menu Item Management */
        $router->group(['prefix' => 'menu-item', 'namespace' => 'V_1_0_0\MenuItems'], function () use ($router) {
            $router->post('list', ['uses' => 'Controllers\MenuItemController@index']);
            $router->post('details', ['uses' => 'Controllers\MenuItemController@details']);
            $router->post('store', ['uses' => 'Controllers\MenuItemController@store']);
            $router->post('update', ['uses' => 'Controllers\MenuItemController@update']);
            $router->post('menu-add-on-category-list', ['uses' => 'Controllers\MenuItemController@menuAddOnCategory']);
            $router->post('update-status', ['uses' => 'Controllers\MenuItemController@updateStatus']);
            $router->post('update-akeed-menu-id', ['uses' => 'Controllers\MenuItemController@updateAkeedMenuId']);
        });

        /* Add-On Category Management */
        $router->group(['prefix' => 'add-on-category', 'namespace' => 'V_1_0_0\AddOnCategory'], function () use ($router) {
            $router->post('list', ['uses' => 'Controllers\AddOnCategoryController@index']);
            $router->post('dropdown-list', ['uses' => 'Controllers\AddOnCategoryController@dropDownList']);
            $router->post('details', ['uses' => 'Controllers\AddOnCategoryController@details']);
            $router->post('store', ['uses' => 'Controllers\AddOnCategoryController@store']);
            $router->post('update', ['uses' => 'Controllers\AddOnCategoryController@update']);
            $router->post('update-status', ['uses' => 'Controllers\AddOnCategoryController@updateStatus']);
            $router->post('delete', ['uses' => 'Controllers\AddOnCategoryController@deleteCategory']);
        });

        /* Tags Management */
        $router->group(['prefix' => 'tags', 'namespace' => 'V_1_0_0\Tags'], function () use ($router) {
            $router->post('dropdown-list', ['uses' => 'Controllers\TagController@dropDownList']);
            $router->post('details', ['uses' => 'Controllers\TagController@details']);
        });

        /* Setting Management */
        $router->group(['prefix' => 'settings', 'namespace' => 'V_1_0_0\GlobalSettings'], function () use ($router) {
            $router->post('list', ['uses' => 'Controllers\SettingController@index']);
        });
        /* Tags Management */
        $router->group(['prefix' => 'tags', 'namespace' => 'V_1_0_0\Tags'], function () use ($router) {
            $router->post('dropdown-list', ['uses' => 'Controllers\TagController@dropDownList']);
        });
    });

});

