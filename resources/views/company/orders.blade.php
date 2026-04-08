<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - {{ $company->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/showcompany.css') }}">
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="dashboard-wrapper">
        <div class="navbar">
            <ol>
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/company">Company</a></li>
            </ol>
        </div>

        <div class="container">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h2>📋 Orders for {{ $company->name }}</h2>
                    <a href="{{ route('company.show', $company) }}" class="btn-back">Back</a>
                </div>

                @if($orders->isEmpty())
                    <p class="empty">No orders yet.</p>
                @else
                    <div class="table-scroll-container">
                        <table class="company-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Customer phone</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Shipping Address</th>
                                    <th>Delivery Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                                    <tr>
                                                        <td><strong>#{{ $order->order_id }}</strong></td>
                                                        <td>{{ $order->customer_name }}</td>

                                                        {{-- ✅ ADD THIS: Customer Phone --}}
                                                        <td>{{ $order->customer_phone ?? 'N/A' }}</td>

                                                        <td>{{ $order->customer_email }}</td>
                                                        <td>Rs {{ number_format($order->total_amount, 2) }}</td>
                                                        <td>
                                                            <span class="payment-badge payment-{{ $order->payment_method }}">
                                                                {{ $order->getPaymentMethodLabel() }}
                                                            </span>
                                                        </td>

                                                        {{-- ✅ ADD THIS: Shipping Address (truncate if long) --}}
                                                        <td title="{{ $order->shipping_address }}">
                                                            {{ Str::limit($order->shipping_address, 30) ?? 'N/A' }}
                                                        </td>

                                                        <td>
                                                            <span class="delivery-badge delivery-{{ $order->delivery_status }}">
                                                                {{ $order->getDeliveryStatusLabel() }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                        <td>
                                                            <button class="btn-view btn-sm" onclick="viewOrderDetails({{ $order->id }})" style="
                                        padding: 6px 16px;
                                        background: var(--ec-gradient, linear-gradient(135deg, #6366f1, #8b5cf6));
                                        color: var(--ec-text-inverse, white);
                                        border: none;
                                        border-radius: var(--ec-radius, 8px);
                                        font-family: var(--ec-font-heading, 'Poppins', sans-serif);
                                        font-weight: 600;
                                        font-size: 0.85rem;
                                        cursor: pointer;
                                        transition: all var(--ec-transition-fast, 0.2s ease);
                                        box-shadow: var(--ec-shadow-sm, 0 2px 4px rgba(99, 102, 241, 0.3));
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 4px;
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(99, 102, 241, 0.3)'">
                                                                👁️ Update
                                                            </button>
                                                        </td>
                                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $orders->links('pagination::simple-bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Order Details Modal --}}
    <div id="orderDetailsModal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>📦 Order Details</h3>
                <button class="modal-close" onclick="closeOrderDetailsModal()">&times;</button>
            </div>
            <div id="orderDetailsContent">
                <div class="modal-loading">
                    <div class="np-spinner"></div>
                    <p>Loading order details...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        window.companyData = {
            slug: '{{ $company->slug }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    <script src="{{ asset('js/company.js') }}"></script>
    <script src="{{ asset('js/orders.js') }}"></script>
</body>

</html>
