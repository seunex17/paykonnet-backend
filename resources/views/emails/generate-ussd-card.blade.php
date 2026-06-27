<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your USSD Voucher Cards</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hello, {{ $user->name }}!</h1>
        </div>
        <p>Your USSD voucher cards have been generated successfully.</p>
        <p>We have attached a PDF document containing all your generated cards to this email for your convenience.</p>

        <p><strong>Transaction Summary:</strong></p>
        <ul>
            <li>Total Cards: {{ $cards->count() }}</li>
            <li>Total Amount: ₦{{ number_format($cards->sum('amount'), 2) }}</li>
            <li>Date: {{ now()->format('d-M-Y H:i') }}</li>
        </ul>

        <p>If you have any questions, please feel free to contact our support team.</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} PayKonet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
