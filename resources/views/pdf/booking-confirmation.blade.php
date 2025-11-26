<!DOCTYPE html>
<html>

<head>
    <title>Hotel Booking Confirmation</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table td,
        table th {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .header-box {
            border: 1px solid #000;
            padding: 15px;
        }
    </style>
</head>

<body>

    <div class="header-box">
        <h2>Hotel Booking Confirmation</h2>
        <p><strong>Booking ID:</strong> {{ $booking->order }}</p>
        <p><strong>Booking Date:</strong> {{ $booking->created_at->format('d M Y') }}</p>
    </div>

    <h3>Guest Details</h3>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $booking->user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $booking->user->email }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $booking->user->mobile }}</td>
        </tr>
    </table>

    {{-- <h3>Hotel Details</h3>
    <table>
        <tr>
            <th>Hotel Name</th>
            <td>{{ $booking->hotel->name }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $booking->hotel->address }}</td>
        </tr>
        <tr>
            <th>City</th>
            <td>{{ $booking->hotel->city }}</td>
        </tr>
    </table> --}}

    <h3>Stay Information</h3>
    <table>
        <tr>
            <th>Check-in</th>
            <td>{{ $booking->check_in->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Check-out</th>
            <td>{{ $booking->check_out->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Total Guests</th>
            <td>{{ $booking->adults + $booking->children }}</td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td>{{ number_format($booking->total_price, 2) }}</td>
        </tr>
        <tr>
            <th>Discount</th>
            <td>{{ number_format($booking->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <th>Currency</th>
            <td>{{ $booking->currency }}</td>
        </tr>
    </table>

    <p style="margin-top: 50px; text-align:center;">
        Thank you for booking with us!
    </p>

</body>

</html>
