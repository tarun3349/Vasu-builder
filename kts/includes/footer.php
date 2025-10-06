<?php $cfg = get_config(); ?>
</main>
<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <strong><?= e($cfg['app']['name']) ?></strong><br/>
      Address: <?= e($cfg['app']['shop_address']) ?>
    </div>
    <div>
      <a class="btn outline" target="_blank" href="https://wa.me/<?= rawurlencode(ltrim($cfg['app']['admin_whatsapp_number'], '+')) ?>?text=Hello%20KTS!">Chat on WhatsApp</a>
    </div>
  </div>
  <script src="<?= asset_url('js/main.js') ?>" defer></script>
</footer>
</body>
</html>
