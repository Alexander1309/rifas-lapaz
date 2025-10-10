// Inicialización segura de DataTables para tablas con la clase .js-dt
(function(){
  function initDT(){
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
    jQuery('.js-dt').each(function(){
      var $t = jQuery(this);
      if ($t.data('dt-initialized')) return;
      $t.DataTable({
        responsive: true,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: 'Bfrtip',
        buttons: [
          { extend: 'excelHtml5', className: 'btn btn-sm btn-outline-success', title: document.title },
          { extend: 'pdfHtml5', className: 'btn btn-sm btn-outline-danger', title: document.title },
          { extend: 'print', className: 'btn btn-sm btn-outline-secondary', title: document.title },
          { extend: 'colvis', className: 'btn btn-sm btn-outline-primary' }
        ]
      });
      $t.data('dt-initialized', true);
    });
  }
  // Intentos periódicos por si DataTables carga tarde
  var tries = 0; var iv = setInterval(function(){
    tries++;
    initDT();
    if (tries > 20) clearInterval(iv);
  }, 500);
  // Init on DOM ready
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(initDT, 0);
  } else {
    document.addEventListener('DOMContentLoaded', initDT);
  }
})();
