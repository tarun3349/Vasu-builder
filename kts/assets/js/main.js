document.addEventListener('DOMContentLoaded',()=>{
  // Add to cart buttons
  document.body.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-add-to-cart]');
    if(btn){
      const pid = btn.getAttribute('data-product-id');
      fetch('cart.php?action=add', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`product_id=${encodeURIComponent(pid)}&csrf_token=${encodeURIComponent(window.csrf||'')}`})
        .then(r=>r.json()).then(d=>{
          alert(d.message||'Added to cart');
          if(d.redirect){ window.location.href=d.redirect; }
        });
    }
  });
});
