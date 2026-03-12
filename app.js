document.addEventListener('DOMContentLoaded', function() {
  function updateCartCount() {
    fetch('api.php?action=cart_count', { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
      .then(function(data) {
        var el = document.getElementById('cart-count');
        if (el && data.count !== undefined) el.textContent = data.count;
      })
      .catch(function() {});
  }
  updateCartCount();
});
