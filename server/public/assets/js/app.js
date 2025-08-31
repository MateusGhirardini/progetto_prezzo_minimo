/* global $, window, document */
(function(){
  // evidenzia voce menu corrente
  function setActiveNav(){
    const path = window.location.pathname;
    $('.main-nav .navlink').removeClass('active').each(function(){
      const href = $(this).attr('href');
      if (href === '/' && (path === '/' || path === '/index.html')) {
        $(this).addClass('active');
      } else if (href !== '/' && path.startsWith(href)) {
        $(this).addClass('active');
      }
    });
  }

  // ping health API
  function checkApi(){
    const $badge = $('#api-status');
    $badge.text('API…').removeClass('badge-ok badge-err').addClass('badge-muted');
    $.ajax({
      url: '/api/health.php',
      method: 'GET',
      dataType: 'json',
      timeout: 4000
    }).done(function(res){
      const ok = res && res.status && res.status.web === 'ok' && res.status.db === 'ok';
      if (ok){
        $badge.text('API OK').removeClass('badge-muted badge-err').addClass('badge-ok');
      } else {
        $badge.text('API parziale').removeClass('badge-muted badge-ok').addClass('badge-err');
      }
    }).fail(function(){
      $badge.text('API KO').removeClass('badge-muted badge-ok').addClass('badge-err');
    });
  }

  $(document).ready(function(){
    setActiveNav();
    checkApi();
  });
})();
