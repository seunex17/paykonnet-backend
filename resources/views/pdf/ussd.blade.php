<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>USSD Voucher Cards</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
        }
        .voucher-cell {
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .voucher-box {
            border: 1px dashed #000;
            padding: 15px;
            text-align: center;
            background-color: #fff;
        }
        .provider {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #0056b3;
        }
        .amount {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .pin {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            background: #f8f9fa;
            padding: 8px;
            border: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .details {
            font-size: 10px;
            color: #6c757d;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Your Generated USSD Cards</h2>
    </div>

    <table class="voucher-table">
        @foreach($cards->chunk(2) as $row)
            <tr>
                @foreach($row as $card)
                    <td class="voucher-cell">
                        <div class="voucher-box">
                            <div class="provider">{{ $card->name }}</div>
                            <div class="amount">VALUE: ₦{{ number_format($card->amount, 2) }}</div>
                            <div class="pin">PIN: {{ $card->number }}</div>
                            <div class="details">
                                Identifier: {{ $card->identifier }} <br>
                                Service: {{ $card->service }} ({{ $card->code }}) <br>
                                Generated: {{ $card->created_at->format('d-M-Y H:i') }}
                                To use dial: *347*2200#
                            </div>
                        </div>
                    </td>
                @endforeach
                @if($row->count() < 2)
                    <td class="voucher-cell"></td>
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
