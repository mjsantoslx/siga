<?php $title = 'Tabelas de apoio'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Tabelas de apoio</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<div class="lookup-tabs">
<?php foreach ($tables as $key => $tableMeta): ?>
<a class="button <?= $key === $table ? '' : 'secondary' ?>"
   href="<?= e($config['app']['base_url']) ?>/tabelas?tabela=<?= e($key) ?>">
   <?= e($tableMeta['label']) ?>
</a>
<?php endforeach; ?>
</div>

<h2><?= e($meta['label']) ?></h2>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/novo">Novo registo</a></p>

<table data-sortable-table="1">
<thead><tr><th>ID</th><th>Designação</th><th>Operações</th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= (int)$row['Id'] ?></td>
<td><?= e($row['Designacao']) ?></td>
<td>
<a href="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/<?= (int)$row['Id'] ?>/editar">Editar</a>
<form method="post" action="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/<?= (int)$row['Id'] ?>/eliminar" style="display:inline">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<button type="submit" onclick="return confirm('Eliminar este registo?')">Eliminar</button>
</form>
</td>
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
