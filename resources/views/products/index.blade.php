<!DOCTYPE html>
<html>
<head>
    <title>Coalition Technologies Laravel Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<div class="container p-5">
    <h2 class="mb-4">Product Management</h2>

    <!-- FORM -->
    <form id="productForm" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
        </div>

        <div class="col-md-3">
            <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
        </div>

        <div class="col-md-3">
            <input type="number" name="price" class="form-control" placeholder="Price" required>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add</button>
        </div>
    </form>

 

    <!-- TABLE -->
    <table class="table table-bordered mt-4" id="productTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Quantity</th>
                <th>Price ($)</th>
                <th>Date</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th colspan="4">Grand Total</th>
                <th id="grandTotal">0</th>
            </tr>
        </tfoot>
    </table>
</div>

<script>

const form = document.getElementById('productForm');
const tableBody = document.querySelector('#productTable tbody');
const grandTotalEl = document.getElementById('grandTotal');

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// FETCH PRODUCTS
async function fetchProducts() {
    const res = await fetch('/products');
    const data = await res.json();

    tableBody.innerHTML = '';
    let grandTotal = 0;

    data.forEach((product, index) => {
        const total = product.quantity * product.price;
        grandTotal += total;

    const row = `
    <tr>
        <td>${product.name}</td>
        <td>${product.quantity}</td>
        <td>${product.price}</td>
        <td>${product.datetime}</td>
        <td>${total}</td>
        <td>
            <button class="btn btn-sm btn-primary" onclick="openEditModal(${index}, this)"><i class="bi bi-pencil me-1"></i>Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${index})"><i class="bi bi-trash me-1"></i>Delete</button>
        </td>
    </tr>
    `;

        tableBody.innerHTML += row;
    });

    grandTotalEl.innerText = grandTotal;
}

// product create
form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    await fetch('/products', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    });

    form.reset();
    fetchProducts();
});

//product edit/update
async function updateProduct(index, btn) {
    const row = btn.closest('tr');

    const name = row.querySelector('.name').value;
    const quantity = row.querySelector('.quantity').value;
    const price = row.querySelector('.price').value;

    await fetch(`/products/${index}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: new URLSearchParams({
            name, quantity, price
        })
    });

    fetchProducts();
}

//product delete
async function deleteProduct(index) {
    if (!confirm('Delete this product?')) return;

    await fetch(`/products/${index}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    fetchProducts();
}

// product initial load
fetchProducts();
</script>

<!-- Bootstrap JS (bundle includes Modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editIndex">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input id="editName" type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input id="editQuantity" type="number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input id="editPrice" type="number" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal helpers
const editModalEl = document.getElementById('editModal');
const editModal = new bootstrap.Modal(editModalEl);
const editIndexEl = document.getElementById('editIndex');
const editNameEl = document.getElementById('editName');
const editQuantityEl = document.getElementById('editQuantity');
const editPriceEl = document.getElementById('editPrice');
const saveEditBtn = document.getElementById('saveEditBtn');

function openEditModal(index, btn) {
        const row = btn.closest('tr');
        const name = row.querySelector('.name').value;
        const quantity = row.querySelector('.quantity').value;
        const price = row.querySelector('.price').value;

        editIndexEl.value = index;
        editNameEl.value = name;
        editQuantityEl.value = quantity;
        editPriceEl.value = price;

        editModal.show();
}

saveEditBtn.addEventListener('click', async () => {
        const index = editIndexEl.value;
        const name = editNameEl.value;
        const quantity = editQuantityEl.value;
        const price = editPriceEl.value;

        await fetch(`/products/${index}`, {
                method: 'POST',
                headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ name, quantity, price })
        });

        editModal.hide();
        fetchProducts();
});
</script>

</body>
</html>