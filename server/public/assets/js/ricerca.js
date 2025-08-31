/* global $, URLSearchParams, history */
(function(){
  const $q = $('#q');
  const $sort = $('#sort');
  const $pageSize = $('#page_size');
  const $btnSearch = $('#btn-search');

  const $table = $('#results tbody');
  const $loading = $('#loading');
  const $error = $('#error');
  const $empty = $('#empty');
  const $count = $('#result-count');
  const $pageInd = $('#page-indicator');
  const $prev = $('#prev');
  const $next = $('#next');

  let state = {
    q: '',
    sort: 'nome_asc',
    page: 1,
    page_size: 20,
    total: 0
  };
  let debounceTimer = null;

  function readParamsFromURL(){
    const p = new URLSearchParams(window.location.search);
    state.q = p.get('q') || '';
    state.sort = p.get('sort') || 'nome_asc';
    state.page = parseInt(p.get('page') || '1', 10);
    state.page_size = parseInt(p.get('page_size') || '20', 10);

    $q.val(state.q);
    $sort.val(state.sort);
    $pageSize.val(String(state.page_size));
  }

  function writeParamsToURL(){
    const p = new URLSearchParams();
    if (state.q) p.set('q', state.q);
    p.set('sort', state.sort);
    p.set('page', String(state.page));
    p.set('page_size', String(state.page_size));
    const newUrl = window.location.pathname + '?' + p.toString();
    history.replaceState(null, '', newUrl);
  }

  function setLoading(on){
    $loading.prop('hidden', !on);
    $error.prop('hidden', true);
  }

  function fetchResults(){
    setLoading(true);
    $empty.prop('hidden', true);
    $table.empty();

    const params = {
      q: state.q,
      sort: state.sort,
      page: state.page,
      page_size: state.page_size
    };

    $.ajax({
      url: '/api/prodotti.php',
      method: 'GET',
      data: params,
      dataType: 'json',
      timeout: 8000
    }).done(function(res, _status, xhr){
      setLoading(false);
      state.total = res.total || 0;

      $count.text(`${state.total} risultati`);
      $pageInd.text(`pag. ${state.page}`);

      if (!res.items || res.items.length === 0) {
        $empty.prop('hidden', false);
        $prev.prop('disabled', state.page <= 1);
        const lastPage = Math.max(1, Math.ceil(state.total / state.page_size));
        $next.prop('disabled', state.page >= lastPage);
        return;
      }

      for (const it of res.items) {
        const foto = it.fotoURL ? `<img src="${it.fotoURL}" alt="foto" class="thumb">` : '<span class="muted">—</span>';
        const tr = `
          <tr>
            <td>${it.id}</td>
            <td>${escapeHtml(it.nome || '')}</td>
            <td>${escapeHtml(it.marca || '')}</td>
            <td>${escapeHtml(it.codiceEAN || '')}</td>
            <td>${foto}</td>
          </tr>`;
        $table.append(tr);
      }

      const lastPage = Math.max(1, Math.ceil(state.total / state.page_size));
      $prev.prop('disabled', state.page <= 1);
      $next.prop('disabled', state.page >= lastPage);

    }).fail(function(){
      setLoading(false);
      $error.prop('hidden', false).text('Errore nel caricamento. Riprova.');
    });
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, function(c){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
    });
  }

  function doSearch(resetPage=true){
    if (resetPage) state.page = 1;
    state.q = ($q.val() || '').trim();
    state.sort = $sort.val();
    state.page_size = parseInt($pageSize.val(), 10) || 20;
    writeParamsToURL();
    fetchResults();
  }

  // Eventi UI
  $btnSearch.on('click', function(){ doSearch(true); });

  $q.on('input', function(){
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function(){ doSearch(true); }, 400);
  });

  $sort.on('change', function(){ doSearch(true); });
  $pageSize.on('change', function(){ doSearch(true); });

  $prev.on('click', function(){
    if (state.page > 1) {
      state.page -= 1;
      writeParamsToURL();
      fetchResults();
    }
  });
  $next.on('click', function(){
    const lastPage = Math.max(1, Math.ceil(state.total / state.page_size));
    if (state.page < lastPage) {
      state.page += 1;
      writeParamsToURL();
      fetchResults();
    }
  });

  // init
  $(document).ready(function(){
    readParamsFromURL();
    fetchResults();
  });
})();
