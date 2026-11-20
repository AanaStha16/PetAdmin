<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($table)?> | Pet Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
:root{font-family:system-ui,-apple-system,sans-serif;color:#263047;background:#f4f6fa}*{box-sizing:border-box}body{margin:0}a{color:inherit;text-decoration:none}.layout{display:flex;min-height:100vh}aside{width:220px;background:#202a46;color:#fff;padding:24px 14px;flex-shrink:0}aside h2{font-size:20px;margin:0 8px 30px}aside a{display:block;padding:12px 15px;border-radius:9px;margin-bottom:5px;color:#cbd3e8}aside a.active,aside a:hover{background:#5365d9;color:white}aside a.dash-link{border:1px solid #697797;color:#fff;margin-bottom:15px}main{flex:1;min-width:0;padding:35px;max-width:1500px}h1{margin:0 0 5px;font-size:30px}p.subtitle{color:#71809d;margin:0 0 26px}.card{background:white;border:1px solid #e2e7f2;border-radius:12px;padding:23px;margin:20px 0;box-shadow:0 3px 14px #24305b09}h3{margin:0 0 18px;font-size:19px}.fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:15px}label{display:block;font-size:13px;font-weight:600;color:#52617c;margin-bottom:6px}input,select{width:100%;padding:11px;border:1px solid #cbd3e2;border-radius:7px;background:white;font:inherit;font-size:14px}button,.btn{display:inline-block;border:0;border-radius:8px;background:#5365d9;color:#fff;cursor:pointer;padding:10px 17px;font-size:13px;font-weight:600}.btn.cancel{background:#e8ebf3;color:#34405b}.danger{background:#d64d55}button:hover,.btn:hover{filter:brightness(.92)}.trace{border-left:4px solid #5365d9;background:#f3f5ff;padding:16px 18px;margin:16px 0;border-radius:8px}.trace h3{margin:0 0 10px}.trace ol{margin:0;padding-left:22px}.trace li{margin:5px 0}.actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.actions form{display:inline}.actions .btn,.actions button{padding:7px 11px}table{border-collapse:collapse;width:100%;font-size:13px}th,td{padding:12px;text-align:left;border-bottom:1px solid #e7ecf3}th{color:#52617c;background:#f7f9fd}td{white-space:nowrap}.scroll{overflow-x:auto}.alert{padding:13px 16px;border-radius:8px;margin:18px 0}.error{background:#fff0f0;color:#9d2525}.ok{background:#e8f8ef;color:#167344}.foot{margin-top:22px;display:flex;gap:10px;align-items:center}@media(max-width:780px){.layout{display:block}aside{width:100%;padding:14px}aside h2{margin:0 0 10px}aside a{display:inline-block}main{padding:20px}}
</style></head><body><div class="layout"><aside><h2>🐾 Pet Admin</h2><a class="dash-link" href="index.php">Dashboard</a>
<?php foreach ($tables as $nav): ?><a class="<?= $nav === $table ? 'active' : '' ?>" href="<?=h($entities[$nav]['page'])?>"><?=h($nav)?></a><?php endforeach; ?></aside><main>
<h1><?=h($table)?></h1><p class="subtitle">Manage <?=h(strtolower($table))?> · <?= $count ?> record(s)</p>
<?php if ($notice): ?><div class="alert ok"><?=h($notice)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error" role="alert"><?=h($error)?></div><?php endif; ?>
<?php if ($table === 'Pets' && $trace): ?><section class="trace" aria-label="Backend request and response evidence"><h3>Request → validation → database → response</h3><ol>
<?php foreach ($trace as $step): ?><li><?=h($step)?></li><?php endforeach; ?></ol></section><?php endif; ?>
<section class="card"><h3><?= $editRow ? 'Edit record' : 'Add new record' ?></h3>
<form method="post" action="<?=h($page)?>"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="operation" value="<?= $editRow ? 'update':'add' ?>">
<?php if ($editRow): ?><input type="hidden" name="record" value="<?=h(encode_key($editRow, $keys))?>"><?php endif; ?>
<div class="fields">
<?php foreach ($fields as $name => $spec): $required = str_ends_with($spec, '!'); $type = rtrim($spec, '!'); $val = $_POST[$name] ?? ($editRow[$name] ?? ''); ?>
<div><label for="f_<?=h($name)?>"><?=h($name)?><?= $required ? ' *':'' ?></label>
<?php if (in_array($type, ['pet','owner','food'],true)): ?>
<select class="form-select" id="f_<?=h($name)?>" name="<?=h($name)?>" <?= $required ? 'required':'' ?>><option value="">Choose...</option>
<?php foreach (options_for($pdo,$type) as $opt): ?><option value="<?=h($opt['id'])?>" <?= (string)$val === (string)$opt['id'] ? 'selected':'' ?>><?=h($opt['label'])?></option><?php endforeach; ?></select>
<?php else: $inputType = in_array($type,['number','year','month','money'],true) ? 'number':'text'; $step = $type === 'money' ? '0.01' : '1'; ?>
<input class="form-control" id="f_<?=h($name)?>" name="<?=h($name)?>" type="<?=$inputType?>" <?= $inputType === 'number' ? 'min="' . (in_array($type, ['year','month'], true) || $name === 'Quantity' ? '1' : '0') . '" ' . ($type === 'month' ? 'max="12" ' : ($type === 'year' ? 'max="9999" ' : '')) . 'step="'. $step .'"' : '' ?> value="<?=h($val)?>" <?= $required ? 'required':'' ?>>
<?php endif; ?></div><?php endforeach; ?></div>
<div class="foot"><button class="btn btn-primary" type="submit"><?= $editRow ? 'Save changes':'Add record' ?></button><?php if ($editRow): ?><a class="btn cancel" href="<?=h($page)?>">Cancel edit</a><?php endif; ?></div>
</form></section>
<section class="card"><h3><?=h($table)?> records</h3><div class="scroll"><table class="table table-hover table-striped align-middle"><thead><tr><?php foreach (array_keys($rows[0] ?? array_merge(array_fill_keys($keys,''),array_fill_keys(array_keys($fields),''))) as $col): ?><th><?=h($col)?></th><?php endforeach; ?><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><?php foreach ($row as $cell): ?><td><?=h($cell)?></td><?php endforeach; ?><td><div class="actions"><a class="btn" href="<?=h($page)?>?edit=<?=h(encode_key($row,$keys))?>">Edit</a>
<form method="post" action="<?=h($page)?>" onsubmit="return confirm('Delete this record? Related records may also be removed.');"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="operation" value="delete"><input type="hidden" name="record" value="<?=h(encode_key($row,$keys))?>"><button class="btn btn-danger danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="20">No records found.</td></tr><?php endif; ?></tbody></table></div></section>
</main></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented || form.querySelector('input[name="operation"][value="delete"]')) return;
        const button = form.querySelector('button[type="submit"]');
        if (button) { button.disabled = true; button.textContent = 'Saving...'; }
    });
});
</script>
</body></html>
