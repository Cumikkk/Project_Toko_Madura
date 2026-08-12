file_path = 'admin/doc/outlet/view.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

badge_search = '''<?= htmlspecialchars(['kecamatan'] ?? '-') ?>
                                                <?php if (!empty(['alamat_outlet'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-xs ms-1" 
                                                            onclick='showAlamat(<?= safeJsonAlamat(['nama_outlet']) ?>, <?= safeJsonAlamat(['alamat_outlet']) ?>)'
                                                            title="Lihat Alamat Lengkap">
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>'''
badge_replace = '''<?php if (!empty(['kecamatan']) && ['kecamatan'] !== '-') : ?>
                                                <?php if (!empty(['alamat_outlet'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-lihat-alamat shadow-xs" style="cursor: pointer; font-size: 11px;" onclick='showAlamat(<?= safeJsonAlamat(['nama_outlet']) ?>, <?= safeJsonAlamat(['alamat_outlet']) ?>)' title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(['kecamatan']) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(['kecamatan']) ?></span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>'''

content = content.replace(badge_search, badge_replace)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Replaced view.php")
