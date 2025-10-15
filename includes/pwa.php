<?php
// PWA meta and service worker registration
$baseUrl = rtrim(SITE_URL, '/');
$path = parse_url($baseUrl, PHP_URL_PATH);
$scope = rtrim(($path ?: '/'), '/') . '/';
?>
<link rel="manifest" href="<?php echo $baseUrl; ?>/public/manifest.webmanifest">
<meta name="theme-color" content="#0077be">
<script>
(function() {
  if ('serviceWorker' in navigator) {
    var swUrl = '<?php echo $baseUrl; ?>/service-worker.js';
    navigator.serviceWorker.register(swUrl, { scope: '<?php echo $scope; ?>' })
      .catch(function(err) { /* no-op */ });
  }
})();
</script>
