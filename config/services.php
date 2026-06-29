<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'version' => env('WHATSAPP_API_VERSION', 'v18.0'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'template_name' => env('WHATSAPP_TEMPLATE_NAME', 'project_status_update'),
        'payment_template_name' => env('WHATSAPP_PAYMENT_TEMPLATE_NAME', 'project_payment_status'),
        'proposal_template_name' => env('WHATSAPP_PROPOSAL_TEMPLATE_NAME', '23233'),
        'template_id' => env('WHATSAPP_TEMPLATE_ID', '1014519774407056'),
        'sender_number' => env('WHATSAPP_SENDER_NUMBER', '+919453619260'),
        'use_default_only' => env('WHATSAPP_USE_DEFAULT_ONLY', true),
        'template_pending_to_running' => env('WHATSAPP_TEMPLATE_PENDING_TO_RUNNING', 'project_status_pending_to_running'),
        'template_running_to_pending_payment' => env('WHATSAPP_TEMPLATE_RUNNING_TO_PENDING_PAYMENT', 'project_status_running_to_pending_payment'),
        'template_pending_payment_to_completed' => env('WHATSAPP_TEMPLATE_PENDING_PAYMENT_TO_COMPLETED', 'project_status_pending_payment_to_completed'),
        'template_pending_to_canceled' => env('WHATSAPP_TEMPLATE_PENDING_TO_CANCELED', 'project_status_pending_to_canceled'),
        'template_running_to_canceled' => env('WHATSAPP_TEMPLATE_RUNNING_TO_CANCELED', 'project_status_running_to_canceled'),
        'template_pending_payment_to_canceled' => env('WHATSAPP_TEMPLATE_PENDING_PAYMENT_TO_CANCELED', 'project_status_pending_payment_to_canceled'),
        'template_canceled_to_pending' => env('WHATSAPP_TEMPLATE_CANCELED_TO_PENDING', 'project_status_canceled_to_pending'),
        'template_canceled_to_running' => env('WHATSAPP_TEMPLATE_CANCELED_TO_RUNNING', 'project_status_canceled_to_running'),
        'language' => env('WHATSAPP_LANGUAGE', 'en'),
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '91'),
        'enabled' => env('WHATSAPP_ENABLED', false),
        'reminder_pending' => env('WHATSAPP_REMINDER_PENDING'),
        'reminder_running' => env('WHATSAPP_REMINDER_RUNNING'),
        'reminder_pending_payment' => env('WHATSAPP_REMINDER_PENDING_PAYMENT'),
        'reminder_completed' => env('WHATSAPP_REMINDER_COMPLETED'),
        'type' => env('WHATSAPP_TYPE', 'official'), // official or custom
        'custom_gateway_url' => env('WHATSAPP_CUSTOM_GATEWAY_URL'), // e.g. https://api.gateway.com/send?phone={phone}&text={text}
    ],

];
