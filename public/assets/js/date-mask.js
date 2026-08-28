/* SIGA - Datas em formato europeu dd/mm/aaaa */
(function () {
  function digits(value) {
    return (value || '').replace(/\D/g, '').slice(0, 8);
  }

  function formatEuropeanDate(value) {
    var d = digits(value);
    if (d.length <= 2) return d;
    if (d.length <= 4) return d.slice(0, 2) + '/' + d.slice(2);
    return d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4);
  }

  function attach(input) {
    if (input.dataset.sigaDateMask === '1') return;
    input.dataset.sigaDateMask = '1';
    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('maxlength', '10');
    input.setAttribute('placeholder', 'dd/mm/aaaa');

    input.addEventListener('input', function () {
      var pos = input.selectionStart || 0;
      var before = input.value;
      input.value = formatEuropeanDate(before);
      var diff = input.value.length - before.length;
      try { input.setSelectionRange(pos + diff, pos + diff); } catch (e) {}
    });

    input.addEventListener('blur', function () {
      input.value = formatEuropeanDate(input.value);
    });

    input.value = formatEuropeanDate(input.value);
  }

  function init() {
    document.querySelectorAll(
      'input[data-date-format="eu"], input.date-eu, input[name="DataNascimento"], input[name="DataInscricao"], input[name="DataEvento"]'
    ).forEach(attach);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
