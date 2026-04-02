<?php
// Tentukan informasi developer dan aplikasi
$app_title = "E-SPMI - Sistem Penjaminan Mutu Internal";
$developer_name = "Rifqi Fauzan Sholeh";
$whatsapp_number = "+62 851 6147 3394";
$whatsapp_link = "https://wa.me/" . str_replace(['+', ' '], '', $whatsapp_number) . "?text=Halo%20Kang%20Rifqi%2C%20saya%20mau%20bertanya%20mengenai%20E-SPMI";
?>
<footer class="footer mt-5 py-3 bg-white border-top shadow-sm small">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                
                <div class="text-muted">
                    &copy; <?php echo date('Y'); ?> E-SPMI. All rights reserved.
                </div>
                
                <div class="text-end">
                    <span class="text-secondary me-2">Developed by</span>
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="text-decoration-none text-dark fw-bold me-3">
                        <?php echo $developer_name; ?>
                    </a>
                    
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="text-decoration-none text-success">
                        <i class="bi bi-whatsapp me-1"></i> <?php echo $whatsapp_number; ?>
                    </a>
                </div>
            </div>
        </footer>