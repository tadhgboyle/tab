<!DOCTYPE html>
<html>

<head>
    <title>User PDF</title>
    <style>
        body {
            font-size: 10px;
        }

        .section {
            padding: 0.5rem;
        }

        .title,
        .subtitle {
            font-size: 14px;
            margin-bottom: 0.3rem;
        }

        table {
            width: 100%;
            margin-bottom: 0.5rem;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.3rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            font-weight: bold;
        }

        .has-text-left {
            text-align: left !important;
        }

        .has-text-right {
            text-align: right !important;
        }

        .has-text-weight-bold {
            font-weight: bold;
        }

        .has-text-italic {
            font-style: italic;
        }

        .column-20 {
            width: 20%;
        }

        .column-10 {
            width: 10%;
        }

        .column-15 {
            width: 15%;
        }

        .column-25 {
            width: 25%;
        }

        .column-5 {
            width: 5%;
        }

        .column-8 {
            width: 8%;
        }

        .section-header {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>

    <section class="section">
        <div>
            <h2 class="title">{{ $user->full_name }}</h2>

            <!-- Order Details Table -->
            <section>
                <h3 class="section-header">Orders</h3>
                @foreach($user->orders as $order)
                    <p>Order: {{ $order->identifier }}</p>
                    <p>Date: {{ $order->created_at->format('M jS Y h:ia') }}</p>

                    <table>
                        <thead>
                            <tr>
                                <th class="has-text-left column-25">Product</th>
                                <th class="has-text-left column-25">Category</th>
                                <th class="has-text-left column-10">Qty</th>
                                <th class="has-text-left column-10">Returned</th>
                                <th class="has-text-right column-15">Price/Unit</th>
                                <th class="has-text-right column-8">PST %</th>
                                <th class="has-text-right column-8">GST %</th>
                                <th class="has-text-right column-20">Total</th>
                                <th class="has-text-right column-5">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->products as $orderProduct)
                                <tr>
                                    <td class="has-text-left column-25">
                                        @if($orderProduct->productVariant)
                                            {{ $orderProduct->productVariant->description(false) }}
                                        @else
                                            {{ $orderProduct->product->name }}
                                        @endif
                                    </td>
                                    <td class="has-text-left column-25">{{ $orderProduct->category->name }}</td>
                                    <td class="has-text-left column-10">{{ $orderProduct->quantity }}</td>
                                    <td class="has-text-left column-10">{{ $orderProduct->returned }}</td>
                                    <td class="has-text-right column-15">
                                        <!-- TODO: This will be inaccurate if price changes after. We need to store price per unit on orderProducts -->
                                        @if($orderProduct->productVariant)
                                            {{ $orderProduct->productVariant->price }}
                                        @else
                                            {{ $orderProduct->product->price }}
                                        @endif
                                    </td>
                                    <td class="has-text-right column-8">{{ number_format($orderProduct->pst, 2) }}</td>
                                    <td class="has-text-right column-8">{{ number_format($orderProduct->gst, 2) }}</td>
                                    <td class="has-text-right column-20">
                                        {{ \App\Helpers\TaxHelper::forOrderProduct($orderProduct, $orderProduct->quantity) }}
                                    </td>
                                    <td class="has-text-right column-5">
                                        {{ \App\Helpers\TaxHelper::forOrderProduct($orderProduct, $orderProduct->quantity - $orderProduct->returned) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if($order->giftCard)
                                <tr>
                                    <td colspan="7" class="has-text-italic column-25">Gift Card ending in {{ $order->giftCard->last4() }}</td>
                                    <td class="has-text-right column-20">{{ $order->gift_card_amount }}</td>
                                    <td class="column-5"></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="7" class="has-text-italic column-25">Cash</td>
                                <td class="has-text-right column-20">{{ $order->purchaser_amount }}</td>
                                <td class="column-5"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="has-text-italic column-25">Total</td>
                                <td class="has-text-right column-20">{{ $order->total_price }}</td>
                                <td class="has-text-right column-5 has-text-weight-bold">{{ $order->getOwingTotal() }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </section>

            <!-- Activities Table -->
            <section>
                <h3 class="section-header">Activities</h3>
                <table>
                    <thead>
                        <tr>
                            <th class="has-text-left column-25">Activity</th>
                            <th class="has-text-left column-25">Category</th>
                            <th class="has-text-left column-10">Returned</th>
                            <th class="has-text-right column-15">Price</th>
                            <th class="has-text-right column-8">PST %</th>
                            <th class="has-text-right column-8">GST %</th>
                            <th class="has-text-right column-20">Total</th>
                            <!-- Better word for outstanding, something that means "price minus returned"? -->
                            <th class="has-text-right column-5">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->activityRegistrations as $activityRegistration)
                            <tr>
                                <td class="has-text-left column-25">{{ $activityRegistration->activity->name }}</td>
                                <td class="has-text-left column-25">{{ $activityRegistration->category->name }}</td>
                                <td class="has-text-left column-10">{{ $activityRegistration->returned ? 'Yes' : 'No' }}</td>
                                <td class="has-text-right column-15">{{ $activityRegistration->activity_price }}</td>
                                <td class="has-text-right column-8">{{ number_format($activityRegistration->activity_pst, 2) }}</td>
                                <td class="has-text-right column-8">{{ number_format($activityRegistration->activity_gst, 2) }}</td>
                                <td class="has-text-right column-20">{{ $activityRegistration->total_price }}</td>
                                <td class="has-text-right column-5">{{ $activityRegistration->returned ? '$0.00' : $activityRegistration->total_price }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- Payout Details Table -->
            <section>
                <h3 class="section-header">Payouts</h3>
                <table>
                    <thead>
                        <tr>
                            <th class="has-text-left column-33">Identifier</th>
                            <th class="has-text-left column-33">Date</th>
                            <th class="has-text-left column-33">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->payouts as $payout)
                            <tr>
                                <td class="has-text-left column-33">{{ $payout->identifier }}</td>
                                <td class="has-text-left column-33">{{ $payout->created_at->format('M jS Y h:ia') }}</td>
                                <td class="has-text-right column-33">{{ $payout->amount }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- Summary Table -->
            <section>
                <h3 class="section-header">Summary</h3>
                <table>
                    <tbody>
                        <tr>
                            <td class="has-text-italic column-25">Total Spent (in cash)</td>
                            <td class="has-text-right has-text-weight-bold column-20">{{ $user->findSpentInCash() }}</td>
                        </tr>
                        <tr>
                            <td class="has-text-italic column-25">Total Returned (to cash)</td>
                            <td class="has-text-right has-text-weight-bold column-20">-{{ $user->findReturnedToCash() }}</td>
                        </tr>
                        <tr>
                            <td class="has-text-italic column-25">Total Payouts</td>
                            <td class="has-text-right has-text-weight-bold column-20">-{{ $user->findPaidOut() }}</td>
                        </tr>
                        <tr>
                            <td class="has-text-italic column-25">Total Owed</td>
                            <td class="has-text-right has-text-weight-bold column-20">{{ $user->findOwing() }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </section>

</body>
</html>
