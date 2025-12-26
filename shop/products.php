<?php
require_once 'config.php';


// Fetch products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

// Cart count
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Sanitize helper
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Products - DailyHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fa;
            --text-color: #2d3436;
            --muted-color: #636e72;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-color);
            background-color: #f5f7ff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .product-card {
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-img {
            height: 180px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.03);
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .card-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .card-text {
            font-size: 0.85rem;
            color: var(--muted-color);
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .card-footer {
            background-color: white;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 0.75rem 1rem;
        }
        
        .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .badge-animate {
            animation: bounce 0.4s;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        #modalProductImage {
            border-radius: 8px;
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }
        
        #modalProductDescription {
            color: var(--muted-color);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        #modalAddToCartBtn {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        #modalAddToCartBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        
        /* Toast Styles */
        .toast {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1199.98px) {
            .product-img {
                height: 160px;
            }
        }
        
        @media (max-width: 991.98px) {
            .product-img {
                height: 140px;
            }
            
            .card-title {
                font-size: 0.95rem;
            }
            
            .card-text {
                font-size: 0.8rem;
            }
            
            .price {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .product-img {
                height: 120px;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            .card-title {
                font-size: 0.9rem;
            }
            
            .card-text {
                font-size: 0.75rem;
                height: 2.6em;
            }
            
            .price {
                font-size: 0.95rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-body .row {
                flex-direction: column;
            }
            
            .modal-body .col-md-5 {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            .product-img {
                height: 100px;
            }
            
            .card-body {
                padding: 0.5rem;
            }
            
            .card-title {
                font-size: 0.85rem;
            }
            
            .card-text {
                font-size: 0.7rem;
                height: 2.4em;
            }
            
            .price {
                font-size: 0.9rem;
            }
            
            .card-footer {
                padding: 0.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 400px) {
            .product-img {
                height: 80px;
            }
            
            .card-title {
                font-size: 0.8rem;
            }
            
            .card-text {
                font-size: 0.65rem;
                height: 2.2em;
            }
            
            .price {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-3 py-md-4">
    <h2 class="text-center mb-3 mb-md-4">Our Products</h2>

    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2 g-sm-3">
        <?php if ($result->num_rows > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card product-card h-100" 
                         data-bs-toggle="modal" 
                         data-bs-target="#productModal" 
                         data-id="<?php echo $product['id']; ?>" 
                         data-name="<?php echo sanitize_input($product['name']); ?>" 
                         data-description="<?php echo sanitize_input($product['description']); ?>" 
                         data-price="<?php echo $product['price']; ?>" 
                         data-image="uploads/products/<?php echo sanitize_input($product['image'] ?? 'default.jpg'); ?>">
                        <img src="uploads/products/<?php echo sanitize_input($product['image'] ?? 'default.jpg'); ?>" 
                             class="card-img-top product-img" alt="<?php echo sanitize_input($product['name']); ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo sanitize_input($product['name']); ?></h6>
                            <p class="card-text">
                                <?php echo strlen($product['description']) > 60 ? 
                                    substr(sanitize_input($product['description']), 0, 60) . '...' : 
                                    sanitize_input($product['description']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <span class="price">Rs.<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white rounded-3 p-4 shadow-sm">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="mb-2">No products available</h4>
                    <p class="text-muted">Check back later for new products</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modalProductName" class="modal-title">Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-5">
                <img id="modalProductImage" src="" alt="Product Image" class="img-fluid rounded">
            </div>
            <div class="col-md-7">
                <p id="modalProductDescription"></p>
                <h4 class="text-primary">Rs.<span id="modalProductPrice"></span></h4>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary mt-3" id="modalAddToCartBtn" data-product-id="">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <div id="cartToast" class="toast bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage">
          <i class="fas fa-check-circle me-2"></i>Product added to cart!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fill modal
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function () {
        const name = this.dataset.name;
        const description = this.dataset.description;
        const price = this.dataset.price;
        const image = this.dataset.image;
        const id = this.dataset.id;

        document.getElementById('modalProductName').textContent = name;
        document.getElementById('modalProductDescription').textContent = description;
        document.getElementById('modalProductPrice').textContent = parseFloat(price).toFixed(2);
        document.getElementById('modalProductImage').src = image;
        document.getElementById('modalProductImage').alt = name;
        document.getElementById('modalAddToCartBtn').setAttribute('data-product-id', id);
    });
});

// Add to Cart
document.getElementById('modalAddToCartBtn').addEventListener('click', function () {
    const productId = this.dataset.productId;
    const btn = this;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `product_id=${productId}`
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.textContent = data.cart_count;
                badge.classList.add('badge-animate');
                setTimeout(() => badge.classList.remove('badge-animate'), 400);
            }

            document.getElementById('toastMessage').innerHTML = 
                `<i class="fas fa-check-circle me-2"></i>${data.message}`;
            
            const toast = new bootstrap.Toast(document.getElementById('cartToast'));
            toast.show();
            
            // Close modal after adding to cart
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            }, 1000);
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Add to Cart';
    });
});

// Initialize toasts
document.addEventListener('DOMContentLoaded', function() {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
