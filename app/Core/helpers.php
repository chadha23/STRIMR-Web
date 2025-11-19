<?php
function base_url() {
    return '/STRIMR/STRIMR-Web/public';
}

function redirect($path = '') {
    header('Location: ' . base_url() . '/' . ltrim($path, '/'));
    exit;
}