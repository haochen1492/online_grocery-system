<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i>Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($catList as $c): ?>
                                    <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Product description…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Product Image</label>
                            <input type="file" name="product_image" class="form-control" accept="image/*">
                            <div class="form-text">Max 2MB. JPG, PNG, WEBP, GIF accepted.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_product" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i>Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-primary me-2"></i>Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id"    id="ep_id">
                    <input type="hidden" name="current_image" id="ep_img">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name_edit" id="ep_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id_edit" id="ep_cat" class="form-select" required>
                                <?php foreach ($catList as $c): ?>
                                    <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price ($)</label>
                            <input type="number" name="price_edit" id="ep_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_edit" id="ep_stock" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description_edit" id="ep_desc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Replace Image</label>
                            <input type="file" name="product_image_edit" class="form-control" accept="image/*">
                            <div id="ep_imgPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
// Edit modal populate
document.querySelectorAll('.btn-edit-product').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ep_id').value    = btn.dataset.id;
        document.getElementById('ep_name').value  = btn.dataset.name;
        document.getElementById('ep_desc').value  = btn.dataset.desc;
        document.getElementById('ep_price').value = btn.dataset.price;
        document.getElementById('ep_stock').value = btn.dataset.stock;
        document.getElementById('ep_img').value   = btn.dataset.img;

        // Set category select
        const sel = document.getElementById('ep_cat');
        for (let opt of sel.options) {
            opt.selected = opt.value == btn.dataset.catid;
        }

        // Show current image preview
        const prev = document.getElementById('ep_imgPreview');
        if (btn.dataset.img) {
            prev.innerHTML = `<img src="uploads/products/${btn.dataset.img}"
                style="height:60px;border-radius:8px;object-fit:cover" alt="current">
                <small class="text-muted ms-2">Current image</small>`;
        } else {
            prev.innerHTML = '<small class="text-muted">No image set.</small>';
        }
    });
});

// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>

