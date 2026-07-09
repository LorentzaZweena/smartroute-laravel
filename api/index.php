<?php

// Paksa pencarian path absolut ke public/index.php bawaan Laravel
$laravelIndex = realpath(__DIR__ . '/../public/index.php');

if ($laravelIndex && file_exists($laravelIndex)) {
    require $laravelIndex;
} else {
    echo "Gagal memuat core Laravel. Periksa struktur folder Anda.";
}