/* global $, document */
(function(){
  const $tblBody = $('#offerte-table tbody');
  const $loading = $('#offerte-loading');
  const $error = $('#offerte-error');
  const $empty = $('#offerte-empty');
  const $btnRefresh = $('#btn-refresh');

  const $form = $('#offerta-form');
  const $pv = $('#pv');
  const $dInizio = $('#data_inizio');
  const $dFine = $('#data_fine');
  const $righeBody = $('#righe-body');
  const $addRiga = $('#add-riga');
  const $submit = $('#submit-offerta');
  const $status = $('#submit-status');

  // ---- LISTA ----
  function setLoading(on){
    $loading.prop('hidden', !on);
    $error.prop('hidden', true);
  }

  function loadOfferte(){
    setLoading(true);
    $tblBody.empty();
    $empty.prop('hidden', true);

    $.ajax({
      url: '/api/offerte',
      method: 'GET',
      dataType: 'json',
      timeout: 8000
    }).done(function(res){
      setLoading(false);
      const items = (res && res.items) ? res.items : [];
      if (items.length === 0){
        $empty.prop('hidden', false);
        return;
      }
      for (const it of items){
        const periodo = `${escapeHtml(it.dataInizio)} → ${escapeHtml(it.dataFine || '')}`;
        const tr = `
          <tr>
            <td>${it.id}</td>
            <td>${escapeHtml(it.puntoVendita || '')} (#${it.puntoVenditaId})</td>
            <td>${periodo}</td>
            <td>${it.prodotti ?? 0}</td>
          </tr>`;
        $tblBody.append(tr);
      }
    }).fail(function(){
      setLoading(false);
      $error.prop('hidden', false);
    });
  }

  // ---- FORM CREAZIONE ----
  function addRiga(idProdotto='', prezzo=''){
    const tr = $(`
      <tr>
        <td><input type="number" class="inp idp" min="1" placeholder="id prodotto" value="${escapeAttr(idProdotto)}"></td>
        <td><input type="number" class="inp prezzo" step="0.01" min="0.01" placeholder="es. 1.99" value="${escapeAttr(prezzo)}"></td>
        <td><button class="btn btn-danger btn-del" type="button">✕</button></td>
      </tr>
    `);
    tr.find('.btn-del').on('click', function(){ tr.remove(); });
    $righeBody.append(tr);
  }

  function buildPayload(){
    const righe = [];
    $righeBody.find('tr').each(function(){
      const idp = parseInt($(this).find('.idp').val(), 10);
      const prezzo = parseFloat($(this).find('.prezzo').val());
      if (!isNaN(idp) && idp > 0 && !isNaN(prezzo) && prezzo > 0){
        righe.push({ id_prodotto: idp, prezzo: prezzo });
      }
    });
    return {
      id_punto_vendita: parseInt($pv.val(), 10),
      data_inizio: $dInizio.val(),
      data_fine: $dFine.val(),
      righe: righe
    };
  }

  function getCsrf(){
    return $.ajax({ url:'/api/auth/csrf', method:'GET', dataType:'json', timeout:5000 })
      .then(res => res && res.csrf_token ? res.csrf_token : null);
  }

  function submitOfferta(){
    $status.text('Invio…');
    const payload = buildPayload();

    // validazioni base lato client
    if (!payload.id_punto_vendita || !payload.data_inizio || !payload.data_fine || payload.righe.length === 0){
      $status.text('Compila tutti i campi e aggiungi almeno una riga.');
      return;
    }

    return getCsrf().then(function(token){
      if (!token){ $status.text('CSRF non disponibile. Effettua login.'); return; }

      return $.ajax({
        url:'/api/offerte',
        method:'POST',
        data: JSON.stringify(payload),
        contentType: 'application/json; charset=utf-8',
        headers: { 'X-CSRF-Token': token },
        dataType: 'json',
        timeout: 10000
      }).done(function(){
        $status.text('Offerta creata ✅');
        loadOfferte();
        // reset parziale: lascia PV e periodo, svuota righe
        $righeBody.empty();
        addRiga();
      }).fail(function(xhr){
        let msg = 'Errore invio offerta.';
        try {
          const r = xhr.responseJSON;
          if (r && r.error && r.error.message) msg = r.error.message;
        } catch(e){}
        $status.text(msg);
      });
    });
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  function escapeAttr(s){ return String(s ?? '').replace(/"/g,'&quot;'); }

  // init
  $(document).ready(function(){
    // lista
    $('#btn-refresh').on('click', loadOfferte);
    loadOfferte();

    // form: prima riga
    addRiga();
    $addRiga.on('click', function(){ addRiga(); });

    $form.on('submit', function(e){
      e.preventDefault();
      submitOfferta();
    });
  });
})();
