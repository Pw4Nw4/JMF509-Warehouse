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
      <div class="footer-col">
        <h4>Diaspora Ordering</h4>
        <ul>
          <li>Send essentials to family in Haiti</li>
          <li>Ships to Haiti & US</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date("Y"); ?> <?php echo defined('BUSINESS_NAME') ? BUSINESS_NAME : 'AyitiCo'; ?>. <?php echo defined('POWERED_BY') ? POWERED_BY : 'Product of F09 tech'; ?>.</p>
    </div>
  </footer>
</div>
</body>
</html>