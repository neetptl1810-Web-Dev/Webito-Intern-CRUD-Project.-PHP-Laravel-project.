<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name }} - Details</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/showcompany.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">
</head>

<body class="company-show-page">

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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h2>🏢 {{ $company->name }}</h2>
                    <a href="{{ route('company.index') }}" class="btn-back">← Back to List</a>
                </div>

                <div class="company-details">
                    <div class="detail-row">
                        <label>Company ID</label>
                        <span>{{ $company->id }}</span>
                    </div>
                    <div class="detail-row">
                        <label>Company Name</label>
                        <span>{{ $company->name }}</span>
                    </div>
                    <div class="detail-row">
                        <label>Description</label>
                        <p>{{ $company->description ?? 'No description provided.' }}</p>
                    </div>
                    <div class="detail-row">
                        <label>Created At</label>
                        <span>{{ $company->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="detail-row">
                        <label>Updated At</label>
                        <span>{{ $company->updated_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-edit"
                        onclick="openEditModal('{{ $company->slug }}', '{{ addslashes($company->name) }}', '{{ addslashes($company->description) }}')">Edit</button>
                    <button type="button" class="btn-delete"
                        onclick="openDeleteModal('{{ $company->slug }}', '{{ addslashes($company->name) }}')">Delete</button>
                </div>
            </div>

            {{-- Products Section --}}
            <div class="card">
                <div class="card-header">
                    <h3>📦 Products</h3>
                    <button class="btn-primary btn-sm" onclick="openAddProductModal()">+ Add Product</button>
                    <a href="{{ route('company.orders', $company) }}" class="btn-view btn-sm"
                        style="text-decoration: none;">
                        📋 View Orders
                    </a>
                </div>

                @if($company->products->isEmpty())
                    <p class="empty">No products added yet.</p>
                @else
                    <div class="products-grid">
                        @foreach($company->products as $product)
                            <div class="product-card">
                                <div class="product-image">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            onerror="this.src='{{ asset('images/no-image.png') }}'">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" alt="No image">
                                    @endif
                                    <span class="product-stock-status" style="{{ $product->getStockStatusBadge() }}">
                                        {{ $product->getStockStatusLabel() }}
                                    </span>
                                </div>
                                <div class="product-info">
                                    <h4>{{ $product->name }}</h4>
                                    <span class="product-type">{{ $product->type }}</span>
                                    @if($product->price)
                                        <span class="product-price">Rs {{ number_format($product->price, 2) }}</span>
                                    @endif
                                    <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
                                    <div class="product-actions">
                                        <button class="btn-edit btn-sm"
                                            onclick="openEditProductModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->type) }}', '{{ $product->stock_status ?? '' }}', '{{ addslashes($product->description) }}', {{ $product->price ?? 'null' }}, '{{ $product->image }}')">Edit</button>
                                        <button type="button" class="btn-delete btn-sm"
                                            onclick="openDeleteProductModal({{ $product->id }}, '{{ addslashes($product->name) }}')">Delete</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

    {{-- Edit Company Modal --}}
    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🏢 Edit Company</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" id="editForm">
                @csrf
                @method('PUT')
                {{-- ✅ Add this hidden input --}}
                <input type="hidden" id="editCompanyId" name="id">

                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" id="editDescription" name="description">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Company</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Company Modal --}}
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content modal-danger">
            <div class="modal-header">
                <h3>⚠️ Confirm Delete</h3>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCompanyName"></strong>?</p>
                <p class="warning-text">This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Yes, Delete</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Add Product Modal --}}
    <div id="addProductModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📦 Add New Product</h3>
                <button class="modal-close" onclick="closeAddProductModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('company.products.store', $company) }}" enctype="multipart/form-data"
                id="addProductForm">
                @csrf
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Product Type *</label>
                    <input type="text" name="type" placeholder="e.g., Electronics, Clothing" required>
                </div>
                <div class="form-group">
                    <label>Stock Status *</label>
                    <select name="stock_status" required>
                        <option value="" disabled selected>Select stock status...</option>
                        <option value="in_stock">✅ In Stock</option>
                        <option value="low_stock">⚠️ Low Stock</option>
                        <option value="out_of_stock">❌ Out of Stock</option>
                        <option value="pre_order">📦 Pre-Order</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (Rs) *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Add Product</button>
                    <button type="button" class="btn-cancel" onclick="closeAddProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div id="editProductModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Edit Product</h3>
                <button class="modal-close" onclick="closeEditProductModal()">&times;</button>
            </div>
            <form method="POST" id="editProductForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editProductId" name="product_id">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="editProductName" name="name" required>
                </div>
                <div class="form-group">
                    <label>Product Type *</label>
                    <input type="text" id="editProductType" name="type" required>
                </div>
                <div class="form-group">
                    <label>Stock Status *</label>
                    <select id="editProductStockStatus" name="stock_status" required>
                        <option value="" disabled>Select stock status...</option>
                        <option value="in_stock">✅ In Stock</option>
                        <option value="low_stock">⚠️ Low Stock</option>
                        <option value="out_of_stock">❌ Out of Stock</option>
                        <option value="pre_order">📦 Pre-Order</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (Rs) *</label>
                    <input type="number" id="editProductPrice" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="editProductDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Current Image</label>
                    <img id="editProductImagePreview" src="" alt="Current image"
                        style="max-width: 150px; border-radius: 6px; margin-bottom: 10px;">
                    <label>Replace Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Product</button>
                    <button type="button" class="btn-cancel" onclick="closeEditProductModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Product Modal --}}
    <div id="deleteProductModal" class="modal-overlay">
        <div class="modal-content modal-danger">
            <div class="modal-header">
                <h3>⚠️ Delete Product</h3>
                <button class="modal-close" onclick="closeDeleteProductModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
                <p class="warning-text">This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <form method="POST" id="deleteProductForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Yes, Delete</button>
                </form>
                <button type="button" class="btn-cancel" onclick="closeDeleteProductModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Company Edit Modal
        function openEditModal(slug, name, description) {
            document.getElementById('editCompanyId').value = slug;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;

            // ✅ Set form action to the correct URL
            document.getElementById('editForm').action = '/company/' + slug;

            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.getElementById('editModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });

        // Company Delete Modal
        function openDeleteModal(slug, name) {
            document.getElementById('deleteCompanyName').textContent = name;
            document.getElementById('deleteForm').action = '/company/' + slug;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('active'); }
        document.getElementById('deleteModal')?.addEventListener('click', function (e) { if (e.target === this) closeDeleteModal(); });

        // Add Product Modal
        function openAddProductModal() { document.getElementById('addProductModal').classList.add('active'); }
        function closeAddProductModal() { document.getElementById('addProductModal').classList.remove('active'); document.getElementById('addProductForm').reset(); }
        document.getElementById('addProductModal')?.addEventListener('click', function (e) { if (e.target === this) closeAddProductModal(); });

        // Edit Product Modal
        function openEditProductModal(id, name, type, stock_status, description, price, image) {
            document.getElementById('editProductId').value = id;
            document.getElementById('editProductName').value = name;
            document.getElementById('editProductType').value = type.toLowerCase();
            document.getElementById('editProductStockStatus').value = stock_status;
            document.getElementById('editProductDescription').value = description;
            document.getElementById('editProductPrice').value = price || '';
            const preview = document.getElementById('editProductImagePreview');
            if (image) { preview.src = '/storage/' + image; } else { preview.src = '{{ asset("images/no-image.png") }}'; }
            preview.style.display = 'block';
            document.getElementById('editProductForm').action = '/company/{{ $company->slug }}/products/' + id;
            document.getElementById('editProductModal').classList.add('active');
        }
        function closeEditProductModal() { document.getElementById('editProductModal').classList.remove('active'); }
        document.getElementById('editProductModal')?.addEventListener('click', function (e) { if (e.target === this) closeEditProductModal(); });

        // Delete Product Modal
        function openDeleteProductModal(id, name) {
            document.getElementById('deleteProductName').textContent = name;
            document.getElementById('deleteProductForm').action = '/company/{{ $company->slug }}/products/' + id;
            document.getElementById('deleteProductModal').classList.add('active');
        }
        function closeDeleteProductModal() { document.getElementById('deleteProductModal').classList.remove('active'); }
        document.getElementById('deleteProductModal')?.addEventListener('click', function (e) { if (e.target === this) closeDeleteProductModal(); });

        // Close all modals on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditModal(); closeDeleteModal(); closeAddProductModal(); closeEditProductModal(); closeDeleteProductModal();
            }
        });
    </script>

    <script src="{{ asset('js/company.js') }}"></script>
    <script src="{{ asset('js/showcompany.js') }}"></script>

</body>

</html>
