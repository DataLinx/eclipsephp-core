<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? __('eclipse::email.email_sent') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #374151;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .message-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            white-space: pre-wrap;
            font-size: 16px;
            line-height: 1.5;
        }
        .sender-info {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #dbeafe;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
        .label {
            font-weight: 600;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        @if($sender)
        <div class="sender-info">
            <p><span class="label">{{ __('eclipse::email.sender') }}:</span> {{ $sender->name }} ({{ $sender->email }})</p>
        </div>
        @endif

        <div class="content">
            <h2>{{ __('eclipse::email.message') }}:</h2>
            <div class="message-content">{!! $messageContent !!}</div>
        </div>

        <div class="footer">
            <p>{{ __('This email was sent through :app_name system.', ['app_name' => config('app.name')]) }}</p>
            <p><span class="label">{{ __('eclipse::email.recipient') }}:</span> {{ $recipient->name }} ({{ $recipient->email }})</p>
        </div>
    </div>
</body>
</html> 