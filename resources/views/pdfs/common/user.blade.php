<div>
    <h2>{{ $user->full_name }}</h2>

    <!-- Summary Table -->
    <div>
        <h3>Summary</h3>
        <table>
            <tbody class="summary-table">
                @foreach($user->orders as $order)
                    <tr>
                        <td>Order: {{ $order->identifier }}</td>
                        <td>{{ $order->purchaser_amount }}</td>
                    </tr>
                @endforeach
                @foreach ($user->activityRegistrations as $activityRegistration)
                    <tr>
                        <td>Activity: {{ $activityRegistration->activity->name }}</td>
                        <td>{{ $activityRegistration->total_price }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="has-text-italic">Total Spent (in cash)</td>
                    <td class="has-text-weight-bold">{{ $user->findSpentInCash() }}</td>
                </tr>
                @foreach($user->orders->where('status', '!=', \App\Enums\OrderStatus::NotReturned) as $order)
                    <tr>
                        <td>Order return: {{ $order->identifier }}</td>
                        <td>-{{ $order->getReturnedTotalToCash() }}</td>
                    </tr>
                @endforeach
                @foreach ($user->activityRegistrations->where('returned', true) as $activityRegistration)
                    <tr>
                        <td>Activity return: {{ $activityRegistration->activity->name }}</td>
                        <td>-{{ $activityRegistration->total_price }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="has-text-italic">Total Returned (to cash)</td>
                    <td class="has-text-weight-bold">-{{ $user->findReturnedToCash() }}</td>
                </tr>
                @foreach ($user->payouts->where('status', \App\Enums\PayoutStatus::Paid) as $payout)
                    <tr>
                        <td>Payout: {{ $payout->id }}</td>
                        <td>-{{ $payout->amount }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="has-text-italic">Total Payouts</td>
                    <td class="has-text-weight-bold">-{{ $user->findPaidOut() }}</td>
                </tr>
                <tr>
                    <td class="has-text-italic">Total Owed</td>
                    <td class="has-text-weight-bold">{{ $user->findOwing() }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Order Details Table -->
    <div>
        <h3>Orders</h3>
        @foreach($user->orders as $order)
            <p>Order: {{ $order->identifier }}</p>
            <p>Date: {{ $order->created_at->format('M jS Y h:ia') }}</p>

            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">Product</th>
                        <th style="width: 10%">Category</th>
                        <th style="width: 10%">Qty</th>
                        <th style="width: 10%">Returned</th>
                        <th style="width: 10%">Price/Unit</th>
                        <th style="width: 10%">PST %</th>
                        <th style="width: 10%">GST %</th>
                        <th style="width: 10%">Total</th>
                        <th style="width: 10%">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->products as $orderProduct)
                        <tr>
                            <td >
                                @if($orderProduct->productVariant)
                                    {{ $orderProduct->productVariant->description(false) }}
                                @else
                                    {{ $orderProduct->product->name }}
                                @endif
                            </td>
                            <td >{{ $orderProduct->category->name }}</td>
                            <td >{{ $orderProduct->quantity }}</td>
                            <td >{{ $orderProduct->returned }}</td>
                            <td class="money-column">
                                <!-- TODO: This will be inaccurate if price changes after. We need to store price per unit on orderProducts -->
                                @if($orderProduct->productVariant)
                                    {{ $orderProduct->productVariant->price }}
                                @else
                                    {{ $orderProduct->product->price }}
                                @endif
                            </td>
                            <td>{{ number_format($orderProduct->pst, 2) }}</td>
                            <td>{{ number_format($orderProduct->gst, 2) }}</td>
                            <td class="money-column">
                                {{ $orderProduct->total_price }}
                            </td>
                            <td class="money-column">
                                {{ \App\Helpers\TaxHelper::forOrderProduct($orderProduct, $orderProduct->quantity - $orderProduct->returned) }}
                            </td>
                        </tr>
                    @endforeach
                    @if($order->giftCard)
                        <tr>
                            <td colspan="7" class="has-text-italic">Gift Card ending in {{ $order->giftCard->last4() }}</td>
                            <td class="money-column">{{ $order->gift_card_amount }}</td>
                            <td></td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="7" class="has-text-italic">Cash</td>
                        <td class="money-column">{{ $order->purchaser_amount }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" class="has-text-italic">Total</td>
                        <td class="money-column">{{ $order->total_price }}</td>
                        <td class="has-text-weight-bold money-column">{{ $order->getPurchaserOwingTotal() }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    </div>

    <!-- Activities Table -->
    <div>
        <h3>Activities</h3>
        <table>
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>Registered at</th>
                    <th>Category</th>
                    <th>Returned</th>
                    <th>Price</th>
                    <th>PST %</th>
                    <th>GST %</th>
                    <th>Total</th>
                    <th>Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->activityRegistrations as $activityRegistration)
                    <tr>
                        <td >{{ $activityRegistration->activity->name }}</td>
                        <td >{{ $activityRegistration->created_at->format('M jS Y h:ia') }}</td>
                        <td >{{ $activityRegistration->category->name }}</td>
                        <td >{{ $activityRegistration->returned ? 'Yes' : 'No' }}</td>
                        <td class="money-column">{{ $activityRegistration->activity_price }}</td>
                        <td>{{ number_format($activityRegistration->activity_pst, 2) }}</td>
                        <td>{{ number_format($activityRegistration->activity_gst, 2) }}</td>
                        <td class="money-column">{{ $activityRegistration->total_price }}</td>
                        <td class="money-column">{{ $activityRegistration->returned ? '$0.00' : $activityRegistration->total_price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Payout Details Table -->
    <div>
        <h3>Payouts</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->payouts->where('status', \App\Enums\PayoutStatus::Paid) as $payout)
                    <tr>
                        <td >{{ $payout->id }}</td>
                        <td >{{ $payout->created_at->format('M jS Y h:ia') }}</td>
                        <td class="money-column">{{ $payout->amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
