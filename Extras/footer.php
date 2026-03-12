<?php
$lastModifiedDate = getlastmod() ? htmlspecialchars(date("F d, Y H:i:s", getlastmod())) : "N/A";
$currentDateTime = htmlspecialchars(date("F j, Y @ g:i:s A"));
?>
  <footer>
    <p>Page last updated: <?php echo $lastModifiedDate; ?> &bull; Server time: <?php echo $currentDateTime; ?></p>
    <p>&copy; <?php echo date("Y"); ?> JMF 509 Warehouse. Essential goods &amp; logistics for Haiti. <a href="mailto:<?php echo defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@jmf509.com'; ?>">Contact</a></p>
  </footer>
</div>
</body>
</html>
