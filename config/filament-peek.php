<?php

return [
    'devicePresets' => [
        'fullscreen' => [
            'icon' => 'heroicon-o-computer-desktop',
            'width' => '100%',
            'height' => '100%',
            'canRotatePreset' => false,
        ],
        'tablet-landscape' => [
            'icon' => 'heroicon-o-device-tablet',
            'rotateIcon' => true,
            'width' => '1080px',
            'height' => '810px',
            'canRotatePreset' => true,
        ],
        'mobile' => [
            'icon' => 'heroicon-o-device-phone-mobile',
            'width' => '375px',
            'height' => '667px',
            'canRotatePreset' => true,
        ],
    ],

    'initialDevicePreset' => 'fullscreen',

    'allowIframeOverflow' => false,

    'allowIframePointerEvents' => false,

    'closeModalWithEscapeKey' => true,

    'internalPreviewUrl' => [
        'enabled' => false,
        'middleware' => ['web'],
        'withSerializableClasses' => true,
    ],
];
