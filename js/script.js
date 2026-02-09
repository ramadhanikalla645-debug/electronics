// Simple JavaScript for the Electronic Ordering System

// Show/hide password fields
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}

// Form validation for registration
function validateRegistration() {
    var password = document.getElementById('reg_password').value;
    var confirmPassword = document.getElementById('reg_confirm_password').value;
    
    if (password.length < 6) {
        alert("Password must be at least 6 characters long!");
        return false;
    }
    
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }
    
    return true;
}

// Simple search function for products
function searchProducts() {
    var searchText = document.getElementById('search').value.toLowerCase();
    var products = document.querySelectorAll('.product-card');
    
    products.forEach(function(product) {
        var productName = product.querySelector('h3').textContent.toLowerCase();
        if (productName.includes(searchText)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Add to cart function (simple version)
var cart = [];

function addToCart(productId, productName, price, quantity) {
    var item = {
        id: productId,
        name: productName,
        price: price,
        quantity: quantity || 1
    };
    
    // Check if item already in cart
    var existingItem = cart.find(function(item) {
        return item.id === productId;
    });
    
    if (existingItem) {
        existingItem.quantity += quantity || 1;
    } else {
        cart.push(item);
    }
    
    updateCartDisplay();
    alert(productName + " added to cart!");
}

function updateCartDisplay() {
    var cartCount = document.getElementById('cart-count');
    if (cartCount) {
        var totalItems = cart.reduce(function(total, item) {
            return total + item.quantity;
        }, 0);
        cartCount.textContent = totalItems;
    }
}

// Simple quantity validation
function validateQuantity(input) {
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 999;
    var value = parseInt(input.value);
    
    if (value < min) {
        input.value = min;
    } else if (value > max) {
        input.value = max;
    }
}

// Tab switching for login/register/reset
function showTab(tabName) {
    // Hide all tab contents
    var tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(function(tab) {
        tab.style.display = 'none';
    });
    
    // Remove active class from all tab buttons
    var tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(function(button) {
        button.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked button
    event.currentTarget.classList.add('active');
}

// Simple confirmation for delete actions
function confirmDelete(message) {
    return confirm(message || "Are you sure you want to delete this?");
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log("Electronic Ordering System loaded!");
    
    // Initialize cart from localStorage if available
    if (localStorage.getItem('cart')) {
        cart = JSON.parse(localStorage.getItem('cart'));
        updateCartDisplay();
    }
    
    // Save cart to localStorage when leaving page
    window.addEventListener('beforeunload', function() {
        localStorage.setItem('cart', JSON.stringify(cart));
    });
});