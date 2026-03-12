<footer class="site-footer">
    <a href="#" class="back-to-top">Back to top</a>
    <div class="footer-columns">
      <div class="footer-col">
        <h4>Get to Know Us</h4>
        <ul>
          <li><a href="index.php">About AyitiCo</a></li>
          <li><a href="index.php#mission">Our Mission</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Let Us Help You</h4>
        <ul>
          <li><a href="mailto:<?php echo defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@ayitico.com'; ?>">Contact</a></li>
          <li><a href="orders.php">Track Orders</a></li>
          <li>Secure checkout</li>
        </ul>
      </div>
      <div class="footer-col footer-newsletter">
        <h4>Stay Updated</h4>
        <p>Subscribe to get special offers and updates.</p>
        <form method="post" action="subscribe.php" class="newsletter-form">
          <input type="email" name="email" placeholder="Enter your email" required>
          <button type="submit">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date("Y"); ?> <?php echo defined('BUSINESS_NAME') ? BUSINESS_NAME : 'AyitiCo'; ?>. <?php echo defined('POWERED_BY') ? POWERED_BY : 'Product of F09 tech'; ?>.</p>
    </div>
  </footer>
</div>
</body>
</html>