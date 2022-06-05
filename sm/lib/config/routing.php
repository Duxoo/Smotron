<?php

return array(
    '' => 'frontend',
    'login/' => 'login',
    'login/page/' => 'loginPage',
    'forgotpassword/' => 'forgotpassword/',
    'signup/' => 'signup',
    'ULogout/' => 'ULogout',
    'rest/' => 'restApi',
    'tariff/<tariff_url>/' => 'frontend/tariff',
    //'my/access/create/save/' => 'frontend/myAccessSubuserCreateSave',
    //'my/access/create/' => 'frontend/myAccessSubuserCreate',
    'my/access/subuser/<sub_user_id:\d+>/delete/' => 'frontend/myAccessSubuserDelete',
    'my/access/subuser<sub_user_id:\d+>/save/' => 'frontend/myAccessSubuserSave',
    'my/access/subuser/<sub_user_id:\d+>/' => 'frontend/myAccessSubuser',
    'my/access/subuser/save/' => 'frontend/myAccessSubuserSave',
    'my/access/subuser/list/' => 'frontend/myAccessSubuserList',
    'my/access/subuser/' => 'frontend/myAccessSubuser',
    'my/access/' => 'frontend/myAccess',

    'my/custom_channels/video/delete/' => 'frontend/myVideoDelete',
    'my/custom_channels/video/' => 'frontend/myVideo',
    'my/custom_channels/video_upload/' => 'frontend/myVideoUpload',
    'my/custom_channels/list/save/' => 'frontend/myCustomChannelsListSave',
    'my/custom_channels/list/' => 'frontend/myCustomChannelsList',
    'my/custom_channels/delete/' => 'frontend/myCustomChannelsDelete',
    'my/custom_channels/edit/' => 'frontend/myCustomChannelsEdit',
    'my/custom_channels/' => 'frontend/myCustomChannels',

    'my/channels/' => 'frontend/myChannels',

    'my/tariff/choose_base_tariff/' => 'frontend/myTariffChoose',
    'my/tariff/custom_tariff_create/' => 'frontend/myTariffCustomCreate',
    'my/tariff/custom/' => 'frontend/myTariffCustom',
    'my/tariff/' => 'frontend/myTariff',
	'my/tariff/refund/' => 'frontend/myTariffRefund',

    'my/stream/' => 'frontend/myStream',

    'my/balance/history/' => 'frontend/myBalanceHistory',
    'my/balance/' => 'frontend/myBalance',

	'my/profile/saveimg/' => 'frontend/myProfileSaveImage',
	'my/profile/savecompany/' => 'frontend/myProfileSaveCompany',
	'my/profile/save/' => 'frontend/myProfileSave',
    'my/profile/' => 'frontend/myProfile',

    'my/referral/' => 'frontend/myReferral',

    'stream/epg/' => 'frontend/epg',
    'channels/' => 'frontend/streamChannels',
    'stream/' => 'frontend/stream',

	'upload/' => 'frontend/imageUpload',

    //контроллер для тестовых запросов
    'test' => 'frontend/test',
    'transcoding_ready/' => 'frontend/transcodingReady',
    'concat_ready/' => 'frontend/concatReady',
    'concat_progress' => 'frontend/concatProgress',

    'save/entity/' => 'frontend/saveEntity',
	
	'checkout/pay/' => 'frontend/order',
	'checkout/' => 'frontend/checkout',
	'payment/' => 'frontend/paymentResult',
	'bills/<bill_id>/' => 'frontend/bill',

    '<url>/' => 'frontend/error',
    '<url>' => 'frontend/noSlash',//noSlash
);