// assets/js/cart.js
function updateCartItem(productId, action) {
  fetch("/cart/update", {
    // Update URL based on your routing
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `product_id=${productId}&action=${action}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const cartCountElements = document.querySelectorAll(".cart-count")
        cartCountElements.forEach((element) => {
          element.textContent = data.cart_count
        })
        document.getElementById("subtotal").textContent = Number.parseFloat(data.cart_total).toFixed(2)
        document.getElementById("tax").textContent = Number.parseFloat(data.tax).toFixed(2)
        document.getElementById("final-total").textContent = Number.parseFloat(data.final_total).toFixed(2)
        location.reload()
      }
    })
    .catch((error) => {
      console.error("Error:", error)
    })
}

function removeCartItem(productId) {
  if (confirm("Are you sure you want to remove this item from your cart?")) {
    window.location.href = "/cart/remove/" + productId // Update URL
  }
}

function clearCart() {
  if (confirm("Are you sure you want to clear your cart?")) {
    window.location.href = "/cart/clear" // Update URL
  }
}
