<?php
function generateId() {
    return bin2hex(random_bytes(12)); // 24 ký tự hex
}
?>
