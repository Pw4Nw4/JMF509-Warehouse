document.addEventListener('DOMContentLoaded', function() {
  var navToggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.nav-wrapper nav');
  if (navToggle && nav) {
    navToggle.addEventListener('click', function() {
      var expanded = navToggle.getAttribute('aria-expanded') === 'true';
      navToggle.setAttribute('aria-expanded', !expanded);
      nav.classList.toggle('is-open', !expanded);
    });
  }

  var backToTop = document.querySelector('.back-to-top');
  if (backToTop) {
    backToTop.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  function updateCartCount() {
    fetch('api.php?action=cart_count', { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
      .then(function(data) {
        var el = document.getElementById('cart-count');
        if (el && data.count !== undefined) {
          el.textContent = data.count;
          el.classList.add('cart-badge-updated');
          setTimeout(function() { el.classList.remove('cart-badge-updated'); }, 300);
        }
      })
      .catch(function() {});
  }
  updateCartCount();
});
