<?php $title = 'Associados'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Associados</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/novo">Novo associado</a></p>
<form class="search" method="get">
<input name="q" value="<?= e($search) ?>" placeholder="Nome, NIF ou Cartão de Cidadão">
<button>Pesquisar</button>
</form>
<table data-sortable-table="1">
<thead><tr><th>N.º</th><th>Nome</th><th>NIF</th><th>Companhia(s)</th><th>Secção / Nominativo</th><th>Estado</th><th></th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= e($row['Numero']) ?></td>
<td><?= e($row['Nome']) ?></td>
<td><?= e($row['NIF']) ?></td>
<td><?= e($row['Companhias']) ?></td>
<td><?= e($row['Nominativo'] ?: ($row['Seccao'] ?? '—')) ?></td>
<td><?= (int)$row['Activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><a href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$row['Id'] ?>">Consultar</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table[data-sortable-table]').forEach(function (table) {
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        headers.forEach(function (th, index) {
            th.classList.add('sortable-header');
            th.style.cursor = 'pointer';
            th.title = 'Ordenar';
            th.addEventListener('click', function () {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const current = th.dataset.order === 'asc' ? 'desc' : 'asc';

                headers.forEach(h => {
                    delete h.dataset.order;
                    h.removeAttribute('aria-sort');
                });
                th.dataset.order = current;
                th.setAttribute('aria-sort', current);

                rows.sort(function (a, b) {
                    const av = a.children[index]?.textContent.trim() ?? '';
                    const bv = b.children[index]?.textContent.trim() ?? '';

                    const an = Number(av.replace(/\./g, '').replace(',', '.'));
                    const bn = Number(bv.replace(/\./g, '').replace(',', '.'));

                    if (!Number.isNaN(an) && !Number.isNaN(bn) && av !== '' && bv !== '') {
                        return current === 'asc' ? an - bn : bn - an;
                    }

                    return current === 'asc'
                        ? av.localeCompare(bv, 'pt-PT', { sensitivity: 'base' })
                        : bv.localeCompare(av, 'pt-PT', { sensitivity: 'base' });
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
});
</script>
