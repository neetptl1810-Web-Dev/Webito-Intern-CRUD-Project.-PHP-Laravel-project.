/**
 * =========================================
 * 📋 Orders Management - JavaScript
 * ✨ Premium Design System Compatible
 * ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 Orders JS loaded');
    
    // Modal elements
    const orderModal = document.getElementById('orderDetailsModal');
    const orderContent = document.getElementById('orderDetailsContent');
    
    // =========================================
    // 1. View Order Details (AJAX)
    // =========================================
    /**
 * View Order Details (AJAX)
 */
window.viewOrderDetails = function(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');
    
    if (!modal || !content) {
        console.error('❌ Order modal elements not found');
        return;
    }
    
    // ✅ Get company slug from window.companyData (set in Blade)
    const companySlug = window.companyData?.slug;
    const csrfToken = window.companyData?.csrfToken || '';
    
    if (!companySlug) {
        console.error('❌ Company slug not found in window.companyData');
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #ef4444;">
                <p>⚠️ Error: Company data not loaded.</p>
                <button class="btn-cancel" onclick="closeOrderDetailsModal()">Close</button>
            </div>
        `;
        modal.style.display = 'flex';
        void modal.offsetWidth;
        modal.classList.add('active');
        return;
    }
    
    // Show modal with loading state
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('active');
    
    content.innerHTML = `
        <div class="modal-loading">
            <div class="np-spinner"></div>
            <p>Loading order details...</p>
        </div>
    `;
    
    // ✅ Use dynamic slug in fetch URL
    fetch(`/company/${companySlug}/orders/${orderId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📥 Order data received:', data);
        
        if (data.success && data.order) {
            renderOrderDetails(data.order);
        } else {
            throw new Error(data.message || 'Failed to load order');
        }
    })
    .catch(error => {
        console.error('❌ Error loading order:', error);
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #ef4444;">
                <p style="font-size: 3rem; margin-bottom: 1rem;">⚠️</p>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">Error Loading Order</p>
                <p style="color: var(--np-text-muted);">${error.message}</p>
                <button class="btn-cancel" onclick="closeOrderDetailsModal()" style="margin-top: 1.5rem;">
                    Close
                </button>
            </div>
        `;
    });
};
    
    // =========================================
    // 2. Render Order Details in Modal
    // =========================================
    function renderOrderDetails(order) {
        const itemsHtml = order.items.map(item => `
            <tr>
                <td><strong>${escapeHtml(item.product_name)}</strong></td>
                <td>${item.quantity}</td>
                <td>Rs ${parseFloat(item.price).toFixed(2)}</td>
                <td>Rs ${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `).join('');
        
        const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
        const grandTotal = order.items.reduce((sum, item) => sum + parseFloat(item.subtotal), 0);
        
        orderContent.innerHTML = `
            <div class="order-details">
                <!-- Order Info Section -->
                <div class="detail-row">
                    <label>Order ID:</label>
                    <span>#${escapeHtml(order.order_id)}</span>
                </div>
                <div class="detail-row">
                    <label>Customer Name:</label>
                    <span>${escapeHtml(order.customer_name)}</span>
                </div>
                <div class="detail-row">
                    <label>Customer Email:</label>
                    <span>${escapeHtml(order.customer_email)}</span>
                </div>
                ${order.customer_phone ? `
                <div class="detail-row">
                    <label>Phone:</label>
                    <span>${escapeHtml(order.customer_phone)}</span>
                </div>
                ` : ''}
                <div class="detail-row">
                    <label>Total Amount:</label>
                    <span style="color: var(--np-primary); font-weight: 700; font-size: 1.1rem;">
                        Rs ${parseFloat(order.total_amount).toFixed(2)}
                    </span>
                </div>
                <div class="detail-row">
                    <label>Payment Method:</label>
                    <span>${escapeHtml(order.payment_method)}</span>
                </div>
                <div class="detail-row">
                    <label>Payment Status:</label>
                    <span class="payment-badge payment-${order.payment_status}">
                        ${escapeHtml(order.payment_status)}
                    </span>
                </div>
                <div class="detail-row">
                    <label>Order Date:</label>
                    <span>${escapeHtml(order.created_at)}</span>
                </div>
                <div class="detail-row">
                    <label>Shipping Address:</label>
                    <p>${escapeHtml(order.shipping_address)}</p>
                </div>
                
                <!-- Products Section -->
                <h4>📦 Products (${totalItems} items)</h4>
                <div class="table-scroll-container" style="max-height: 300px;">
                    <table class="company-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                            <tr style="background: var(--np-bg-secondary); font-weight: 600;">
                                <td colspan="3" style="text-align: right;">Total:</td>
                                <td>Rs ${grandTotal.toFixed(2)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Status Update Section -->
                <h4>🚚 Update Delivery Status</h4>
                <div class="form-group">
                    <label for="orderDeliveryStatus">Current Status:</label>
                    <select id="orderDeliveryStatus" class="delivery-status-select">
                        <option value="pending" ${order.delivery_status === 'pending' ? 'selected' : ''}>⏳ Pending</option>
                        <option value="processing" ${order.delivery_status === 'processing' ? 'selected' : ''}>⚙️ Processing</option>
                        <option value="shipped" ${order.delivery_status === 'shipped' ? 'selected' : ''}>🚚 Shipped</option>
                        <option value="delivered" ${order.delivery_status === 'delivered' ? 'selected' : ''}>✅ Delivered</option>
                        <option value="cancelled" ${order.delivery_status === 'cancelled' ? 'selected' : ''}>❌ Cancelled</option>
                        <option value="no_response" ${order.delivery_status === 'no_response' ? 'selected' : ''}>📭 No Response</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="orderNotes">Notes (Optional):</label>
                    <textarea id="orderNotes" rows="3" placeholder="Add notes about this order...">${escapeHtml(order.notes || '')}</textarea>
                </div>
                
                <!-- Action Buttons -->
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeOrderDetailsModal()">Close</button>
                    <button type="button" class="btn-primary" id="updateStatusBtn" onclick="updateOrderStatus(${order.id})">
                        💾 Update Status
                    </button>
                </div>
            </div>
        `;
        
        console.log('✅ Order details rendered');
    }
    
    // =========================================
    // 3. Update Order Status (AJAX)
    // =========================================
    /**
 * Update Order Status (AJAX) - With UI Refresh
 */
window.updateOrderStatus = function(orderId) {
    const statusSelect = document.getElementById('orderDeliveryStatus');
    const notesInput = document.getElementById('orderNotes');
    const updateBtn = document.getElementById('updateStatusBtn');
    
    if (!statusSelect || !updateBtn) {
        console.error('❌ Status update elements not found');
        return;
    }
    
    const newStatus = statusSelect.value;
    const notes = notesInput?.value || '';
    
    // ✅ Get company slug from window.companyData
    const companySlug = window.companyData?.slug;
    const csrfToken = window.companyData?.csrfToken || '';
    
    if (!companySlug) {
        console.error('❌ Company slug not found');
        return;
    }
    
    // Show loading state
    const originalBtnText = updateBtn.innerHTML;
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<span class="np-spinner"></span> Updating...';
    
    console.log('🔄 Updating order status:', { orderId, newStatus, notes });
    
    // ✅ Use dynamic slug in fetch URL
    fetch(`/company/${companySlug}/orders/${orderId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            delivery_status: newStatus,
            notes: notes
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📥 Status update response:', data);
        
        if (data.success) {
            // ✅ Show success notification
            if (typeof window.showNotification === 'function') {
                window.showNotification('✅ ' + data.message, 'success');
            }
            
            // ✅ Update UI immediately without page refresh
            if (data.order) {
                // Update status badge in modal
                const statusBadge = document.querySelector('.delivery-badge');
                if (statusBadge) {
                    statusBadge.textContent = data.order.delivery_status_label;
                    statusBadge.className = `delivery-badge delivery-${data.order.delivery_status}`;
                    statusBadge.style.cssText = data.order.delivery_status_badge;
                }
                
                // Update notes if changed
                if (notesInput && data.order.notes !== undefined) {
                    notesInput.value = data.order.notes;
                }
                
                // Update the dropdown to match new status
                if (statusSelect) {
                    statusSelect.value = data.order.delivery_status;
                }
            }
            
            // ✅ Refresh modal content after short delay to show all updates
            setTimeout(() => {
                viewOrderDetails(orderId);
            }, 800);
            
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('❌ Error updating status:', error);
        
        // Show error notification
        if (typeof window.showNotification === 'function') {
            window.showNotification('⚠️ ' + error.message, 'error');
        }
    })
    .finally(() => {
        // Restore button state
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalBtnText;
        }
    });
};
    
    // =========================================
    // 4. Close Order Details Modal
    // =========================================
    window.closeOrderDetailsModal = function() {
        if (!orderModal) return;
        
        orderModal.classList.remove('active');
        
        // Hide after animation completes
        setTimeout(() => {
            orderModal.style.display = 'none';
            // Clear content to free memory
            if (orderContent) {
                orderContent.innerHTML = '';
            }
        }, 300);
        
        console.log('🔐 Order modal closed');
    };
    
    // =========================================
    // 5. Modal Event Listeners
    // =========================================
    // Close modal when clicking outside
    if (orderModal) {
        orderModal.addEventListener('click', function(e) {
            if (e.target === orderModal) {
                closeOrderDetailsModal();
            }
        });
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && orderModal?.classList.contains('active')) {
            closeOrderDetailsModal();
        }
    });
    
    // =========================================
    // 6. Utility: Escape HTML to prevent XSS
    // =========================================
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    console.log('✅ Orders JS initialized');
});